@extends('layouts.app')
@section('title','Reports')

@section('content')
  @php
    $total = $parts->total();
  @endphp



<div class="reports-container">
  {{-- ฟิลเตอร์ --}}
  <div class="filter-card">
    <form id="frmFilters" method="get" action="{{ route('reports.index') }}" class="filter-form">
      <div class="filter-field">
        <label>🔍 Search</label>
        <input name="q" value="{{ request('q') }}" placeholder="Part No / Name / Supplier...">
      </div>

      <div class="filter-field">
        <label>Supplier Code</label>
        <select name="supplier_code">
          <option value="">— All —</option>
          @foreach ($supplierCodes as $c)
            <option value="{{ $c }}" @selected(request('supplier_code')===$c)>{{ $c }}</option>
          @endforeach
        </select>
      </div>

      <div class="filter-field">
        <label>TYPE</label>
        <select name="type">
          <option value="">— All —</option>
          @foreach ($types as $t)
            <option value="{{ $t }}" @selected(request('type')===$t)>{{ $t }}</option>
          @endforeach
        </select>
      </div>

      <div class="filter-field">
        <label>SUPPLIER</label>
        <select name="supplier">
          <option value="">— All —</option>
          @foreach ($suppliers as $s)
            <option value="{{ $s }}" @selected(request('supplier')===$s)>{{ $s }}</option>
          @endforeach
        </select>
      </div>

      <div class="filter-field">
        <label>Location</label>
        <select name="location">
          <option value="">— All —</option>
          @foreach ($locations as $l)
            <option value="{{ $l }}" @selected(request('location')===$l)>{{ $l }}</option>
          @endforeach
        </select>
      </div>

      <div class="filter-field">
        <label>Part / Page</label>
        <select name="per_page">
          @foreach ([20,50,100,200] as $pp)
            <option value="{{ $pp }}" @selected(request('per_page',50)==$pp)>{{ $pp }}</option>
          @endforeach
        </select>
      </div>

      <div class="filter-actions">
        <button type="submit" class="btn btn-primary">
          🔍 Search
        </button>
        <a id="btnExport" href="#" class="btn btn-success">
          📊 Export XLSX
        </a>
      </div>
    </form>
  </div>

  {{-- สถิติ --}}
  <div class="stats-card">
    <div class="stats-content">
      <div class="stats-info">
        <div class="stats-label">📦 Parts all</div>
        <div class="stats-value">{{ number_format($total) }}</div>
        <div class="stats-page">Page {{ $parts->currentPage() }} / {{ $parts->lastPage() }}</div>
      </div>
      <div style="font-size: 48px; opacity: 0.3;">📋</div>
    </div>
  </div>

  {{-- ตารางข้อมูล --}}
  <div class="table-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
      <h3 style="margin:0; font-size:18px; color:#111827;">📊 Data report Parts</h3>
      <span style="font-size:13px; color:#6b7280;">Show {{ $parts->count() }} List</span>
    </div>

    <div class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>PIC</th>
            <th>TYPE</th>
            <th>SUPPLIER </th>
            <th>Supplier Code</th>
            <th>Supplier Name</th>
            <th>Location</th>
            <th>Part No</th>
            <th>Part Name</th>
            <th class="text-right">Q'ty /Box</th>
            <th class="text-right">MOQ (Pcs)</th>
            <th>Item No.</th>
            <th>UNIT</th>
            <th>Remark</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($parts as $p)
            <tr>
              <td data-label="PIC">{{ $p->pic }}</td>
              <td data-label="TYPE">
                @if($p->type)
                  <span class="badge">{{ $p->type }}</span>
                @else
                  —
                @endif
              </td>
              <td data-label="SUPPLIER (กลุ่ม)">{{ $p->supplier ?? '—' }}</td>
              <td data-label="Supplier Code">{{ $p->supplier_code ?? '—' }}</td>
              <td data-label="Supplier Name">{{ $p->supplier_name ?? '—' }}</td>
              <td data-label="Location">{{ $p->location ?? '—' }}</td>
              <td data-label="Part No" class="part-no">{{ $p->part_no }}</td>
              <td data-label="Part Name">{{ $p->part_name ?? '—' }}</td>
              <td data-label="Q'ty /Box" class="text-right">
                <b>{{ number_format($p->qty_per_box) }}</b>
              </td>
              <td data-label="MOQ (Pcs)" class="text-right">
                {{ number_format($p->moq) }}
              </td>
              <td data-label="Item No.">{{ $p->item_no ?? '—' }}</td>
              <td data-label="UNIT">{{ $p->unit ?? '—' }}</td>
              <td data-label="Remark" class="remark-cell" title="{{ $p->remark }}">
                {{ $p->remark ?? '—' }}
              </td>
              <td data-label="Date">{{ optional($p->date)->format('Y-m-d') ?? '—' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="14" style="padding:0; border:none;">
                <div class="empty-state">
                  <div class="empty-icon">🔍</div>
                  <div style="font-size:16px; font-weight:600; color:#6b7280; margin-bottom:4px;">
                    ไม่พบข้อมูล
                  </div>
                  <div style="font-size:14px;">
                    ลองปรับเปลี่ยนตัวกรองหรือค้นหาด้วยคำอื่น
                  </div>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($parts->hasPages())
      <div style="margin-top:16px; padding-top:16px; border-top:1px solid #e5e7eb;">
        {{ $parts->links() }}
      </div>
    @endif
  </div>
</div>

<script>
  // Export XLSX พร้อมพารามิเตอร์จากฟอร์ม
  const btnExport = document.getElementById('btnExport');
  const frm = document.getElementById('frmFilters');
  
  btnExport?.addEventListener('click', (e) => {
    e.preventDefault();
    
    // แสดง loading state
    const originalText = btnExport.innerHTML;
    btnExport.innerHTML = '⏳ กำลังสร้างไฟล์...';
    btnExport.style.opacity = '0.6';
    btnExport.style.pointerEvents = 'none';
    
    const params = new URLSearchParams(new FormData(frm));
    const url = "{{ route('reports.export.xlsx') }}?" + params.toString();
    
    // สร้าง iframe ซ่อนเพื่อดาวน์โหลด
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = url;
    document.body.appendChild(iframe);
    
    // Reset button หลัง 2 วินาที
    setTimeout(() => {
      btnExport.innerHTML = originalText;
      btnExport.style.opacity = '1';
      btnExport.style.pointerEvents = 'auto';
      document.body.removeChild(iframe);
    }, 2000);
  });
</script>
@endsection