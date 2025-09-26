@extends('layouts.app')
@section('title','ลบ Part: '.$part->part_no)

@section('content')
  @php
    $u = session('user');
    $isPc = ($u['role'] ?? 'user') === 'pc';
  @endphp

  <div class="card" style="max-width:820px; margin:0 auto; border:1px solid #e5e7eb; border-radius:12px; padding:14px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
      <h2 style="margin:0;">ลบชิ้นส่วน</h2>
      <a href="{{ route('parts.index') }}" class="btn" style="padding:8px 12px; border:1px solid #e5e7eb; border-radius:8px; text-decoration:none;">ย้อนกลับ</a>
    </div>

    @if ($errors->any())
      <div style="background:#fee2e2; border:1px solid #fecaca; padding:10px; border-radius:10px; margin-bottom:12px;">
        {{ $errors->first() }}
      </div>
    @endif

    <div style="background:#fff; border:1px solid #fde68a; border-radius:10px; padding:12px; margin-bottom:12px;">
      <div style="color:#92400e; font-weight:600; margin-bottom:6px;">ยืนยันการลบ</div>
      <div style="color:#92400e;">การลบเป็นการลบถาวร โปรดยืนยันให้แน่ใจ</div>
    </div>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:12px;">
      <div>
        <div style="font-size:12px; color:#6b7280;">Part No</div>
        <div style="font-weight:600;">{{ $part->part_no }}</div>
      </div>
      <div>
        <div style="font-size:12px; color:#6b7280;">Part Name</div>
        <div style="font-weight:600;">{{ $part->part_name }}</div>
      </div>
      <div>
        <div style="font-size:12px; color:#6b7280;">Supplier</div>
        <div>{{ $part->supplier_name }} ({{ $part->supplier_code }})</div>
      </div>
      <div>
        <div style="font-size:12px; color:#6b7280;">MOQ / Unit</div>
        <div>{{ $part->moq ?? '-' }} {{ $part->unit ?? '' }}</div>
      </div>
    </div>

    @if ($isPc)
      <form method="post" action="{{ route('parts.destroy', $part) }}" onsubmit="return confirm('ยืนยันลบ {{ $part->part_no }} ?');" style="display:flex; gap:8px; justify-content:flex-end;">
        @csrf
        @method('DELETE')
        <a href="{{ route('parts.index') }}" class="btn" style="padding:8px 12px; border:1px solid #e5e7eb; border-radius:8px; text-decoration:none;">ยกเลิก</a>
        <button type="submit" style="padding:8px 12px; border-radius:8px; background:#ef4444; color:#fff; border:0;">ลบ</button>
      </form>
    @else
      <div style="padding:10px; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:8px; color:#6b7280;">
        ต้องเป็น role <b>pc</b> เท่านั้นจึงจะลบได้
      </div>
    @endif
  </div>
@endsection
