<?php
require_once __DIR__ . '/layouts/header.php';

$tree    = $tree    ?? [];
$baseUrl = $baseUrl ?? (defined('BASE_URL') ? BASE_URL : '');
?>

<style>
  body { background: #F8FAFC; }
  @keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
  .fade-up { animation: fadeUp .4s ease-out both; }

  .categories-hero {
    background: linear-gradient(135deg, #0F172A 0%, #1E1B4B 45%, #2D82FF 100%);
    border-radius: 24px; color: #fff; position: relative; overflow: hidden;
  }
  .categories-hero::before {
    content:''; position:absolute; width:340px; height:340px;
    right:-80px; top:-80px;
    background: radial-gradient(circle, rgba(140,48,245,.3) 0%, transparent 70%);
    border-radius:50%; pointer-events:none;
  }
  .categories-hero::after {
    content:''; position:absolute; width:220px; height:220px;
    left:-40px; bottom:-60px;
    background: radial-gradient(circle, rgba(255,81,0,.2) 0%, transparent 70%);
    border-radius:50%; pointer-events:none;
  }

  .cat-card {
    background: #ffffff;
    border: 1.5px solid #E2E8F0;
    border-radius: 20px;
    padding: 24px;
    transition: transform 0.25s cubic-bezier(.4,0,.2,1), box-shadow 0.25s, border-color 0.25s;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
  }
  .cat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 36px rgba(45,130,255,0.12);
    border-color: #93C5FD;
  }
  .cat-icon-wrap {
    width: 52px; height: 52px;
    border-radius: 16px;
    background: linear-gradient(135deg, #EFF6FF, #DBEAFE);
    color: #2D82FF;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.25s;
  }
  .cat-card:hover .cat-icon-wrap {
    background: linear-gradient(135deg, #2D82FF, #4F46E5);
    color: #ffffff;
    transform: scale(1.08);
  }
</style>

<main class="max-w-7xl mx-auto px-3 sm:px-4 py-4 sm:py-8 space-y-6 sm:space-y-10 w-full">

  <!-- ================= HERO HEADER ================= -->
  <section class="categories-hero p-6 sm:p-10 md:p-12 shadow-xl fade-up">
    <div class="relative z-10 max-w-2xl space-y-3">
      <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full text-xs font-extrabold bg-amber-400 text-slate-900 uppercase tracking-wider shadow-md">
        <span class="material-icons text-[15px]">grid_view</span> Browse Store Catalog
      </span>
      <h1 class="font-heading text-3xl sm:text-4xl md:text-5xl font-extrabold text-white leading-tight">
        All Categories
      </h1>
      <p class="text-white/80 text-sm sm:text-base leading-relaxed">
        Explore our curated collection of electronics, fashion, home essentials, audio gear, and more.
      </p>
    </div>
  </section>

  <!-- ================= CATEGORIES GRID ================= -->
  <section class="space-y-6">
    <div class="flex items-center justify-between">
      <h2 class="font-heading text-xl sm:text-2xl font-bold text-slate-900 flex items-center gap-2">
        <span class="material-icons text-cc-blue">category</span> Top Product Categories
      </h2>
      <span class="text-xs text-slate-400 font-semibold"><?= count($tree) ?> Main Categories</span>
    </div>

    <?php if (empty($tree)): ?>
      <div class="bg-white border border-slate-200 rounded-2xl p-12 text-center text-slate-500">
        <span class="material-icons text-5xl text-slate-300 mb-2">category</span>
        <p class="font-heading font-bold text-lg text-slate-700">No categories found</p>
        <p class="text-xs text-slate-400 mt-1">Check back later or return to home.</p>
        <a href="<?= $baseUrl ?>/" class="inline-block mt-4 text-xs font-bold text-cc-blue hover:underline">Go to Homepage &rarr;</a>
      </div>
    <?php else: ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($tree as $cat):
          $cName    = htmlspecialchars($cat['name'] ?? '');
          $cSlug    = htmlspecialchars($cat['slug'] ?? $cat['id']);
          $cDesc    = htmlspecialchars($cat['description'] ?? '');
          $children = $cat['children'] ?? [];
          $iconName = !empty($cat['icon']) ? $cat['icon'] : 'category';

          $cLower = strtolower($cName);
          if (strpos($cLower, 'electr') !== false || strpos($cLower, 'tech') !== false || strpos($cLower, 'gadget') !== false) $iconName = 'devices';
          elseif (strpos($cLower, 'cloth') !== false || strpos($cLower, 'fash') !== false || strpos($cLower, 'apparel') !== false) $iconName = 'checkroom';
          elseif (strpos($cLower, 'kitch') !== false || strpos($cLower, 'home') !== false) $iconName = 'soup_kitchen';
          elseif (strpos($cLower, 'gym') !== false || strpos($cLower, 'fit') !== false || strpos($cLower, 'sport') !== false) $iconName = 'fitness_center';
          elseif (strpos($cLower, 'book') !== false) $iconName = 'menu_book';
          elseif (strpos($cLower, 'beaut') !== false || strpos($cLower, 'skin') !== false) $iconName = 'face';
        ?>
          <div class="cat-card">
            <div>
              <div class="flex items-start justify-between mb-4">
                <div class="cat-icon-wrap">
                  <span class="material-icons text-[26px]"><?= $iconName ?></span>
                </div>
                <?php if (!empty($children)): ?>
                  <span class="text-[10px] font-extrabold uppercase tracking-wider bg-slate-100 text-slate-600 px-2.5 py-1 rounded-full">
                    <?= count($children) ?> Subcategories
                  </span>
                <?php endif; ?>
              </div>

              <h3 class="font-heading font-extrabold text-lg text-slate-900 mb-1.5 hover:text-cc-blue transition">
                <a href="<?= $baseUrl ?>/category/<?= $cSlug ?>"><?= $cName ?></a>
              </h3>

              <?php if (!empty($cDesc)): ?>
                <p class="text-slate-500 text-xs line-clamp-2 mb-3 leading-relaxed"><?= $cDesc ?></p>
              <?php endif; ?>

              <!-- Subcategories list if any -->
              <?php if (!empty($children)): ?>
                <div class="pt-3 border-t border-slate-100 mt-3">
                  <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Popular Subcategories</p>
                  <div class="flex flex-wrap gap-1.5">
                    <?php foreach (array_slice($children, 0, 5) as $sub): ?>
                      <a href="<?= $baseUrl ?>/category/<?= htmlspecialchars($sub['slug'] ?? $sub['id']) ?>"
                         class="text-[11px] font-semibold text-slate-700 bg-slate-50 hover:bg-cc-blue/10 hover:text-cc-blue px-2.5 py-1 rounded-lg border border-slate-200 transition">
                        <?= htmlspecialchars($sub['name']) ?>
                      </a>
                    <?php endforeach; ?>
                    <?php if (count($children) > 5): ?>
                      <span class="text-[10px] font-bold text-slate-400 px-1 py-1">+<?= count($children) - 5 ?> more</span>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
              <span class="text-xs text-slate-400 font-medium">Browse All Items</span>
              <a href="<?= $baseUrl ?>/category/<?= $cSlug ?>" class="inline-flex items-center gap-1 text-xs font-extrabold text-cc-blue hover:text-blue-700 transition">
                Explore Category <span class="material-icons text-[15px]">arrow_forward</span>
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

</main>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
