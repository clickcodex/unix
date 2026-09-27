<?php
$query = htmlspecialchars($query ?? '');
$pageTitle = !empty($query) ? 'Search: ' . $query : 'Global Search Center';
$activeMenu = 'dashboard';

require_once __DIR__ . '/../layouts/header.php';

$results = $results ?? [
    'products' => [], 'orders' => [], 'users' => [], 'categories' => [],
    'coupons' => [], 'banners' => [], 'media' => [], 'audit_logs' => [], 'total_count' => 0
];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Header & Search Input Banner -->
  <div class="section-card p-6 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white relative overflow-hidden">
    <div class="relative z-10 max-w-3xl">
      <div class="flex items-center gap-2 text-cc-yellow text-xs font-bold uppercase tracking-wider mb-2">
        <span class="material-icons text-[18px]">saved_search</span> Global Database Search Engine
      </div>
      <h1 class="font-heading text-2xl sm:text-3xl font-extrabold mb-4">Search System Resources</h1>
      
      <form action="<?= $baseUrl ?>/admin/search" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
        <div class="relative flex-1">
          <span class="material-icons absolute left-4 top-3 text-slate-400 text-[20px]">search</span>
          <input type="text" name="q" value="<?= $query ?>" placeholder="Type product SKU, order #, customer name, email, promo code..." class="w-full pl-12 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:border-cc-blue focus:bg-white/20 transition text-sm">
        </div>
        <button type="submit" class="btn-primary text-sm shadow-lg shadow-cc-blue/30 py-3 px-6 shrink-0">
          <span class="material-icons text-[18px]">search</span> Search Database
        </button>
      </form>

      <p class="text-slate-400 text-xs mt-3">
        Press <kbd class="bg-white/10 px-2 py-0.5 rounded text-white font-mono text-[11px]">Ctrl + K</kbd> anywhere in the admin panel to open instant global search.
      </p>
    </div>
  </div>

  <!-- KPI Match Counts Grid -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="kpi-card p-4">
      <span class="text-xs font-bold text-slate-400 uppercase">Total Matches</span>
      <p class="font-heading text-2xl font-extrabold text-slate-900 mt-1"><?= number_format($results['total_count']) ?></p>
      <span class="text-[11px] text-slate-400">Across all 8 tables</span>
    </div>
    <div class="kpi-card p-4">
      <span class="text-xs font-bold text-slate-400 uppercase">Products</span>
      <p class="font-heading text-2xl font-extrabold text-cc-blue mt-1"><?= count($results['products']) ?></p>
      <span class="text-[11px] text-slate-400">Catalog items</span>
    </div>
    <div class="kpi-card p-4">
      <span class="text-xs font-bold text-slate-400 uppercase">Orders</span>
      <p class="font-heading text-2xl font-extrabold text-emerald-600 mt-1"><?= count($results['orders']) ?></p>
      <span class="text-[11px] text-slate-400">Transactions</span>
    </div>
    <div class="kpi-card p-4">
      <span class="text-xs font-bold text-slate-400 uppercase">Users &amp; Accounts</span>
      <p class="font-heading text-2xl font-extrabold text-cc-purple mt-1"><?= count($results['users']) ?></p>
      <span class="text-[11px] text-slate-400">Customers &amp; Admins</span>
    </div>
  </div>

  <?php if (empty($query)): ?>
    <div class="section-card p-12 text-center text-slate-400">
      <span class="material-icons text-5xl mb-3 text-slate-300">search</span>
      <h3 class="font-heading text-lg font-bold text-slate-700">Enter a Search Query</h3>
      <p class="text-sm mt-1 text-slate-400">Search for products, SKUs, customer emails, orders, coupons, media files, or audit logs.</p>
    </div>
  <?php elseif ($results['total_count'] === 0): ?>
    <div class="section-card p-12 text-center text-slate-400">
      <span class="material-icons text-5xl mb-3 text-slate-300">search_off</span>
      <h3 class="font-heading text-lg font-bold text-slate-700">No Results Found for "<?= $query ?>"</h3>
      <p class="text-sm mt-1 text-slate-400">Check spelling or try searching with different keywords (e.g. SKU, customer name, or order ID).</p>
    </div>
  <?php else: ?>

    <!-- Results Grouped by Category -->
    <div class="space-y-6">

      <!-- PRODUCTS MATCHES -->
      <?php if (!empty($results['products'])): ?>
        <div class="section-card p-5">
          <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
              <span class="material-icons text-cc-blue text-[20px]">inventory_2</span>
              <h3 class="font-heading text-base font-bold text-slate-900">Products (<?= count($results['products']) ?>)</h3>
            </div>
            <a href="<?= $baseUrl ?>/admin/products?search=<?= urlencode($query) ?>" class="text-xs text-cc-blue hover:underline font-semibold">View Catalog &rarr;</a>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($results['products'] as $p): ?>
              <a href="<?= $p['url'] ?>" class="p-3.5 rounded-xl border border-slate-200 hover:border-cc-blue hover:shadow-md transition bg-white flex items-center justify-between">
                <div>
                  <p class="text-sm font-bold text-slate-800 line-clamp-1"><?= htmlspecialchars($p['name']) ?></p>
                  <p class="text-xs text-slate-400 mono mt-0.5">SKU: <?= htmlspecialchars($p['sku']) ?></p>
                </div>
                <div class="text-right shrink-0 ml-3">
                  <span class="font-heading font-extrabold text-sm text-slate-900">₹<?= number_format($p['base_price'], 2) ?></span>
                  <span class="block text-[10px] uppercase font-bold <?= $p['is_active'] ? 'text-emerald-600' : 'text-slate-400' ?>"><?= $p['is_active'] ? 'Active' : 'Draft' ?></span>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- ORDERS MATCHES -->
      <?php if (!empty($results['orders'])): ?>
        <div class="section-card p-5">
          <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
              <span class="material-icons text-emerald-600 text-[20px]">shopping_bag</span>
              <h3 class="font-heading text-base font-bold text-slate-900">Orders (<?= count($results['orders']) ?>)</h3>
            </div>
            <a href="<?= $baseUrl ?>/admin/orders?search=<?= urlencode($query) ?>" class="text-xs text-emerald-600 hover:underline font-semibold">View All Orders &rarr;</a>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($results['orders'] as $o): ?>
              <a href="<?= $o['url'] ?>" class="p-3.5 rounded-xl border border-slate-200 hover:border-emerald-500 hover:shadow-md transition bg-white flex items-center justify-between">
                <div>
                  <p class="text-sm font-bold text-slate-900 mono"><?= htmlspecialchars($o['order_number']) ?></p>
                  <p class="text-xs text-slate-400 mt-0.5"><?= date('M j, Y', strtotime($o['created_at'])) ?></p>
                </div>
                <div class="text-right shrink-0 ml-3">
                  <span class="font-heading font-extrabold text-sm text-slate-900">₹<?= number_format($o['grand_total'], 2) ?></span>
                  <span class="block badge badge-slate uppercase text-[10px] mt-0.5"><?= htmlspecialchars($o['status']) ?></span>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- USERS MATCHES -->
      <?php if (!empty($results['users'])): ?>
        <div class="section-card p-5">
          <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
              <span class="material-icons text-cc-purple text-[20px]">group</span>
              <h3 class="font-heading text-base font-bold text-slate-900">Users &amp; Accounts (<?= count($results['users']) ?>)</h3>
            </div>
            <a href="<?= $baseUrl ?>/admin/users?search=<?= urlencode($query) ?>" class="text-xs text-cc-purple hover:underline font-semibold">Manage Users &rarr;</a>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            <?php foreach ($results['users'] as $u): ?>
              <a href="<?= $u['url'] ?>" class="p-3.5 rounded-xl border border-slate-200 hover:border-cc-purple hover:shadow-md transition bg-white flex items-center justify-between">
                <div>
                  <p class="text-sm font-bold text-slate-900"><?= htmlspecialchars($u['name']) ?></p>
                  <p class="text-xs text-slate-400 truncate max-w-[200px]"><?= htmlspecialchars($u['email']) ?></p>
                </div>
                <span class="badge <?= $u['is_active'] ? 'badge-green' : 'badge-red' ?> text-[10px]"><?= $u['is_active'] ? 'Active' : 'Disabled' ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- CATEGORIES & COUPONS GRID -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <?php if (!empty($results['categories'])): ?>
          <div class="section-card p-5">
            <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
              <span class="font-heading text-sm font-bold text-slate-900">Categories (<?= count($results['categories']) ?>)</span>
              <a href="<?= $baseUrl ?>/admin/categories" class="text-xs text-cc-blue hover:underline">View All</a>
            </div>
            <div class="space-y-2">
              <?php foreach ($results['categories'] as $c): ?>
                <a href="<?= $c['url'] ?>" class="p-2.5 rounded-lg border border-slate-100 hover:bg-slate-50 flex items-center justify-between text-xs font-semibold text-slate-800">
                  <span><?= htmlspecialchars($c['name']) ?></span>
                  <span class="mono text-[11px] text-slate-400">/<?= htmlspecialchars($c['slug']) ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <?php if (!empty($results['coupons'])): ?>
          <div class="section-card p-5">
            <div class="flex items-center justify-between mb-3 border-b border-slate-100 pb-2">
              <span class="font-heading text-sm font-bold text-slate-900">Coupons &amp; Offers (<?= count($results['coupons']) ?>)</span>
              <a href="<?= $baseUrl ?>/admin/coupons" class="text-xs text-cc-blue hover:underline">View All</a>
            </div>
            <div class="space-y-2">
              <?php foreach ($results['coupons'] as $cp): ?>
                <a href="<?= $cp['url'] ?>" class="p-2.5 rounded-lg border border-slate-100 hover:bg-slate-50 flex items-center justify-between text-xs font-semibold text-slate-800">
                  <span class="mono bg-cc-yellow/10 text-slate-900 px-2 py-0.5 rounded font-bold"><?= htmlspecialchars($cp['code']) ?></span>
                  <span class="text-slate-500"><?= $cp['discount_type'] === 'flat' ? '₹' . number_format($cp['discount_value']) : $cp['discount_value'] . '%' ?> OFF</span>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

      </div>

    </div>

  <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
