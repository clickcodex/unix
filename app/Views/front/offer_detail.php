<?php
require_once __DIR__ . '/layouts/header.php';

$offer         = $offer         ?? [];
$offerProducts = $offerProducts ?? [];
$coupons       = $coupons       ?? [];
$baseUrl       = $baseUrl       ?? (defined('BASE_URL') ? BASE_URL : '');

$title       = htmlspecialchars($offer['title'] ?? $offer['name'] ?? 'Special Offer');
$desc        = htmlspecialchars($offer['description'] ?? 'Enjoy special promotional discounts automatically applied to qualifying cart items.');
$offerType   = htmlspecialchars($offer['offer_type'] ?? 'Limited Time Offer');
$discVal     = (float)($offer['discount_value'] ?? 0);
$endDate     = !empty($offer['ends_at']) ? $offer['ends_at'] : (!empty($offer['end_date']) ? $offer['end_date'] : null);
$bannerImg   = !empty($offer['banner_image']) ? $offer['banner_image'] : (!empty($offer['image_url']) ? $offer['image_url'] : null);
?>

<style>
  body { background: #F8FAFC; }
  @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
  .fade-up { animation: fadeUp .4s ease-out both; }

  /* --- Single Offer Hero Banner --- */
  .offer-detail-hero {
    position: relative;
    border-radius: 28px;
    overflow: hidden;
    color: #ffffff;
    background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 40%, #312E81 70%, #4C1D95 100%);
    box-shadow: 0 20px 50px rgba(15,23,42,0.3);
  }
  .offer-detail-hero::before {
    content:''; position:absolute; inset:0;
    background: radial-gradient(circle at 80% 30%, rgba(255,81,0,0.3) 0%, transparent 60%),
                radial-gradient(circle at 10% 80%, rgba(140,48,245,0.35) 0%, transparent 50%);
  }

  /* Countdown */
  .cd-box {
    background: rgba(255,255,255,0.12);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.25);
    border-radius: 12px;
    min-width: 56px;
    text-align: center;
    padding: 8px 10px;
    font-family: 'Poppins', monospace;
  }

  /* Product Card */
  .offer-product-card {
    background: #ffffff;
    border: 1.5px solid #E2E8F0;
    border-radius: 20px;
    overflow: hidden;
    transition: transform .25s cubic-bezier(.4,0,.2,1), box-shadow .25s, border-color .25s;
    display: flex; flex-direction: column;
    position: relative;
  }
  .offer-product-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 40px rgba(45,130,255,0.14);
    border-color: #93c5fd;
  }
  .card-img-wrap {
    aspect-ratio: 1;
    overflow: hidden;
    background: #F8FAFC;
    position: relative;
  }
  .card-img-wrap img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .4s ease;
  }
  .offer-product-card:hover .card-img-wrap img { transform: scale(1.07); }
  .discount-badge {
    position: absolute; top: 10px; left: 10px;
    background: linear-gradient(135deg, #FF5100, #FF8C00);
    color: #fff; font-size: 10px; font-weight: 800;
    padding: 3px 9px; border-radius: 8px;
    letter-spacing: .05em; text-transform: uppercase;
    box-shadow: 0 2px 8px rgba(255,81,0,.35);
  }
  .wishlist-btn {
    position: absolute; top: 10px; right: 10px;
    width: 32px; height: 32px; border-radius: 50%;
    background: rgba(255,255,255,.85); backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center;
    color: #94a3b8; transition: all .2s; text-decoration: none;
    box-shadow: 0 2px 8px rgba(0,0,0,.1);
  }
  .wishlist-btn:hover { background: #fff; color: #FF006B; transform: scale(1.1); }
  .card-body { padding: 14px; flex: 1; display: flex; flex-direction: column; }
  .card-category { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: #94a3b8; margin-bottom: 4px; }
  .card-title {
    font-family: 'Poppins', sans-serif; font-size: 14px; font-weight: 700; color: #0F172A;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    margin-bottom: auto; line-height: 1.4;
  }
  .card-title a { color: inherit; text-decoration: none; transition: color .2s; }
  .card-title a:hover { color: #2D82FF; }
  .card-prices { display: flex; align-items: baseline; gap: 8px; margin-top: 10px; }
  .price-current { font-family: 'Poppins', sans-serif; font-weight: 800; font-size: 17px; color: #0F172A; }
  .price-original { font-size: 12px; color: #94a3b8; text-decoration: line-through; font-family: monospace; }
  .btn-add-cart {
    margin-top: 12px; width: 100%;
    background: linear-gradient(135deg, #2D82FF, #4F46E5);
    color: #fff; font-weight: 700; font-size: 12px;
    padding: 10px 14px; border-radius: 12px; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    transition: all .25s; letter-spacing: .03em; text-transform: uppercase;
    box-shadow: 0 4px 12px rgba(45,130,255,.3);
  }
  .btn-add-cart:hover { background: linear-gradient(135deg, #1a6fe8, #4338CA); transform: translateY(-1px); }
</style>

<main class="max-w-7xl mx-auto px-3 sm:px-4 py-4 sm:py-8 space-y-8 sm:space-y-12 w-full">

  <!-- ================= BREADCRUMB ================= -->
  <nav class="flex items-center gap-2 text-xs text-slate-400 font-medium fade-up">
    <a href="<?= $baseUrl ?>/" class="hover:text-cc-blue transition">Home</a>
    <span>&rsaquo;</span>
    <a href="<?= $baseUrl ?>/offers" class="hover:text-cc-blue transition">Offers</a>
    <span>&rsaquo;</span>
    <span class="text-slate-700 font-bold truncate max-w-xs"><?= $title ?></span>
  </nav>

  <!-- ================= OFFER HERO BANNER ================= -->
  <section class="offer-detail-hero p-6 sm:p-10 md:p-14 fade-up">
    <?php if ($bannerImg): ?>
      <img src="<?= htmlspecialchars($bannerImg) ?>" alt="<?= $title ?>" class="absolute inset-0 w-full h-full object-cover opacity-20 pointer-events-none">
    <?php endif; ?>

    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-3 gap-8 items-center">
      <!-- Left Info -->
      <div class="lg:col-span-2 space-y-4">
        <div class="flex items-center gap-2 flex-wrap">
          <span class="inline-flex items-center gap-1.5 bg-amber-400 text-slate-900 text-xs font-extrabold px-3.5 py-1 rounded-full uppercase tracking-wider shadow-md">
            <span class="material-icons text-[15px]">bolt</span> <?= $offerType ?>
          </span>
          <?php if ($discVal > 0): ?>
            <span class="inline-flex items-center gap-1.5 bg-cc-orange text-white text-xs font-extrabold px-3 py-1 rounded-full uppercase tracking-wider shadow-md">
              Up to <?= $discVal ?>% OFF
            </span>
          <?php endif; ?>
        </div>

        <h1 class="font-heading text-3xl sm:text-4xl md:text-5xl font-extrabold text-white leading-tight">
          <?= $title ?>
        </h1>

        <p class="text-white/85 text-sm sm:text-base max-w-2xl leading-relaxed">
          <?= $desc ?>
        </p>

        <div class="flex items-center gap-4 pt-2">
          <a href="#offer-products" class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-300 text-slate-900 font-extrabold px-6 py-3 rounded-xl transition shadow-lg text-sm">
            <span class="material-icons text-[18px]">shopping_bag</span> Shop Offer Products
          </a>
          <a href="<?= $baseUrl ?>/offers" class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 border border-white/25 text-white font-bold px-5 py-3 rounded-xl transition text-sm backdrop-blur">
            &larr; View All Offers
          </a>
        </div>
      </div>

      <!-- Right: Countdown Timer -->
      <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-6 text-center space-y-3">
        <p class="text-amber-300 text-xs font-extrabold uppercase tracking-wider">⚡ Offer Expiration</p>
        <div class="flex items-center justify-center gap-2">
          <div class="cd-box"><div id="od-hrs" class="text-white text-2xl font-extrabold">00</div><div class="text-white/50 text-[10px] font-bold mt-0.5">HOURS</div></div>
          <span class="text-amber-400 text-2xl font-black">:</span>
          <div class="cd-box"><div id="od-mins" class="text-white text-2xl font-extrabold">00</div><div class="text-white/50 text-[10px] font-bold mt-0.5">MINS</div></div>
          <span class="text-amber-400 text-2xl font-black">:</span>
          <div class="cd-box"><div id="od-secs" class="text-white text-2xl font-extrabold">00</div><div class="text-white/50 text-[10px] font-bold mt-0.5">SECS</div></div>
        </div>
        <?php if ($endDate): ?>
          <p class="text-white/60 text-xs">Valid till <?= date('M d, Y', strtotime($endDate)) ?></p>
        <?php else: ?>
          <p class="text-white/60 text-xs">Limited stock available!</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <!-- ================= PRODUCTS IN THIS OFFER ================= -->
  <section id="offer-products" class="space-y-6 fade-up">
    <div class="flex items-center justify-between flex-wrap gap-2 border-b border-slate-200 pb-4">
      <div>
        <div class="inline-flex items-center gap-1 text-xs font-bold text-cc-blue bg-blue-50 px-3 py-1 rounded-full uppercase tracking-wider mb-1">
          <span class="material-icons text-[14px]">local_offer</span> Eligible Products
        </div>
        <h2 class="font-heading text-xl sm:text-2xl font-bold text-slate-900">Products Included in <?= $title ?></h2>
      </div>
      <span class="text-xs text-slate-500 font-bold"><?= count($offerProducts) ?> Items On Sale</span>
    </div>

    <?php if (empty($offerProducts)): ?>
      <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-500">
        <span class="material-icons text-5xl text-slate-300 mb-2">inventory_2</span>
        <p class="font-heading font-bold text-lg text-slate-700">No specific products tagged for this offer</p>
        <p class="text-xs text-slate-400 mt-1">Explore our main catalog to view active deals.</p>
        <a href="<?= $baseUrl ?>/categories" class="inline-block mt-4 text-xs font-bold text-cc-blue hover:underline">Explore Store Catalog &rarr;</a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-5">
        <?php foreach ($offerProducts as $p):
          $basePrice  = (float)$p['base_price'];
          $effPrice   = (float)$p['effective_price'];
          $discount   = (int)$p['discount_percent'];
          $inStock    = (bool)($p['is_in_stock'] ?? true);
          $rating     = round((float)($p['average_rating'] ?? 4.5), 1);
          $reviews    = (int)($p['review_count'] ?? 12);
          $encId      = htmlspecialchars($p['encrypted_id'] ?? $p['id']);
        ?>
          <div class="offer-product-card">
            <div class="card-img-wrap">
              <?php if (!empty($p['main_image_url'])): ?>
                <img src="<?= htmlspecialchars($p['main_image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
              <?php else: ?>
                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-50 to-blue-50">
                  <span class="material-icons text-slate-300 text-[60px]">inventory_2</span>
                </div>
              <?php endif; ?>
              <span class="discount-badge">-<?= $discount ?>% OFF</span>
              <a href="<?= $baseUrl ?>/wishlist" class="wishlist-btn" title="Add to Wishlist">
                <span class="material-icons text-[16px]">favorite_border</span>
              </a>
            </div>
            <div class="card-body">
              <div class="card-category"><?= htmlspecialchars($p['category_name'] ?? 'Offer Item') ?></div>
              <div class="card-title">
                <a href="<?= $baseUrl ?>/product/<?= $encId ?>"><?= htmlspecialchars($p['name']) ?></a>
              </div>
              <div class="card-prices">
                <span class="price-current">₹<?= number_format($effPrice, 0) ?></span>
                <span class="price-original">₹<?= number_format($basePrice, 0) ?></span>
              </div>
              <div class="card-rating flex items-center gap-1 text-xs text-slate-500 mt-1">
                <span class="material-icons text-amber-400 text-[14px]">star</span>
                <span class="font-bold text-slate-700"><?= $rating ?></span>
                <span class="text-slate-400">(<?= $reviews ?>)</span>
              </div>
              <?php if ($inStock): ?>
                <button onclick="addToCartAJAX('<?= $encId ?>', this)" class="btn-add-cart">
                  <span class="material-icons text-[15px]">shopping_cart</span> Add to Cart
                </button>
              <?php else: ?>
                <button class="btn-add-cart opacity-50 cursor-not-allowed" disabled>
                  <span class="material-icons text-[15px]">remove_shopping_cart</span> Out of Stock
                </button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

</main>

<script>
(function() {
  function pad(n) { return String(n).padStart(2,'0'); }
  function updateTimer() {
    const now = new Date();
    const midnight = new Date(now);
    midnight.setHours(24, 0, 0, 0);
    let s = Math.max(0, Math.floor((midnight - now) / 1000));
    const h = Math.floor(s / 3600);
    const m = Math.floor((s % 3600) / 60);
    const sec = s % 60;
    const hEl = document.getElementById('od-hrs');
    const mEl = document.getElementById('od-mins');
    const sEl = document.getElementById('od-secs');
    if (hEl) hEl.textContent = pad(h);
    if (mEl) mEl.textContent = pad(m);
    if (sEl) sEl.textContent = pad(sec);
  }
  updateTimer();
  setInterval(updateTimer, 1000);
})();
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
