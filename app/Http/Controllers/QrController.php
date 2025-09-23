<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Part;
use Barryvdh\DomPDF\Facade\Pdf;

class QrController extends Controller
{


    private function buildPayload(Part $p): string
    {
        // Date -> Y/m/d (ถ้าไม่มี ให้ค่าว่าง)

        $no = "0";
        $date = $p->date ? $p->date->format('Y/m/d') : '';

        // รูปแบบ: No|Part No|Part Name|Supplier code|MOQ|Date(Y/m/d)
        return implode('|', [
            $no,    
            $p->part_no ?? '',
            $p->part_name ?? '',
            $p->supplier_code ?? '',
            isset($p->moq) ? (string)$p->moq : '',
            $date,
        ]);
    }

    public function showForPart(Part $part)
    {
        $payload = $this->buildPayload($part);
        $svg = QrCode::format('svg')->size(320)->margin(1)->generate($payload);

        return view('qr.from_part', [
            'part'    => $part,
            'payload' => $payload,
            'svg'     => $svg,
            'filename'=> $part->part_no ?: 'qr_code',
        ]);
    }

    public function bulkFromIds(Request $req)
    {
        $ids = $req->validate([
            'ids'   => ['required','array','min:1'],
            'ids.*' => ['integer','exists:parts,id'],
        ])['ids'];

        // เก็บ ids ใส่ session แล้ว redirect ไปหน้า GET
        $req->session()->put('bulk_ids', $ids);
        return redirect()->route('parts.qr.bulk.view');
    }

    public function bulkView(Request $req)
    {
        // ดึง ids จาก query ?ids[]= หรือจาก session
        $ids = $req->query('ids', $req->session()->get('bulk_ids', []));
        if (empty($ids)) {
            return redirect()->route('parts.index')->withErrors(['bulk' => 'ยังไม่ได้เลือกรายการ']);
        }

        $parts = \App\Models\Part::whereIn('id', $ids)->orderBy('id')->get();
        // สร้าง items เหมือนเดิม
        $items = $parts->map(function($p){
            $payload = $this->buildPayload($p);
            $svg = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size(320)->margin(1)->generate($payload);
            return [
                'part'    => $p,
                'payload' => $payload,
                'svg'     => $svg,
                'filename'=> $p->part_no ?: ("qr_{$p->id}"),
            ];
        });

        return view('qr.bulk', compact('items'));
    }

    // ของเดิม (ดาวน์โหลด svg/png)
    public function download(Request $req)
    {
        $req->validate([
            'payload'  => ['required','string'],
            'filename' => ['nullable','string','max:200'],
            'format'   => ['required','in:svg,png'],
        ]);

        $payload  = $req->string('payload');
        $filename = trim($req->input('filename') ?: 'qr_code');
        $format   = $req->input('format');

        if ($format === 'svg') {
            $svg = QrCode::format('svg')->size(320)->margin(1)->generate($payload);
            return response($svg)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'.svg"');
        }

        try {
            $png = QrCode::format('png')->size(640)->margin(1)->generate($payload);
            return response($png)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'.png"');
        } catch (\Throwable $e) {
            return back()->withErrors([
                'download' => 'ต้องใช้ PHP ext-gd สำหรับ PNG — ใช้ SVG แทนก่อนได้ครับ',
            ])->withInput();
        }
    }


    public function exportPdf(Request $req)
    {
        $ids = $req->validate([
            'ids'   => ['required','array','min:1'],
            'ids.*' => ['integer','exists:parts,id'],
        ])['ids'];

        $parts = \App\Models\Part::whereIn('id',$ids)->orderBy('id')->get();

        // เตรียมข้อมูล QR + payload
        $items = $parts->map(function($p){
            $date = $p->date ? $p->date->format('Y/m/d') : '';
            $payload = implode('|', [
                $p->no ?? '',
                $p->part_no ?? '',
                $p->part_name ?? '',
                $p->supplier_code ?? '',
                $p->moq ?? '',
                $date,
            ]);
            $svg = QrCode::format('svg')->size(140)->margin(0)->generate($payload);
            return compact('p','payload','svg');
        });

        $pdf = Pdf::loadView('qr.sheet_a4', compact('items'));
        $pdf->setPaper('A4','portrait'); // แนวตั้ง A4

        return $pdf->download('qr_sheet.pdf');
    }

    private function makeItemFromPart(Part $p): array
    {
        $payload = $this->buildPayload($p);
        $svg = QrCode::format('svg')->size(260)->margin(0)->generate($payload); // size พอดีกับบัตร 8 ชิ้น/หน้า
        // ข้อมูลด้านขวา (ปรับข้อความตามต้องการ)
        $info = [
            'Part No' => $p->part_no,
            'Name'    => $p->part_name,
            'Code'    => $p->supplier_code,
            'MOQ'     => $p->moq,
            'Date'    => $p->date ? $p->date->format('Y/m/d') : '',
        ];
        return [
            'part'    => $p,
            'payload' => $payload,
            'svg'     => $svg,
            'info'    => $info,
            'filename'=> $p->part_no ?: ("qr_{$p->id}"),
        ];
    }

    // ==== ใหม่: Print A4 (8 ชิ้น/หน้า) - Part เดี่ยว ====
    public function printSingle(Request $req)
    {
        $data = $req->validate([
            'part_id' => ['required','integer','exists:parts,id'],
            'copies'  => ['nullable','integer','min:1','max:999'],
        ]);

        $copies = (int)($data['copies'] ?? 8);
        $part   = Part::findOrFail($data['part_id']);

        $item   = $this->makeItemFromPart($part);
        // ทำซ้ำตาม copies
        $items = array_fill(0, $copies, $item);

        return view('qr.print_a4', [
            'title'     => "Print A4 — {$part->part_no}",
            'items'     => $items,
            'per_page'  => 8,
        ]);
    }

    public function printBulk(Request $req)
    {
        $data = $req->validate([
            'qty'      => ['required','array'],                // qty[<part_id>] => จำนวน
            'qty.*'    => ['nullable','integer','min:0','max:999'],
        ]);

        // กรองเอาเฉพาะรายการที่ qty > 0
        $qtyMap = collect($data['qty'] ?? [])
            ->filter(fn($v) => is_numeric($v) && (int)$v > 0)
            ->map(fn($v) => (int)$v);

        if ($qtyMap->isEmpty()) {
            return back()->withErrors(['qty' => 'กรุณากำหนดจำนวนอย่างน้อย 1 ชิ้น'])->withInput();
        }

        $partIds = $qtyMap->keys()->all();
        $parts   = Part::whereIn('id', $partIds)->get()->keyBy('id');

        $items = [];
        foreach ($qtyMap as $pid => $count) {
            $p = $parts[$pid] ?? null;
            if (!$p) continue;
            $item = $this->makeItemFromPart($p);
            for ($i=0; $i<$count; $i++) {
                $items[] = $item;
            }
        }

        return view('qr.print_a4', [
            'title'     => "Print A4 — ".count($items)." ชิ้น",
            'items'     => $items,
            'per_page'  => 8,
        ]);
    }
    
}
