<?php
$baseUrl = $baseUrl ?? (defined('BASE_URL') ? BASE_URL : '');
$order   = $order   ?? [];

$db = \App\Config\Database::connect();
$storeUpiVpa = $_ENV['UPI_VPA_ID'] ?? getenv('UPI_VPA_ID') ?: 'clickcodex@ybl';
$storeMerchantName = $_ENV['UPI_MERCHANT_NAME'] ?? getenv('UPI_MERCHANT_NAME') ?: 'ClickCodex Marketplace';

$res = $db->query("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ('upi_vpa_id', 'upi_merchant_name')");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        if ($row['setting_key'] === 'upi_vpa_id' && !empty($row['setting_value'])) {
            $storeUpiVpa = $row['setting_value'];
        }
        if ($row['setting_key'] === 'upi_merchant_name' && !empty($row['setting_value'])) {
            $storeMerchantName = $row['setting_value'];
        }
    }
}

$orderIdEnc  = \App\Helpers\SecurityHelper::encryptId($order['id'] ?? 0);
$orderNumber = $order['order_number'] ?? 'ORD-PHONEPE';
$rawTotal    = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
$totalAmount = number_format($rawTotal, 2);

// Standard strict UPI deep link format locking amount (am & mam = minimum amount)
$upiDeepLink = "upi://pay?pa=" . urlencode($storeUpiVpa) . 
               "&pn=" . urlencode($storeMerchantName) . 
               "&am=" . urlencode($rawTotal) . 
               "&mam=" . urlencode($rawTotal) . 
               "&tr=" . urlencode($orderNumber) . 
               "&tn=" . urlencode("Order {$orderNumber}") . 
               "&cu=INR";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PhonePe Secure Payment — ClickCodex</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; background: #F4F0F9; }
    .phonepe-violet { background-color: #5F259F; }
    .phonepe-violet-text { color: #5F259F; }
    .phonepe-btn { background-color: #5F259F; transition: all .2s ease; }
    .phonepe-btn:hover { background-color: #4A1C7F; transform: translateY(-1px); box-shadow: 0 10px 25px rgba(95, 37, 159, 0.35); }
    @keyframes pulseSoft { 0%,100%{transform:scale(1)} 50%{transform:scale(1.04)} }
    .qr-pulse { animation: pulseSoft 2.5s infinite ease-in-out; }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-3 sm:p-6">

  <div class="w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden border border-slate-200">
    
    <!-- PhonePe Top Header Bar -->
    <div class="phonepe-violet text-white p-5 sm:p-6 text-center relative">
      <div class="inline-flex items-center justify-center bg-white rounded-2xl px-4 py-2 shadow-md mb-3">
        <span class="font-extrabold text-xl phonepe-violet-text tracking-tight flex items-center gap-1.5">
          <span class="w-7 h-7 rounded-xl bg-[#5F259F] text-white flex items-center justify-center font-black text-sm">पे</span>
          PhonePe
        </span>
      </div>
      <p class="text-xs text-purple-200 font-medium">Merchant: <span class="font-bold text-white"><?= htmlspecialchars($storeMerchantName) ?></span></p>
      <div class="mt-4 pt-4 border-t border-purple-400/30 flex items-center justify-between text-xs sm:text-sm">
        <span class="text-purple-200">Order ID: <strong class="text-white font-mono"><?= htmlspecialchars($orderNumber) ?></strong></span>
        <span class="text-xs bg-emerald-400/20 text-emerald-200 px-3 py-1 rounded-full font-extrabold flex items-center gap-1 border border-emerald-400/40">
          <span class="material-icons text-xs">lock</span> Fixed Amount: ₹<?= $totalAmount ?>
        </span>
      </div>
    </div>

    <!-- Main Payment Body -->
    <div class="p-5 sm:p-7 space-y-6">

      <!-- Dynamic UPI QR Code Section -->
      <div class="bg-purple-50/70 rounded-2xl p-5 border border-purple-100 text-center space-y-3">
        <div class="flex items-center justify-center gap-1.5 text-xs font-bold text-slate-800">
          <span class="material-icons text-sm phonepe-violet-text">qr_code_2</span> Scan &amp; Pay Fixed Amount via PhonePe
          <span class="text-[10px] bg-purple-200 text-[#5F259F] px-2 py-0.5 rounded-md font-extrabold">NON-EDITABLE</span>
        </div>

        <div class="inline-block bg-white p-3 rounded-2xl shadow-md border border-purple-200 qr-pulse">
          <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?= urlencode($upiDeepLink) ?>" 
               alt="PhonePe QR Code" class="w-40 h-40 object-contain mx-auto rounded-xl">
        </div>

        <p class="text-[11px] text-slate-500">Scan using <strong>PhonePe</strong>, GPay, Paytm, or BHIM</p>
        <p class="text-[11px] font-mono font-bold phonepe-violet-text bg-white px-3 py-1 rounded-lg inline-block border border-purple-200">
          VPA: <?= htmlspecialchars($storeUpiVpa) ?>
        </p>

        <!-- Direct PhonePe App Deep Link Button for Mobile Users -->
        <div class="pt-1">
          <a href="<?= htmlspecialchars($upiDeepLink) ?>" class="w-full bg-[#5F259F] hover:bg-[#4A1C7F] text-white font-extrabold py-2.5 px-4 rounded-xl flex items-center justify-center gap-2 text-xs transition shadow-md">
            <span class="w-5 h-5 rounded-md bg-white/20 flex items-center justify-center text-[10px] font-black">पे</span>
            Open PhonePe App (Pay Fixed ₹<?= $totalAmount ?>)
          </a>
        </div>
      </div>

      <!-- Payment Gateway Action Buttons -->
      <form action="<?= $baseUrl ?>/payment/phonepe/callback" method="POST" class="space-y-3">
        <input type="hidden" name="merchantTransactionId" value="<?= htmlspecialchars($orderNumber) ?>">
        <input type="hidden" name="transactionId" value="T<?= time() . rand(100, 999) ?>">
        <input type="hidden" name="payment_instrument" value="PhonePe UPI App">

        <!-- Success Simulation Button -->
        <button type="submit" name="code" value="PAYMENT_SUCCESS" class="w-full phonepe-btn text-white font-extrabold py-4 px-6 rounded-2xl flex items-center justify-center gap-2 text-base shadow-lg cursor-pointer">
          <span class="material-icons text-xl">verified</span> Approve &amp; Pay Fixed ₹<?= $totalAmount ?>
        </button>

        <!-- Failure / Cancel Button -->
        <button type="submit" name="code" value="PAYMENT_FAILED" class="w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2.5 px-4 rounded-xl flex items-center justify-center gap-1.5 text-xs transition cursor-pointer">
          <span class="material-icons text-sm text-amber-600">schedule</span> Save Order &amp; Pay Later (Process)
        </button>
      </form>

      <!-- Security Trust Footer -->
      <div class="border-t border-slate-100 pt-4 flex items-center justify-between text-[11px] text-slate-400">
        <span class="flex items-center gap-1">
          <span class="material-icons text-xs text-emerald-500">shield</span> 256-Bit PhonePe PG SSL
        </span>
        <span>NPCI &amp; RBI Approved</span>
      </div>

    </div>

  </div>

</body>
</html>
