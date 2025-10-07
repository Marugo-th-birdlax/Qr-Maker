@extends('layouts.app')

@section('title','Parts')

@section('content')
@php
  $u = session('user');
  $role = $u['role'] ?? 'user';
  $isAdmin = $role === 'admin';
  $canEdit = in_array($role, ['admin','pp','qc'], true);
  $canDelete = in_array($role, ['admin','pc'], true);
@endphp

<style>
  /* Container */
  .parts-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 16px;
  }

  /* Alert Success */
  .alert-success {
    background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
    border: 1px solid #a7f3d0;
    padding: 12px 16px;
    border-radius: 10px;
    margin-bottom: 16px;
    color: #065f46;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 10px;
  }

  /* Filter Card */
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
    flex-wrap: wrap;
  }

  /* Buttons */
  .btn {
    padding: 10px 18px;
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

  .btn-secondary {
    background: #2563eb;
    color: #fff;
  }

  .btn-secondary:hover {
    background: #1d4ed8;
    transform: translateY(-1px);
    box-shadow: 0 4px 6px rgba(37, 99, 235, 0.3);
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

  .btn-edit {
    padding: 6px 12px;
    border-radius: 6px;
    background: #2563eb;
    color: #fff;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.15s;
  }

  .btn-edit:hover {
    background: #1d4ed8;
  }

  .btn-delete {
    padding: 6px 12px;
    border-radius: 6px;
    background: #fee2e2;
    color: #b91c1c;
    border: 1px solid #fecaca;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.15s;
  }

  .btn-delete:hover {
    background: #fecaca;
  }

  .btn-qr {
    padding: 6px 12px;
    border-radius: 6px;
    background: #4f46e5;
    color: #fff;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.15s;
  }

  .btn-qr:hover {
    background: #4338ca;
  }

  /* Table Card */
  .table-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  }

  .table-wrap {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    margin-top: 12px;
  }

  .parts-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 900px;
  }

  .parts-table thead {
    background: linear-gradient(to bottom, #f9fafb, #f3f4f6);
    position: sticky;
    top: 0;
    z-index: 10;
  }

  .parts-table th {
    padding: 12px 10px;
    text-align: left;
    font-weight: 700;
    font-size: 12px;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .parts-table td {
    padding: 12px 10px;
    border-bottom: 1px solid #f3f4f6;
    font-size: 13px;
  }

  /* Main Row (คลิกได้) */
  .main-row {
    cursor: pointer;
    transition: all 0.15s;
  }

  .main-row:hover {
    background: #f9fafb;
  }

  .main-row.expanded {
    background: #eff6ff;
    box-shadow: inset 0 0 0 1px #93c5fd;
  }

  /* Expand Icon */
  .expand-icon {
    display: inline-block;
    transition: transform 0.2s;
    font-size: 14px;
    color: #6b7280;
    width: 20px;
    text-align: center;
  }

  .main-row.expanded .expand-icon {
    transform: rotate(90deg);
    color: #4f46e5;
  }

  /* Detail Row */
  .detail-row {
    display: none;
    background: #f8fafc;
  }

  .detail-row.show {
    display: table-row;
    animation: slideDown 0.2s ease-out;
  }

  @keyframes slideDown {
    from {
      opacity: 0;
    }
    to {
      opacity: 1;
    }
  }

  .detail-cell {
    padding: 20px !important;
    border-bottom: 2px solid #e5e7eb !important;
  }

  .detail-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 16px 24px;
  }

  .detail-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .detail-label {
    font-size: 11px;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }

  .detail-value {
    font-size: 14px;
    color: #111827;
    font-weight: 500;
  }

  /* Badge */
  .badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 700;
    background: #e0e7ff;
    color: #4338ca;
    letter-spacing: 0.3px;
  }

  /* Part No Style */
  .part-no {
    font-weight: 700;
    color: #1e40af;
  }

  /* Action Buttons */
  .action-btns {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
  }

  /* Footer Actions */
  .footer-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
    flex-wrap: wrap;
    gap: 12px;
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
    .parts-container {
      padding: 12px;
    }

    .filter-form {
      grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    }

    .parts-table {
      min-width: 800px;
    }

    .detail-grid {
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 12px 20px;
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

    .parts-table th,
    .parts-table td {
      font-size: 12px;
      padding: 10px 6px;
    }

    .detail-grid {
      grid-template-columns: 1fr;
      gap: 12px;
    }

    .action-btns {
      flex-direction: column;
      width: 100%;
    }

    .action-btns a {
      width: 100%;
      text-align: center;
      justify-content: center;
    }

    .footer-actions {
      flex-direction: column;
      align-items: stretch;
    }

    .footer-actions button {
      width: 100%;
    }
  }

  @media (max-width: 480px) {
    .parts-table {
      min-width: 700px;
    }
  }
