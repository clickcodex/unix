<?php
require_once __DIR__ . '/layouts/header.php';

$category  = $category  ?? [];
$products  = $category['products'] ?? [];
$children  = $category['children'] ?? [];
$parent    = $category['parent']   ?? null;
$baseUrl   = $baseUrl   ?? (defined('BASE_URL') ? BASE_URL : '');
$userWpids = $userWishlistProductIds ?? [];
$catIcon   = !empty($category['icon']) ? $category['icon'] : 'category';
?>

<style>
  body { background: #F1F5F9; overflow-x: hidden; }
  @keyframes fadeUp { from{opacity:0;transform:translateY(14px)} to{opacity:1;transform:translateY(0)} }
  .fade-up { animation: fadeUp .4s ease-out both; }

  /* ── Responsive Grid & Cards Safety ── */
  .product-card {
    min-width: 0;
    max-width: 100%;
    width: 100%;
    box-sizing: border-box;
  }
  #products-container {
    min-width: 0;
    width: 100%;
  }

  /* ── Category Hero ── */
  .category-hero {
    background: linear-gradient(135deg, #0F172A 0%, #1E293B 45%, #2D82FF 100%);
    border-radius: 16px; color: #fff; position: relative; overflow: hidden;
  }
  @media (min-width: 640px) {
    .category-hero { border-radius: 20px; }
  }
  .category-hero::before {
    content:''; position:absolute; width:320px; height:320px;
    right:-80px; top:-80px;
    background: radial-gradient(circle, rgba(45,130,255,.25) 0%, transparent 70%);
    border-radius:50%; pointer-events:none;
  }
  .category-hero::after {
    content:''; position:absolute; width:200px; height:200px;
    left:-40px; bottom:-60px;
    background: radial-gradient(circle, rgba(255,81,0,.15) 0%, transparent 70%);
    border-radius:50%; pointer-events:none;
  }

  /* ── Subcategory Pills ── */
  .subcat-pill {
    padding: 6px 14px; border-radius: 10px; border: 1.5px solid #E2E8F0;
    font-size: 11px; font-weight: 600; color: #475569; background: #fff;
    transition: all .15s ease; white-space: nowrap; cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px;
  }
  @media (min-width: 640px) {
    .subcat-pill { padding: 7px 16px; font-size: 12px; gap: 6px; }
  }
  .subcat-pill:hover, .subcat-pill-active {
    border-color: #2D82FF; background: #EFF6FF; color: #2D82FF;
  }
  .subcat-pill-active { font-weight: 700; }

  /* ── No Image Placeholder ── */
  .no-img-placeholder {
    width: 100%; height: 100%;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #F8FAFC 0%, #E2E8F0 100%);
    border-radius: 12px;
  }

  /* ── Toast ── */
  .toast-in { animation: slideInRight .35s ease-out forwards; }
  @keyframes slideInRight { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
</style>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-5 right-5 z-[200] flex flex-col gap-2.5 pointer-events-none" style="max-width:380px"></div>

<main class="max-w-7xl mx-auto px-2.5 sm:px-4 py-3 sm:py-6 pb-20 md:pb-6 space-y-3 sm:space-y-6 w-full overflow-hidden">

  <!-- Breadcrumb -->
  <nav class="flex items-center gap-1.5 sm:gap-2 text-[11px] sm:text-xs text-slate-500 fade-up truncate">
    <a href="<?= $baseUrl ?>/" class="hover:text-cc-blue transition shrink-0">Home</a>
    <span class="material-icons text-[12px] sm:text-[13px] shrink-0">chevron_right</span>
    <?php if ($parent): ?>
      <a href="<?= $baseUrl ?>/category/<?= htmlspecialchars($parent['slug']) ?>" class="hover:text-cc-blue transition truncate"><?= htmlspecialchars($parent['name']) ?></a>
      <span class="material-icons text-[12px] sm:text-[13px] shrink-0">chevron_right</span>
    <?php endif; ?>
    <span class="text-slate-800 font-semibold truncate"><?= htmlspecialchars($category['name']) ?></span>
  </nav>

  <!-- ════════════  Category Hero Banner  ════════════ -->
  <div class="category-hero p-4 sm:p-8 md:p-10 fade-up" style="animation-delay:.04s">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 sm:gap-6 relative z-10">
      <div class="space-y-2 sm:space-y-3 max-w-2xl min-w-0">
        <div class="flex items-center gap-2.5 sm:gap-3">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl sm:rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center shrink-0">
            <span class="material-icons text-xl sm:text-2xl text-cc-yellow"><?= $catIcon ?></span>
          </div>
          <div class="min-w-0">
            <span class="text-[9px] sm:text-[10px] font-bold uppercase tracking-widest text-blue-300">Category</span>
            <h1 class="text-lg sm:text-3xl md:text-4xl font-extrabold font-heading text-white leading-tight truncate"><?= htmlspecialchars($category['name']) ?></h1>
          </div>
        </div>

        <?php if (!empty($category['description'])): ?>
          <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-lg line-clamp-2 sm:line-clamp-none"><?= htmlspecialchars($category['description']) ?></p>
        <?php endif; ?>

        <div class="flex flex-wrap items-center gap-2 sm:gap-3 pt-0.5">
          <span class="bg-white/15 backdrop-blur-sm text-white text-[11px] sm:text-xs font-semibold px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-full border border-white/20 inline-flex items-center gap-1.5">
            <span class="material-icons text-xs sm:text-sm text-cc-yellow">inventory_2</span>
            <strong><?= count($products) ?></strong> Products Available
          </span>
          <?php if (!empty($children)): ?>
            <span class="bg-white/15 backdrop-blur-sm text-white text-[11px] sm:text-xs font-semibold px-2.5 sm:px-3.5 py-1 sm:py-1.5 rounded-full border border-white/20 inline-flex items-center gap-1.5">
              <span class="material-icons text-xs sm:text-sm text-blue-300">grid_view</span>
              <strong><?= count($children) ?></strong> Subcategories
            </span>
          <?php endif; ?>
        </div>
      </div>

      <?php if (!empty($category['image_url'])): ?>
        <div class="w-20 h-20 sm:w-32 sm:h-32 md:w-36 md:h-36 rounded-xl sm:rounded-2xl bg-white/10 border border-white/20 p-2 sm:p-3 overflow-hidden shrink-0 self-end sm:self-auto flex items-center justify-center -mt-6 sm:mt-0">
          <img src="<?= htmlspecialchars($category['image_url']) ?>" alt="<?= htmlspecialchars($category['name']) ?>" class="max-h-full max-w-full object-contain rounded-lg sm:rounded-xl">
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ════════════  Subcategory Chips  ════════════ -->
  <?php if (!empty($children)): ?>
    <div class="space-y-1.5 sm:space-y-2 fade-up" style="animation-delay:.06s">
      <p class="text-[10px] sm:text-xs font-bold text-slate-400 uppercase tracking-wider">Browse Subcategories</p>
      <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto scrollbar-none pb-1 -mx-2.5 px-2.5 sm:mx-0 sm:px-0">
        <a href="<?= $baseUrl ?>/category/<?= htmlspecialchars($category['slug']) ?>" class="subcat-pill subcat-pill-active shrink-0">
          <span>All <?= htmlspecialchars($category['name']) ?></span>
          <span class="bg-cc-blue text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full"><?= count($products) ?></span>
        </a>
        <?php foreach ($children as $sub): ?>
          <a href="<?= $baseUrl ?>/category/<?= htmlspecialchars($sub['slug']) ?>" class="subcat-pill shrink-0">
            <span><?= htmlspecialchars($sub['name']) ?></span>
            <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-1.5 py-0.5 rounded-full"><?= $sub['product_count'] ?? 0 ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- ════════════  Toolbar: Sort, Filter & View  ════════════ -->
  <div class="bg-white rounded-xl sm:rounded-2xl border border-slate-200 shadow-2xs p-2.5 sm:p-4 flex flex-wrap items-center justify-between gap-2.5 sm:gap-3 fade-up" style="animation-delay:.08s">
    <div class="flex items-center gap-2 sm:gap-3">
      <span class="text-xs sm:text-sm font-bold text-slate-800"><span id="prod-count-label"><?= count($products) ?></span> Items</span>
      <label class="flex items-center gap-1.5 text-[11px] sm:text-xs text-slate-600 cursor-pointer ml-1.5 sm:ml-3 border-l border-slate-200 pl-2 sm:pl-3">
        <input type="checkbox" id="instock-only" onchange="filterProducts()" class="w-3.5 h-3.5 sm:w-4 sm:h-4 rounded accent-cc-blue">
        <span>In-Stock Only</span>
      </label>
    </div>

    <div class="flex items-center gap-2 sm:gap-3 ml-auto sm:ml-0">
      <div class="flex items-center gap-1 sm:gap-2">
        <span class="text-[10px] sm:text-xs text-slate-400 font-medium">Sort:</span>
        <select id="sort-select" onchange="sortProducts()" class="border border-slate-200 rounded-lg sm:rounded-xl text-[11px] sm:text-xs px-2 sm:px-3 py-1 sm:py-2 cursor-pointer outline-none bg-white font-semibold text-slate-700">
          <option value="featured">Featured First</option>
          <option value="price-low">Price: Low → High</option>
          <option value="price-high">Price: High → Low</option>
          <option value="discount">Biggest Discount</option>
          <option value="name">Name A-Z</option>
        </select>
      </div>

      <div class="flex items-center bg-slate-100 p-0.5 sm:p-1 rounded-lg sm:rounded-xl">
        <button onclick="setViewMode('grid')" id="view-grid" class="p-1 sm:p-1.5 rounded-md sm:rounded-lg bg-white shadow-xs text-cc-blue transition" aria-label="Grid View"><span class="material-icons text-sm sm:text-base">grid_view</span></button>
        <button onclick="setViewMode('list')" id="view-list" class="p-1 sm:p-1.5 rounded-md sm:rounded-lg text-slate-400 hover:text-slate-700 transition" aria-label="List View"><span class="material-icons text-sm sm:text-base">view_list</span></button>
      </div>
    </div>
  </div>

  <!-- ════════════  Products Grid  ════════════ -->
  <div id="products-container" class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 fade-up w-full min-w-0" style="animation-delay:.12s">
    <?php if (!empty($products)): ?>
      <?php foreach ($products as $idx => $p):
        $encId     = $p['encrypted_id'] ?? $p['id'];
        $basePrice = (float)($p['base_price'] ?? 0);
        $salePrice = !empty($p['sale_price']) ? (float)$p['sale_price'] : $basePrice;
        $disc      = (int)($p['discount_pct'] ?? 0);
        $img       = !empty($p['primary_image']) ? $p['primary_image'] : '';
        $rating    = (float)($p['average_rating'] ?? 0);
        $reviews   = (int)($p['review_count'] ?? 0);
        $isWish    = in_array((int)($p['id'] ?? 0), $userWpids);
        $isOOS     = intval($p['is_in_stock'] ?? 1) === 0;
        $catName   = $p['category_name'] ?? 'Product';
      ?>
        <div class="product-card p-2.5 sm:p-4 flex flex-col justify-between min-w-0"
             data-price="<?= $salePrice ?>"
             data-base-price="<?= $basePrice ?>"
             data-discount="<?= $disc ?>"
             data-name="<?= htmlspecialchars($p['name']) ?>"
             data-stock="<?= $p['is_in_stock'] ?? 1 ?>"
             data-featured="<?= $p['is_featured'] ?? 0 ?>"
             data-enc-id="<?= $encId ?>">
          <div>
            <!-- Image Area -->
            <div class="relative rounded-lg sm:rounded-xl overflow-hidden bg-slate-50 mb-2 sm:mb-3 aspect-square flex items-center justify-center">
              <?php if (!empty($img)): ?>
                <img src="<?= htmlspecialchars($img) ?>"
                     alt="<?= htmlspecialchars($p['name']) ?>"
                     class="object-contain w-full h-full p-2 sm:p-3 hover:scale-105 transition-transform duration-300"
                     loading="lazy"
                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                <div class="no-img-placeholder" style="display:none; position:absolute; inset:0;">
                  <span class="material-icons text-slate-300 text-3xl sm:text-5xl">inventory_2</span>
                </div>
              <?php else: ?>
                <div class="no-img-placeholder">
                  <span class="material-icons text-slate-300 text-3xl sm:text-5xl">inventory_2</span>
                </div>
              <?php endif; ?>

              <?php if ($disc > 0): ?>
                <span class="absolute top-1.5 left-1.5 sm:top-2 sm:left-2 bg-cc-orange text-white text-[9px] sm:text-[10px] font-extrabold px-1.5 sm:px-2 py-0.5 rounded uppercase tracking-wider z-10">
                  <?= $disc ?>% OFF
                </span>
              <?php endif; ?>

              <!-- Wishlist Button -->
              <button onclick="toggleWishlist('<?= $encId ?>', this); event.stopPropagation();"
                      class="absolute top-1.5 right-1.5 sm:top-2 sm:right-2 w-6 h-6 sm:w-8 sm:h-8 rounded-full bg-white/90 hover:bg-white shadow text-slate-400 hover:text-cc-pink flex items-center justify-center transition z-10 cursor-pointer" aria-label="Wishlist">
                <span class="material-icons text-[13px] sm:text-[16px] <?= $isWish ? 'text-cc-pink' : '' ?>"><?= $isWish ? 'favorite' : 'favorite_border' ?></span>
              </button>
            </div>

            <!-- Product Info -->
            <p class="text-[9px] sm:text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-0.5 sm:mb-1 truncate"><?= htmlspecialchars($catName) ?></p>
            <a href="<?= $baseUrl ?>/product/<?= $encId ?>" class="font-heading font-bold text-xs sm:text-sm text-slate-900 hover:text-cc-blue transition line-clamp-2 leading-snug mb-1 sm:mb-1.5 block min-h-[28px] sm:min-h-[36px]" title="<?= htmlspecialchars($p['name']) ?>">
              <?= htmlspecialchars($p['name']) ?>
            </a>

            <!-- Star Rating -->
            <div class="flex items-center gap-0.5 sm:gap-1 mb-2">
              <?php for ($i = 1; $i <= 5; $i++):
                $starCls = $i <= round($rating) ? 'text-amber-400' : 'text-slate-200';
              ?>
                <span class="material-icons <?= $starCls ?> text-[11px] sm:text-[13px]">star</span>
              <?php endfor; ?>
              <span class="text-[9px] sm:text-[10px] text-slate-400 ml-0.5">(<?= $reviews ?>)</span>
            </div>
          </div>

          <!-- Price & Actions -->
          <div class="space-y-2 sm:space-y-3 pt-2 border-t border-slate-100 mt-auto">
            <div class="flex flex-wrap items-baseline gap-x-1.5 gap-y-0.5">
              <span class="font-heading font-extrabold text-sm sm:text-base text-slate-900 leading-tight">₹<?= number_format($salePrice, 2) ?></span>
              <?php if ($disc > 0): ?>
                <span class="price-strike text-[10px] sm:text-xs leading-tight">₹<?= number_format($basePrice, 2) ?></span>
              <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-2 gap-1 sm:gap-2">
              <button onclick="addToCart('<?= $encId ?>')" <?= $isOOS ? 'disabled' : '' ?>
                      class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-[9px] sm:text-[11px] py-1.5 sm:py-2.5 px-1 sm:px-3 rounded-lg sm:rounded-xl flex items-center justify-center gap-0.5 sm:gap-1.5 transition shadow-xs cursor-pointer <?= $isOOS ? 'opacity-40 cursor-not-allowed' : '' ?>">
                <span class="material-icons text-[12px] sm:text-[14px]">shopping_cart</span> <span class="truncate">Cart</span>
              </button>
              <button onclick="buyNow('<?= $encId ?>')" <?= $isOOS ? 'disabled' : '' ?>
                      class="bg-cc-blue hover:bg-blue-600 text-white font-bold text-[9px] sm:text-[11px] py-1.5 sm:py-2.5 px-1 sm:px-3 rounded-lg sm:rounded-xl flex items-center justify-center gap-0.5 sm:gap-1.5 transition shadow-sm shadow-cc-blue/20 cursor-pointer <?= $isOOS ? 'opacity-40 cursor-not-allowed' : '' ?>">
                <span class="material-icons text-[12px] sm:text-[14px]">bolt</span> <span class="truncate">Buy</span>
              </button>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Empty State -->
  <div id="empty-products" class="<?= empty($products) ? '' : 'hidden' ?> bg-white rounded-2xl border border-slate-200 p-8 sm:p-12 text-center fade-up">
    <div class="w-16 h-16 sm:w-20 sm:h-20 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
      <span class="material-icons text-3xl sm:text-4xl text-slate-300">inventory_2</span>
    </div>
    <h3 class="font-bold text-base sm:text-lg text-slate-800 mb-1">No products found</h3>
    <p class="text-xs sm:text-sm text-slate-500 max-w-sm mx-auto mb-5">There are currently no products available matching this category filter.</p>
    <a href="<?= $baseUrl ?>/" class="inline-flex items-center gap-2 bg-cc-blue hover:bg-blue-600 text-white font-bold text-xs sm:text-sm px-5 sm:px-6 py-2 sm:py-2.5 rounded-xl transition">Browse All Products</a>
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

// ── Sort & Filter (client-side on the PHP-rendered cards) ──
function getCards() { return [...document.querySelectorAll('#products-container .product-card')]; }

function filterProducts() {
  const inStockOnly = document.getElementById('instock-only')?.checked;
  const cards = getCards();
  let visible = 0;
  cards.forEach(card => {
    const stock = parseInt(card.dataset.stock || '1');
    const hide = inStockOnly && stock === 0;
    card.style.display = hide ? 'none' : '';
    if (!hide) visible++;
  });
  document.getElementById('prod-count-label').textContent = visible;
  document.getElementById('empty-products').classList.toggle('hidden', visible > 0);
  sortProducts(); // re-sort after filter
}

function sortProducts() {
  const container = document.getElementById('products-container');
  const cards = getCards().filter(c => c.style.display !== 'none');
  const sort = document.getElementById('sort-select')?.value || 'featured';

  cards.sort((a, b) => {
    switch (sort) {
      case 'price-low':  return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
      case 'price-high': return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
      case 'discount':   return parseInt(b.dataset.discount) - parseInt(a.dataset.discount);
      case 'name':       return (a.dataset.name || '').localeCompare(b.dataset.name || '');
      default:           return parseInt(b.dataset.featured || 0) - parseInt(a.dataset.featured || 0);
    }
  });

  // Re-append in sorted order
  const allCards = getCards();
  const hidden = allCards.filter(c => c.style.display === 'none');
  cards.forEach(c => container.appendChild(c));
  hidden.forEach(c => container.appendChild(c));
}

function setViewMode(mode) {
  const container = document.getElementById('products-container');
  const gridBtn = document.getElementById('view-grid');
  const listBtn = document.getElementById('view-list');

  if (mode === 'grid') {
    container.className = 'grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4 fade-up w-full min-w-0';
    gridBtn.className = 'p-1 sm:p-1.5 rounded-md sm:rounded-lg bg-white shadow-xs text-cc-blue transition';
    listBtn.className = 'p-1 sm:p-1.5 rounded-md sm:rounded-lg text-slate-400 hover:text-slate-700 transition';
    getCards().forEach(card => {
      card.classList.remove('flex-row', 'items-center', 'gap-3', 'sm:gap-4');
      card.classList.add('flex-col');
      const imgArea = card.querySelector('[class*="aspect-square"], [class*="w-24"]');
      if (imgArea) {
        imgArea.classList.remove('w-24', 'h-24', 'sm:w-36', 'sm:h-36', 'shrink-0');
        imgArea.classList.add('aspect-square');
        imgArea.style.aspectRatio = '';
      }
    });
  } else {
    container.className = 'flex flex-col gap-2.5 sm:gap-3 fade-up w-full min-w-0';
    gridBtn.className = 'p-1 sm:p-1.5 rounded-md sm:rounded-lg text-slate-400 hover:text-slate-700 transition';
    listBtn.className = 'p-1 sm:p-1.5 rounded-md sm:rounded-lg bg-white shadow-xs text-cc-blue transition';
    getCards().forEach(card => {
      card.classList.remove('flex-col');
      card.classList.add('flex-row', 'items-center', 'gap-3', 'sm:gap-4');
      const imgArea = card.querySelector('.aspect-square');
      if (imgArea) {
        imgArea.classList.remove('aspect-square');
        imgArea.classList.add('w-24', 'h-24', 'sm:w-36', 'sm:h-36', 'shrink-0');
        imgArea.style.aspectRatio = 'auto';
      }
    });
  }
}

// ── AJAX Actions ──
function addToCart(encId) {
  fetch(`${BASE_URL}/cart/add`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ product_id: encId, quantity: 1 })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      document.querySelectorAll('.cart-badge-count').forEach(el => el.textContent = data.cart_count || 0);
      showToast('Added to Cart!', 'success');
    } else {
      showToast(data.message || 'Failed to add to cart.', 'error');
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}

function buyNow(encId) {
  fetch(`${BASE_URL}/cart/add`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ product_id: encId, quantity: 1 })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      window.location.href = `${BASE_URL}/checkout`;
    } else {
      showToast(data.message || 'Failed to proceed to checkout.', 'error');
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}

function toggleWishlist(encId, btn) {
  fetch(`${BASE_URL}/wishlist/add`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ product_id: encId })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const icon = btn.querySelector('.material-icons');
      if (data.action === 'added') {
        icon.textContent = 'favorite';
        icon.classList.add('text-cc-pink');
        showToast('Saved to Wishlist!', 'success');
      } else {
        icon.textContent = 'favorite_border';
        icon.classList.remove('text-cc-pink');
        showToast('Removed from Wishlist.', 'info');
      }
      document.querySelectorAll('.wishlist-badge-count').forEach(el => el.textContent = data.wishlist_count || 0);
    } else {
      showToast(data.message || 'Please log in to save wishlist.', 'warning');
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
