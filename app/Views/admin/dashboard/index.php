<?php
$pageTitle = 'Dashboard';
$activeMenu = 'dashboard';

require_once __DIR__ . '/../layouts/header.php';

$data = $dashboardData ?? [];
$totalRevenue = $data['totalRevenue'] ?? 0;
$ordersToday = $data['ordersToday'] ?? 0;
$outOfStockCount = $data['outOfStockCount'] ?? 0;
$pendingReviewsCount = $data['pendingReviewsCount'] ?? 0;
$avgRating = $data['avgRating'] ?? 0;
$statusCounts = $data['statusCounts'] ?? [];
$totalOrdersSum = $data['totalOrdersSum'] ?? 1;

$activeUsers = $data['activeUsers'] ?? 0;
$activeProducts = $data['activeProducts'] ?? 0;
$activeCategories = $data['activeCategories'] ?? 0;
$wishlistCount = $data['wishlistCount'] ?? 0;
$activeCarts = $data['activeCarts'] ?? 0;
$activeOffers = $data['activeOffers'] ?? 0;

$recentOrders = $data['recentOrders'] ?? [];
$topProducts = $data['topProducts'] ?? [];
$recentReviews = $data['recentReviews'] ?? [];
$recentTransactions = $data['recentTransactions'] ?? [];

$successTxTotal = $data['successTxTotal'] ?? 0;
$refundedTxTotal = $data['refundedTxTotal'] ?? 0;
$failedTxTotal = $data['failedTxTotal'] ?? 0;
$pendingTxTotal = $data['pendingTxTotal'] ?? 0;
?>

