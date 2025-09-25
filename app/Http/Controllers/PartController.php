<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Part;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PartController extends Controller
{
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

        if ($role === 'manager') {
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

        $part->fill($data)->save();

        return redirect()
            ->route('parts.index', $req->query())
            ->with('ok', 'บันทึกการแก้ไขเรียบร้อย');
    }

}
