@extends('layouts.app')

@section('title','Parts')

@section('content')
@php
  $u = session('user');
  $role = $u['role'] ?? 'user';
  $isAdmin = in_array($role, ['admin','pp'], true);
  $canEdit = in_array($role, ['admin','pp','qc'], true);
  $canDelete = in_array($role, ['admin'], true);
  $canActivate = in_array($role, ['admin','pc'], true);
@endphp



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

                {{-- ปุ่มเปิด/ปิดการใช้งาน (admin, pแ เท่านั้น) --}}
                @if ($canActivate)
                  @if ($p->is_active)
                    <button type="button"
                            class="btn-edit"
                            onclick="confirmToggle('{{ route('parts.deactivate', $p) }}', 'ปิดใช้งาน {{ $p->part_no }} ?')">
                      ปิดใช้งาน
                    </button>
                  @else
                    <button type="button"
                            class="btn-success"
                            onclick="confirmToggle('{{ route('parts.activate', $p) }}', 'เปิดใช้งาน {{ $p->part_no }} ?')">
                      เปิดใช้งาน
                    </button>
                  @endif
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




<script>
  // --- CSRF token สำหรับ fetch ---
  const CSRF_TOKEN = @json(csrf_token());

  async function confirmToggle(url, msg){
    if(!confirm(msg)) return;

    try{
      const res = await fetch(url, {
        method: 'POST',              // Laravel ต้องการ POST + _method=PATCH
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN,
          'Accept': 'application/json, text/html, */*',
        },
        body: JSON.stringify({ _method: 'PATCH' })
      });

      // สำเร็จให้ reload เพื่อให้สถานะ/ปุ่มอัปเดต
      if (res.ok) {
        location.reload();
      } else {
        // เผื่อ backend redirect (302) หรือ validation
        // ถ้าไม่ได้เป็น ok แต่ server ส่ง html กลับมา ให้ force reload
        location.reload();
      }
    } catch (e){
      alert('เกิดข้อผิดพลาด ไม่สามารถอัปเดตสถานะได้');
      console.error(e);
    }
  }
</script>
