<?php
$baseUrl = $baseUrl ?? (defined('BASE_URL') ? BASE_URL : '');
$order   = $order   ?? [];
$items   = $items   ?? [];

$orderNumber = $order['order_number'] ?? ('ORD-' . ($order['id'] ?? 0));
$orderDate   = !empty($order['created_at']) ? date('d M Y, h:i A', strtotime($order['created_at'])) : date('d M Y');
$delivDate   = !empty($order['updated_at']) ? date('d M Y', strtotime($order['updated_at'])) : date('d M Y');
$subtotal    = (float)($order['subtotal'] ?? $order['total_amount'] ?? 0);
$discount    = (float)($order['discount_amount'] ?? 0);
$shipping    = (float)($order['shipping_charge'] ?? 0);
$grandTotal  = (float)($order['grand_total'] ?? $order['total_amount'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tax Invoice — <?= htmlspecialchars($orderNumber) ?> — ClickCodex</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #0f172a; }
    @media print {
      body { background: #fff; }
      .no-print { display: none !important; }
      .print-shadow-none { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
    }
  </style>
</head>
<body class="p-4 sm:p-8">

  <!-- Printable Action Bar -->
  <div class="max-w-4xl mx-auto mb-6 flex items-center justify-between no-print bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
    <div class="flex items-center gap-2 text-xs font-bold text-slate-600">
      <span class="material-icons text-emerald-600 text-lg">verified</span>
      <span>Official Tax Invoice for Delivered Order</span>
    </div>
    <div class="flex items-center gap-3">
      <button onclick="window.print()" class="bg-[#2D82FF] hover:bg-blue-600 text-white font-bold text-xs px-4 py-2.5 rounded-xl flex items-center gap-1.5 shadow-md transition cursor-pointer">
        <span class="material-icons text-base">print</span> Print / Save PDF
      </button>
      <button onclick="window.close()" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs px-3.5 py-2.5 rounded-xl transition cursor-pointer">
        Close
      </button>
    </div>
  </div>

  <!-- Tax Invoice Card -->
  <div class="max-w-4xl mx-auto bg-white rounded-3xl border border-slate-200 shadow-xl p-6 sm:p-10 space-y-8 print-shadow-none">
    
    <!-- Top Header & Logo -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pb-6 border-b border-slate-200">
      <div>
        <div class="flex items-center gap-2 mb-1">
          <div class="w-8 h-8 rounded-lg bg-[#2D82FF] text-white font-black text-lg flex items-center justify-center">C</div>
          <span class="text-xl font-extrabold text-slate-900 tracking-tight">ClickCodex Marketplace</span>
        </div>
        <p class="text-xs text-slate-400">GSTIN: 27AAAAA0000A1Z5 | Reg. No: CC-2026-IN</p>
      </div>

      <div class="text-left sm:text-right">
        <span class="inline-block bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-extrabold px-3 py-1 rounded-full uppercase tracking-wider mb-1">
          TAX INVOICE
        </span>
        <p class="text-xs text-slate-500 font-mono">Invoice #: <strong class="text-slate-900">INV-<?= htmlspecialchars($orderNumber) ?></strong></p>
        <p class="text-xs text-slate-500">Order Date: <?= htmlspecialchars($orderDate) ?></p>
        <p class="text-xs text-emerald-600 font-bold">Delivered On: <?= htmlspecialchars($delivDate) ?></p>
      </div>
    </div>

    <!-- Bill To & Ship To Details -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 bg-slate-50 p-5 rounded-2xl border border-slate-100 text-xs">
      <div>
        <h4 class="font-bold text-slate-400 uppercase tracking-wider mb-2">Billed &amp; Delivered To</h4>
        <p class="text-sm font-bold text-slate-900"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Valued Customer') ?></p>
        <p class="text-slate-600 mt-1 leading-relaxed"><?= nl2br(htmlspecialchars($order['shipping_address'] ?? 'Customer Delivery Address')) ?></p>
      </div>

      <div>
        <h4 class="font-bold text-slate-400 uppercase tracking-wider mb-2">Payment Details</h4>
        <p class="text-slate-700">Payment Mode: <strong class="text-slate-900 font-bold"><?= htmlspecialchars($order['payment_method'] ?? 'Online Payment') ?></strong></p>
        <p class="text-slate-700 mt-1">Payment Status: <strong class="text-emerald-600 font-bold uppercase"><?= htmlspecialchars($order['payment_status'] ?? 'Paid') ?></strong></p>
        <p class="text-slate-700 mt-1">Order Number: <strong class="text-slate-900 font-mono"><?= htmlspecialchars($orderNumber) ?></strong></p>
      </div>
    </div>

    <!-- Itemized Product Table -->
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs border-collapse">
        <thead>
          <tr class="border-b border-slate-200 bg-slate-100/70 text-slate-600 font-bold uppercase tracking-wider">
            <th class="py-3 px-4">Item &amp; Description</th>
            <th class="py-3 px-4 text-center">Qty</th>
            <th class="py-3 px-4 text-right">Unit Price</th>
            <th class="py-3 px-4 text-right">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php foreach ($items as $item): ?>
            <?php 
            $qty = (int)($item['quantity'] ?? 1);
            $uPrc = (float)($item['unit_price'] ?? 0);
            $tPrc = (float)($item['total_price'] ?? ($uPrc * $qty));
            ?>
            <tr>
              <td class="py-3.5 px-4 font-bold text-slate-800">
                <?= htmlspecialchars($item['name'] ?? $item['product_name'] ?? 'Product Item') ?>
              </td>
              <td class="py-3.5 px-4 text-center font-semibold text-slate-700"><?= $qty ?></td>
              <td class="py-3.5 px-4 text-right text-slate-700">₹<?= number_format($uPrc, 2) ?></td>
              <td class="py-3.5 px-4 text-right font-extrabold text-slate-900">₹<?= number_format($tPrc, 2) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Summary Total Calculation -->
    <div class="flex justify-end pt-4 border-t border-slate-200">
      <div class="w-full max-w-xs space-y-2 text-xs">
        <div class="flex justify-between text-slate-600">
          <span>Items Subtotal</span>
          <span class="font-bold text-slate-900">₹<?= number_format($subtotal, 2) ?></span>
        </div>
        <?php if ($discount > 0): ?>
          <div class="flex justify-between text-green-600">
            <span>Discount</span>
            <span class="font-bold">−₹<?= number_format($discount, 2) ?></span>
          </div>
        <?php endif; ?>
        <div class="flex justify-between text-slate-600">
          <span>Shipping Charges</span>
          <span class="font-bold text-slate-900"><?= $shipping == 0 ? 'FREE' : ('₹' . number_format($shipping, 2)) ?></span>
        </div>
        <div class="flex justify-between text-sm font-extrabold text-slate-900 pt-3 border-t border-slate-200">
          <span>Grand Total (Incl. GST)</span>
          <span class="text-[#2D82FF] text-base">₹<?= number_format($grandTotal, 2) ?></span>
        </div>
      </div>
    </div>

    <!-- Footer Seal & Disclaimer -->
    <div class="border-t border-slate-100 pt-6 text-[11px] text-slate-400 flex flex-col sm:flex-row items-center justify-between gap-2">
      <p>Thank you for shopping with ClickCodex Marketplace! This is a computer-generated tax invoice.</p>
      <p class="font-bold text-slate-600">ClickCodex Authorized Merchant</p>
    </div>

  </div>

</body>
</html>
