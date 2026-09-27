<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$userName = $_SESSION['admin_user_name'] ?? 'Super Admin';
$userEmail = $_SESSION['admin_user_email'] ?? 'admin@clickcodex.in';
$roleName = $_SESSION['admin_role_name'] ?? 'Super Admin';

// User Initials
$words = explode(' ', $userName);
$initials = strtoupper(substr($words[0] ?? 'A', 0, 1) . substr($words[1] ?? '', 0, 1));
if (empty($initials)) $initials = 'AD';

$pageTitle = $pageTitle ?? 'Admin Control Center';
$activeMenu = $activeMenu ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> — ClickCodex</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          cc: { yellow:'#FFB800', orange:'#FF5100', pink:'#FF006B', purple:'#8C30F5', blue:'#2D82FF', dark:'#0F172A', light:'#F8FAFC', ice:'#F1F5F9' }
        },
        fontFamily: { heading:['Poppins','sans-serif'], body:['Inter','sans-serif'] }
      }
    }
  }
</script>
<style>
  body{font-family:'Inter',sans-serif;background:#F1F5F9}
  h1,h2,h3,h4,.font-heading{font-family:'Poppins',sans-serif}
  ::selection{background:#FF5100;color:#fff}
  .scrollbar-thin::-webkit-scrollbar{width:5px}
  .scrollbar-thin::-webkit-scrollbar-track{background:transparent}
  .scrollbar-thin::-webkit-scrollbar-thumb{background:#CBD5E1;border-radius:10px}
  .sidebar{width:260px;transition:width .3s cubic-bezier(.4,0,.2,1)}
  .sidebar.collapsed{width:72px}
  .sidebar.collapsed .nav-label,.sidebar.collapsed .sidebar-section-title,.sidebar.collapsed .sidebar-brand-text,.sidebar.collapsed .sidebar-user-info{display:none}
  .sidebar.collapsed .nav-item{justify-content:center;padding-left:0;padding-right:0}
  .sidebar.collapsed .nav-item .material-icons{margin-right:0}
  .nav-item{display:flex;align-items:center;padding:9px 14px;border-radius:10px;color:#94A3B8;font-size:.875rem;font-weight:500;transition:all .15s ease;cursor:pointer;width:100%;text-decoration:none}
  .nav-item:hover{background:rgba(45,130,255,.08);color:#2D82FF}
  .nav-item.active{background:rgba(45,130,255,.12);color:#2D82FF;font-weight:600}
  .nav-item .material-icons{font-size:20px;margin-right:12px;flex-shrink:0}
  .main-area{margin-left:260px;transition:margin-left .3s cubic-bezier(.4,0,.2,1)}
  .main-area.expanded{margin-left:72px}
  .kpi-card{background:#fff;border-radius:16px;border:1.5px solid #E2E8F0;transition:transform .2s ease,box-shadow .2s ease;position:relative;overflow:hidden}
  .kpi-card:hover{transform:translateY(-3px);box-shadow:0 12px 24px -8px rgba(15,23,42,.08)}
  .data-table{width:100%;border-collapse:separate;border-spacing:0}
  .data-table thead th{background:#F8FAFC;padding:12px 16px;font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;color:#64748B;text-align:left;border-bottom:1.5px solid #E2E8F0;position:sticky;top:0;z-index:2}
  .data-table thead th:first-child{border-radius:12px 0 0 0}
  .data-table thead th:last-child{border-radius:0 12px 0 0}
  .data-table tbody td{padding:12px 16px;font-size:.875rem;color:#475569;border-bottom:1px solid #F1F5F9;vertical-align:middle}
  .data-table tbody tr{transition:background .1s ease}
  .data-table tbody tr:hover{background:#F8FAFC}
  .data-table tbody tr:last-child td{border-bottom:none}
  .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:.75rem;font-weight:600;white-space:nowrap}
  .badge-green{background:#DCFCE7;color:#166534}
  .badge-yellow{background:#FEF3C7;color:#92400E}
  .badge-red{background:#FFE4E6;color:#9F1239}
  .badge-blue{background:#DBEAFE;color:#1E40AF}
  .badge-purple{background:#F3E8FF;color:#6B21A8}
  .badge-slate{background:#F1F5F9;color:#475569}
  .badge-orange{background:#FFF7ED;color:#C2410C}
  .badge-cyan{background:#ECFEFF;color:#155E75}
  .badge-indigo{background:#E0E7FF;color:#3730A3}
  .badge-amber{background:#FEF3C7;color:#92400E}
  .section-card{background:#fff;border-radius:16px;border:1.5px solid #E2E8F0;overflow:hidden}
  .stars-filled{color:#FFB800}
  .stars-empty{color:#E2E8F0}
  .spark-bar{display:flex;align-items:flex-end;gap:2px;height:32px}
  .spark-bar span{width:4px;border-radius:2px}
  .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:40;backdrop-filter:blur(2px)}
  .sidebar-overlay.show{display:block}
  
  .stat-mini{background:#fff;border-radius:12px;border:1.5px solid #E2E8F0;padding:16px 20px;transition:all .2s ease}
  .stat-mini:hover{border-color:#2D82FF;box-shadow:0 4px 12px -4px rgba(45,130,255,.1)}

  .action-btn{width:28px;height:28px;border-radius:7px;display:inline-flex;align-items:center;justify-content:center;transition:all .15s ease;color:#94A8B8}
  .action-btn:hover{background:#F1F5F9;color:#2D82FF}
  .action-btn.danger:hover{background:#FFE4E6;color:#FF006B}

  /* Inline toggle switches */
  .inline-toggle{width:34px;height:19px;border-radius:10px;background:#E2E8F0;position:relative;cursor:pointer;transition:background .2s ease;flex-shrink:0;display:inline-block}
  .inline-toggle.on{background:#2D82FF}
  .inline-toggle .it-thumb{width:15px;height:15px;border-radius:50%;background:#fff;position:absolute;top:2px;left:2px;transition:transform .2s cubic-bezier(.4,0,.2,1);box-shadow:0 1px 3px rgba(0,0,0,.15)}
  .inline-toggle.on .it-thumb{transform:translateX(15px)}

  /* Custom row check */
  .row-check{width:18px;height:18px;border-radius:5px;border:1.5px solid #CBD5E1;appearance:none;-webkit-appearance:none;background:#fff;cursor:pointer;transition:all .15s ease;flex-shrink:0;position:relative}
  .row-check:checked{background:#2D82FF;border-color:#2D82FF}
  .row-check:checked::after{content:'';position:absolute;left:5px;top:1.5px;width:5px;height:9px;border:solid #fff;border-width:0 2px 2px 0;transform:rotate(45deg)}

  /* Bulk action bar */
  .bulk-bar{max-height:0;overflow:hidden;transition:max-height .3s cubic-bezier(.4,0,.2,1),padding .3s ease,padding-top:0;padding-bottom:0}
  .bulk-bar.show{max-height:80px;padding:12px 16px}

  /* Modal Overlay System */
  .modal-backdrop{display:none;position:fixed;inset:0;background:rgba(15,23,42,.5);z-index:60;backdrop-filter:blur(4px);align-items:center;justify-content:center}
  .modal-backdrop.show{display:flex!important}
  .modal-panel{background:#fff;border-radius:20px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 60px -15px rgba(15,23,42,.3)}

  .modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.5);backdrop-filter:blur(4px);z-index:100;display:flex;align-items:center;justify-content:center;padding:16px;opacity:0;pointer-events:none;transition:opacity .25s ease}
  .modal-overlay.show{opacity:1!important;pointer-events:auto!important}
  .modal-box{background:#fff;border-radius:20px;width:100%;max-width:640px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25);transform:translateY(20px) scale(.97);transition:transform .25s cubic-bezier(.4,0,.2,1)}
  .modal-overlay.show .modal-box{transform:translateY(0) scale(1)}

  /* Form & Images */
  .form-field{width:100%;padding:9px 12px;border:1.5px solid #E2E8F0;border-radius:10px;font-size:.8125rem;color:#1E293B;transition:all .15s ease;background:#fff}
  .form-field:focus{outline:none;border-color:#2D82FF;box-shadow:0 0 0 3px rgba(45,130,255,.1)}
  .form-field::placeholder{color:#94A3B8}

  .img-thumb{width:44px;height:44px;border-radius:8px;object-fit:cover;border:1.5px solid #E2E8F0;cursor:pointer;transition:all .15s ease;flex-shrink:0}
  .img-thumb:hover{border-color:#2D82FF;transform:scale(1.08)}

  /* Toast notification system */
  .toast-container{position:fixed;top:20px;right:20px;z-index:200;display:flex;flex-direction:column;gap:8px;pointer-events:none}
  .toast-item{pointer-events:auto;display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:12px;font-size:.875rem;box-shadow:0 10px 25px -5px rgba(0,0,0,.15);animation:toast-in .35s cubic-bezier(.4,0,.2,1) forwards;border:1px solid transparent}
  .toast-item.removing{animation:toast-out .3s ease forwards}
  .toast-success{background:#F0FDF4;border-color:#BBF7D0;color:#166534}
  .toast-error{background:#FFF1F2;border-color:#FECDD3;color:#9F1239}
  .toast-info{background:#EFF6FF;border-color:#BFDBFE;color:#1E40AF}
  @keyframes toast-in{from{transform:translateX(100%);opacity:0}to{transform:translateX(0);opacity:1}}
  @keyframes toast-out{from{transform:translateX(0);opacity:1}to{transform:translateX(100%);opacity:0}}

  /* Input & Button Styles */
  .input-field{width:100%;padding:9px 14px;border:1.5px solid #E2E8F0;border-radius:10px;font-size:.875rem;color:#334155;transition:border-color .15s,box-shadow .15s;background:#fff}
  .input-field:focus{outline:none;border-color:#2D82FF;box-shadow:0 0 0 3px rgba(45,130,255,.1)}
  .input-field::placeholder{color:#94A3B8}
  .btn-primary{display:inline-flex;align-items:center;gap:8px;background:#2D82FF;color:#fff;font-size:.875rem;font-weight:600;padding:10px 20px;border-radius:12px;border:none;cursor:pointer;transition:all .15s;box-shadow:0 4px 12px -2px rgba(45,130,255,.3)}
  .btn-primary:hover{background:#2566CC;transform:translateY(-1px);box-shadow:0 6px 16px -2px rgba(45,130,255,.35)}
  .btn-secondary{display:inline-flex;align-items:center;gap:8px;background:#fff;color:#475569;font-size:.875rem;font-weight:600;padding:10px 20px;border-radius:12px;border:1.5px solid #E2E8F0;cursor:pointer;transition:all .15s}
  .btn-secondary:hover{background:#F8FAFC;border-color:#CBD5E1}
  .btn-danger{display:inline-flex;align-items:center;gap:6px;background:#FEF2F2;color:#DC2626;font-size:.8rem;font-weight:600;padding:7px 14px;border-radius:10px;border:1.5px solid #FECACA;cursor:pointer;transition:all .15s}
  .btn-danger:hover{background:#FEE2E2;border-color:#FCA5A5}
  .btn-icon{display:inline-flex;align-items:center;justify-content:center;width:34px;height:34px;border-radius:10px;border:1.5px solid #E2E8F0;background:#fff;color:#64748B;cursor:pointer;transition:all .15s}
  .btn-icon:hover{background:#F8FAFC;border-color:#CBD5E1;color:#2D82FF}
  .status-select{appearance:none;background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24'%3E%3Cpath fill='%2364748B' d='M7 10l5 5 5-5z'/%3E%3C/svg%3E") no-repeat right 10px center;padding-right:28px;border:1.5px solid #E2E8F0;border-radius:10px;font-size:.8rem;font-weight:600;padding:6px 12px;color:#475569;cursor:pointer;transition:border-color .15s}
  .status-select:focus{outline:none;border-color:#2D82FF;box-shadow:0 0 0 3px rgba(45,130,255,.1)}
  .toast{position:fixed;bottom:24px;right:24px;z-index:100;padding:14px 20px;border-radius:14px;font-size:.875rem;font-weight:500;color:#fff;display:flex;align-items:center;gap:10px;box-shadow:0 12px 32px -8px rgba(0,0,0,.25)}

  @media(max-width:1023px){
    .sidebar{position:fixed;left:0;top:0;bottom:0;z-index:50;transform:translateX(-100%);width:260px!important}
    .sidebar.mobile-open{transform:translateX(0)}
    .main-area{margin-left:0!important}
  }
  .mono{font-family:'SF Mono',SFMono-Regular,Consolas,'Liberation Mono',Menlo,monospace}
</style>
</head>
<body class="antialiased text-slate-800">

<div class="toast-container" id="toastContainer"></div>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<?php require_once __DIR__ . '/sidebar.php'; ?>

<!-- ================= MAIN AREA ================= -->
<div class="main-area min-h-screen" id="mainArea">

  <!-- Top Navigation Header -->
  <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
    <div class="flex items-center justify-between px-4 sm:px-6 py-3">
      <div class="flex items-center gap-3">
        <button onclick="toggleSidebar()" class="lg:hidden text-slate-600 hover:text-cc-blue p-1.5 rounded-lg hover:bg-slate-100 transition"><span class="material-icons text-2xl">menu</span></button>
        <button onclick="collapseSidebar()" class="hidden lg:flex text-slate-400 hover:text-cc-blue p-1.5 rounded-lg hover:bg-slate-100 transition"><span class="material-icons text-2xl" id="collapseIcon">menu_open</span></button>
        <div class="hidden sm:flex items-center gap-2 text-sm">
          <a href="<?= $baseUrl ?>/admin/dashboard" class="text-slate-400 hover:text-cc-blue transition">Home</a>
          <span class="material-icons text-slate-300 text-[16px]">chevron_right</span>
          <span class="text-slate-700 font-medium"><?= htmlspecialchars($pageTitle) ?></span>
        </div>
      </div>
      <div class="flex items-center gap-2 sm:gap-4">
        <!-- Live Global Autocomplete Search Bar -->
        <div class="relative hidden md:block">
          <form action="<?= $baseUrl ?>/admin/search" method="GET" id="globalSearchForm">
            <div class="flex items-center bg-slate-50 rounded-xl border border-slate-200 px-3 py-2 gap-2 w-64 lg:w-80 focus-within:border-cc-blue focus-within:ring-2 focus-within:ring-cc-blue/10 transition">
              <span class="material-icons text-slate-400 text-[18px]">search</span>
              <input type="text" name="q" id="globalSearchInput" autocomplete="off" placeholder="Search orders, products, users... (Ctrl+K)" class="bg-transparent text-xs text-slate-700 w-full focus:outline-none placeholder:text-slate-400">
              <kbd class="hidden lg:inline-block bg-slate-200 text-slate-500 font-mono text-[10px] px-1.5 py-0.5 rounded">Ctrl+K</kbd>
            </div>
          </form>

          <!-- Live Autocomplete Floating Panel -->
          <div id="globalSearchPanel" class="hidden absolute left-0 mt-2 w-80 lg:w-96 bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 overflow-hidden text-xs">
            <div class="p-2.5 bg-slate-900 text-white flex items-center justify-between">
              <span class="text-[11px] font-bold uppercase tracking-wider text-slate-300">Live Search Results</span>
              <span id="globalSearchTotal" class="text-[10px] bg-cc-blue text-white px-2 py-0.5 rounded-full font-bold">0 matches</span>
            </div>
            <div id="globalSearchFeed" class="max-h-96 overflow-y-auto divide-y divide-slate-100">
              <div class="p-4 text-center text-slate-400">Type at least 2 characters to search...</div>
            </div>
            <div class="p-2.5 bg-slate-50 border-t border-slate-100 text-center">
              <a id="globalSearchViewAll" href="<?= $baseUrl ?>/admin/search" class="text-cc-blue text-xs font-semibold hover:underline">Press Enter for all results &rarr;</a>
            </div>
          </div>
        </div>

        <script>
        if (typeof window.BASE_URL === 'undefined') {
          window.BASE_URL = '<?= $baseUrl ?>';
        }
        var BASE_URL = window.BASE_URL;
        let searchTimer = null;

        // Ctrl + K Shortcut to focus search bar
        document.addEventListener('keydown', function(e) {
          if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            const input = document.getElementById('globalSearchInput');
            if (input) {
              input.focus();
              input.select();
            }
          }
        });

        const searchInput = document.getElementById('globalSearchInput');
        const searchPanel = document.getElementById('globalSearchPanel');

        if (searchInput && searchPanel) {
          searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            const query = this.value.trim();
            if (query.length < 2) {
              searchPanel.classList.add('hidden');
              return;
            }
            searchPanel.classList.remove('hidden');
            const feed = document.getElementById('globalSearchFeed');
            if (feed) {
              feed.innerHTML = '<div class="p-3 text-center text-slate-400">Searching database...</div>';
            }
            searchTimer = setTimeout(() => fetchLiveResults(query), 200);
          });

          searchInput.addEventListener('focus', function() {
            const query = this.value.trim();
            if (query.length >= 2) {
              searchPanel.classList.remove('hidden');
              fetchLiveResults(query);
            }
          });

          document.addEventListener('click', function(e) {
            if (searchInput && searchPanel && !searchInput.contains(e.target) && !searchPanel.contains(e.target)) {
              searchPanel.classList.add('hidden');
            }
          });
        }

        function escapeHtml(str) {
          if (!str) return '';
          return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        async function fetchLiveResults(query) {
          try {
            const res = await fetch(`${BASE_URL}/admin/search/api?q=${encodeURIComponent(query)}&limit=3`);
            const data = await res.json();
            if (data.success && searchPanel) {
              searchPanel.classList.remove('hidden');
              document.getElementById('globalSearchTotal').innerText = `${data.total_count} matches`;
              document.getElementById('globalSearchViewAll').href = `${BASE_URL}/admin/search?q=${encodeURIComponent(query)}`;

              const feed = document.getElementById('globalSearchFeed');
              if (!feed) return;

              if (data.total_count === 0) {
                feed.innerHTML = `<div class="p-4 text-center text-slate-400">No matches found for "${escapeHtml(query)}"</div>`;
                return;
              }

              let html = '';
              const r = data.results;

              if (r.products && r.products.length > 0) {
                html += `<div class="p-2 bg-slate-50 font-bold text-[10px] uppercase text-slate-400 tracking-wider">Products (${r.products.length})</div>`;
                r.products.forEach(p => {
                  html += `
                    <a href="${p.url}" class="p-2.5 hover:bg-slate-50 transition flex items-center justify-between">
                      <div>
                        <p class="font-bold text-slate-800 line-clamp-1">${escapeHtml(p.name)}</p>
                        <p class="text-[10px] text-slate-400 font-mono">SKU: ${escapeHtml(p.sku)}</p>
                      </div>
                      <span class="font-bold text-slate-900 text-xs">₹${parseFloat(p.base_price).toFixed(2)}</span>
                    </a>
                  `;
                });
              }

              if (r.orders && r.orders.length > 0) {
                html += `<div class="p-2 bg-slate-50 font-bold text-[10px] uppercase text-slate-400 tracking-wider">Orders (${r.orders.length})</div>`;
                r.orders.forEach(o => {
                  html += `
                    <a href="${o.url}" class="p-2.5 hover:bg-slate-50 transition flex items-center justify-between">
                      <div>
                        <p class="font-bold text-slate-800 font-mono">${escapeHtml(o.order_number)}</p>
                        <p class="text-[10px] text-slate-400">${o.status.toUpperCase()}</p>
                      </div>
                      <span class="font-bold text-emerald-600 text-xs">₹${parseFloat(o.grand_total).toFixed(2)}</span>
                    </a>
                  `;
                });
              }

              if (r.users && r.users.length > 0) {
                html += `<div class="p-2 bg-slate-50 font-bold text-[10px] uppercase text-slate-400 tracking-wider">Users (${r.users.length})</div>`;
                r.users.forEach(u => {
                  html += `
                    <a href="${u.url}" class="p-2.5 hover:bg-slate-50 transition flex items-center justify-between">
                      <div>
                        <p class="font-bold text-slate-800">${escapeHtml(u.name)}</p>
                        <p class="text-[10px] text-slate-400">${escapeHtml(u.email)}</p>
                      </div>
                    </a>
                  `;
                });
              }

              if (r.categories && r.categories.length > 0) {
                html += `<div class="p-2 bg-slate-50 font-bold text-[10px] uppercase text-slate-400 tracking-wider">Categories (${r.categories.length})</div>`;
                r.categories.forEach(c => {
                  html += `
                    <a href="${c.url}" class="p-2.5 hover:bg-slate-50 transition flex items-center justify-between">
                      <span class="font-bold text-slate-800">${escapeHtml(c.name)}</span>
                      <span class="text-[10px] text-slate-400 font-mono">/${escapeHtml(c.slug)}</span>
                    </a>
                  `;
                });
              }

              feed.innerHTML = html;
            }
          } catch(e) {}
        }
        </script>
        <!-- Notifications Dropdown Menu -->
        <div class="relative">
          <button id="notifBellBtn" onclick="toggleNotifDropdown()" class="relative p-2 rounded-xl hover:bg-slate-100 transition text-slate-500 hover:text-cc-blue" title="Notifications">
            <span class="material-icons text-[22px]">notifications</span>
            <span id="notifBadgeDot" class="hidden absolute top-1.5 right-1.5 w-2.5 h-2.5 bg-cc-pink rounded-full border-2 border-white"></span>
          </button>

          <!-- Dropdown Drawer -->
          <div id="notifDropdown" class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 overflow-hidden">
            <div class="p-3.5 bg-slate-900 text-white flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="material-icons text-cc-yellow text-[18px]">notifications_active</span>
                <span class="text-xs font-bold uppercase tracking-wider">Notifications</span>
                <span id="notifBadgeCount" class="bg-cc-pink text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full">0</span>
              </div>
              <a href="<?= $baseUrl ?>/admin/notifications" class="text-[11px] text-slate-300 hover:text-white underline">View All</a>
            </div>

            <div id="notifDropdownFeed" class="max-h-80 overflow-y-auto divide-y divide-slate-100 text-xs">
              <div class="p-4 text-center text-slate-400">Loading notifications...</div>
            </div>

            <div class="p-2.5 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
              <button onclick="headerMarkAllRead()" class="text-cc-blue hover:underline text-[11px] font-semibold">Mark all read</button>
              <a href="<?= $baseUrl ?>/admin/notifications" class="btn-secondary text-[11px] py-1 px-2.5">Open Center</a>
            </div>
          </div>
        </div>

        <script>
        function toggleNotifDropdown() {
          const dd = document.getElementById('notifDropdown');
          if (dd) {
            dd.classList.toggle('hidden');
            if (!dd.classList.contains('hidden')) {
              fetchHeaderUnreadFeed();
            }
          }
        }

        document.addEventListener('click', function(e) {
          const btn = document.getElementById('notifBellBtn');
          const dd = document.getElementById('notifDropdown');
          if (btn && dd && !btn.contains(e.target) && !dd.contains(e.target)) {
            dd.classList.add('hidden');
          }
        });

        async function fetchHeaderUnreadFeed() {
          try {
            const res = await fetch(`${BASE_URL}/admin/notifications/unread-feed?limit=5`);
            const data = await res.json();
            if (data.success) {
              const dot = document.getElementById('notifBadgeDot');
              const count = document.getElementById('notifBadgeCount');
              const feed = document.getElementById('notifDropdownFeed');

              if (data.count > 0) {
                if (dot) dot.classList.remove('hidden');
                if (count) count.innerText = data.count;
              } else {
                if (dot) dot.classList.add('hidden');
                if (count) count.innerText = '0';
              }

              if (feed) {
                if (data.items.length === 0) {
                  feed.innerHTML = '<div class="p-4 text-center text-slate-400">No new notifications.</div>';
                } else {
                  feed.innerHTML = data.items.map(item => `
                    <div class="p-3 hover:bg-slate-50 transition flex items-start gap-2.5 ${item.is_read == 0 ? 'bg-blue-50/20' : ''}">
                      <div class="w-7 h-7 rounded-lg ${item.is_read == 0 ? 'bg-cc-pink/10 text-cc-pink' : 'bg-slate-100 text-slate-400'} flex items-center justify-center shrink-0 mt-0.5">
                        <span class="material-icons text-[16px]">${item.channel === 'email' ? 'email' : 'notifications'}</span>
                      </div>
                      <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-800 truncate">${escapeHtml(item.title)}</p>
                        <p class="text-slate-500 text-[11px] line-clamp-1">${escapeHtml(item.body)}</p>
                        <span class="text-[10px] text-slate-400 mt-0.5 block">${item.time_ago}</span>
                      </div>
                    </div>
                  `).join('');
                }
              }
            }
          } catch(e) {}
        }

        async function headerMarkAllRead() {
          try {
            const res = await fetch(`${BASE_URL}/admin/notifications/mark-all-read`, { method: 'POST' });
            const data = await res.json();
            if (data.success) {
              fetchHeaderUnreadFeed();
            }
          } catch(e) {}
        }

        // Auto-fetch unread count on page load
        document.addEventListener('DOMContentLoaded', fetchHeaderUnreadFeed);
        </script>
        <div class="flex items-center gap-2.5 pl-2 sm:pl-3 border-l border-slate-200">
          <div class="w-8 h-8 rounded-full bg-cc-purple flex items-center justify-center text-white font-bold text-xs"><?= $initials ?></div>
          <div class="hidden sm:block">
            <p class="text-sm font-semibold text-slate-700 leading-tight"><?= htmlspecialchars($userName) ?></p>
            <p class="text-[11px] text-slate-400"><?= htmlspecialchars($roleName) ?></p>
          </div>
        </div>
      </div>
    </div>
  </header>
