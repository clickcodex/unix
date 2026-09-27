<?php
require_once __DIR__ . '/layouts/header.php';

$items   = $items   ?? [];
$user    = $user    ?? [];
$baseUrl = $baseUrl ?? (defined('BASE_URL') ? BASE_URL : '');

$FREE_SHIPPING_THRESHOLD = 999;
$totalCount              = count($items);
?>

<style>
  body { background: #F1F5F9; }
  @keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
  .fade-up { animation: fadeUp .35s ease-out both; }
  @keyframes slideInRight { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
  @keyframes slideOutRight { from{transform:translateX(0);opacity:1} to{transform:translateX(100%);opacity:0} }
  .toast-in { animation: slideInRight .35s ease-out forwards; }
  .toast-out { animation: slideOutRight .3s ease-in forwards; }
  @keyframes progressPulse { 0%,100%{opacity:1} 50%{opacity:.6} }
  .progress-pulse { animation: progressPulse 2s ease-in-out infinite; }
  @keyframes scaleIn { from{transform:scale(.95);opacity:0} to{transform:scale(1);opacity:1} }
  .scale-in { animation: scaleIn .2s ease-out both; }

  .cart-row {
    border: 1.5px solid #E2E8F0; border-radius: 16px; background: #fff;
    transition: border-color .2s ease, box-shadow .2s ease, transform .2s ease;
  }
  .cart-row:hover { border-color: #2D82FF; box-shadow: 0 4px 16px rgba(45,130,255,.06); }
  .cart-row.removing { opacity:0; transform:translateX(40px); transition: all .3s ease; max-height:0; overflow:hidden; margin:0!important; padding:0!important; border:0!important; }

  .qty-btn {
    width:34px; height:34px; border-radius:10px; border:1.5px solid #E2E8F0;
    background:#fff; display:flex; align-items:center; justify-content:center;
    font-size:18px; font-weight:700; color:#475569; cursor:pointer;
    transition:all .15s ease; user-select:none; line-height:1;
  }
  .qty-btn:hover:not(:disabled) { border-color:#2D82FF; color:#2D82FF; background:#F0F6FF; }
  .qty-btn:active:not(:disabled) { transform:scale(.92); }
  .qty-btn:disabled { opacity:.35; cursor:not-allowed; }

  .order-summary-sticky { position:sticky; top:80px; }
  @media(max-width:1023px){ .order-summary-sticky { position:relative; top:auto; } }

  .coupon-input-wrap input:focus { border-color:#8C30F5; box-shadow:0 0 0 3px rgba(140,48,245,.1); }

  .trust-icon { transition: transform .2s ease; }
  .trust-icon:hover { transform: translateY(-2px); }

  .modal-scroll::-webkit-scrollbar { width:4px; }
  .modal-scroll::-webkit-scrollbar-thumb { background:#CBD5E1; border-radius:4px; }
</style>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-5 right-5 z-[200] flex flex-col gap-2.5 pointer-events-none" style="max-width:380px"></div>

<main class="max-w-7xl mx-auto px-4 py-6 pb-24 md:pb-6">

  <!-- Breadcrumb -->
  <nav class="flex items-center gap-2 text-xs text-slate-500 mb-5">
    <a href="<?= $baseUrl ?>/" class="hover:text-[#2D82FF] transition">Home</a>
    <span class="material-icons text-[13px]">chevron_right</span>
    <a href="<?= $baseUrl ?>/user/dashboard" class="hover:text-[#2D82FF] transition">My Account</a>
    <span class="material-icons text-[13px]">chevron_right</span>
    <span class="text-slate-800 font-semibold">Shopping Cart</span>
  </nav>

  <div class="flex gap-6">

    <!-- Sidebar -->
    <?php require_once __DIR__ . '/user/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 min-w-0 space-y-4">

      <!-- Page Header -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 fade-up">
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center shrink-0">
              <span class="material-icons text-[#FF5100] text-xl">shopping_cart</span>
            </div>
            <div>
              <h1 class="font-bold text-xl sm:text-2xl text-slate-900">Shopping Cart</h1>
              <p class="text-sm text-slate-400 mt-0.5"><span id="cart-item-count"><?= $totalCount ?></span> item<?= $totalCount !== 1 ? 's' : '' ?> in cart</p>
            </div>
          </div>
          <span class="hidden sm:flex items-center gap-1.5 text-xs text-slate-400 font-medium">
            <span class="material-icons text-slate-400 text-sm">lock</span> Secure Checkout
          </span>
        </div>
      </div>

      <!-- Free Shipping Progress Bar -->
      <div id="shipping-progress-bar" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 fade-up" style="animation-delay:.06s">
        <div class="flex items-center gap-3 mb-2.5">
          <span class="material-icons text-green-500 text-xl">local_shipping</span>
          <div class="flex-1">
            <p id="shipping-progress-text" class="text-sm font-semibold text-slate-700"></p>
            <p id="shipping-progress-sub" class="text-xs text-slate-400 mt-0.5"></p>
          </div>
          <span id="shipping-progress-badge" class="text-xs font-bold px-3 py-1 rounded-full"></span>
        </div>
        <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden">
          <div id="shipping-progress-fill" class="h-full rounded-full transition-all duration-700 ease-out progress-pulse" style="width:0%"></div>
        </div>
      </div>

      <!-- Delivery Pincode Checker -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col sm:flex-row sm:items-center gap-3 fade-up" style="animation-delay:.08s">
        <div class="flex items-center gap-2 text-sm text-slate-600 shrink-0">
          <span class="material-icons text-[#2D82FF] text-xl">location_on</span>
          <span class="font-semibold">Deliver to:</span>
        </div>
        <div class="flex-1 flex gap-2">
          <input type="text" id="pincode-input" value="400001" maxlength="6" placeholder="Enter 6-digit pincode"
                 class="flex-1 border border-slate-200 px-4 py-2 rounded-xl text-sm transition outline-none focus:border-[#2D82FF]">
          <button onclick="checkPincode()" class="bg-[#2D82FF] hover:bg-blue-600 text-white px-5 py-2 rounded-xl text-xs font-semibold transition shrink-0">Check</button>
        </div>
        <div id="pincode-result" class="hidden sm:flex items-center gap-1.5 text-xs font-semibold"></div>
      </div>

      <!-- Two Column: Items Left + Summary Right -->
      <div class="flex flex-col lg:flex-row gap-5 fade-up" style="animation-delay:.1s">

        <!-- LEFT: Cart Items -->
        <div class="flex-1 min-w-0 space-y-3">

          <!-- Cart Items Container -->
          <div id="cart-items-container" class="space-y-3">
            <?php foreach ($items as $idx => $item): ?>
              <?php
              $disc          = (int)($item['discount_pct'] ?? 0);
              $img           = !empty($item['image_url']) ? $item['image_url'] : '';
              $unitPrice     = (float)($item['unit_price'] ?? 0);
              $quantity      = (int)($item['quantity'] ?? 1);
              $lineTotal     = $unitPrice * $quantity;
              $itemName      = $item['name'] ?? '';
              $isInStock     = !empty($item['is_in_stock']);
              $cartItemId    = (int)($item['cart_item_id'] ?? 0);
              $productId     = (int)($item['product_id'] ?? 0);
              $encCartItemId = $item['encrypted_cart_item_id'] ?? $cartItemId;
              $encProductId  = $item['encrypted_product_id'] ?? $productId;
              ?>
              <div class="cart-row p-4 fade-up" id="row-<?= $encCartItemId ?>" style="animation-delay:<?= .1 + $idx * .04 ?>s" data-base-price="<?= (float)($item['base_price'] ?? $unitPrice) ?>">
                <div class="flex gap-4">
                  <!-- Checkbox + Image -->
                  <div class="flex items-start gap-3 shrink-0">
                    <input type="checkbox" class="item-checkbox w-4 h-4 mt-1 rounded accent-[#2D82FF] cursor-pointer"
                           data-item-id="<?= $encCartItemId ?>"
                           data-price="<?= $unitPrice ?>"
                           data-qty="<?= $quantity ?>"
                           checked onchange="recalcTotals()">
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center overflow-hidden shrink-0">
                      <?php if ($img): ?>
                        <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($itemName) ?>"
                             class="max-h-full max-w-full object-contain p-2" loading="lazy">
                      <?php else: ?>
                        <span class="material-icons text-3xl text-slate-300">inventory_2</span>
                      <?php endif; ?>
                    </div>
                  </div>

                  <!-- Item Details -->
                  <div class="flex-1 min-w-0 flex flex-col sm:flex-row sm:items-start gap-3">
                    <div class="flex-1 min-w-0">
                      <a href="<?= $baseUrl ?>/product/<?= $encProductId ?>" class="text-sm font-semibold text-slate-800 line-clamp-2 hover:text-[#2D82FF] transition block"><?= htmlspecialchars($itemName) ?></a>
                      <?php if (!$isInStock): ?>
                        <span class="inline-flex items-center gap-1 mt-1 text-[10px] font-bold text-red-600 bg-red-50 px-2 py-0.5 rounded">
                          <span class="material-icons text-[10px]">cancel</span> Out of Stock
                        </span>
                      <?php endif; ?>
                      <div class="flex items-baseline gap-2 mt-1.5">
                        <span class="font-extrabold text-sm text-slate-900">₹<?= number_format($unitPrice, 2) ?></span>
                        <?php if ($disc > 0): ?>
                          <span class="text-xs line-through text-slate-400">₹<?= number_format((float)($item['base_price'] ?? 0), 2) ?></span>
                          <span class="text-[10px] font-bold text-green-600 bg-green-50 px-1.5 py-0.5 rounded"><?= $disc ?>% off</span>
                        <?php endif; ?>
                      </div>

                      <!-- Qty controls -->
                      <div class="flex items-center gap-2 mt-3">
                        <button class="qty-btn" onclick="changeQty('<?= $encCartItemId ?>', -1)"
                                id="dec-<?= $encCartItemId ?>" <?= $quantity <= 1 ? 'disabled' : '' ?>>−</button>
                        <span class="w-8 text-center font-bold text-sm" id="qty-<?= $encCartItemId ?>"><?= $quantity ?></span>
                        <button class="qty-btn" onclick="changeQty('<?= $encCartItemId ?>', 1)"
                                id="inc-<?= $encCartItemId ?>" <?= $quantity >= 10 ? 'disabled' : '' ?>>+</button>
                      </div>
                    </div>

                    <!-- Line total + Actions -->
                    <div class="flex sm:flex-col items-center sm:items-end justify-between gap-2 shrink-0">
                      <p class="font-extrabold text-base text-[#FF5100]" id="ltotal-<?= $encCartItemId ?>">
                        ₹<?= number_format($lineTotal, 2) ?>
                      </p>
                      <div class="flex items-center gap-2">
                        <button onclick="saveForLater('<?= $encCartItemId ?>', '<?= $encProductId ?>')"
                                class="text-xs text-slate-500 hover:text-purple-600 transition flex items-center gap-1 px-2.5 py-1.5 border border-slate-200 rounded-lg hover:border-purple-300 hover:bg-purple-50"
                                title="Move item to wishlist">
                          <span class="material-icons text-sm">bookmark_border</span>
                          <span class="hidden sm:inline">Save Later</span>
                        </button>
                        <button onclick="removeItem('<?= $encCartItemId ?>')"
                                class="text-xs text-slate-400 hover:text-red-500 transition flex items-center gap-1 px-2.5 py-1.5 border border-slate-200 rounded-lg hover:border-red-200 hover:bg-red-50"
                                title="Remove item from cart">
                          <span class="material-icons text-sm">delete_outline</span>
                          <span class="hidden sm:inline">Remove</span>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <!-- Empty State -->
          <div id="empty-state" class="<?= !empty($items) ? 'hidden' : '' ?> bg-white rounded-2xl border border-slate-200 shadow-sm p-10 sm:p-16 text-center">
            <div class="w-24 h-24 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-5">
              <span class="material-icons text-5xl text-slate-300">shopping_cart</span>
            </div>
            <h3 class="font-bold text-xl text-slate-800 mb-2">Your cart is empty</h3>
            <p class="text-sm text-slate-500 max-w-sm mx-auto mb-6 leading-relaxed">Add items you love to your cart. Start by exploring our catalog.</p>
            <div class="flex flex-wrap gap-3 justify-center">
              <a href="<?= $baseUrl ?>/" class="bg-[#2D82FF] hover:bg-blue-600 text-white font-bold px-7 py-3 rounded-xl transition shadow-md inline-flex items-center gap-2 text-sm">
                <span class="material-icons text-[18px]">explore</span> Start Shopping
              </a>
              <a href="<?= $baseUrl ?>/wishlist" class="border-2 border-[#FF006B] text-[#FF006B] hover:bg-[#FF006B] hover:text-white font-semibold px-7 py-3 rounded-xl transition inline-flex items-center gap-2 text-sm">
                <span class="material-icons text-[18px]">favorite</span> View Wishlist
              </a>
            </div>
          </div>

          <!-- Clear All Cart Link -->
          <div id="clear-cart-wrap" class="<?= empty($items) ? 'hidden' : '' ?> flex justify-end">
            <button onclick="clearCart()" class="text-xs text-slate-400 hover:text-red-500 transition flex items-center gap-1.5 font-medium">
              <span class="material-icons text-sm">delete_sweep</span> Clear all items
            </button>
          </div>

        </div><!-- /Left -->

        <!-- RIGHT: Order Summary (sticky) -->
        <div id="summary-section" class="lg:w-[340px] shrink-0 <?= empty($items) ? 'hidden' : '' ?>">
          <div class="order-summary-sticky space-y-4">

            <!-- Summary Card -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
              <div class="bg-gradient-to-r from-[#0F172A] to-slate-700 text-white px-5 py-4 flex items-center gap-2.5">
                <span class="material-icons text-[#FFB800] text-lg">receipt_long</span>
                <h3 class="font-bold text-sm">Order Summary</h3>
              </div>

              <div class="p-5 space-y-4">
                <!-- Price Breakdown -->
                <div class="space-y-2.5 text-sm">
                  <div class="flex justify-between">
                    <span class="text-slate-500">Price (<span id="summary-qty">0</span> items)</span>
                    <span id="summary-subtotal" class="font-semibold text-slate-800">₹0</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-slate-500">Discount</span>
                    <span id="summary-discount" class="font-semibold text-green-600">−₹0</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-slate-500">Delivery</span>
                    <span id="summary-delivery" class="font-semibold text-green-600">FREE</span>
                  </div>
                  <div class="flex justify-between" id="summary-coupon-row" style="display:none">
                    <span class="text-slate-500 flex items-center gap-1">
                      <span class="material-icons text-[13px] text-purple-600">local_offer</span>
                      Coupon (<span id="summary-coupon-code"></span>)
                    </span>
                    <span id="summary-coupon-discount" class="font-semibold text-green-600">−₹0</span>
                  </div>
                </div>

                <!-- Total -->
                <div class="border-t-2 border-dashed border-slate-200 pt-3 flex justify-between items-baseline">
                  <span class="font-bold text-base text-slate-900">Total Amount</span>
                  <span id="summary-total" class="font-extrabold text-2xl text-[#FF5100]">₹0</span>
                </div>

                <!-- Savings Banner -->
                <div id="savings-banner" class="hidden bg-green-50 border border-green-200 rounded-xl p-3 flex items-center gap-2.5">
                  <span class="material-icons text-green-600 text-xl">savings</span>
                  <div>
                    <p class="text-xs font-bold text-green-800">You save <span id="total-savings">₹0</span> on this order!</p>
                    <p class="text-[10px] text-green-600 mt-0.5">Inclusive of all discounts</p>
                  </div>
                </div>

                <!-- Coupon Input -->
                <div class="space-y-1.5">
                  <div class="coupon-input-wrap border border-slate-200 rounded-xl overflow-hidden">
                    <div class="flex">
                      <div class="flex-1 flex items-center gap-2 px-3">
                        <span class="material-icons text-slate-400 text-[18px]">local_offer</span>
                        <input type="text" id="coupon-input" placeholder="Enter coupon code"
                               class="flex-1 py-3 text-sm text-slate-700 bg-transparent border-none outline-none" maxlength="20">
                      </div>
                      <button onclick="applyCoupon()" class="bg-[#8C30F5] hover:bg-purple-700 text-white px-5 py-3 text-sm font-semibold transition whitespace-nowrap">Apply</button>
                    </div>
                  </div>
                  <div class="flex justify-between items-center text-xs px-1">
                    <span class="text-slate-400">Have a promo code?</span>
                    <button onclick="openCouponModal()" class="text-[#8C30F5] font-semibold hover:underline">View Coupons</button>
                  </div>
                </div>

                <!-- Applied Coupon Tag -->
                <div id="applied-coupon-tag" class="hidden bg-purple-50 border border-purple-200 rounded-xl p-3 flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span class="material-icons text-purple-600 text-lg">verified</span>
                    <div>
                      <p class="text-xs font-bold text-purple-700" id="applied-coupon-label"></p>
                      <p class="text-[10px] text-slate-500" id="applied-coupon-desc"></p>
                    </div>
                  </div>
                  <button onclick="removeCoupon()" class="text-slate-400 hover:text-red-500 transition" title="Remove coupon">
                    <span class="material-icons text-[18px]">close</span>
                  </button>
                </div>

                <!-- Checkout Button -->
                <button onclick="proceedToCheckout()"
                        class="w-full bg-[#FF5100] hover:bg-orange-600 text-white font-bold py-4 rounded-xl transition shadow-lg text-base flex items-center justify-center gap-2">
                  <span class="material-icons text-xl">lock</span> Proceed to Checkout
                </button>

                <!-- Trust Badges -->
                <div class="grid grid-cols-3 gap-2 pt-1">
                  <?php foreach ([['shield', 'Safe Payment'], ['autorenew', 'Easy Returns'], ['verified_user', '100% Genuine']] as $b): ?>
                    <div class="trust-icon text-center cursor-default">
                      <span class="material-icons text-slate-400 text-[20px]"><?= $b[0] ?></span>
                      <p class="text-[9px] text-slate-400 mt-0.5 font-medium"><?= $b[1] ?></p>
                    </div>
                  <?php endforeach; ?>
                </div>

                <!-- Pay Methods -->
                <div class="flex items-center justify-center gap-4 pt-2 border-t border-slate-100 flex-wrap">
                  <?php foreach ([['credit_card','Cards'], ['account_balance','UPI'], ['payments','Net Banking'], ['currency_rupee','COD']] as $p): ?>
                    <div class="flex items-center gap-1 text-[10px] text-slate-400">
                      <span class="material-icons text-[14px]"><?= $p[0] ?></span><?= $p[1] ?>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div><!-- /Two column -->

    </div><!-- /Main Content -->
  </div><!-- /Flex Layout -->
</main>

<!-- Mobile Bottom Nav -->
<div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-50 flex justify-around py-2 shadow-2xl">
  <a href="<?= $baseUrl ?>/" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">home</span><span class="text-[9px] font-semibold">Home</span>
  </a>
  <a href="<?= $baseUrl ?>/user/orders" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">shopping_bag</span><span class="text-[9px] font-semibold">Orders</span>
  </a>
  <a href="<?= $baseUrl ?>/wishlist" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">favorite_border</span><span class="text-[9px] font-semibold">Wishlist</span>
  </a>
  <a href="<?= $baseUrl ?>/cart" class="flex flex-col items-center text-[#FF5100] py-1">
    <span class="material-icons text-2xl">shopping_cart</span><span class="text-[9px] font-semibold">Cart</span>
  </a>
  <button onclick="toggleMobileSidebar(true)" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">person</span><span class="text-[9px] font-semibold">Account</span>
  </button>
</div>

<!-- Mobile Sticky Checkout Bar -->
<div id="mobile-checkout-bar" class="md:hidden fixed bottom-[56px] left-0 right-0 bg-white border-t border-slate-200 z-40 px-4 py-2.5 flex items-center justify-between shadow-lg <?= empty($items) ? 'hidden' : '' ?>">
  <div>
    <p class="text-[9px] text-slate-400 font-bold uppercase">Total</p>
    <p id="mobile-total" class="font-extrabold text-xl text-[#FF5100]">₹0</p>
  </div>
  <button onclick="proceedToCheckout()" class="bg-[#FF5100] hover:bg-orange-600 text-white font-bold px-6 py-2.5 rounded-xl transition text-sm flex items-center gap-1.5 shadow-md">
    <span class="material-icons text-[18px]">lock</span> Checkout
  </button>
</div>

<!-- Confirm Remove Modal -->
<div id="confirm-modal" class="fixed inset-0 z-[80] items-center justify-center overflow-hidden hidden" style="display:none!important">
  <div onclick="closeConfirm()" class="absolute inset-0 bg-black/50 cursor-pointer" style="backdrop-filter:blur(4px)"></div>
  <div class="bg-white rounded-2xl max-w-sm w-[90%] shadow-2xl relative z-10 p-6 scale-in">
    <button onclick="closeConfirm()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 transition"><span class="material-icons">close</span></button>
    <div class="text-center">
      <div class="w-14 h-14 mx-auto rounded-full bg-red-50 flex items-center justify-center mb-4">
        <span class="material-icons text-red-500 text-2xl">delete_outline</span>
      </div>
      <h3 class="font-bold text-lg text-slate-800 mb-1">Remove Item?</h3>
      <p class="text-sm text-slate-500 mb-5">This item will be removed from your cart.</p>
      <div class="flex gap-3">
        <button onclick="closeConfirm()" class="flex-1 border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold py-2.5 rounded-xl transition text-sm">Cancel</button>
        <button id="confirm-remove-btn" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-2.5 rounded-xl transition text-sm">Remove</button>
      </div>
    </div>
  </div>
</div>

<!-- Coupon List Modal -->
<div id="coupon-modal" class="fixed inset-0 z-[80] items-center justify-center overflow-hidden hidden" style="display:none!important">
  <div onclick="closeCouponModal()" class="absolute inset-0 bg-black/50 cursor-pointer" style="backdrop-filter:blur(4px)"></div>
  <div class="bg-white rounded-2xl max-w-md w-[90%] shadow-2xl relative z-10 p-6 scale-in">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
      <h3 class="font-bold text-base text-slate-900 flex items-center gap-2">
        <span class="material-icons text-purple-600">local_offer</span> Available Coupons
      </h3>
      <button onclick="closeCouponModal()" class="text-slate-400 hover:text-slate-700 transition"><span class="material-icons">close</span></button>
    </div>
    <div class="space-y-3 max-h-[60vh] overflow-y-auto modal-scroll pr-1">
      <?php
      $couponsList = [
        ['code'=>'WELCOME100', 'desc'=>'₹100 flat discount on your first order', 'min'=>499],
        ['code'=>'SAVE20',     'desc'=>'20% instant discount up to ₹500',         'min'=>1000],
        ['code'=>'FLAT500',    'desc'=>'₹500 flat discount on orders above ₹2000', 'min'=>2000],
      ];
      foreach ($couponsList as $cp): ?>
        <div class="border border-purple-100 bg-purple-50/50 rounded-xl p-3.5 flex items-center justify-between gap-3">
          <div>
            <span class="font-extrabold text-sm text-purple-700 bg-purple-100 px-2.5 py-0.5 rounded tracking-wider"><?= $cp['code'] ?></span>
            <p class="text-xs text-slate-600 mt-1"><?= $cp['desc'] ?></p>
            <p class="text-[10px] text-slate-400">Min order: ₹<?= $cp['min'] ?></p>
          </div>
          <button onclick="quickApplyCoupon('<?= $cp['code'] ?>')" class="bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs px-4 py-2 rounded-lg transition shrink-0">
            Apply
          </button>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';
const FREE_SHIP = <?= $FREE_SHIPPING_THRESHOLD ?>;
const COUPONS = [
  { code:'WELCOME100', desc:'₹100 off on first order', type:'flat',  value:100, min:499  },
  { code:'SAVE20',     desc:'20% off up to ₹500',      type:'pct',   value:20,  min:1000, max:500 },
  { code:'FLAT500',    desc:'Flat ₹500 off above ₹2000', type:'flat', value:500, min:2000 },
];
let appliedCoupon = null;
let pendingRemoveId = null;

// ============================================================
// HELPERS
// ============================================================
function fp(n){ return '₹' + Math.round(n).toLocaleString('en-IN'); }

function showToast(msg, type='success'){
  const c=document.getElementById('toast-container');
  if(!c) return;
  const cls={success:'bg-emerald-600',error:'bg-red-500',info:'bg-[#2D82FF]',warning:'bg-amber-500'};
  const ico={success:'check_circle',error:'error',info:'info',warning:'warning'};
  const t=document.createElement('div');
  t.className=`toast-in pointer-events-auto flex items-center gap-3 ${cls[type]||cls.info} text-white px-5 py-3.5 rounded-xl shadow-2xl text-sm font-medium`;
  t.innerHTML=`<span class="material-icons text-lg">${ico[type]||'info'}</span><span class="flex-1">${msg}</span>`;
  c.appendChild(t);
  setTimeout(()=>{t.classList.replace('toast-in','toast-out');setTimeout(()=>t.remove(),300)},3500);
}

function updateHeaderBadge(count){
  const badge = document.getElementById('header-cart-badge');
  if (badge) badge.innerText = count || 0;
  document.querySelectorAll('.cart-badge-count').forEach(el => el.textContent = count || 0);
}

// ============================================================
// FREE SHIPPING PROGRESS
// ============================================================
function renderShippingProgress(){
  const subtotal = getSubtotal();
  const pct = Math.min(100, (subtotal / FREE_SHIP) * 100);
  const remaining = FREE_SHIP - subtotal;
  const fill = document.getElementById('shipping-progress-fill');
  const txt  = document.getElementById('shipping-progress-text');
  const sub  = document.getElementById('shipping-progress-sub');
  const badge= document.getElementById('shipping-progress-badge');
  if(!fill) return;
  fill.style.width = pct + '%';
  if(subtotal >= FREE_SHIP){
    fill.style.background = 'linear-gradient(90deg,#10B981,#059669)';
    fill.classList.remove('progress-pulse');
    if(txt) txt.textContent = '🎉 You\'ve unlocked FREE delivery!';
    if(sub) sub.textContent = 'Your entire order ships for free.';
    if(badge){ badge.textContent = 'FREE'; badge.className = 'text-xs font-bold px-3 py-1 rounded-full bg-green-100 text-green-700'; }
  } else {
    fill.style.background = 'linear-gradient(90deg,#3B82F6,#2D82FF)';
    fill.classList.add('progress-pulse');
    if(txt) txt.textContent = `Add ${fp(remaining)} more for FREE delivery`;
    if(sub) sub.textContent = `You're ${Math.round(pct)}% of the way there`;
    if(badge){ badge.textContent = fp(remaining)+' away'; badge.className = 'text-xs font-bold px-3 py-1 rounded-full bg-blue-50 text-[#2D82FF]'; }
  }
}

// ============================================================
// TOTALS & CHECKBOXES
// ============================================================
function getCheckedItems(){
  return [...document.querySelectorAll('.item-checkbox:checked')];
}

function getSubtotal(){
  return getCheckedItems().reduce((s,cb) => s + parseFloat(cb.dataset.price||0) * parseInt(cb.dataset.qty||1), 0);
}

function getMRP(){
  return getCheckedItems().reduce((s,cb) => {
    const row = document.getElementById('row-'+cb.dataset.itemId);
    const base = row ? parseFloat(row.dataset.basePrice||cb.dataset.price) : parseFloat(cb.dataset.price);
    return s + base * parseInt(cb.dataset.qty||1);
  }, 0);
}

function recalcTotals(){
  const subtotal = getSubtotal();
  const mrp      = getMRP();
  const disc     = Math.max(0, mrp - subtotal);
  const delivery = subtotal >= FREE_SHIP ? 0 : (subtotal > 0 ? 49 : 0);
  const qty      = getCheckedItems().reduce((s,cb) => s + parseInt(cb.dataset.qty||1), 0);

  let couponDisc = 0;
  if(appliedCoupon && subtotal >= appliedCoupon.min){
    couponDisc = appliedCoupon.type==='flat'
      ? Math.min(appliedCoupon.value, subtotal)
      : Math.min(appliedCoupon.max||9999, subtotal * appliedCoupon.value / 100);
  }

  const total = Math.max(0, subtotal - couponDisc + delivery);
  const saved = disc + couponDisc;

  const els = {
    'summary-qty':       qty,
    'summary-subtotal':  fp(mrp),
    'summary-discount':  '−'+fp(disc),
    'summary-delivery':  delivery===0?'FREE':fp(delivery),
    'summary-total':     fp(total),
    'cart-item-count':   document.querySelectorAll('.item-checkbox').length,
    'mobile-total':      fp(total),
  };
  Object.entries(els).forEach(([id,val]) => {
    const el = document.getElementById(id);
    if(el) el.textContent = val;
  });

  const sb = document.getElementById('savings-banner');
  if(sb){
    if (saved > 0) sb.classList.remove('hidden');
    else sb.classList.add('hidden');
    const ts = document.getElementById('total-savings');
    if(ts) ts.textContent = fp(saved);
  }

  const cRow = document.getElementById('summary-coupon-row');
  if(cRow) cRow.style.display = appliedCoupon ? '' : 'none';
  const cDisc = document.getElementById('summary-coupon-discount');
  if(cDisc) cDisc.textContent = '−'+fp(couponDisc);

  renderShippingProgress();
}

// ============================================================
// QTY CHANGE (+ / -)
// ============================================================
function changeQty(itemId, delta){
  const qtyEl   = document.getElementById('qty-'+itemId);
  const cb       = document.querySelector('[data-item-id="'+itemId+'"]');
  const decBtn   = document.getElementById('dec-'+itemId);
  const incBtn   = document.getElementById('inc-'+itemId);
  if(!qtyEl||!cb) return;

  let qty = parseInt(qtyEl.textContent) + delta;
  qty = Math.max(1, Math.min(10, qty));

  qtyEl.textContent = qty;
  cb.dataset.qty    = qty;
  if(decBtn) decBtn.disabled = qty <= 1;
  if(incBtn) incBtn.disabled = qty >= 10;

  // Update line total display
  const price = parseFloat(cb.dataset.price||0);
  const lt = document.getElementById('ltotal-'+itemId);
  if(lt) lt.textContent = fp(price * qty);

  recalcTotals();

  fetch(`${BASE_URL}/cart/update-qty`, {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({item_id: itemId, qty: qty})
  })
  .then(r => r.json())
  .then(d => {
    if(!d.success) showToast('Could not update quantity', 'error');
    else updateHeaderBadge(d.cart_count);
  })
  .catch(() => showToast('Network connection issue', 'warning'));
}

// ============================================================
// REMOVE ITEM & MODAL
// ============================================================
function removeItem(itemId){
  pendingRemoveId = itemId;
  const modal = document.getElementById('confirm-modal');
  document.getElementById('confirm-remove-btn').onclick = executeRemove;
  modal.style.display = 'flex';
  modal.classList.remove('hidden');
}

function closeConfirm(){
  const modal = document.getElementById('confirm-modal');
  modal.style.display = 'none';
  modal.classList.add('hidden');
}

function executeRemove(){
  closeConfirm();
  const row = document.getElementById('row-'+pendingRemoveId);
  if(row){
    row.classList.add('removing');
    setTimeout(() => row.remove(), 320);
  }

  fetch(`${BASE_URL}/cart/remove-item`, {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({item_id: pendingRemoveId})
  })
  .then(r => r.json())
  .then(d => {
    recalcTotals();
    updateHeaderBadge(d.cart_count);
    const remainingRows = document.querySelectorAll('.item-checkbox');
    if(remainingRows.length === 0){
      document.getElementById('empty-state')?.classList.remove('hidden');
      document.getElementById('summary-section')?.classList.add('hidden');
      document.getElementById('clear-cart-wrap')?.classList.add('hidden');
      document.getElementById('mobile-checkout-bar')?.classList.add('hidden');
    } else {
      showToast('Item removed from cart.', 'info');
    }
  })
  .catch(() => showToast('Network connection issue', 'warning'));
}

// ============================================================
// SAVE FOR LATER (MOVE TO WISHLIST)
// ============================================================
function saveForLater(cartItemId, productId){
  // Add to wishlist
  fetch(`${BASE_URL}/wishlist/add`, {
    method:'POST',
    headers:{'Content-Type':'application/json'},
    body:JSON.stringify({product_id: productId})
  })
  .then(r => r.json())
  .then(d => {
    // Remove from cart
    fetch(`${BASE_URL}/cart/remove-item`, {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({item_id: cartItemId})
    })
    .then(r2 => r2.json())
    .then(d2 => {
      const row = document.getElementById('row-'+cartItemId);
      if(row){ row.classList.add('removing'); setTimeout(()=>row.remove(), 320); }
      recalcTotals();
      updateHeaderBadge(d2.cart_count);
      showToast('Moved to your wishlist!', 'success');
      const remainingRows = document.querySelectorAll('.item-checkbox');
      if(remainingRows.length === 0){
        document.getElementById('empty-state')?.classList.remove('hidden');
        document.getElementById('summary-section')?.classList.add('hidden');
        document.getElementById('clear-cart-wrap')?.classList.add('hidden');
        document.getElementById('mobile-checkout-bar')?.classList.add('hidden');
      }
    });
  })
  .catch(() => showToast('Could not save item.', 'error'));
}

// ============================================================
// CLEAR ALL CART
// ============================================================
function clearCart(){
  if(!confirm('Are you sure you want to remove all items from your cart?')) return;
  const checkboxes = document.querySelectorAll('.item-checkbox');
  let done = 0;
  checkboxes.forEach(cb => {
    const id = cb.dataset.itemId;
    fetch(`${BASE_URL}/cart/remove-item`, {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify({item_id: id})
    })
    .then(() => {
      done++;
      if (done === checkboxes.length) {
        location.reload();
      }
    });
  });
}

// ============================================================
// PINCODE CHECKER
// ============================================================
function checkPincode(){
  const pin = document.getElementById('pincode-input').value.trim();
  const res = document.getElementById('pincode-result');
  if(!/^\d{6}$/.test(pin)){
    res.className = 'flex items-center gap-1.5 text-xs font-semibold text-red-500';
    res.innerHTML = '<span class="material-icons text-sm">error</span> Enter valid 6-digit pincode';
    return;
  }
  const estDate = new Date();
  estDate.setDate(estDate.getDate() + 3);
  const dateStr = estDate.toLocaleDateString('en-IN', {weekday:'short', day:'numeric', month:'short'});
  res.className = 'flex items-center gap-1.5 text-xs font-semibold text-green-600';
  res.innerHTML = `<span class="material-icons text-sm">check_circle</span> Delivery available by <strong>${dateStr}</strong>`;
  showToast(`Pincode ${pin} is eligible for fast delivery!`, 'success');
}

// ============================================================
// COUPON MODALS & ACTIONS
// ============================================================
function openCouponModal(){
  const modal = document.getElementById('coupon-modal');
  modal.style.display = 'flex';
  modal.classList.remove('hidden');
}
function closeCouponModal(){
  const modal = document.getElementById('coupon-modal');
  modal.style.display = 'none';
  modal.classList.add('hidden');
}
function quickApplyCoupon(code){
  closeCouponModal();
  document.getElementById('coupon-input').value = code;
  applyCoupon();
}

function applyCoupon(){
  const code = (document.getElementById('coupon-input')?.value || '').trim().toUpperCase();
  if(!code){ showToast('Please enter a coupon code.', 'warning'); return; }
  const c = COUPONS.find(x => x.code === code);
  if(!c){ showToast('Invalid or expired coupon code.', 'error'); return; }
  const sub = getSubtotal();
  if(sub < c.min){ showToast(`Minimum order of ${fp(c.min)} required for code ${c.code}.`, 'warning'); return; }

  appliedCoupon = c;
  document.getElementById('applied-coupon-tag')?.classList.remove('hidden');
  document.getElementById('applied-coupon-label').textContent = c.code;
  document.getElementById('applied-coupon-desc').textContent  = c.desc;
  document.getElementById('summary-coupon-code').textContent  = c.code;
  const ci = document.getElementById('coupon-input'); if(ci) ci.value = '';
  recalcTotals();
  showToast(`Coupon "${c.code}" applied successfully!`, 'success');
}

function removeCoupon(){
  appliedCoupon = null;
  document.getElementById('applied-coupon-tag')?.classList.add('hidden');
  recalcTotals();
  showToast('Coupon removed.', 'info');
}

// ============================================================
// PROCEED TO CHECKOUT
// ============================================================
function proceedToCheckout(){
  const checked = getCheckedItems();
  if(checked.length === 0){
    showToast('Please select at least one item to checkout.', 'warning');
    return;
  }
  showToast('Proceeding to checkout…', 'info');
  setTimeout(() => {
    window.location.href = BASE_URL + '/checkout';
  }, 400);
}

// ============================================================
// INIT & KEYBOARD LISTENERS
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  recalcTotals();
});

document.addEventListener('keydown', e => {
  if(e.key === 'Escape'){
    closeConfirm();
    closeCouponModal();
  }
});
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
