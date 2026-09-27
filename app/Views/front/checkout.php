<?php
require_once __DIR__ . '/layouts/header.php';

$cartItems = $cartItems ?? [];
$addresses = $addresses ?? [];
$user      = $user      ?? [];
$subtotal  = $subtotal  ?? 0;
$discount  = $discount  ?? 0;
$shipping  = $shipping  ?? 0;
$total     = $total     ?? 0;
$baseUrl   = $baseUrl   ?? (defined('BASE_URL') ? BASE_URL : '');

$defaultAddressId = '';
foreach ($addresses as $a) {
  if (intval($a['is_default'] ?? 0) === 1) {
    $defaultAddressId = $a['encrypted_id'] ?? $a['id'];
    break;
  }
}
if (empty($defaultAddressId) && !empty($addresses)) {
  $defaultAddressId = $addresses[0]['encrypted_id'] ?? $addresses[0]['id'];
}
?>

<style>
  body { background: #F1F5F9; }
  @keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
  .fade-up { animation: fadeUp .35s ease-out both; }
  @keyframes scaleIn { from{opacity:0;transform:scale(.97)} to{opacity:1;transform:scale(1)} }
  .scale-in { animation: scaleIn .2s ease-out both; }

  .addr-radio-card {
    border: 1.5px solid #E2E8F0; border-radius: 16px; background: #fff; p: 4;
    transition: all .2s ease; cursor: pointer; position: relative;
  }
  .addr-radio-card:hover { border-color: #2D82FF; }
  .addr-radio-card.selected { border-color: #2D82FF; background: #F0F6FF; }

  .pay-option-card {
    border: 1.5px solid #E2E8F0; border-radius: 14px; background: #fff; padding: 14px 18px;
    transition: all .15s ease; cursor: pointer; display: flex; align-items: center; justify-content: space-between;
  }
  .pay-option-card:hover, .pay-option-card.selected { border-color: #2D82FF; background: #F0F6FF; }

  .btn-place-order {
    background: linear-gradient(135deg, #2D82FF, #1D6FE0); color: #fff; font-weight: 800; font-size: 15px;
    padding: 14px 28px; border-radius: 14px; transition: all .2s ease;
    display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: none; cursor: pointer; width: 100%;
  }
  .btn-place-order:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(45,130,255,.35); }

  .toast-in { animation: slideInRight .35s ease-out forwards; }
  @keyframes slideInRight { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
</style>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-5 right-5 z-[200] flex flex-col gap-2.5 pointer-events-none" style="max-width:380px"></div>

<main class="max-w-7xl mx-auto px-3 sm:px-4 py-4 sm:py-6 pb-20 md:pb-6 space-y-4 sm:space-y-6">

  <!-- Breadcrumb -->
  <nav class="flex items-center gap-2 text-xs text-slate-500">
    <a href="<?= $baseUrl ?>/" class="hover:text-[#2D82FF] transition">Home</a>
    <span class="material-icons text-[13px]">chevron_right</span>
    <a href="<?= $baseUrl ?>/cart" class="hover:text-[#2D82FF] transition">Shopping Cart</a>
    <span class="material-icons text-[13px]">chevron_right</span>
    <span class="text-slate-800 font-semibold">Checkout</span>
  </nav>

  <!-- Checkout Header Banner -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 sm:p-6 fade-up">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-blue-50 flex items-center justify-center shrink-0">
          <span class="material-icons text-[#2D82FF] text-xl sm:text-2xl">verified_user</span>
        </div>
        <div>
          <h1 class="text-lg sm:text-xl md:text-2xl font-extrabold text-slate-900 font-heading">Secure Checkout</h1>
          <p class="text-[11px] sm:text-xs text-slate-400 mt-0.5">Review delivery, items &amp; payment to confirm</p>
        </div>
      </div>
      <span class="bg-emerald-50 text-emerald-700 text-xs font-bold px-3 py-1.5 rounded-full border border-emerald-200 self-start sm:self-auto flex items-center gap-1">
        <span class="material-icons text-sm">lock</span> 256-Bit SSL Encrypted
      </span>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

    <!-- Left Column: Address, Items & Payment (8 cols on lg) -->
    <div class="lg:col-span-8 space-y-6">
      
      <!-- Step 1: Delivery Address -->
      <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4 fade-up" style="animation-delay:.06s">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h2 class="font-bold text-base text-slate-900 flex items-center gap-2">
            <span class="w-7 h-7 rounded-lg bg-[#2D82FF] text-white flex items-center justify-center text-xs font-extrabold">1</span>
            Select Delivery Address
          </h2>
          <a href="<?= $baseUrl ?>/user/addresses" class="text-xs font-bold text-[#2D82FF] hover:underline flex items-center gap-1">
            <span class="material-icons text-sm">add_location_alt</span> Manage Addresses
          </a>
        </div>

        <?php if (empty($addresses)): ?>
          <div class="p-6 bg-amber-50 rounded-2xl border border-amber-200 text-center space-y-3">
            <p class="text-xs text-amber-800 font-semibold">You have no saved delivery addresses.</p>
            <a href="<?= $baseUrl ?>/user/addresses" class="btn-place-order text-xs py-2 px-4 inline-flex w-auto">
              + Add Shipping Address
            </a>
          </div>
        <?php else: ?>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="address-selection-grid">
            <?php foreach ($addresses as $a): ?>
              <?php 
              $aEnc = $a['encrypted_id'] ?? $a['id'];
              $isSel = $aEnc == $defaultAddressId;
              ?>
              <div onclick="selectCheckoutAddress('<?= $aEnc ?>', this)" class="addr-radio-card p-4 <?= $isSel ? 'selected' : '' ?>">
                <div class="flex items-start justify-between gap-2 mb-2">
                  <div class="flex items-center gap-2">
                    <input type="radio" name="chk_address" value="<?= $aEnc ?>" <?= $isSel ? 'checked' : '' ?> class="w-4 h-4 accent-[#2D82FF]">
                    <span class="font-bold text-xs text-slate-800"><?= htmlspecialchars($a['label'] ?? 'Home') ?></span>
                  </div>
                  <?php if (!empty($a['is_default'])): ?>
                    <span class="bg-[#2D82FF] text-white text-[9px] font-extrabold px-2 py-0.5 rounded-full">DEFAULT</span>
                  <?php endif; ?>
                </div>

                <p class="font-bold text-xs text-slate-900"><?= htmlspecialchars($a['recipient_name'] ?? '') ?></p>
                <p class="text-[11px] text-slate-500 mt-0.5"><?= htmlspecialchars($a['phone'] ?? '') ?></p>
                <p class="text-[11px] text-slate-600 mt-2 border-t border-slate-100 pt-2 leading-relaxed">
                  <?= htmlspecialchars($a['address_line1'] ?? '') ?><br>
                  <?= htmlspecialchars($a['city'] ?? '') ?>, <?= htmlspecialchars($a['state'] ?? '') ?> — <strong><?= htmlspecialchars($a['postal_code'] ?? '') ?></strong>
                </p>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Step 2: Order Items Summary -->
      <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4 fade-up" style="animation-delay:.08s">
        <h2 class="font-bold text-base text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
          <span class="w-7 h-7 rounded-lg bg-[#2D82FF] text-white flex items-center justify-center text-xs font-extrabold">2</span>
          Order Items (<?= count($cartItems) ?> Items)
        </h2>

        <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto pr-1">
          <?php foreach ($cartItems as $ci): ?>
            <?php 
            $price = (float)($ci['unit_price'] ?? 0);
            $qty   = (int)($ci['quantity'] ?? 1);
            $img = !empty($ci['primary_image']) ? $ci['primary_image'] : (!empty($ci['image_url']) ? $ci['image_url'] : 'https://via.placeholder.com/100');
            ?>
            <div class="py-3 flex items-center gap-4">
              <div class="w-14 h-14 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center overflow-hidden shrink-0">
                <img src="<?= htmlspecialchars($img) ?>" alt="" class="max-h-full max-w-full object-contain p-1">
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="text-xs font-bold text-slate-800 truncate"><?= htmlspecialchars($ci['name'] ?? 'Product') ?></h4>
                <p class="text-[11px] text-slate-400">Qty: <?= $qty ?> × ₹<?= number_format($price, 2) ?></p>
              </div>
              <span class="font-extrabold text-xs text-slate-900">₹<?= number_format($price * $qty, 2) ?></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Step 3: Payment Method -->
      <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4 fade-up" style="animation-delay:.1s">
        <h2 class="font-bold text-base text-slate-900 flex items-center gap-2 border-b border-slate-100 pb-3">
          <span class="w-7 h-7 rounded-lg bg-[#2D82FF] text-white flex items-center justify-center text-xs font-extrabold">3</span>
          Select Payment Method
        </h2>

        <div class="space-y-2.5">
          <div onclick="selectPayMethod('PhonePe', this)" class="pay-option-card selected">
            <div class="flex items-center gap-3">
              <input type="radio" name="chk_payment" value="PhonePe" checked class="w-4 h-4 accent-[#5F259F]">
              <div>
                <p class="font-bold text-xs text-slate-900 flex items-center gap-1.5">
                  <span class="w-5 h-5 rounded-md bg-[#5F259F] text-white flex items-center justify-center font-black text-[10px]">पे</span>
                  PhonePe (UPI, QR Code, Cards & Wallets)
                </p>
                <p class="text-[11px] text-slate-500">Pay instantly via PhonePe, GPay, Paytm or Cards</p>
              </div>
            </div>
            <span class="text-[10px] font-extrabold text-purple-700 bg-purple-100 px-2.5 py-0.5 rounded-full uppercase tracking-wide">RECOMMENDED</span>
          </div>

          <div onclick="selectPayMethod('COD', this)" class="pay-option-card">
            <div class="flex items-center gap-3">
              <input type="radio" name="chk_payment" value="COD" class="w-4 h-4 accent-[#2D82FF]">
              <div>
                <p class="font-bold text-xs text-slate-900">💵 Cash on Delivery (COD)</p>
                <p class="text-[11px] text-slate-400">Pay cash upon package arrival</p>
              </div>
            </div>
            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">NO EXTRA FEE</span>
          </div>

          <div onclick="selectPayMethod('Card', this)" class="pay-option-card">
            <div class="flex items-center gap-3">
              <input type="radio" name="chk_payment" value="Card" class="w-4 h-4 accent-[#2D82FF]">
              <div>
                <p class="font-bold text-xs text-slate-900">💳 Credit / Debit Card</p>
                <p class="text-[11px] text-slate-400">Visa, MasterCard, RuPay</p>
              </div>
            </div>
            <span class="text-[10px] font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded">SECURE</span>
          </div>
        </div>
      </div>

    </div>

    <!-- Right Column: Order Summary & Confirm (4 cols on lg) -->
    <div class="lg:col-span-4 space-y-4">
      <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 space-y-4 sticky top-[76px] fade-up" style="animation-delay:.12s">
        <h3 class="font-bold text-base text-slate-900 border-b border-slate-100 pb-3">Payment Summary</h3>

        <div class="space-y-2.5 text-xs text-slate-600">
          <div class="flex justify-between">
            <span>Items Subtotal</span>
            <span class="font-bold text-slate-900">₹<?= number_format($subtotal, 2) ?></span>
          </div>
          <?php if ($discount > 0): ?>
            <div class="flex justify-between text-emerald-600 font-semibold">
              <span>Catalog Discount</span>
              <span>-₹<?= number_format($discount, 2) ?></span>
            </div>
          <?php endif; ?>
          <div class="flex justify-between">
            <span>Delivery Shipping</span>
            <span class="font-bold text-slate-900"><?= $shipping === 0 ? '<span class="text-emerald-600 uppercase font-extrabold">FREE</span>' : '₹' . number_format($shipping, 2) ?></span>
          </div>
          <div class="border-t border-slate-100 pt-3 flex justify-between items-baseline text-slate-900">
            <span class="font-extrabold text-sm">Grand Total</span>
            <span class="font-extrabold text-xl text-[#2D82FF]">₹<?= number_format($total, 2) ?></span>
          </div>
        </div>

        <input type="hidden" id="selected-address-id" value="<?= $defaultAddressId ?>">
        <input type="hidden" id="selected-pay-method" value="PhonePe">

        <button onclick="placeOrderNow()" class="btn-place-order" id="place-order-btn">
          <span class="material-icons text-lg">check_circle</span> Place Order Now
        </button>

        <p class="text-[11px] text-slate-400 text-center leading-snug">
          By clicking Place Order, you confirm your order agreement and delivery details.
        </p>
      </div>
    </div>

  </div>

</main>

<script>
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';

function showToast(msg, type='success'){
  const c = document.getElementById('toast-container');
  if(!c) return;
  const cls = {success:'bg-emerald-600', error:'bg-red-500', info:'bg-[#2D82FF]', warning:'bg-amber-500'};
  const ico = {success:'check_circle', error:'error', info:'info', warning:'warning'};
  const t = document.createElement('div');
  t.className = `toast-in pointer-events-auto flex items-center gap-3 ${cls[type]||cls.info} text-white px-5 py-3.5 rounded-xl shadow-2xl text-sm font-medium`;
  t.innerHTML = `<span class="material-icons text-lg">${ico[type]||'info'}</span><span class="flex-1">${msg}</span>`;
  c.appendChild(t);
  setTimeout(()=>{ t.remove(); },3500);
}

function selectCheckoutAddress(encId, cardEl) {
  document.getElementById('selected-address-id').value = encId;
  document.querySelectorAll('.addr-radio-card').forEach(c => c.classList.remove('selected'));
  cardEl.classList.add('selected');
  const radio = cardEl.querySelector('input[type="radio"]');
  if (radio) radio.checked = true;
}

function selectPayMethod(method, cardEl) {
  document.getElementById('selected-pay-method').value = method;
  document.querySelectorAll('.pay-option-card').forEach(c => c.classList.remove('selected'));
  cardEl.classList.add('selected');
  const radio = cardEl.querySelector('input[type="radio"]');
  if (radio) radio.checked = true;
}

function placeOrderNow() {
  const addrId = document.getElementById('selected-address-id').value;
  const method = document.getElementById('selected-pay-method').value;

  if (!addrId) {
    showToast('Please select a delivery address.', 'warning');
    return;
  }

  const btn = document.getElementById('place-order-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="material-icons text-lg animate-spin">sync</span> Placing Order...';

  fetch(`${BASE_URL}/checkout/place-order`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({
      address_id: addrId,
      payment_method: method
    })
  })
  .then(async r => {
    const text = await r.text();
    try {
      return JSON.parse(text);
    } catch (e) {
      console.error('Raw Server Response:', text);
      return { success: false, message: 'Server response error: ' + (text.replace(/<[^>]*>?/gm, '').substring(0, 100) || 'Invalid server response.') };
    }
  })
  .then(data => {
    if (data.success) {
      showToast(data.message || 'Order placed successfully!', 'success');
      setTimeout(() => {
        window.location.href = data.redirect_url || `${BASE_URL}/user/orders`;
      }, 800);
    } else {
      btn.disabled = false;
      btn.innerHTML = '<span class="material-icons text-lg">check_circle</span> Place Order Now';
      showToast(data.message || 'Failed to place order.', 'error');
    }
  })
  .catch((err) => {
    btn.disabled = false;
    btn.innerHTML = '<span class="material-icons text-lg">check_circle</span> Place Order Now';
    console.error(err);
    showToast(err.message || 'Network connection issue.', 'error');
  });
}
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
