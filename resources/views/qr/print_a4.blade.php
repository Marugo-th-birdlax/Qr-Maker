{{-- resources/views/qr/print_a4.blade.php --}}
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>{{ $title ?? 'Print A4' }}</title>
  <style>
    /* ขนาดหน้า A4 */
    @page { size: A4; margin: 10mm; }
    @media print { .no-print { display: none !important; } }

    body { margin:0; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial; color:#111; }
    .toolbar { padding:8px 10px; background:#f8fafc; border-bottom:1px solid #e5e7eb; position:sticky; top:0; }
    .sheet {
      display: grid;
      grid-template-columns: 1fr;
      gap: 0;
      page-break-after: always;
    }
    /* 8 ชิ้น/หน้า: 2 คอลัมน์ x 4 แถว */
    .grid8 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      grid-auto-rows: 50mm; /* ความสูงต่อชิ้น (ปรับได้) */
      gap: 6mm 6mm;        /* ช่องว่างระหว่างชิ้น */
    }

    .cell {
      border: 1px dashed #d1d5db;
      padding: 4mm;
      border-radius: 3mm;
      display: flex;
      align-items: center;
      gap: 4mm;
    }
    .qrbox {
      width: 35mm; height: 35mm; /* ขนาด QR (พอดีกับข้อความขวา) */
      display:flex; align-items:center; justify-content:center;
    }
    .qrbox svg {
      width: 100%; height: 100%;
    }
    .info {
      flex:1;
      font-size: 11px;
      line-height: 1.25;
      word-break: break-word;
    }
    .info b { display:inline-block; min-width: 22mm; }
  </style>
</head>
<body>
  <div class="toolbar no-print">
    <button onclick="window.print()">พิมพ์</button>
    <span style="margin-left:8px; color:#64748b;">จะจัดเรียง 8 ชิ้นต่อหน้าให้โดยอัตโนมัติ</span>
  </div>

  @php
    $perPage = $per_page ?? 8;
    $chunks  = array_chunk($items, $perPage);
  @endphp

  @foreach ($chunks as $pageItems)
    <div class="sheet">
      <div class="grid8">
        @foreach ($pageItems as $it)
          <div class="cell">
            <div class="qrbox">{!! $it['svg'] !!}</div>
            <div class="info">
              <div><b>Part No:</b> {{ $it['info']['Part No'] ?? '' }}</div>
              <div><b>Name:</b>    {{ $it['info']['Name'] ?? '' }}</div>
              <div><b>Code:</b>    {{ $it['info']['Code'] ?? '' }}</div>
              <div><b>MOQ:</b>     {{ $it['info']['MOQ'] ?? '' }}</div>
              <div><b>Date:</b>    {{ $it['info']['Date'] ?? '' }}</div>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  @endforeach
</body>
</html>
