<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Part;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\PartHistory;        
use Illuminate\Support\Arr;        

class PartController extends Controller
{

    // ====== Audit config & helpers ======
    protected array $auditFields = [
        'no','pic','type','part_no','part_name','supplier_name','supplier_code',
        'supplier','location','moq','qty_per_box','remark','item_no','unit','date',
        'qr_payload','is_active','deactivated_at',
    ];

    protected function actorId(Request $request): ?int
    {
        // ถ้ามี user.id ใน session ให้คืนค่า (ถ้าไม่มี ก็คืน null)
        return (int) data_get($request->session()->get('user'), 'id');
    }

    protected function logHistory(Part $part, ?int $userId, string $action, ?array $before, ?array $after, array $changedFields = [], ?string $note = null): void
    {
        PartHistory::create([
            'part_id'        => $part->id,
            'user_id'        => $userId,
            'action'         => $action,           // create|update|delete|activate|deactivate
            'before'         => $before,
            'after'          => $after,
            'changed_fields' => array_values($changedFields),
            'note'           => $note,
        ]);
    }


    public function index(Request $req)
    {
        $q = Part::query();

        if ($kw = trim($req->get('q',''))) {
            $q->where(function($w) use ($kw) {
                $w->where('part_no','like',"%$kw%")
                ->orWhere('part_name','like',"%$kw%")
                ->orWhere('supplier_name','like',"%$kw%")
                ->orWhere('supplier_code','like',"%$kw%");
            });
        }

        if ($sc = $req->get('supplier_code')) {
            $q->where('supplier_code',$sc);
        }

        // ===== NEW: ตัวกรองใหม่ =====
        if ($t = $req->get('type')) {
            $q->where('type', $t);
        }
        if ($sup = $req->get('supplier')) { // อันนี้คือ "กลุ่ม/หมวด" ตามคอลัมน์ใหม่ ไม่ใช่ supplier_name
            $q->where('supplier', $sup);
        }
        if ($loc = $req->get('location')) {
            $q->where('location', $loc);
        }
        // แสดงเฉพาะที่เปิดใช้งานเป็นค่าเริ่มต้น (active=1|0|all)
        $active = $req->get('active', '1');
        if ($active === '1') {
            $q->where('is_active', true);
        } elseif ($active === '0') {
            $q->where('is_active', false);
        }

        // =============================

        $parts = $q->orderByDesc('id')->paginate(20)->withQueryString();

        $supplierCodes = Part::select('supplier_code')->distinct()->pluck('supplier_code')->filter();

        // ===== NEW: รายการตัวเลือกสำหรับ dropdown =====
        $types     = Part::select('type')->distinct()->pluck('type')->filter()->values();
        $suppliers = Part::select('supplier')->distinct()->pluck('supplier')->filter()->values();
        $locations = Part::select('location')->distinct()->pluck('location')->filter()->values();
        // ==============================================

        // เพิ่มตัวแปรลง view
        return view('parts.index', compact('parts','supplierCodes','types','suppliers','locations')); // NEW
    }


    public function createImport()
    {
        return view('parts.import');
    }

