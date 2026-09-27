<?php
require_once __DIR__ . '/../layouts/header.php';

$user    = $user    ?? [];
$baseUrl = $baseUrl ?? (defined('BASE_URL') ? BASE_URL : '');
?>

<style>
  body { background: #F1F5F9; }
  @keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
  .fade-up { animation: fadeUp .35s ease-out both; }
  @keyframes scaleIn { from{transform:scale(.95);opacity:0} to{transform:scale(1);opacity:1} }
  .scale-in { animation: scaleIn .2s ease-out both; }
  @keyframes slideInRight { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
  @keyframes slideOutRight { from{transform:translateX(0);opacity:1} to{transform:translateX(100%);opacity:0} }
  .toast-in { animation: slideInRight .35s ease-out forwards; }
  .toast-out { animation: slideOutRight .3s ease-in forwards; }

  .topic-card {
    border: 1.5px solid #E2E8F0; border-radius: 16px; background: #fff;
    padding: 20px; transition: all .25s ease; position: relative; overflow: hidden;
  }
  .topic-card:hover { border-color: #2D82FF; box-shadow: 0 12px 24px -6px rgba(15,23,42,.08); transform: translateY(-2px); }

  .faq-item { border: 1.5px solid #E2E8F0; border-radius: 14px; background: #fff; overflow: hidden; transition: all .2s ease; }
  .faq-item.active { border-color: #2D82FF; box-shadow: 0 4px 14px rgba(45,130,255,.08); }
  .faq-header { padding: 16px 20px; cursor: pointer; display: flex; align-items: center; justify-content: space-between; gap: 12px; user-select: none; }
  .faq-body { max-height: 0; overflow: hidden; transition: max-height .3s cubic-bezier(0,1,0,1); padding: 0 20px; }
  .faq-item.active .faq-body { max-height: 500px; padding: 0 20px 18px 20px; transition: max-height .35s ease-in-out; }
  .faq-item.active .faq-icon { transform: rotate(180deg); color: #2D82FF; }

  .channel-card { border: 1.5px solid #E2E8F0; border-radius: 16px; background: #fff; padding: 20px; transition: all .2s ease; }
  .channel-card:hover { border-color: #2D82FF; transform: translateY(-2px); }

  .form-input {
    width: 100%; border: 1.5px solid #E2E8F0; border-radius: 12px;
    padding: 10px 14px; font-size: 14px; transition: all .15s ease; outline: none; background: #fff;
  }
  .form-input:focus { border-color: #2D82FF; box-shadow: 0 0 0 3px rgba(45,130,255,.1); }

  .btn-primary {
    background: #2D82FF; color: #fff; font-weight: 700; font-size: 13px;
    padding: 10px 20px; border-radius: 12px; transition: all .2s ease;
    display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border: none;
  }
  .btn-primary:hover { background: #1D6FE0; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(45,130,255,.3); }

  .btn-outline {
    border: 1.5px solid #E2E8F0; color: #475569; font-weight: 600; font-size: 12px;
    padding: 7px 14px; border-radius: 10px; transition: all .15s ease;
    display: inline-flex; align-items: center; gap: 4px; background: #fff; cursor: pointer;
  }
  .btn-outline:hover { border-color: #2D82FF; color: #2D82FF; background: #F0F6FF; }

  .tab-btn {
    padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 600;
    color: #64748B; cursor: pointer; transition: all .15s ease; border: 1.5px solid #E2E8F0; background: #fff;
  }
  .tab-btn.active { border-color: #2D82FF; background: #F0F6FF; color: #2D82FF; }

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
    <span class="text-slate-800 font-semibold">Help Center</span>
  </nav>

  <div class="flex gap-6">

    <!-- Sidebar -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 min-w-0 space-y-5">

      <!-- Mobile Sidebar Toggle Button -->
      <button onclick="toggleMobileSidebar(true)" class="lg:hidden flex items-center justify-between w-full bg-white border border-slate-200 p-3.5 rounded-2xl shadow-xs text-xs font-bold text-slate-800 hover:bg-slate-50 transition">
        <div class="flex items-center gap-2">
          <span class="material-icons text-[#2D82FF] text-lg">menu</span>
          <span>Account Navigation Menu</span>
        </div>
        <span class="bg-[#2D82FF]/10 text-[#2D82FF] text-[10px] font-extrabold px-2.5 py-1 rounded-lg">MENU</span>
      </button>

      <!-- Hero Search Banner -->
      <div class="bg-gradient-to-br from-[#2D82FF] via-[#5B4FD9] to-[#0F172A] rounded-2xl p-6 sm:p-8 text-white shadow-md fade-up">
        <div class="max-w-2xl mx-auto text-center space-y-3">
          <span class="bg-white/20 text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider">Customer Support</span>
          <h1 class="font-extrabold text-2xl sm:text-3xl">How can we help you today?</h1>
          <p class="text-white/70 text-xs sm:text-sm">Search our knowledge base for answers regarding orders, returns, payments &amp; account settings.</p>

          <!-- Search Bar -->
          <div class="relative max-w-xl mx-auto pt-2">
            <span class="material-icons absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xl">search</span>
            <input type="text" id="help-search" oninput="searchHelp()" placeholder="Search FAQs, e.g. order status, refund, address..."
                   class="w-full pl-11 pr-4 py-3.5 rounded-xl text-slate-900 placeholder:text-slate-400 text-sm font-medium outline-none shadow-lg bg-white border-2 border-transparent focus:border-yellow-400">
          </div>

          <!-- Quick Topic Pills -->
          <div class="flex flex-wrap items-center justify-center gap-2 pt-2 text-xs">
            <span class="text-white/60 text-[11px]">Popular:</span>
            <button onclick="quickFilter('order')" class="bg-white/10 hover:bg-white/20 text-white px-2.5 py-1 rounded-lg transition">📦 Track Package</button>
            <button onclick="quickFilter('return')" class="bg-white/10 hover:bg-white/20 text-white px-2.5 py-1 rounded-lg transition">🔄 Returns</button>
            <button onclick="quickFilter('payment')" class="bg-white/10 hover:bg-white/20 text-white px-2.5 py-1 rounded-lg transition">💳 Refunds</button>
            <button onclick="quickFilter('address')" class="bg-white/10 hover:bg-white/20 text-white px-2.5 py-1 rounded-lg transition">📍 Change Address</button>
          </div>
        </div>
      </div>

      <!-- Help Categories Grid -->
      <div class="space-y-3 fade-up" style="animation-delay:.06s">
        <h2 class="font-bold text-lg text-slate-900">Explore Help Topics</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          
          <!-- Category 1 -->
          <div class="topic-card">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center mb-3">
              <span class="material-icons text-[#2D82FF] text-xl">local_shipping</span>
            </div>
            <h3 class="font-bold text-base text-slate-900 mb-1">Orders &amp; Delivery</h3>
            <p class="text-xs text-slate-500 mb-3 leading-relaxed">Track packages, delivery delays, shipping charges, and address updates.</p>
            <ul class="space-y-1.5 text-xs text-[#2D82FF] font-semibold">
              <li><a href="javascript:void(0)" onclick="quickFilter('track')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> How to track my order?</a></li>
              <li><a href="javascript:void(0)" onclick="quickFilter('cancel')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> Can I cancel my order?</a></li>
              <li><a href="javascript:void(0)" onclick="quickFilter('delay')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> What if my order is delayed?</a></li>
            </ul>
          </div>

          <!-- Category 2 -->
          <div class="topic-card">
            <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center mb-3">
              <span class="material-icons text-purple-600 text-xl">assignment_return</span>
            </div>
            <h3 class="font-bold text-base text-slate-900 mb-1">Returns &amp; Refunds</h3>
            <p class="text-xs text-slate-500 mb-3 leading-relaxed">Return policies, pickup scheduling, replacement requests, and refund statuses.</p>
            <ul class="space-y-1.5 text-xs text-purple-600 font-semibold">
              <li><a href="javascript:void(0)" onclick="quickFilter('policy')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> What is the return policy?</a></li>
              <li><a href="javascript:void(0)" onclick="quickFilter('refund')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> How long do refunds take?</a></li>
              <li><a href="javascript:void(0)" onclick="quickFilter('exchange')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> How to request an exchange?</a></li>
            </ul>
          </div>

          <!-- Category 3 -->
          <div class="topic-card">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center mb-3">
              <span class="material-icons text-emerald-600 text-xl">account_balance_wallet</span>
            </div>
            <h3 class="font-bold text-base text-slate-900 mb-1">Payments &amp; Offers</h3>
            <p class="text-xs text-slate-500 mb-3 leading-relaxed">Credit cards, UPI, Cash on Delivery, coupon codes, and failed transactions.</p>
            <ul class="space-y-1.5 text-xs text-emerald-600 font-semibold">
              <li><a href="javascript:void(0)" onclick="quickFilter('cod')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> Is Cash on Delivery available?</a></li>
              <li><a href="javascript:void(0)" onclick="quickFilter('failed')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> Money debited but order failed?</a></li>
              <li><a href="javascript:void(0)" onclick="quickFilter('coupon')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> How to apply promo coupons?</a></li>
            </ul>
          </div>

          <!-- Category 4 -->
          <div class="topic-card">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center mb-3">
              <span class="material-icons text-amber-600 text-xl">manage_accounts</span>
            </div>
            <h3 class="font-bold text-base text-slate-900 mb-1">Account &amp; Profile</h3>
            <p class="text-xs text-slate-500 mb-3 leading-relaxed">Password reset, profile updates, saved addresses, and email management.</p>
            <ul class="space-y-1.5 text-xs text-amber-600 font-semibold">
              <li><a href="<?= $baseUrl ?>/user/profile" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> Edit name &amp; password</a></li>
              <li><a href="<?= $baseUrl ?>/user/addresses" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> Manage shipping addresses</a></li>
              <li><a href="javascript:void(0)" onclick="quickFilter('password')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> Forgot my password?</a></li>
            </ul>
          </div>

          <!-- Category 5 -->
          <div class="topic-card">
            <div class="w-10 h-10 rounded-xl bg-rose-50 flex items-center justify-center mb-3">
              <span class="material-icons text-rose-600 text-xl">verified</span>
            </div>
            <h3 class="font-bold text-base text-slate-900 mb-1">Products &amp; Warranty</h3>
            <p class="text-xs text-slate-500 mb-3 leading-relaxed">Product authenticity, warranty claims, user manuals, and brand coverage.</p>
            <ul class="space-y-1.5 text-xs text-rose-600 font-semibold">
              <li><a href="javascript:void(0)" onclick="quickFilter('authentic')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> Are products genuine?</a></li>
              <li><a href="javascript:void(0)" onclick="quickFilter('warranty')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> How to claim warranty?</a></li>
              <li><a href="javascript:void(0)" onclick="quickFilter('stock')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> Notify me when back in stock</a></li>
            </ul>
          </div>

          <!-- Category 6 -->
          <div class="topic-card">
            <div class="w-10 h-10 rounded-xl bg-cyan-50 flex items-center justify-center mb-3">
              <span class="material-icons text-cyan-600 text-xl">shield</span>
            </div>
            <h3 class="font-bold text-base text-slate-900 mb-1">Safety &amp; Privacy</h3>
            <p class="text-xs text-slate-500 mb-3 leading-relaxed">Secure shopping, data protection, reporting fraud, and terms of service.</p>
            <ul class="space-y-1.5 text-xs text-cyan-600 font-semibold">
              <li><a href="javascript:void(0)" onclick="quickFilter('security')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> How safe is my personal data?</a></li>
              <li><a href="javascript:void(0)" onclick="quickFilter('fraud')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> Report suspicious activity</a></li>
              <li><a href="javascript:void(0)" onclick="quickFilter('privacy')" class="hover:underline flex items-center gap-1"><span class="material-icons text-xs">chevron_right</span> Read Privacy Policy</a></li>
            </ul>
          </div>

        </div>
      </div>

      <!-- Accordion FAQs Section -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 space-y-4 fade-up" style="animation-delay:.1s">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
          <div>
            <h2 class="font-bold text-lg text-slate-900">Frequently Asked Questions</h2>
            <p class="text-xs text-slate-400">Click on any question to view detailed step-by-step instructions</p>
          </div>

          <!-- Category Filter Tabs -->
          <div class="flex items-center gap-1.5 overflow-x-auto scrollbar-none pb-1">
            <button onclick="setFaqTab('all')" id="tab-all" class="tab-btn active">All</button>
            <button onclick="setFaqTab('orders')" id="tab-orders" class="tab-btn">Orders</button>
            <button onclick="setFaqTab('returns')" id="tab-returns" class="tab-btn">Returns</button>
            <button onclick="setFaqTab('payments')" id="tab-payments" class="tab-btn">Payments</button>
            <button onclick="setFaqTab('account')" id="tab-account" class="tab-btn">Account</button>
          </div>
        </div>

        <!-- FAQ Items List -->
        <div id="faq-container" class="space-y-3">
          
          <!-- Q1 -->
          <div class="faq-item" data-cat="orders" data-tags="track order status delivery location carrier">
            <div class="faq-header" onclick="toggleFaq(this)">
              <span class="font-bold text-sm text-slate-800">How do I track my order delivery in real time?</span>
              <span class="material-icons faq-icon text-slate-400 transition-transform">expand_more</span>
            </div>
            <div class="faq-body text-xs text-slate-600 leading-relaxed border-t border-slate-50 pt-3">
              You can track your order at any time by visiting your <a href="<?= $baseUrl ?>/user/orders" class="text-[#2D82FF] font-bold hover:underline">My Orders</a> page. Click on "View Order Details" for any active order to see live tracking milestones, courier partner details, and expected delivery date.
            </div>
          </div>

          <!-- Q2 -->
          <div class="faq-item" data-cat="orders" data-tags="cancel order cancellation dispatch refund">
            <div class="faq-header" onclick="toggleFaq(this)">
              <span class="font-bold text-sm text-slate-800">Can I cancel an order after placing it?</span>
              <span class="material-icons faq-icon text-slate-400 transition-transform">expand_more</span>
            </div>
            <div class="faq-body text-xs text-slate-600 leading-relaxed border-t border-slate-50 pt-3">
              Yes, you can cancel any order before it is packed or dispatched from our warehouse. Go to <a href="<?= $baseUrl ?>/user/orders" class="text-[#2D82FF] font-bold hover:underline">My Orders</a>, select the order, and click the <strong>Cancel Order</strong> button. Prepaid orders will be automatically refunded within 3-5 business days.
            </div>
          </div>

          <!-- Q3 -->
          <div class="faq-item" data-cat="returns" data-tags="return policy days eligibility replacement">
            <div class="faq-header" onclick="toggleFaq(this)">
              <span class="font-bold text-sm text-slate-800">What is ClickCodex’s Return &amp; Exchange policy?</span>
              <span class="material-icons faq-icon text-slate-400 transition-transform">expand_more</span>
            </div>
            <div class="faq-body text-xs text-slate-600 leading-relaxed border-t border-slate-50 pt-3">
              We offer a hassle-free <strong>7-Day Easy Return Policy</strong> for eligible electronics and accessories. Items must be unused, in original packaging with intact tags and seal. Simply navigate to your delivered order under <a href="<?= $baseUrl ?>/user/orders" class="text-[#2D82FF] font-bold hover:underline">My Orders</a> and click "Request Return".
            </div>
          </div>

          <!-- Q4 -->
          <div class="faq-item" data-cat="returns" data-tags="refund time processing bank account upi wallet">
            <div class="faq-header" onclick="toggleFaq(this)">
              <span class="font-bold text-sm text-slate-800">How long does it take to receive a refund?</span>
              <span class="material-icons faq-icon text-slate-400 transition-transform">expand_more</span>
            </div>
            <div class="faq-body text-xs text-slate-600 leading-relaxed border-t border-slate-50 pt-3">
              Once your returned package reaches our fulfillment center and passes quality check (usually within 24 hours), refunds are processed immediately:
              <ul class="list-disc pl-4 mt-1.5 space-y-1">
                <li><strong>UPI / Wallet:</strong> 2 - 24 hours</li>
                <li><strong>Debit / Credit Cards:</strong> 3 - 5 business days</li>
                <li><strong>COD Orders:</strong> Refunded directly to your saved Bank Account / UPI ID within 24 hours of pickup.</li>
              </ul>
            </div>
          </div>

          <!-- Q5 -->
          <div class="faq-item" data-cat="payments" data-tags="payment method cod cash on delivery netbanking upi card">
            <div class="faq-header" onclick="toggleFaq(this)">
              <span class="font-bold text-sm text-slate-800">What payment methods do you accept?</span>
              <span class="material-icons faq-icon text-slate-400 transition-transform">expand_more</span>
            </div>
            <div class="faq-body text-xs text-slate-600 leading-relaxed border-t border-slate-50 pt-3">
              We accept all major payment options across India:
              <ul class="list-disc pl-4 mt-1.5 space-y-1">
                <li>Google Pay, PhonePe, Paytm, BHIM UPI</li>
                <li>Visa, MasterCard, RuPay Debit &amp; Credit Cards</li>
                <li>Net Banking across 50+ Indian Banks</li>
                <li>Cash on Delivery (COD) for orders up to ₹25,000</li>
              </ul>
            </div>
          </div>

          <!-- Q6 -->
          <div class="faq-item" data-cat="payments" data-tags="failed debited money account checkout promo coupon">
            <div class="faq-header" onclick="toggleFaq(this)">
              <span class="font-bold text-sm text-slate-800">What if money was debited but the order failed?</span>
              <span class="material-icons faq-icon text-slate-400 transition-transform">expand_more</span>
            </div>
            <div class="faq-body text-xs text-slate-600 leading-relaxed border-t border-slate-50 pt-3">
              Don't worry! In case of a bank transaction timeout or payment gateway error, your debited amount is securely held by the bank and automatically reversed back to your original payment mode within 24 to 48 hours. If the status doesn't change, please raise a support ticket below with your Payment Ref ID.
            </div>
          </div>

          <!-- Q7 -->
          <div class="faq-item" data-cat="account" data-tags="address change shipping location edit profile">
            <div class="faq-header" onclick="toggleFaq(this)">
              <span class="font-bold text-sm text-slate-800">How do I manage my saved delivery addresses?</span>
              <span class="material-icons faq-icon text-slate-400 transition-transform">expand_more</span>
            </div>
            <div class="faq-body text-xs text-slate-600 leading-relaxed border-t border-slate-50 pt-3">
              You can add, edit, or set default delivery addresses in your <a href="<?= $baseUrl ?>/user/addresses" class="text-[#2D82FF] font-bold hover:underline">Saved Addresses</a> tab. You can save multiple addresses like Home 🏠, Office 🏢, or Parents Home 📍 for quick checkout.
            </div>
          </div>

          <!-- Q8 -->
          <div class="faq-item" data-cat="account" data-tags="password reset change security email phone avatar">
            <div class="faq-header" onclick="toggleFaq(this)">
              <span class="font-bold text-sm text-slate-800">How do I change my password or profile picture?</span>
              <span class="material-icons faq-icon text-slate-400 transition-transform">expand_more</span>
            </div>
            <div class="faq-body text-xs text-slate-600 leading-relaxed border-t border-slate-50 pt-3">
              Go to <a href="<?= $baseUrl ?>/user/profile" class="text-[#2D82FF] font-bold hover:underline">Edit Profile</a>. You can upload a new profile photo by clicking the camera icon 📷, update your personal details, or change your password under the Change Password section.
            </div>
          </div>

        </div>
      </div>

      <!-- Contact Support Channels -->
      <div class="space-y-3 fade-up" style="animation-delay:.14s">
        <h2 class="font-bold text-lg text-slate-900">Still need help? Get in touch</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          
          <!-- Channel 1: Live Chat -->
          <div class="channel-card flex flex-col justify-between">
            <div>
              <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center mb-3">
                <span class="material-icons text-[#2D82FF] text-xl">forum</span>
              </div>
              <h3 class="font-bold text-base text-slate-900 mb-1">24/7 Live Chat</h3>
              <p class="text-xs text-slate-500 mb-4 leading-relaxed">Instant answers from our automated AI assistant or live agent.</p>
            </div>
            <button onclick="startLiveChat()" class="btn-outline justify-center w-full">
              <span class="material-icons text-base">chat</span> Start Chat Now
            </button>
          </div>

          <!-- Channel 2: Submit Ticket -->
          <div class="channel-card flex flex-col justify-between">
            <div>
              <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center mb-3">
                <span class="material-icons text-purple-600 text-xl">confirmation_number</span>
              </div>
              <h3 class="font-bold text-base text-slate-900 mb-1">Submit Support Ticket</h3>
              <p class="text-xs text-slate-500 mb-4 leading-relaxed">Send an inquiry &amp; track resolution. Average reply time: &lt; 2 hours.</p>
            </div>
            <button onclick="openTicketModal()" class="btn-primary justify-center w-full">
              <span class="material-icons text-base">add_comment</span> Raise Ticket
            </button>
          </div>

          <!-- Channel 3: Phone Support -->
          <div class="channel-card flex flex-col justify-between">
            <div>
              <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center mb-3">
                <span class="material-icons text-emerald-600 text-xl">call</span>
              </div>
              <h3 class="font-bold text-base text-slate-900 mb-1">Toll-Free Hotline</h3>
              <p class="text-xs text-slate-500 mb-4 leading-relaxed">+1-800-123-4567<br><span class="text-slate-400">Mon - Sat, 9:00 AM - 8:00 PM IST</span></p>
            </div>
            <a href="tel:18001234567" class="btn-outline justify-center w-full !text-emerald-700 hover:!bg-emerald-50 hover:!border-emerald-200">
              <span class="material-icons text-base">phone_in_talk</span> Call Customer Care
            </a>
          </div>

        </div>
      </div>

    </div><!-- /Main Content -->
  </div><!-- /Flex -->
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
  <a href="<?= $baseUrl ?>/cart" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">shopping_cart</span><span class="text-[9px] font-semibold">Cart</span>
  </a>
  <button onclick="toggleMobileSidebar(true)" class="flex flex-col items-center text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">person</span><span class="text-[9px] font-semibold">Account</span>
  </button>
</div>

<!-- SUBMIT SUPPORT TICKET MODAL -->
<div id="ticket-modal" class="fixed inset-0 z-[80] items-center justify-center overflow-hidden hidden" style="display:none!important">
  <div onclick="closeTicketModal()" class="absolute inset-0 bg-black/50 cursor-pointer" style="backdrop-filter:blur(4px)"></div>
  <div class="bg-white rounded-2xl max-w-lg w-[92%] shadow-2xl relative z-10 p-6 scale-in">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
      <h3 class="font-bold text-base text-slate-900 flex items-center gap-2">
        <span class="material-icons text-purple-600">confirmation_number</span> Raise Support Ticket
      </h3>
      <button onclick="closeTicketModal()" class="text-slate-400 hover:text-slate-700 transition"><span class="material-icons">close</span></button>
    </div>

    <form id="ticket-form" onsubmit="submitSupportTicket(event)" class="space-y-4">
      
      <!-- Category Selector -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Issue Category <span class="text-red-500">*</span></label>
        <select id="tkt-category" class="form-input cursor-pointer font-medium">
          <option value="Orders & Delivery">📦 Orders &amp; Delivery Issue</option>
          <option value="Returns & Refunds">🔄 Returns &amp; Refunds</option>
          <option value="Payments & Coupons">💳 Payments &amp; Billing</option>
          <option value="Account & Security">👤 Account &amp; Login</option>
          <option value="Product & Warranty">🛡️ Product &amp; Warranty Claim</option>
          <option value="General Inquiry">❓ General Inquiry</option>
        </select>
      </div>

      <!-- Subject -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Subject <span class="text-red-500">*</span></label>
        <input type="text" id="tkt-subject" required placeholder="Brief summary of your issue..." class="form-input">
      </div>

      <!-- Detailed Message -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Detailed Description <span class="text-red-500">*</span></label>
        <textarea id="tkt-message" required rows="4" placeholder="Please describe your issue in detail (order number, item details, etc.)..." class="form-input resize-none"></textarea>
      </div>

      <!-- Action Buttons -->
      <div class="pt-3 border-t border-slate-100 flex gap-3 justify-end">
        <button type="button" onclick="closeTicketModal()" class="btn-outline">Cancel</button>
        <button type="submit" class="btn-primary" id="tkt-submit-btn">
          <span class="material-icons text-base">send</span> Submit Ticket
        </button>
      </div>
    </form>
  </div>
</div>

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
  setTimeout(()=>{ t.classList.replace('toast-in','toast-out'); setTimeout(()=>t.remove(),300); },3500);
}

// ============================================================
// ACCORDION TOGGLE
// ============================================================
function toggleFaq(header) {
  const item = header.parentElement;
  const isActive = item.classList.contains('active');
  document.querySelectorAll('.faq-item').forEach(el => el.classList.remove('active'));
  if (!isActive) item.classList.add('active');
}

// ============================================================
// SEARCH & FILTERS
// ============================================================
function searchHelp() {
  const q = (document.getElementById('help-search')?.value || '').trim().toLowerCase();
  const items = document.querySelectorAll('.faq-item');
  items.forEach(el => {
    const text = el.innerText.toLowerCase();
    const tags = el.getAttribute('data-tags') || '';
    if (!q || text.includes(q) || tags.toLowerCase().includes(q)) {
      el.classList.remove('hidden');
    } else {
      el.classList.add('hidden');
    }
  });
}

function quickFilter(keyword) {
  const input = document.getElementById('help-search');
  if (input) {
    input.value = keyword;
    searchHelp();
    input.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
}

function setFaqTab(cat) {
  ['all', 'orders', 'returns', 'payments', 'account'].forEach(c => {
    const btn = document.getElementById('tab-' + c);
    if (btn) {
      if (c === cat) btn.className = 'tab-btn active';
      else btn.className = 'tab-btn';
    }
  });

  const items = document.querySelectorAll('.faq-item');
  items.forEach(el => {
    const itemCat = el.getAttribute('data-cat');
    if (cat === 'all' || itemCat === cat) {
      el.classList.remove('hidden');
    } else {
      el.classList.add('hidden');
    }
  });
}

// ============================================================
// TICKET MODAL & AJAX
// ============================================================
function openTicketModal() {
  const modal = document.getElementById('ticket-modal');
  modal.style.display = 'flex';
  modal.classList.remove('hidden');
}

function closeTicketModal() {
  const modal = document.getElementById('ticket-modal');
  modal.style.display = 'none';
  modal.classList.add('hidden');
}

function submitSupportTicket(e) {
  e.preventDefault();
  const category = document.getElementById('tkt-category').value;
  const subject  = document.getElementById('tkt-subject').value.trim();
  const message  = document.getElementById('tkt-message').value.trim();

  if (!subject || !message) {
    showToast('Please fill in all required fields.', 'warning');
    return;
  }

  fetch(`${BASE_URL}/user/help/ticket`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ category, subject, message })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      closeTicketModal();
      document.getElementById('ticket-form').reset();
      showToast(data.message || 'Support ticket submitted!', 'success');
    } else {
      showToast(data.message || 'Failed to submit ticket.', 'error');
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}

function startLiveChat() {
  showToast('Connecting to 24/7 Live Support assistant...', 'info');
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeTicketModal();
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
