<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>{{ $title ?? 'Print A4' }}</title>
  <style>
    @page { size: A4; margin: 10mm; }
    @media print { .no-print { display:none !important; } }

    body { margin:0; font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Arial; color:#111; }
    .toolbar { padding:8px 10px; background:#f8fafc; border-bottom:1px solid #e5e7eb; position:sticky; top:0; }

    .sheet { page-break-after: always; }

    /* 10 ชิ้นต่อหน้า = 2 คอลัมน์ x 5 แถว */
    .grid10 {
      display: grid;
      grid-template-columns: 1fr 1fr;
      grid-auto-rows: 43mm;   /* ความสูงต่อชิ้น */
      gap: 5mm 6mm;           /* ช่องว่างระหว่างชิ้น */
    }

    .header {
      font-size: 12px; color:#475569; margin: 0 0 6mm;
      display:flex; justify-content:space-between; align-items:center;
    }
    .cell {
      border: 1px dashed #d1d5db;
      padding: 3.5mm;
      border-radius: 3mm;
      display: flex;
      align-items: center;
      gap: 4mm;
      height: 100%;
    }
    .qrbox { width: 30mm; height: 30mm; display:flex; align-items:center; justify-content:center; }
    .qrbox svg { width:100%; height:100%; }

    .info { flex:1; font-size: 10.5px; line-height: 1.25; word-break: break-word; }
    .info b { display:inline-block; min-width: 22mm; }
  </style>
</head>
<body>
  <div class="toolbar no-print">
    <button onclick="window.print()">พิมพ์</button>
    <span style="margin-left:8px; color:#64748b;">โหมดพิมพ์ A4 — 10 ชิ้น/หน้า</span>
  </div>

  @php
    $perPage = $per_page ?? 10;

    /**
     * รูปแบบข้อมูลที่ View นี้รองรับ:
     * - โหมด A: $pages = [
     *      ['part' => Part, 'cells' => [item,item,...]],   // แยกหน้า/พาร์ท (แต่ละหน้าเป็น part เดียว)
     *   ]
     * - โหมด B: $items = [ item, item, ... ]              // รายการก้อนเดียว (จะ chunk เป็นหน้าเอง)
     *
     * โครงสร้าง item:
     *   ['svg' => '<svg ...>', 'info'=>['Part No'=>..,'Name'=>..,'Code'=>..,'MOQ'=>..,'Date'=>..]]
     */

    $renderPages = [];

    if (isset($pages) && is_iterable($pages)) {
        // โหมด A: ใช้ $pages ตรง ๆ
        $renderPages = collect($pages)->map(function($pg){
            return [
                'part'  => $pg['part'] ?? null,     // อาจมีหรือไม่มีก็ได้
                'cells' => $pg['cells'] ?? [],
            ];
        })->all();
    } elseif (isset($items) && is_iterable($items)) {
        // โหมด B: สร้าง pages จาก items ด้วยการ chunk
        $chunks = array_chunk(is_array($items)? $items : iterator_to_array($items), $perPage);
        $renderPages = array_map(function($cells){
            return ['part'=>null, 'cells'=>$cells];
        }, $chunks);
    }
  @endphp

  @forelse ($renderPages as $page)
    <div class="sheet">
      {{-- แสดงหัวกระดาษถ้ามีข้อมูล Part (กรณีโหมดแยกพาร์ท) --}}
      @if (!empty($page['part']))
        <div class="header">
          <div>
            <b>Part No:</b> {{ $page['part']->part_no }}
            &nbsp; <b>Name:</b> {{ $page['part']->part_name }}
          </div>
          <div>
            <b>Code:</b> {{ $page['part']->supplier_code }}
            &nbsp; <b>Date:</b> {{ optional($page['part']->date)->format('Y/m/d') }}
          </div>
        </div>
      @endif

      <div class="grid10">
        @foreach ($page['cells'] as $it)
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

        {{-- เติมช่องว่างให้ครบ 10 ช่อง (รักษาเลย์เอาต์คงที่) --}}
        @for ($i = count($page['cells']); $i < $perPage; $i++)
          <div class="cell"></div>
        @endfor
      </div>
    </div>
  @empty
    <div style="padding:16px; color:#64748b;">ไม่มีข้อมูลสำหรับพิมพ์</div>
  @endforelse
</body>
</html>