    public function storeImport(Request $req)
    {
        $req->validate([
            'file' => ['required','file','mimes:csv,txt'],
            'date_format' => ['nullable','in:Y-m-d,Y/m/d,d/m/Y,m/d/Y,d-m-Y,m-d-Y']
        ]);

        $file = $req->file('file')->getRealPath();
        [$delimiter, $enclosure] = $this->detectDelimiter($file);

        $fh = fopen($file,'r');
        if (!$fh) return back()->withErrors(['file'=>'อ่านไฟล์ไม่สำเร็จ']);

        $headers = fgetcsv($fh, 0, $delimiter, $enclosure) ?: [];
        $map = $this->normalizeHeaderMap($headers);
        $dateFormat = $req->input('date_format');

        DB::beginTransaction();
        try {
            $inserted = 0; $updated = 0; 
            $skipped  = 0; // ← NEW: ย้ายออกมาประกาศก่อนลูป

            while (($row = fgetcsv($fh, 0, $delimiter, $enclosure)) !== false) {
                $data = $this->rowToAssoc($map, $row);

                $no            = $this->getVal($data, ['no','No']);
                $partNo        = $this->getVal($data, [
                                    'part no','part no.','part_no','part number','partnumber','partno'
                                ]);
                $partName      = $this->getVal($data, ['part name','part_name','partname','part']);
                $supplierName  = $this->getVal($data, ['supplier name','supplier_name']);
                $supplierCode  = $this->getVal($data, ['supplier code','supplier_code','code']);

                // NEW: คอลัมน์ใหม่
                $pic           = $this->getVal($data, ['pic','PIC']);
                $type          = $this->getVal($data, ['type','TYPE']);
                $supplierGroup = $this->getVal($data, ['supplier','SUPPLIER']); // กลุ่ม/หมวด
                $location      = $this->getVal($data, ['location','Location']);
                $qtyRaw        = $this->getVal($data, [
                                    "q'ty /box","q’ty /box","qty /box","qty per box","qty_per_box","q'ty per box","Q'ty /Box","box"
                                ]); // ← เหลือแค่ครั้งเดียว
                $remark        = $this->getVal($data, ['remark','Remark']);
                $itemNo        = $this->getVal($data, ['item no','item no.','item_no','Item No.']);
                $unit          = $this->getVal($data, ['unit','UNIT']);

                // MOQ (หัวจริงมีขึ้นบรรทัด → normalize แล้วเหลือแบบนี้)
                $moqRaw = $this->getVal($data, [
                    'moq','minimum order quantity (moq) : pcs','minimum order quantity: pcs'
                ]);
                $dateRaw = $this->getVal($data, ['date','Date']);

                $toInt = function($v) {
                    if ($v === null || $v === '') return null;
                    $v = preg_replace('/[^\d\-]/', '', (string)$v);
                    return ($v === '' || $v === '-') ? null : (int)$v;
                };
                $qty = $toInt($qtyRaw);
                $moq = $toInt($moqRaw);
                $date = $this->parseDateFlexible($dateRaw, $dateFormat);

                $payload = implode('|', [
                    $no ?? '', $partNo ?? '', $partName ?? '', $supplierName ?? '', $supplierCode ?? '', $moq ?? '', optional($date)->format('Y-m-d')
                ]);

                if (!$partNo) { $skipped++; continue; } // ← นับข้ามได้จริง

                $values = [
                    'no'            => $no ?: null,
                    'part_name'     => $partName,
                    'supplier_name' => $supplierName,
                    'supplier_code' => $supplierCode,

                    // NEW: map คอลัมน์ใหม่
                    'pic'           => $pic,
                    'type'          => $type,
                    'supplier'      => $supplierGroup,
                    'location'      => $location,
                    'qty_per_box'   => $qty,
                    'remark'        => $remark,
                    'item_no'       => $itemNo,
                    'unit'          => $unit,

                    'moq'           => $moq,
                    'date'          => $date,
                    'qr_payload'    => $payload,
                    'updated_at'    => now(),
                    'is_active'     => true,   
                ];

                $existing = Part::where('part_no', $partNo)->first();
                if ($existing) {
                    $existing->update($values);
                    $updated++;
                } else {
                    Part::create(array_merge(['part_no'=>$partNo], $values));
                    $inserted++;
                }
            }

            DB::commit();
            fclose($fh);

            return redirect()->route('parts.index')
                ->with('ok', "นำเข้าเสร็จ: เพิ่มใหม่ $inserted รายการ, อัปเดต $updated รายการ, ข้าม $skipped รายการ");
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($fh);
            return back()->withErrors(['file'=>$e->getMessage()]);
        }
    }

    private function toUtf8Clean(?string $s): ?string
    {
        if ($s === null) return null;

        // เคลียร์อักขระกวนใจ
        $s = str_replace(
            ["\xC2\xA0", "\xE2\x80\x8B", "\xE2\x80\x8C", "\xE2\x80\x8D"], // NBSP & zero-width
            ' ',
            $s
        );
        $s = str_replace(['’','‘','“','”'], ["'", "'", '"', '"'], $s);   // curly quotes → ปกติ
        $s = str_replace(["\xE2\x80\x93","\xE2\x80\x94"], '-', $s);      // en/em dash → -

        // ถ้าเป็น UTF-8 อยู่แล้ว ก็คืนเลย
        if (mb_check_encoding($s, 'UTF-8')) {
            $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $s);
            return trim($s);
        }

        // ลอง convert จาก encoding ที่เจอบ่อย (ไม่ error แม้ไม่รองรับ — iconv จะคืน false)
        foreach (['Windows-1252','ISO-8859-1','ISO-8859-11','CP874','Windows-874'] as $enc) {
            $converted = @iconv($enc, 'UTF-8//IGNORE', $s);
            if ($converted !== false && $converted !== '') {
                $s = $converted;
                break;
            }
        }

