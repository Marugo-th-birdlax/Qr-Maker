@extends('layouts.app')
@section('title','Import Parts')

@section('content')
@php
  $selectedFmt = request('date_format', 'Y/m/d');
@endphp

  <style>
    .wrap{max-width:840px;margin:0 auto}
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:14px}
    .hrow{display:flex;justify-content:space-between;align-items:center;gap:8px}
    .title{font-weight:700;color:#111827;font-size:18px}
    .muted{color:#6b7280;font-size:12px}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .lbl{display:block;font-size:12px;color:#374151;margin-bottom:6px}
    .inp,.sel{width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:10px;background:#fff}
    .btn{padding:10px 14px;border-radius:10px;border:0;background:#4f46e5;color:#fff}
    .btn-ghost{padding:8px 10px;border-radius:10px;border:1px solid #e5e7eb;background:#fff;color:#111}
    .help{background:#f8fafc;border:1px dashed #e5e7eb;border-radius:12px;padding:10px}
    .err{background:#fff1f2;border:1px solid #fecaca;color:#991b1b;padding:10px;border-radius:10px;margin-bottom:10px}
    .ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;padding:10px;border-radius:10px;margin-bottom:10px}
    .drop{border:2px dashed #cbd5e1;border-radius:12px;padding:18px;text-align:center;cursor:pointer;transition:.15s}
    .drop:hover{border-color:#94a3b8;background:#f8fafc}
    .drop.drag{border-color:#4f46e5;background:#eef2ff}
    code{background:#f1f5f9;border:1px solid #e2e8f0;border-radius:6px;padding:1px 6px}
    @media(max-width:768px){.grid{grid-template-columns:1fr}}
  </style>

  <div class="wrap">
    @if (session('ok'))
      <div class="ok">{{ session('ok') }}</div>
    @endif
    @if ($errors->any())
      <div class="err">{{ $errors->first() }}</div>
    @endif

    <div class="card" style="padding:16px;">
      <div class="hrow" style="margin-bottom:10px;">
        <div class="title">Import Part (CSV / TXT)</div>
        <div class="muted">รองรับตัวคั่น , | Tab ;  และ Encoding ไทย (CP874/ISO-8859-11)</div>
      </div>

      <form id="frmImport" action="{{ route('parts.import.store') }}" method="post" enctype="multipart/form-data">
        @csrf

        {{-- อัปโหลดไฟล์แบบ Drag & Drop --}}
        <label for="file" id="dropZone" class="drop" title="คลิกเพื่อเลือกไฟล์ หรือวางไฟล์ที่นี่">
          <div style="font-weight:600;margin-bottom:4px;">ลากไฟล์มาวาง หรือคลิกเพื่อเลือก</div>
          <div class="muted">Type: .csv, .txt</div>
          <input id="file" type="file" name="file" accept=".csv,.txt" required style="display:none">
        </label>

        <div class="grid" style="margin-top:12px;">
          <div>
            <label class="lbl">รูปแบบวันที่</label>
              <select name="date_format" class="sel">
                <option value="" @selected($selectedFmt==='')>— ให้ระบบเดา —</option>
                <option value="Y/m/d" @selected($selectedFmt==='Y/m/d')>YYYY/MM/DD (Y/M/D)</option>  {{-- ← default --}}
                <option value="Y-m-d" @selected($selectedFmt==='Y-m-d')>YYYY-MM-DD</option>
                <option value="d/m/Y" @selected($selectedFmt==='d/m/Y')>DD/MM/YYYY</option>
                <option value="m/d/Y" @selected($selectedFmt==='m/d/Y')>MM/DD/YYYY</option>
                <option value="d-m-Y" @selected($selectedFmt==='d-m-Y')>DD-MM-YYYY</option>
                <option value="m-d-Y" @selected($selectedFmt==='m-d-Y')>MM-DD-YYYY</option>
              </select>
              <div class="muted" style="margin-top:6px;">
                ค่าเริ่มต้น: <b>YYYY/MM/DD</b> (Y/M/D)
              </div>
            <div class="muted" style="margin-top:6px;">ต้องการ Y/M/D ให้เลือก <b>YYYY/MM/DD</b></div>
          </div>

          <div style="display:flex;align-items:flex-end;gap:8px;">
            <button type="submit" class="btn">อัปโหลดและนำเข้า</button>
            <a href="{{ route('parts.index') }}" class="btn-ghost">ยกเลิก</a>
          </div>
        </div>

        <div class="help" style="margin-top:14px;">
          <div style="font-weight:600;margin-bottom:6px;">หัวคอลัมน์ที่รองรับ (อย่างน้อยต้องมี <code>Part No</code>)</div>
          <div class="muted" style="margin-bottom:6px;">กรณีหัวไม่ตรง ระบบจะพยายามจับอัตโนมัติ (รองรับ quote งอ/ขึ้นบรรทัดในหัวคอลัมน์)</div>
          <pre style="margin:0;white-space:pre-wrap;font-size:12px;">
No., PIC, TYPE, SUPPLIER, Supplier code, Supplier Name, Location, Part No, PART NAME,
Q'ty /Box, "Minimum order quantity (MOQ) : Pcs", Remark, Item No., UNIT, Date
          </pre>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Drag & Drop พื้นฐาน
    const dz = document.getElementById('dropZone');
    const fi = document.getElementById('file');
    dz?.addEventListener('click', () => fi?.click());
    ['dragenter','dragover'].forEach(ev =>
      dz?.addEventListener(ev, e => { e.preventDefault(); dz.classList.add('drag'); }));
    ['dragleave','drop'].forEach(ev =>
      dz?.addEventListener(ev, e => { e.preventDefault(); dz.classList.remove('drag'); }));
    dz?.addEventListener('drop', e => {
      if (e.dataTransfer?.files?.length) {
        fi.files = e.dataTransfer.files;
      }
    });
  </script>
@endsection
