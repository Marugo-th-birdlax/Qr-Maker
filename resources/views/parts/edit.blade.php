@extends('layouts.app')

@section('title','แก้ไข Part: '.$part->part_no)

@section('content')
@php
  $u = session('user');
  $role = $u['role'] ?? 'user';
  $isManager = $role === 'manager';
  $ro = $isManager ? 'readonly' : '';          // สำหรับ input text
  $dis = $isManager ? 'disabled' : '';         // สำหรับ select/disabled field
@endphp

  <style>
    /* สไตล์คอมแพ็คเฉพาะหน้านี้ */
    .wrap      { max-width: 860px; margin: 0 auto; }
    .card      { background:#fff; border:1px solid #e5e7eb; border-radius:12px; }
    .hd        { font-weight:600; color:#111827; font-size:14px; margin-bottom:6px; }
    .sec       { padding:12px; border:1px solid #f3f4f6; border-radius:10px; background:#fafafa; }
    .sec + .sec{ margin-top:10px; }
    .grid2     { display:grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap:10px; }
    .grid3     { display:grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap:10px; }
    .row       { display:flex; gap:10px; align-items:center; justify-content:flex-end; }
    .lbl       { display:block; font-size:12px; color:#374151; margin-bottom:4px; }
    .inp, .sel, .txt {
      width:100%; padding:7px 9px; border:1px solid #e5e7eb; border-radius:8px;
      font-size:13px; line-height:1.3; background:#fff;
    }
    .inp[disabled]{ background:#f9fafb; color:#6b7280; }
    .num       { text-align:right; }
    .actions   { display:flex; gap:8px; justify-content:flex-end; }
    .btn       { padding:8px 12px; border-radius:8px; border:1px solid #e5e7eb; background:#fff; color:#111; text-decoration:none; }
    .btn-primary{ background:#2563eb; color:#fff; border:0; }
    .btn-ghost { background:#fff; }
    .sec-title { font-size:12px; color:#6b7280; letter-spacing:.02em; text-transform:uppercase; margin-bottom:6px; }
    .muted     { color:#6b7280; font-size:12px; }

    .inp:not([disabled]):not([readonly]),
    .sel:not([disabled]),
    .txt:not([readonly]) {
      background:#ecfdf5;              /* เขียวอ่อน */
      border-color:#34d399;            /* เขียวกลาง */
    }

    /* โทนตอนโฟกัส */
    .inp:not([disabled]):not([readonly]):focus,
    .sel:not([disabled]):focus,
    .txt:not([readonly]):focus {
      outline: none;
      border-color:#10b981;            /* เขียวเข้มขึ้น */
      box-shadow:0 0 0 3px rgba(16,185,129,.2);
    }

    /* เทาให้ฟิลด์อ่านอย่างเดียว/ปิดการแก้ไข */
    .inp[readonly], .txt[readonly],
    .inp[disabled], .sel[disabled], .txt[disabled]{
      background:#f9fafb;
      color:#6b7280;
      border-color:#e5e7eb;
      box-shadow:none;
    }
    
    @media (max-width: 768px) {
      .grid2, .grid3 { grid-template-columns: 1fr; }
      .wrap { padding: 0 8px; }
    }
    /* แบ่งกลุ่มแบบพับได้ */
    details.group { background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:10px; }
    details.group + details.group { margin-top:10px; }
    details.group summary { cursor:pointer; list-style:none; outline:none; }
    details.group summary::-webkit-details-marker{ display:none; }
    .grp-title { display:flex; align-items:center; justify-content:space-between; font-weight:600; font-size:14px; color:#111827; }
    .grp-body  { margin-top:8px; }
  </style>

  <div class="wrap">
    @if ($errors->any())
      <div class="card" style="padding:10px; margin-bottom:10px; border-color:#fecaca; background:#fff1f2; color:#991b1b;">
        {{ $errors->first() }}
      </div>
    @endif

    <div class="card" style="padding:14px;">
      <div class="row" style="justify-content:space-between; margin-bottom:6px;">
        <div class="hd">แก้ไขข้อมูลชิ้นส่วน</div>
        <div class="muted">Part No: <b>{{ $part->part_no }}</b></div>
      </div>

      <form method="post" action="{{ route('parts.update', $part) }}">
        @csrf
        @method('PUT')

        {{-- กลุ่ม 1: ข้อมูลหลัก --}}
        <details class="group" open>
          <summary class="grp-title">
            <span>ข้อมูลหลัก</span>
            <span class="muted">แก้เฉพาะที่จำเป็น</span>
          </summary>
          <div class="grp-body">
            <div class="grid3">
              {{-- <div>
                <label class="lbl">No</label>
                <input class="inp" name="no" value="{{ old('no',$part->no) }}"{!! $ro !!}>
                
              </div> --}}
              <div>
                <label class="lbl">Part No (อ่านอย่างเดียว)</label>
                <input class="inp" value="{{ $part->part_no }}" disabled>
              </div>
              <div>
                <label class="lbl">Part Name</label>
                <input class="inp" name="part_name" value="{{ old('part_name',$part->part_name) }}" {!! $ro !!}>
              </div>
            </div>
          </div>
        </details>

        {{-- กลุ่ม 2: ผู้ผลิต/ซัพพลาย --}}
        <details class="group" open>
          <summary class="grp-title">
            <span>ซัพพลายเออร์</span>
            <span class="muted">Supplier / PIC / TYPE</span>
          </summary>
          <div class="grp-body">
            <div class="grid3">
              <div>
                <label class="lbl">Supplier Name</label>
                <input class="inp" name="supplier_name" value="{{ old('supplier_name',$part->supplier_name) }}" {!! $ro !!}>
              </div>
              <div>
                <label class="lbl">Supplier Code</label>
                <input class="inp" name="supplier_code" value="{{ old('supplier_code',$part->supplier_code) }}" {!! $ro !!}>
              </div>
              <div>
                <label class="lbl">SUPPLIER (กลุ่ม)</label>
                <input class="inp" name="supplier" value="{{ old('supplier',$part->supplier) }}" {!! $ro !!}>
              </div>
            </div>
            <div class="grid2" style="margin-top:8px;">
              <div>
                <label class="lbl">PIC</label>
                <input class="inp" name="pic" value="{{ old('pic',$part->pic) }}" {!! $ro !!}>
              </div>
              <div>
                <label class="lbl">TYPE</label>
                <input class="inp" name="type" value="{{ old('type',$part->type) }}" {!! $ro !!}>
              </div>
            </div>
          </div>
        </details>

        {{-- กลุ่ม 3: โลจิสติกส์/สต็อก --}}
        <details class="group" open>
          <summary class="grp-title">
            <span>โลจิสติกส์ / สต็อก</span>
            <span class="muted">Location / Qty / Unit / MOQ</span>
          </summary>
          <div class="grp-body">
            <div class="grid3">
              <div>
                <label class="lbl">Location</label>
                <input class="inp" name="location" value="{{ old('location',$part->location) }}" {!! $ro !!}>
              </div>
              <div>
                <label class="lbl">Q'ty /Box</label>
                <input class="inp num" type="number" min="0" name="qty_per_box" value="{{ old('qty_per_box',$part->qty_per_box) }}">
              </div>
              <div>
                <label class="lbl">UNIT</label>
                <input class="inp" name="unit" value="{{ old('unit',$part->unit) }}" {!! $ro !!}>
              </div>
            </div>
            <div class="grid3" style="margin-top:8px;">
              <div>
                <label class="lbl">MOQ</label>
                <input class="inp num" type="number" min="0" name="moq" value="{{ old('moq',$part->moq) }}">
              </div>
              <div>
                <label class="lbl">Item No.</label>
                <input class="inp" name="item_no" value="{{ old('item_no',$part->item_no) }}" {!! $ro !!}>
              </div>
              <div>
                <label class="lbl">Date</label>
                <input class="inp" type="date" name="date" value="{{ old('date', optional($part->date)->format('Y-m-d')) }}" {!! $ro !!}>
              </div>
            </div>
          </div>
        </details>

        {{-- กลุ่ม 4: หมายเหตุ --}}
        <details class="group" open>
          <summary class="grp-title">
            <span>หมายเหตุ</span>
            <span class="muted">เพิ่มเติม</span>
          </summary>
          <div class="grp-body">
            <textarea class="txt" name="remark" rows="3" {!! $ro !!}>{{ old('remark',$part->remark) }}</textarea>
          </div>
        </details>

        <div class="actions" style="margin-top:12px;">
          <a href="{{ route('parts.index') }}" class="btn btn-ghost">ยกเลิก</a>
          <button class="btn btn-primary">บันทึก</button>
        </div>
      </form>
    </div>
  </div>
@endsection
