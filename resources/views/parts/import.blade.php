@extends('layouts.app')
@section('title','Import Parts')
@section('content')
  <div class="card" style="max-width:720px;">
    <h2 style="margin:0 0 10px;">นำเข้าข้อมูลชิ้นส่วน (CSV / TXT)</h2>
    @if ($errors->any())
      <div style="background:#fee2e2; border:1px solid #fecaca; padding:10px; border-radius:10px; margin-bottom:10px;">
        {{ $errors->first() }}
      </div>
    @endif

    <form action="{{ route('parts.import.store') }}" method="post" enctype="multipart/form-data">
      @csrf
      <div style="margin-bottom:10px;">
        <label>ไฟล์ (.csv, .txt)</label>
        <input type="file" name="file" accept=".csv,.txt" required>
      </div>
      <div style="margin-bottom:10px;">
        <label>รูปแบบวันที่ (ถ้าทราบ):</label>
        <select name="date_format">
          <option value="">— ให้ระบบเดา —</option>
          <option value="Y-m-d">YYYY-MM-DD</option>
          <option value="d/m/Y">DD/MM/YYYY</option>
          <option value="m/d/Y">MM/DD/YYYY</option>
          <option value="d-m-Y">DD-MM-YYYY</option>
          <option value="m-d-Y">MM-DD-YYYY</option>
        </select>
      </div>
      <button type="submit" style="padding:10px 14px; border-radius:10px; background:#4f46e5; color:#fff; border:0;">
        อัปโหลดและนำเข้า
      </button>
      <p style="margin-top:10px; color:#475569;">
        ตัวอย่างหัวคอลัมน์ที่รองรับ:  
        <code>No, Part No, Part Name, Supplier Name, Supplier code, MOQ, Date</code>  
        (หรือใช้ตัวคั่นเป็น <code>,</code> / <code>|</code> / <code>Tab</code> ได้)
      </p>
    </form>
  </div>
@endsection
