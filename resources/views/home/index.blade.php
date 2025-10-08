@extends('layouts.app')

@section('title', 'Home | Qr')

@push('styles')



@endpush

@section('content')
<div class="dashboard-container">
  {{-- Header --}}
  <div class="dashboard-header">
    <h1 class="dashboard-title">Dashboard</h1>
    <p class="dashboard-subtitle">ภาพรวมระบบจัดการ Parts และ QR Code</p>
  </div>

  {{-- Quick Actions --}}
  <div class="quick-actions">
    <a href="{{ route('home') }}" class="action-card">
      <span class="action-icon">🏠</span>
      <div class="action-label">Demo</div>
      <div class="action-title">Back to Home</div>
    </a>

    <a href="{{ route('parts.import.form') }}" class="action-card">
      <span class="action-icon">📥</span>
      <div class="action-label">QR / Parts</div>
      <div class="action-title">Import CSV</div>
    </a>

    <a href="{{ route('reports.index') }}" class="action-card">
      <span class="action-icon">📊</span>
      <div class="action-label">Reports</div>
      <div class="action-title">Export XLSX</div>
    </a>

    <a href="{{ route('parts.index') }}" class="action-card">
      <span class="action-icon">📦</span>
      <div class="action-label">Part</div>
      <div class="action-title">Part List</div>
    </a>
  </div>

  {{-- Stats Cards --}}
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Part Summary</div>
      <div class="stat-value">{{ number_format($stats['parts_total'] ?? 0) }}</div>
      <div class="stat-trend">📦 ชิ้นส่วนทั้งหมด</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">Supplier List</div>
      <div class="stat-value">{{ number_format($stats['suppliers'] ?? 0) }}</div>
      <div class="stat-trend">🏢 ซัพพลายเออร์</div>
    </div>

    <div class="stat-card">
      <div class="stat-label">New Import Today</div>
      <div class="stat-value">{{ number_format($stats['today_import'] ?? 0) }}</div>
      <div class="stat-trend">📈 นำเข้าวันนี้</div>
    </div>
  </div>

  {{-- Recent Activity --}}
  <div class="activity-card">
    <div class="activity-header">
      <h2 class="activity-title">🕒 Last List</h2>
      <p class="activity-subtitle">ชิ้นส่วนที่เพิ่มล่าสุด</p>
    </div>

    <div class="activity-table-wrapper">
      <table class="activity-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Part No / Name</th>
            <th>Supplier</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @php
            $u = session('user');
            $role = $u['role'] ?? 'user';
            $canEdit = in_array($role, ['admin','pp','qc'], true);
          @endphp

          @forelse ($recent as $p)
            <tr>
              <td data-label="#">
                <span class="part-id">#{{ $p->id }}</span>
              </td>

              <td data-label="Part No / Name">
                <div class="part-no">{{ $p->part_no }}</div>
                <div class="part-name">{{ $p->part_name }}</div>
              </td>

              <td data-label="Supplier">
                <div class="supplier-code">{{ $p->supplier_code ?? '—' }}</div>
                <div class="supplier-name">{{ $p->supplier_name ?? '—' }}</div>
              </td>

              <td data-label="Created At">
                <span style="color:#6b7280; font-size:13px;">
                  {{ optional($p->created_at)->timezone(config('app.timezone'))->format('d/m/Y H:i') }}
                </span>
              </td>

              <td data-label="Actions">
                <div class="action-buttons">
                  <a href="{{ route('parts.qr.show', $p) }}" class="btn-action btn-qr">
                    📄 QR
                  </a>
                  @if ($canEdit)
                    <a href="{{ route('parts.edit', $p) }}" class="btn-action btn-edit">
                      ✏️ Edit
                    </a>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" style="padding:0; border:none;">
                <div class="empty-state">
                  <div class="empty-icon">📭</div>
                  <div class="empty-text">ยังไม่มีข้อมูล</div>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@push('scripts')
{{-- JS เฉพาะหน้าถ้าต้องการ --}}
<script>
  // Animation สำหรับ stat cards เมื่อโหลดหน้า
  document.addEventListener('DOMContentLoaded', function() {
    const statValues = document.querySelectorAll('.stat-value');
    
    statValues.forEach(el => {
      const finalValue = parseInt(el.textContent.replace(/,/g, ''));
      if (isNaN(finalValue)) return;
      
      let currentValue = 0;
      const increment = finalValue / 30;
      const duration = 1000;
      const stepTime = duration / 30;
      
      const timer = setInterval(() => {
        currentValue += increment;
        if (currentValue >= finalValue) {
          currentValue = finalValue;
          clearInterval(timer);
        }
        el.textContent = Math.floor(currentValue).toLocaleString();
      }, stepTime);
    });
  });
</script>
@endpush