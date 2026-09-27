<?php
require_once __DIR__ . '/layouts/header.php';

$items     = $items     ?? [];
$wishlists = $wishlists ?? [];
$wishlist  = $wishlist  ?? [];
$totalItems = count($items);
?>

<style>
  body { background: #F1F5F9; }
  @keyframes fadeUp { from { opacity:0; transform:translateY(14px); } to { opacity:1; transform:translateY(0); } }
  .fade-up { animation: fadeUp .38s ease-out both; }
  @keyframes heartBeat { 0%{transform:scale(1)} 15%{transform:scale(1.3)} 30%{transform:scale(1)} 45%{transform:scale(1.15)} 60%{transform:scale(1)} }
  .heart-beat { animation: heartBeat .6s ease-in-out; }
  @keyframes slideInRight { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
  @keyframes slideOutRight { from{transform:translateX(0);opacity:1} to{transform:translateX(100%);opacity:0} }
  .toast-in { animation: slideInRight .35s ease-out forwards; }
  .toast-out { animation: slideOutRight .3s ease-in forwards; }
  @keyframes scaleIn { from{transform:scale(.95);opacity:0} to{transform:scale(1);opacity:1} }
  .scale-in { animation: scaleIn .2s ease-out both; }

  .wishlist-card {
    transition: transform .25s cubic-bezier(.4,0,.2,1), box-shadow .25s cubic-bezier(.4,0,.2,1), border-color .2s ease;
    border: 1.5px solid #E2E8F0;
    position: relative;
    overflow: hidden;
    background: #fff;
    border-radius: 16px;
  }
  .wishlist-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #FF006B, #8C30F5);
    opacity: 0;
    transition: opacity .25s ease;
  }
  .wishlist-card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px -8px rgba(15,23,42,.12); border-color: #FF006B; }
  .wishlist-card:hover::before { opacity: 1; }
  .wishlist-card.list-view { display: grid; grid-template-columns: 130px 1fr; border-radius: 14px; }
  .wishlist-card.list-view .card-img-wrap { border-radius: 12px 0 0 12px; border-right: 1px solid #E2E8F0; }

  .remove-btn { transition: all .2s ease; }
  .remove-btn:hover { color: #EF4444 !important; background: #FEF2F2 !important; }

  .oos-overlay { position:absolute; inset:0; background:rgba(255,255,255,.75); backdrop-filter:blur(1px); display:flex; align-items:center; justify-content:center; z-index:5; border-radius:12px; }

  .btn-primary { background:#2D82FF; color:#fff; font-weight:600; padding:9px 18px; border-radius:10px; transition:all .2s ease; display:inline-flex; align-items:center; gap:6px; font-size:12px; }
  .btn-primary:hover { background:#1D6FE0; transform:translateY(-1px); box-shadow:0 4px 12px rgba(45,130,255,.3); }
  .btn-outline { border:1.5px solid #E2E8F0; color:#475569; font-weight:500; padding:8px 16px; border-radius:10px; transition:all .2s ease; display:inline-flex; align-items:center; gap:6px; font-size:12px; background:#fff; }
  .btn-outline:hover { border-color:#2D82FF; color:#2D82FF; background:#F0F6FF; }

  .view-btn-active { background:#fff; color:#2D82FF; box-shadow:0 1px 4px rgba(15,23,42,.08); }
  .view-btn-idle { color:#94A3B8; }

  @media(max-width:640px){ .wishlist-card.list-view { grid-template-columns: 100px 1fr; } }
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
    <span class="text-slate-800 font-semibold">My Wishlist</span>
  </nav>

  <div class="flex gap-6">

    <!-- Sidebar -->
    <?php require_once __DIR__ . '/user/sidebar.php'; ?>

    <!-- Main Content -->
    <div class="flex-1 min-w-0 space-y-4">

      <!-- Page Header -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 fade-up">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-pink-50 flex items-center justify-center shrink-0">
              <span class="material-icons text-[#FF006B] text-xl">favorite</span>
            </div>
            <div>
              <h1 class="font-bold text-xl sm:text-2xl text-slate-900">My Wishlist</h1>
              <p class="text-sm text-slate-500 mt-0.5"><span id="wishlist-total-count"><?= $totalItems ?></span> saved item<?= $totalItems !== 1 ? 's' : '' ?></p>
            </div>
          </div>

          <div class="flex items-center gap-2 flex-wrap">
            <!-- Sort -->
            <select id="sort-select" onchange="renderWishlist()" class="btn-outline text-xs cursor-pointer">
              <option value="recent">Recently Added</option>
              <option value="price-low">Price: Low to High</option>
              <option value="price-high">Price: High to Low</option>
              <option value="discount">Biggest Discount</option>
              <option value="name">Name: A–Z</option>
            </select>

            <!-- View Toggle -->
            <div class="flex bg-slate-100 rounded-xl p-1 gap-0.5">
              <button onclick="setView('grid')" id="view-grid-btn" class="p-2 rounded-lg transition view-btn-active" title="Grid view">
                <span class="material-icons text-[18px]">grid_view</span>
              </button>
              <button onclick="setView('list')" id="view-list-btn" class="p-2 rounded-lg transition view-btn-idle" title="List view">
                <span class="material-icons text-[18px]">view_list</span>
              </button>
            </div>

            <!-- Add All to Cart -->
            <?php if ($totalItems > 0): ?>
              <button onclick="addAllToCart()" class="btn-primary">
                <span class="material-icons text-base">shopping_cart</span>
                <span class="hidden sm:inline">Add All to Cart</span>
                <span class="sm:hidden">All</span>
              </button>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Bulk Action Bar -->
      <?php if ($totalItems > 0): ?>
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm px-5 py-3 flex items-center justify-between gap-4 fade-up" style="animation-delay:.06s">
        <label class="flex items-center gap-2.5 cursor-pointer select-none">
          <input type="checkbox" id="select-all-cb" onchange="toggleSelectAll()" class="w-4 h-4 rounded accent-[#2D82FF] cursor-pointer">
          <span class="text-sm font-medium text-slate-700">Select All</span>
          <span id="selected-label" class="text-xs text-slate-400">(0 selected)</span>
        </label>
        <div class="flex gap-2">
          <button onclick="moveSelectedToCart()" class="btn-primary text-xs py-2 px-3">
            <span class="material-icons text-sm">shopping_cart</span> Move to Cart
          </button>
          <button onclick="removeSelected()" class="btn-outline text-xs py-2 px-3 !text-red-500 !border-red-200 hover:!bg-red-50">
            <span class="material-icons text-sm">delete_outline</span> Remove
          </button>
        </div>
      </div>
      <?php endif; ?>

      <!-- Wishlist Grid/List -->
      <div id="wishlist-grid" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 fade-up" style="animation-delay:.1s">
        <!-- Rendered by JS -->
      </div>

      <!-- Empty State -->
      <div id="empty-state" class="hidden fade-up">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-10 sm:p-16 text-center">
          <div class="w-24 h-24 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-5">
            <span class="material-icons text-5xl text-slate-300">favorite_border</span>
          </div>
          <h3 class="font-bold text-xl text-slate-800 mb-2">Your wishlist is empty</h3>
          <p class="text-sm text-slate-500 max-w-sm mx-auto mb-6 leading-relaxed">
            Save items you love! Browse our catalog and tap the heart icon to save products here.
          </p>
          <a href="<?= $baseUrl ?>/" class="btn-primary inline-flex text-sm px-8 py-3 rounded-xl">
            <span class="material-icons text-[18px]">explore</span> Start Shopping
          </a>
        </div>
      </div>

    </div>
  </div>
</main>

<!-- Mobile Bottom Nav -->
<div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-50 flex justify-around py-2 shadow-2xl">
  <a href="<?= $baseUrl ?>/" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">home</span><span class="text-[9px] font-semibold">Home</span>
  </a>
  <a href="<?= $baseUrl ?>/user/orders" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">shopping_bag</span><span class="text-[9px] font-semibold">Orders</span>
  </a>
  <a href="<?= $baseUrl ?>/wishlist" class="flex flex-col items-center text-[#FF006B] py-1">
    <span class="material-icons text-2xl">favorite</span><span class="text-[9px] font-semibold">Wishlist</span>
  </a>
  <a href="<?= $baseUrl ?>/cart" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">shopping_cart</span><span class="text-[9px] font-semibold">Cart</span>
  </a>
  <button onclick="toggleMobileSidebar(true)" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">person</span><span class="text-[9px] font-semibold">Account</span>
  </button>
</div>

<script>
// ============================================================
// WISHLIST DATA (from PHP)
// ============================================================
let ITEMS = <?= json_encode(array_values($items)) ?>;
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';

let viewMode = 'grid';
let selected = new Set();

// ============================================================
// TOAST
// ============================================================
function showToast(msg, type='success') {
  const c = document.getElementById('toast-container');
  const cls = {success:'bg-emerald-600', error:'bg-red-500', info:'bg-[#2D82FF]', warning:'bg-amber-500'};
  const ico = {success:'check_circle', error:'error', info:'info', warning:'warning'};
  const t = document.createElement('div');
  t.className = `toast-in pointer-events-auto flex items-center gap-3 ${cls[type]||cls.info} text-white px-5 py-3.5 rounded-xl shadow-2xl text-sm font-medium`;
  t.innerHTML = `<span class="material-icons text-lg">${ico[type]||'info'}</span><span class="flex-1">${msg}</span>`;
  c.appendChild(t);
  setTimeout(() => { t.classList.replace('toast-in','toast-out'); setTimeout(() => t.remove(), 300); }, 3500);
}

// ============================================================
// VIEW MODE
// ============================================================
function setView(mode) {
  viewMode = mode;
  document.getElementById('view-grid-btn').className = `p-2 rounded-lg transition ${mode==='grid'?'view-btn-active':'view-btn-idle'}`;
  document.getElementById('view-list-btn').className = `p-2 rounded-lg transition ${mode==='list'?'view-btn-active':'view-btn-idle'}`;
  renderWishlist();
}

// ============================================================
// SELECT ALL / BULK
// ============================================================
function toggleSelectAll() {
  const cb = document.getElementById('select-all-cb');
  if (cb.checked) { ITEMS.forEach(i => selected.add(i.encrypted_wishlist_item_id || i.wishlist_item_id)); }
  else { selected.clear(); }
  updateSelectedLabel();
  renderWishlist();
}

function toggleItem(id) {
  if (selected.has(id)) selected.delete(id);
  else selected.add(id);
  updateSelectedLabel();
  const allCb = document.getElementById('select-all-cb');
  if (allCb) allCb.checked = ITEMS.length > 0 && selected.size === ITEMS.length;
  renderWishlist();
}

function updateSelectedLabel() {
  const el = document.getElementById('selected-label');
  if (el) el.textContent = `(${selected.size} selected)`;
}

// ============================================================
// SORTED LIST
// ============================================================
function getSorted() {
  let list = [...ITEMS];
  const sort = document.getElementById('sort-select')?.value || 'recent';
  switch(sort) {
    case 'price-low':  list.sort((a,b) => a.sale_price - b.sale_price); break;
    case 'price-high': list.sort((a,b) => b.sale_price - a.sale_price); break;
    case 'discount':   list.sort((a,b) => b.discount_pct - a.discount_pct); break;
    case 'name':       list.sort((a,b) => a.name.localeCompare(b.name)); break;
    default:           list.sort((a,b) => new Date(b.added_at) - new Date(a.added_at));
  }
  return list;
}

// ============================================================
// RENDER
// ============================================================
function renderWishlist() {
  const grid   = document.getElementById('wishlist-grid');
  const empty  = document.getElementById('empty-state');
  const countEl= document.getElementById('wishlist-total-count');

  if (countEl) countEl.textContent = ITEMS.length;

  if (ITEMS.length === 0) {
    grid.innerHTML = '';
    grid.classList.add('hidden');
    empty.classList.remove('hidden');
    return;
  }
  empty.classList.add('hidden');
  grid.classList.remove('hidden');

  const isGrid = viewMode === 'grid';
  grid.className = isGrid
    ? 'grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 fade-up'
    : 'flex flex-col gap-3 fade-up';

  const list = getSorted();
  grid.innerHTML = list.map((item, idx) => {
    const encItemId = item.encrypted_wishlist_item_id || item.wishlist_item_id;
    const encProdId = item.encrypted_product_id || item.product_id;
    const isSelected = selected.has(encItemId);
    const isOOS = !item.is_in_stock;
    const disc = item.discount_pct;
    const img = item.image_url || 'https://via.placeholder.com/200x200?text=No+Image';
    const stars = renderStars(parseFloat(item.average_rating || 0));
    const price = `₹${parseFloat(item.sale_price).toLocaleString('en-IN', {minimumFractionDigits:2})}`;
    const base  = disc > 0 ? `₹${parseFloat(item.base_price).toLocaleString('en-IN', {minimumFractionDigits:2})}` : '';
    const delay = Math.min(idx * 0.04, 0.3);

    if (isGrid) {
      return `
      <div class="wishlist-card scale-in" style="animation-delay:${delay}s">
        ${isOOS ? '<div class="oos-overlay"><span class="bg-slate-800 text-white text-xs font-bold px-3 py-1 rounded-full">Out of Stock</span></div>' : ''}
        <!-- Selection Checkbox -->
        <div class="absolute top-2 left-2 z-10">
          <input type="checkbox" class="w-4 h-4 rounded accent-[#2D82FF] cursor-pointer" ${isSelected?'checked':''} onchange="toggleItem('${encItemId}')">
        </div>
        <!-- Remove Button -->
        <button onclick="removeItem('${encItemId}')"
          class="remove-btn absolute top-2 right-2 z-10 w-8 h-8 rounded-full bg-white/90 text-slate-400 flex items-center justify-center shadow hover:!text-red-500 hover:!bg-red-50 transition"
          title="Remove from wishlist">
          <span class="material-icons text-base">favorite</span>
        </button>
        <!-- Discount Badge -->
        ${disc > 0 ? `<div class="absolute top-2 right-12 z-10"><span class="bg-[#FF006B] text-white text-[9px] font-extrabold px-2 py-0.5 rounded-full">${disc}% OFF</span></div>` : ''}
        <!-- Image -->
        <div class="card-img-wrap bg-slate-50 flex items-center justify-center overflow-hidden" style="height:160px; border-radius:12px 12px 0 0">
          <a href="${BASE_URL}/product/${encProdId}">
            <img src="${img}" alt="${item.name}" class="max-h-full max-w-full object-contain p-3 hover:scale-105 transition-transform duration-300" loading="lazy">
          </a>
        </div>
        <!-- Info -->
        <div class="p-3.5 space-y-2">
          <a href="${BASE_URL}/product/${encProdId}" class="block">
            <p class="text-xs font-semibold text-slate-800 line-clamp-2 leading-snug hover:text-[#2D82FF] transition">${item.name}</p>
          </a>
          <div class="flex items-center gap-1">
            ${stars}
            <span class="text-[10px] text-slate-400">(${item.review_count||0})</span>
          </div>
          <div class="flex items-baseline gap-1.5">
            <span class="font-extrabold text-sm text-slate-900">${price}</span>
            ${disc > 0 ? `<span class="text-xs line-through text-slate-400">${base}</span>` : ''}
          </div>
          <div class="flex gap-1.5">
            <button onclick="moveToCart('${encItemId}')" ${isOOS?'disabled':''}
              class="btn-primary flex-1 justify-center text-[11px] py-2 ${isOOS?'opacity-50 cursor-not-allowed':''}">
              <span class="material-icons text-sm">shopping_cart</span>
              ${isOOS ? 'Out of Stock' : 'Add to Cart'}
            </button>
          </div>
        </div>
      </div>`;
    } else {
      // List view
      return `
      <div class="wishlist-card list-view scale-in" style="animation-delay:${delay}s">
        ${isOOS ? '<div class="oos-overlay rounded-l-xl"><span class="bg-slate-800 text-white text-xs font-bold px-3 py-1 rounded-full">OOS</span></div>' : ''}
        <!-- Image -->
        <div class="card-img-wrap bg-slate-50 flex items-center justify-center overflow-hidden relative" style="min-height:120px">
          <input type="checkbox" class="absolute top-2 left-2 w-4 h-4 rounded accent-[#2D82FF] cursor-pointer z-10" ${isSelected?'checked':''} onchange="toggleItem('${encItemId}')">
          ${disc > 0 ? `<span class="absolute top-2 right-2 bg-[#FF006B] text-white text-[9px] font-extrabold px-2 py-0.5 rounded-full z-10">${disc}%</span>` : ''}
          <a href="${BASE_URL}/product/${encProdId}">
            <img src="${img}" alt="${item.name}" class="max-h-full max-w-full object-contain p-3 hover:scale-105 transition-transform" loading="lazy">
          </a>
        </div>
        <!-- Info -->
        <div class="p-4 flex flex-col justify-between gap-2">
          <div class="space-y-1">
            <a href="${BASE_URL}/product/${encProdId}" class="text-sm font-semibold text-slate-800 line-clamp-2 hover:text-[#2D82FF] transition block">${item.name}</a>
            <div class="flex items-center gap-1">${stars}<span class="text-[10px] text-slate-400">(${item.review_count||0})</span></div>
          </div>
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-extrabold text-base text-slate-900">${price}</span>
            ${disc > 0 ? `<span class="text-xs line-through text-slate-400">${base}</span><span class="text-[10px] font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded">${disc}% off</span>` : ''}
          </div>
          <div class="flex gap-2 mt-1">
            <button onclick="moveToCart('${encItemId}')" ${isOOS?'disabled':''}
              class="btn-primary text-xs py-2 px-3 ${isOOS?'opacity-50 cursor-not-allowed':''}">
              <span class="material-icons text-sm">shopping_cart</span> ${isOOS ? 'Out of Stock' : 'Add to Cart'}
            </button>
            <button onclick="removeItem('${encItemId}')" class="remove-btn btn-outline text-xs py-2 px-3 text-red-500 border-red-200">
              <span class="material-icons text-sm">delete_outline</span>
            </button>
          </div>
        </div>
      </div>`;
    }
  }).join('');
}

function renderStars(rating) {
  let html = '<div class="flex items-center gap-0.5">';
  for (let i = 1; i <= 5; i++) {
    const cls = i <= Math.round(rating) ? 'text-amber-400' : 'text-slate-200';
    html += `<span class="material-icons ${cls}" style="font-size:11px">star</span>`;
  }
  html += '</div>';
  return html;
}

// ============================================================
// REMOVE ITEM
// ============================================================
function removeItem(itemId) {
  fetch(`${BASE_URL}/wishlist/remove`, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({item_id: itemId})
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      ITEMS = ITEMS.filter(i => i.encrypted_wishlist_item_id !== itemId && i.wishlist_item_id != itemId);
      selected.delete(itemId);
      updateSelectedLabel();
      renderWishlist();
      // Update header badge
      document.querySelectorAll('.wishlist-badge-count').forEach(el => el.textContent = data.wishlist_count || 0);
      showToast('Removed from wishlist.', 'info');
    } else {
      showToast(data.message || 'Failed to remove.', 'error');
    }
  })
  .catch(() => showToast('Network error.', 'error'));
}

// ============================================================
// MOVE TO CART
// ============================================================
function moveToCart(itemId) {
  fetch(`${BASE_URL}/wishlist/move-to-cart`, {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({item_id: itemId})
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      ITEMS = ITEMS.filter(i => i.encrypted_wishlist_item_id !== itemId && i.wishlist_item_id != itemId);
      selected.delete(itemId);
      updateSelectedLabel();
      renderWishlist();
      // Update cart badge in header
      document.querySelectorAll('.cart-badge-count').forEach(el => el.textContent = data.cart_count || 0);
      showToast('Moved to cart!', 'success');
    } else {
      showToast(data.message || 'Failed to move.', 'error');
    }
  })
  .catch(() => showToast('Network error.', 'error'));
}

// ============================================================
// ADD ALL TO CART
// ============================================================
function addAllToCart() {
  const inStock = ITEMS.filter(i => i.is_in_stock);
  if (inStock.length === 0) { showToast('All items are out of stock.', 'warning'); return; }
  let done = 0;
  inStock.forEach(item => {
    fetch(`${BASE_URL}/wishlist/move-to-cart`, {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({item_id: item.wishlist_item_id})
    })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        ITEMS = ITEMS.filter(i => i.wishlist_item_id !== item.wishlist_item_id);
        done++;
        if (done === inStock.length) {
          selected.clear();
          updateSelectedLabel();
          renderWishlist();
          document.querySelectorAll('.cart-badge-count').forEach(el => el.textContent = data.cart_count || 0);
          showToast(`${done} item${done!==1?'s':''} moved to cart!`, 'success');
        }
      }
    });
  });
}

// ============================================================
// BULK REMOVE
// ============================================================
function removeSelected() {
  if (selected.size === 0) { showToast('No items selected.', 'warning'); return; }
  [...selected].forEach(id => removeItem(id));
}

// ============================================================
// BULK MOVE TO CART
// ============================================================
function moveSelectedToCart() {
  if (selected.size === 0) { showToast('No items selected.', 'warning'); return; }
  [...selected].forEach(id => moveToCart(id));
}

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => renderWishlist());
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
