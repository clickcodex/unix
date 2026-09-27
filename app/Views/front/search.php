<?php
include __DIR__ . '/layouts/header.php';

$queryDisplay = htmlspecialchars($q ?? '');
$activeFilterCount = 0;
if (!empty($categorySlug)) $activeFilterCount++;
if ($minPrice !== null || $maxPrice !== null) $activeFilterCount++;
if ($minRating !== null) $activeFilterCount++;
if ($inStockOnly) $activeFilterCount++;
if ($minDiscount !== null) $activeFilterCount++;
?>

<!-- ================= CLEAN WHITE HEADER & BREADCRUMB ================= -->
<div class="bg-white border-b border-slate-200 py-4 sm:py-6 px-3 sm:px-4">
  <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
      <nav class="text-xs text-slate-400 mb-1 flex items-center gap-1">
        <a href="<?= $baseUrl ?>/" class="hover:text-cc-blue transition">Home</a>
        <span class="material-icons text-[12px]">chevron_right</span>
        <span class="text-slate-700 font-medium">Search Results</span>
      </nav>
      <h1 class="font-heading font-extrabold text-xl sm:text-2xl text-slate-900 flex items-center gap-2">
        <?php if (!empty($q)): ?>
          Results for "<span class="text-cc-blue font-bold"><?= $queryDisplay ?></span>"
        <?php else: ?>
          Product Catalog
        <?php endif; ?>
        <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full border border-slate-200">
          <?= count($products) ?> items
        </span>
      </h1>
    </div>

    <!-- Active Filter Chips / Clear All -->
    <?php if ($activeFilterCount > 0 || !empty($q)): ?>
      <div class="flex items-center gap-1.5 flex-wrap text-xs">
        <span class="text-slate-400 font-bold text-[11px] uppercase tracking-wider">Active:</span>
        <?php if (!empty($q)): ?>
          <a href="<?= $baseUrl ?>/search?<?= http_build_query(array_merge($_GET, ['q' => ''])) ?>" 
             class="bg-blue-50 text-cc-blue font-semibold px-2.5 py-1 rounded-lg border border-blue-200 hover:bg-blue-100 transition flex items-center gap-1">
            "<?= $queryDisplay ?>" <span class="material-icons text-xs">close</span>
          </a>
        <?php endif; ?>
        <?php if (!empty($categorySlug)): ?>
          <a href="<?= $baseUrl ?>/search?<?= http_build_query(array_merge($_GET, ['category' => ''])) ?>" 
             class="bg-blue-50 text-cc-blue font-semibold px-2.5 py-1 rounded-lg border border-blue-200 hover:bg-blue-100 transition flex items-center gap-1">
            Category: <?= htmlspecialchars($categorySlug) ?> <span class="material-icons text-xs">close</span>
          </a>
        <?php endif; ?>
        <?php if ($minPrice !== null || $maxPrice !== null): ?>
          <a href="<?= $baseUrl ?>/search?<?= http_build_query(array_merge($_GET, ['min_price' => '', 'max_price' => ''])) ?>" 
             class="bg-blue-50 text-cc-blue font-semibold px-2.5 py-1 rounded-lg border border-blue-200 hover:bg-blue-100 transition flex items-center gap-1">
            Price: ₹<?= $minPrice ?? 0 ?> - ₹<?= $maxPrice ?? 'Max' ?> <span class="material-icons text-xs">close</span>
          </a>
        <?php endif; ?>
        <?php if ($minRating !== null): ?>
          <a href="<?= $baseUrl ?>/search?<?= http_build_query(array_merge($_GET, ['rating' => ''])) ?>" 
             class="bg-blue-50 text-cc-blue font-semibold px-2.5 py-1 rounded-lg border border-blue-200 hover:bg-blue-100 transition flex items-center gap-1">
            <?= $minRating ?>★ &amp; Above <span class="material-icons text-xs">close</span>
          </a>
        <?php endif; ?>
        <?php if ($inStockOnly): ?>
          <a href="<?= $baseUrl ?>/search?<?= http_build_query(array_merge($_GET, ['in_stock' => ''])) ?>" 
             class="bg-blue-50 text-cc-blue font-semibold px-2.5 py-1 rounded-lg border border-blue-200 hover:bg-blue-100 transition flex items-center gap-1">
            In Stock Only <span class="material-icons text-xs">close</span>
          </a>
        <?php endif; ?>
        <?php if ($minDiscount !== null): ?>
          <a href="<?= $baseUrl ?>/search?<?= http_build_query(array_merge($_GET, ['discount' => ''])) ?>" 
             class="bg-blue-50 text-cc-blue font-semibold px-2.5 py-1 rounded-lg border border-blue-200 hover:bg-blue-100 transition flex items-center gap-1">
            <?= $minDiscount ?>%+ Off <span class="material-icons text-xs">close</span>
          </a>
        <?php endif; ?>
        <a href="<?= $baseUrl ?>/search" class="text-cc-orange font-bold hover:underline text-[11px] ml-1">Reset All</a>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- ================= MAIN CONTENT ================= -->
