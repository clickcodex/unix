<?php
require_once __DIR__ . '/../layouts/header.php';

$user           = $user ?? [];
$orderSummary   = $user['order_summary'] ?? ['total_orders' => 0, 'lifetime_value' => 0, 'last_order_at' => null];
$orders         = $user['orders'] ?? [];
$addresses      = $user['addresses'] ?? [];
$reviews        = $user['reviews'] ?? [];

$totalOrders    = (int)($orderSummary['total_orders'] ?? count($orders));
$lifetimeSpend  = (float)($orderSummary['lifetime_value'] ?? 0);
$totalAddresses = count($addresses);
$totalReviews   = count($reviews);

// Calculate avg rating
$avgRating = 0;
if ($totalReviews > 0) {
    $avgRating = round(array_sum(array_column($reviews, 'rating')) / $totalReviews, 1);
}

// Recent 5 orders
$recentOrders = array_slice($orders, 0, 5);

// Status badge helper
function statusBadge(string $status): string {
    $map = [
        'pending'          => ['bg-amber-100',   'text-amber-800',   'schedule'],
        'confirmed'        => ['bg-blue-100',     'text-blue-800',    'check_circle'],
        'processing'       => ['bg-indigo-100',   'text-indigo-800',  'sync'],
        'shipped'          => ['bg-purple-100',   'text-purple-800',  'local_shipping'],
        'delivered'        => ['bg-green-100',    'text-green-800',   'done_all'],
        'cancelled'        => ['bg-red-100',      'text-red-800',     'cancel'],
        'return_requested' => ['bg-orange-100',   'text-orange-800',  'replay'],
        'returned'         => ['bg-slate-100',    'text-slate-600',   'assignment_return'],
        'refunded'         => ['bg-teal-100',     'text-teal-700',    'currency_rupee'],
    ];
    [$bg, $text, $icon] = $map[$status] ?? ['bg-slate-100', 'text-slate-600', 'help'];
    $label = ucwords(str_replace('_', ' ', $status));
    return "<span class=\"inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {$bg} {$text}\">
                <span class=\"material-icons\" style=\"font-size:11px\">{$icon}</span>{$label}
            </span>";
}

function payBadge(string $status): string {
    $map = [
        'paid'    => ['bg-emerald-100', 'text-emerald-800'],
        'pending' => ['bg-amber-100',   'text-amber-800'],
        'refunded'=> ['bg-teal-100',    'text-teal-700'],
        'failed'  => ['bg-red-100',     'text-red-800'],
    ];
    [$bg, $text] = $map[$status] ?? ['bg-slate-100', 'text-slate-600'];
    return "<span class=\"px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {$bg} {$text}\">" . ucfirst($status) . "</span>";
}
?>

