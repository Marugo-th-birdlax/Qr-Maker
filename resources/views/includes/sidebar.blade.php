@php
    // helper เดิม…
    if (!function_exists('frs_is_active')) {
        function frs_is_active($patterns) {
            foreach ((array) $patterns as $p) {
                if (request()->routeIs($p)) return 'is-active';
            }
            return '';
        }
    }

    $u    = session('user');
    $role = data_get($u, 'role', 'user');
    $canSeeSettings = in_array($role, ['admin','pc'], true);

    $pendingCount = $pendingCount ?? 0;

    // เมนูพื้นฐาน (ทุกคนเห็น)
    $menus = [
        ['label'=>'Parts',   'route'=>'parts.index',   'patterns'=>['parts.*'],   'icon'=>'#', 'badge'=>null],
        ['label'=>'Reports', 'route'=>'reports.index', 'patterns'=>['reports.*'], 'icon'=>'#', 'badge'=>$pendingCount > 0 ? $pendingCount : null],
    ];

    // เพิ่ม Settings เฉพาะ admin/pc
    if ($canSeeSettings) {
        $menus[] = ['label'=>'Settings', 'route'=>'settings.index', 'patterns'=>['settings.*'], 'icon'=>'#', 'badge'=>null];
    }
@endphp


<aside id="sidebar" class="frs-sidebar">
  <div class="frs-sidebar__header">เมนูระบบ</div>

  <nav class="frs-menu">
    <div class="frs-menu__section">เมนูหลัก</div>

    @php $hasHome = Route::has('home'); @endphp

    {{-- Dashboard: ถ้ามี route ค่อยทำเป็น <a> ไม่มีก็เป็น <span> --}}
    @if ($hasHome)
      <a href="{{ route('home') }}" class="frs-menu__item {{ frs_is_active('home') }}" @if(request()->routeIs('home')) aria-current="page" @endif>
        <span class="frs-menu__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-width="2" d="M3 12h18M3 6h18M3 18h18"/>
          </svg>
        </span>
        <span class="frs-menu__label">Dashboard</span>
      </a>
    @else
      <span class="frs-menu__item is-disabled" aria-disabled="true">
        <span class="frs-menu__icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-width="2" d="M3 12h18M3 6h18M3 18h18"/>
          </svg>
        </span>
        <span class="frs-menu__label">Dashboard</span>
      </span>
    @endif

    {{-- เมนูจากอาร์เรย์ --}}
    @foreach ($menus as $m)
      @php $exists = Route::has($m['route']); @endphp

      @if ($exists)
        @php $active = frs_is_active($m['patterns']); @endphp
        <a href="{{ route($m['route']) }}" class="frs-menu__item {{ $active }}" @if($active) aria-current="page" @endif>
          <span class="frs-menu__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </span>
          <span class="frs-menu__label">{{ $m['label'] }}</span>

          @if(!empty($m['badge']))
            <span class="frs-badge" aria-label="pending">{{ $m['badge'] }}</span>
          @endif
        </a>
      @else
        <span class="frs-menu__item is-disabled" aria-disabled="true">
          <span class="frs-menu__icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
          </span>
          <span class="frs-menu__label">{{ $m['label'] }}</span>
        </span>
      @endif
    @endforeach
  </nav>
</aside>

@push('styles')
<style>
  :root{ --frs-sbw:260px; --frs-brand:#4f46e5; --frs-text:#111827; --frs-muted:#6b7280; }
  .frs-sidebar{
    position:fixed; left:0; top:0; bottom:0; width:var(--frs-sbw);
    background:#fff; border-right:1px solid #e5e7eb;
    transform:translateX(-100%); transition:transform .2s ease; overflow-y:auto; z-index:40;
  }
  body.sidebar-open .frs-sidebar{ transform:translateX(0); }
  @media (min-width:1024px){ .frs-sidebar{ transform:translateX(0); } }

  .frs-sidebar__header{ padding:14px 16px; font-weight:700; border-bottom:1px solid #e5e7eb; }
  .frs-menu{ padding:8px; font-size:14px; }
  .frs-menu__section{
    margin:10px 6px 4px; color:var(--frs-muted); font-size:11px; text-transform:uppercase; font-weight:600;
  }
  .frs-menu__item{
    display:flex; align-items:center; gap:10px; padding:8px 10px; margin:2px 6px;
    border-radius:12px; color:var(--frs-text); text-decoration:none; position:relative;
  }
  .frs-menu__item:hover{ background:#f3f4f6; }
  .frs-menu__item.is-active{ background:#eef2ff; color:#3730a3; font-weight:600; }
  .frs-menu__item.is-active::before{
    content:''; position:absolute; left:-6px; top:6px; bottom:6px; width:3px; background:var(--frs-brand); border-radius:2px;
  }
  .frs-menu__item.is-disabled{ color:#cbd5e1; cursor:not-allowed; }
  .frs-menu__item.is-disabled svg{ opacity:.35; }
  .frs-menu__icon svg{ width:18px; height:18px; stroke:#6b7280; }
  .frs-menu__item.is-active .frs-menu__icon svg{ stroke:currentColor; }
  .frs-badge{
    margin-left:auto; display:inline-flex; min-width:20px; height:20px; padding:0 6px;
    border-radius:999px; background:#fee2e2; color:#991b1b; font-size:11px; font-weight:700; align-items:center; justify-content:center;
  }
  .frs-sidebar::-webkit-scrollbar{ width:10px; }
  .frs-sidebar::-webkit-scrollbar-thumb{ background:#e5e7eb; border-radius:8px; }
</style>
@endpush
