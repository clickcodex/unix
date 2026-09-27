<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$userName = $_SESSION['admin_user_name'] ?? 'Super Admin';
$roleName = $_SESSION['admin_role_name'] ?? 'Super Admin';

$words = explode(' ', $userName);
$initials = strtoupper(substr($words[0] ?? 'A', 0, 1) . substr($words[1] ?? '', 0, 1));
if (empty($initials)) $initials = 'AD';

$activeMenu = $activeMenu ?? 'dashboard';
?>
<!-- ================= SIDEBAR ================= -->
<aside class="sidebar fixed top-0 left-0 bottom-0 bg-cc-dark z-50 flex flex-col" id="sidebar">
  <div class="flex items-center gap-2.5 px-5 py-5 border-b border-white/5">
    <div class="w-9 h-9 rounded-xl bg-cc-blue/20 border border-cc-blue/30 flex items-center justify-center shrink-0">
      <svg width="22" height="22" viewBox="0 0 100 100"><path d="M78 24 A38 38 0 1 0 78 76" fill="none" stroke="white" stroke-width="14" stroke-linecap="round"/><path d="M55 38 L44 50 L55 62" fill="none" stroke="white" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" opacity=".85"/></svg>
    </div>
    <span class="sidebar-brand-text font-heading font-extrabold text-lg text-white leading-none">Click<span class="text-cc-yellow">Codex</span></span>
  </div>

  <nav class="flex-1 overflow-y-auto scrollbar-thin py-4 px-3 space-y-1">
    <p class="sidebar-section-title text-[10px] font-bold text-white/25 uppercase tracking-widest px-3 mb-2">Main</p>
    <a href="<?= $baseUrl ?>/admin/dashboard" class="nav-item <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
      <span class="material-icons">dashboard</span><span class="nav-label">Dashboard</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/orders" class="nav-item <?= $activeMenu === 'orders' ? 'active' : '' ?>">
      <span class="material-icons">shopping_bag</span><span class="nav-label">Orders</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/categories" class="nav-item <?= $activeMenu === 'categories' ? 'active' : '' ?>">
      <span class="material-icons">category</span><span class="nav-label">Categories</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/products" class="nav-item <?= $activeMenu === 'products' ? 'active' : '' ?>">
      <span class="material-icons">inventory_2</span><span class="nav-label">Products</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/users" class="nav-item <?= $activeMenu === 'users' ? 'active' : '' ?>">
      <span class="material-icons">group</span><span class="nav-label">Users</span>
    </a>

    <p class="sidebar-section-title text-[10px] font-bold text-white/25 uppercase tracking-widest px-3 mb-2 mt-6">Management</p>
    <a href="<?= $baseUrl ?>/admin/units" class="nav-item <?= $activeMenu === 'units' ? 'active' : '' ?>">
      <span class="material-icons">square_foot</span><span class="nav-label">Units</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/tags" class="nav-item <?= $activeMenu === 'tags' ? 'active' : '' ?>">
      <span class="material-icons">label</span><span class="nav-label">Tags</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/inventory" class="nav-item <?= $activeMenu === 'inventory' ? 'active' : '' ?>">
      <span class="material-icons">inventory</span><span class="nav-label">Inventory Log</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/returns" class="nav-item <?= $activeMenu === 'returns' ? 'active' : '' ?>">
      <span class="material-icons">assignment_return</span><span class="nav-label">Returns &amp; Refunds</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/search-analytics" class="nav-item <?= $activeMenu === 'search_analytics' ? 'active' : '' ?>">
      <span class="material-icons">analytics</span><span class="nav-label">Search Analytics</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/reviews" class="nav-item <?= $activeMenu === 'reviews' ? 'active' : '' ?>">
      <span class="material-icons">reviews</span><span class="nav-label">Reviews</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/payments" class="nav-item <?= $activeMenu === 'payments' ? 'active' : '' ?>">
      <span class="material-icons">account_balance_wallet</span><span class="nav-label">Payments</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/shipments" class="nav-item <?= $activeMenu === 'shipments' ? 'active' : '' ?>">
      <span class="material-icons">local_shipping</span><span class="nav-label">Shipments</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/coupons" class="nav-item <?= $activeMenu === 'offers' ? 'active' : '' ?>">
      <span class="material-icons">sell</span><span class="nav-label">Offers</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/banners" class="nav-item <?= $activeMenu === 'banners' ? 'active' : '' ?>">
      <span class="material-icons">wallpaper</span><span class="nav-label">Banners</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/media" class="nav-item <?= $activeMenu === 'media' ? 'active' : '' ?>">
      <span class="material-icons">photo_library</span><span class="nav-label">Media</span>
    </a>

    <p class="sidebar-section-title text-[10px] font-bold text-white/25 uppercase tracking-widest px-3 mb-2 mt-6">System</p>
    <a href="<?= $baseUrl ?>/admin/roles" class="nav-item <?= $activeMenu === 'roles' ? 'active' : '' ?>">
      <span class="material-icons">admin_panel_settings</span><span class="nav-label">Roles &amp; RBAC</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/settings" class="nav-item <?= $activeMenu === 'settings' ? 'active' : '' ?>">
      <span class="material-icons">settings</span><span class="nav-label">Settings</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/audit" class="nav-item <?= $activeMenu === 'audit' ? 'active' : '' ?>">
      <span class="material-icons">history</span><span class="nav-label">Audit Log</span>
    </a>
    <a href="<?= $baseUrl ?>/admin/notifications" class="nav-item <?= $activeMenu === 'notifications' ? 'active' : '' ?>">
      <span class="material-icons">notifications</span><span class="nav-label">Notifications</span>
    </a>
    <form action="<?= $baseUrl ?>/admin/logout" method="POST" class="w-full">
      <button type="submit" class="nav-item text-rose-400 hover:bg-rose-500/10 hover:text-rose-300">
        <span class="material-icons text-rose-400">logout</span>
        <span class="nav-label">Logout</span>
      </button>
    </form>
  </nav>

  <!-- User profile footer -->
  <div class="border-t border-white/5 px-4 py-4 flex items-center justify-between">
    <div class="flex items-center gap-3 sidebar-user-info min-w-0">
      <div class="w-9 h-9 rounded-full bg-cc-purple flex items-center justify-center text-white font-bold text-sm shrink-0">
        <?= $initials ?>
      </div>
      <div class="min-w-0">
        <p class="text-white text-sm font-semibold truncate"><?= htmlspecialchars($userName) ?></p>
        <p class="text-white/35 text-xs truncate"><?= htmlspecialchars($roleName) ?></p>
      </div>
    </div>
    <form action="<?= $baseUrl ?>/admin/logout" method="POST">
      <button type="submit" title="Logout" class="p-2 rounded-lg text-white/40 hover:text-white hover:bg-white/10 transition">
        <span class="material-icons text-[18px]">power_settings_new</span>
      </button>
    </form>
  </div>
</aside>