<!-- Main Content -->
<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Title Row -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">Dashboard</h1>
      <p class="text-slate-500 text-sm mt-1">Real-time marketplace overview and key performance metrics</p>
    </div>
    <div class="flex items-center gap-2.5 flex-wrap">
      <button class="flex items-center gap-2 bg-white border border-slate-200 text-slate-600 text-sm font-medium px-4 py-2.5 rounded-xl hover:bg-slate-50 transition">
        <span class="material-icons text-[18px]">calendar_month</span>
        <span class="hidden sm:inline">Today: <?= date('d M Y') ?></span>
      </button>
      <a href="<?= $baseUrl ?>/" target="_blank" class="flex items-center gap-2 bg-cc-blue hover:bg-blue-600 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition shadow-md shadow-cc-blue/20">
        <span class="material-icons text-[18px]">storefront</span>
        <span class="hidden sm:inline">Visit Storefront</span>
      </a>
    </div>
  </div>

  <!-- ===== KPI CARDS ===== -->
  <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 sm:gap-5">

    <!-- KPI 1: Total Revenue -->
    <div class="kpi-card p-5">
      <div class="flex items-start justify-between mb-4">
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Revenue</p>
          <p class="font-heading text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">₹<?= number_format($totalRevenue, 2) ?></p>
        </div>
        <div class="w-11 h-11 rounded-xl bg-cc-blue/10 flex items-center justify-center">
          <span class="material-icons text-cc-blue text-[22px]">account_balance_wallet</span>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-1.5">
          <span class="inline-flex items-center gap-0.5 text-green-600 text-xs font-bold bg-green-50 px-2 py-0.5 rounded-full">
            <span class="material-icons text-[14px]">trending_up</span> 12.5%
          </span>
          <span class="text-xs text-slate-400">vs last month</span>
        </div>
        <div class="spark-bar">
          <span style="height:40%;background:#2D82FF;opacity:.4"></span>
          <span style="height:55%;background:#2D82FF;opacity:.5"></span>
          <span style="height:45%;background:#2D82FF;opacity:.4"></span>
          <span style="height:70%;background:#2D82FF;opacity:.6"></span>
          <span style="height:60%;background:#2D82FF;opacity:.5"></span>
          <span style="height:50%;background:#2D82FF;opacity:.5"></span>
          <span style="height:80%;background:#2D82FF;opacity:.7"></span>
          <span style="height:75%;background:#2D82FF;opacity:.6"></span>
          <span style="height:90%;background:#2D82FF"></span>
          <span style="height:85%;background:#2D82FF"></span>
        </div>
      </div>
      <div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:#2D82FF;border-radius:0 0 16px 16px"></div>
    </div>

    <!-- KPI 2: Total Orders -->
    <div class="kpi-card p-5">
      <div class="flex items-start justify-between mb-4">
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Orders</p>
          <p class="font-heading text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1"><?= number_format($ordersToday) ?></p>
        </div>
        <div class="w-11 h-11 rounded-xl bg-cc-orange/10 flex items-center justify-center">
          <span class="material-icons text-cc-orange text-[22px]">shopping_bag</span>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-1.5">
          <span class="inline-flex items-center gap-0.5 text-green-600 text-xs font-bold bg-green-50 px-2 py-0.5 rounded-full">
            <span class="material-icons text-[14px]">trending_up</span> 8.3%
          </span>
          <span class="text-xs text-slate-400">vs yesterday</span>
        </div>
        <div class="spark-bar">
          <span style="height:50%;background:#FF5100;opacity:.4"></span>
          <span style="height:65%;background:#FF5100;opacity:.5"></span>
          <span style="height:40%;background:#FF5100;opacity:.4"></span>
          <span style="height:80%;background:#FF5100;opacity:.6"></span>
          <span style="height:55%;background:#FF5100;opacity:.5"></span>
          <span style="height:70%;background:#FF5100;opacity:.6"></span>
          <span style="height:60%;background:#FF5100;opacity:.5"></span>
          <span style="height:85%;background:#FF5100;opacity:.7"></span>
          <span style="height:75%;background:#FF5100;opacity:.7"></span>
          <span style="height:95%;background:#FF5100"></span>
        </div>
      </div>
      <div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:#FF5100;border-radius:0 0 16px 16px"></div>
    </div>

    <!-- KPI 3: Out of Stock -->
    <div class="kpi-card p-5">
      <div class="flex items-start justify-between mb-4">
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Out of Stock</p>
          <p class="font-heading text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1"><?= number_format($outOfStockCount) ?></p>
        </div>
        <div class="w-11 h-11 rounded-xl bg-cc-pink/10 flex items-center justify-center">
          <span class="material-icons text-cc-pink text-[22px]">remove_shopping_cart</span>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-1.5">
          <span class="inline-flex items-center gap-0.5 text-red-600 text-xs font-bold bg-red-50 px-2 py-0.5 rounded-full">
            <span class="material-icons text-[14px]">warning</span> Alert
          </span>
          <span class="text-xs text-slate-400">need restock</span>
        </div>
        <div class="flex -space-x-1">
          <div class="w-6 h-6 rounded-full bg-cc-pink/20 border-2 border-white flex items-center justify-center"><span class="text-[9px] font-bold text-cc-pink"><?= $outOfStockCount ?></span></div>
        </div>
      </div>
      <div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:#FF006B;border-radius:0 0 16px 16px"></div>
    </div>

    <!-- KPI 4: Pending Reviews -->
    <div class="kpi-card p-5">
      <div class="flex items-start justify-between mb-4">
        <div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Pending Reviews</p>
          <p class="font-heading text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1"><?= number_format($pendingReviewsCount) ?></p>
        </div>
        <div class="w-11 h-11 rounded-xl bg-cc-purple/10 flex items-center justify-center">
          <span class="material-icons text-cc-purple text-[22px]">rate_review</span>
        </div>
      </div>
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-1.5">
          <span class="text-cc-yellow text-sm font-bold">★ <?= $avgRating ?></span>
          <span class="text-xs text-slate-400">avg rating</span>
        </div>
        <div class="spark-bar">
          <span style="height:60%;background:#8C30F5;opacity:.4"></span>
          <span style="height:45%;background:#8C30F5;opacity:.4"></span>
          <span style="height:75%;background:#8C30F5;opacity:.6"></span>
          <span style="height:50%;background:#8C30F5;opacity:.5"></span>
          <span style="height:85%;background:#8C30F5;opacity:.7"></span>
          <span style="height:65%;background:#8C30F5;opacity:.6"></span>
          <span style="height:70%;background:#8C30F5;opacity:.6"></span>
          <span style="height:80%;background:#8C30F5;opacity:.7"></span>
          <span style="height:90%;background:#8C30F5"></span>
          <span style="height:88%;background:#8C30F5"></span>
        </div>
      </div>
      <div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:#8C30F5;border-radius:0 0 16px 16px"></div>
    </div>
  </div>

  <!-- ===== ORDER STATUS BREAKDOWN ===== -->
  <div class="section-card p-5 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
      <div>
        <h2 class="font-heading text-lg font-bold text-slate-900">Order Status Distribution</h2>
        <p class="text-xs text-slate-500 mt-0.5">Real-time count of orders categorized by fulfillment stage</p>
      </div>
      <a href="#" class="text-xs text-cc-blue hover:underline font-semibold flex items-center gap-1"><span class="material-icons text-[14px]">open_in_new</span> View All Orders</a>
    </div>
    <div class="grid grid-cols-3 sm:grid-cols-5 lg:grid-cols-9 gap-3">
      <?php
      $stMap = [
        'pending' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-700', 'sub' => 'text-yellow-600', 'label' => 'Pending'],
        'confirmed' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-700', 'sub' => 'text-blue-600', 'label' => 'Confirmed'],
        'processing' => ['bg' => 'bg-cyan-50', 'border' => 'border-cyan-200', 'text' => 'text-cyan-700', 'sub' => 'text-cyan-600', 'label' => 'Processing'],
        'shipped' => ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-200', 'text' => 'text-indigo-700', 'sub' => 'text-indigo-600', 'label' => 'Shipped'],
        'delivered' => ['bg' => 'bg-green-50', 'border' => 'border-green-200', 'text' => 'text-green-700', 'sub' => 'text-green-600', 'label' => 'Delivered'],
        'cancelled' => ['bg' => 'bg-red-50', 'border' => 'border-red-200', 'text' => 'text-red-700', 'sub' => 'text-red-600', 'label' => 'Cancelled'],
        'return_requested' => ['bg' => 'bg-orange-50', 'border' => 'border-orange-200', 'text' => 'text-orange-700', 'sub' => 'text-orange-600', 'label' => 'Return Req.'],
        'returned' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-700', 'sub' => 'text-amber-600', 'label' => 'Returned'],
        'refunded' => ['bg' => 'bg-slate-100', 'border' => 'border-slate-200', 'text' => 'text-slate-600', 'sub' => 'text-slate-500', 'label' => 'Refunded']
      ];
      foreach ($stMap as $stKey => $conf):
        $cnt = $statusCounts[$stKey] ?? 0;
      ?>
      <div class="<?= $conf['bg'] ?> border <?= $conf['border'] ?> rounded-xl p-3 text-center hover:shadow-md transition cursor-pointer">
        <p class="text-xl font-bold <?= $conf['text'] ?> font-heading"><?= number_format($cnt) ?></p>
        <p class="text-[11px] <?= $conf['sub'] ?> font-semibold mt-1"><?= $conf['label'] ?></p>
      </div>
      <?php endforeach; ?>
    </div>
    <!-- Visual progress bar -->
    <div class="mt-4 flex rounded-full overflow-hidden h-3">
      <?php
      $barColors = [
        'pending' => 'bg-yellow-400', 'confirmed' => 'bg-blue-400', 'processing' => 'bg-cyan-400',
        'shipped' => 'bg-indigo-400', 'delivered' => 'bg-green-500', 'cancelled' => 'bg-red-400',
        'return_requested' => 'bg-orange-400', 'returned' => 'bg-amber-400', 'refunded' => 'bg-slate-400'
      ];
      foreach ($stMap as $stKey => $conf):
        $cnt = $statusCounts[$stKey] ?? 0;
        $pct = round(($cnt / $totalOrdersSum) * 100, 1);
        if ($pct > 0):
      ?>
        <div class="<?= $barColors[$stKey] ?>" style="width:<?= $pct ?>%" title="<?= $conf['label'] ?>: <?= $cnt ?> (<?= $pct ?>%)"></div>
      <?php
        endif;
      endforeach;
      ?>
    </div>
  </div>

  <!-- ===== REVENUE CHART ===== -->
  <div class="section-card p-5 sm:p-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
      <div>
        <h2 class="font-heading text-lg font-bold text-slate-900">Revenue Trend — 14 Days</h2>
        <p class="text-xs text-slate-500 mt-0.5">Daily sales performance vs. sales target</p>
      </div>
      <div class="flex items-center gap-4 text-xs">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-cc-blue"></span> Revenue</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-cc-blue/15"></span> Target</span>
      </div>
    </div>
    <div class="flex items-end gap-1.5 sm:gap-2 h-48">
      <?php
      for ($i = 13; $i >= 0; $i--):
        $dLabel = date('M j', strtotime("-{$i} days"));
        $isToday = ($i === 0);
        $barH1 = rand(50, 90);
        $barH2 = rand(45, 100);
      ?>
      <div class="flex-1 flex flex-col items-center gap-1">
        <div class="w-full flex items-end gap-0.5 h-40">
          <div class="flex-1 bg-cc-blue/15 rounded-t-md" style="height:<?= $barH1 ?>%"></div>
          <div class="flex-1 bg-cc-blue rounded-t-md" style="height:<?= $barH2 ?>%"></div>
        </div>
        <span class="text-[10px] <?= $isToday ? 'text-slate-900 font-bold' : 'text-slate-400' ?>"><?= $isToday ? 'Today' : $dLabel ?></span>
      </div>
      <?php endfor; ?>
    </div>
  </div>

  <!-- ===== QUICK METRICS ROW ===== -->
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
    <div class="bg-white rounded-xl border border-slate-200 px-4 py-3 flex items-center gap-3 hover:border-cc-blue hover:shadow-sm transition">
      <span class="material-icons text-cc-blue text-[20px]">people</span>
      <div><p class="text-lg font-bold text-slate-800 leading-tight"><?= number_format($activeUsers) ?></p><p class="text-[11px] text-slate-500">Active Users</p></div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 px-4 py-3 flex items-center gap-3 hover:border-cc-blue hover:shadow-sm transition">
      <span class="material-icons text-cc-purple text-[20px]">inventory_2</span>
      <div><p class="text-lg font-bold text-slate-800 leading-tight"><?= number_format($activeProducts) ?></p><p class="text-[11px] text-slate-500">Active Products</p></div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 px-4 py-3 flex items-center gap-3 hover:border-cc-blue hover:shadow-sm transition">
      <span class="material-icons text-cc-orange text-[20px]">category</span>
      <div><p class="text-lg font-bold text-slate-800 leading-tight"><?= number_format($activeCategories) ?></p><p class="text-[11px] text-slate-500">Categories</p></div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 px-4 py-3 flex items-center gap-3 hover:border-cc-blue hover:shadow-sm transition">
      <span class="material-icons text-cc-pink text-[20px]">favorite</span>
      <div><p class="text-lg font-bold text-slate-800 leading-tight"><?= number_format($wishlistCount) ?></p><p class="text-[11px] text-slate-500">Wishlist Items</p></div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 px-4 py-3 flex items-center gap-3 hover:border-cc-blue hover:shadow-sm transition">
      <span class="material-icons text-green-600 text-[20px]">shopping_cart</span>
      <div><p class="text-lg font-bold text-slate-800 leading-tight"><?= number_format($activeCarts) ?></p><p class="text-[11px] text-slate-500">Active Carts</p></div>
    </div>
    <div class="bg-white rounded-xl border border-slate-200 px-4 py-3 flex items-center gap-3 hover:border-cc-blue hover:shadow-sm transition">
      <span class="material-icons text-cc-yellow text-[20px]">sell</span>
      <div><p class="text-lg font-bold text-slate-800 leading-tight"><?= number_format($activeOffers) ?></p><p class="text-[11px] text-slate-500">Active Offers</p></div>
    </div>
  </div>

  <!-- ===== RECENT ORDERS TABLE ===== -->
  <div class="section-card">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
      <div class="flex items-center gap-3">
        <span class="material-icons text-cc-orange text-[22px]">shopping_bag</span>
        <div>
          <h2 class="font-heading text-lg font-bold text-slate-900">Recent Orders</h2>
        </div>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <button class="text-xs bg-cc-blue/10 text-cc-blue font-semibold px-3 py-1.5 rounded-lg">Total (<?= count($recentOrders) ?>)</button>
        <a href="#" class="text-xs text-cc-blue hover:underline font-semibold ml-1">View All →</a>
      </div>
    </div>
    <div class="overflow-x-auto">
      <table class="data-table">
        <thead>
          <tr>
            <th>Order Number</th>
            <th>Customer</th>
            <th>Items</th>
            <th>Total Amount</th>
            <th>Payment Status</th>
            <th>Order Status</th>
            <th>Placed Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentOrders)): ?>
          <tr>
            <td colspan="7" class="text-center py-6 text-slate-400">No recent orders found.</td>
          </tr>
          <?php else: ?>
            <?php foreach ($recentOrders as $ord): ?>
            <tr>
              <td class="font-semibold text-slate-800 mono text-xs"><?= htmlspecialchars($ord['order_number']) ?></td>
              <td>
                <div class="flex items-center gap-2.5">
                  <div class="w-7 h-7 rounded-full bg-cc-blue/10 flex items-center justify-center text-cc-blue text-[10px] font-bold shrink-0">
                    <?= strtoupper(substr($ord['user_name'] ?? 'U', 0, 2)) ?>
                  </div>
                  <div>
                    <p class="font-medium text-slate-700 text-sm"><?= htmlspecialchars($ord['user_name'] ?? 'Guest Customer') ?></p>
                  </div>
                </div>
              </td>
              <td class="text-sm"><?= htmlspecialchars($ord['items_summary'] ?: 'Products') ?></td>
              <td class="font-semibold text-slate-800">₹<?= number_format($ord['total_amount'], 2) ?></td>
              <td>
                <?php
                $pBadge = [
                  'paid' => 'badge-green',
                  'unpaid' => 'badge-slate',
                  'refunded' => 'badge-red',
                  'partially_refunded' => 'badge-orange'
                ][$ord['payment_status']] ?? 'badge-slate';
                ?>
                <span class="badge <?= $pBadge ?>"><?= htmlspecialchars($ord['payment_status']) ?></span>
              </td>
              <td>
                <?php
                $sBadge = [
                  'delivered' => 'badge-green',
                  'shipped' => 'badge-blue',
                  'processing' => 'badge-cyan',
                  'confirmed' => 'badge-blue',
                  'pending' => 'badge-yellow',
                  'cancelled' => 'badge-red',
                  'return_requested' => 'badge-orange',
                  'returned' => 'badge-yellow',
                  'refunded' => 'badge-slate'
                ][$ord['status']] ?? 'badge-slate';
                ?>
                <span class="badge <?= $sBadge ?>"><?= htmlspecialchars($ord['status']) ?></span>
              </td>
              <td class="text-slate-400 text-sm"><?= date('d M, h:i A', strtotime($ord['placed_at'])) ?></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ===== TWO-COLUMN: PRODUCTS + REVIEWS ===== -->
  <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">

    <!-- PRODUCTS TABLE -->
    <div class="section-card">
      <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100">
        <div class="flex items-center gap-3">
          <span class="material-icons text-cc-blue text-[22px]">inventory_2</span>
          <div>
            <h2 class="font-heading text-lg font-bold text-slate-900">Active Products</h2>
          </div>
        </div>
        <a href="#" class="text-xs text-cc-blue hover:underline font-semibold">Manage →</a>
      </div>
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead>
            <tr>
              <th>Product</th>
              <th>Category</th>
              <th>Price</th>
              <th>Status</th>
              <th>Rating</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($topProducts)): ?>
            <tr>
              <td colspan="5" class="text-center py-6 text-slate-400">No active products found.</td>
            </tr>
            <?php else: ?>
              <?php foreach ($topProducts as $prod): ?>
              <tr>
                <td>
                  <div class="flex items-center gap-3">
                    <?php if (!empty($prod['image_url'])): ?>
                      <img src="<?= htmlspecialchars($prod['image_url']) ?>" alt="" class="w-10 h-10 rounded-lg object-cover shrink-0">
                    <?php else: ?>
                      <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 shrink-0">
                        <span class="material-icons text-[20px]">image</span>
                      </div>
                    <?php endif; ?>
                    <div class="min-w-0">
                      <p class="font-medium text-slate-700 text-sm truncate max-w-[130px]"><?= htmlspecialchars($prod['name']) ?></p>
                      <p class="text-[10px] text-slate-400 mono">SKU: <?= htmlspecialchars($prod['sku']) ?></p>
                    </div>
                  </div>
                </td>
                <td><span class="badge badge-blue"><?= htmlspecialchars($prod['category_name'] ?: 'General') ?></span></td>
                <td class="font-semibold text-slate-800">₹<?= number_format($prod['sale_price'] ?: $prod['base_price'], 2) ?></td>
                <td>
                  <?php if ((int)$prod['is_in_stock'] === 1): ?>
                    <span class="badge badge-green text-[11px]"><span class="material-icons text-[12px]">check</span> In Stock</span>
                  <?php else: ?>
                    <span class="badge badge-red text-[11px]"><span class="material-icons text-[12px]">close</span> Out of Stock</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="flex items-center gap-1">
                    <span class="text-cc-yellow text-sm">★</span>
                    <span class="text-sm font-semibold text-slate-700"><?= number_format($prod['average_rating'], 1) ?></span>
                    <span class="text-[10px] text-slate-400 mono">(<?= number_format($prod['review_count']) ?>)</span>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- REVIEWS TABLE -->
    <div class="section-card">
      <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-100">
        <div class="flex items-center gap-3">
          <span class="material-icons text-cc-purple text-[22px]">rate_review</span>
          <div>
            <h2 class="font-heading text-lg font-bold text-slate-900">Recent Reviews</h2>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span class="badge badge-yellow text-[11px]"><?= $pendingReviewsCount ?> pending</span>
          <a href="#" class="text-xs text-cc-blue hover:underline font-semibold">All →</a>
        </div>
      </div>
      <div class="divide-y divide-slate-50">
        <?php if (empty($recentReviews)): ?>
          <div class="p-6 text-center text-slate-400">No reviews recorded yet.</div>
        <?php else: ?>
          <?php foreach ($recentReviews as $rev): ?>
          <?php
          $rBadge = [
            'approved' => 'badge-green',
            'pending' => 'badge-yellow',
            'rejected' => 'badge-red'
          ][$rev['status']] ?? 'badge-slate';
          ?>
          <div class="p-4 sm:p-5 hover:bg-slate-50/50 transition <?= $rev['status'] === 'pending' ? 'bg-yellow-50/30' : '' ?>">
            <div class="flex items-start justify-between mb-2">
              <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-cc-purple/10 flex items-center justify-center text-cc-purple text-xs font-bold shrink-0">
                  <?= strtoupper(substr($rev['user_name'] ?? 'U', 0, 2)) ?>
                </div>
                <div>
                  <p class="text-sm font-semibold text-slate-700"><?= htmlspecialchars($rev['user_name'] ?? 'Customer') ?></p>
                  <p class="text-[11px] text-slate-400">on <span class="font-medium text-cc-blue"><?= htmlspecialchars($rev['product_name'] ?: 'Product') ?></span></p>
                </div>
              </div>
              <div class="flex items-center gap-2 shrink-0">
                <span class="badge <?= $rBadge ?>"><?= htmlspecialchars($rev['status']) ?></span>
                <span class="text-[11px] text-slate-400"><?= date('d M', strtotime($rev['created_at'])) ?></span>
              </div>
            </div>
            <div class="flex items-center gap-0.5 mb-2">
              <?php for ($s = 1; $s <= 5; $s++): ?>
                <span class="material-icons <?= $s <= $rev['rating'] ? 'stars-filled' : 'stars-empty' ?> text-[16px]">star</span>
              <?php endfor; ?>
              <span class="text-xs text-slate-400 ml-1.5 font-medium">Rating: <?= $rev['rating'] ?></span>
            </div>
            <p class="text-sm text-slate-600 leading-relaxed"><?= htmlspecialchars($rev['body'] ?: ($rev['title'] ?: 'No review body.')) ?></p>
            <div class="flex items-center gap-3 mt-3">
              <?php if ((int)$rev['is_verified_purchase'] === 1): ?>
                <span class="badge badge-green text-[11px]"><span class="material-icons text-[12px]">verified</span> Verified Purchase</span>
              <?php else: ?>
                <span class="badge badge-slate text-[11px]">Unverified</span>
              <?php endif; ?>
              <span class="text-[11px] text-slate-400">Helpful: <?= $rev['helpful_count'] ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ===== PAYMENT TRANSACTIONS TABLE ===== -->
  <div class="section-card">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
      <div class="flex items-center gap-3">
        <span class="material-icons text-cc-yellow text-[22px]">receipt_long</span>
        <div>
          <h2 class="font-heading text-lg font-bold text-slate-900">Payment Transactions</h2>
        </div>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <button class="text-xs bg-cc-blue/10 text-cc-blue font-semibold px-3 py-1.5 rounded-lg">Total (<?= count($recentTransactions) ?>)</button>
        <a href="#" class="text-xs text-cc-blue hover:underline font-semibold ml-1">View All →</a>
      </div>
    </div>
    <div class="overflow-x-auto">
      <table class="data-table">
        <thead>
          <tr>
            <th>Transaction ID</th>
            <th>Order Number</th>
            <th>Customer</th>
            <th>Gateway</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Processed Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($recentTransactions)): ?>
          <tr>
            <td colspan="7" class="text-center py-6 text-slate-400">No payment transactions recorded yet.</td>
          </tr>
          <?php else: ?>
            <?php foreach ($recentTransactions as $tx): ?>
            <tr>
              <td class="mono text-xs font-semibold text-slate-600">PTX-<?= sprintf('%05d', $tx['id']) ?></td>
              <td class="text-sm text-cc-blue font-medium mono"><?= htmlspecialchars($tx['order_number'] ?: 'N/A') ?></td>
              <td class="text-sm"><?= htmlspecialchars($tx['user_name'] ?: 'Customer') ?></td>
              <td>
                <div class="flex items-center gap-2">
                  <span class="material-icons text-green-600 text-[18px]">account_balance</span>
                  <div>
                    <span class="text-sm font-medium capitalize"><?= htmlspecialchars($tx['gateway'] ?: 'razorpay') ?></span>
                  </div>
                </div>
              </td>
              <td class="font-semibold <?= $tx['status'] === 'refunded' ? 'text-red-500' : 'text-green-600' ?>">
                <?= $tx['status'] === 'refunded' ? '-' : '+' ?>₹<?= number_format($tx['amount'], 2) ?>
              </td>
              <td>
                <?php
                $tBadge = [
                  'success' => 'badge-green',
                  'pending' => 'badge-yellow',
                  'initiated' => 'badge-slate',
                  'failed' => 'badge-red',
                  'refunded' => 'badge-red'
                ][$tx['status']] ?? 'badge-slate';
                ?>
                <span class="badge <?= $tBadge ?>"><?= htmlspecialchars($tx['status']) ?></span>
              </td>
              <td class="text-slate-400 text-sm"><?= $tx['processed_at'] ? date('d M, h:i A', strtotime($tx['processed_at'])) : '—' ?></td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <!-- Transaction Summary -->
    <div class="px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
      <span class="text-slate-500">Total Paid: <strong class="text-slate-800">₹<?= number_format($totalRevenue, 2) ?></strong></span>
      <span class="text-slate-300">|</span>
      <span class="text-green-600">Success: <strong>₹<?= number_format($successTxTotal, 2) ?></strong></span>
      <span class="text-slate-300">|</span>
      <span class="text-red-500">Refunded: <strong>-₹<?= number_format($refundedTxTotal, 2) ?></strong></span>
      <span class="text-slate-300">|</span>
      <span class="text-yellow-600">Pending: <strong>₹<?= number_format($pendingTxTotal, 2) ?></strong></span>
    </div>
  </div>

  <div class="h-4"></div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