</style>

<div class="parts-container">
  {{-- Success Alert --}}
  @if (session('ok'))
    <div class="alert-success">
      <span>✅</span>
      <span>{{ session('ok') }}</span>
    </div>
  @endif

  {{-- Filter Card --}}
  <div class="filter-card">
    <form method="get" class="filter-form">
      <div class="filter-field">
        <label>🔍 Search</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Part No / Name / Supplier...">
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
          @foreach (($types ?? []) as $t)
            <option value="{{ $t }}" @selected(request('type')===$t)>{{ $t }}</option>
          @endforeach
        </select>
      </div>

      <div class="filter-field">
        <label>SUPPLIER (กลุ่ม)</label>
        <select name="supplier">
          <option value="">— All —</option>
          @foreach (($suppliers ?? []) as $s)
            <option value="{{ $s }}" @selected(request('supplier')===$s)>{{ $s }}</option>
          @endforeach
        </select>
      </div>

      <div class="filter-field">
        <label>Location</label>
        <select name="location">
          <option value="">— All —</option>
          @foreach (($locations ?? []) as $l)
            <option value="{{ $l }}" @selected(request('location')===$l)>{{ $l }}</option>
          @endforeach
        </select>
      </div>

      <div class="filter-actions">
        <button type="submit" class="btn btn-primary">
          🔍 Search
        </button>

        @if ($isAdmin)
          <a href="{{ route('parts.create') }}" class="btn btn-secondary">
            ➕ เพิ่มข้อมูล
          </a>
          <a href="{{ route('parts.import.form') }}" class="btn btn-success">
            📥 Import CSV
          </a>
        @endif
      </div>
    </form>
  </div>

  {{-- Table Card --}}
  <form action="{{ route('parts.qr.bulk') }}" method="post" class="table-card">
    @csrf

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
      <h3 style="margin:0; font-size:18px; color:#111827;">📦 รายการ Parts</h3>
      <span style="font-size:13px; color:#6b7280;">คลิกแถวเพื่อดูรายละเอียด</span>
    </div>

    <div class="table-wrap">
      <table class="parts-table">
        <thead>
          <tr>
            <th style="width:40px;">
              <input type="checkbox" id="chk-all" style="cursor:pointer;">
            </th>
            <th style="width:30px;"></th>
            <th style="min-width:120px;">Part No</th>
            <th style="min-width:180px;">Part Name</th>
            <th style="min-width:100px;">TYPE</th>
            <th style="min-width:100px;">Location</th>
            <th style="text-align:right; min-width:90px;">Q'ty/Box</th>
            <th style="width:70px; text-align:center;">QR</th>
            <th style="min-width:140px;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($parts as $index => $p)
            {{-- Main Row --}}
            <tr class="main-row" data-row="{{ $index }}">
              <td onclick="event.stopPropagation()">
                <input type="checkbox" name="ids[]" value="{{ $p->id }}" class="chk-row" style="cursor:pointer;">
              </td>
              <td>
                <span class="expand-icon">▶</span>
              </td>
              <td class="part-no">{{ $p->part_no }}</td>
              <td>{{ $p->part_name }}</td>
              <td>
                @if($p->type)
                  <span class="badge">{{ $p->type }}</span>
                @else
                  <span style="color:#9ca3af;">—</span>
                @endif
              </td>
              <td>{{ $p->location ?? '—' }}</td>
              <td style="text-align:right; font-weight:600;">
                {{ number_format($p->qty_per_box) }}
              </td>
              <td style="text-align:center;" onclick="event.stopPropagation()">
                <a href="{{ route('parts.qr.show', $p) }}" class="btn-qr">
                  QR
                </a>
              </td>
              <td onclick="event.stopPropagation()">
                <div class="action-btns">
                  @if ($canEdit)
                    <a href="{{ route('parts.edit', $p) }}" class="btn-edit">
                      ✏️ Edit
                    </a>
                  @endif

                  @if ($canDelete)
                    <a href="{{ route('parts.delete.confirm', $p) }}" class="btn-delete">
                      🗑️ ลบ
                    </a>
                  @endif
                </div>
              </td>
            </tr>
            
            {{-- Detail Row --}}
            <tr class="detail-row" data-detail="{{ $index }}">
              <td colspan="9" class="detail-cell">
                <div style="margin-bottom:12px; font-weight:600; color:#4f46e5; font-size:14px;">
                  📋 รายละเอียดเพิ่มเติม
                </div>
                <div class="detail-grid">
                  <div class="detail-item">
                    <span class="detail-label">PIC</span>
                    <span class="detail-value">{{ $p->pic ?? '—' }}</span>
                  </div>
                  
                  <div class="detail-item">
                    <span class="detail-label">Supplier (กลุ่ม)</span>
                    <span class="detail-value">{{ $p->supplier ?? '—' }}</span>
                  </div>
                  
                  <div class="detail-item">
                    <span class="detail-label">Supplier Name</span>
                    <span class="detail-value">{{ $p->supplier_name ?? '—' }}</span>
                  </div>
                  
                  <div class="detail-item">
                    <span class="detail-label">Supplier Code</span>
                    <span class="detail-value">{{ $p->supplier_code ?? '—' }}</span>
                  </div>
                  
                  <div class="detail-item">
                    <span class="detail-label">MOQ (Minimum Order)</span>
                    <span class="detail-value">{{ number_format($p->moq) }} pcs</span>
                  </div>
                  
                  <div class="detail-item">
                    <span class="detail-label">Item No.</span>
                    <span class="detail-value">{{ $p->item_no ?? '—' }}</span>
                  </div>
                  
                  <div class="detail-item">
                    <span class="detail-label">UNIT</span>
                    <span class="detail-value">{{ $p->unit ?? '—' }}</span>
                  </div>
                  
                  <div class="detail-item">
                    <span class="detail-label">Date</span>
                    <span class="detail-value">{{ optional($p->date)->format('d/m/Y') ?? '—' }}</span>
                  </div>
                  
                  <div class="detail-item" style="grid-column: 1 / -1;">
                    <span class="detail-label">📝 Remark</span>
                    <span class="detail-value" style="white-space: pre-wrap;">{{ $p->remark ?? '—' }}</span>
                  </div>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" style="padding:0; border:none;">
                <div class="empty-state">
                  <div class="empty-icon">📦</div>
                  <div style="font-size:16px; font-weight:600; color:#6b7280; margin-bottom:4px;">
                    ยังไม่มีข้อมูล
                  </div>
                  <div style="font-size:14px;">
                    เริ่มต้นด้วยการเพิ่มข้อมูล Parts ใหม่
                  </div>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="footer-actions">
      <div>{{ $parts->links() }}</div>

      <button type="submit" class="btn btn-success">
        📄 Generate QR (Multiple Items)
      </button>
    </div>
  </form>
</div>

<script>
  // Select All Checkbox
  const checkAll = document.getElementById('chk-all');
  const checkRows = document.querySelectorAll('.chk-row');
  
  checkAll?.addEventListener('change', () => {
    checkRows.forEach(ch => ch.checked = checkAll.checked);
  });
  
  // Expandable Rows
  document.querySelectorAll('.main-row').forEach(row => {
    row.addEventListener('click', function(e) {
      const rowIndex = this.dataset.row;
      const detailRow = document.querySelector(`.detail-row[data-detail="${rowIndex}"]`);
      
      // Toggle expanded state
      this.classList.toggle('expanded');
      detailRow.classList.toggle('show');
    });
  });
</script>
@endsection