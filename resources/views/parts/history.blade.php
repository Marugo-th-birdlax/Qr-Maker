@extends('layouts.app')
@section('title','History: '.$part->part_no)

@section('content')
@php
  // แผนที่ชื่อฟิลด์ -> ป้ายอ่านง่าย
  $labels = [
    'part_no'=>'Part No','part_name'=>'Part Name','supplier_name'=>'Supplier Name','supplier_code'=>'Supplier Code',
    'supplier'=>'SUPPLIER (กลุ่ม)','pic'=>'PIC','type'=>'TYPE','location'=>'Location',"qty_per_box"=>"Q'ty /Box",
    'moq'=>'MOQ','item_no'=>'Item No.','unit'=>'Unit','remark'=>'หมายเหตุ','date'=>'Date',
    'is_active'=>'สถานะ','deactivated_at'=>'ปิดใช้งานเมื่อ','qr_payload'=>'QR Payload','no'=>'No.',
  ];

  // ฟอร์แมตค่าให้เป็นมิตร
  $fmt = function($field, $val) {
      if ($val === null || $val === '') return '—';
      if ($field === 'is_active') return $val ? 'เปิดใช้งาน' : 'ปิดใช้งาน';
      if (in_array($field, ['moq','qty_per_box','no'])) return number_format((int)$val);
      if ($field === 'date')          { try { return \Carbon\Carbon::parse($val)->format('Y-m-d'); } catch (\Throwable $e) { return (string)$val; } }
      if ($field === 'deactivated_at'){ try { return \Carbon\Carbon::parse($val)->format('Y-m-d H:i'); } catch (\Throwable $e) { return (string)$val; } }
      return (string)$val;
  };

  // สีป้าย action
  $actionStyle = fn($a) => match($a){
    'create'=>'background:#dcfce7;color:#166534;border:1px solid #86efac;',
    'update'=>'background:#eef2ff;color:#3730a3;border:1px solid #c7d2fe;',
    'delete'=>'background:#fee2e2;color:#991b1b;border:1px solid #fecaca;',
    'activate'=>'background:#ecfeff;color:#155e75;border:1px solid #a5f3fc;',
    'deactivate'=>'background:#fef3c7;color:#92400e;border:1px solid #fde68a;',
    default=>'background:#f3f4f6;color:#374151;border:1px solid #e5e7eb;'
  };

  // ชื่อผู้แก้ไข
  $displayName = function($user) {
      if (!$user) return null;
      if (!empty($user->name)) return $user->name;
      $full = trim(($user->first_name ?? '').' '.($user->last_name ?? ''));
      return $full !== '' ? $full : null;
  };

  // fallback list สำหรับ dropdown (ถ้า controller ไม่ได้ส่งมา)
  $actions    = $actions    ?? ['create','update','activate','deactivate','delete'];
  $fieldsList = $fieldsList ?? array_keys($labels);
@endphp

