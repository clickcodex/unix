<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main class="max-w-4xl mx-auto px-4 py-12 space-y-8">
  <div class="text-center space-y-2">
    <h1 class="text-3xl font-extrabold text-slate-900">Frequently Asked Questions</h1>
    <p class="text-slate-500 text-sm">Find quick answers to common questions about orders, payments, and shipping.</p>
  </div>

  <div class="space-y-4">
    <details class="group bg-white border border-slate-200 rounded-2xl p-6 transition-all [&_summary::-webkit-details-marker]:hidden cursor-pointer" open>
      <summary class="flex items-center justify-between font-bold text-slate-900 text-base">
        How long does shipping take?
        <span class="material-icons text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
      </summary>
      <p class="text-slate-600 text-sm mt-3 leading-relaxed">Standard shipping takes 3-5 business days depending on your location. Express dispatch is available for select pin codes.</p>
    </details>

    <details class="group bg-white border border-slate-200 rounded-2xl p-6 transition-all [&_summary::-webkit-details-marker]:hidden cursor-pointer">
      <summary class="flex items-center justify-between font-bold text-slate-900 text-base">
        What payment methods do you accept?
        <span class="material-icons text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
      </summary>
      <p class="text-slate-600 text-sm mt-3 leading-relaxed">We support PhonePe Payment Gateway (Cards, NetBanking, UPI), direct Merchant UPI VPA QR scanning, and Cash on Delivery (COD).</p>
    </details>

    <details class="group bg-white border border-slate-200 rounded-2xl p-6 transition-all [&_summary::-webkit-details-marker]:hidden cursor-pointer">
      <summary class="flex items-center justify-between font-bold text-slate-900 text-base">
        How do I track my order?
        <span class="material-icons text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
      </summary>
      <p class="text-slate-600 text-sm mt-3 leading-relaxed">Go to My Orders in your account dashboard. Click on any active order to view full timeline tracking, carrier details, and download tax invoices.</p>
    </details>

    <details class="group bg-white border border-slate-200 rounded-2xl p-6 transition-all [&_summary::-webkit-details-marker]:hidden cursor-pointer">
      <summary class="flex items-center justify-between font-bold text-slate-900 text-base">
        What is your return policy?
        <span class="material-icons text-slate-400 group-open:rotate-180 transition-transform">expand_more</span>
      </summary>
      <p class="text-slate-600 text-sm mt-3 leading-relaxed">You can request a return within 7 days of delivery directly from your order detail page. Once inspected, your refund is processed within 48 hours.</p>
    </details>
  </div>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
