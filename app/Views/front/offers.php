<?php
require_once __DIR__ . '/layouts/header.php';

// Defaults
$offers        = $offers        ?? [];
$banners       = $banners       ?? [];
$flashDeals    = $flashDeals    ?? [];
$moreProducts  = $moreProducts  ?? [];
$isLoggedIn    = $isLoggedIn    ?? false;
$userId        = $userId        ?? 0;
$userStats     = $userStats     ?? ['order_count'=>0,'total_spent'=>0,'tier'=>'new','tier_label'=>'New Member'];
$myCoupons     = $myCoupons     ?? [];
$lockedCoupons = $lockedCoupons ?? [];
$usedCoupons   = $usedCoupons   ?? [];
$allCoupons    = $allCoupons    ?? [];   // guest view (no code)
$tiers         = $tiers         ?? [];
?>

<!-- ===== OFFERS PAGE STYLES ===== -->
<style>
  /* Hero */
  .offers-hero { background:linear-gradient(135deg,#0f172a 0%,#1e1b4b 40%,#312e81 70%,#4c1d95 100%); position:relative; overflow:hidden; }
  .offers-hero::before { content:''; position:absolute; inset:0; background:radial-gradient(circle at 80% 50%,rgba(139,92,246,.35) 0%,transparent 60%),radial-gradient(circle at 10% 80%,rgba(255,81,0,.2) 0%,transparent 50%); }
  .hero-floating-badge { animation:float-badge 3s ease-in-out infinite; }
  @keyframes float-badge { 0%,100%{transform:translateY(0) rotate(-2deg)}50%{transform:translateY(-10px) rotate(2deg)} }
  .particle { position:absolute;border-radius:50%;animation:particle-float linear infinite;opacity:.15; }
  @keyframes particle-float { 0%{transform:translateY(100%) scale(.8);opacity:0}10%{opacity:.15}90%{opacity:.1}100%{transform:translateY(-120%) scale(1.2);opacity:0} }

  /* Countdown */
  .countdown-digit { background:rgba(255,255,255,.12);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.2);border-radius:10px;min-width:52px;text-align:center;padding:6px 8px;font-family:'Poppins',monospace; }

  /* Offer banner cards */
  .offer-banner-card { position:relative;border-radius:20px;overflow:hidden;cursor:pointer;transition:transform .3s cubic-bezier(.4,0,.2,1),box-shadow .3s; }
  .offer-banner-card:hover { transform:translateY(-4px);box-shadow:0 20px 40px rgba(0,0,0,.18); }
  .offer-banner-card img { width:100%;height:180px;object-fit:cover;display:block; }
  .offer-banner-overlay { position:absolute;inset:0;background:linear-gradient(to top,rgba(0,0,0,.7) 0%,transparent 60%);padding:16px;display:flex;flex-direction:column;justify-content:flex-end; }

  /* Flash countdown */
  .flash-countdown { display:flex;align-items:center;gap:4px; }
  .flash-countdown span.digit { background:#0F172A;color:#FFB800;font-family:'Poppins',monospace;font-weight:800;font-size:13px;padding:2px 6px;border-radius:5px;min-width:26px;text-align:center; }
  .flash-countdown .colon { color:#FF5100;font-weight:900;font-size:14px; }

  /* Product cards */
  .offer-product-card { background:#fff;border:1.5px solid #E2E8F0;border-radius:18px;overflow:hidden;transition:transform .25s cubic-bezier(.4,0,.2,1),box-shadow .25s,border-color .25s;display:flex;flex-direction:column;position:relative; }
  .offer-product-card:hover { transform:translateY(-5px);box-shadow:0 16px 40px rgba(45,130,255,.13);border-color:#93c5fd; }
  .offer-product-card .card-img-wrap { aspect-ratio:1;overflow:hidden;background:#F8FAFC;position:relative; }
  .offer-product-card .card-img-wrap img { width:100%;height:100%;object-fit:cover;transition:transform .4s ease; }
  .offer-product-card:hover .card-img-wrap img { transform:scale(1.06); }
  .discount-badge { position:absolute;top:10px;left:10px;background:linear-gradient(135deg,#FF5100,#FF8C00);color:#fff;font-size:10px;font-weight:800;padding:3px 9px;border-radius:8px;letter-spacing:.05em;text-transform:uppercase;box-shadow:0 2px 8px rgba(255,81,0,.35); }
  .wishlist-btn { position:absolute;top:10px;right:10px;width:30px;height:30px;border-radius:50%;background:rgba(255,255,255,.85);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;color:#94a3b8;transition:all .2s;text-decoration:none;box-shadow:0 2px 8px rgba(0,0,0,.1); }
  .wishlist-btn:hover { background:#fff;color:#FF006B;transform:scale(1.1); }
  .card-body { padding:12px 14px 14px;flex:1;display:flex;flex-direction:column; }
  .card-category { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:4px; }
  .card-title { font-family:'Poppins',sans-serif;font-size:13px;font-weight:700;color:#0F172A;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin-bottom:auto;line-height:1.4; }
  .card-title a { color:inherit;text-decoration:none;transition:color .2s; }
  .card-title a:hover { color:#2D82FF; }
  .card-prices { display:flex;align-items:baseline;gap:6px;margin-top:8px; }
  .price-current { font-family:'Poppins',sans-serif;font-weight:800;font-size:16px;color:#0F172A; }
  .price-original { font-size:11px;color:#94a3b8;text-decoration:line-through;font-family:monospace; }
  .card-rating { display:flex;align-items:center;gap:3px;font-size:11px;color:#64748b;margin-top:4px; }
  .card-rating .star { color:#FFB800;font-size:13px; }
  .btn-add-cart { margin-top:10px;width:100%;background:linear-gradient(135deg,#2D82FF,#4F46E5);color:#fff;font-weight:700;font-size:11px;padding:9px 12px;border-radius:12px;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;transition:all .25s;letter-spacing:.03em;text-transform:uppercase;box-shadow:0 4px 12px rgba(45,130,255,.3); }
  .btn-add-cart:hover { background:linear-gradient(135deg,#1a6fe8,#4338CA);transform:translateY(-1px);box-shadow:0 6px 16px rgba(45,130,255,.4); }
  .out-of-stock-overlay { position:absolute;inset:0;background:rgba(255,255,255,.6);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:11px;color:#ef4444;text-transform:uppercase;letter-spacing:.1em;backdrop-filter:blur(1px); }

  /* Section badges */
  .section-badge { display:inline-flex;align-items:center;gap:5px;background:linear-gradient(135deg,#EEF2FF,#E0E7FF);color:#4F46E5;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;padding:5px 14px;border-radius:20px;margin-bottom:8px; }

  /* Promo cards */
  .promo-card { position:relative;border-radius:20px;overflow:hidden;padding:28px 24px;color:#fff;transition:transform .3s,box-shadow .3s; }
  .promo-card:hover { transform:translateY(-4px);box-shadow:0 18px 40px rgba(0,0,0,.2); }
  .promo-card::after { content:'';position:absolute;right:-20px;bottom:-20px;width:120px;height:120px;border-radius:50%;background:rgba(255,255,255,.08); }

  /* ── Coupon Card (unlocked) ── */
  .coupon-card { background:#fff;border:2px dashed #C7D2FE;border-radius:18px;padding:20px;position:relative;overflow:hidden;transition:all .25s; }
  .coupon-card::before { content:'';position:absolute;top:-20px;right:-20px;width:80px;height:80px;border-radius:50%;background:rgba(99,102,241,.06); }
  .coupon-card:hover { border-color:#818CF8;box-shadow:0 8px 30px rgba(99,102,241,.15);transform:translateY(-3px); }
  .coupon-code-box { font-family:'Courier New',monospace;font-weight:800;font-size:14px;background:#F1F5F9;border:1.5px solid #E2E8F0;border-radius:10px;padding:8px 14px;letter-spacing:.1em;color:#1e293b;cursor:pointer;transition:background .2s;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap; }
  .coupon-copy-btn { display:flex;align-items:center;gap:4px;font-size:11px;font-weight:700;color:#6366F1;background:#EEF2FF;border:none;cursor:pointer;padding:8px 14px;border-radius:10px;transition:all .2s;white-space:nowrap;flex-shrink:0; }
  .coupon-copy-btn:hover { background:#E0E7FF;color:#4338CA; }

  /* ── Coupon Card (locked) ── */
  .coupon-locked { background:#F8FAFC;border:2px dashed #E2E8F0;border-radius:18px;padding:20px;position:relative;overflow:hidden;transition:all .25s; }
  .coupon-locked:hover { border-color:#cbd5e1; }
  .locked-code-mask { font-family:'Courier New',monospace;font-weight:800;font-size:14px;background:#F1F5F9;border:1.5px solid #E2E8F0;border-radius:10px;padding:8px 14px;letter-spacing:.15em;color:#cbd5e1;user-select:none;filter:blur(3px);flex:1; }
  .locked-badge { display:inline-flex;align-items:center;gap:4px;background:#FEF3C7;color:#92400E;font-size:10px;font-weight:800;padding:3px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:.06em; }

  /* ── Guest Gate Card ── */
  .guest-gate { background:linear-gradient(135deg,#1e1b4b,#312e81);border-radius:24px;padding:40px 32px;text-align:center;position:relative;overflow:hidden; }
  .guest-gate::before { content:'';position:absolute;inset:0;background:radial-gradient(circle at 70% 30%,rgba(139,92,246,.4) 0%,transparent 60%); }

  /* ── Blurred coupon teaser (guest) ── */
  .coupon-teaser { background:#fff;border:2px dashed #C7D2FE;border-radius:18px;padding:18px;position:relative;overflow:hidden;filter:blur(3px) brightness(.97);pointer-events:none;user-select:none; }

  /* ── Tier Progress ── */
  .tier-progress-bar { height:8px;background:#E2E8F0;border-radius:99px;overflow:hidden; }
  .tier-progress-fill { height:100%;border-radius:99px;transition:width .8s ease; }
  .tier-card { border-radius:16px;padding:16px 20px;display:flex;align-items:center;gap:12px;border:2px solid transparent;transition:all .25s; }
  .tier-card.active { box-shadow:0 4px 20px rgba(0,0,0,.1); }

  /* Usage bar */
  .usage-bar { height:4px;background:#E2E8F0;border-radius:99px;overflow:hidden;margin-top:6px; }
  .usage-bar-fill { height:100%;border-radius:99px;background:linear-gradient(90deg,#6366F1,#8B5CF6); }

  /* Trust strip */
  .trust-strip-item { display:flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:#475569;background:#fff;border:1.5px solid #E2E8F0;border-radius:12px;padding:10px 14px;transition:all .2s; }
  .trust-strip-item:hover { border-color:#C7D2FE;box-shadow:0 4px 12px rgba(99,102,241,.1); }

  /* Scroll animation */
  .fade-in-up { opacity:0;transform:translateY(24px);transition:opacity .5s ease,transform .5s ease; }
  .fade-in-up.visible { opacity:1;transform:translateY(0); }
</style>

<main class="max-w-7xl mx-auto px-3 sm:px-4 py-4 sm:py-8 space-y-8 sm:space-y-12 w-full">

  <!-- ============================================================ -->
  <!-- HERO                                                          -->
  <!-- ============================================================ -->
  <section class="offers-hero rounded-3xl text-white p-6 sm:p-10 md:p-14 shadow-2xl overflow-hidden relative">
    <div class="particle" style="width:200px;height:200px;background:#8B5CF6;left:65%;top:10%;animation-duration:12s;animation-delay:-2s;"></div>
    <div class="particle" style="width:100px;height:100px;background:#FF5100;left:85%;top:60%;animation-duration:9s;animation-delay:-4s;"></div>
    <div class="particle" style="width:60px;height:60px;background:#FFB800;left:75%;top:30%;animation-duration:7s;animation-delay:-1s;"></div>

    <div class="relative z-10 grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
      <div class="space-y-5">
        <div class="flex items-center gap-2 flex-wrap">
          <span class="inline-flex items-center gap-1.5 bg-amber-400 text-slate-900 text-xs font-extrabold px-3.5 py-1.5 rounded-full uppercase tracking-wider shadow-lg">
            <span class="material-icons text-[15px]">local_fire_department</span> Hot Deals
          </span>
          <?php if ($isLoggedIn): ?>
            <span class="inline-flex items-center gap-1.5 bg-white/15 backdrop-blur text-white text-xs font-bold px-3 py-1.5 rounded-full border border-white/20">
              <span class="material-icons text-[14px]">workspace_premium</span>
              <?= htmlspecialchars($userStats['tier_label']) ?>
            </span>
          <?php endif; ?>
        </div>
        <h1 class="font-heading text-3xl sm:text-4xl md:text-5xl font-extrabold leading-tight tracking-tight">
          Exclusive Deals &<br>
          <span class="text-transparent bg-clip-text" style="background-image:linear-gradient(90deg,#FFB800,#FF5100)">Mega Savings!</span>
        </h1>
        <p class="text-white/80 text-sm sm:text-base max-w-md leading-relaxed">
          <?php if ($isLoggedIn): ?>
            Welcome back! You have <strong class="text-amber-400"><?= count($myCoupons) ?> unlocked coupon<?= count($myCoupons) !== 1 ? 's' : '' ?></strong> ready to use.
          <?php else: ?>
            Sign in to unlock exclusive coupon codes based on your purchase history!
          <?php endif; ?>
        </p>
        <div class="flex flex-wrap gap-3">
          <a href="#flash-deals" class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-300 text-slate-900 font-extrabold px-6 py-3 rounded-xl transition shadow-lg shadow-amber-400/30 text-sm">
            <span class="material-icons text-[18px]">flash_on</span> Flash Deals
          </a>
          <a href="#offer-coupons" class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 border border-white/25 text-white font-bold px-6 py-3 rounded-xl transition text-sm backdrop-blur">
            <span class="material-icons text-[18px]">confirmation_number</span> My Coupons
          </a>
        </div>
        <!-- Countdown -->
        <div>
          <p class="text-white/60 text-xs font-bold uppercase tracking-wider mb-2">⏱ Sale ends in</p>
          <div class="flex items-center gap-2">
            <div class="countdown-digit"><div id="cd-hours" class="text-white text-xl font-extrabold">00</div><div class="text-white/50 text-[10px] font-bold mt-0.5">HRS</div></div>
            <span class="text-amber-400 text-2xl font-black">:</span>
            <div class="countdown-digit"><div id="cd-mins" class="text-white text-xl font-extrabold">00</div><div class="text-white/50 text-[10px] font-bold mt-0.5">MIN</div></div>
            <span class="text-amber-400 text-2xl font-black">:</span>
            <div class="countdown-digit"><div id="cd-secs" class="text-white text-xl font-extrabold">00</div><div class="text-white/50 text-[10px] font-bold mt-0.5">SEC</div></div>
          </div>
        </div>
      </div>

      <!-- Floating badges -->
      <div class="hidden lg:flex items-center justify-center relative h-52">
        <div class="hero-floating-badge absolute top-0 right-20 w-28 h-28 rounded-full flex flex-col items-center justify-center shadow-2xl border-4 border-white/20" style="background:linear-gradient(135deg,#FF5100,#FF8C00)">
          <span class="text-white font-extrabold text-3xl leading-none">70%</span>
          <span class="text-white/80 text-xs font-bold">OFF</span>
        </div>
        <div class="hero-floating-badge absolute bottom-0 left-10 w-22 h-22 rounded-full flex flex-col items-center justify-center shadow-2xl border-4 border-white/20" style="background:linear-gradient(135deg,#6366F1,#8B5CF6);width:88px;height:88px;animation-delay:-1.5s">
          <span class="text-white font-extrabold text-2xl leading-none">50%</span>
          <span class="text-white/80 text-xs font-bold">FLAT</span>
        </div>
        <div class="hero-floating-badge absolute top-10 left-32 rounded-full flex flex-col items-center justify-center shadow-xl border-4 border-white/20" style="background:linear-gradient(135deg,#FFB800,#FF5100);width:76px;height:76px;animation-delay:-0.5s">
          <span class="text-white font-extrabold text-xl leading-none">₹500</span>
          <span class="text-white/80 text-[10px] font-bold">CASHBACK</span>
        </div>
        <div class="w-36 h-36 rounded-3xl flex items-center justify-center" style="background:rgba(255,255,255,.08);backdrop-filter:blur(10px);border:2px solid rgba(255,255,255,.15)">
          <span class="material-icons text-white/30 text-[90px]">sell</span>
        </div>
      </div>
    </div>
  </section>

  <!-- ============================================================ -->
  <!-- LOYALTY TIER CARD (logged-in users only)                      -->
  <!-- ============================================================ -->
  <?php if ($isLoggedIn && !empty($tiers)): ?>
  <?php
    $tier     = $userStats['tier'];
    $spent    = $userStats['total_spent'];
    $orders   = $userStats['order_count'];
    $tierInfo = $tiers[$tier] ?? $tiers['new'];

    // Progress to next tier
    $nextTierKey = null;
    $tierKeys    = array_keys($tiers);
    $tierPos     = array_search($tier, $tierKeys);
    if ($tierPos !== false && isset($tierKeys[$tierPos + 1])) {
        $nextTierKey = $tierKeys[$tierPos + 1];
    }
    $nextTierInfo   = $nextTierKey ? $tiers[$nextTierKey] : null;
    $progressPct    = 0;
    $spendToNext    = 0;
    if ($nextTierInfo) {
        $rangeStart  = $tierInfo['min_spend'];
        $rangeEnd    = $nextTierInfo['min_spend'];
        $progressPct = min(100, round(($spent - $rangeStart) / max(1, ($rangeEnd - $rangeStart)) * 100));
        $spendToNext = max(0, $rangeEnd - $spent);
    }

    $tierColors = [
      'new'      => ['bg'=>'bg-slate-50','border'=>'border-slate-200','badge'=>'bg-slate-200 text-slate-700','bar'=>'#94a3b8'],
      'bronze'   => ['bg'=>'bg-orange-50','border'=>'border-orange-200','badge'=>'bg-orange-200 text-orange-800','bar'=>'#F97316'],
      'silver'   => ['bg'=>'bg-blue-50','border'=>'border-blue-200','badge'=>'bg-blue-200 text-blue-800','bar'=>'#3B82F6'],
      'gold'     => ['bg'=>'bg-amber-50','border'=>'border-amber-200','badge'=>'bg-amber-200 text-amber-800','bar'=>'#F59E0B'],
      'platinum' => ['bg'=>'bg-purple-50','border'=>'border-purple-200','badge'=>'bg-purple-200 text-purple-800','bar'=>'#8B5CF6'],
    ];
    $tc = $tierColors[$tier] ?? $tierColors['new'];
  ?>
  <section class="fade-in-up">
    <div class="<?= $tc['bg'] ?> border-2 <?= $tc['border'] ?> rounded-2xl p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center gap-5">
      <!-- Tier badge -->
      <div class="flex items-center gap-4 flex-1">
        <div class="w-14 h-14 rounded-2xl <?= $tc['badge'] ?> flex items-center justify-center flex-shrink-0">
          <span class="material-icons text-[28px]"><?= htmlspecialchars($tierInfo['icon']) ?></span>
        </div>
        <div>
          <p class="font-heading font-extrabold text-slate-900 text-lg"><?= htmlspecialchars($tierInfo['label']) ?></p>
          <p class="text-slate-500 text-xs mt-0.5">
            <?= $orders ?> order<?= $orders !== 1 ? 's' : '' ?> &bull; ₹<?= number_format($spent, 0) ?> total spent
          </p>
          <?php if ($nextTierInfo): ?>
            <p class="text-xs font-semibold mt-1" style="color:<?= $tc['bar'] ?>">
              ₹<?= number_format($spendToNext, 0) ?> more to reach <strong><?= htmlspecialchars($nextTierInfo['label']) ?></strong>
            </p>
          <?php else: ?>
            <p class="text-xs font-bold text-purple-700 mt-1">🏆 You've reached the highest tier!</p>
          <?php endif; ?>
        </div>
      </div>
      <!-- Progress bar -->
      <?php if ($nextTierInfo): ?>
      <div class="w-full sm:w-56 flex-shrink-0">
        <div class="flex justify-between text-[10px] font-bold text-slate-500 mb-1.5">
          <span><?= htmlspecialchars($tierInfo['label']) ?></span>
          <span><?= htmlspecialchars($nextTierInfo['label']) ?></span>
        </div>
        <div class="tier-progress-bar">
          <div class="tier-progress-fill" style="width:<?= $progressPct ?>%;background:<?= $tc['bar'] ?>"></div>
        </div>
        <p class="text-[10px] text-slate-400 mt-1 text-right"><?= $progressPct ?>% unlocked</p>
      </div>
      <?php endif; ?>
      <!-- Stats -->
      <div class="flex gap-4 flex-shrink-0">
        <div class="text-center">
          <p class="font-extrabold text-xl font-heading text-slate-900"><?= count($myCoupons) ?></p>
          <p class="text-[10px] text-slate-500 font-bold uppercase">Unlocked</p>
        </div>
        <div class="w-px bg-slate-200"></div>
        <div class="text-center">
          <p class="font-extrabold text-xl font-heading text-slate-400"><?= count($lockedCoupons) ?></p>
          <p class="text-[10px] text-slate-400 font-bold uppercase">Locked</p>
        </div>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <!-- ============================================================ -->
  <!-- DB BANNERS                                                     -->
  <!-- ============================================================ -->
  <?php if (!empty($banners)): ?>
  <section class="fade-in-up">
    <div class="mb-4">
      <div class="section-badge"><span class="material-icons text-[14px]">image</span> Featured Banners</div>
      <h2 class="font-heading text-xl sm:text-2xl font-bold text-slate-900">Promotional Banners</h2>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5">
      <?php foreach ($banners as $b):
        $bannerBg = match($b['banner_type'] ?? '') {
          'offer_banner'    => 'linear-gradient(135deg,#FF5100,#FF8C00)',
          'category_banner' => 'linear-gradient(135deg,#2D82FF,#4F46E5)',
          'hero_slider'     => 'linear-gradient(135deg,#0F172A,#312E81)',
          default           => 'linear-gradient(135deg,#6366F1,#8B5CF6)',
        };
      ?>
        <div class="offer-banner-card" <?= !empty($b['link_url']) ? "onclick=\"window.location.href='".htmlspecialchars($b['link_url'])."'\"" : '' ?>>
          <?php if (!empty($b['image_url'])): ?>
            <img src="<?= htmlspecialchars($b['image_url']) ?>" alt="<?= htmlspecialchars($b['title'] ?? 'Offer Banner') ?>">
            <div class="offer-banner-overlay">
              <?php if (!empty($b['title'])): ?><h3 class="text-white font-heading font-bold text-base leading-tight"><?= htmlspecialchars($b['title']) ?></h3><?php endif; ?>
              <?php if (!empty($b['subtitle'])): ?><p class="text-white/75 text-xs mt-1"><?= htmlspecialchars($b['subtitle']) ?></p><?php endif; ?>
              <?php if (!empty($b['link_url'])): ?><a href="<?= htmlspecialchars($b['link_url']) ?>" class="inline-flex items-center gap-1 text-amber-400 text-xs font-bold mt-2 hover:text-amber-300 transition">Shop Now <span class="material-icons text-[14px]">arrow_forward</span></a><?php endif; ?>
            </div>
          <?php else: ?>
            <div class="flex flex-col justify-end p-5 h-44" style="background:<?= $bannerBg ?>">
              <?php if (!empty($b['title'])): ?><h3 class="text-white font-heading font-bold text-lg leading-tight"><?= htmlspecialchars($b['title']) ?></h3><?php endif; ?>
              <?php if (!empty($b['subtitle'])): ?><p class="text-white/80 text-xs mt-1"><?= htmlspecialchars($b['subtitle']) ?></p><?php endif; ?>
              <?php if (!empty($b['link_url'])): ?><a href="<?= htmlspecialchars($b['link_url']) ?>" class="inline-flex items-center gap-1 text-amber-400 text-xs font-bold mt-3 hover:text-amber-300 transition">Shop Now <span class="material-icons text-[14px]">arrow_forward</span></a><?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ============================================================ -->
  <!-- PROMO BANNER STRIP                                             -->
  <!-- ============================================================ -->
  <section class="grid grid-cols-1 md:grid-cols-3 gap-4 fade-in-up">
    <div class="promo-card" style="background:linear-gradient(135deg,#0F172A 0%,#1e3a5f 100%)">
      <div class="relative z-10"><span class="text-xs font-bold uppercase tracking-wider text-amber-400">Electronics</span><h3 class="font-heading text-xl font-extrabold mt-1 leading-tight">Up to 60% Off<br>on Gadgets</h3><p class="text-white/60 text-xs mt-1.5 mb-4">Smartphones, Laptops & Audio</p><a href="<?= $baseUrl ?>/categories" class="inline-flex items-center gap-1 text-xs font-bold text-amber-400 hover:text-amber-300 transition">Shop Now <span class="material-icons text-[14px]">arrow_forward</span></a></div>
      <span class="material-icons absolute right-5 bottom-5 text-[60px] text-white/10">devices</span>
    </div>
    <div class="promo-card" style="background:linear-gradient(135deg,#7C3AED 0%,#4C1D95 100%)">
      <div class="relative z-10"><span class="text-xs font-bold uppercase tracking-wider text-pink-300">Fashion</span><h3 class="font-heading text-xl font-extrabold mt-1 leading-tight">Min 50% Off<br>on Top Brands</h3><p class="text-white/60 text-xs mt-1.5 mb-4">Clothing, Footwear & Accessories</p><a href="<?= $baseUrl ?>/categories" class="inline-flex items-center gap-1 text-xs font-bold text-pink-300 hover:text-pink-200 transition">Explore <span class="material-icons text-[14px]">arrow_forward</span></a></div>
      <span class="material-icons absolute right-5 bottom-5 text-[60px] text-white/10">checkroom</span>
    </div>
    <div class="promo-card" style="background:linear-gradient(135deg,#FF5100 0%,#B91C1C 100%)">
      <div class="relative z-10"><span class="text-xs font-bold uppercase tracking-wider text-orange-200">Home & Kitchen</span><h3 class="font-heading text-xl font-extrabold mt-1 leading-tight">Extra ₹500 Off<br>on Home Essentials</h3><p class="text-white/60 text-xs mt-1.5 mb-4">Appliances, Furniture & Decor</p><a href="<?= $baseUrl ?>/categories" class="inline-flex items-center gap-1 text-xs font-bold text-orange-200 hover:text-white transition">Buy Now <span class="material-icons text-[14px]">arrow_forward</span></a></div>
      <span class="material-icons absolute right-5 bottom-5 text-[60px] text-white/10">soup_kitchen</span>
    </div>
  </section>

  <!-- ============================================================ -->
  <!-- FLASH DEALS                                                    -->
  <!-- ============================================================ -->
  <section id="flash-deals" class="fade-in-up">
    <div class="bg-gradient-to-r from-orange-500 to-red-500 rounded-2xl px-5 sm:px-7 py-4 sm:py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5 shadow-lg">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
          <span class="material-icons text-white text-[22px]">flash_on</span>
        </div>
        <div>
          <h2 class="font-heading text-white text-lg sm:text-xl font-extrabold">⚡ Flash Deals</h2>
          <p class="text-orange-100 text-xs">Highest discounts today — grab before they're gone!</p>
        </div>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-orange-100 text-xs font-bold uppercase">Ends in:</span>
        <div class="flash-countdown">
          <span class="digit" id="flash-hrs">00</span>
          <span class="colon">:</span>
          <span class="digit" id="flash-mins">00</span>
          <span class="colon">:</span>
          <span class="digit" id="flash-secs">00</span>
        </div>
      </div>
    </div>

    <?php if (empty($flashDeals)): ?>
      <?php $sampleDeals = [['name'=>'Premium Wireless Headphones','category'=>'Electronics','price'=>8999,'sale'=>3599,'discount'=>60,'icon'=>'headphones'],['name'=>'Slim Fit Smart Watch','category'=>'Wearables','price'=>5999,'sale'=>2999,'discount'=>50,'icon'=>'watch'],['name'=>'Running Sports Shoes','category'=>'Footwear','price'=>3499,'sale'=>1749,'discount'=>50,'icon'=>'directions_run'],['name'=>'Non-stick Cookware Set','category'=>'Kitchen','price'=>2599,'sale'=>1299,'discount'=>50,'icon'=>'soup_kitchen']]; ?>
      <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <?php foreach ($sampleDeals as $sd): ?>
          <div class="offer-product-card">
            <div class="card-img-wrap"><div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-slate-100 to-slate-200" style="aspect-ratio:1"><span class="material-icons text-slate-400 text-6xl"><?= $sd['icon'] ?></span></div><span class="discount-badge">-<?= $sd['discount'] ?>%</span></div>
            <div class="card-body"><div class="card-category"><?= $sd['category'] ?></div><div class="card-title"><?= $sd['name'] ?></div><div class="card-prices"><span class="price-current">₹<?= number_format($sd['sale']) ?></span><span class="price-original">₹<?= number_format($sd['price']) ?></span></div><div class="card-rating"><span class="material-icons star">star</span><span>4.5</span></div><button class="btn-add-cart opacity-60 cursor-not-allowed" disabled><span class="material-icons text-[15px]">shopping_cart</span>Add to Cart</button></div>
          </div>
        <?php endforeach; ?>
      </div>
      <p class="text-center text-slate-400 text-xs mt-3">⬆ Sample deals — add products with sale prices to see live deals</p>
    <?php else: ?>
      <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <?php foreach ($flashDeals as $p):
          $salePrice = (float)$p['sale_price']; $basePrice = (float)$p['base_price'];
          $discount = $p['discount_percent'] ?? 0; $rating = round((float)($p['average_rating'] ?? 4.5),1);
          $reviews = (int)($p['review_count'] ?? 0); $inStock = (bool)($p['is_in_stock'] ?? true);
          $encId = htmlspecialchars($p['encrypted_id'] ?? $p['id']);
        ?>
          <div class="offer-product-card">
            <div class="card-img-wrap">
              <?php if (!empty($p['main_image_url'])): ?><img src="<?= htmlspecialchars($p['main_image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy"><?php else: ?><div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-indigo-50 to-blue-50"><span class="material-icons text-slate-300 text-[60px]">inventory_2</span></div><?php endif; ?>
              <span class="discount-badge">-<?= $discount ?>%</span>
              <a href="<?= $baseUrl ?>/wishlist" class="wishlist-btn"><span class="material-icons text-[16px]">favorite_border</span></a>
              <?php if (!$inStock): ?><div class="out-of-stock-overlay">Out of Stock</div><?php endif; ?>
            </div>
            <div class="card-body">
              <div class="card-category"><?= htmlspecialchars($p['category_name'] ?? '') ?></div>
              <div class="card-title"><a href="<?= $baseUrl ?>/product/<?= $encId ?>"><?= htmlspecialchars($p['name']) ?></a></div>
              <div class="card-prices"><span class="price-current">₹<?= number_format($salePrice, 0) ?></span><span class="price-original">₹<?= number_format($basePrice, 0) ?></span></div>
              <?php if ($rating > 0): ?><div class="card-rating"><span class="material-icons star">star</span><span><?= $rating ?></span><?php if ($reviews > 0): ?><span class="text-slate-400">(<?= $reviews ?>)</span><?php endif; ?></div><?php endif; ?>
              <?php if ($inStock): ?>
                <button onclick="addToCartAJAX('<?= $encId ?>', this)" class="btn-add-cart"><span class="material-icons text-[15px]">shopping_cart</span>Add to Cart</button>
              <?php else: ?>
                <button class="btn-add-cart opacity-50 cursor-not-allowed" disabled><span class="material-icons text-[15px]">remove_shopping_cart</span>Out of Stock</button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <!-- ============================================================ -->
  <!-- COUPON SECTION — gated by login & purchase tier               -->
  <!-- ============================================================ -->
  <section id="offer-coupons" class="fade-in-up">
    <div class="mb-5">
      <div class="section-badge"><span class="material-icons text-[14px]">confirmation_number</span> Member Coupons</div>
      <div class="flex items-end justify-between flex-wrap gap-2">
        <h2 class="font-heading text-xl sm:text-2xl font-bold text-slate-900">Your Exclusive Coupon Codes</h2>
        <?php if ($isLoggedIn): ?>
          <span class="text-xs text-slate-500 font-medium">Codes auto-unlock based on your purchase history</span>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!$isLoggedIn): ?>
      <!-- ── GUEST GATE ── -->
      <div class="relative">
        <!-- Blurred teaser cards behind the gate -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-4 pointer-events-none select-none" aria-hidden="true">
          <?php
            $teaserCount = min(6, max(3, count($allCoupons)));
            $sampleTiers = ['20% OFF – All Orders','₹500 Flat Discount','30% Off – First Order','15% Off Electronics','Free Delivery Code','40% Flash Coupon'];
            for ($ti = 0; $ti < $teaserCount; $ti++):
              $c = $allCoupons[$ti] ?? null;
              $label = $c
                ? (($c['discount_type'] === 'percentage') ? $c['discount_value'].'% OFF' : '₹'.number_format((float)$c['discount_value'],0).' OFF')
                : $sampleTiers[$ti % count($sampleTiers)];
          ?>
            <div class="coupon-teaser">
              <div class="flex items-start justify-between mb-3">
                <span class="inline-block text-xs font-extrabold px-3 py-1 rounded-lg bg-indigo-50 text-indigo-700 uppercase"><?= htmlspecialchars($label) ?></span>
                <div class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center"><span class="material-icons text-indigo-400 text-[20px]">card_giftcard</span></div>
              </div>
              <div class="h-3 bg-slate-200 rounded-full w-3/4 mb-2"></div>
              <div class="h-3 bg-slate-100 rounded-full w-1/2 mb-4"></div>
              <div class="flex gap-2 mt-4 pt-3 border-t border-slate-100">
                <div class="flex-1 h-9 bg-slate-100 rounded-lg"></div>
                <div class="w-20 h-9 bg-indigo-100 rounded-lg"></div>
              </div>
            </div>
          <?php endfor; ?>
        </div>

        <!-- Overlay gate card -->
        <div class="guest-gate relative z-10 -mt-4">
          <div class="relative z-10 space-y-4 max-w-md mx-auto">
            <div class="w-16 h-16 mx-auto rounded-2xl bg-white/15 flex items-center justify-center backdrop-blur">
              <span class="material-icons text-white text-[36px]">lock</span>
            </div>
            <h3 class="font-heading font-extrabold text-white text-2xl">Members-Only Coupons</h3>
            <p class="text-white/70 text-sm leading-relaxed">
              We have <strong class="text-amber-400"><?= count($allCoupons) ?: '6+' ?> exclusive coupons</strong> available for registered members. Your coupons are automatically unlocked based on your purchase history — the more you shop, the better deals you unlock!
            </p>
            <div class="grid grid-cols-2 gap-3 text-left mt-4">
              <div class="bg-white/10 rounded-xl p-3 backdrop-blur border border-white/10">
                <span class="material-icons text-amber-400 text-[18px]">person</span>
                <p class="text-white text-xs font-bold mt-1">New Member</p>
                <p class="text-white/60 text-[11px]">Unlock welcome coupons</p>
              </div>
              <div class="bg-white/10 rounded-xl p-3 backdrop-blur border border-white/10">
                <span class="material-icons text-orange-400 text-[18px]">workspace_premium</span>
                <p class="text-white text-xs font-bold mt-1">Bronze+ (₹999+)</p>
                <p class="text-white/60 text-[11px]">Loyalty discount codes</p>
              </div>
              <div class="bg-white/10 rounded-xl p-3 backdrop-blur border border-white/10">
                <span class="material-icons text-blue-300 text-[18px]">workspace_premium</span>
                <p class="text-white text-xs font-bold mt-1">Silver (₹5,000+)</p>
                <p class="text-white/60 text-[11px]">Premium savings codes</p>
              </div>
              <div class="bg-white/10 rounded-xl p-3 backdrop-blur border border-white/10">
                <span class="material-icons text-purple-300 text-[18px]">diamond</span>
                <p class="text-white text-xs font-bold mt-1">Gold & Platinum</p>
                <p class="text-white/60 text-[11px]">Best exclusive deals</p>
              </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 justify-center mt-2">
              <a href="<?= $baseUrl ?>/login" class="inline-flex items-center justify-center gap-2 bg-amber-400 hover:bg-amber-300 text-slate-900 font-extrabold px-6 py-3 rounded-xl transition shadow-lg text-sm">
                <span class="material-icons text-[18px]">login</span> Sign In to Unlock
              </a>
              <a href="<?= $baseUrl ?>/register" class="inline-flex items-center justify-center gap-2 bg-white/15 hover:bg-white/25 border border-white/25 text-white font-bold px-6 py-3 rounded-xl transition text-sm backdrop-blur">
                <span class="material-icons text-[18px]">person_add</span> Create Account
              </a>
            </div>
          </div>
        </div>
      </div>

    <?php elseif (empty($myCoupons) && empty($lockedCoupons) && empty($usedCoupons)): ?>
      <!-- Logged in but no coupons in DB -->
      <div class="bg-white border border-slate-200 rounded-2xl p-10 text-center">
        <span class="material-icons text-slate-300 text-[56px] block mb-3">confirmation_number</span>
        <p class="font-heading font-bold text-slate-700 text-lg">No coupons available right now</p>
        <p class="text-slate-400 text-sm mt-1">Check back soon — exclusive codes are added regularly!</p>
      </div>

    <?php else: ?>

      <!-- ── UNLOCKED COUPONS ── -->
      <?php if (!empty($myCoupons)): ?>
        <div class="mb-3 flex items-center gap-2">
          <span class="material-icons text-emerald-600 text-[18px]">lock_open</span>
          <h3 class="font-heading font-bold text-slate-800 text-base">Unlocked Coupons (<?= count($myCoupons) ?>)</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
          <?php
          $couponColors = ['from-indigo-600 to-purple-600','from-orange-500 to-red-500','from-emerald-500 to-teal-600','from-blue-500 to-cyan-500','from-amber-500 to-orange-400','from-pink-500 to-rose-500'];
          $ci = 0;
          foreach ($myCoupons as $c):
            $discLabel = ($c['discount_type'] === 'percentage')
                ? $c['discount_value'].'% OFF'
                : '₹'.number_format((float)$c['discount_value'], 0).' FLAT OFF';
            $cColor = $couponColors[$ci++ % count($couponColors)];
            $expiryTs = !empty($c['ends_at']) ? strtotime($c['ends_at']) : null;
            $daysLeft = $expiryTs ? ceil(($expiryTs - time()) / 86400) : null;
          ?>
            <div class="coupon-card">
              <div class="flex items-start justify-between mb-3">
                <div>
                  <span class="inline-block text-xs font-extrabold px-3 py-1 rounded-lg bg-indigo-50 text-indigo-700 uppercase tracking-wider mb-1"><?= htmlspecialchars($discLabel) ?></span>
                  <h4 class="font-heading font-bold text-slate-900 text-base"><?= htmlspecialchars($c['code']) ?></h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br <?= $cColor ?> flex items-center justify-center flex-shrink-0">
                  <span class="material-icons text-white text-[20px]">card_giftcard</span>
                </div>
              </div>
              <?php if (!empty($c['description'])): ?><p class="text-slate-500 text-xs leading-relaxed"><?= htmlspecialchars($c['description']) ?></p><?php endif; ?>
              <?php if (!empty($c['min_order_value']) && $c['min_order_value'] > 0): ?><p class="text-xs text-amber-600 font-semibold mt-1.5">Min. order: ₹<?= number_format((float)$c['min_order_value'], 0) ?></p><?php endif; ?>
              <?php if ($daysLeft !== null): ?>
                <p class="text-xs mt-1 <?= $daysLeft <= 3 ? 'text-red-500 font-bold' : 'text-slate-400' ?>">
                  <span class="material-icons text-[12px] align-middle"><?= $daysLeft <= 3 ? 'warning' : 'schedule' ?></span>
                  <?= $daysLeft <= 0 ? 'Expires today!' : "Expires in {$daysLeft} day".($daysLeft > 1 ? 's' : '') ?>
                </p>
              <?php endif; ?>
              <?php if (!empty($c['usage_limit'])): ?>
                <?php $usedPct = min(100, round(($c['used_count'] ?? 0) / max(1, $c['usage_limit']) * 100)); ?>
                <div class="usage-bar mt-2"><div class="usage-bar-fill" style="width:<?= $usedPct ?>%"></div></div>
                <p class="text-[10px] text-slate-400 mt-1"><?= $c['used_count'] ?? 0 ?>/<?= $c['usage_limit'] ?> used</p>
              <?php endif; ?>
              <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-100 gap-2">
                <div class="coupon-code-box"><?= htmlspecialchars($c['code']) ?></div>
                <button onclick="copyCouponCode('<?= htmlspecialchars($c['code']) ?>', this)" class="coupon-copy-btn">
                  <span class="material-icons text-[14px]">content_copy</span> Copy
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- ── ALREADY USED / CLAIMED COUPONS (1-TIME USE LIMIT) ── -->
      <?php if (!empty($usedCoupons)): ?>
        <div class="mb-3 flex items-center gap-2">
          <span class="material-icons text-slate-400 text-[18px]">check_circle</span>
          <h3 class="font-heading font-bold text-slate-500 text-base">Already Used Coupons (1-Time Use Reached) (<?= count($usedCoupons) ?>)</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
          <?php foreach ($usedCoupons as $c):
            $discLabel = ($c['discount_type'] === 'percentage')
                ? $c['discount_value'].'% OFF'
                : '₹'.number_format((float)$c['discount_value'], 0).' FLAT OFF';
          ?>
            <div class="coupon-card opacity-65 bg-slate-50 border-slate-200 grayscale-[40%]">
              <div class="flex items-start justify-between mb-3">
                <div>
                  <span class="inline-block text-xs font-extrabold px-3 py-1 rounded-lg bg-emerald-100 text-emerald-800 uppercase tracking-wider mb-1 flex items-center gap-1 w-fit">
                    <span class="material-icons text-[13px]">check_circle</span> Already Claimed
                  </span>
                  <h4 class="font-heading font-bold text-slate-500 text-base line-through"><?= htmlspecialchars($c['code']) ?></h4>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-200 text-slate-400 flex items-center justify-center flex-shrink-0">
                  <span class="material-icons text-[20px]">task_alt</span>
                </div>
              </div>
              <?php if (!empty($c['description'])): ?>
                <p class="text-slate-400 text-xs leading-relaxed"><?= htmlspecialchars($c['description']) ?></p>
              <?php endif; ?>
              <p class="text-[11px] text-slate-400 mt-2 font-medium bg-slate-100 p-2 rounded-lg border border-slate-200">
                ✓ You have already redeemed this 1-time coupon code on a previous order.
              </p>
              <div class="flex items-center justify-between mt-4 pt-3 border-t border-slate-200 gap-2">
                <div class="coupon-code-box bg-slate-100 text-slate-400 line-through select-none"><?= htmlspecialchars($c['code']) ?></div>
                <button class="coupon-copy-btn bg-slate-200 text-slate-400 cursor-not-allowed" disabled>
                  <span class="material-icons text-[14px]">done_all</span> Used
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- ── LOCKED COUPONS ── -->
      <?php if (!empty($lockedCoupons)): ?>
        <div class="mb-3 flex items-center gap-2">
          <span class="material-icons text-slate-400 text-[18px]">lock</span>
          <h3 class="font-heading font-bold text-slate-500 text-base">Locked Coupons — Spend More to Unlock (<?= count($lockedCoupons) ?>)</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php foreach ($lockedCoupons as $c):
            $discLabel = ($c['discount_type'] === 'percentage')
                ? $c['discount_value'].'% OFF'
                : '₹'.number_format((float)$c['discount_value'],0).' FLAT OFF';
            $spendNeeded = number_format((float)($c['spend_needed'] ?? 0), 0);
            $minOrder    = number_format((float)($c['min_order_value'] ?? 0), 0);
          ?>
            <div class="coupon-locked">
              <!-- Lock overlay icon -->
              <div class="absolute top-3 right-3 z-10">
                <div class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center">
                  <span class="material-icons text-slate-400 text-[18px]">lock</span>
                </div>
              </div>
              <div class="flex items-start justify-between mb-3 pr-10">
                <div>
                  <span class="locked-badge mb-1">🔒 <?= htmlspecialchars($discLabel) ?></span>
                  <h4 class="font-heading font-bold text-slate-400 text-base mt-1">Hidden Coupon</h4>
                </div>
              </div>
              <?php if (!empty($c['description'])): ?>
                <p class="text-slate-400 text-xs leading-relaxed line-clamp-2"><?= htmlspecialchars($c['description']) ?></p>
              <?php else: ?>
                <p class="text-slate-400 text-xs">Purchase more to reveal this exclusive discount code.</p>
              <?php endif; ?>
              <div class="mt-3 p-3 bg-amber-50 border border-amber-200 rounded-xl">
                <p class="text-amber-700 text-xs font-bold flex items-center gap-1.5">
                  <span class="material-icons text-[15px]">trending_up</span>
                  Spend ₹<?= $spendNeeded ?> more to unlock
                </p>
                <p class="text-amber-600 text-[11px] mt-0.5">Requires ₹<?= $minOrder ?> in total purchases</p>
              </div>
              <div class="flex items-center gap-2 mt-4 pt-3 border-t border-slate-200">
                <div class="locked-code-mask flex-1 select-none">••••••••</div>
                <button class="coupon-copy-btn opacity-40 cursor-not-allowed" disabled>
                  <span class="material-icons text-[14px]">lock</span> Locked
                </button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="mt-4 text-center">
          <a href="<?= $baseUrl ?>/categories" class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-6 py-3 rounded-xl transition text-sm shadow-lg">
            <span class="material-icons text-[18px]">shopping_bag</span>
            Shop More to Unlock Coupons
          </a>
        </div>
      <?php endif; ?>

    <?php endif; ?>
  </section>

  <!-- ============================================================ -->
  <!-- PROMOTIONAL OFFERS (from DB)                                   -->
  <!-- ============================================================ -->
  <?php if (!empty($offers)): ?>
  <section class="fade-in-up">
    <div class="mb-5">
      <div class="section-badge"><span class="material-icons text-[14px]">stars</span> Promotions</div>
      <h2 class="font-heading text-xl sm:text-2xl font-bold text-slate-900">Featured Promotional Offers</h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
      <?php
      $offerGrads = ['linear-gradient(135deg,#0F172A,#1e3a5f)','linear-gradient(135deg,#4C1D95,#7C3AED)','linear-gradient(135deg,#064E3B,#065F46)','linear-gradient(135deg,#7F1D1D,#B91C1C)'];
      $oi = 0;
      foreach ($offers as $o):
        $grad = $offerGrads[$oi++ % count($offerGrads)];
      ?>
        <div class="promo-card" style="background:<?= $grad ?>">
          <div class="relative z-10 space-y-3">
            <div class="flex items-center justify-between">
              <span class="text-xs font-extrabold uppercase tracking-wider bg-white/15 text-white px-3 py-1 rounded-full"><?= htmlspecialchars($o['offer_type'] ?? 'Special Offer') ?></span>
              <?php if (!empty($o['end_date'])): ?><span class="text-white/50 text-xs flex items-center gap-1"><span class="material-icons text-[12px]">schedule</span>Till <?= date('M d, Y', strtotime($o['end_date'])) ?></span><?php endif; ?>
            </div>
            <h3 class="font-heading text-xl font-extrabold text-white leading-snug"><?= htmlspecialchars($o['title'] ?? $o['name'] ?? '') ?></h3>
            <?php if (!empty($o['description'])): ?><p class="text-white/60 text-sm leading-relaxed"><?= htmlspecialchars($o['description']) ?></p><?php endif; ?>
            <?php $offEncId = htmlspecialchars($o['encrypted_id'] ?? $o['id']); ?>
            <a href="<?= $baseUrl ?>/offer/<?= $offEncId ?>" class="inline-flex items-center gap-2 bg-white text-slate-900 font-bold text-xs px-5 py-2.5 rounded-xl hover:bg-amber-400 transition shadow-lg">Shop Eligible Items <span class="material-icons text-[15px]">arrow_forward</span></a>
          </div>
          <span class="material-icons absolute right-6 bottom-6 text-[70px] text-white/10">campaign</span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ============================================================ -->
  <!-- MORE PRODUCTS ON SALE                                          -->
  <!-- ============================================================ -->
  <?php if (!empty($moreProducts)): ?>
  <section class="fade-in-up">
    <div class="flex items-end justify-between mb-5">
      <div>
        <div class="section-badge"><span class="material-icons text-[14px]">local_offer</span> More Savings</div>
        <h2 class="font-heading text-xl sm:text-2xl font-bold text-slate-900">More Products on Sale</h2>
      </div>
      <a href="<?= $baseUrl ?>/categories" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition">View All <span class="material-icons text-[15px]">arrow_forward</span></a>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
      <?php foreach ($moreProducts as $p):
        $salePrice = (float)$p['sale_price']; $basePrice = (float)$p['base_price'];
        $discount = $p['discount_percent'] ?? 0; $inStock = (bool)($p['is_in_stock'] ?? true);
        $encId = htmlspecialchars($p['encrypted_id'] ?? $p['id']);
        $rating = round((float)($p['average_rating'] ?? 0), 1);
        $reviews = (int)($p['review_count'] ?? 0);
      ?>
        <div class="offer-product-card">
          <div class="card-img-wrap">
            <?php if (!empty($p['main_image_url'])): ?><img src="<?= htmlspecialchars($p['main_image_url']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy"><?php else: ?><div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-50 to-indigo-50"><span class="material-icons text-slate-300 text-[60px]">inventory_2</span></div><?php endif; ?>
            <span class="discount-badge">-<?= $discount ?>%</span>
            <a href="<?= $baseUrl ?>/wishlist" class="wishlist-btn"><span class="material-icons text-[16px]">favorite_border</span></a>
            <?php if (!$inStock): ?><div class="out-of-stock-overlay">Out of Stock</div><?php endif; ?>
          </div>
          <div class="card-body">
            <div class="card-category"><?= htmlspecialchars($p['category_name'] ?? '') ?></div>
            <div class="card-title"><a href="<?= $baseUrl ?>/product/<?= $encId ?>"><?= htmlspecialchars($p['name']) ?></a></div>
            <div class="card-prices"><span class="price-current">₹<?= number_format($salePrice, 0) ?></span><span class="price-original">₹<?= number_format($basePrice, 0) ?></span></div>
            <?php if ($rating > 0): ?><div class="card-rating"><span class="material-icons star">star</span><span><?= $rating ?></span><?php if ($reviews > 0): ?><span class="text-slate-400">(<?= $reviews ?>)</span><?php endif; ?></div><?php endif; ?>
            <?php if ($inStock): ?>
              <button onclick="addToCartAJAX('<?= $encId ?>', this)" class="btn-add-cart"><span class="material-icons text-[15px]">shopping_cart</span>Add to Cart</button>
            <?php else: ?>
              <button class="btn-add-cart opacity-50 cursor-not-allowed" disabled><span class="material-icons text-[15px]">remove_shopping_cart</span>Out of Stock</button>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <!-- ============================================================ -->
  <!-- TRUST STRIP                                                    -->
  <!-- ============================================================ -->
  <section class="fade-in-up bg-white rounded-2xl border border-slate-100 shadow-sm p-5 sm:p-7">
    <p class="text-center text-xs font-bold uppercase tracking-wider text-slate-400 mb-4">Why shop offers with us</p>
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <div class="trust-strip-item"><span class="material-icons text-indigo-500 text-[22px]">verified</span><span>100% Genuine Deals</span></div>
      <div class="trust-strip-item"><span class="material-icons text-green-500 text-[22px]">lock</span><span>Secure Checkout</span></div>
      <div class="trust-strip-item"><span class="material-icons text-amber-500 text-[22px]">local_shipping</span><span>Free Delivery ₹999+</span></div>
      <div class="trust-strip-item"><span class="material-icons text-rose-500 text-[22px]">support_agent</span><span>24/7 Live Support</span></div>
    </div>
  </section>

</main>

<script>
// Countdown to midnight
(function() {
  function pad(n) { return String(n).padStart(2,'0'); }
  function update() {
    const now = new Date(), midnight = new Date(now); midnight.setHours(24,0,0,0);
    let s = Math.max(0, Math.floor((midnight - now) / 1000));
    const h = Math.floor(s/3600), m = Math.floor((s%3600)/60), sec = s%60;
    ['cd-hours','flash-hrs'].forEach(id => { const el = document.getElementById(id); if(el) el.textContent=pad(h); });
    ['cd-mins','flash-mins'].forEach(id => { const el = document.getElementById(id); if(el) el.textContent=pad(m); });
    ['cd-secs','flash-secs'].forEach(id => { const el = document.getElementById(id); if(el) el.textContent=pad(sec); });
  }
  update(); setInterval(update, 1000);
})();

// Copy coupon code
function copyCouponCode(code, btn) {
  navigator.clipboard.writeText(code).then(() => {
    const orig = btn.innerHTML;
    btn.innerHTML = '<span class="material-icons text-[14px]">check</span> Copied!';
    btn.style.background = '#D1FAE5'; btn.style.color = '#065F46';
    setTimeout(() => { btn.innerHTML = orig; btn.style.background=''; btn.style.color=''; }, 2000);
  });
}

// Scroll fade-in
(function() {
  const obs = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => { if(e.isIntersecting) { setTimeout(() => e.target.classList.add('visible'), i*80); obs.unobserve(e.target); }});
  }, { threshold: 0.08 });
  document.querySelectorAll('.fade-in-up').forEach(el => obs.observe(el));
})();

// Animate tier progress bar on scroll
(function() {
  const bars = document.querySelectorAll('.tier-progress-fill');
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(e => { if(e.isIntersecting) { e.target.style.width = e.target.dataset.width || e.target.style.width; obs.unobserve(e.target); }});
  }, { threshold: 0.5 });
  bars.forEach(b => obs.observe(b));
})();
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
