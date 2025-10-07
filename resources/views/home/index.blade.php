@extends('layouts.app')

@section('title', 'Home | Qr')

@push('styles')
{{-- CSS เฉพาะหน้า (ถ้ามี) --}}
@endpush

@section('content')
  {{-- Breadcrumb / Heading --}}
  <div class="mb-6">
    <h1 class="text-2xl font-semibold text-gray-800">Dashboard</h1>
    <p class="text-sm text-gray-500">Overview</p>
  </div>

  {{-- Quick Actions --}}
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <a href="{{ route('home') }}" class="block rounded-xl border bg-white p-4 hover:shadow">
      <div class="text-sm text-gray-500">Demo</div>
      <div class="mt-1 font-medium">back to Home</div>
    </a>

    <a href="{{ route('parts.import.form') }}" class="block rounded-xl border bg-white p-4 hover:shadow">
      <div class="text-sm text-gray-500">QR / Parts</div>
      <div class="mt-1 font-medium">Import CSV</div>
    </a>

    {{-- เปลี่ยนจาก Create New Qr → Reports --}}
    <a href="{{ route('reports.index') }}" class="block rounded-xl border bg-white p-4 hover:shadow">
      <div class="text-sm text-gray-500">Reports</div>
      <div class="mt-1 font-medium">Export XLSX</div>
    </a>

    <a href="{{ route('parts.index') }}" class="block rounded-xl border bg-white p-4 hover:shadow">
      <div class="text-sm text-gray-500">Part</div>
      <div class="mt-1 font-medium">Part List</div>
    </a>
  </div>


  {{-- Stats Cards --}}
  <div class="grid md:grid-cols-3 gap-4 mb-8">
    <div class="rounded-2xl border bg-white p-5">
      <div class="text-sm text-gray-500">Part summary</div>
      <div class="mt-1 text-xl font-semibold">{{ $stats['parts_total'] ?? 0 }}</div>
    </div>
    <div class="rounded-2xl border bg-white p-5">
      <div class="text-sm text-gray-500">Supplier List</div>
      <div class="mt-1 text-xl font-semibold">{{ $stats['suppliers'] ?? 0 }}</div>
    </div>
    <div class="rounded-2xl border bg-white p-5">
      <div class="text-sm text-gray-500">New Import (Today)</div>
      <div class="mt-1 text-xl font-semibold">{{ $stats['today_import'] ?? 0 }}</div>
    </div>
  </div>

  {{-- Recent Activity --}}
{{-- Recent Activity --}}
<div class="rounded-2xl border bg-white">
  <div class="px-5 py-4 border-b">
    <h2 class="font-medium">Last List</h2>
    <p class="text-sm text-gray-500">ชิ้นส่วนที่เพิ่มล่าสุด</p>
  </div>
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-50">
        <tr class="text-left text-gray-600">
          <th class="px-5 py-3 font-medium">#</th>
          <th class="px-5 py-3 font-medium">Part No / Name</th>
          <th class="px-5 py-3 font-medium">Supplier</th>
          <th class="px-5 py-3 font-medium">Created At</th>
          <th class="px-5 py-3 font-medium">Actions</th>
        </tr>
      </thead>
      <tbody class="divide-y">
        @php
          $u = session('user');
          $role = $u['role'] ?? 'user';
          $canEdit = in_array($role, ['admin','pp','qc'], true);
        @endphp

        @forelse ($recent as $p)
          <tr>
            <td class="px-5 py-3 text-gray-500">#{{ $p->id }}</td>

            <td class="px-5 py-3">
              <div class="font-medium text-gray-800">{{ $p->part_no }}</div>
              <div class="text-gray-500">{{ $p->part_name }}</div>
            </td>

            <td class="px-5 py-3">
              <div class="text-gray-800">{{ $p->supplier_code }}</div>
              <div class="text-gray-500">{{ $p->supplier_name }}</div>
            </td>

            <td class="px-5 py-3 text-gray-500">
              {{ optional($p->created_at)->timezone(config('app.timezone'))->format('Y/m/d H:i') }}
            </td>

            <td class="px-5 py-3">
              <div class="flex gap-2">
                <a href="{{ route('parts.qr.show', $p) }}"
                   class="inline-flex items-center rounded-lg border px-3 py-1.5 hover:bg-gray-50">
                  QR
                </a>
                @if ($canEdit)
                  <a href="{{ route('parts.edit', $p) }}"
                     class="inline-flex items-center rounded-lg border px-3 py-1.5 hover:bg-gray-50">
                    Edit
                  </a>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-5 py-6 text-center text-gray-500">Not found Data</td>
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
