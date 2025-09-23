@php
    $u = session('user');
    $userFull = $u ? trim(($u['first_name'] ?? '').' '.($u['last_name'] ?? '')) : null;
    $initials = '';
    if ($userFull) {
        $parts = preg_split('/\s+/', $userFull);
        $initials = strtoupper(mb_substr($parts[0] ?? '',0,1).mb_substr($parts[1] ?? '',0,1));
    }
@endphp

<header id="topbar" class="sticky top-0 z-40 bg-white border-b transition-shadow">
  <div class="h-14 flex items-center justify-between px-4">
    <div class="flex items-center gap-2">
      <button id="btnSidebar" class="p-2 rounded hover:bg-gray-100" title="เปิด/ปิด Sidebar">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      </button>
      <button id="btnSidebarMini" class="hidden lg:inline-flex p-2 rounded hover:bg-gray-100" title="ย่อเป็นไอคอน (Desktop)">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-width="2" d="M4 4h6v6H4zM4 14h6v6H4zM14 4h6v6h-6zM14 14h6v6h-6z"/>
        </svg>
      </button>
      <a href="{{ Route::has('home') ? route('home') : url('/') }}" class="font-semibold">Qr • Maker</a>
      @hasSection('title')
        <span class="text-gray-300 px-2">/</span>
        <span class="text-gray-700">@yield('title')</span>
      @endif
    </div>

    <div class="flex items-center gap-3">
      <form action="#" class="hidden md:block">
        <label class="relative block">
          <input class="h-9 w-64 rounded-lg border border-gray-300 pl-9 pr-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-200"
                 placeholder="ค้นหาเมนู/ผู้ใช้ (เร็วๆนี้)"/>
          <span class="absolute inset-y-0 left-2 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <circle cx="11" cy="11" r="7"></circle><path d="m20 20-3.5-3.5"></path>
            </svg>
          </span>
        </label>
      </form>

      <button class="p-2 rounded hover:bg-gray-100" title="การแจ้งเตือน (เร็วๆนี้)">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 00-5-5.917V4a2 2 0 10-4 0v1.083A6 6 0 004 11v3.159c0 .538-.214 1.055-.595 1.436L2 17h5m8 0v1a3 3 0 11-6 0v-1m6 0H8"/>
        </svg>
      </button>

      <div class="relative">
        <button id="btnUserMenu" class="h-9 px-2 rounded-lg border border-gray-200 flex items-center gap-2 hover:bg-gray-50">
          @if($u)
            <div class="h-7 w-7 rounded-full bg-indigo-600 text-white grid place-items-center text-xs font-semibold">{{ $initials ?: 'U' }}</div>
            <div class="hidden sm:block text-left">
              <div class="text-sm leading-4">{{ $userFull }}</div>
              <div class="text-[11px] text-gray-500 -mt-0.5">{{ $u['role'] ?? 'user' }}</div>
            </div>
          @else
            <div class="text-sm">Guest</div>
          @endif
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="m6 9 6 6 6-6"/></svg>
        </button>

        <div id="userMenu" class="hidden absolute right-0 mt-2 w-52 rounded-xl border border-gray-200 bg-white shadow-lg overflow-hidden">
          @if($u)
            <div class="px-3 py-2 border-b">
              <div class="text-sm font-medium">{{ $userFull }}</div>
              <div class="text-xs text-gray-500vvvvvvvvv>{{ $u['email'] ?? '' }}</div>
            </div>
            <a href="#" class="block px-3 py-2 text-sm hover:bg-gray-50">โปรไฟล์ (เร็วๆนี้)</a>
            <a href="#" class="block px-3 py-2 text-sm hover:bg-gray-50">ตั้งค่า (เร็วๆนี้)</a>
            @if(Route::has('logout'))
              <form method="POST" action="#" class="border-t">
                @csrf
                <button type="submit" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 text-rose-600">ออกจากระบบ</button>
              </form>
            @endif
          @else
            @if(Route::has('login'))
              <a href="#" class="block px-3 py-2 text-sm hover:bg-gray-50">เข้าสู่ระบบ</a>
            @endif
          @endif
        </div>
      </div>
    </div>
  </div>
</header>

