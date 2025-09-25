@extends('layouts.app')
@section('title','Reports')

@section('content')
  @php
    $total = $parts->total();
  @endphp

  {{-- ฟิลเตอร์ + ปุ่ม Export --}}
  <div class="card" style="padding:12px; margin-bottom:12px;">
    <form id="frmFilters" method="get" action="{{ route('reports.index') }}" style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
      <div>
        <label>ค้นหา</label><br>
        <input name="q" value="{{ request('q') }}" placeholder="Part No / Name / Supplier..."
               style="padding:8px;border:1px solid #e5e7eb;border-radius:8px;min-width:240px;">
      </div>

      <div>
        <label>Supplier Code</label><br>
        <select name="supplier_code" style="padding:8px;border:1px solid #e5e7eb;border-radius:8px;min-width:160px;">
          <option value="">— All —</option>
          @foreach ($supplierCodes as $c)
            <option value="{{ $c }}" @selected(request('supplier_code')===$c)>{{ $c }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label>TYPE</label><br>
        <select name="type" style="padding:8px;border:1px solid #e5e7eb;border-radius:8px;min-width:140px;">
          <option value="">— All —</option>
          @foreach ($types as $t)
            <option value="{{ $t }}" @selected(request('type')===$t)>{{ $t }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label>SUPPLIER (กลุ่ม)</label><br>
        <select name="supplier" style="padding:8px;border:1px solid #e5e7eb;border-radius:8px;min-width:160px;">
          <option value="">— All —</option>
          @foreach ($suppliers as $s)
            <option value="{{ $s }}" @selected(request('supplier')===$s)>{{ $s }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label>Location</label><br>
        <select name="location" style="padding:8px;border:1px solid #e5e7eb;border-radius:8px;min-width:140px;">
          <option value="">— All —</option>
          @foreach ($locations as $l)
            <option value="{{ $l }}" @selected(request('location')===$l)>{{ $l }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label>ต่อหน้า</label><br>
        <select name="per_page" style="padding:8px;border:1px solid #e5e7eb;border-radius:8px;min-width:100px;">
          @foreach ([20,50,100,200] as $pp)
            <option value="{{ $pp }}" @selected(request('per_page',50)==$pp)>{{ $pp }}</option>
          @endforeach
        </select>
      </div>

      <div style="flex:1"></div>

      <div style="display:flex; gap:8px;">
        <button class="btn" style="padding:8px 12px;border-radius:8px;background:#4f46e5;color:#fff;border:0;">
          search
        </button>

        {{-- ปุ่ม Export XLSX (ดึงค่าจากฟอร์มปัจจุบัน) --}}
        <a id="btnExport" href="#" class="btn"
           style="padding:8px 12px;border-radius:8px;background:#10b981;color:#fff;border:0; text-decoration:none;">
          Export XLSX
        </a>
      </div>
    </form>
  </div>

  {{-- พรีวิวข้อมูลก่อน Export --}}
  <div class="card" style="padding:10px;">
    <div style="margin-bottom:8px; color:#6b7280;">
      พบAll <b>{{ number_format($total) }}</b> รายการ — แสดงหน้า {{ $parts->currentPage() }} / {{ $parts->lastPage() }}
    </div>

    <div style="overflow:auto;">
      <table style="width:100%; border-collapse:collapse; min-width:1200px;">
        <thead>
          <tr style="background:#f3f4f6;">
            <th style="text-align:left; padding:8px;">No</th>
            <th style="text-align:left; padding:8px;">PIC</th>
            <th style="text-align:left; padding:8px;">TYPE</th>
            <th style="text-align:left; padding:8px;">SUPPLIER (กลุ่ม)</th>
            <th style="text-align:left; padding:8px;">Supplier code</th>
            <th style="text-align:left; padding:8px;">Supplier Name</th>
            <th style="text-align:left; padding:8px;">Location</th>
            <th style="text-align:left; padding:8px;">Part No</th>
            <th style="text-align:left; padding:8px;">PART NAME</th>
            <th style="text-align:right; padding:8px;">Q'ty /Box</th>
            <th style="text-align:right; padding:8px;">MOQ (Pcs)</th>
            <th style="text-align:left; padding:8px;">Item No.</th>
            <th style="text-align:left; padding:8px;">UNIT</th>
            <th style="text-align:left; padding:8px; min-width:220px;">Remark</th>
            <th style="text-align:left; padding:8px;">Date</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($parts as $p)
            <tr style="border-top:1px solid #e5e7eb;">
              <td style="padding:8px;">{{ $p->no }}</td>
              <td style="padding:8px;">{{ $p->pic }}</td>
              <td style="padding:8px;">{{ $p->type }}</td>
              <td style="padding:8px;">{{ $p->supplier }}</td>
              <td style="padding:8px;">{{ $p->supplier_code }}</td>
              <td style="padding:8px;">{{ $p->supplier_name }}</td>
              <td style="padding:8px;">{{ $p->location }}</td>
              <td style="padding:8px; font-weight:600;">{{ $p->part_no }}</td>
              <td style="padding:8px;">{{ $p->part_name }}</td>
              <td style="padding:8px; text-align:right;">{{ $p->qty_per_box }}</td>
              <td style="padding:8px; text-align:right;">{{ $p->moq }}</td>
              <td style="padding:8px;">{{ $p->item_no }}</td>
              <td style="padding:8px;">{{ $p->unit }}</td>
              <td style="padding:8px; max-width:320px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $p->remark }}">
                {{ $p->remark }}
              </td>
              <td style="padding:8px;">{{ optional($p->date)->format('Y-m-d') }}</td>
            </tr>
          @empty
            <tr><td colspan="15" style="padding:12px; color:#6b7280;">ไม่พบข้อมูล</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="margin-top:10px;">
      {{ $parts->links() }}
    </div>
  </div>

  <script>
    // กดปุ่ม Export แล้วพาไปที่ route export พร้อมพารามิเตอร์จากฟอร์ม (ไม่ต้องกดค้นหาก่อน)
    const btnExport = document.getElementById('btnExport');
    const frm = document.getElementById('frmFilters');
    btnExport?.addEventListener('click', (e) => {
      e.preventDefault();
      const params = new URLSearchParams(new FormData(frm));
      const url = "{{ route('reports.export.xlsx') }}?" + params.toString();
      window.location.href = url;
    });
  </script>
@endsection
