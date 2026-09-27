<?php
require_once __DIR__ . '/layouts/header.php';

$categories = $categories ?? $activeCategories ?? [];
$featuredProducts = $featuredProducts ?? [];
$allProducts = $allProducts ?? [];
?>

<main class="max-w-7xl mx-auto px-3 sm:px-4 py-4 sm:py-6 space-y-6 sm:space-y-10 w-full overflow-x-hidden">

  <!-- ================= HERO SECTION ================= -->
  <section class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-5 w-full">

    <!-- Categories Sidebar (Desktop) -->
    <aside class="hidden lg:block bg-white rounded-2xl shadow-xs p-3.5 h-fit border border-slate-100">
      <p class="text-xs font-bold text-slate-400 uppercase tracking-wider px-2.5 pb-2">Top Categories</p>
      <ul class="text-xs space-y-0.5">
        <?php foreach (array_slice($categories, 0, 9) as $cat): ?>
          <li>
            <a href="<?= $baseUrl ?>/category/<?= htmlspecialchars($cat['slug'] ?? '') ?>" class="w-full text-left sidebar-link flex justify-between items-center font-medium text-slate-700">
              <span class="flex items-center gap-2">
                <span class="material-icons text-slate-400 text-[18px]">category</span>
                <?= htmlspecialchars($cat['name']) ?>
              </span>
              <span class="text-slate-400 text-xs">&rsaquo;</span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </aside>

<?php
$heroOffers  = $heroOffers  ?? [];
$heroBanners = $heroBanners ?? [];

// Build combined slides array for the hero offer slider
$slides = [];

if (!empty($heroOffers)) {
    $grads = ['from-cc-purple via-indigo-900 to-slate-900', 'from-slate-900 via-purple-900 to-cc-orange', 'from-indigo-900 via-slate-900 to-cc-blue'];
    $gi = 0;
    foreach ($heroOffers as $ho) {
        $slides[] = [
            'title'       => $ho['title'] ?? $ho['name'] ?? 'Special Promotional Sale',
            'badge'       => strtoupper($ho['offer_type'] ?? 'LIMITED TIME OFFER'),
            'desc'        => $ho['description'] ?? 'Enjoy special discounts applied automatically at checkout.',
            'link'        => $baseUrl . '/offer/' . ($ho['encrypted_id'] ?? $ho['id']),
            'image'       => !empty($ho['banner_image']) ? $ho['banner_image'] : (!empty($ho['image_url']) ? $ho['image_url'] : null),
            'bg_gradient' => $grads[$gi++ % count($grads)],
            'discount'    => !empty($ho['discount_value']) ? (float)$ho['discount_value'] : null,
        ];
    }
}

if (!empty($heroBanners)) {
    foreach ($heroBanners as $hb) {
        $slides[] = [
            'title'       => $hb['title'] ?? 'Exclusive Store Promotion',
            'badge'       => 'HOT DEAL',
            'desc'        => $hb['subtitle'] ?? 'Discover storewide active promotional offers.',
            'link'        => !empty($hb['link_url']) ? $hb['link_url'] : ($baseUrl . '/offers'),
            'image'       => !empty($hb['image_url']) ? $hb['image_url'] : null,
            'bg_gradient' => 'from-blue-900 via-indigo-900 to-purple-900',
            'discount'    => null,
        ];
    }
}

