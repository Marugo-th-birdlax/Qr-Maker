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

        $parts = $q->orderByDesc('id')->paginate(20)->withQueryString();
        $supplierCodes = Part::select('supplier_code')->distinct()->pluck('supplier_code')->filter();

        return view('parts.index', compact('parts','supplierCodes'));
    }

    public function createImport()
    {
        return view('parts.import');
    }

    public function storeImport(Request $req)
    {
        $req->validate([
            'file' => ['required','file','mimes:csv,txt'],
            'date_format' => ['nullable','in:Y-m-d,d/m/Y,m/d/Y,d-m-Y,m-d-Y']
        ]);

        $file = $req->file('file')->getRealPath();
        [$delimiter, $enclosure] = $this->detectDelimiter($file);

        $fh = fopen($file,'r');
        if (!$fh) return back()->withErrors(['file'=>'อ่านไฟล์ไม่สำเร็จ']);

        // อ่าน header
        $headers = fgetcsv($fh, 0, $delimiter, $enclosure) ?: [];
        $map = $this->normalizeHeaderMap($headers);

        $dateFormat = $req->input('date_format'); // ถ้าไม่ใส่ จะพยายามเดา

        DB::beginTransaction();
        try {
            $inserted = 0; $updated = 0;
            while (($row = fgetcsv($fh, 0, $delimiter, $enclosure)) !== false) {
                $data = $this->rowToAssoc($map, $row);

                // ดึงคอลัมน์ (รองรับชื่อหัวหลายแบบ)
                $no            = $this->getVal($data, ['no','No']);
                $partNo        = $this->getVal($data, ['part_no','Part No','PartNo','Part_Number']);
                $partName      = $this->getVal($data, ['part_name','Part Name','PartName']);
                $supplierName  = $this->getVal($data, ['supplier_name','Supplier Name','Supplier']);
                $supplierCode  = $this->getVal($data, ['supplier_code','Supplier code','SupplierCode','Code']);
                $moqRaw        = $this->getVal($data, ['moq','MOQ']);
                $dateRaw       = $this->getVal($data, ['date','Date']);

                $moq = is_numeric($moqRaw) ? (int)$moqRaw : null;
                $date = $this->parseDateFlexible($dateRaw, $dateFormat);

                // สร้าง payload สำหรับ QR (ปรับรูปแบบตามที่ต้องใช้จริง)
                $payload = implode('|', [
                    $no ?? '', $partNo ?? '', $partName ?? '', $supplierName ?? '', $supplierCode ?? '', $moq ?? '', optional($date)->format('Y-m-d')
                ]);

                if (!$partNo) { continue; }

                $values = [
                    'no'            => $no ?: null,
                    'part_name'     => $partName,
                    'supplier_name' => $supplierName,
                    'supplier_code' => $supplierCode,
                    'moq'           => $moq,
                    'date'          => $date,
                    'qr_payload'    => $payload,
                    'updated_at'    => now(),
                ];

                // upsert โดยใช้ part_no เป็น key
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

            return redirect()->route('parts.index')->with('ok', "นำเข้าเสร็จ: เพิ่มใหม่ $inserted รายการ, อัปเดต $updated รายการ");
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($fh);
            return back()->withErrors(['file'=>$e->getMessage()]);
        }
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

    private function normalizeHeaderMap(array $headers): array
    {
        $map = [];
        foreach ($headers as $i => $h) {
            $key = strtolower(trim(preg_replace('/\s+/',' ', $h)));
            $map[$i] = $key;
        }
        return $map;
    }

    private function rowToAssoc(array $map, array $row): array
    {
        $out = [];
        foreach ($row as $i => $val) {
            $out[$map[$i] ?? "col_$i"] = trim($val);
        }
        return $out;
    }

    private function getVal(array $data, array $keys)
    {
        foreach ($keys as $k) {
            $norm = strtolower($k);
            if (array_key_exists($norm, $data) && $data[$norm] !== '') return $data[$norm];
        }
        // เผื่อ header เป็นรูปแบบมีช่องว่างต่างกัน
        foreach ($keys as $k) {
            $norm = strtolower(preg_replace('/\s+/', ' ', $k));
            if (array_key_exists($norm, $data) && $data[$norm] !== '') return $data[$norm];
        }
        return null;
    }

    private function parseDateFlexible(?string $raw, ?string $preferred)
    {
        if (!$raw) return null;

        // ถ้าผู้ใช้ระบุรูปแบบมา
        if ($preferred) {
            try { return Carbon::createFromFormat($preferred, $raw); } catch (\Throwable $e) {}
        }

        // เดารูปแบบทั่วไป
        $candidates = ['Y-m-d','d/m/Y','m/d/Y','d-m-Y','m-d-Y'];
        foreach ($candidates as $fmt) {
            try { return Carbon::createFromFormat($fmt, $raw); } catch (\Throwable $e) {}
        }

        // ปล่อยให้ Carbon เดา (อาจผิดได้ถ้าข้อมูลกำกวม)
        try { return Carbon::parse($raw); } catch (\Throwable $e) { return null; }
    }
}
