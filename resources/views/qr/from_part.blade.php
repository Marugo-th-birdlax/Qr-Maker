{{-- resources/views/qr/from_part.blade.php --}}
@extends('layouts.app')
@section('title','QR Preview')

@section('content')
  <div class="card" style="display:grid; grid-template-columns: 380px 1fr; gap:16px;">
    <div>
      <h3 style="margin:0 0 8px;">QR ของ {{ $part->part_no }}</h3>
      <div style="background:#fff; border:1px dashed #e5e7eb; border-radius:12px; padding:8px; display:flex; align-items:center; justify-content:center;">
        {!! $svg !!}
      </div>

      <form method="get" action="{{ route('qr.print.single') }}" style="margin-top:12px; display:flex; gap:8px; align-items:end; flex-wrap:wrap;">
        <input type="hidden" name="part_id" value="{{ $part->id }}">
        <div>
          <label>จำนวนแผ่นงาน (ชิ้น)</label>
          <input type="number" min="1" max="999" name="copies" value="10" class="i" style="width:120px;">
        </div>
        <button class="btn-primary" type="submit">พิมพ์</button>
        <div style="color:#64748b;">ระบบจะจัดเรียง 10 ชิ้นต่อหน้า อัตโนมัติ</div>
      </form>
    </div>

    <div>
      <h3 style="margin:0 0 8px;">Payload</h3>
      <div style="background:#f8fafc; border:1px solid #e5e7eb; border-radius:10px; padding:10px; font-family:ui-monospace,monospace; word-break:break-all;">
        {{ $payload }}
      </div>

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

      {{-- ===== ข้อมูลด้านขวา: หัวชื่อบริษัท + ตารางรายละเอียด ===== --}}
      @php
        $company = config('app.company_name', config('app.name'));
      @endphp
      <div class="info-card">
        <div class="info-header">
          <div class="company">{{ $company }}</div>
          <div class="subtitle">Part Information</div>
        </div>

        <table class="info-table">
          <tbody>
            <tr>
              <th>Part No</th>
              <td>{{ $part->part_no }}</td>
            </tr>
            <tr>
              <th>Name</th>
              <td>{{ $part->part_name }}</td>
            </tr>
            <tr>
              <th>Supplier Code</th>
              <td>{{ $part->supplier_code }}</td>
            </tr>
            <tr>
              <th>Qty/Box</th> {{-- เปลี่ยนจาก MOQ เป็น Qty/Box --}}
              <td>{{ $part->qty_per_box }}</td>
            </tr>
            <tr>
              <th>Date</th>
              <td>{{ optional($part->date)->format('Y/m/d') }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      {{-- ===== /จบส่วนข้อมูล ===== --}}
    </div>
  </div>

  <style>
    .i{width:100%; padding:10px; border:1px solid #e5e7eb; border-radius:10px}
    .btn-primary{padding:10px 14px; border-radius:10px; background:#4f46e5; color:#fff; border:0}

    .info-card{
      margin-top:16px; background:#fff; border:1px solid #e5e7eb; border-radius:12px; overflow:hidden;
    }
    .info-header{
      display:flex; align-items:baseline; justify-content:space-between;
      padding:12px 14px; background:#f9fafb; border-bottom:1px solid #e5e7eb;
    }
    .info-header .company{
      font-weight:700; font-size:18px; color:#111827;
    }
    .info-header .subtitle{
      font-size:12px; color:#6b7280;
    }
    .info-table{
      width:100%; border-collapse:collapse;
    }
    .info-table th{
      width:180px; text-align:left; background:#f9fafb; color:#374151;
      padding:10px 12px; border-bottom:1px solid #e5e7eb;
    }
    .info-table td{
      padding:10px 12px; border-bottom:1px solid #e5e7eb; color:#111827;
      word-break: break-word;
    }
    .info-table tr:last-child th,
    .info-table tr:last-child td{
      border-bottom:0;
    }
  </style>
@endsection
