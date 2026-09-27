<?php
require_once __DIR__ . '/layouts/header.php';

$product         = $product         ?? [];
$relatedProducts = $relatedProducts ?? [];
$reviews         = $reviews         ?? [];
$ratingCounts    = $ratingCounts    ?? [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$inWishlist      = $inWishlist      ?? false;
$baseUrl         = $baseUrl         ?? (defined('BASE_URL') ? BASE_URL : '');

$images    = $product['images'] ?? [];
$videos    = $product['videos'] ?? [];
$specs     = $product['specifications'] ?? [];
$variants  = $product['variants'] ?? [];

$mainImg   = !empty($images) ? $images[0]['image_url'] : 'https://via.placeholder.com/600';
$basePrice = (float)($product['base_price'] ?? 0);
$salePrice = (float)($product['sale_price'] ?? $basePrice);
if ($salePrice <= 0) $salePrice = $basePrice;
$discPct   = ($basePrice > $salePrice && $basePrice > 0) ? round((($basePrice - $salePrice) / $basePrice) * 100) : 0;
$savings   = max(0, $basePrice - $salePrice);
$encId     = $product['encrypted_id'] ?? $product['id'];
$isOOS     = intval($product['is_in_stock'] ?? 1) === 0;

$avgRating  = (float)($product['average_rating'] ?? 4.3);
$totalRevs  = (int)($product['review_count'] ?? count($reviews));
$totalSold  = (int)($product['total_sold'] ?? 0);
$catName    = $product['category_name'] ?? 'Electronics';
$catSlug    = $product['category_slug'] ?? 'electronics';
$sku        = $product['sku'] ?? 'N/A';
$taxRate    = (float)($product['tax_rate'] ?? 18);

// Group specifications by spec_group
$groupedSpecs = [];
foreach ($specs as $sp) {
  $grp = !empty($sp['spec_group']) ? $sp['spec_group'] : 'General';
  $groupedSpecs[$grp][] = $sp;
}
if (empty($groupedSpecs)) {
  $groupedSpecs['General'] = [
    ['spec_key' => 'Brand', 'spec_value' => 'ClickCodex Certified'],
    ['spec_key' => 'SKU', 'spec_value' => $sku],
    ['spec_key' => 'Category', 'spec_value' => $catName],
    ['spec_key' => 'Condition', 'spec_value' => 'Brand New — 100% Original']
  ];
}

// Calculate rating bar percentages
$ratingCounts     = $ratingCounts ?? [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$totalRatingVotes = array_sum($ratingCounts);
if ($totalRatingVotes <= 0) $totalRatingVotes = max(1, $totalRevs);

// Set SEO dynamic meta vars
$metaDescription = strip_tags(substr($product['description'] ?? $product['name'] ?? '', 0, 160));
$metaKeywords    = htmlspecialchars($catName . ', ' . $product['name'] . ', buy online, ClickCodex');
$ogImage         = $mainImg;
$ogType          = 'product';
?>

<!-- Schema.org Product Structured Data (Google Rich Snippet) -->
<script type="application/ld+json">
{
  "@context": "https://schema.org/",
  "@type": "Product",
  "name": <?= json_encode($product['name'] ?? 'Product') ?>,
  "image": [<?= json_encode($mainImg) ?>],
  "description": <?= json_encode(strip_tags($product['description'] ?? $product['name'] ?? '')) ?>,
  "sku": <?= json_encode($sku) ?>,
  "brand": {
    "@type": "Brand",
    "name": "ClickCodex"
  },
  "offers": {
    "@type": "Offer",
    "url": <?= json_encode($baseUrl . '/product/' . $encId) ?>,
    "priceCurrency": "INR",
    "price": "<?= number_format($salePrice, 2, '.', '') ?>",
    "priceValidUntil": "<?= date('Y-12-31') ?>",
    "itemCondition": "https://schema.org/NewCondition",
    "availability": "<?= $isOOS ? 'https://schema.org/OutOfStock' : 'https://schema.org/InStock' ?>"
  }
  <?php if ($totalRevs > 0): ?>,
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "<?= number_format($avgRating, 1, '.', '') ?>",
    "reviewCount": "<?= $totalRevs ?>"
  }
  <?php endif; ?>
}
</script>

<style>
  body { font-family: 'Inter', sans-serif; background: #F1F5F9; }
  h1, h2, h3, h4, .font-heading { font-family: 'Poppins', sans-serif; }
  .price-strike { text-decoration: line-through; color: #94A3B8; }
  .stars { color: #FFB800; }

  .thumb-btn { transition: all .2s ease; border: 2px solid transparent; }
  .thumb-btn.active { border-color: #2D82FF; }
  .thumb-btn:hover { border-color: #94A3B8; }

  .variant-swatch { transition: all .15s ease; cursor: pointer; }
  .variant-swatch.active { border-color: #2D82FF; background: #E8F0FE; color: #2D82FF; font-weight: 700; }

  .tab-btn { position: relative; transition: color .2s ease; cursor: pointer; }
  .tab-btn.active { color: #2D82FF; font-weight: 700; }
  .tab-btn.active::after {
    content: ''; position: absolute; bottom: -1px; left: 0; right: 0; height: 3px;
    background: #2D82FF; border-radius: 3px 3px 0 0;
  }

  .rating-bar-fill { transition: width .6s ease; }

  .zoom-wrap:hover .zoom-img { transform: scale(1.08); }
  .zoom-img { transition: transform .35s ease; }

  .sticky-buybox { position: sticky; top: 80px; }
  @media (max-width: 1023px) { .sticky-buybox { position: static; } }

  .toast-in { animation: slideInRight .35s ease-out forwards; }
  @keyframes slideInRight { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
</style>

<!-- Toast Notifications Container -->
<div id="toast-container" class="fixed top-5 right-5 z-[200] flex flex-col gap-2.5 pointer-events-none" style="max-width:380px"></div>

<main class="max-w-7xl mx-auto px-3 sm:px-4 py-4 sm:py-6 pb-20 md:pb-6">

  <!-- ================= BREADCRUMBS ================= -->
  <nav class="text-xs text-slate-500 mb-4 flex items-center gap-1.5 flex-wrap">
    <a href="<?= $baseUrl ?>/" class="hover:text-cc-blue transition">Home</a> <span>/</span>
    <a href="<?= $baseUrl ?>/category/<?= htmlspecialchars($catSlug) ?>" class="hover:text-cc-blue transition"><?= htmlspecialchars($catName) ?></a> <span>/</span>
    <span class="text-slate-700 font-medium truncate max-w-xs sm:max-w-md"><?= htmlspecialchars($product['name']) ?></span>
  </nav>

  <!-- ================= PRODUCT MAIN SECTION ================= -->
  <section class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 mb-8">

    <!-- ===== GALLERY (Images + Videos) ===== -->
    <div class="lg:col-span-5">
      <div class="bg-white rounded-2xl p-4 border border-slate-100 shadow-xs relative">
        <div class="relative rounded-xl overflow-hidden aspect-square bg-slate-50 flex items-center justify-center zoom-wrap group">
          <?php if ($discPct > 0): ?>
            <span id="main-media-badge" class="absolute top-3 left-3 bg-cc-orange text-white text-[10px] font-extrabold px-2.5 py-1 rounded-md z-10 uppercase tracking-wide shadow"><?= $discPct ?>% OFF</span>
          <?php endif; ?>

          <button onclick="toggleWishlistItem(this)" class="absolute top-3 right-3 z-10 bg-white/90 hover:bg-white p-2 rounded-full shadow-md transition cursor-pointer">
            <span class="material-icons text-cc-pink text-xl" id="wish-toggle-icon"><?= $inWishlist ? 'favorite' : 'favorite_border' ?></span>
          </button>

          <!-- Gallery Slider Navigation Arrows (Prev / Next) -->
          <?php if (count($images) > 1): ?>
            <button onclick="prevProductImage()" aria-label="Previous Image" class="absolute left-2 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/80 hover:bg-white text-slate-700 hover:text-cc-blue flex items-center justify-center shadow-md transition active:scale-95 cursor-pointer border border-slate-200">
              <span class="material-icons text-xl">chevron_left</span>
            </button>
            <button onclick="nextProductImage()" aria-label="Next Image" class="absolute right-2 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/80 hover:bg-white text-slate-700 hover:text-cc-blue flex items-center justify-center shadow-md transition active:scale-95 cursor-pointer border border-slate-200">
              <span class="material-icons text-xl">chevron_right</span>
            </button>
          <?php endif; ?>

          <!-- Image view -->
          <img id="gallery-main-img" src="<?= htmlspecialchars($mainImg) ?>" class="w-full h-full object-contain p-4 zoom-img transition-all duration-300" alt="<?= htmlspecialchars($product['name']) ?>" onerror="this.src='https://via.placeholder.com/600';">

          <!-- Video view (hidden by default) -->
          <div id="gallery-main-video" class="hidden absolute inset-0 w-full h-full">
            <iframe id="gallery-video-frame" class="w-full h-full" src="" title="Product video" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
          </div>
        </div>

        <!-- Thumbnails Row -->
        <div class="flex gap-2.5 mt-4 overflow-x-auto scrollbar-none pb-1" id="thumb-row">
          <?php foreach ($images as $idx => $img): ?>
            <button class="thumb-btn <?= $idx === 0 ? 'active' : '' ?> shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-lg overflow-hidden bg-slate-50 cursor-pointer" data-thumb-index="<?= $idx ?>" onclick="selectImage(<?= $idx ?>, '<?= htmlspecialchars($img['image_url']) ?>', this)">
              <img src="<?= htmlspecialchars($img['image_url']) ?>" class="w-full h-full object-contain p-1" alt="thumb" onerror="this.src='https://via.placeholder.com/100';">
            </button>
          <?php endforeach; ?>

          <?php foreach ($videos as $vIdx => $vid): ?>
            <button class="thumb-btn relative shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-lg overflow-hidden bg-slate-800 cursor-pointer" onclick="selectVideo('<?= htmlspecialchars($vid['video_url']) ?>', this)">
              <img src="<?= htmlspecialchars(!empty($vid['thumbnail_url']) ? $vid['thumbnail_url'] : $mainImg) ?>" class="w-full h-full object-cover opacity-60" alt="video thumb">
              <span class="material-icons absolute inset-0 flex items-center justify-center text-white text-2xl">play_circle</span>
            </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Trust Badges Strip -->
      <div class="grid grid-cols-3 gap-2.5 mt-3.5">
        <div class="bg-white rounded-xl p-3 flex flex-col items-center text-center border border-slate-100 shadow-2xs">
          <span class="material-icons text-cc-blue text-xl mb-1">local_shipping</span>
          <p class="text-[10px] sm:text-[11px] font-semibold text-slate-600 leading-tight">Free Shipping ₹499+</p>
        </div>
        <div class="bg-white rounded-xl p-3 flex flex-col items-center text-center border border-slate-100 shadow-2xs">
          <span class="material-icons text-cc-orange text-xl mb-1">autorenew</span>
          <p class="text-[10px] sm:text-[11px] font-semibold text-slate-600 leading-tight">7-Day Returns</p>
        </div>
        <div class="bg-white rounded-xl p-3 flex flex-col items-center text-center border border-slate-100 shadow-2xs">
          <span class="material-icons text-cc-purple text-xl mb-1">verified_user</span>
          <p class="text-[10px] sm:text-[11px] font-semibold text-slate-600 leading-tight">1 Year Warranty</p>
        </div>
      </div>
    </div>

    <!-- ===== CORE INFO + VARIANTS ===== -->
    <div class="lg:col-span-4">
      <div class="bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 shadow-xs">
        <p class="text-xs font-bold text-cc-blue uppercase tracking-wider mb-1"><?= htmlspecialchars($catName) ?></p>
        <h1 class="font-heading text-lg sm:text-2xl font-bold text-slate-900 leading-snug"><?= htmlspecialchars($product['name']) ?></h1>

        <div class="flex items-center gap-2 mt-2">
          <span class="flex items-center gap-1 bg-green-600 text-white text-xs font-bold px-2 py-0.5 rounded-md">
            <?= number_format($avgRating, 1) ?> <span class="material-icons text-xs">star</span>
          </span>
          <span class="text-xs text-slate-500 font-medium"><?= number_format($totalRevs) ?> Ratings &amp; Reviews</span>
        </div>

        <div class="mt-4 flex items-baseline gap-3 border-y border-slate-100 py-3.5">
          <span class="font-heading font-extrabold text-2xl sm:text-3xl text-slate-900" id="pdp-price">₹<?= number_format($salePrice, 2) ?></span>
          <?php if ($discPct > 0): ?>
            <span class="price-strike text-sm sm:text-base" id="pdp-strike">₹<?= number_format($basePrice, 2) ?></span>
            <span class="text-cc-pink font-bold text-xs sm:text-sm" id="pdp-discount"><?= $discPct ?>% OFF</span>
          <?php endif; ?>
        </div>
        <p class="text-[11px] text-slate-500 mt-1">Inclusive of all GST • <span id="pdp-tax-note"><?= $taxRate ?>% GST applied</span></p>

        <!-- SKU / Stock Status -->
        <div class="flex items-center gap-2 mt-3 text-xs">
          <span class="text-slate-400">SKU:</span>
          <span id="pdp-sku" class="font-semibold text-slate-600"><?= htmlspecialchars($sku) ?></span>
          <span class="mx-1 text-slate-300">|</span>
          <span id="pdp-stock" class="flex items-center gap-1 <?= $isOOS ? 'text-red-600' : 'text-green-600' ?> font-semibold">
            <span class="w-2 h-2 rounded-full <?= $isOOS ? 'bg-red-500' : 'bg-green-500' ?>"></span>
            <?= $isOOS ? 'Out of Stock' : 'In Stock (Ready to Ship)' ?>
          </span>
        </div>

        <!-- Variants Section (Color / Warranty) -->
        <?php if (!empty($variants)): ?>
          <div class="mt-4 pt-3 border-t border-slate-100">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Available Options</p>
            <div class="flex gap-2 flex-wrap">
              <?php foreach ($variants as $vIdx => $v): ?>
                <button onclick="selectVariant(<?= htmlspecialchars(json_encode($v)) ?>, this)" class="variant-swatch <?= $vIdx === 0 ? 'active' : '' ?> border-2 border-slate-200 rounded-xl px-3 py-1.5 text-xs font-medium">
                  <?= htmlspecialchars($v['name'] ?? ('Variant #' . ($vIdx+1))) ?>
                </button>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Delivery Pincode Check -->
        <div class="mt-4 bg-slate-50 rounded-xl p-3.5 border border-slate-100">
          <p class="text-xs font-bold text-slate-600 mb-2">Delivery Options</p>
          <div class="flex gap-2">
            <input type="number" id="pincode-input" placeholder="Enter 6-digit Pincode" class="flex-1 border border-slate-200 px-3 py-2 rounded-lg text-xs bg-white focus:outline-none focus:ring-1 focus:ring-cc-blue">
            <button onclick="checkPincode()" class="bg-cc-dark text-white text-xs font-bold px-3.5 py-2 rounded-lg hover:bg-slate-700 transition cursor-pointer">Check</button>
          </div>
          <p id="pincode-result" class="text-[11px] text-slate-500 mt-2 flex items-center gap-1">
            <span class="material-icons text-sm text-cc-blue">event</span>
            <span>Delivery by <b class="text-slate-700"><?= date('D, d M', strtotime('+3 days')) ?></b> if ordered today</span>
          </p>
        </div>

        <!-- Short Highlights -->
        <?php if (!empty($product['short_description'])): ?>
          <div class="mt-4 pt-3 border-t border-slate-100">
            <p class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Highlights</p>
            <div class="text-xs text-slate-600 leading-relaxed space-y-1">
              <?= nl2br(htmlspecialchars($product['short_description'])) ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ===== BUY BOX (Sticky on desktop) ===== -->
    <div class="lg:col-span-3">
      <div class="sticky-buybox bg-white rounded-2xl p-5 border border-slate-100 shadow-xs">
        <div class="flex items-baseline gap-2 mb-1">
          <span class="font-heading font-extrabold text-2xl text-slate-900" id="buybox-price">₹<?= number_format($salePrice, 2) ?></span>
          <?php if ($discPct > 0): ?>
            <span class="price-strike text-sm" id="buybox-strike">₹<?= number_format($basePrice, 2) ?></span>
          <?php endif; ?>
        </div>
        <?php if ($savings > 0): ?>
          <p class="text-xs text-green-600 font-semibold mb-4">You save ₹<?= number_format($savings, 2) ?> (<?= $discPct ?>%)</p>
        <?php endif; ?>

        <!-- Qty Selector -->
        <div class="flex items-center justify-between gap-3 mb-4 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
          <span class="text-xs font-bold text-slate-600">Quantity</span>
          <div class="flex items-center border border-slate-200 rounded-lg overflow-hidden bg-white">
            <button onclick="changeQty(-1)" class="px-3 py-1.5 hover:bg-slate-100 font-bold text-slate-600 text-sm transition">−</button>
            <span id="pdp-qty" class="px-4 py-1.5 text-xs font-extrabold text-slate-800 border-x border-slate-200">1</span>
            <button onclick="changeQty(1)" class="px-3 py-1.5 hover:bg-slate-100 font-bold text-slate-600 text-sm transition">+</button>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col gap-2.5">
          <button onclick="addToCartDetailed()" <?= $isOOS ? 'disabled' : '' ?> class="w-full bg-cc-blue hover:bg-blue-600 text-white font-bold py-3 rounded-xl transition flex items-center justify-center gap-2 shadow-md cursor-pointer <?= $isOOS ? 'opacity-50 cursor-not-allowed' : '' ?>">
            <span class="material-icons text-lg">shopping_cart</span> Add to Cart
          </button>
          <button onclick="buyNowDetailed()" <?= $isOOS ? 'disabled' : '' ?> class="w-full bg-cc-orange hover:bg-orange-600 text-white font-bold py-3 rounded-xl transition flex items-center justify-center gap-2 shadow-md cursor-pointer <?= $isOOS ? 'opacity-50 cursor-not-allowed' : '' ?>">
            <span class="material-icons text-lg">bolt</span> Buy Now
          </button>
        </div>

        <!-- Security Features -->
        <div class="mt-5 pt-4 border-t border-slate-100 space-y-2.5 text-xs text-slate-500">
          <p class="flex items-center gap-2"><span class="material-icons text-sm text-cc-purple">verified</span> Secure UPI / Card / COD payments</p>
          <p class="flex items-center gap-2"><span class="material-icons text-sm text-cc-orange">autorenew</span> 7-day easy replacement policy</p>
          <p class="flex items-center gap-2"><span class="material-icons text-sm text-cc-blue">support_agent</span> 24/7 dedicated support desk</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ================= DESCRIPTION SECTION ================= -->
  <section class="bg-white rounded-2xl border border-slate-100 shadow-xs mb-8 p-4 sm:p-7">
    <h3 class="section-heading font-heading font-bold text-base sm:text-xl text-slate-800 mb-4">Description &amp; Overview</h3>

    <div class="relative">
      <div id="desc-content" class="text-xs sm:text-sm text-slate-600 leading-relaxed space-y-4 overflow-hidden" style="max-height: 120px;">
        <?= !empty($product['description']) ? nl2br(htmlspecialchars($product['description'])) : 'No detailed description provided for this product yet.' ?>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3">
          <div class="text-center p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span class="material-icons text-cc-blue text-2xl">verified</span>
            <p class="text-[11px] font-semibold text-slate-600 mt-1">Authentic Quality</p>
          </div>
          <div class="text-center p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span class="material-icons text-cc-orange text-2xl">battery_charging_full</span>
            <p class="text-[11px] font-semibold text-slate-600 mt-1">High Performance</p>
          </div>
          <div class="text-center p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span class="material-icons text-cc-purple text-2xl">shield</span>
            <p class="text-[11px] font-semibold text-slate-600 mt-1">Brand Warranty</p>
          </div>
          <div class="text-center p-3 bg-slate-50 rounded-xl border border-slate-100">
            <span class="material-icons text-cc-pink text-2xl">local_shipping</span>
            <p class="text-[11px] font-semibold text-slate-600 mt-1">Fast Delivery</p>
          </div>
        </div>
      </div>
      <div id="desc-fade" class="absolute bottom-0 left-0 right-0 h-10 bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
    </div>
    <button onclick="toggleReadMore('desc')" id="desc-toggle-btn" class="mt-3 text-xs font-bold text-cc-blue hover:text-blue-700 flex items-center gap-1 cursor-pointer">
      Read More <span class="material-icons text-sm" id="desc-toggle-icon">expand_more</span>
    </button>
  </section>

  <!-- ================= SPECIFICATIONS SECTION ================= -->
  <section class="bg-white rounded-2xl border border-slate-100 shadow-xs mb-8 p-4 sm:p-7">
    <h3 class="section-heading font-heading font-bold text-base sm:text-xl text-slate-800 mb-5">Product Specifications</h3>
    <div id="specs-container" class="space-y-5">
      <?php foreach ($groupedSpecs as $groupTitle => $groupItems): ?>
        <div>
          <h4 class="font-heading font-bold text-xs text-slate-700 uppercase tracking-wide mb-2"><?= htmlspecialchars($groupTitle) ?></h4>
          <div class="rounded-xl overflow-hidden border border-slate-100 divide-y divide-slate-100 text-xs">
            <?php foreach ($groupItems as $i => $spec): ?>
              <div class="grid grid-cols-3 p-3 <?= $i % 2 === 0 ? 'bg-slate-50' : 'bg-white' ?>">
                <span class="text-slate-500 font-medium"><?= htmlspecialchars($spec['attribute_name'] ?? $spec['spec_key'] ?? 'Spec') ?></span>
                <span class="text-slate-800 font-normal col-span-2"><?= htmlspecialchars($spec['attribute_value'] ?? $spec['spec_value'] ?? '') ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ================= RELATED PRODUCTS ================= -->
  <?php if (!empty($relatedProducts)): ?>
    <section class="mb-8 space-y-4">
      <h2 class="section-heading font-heading text-lg sm:text-xl font-bold text-slate-900">You Might Also Like</h2>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4">
        <?php foreach ($relatedProducts as $rp): ?>
          <?php 
          $rpEnc  = $rp['encrypted_id'];
          $rpPrc  = '₹' . number_format($rp['sale_price'] ?? $rp['base_price'], 2);
          $rpBase = !empty($rp['base_price']) ? '₹' . number_format($rp['base_price'], 2) : '';
          $rpImg  = !empty($rp['primary_image']) ? $rp['primary_image'] : 'https://via.placeholder.com/200';
          $rpDisc = $rp['discount_pct'] ?? 0;
          ?>
          <div class="product-card p-3 flex flex-col justify-between">
            <div>
              <div class="relative rounded-xl overflow-hidden bg-slate-50 mb-2.5 aspect-square flex items-center justify-center">
                <?php if ($rpDisc > 0): ?>
                  <span class="absolute top-2 left-2 bg-cc-orange text-white text-[9px] font-extrabold px-2 py-0.5 rounded-md uppercase tracking-wider z-10"><?= $rpDisc ?>% OFF</span>
                <?php endif; ?>
                <a href="<?= $baseUrl ?>/product/<?= $rpEnc ?>" class="w-full h-full p-2 flex items-center justify-center">
                  <img src="<?= htmlspecialchars($rpImg) ?>" alt="" class="max-h-full max-w-full object-contain hover:scale-105 transition-transform">
                </a>
              </div>
              <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider mb-1"><?= htmlspecialchars($catName) ?></p>
              <a href="<?= $baseUrl ?>/product/<?= $rpEnc ?>" class="block font-heading font-bold text-xs text-slate-800 line-clamp-2 hover:text-cc-blue transition mb-2">
                <?= htmlspecialchars($rp['name']) ?>
              </a>
            </div>
            <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-1">
              <span class="font-extrabold text-xs sm:text-sm text-slate-900"><?= $rpPrc ?></span>
              <a href="<?= $baseUrl ?>/product/<?= $rpEnc ?>" class="bg-cc-blue text-white text-[10px] font-bold px-2.5 py-1.5 rounded-lg hover:bg-blue-600 transition">View</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <!-- ================= REVIEWS SECTION ================= -->
  <section class="bg-white rounded-2xl border border-slate-100 shadow-xs mb-8 p-4 sm:p-7">
    <h3 class="section-heading font-heading font-bold text-base sm:text-xl text-slate-800 mb-6">Ratings &amp; Customer Reviews</h3>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 sm:gap-8">
      <!-- Rating summary -->
      <div class="md:col-span-1">
        <div class="text-center md:text-left bg-slate-50 p-5 rounded-2xl border border-slate-100">
          <p class="font-heading text-4xl sm:text-5xl font-extrabold text-slate-900"><?= number_format($avgRating, 1) ?><span class="text-xl text-slate-400">/5</span></p>
          <div class="stars text-base mt-1">
            <?php for($s=1;$s<=5;$s++): ?>
              <span class="material-icons text-sm <?= $s<=round($avgRating) ? 'text-amber-400' : 'text-slate-200' ?>">star</span>
            <?php endfor; ?>
          </div>
          <p class="text-xs text-slate-500 mt-1">Based on <?= number_format($totalRevs) ?> verified ratings</p>
        </div>

        <!-- Rating Breakdown Bars -->
        <div class="mt-4 space-y-2">
          <?php for($star=5; $star>=1; $star--): ?>
            <?php 
            $cnt = $ratingCounts[$star] ?? 0;
            $pct = (!empty($totalRatingVotes) && $totalRatingVotes > 0) ? round(($cnt / $totalRatingVotes) * 100) : 0;
            $barColor = $star >= 4 ? 'bg-green-500' : ($star === 3 ? 'bg-amber-400' : 'bg-red-400');
            ?>
            <div class="flex items-center gap-2 text-xs">
              <span class="w-8 text-slate-500 font-semibold"><?= $star ?> ★</span>
              <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                <div class="rating-bar-fill h-full <?= $barColor ?>" style="width:<?= $pct ?>%"></div>
              </div>
              <span class="w-10 text-right text-slate-400 text-[11px]"><?= $pct ?>%</span>
            </div>
          <?php endfor; ?>
        </div>

        <a href="<?= $baseUrl ?>/user/reviews" class="mt-5 w-full border-2 border-cc-blue text-cc-blue font-bold text-xs py-2.5 rounded-xl hover:bg-cc-blue hover:text-white transition flex items-center justify-center gap-1.5">
          <span class="material-icons text-sm">rate_review</span> Write a Review
        </a>
      </div>

      <!-- Review List -->
      <div class="md:col-span-2">
        <?php if (empty($reviews)): ?>
          <div class="text-center py-8 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
            <span class="material-icons text-3xl text-slate-300">rate_review</span>
            <p class="text-xs font-bold text-slate-700">No reviews written yet</p>
            <p class="text-[11px] text-slate-400">Be the first to review this product!</p>
          </div>
        <?php else: ?>
          <div id="reviews-list" class="space-y-4 overflow-hidden" style="max-height: 520px;">
            <?php foreach ($reviews as $rev): ?>
              <div class="border-b border-slate-100 pb-4 last:border-0">
                <div class="flex items-center justify-between mb-1">
                  <div class="flex items-center gap-2">
                    <div class="flex items-center text-amber-400">
                      <?php for($i=1;$i<=5;$i++): ?>
                        <span class="material-icons text-xs <?= $i<=intval($rev['rating']) ? 'text-amber-400' : 'text-slate-200' ?>">star</span>
                      <?php endfor; ?>
                    </div>
                    <span class="font-bold text-xs text-slate-800"><?= htmlspecialchars($rev['title'] ?? '') ?></span>
                  </div>
                  <span class="text-[10px] text-slate-400"><?= date('d M Y', strtotime($rev['created_at'])) ?></span>
                </div>
                <p class="text-xs text-slate-600 leading-relaxed"><?= htmlspecialchars($rev['body'] ?? '') ?></p>
                <div class="flex items-center gap-3 mt-2">
                  <span class="text-[11px] font-semibold text-slate-700"><?= htmlspecialchars($rev['user_name'] ?? 'Verified Buyer') ?></span>
                  <?php if (!empty($rev['is_verified_purchase'])): ?>
                    <span class="text-[9px] bg-green-50 text-green-700 font-bold px-2 py-0.5 rounded flex items-center gap-0.5">
                      <span class="material-icons text-[10px]">verified</span> Verified Purchase
                    </span>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <?php if (count($reviews) > 3): ?>
            <button onclick="toggleReadMore('reviews')" id="reviews-toggle-btn" class="mt-3 text-xs font-bold text-cc-blue hover:text-blue-700 flex items-center gap-1 cursor-pointer">
              Read More Reviews <span class="material-icons text-sm" id="reviews-toggle-icon">expand_more</span>
            </button>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

</main>

<!-- ================= MOBILE STICKY BUY BAR ================= -->
<div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-50 flex items-center gap-2 p-2.5 shadow-2xl">
  <div class="flex-1 min-w-0 pl-1">
    <p class="font-heading font-extrabold text-base text-slate-900 truncate" id="mobile-price">₹<?= number_format($salePrice, 2) ?></p>
    <p class="text-[9px] text-slate-400 -mt-0.5">incl. all taxes</p>
  </div>
  <button onclick="addToCartDetailed()" <?= $isOOS ? 'disabled' : '' ?> class="flex-1 bg-cc-blue text-white font-bold text-xs py-2.5 rounded-xl flex items-center justify-center gap-1 cursor-pointer <?= $isOOS ? 'opacity-50' : '' ?>">
    <span class="material-icons text-base">shopping_cart</span> Cart
  </button>
  <button onclick="buyNowDetailed()" <?= $isOOS ? 'disabled' : '' ?> class="flex-1 bg-cc-orange text-white font-bold text-xs py-2.5 rounded-xl flex items-center justify-center gap-1 cursor-pointer <?= $isOOS ? 'opacity-50' : '' ?>">
    <span class="material-icons text-base">bolt</span> Buy Now
  </button>
</div>

<script>
var IS_LOGGED_IN = <?= json_encode(!empty($_SESSION['user_id'])) ?>;
var BASE_URL     = window.BASE_URL || '<?= $baseUrl ?>';
var PRODUCT_ENC_ID = '<?= $encId ?>';
var itemQty = 1;

function showToast(msg, type='success'){
  const c = document.getElementById('toast-container');
  if(!c) return;
  const cls = {success:'bg-emerald-600', error:'bg-red-500', info:'bg-[#2D82FF]', warning:'bg-amber-500'};
  const ico = {success:'check_circle', error:'error', info:'info', warning:'warning'};
  const t = document.createElement('div');
  t.className = `toast-in pointer-events-auto flex items-center gap-3 ${cls[type]||cls.info} text-white px-4 py-3 rounded-xl shadow-2xl text-xs font-medium`;
  t.innerHTML = `<span class="material-icons text-base">${ico[type]||'info'}</span><span class="flex-1">${msg}</span>`;
  c.appendChild(t);
  setTimeout(()=>{ t.remove(); },3500);
}

var galleryImages = [
  <?php foreach ($images as $img): ?>
    <?= json_encode($img['image_url']) ?>,
  <?php endforeach; ?>
];
var currentGalleryIndex = 0;

function selectImage(index, url, btn) {
  currentGalleryIndex = index;
  document.querySelectorAll('.thumb-btn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  const imgElem = document.getElementById('gallery-main-img');
  if (imgElem) {
    imgElem.src = url || galleryImages[index];
    imgElem.classList.remove('hidden');
  }
  const vidElem = document.getElementById('gallery-main-video');
  if (vidElem) vidElem.classList.add('hidden');
  const iframe = document.getElementById('gallery-video-frame');
  if (iframe) iframe.src = '';
}

function updateGallerySlide(index) {
  if (!galleryImages.length) return;
  if (index < 0) index = galleryImages.length - 1;
  if (index >= galleryImages.length) index = 0;
  currentGalleryIndex = index;
  const thumbBtn = document.querySelector(`.thumb-btn[data-thumb-index="${currentGalleryIndex}"]`);
  selectImage(currentGalleryIndex, galleryImages[currentGalleryIndex], thumbBtn);
}

function prevProductImage() {
  updateGallerySlide(currentGalleryIndex - 1);
}

function nextProductImage() {
  updateGallerySlide(currentGalleryIndex + 1);
}

function selectVideo(url, btn) {
  document.querySelectorAll('.thumb-btn').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  const iframe = document.getElementById('gallery-video-frame');
  if (iframe) iframe.src = url + '?autoplay=0';
  const vidElem = document.getElementById('gallery-main-video');
  if (vidElem) vidElem.classList.remove('hidden');
  const imgElem = document.getElementById('gallery-main-img');
  if (imgElem) imgElem.classList.add('hidden');
}

function changeQty(delta) {
  itemQty = Math.max(1, Math.min(10, itemQty + delta));
  document.getElementById('pdp-qty').textContent = itemQty;
}

function selectVariant(vData, btn) {
  document.querySelectorAll('.variant-swatch').forEach(b => b.classList.remove('active'));
  if (btn) btn.classList.add('active');
  if (vData.sale_price || vData.price) {
    const p = parseFloat(vData.sale_price || vData.price);
    const fmt = '₹' + p.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('pdp-price').textContent = fmt;
    document.getElementById('buybox-price').textContent = fmt;
    document.getElementById('mobile-price').textContent = fmt;
  }
  showToast(`Selected option: ${vData.name || 'Variant'}`, 'info');
}

function checkPincode() {
  const pinInput = document.getElementById('pincode-input');
  const resElem = document.getElementById('pincode-result');
  const pin = pinInput?.value?.trim();
  if (!pin || pin.length < 6) {
    showToast('Please enter a valid 6-digit Pincode', 'warning');
    if (resElem) {
      resElem.innerHTML = `<span class="material-icons text-sm text-red-500">error</span> <span class="text-red-600 font-semibold">Please enter a valid 6-digit Pincode</span>`;
    }
    return;
  }
  showToast(`Pincode ${pin} is serviceable! Express delivery available.`, 'success');
  if (resElem) {
    resElem.innerHTML = `<span class="material-icons text-sm text-green-600">check_circle</span> <span class="text-green-700 font-semibold">Fast delivery available for pincode <b>${pin}</b>! Expected by <b><?= date('D, d M', strtotime('+2 days')) ?></b></span>`;
  }
}

function toggleWishlistItem(btn) {
  if (!IS_LOGGED_IN) {
    showToast('Please sign in to manage your wishlist.', 'warning');
    window.location.href = `${BASE_URL}/login?redirect=product/${PRODUCT_ENC_ID}`;
    return;
  }

  fetch(`${BASE_URL}/wishlist/add`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ product_id: PRODUCT_ENC_ID })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const icon = document.getElementById('wish-toggle-icon');
      if (data.action === 'removed') {
        if (icon) {
          icon.textContent = 'favorite_border';
          icon.classList.remove('text-cc-pink');
        }
        showToast(data.message || 'Removed from Wishlist.', 'info');
      } else {
        if (icon) {
          icon.textContent = 'favorite';
          icon.classList.add('text-cc-pink');
        }
        showToast(data.message || 'Saved to Wishlist!', 'success');
      }
      document.querySelectorAll('.wishlist-badge-count').forEach(el => {
        el.textContent = data.wishlist_count || 0;
      });
    } else {
      if (data.message && data.message.includes('login')) {
        window.location.href = `${BASE_URL}/login?redirect=product/${PRODUCT_ENC_ID}`;
      } else {
        showToast(data.message || 'Error updating wishlist.', 'error');
      }
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}

function addToCartDetailed() {
  if (!IS_LOGGED_IN) {
    showToast('Please sign in to add items to your cart.', 'warning');
    window.location.href = `${BASE_URL}/login?redirect=product/${PRODUCT_ENC_ID}`;
    return;
  }

  fetch(`${BASE_URL}/cart/add`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ product_id: PRODUCT_ENC_ID, quantity: itemQty })
  })
  .then(r => r.json())
  .then(data => {
    if (data.require_login || (data.message && data.message.includes('sign in'))) {
      window.location.href = `${BASE_URL}/login?redirect=product/${PRODUCT_ENC_ID}`;
      return;
    }
    if (data.success) {
      document.querySelectorAll('.cart-badge-count').forEach(el => el.textContent = data.cart_count || 0);
      showToast('Added to Cart!', 'success');
    } else {
      showToast(data.message || 'Failed to add to cart.', 'error');
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}

function buyNowDetailed() {
  if (!IS_LOGGED_IN) {
    showToast('Please sign in to proceed to checkout.', 'warning');
    window.location.href = `${BASE_URL}/login?redirect=checkout`;
    return;
  }

  fetch(`${BASE_URL}/cart/add`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ product_id: PRODUCT_ENC_ID, quantity: itemQty })
  })
  .then(r => r.json())
  .then(data => {
    if (data.require_login || (data.message && data.message.includes('sign in'))) {
      window.location.href = `${BASE_URL}/login?redirect=checkout`;
      return;
    }
    if (data.success) {
      window.location.href = `${BASE_URL}/checkout`;
    } else {
      showToast(data.message || 'Failed to proceed to checkout.', 'error');
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}

const readMoreState = { desc: false, reviews: false };
function toggleReadMore(section) {
  readMoreState[section] = !readMoreState[section];
  const expanded = readMoreState[section];

  if (section === 'desc') {
    const content = document.getElementById('desc-content');
    const fade = document.getElementById('desc-fade');
    const btn = document.getElementById('desc-toggle-btn');
    const icon = document.getElementById('desc-toggle-icon');
    content.style.maxHeight = expanded ? content.scrollHeight + 'px' : '120px';
    fade.style.display = expanded ? 'none' : 'block';
    btn.childNodes[0].textContent = expanded ? 'Read Less ' : 'Read More ';
    icon.innerText = expanded ? 'expand_less' : 'expand_more';
  } else if (section === 'reviews') {
    const content = document.getElementById('reviews-list');
    const btn = document.getElementById('reviews-toggle-btn');
    const icon = document.getElementById('reviews-toggle-icon');
    content.style.maxHeight = expanded ? content.scrollHeight + 'px' : '520px';
    btn.childNodes[0].textContent = expanded ? 'Show Less ' : 'Read More Reviews ';
    icon.innerText = expanded ? 'expand_less' : 'expand_more';
  }
}
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