<style>
  .history-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 16px;
  }

  .history-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 16px;
    gap: 12px;
    flex-wrap: wrap;
  }

  .history-title h2 {
    margin: 0 0 4px 0;
    font-size: 24px;
    color: #111827;
  }

  .history-subtitle {
    color: #6b7280;
    font-size: 14px;
  }

  .header-actions {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }

  /* ฟิลเตอร์ */
  .filter-box {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 16px;
  }

  .filter-summary {
    cursor: pointer;
    list-style: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 600;
    user-select: none;
  }

  .filter-summary::-webkit-details-marker {
    display: none;
  }

  .filter-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin-top: 16px;
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
    text-transform: uppercase;
  }

  .filter-field input,
  .filter-field select {
    padding: 8px 12px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 14px;
  }

  .filter-actions {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-top: 12px;
    grid-column: 1 / -1;
  }

  /* ปุ่มควบคุม */
  .control-buttons {
    display: flex;
    gap: 8px;
    justify-content: flex-end;
    margin-bottom: 12px;
    flex-wrap: wrap;
  }

  /* ปุ่มทั่วไป */
  .btn {
    padding: 8px 16px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #fff;
    color: #374151;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.15s;
  }

  .btn:hover {
    background: #f9fafb;
    border-color: #d1d5db;
  }

  .btn-primary {
    background: #4f46e5;
    color: #fff;
    border-color: #4f46e5;
  }

  .btn-primary:hover {
    background: #4338ca;
    border-color: #4338ca;
  }

  /* รายการ Log */
  .history-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .log-item {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px;
    transition: box-shadow 0.15s;
  }

  .log-item:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  }

  .log-summary {
    cursor: pointer;
    list-style: none;
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    user-select: none;
  }

  .log-summary::-webkit-details-marker {
    display: none;
  }

  .action-badge {
    padding: 4px 12px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
  }

  .log-time {
    color: #6b7280;
    font-size: 13px;
    white-space: nowrap;
  }

  .log-user {
    font-size: 14px;
  }

  .log-user b {
    color: #111827;
  }

  .log-count {
    color: #6b7280;
    font-size: 12px;
    margin-left: auto;
  }

  /* ตารางเปลี่ยนแปลง */
  .changes-table-wrapper {
    overflow-x: auto;
    margin-top: 12px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
  }

  .changes-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 500px;
  }

  .changes-table thead {
    background: #f9fafb;
  }

  .changes-table th {
    text-align: left;
    padding: 10px 12px;
    font-size: 13px;
    font-weight: 600;
    color: #374151;
    border-bottom: 2px solid #e5e7eb;
  }

  .changes-table td {
    padding: 10px 12px;
    font-size: 14px;
    border-top: 1px solid #f3f4f6;
  }

  .changes-table tbody tr:hover {
    background: #fafbfc;
  }

  .field-name {
    font-weight: 600;
    color: #111827;
  }

  .old-value {
    color: #6b7280;
  }

  .old-value-strike {
    text-decoration: line-through;
  }

  .new-value {
    font-weight: 600;
    color: #111827;
  }

  /* สีไฮไลท์สำหรับ is_active */
  .row-activated {
    background: #f0fdf4 !important;
  }

  .row-deactivated {
    background: #fff7ed !important;
  }

  /* Empty state */
  .empty-state {
    padding: 40px 20px;
    text-align: center;
    color: #9ca3af;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .history-container {
      padding: 12px;
    }

    .history-header {
      flex-direction: column;
      align-items: stretch;
    }

    .header-actions {
      width: 100%;
      justify-content: stretch;
    }

    .header-actions .btn {
      flex: 1;
      text-align: center;
    }

    .filter-grid {
      grid-template-columns: 1fr;
    }

    .control-buttons {
      flex-direction: column;
    }

    .control-buttons .btn {
      width: 100%;
    }

    .log-summary {
      font-size: 13px;
    }

    .log-count {
      margin-left: 0;
      width: 100%;
      flex-basis: 100%;
    }

    /* ตารางแบบ card ในมือถือ */
    .changes-table-wrapper {
      border: none;
    }

    .changes-table {
      min-width: 0;
    }

    .changes-table thead {
      display: none;
    }

    .changes-table tbody {
      display: block;
    }

    .changes-table tr {
      display: block;
      margin-bottom: 12px;
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 12px;
    }

    .changes-table td {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      border: none;
    }

    .changes-table td::before {
      content: attr(data-label);
      font-weight: 600;
      color: #6b7280;
      font-size: 12px;
      text-transform: uppercase;
      margin-right: 12px;
    }
  }

  @media (max-width: 480px) {
    .history-title h2 {
      font-size: 20px;
    }

    .log-summary {
      gap: 8px;
    }
  }
</style>

