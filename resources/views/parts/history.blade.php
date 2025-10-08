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