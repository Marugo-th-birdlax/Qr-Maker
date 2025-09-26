<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>{{ $title ?? 'Print A4' }}</title>
  <style>
    @page { size: A4; margin: 10mm; }
    @media print { .no-print { display:none !important; } }

    /* ===== ปรับขนาดได้ง่ายด้วยตัวแปร ===== */
    :root{
      --cell-h:   44mm;  /* สูงต่อชิ้น (เดิม 40mm) */
      --gap-v:     10mm;    /* ช่องว่างแนวตั้งระหว่างป้าย */
      --gap-h:     2mm;    /* ช่องว่างแนวนอนระหว่างป้าย */
      --qr:       28mm;    /* ขนาด QR */
      --left-col: 30mm;    /* ความกว้างคอลัมน์ซ้าย (QR) */
      --label-col:22mm;    /* ความกว้างคอลัมน์ชื่อฟิลด์ */
    }

    body { margin:0; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial; color:#111; }
    .toolbar { padding:8px 10px; background:#f8fafc; border-bottom:1px solid #e5e7eb; position:sticky; top:0; }

    .sheet { page-break-after: always; }

    /* 10 ชิ้น/หน้า */
    .grid10{
      display:grid;
      grid-template-columns: 1fr 1fr;
      grid-auto-rows: var(--cell-h);
      gap: var(--gap-v) var(--gap-h);
    }

    /* กรอบป้าย */
    .plate{
      height:100%;
      border:1.2px solid #111;
      border-radius:3mm;
      padding:2.4mm 3mm 3mm;            /* ลด padding ให้ info ใส่ได้มากขึ้น */
      display:flex; flex-direction:column; background:#fff;
    }

    /* หัวบริษัท */
    .company{
      text-align:center; font-weight:800;
      font-size:12px; letter-spacing:.2px;
      margin-bottom:1.6mm;               /* เว้นน้อยลง */
      white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
    }

    /* เนื้อหา: ซ้าย QR / ขวา รายการ */
    .content{
      display:grid; grid-template-columns: var(--left-col) 1fr;
      gap:3mm; align-items:stretch; flex:1; min-height:0;
    }

    .qrbox{ width:var(--qr); height:var(--qr); margin:auto; display:flex; align-items:center; justify-content:center; }
    .qrbox svg{ width:100%; height:100%; display:block; }

    /* กล่องข้อมูลเป็น Grid 6 แถวเท่ากันพอดีกับพื้นที่ -> แถวจะเตี้ยลงโดยอัตโนมัติ */
    .info{
      display:grid; grid-template-rows: repeat(6, 1fr);
      border:1px solid #111; border-radius:1.5mm; overflow:hidden;
      font-size:10px; line-height:1.05;           /* ตัวเล็กลงเล็กน้อย */
      min-height:0;
    }

    .row{
      display:grid; grid-template-columns: var(--label-col) 1fr;
      align-items:center;
      padding:1px 5px;                  /* ช่องข้อมูลเล็กลง */
      border-top:1px solid #111;
      min-height:0;                      /* ให้ยืด/ยุบได้ตามพื้นที่ */
    }
    .row:first-child{ border-top:0; }

    .k{ font-weight:700; padding-right:4px; }
    .v{ overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .btn{ padding:6px 10px; border-radius:8px; background:#4f46e5; color:#fff; border:0; cursor:pointer; }
  </style>
</head>
<body>
  <div class="toolbar no-print">
    <button class="btn" onclick="window.print()">พิมพ์</button>
    <span style="margin-left:8px; color:#64748b;">โหมดพิมพ์ A4 — 10 ชิ้น/หน้า</span>
  </div>

  @php
    $perPage  = $per_page ?? 10;
    $company  = config('app.company_name', 'MARUGO RUBBER (THAILAND) CO., LTD');
    $renderPages = [];
    if (isset($pages) && is_iterable($pages)) {
      $renderPages = collect($pages)->map(fn($pg)=>['part'=>$pg['part']??null,'cells'=>$pg['cells']??[]])->all();
    } elseif (isset($items) && is_iterable($items)) {
      $chunks = array_chunk(is_array($items)?$items:iterator_to_array($items), $perPage);
      $renderPages = array_map(fn($cells)=>['part'=>null,'cells'=>$cells], $chunks);
    }
  @endphp

  @forelse ($renderPages as $page)
    <div class="sheet">
      <div class="grid10">
        @foreach ($page['cells'] as $it)
          <div class="plate">
            <div class="company">{{ strtoupper($company) }}</div>
            <div class="content">
              <div class="qrbox">{!! $it['svg'] ?? '' !!}</div>
              <div class="info">
                <div class="row"><div class="k">Part No:</div>        <div class="v">{{ $it['info']['Part No'] ?? '' }}</div></div>
                <div class="row"><div class="k">Part Name:</div>      <div class="v">{{ $it['info']['Part Name'] ?? '' }}</div></div>
                <div class="row"><div class="k">Supplier Name:</div>  <div class="v">{{ $it['info']['Supplier Name'] ?? '' }}</div></div>
                <div class="row"><div class="k">Supplier : Code:</div><div class="v">{{ $it['info']['Supplier : Code'] ?? '' }}</div></div>
                <div class="row"><div class="k">Qty/Box:</div>        <div class="v">{{ $it['info']['Qty/Box'] ?? '' }}</div></div>
                <div class="row"><div class="k">Date:</div>           <div class="v">{{ $it['info']['Date'] ?? '' }}</div></div>
              </div>
            </div>
          </div>
        @endforeach

        @for ($i = count($page['cells']); $i < $perPage; $i++)
          <div class="plate"></div>
        @endfor
      </div>
    </div>
  @empty
    <div style="padding:16px; color:#64748b;">ไม่มีข้อมูลสำหรับพิมพ์</div>
  @endforelse
</body>
</html>
