// resources/js/layout.js
// คุม sidebar, mini mode, user menu, header shadow และ backdrop
window.addEventListener('DOMContentLoaded', () => {
  const body      = document.body;
  const btn       = document.getElementById('btnSidebar');       // ปุ่มสามขีด
  const miniBtn   = document.getElementById('btnSidebarMini');   // ปุ่ม mini (ถ้ามี)
  const userBtn   = document.getElementById('btnUserMenu');
  const userMenu  = document.getElementById('userMenu');
  const topbar    = document.getElementById('topbar');
  const sidebar   = document.getElementById('sidebar');
  const backdrop  = document.getElementById('sidebarBackdrop');  // <div id="sidebarBackdrop"></div>
  const mqDesktop = window.matchMedia('(min-width:1024px)');

  // --- Sidebar open/close ---
  const setSidebar = (open) => {
    if (open) {
      body.classList.add('sidebar-open');
      localStorage.setItem('frs.sidebarOpen', '1');
      if (backdrop) backdrop.style.display = mqDesktop.matches ? 'none' : 'block';
      btn?.setAttribute('aria-expanded', 'true');
    } else {
      body.classList.remove('sidebar-open');
      localStorage.setItem('frs.sidebarOpen', '0');
      if (backdrop) backdrop.style.display = 'none';
      btn?.setAttribute('aria-expanded', 'false');
    }
  };
  const toggleSidebar = () => setSidebar(!body.classList.contains('sidebar-open'));

  // เริ่มต้น: ถ้าไม่เคยมีค่า -> เปิดบน desktop, ปิดบน mobile
  const savedOpen = localStorage.getItem('frs.sidebarOpen');
  if (savedOpen === '1') setSidebar(true);
  else if (savedOpen === '0') setSidebar(false);
  else setSidebar(mqDesktop.matches);

  // ปุ่มสามขีด
  btn?.addEventListener('click', (e) => { e.preventDefault(); toggleSidebar(); });
  // คลิก backdrop เพื่อปิด (เฉพาะ mobile)
  backdrop?.addEventListener('click', () => setSidebar(false));
  // ESC เพื่อปิด
  document.addEventListener('keydown', (e) => { if (e.key === 'Escape') setSidebar(false); });

  // --- Mini mode (เฉพาะเดสก์ท็อป) ---
  const setMini = (mini) => {
    if (mini) body.classList.add('sidebar-mini');
    else body.classList.remove('sidebar-mini');
    localStorage.setItem('frs.sidebarMini', mini ? '1' : '0');
  };
  miniBtn?.addEventListener('click', () => setMini(!body.classList.contains('sidebar-mini')));
  if (localStorage.getItem('frs.sidebarMini') === '1') body.classList.add('sidebar-mini');

  // ปรับสถานะเมื่อสลับ mobile/desktop (ถ้าไม่เคยตั้งเอง)
  mqDesktop.addEventListener('change', (e) => {
    const pref = localStorage.getItem('frs.sidebarOpen');
    if (pref === null) setSidebar(e.matches);
  });

  // --- User dropdown ---
  if (userBtn && userMenu) {
    userBtn.addEventListener('click', (e) => { e.stopPropagation(); userMenu.classList.toggle('hidden'); });
    document.addEventListener('click', () => userMenu.classList.add('hidden'));
  }

  // --- Header shadow เมื่อ scroll ---
  const onScroll = () => {
    if (!topbar) return;
    if (window.scrollY > 10) topbar.classList.add('shadow');
    else topbar.classList.remove('shadow');
  };
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // กันกระพริบตอนโหลด
  body.classList.add('sidebar-init');
});





