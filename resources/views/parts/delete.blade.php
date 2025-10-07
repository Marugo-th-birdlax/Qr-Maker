@extends('layouts.app')
@section('title','ลบ Part: '.$part->part_no)

@section('content')
@php
  $u    = session('user');
  $role = data_get($u, 'role', 'user');
  $isDelete = in_array($role, ['admin','pc'], true); // ใครมีสิทธิ์ลบ
@endphp

<div class="card" style="max-width:820px; margin:0 auto; border:1px solid #e5e7eb; border-radius:12px; padding:14px;">
  <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
    <h2 style="margin:0;">ย้ายไปถังขยะ (Soft delete)</h2>
    <a href="{{ route('parts.index') }}" class="btn" style="padding:8px 12px; border:1px solid #e5e7eb; border-radius:8px; text-decoration:none;">ย้อนกลับ</a>
  </div>

  @if ($errors->any())
    <div style="background:#fee2e2; border:1px solid #fecaca; padding:10px; border-radius:10px; margin-bottom:12px;">
      {{ $errors->first() }}
    </div>
  @endif

  <div style="background:#ecfeff; border:1px solid #a5f3fc; border-radius:10px; padding:12px; margin-bottom:12px;">
    <div style="color:#155e75; font-weight:600; margin-bottom:6px;">ยืนยันการย้ายไปถังขยะ</div>
    <div style="color:#155e75; font-size:14px;">
      รายการจะถูกซ่อนจากหน้ารายการปกติ แต่ยังสามารถ <b>กู้คืน</b> ได้จากหน้า <b>Settings &gt; ถังขยะ</b>.
      การลบถาวร (ล้างจากถังขยะ) ทำได้จากหน้า Settings เช่นกัน
    </div>
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

  @if ($isDelete)
    <form method="post" action="{{ route('parts.destroy', $part) }}"
          onsubmit="return confirm('ยืนยันย้าย {{ $part->part_no }} ไปถังขยะ ?');"
          style="display:flex; gap:8px; justify-content:flex-end;">
      @csrf
      @method('DELETE')
      <a href="{{ route('parts.index') }}" class="btn" style="padding:8px 12px; border:1px solid #e5e7eb; border-radius:8px; text-decoration:none;">ยกเลิก</a>
      <button type="submit" style="padding:8px 12px; border-radius:8px; background:#f59e0b; color:#111; border:1px solid #fbbf24;">
        ย้ายไปถังขยะ
      </button>
    </form>
  @else
    <div style="padding:10px; background:#f3f4f6; border:1px solid #e5e7eb; border-radius:8px; color:#6b7280;">
      ต้องเป็น role <b>pc</b> หรือ <b>admin</b> จึงจะลบได้
    </div>
  @endif
</div>
@endsection
