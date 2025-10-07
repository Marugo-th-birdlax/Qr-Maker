@extends('layouts.app')
@section('title','Reports')

@section('content')
  @php
    $total = $parts->total();
  @endphp

<style>
  .reports-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 16px;
  }

  /* ฟิลเตอร์ */
  .filter-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  }

  .filter-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
    align-items: end;
  }

  .filter-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .filter-field label {
    font-size: 12px;
    font-weight: 600;
    color: #6b7280;
  }

  .filter-field input,
  .filter-field select {
    padding: 8px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
    background: #fff;
    transition: border-color 0.15s;
  }

  .filter-field input:focus,
  .filter-field select:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
  }

  .filter-actions {
    grid-column: 1 / -1;
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 8px;
  }

  /* ปุ่ม */
  .btn {
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.15s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }

  .btn-primary {
    background: #4f46e5;
    color: #fff;
  }

  .btn-primary:hover {
    background: #4338ca;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);
  }

  .btn-success {
    background: #10b981;
    color: #fff;
  }

  .btn-success:hover {
    background: #059669;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);
  }

  /* สถิติ */
  .stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
    color: #fff;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  }

  .stats-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
  }

  .stats-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
  }

  .stats-label {
    font-size: 13px;
    opacity: 0.9;
  }

  .stats-value {
    font-size: 28px;
    font-weight: 700;
  }

  .stats-page {
    font-size: 13px;
    opacity: 0.85;
  }

  /* ตาราง */
  .table-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  }

  .table-wrapper {
    overflow-x: auto;
    margin-top: 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
  }

  .data-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1200px;
  }

  .data-table thead {
    background: linear-gradient(to bottom, #f9fafb, #f3f4f6);
    position: sticky;
    top: 0;
    z-index: 5;
  }

  .data-table th {
    text-align: left;
    padding: 12px 10px;
    font-size: 12px;
    font-weight: 700;
    color: #374151;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e5e7eb;
    white-space: nowrap;
  }

  .data-table th.text-right {
    text-align: right;
  }

  .data-table td {
    padding: 10px;
    font-size: 13px;
    border-top: 1px solid #f3f4f6;
  }

  .data-table td.text-right {
    text-align: right;
  }

  .data-table tbody tr {
    transition: background 0.15s;
  }

  .data-table tbody tr:hover {
    background: #f9fafb;
  }

  .part-no {
    font-weight: 600;
    color: #1e40af;
  }

  .remark-cell {
    max-width: 280px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  /* Badge */
  .badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    background: #e0e7ff;
    color: #4338ca;
  }

  /* Empty State */
  .empty-state {
    padding: 60px 20px;
    text-align: center;
    color: #9ca3af;
  }

  .empty-icon {
    font-size: 64px;
    margin-bottom: 16px;
    opacity: 0.5;
  }

  /* Responsive */
  @media (max-width: 1024px) {
    .reports-container {
      padding: 12px;
    }

    .filter-form {
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    }

    .data-table {
      min-width: 1000px;
    }
  }

  @media (max-width: 768px) {
    .filter-form {
      grid-template-columns: 1fr;
    }

    .filter-actions {
      flex-direction: column;
    }

    .filter-actions .btn {
      width: 100%;
      justify-content: center;
    }

    .stats-content {
      flex-direction: column;
      text-align: center;
    }

    .stats-value {
      font-size: 32px;
    }

    /* ตารางแบบการ์ดในมือถือ */
    .table-wrapper {
      border: none;
    }

    .data-table {
      min-width: 0;
    }

    .data-table thead {
      display: none;
    }

    .data-table tbody {
      display: block;
    }

    .data-table tr {
      display: block;
      margin-bottom: 16px;
      border: 1px solid #e5e7eb;
      border-radius: 12px;
      padding: 12px;
      background: #fff;
    }

    .data-table tr:hover {
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .data-table td {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      border: none;
    }

    .data-table td::before {
      content: attr(data-label);
      font-weight: 600;
      color: #6b7280;
      font-size: 11px;
      text-transform: uppercase;
      margin-right: 12px;
      flex: 0 0 45%;
    }

    .data-table td.text-right {
      text-align: right;
    }

    .remark-cell {
      max-width: none;
      white-space: normal;
    }
  }

  @media (max-width: 480px) {
    .data-table td::before {
      flex: 0 0 40%;
      font-size: 10px;
    }
  }
</style>

<div class="reports-container">
  {{-- ฟิลเตอร์ --}}
  <div class="filter-card">
    <form id="frmFilters" method="get" action="{{ route('reports.index') }}" class="filter-form">
      <div class="filter-field">
        <label>🔍 ค้นหา</label>
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
        <label>SUPPLIER (กลุ่ม)</label>
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
        <label>ต่อหน้า</label>
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
        <div class="stats-label">📦 ข้อมูลทั้งหมด</div>
        <div class="stats-value">{{ number_format($total) }}</div>
        <div class="stats-page">หน้า {{ $parts->currentPage() }} / {{ $parts->lastPage() }}</div>
      </div>
      <div style="font-size: 48px; opacity: 0.3;">📋</div>
    </div>
  </div>

  {{-- ตารางข้อมูล --}}
  <div class="table-card">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
      <h3 style="margin:0; font-size:18px; color:#111827;">📊 รายงานข้อมูล Parts</h3>
      <span style="font-size:13px; color:#6b7280;">แสดง {{ $parts->count() }} รายการ</span>
    </div>

    <div class="table-wrapper">
      <table class="data-table">
        <thead>
          <tr>
            <th>PIC</th>
            <th>TYPE</th>
            <th>SUPPLIER (กลุ่ม)</th>
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