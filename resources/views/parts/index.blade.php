@extends('layouts.app')

@section('title','Parts')

@section('content')
@php
  $u = session('user');
  $role = $u['role'] ?? 'user';
  $isAdmin = $role === 'admin';
  $canEdit = in_array($role, ['admin','manager'], true);
  $canDelete = ($role === 'pc'); 
@endphp

  @if (session('ok'))
    <div style="background:#ecfdf5; border:1px solid #a7f3d0; padding:10px; border-radius:10px; margin-bottom:10px;">
      {{ session('ok') }}
    </div>
  @endif

  {{-- แถบค้นหา / กรอง --}}
  <div class="card" style="margin-bottom:14px;">
    <form method="get" style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
      <div>
        <label>search</label><br>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Part No / Name / Supplier..."
               style="padding:8px; border:1px solid #e5e7eb; border-radius:8px; width:260px;">
      </div>

      <div>
        <label>Supplier Code</label><br>
        <select name="supplier_code" style="padding:8px; border:1px solid #e5e7eb; border-radius:8px; min-width:160px;">
          <option value="">— All —</option>
          @foreach ($supplierCodes as $c)
            <option value="{{ $c }}" @selected(request('supplier_code')===$c)>{{ $c }}</option>
          @endforeach
        </select>
      </div>

      {{-- ฟิลเตอร์ใหม่: TYPE / SUPPLIER (กลุ่ม) / Location --}}
      <div>
        <label>TYPE</label><br>
        <select name="type" style="padding:8px; border:1px solid #e5e7eb; border-radius:8px; min-width:140px;">
          <option value="">— All —</option>
          @foreach (($types ?? []) as $t)
            <option value="{{ $t }}" @selected(request('type')===$t)>{{ $t }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label>SUPPLIER (กลุ่ม)</label><br>
        <select name="supplier" style="padding:8px; border:1px solid #e5e7eb; border-radius:8px; min-width:160px;">
          <option value="">— All —</option>
          @foreach (($suppliers ?? []) as $s)
            <option value="{{ $s }}" @selected(request('supplier')===$s)>{{ $s }}</option>
          @endforeach
        </select>
      </div>

      <div>
        <label>Location</label><br>
        <select name="location" style="padding:8px; border:1px solid #e5e7eb; border-radius:8px; min-width:140px;">
          <option value="">— All —</option>
          @foreach (($locations ?? []) as $l)
            <option value="{{ $l }}" @selected(request('location')===$l)>{{ $l }}</option>
          @endforeach
        </select>
      </div>

      <div style="flex:1"></div>

      <div>
        <button class="btn" style="padding:8px 12px; border-radius:8px; background:#4f46e5; color:#fff; border:0;">search</button>

      @if ($isAdmin)
        <a href="{{ route('parts.create') }}" style="margin-left:8px; padding:8px 12px; border-radius:8px; background:#2563eb; color:#fff; text-decoration:none;">
          เพิ่มข้อมูล
        </a>
        <a href="{{ route('parts.import.form') }}" style="margin-left:8px; padding:8px 12px; border-radius:8px; background:#10b981; color:#fff; text-decoration:none;">
          Import CSV
        </a>
      @endif

      </div>
    </form>
  </div>

  {{-- ตาราง + ฟอร์มสร้าง QR หลายรายการ --}}
  <form action="{{ route('parts.qr.bulk') }}" method="post" class="card">
    @csrf

    <div style="overflow:auto;">
      <table style="width:100%; border-collapse:collapse; min-width:1200px;">
        <thead>
          <tr style="background:#f3f4f6;">
            <th style="padding:8px; width:36px;">
              <input type="checkbox" id="chk-all">
            </th>
            {{-- <th style="text-align:left; padding:8px;">No</th> --}}
            <th style="text-align:left; padding:8px;">Part No</th>
            <th style="text-align:left; padding:8px;">Part Name</th>

            {{-- ใหม่ --}}
            <th style="text-align:left; padding:8px;">PIC</th>
            <th style="text-align:left; padding:8px;">TYPE</th>
            <th style="text-align:left; padding:8px;">SUPPLIER</th>
            <th style="text-align:left; padding:8px;">Location</th>
            <th style="text-align:right; padding:8px;">Q'ty /Box</th>

            {{-- เดิม --}}
            <th style="text-align:left; padding:8px;">Supplier Name</th>
            <th style="text-align:left; padding:8px;">Code</th>
            <th style="text-align:right; padding:8px;">MOQ</th>
            <th style="text-align:left; padding:8px;">Date</th>

            {{-- ใหม่เพิ่มเติม --}}
            <th style="text-align:left; padding:8px;">Item No.</th>
            <th style="text-align:left; padding:8px;">UNIT</th>
            <th style="text-align:left; padding:8px; min-width:220px;">Remark</th>

            <th style="padding:8px;">QR</th>
            <th style="padding:8px;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($parts as $p)
            <tr style="border-top:1px solid #e5e7eb;">
              <td style="padding:8px;">
                <input type="checkbox" name="ids[]" value="{{ $p->id }}" class="chk-row">
              </td>

              {{-- <td style="padding:8px;">{{ $p->no }}</td> --}}
              <td style="padding:8px; font-weight:600;">{{ $p->part_no }}</td>
              <td style="padding:8px;">{{ $p->part_name }}</td>

              {{-- ใหม่ --}}
              <td style="padding:8px;">{{ $p->pic }}</td>
              <td style="padding:8px;">{{ $p->type }}</td>
              <td style="padding:8px;">{{ $p->supplier }}</td>
              <td style="padding:8px;">{{ $p->location }}</td>
              <td style="padding:8px; text-align:right;">{{ $p->qty_per_box }}</td>

              {{-- เดิม --}}
              <td style="padding:8px;">{{ $p->supplier_name }}</td>
              <td style="padding:8px;">{{ $p->supplier_code }}</td>
              <td style="padding:8px; text-align:right;">{{ $p->moq }}</td>
              <td style="padding:8px;">{{ optional($p->date)->format('Y-m-d') }}</td>

              {{-- เพิ่มเติม --}}
              <td style="padding:8px;">{{ $p->item_no }}</td>
              <td style="padding:8px;">{{ $p->unit }}</td>
              <td style="padding:8px; max-width:280px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $p->remark }}">
                {{ $p->remark }}
              </td>

              <td style="padding:8px;">
                <a href="{{ route('parts.qr.show', $p) }}" class="btn" style="padding:6px 10px; border-radius:8px; background:#4f46e5; color:#fff; text-decoration:none;">
                  QR
                </a>
              </td>

          <td style="padding:8px;">
            @if ($canEdit)
              <a href="{{ route('parts.edit', $p) }}" class="btn" style="padding:6px 10px; border-radius:8px; background:#2563eb; color:#fff; text-decoration:none;">
                Edit
              </a>
            @else
              
            @endif

            @if ($canDelete)
              {{-- ✅ ไปหน้า confirm ลบ (จำกัดสิทธิ์ด้วย middleware require.role:pc) --}}
              <a href="{{ route('parts.delete.confirm', $p) }}"
                class="btn"
                style="padding:6px 10px; border-radius:8px; background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; text-decoration:none;">
                ลบ
              </a>

              {{--
              🧰 ออปชัน: “ลบเร็ว” ไม่ผ่านหน้า confirm (ใช้แทนลิงก์ด้านบนได้ถ้าต้องการ)
              <form action="{{ route('parts.destroy', $p) }}" method="post" style="display:inline"
                    onsubmit="return confirm('ยืนยันลบ {{ $p->part_no }} ?');">
                @csrf
                @method('DELETE')
                <button class="btn" style="padding:6px 10px; border-radius:8px; background:#ef4444; color:#fff; border:0;">
                  ลบ
                </button>
              </form>
              --}}
            @endif
          </td>
            </tr>
          @empty
            <tr><td colspan="18" style="padding:12px; color:#6b7280;">ยังไม่มีข้อมูล</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px;">
      <div>{{ $parts->links() }}</div>

      <div style="display:flex; gap:8px; align-items:center;">
        <button type="submit" class="btn" style="padding:8px 12px; border-radius:8px; background:#10b981; color:#fff; border:0;">
          Generate QR (multiple items)
        </button>
      </div>
    </div>
  </form>

  <script>
    // เลือกAll / ยกเลิกAll
    const all = document.getElementById('chk-all');
    const rows = document.querySelectorAll('.chk-row');
    all?.addEventListener('change', () => rows.forEach(ch => ch.checked = all.checked));
  </script>
@endsection