// Fallback slides if DB offers & banners are empty
if (empty($slides)) {
    $slides = [
        [
            'title'       => 'The Big Tech & Style Sale',
            'badge'       => 'LIMITED TIME OFFER',
            'desc'        => 'Up to <span class="font-extrabold text-cc-yellow text-sm sm:text-lg">70% OFF</span> on smartphones, electronics, fashion & audio gear.',
            'link'        => $baseUrl . '/offer/1',
            'image'       => null,
            'bg_gradient' => 'from-cc-purple via-indigo-900 to-slate-900',
            'discount'    => 70,
        ],
        [
            'title'       => 'Summer Electronics Festival',
            'badge'       => 'MEGA DISCOUNT',
            'desc'        => 'Save up to <span class="font-extrabold text-cc-yellow text-sm sm:text-lg">50% FLAT OFF</span> on laptops, audio & smart devices.',
            'link'        => $baseUrl . '/offers',
            'image'       => null,
            'bg_gradient' => 'from-slate-900 via-purple-900 to-cc-orange',
            'discount'    => 50,
        ],
        [
            'title'       => 'Trending Fashion Extravaganza',
            'badge'       => 'NEW ARRIVALS',
            'desc'        => 'Extra <span class="font-extrabold text-cc-yellow text-sm sm:text-lg">₹2,000 OFF</span> on premium apparel & footwear brands.',
            'link'        => $baseUrl . '/offers',
            'image'       => null,
            'bg_gradient' => 'from-indigo-900 via-slate-900 to-cc-blue',
            'discount'    => null,
        ],
    ];
}
?>

    <!-- Main Hero Offer Slider Card -->
    <div class="lg:col-span-3 rounded-2xl overflow-hidden relative text-white shadow-xl w-full min-h-[260px] sm:min-h-[340px] flex flex-col justify-center bg-slate-900">
      
      <!-- Slider Container -->
      <div id="hero-slider-container" class="relative w-full h-full min-h-[260px] sm:min-h-[340px]">
        <?php foreach ($slides as $idx => $s): ?>
          <div class="hero-slide absolute inset-0 transition-opacity duration-700 ease-in-out <?= $idx === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none' ?>" data-slide-index="<?= $idx ?>">
            
            <?php if (!empty($s['image'])): ?>
              <!-- 100% Crystal Clear Clickable Graphic Banner Image -->
              <a href="<?= htmlspecialchars($s['link']) ?>" class="block w-full h-full relative group cursor-pointer overflow-hidden rounded-2xl" title="<?= htmlspecialchars($s['title']) ?>">
                <img src="<?= htmlspecialchars($s['image']) ?>" alt="<?= htmlspecialchars($s['title']) ?>" class="w-full h-full object-cover block rounded-2xl transition-transform duration-500 group-hover:scale-[1.012]">
              </a>
            <?php else: ?>
              <!-- Fallback Gradient Slide (only when no banner image is provided) -->
              <a href="<?= htmlspecialchars($s['link']) ?>" class="block w-full h-full p-4 sm:p-8 md:p-12 flex flex-col justify-center bg-gradient-to-br <?= $s['bg_gradient'] ?> rounded-2xl relative overflow-hidden group cursor-pointer">
                <div class="relative z-10 max-w-xl space-y-2 sm:space-y-3">
                  <span class="bg-cc-orange inline-flex items-center gap-1.5 text-[10px] sm:text-xs font-bold px-3 sm:px-4 py-1 sm:py-1.5 rounded-full uppercase tracking-wider shadow-md">
                    <span class="material-icons text-[14px] sm:text-[16px]">bolt</span> <?= htmlspecialchars($s['badge']) ?>
                  </span>

                  <h1 class="font-heading text-xl sm:text-3xl md:text-5xl font-extrabold leading-tight text-white drop-shadow-md">
                    <?= htmlspecialchars($s['title']) ?>
                  </h1>

                  <p class="text-white/90 text-xs sm:text-base leading-relaxed drop-shadow-sm">
                    <?= $s['desc'] ?>
                  </p>

                  <div class="pt-2 sm:pt-4 flex items-center">
                    <span class="bg-white text-cc-purple group-hover:bg-cc-yellow group-hover:text-cc-dark font-extrabold px-5 sm:px-7 py-2.5 sm:py-3.5 rounded-xl transition shadow-lg text-[11px] sm:text-xs uppercase tracking-wide flex items-center gap-1.5">
                      Shop Offer Deals &rarr;
                    </span>
                  </div>
                </div>
              </a>
            <?php endif; ?>

          </div>
        <?php endforeach; ?>
      </div>

      <!-- Slider Controls (Prev / Next & Dots) -->
      <?php if (count($slides) > 1): ?>
        <button onclick="changeHeroSlide(-1)" aria-label="Previous Offer" class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center backdrop-blur-xs transition active:scale-95 cursor-pointer border border-white/20">
          <span class="material-icons text-[20px] sm:text-[24px]">chevron_left</span>
        </button>

        <button onclick="changeHeroSlide(1)" aria-label="Next Offer" class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-black/40 hover:bg-black/70 text-white flex items-center justify-center backdrop-blur-xs transition active:scale-95 cursor-pointer border border-white/20">
          <span class="material-icons text-[20px] sm:text-[24px]">chevron_right</span>
        </button>

        <div class="absolute bottom-3 right-4 sm:bottom-4 sm:right-6 z-20 flex items-center gap-1.5">
          <?php foreach ($slides as $idx => $s): ?>
            <button onclick="goToHeroSlide(<?= $idx ?>)" aria-label="Slide <?= $idx+1 ?>" class="hero-dot w-2 sm:w-2.5 h-2 sm:h-2.5 rounded-full transition-all duration-300 cursor-pointer <?= $idx === 0 ? 'bg-white w-6 sm:w-7' : 'bg-white/40 hover:bg-white/70' ?>" data-dot-index="<?= $idx ?>"></button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

    </div>
  </section>

  <script>
  let currentHeroSlide = 0;
  const totalHeroSlides = <?= count($slides) ?>;
  let heroSlideTimer = null;

  function showHeroSlide(index) {
    if (totalHeroSlides <= 1) return;
    const slides = document.querySelectorAll('.hero-slide');
    const dots   = document.querySelectorAll('.hero-dot');
    
    if (index >= totalHeroSlides) currentHeroSlide = 0;
    else if (index < 0) currentHeroSlide = totalHeroSlides - 1;
    else currentHeroSlide = index;

    slides.forEach((slide, i) => {
      if (i === currentHeroSlide) {
        slide.classList.remove('opacity-0', 'z-0', 'pointer-events-none');
        slide.classList.add('opacity-100', 'z-10');
      } else {
        slide.classList.remove('opacity-100', 'z-10');
        slide.classList.add('opacity-0', 'z-0', 'pointer-events-none');
      }
    });

    dots.forEach((dot, i) => {
      if (i === currentHeroSlide) {
        dot.classList.remove('bg-white/40', 'w-2', 'sm:w-2.5');
        dot.classList.add('bg-white', 'w-6', 'sm:w-7');
      } else {
        dot.classList.remove('bg-white', 'w-6', 'sm:w-7');
        dot.classList.add('bg-white/40', 'w-2', 'sm:w-2.5');
      }
    });
  }

  function changeHeroSlide(direction) {
    showHeroSlide(currentHeroSlide + direction);
    resetHeroSlideTimer();
  }

  function goToHeroSlide(index) {
    showHeroSlide(index);
    resetHeroSlideTimer();
  }

  function resetHeroSlideTimer() {
    if (heroSlideTimer) clearInterval(heroSlideTimer);
    if (totalHeroSlides > 1) {
      heroSlideTimer = setInterval(() => {
        showHeroSlide(currentHeroSlide + 1);
      }, 5000);
    }
  }

  document.addEventListener('DOMContentLoaded', () => {
    resetHeroSlideTimer();
  });
  </script>

  <!-- ================= TOP CATEGORIES SLIDER ================= -->
  <section id="categories-section" class="relative w-full overflow-hidden">
    <div class="flex items-center justify-between mb-3 sm:mb-4">
      <div>
        <h2 class="font-heading text-lg sm:text-xl font-bold text-slate-900 section-heading">Explore Top Categories</h2>
      </div>
      <div class="flex items-center gap-1.5 sm:gap-2">
        <button onclick="slideCategories(-1)" aria-label="Slide Left" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white border border-slate-200 hover:bg-slate-100 hover:border-cc-blue text-slate-600 hover:text-cc-blue flex items-center justify-center shadow-xs transition active:scale-95 cursor-pointer shrink-0">
          <span class="material-icons text-[16px] sm:text-[18px]">chevron_left</span>
        </button>
        <button onclick="slideCategories(1)" aria-label="Slide Right" class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-white border border-slate-200 hover:bg-slate-100 hover:border-cc-blue text-slate-600 hover:text-cc-blue flex items-center justify-center shadow-xs transition active:scale-95 cursor-pointer shrink-0">
          <span class="material-icons text-[16px] sm:text-[18px]">chevron_right</span>
        </button>
      </div>
    </div>

    <div id="category-slider" class="flex items-center gap-2.5 sm:gap-4 overflow-x-auto scrollbar-none scroll-smooth pb-2 pt-1 w-full max-w-full">
      <?php foreach ($categories as $cat): 
        $catName = htmlspecialchars($cat['name'] ?? '');
        $catLower = strtolower($catName);
        $icon = 'category';
        if (strpos($catLower, 'electr') !== false || strpos($catLower, 'tech') !== false) $icon = 'devices';
        elseif (strpos($catLower, 'fict') !== false || strpos($catLower, 'book') !== false) $icon = 'menu_book';
        elseif (strpos($catLower, 'gym') !== false || strpos($catLower, 'fit') !== false || strpos($catLower, 'sport') !== false) $icon = 'fitness_center';
        elseif (strpos($catLower, 'kitch') !== false || strpos($catLower, 'home') !== false) $icon = 'soup_kitchen';
        elseif (strpos($catLower, 'cloth') !== false || strpos($catLower, 'fash') !== false || strpos($catLower, 'apparel') !== false) $icon = 'checkroom';
        elseif (strpos($catLower, 'skin') !== false || strpos($catLower, 'beaut') !== false) $icon = 'face';
      ?>
        <a href="<?= $baseUrl ?>/category/<?= htmlspecialchars($cat['slug'] ?? '') ?>" class="cat-pill bg-white p-3 sm:p-4 rounded-xl sm:rounded-2xl text-center border border-slate-100 shadow-xs flex flex-col items-center justify-center gap-1.5 sm:gap-2 group w-[105px] sm:w-[140px] shrink-0 hover:shadow-md transition">
          <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-slate-50 group-hover:bg-cc-blue/10 flex items-center justify-center text-cc-blue transition shrink-0">
            <span class="material-icons text-[22px] sm:text-[26px]"><?= $icon ?></span>
          </div>
          <span class="font-bold text-[11px] sm:text-xs text-slate-800 group-hover:text-cc-blue transition truncate w-full px-1"><?= $catName ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <script>
    function slideCategories(direction) {
      const container = document.getElementById('category-slider');
      if (container) {
        const scrollAmount = container.clientWidth * 0.75;
        container.scrollBy({ left: direction * scrollAmount, behavior: 'smooth' });
      }
    }
  </script>

  <!-- ================= FLASH DEALS SECTION ================= -->
  <section id="flash-deals" class="bg-white rounded-2xl p-4 sm:p-6 border border-slate-100 shadow-xs space-y-4 sm:space-y-6 w-full">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
      <div class="flex items-center gap-3">
        <span class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-cc-orange/10 flex items-center justify-center text-cc-orange shrink-0">
          <span class="material-icons text-[20px] sm:text-[22px]">flash_on</span>
        </span>
        <div>
          <h2 class="font-heading text-lg sm:text-xl font-bold text-slate-900">Deals of the Day</h2>
          <p class="text-slate-400 text-xs">Handpicked deals with highest discounts</p>
        </div>
      </div>

      <div class="flex items-center gap-3">
        <a href="<?= $baseUrl ?>/offers" class="inline-flex items-center gap-1.5 text-xs font-bold text-cc-orange hover:text-orange-700 bg-orange-50 hover:bg-orange-100 border border-orange-200 px-3 py-1.5 rounded-xl transition">
          <span class="material-icons text-[14px]">local_offer</span> View All Offers
        </a>
        <div class="flex items-center gap-2 text-xs font-bold text-slate-700 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
          <span class="text-slate-400 uppercase">Ends in:</span>
          <span class="mono bg-cc-dark text-white px-2 py-0.5 rounded font-bold">05h</span> :
          <span class="mono bg-cc-dark text-white px-2 py-0.5 rounded font-bold">23m</span> :
          <span class="mono bg-cc-orange text-white px-2 py-0.5 rounded font-bold">42s</span>
        </div>
      </div>
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <?php 
      $dealProducts = !empty($featuredProducts) ? $featuredProducts : $allProducts;
      foreach (array_slice($dealProducts, 0, 4) as $p):
        $price = (float)$p['base_price'];
        $salePrice = !empty($p['sale_price']) ? (float)$p['sale_price'] : $price * 0.85;
        $discPercent = !empty($p['discount_percent']) ? (int)$p['discount_percent'] : 15;
      ?>
        <div class="product-card p-3 sm:p-4 flex flex-col justify-between">
          <div>
            <div class="relative rounded-xl overflow-hidden bg-slate-50 mb-3 aspect-square flex items-center justify-center">
              <?php if (!empty($p['main_image_url'])): ?>
                <img src="<?= htmlspecialchars($p['main_image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="object-cover w-full h-full">
              <?php else: ?>
                <span class="material-icons text-slate-300 text-5xl">inventory_2</span>
              <?php endif; ?>

              <span class="absolute top-2 left-2 bg-cc-orange text-white text-[10px] font-extrabold px-2 py-0.5 rounded-md uppercase tracking-wider">
                -<?= $discPercent ?>% OFF
              </span>

              <!-- SEPARATE WISHLIST LINK -->
              <a href="<?= $baseUrl ?>/wishlist" class="absolute top-2 right-2 w-7 h-7 rounded-full bg-white/80 hover:bg-white text-slate-400 hover:text-cc-pink flex items-center justify-center shadow-md transition" title="Add to Wishlist">
                <span class="material-icons text-[16px]">favorite_border</span>
              </a>
            </div>

            <p class="text-[10px] sm:text-[11px] text-slate-400 uppercase font-bold tracking-wider mb-1 truncate"><?= htmlspecialchars($p['category_name'] ?? 'Electronics') ?></p>
            
            <!-- SEPARATE PRODUCT DETAIL PAGE LINK -->
            <a href="<?= $baseUrl ?>/product/<?= htmlspecialchars($p['encrypted_id'] ?? $p['id']) ?>" class="font-heading font-bold text-xs sm:text-sm text-slate-900 hover:text-cc-blue transition line-clamp-2 mb-2">
              <?= htmlspecialchars($p['name']) ?>
            </a>
          </div>

          <div class="space-y-2 sm:space-y-3 pt-2 border-t border-slate-100">
            <div class="flex items-baseline gap-1.5 sm:gap-2 flex-wrap">
              <span class="font-heading font-extrabold text-sm sm:text-base text-slate-900">₹<?= number_format($salePrice, 2) ?></span>
              <span class="price-strike text-[10px] sm:text-xs font-mono">₹<?= number_format($price, 2) ?></span>
            </div>

            <!-- AJAX ADD TO CART BUTTON -->
            <button onclick="addToCartAJAX('<?= htmlspecialchars($p['encrypted_id'] ?? $p['id']) ?>', this)" class="w-full bg-cc-blue hover:bg-blue-600 text-white font-bold text-[10px] sm:text-xs py-2 sm:py-2.5 px-2 sm:px-4 rounded-xl flex items-center justify-center gap-1 sm:gap-2 transition shadow-md shadow-cc-blue/20 cursor-pointer">
              <span class="material-icons text-[14px] sm:text-[16px]">shopping_cart</span>
              <span>Add to Cart</span>
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ================= PROMOTIONAL BANNER STRIP ================= -->
  <section class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-5 w-full">
    <div class="bg-gradient-to-r from-slate-900 to-slate-800 text-white rounded-2xl p-5 sm:p-8 flex items-center justify-between shadow-md relative overflow-hidden">
      <div class="space-y-1.5 sm:space-y-2 max-w-xs relative z-10">
        <span class="text-cc-yellow text-[10px] sm:text-xs font-bold uppercase tracking-wider">Smartphones &amp; Audio</span>
        <h3 class="font-heading text-lg sm:text-2xl font-bold">Extra ₹2,000 Off on Premium Headphones</h3>
        <a href="<?= $baseUrl ?>/offers" class="inline-flex items-center gap-1 text-xs font-bold text-cc-blue hover:text-cc-yellow pt-1 sm:pt-2 font-mono transition">
          Shop Audio &rarr;
        </a>
      </div>
      <span class="material-icons text-white/10 text-[80px] absolute right-4 bottom-0">headphones</span>
    </div>
    <div class="bg-gradient-to-r from-cc-blue to-indigo-700 text-white rounded-2xl p-5 sm:p-8 flex items-center justify-between shadow-md relative overflow-hidden">
      <div class="space-y-1.5 sm:space-y-2 max-w-xs relative z-10">
        <span class="text-cc-yellow text-[10px] sm:text-xs font-bold uppercase tracking-wider">Trending Fashion</span>
        <h3 class="font-heading text-lg sm:text-2xl font-bold">Min 50% Off on Top Apparel Brands</h3>
        <a href="<?= $baseUrl ?>/offers" class="inline-flex items-center gap-1 text-xs font-bold text-white hover:text-cc-yellow pt-1 sm:pt-2 font-mono transition">
          Explore Offers &rarr;
        </a>
      </div>
      <span class="material-icons text-white/10 text-[80px] absolute right-4 bottom-0">checkroom</span>
    </div>
  </section>

  <!-- ================= FEATURED CATALOG GRID ================= -->
  <section id="featured-catalog" class="space-y-4 sm:space-y-6 w-full">
    <div class="flex items-center justify-between">
      <h2 class="font-heading text-lg sm:text-xl font-bold text-slate-900 section-heading">Featured Store Catalog</h2>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <?php foreach ($allProducts as $p): 
        $price = (float)$p['base_price'];
        $salePrice = !empty($p['sale_price']) ? (float)$p['sale_price'] : $price;
      ?>
        <div class="product-card p-3 sm:p-4 flex flex-col justify-between">
          <div>
            <div class="relative rounded-xl overflow-hidden bg-slate-50 mb-3 aspect-square flex items-center justify-center">
              <?php if (!empty($p['main_image_url'])): ?>
                <img src="<?= htmlspecialchars($p['main_image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="object-cover w-full h-full">
              <?php else: ?>
                <span class="material-icons text-slate-300 text-5xl">inventory_2</span>
              <?php endif; ?>

              <!-- SEPARATE WISHLIST LINK -->
              <a href="<?= $baseUrl ?>/wishlist" class="absolute top-2 right-2 w-7 h-7 rounded-full bg-white/80 hover:bg-white text-slate-400 hover:text-cc-pink flex items-center justify-center shadow-md transition" title="Add to Wishlist">
                <span class="material-icons text-[16px]">favorite_border</span>
              </a>
            </div>

            <p class="text-[10px] sm:text-[11px] text-slate-400 uppercase font-bold tracking-wider mb-1 truncate"><?= htmlspecialchars($p['category_name'] ?? 'Catalog') ?></p>
            
            <!-- SEPARATE PRODUCT DETAIL PAGE LINK -->
            <a href="<?= $baseUrl ?>/product/<?= htmlspecialchars($p['encrypted_id'] ?? $p['id']) ?>" class="font-heading font-bold text-xs sm:text-sm text-slate-900 hover:text-cc-blue transition line-clamp-2 mb-2">
              <?= htmlspecialchars($p['name']) ?>
            </a>
          </div>

          <div class="space-y-2 sm:space-y-3 pt-2 border-t border-slate-100">
            <div class="flex items-baseline gap-2">
              <span class="font-heading font-extrabold text-sm sm:text-base text-slate-900">₹<?= number_format($salePrice, 2) ?></span>
            </div>

            <!-- AJAX ADD TO CART BUTTON -->
            <button onclick="addToCartAJAX('<?= htmlspecialchars($p['encrypted_id'] ?? $p['id']) ?>', this)" class="w-full bg-slate-900 hover:bg-slate-800 text-white font-bold text-[10px] sm:text-xs py-2 sm:py-2.5 px-2 sm:px-4 rounded-xl flex items-center justify-center gap-1 sm:gap-2 transition shadow-md cursor-pointer">
              <span class="material-icons text-[14px] sm:text-[16px]">shopping_cart</span>
              <span>Add to Cart</span>
            </button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <!-- ================= TRUST & VALUE GUARANTEES (MOVED TO BOTTOM) ================= -->
  <section class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-8 border border-slate-100 shadow-xs space-y-5 sm:space-y-6 w-full">
    <div class="text-center max-w-xl mx-auto space-y-1">
      <span class="text-cc-orange text-[10px] sm:text-xs font-bold uppercase tracking-wider">Why Shop With Us</span>
      <h2 class="font-heading text-lg sm:text-2xl font-bold text-slate-900">Your Trusted Shopping Experience</h2>
      <p class="text-[11px] sm:text-xs text-slate-500">We prioritize customer satisfaction, guaranteed quality, and seamless support every step of the way.</p>
    </div>

    <!-- The 4 Main Feature Cards with Enriched Information -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
      <div class="bg-slate-50/80 hover:bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 transition shadow-xs hover:shadow-md flex items-start gap-3.5 sm:gap-4">
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-cc-blue/10 flex items-center justify-center text-cc-blue shrink-0">
          <span class="material-icons text-[22px] sm:text-[26px]">local_shipping</span>
        </div>
        <div class="space-y-1">
          <p class="font-bold text-xs sm:text-sm text-slate-800">Free Shipping</p>
          <p class="text-[11px] sm:text-xs text-slate-500 leading-snug">On orders ₹999+. Express 24-48 hr dispatch available nationwide.</p>
          <span class="inline-block text-[10px] font-bold text-cc-blue bg-blue-50 px-2 py-0.5 rounded-md mt-1">Fast &amp; Reliable</span>
        </div>
      </div>

      <div class="bg-slate-50/80 hover:bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 transition shadow-xs hover:shadow-md flex items-start gap-3.5 sm:gap-4">
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-green-50 flex items-center justify-center text-green-600 shrink-0">
          <span class="material-icons text-[22px] sm:text-[26px]">verified_user</span>
        </div>
        <div class="space-y-1">
          <p class="font-bold text-xs sm:text-sm text-slate-800">Secure Payments</p>
          <p class="text-[11px] sm:text-xs text-slate-500 leading-snug">100% Encrypted transactions via UPI, Credit/Debit Cards, NetBanking &amp; COD.</p>
          <span class="inline-block text-[10px] font-bold text-green-600 bg-green-50 px-2 py-0.5 rounded-md mt-1">256-Bit SSL Protection</span>
        </div>
      </div>

      <div class="bg-slate-50/80 hover:bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 transition shadow-xs hover:shadow-md flex items-start gap-3.5 sm:gap-4">
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-cc-purple/10 flex items-center justify-center text-cc-purple shrink-0">
          <span class="material-icons text-[22px] sm:text-[26px]">autorenew</span>
        </div>
        <div class="space-y-1">
          <p class="font-bold text-xs sm:text-sm text-slate-800">Easy Returns</p>
          <p class="text-[11px] sm:text-xs text-slate-500 leading-snug">7 Days return window with instant doorstep pickup and hassle-free replacements.</p>
          <span class="inline-block text-[10px] font-bold text-cc-purple bg-purple-50 px-2 py-0.5 rounded-md mt-1">No Questions Asked</span>
        </div>
      </div>

      <div class="bg-slate-50/80 hover:bg-white rounded-2xl p-4 sm:p-5 border border-slate-100 transition shadow-xs hover:shadow-md flex items-start gap-3.5 sm:gap-4">
        <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-2xl bg-amber-50 flex items-center justify-center text-amber-600 shrink-0">
          <span class="material-icons text-[22px] sm:text-[26px]">support_agent</span>
        </div>
        <div class="space-y-1">
          <p class="font-bold text-xs sm:text-sm text-slate-800">24/7 Support</p>
          <p class="text-[11px] sm:text-xs text-slate-500 leading-snug">Dedicated help desk with round-the-clock live chat, email, and call support.</p>
          <span class="inline-block text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md mt-1">Instant Assistance</span>
        </div>
      </div>
    </div>

    <!-- Additional Trust Highlights Strip -->
    <div class="pt-4 border-t border-slate-100 grid grid-cols-2 md:grid-cols-4 gap-3 text-center">
      <div class="flex items-center justify-center gap-1.5 sm:gap-2 text-slate-600">
        <span class="material-icons text-cc-blue text-[16px] sm:text-[18px]">workspace_premium</span>
        <span class="text-[11px] sm:text-xs font-semibold">100% Genuine Products</span>
      </div>
      <div class="flex items-center justify-center gap-1.5 sm:gap-2 text-slate-600">
        <span class="material-icons text-green-600 text-[16px] sm:text-[18px]">thumb_up</span>
        <span class="text-[11px] sm:text-xs font-semibold">50,000+ Happy Buyers</span>
      </div>
      <div class="flex items-center justify-center gap-1.5 sm:gap-2 text-slate-600">
        <span class="material-icons text-cc-purple text-[16px] sm:text-[18px]">sell</span>
        <span class="text-[11px] sm:text-xs font-semibold">Best Price Match</span>
      </div>
      <div class="flex items-center justify-center gap-1.5 sm:gap-2 text-slate-600">
        <span class="material-icons text-amber-500 text-[16px] sm:text-[18px]">verified</span>
        <span class="text-[11px] sm:text-xs font-semibold">4.9★ Store Rating</span>
      </div>
    </div>
  </section>

</main>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
