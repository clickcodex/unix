<?php
require_once __DIR__ . '/layouts/header.php';

$order      = $order      ?? [];
$orderItems = $orderItems ?? [];
$baseUrl    = $baseUrl    ?? (defined('BASE_URL') ? BASE_URL : '');

$payStatus  = strtolower($order['payment_status'] ?? 'paid');
$isPaid     = in_array($payStatus, ['paid', 'success', 'completed']);
$encOrderId = \App\Helpers\SecurityHelper::encryptId($order['id'] ?? 0);
$grandTotal = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
?>

<main class="max-w-4xl mx-auto px-3 sm:px-4 py-8 sm:py-12 space-y-6">

  <!-- Order Status Banner -->
  <div class="bg-white rounded-3xl border border-slate-200 shadow-lg p-6 sm:p-10 text-center space-y-4 relative overflow-hidden">
    
    <?php if ($isPaid): ?>
      <!-- PAID STATE -->
      <div class="w-16 h-16 sm:w-20 sm:h-20 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
        <span class="material-icons text-3xl sm:text-5xl">check_circle</span>
      </div>

      <div>
        <span class="bg-emerald-50 text-emerald-700 text-xs font-extrabold px-3 py-1 rounded-full border border-emerald-200 uppercase tracking-wide">
          Order Confirmed &amp; Paid
        </span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-heading mt-2">Thank You for Your Order!</h1>
        <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto mt-1">
          We have received your payment via PhonePe and are preparing order <strong class="text-slate-800 font-mono">#<?= htmlspecialchars($order['order_number'] ?? '') ?></strong> for dispatch.
        </p>
      </div>
    <?php else: ?>
      <!-- PENDING / IN PROCESS STATE -->
      <div class="w-16 h-16 sm:w-20 sm:h-20 bg-purple-100 text-[#5F259F] rounded-full flex items-center justify-center mx-auto shadow-inner">
        <span class="material-icons text-3xl sm:text-5xl">hourglass_top</span>
      </div>

      <div>
        <span class="bg-purple-50 text-[#5F259F] text-xs font-extrabold px-3 py-1 rounded-full border border-purple-200 uppercase tracking-wide">
          Order Placed — Payment In Process
        </span>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 font-heading mt-2">Order #<?= htmlspecialchars($order['order_number'] ?? '') ?> Received!</h1>
        <p class="text-xs sm:text-sm text-slate-500 max-w-md mx-auto mt-1">
          Your order has been safely created and stored in your account. You can complete your fixed PhonePe payment now or at your convenience.
        </p>
      </div>

      <!-- Complete PhonePe Payment Button -->
      <div class="pt-2">
        <a href="<?= $baseUrl ?>/payment/phonepe/initiate/<?= $encOrderId ?>" class="inline-flex items-center gap-2 bg-[#5F259F] hover:bg-[#4A1C7F] text-white font-extrabold text-sm py-3.5 px-6 rounded-2xl shadow-xl transition transform hover:-translate-y-0.5">
          <span class="w-6 h-6 rounded-lg bg-white/20 flex items-center justify-center text-xs font-black">पे</span>
          Complete PhonePe Payment (Fixed ₹<?= number_format($grandTotal, 2) ?>)
        </a>
      </div>
    <?php endif; ?>

    <!-- Quick Status Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-5 border-t border-slate-100 text-left">
      <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
        <p class="text-[10px] font-bold text-slate-400 uppercase">Payment Status</p>
        <p class="text-xs font-extrabold <?= $isPaid ? 'text-emerald-600' : 'text-purple-700' ?> capitalize mt-0.5 flex items-center gap-1">
          <span class="material-icons text-sm"><?= $isPaid ? 'verified' : 'pending' ?></span> 
          <?= $isPaid ? 'Paid' : 'Pending / In Process' ?> (<?= htmlspecialchars($order['payment_method'] ?? 'PhonePe') ?>)
        </p>
      </div>

      <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
        <p class="text-[10px] font-bold text-slate-400 uppercase">Fixed Amount</p>
        <p class="text-xs font-extrabold text-slate-900 mt-0.5">₹<?= number_format($grandTotal, 2) ?></p>
      </div>

      <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-100">
        <p class="text-[10px] font-bold text-slate-400 uppercase">Estimated Delivery</p>
        <p class="text-xs font-extrabold text-[#2D82FF] mt-0.5"><?= date('D, M d', strtotime('+3 days')) ?> - <?= date('D, M d', strtotime('+5 days')) ?></p>
      </div>
    </div>
  </div>

  <!-- Order Items & Shipping Address Details -->
  <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

    <!-- Purchased Items List (7 cols on md) -->
    <div class="md:col-span-7 bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4">
      <h3 class="font-bold text-sm text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
        <span class="material-icons text-base text-[#2D82FF]">shopping_bag</span> Order Summary (<?= count($orderItems) ?> Items)
      </h3>

      <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto pr-1">
        <?php foreach ($orderItems as $item): ?>
          <?php 
          $qty   = (int)($item['quantity'] ?? 1);
          $price = (float)($item['unit_price'] ?? 0);
          $img   = !empty($item['primary_image']) ? $item['primary_image'] : (!empty($item['image_url']) ? $item['image_url'] : 'https://via.placeholder.com/100');
          ?>
          <div class="py-3 flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center overflow-hidden shrink-0">
              <img src="<?= htmlspecialchars($img) ?>" alt="" class="max-h-full max-w-full object-contain p-1">
            </div>
            <div class="flex-1 min-w-0">
              <h4 class="text-xs font-bold text-slate-800 truncate"><?= htmlspecialchars($item['name'] ?? $item['product_name'] ?? 'Product') ?></h4>
              <p class="text-[11px] text-slate-400">Qty: <?= $qty ?> × ₹<?= number_format($price, 2) ?></p>
            </div>
            <span class="font-extrabold text-xs text-slate-900">₹<?= number_format($price * $qty, 2) ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Delivery Address Card (5 cols on md) -->
    <div class="md:col-span-5 bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4 flex flex-col justify-between">
      <div>
        <h3 class="font-bold text-sm text-slate-900 border-b border-slate-100 pb-3 flex items-center gap-2">
          <span class="material-icons text-base text-[#2D82FF]">local_shipping</span> Shipping Address
        </h3>
        <p class="text-xs text-slate-700 mt-3 leading-relaxed">
          <?= nl2br(htmlspecialchars($order['shipping_address'] ?? 'Customer Delivery Address')) ?>
        </p>
      </div>

      <div class="pt-4 border-t border-slate-100 space-y-2">
        <a href="<?= $baseUrl ?>/user/orders" class="w-full bg-[#2D82FF] hover:bg-blue-600 text-white font-bold text-xs py-3 px-4 rounded-xl flex items-center justify-center gap-2 shadow-md transition">
          <span class="material-icons text-base">receipt_long</span> View All My Orders
        </a>
        <a href="<?= $baseUrl ?>/" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-2.5 px-4 rounded-xl flex items-center justify-center gap-1.5 transition">
          <span class="material-icons text-base">storefront</span> Continue Shopping
        </a>
      </div>
    </div>

  </div>

</main>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
