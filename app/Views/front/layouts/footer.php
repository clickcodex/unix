<?php
$baseUrl     = defined('BASE_URL') ? BASE_URL : '';
$rawSiteName = \App\Models\Setting::get('site_name', 'ClickCodex');
if (strpos($rawSiteName, '—') !== false) {
    $rawSiteName = trim(explode('—', $rawSiteName)[0]);
} elseif (strpos($rawSiteName, '-') !== false) {
    $rawSiteName = trim(explode('-', $rawSiteName)[0]);
}

$nameParts = preg_split('/(?=[A-Z])|\s+/', $rawSiteName, -1, PREG_SPLIT_NO_EMPTY);
if (count($nameParts) >= 2) {
    $siteFirstName  = array_shift($nameParts);
    $siteSecondName = implode(' ', $nameParts);
} else {
    $len = strlen($rawSiteName);
    $mid = (int)ceil($len / 2);
    $siteFirstName  = substr($rawSiteName, 0, $mid);
    $siteSecondName = substr($rawSiteName, $mid);
}
?>
<!-- ================= FOOTER ================= -->
<footer class="bg-cc-dark text-white text-xs pt-10 pb-6 sm:pt-12 sm:pb-8 border-t border-slate-800 mt-10 sm:mt-16">
  <div class="max-w-7xl mx-auto px-4 grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 pb-6 sm:pb-8 border-b border-slate-800">
    <div class="col-span-2 md:col-span-1 space-y-3">
      <span class="font-heading font-extrabold text-xl sm:text-2xl text-white"><?= htmlspecialchars($siteFirstName) ?><span class="text-cc-blue"><?= htmlspecialchars($siteSecondName) ?></span></span>
      <p class="text-slate-400 text-xs leading-relaxed">India's premier online marketplace for high-performance tech, electronics, fashion, and home accessories.</p>
    </div>

    <div>
      <h4 class="font-bold text-sm text-white mb-3 uppercase tracking-wider">Quick Links</h4>
      <ul class="space-y-2 text-slate-400">
        <li><a href="<?= $baseUrl ?>/" class="hover:text-white transition">Home</a></li>
        <li><a href="<?= $baseUrl ?>/offers" class="hover:text-white transition">Offers &amp; Deals</a></li>
        <li><a href="<?= $baseUrl ?>/cart" class="hover:text-white transition">Shopping Cart</a></li>
        <li><a href="<?= $baseUrl ?>/wishlist" class="hover:text-white transition">Saved Wishlist</a></li>
        <li><a href="<?= $baseUrl ?>/about" class="hover:text-white transition">About Us</a></li>
        <li><a href="<?= $baseUrl ?>/admin/login" class="hover:text-white transition">Admin Portal</a></li>
      </ul>
    </div>

    <div>
      <h4 class="font-bold text-sm text-white mb-3 uppercase tracking-wider">Customer Support</h4>
      <ul class="space-y-2 text-slate-400">
        <li><a href="<?= $baseUrl ?>/user/orders" class="hover:text-white transition">Track Orders</a></li>
        <li><a href="<?= $baseUrl ?>/contact" class="hover:text-white transition">Contact Us</a></li>
        <li><a href="<?= $baseUrl ?>/faq" class="hover:text-white transition">FAQ &amp; Help</a></li>
        <li><a href="<?= $baseUrl ?>/terms" class="hover:text-white transition">Terms &amp; Conditions</a></li>
        <li><a href="<?= $baseUrl ?>/privacy" class="hover:text-white transition">Privacy Policy</a></li>
      </ul>
    </div>

    <div class="space-y-3">
      <h4 class="font-bold text-sm text-white uppercase tracking-wider">Payments</h4>
      <p class="text-slate-400">UPI, Visa, Mastercard, RuPay, NetBanking &amp; COD available.</p>
      <div class="flex items-center gap-2 pt-1 flex-wrap">
        <span class="bg-white/10 px-2 py-1 rounded text-[10px] font-bold">UPI</span>
        <span class="bg-white/10 px-2 py-1 rounded text-[10px] font-bold">VISA</span>
        <span class="bg-white/10 px-2 py-1 rounded text-[10px] font-bold">RuPay</span>
        <span class="bg-white/10 px-2 py-1 rounded text-[10px] font-bold">COD</span>
      </div>
    </div>
  </div>

  <div class="max-w-7xl mx-auto px-4 pt-5 flex flex-col sm:flex-row items-center justify-between text-slate-500 text-[11px] gap-2">
    <p>&copy; <?= date('Y') ?> ClickCodex Storefront. All rights reserved.</p>
    <p class="mono">Powered by ClickCodex Engine</p>
  </div>
</footer>

</body>
</html>
