@extends('layouts.app')

@section('title','Parts')

@section('content')
  @if (session('ok'))
    <div style="background:#ecfdf5; border:1px solid #a7f3d0; padding:10px; border-radius:10px; margin-bottom:10px;">
      {{ session('ok') }}
    </div>
  @endif

  {{-- แถบค้นหา --}}
  <div class="card" style="margin-bottom:14px;">
    <form method="get" style="display:flex; gap:8px; align-items:end;">
      <div>
        <label>ค้นหา</label><br>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Part No / Name / Supplier..." style="padding:8px; border:1px solid #e5e7eb; border-radius:8px; width:260px;">
      </div>
      <div>
        <label>Supplier Code</label><br>
        <select name="supplier_code" style="padding:8px; border:1px solid #e5e7eb; border-radius:8px;">
          <option value="">— ทั้งหมด —</option>
          @foreach ($supplierCodes as $c)
            <option value="{{ $c }}" @selected(request('supplier_code')===$c)>{{ $c }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <button class="btn" style="padding:8px 12px; border-radius:8px; background:#4f46e5; color:#fff; border:0;">ค้นหา</button>
        <a href="{{ route('parts.import.form') }}" style="margin-left:8px; padding:8px 12px; border-radius:8px; background:#10b981; color:#fff; text-decoration:none;">นำเข้า CSV</a>
      </div>
    </form>
  </div>

  {{-- ตาราง + ฟอร์มสร้าง QR หลายรายการ --}}
  <form action="{{ route('parts.qr.bulk') }}" method="post" class="card">
    @csrf

    <div style="overflow:auto;">
      <table style="width:100%; border-collapse:collapse;">
        <thead>
          <tr style="background:#f3f4f6;">
            <th style="padding:8px; width:36px;">
              <input type="checkbox" id="chk-all">
            </th>
            <th style="text-align:left; padding:8px;">No</th>
            <th style="text-align:left; padding:8px;">Part No</th>
            <th style="text-align:left; padding:8px;">Part Name</th>
            <th style="text-align:left; padding:8px;">Supplier</th>
            <th style="text-align:left; padding:8px;">Code</th>
            <th style="text-align:right; padding:8px;">MOQ</th>
            <th style="text-align:left; padding:8px;">Date</th>
            <th style="padding:8px;">QR</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($parts as $p)
            <tr style="border-top:1px solid #e5e7eb;">
              <td style="padding:8px;">
                <input type="checkbox" name="ids[]" value="{{ $p->id }}" class="chk-row">
              </td>
              <td style="padding:8px;">{{ $p->no }}</td>
              <td style="padding:8px; font-weight:600;">{{ $p->part_no }}</td>
              <td style="padding:8px;">{{ $p->part_name }}</td>
              <td style="padding:8px;">{{ $p->supplier_name }}</td>
              <td style="padding:8px;">{{ $p->supplier_code }}</td>
              <td style="padding:8px; text-align:right;">{{ $p->moq }}</td>
              <td style="padding:8px;">{{ optional($p->date)->format('Y-m-d') }}</td>
              <td style="padding:8px;">
                {{-- ดู/ดาวน์โหลด QR ของรายการเดียว --}}
                <a href="{{ route('parts.qr.show', $p) }}" class="btn" style="padding:6px 10px; border-radius:8px; background:#4f46e5; color:#fff; text-decoration:none;">
                  QR
                </a>
              </td>
            </tr>
          @empty
            <tr><td colspan="9" style="padding:12px; color:#6b7280;">ยังไม่มีข้อมูล</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-top:10px;">
      <div>{{ $parts->links() }}</div>

      <div style="display:flex; gap:8px; align-items:center;">
        <button type="submit" class="btn" style="padding:8px 12px; border-radius:8px; background:#10b981; color:#fff; border:0;">
          Generate QR (หลายรายการ)
        </button>
      </div>
    </div>
  </form>

  <script>
    // เลือกทั้งหมด / ยกเลิกทั้งหมด
    const all = document.getElementById('chk-all');
    const rows = document.querySelectorAll('.chk-row');
    all?.addEventListener('change', () => rows.forEach(ch => ch.checked = all.checked));
  </script>
@endsection
