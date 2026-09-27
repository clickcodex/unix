<?php
require_once __DIR__ . '/../layouts/header.php';

$user    = $user    ?? [];
$reviews = $reviews ?? [];
$baseUrl = $baseUrl ?? (defined('BASE_URL') ? BASE_URL : '');
$total   = count($reviews);

$avgRating = $total > 0 ? round(array_sum(array_column($reviews, 'rating')) / $total, 1) : 0;
$approved  = count(array_filter($reviews, fn($r) => ($r['status'] ?? '') === 'approved'));
$verified  = count(array_filter($reviews, fn($r) => !empty($r['is_verified_purchase'])));
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

  .review-card {
    border: 1.5px solid #E2E8F0; border-radius: 16px; background: #fff;
    transition: all .25s ease; position: relative; overflow: hidden;
  }
  .review-card:hover { border-color: #2D82FF; box-shadow: 0 12px 24px -6px rgba(15,23,42,.08); }

  .stat-card { border: 1.5px solid #E2E8F0; border-radius: 16px; background: #fff; transition: all .2s ease; }
  .stat-card:hover { border-color: #2D82FF; transform: translateY(-2px); }

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

  .star-select span { cursor: pointer; transition: transform .15s ease, color .15s ease; }
  .star-select span:hover { transform: scale(1.25); }

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
    <span class="text-slate-800 font-semibold">My Reviews</span>
  </nav>

  <div class="flex gap-6">

    <!-- Sidebar -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 min-w-0 space-y-4">

      <!-- Mobile Sidebar Toggle Button -->
      <button onclick="toggleMobileSidebar(true)" class="lg:hidden flex items-center justify-between w-full bg-white border border-slate-200 p-3.5 rounded-2xl shadow-xs text-xs font-bold text-slate-800 hover:bg-slate-50 transition">
        <div class="flex items-center gap-2">
          <span class="material-icons text-[#2D82FF] text-lg">menu</span>
          <span>Account Navigation Menu</span>
        </div>
        <span class="bg-[#2D82FF]/10 text-[#2D82FF] text-[10px] font-extrabold px-2.5 py-1 rounded-lg">MENU</span>
      </button>

      <!-- Page Header -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 fade-up">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
              <span class="material-icons text-purple-600 text-xl">rate_review</span>
            </div>
            <div>
              <h1 class="font-bold text-xl sm:text-2xl text-slate-900">My Reviews &amp; Ratings</h1>
              <p class="text-sm text-slate-400 mt-0.5"><span id="review-count"><?= $total ?></span> product review<?= $total !== 1 ? 's' : '' ?> published</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Bar -->
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 fade-up" style="animation-delay:.06s">
        <div class="stat-card p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
            <span class="material-icons text-amber-500 text-lg">star</span>
          </div>
          <div>
            <p class="text-lg font-extrabold text-slate-900 font-heading"><?= $avgRating > 0 ? $avgRating . ' ★' : 'N/A' ?></p>
            <p class="text-[11px] text-slate-500 font-medium">Avg Rating Given</p>
          </div>
        </div>
        <div class="stat-card p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
            <span class="material-icons text-[#2D82FF] text-lg">rate_review</span>
          </div>
          <div>
            <p class="text-lg font-extrabold text-slate-900 font-heading"><?= $total ?></p>
            <p class="text-[11px] text-slate-500 font-medium">Total Reviews</p>
          </div>
        </div>
        <div class="stat-card p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
            <span class="material-icons text-emerald-600 text-lg">verified_user</span>
          </div>
          <div>
            <p class="text-lg font-extrabold text-slate-900 font-heading"><?= $verified ?></p>
            <p class="text-[11px] text-slate-500 font-medium">Verified Purchases</p>
          </div>
        </div>
        <div class="stat-card p-4 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center shrink-0">
            <span class="material-icons text-purple-600 text-lg">check_circle</span>
          </div>
          <div>
            <p class="text-lg font-extrabold text-slate-900 font-heading"><?= $approved ?></p>
            <p class="text-[11px] text-slate-500 font-medium">Approved Reviews</p>
          </div>
        </div>
      </div>

      <!-- Search & Filter Bar -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-col sm:flex-row gap-3 fade-up" style="animation-delay:.08s">
        <div class="relative flex-1">
          <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">search</span>
          <input type="text" id="review-search" oninput="filterReviews()" placeholder="Search by product name or review title..."
                 class="w-full pl-9 pr-4 py-2 border border-slate-200 rounded-xl text-xs outline-none focus:border-[#2D82FF]">
        </div>
        <select id="rating-filter" onchange="filterReviews()" class="border border-slate-200 rounded-xl text-xs px-3 py-2 cursor-pointer outline-none bg-white font-medium">
          <option value="all">All Ratings</option>
          <option value="5">5 Stars ★★★★★</option>
          <option value="4">4 Stars ★★★★</option>
          <option value="3">3 Stars ★★★</option>
          <option value="2">2 Stars ★★</option>
          <option value="1">1 Star ★</option>
        </select>
      </div>

      <!-- Reviews Container -->
      <div id="reviews-list" class="space-y-4 fade-up" style="animation-delay:.12s">
        <!-- Rendered via JS -->
      </div>

      <!-- Empty State -->
      <div id="empty-state" class="hidden fade-up">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-10 sm:p-16 text-center">
          <div class="w-24 h-24 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-5">
            <span class="material-icons text-5xl text-slate-300">rate_review</span>
          </div>
          <h3 class="font-bold text-xl text-slate-800 mb-2">No reviews written yet</h3>
          <p class="text-sm text-slate-500 max-w-sm mx-auto mb-6 leading-relaxed">Share your honest feedback on products you've purchased.</p>
          <a href="<?= $baseUrl ?>/user/orders" class="btn-primary text-sm px-7 py-3 rounded-xl">
            <span class="material-icons text-[18px]">shopping_bag</span> Review Past Orders
          </a>
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

<!-- EDIT REVIEW MODAL -->
<div id="review-modal" class="fixed inset-0 z-[80] items-center justify-center overflow-hidden hidden" style="display:none!important">
  <div onclick="closeReviewModal()" class="absolute inset-0 bg-black/50 cursor-pointer" style="backdrop-filter:blur(4px)"></div>
  <div class="bg-white rounded-2xl max-w-lg w-[92%] shadow-2xl relative z-10 p-6 scale-in">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
      <h3 class="font-bold text-base text-slate-900 flex items-center gap-2">
        <span class="material-icons text-amber-500">rate_review</span> Edit Review
      </h3>
      <button onclick="closeReviewModal()" class="text-slate-400 hover:text-slate-700 transition"><span class="material-icons">close</span></button>
    </div>

    <form id="review-form" onsubmit="saveReview(event)" class="space-y-4">
      <input type="hidden" id="rev-enc-id" value="">
      <input type="hidden" id="rev-rating" value="5">

      <!-- Product Info Header -->
      <div id="modal-prod-info" class="flex items-center gap-3 bg-slate-50 p-3 rounded-xl border border-slate-100">
        <div class="w-12 h-12 rounded-lg bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
          <img id="modal-prod-img" src="" alt="" class="max-h-full max-w-full object-contain p-1">
        </div>
        <p id="modal-prod-name" class="text-xs font-semibold text-slate-800 line-clamp-2"></p>
      </div>

      <!-- Star Rating Picker -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Your Rating</label>
        <div class="star-select flex items-center gap-1" id="star-picker">
          <?php for($i=1; $i<=5; $i++): ?>
            <span onclick="setRating(<?= $i ?>)" id="star-<?= $i ?>" class="material-icons text-2xl text-amber-400">star</span>
          <?php endfor; ?>
          <span id="rating-label" class="ml-2 text-xs font-bold text-slate-700">5.0 / 5</span>
        </div>
      </div>

      <!-- Review Title -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Review Title <span class="text-red-500">*</span></label>
        <input type="text" id="rev-title" required placeholder="e.g. Amazing quality and fast delivery!" class="form-input">
      </div>

      <!-- Review Body -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Review Details <span class="text-red-500">*</span></label>
        <textarea id="rev-body" required rows="4" placeholder="Write your detailed experience with the product..." class="form-input resize-none"></textarea>
      </div>

      <!-- Action Buttons -->
      <div class="pt-3 border-t border-slate-100 flex gap-3 justify-end">
        <button type="button" onclick="closeReviewModal()" class="btn-outline">Cancel</button>
        <button type="submit" class="btn-primary">
          <span class="material-icons text-base">save</span> Update Review
        </button>
      </div>
    </form>
  </div>
</div>

<!-- CONFIRM DELETE MODAL -->
<div id="delete-modal" class="fixed inset-0 z-[80] items-center justify-center overflow-hidden hidden" style="display:none!important">
  <div onclick="closeDeleteModal()" class="absolute inset-0 bg-black/50 cursor-pointer" style="backdrop-filter:blur(4px)"></div>
  <div class="bg-white rounded-2xl max-w-sm w-[90%] shadow-2xl relative z-10 p-6 scale-in">
    <button onclick="closeDeleteModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 transition"><span class="material-icons">close</span></button>
    <div class="text-center">
      <div class="w-14 h-14 mx-auto rounded-full bg-red-50 flex items-center justify-center mb-4">
        <span class="material-icons text-red-500 text-2xl">delete_outline</span>
      </div>
      <h3 class="font-bold text-lg text-slate-800 mb-1">Delete Review?</h3>
      <p class="text-sm text-slate-500 mb-5">Your review and rating will be removed.</p>
      <div class="flex gap-3">
        <button onclick="closeDeleteModal()" class="flex-1 border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold py-2.5 rounded-xl transition text-sm">Cancel</button>
        <button id="confirm-del-btn" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-2.5 rounded-xl transition text-sm">Delete</button>
      </div>
    </div>
  </div>
</div>

<script>
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';
let REVIEWS = <?= json_encode(array_values($reviews)) ?>;
let pendingDeleteId = null;

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

function renderStarsHTML(rating) {
  let html = '<div class="flex items-center gap-0.5">';
  for (let i = 1; i <= 5; i++) {
    const cls = i <= Math.round(rating) ? 'text-amber-400' : 'text-slate-200';
    html += `<span class="material-icons ${cls}" style="font-size:16px">star</span>`;
  }
  html += '</div>';
  return html;
}

// ============================================================
// RENDER REVIEWS
// ============================================================
function renderReviews(list = REVIEWS) {
  const container = document.getElementById('reviews-list');
  const empty = document.getElementById('empty-state');
  const countEl = document.getElementById('review-count');

  if (countEl) countEl.textContent = REVIEWS.length;

  if (REVIEWS.length === 0) {
    container.innerHTML = '';
    container.classList.add('hidden');
    empty.classList.remove('hidden');
    return;
  }
  empty.classList.add('hidden');
  container.classList.remove('hidden');

  container.innerHTML = list.map(item => {
    const encId = item.encrypted_id || item.review_id;
    const encProdId = item.encrypted_product_id || item.product_id;
    const img = item.product_image || 'https://via.placeholder.com/150';
    const stars = renderStarsHTML(parseInt(item.rating || 5));
    const isVerified = parseInt(item.is_verified_purchase || 0) === 1;

    const statusBadge = {
      approved: '<span class="bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1"><span class="material-icons text-xs">check_circle</span> Approved</span>',
      pending:  '<span class="bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1"><span class="material-icons text-xs">schedule</span> Pending</span>',
      rejected: '<span class="bg-red-50 text-red-700 border border-red-200 text-[10px] font-bold px-2.5 py-0.5 rounded-full flex items-center gap-1"><span class="material-icons text-xs">cancel</span> Rejected</span>'
    }[item.status] || '';

    const dateStr = item.created_at ? new Date(item.created_at).toLocaleDateString('en-IN', {day:'numeric', month:'short', year:'numeric'}) : '';

    return `
    <div class="review-card p-5 scale-in" id="card-${encId}">
      <div class="flex flex-col sm:flex-row sm:items-start gap-4">
        <!-- Product Thumbnail -->
        <a href="${BASE_URL}/product/${encProdId}" class="w-16 h-16 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-center overflow-hidden shrink-0 hover:border-[#2D82FF] transition">
          <img src="${img}" alt="${item.product_name}" class="max-h-full max-w-full object-contain p-1.5" loading="lazy">
        </a>

        <!-- Review Content -->
        <div class="flex-1 min-w-0 space-y-2">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <a href="${BASE_URL}/product/${encProdId}" class="text-sm font-bold text-slate-800 line-clamp-1 hover:text-[#2D82FF] transition">
              ${item.product_name || 'Product'}
            </a>
            <div class="flex items-center gap-2">
              ${statusBadge}
              ${isVerified ? '<span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">✓ Verified Purchase</span>' : ''}
            </div>
          </div>

          <!-- Stars + Date -->
          <div class="flex items-center gap-2">
            ${stars}
            <span class="text-xs font-bold text-slate-700">${item.rating}.0</span>
            <span class="text-slate-300">·</span>
            <span class="text-xs text-slate-400">${dateStr}</span>
          </div>

          <!-- Title & Body -->
          <h4 class="font-bold text-sm text-slate-900">${item.title || ''}</h4>
          <p class="text-xs text-slate-600 leading-relaxed">${item.body || ''}</p>

          <!-- Footer: Helpful count + Actions -->
          <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs text-slate-400">
            <span class="flex items-center gap-1">
              <span class="material-icons text-sm text-slate-400">thumb_up</span> ${item.helpful_count || 0} found helpful
            </span>

            <div class="flex items-center gap-2">
              <button onclick="editReview('${encId}')" class="btn-outline py-1 px-2.5">
                <span class="material-icons text-sm">edit</span> Edit
              </button>
              <button onclick="confirmDelete('${encId}')" class="btn-outline py-1 px-2.5 !text-red-500 hover:!bg-red-50 hover:!border-red-200">
                <span class="material-icons text-sm">delete_outline</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>`;
  }).join('');
}

function filterReviews() {
  const q = (document.getElementById('review-search')?.value || '').trim().toLowerCase();
  const star = document.getElementById('rating-filter')?.value || 'all';

  let filtered = [...REVIEWS];

  if (q) {
    filtered = filtered.filter(r =>
      (r.product_name || '').toLowerCase().includes(q) ||
      (r.title || '').toLowerCase().includes(q) ||
      (r.body || '').toLowerCase().includes(q)
    );
  }

  if (star !== 'all') {
    filtered = filtered.filter(r => parseInt(r.rating) === parseInt(star));
  }

  renderReviews(filtered);
}

// ============================================================
// STAR RATING PICKER
// ============================================================
function setRating(val) {
  document.getElementById('rev-rating').value = val;
  document.getElementById('rating-label').textContent = val + '.0 / 5';
  for (let i = 1; i <= 5; i++) {
    const star = document.getElementById('star-' + i);
    if (star) {
      if (i <= val) star.className = 'material-icons text-2xl text-amber-400';
      else star.className = 'material-icons text-2xl text-slate-200';
    }
  }
}

// ============================================================
// MODAL CONTROLS
// ============================================================
function editReview(encId) {
  const item = REVIEWS.find(r => (r.encrypted_id || r.review_id) == encId);
  if (!item) return;

  document.getElementById('rev-enc-id').value = item.encrypted_id || item.review_id;
  document.getElementById('modal-prod-name').textContent = item.product_name || 'Product';
  document.getElementById('modal-prod-img').src = item.product_image || 'https://via.placeholder.com/150';
  document.getElementById('rev-title').value = item.title || '';
  document.getElementById('rev-body').value  = item.body || '';
  setRating(parseInt(item.rating || 5));

  const modal = document.getElementById('review-modal');
  modal.style.display = 'flex';
  modal.classList.remove('hidden');
}

function closeReviewModal() {
  const modal = document.getElementById('review-modal');
  modal.style.display = 'none';
  modal.classList.add('hidden');
}

// ============================================================
// AJAX UPDATE REVIEW
// ============================================================
function saveReview(e) {
  e.preventDefault();
  const encId = document.getElementById('rev-enc-id').value;

  const payload = {
    review_id: encId,
    rating:    parseInt(document.getElementById('rev-rating').value || 5),
    title:     document.getElementById('rev-title').value.trim(),
    body:      document.getElementById('rev-body').value.trim()
  };

  fetch(`${BASE_URL}/user/reviews/update`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      closeReviewModal();
      showToast(data.message || 'Review updated!', 'success');
      setTimeout(() => location.reload(), 600);
    } else {
      showToast(data.message || 'Failed to update review.', 'error');
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}

// ============================================================
// AJAX DELETE REVIEW
// ============================================================
function confirmDelete(encId) {
  pendingDeleteId = encId;
  const modal = document.getElementById('delete-modal');
  document.getElementById('confirm-del-btn').onclick = executeDelete;
  modal.style.display = 'flex';
  modal.classList.remove('hidden');
}

function closeDeleteModal() {
  const modal = document.getElementById('delete-modal');
  modal.style.display = 'none';
  modal.classList.add('hidden');
}

function executeDelete() {
  closeDeleteModal();
  fetch(`${BASE_URL}/user/reviews/delete`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ review_id: pendingDeleteId })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      REVIEWS = REVIEWS.filter(r => (r.encrypted_id || r.review_id) != pendingDeleteId);
      renderReviews();
      showToast('Review deleted.', 'info');
    } else {
      showToast(data.message || 'Failed to delete review.', 'error');
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  renderReviews();
});

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeReviewModal();
    closeDeleteModal();
  }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
