@extends('layouts.app')
@section('title','Settings — Parts')

@section('content')
@php
  $u = session('user');
  $role = $u['role'] ?? 'user';
  $canManageTrash = in_array($role, ['admin'], true);
@endphp

@if (session('ok'))
  <div style="background:#ecfdf5; border:1px solid #a7f3d0; padding:10px; border-radius:10px; margin-bottom:10px;">
    {{ session('ok') }}
  </div>
@endif

<style>
  .wrap { max-width: 1200px; margin:0 auto; }
  .card { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:12px; }
  .hd   { font-weight:700; margin:0 0 8px; }
  .muted{ color:#6b7280; font-size:13px; }

  .table-wrap { border:1px solid #e5e7eb; border-radius:10px; overflow:auto; }
  table { width:100%; border-collapse:collapse; min-width:900px; }
  thead th { position:sticky; top:0; background:#f9fafb; border-bottom:2px solid #e5e7eb; text-align:left; padding:10px 8px; font-size:13px; }
  tbody td { border-top:1px solid #f3f4f6; padding:8px; font-size:14px; vertical-align:top; }
  .btn { padding:6px 10px; border-radius:8px; border:1px solid #e5e7eb; background:#fff; color:#111; text-decoration:none; display:inline-block; }
  .btn-primary { background:#2563eb; color:#fff; border:0; }
  .btn-green   { background:#10b981; color:#fff; border:0; }
  .btn-red     { background:#ef4444; color:#fff; border:0; }
  .badge      { display:inline-block; padding:2px 8px; border-radius:999px; font-size:12px; }
  .badge-gray { background:#f3f4f6; color:#374151; border:1px solid #e5e7eb; }
  .badge-red  { background:#fee2e2; color:#991b1b; border:1px solid #fecaca; }
  .row { display:flex; gap:10px; align-items:center; justify-content:space-between; flex-wrap:wrap; }
</style>

<div class="wrap">
  <div class="row" style="margin-bottom:10px;">
    <h2 class="hd">ตั้งค่า / การจัดการรายการ</h2>
    <form method="get" action="{{ route('settings.index') }}" style="display:flex; gap:8px;">
      <input type="text" name="q" value="{{ $kw ?? '' }}" placeholder="ค้นหา (Part No / Name / Supplier ...)"
             style="padding:8px; border:1px solid #e5e7eb; border-radius:8px; min-width:260px;">
      <button class="btn btn-primary">ค้นหา</button>
      <a href="{{ route('settings.index') }}" class="btn">ล้าง</a>
    </form>
  </div>

  {{-- INACTIVE --}}
  <div class="card" style="margin-bottom:14px;">
    <div class="row">
      <div>
        <div class="hd">ชิ้นส่วนที่ปิดใช้งาน</div>
        <div class="muted">is_active = 0 (ยังไม่ถูกลบ)</div>
      </div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th style="min-width:140px;">Part No</th>
            <th style="min-width:220px;">Part Name</th>
            <th style="min-width:120px;">Supplier</th>
            <th style="min-width:110px;">Location</th>
            <th style="min-width:100px; text-align:right;">Qty/Box</th>
            <th style="min-width:120px;">Date</th>
            <th style="min-width:160px;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($inactive as $p)
            <tr>
              <td><span class="badge badge-gray">Inactive</span> <b>{{ $p->part_no }}</b></td>
              <td>{{ $p->part_name }}</td>
              <td>{{ $p->supplier ?? '-' }}</td>
              <td>{{ $p->location ?? '-' }}</td>
              <td style="text-align:right;">{{ number_format($p->qty_per_box) }}</td>
              <td>{{ optional($p->date)->format('Y-m-d') }}</td>
              <td>
                <div style="display:flex; gap:6px; flex-wrap:wrap;">
                  {{-- เปิดใช้งานกลับ --}}
                  <form action="{{ route('parts.activate', $p) }}" method="post" onsubmit="return confirm('เปิดใช้งาน {{ $p->part_no }} ?')">
                    @csrf @method('PATCH')
                    <button class="btn btn-green" type="submit">เปิดใช้งาน</button>
                  </form>
                  {{-- แก้ไข --}}
                  <a href="{{ route('parts.edit', $p) }}" class="btn btn-primary">แก้ไข</a>
                </div>
              </td>
            </tr>
          @empty
            <tr><td colspan="7" style="color:#6b7280; padding:10px;">— ไม่มีรายการ —</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="margin-top:8px;">{{ $inactive->appends(['trash_page'=>$trashed->currentPage(),'q'=>$kw])->links() }}</div>
  </div>

  {{-- TRASH (SOFT DELETED) --}}
  <div class="card">
    <div class="row">
      <div>
        <div class="hd">ถังขยะ (Soft Deleted)</div>
        <div class="muted">สามารถกู้คืน หรือ ลบถาวรได้</div>
      </div>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th style="min-width:140px;">Part No</th>
            <th style="min-width:220px;">Part Name</th>
            <th style="min-width:120px;">Supplier</th>
            <th style="min-width:110px;">Location</th>
            <th style="min-width:140px;">ลบเมื่อ</th>
            <th style="min-width:200px;">จัดการ</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($trashed as $p)
            <tr>
              <td><span class="badge badge-red">Trashed</span> <b>{{ $p->part_no }}</b></td>
              <td>{{ $p->part_name }}</td>
              <td>{{ $p->supplier ?? '-' }}</td>
              <td>{{ $p->location ?? '-' }}</td>
              <td>{{ optional($p->deleted_at)->format('Y-m-d H:i') }}</td>
              <td>
                @if ($canManageTrash)
                  <div style="display:flex; gap:6px; flex-wrap:wrap;">
                    <form action="{{ route('settings.restore', $p->id) }}" method="post" onsubmit="return confirm('กู้คืน {{ $p->part_no }} ?')">
                      @csrf @method('PATCH')
                      <button class="btn btn-green" type="submit">กู้คืน</button>
                    </form>
                    <form action="{{ route('settings.force', $p->id) }}" method="post" onsubmit="return confirm('ลบถาวร {{ $p->part_no }} ? การกระทำนี้ย้อนกลับไม่ได้!')">
                      @csrf @method('DELETE')
                      <button class="btn btn-red" type="submit">ลบถาวร</button>
                    </form>
                  </div>
                @else
                  <span class="muted">— ไม่มีสิทธิ์ —</span>
                @endif
              </td>
            </tr>
          @empty
            <tr><td colspan="6" style="color:#6b7280; padding:10px;">— ถังขยะว่าง —</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="margin-top:8px;">{{ $trashed->appends(['inactive_page'=>$inactive->currentPage(),'q'=>$kw])->links() }}</div>
  </div>
</div>
@endsection