<main class="max-w-7xl mx-auto px-3 sm:px-4 py-4 sm:py-8">

  <!-- MOBILE CONTROL BAR (Single Sort Dropdown & Filter Drawer Toggle) -->
  <div class="sm:hidden mb-4 bg-white rounded-2xl border border-slate-200 p-2.5 shadow-xs flex items-center justify-between gap-2 sticky top-[60px] z-30">
    <!-- Filter Toggle Button -->
    <button onclick="toggleMobileFilterDrawer(true)" class="flex-1 bg-slate-900 text-white font-bold text-xs py-2 px-3 rounded-xl flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
      <span class="material-icons text-base">tune</span>
      <span>Filter</span>
      <?php if ($activeFilterCount > 0): ?>
        <span class="bg-cc-orange text-white text-[10px] font-extrabold rounded-full h-4 w-4 flex items-center justify-center"><?= $activeFilterCount ?></span>
      <?php endif; ?>
    </button>

    <!-- Mobile Sort Dropdown -->
    <form action="<?= $baseUrl ?>/search" method="GET" class="flex-1">
      <input type="hidden" name="q" value="<?= $queryDisplay ?>">
      <?php if (!empty($categorySlug)): ?><input type="hidden" name="category" value="<?= htmlspecialchars($categorySlug) ?>"><?php endif; ?>
      <?php if ($minPrice !== null): ?><input type="hidden" name="min_price" value="<?= $minPrice ?>"><?php endif; ?>
      <?php if ($maxPrice !== null): ?><input type="hidden" name="max_price" value="<?= $maxPrice ?>"><?php endif; ?>
      <?php if ($minRating !== null): ?><input type="hidden" name="rating" value="<?= $minRating ?>"><?php endif; ?>
      <?php if ($inStockOnly): ?><input type="hidden" name="in_stock" value="1"><?php endif; ?>
      <?php if ($minDiscount !== null): ?><input type="hidden" name="discount" value="<?= $minDiscount ?>"><?php endif; ?>

      <select name="sort" onchange="this.form.submit()" 
              class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-2 text-xs font-semibold text-slate-800 outline-none focus:border-cc-blue cursor-pointer">
        <option value="relevance" <?= $sort === 'relevance' ? 'selected' : '' ?>>Sort: Relevance</option>
        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Sort: Low to High</option>
        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Sort: High to Low</option>
        <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Sort: Highest Rated</option>
        <option value="popularity" <?= $sort === 'popularity' ? 'selected' : '' ?>>Sort: Most Popular</option>
        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Sort: Newest</option>
      </select>
    </form>
  </div>

  <div class="flex flex-col lg:flex-row gap-6">

    <!-- ================= DESKTOP HIGH QUALITY FILTER SIDEBAR ================= -->
    <aside class="hidden lg:block w-64 shrink-0 space-y-6">
      <form action="<?= $baseUrl ?>/search" method="GET" class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs space-y-5">
        <input type="hidden" name="q" value="<?= $queryDisplay ?>">
        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">

        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
            <span class="material-icons text-cc-blue text-base">tune</span> High-Quality Filters
          </h3>
          <?php if ($activeFilterCount > 0): ?>
            <a href="<?= $baseUrl ?>/search?q=<?= urlencode($q) ?>" class="text-xs text-cc-orange font-semibold hover:underline">Reset</a>
          <?php endif; ?>
        </div>

        <!-- 1. Categories Filter -->
        <div class="space-y-2">
          <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Category</h4>
          <select name="category" onchange="this.form.submit()"
                  class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-medium text-slate-800 outline-none focus:border-cc-blue cursor-pointer">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= ($categorySlug === $cat['slug'] || $categorySlug === $cat['name']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <hr class="border-slate-100">

        <!-- 2. Price Filter & Presets -->
        <div class="space-y-3">
          <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Price (₹)</h4>
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div>
              <label class="text-[10px] text-slate-400 font-bold block mb-1">MIN</label>
              <input type="number" name="min_price" value="<?= $minPrice !== null ? $minPrice : '' ?>" placeholder="0"
                     class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-slate-800 outline-none focus:border-cc-blue">
            </div>
            <div>
              <label class="text-[10px] text-slate-400 font-bold block mb-1">MAX</label>
              <input type="number" name="max_price" value="<?= $maxPrice !== null ? $maxPrice : '' ?>" placeholder="50000"
                     class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-slate-800 outline-none focus:border-cc-blue">
            </div>
          </div>
          <!-- Quick Presets -->
          <div class="flex flex-wrap gap-1 pt-1">
            <a href="<?= $baseUrl ?>/search?<?= http_build_query(array_merge($_GET, ['min_price' => 0, 'max_price' => 999])) ?>" 
               class="text-[10px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-1 rounded-md">Under ₹1k</a>
            <a href="<?= $baseUrl ?>/search?<?= http_build_query(array_merge($_GET, ['min_price' => 1000, 'max_price' => 4999])) ?>" 
               class="text-[10px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-1 rounded-md">₹1k - ₹5k</a>
            <a href="<?= $baseUrl ?>/search?<?= http_build_query(array_merge($_GET, ['min_price' => 5000, 'max_price' => 19999])) ?>" 
               class="text-[10px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 px-2 py-1 rounded-md">₹5k - ₹20k</a>
          </div>
        </div>

        <hr class="border-slate-100">

        <!-- 3. Minimum Customer Rating -->
        <div class="space-y-2">
          <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Customer Rating</h4>
          <div class="space-y-1.5 text-xs">
            <?php foreach ([4 => '4★ & Above', 3 => '3★ & Above', 2 => '2★ & Above'] as $starVal => $starLabel): ?>
              <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-700 hover:text-slate-900">
                <input type="radio" name="rating" value="<?= $starVal ?>" <?= $minRating == $starVal ? 'checked' : '' ?> onchange="this.form.submit()" class="text-cc-blue focus:ring-0">
                <span class="flex items-center gap-1 text-amber-400">
                  <span class="material-icons text-sm">star</span>
                  <span class="text-slate-700 font-bold"><?= $starLabel ?></span>
                </span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <hr class="border-slate-100">

        <!-- 4. Availability / Stock Filter -->
        <div class="space-y-2">
          <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Availability</h4>
          <label class="flex items-center justify-between cursor-pointer text-xs font-semibold text-slate-700">
            <span>In Stock Only</span>
            <input type="checkbox" name="in_stock" value="1" <?= $inStockOnly ? 'checked' : '' ?> onchange="this.form.submit()"
                   class="w-4 h-4 text-cc-blue rounded border-slate-300 focus:ring-0">
          </label>
        </div>

        <hr class="border-slate-100">

        <!-- 5. Discount Percentage Filter -->
        <div class="space-y-2">
          <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Discounts &amp; Offers</h4>
          <div class="space-y-1 text-xs">
            <?php foreach ([10 => '10% or more', 20 => '20% or more', 30 => '30% or more', 50 => '50% or more'] as $discVal => $discLabel): ?>
              <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-700 hover:text-slate-900">
                <input type="radio" name="discount" value="<?= $discVal ?>" <?= $minDiscount == $discVal ? 'checked' : '' ?> onchange="this.form.submit()" class="text-cc-blue focus:ring-0">
                <span><?= $discLabel ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <button type="submit" class="w-full bg-cc-blue hover:bg-blue-600 text-white font-bold text-xs py-2.5 rounded-xl transition shadow-sm cursor-pointer">
          Apply Filters
        </button>

      </form>
    </aside>

    <!-- ================= MAIN PRODUCTS LISTING GRID ================= -->
    <div class="flex-1 space-y-4">
      
      <!-- Desktop Sorting Header -->
      <div class="hidden sm:flex bg-white rounded-2xl border border-slate-200 p-3.5 shadow-xs items-center justify-between gap-3">
        <div class="text-xs text-slate-600 font-medium">
          Showing <span class="font-bold text-slate-900"><?= count($products) ?></span> matching product<?= count($products) !== 1 ? 's' : '' ?>
        </div>

        <form action="<?= $baseUrl ?>/search" method="GET" class="flex items-center gap-2 text-xs">
          <input type="hidden" name="q" value="<?= $queryDisplay ?>">
          <?php if (!empty($categorySlug)): ?><input type="hidden" name="category" value="<?= htmlspecialchars($categorySlug) ?>"><?php endif; ?>
          <?php if ($minPrice !== null): ?><input type="hidden" name="min_price" value="<?= $minPrice ?>"><?php endif; ?>
          <?php if ($maxPrice !== null): ?><input type="hidden" name="max_price" value="<?= $maxPrice ?>"><?php endif; ?>
          <?php if ($minRating !== null): ?><input type="hidden" name="rating" value="<?= $minRating ?>"><?php endif; ?>
          <?php if ($inStockOnly): ?><input type="hidden" name="in_stock" value="1"><?php endif; ?>
          <?php if ($minDiscount !== null): ?><input type="hidden" name="discount" value="<?= $minDiscount ?>"><?php endif; ?>

          <label class="font-bold text-slate-500 shrink-0">Sort By:</label>
          <select name="sort" onchange="this.form.submit()"
                  class="bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 font-semibold text-slate-700 outline-none focus:border-cc-blue cursor-pointer">
            <option value="relevance" <?= $sort === 'relevance' ? 'selected' : '' ?>>Relevance</option>
            <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
            <option value="popularity" <?= $sort === 'popularity' ? 'selected' : '' ?>>Most Popular</option>
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest Arrivals</option>
          </select>
        </form>
      </div>

      <!-- Product Cards Grid -->
      <?php if (!empty($products)): ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3 sm:gap-5">
          <?php foreach ($products as $prod): ?>
            <?php
              $origPrice = (float)($prod['base_price'] ?? 0);
              $salePrice = !empty($prod['sale_price']) ? (float)$prod['sale_price'] : null;
              $currPrice = $salePrice !== null ? $salePrice : $origPrice;
              $hasDiscount = ($salePrice !== null && $salePrice < $origPrice);
              $discountPct = $hasDiscount ? round((($origPrice - $salePrice) / $origPrice) * 100) : 0;
              $rating = (float)($prod['average_rating'] ?? 0);
              $imgUrl = !empty($prod['primary_image']) ? $prod['primary_image'] : 'https://placehold.co/400x400/f1f5f9/94a3b8?text=No+Image';
            ?>
            <div class="product-card group flex flex-col justify-between">
              
              <div>
                <!-- Image Container -->
                <div class="relative w-full aspect-square bg-slate-100 overflow-hidden">
                  <a href="<?= $baseUrl ?>/product/<?= htmlspecialchars($prod['slug']) ?>">
                    <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($prod['name']) ?>"
                         onerror="this.onerror=null;this.src='https://placehold.co/400x400/f1f5f9/94a3b8?text=No+Image';"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                  </a>

                  <?php if ($hasDiscount): ?>
                    <span class="absolute top-2 left-2 bg-cc-orange text-white text-[10px] font-extrabold px-2 py-0.5 rounded-md shadow-sm">
                      -<?= $discountPct ?>%
                    </span>
                  <?php endif; ?>

                  <!-- Wishlist Button -->
                  <button onclick="toggleWishlist('<?= $prod['encrypted_id'] ?>', this)" 
                          class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white/90 shadow-md flex items-center justify-center text-slate-400 hover:text-cc-pink transition cursor-pointer">
                    <span class="material-icons text-lg">favorite_border</span>
                  </button>
                </div>

                <!-- Product Details -->
                <div class="p-3 sm:p-4 space-y-1.5">
                  <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider truncate">
                    <?= htmlspecialchars($prod['category_name'] ?? 'ClickCodex') ?>
                  </p>
                  
                  <h3 class="font-bold text-xs sm:text-sm text-slate-800 line-clamp-2 leading-tight group-hover:text-cc-blue transition">
                    <a href="<?= $baseUrl ?>/product/<?= htmlspecialchars($prod['slug']) ?>">
                      <?= htmlspecialchars($prod['name']) ?>
                    </a>
                  </h3>

                  <!-- Rating Stars -->
                  <div class="flex items-center gap-1 text-xs pt-0.5">
                    <div class="flex text-amber-400">
                      <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="material-icons text-xs"><?= $i <= floor($rating) ? 'star' : ($i - $rating <= 0.5 ? 'star_half' : 'star_border') ?></span>
                      <?php endfor; ?>
                    </div>
                    <span class="text-[11px] text-slate-500 font-semibold">(<?= (int)($prod['review_count'] ?? 0) ?>)</span>
                  </div>

                  <!-- Price Container -->
                  <div class="pt-1 flex items-baseline gap-2 flex-wrap">
                    <span class="font-extrabold text-sm sm:text-base text-slate-900">
                      ₹<?= number_format($currPrice, 2) ?>
                    </span>
                    <?php if ($hasDiscount): ?>
                      <span class="text-xs text-slate-400 line-through">
                        ₹<?= number_format($origPrice, 2) ?>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

              <!-- Add to Cart Button -->
              <div class="p-3 sm:p-4 pt-0">
                <button onclick="addToCart('<?= $prod['encrypted_id'] ?>', 1)" 
                        class="w-full bg-slate-900 hover:bg-cc-blue text-white font-bold text-xs py-2 rounded-xl transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                  <span class="material-icons text-sm">shopping_cart</span> Add to Cart
                </button>
              </div>

            </div>
          <?php endforeach; ?>
        </div>

      <?php else: ?>
        
        <!-- Empty Results State -->
        <div class="bg-white rounded-3xl border border-slate-200 p-8 sm:p-12 text-center space-y-4 shadow-xs">
          <div class="w-16 h-16 sm:w-20 sm:h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto text-slate-400">
            <span class="material-icons text-4xl sm:text-5xl">search_off</span>
          </div>
          <h2 class="font-heading font-extrabold text-xl sm:text-2xl text-slate-800">
            No products match your active filters
          </h2>
          <p class="text-slate-500 text-xs sm:text-sm max-w-md mx-auto leading-relaxed">
            Try clearing some filters or search for broader terms like *Electronics*, *Fashion*, or *Home*.
          </p>
          <div class="pt-2">
            <a href="<?= $baseUrl ?>/search" class="inline-block bg-cc-blue text-white font-bold text-xs px-5 py-2.5 rounded-xl shadow-sm">
              Reset Filters &amp; Browse Catalog
            </a>
          </div>
        </div>

      <?php endif; ?>

    </div>

  </div>

