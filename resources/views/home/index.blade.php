@extends('layouts.app')

@section('title', 'หน้าแรก | Qr')

@push('styles')
{{-- CSS เฉพาะหน้า (ถ้ามี) --}}
@endpush

@section('content')
  {{-- Breadcrumb / Heading --}}
  <div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-800">แดชบอร์ด</h1>
    <p class="text-sm text-gray-500">สรุปภาพรวมและลัดไปทำงานที่ใช้บ่อย</p>
  </div>

  {{-- Quick Actions --}}
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <a href="{{ route('home') }}" class="block rounded-xl border bg-white p-4 hover:shadow">
      <div class="text-sm text-gray-500">ลิงก์ตัวอย่าง</div>
      <div class="mt-1 font-medium">กลับหน้าแรก</div>
    </a>

    {{-- แก้ให้ใช้ route name ตามที่กำหนดในเว็บ.php --}}
    <a href="{{ route('parts.import.form') }}" class="block rounded-xl border bg-white p-4 hover:shadow">
      <div class="text-sm text-gray-500">QR / Parts</div>
      <div class="mt-1 font-medium">นำเข้า CSV</div>
    </a>

    {{-- ถ้าคุณมีหน้า generate QR แยก route ชื่อ qr.create --}}
    <a href="#" class="block rounded-xl border bg-white p-4 hover:shadow">
      <div class="text-sm text-gray-500">QR</div>
      <div class="mt-1 font-medium">สร้าง QR ใหม่</div>
    </a>

    <a href="{{ route('parts.index') }}" class="block rounded-xl border bg-white p-4 hover:shadow">
      <div class="text-sm text-gray-500">ชิ้นส่วน</div>
      <div class="mt-1 font-medium">รายการชิ้นส่วน</div>
    </a>
  </div>

  {{-- Stats Cards --}}
  <div class="grid md:grid-cols-3 gap-4 mb-8">
    <div class="rounded-2xl border bg-white p-5">
      <div class="text-sm text-gray-500">จำนวนชิ้นส่วนทั้งหมด</div>
      <div class="mt-1 text-xl font-semibold">{{ $stats['parts_total'] ?? 0 }}</div>
    </div>
    <div class="rounded-2xl border bg-white p-5">
      <div class="text-sm text-gray-500">ซัพพลายเออร์ (code) ทั้งหมด</div>
      <div class="mt-1 text-xl font-semibold">{{ $stats['suppliers'] ?? 0 }}</div>
    </div>
    <div class="rounded-2xl border bg-white p-5">
      <div class="text-sm text-gray-500">นำเข้าวันนี้</div>
      <div class="mt-1 text-xl font-semibold">{{ $stats['today_import'] ?? 0 }}</div>
    </div>
  </div>

  {{-- Recent Activity --}}
  <div class="rounded-2xl border bg-white">
    <div class="px-5 py-4 border-b">
      <h2 class="font-medium">รายการเคลื่อนไหวล่าสุด</h2>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50">
          <tr class="text-left text-gray-600">
            <th class="px-5 py-3 font-medium">#</th>
            <th class="px-5 py-3 font-medium">รายละเอียด</th>
            <th class="px-5 py-3 font-medium">โดย</th>
            <th class="px-5 py-3 font-medium">เมื่อ</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          @forelse ($recent as $row)
            <tr>
              <td class="px-5 py-3 text-gray-500">#{{ $row['id'] }}</td>
              <td class="px-5 py-3">{{ $row['title'] }}</td>
              <td class="px-5 py-3">{{ $row['by'] }}</td>
              <td class="px-5 py-3 text-gray-500">{{ $row['when'] }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-5 py-6 text-center text-gray-500">ยังไม่มีข้อมูล</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
@endsection

@push('scripts')
{{-- JS เฉพาะหน้าถ้าต้องการ --}}
@endpush
