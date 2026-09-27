<?php
$baseUrl   = defined('BASE_URL') ? BASE_URL : '';
$activeTab = $activeTab ?? 'dashboard';
$user      = $user ?? [];
$initials  = !empty($user['name']) ? strtoupper(mb_substr($user['name'], 0, 1)) : 'U';
$avatarUrl = !empty($user['avatar_url']) ? $user['avatar_url'] : '';
?>

<!-- Mobile Overlay Sidebar -->
<div id="mobile-sidebar-overlay" class="fixed inset-0 z-[60] hidden">
  <div onclick="toggleMobileSidebar(false)" class="absolute inset-0 bg-black/50 backdrop-blur-sm cursor-pointer"></div>
  <div class="absolute inset-y-0 left-0 max-w-[280px] w-full bg-white shadow-2xl flex flex-col h-full z-10">
    <div class="bg-gradient-to-br from-[#2D82FF] to-[#8C30F5] text-white p-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-lg font-bold overflow-hidden border border-white/30 shrink-0">
          <?php if ($avatarUrl): ?>
            <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="w-full h-full object-cover sidebar-avatar-img">
          <?php else: ?>
            <span class="sidebar-avatar-initials"><?= $initials ?></span>
          <?php endif; ?>
        </div>
        <div class="min-w-0">
          <p class="font-bold text-sm truncate"><?= htmlspecialchars($user['name'] ?? 'Customer') ?></p>
          <p class="text-white/70 text-xs truncate"><?= htmlspecialchars($user['email'] ?? '') ?></p>
        </div>
      </div>
      <button onclick="toggleMobileSidebar(false)" class="text-white hover:text-yellow-300 transition"><span class="material-icons">close</span></button>
    </div>
    <div class="flex-1 overflow-y-auto p-4 space-y-0.5">
      <?php include __DIR__ . '/sidebar_nav_items.php'; ?>
    </div>
  </div>
</div>

<!-- Desktop Sidebar -->
<aside class="hidden lg:block w-64 shrink-0 sticky top-[76px] self-start">
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
    <!-- Profile Header -->
    <div class="bg-gradient-to-br from-[#2D82FF] to-[#8C30F5] p-5 text-white">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-full bg-white/20 flex items-center justify-center text-xl font-bold shrink-0 border-2 border-white/30 overflow-hidden">
          <?php if ($avatarUrl): ?>
            <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="w-full h-full object-cover sidebar-avatar-img">
          <?php else: ?>
            <span class="sidebar-avatar-initials"><?= $initials ?></span>
          <?php endif; ?>
        </div>
        <div class="min-w-0">
          <p class="font-bold text-sm truncate"><?= htmlspecialchars($user['name'] ?? 'Customer Account') ?></p>
          <p class="text-white/70 text-xs truncate"><?= htmlspecialchars($user['email'] ?? '') ?></p>
          <span class="inline-block mt-1 bg-white/20 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wider">Verified Customer</span>
        </div>
      </div>
    </div>
    <!-- Navigation -->
    <div class="p-3 space-y-0.5">
      <?php include __DIR__ . '/sidebar_nav_items.php'; ?>
    </div>
  </div>
</aside>

<script>
function toggleMobileSidebar(open) {
  const el = document.getElementById('mobile-sidebar-overlay');
  if (open) el.classList.remove('hidden');
  else el.classList.add('hidden');
}
</script>