<style>
  body { background: #F1F5F9; }
  .user-stat-card { transition: transform .2s ease, box-shadow .2s ease; }
  .user-stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(15,23,42,.08); }
  .order-row { transition: border-color .15s ease, box-shadow .15s ease; cursor: pointer; }
  .order-row:hover { border-color: #2D82FF; box-shadow: 0 4px 16px rgba(45,130,255,.08); }
  .sidebar-link { transition: all .15s ease; }
  @keyframes fadeUp { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }
  .fade-up { animation: fadeUp .35s ease-out both; }
  @keyframes scaleIn { from { transform:scale(.95); opacity:0; } to { transform:scale(1); opacity:1; } }
  .scale-in { animation: scaleIn .25s ease-out both; }
  .modal-scroll::-webkit-scrollbar { width: 4px; }
  .modal-scroll::-webkit-scrollbar-thumb { background: #CBD5E1; border-radius: 4px; }
  .star-filled { color: #FFB800; }
  .progress-ring { transform: rotate(-90deg); }
  @keyframes shimmer { 0% { background-position: -400px 0; } 100% { background-position: 400px 0; } }
  .skeleton { background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%); background-size: 800px 100%; animation: shimmer 1.5s infinite; border-radius: 8px; }
</style>

<main class="max-w-7xl mx-auto px-4 py-6 pb-24 md:pb-6">

  <!-- Breadcrumb -->
  <nav class="flex items-center gap-2 text-xs text-slate-500 mb-5">
    <a href="<?= $baseUrl ?>/" class="hover:text-[#2D82FF] transition">Home</a>
    <span class="material-icons text-[13px]">chevron_right</span>
    <span class="text-slate-800 font-semibold">My Account</span>
    <span class="material-icons text-[13px]">chevron_right</span>
    <span class="text-[#2D82FF] font-semibold">Dashboard</span>
  </nav>

  <div class="flex gap-6">
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 min-w-0 space-y-4">

      <!-- Mobile Sidebar Toggle Button -->
      <button onclick="toggleMobileSidebar(true)" class="lg:hidden flex items-center justify-between w-full bg-white border border-slate-200 p-3.5 rounded-2xl shadow-xs text-xs font-bold text-slate-800 hover:bg-slate-50 transition">
        <div class="flex items-center gap-2">
          <span class="material-icons text-[#2D82FF] text-lg">menu</span>
          <span>Account Navigation Menu</span>
        </div>
        <span class="bg-[#2D82FF]/10 text-[#2D82FF] text-[10px] font-extrabold px-2.5 py-1 rounded-lg">MENU</span>
      </button>

      <!-- Welcome Banner -->
      <div class="relative bg-gradient-to-br from-[#2D82FF] via-[#5B4FD9] to-[#0F172A] rounded-2xl p-6 sm:p-8 text-white overflow-hidden fade-up">
        <!-- Decorative circles -->
        <div class="absolute -top-8 -right-8 w-48 h-48 rounded-full bg-white/5"></div>
        <div class="absolute top-4 -right-4 w-28 h-28 rounded-full bg-white/5"></div>
        <div class="absolute bottom-0 left-1/3 w-32 h-32 rounded-full bg-white/5"></div>

        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
          <div class="flex items-start gap-4">
            <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-sm flex items-center justify-center text-2xl font-extrabold border border-white/20 shrink-0">
              <?= strtoupper(mb_substr($user['name'] ?? 'U', 0, 1)) ?>
            </div>
            <div>
              <div class="flex items-center gap-2 mb-1">
                <span class="bg-white/20 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">✓ Verified Customer</span>
                <span class="text-white/60 text-[10px]">ID #<?= sprintf('%06d', $user['id'] ?? 0) ?></span>
              </div>
              <h1 class="font-bold text-xl sm:text-2xl leading-tight">Welcome back, <?= htmlspecialchars(explode(' ', $user['name'] ?? 'Customer')[0]) ?>! 👋</h1>
              <p class="text-white/70 text-sm mt-0.5">Here's a summary of your account activity.</p>
            </div>
          </div>
          <div class="flex flex-wrap gap-2 shrink-0">
            <a href="<?= $baseUrl ?>/user/orders" class="bg-white text-[#2D82FF] font-bold text-xs px-4 py-2.5 rounded-xl hover:bg-blue-50 transition shadow-lg flex items-center gap-1.5">
              <span class="material-icons text-base">shopping_bag</span> My Orders
            </a>
            <a href="<?= $baseUrl ?>/" class="bg-white/15 hover:bg-white/25 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition backdrop-blur-sm flex items-center gap-1.5 border border-white/20">
              <span class="material-icons text-base">explore</span> Shop Now
            </a>
          </div>
        </div>

        <!-- Stats inline -->
        <div class="relative z-10 mt-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
          <?php
          $bannerStats = [
              ['val' => $totalOrders,                    'label' => 'Total Orders',       'icon' => 'receipt_long'],
              ['val' => '₹' . number_format($lifetimeSpend, 0), 'label' => 'Total Spent', 'icon' => 'account_balance_wallet'],
              ['val' => $totalAddresses,                 'label' => 'Saved Addresses',    'icon' => 'location_on'],
              ['val' => $totalReviews,                   'label' => 'Reviews Written',    'icon' => 'rate_review'],
          ];
          foreach ($bannerStats as $s): ?>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/15">
              <div class="flex items-center gap-2 mb-1">
                <span class="material-icons text-white/70 text-base"><?= $s['icon'] ?></span>
                <span class="text-white/60 text-[10px] font-medium"><?= $s['label'] ?></span>
              </div>
              <p class="font-extrabold text-lg text-white leading-none"><?= $s['val'] ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- KPI Cards Row -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <?php
        $kpis = [
            [
                'label'   => 'Total Orders',
                'value'   => number_format($totalOrders),
                'sub'     => 'Placed in store',
                'icon'    => 'shopping_bag',
                'color'   => 'text-[#2D82FF]',
                'bg'      => 'bg-blue-50',
                'border'  => 'border-blue-100',
            ],
            [
                'label'   => 'Lifetime Spend',
                'value'   => '₹' . number_format($lifetimeSpend, 2),
                'sub'     => 'Completed purchases',
                'icon'    => 'payments',
                'color'   => 'text-emerald-600',
                'bg'      => 'bg-emerald-50',
                'border'  => 'border-emerald-100',
            ],
            [
                'label'   => 'Avg. Rating',
                'value'   => $avgRating > 0 ? $avgRating . '/5 ★' : 'N/A',
                'sub'     => $totalReviews . ' review' . ($totalReviews !== 1 ? 's' : '') . ' submitted',
                'icon'    => 'star',
                'color'   => 'text-amber-500',
                'bg'      => 'bg-amber-50',
                'border'  => 'border-amber-100',
            ],
            [
                'label'   => 'Saved Addresses',
                'value'   => number_format($totalAddresses),
                'sub'     => 'Delivery locations',
                'icon'    => 'location_on',
                'color'   => 'text-purple-600',
                'bg'      => 'bg-purple-50',
                'border'  => 'border-purple-100',
            ],
        ];
        foreach ($kpis as $i => $kpi): ?>
          <div class="user-stat-card bg-white rounded-2xl border <?= $kpi['border'] ?> p-4 shadow-sm fade-up" style="animation-delay: <?= $i * 0.07 ?>s">
            <div class="flex items-center justify-between mb-3">
              <div class="w-9 h-9 rounded-xl <?= $kpi['bg'] ?> flex items-center justify-center">
                <span class="material-icons text-lg <?= $kpi['color'] ?>"><?= $kpi['icon'] ?></span>
              </div>
              <span class="material-icons text-slate-200 text-sm">trending_up</span>
            </div>
            <p class="font-extrabold text-xl text-slate-900 leading-none mb-1"><?= $kpi['value'] ?></p>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider"><?= $kpi['label'] ?></p>
            <p class="text-[11px] text-slate-400 mt-0.5"><?= $kpi['sub'] ?></p>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Recent Orders Section -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden fade-up" style="animation-delay:.28s">
        <div class="flex items-center justify-between p-5 border-b border-slate-100">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-xl bg-blue-50 flex items-center justify-center">
              <span class="material-icons text-[#2D82FF] text-lg">receipt_long</span>
            </div>
            <div>
              <h2 class="font-bold text-sm text-slate-900">Recent Orders</h2>
              <p class="text-[11px] text-slate-400"><?= $totalOrders ?> total orders placed</p>
            </div>
          </div>
          <a href="<?= $baseUrl ?>/user/orders" class="text-xs font-bold text-[#2D82FF] hover:underline flex items-center gap-1">
            View All <span class="material-icons text-sm">arrow_forward</span>
          </a>
        </div>

        <?php if (empty($recentOrders)): ?>
          <div class="p-12 text-center">
            <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-3">
              <span class="material-icons text-3xl text-slate-300">inventory_2</span>
            </div>
            <h3 class="font-bold text-slate-800 text-sm mb-1">No orders yet</h3>
            <p class="text-xs text-slate-400 mb-4">Start shopping to see your orders here.</p>
            <a href="<?= $baseUrl ?>/" class="inline-flex items-center gap-1.5 bg-[#2D82FF] hover:bg-blue-600 text-white font-bold text-xs px-5 py-2.5 rounded-xl transition">
              <span class="material-icons text-sm">explore</span> Start Shopping
            </a>
          </div>
        <?php else: ?>
          <div class="divide-y divide-slate-100">
            <?php foreach ($recentOrders as $idx => $o): ?>
              <div class="order-row p-4 hover:bg-slate-50 flex flex-col sm:flex-row sm:items-center gap-3 transition fade-up" style="animation-delay: <?= .32 + $idx * .06 ?>s" onclick="openDashOrderModal(<?= (int)$o['id'] ?>)">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                  <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center shrink-0">
                    <span class="material-icons text-slate-400 text-lg">shopping_bag</span>
                  </div>
                  <div class="min-w-0">
                    <p class="font-bold text-sm text-[#2D82FF] truncate">#<?= htmlspecialchars($o['order_number'] ?? ('ORD-' . $o['id'])) ?></p>
                    <p class="text-[11px] text-slate-400"><?= date('M j, Y', strtotime($o['created_at'])) ?></p>
                  </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                  <?= statusBadge($o['status'] ?? 'processing') ?>
                  <?= payBadge($o['payment_status'] ?? 'paid') ?>
                </div>
                <div class="sm:text-right shrink-0">
                  <p class="font-extrabold text-sm text-slate-900">₹<?= number_format((float)($o['grand_total'] ?? 0), 2) ?></p>
                </div>
                <span class="material-icons text-slate-300 text-xl hidden sm:block">chevron_right</span>
              </div>
            <?php endforeach; ?>
          </div>
          <?php if ($totalOrders > 5): ?>
            <div class="p-4 border-t border-slate-100 text-center">
              <a href="<?= $baseUrl ?>/user/orders" class="text-xs font-bold text-[#2D82FF] hover:underline">
                View all <?= $totalOrders ?> orders →
              </a>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <!-- Bottom Grid: Addresses + Reviews -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <!-- Saved Addresses -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden fade-up" style="animation-delay:.42s">
          <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-purple-50 flex items-center justify-center">
                <span class="material-icons text-purple-600 text-lg">location_on</span>
              </div>
              <div>
                <h2 class="font-bold text-sm text-slate-900">Saved Addresses</h2>
                <p class="text-[11px] text-slate-400"><?= $totalAddresses ?> delivery location<?= $totalAddresses !== 1 ? 's' : '' ?></p>
              </div>
            </div>
            <a href="<?= $baseUrl ?>/user/addresses" class="text-xs font-bold text-[#2D82FF] hover:underline">Manage</a>
          </div>

          <?php if (empty($addresses)): ?>
            <div class="p-8 text-center">
              <span class="material-icons text-3xl text-slate-300 block mb-2">add_location_alt</span>
              <p class="text-xs text-slate-400">No saved addresses. Add one to speed up checkout.</p>
            </div>
          <?php else: ?>
            <div class="p-4 space-y-3">
              <?php foreach (array_slice($addresses, 0, 2) as $addr): ?>
                <div class="border border-slate-200 rounded-xl p-3.5 space-y-1 text-xs hover:border-purple-200 transition">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-800"><?= htmlspecialchars($addr['recipient_name'] ?? $user['name'] ?? '') ?></span>
                    <span class="bg-purple-50 text-purple-700 font-bold text-[9px] px-2 py-0.5 rounded uppercase tracking-wider">
                      <?= htmlspecialchars($addr['address_type'] ?? 'Home') ?>
                    </span>
                  </div>
                  <p class="text-slate-500 leading-relaxed">
                    <?= htmlspecialchars(trim(($addr['address_line1'] ?? '') . ($addr['address_line2'] ? ', ' . $addr['address_line2'] : ''))) ?><br>
                    <?= htmlspecialchars($addr['city'] ?? '') ?>, <?= htmlspecialchars($addr['state'] ?? '') ?> — <?= htmlspecialchars($addr['postal_code'] ?? '') ?>
                  </p>
                  <?php if (!empty($addr['is_default'])): ?>
                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-600">
                      <span class="material-icons text-[10px]">check_circle</span> Default Address
                    </span>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
              <?php if ($totalAddresses > 2): ?>
                <p class="text-center text-[11px] text-slate-400 pt-1">+<?= $totalAddresses - 2 ?> more saved addresses</p>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

        <!-- My Reviews -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden fade-up" style="animation-delay:.48s">
          <div class="flex items-center justify-between p-5 border-b border-slate-100">
            <div class="flex items-center gap-2.5">
              <div class="w-9 h-9 rounded-xl bg-amber-50 flex items-center justify-center">
                <span class="material-icons text-amber-500 text-lg">star_rate</span>
              </div>
              <div>
                <h2 class="font-bold text-sm text-slate-900">My Reviews</h2>
                <p class="text-[11px] text-slate-400"><?= $totalReviews ?> review<?= $totalReviews !== 1 ? 's' : '' ?> submitted</p>
              </div>
            </div>
            <?php if ($totalReviews > 0): ?>
              <span class="text-xs font-bold text-amber-500">Avg <?= $avgRating ?>★</span>
            <?php endif; ?>
          </div>

          <?php if (empty($reviews)): ?>
            <div class="p-8 text-center">
              <span class="material-icons text-3xl text-slate-300 block mb-2">rate_review</span>
              <p class="text-xs text-slate-400">You haven't written any reviews yet.</p>
            </div>
          <?php else: ?>
            <div class="p-4 space-y-3">
              <?php foreach (array_slice($reviews, 0, 2) as $rev): ?>
                <div class="border border-slate-200 rounded-xl p-3.5 space-y-1.5 text-xs hover:border-amber-200 transition">
                  <div class="flex items-center justify-between gap-2">
                    <p class="font-bold text-slate-800 truncate"><?= htmlspecialchars($rev['product_name'] ?? 'Product') ?></p>
                    <div class="flex items-center gap-0.5 shrink-0">
                      <?php for ($s = 1; $s <= 5; $s++): ?>
                        <span class="material-icons text-[13px] <?= $s <= (int)$rev['rating'] ? 'text-amber-400' : 'text-slate-200' ?>">star</span>
                      <?php endfor; ?>
                    </div>
                  </div>
                  <?php if (!empty($rev['title'])): ?>
                    <p class="font-semibold text-slate-700">"<?= htmlspecialchars($rev['title']) ?>"</p>
                  <?php endif; ?>
                  <p class="text-slate-500 line-clamp-2"><?= htmlspecialchars($rev['body'] ?? '') ?></p>
                  <div class="flex items-center justify-between pt-1">
                    <span class="text-[10px] text-slate-400"><?= !empty($rev['created_at']) ? date('M j, Y', strtotime($rev['created_at'])) : '' ?></span>
                    <?php
                    $revStatus = $rev['status'] ?? 'pending';
                    $sBg = $revStatus === 'approved' ? 'bg-green-100 text-green-700' : ($revStatus === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700');
                    ?>
                    <span class="<?= $sBg ?> text-[9px] font-bold px-2 py-0.5 rounded uppercase"><?= ucfirst($revStatus) ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
              <?php if ($totalReviews > 2): ?>
                <p class="text-center text-[11px] text-slate-400 pt-1">+<?= $totalReviews - 2 ?> more reviews</p>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

      </div>

      <!-- Quick Account Actions -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 fade-up" style="animation-delay:.54s">
        <h2 class="font-bold text-sm text-slate-900 mb-4 flex items-center gap-2">
          <span class="material-icons text-slate-400 text-lg">bolt</span> Quick Actions
        </h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <?php
          $quickActions = [
              ['icon' => 'track_changes',  'label' => 'Track Order',      'href' => '/user/orders',   'color' => 'bg-blue-50 text-[#2D82FF]'],
              ['icon' => 'local_offer',    'label' => 'Browse Deals',     'href' => '/',              'color' => 'bg-orange-50 text-orange-600'],
              ['icon' => 'favorite',       'label' => 'My Wishlist',      'href' => '/wishlist',      'color' => 'bg-pink-50 text-pink-600'],
              ['icon' => 'help_outline',   'label' => 'Help & Support',   'href' => '/help',          'color' => 'bg-green-50 text-green-600'],
          ];
          foreach ($quickActions as $qa): ?>
            <a href="<?= $baseUrl . $qa['href'] ?>"
               class="group flex flex-col items-center gap-2 p-4 rounded-2xl border border-slate-100 hover:border-slate-200 hover:shadow-md transition text-center">
              <div class="w-11 h-11 rounded-xl <?= $qa['color'] ?> flex items-center justify-center group-hover:scale-110 transition-transform">
                <span class="material-icons text-xl"><?= $qa['icon'] ?></span>
              </div>
              <span class="text-xs font-semibold text-slate-700"><?= $qa['label'] ?></span>
            </a>
          <?php endforeach; ?>
        </div>
      </div>

    </div><!-- /Main Content -->
  </div><!-- /Flex Layout -->
</main>

<!-- Mobile Bottom Nav -->
<div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-50 flex justify-around py-2 shadow-2xl">
  <a href="<?= $baseUrl ?>/" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">home</span><span class="text-[9px] font-semibold">Home</span>
  </a>
  <a href="<?= $baseUrl ?>/user/orders" class="flex flex-col items-center text-[#2D82FF] py-1">
    <span class="material-icons text-2xl">shopping_bag</span><span class="text-[9px] font-semibold">Orders</span>
  </a>
  <a href="<?= $baseUrl ?>/wishlist" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">favorite_border</span><span class="text-[9px] font-semibold">Wishlist</span>
  </a>
  <a href="<?= $baseUrl ?>/cart" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">shopping_cart</span><span class="text-[9px] font-semibold">Cart</span>
  </a>
  <button onclick="toggleMobileSidebar(true)" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">person</span><span class="text-[9px] font-semibold">Account</span>
  </button>
</div>

<!-- Order Detail Quick-View Modal (Dashboard) -->
<div id="dash-order-modal" class="fixed inset-0 z-[70] items-end sm:items-center justify-center overflow-hidden hidden" style="display:none!important">
  <div onclick="closeDashOrderModal()" class="absolute inset-0 bg-black/50 cursor-pointer" style="backdrop-filter:blur(4px)"></div>
  <div class="bg-white rounded-t-2xl sm:rounded-2xl max-w-lg w-full shadow-2xl relative z-10 max-h-[85vh] flex flex-col scale-in mx-4">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between shrink-0">
      <h3 id="dash-modal-title" class="font-bold text-base text-slate-900"></h3>
      <button onclick="closeDashOrderModal()" class="text-slate-400 hover:text-slate-700 transition"><span class="material-icons">close</span></button>
    </div>
    <div id="dash-modal-body" class="flex-1 overflow-y-auto modal-scroll p-5 space-y-4"></div>
  </div>
</div>

<script>
// Pass PHP orders data to JS
const DASH_ORDERS = <?= json_encode($orders) ?>;

function openDashOrderModal(id) {
  const o = DASH_ORDERS.find(x => parseInt(x.id) === id);
  if (!o) return;

  const statusMap = {
    pending:          ['bg-amber-100 text-amber-800',  'schedule',        'Pending'],
    confirmed:        ['bg-blue-100 text-blue-800',    'check_circle',    'Confirmed'],
    processing:       ['bg-indigo-100 text-indigo-800','sync',            'Processing'],
    shipped:          ['bg-purple-100 text-purple-800','local_shipping',  'Shipped'],
    delivered:        ['bg-green-100 text-green-800',  'done_all',        'Delivered'],
    cancelled:        ['bg-red-100 text-red-800',      'cancel',          'Cancelled'],
    return_requested: ['bg-orange-100 text-orange-800','replay',          'Return Requested'],
    returned:         ['bg-slate-100 text-slate-600',  'assignment_return','Returned'],
    refunded:         ['bg-teal-100 text-teal-700',    'currency_rupee',  'Refunded'],
  };
  const [sBg, sIcon, sLabel] = statusMap[o.status] ?? ['bg-slate-100 text-slate-600', 'help', o.status];
  const payClr = o.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : o.payment_status === 'refunded' ? 'bg-teal-100 text-teal-700' : 'bg-amber-100 text-amber-800';

  document.getElementById('dash-modal-title').textContent = '#' + (o.order_number || 'ORD-' + o.id);
  document.getElementById('dash-modal-body').innerHTML = `
    <div class="flex flex-wrap gap-2">
      <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold uppercase ${sBg}">
        <span class="material-icons" style="font-size:13px">${sIcon}</span>${sLabel}
      </span>
      <span class="px-2.5 py-1 rounded-full text-xs font-bold uppercase ${payClr}">${(o.payment_status||'paid').charAt(0).toUpperCase()+(o.payment_status||'paid').slice(1)}</span>
    </div>
    <div class="bg-slate-50 rounded-xl p-4 space-y-2 text-sm">
      <div class="flex justify-between">
        <span class="text-slate-500">Order Date</span>
        <span class="font-semibold">${new Date(o.created_at).toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'})}</span>
      </div>
      <div class="flex justify-between border-t border-dashed border-slate-200 pt-2 mt-1">
        <span class="font-bold text-slate-700">Total Amount</span>
        <span class="font-extrabold text-[#FF5100] text-base">₹${parseFloat(o.grand_total||0).toLocaleString('en-IN', {minimumFractionDigits:2})}</span>
      </div>
    </div>
    <div class="flex flex-wrap gap-2 pt-2">
      <a href="<?= $baseUrl ?>/user/orders" class="flex-1 text-center bg-[#2D82FF] hover:bg-blue-600 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition">
        View Full Order Details
      </a>
    </div>
  `;

  const modal = document.getElementById('dash-order-modal');
  modal.style.display = 'flex';
  modal.classList.remove('hidden');
}

function closeDashOrderModal() {
  const modal = document.getElementById('dash-order-modal');
  modal.style.display = 'none';
  modal.classList.add('hidden');
}

// Close on ESC
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDashOrderModal(); });
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
