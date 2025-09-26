@extends('layouts.app')
@section('title','Bulk QR')

@section('content')
  @if ($errors->any())
    <div style="background:#fee2e2; border:1px solid #fecaca; padding:10px; border-radius:10px; margin-bottom:10px;">
      {{ $errors->first() }}
    </div>
  @endif

  {{-- ฟอร์มเดียว ครอบทั้งตารางและปุ่ม --}}
  <form method="post" action="{{ route('qr.print.bulk') }}" class="card">
    @csrf

    <div style="overflow:auto;">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="background:#f3f4f6;">
            <th style="text-align:left; padding:8px; min-width:200px;">Part No</th>
            <th style="text-align:left; padding:8px;">Name</th>
            <th style="text-align:left; padding:8px;">Code</th>
            <th style="text-align:right; padding:8px;">Qty/Box</th> {{-- เปลี่ยนหัวตาราง --}}
            <th style="text-align:left; padding:8px;">Date</th>
            <th style="text-align:center; padding:8px; width:180px;">จำนวน</th>
            <th style="text-align:center; padding:8px; width:120px;">ตัวอย่าง</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($items as $it)
            @php $p = $it['part']; @endphp
            <tr style="border-top:1px solid #e5e7eb;">
              <td style="padding:8px; font-weight:600;">{{ $p->part_no }}</td>
              <td style="padding:8px;">{{ $p->part_name }}</td>
              <td style="padding:8px;">{{ $p->supplier_code }}</td>
              <td style="padding:8px; text-align:right;">{{ $p->qty_per_box }}</td> {{-- ใช้ qty_per_box --}}
              <td style="padding:8px;">{{ optional($p->date)->format('Y/m/d') }}</td>
              <td style="padding:8px; text-align:center;">
                <div style="display:inline-flex; gap:6px; align-items:center;">
                  <button type="button" class="qty-btn" data-target="qty-{{ $p->id }}" data-step="-1">−</button>
                  <input
                    id="qty-{{ $p->id }}"
                    type="number"
                    name="qty[{{ $p->id }}]"
                    value="1"
                    min="0"
                    max="999"
                    style="width:70px; text-align:center; padding:6px; border:1px solid #e5e7eb; border-radius:8px;"
                  >
                  <button type="button" class="qty-btn" data-target="qty-{{ $p->id }}" data-step="1">+</button>
                </div>
              </td>
              <td style="padding:8px; text-align:center;">
                @php
                  $thumbSvg = isset($it['svg'])
                    ? str_replace('<svg', '<svg style="width:64px; height:64px;"', $it['svg'])
                    : '';
                @endphp
                {!! $thumbSvg !!}
              </td>
            </tr>
          @endforeach
          @if (empty($items))
            <tr><td colspan="7" style="padding:12px; color:#6b7280;">ไม่มีรายการ</td></tr>
          @endif
        </tbody>
      </table>
    </div>

    <div style="margin-top:10px; display:flex; gap:16px; align-items:center; justify-content:flex-end;">
      {{-- ให้ค่าตรงกับข้อความ (10 ชิ้น/หน้า) --}}
      <label><input type="radio" name="per_page" value="10" checked> 10 ชิ้น/หน้า</label>
      <button class="btn-primary" type="submit">พิมพ์</button>
    </div>
  </form>

  <style>
    .btn-primary{padding:8px 12px; border-radius:8px; background:#4f46e5; color:#fff; border:0; cursor:pointer;}
    .btn{padding:8px 12px; border-radius:8px; background:#f3f4f6; color:#111; border:0; cursor:pointer;}
    .qty-btn{width:32px; height:32px; border:1px solid #e5e7eb; background:#fff; border-radius:8px; cursor:pointer;}
    .qty-btn:hover{background:#f9fafb;}
  </style>

  <script>
    // ปุ่ม +/- ต่อแถว
    document.querySelectorAll('.qty-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id   = btn.getAttribute('data-target');
        const step = parseInt(btn.getAttribute('data-step'), 10);
        const el   = document.getElementById(id);
        if (!el) return;
        const min = parseInt(el.min || '0', 10);
        const max = parseInt(el.max || '999', 10);
        let val = parseInt(el.value || '0', 10) + step;
        if (val < min) val = min;
        if (val > max) val = max;
        el.value = val;
      });
    });

    // ตั้งค่าทุกแถว (ถ้ามีปุ่ม set-all)
    document.getElementById('set-all-btn')?.addEventListener('click', () => {
      const v = parseInt(document.getElementById('set-all-input').value || '0', 10);
      document.querySelectorAll('input[name^="qty["]').forEach(inp => {
        inp.value = Math.max(0, Math.min(999, v));
      });
    });
  </script>
@endsection
