@extends('layouts.app')

@section('title','เข้าสู่ระบบ')

@section('content')
<div class="min-h-[60vh] grid place-items-center">
  <form method="POST" action="{{ route('login.attempt') }}" class="w-full max-w-sm border rounded-xl p-5 bg-white shadow-sm">
    @csrf
    <h1 class="text-xl font-semibold mb-4">เข้าสู่ระบบ</h1>

    @if ($errors->any())
      <div class="mb-3 text-sm text-rose-600">
        {{ $errors->first() }}
      </div>
    @endif

    <label class="block mb-3">
      <span class="block text-sm mb-1">รหัสพนักงาน</span>
      <input name="employee_id" value="{{ old('employee_id') }}" required
             class="w-full h-10 rounded-lg border px-3 focus:outline-none focus:ring-2 focus:ring-indigo-200">
    </label>

    <label class="block mb-4">
      <span class="block text-sm mb-1">รหัสผ่าน</span>
      <input type="password" name="password" required
             class="w-full h-10 rounded-lg border px-3 focus:outline-none focus:ring-2 focus:ring-indigo-200">
    </label>

    <button class="w-full h-10 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">เข้าสู่ระบบ</button>
  </form>
</div>
@endsection
