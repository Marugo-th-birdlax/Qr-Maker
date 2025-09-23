{{-- resources/views/qr/from_part.blade.php (ต่อยอดของเดิม) --}}
@extends('layouts.app')
@section('title','QR Preview')

@section('content')
  <div class="card" style="display:grid; grid-template-columns: 380px 1fr; gap:16px;">
    <div>
      <h3 style="margin:0 0 8px;">QR ของ {{ $part->part_no }}</h3>
      <div style="background:#fff; border:1px dashed #e5e7eb; border-radius:12px; padding:8px; display:flex; align-items:center; justify-content:center;">
        {!! $svg !!}
      </div>

      {{-- ปุ่มพิมพ์ A4 8 ชิ้น/หน้า --}}
      <form method="get" action="{{ route('qr.print.single') }}" style="margin-top:12px; display:flex; gap:8px; align-items:end; flex-wrap:wrap;">
        <input type="hidden" name="part_id" value="{{ $part->id }}">
        <div>
          <label>จำนวนแผ่นงาน (ชิ้น)</label>
          <input type="number" min="1" max="999" name="copies" value="8" class="i" style="width:120px;">
        </div>
        <button class="btn-primary" type="submit">พิมพ์ A4 (8/หน้า)</button>
        <div style="color:#64748b;">ระบบจะจัดเรียง 8 ชิ้นต่อหน้า อัตโนมัติ</div>
      </form>
    </div>

    <div>
      <h3 style="margin:0 0 8px;">Payload</h3>
      <div style="background:#f8fafc; border:1px solid #e5e7eb; border-radius:10px; padding:10px; font-family:ui-monospace,monospace; word-break:break-all;">
        {{ $payload }}
      </div>

      {{-- ดาวน์โหลดไฟล์ (เดิม) --}}
      <form action="{{ route('qr.download') }}" method="post" style="margin-top:12px; display:flex; gap:8px; align-items:end; flex-wrap:wrap;">
        @csrf
        <input type="hidden" name="payload" value="{{ $payload }}">
        <div>
          <label>ชื่อไฟล์</label>
          <input type="text" name="filename" value="{{ $filename ?? ($part->part_no ?? 'qr_code') }}" class="i">
        </div>
        <div>
          <label>ฟอร์แมต</label>
          <select name="format" class="i">
            <option value="svg">SVG (แนะนำ)</option>
            <option value="png">PNG</option>
          </select>
        </div>
        <button class="btn-primary" type="submit">ดาวน์โหลด</button>
      </form>

      {{-- แสดงข้อมูลตัวหนังสือด้านขวา (ตัวอย่างเดียวกับที่จะไปอยู่ในแผ่นพิมพ์) --}}
      <div style="margin-top:16px; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:10px;">
        <div><b>Part No:</b> {{ $part->part_no }}</div>
        <div><b>Name:</b> {{ $part->part_name }}</div>
        <div><b>Code:</b> {{ $part->supplier_code }}</div>
        <div><b>MOQ:</b> {{ $part->moq }}</div>
        <div><b>Date:</b> {{ optional($part->date)->format('Y/m/d') }}</div>
      </div>
    </div>
  </div>

  <style>.i{width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:10px}.btn-primary{padding:10px 14px; border-radius:10px; background:#4f46e5; color:#fff; border:0}</style>
@endsection
