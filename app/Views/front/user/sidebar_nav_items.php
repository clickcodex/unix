<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$activeTab = $activeTab ?? 'dashboard';

$sidebarNavGroups = [
    'Shopping' => [
        ['tab' => 'dashboard',  'href' => '/user/dashboard', 'icon' => 'dashboard',         'label' => 'My Dashboard'],
        ['tab' => 'orders',     'href' => '/user/orders',    'icon' => 'shopping_bag',       'label' => 'My Orders'],
        ['tab' => 'wishlist',   'href' => '/wishlist',       'icon' => 'favorite_border',    'label' => 'My Wishlist'],
        ['tab' => 'cart',       'href' => '/cart',           'icon' => 'shopping_cart',      'label' => 'Shopping Cart'],
    ],
    'Account' => [
        ['tab' => 'profile',    'href' => '/user/profile',   'icon' => 'person',             'label' => 'Edit Profile'],
        ['tab' => 'addresses',  'href' => '/user/addresses', 'icon' => 'location_on',        'label' => 'Saved Addresses'],
        ['tab' => 'reviews',    'href' => '/user/reviews',   'icon' => 'rate_review',        'label' => 'My Reviews'],
    ],
    'Support' => [
        ['tab' => 'help',       'href' => '/help',           'icon' => 'help_outline',       'label' => 'Help Center'],
    ],
];
?>

<?php foreach ($sidebarNavGroups as $groupLabel => $sidebarGroupItems): ?>
  <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 pt-3 pb-1"><?= $groupLabel ?></p>
  <?php foreach ($sidebarGroupItems as $sidebarNavItem): ?>
    <?php $isActive = $activeTab === $sidebarNavItem['tab']; ?>
    <a href="<?= $baseUrl . $sidebarNavItem['href'] ?>"
       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition <?= $isActive ? 'bg-blue-50 text-[#2D82FF] font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' ?>">
      <span class="material-icons text-[18px] <?= $isActive ? 'text-[#2D82FF]' : 'text-slate-400' ?>"><?= $sidebarNavItem['icon'] ?></span>
      <span><?= $sidebarNavItem['label'] ?></span>
      <?php if ($isActive): ?>
        <span class="ml-auto w-1.5 h-1.5 rounded-full bg-[#2D82FF]"></span>
      <?php endif; ?>
    </a>
  <?php endforeach; ?>
<?php endforeach; ?>

<div class="border-t border-slate-100 mt-2 pt-2">
  <a href="<?= $baseUrl ?>/auth/logout"
     class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-red-600 hover:bg-red-50 transition">
    <span class="material-icons text-[18px] text-red-500">logout</span>
    <span>Sign Out</span>
  </a>
</div>