</main>

<!-- ================= MOBILE HIGH QUALITY FILTER BOTTOM SHEET / DRAWER ================= -->
<div id="mobileFilterDrawer" class="fixed inset-0 z-[100] pointer-events-none opacity-0 transition-opacity duration-300">
  <div onclick="toggleMobileFilterDrawer(false)" class="absolute inset-0 bg-black/50 backdrop-blur-sm cursor-pointer"></div>
  <div class="mobile-filter-panel absolute inset-y-0 right-0 max-w-xs w-full bg-white shadow-2xl flex flex-col h-full z-10 transform translate-x-full transition-transform duration-350 ease-out">
    
    <!-- Drawer Header -->
    <div class="bg-slate-900 text-white p-4 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="material-icons text-cc-yellow text-xl">tune</span>
        <h3 class="font-bold text-sm">Filter Products</h3>
      </div>
      <button onclick="toggleMobileFilterDrawer(false)" class="text-slate-400 hover:text-white p-1">
        <span class="material-icons text-xl">close</span>
      </button>
    </div>

    <!-- Drawer Body Form -->
    <form action="<?= $baseUrl ?>/search" method="GET" class="flex-1 overflow-y-auto p-4 space-y-5 text-xs">
      <input type="hidden" name="q" value="<?= $queryDisplay ?>">
      <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">

      <!-- Categories Filter -->
      <div class="space-y-2">
        <h4 class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">Category</h4>
        <select name="category" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs font-semibold text-slate-800">
          <option value="">All Categories</option>
          <?php foreach ($categories as $cat): ?>
            <option value="<?= htmlspecialchars($cat['slug']) ?>" <?= ($categorySlug === $cat['slug'] || $categorySlug === $cat['name']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <hr class="border-slate-100">

      <!-- Price Range -->
      <div class="space-y-2">
        <h4 class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">Price Range (₹)</h4>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="text-[10px] text-slate-400 font-bold block mb-1">MIN</label>
            <input type="number" name="min_price" value="<?= $minPrice !== null ? $minPrice : '' ?>" placeholder="0" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-slate-800">
          </div>
          <div>
            <label class="text-[10px] text-slate-400 font-bold block mb-1">MAX</label>
            <input type="number" name="max_price" value="<?= $maxPrice !== null ? $maxPrice : '' ?>" placeholder="50000" class="w-full px-2.5 py-1.5 border border-slate-200 rounded-lg text-slate-800">
          </div>
        </div>
      </div>

      <hr class="border-slate-100">

      <!-- Rating -->
      <div class="space-y-2">
        <h4 class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">Customer Rating</h4>
        <div class="space-y-1.5">
          <?php foreach ([4 => '4★ & Above', 3 => '3★ & Above', 2 => '2★ & Above'] as $starVal => $starLabel): ?>
            <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-700">
              <input type="radio" name="rating" value="<?= $starVal ?>" <?= $minRating == $starVal ? 'checked' : '' ?> class="text-cc-blue">
              <span><?= $starLabel ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <hr class="border-slate-100">

      <!-- In Stock -->
      <label class="flex items-center justify-between cursor-pointer font-semibold text-slate-800">
        <span>In Stock Only</span>
        <input type="checkbox" name="in_stock" value="1" <?= $inStockOnly ? 'checked' : '' ?> class="w-4 h-4 text-cc-blue rounded">
      </label>

      <hr class="border-slate-100">

      <!-- Discount -->
      <div class="space-y-2">
        <h4 class="font-bold text-slate-800 uppercase tracking-wider text-[11px]">Discount Percentage</h4>
        <div class="space-y-1.5">
          <?php foreach ([10 => '10% or more', 20 => '20% or more', 30 => '30% or more', 50 => '50% or more'] as $discVal => $discLabel): ?>
            <label class="flex items-center gap-2 cursor-pointer font-medium text-slate-700">
              <input type="radio" name="discount" value="<?= $discVal ?>" <?= $minDiscount == $discVal ? 'checked' : '' ?> class="text-cc-blue">
              <span><?= $discLabel ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="pt-4 flex items-center gap-2">
        <a href="<?= $baseUrl ?>/search?q=<?= urlencode($q) ?>" class="flex-1 bg-slate-100 text-slate-700 font-bold py-2.5 rounded-xl text-center">Reset</a>
        <button type="submit" class="flex-1 bg-cc-blue text-white font-bold py-2.5 rounded-xl text-center shadow-md">Apply</button>
      </div>

    </form>
  </div>
</div>

<script>
function toggleMobileFilterDrawer(open) {
  const drawer = document.getElementById('mobileFilterDrawer');
  const panel = drawer.querySelector('.mobile-filter-panel');
  if (!drawer || !panel) return;

  if (open) {
    drawer.classList.remove('pointer-events-none', 'opacity-0');
    drawer.classList.add('opacity-100');
    panel.classList.remove('translate-x-full');
  } else {
    drawer.classList.remove('opacity-100');
    drawer.classList.add('opacity-0', 'pointer-events-none');
    panel.classList.add('translate-x-full');
  }
}
</script>

<?php include __DIR__ . '/layouts/footer.php'; ?>
