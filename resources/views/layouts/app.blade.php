<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>@yield('title', 'FRS')</title>

  {{-- โหลดไฟล์ผ่าน Vite (dev/build) --}}
  @vite([
    'resources/js/app.js',    // ใน app.js จะ import layout.js (ดูข้อ 2)
  ])

  @stack('styles')
</head>
<body class="min-h-screen bg-gray-50">

  @include('includes.sidebar')
  <div id="sidebarBackdrop"></div> {{-- ต้องมีสำหรับ mobile overlay --}}

  <div class="main-wrap min-h-screen flex flex-col" style="transition: margin .2s ease;">
    <style>
      :root{ --sbw:260px; --sbw-mini:72px; }
      @media (min-width: 1024px){
        body .main-wrap{ margin-left: var(--sbw); }         /* desktop เริ่มต้นเปิด */
        body.sidebar-mini .main-wrap{ margin-left: var(--sbw-mini); }
      }
      body.sidebar-open .main-wrap{ margin-left: var(--sbw); }
    </style>

    @include('includes.header')

    <main class="flex-1 p-4 lg:p-6">
      @yield('content')
    </main>

    @include('includes.footer')
  </div>

  @stack('scripts')
</body>
</html>
