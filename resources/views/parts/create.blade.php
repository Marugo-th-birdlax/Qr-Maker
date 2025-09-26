@extends('layouts.app')
@section('title','Create Part')

@section('content')
  <style>
    /* กล่องอินพุตเขียวสำหรับช่องที่แก้ไขได้ */
    .inp, .sel, .txt {
      width:100%; padding:7px 9px; border:1px solid #e5e7eb; border-radius:8px;
      font-size:13px; line-height:1.3; background:#ecfdf5; border-color:#34d399;
    }
    .inp:focus, .sel:focus, .txt:focus {
      outline:none; border-color:#10b981; box-shadow:0 0 0 3px rgba(16,185,129,.2);
    }
  </style>

  <div class="card" style="max-width:980px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
      <h2 style="margin:0;">เพิ่มข้อมูลชิ้นส่วน</h2>
      <a href="{{ route('parts.index') }}" style="padding:8px 12px; border-radius:8px; background:#f3f4f6; color:#111; text-decoration:none;">กลับรายการ</a>
    </div>

    @if ($errors->any())
      <div style="background:#fee2e2; border:1px solid #fecaca; padding:10px; border-radius:10px; margin-bottom:12px;">
        {{ $errors->first() }}
      </div>
    @endif

    <form action="{{ route('parts.store') }}" method="post" style="display:grid; gap:14px;">
      @csrf

      {{-- ✅ ซ่อน no ส่งค่าเป็น 0 เสมอ --}}
      <input type="hidden" name="no" value="0">

      {{-- กลุ่ม: ข้อมูลหลัก --}}
      <div style="border:1px solid #e5e7eb; border-radius:12px; padding:12px;">
        <div style="font-weight:600; margin-bottom:10px;">ข้อมูลหลัก</div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
          <div>
            <label>Part No <span style="color:#ef4444;">*</span></label>
            <input class="inp" type="text" name="part_no" value="{{ old('part_no') }}" required>
          </div>
          {{-- เดิมมี No. → เอาออกแล้ว (ใช้ hidden ข้างบนแทน) --}}
          <div style="grid-column:1 / -1;">
            <label>Part Name <span style="color:#ef4444;">*</span></label>
            <input class="inp" type="text" name="part_name" value="{{ old('part_name') }}" required>
          </div>
        </div>
      </div>

      {{-- กลุ่ม: ผู้ผลิต/ซัพพลายเออร์ --}}
      <div style="border:1px solid #e5e7eb; border-radius:12px; padding:12px;">
        <div style="font-weight:600; margin-bottom:10px;">ซัพพลายเออร์</div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
          <div>
            <label>Supplier Name</label>
            <input class="inp" type="text" name="supplier_name" value="{{ old('supplier_name') }}">
          </div>
          <div>
            <label>Supplier Code</label>
            <input class="inp" type="text" name="supplier_code" value="{{ old('supplier_code') }}">
          </div>
          <div>
            <label>TYPE</label>
            <input class="inp" type="text" name="type" value="{{ old('type') }}">
          </div>
          <div>
            <label>SUPPLIER</label>
            <input class="inp" type="text" name="supplier" value="{{ old('supplier') }}">
          </div>
          <div>
            <label>Location</label>
            <input class="inp" type="text" name="location" value="{{ old('location') }}">
          </div>
          <div>
            <label>PIC</label>
            <input class="inp" type="text" name="pic" value="{{ old('pic') }}">
          </div>
        </div>
      </div>

      {{-- กลุ่ม: บรรจุภัณฑ์/จำนวนต่อกล่อง --}}
      <div style="border:1px solid #e5e7eb; border-radius:12px; padding:12px;">
        <div style="font-weight:600; margin-bottom:10px;">บรรจุภัณฑ์</div>
        <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:10px;">
          <div>
            <label>Qty/Box</label>
            <input class="inp" type="number" name="qty_per_box" value="{{ old('qty_per_box') }}" min="0">
          </div>
          <div>
            <label>MOQ</label>
            <input class="inp" type="number" name="moq" value="{{ old('moq') }}" min="0">
          </div>
          <div>
            <label>Unit</label>
            <input class="inp" type="text" name="unit" value="{{ old('unit','PCS') }}">
          </div>
        </div>
      </div>

      {{-- กลุ่ม: อ้างอิงอื่น ๆ --}}
      <div style="border:1px solid #e5e7eb; border-radius:12px; padding:12px;">
        <div style="font-weight:600; margin-bottom:10px;">ข้อมูลอื่น ๆ</div>
        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
          <div>
            <label>Item No</label>
            <input class="inp" type="text" name="item_no" value="{{ old('item_no') }}">
          </div>
          <div>
            <label>Date</label>
            <input class="inp" type="date" name="date" value="{{ old('date') }}">
          </div>
          <div style="grid-column:1 / -1;">
            <label>Remark</label>
            <input class="inp" type="text" name="remark" value="{{ old('remark') }}">
          </div>
        </div>
      </div>

      <div style="display:flex; gap:8px; justify-content:flex-end;">
        <button type="submit" style="padding:10px 14px; border-radius:10px; background:#4f46e5; color:#fff; border:0;">บันทึก</button>
      </div>
    </form>
  </div>
@endsection