<div class="history-container">
  {{-- Header --}}
  <div class="history-header">
    <div class="history-title">
      <h2>ประวัติการแก้ไข</h2>
      <div class="history-subtitle">
        Part No: <b>{{ $part->part_no }}</b> — {{ $part->part_name ?? '-' }}
      </div>
    </div>
    <div class="header-actions">
      <a href="{{ route('parts.edit', $part) }}" class="btn">กลับหน้าแก้ไข</a>
      <a href="{{ route('parts.index') }}" class="btn">รายการทั้งหมด</a>
    </div>
  </div>

  {{-- แถบตัวกรอง --}}
  <details class="filter-box">
    <summary class="filter-summary">
      <span>🔍 ตัวกรอง</span>
      <span style="color:#6b7280; font-size:12px; font-weight:400;">คลิกเพื่อเปิด/ปิด</span>
    </summary>

    <form method="get" action="{{ route('parts.history', $part) }}">
      <div class="filter-grid">
        <div class="filter-field">
          <label>Action</label>
          <select name="action">
            <option value="all">— All —</option>
            @foreach ($actions as $a)
              <option value="{{ $a }}" @selected(request('action')===$a)>{{ ucfirst($a) }}</option>
            @endforeach
          </select>
        </div>

        <div class="filter-field">
          <label>ผู้แก้ไข</label>
          <input type="text" name="user" value="{{ request('user') }}" placeholder="ค้นหาชื่อ">
        </div>

        <div class="filter-field">
          <label>ฟิลด์</label>
          <select name="field">
            <option value="all">— All —</option>
            @foreach ($fieldsList as $f)
              <option value="{{ $f }}" @selected(request('field')===$f)>{{ $labels[$f] ?? $f }}</option>
            @endforeach
          </select>
        </div>

        <div class="filter-field">
          <label>ต่อหน้า</label>
          <select name="per_page">
            @foreach ([20,50,100] as $pp)
              <option value="{{ $pp }}" @selected((int)request('per_page',20)===$pp)>{{ $pp }}</option>
            @endforeach
          </select>
        </div>

        <div class="filter-field">
          <label>จากวันที่</label>
          <input type="date" name="from" value="{{ request('from') }}">
        </div>

        <div class="filter-field">
          <label>ถึงวันที่</label>
          <input type="date" name="to" value="{{ request('to') }}">
        </div>
      </div>

      <div class="filter-actions">
        <a href="{{ route('parts.history', $part) }}" class="btn">ล้างตัวกรอง</a>
        <button class="btn btn-primary" type="submit">กรอง</button>
      </div>
    </form>
  </details>

  {{-- ปุ่มควบคุม --}}
  <div class="control-buttons">
    <button id="open-all" class="btn" type="button">📂 เปิดทั้งหมด</button>
    <button id="close-all" class="btn" type="button">📁 ปิดทั้งหมด</button>
  </div>

  {{-- รายการประวัติ --}}
  @if ($histories->isEmpty())
    <div class="empty-state">
      <div style="font-size:48px; margin-bottom:12px;">📋</div>
      <div style="font-size:16px; font-weight:600; color:#6b7280;">ยังไม่มีประวัติการแก้ไข</div>
    </div>
  @else
    <div class="history-list">
      @foreach ($histories as $h)
        @php
          $fields = (array)($h->changed_fields ?? []);
          $before = (array)($h->before ?? []);
          $after  = (array)($h->after  ?? []);
          $name   = $displayName($h->user) ?? ('ผู้ใช้ #' . $h->user_id);
        @endphp

        <details class="log-item">
          <summary class="log-summary">
            <span class="action-badge" style="{{ $actionStyle($h->action) }}">
              {{ strtoupper($h->action) }}
            </span>
            <span class="log-time">⏰ {{ $h->created_at->format('Y-m-d H:i:s') }}</span>
            <span class="log-user">ผู้แก้ไข: <b>{{ $name }}</b></span>
            <span class="log-count">🔄 เปลี่ยน {{ count($fields) }} ฟิลด์</span>
          </summary>

          @if (!empty($fields))
            <div class="changes-table-wrapper">
              <table class="changes-table">
                <thead>
                  <tr>
                    <th style="width:30%;">ฟิลด์</th>
                    <th style="width:35%;">ก่อน</th>
                    <th style="width:35%;">หลัง</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($fields as $f)
                    @php
                      $label = $labels[$f] ?? $f;
                      $b = array_key_exists($f, $before) ? $fmt($f, $before[$f]) : '—';
                      $a = array_key_exists($f, $after)  ? $fmt($f, $after[$f])  : '—';
                      $rowClass = '';
                      if ($f === 'is_active') {
                        $rowClass = (isset($after[$f]) && $after[$f]) ? 'row-activated' : 'row-deactivated';
                      }
                    @endphp
                    <tr class="{{ $rowClass }}">
                      <td class="field-name" data-label="ฟิลด์">{{ $label }}</td>
                      <td class="old-value" data-label="ก่อน">
                        @if ($b === '—')
                          —
                        @else
                          <span class="old-value-strike">{{ $b }}</span>
                        @endif
                      </td>
                      <td class="new-value" data-label="หลัง">{{ $a }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div style="margin-top:12px; padding:12px; background:#f9fafb; border-radius:8px; color:#6b7280; text-align:center;">
              ไม่มีฟิลด์ที่เปลี่ยนแปลง
            </div>
          @endif
        </details>
      @endforeach
    </div>

    <div style="margin-top:20px;">
      {{ $histories->links() }}
    </div>
  @endif
</div>

{{-- เปิด/ปิดทั้งหมด --}}
<script>
  const openAll  = document.getElementById('open-all');
  const closeAll = document.getElementById('close-all');
  const logItems = document.querySelectorAll('.log-item');
  
  openAll?.addEventListener('click', () => {
    logItems.forEach(d => d.open = true);
  });
  
  closeAll?.addEventListener('click', () => {
    logItems.forEach(d => d.open = false);
  });
</script>
@endsection