        // ตัด byte ที่ไม่ใช่ UTF-8 ทิ้ง (กัน \x9D)
        $s = @iconv('UTF-8', 'UTF-8//IGNORE', $s);
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $s);

        return trim($s);
    }



    private function detectDelimiter(string $path): array
    {
        // ลองเดา , | \t ; (ตามลำดับ)
        $candidates = [',','|',"\t",';'];
        $enclosure = '"';
        $first = file($path, FILE_IGNORE_NEW_LINES)[0] ?? '';
        $best = ',';
        $maxCount = -1;
        foreach ($candidates as $d) {
            $count = count(str_getcsv($first, $d, $enclosure));
            if ($count > $maxCount) { $maxCount = $count; $best = $d; }
        }
        return [$best, $enclosure];
    }

    private function normalizeHeaderKey(string $h): string
    {
        // ตัด BOM
        $h = preg_replace('/^\xEF\xBB\xBF/', '', $h);

        // แทนที่ NBSP และ zero-width ด้วยช่องว่างปกติ
        $h = str_replace(
            ["\xC2\xA0", "\xE2\x80\x8B", "\xE2\x80\x8C", "\xE2\x80\x8D"],
            ' ',
            $h
        );

        // แปลงขึ้นบรรทัดเป็น space
        $h = preg_replace("/[\r\n]+/", ' ', $h);

        // แทน curly quotes เป็นปกติ
        $h = str_replace(['’','‘','“','”'], ["'", "'", '"', '"'], $h);

        // ตัดช่องว่างหัวท้าย + ยุบหลายช่องว่างเป็นช่องเดียว
        $h = preg_replace('/\s+/', ' ', trim($h));

        // ตัดจุดท้ายหัว (เช่น "Part No.")
        $h = preg_replace('/\.+$/', '', $h);

        return strtolower($h);
    }

    private function normalizeHeaderMap(array $headers): array
    {
        $map = [];
        foreach ($headers as $i => $h) {
            $map[$i] = $this->normalizeHeaderKey((string)$h); // ใช้ตัวช่วยใหม่
        }
        return $map;
    }

    private function rowToAssoc(array $map, array $row): array
    {
        $out = [];
        foreach ($row as $i => $val) {
            $key = $map[$i] ?? "col_$i";
            $out[$key] = $this->toUtf8Clean(is_string($val) ? $val : (string)$val);
        }
        return $out;
    }



    private function getVal(array $data, array $keys)
    {
        foreach ($keys as $k) {
            $norm = $this->normalizeHeaderKey((string)$k); // ใช้ตัวช่วยใหม่
            if (array_key_exists($norm, $data) && $data[$norm] !== '') {
                return $data[$norm];
            }
        }
        return null;
    }
    
    private function parseDateFlexible(?string $raw, ?string $preferred)
    {
        if (!$raw) return null;

        if ($preferred) {
            try { return \Carbon\Carbon::createFromFormat($preferred, $raw); } catch (\Throwable $e) {}
        }

        // เรียงให้ Y/m/d มาก่อน
        $candidates = ['Y/m/d','Y-m-d','d/m/Y','m/d/Y','d-m-Y','m-d-Y'];
        foreach ($candidates as $fmt) {
            try { return \Carbon\Carbon::createFromFormat($fmt, $raw); } catch (\Throwable $e) {}
        }

        try { return \Carbon\Carbon::parse($raw); } catch (\Throwable $e) { return null; }
    }



    public function edit(Part $part)
    {
        return view('parts.edit', compact('part'));
    }

    public function update(Request $req, Part $part)
    {
        $role = data_get($req->session()->get('user'), 'role', 'user');

        if ($role === 'pp') {
            // ให้แก้ได้เฉพาะ 2 ช่องนี้เท่านั้น
            $data = $req->validate([
                'qty_per_box' => ['nullable','integer','min:0'],
                'moq'         => ['nullable','integer','min:0'],
            ]);
        } else { // admin เต็มสิทธิ์
            $data = $req->validate([
                'no'            => ['nullable','string','max:50'],
                'part_name'     => ['nullable','string','max:255'],
                'supplier_name' => ['nullable','string','max:255'],
                'supplier_code' => ['nullable','string','max:64'],
                'pic'           => ['nullable','string','max:50'],
                'type'          => ['nullable','string','max:50'],
                'supplier'      => ['nullable','string','max:100'],
                'location'      => ['nullable','string','max:100'],
                'qty_per_box'   => ['nullable','integer','min:0'],
                'moq'           => ['nullable','integer','min:0'],
                'remark'        => ['nullable','string'],
                'item_no'       => ['nullable','string','max:100'],
                'unit'          => ['nullable','string','max:20'],
                'date'          => ['nullable','date'],
            ]);
        }
        // เก็บประวัติ (แก้ไข)
        
        $beforeAll = Arr::only($part->getAttributes(), $this->auditFields);

        $part->fill($data);
        // ถ้ามีคอลัมน์ updated_by ให้ปลดคอมเมนต์บรรทัดด้านล่าง
        // $part->updated_by = $this->actorId($req);

        $dirty = array_intersect(array_keys($part->getDirty()), $this->auditFields);

        if (count($dirty) === 0) {
            $part->save(); // อัปเดต timestamp อย่างเดียว
            return redirect()->route('parts.index', $req->query())->with('ok', 'ไม่มีการเปลี่ยนแปลง');
        }

        $part->save();

        $afterAll   = Arr::only($part->fresh()->getAttributes(), $this->auditFields);
        $beforeOnly = Arr::only($beforeAll, $dirty);
        $afterOnly  = Arr::only($afterAll,  $dirty);

        $this->logHistory(
            $part,
            $this->actorId($req),
            'update',
            $beforeOnly,
            $afterOnly,
            $dirty
        );

        return redirect()->route('parts.index', $req->query())->with('ok', 'บันทึกการแก้ไขเรียบร้อย');

    }


    ########################################################## Create Part#############

    public function create()
    {
        return view('parts.create');
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'part_no'        => ['required','string','max:255'],
            'part_name'      => ['required','string','max:255'],
            'supplier_name'  => ['nullable','string','max:255'],
            'supplier_code'  => ['nullable','string','max:255'],
            'pic'            => ['nullable','string','max:255'],
            'type'           => ['nullable','string','max:100'],
            'supplier'       => ['nullable','string','max:100'],
            'location'       => ['nullable','string','max:255'],
            'qty_per_box'    => ['nullable','integer','min:0'],
            'remark'         => ['nullable','string','max:500'],
            'item_no'        => ['nullable','string','max:100'],
            'unit'           => ['nullable','string','max:50'],
            'moq'            => ['nullable','integer','min:0'],
            'date'           => ['nullable','string','max:20'], // รับเป็นสตริงก่อน
        ]);

        $data['no'] = 0;

        // แปลงวันที่สตริง -> date (รองรับ Y/m/d และ Y-m-d)
        if (!empty($data['date'])) {
            try {
                $data['date'] = \Carbon\Carbon::createFromFormat('Y/m/d', $data['date'])->format('Y-m-d');
            } catch (\Throwable $e) {
                try {
                    $data['date'] = \Carbon\Carbon::createFromFormat('Y-m-d', $data['date'])->format('Y-m-d');
                } catch (\Throwable $e2) {
                    $data['date'] = null;
                }
            }
        }

        // เปิดใช้งานทันที
        $data['is_active'] = true;

        $part = Part::create($data);

        // บันทึกประวัติ (สร้าง)
        $this->logHistory(
            $part,
            $this->actorId($req),
            'create',
            null,
            Arr::only($part->fresh()->toArray(), $this->auditFields),
            $this->auditFields
        );

        return redirect()->route('parts.index')->with('ok', 'เพิ่มข้อมูลเรียบร้อยแล้ว');
    }



    public function __construct()
    {
        // เดิม: $this->middleware('role:pc')->only(['destroy','deleteConfirm']);
        $this->middleware('role:admin,pc')->only(['destroy','deleteConfirm']); // ✅
    }



    public function deleteConfirm(Part $part)
    {
        return view('parts.delete', compact('part'));
    }

    public function destroy(Request $request, Part $part)
    {
        try {
            $snapshot = Arr::only($part->getAttributes(), $this->auditFields);

            $part->delete(); // ลบจริง (hard delete)

            $this->logHistory(
                $part,
                $this->actorId($request),
                'delete',
                $snapshot,
                null,
                array_keys($snapshot)
            );

            return redirect()->route('parts.index')->with('ok', 'ลบรายการเรียบร้อย');
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors('ลบไม่ได้: ข้อมูลอาจถูกอ้างอิงอยู่หรือเกิดข้อผิดพลาด');
        }
    }

    public function activate(Request $request, Part $part)
    {
        if ($part->is_active) return back()->with('ok','รายการนี้เปิดใช้งานอยู่แล้ว');

        $before = Arr::only($part->getAttributes(), ['is_active','deactivated_at']);
        $part->is_active = true;
        $part->deactivated_at = null;
        // ถ้ามีคอลัมน์ updated_by ให้ปลดคอมเมนต์
        // $part->updated_by = $this->actorId($request);
        $part->save();

        $after = Arr::only($part->getAttributes(), ['is_active','deactivated_at']);

        $this->logHistory($part, $this->actorId($request), 'activate', $before, $after, ['is_active','deactivated_at']);

        return back()->with('ok','เปิดใช้งานแล้ว');
    }

    public function deactivate(Request $request, Part $part)
    {
        if (!$part->is_active) return back()->with('ok','รายการนี้ถูกปิดใช้งานอยู่แล้ว');

        $before = Arr::only($part->getAttributes(), ['is_active','deactivated_at']);
        $part->is_active = false;
        $part->deactivated_at = now();
        // ถ้ามีคอลัมน์ updated_by ให้ปลดคอมเมนต์
        // $part->updated_by = $this->actorId($request);
        $part->save();

        $after = Arr::only($part->getAttributes(), ['is_active','deactivated_at']);

        $this->logHistory($part, $this->actorId($request), 'deactivate', $before, $after, ['is_active','deactivated_at']);

        return back()->with('ok','ปิดใช้งานแล้ว');
    }



    ########################################################### History #############
    
    public function history(Request $request, Part $part)
    {
        $q = \App\Models\PartHistory::where('part_id', $part->id)->with('user');

        if (($action = $request->get('action')) && $action !== 'all') {
            $q->where('action', $action);
        }
        if ($user = trim($request->get('user',''))) {
            $q->whereHas('user', fn($w) => $w->where('name','like',"%{$user}%"));
        }
        if (($field = $request->get('field')) && $field !== 'all') {
            $q->where(fn($w) =>
                $w->whereJsonContains('changed_fields',$field)
                ->orWhere('changed_fields','like','%"'.$field.'"%')
            );
        }
        if ($from = $request->get('from')) $q->whereDate('created_at','>=',$from);
        if ($to   = $request->get('to'))   $q->whereDate('created_at','<=',$to);

        $perPage   = max(10, min(100, (int)$request->get('per_page',20)));
        $histories = $q->latest()->paginate($perPage)->withQueryString();

        $actions    = ['create','update','activate','deactivate','delete'];
        $fieldsList = array_keys([
            'part_no'=>1,'part_name'=>1,'supplier_name'=>1,'supplier_code'=>1,'supplier'=>1,'pic'=>1,'type'=>1,
            'location'=>1,'qty_per_box'=>1,'moq'=>1,'item_no'=>1,'unit'=>1,'remark'=>1,'date'=>1,'qr_payload'=>1,
            'is_active'=>1,'deactivated_at'=>1,'no'=>1,
        ]);

        return view('parts.history', compact('part','histories','actions','fieldsList'));
    }



    #################### setting is_adctive Restore part #######
    public function settings(\Illuminate\Http\Request $req)
    {
        // กรองเบื้องต้น (เลือกได้)
        $kw = trim($req->get('q', ''));

        $inactive = Part::inactive()
            ->when($kw, fn($q)=>$q->where(function($w) use ($kw){
                $w->where('part_no','like',"%{$kw}%")
                ->orWhere('part_name','like',"%{$kw}%")
                ->orWhere('supplier_name','like',"%{$kw}%");
            }))
            ->orderBy('part_no')
            ->paginate(20, ['*'], 'inactive_page')
            ->withQueryString();

        $trashed = Part::onlyTrashed()
            ->when($kw, fn($q)=>$q->where(function($w) use ($kw){
                $w->where('part_no','like',"%{$kw}%")
                ->orWhere('part_name','like',"%{$kw}%")
                ->orWhere('supplier_name','like',"%{$kw}%");
            }))
            ->orderByDesc('deleted_at')
            ->paginate(20, ['*'], 'trash_page')
            ->withQueryString();

        return view('parts.settings', compact('inactive','trashed','kw'));
    }

    public function restore($id)
    {
        $part = Part::withTrashed()->findOrFail($id);
        $part->restore();

        // (ออปชัน) บันทึก part_history ที่นี่ได้
        // PartHistory::create([... 'action' => 'restore', ...]);

        return back()->with('ok', "กู้คืน {$part->part_no} แล้ว");
    }

    public function forceDelete($id)
    {
        $part = Part::withTrashed()->findOrFail($id);
        $no = $part->part_no;

        $part->forceDelete();

        // (ออปชัน) บันทึก part_history: action = 'force_delete'

        return back()->with('ok', "ลบถาวร {$no} แล้ว");
    }



}
