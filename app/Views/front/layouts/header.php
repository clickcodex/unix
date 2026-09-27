<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$baseUrl     = defined('BASE_URL') ? BASE_URL : '';
$siteTitle   = $siteTitle ?? \App\Models\Setting::get('site_name', 'ClickCodex — Shop Electronics, Fashion, Home & More Online');
$rawLogo     = \App\Models\Setting::get('logo_url') ?: \App\Models\Setting::get('site_logo', '');
$rawFavicon  = \App\Models\Setting::get('favicon_url') ?: \App\Models\Setting::get('site_favicon', '');

$siteLogoRaw    = !empty($siteLogo) ? $siteLogo : $rawLogo;
$siteFaviconRaw = !empty($siteFavicon) ? $siteFavicon : $rawFavicon;

$formatBrandingUrl = function(?string $url, string $baseUrl): string {
    if (empty($url)) return '';
    if (preg_match('#(?:public/)?uploads/(.+)$#', $url, $m)) {
        return rtrim($baseUrl, '/') . '/public/uploads/' . $m[1];
    }
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) return $url;
    $clean = ltrim($url, '/');
    if (strpos($clean, 'public/uploads/') === 0) return rtrim($baseUrl, '/') . '/' . $clean;
    if (strpos($clean, 'uploads/') === 0) return rtrim($baseUrl, '/') . '/public/' . $clean;
    return rtrim($baseUrl, '/') . '/' . $clean;
};

$siteLogo    = $formatBrandingUrl($siteLogoRaw, $baseUrl);
$siteFavicon = $formatBrandingUrl($siteFaviconRaw, $baseUrl);
$rawSiteName = \App\Models\Setting::get('site_name', 'ClickCodex');
if (strpos($rawSiteName, '—') !== false) {
    $rawSiteName = trim(explode('—', $rawSiteName)[0]);
} elseif (strpos($rawSiteName, '-') !== false) {
    $rawSiteName = trim(explode('-', $rawSiteName)[0]);
}

$nameParts = preg_split('/(?=[A-Z])|\s+/', $rawSiteName, -1, PREG_SPLIT_NO_EMPTY);
if (count($nameParts) >= 2) {
    $siteFirstName  = array_shift($nameParts);
    $siteSecondName = implode(' ', $nameParts);
} else {
    $len = strlen($rawSiteName);
    $mid = (int)ceil($len / 2);
    $siteFirstName  = substr($rawSiteName, 0, $mid);
    $siteSecondName = substr($rawSiteName, $mid);
}

$categories = $categories ?? $activeCategories ?? [];

$isLoggedIn = !empty($_SESSION['user_id']);
$isAdmin = !empty($_SESSION['admin_user_id']) || (!empty($_SESSION['user_role']) && strtolower($_SESSION['user_role']) === 'admin');
$userName = $_SESSION['user_name'] ?? 'Account';
$userEmail = $_SESSION['user_email'] ?? '';

$cartSummary   = $isLoggedIn ? \App\Models\Cart::getCartSummary() : ['item_count' => 0, 'total_amount' => 0];
$cartCount     = $isLoggedIn ? ($cartSummary['item_count'] ?? 0) : 0;
$wishlistCount = $isLoggedIn ? \App\Models\Wishlist::getCount((int)$_SESSION['user_id']) : 0;
$metaDescription = $metaDescription ?? $metaDesc ?? 'Shop top electronics, smartphones, fashion, audio gear, home appliances and mega discount offers online at ClickCodex with fast delivery.';
$metaKeywords    = $metaKeywords    ?? 'online shopping, ecommerce, buy smartphones, electronics sale, fashion offers, discount coupons, ClickCodex';
$canonicalUrl    = $canonicalUrl    ?? (isset($_SERVER['HTTP_HOST']) ? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : $baseUrl);
$ogImage         = $ogImage         ?? (!empty($siteLogo) ? $siteLogo : $baseUrl . '/public/uploads/branding/logo.png');
$ogType          = $ogType          ?? 'website';
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($siteTitle) ?></title>

<!-- Primary SEO Meta Tags -->
<meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
<meta name="keywords" content="<?= htmlspecialchars($metaKeywords) ?>">
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="author" content="ClickCodex">
<meta name="theme-color" content="#0F172A">
<link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">

<!-- Open Graph / Facebook / WhatsApp -->
<meta property="og:type" content="<?= htmlspecialchars($ogType) ?>">
<meta property="og:site_name" content="ClickCodex">
<meta property="og:title" content="<?= htmlspecialchars($siteTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($metaDescription) ?>">
<meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
<meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">

<!-- Twitter Cards -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= htmlspecialchars($siteTitle) ?>">
<meta name="twitter:description" content="<?= htmlspecialchars($metaDescription) ?>">
<meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>">

<!-- Schema.org WebSite Structured Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "ClickCodex",
  "url": "<?= htmlspecialchars($baseUrl ?: 'http://localhost') ?>",
  "potentialAction": {
    "@type": "SearchAction",
    "target": "<?= htmlspecialchars($baseUrl) ?>/search?q={search_term_string}",
    "query-input": "required name=search_term_string"
  }
}
</script>

<?php if (!empty($siteFavicon)): ?>
  <link rel="shortcut icon" href="<?= htmlspecialchars($siteFavicon) ?>" type="image/x-icon">
<?php endif; ?>

<!-- Google Fonts & Material Icons -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
  window.BASE_URL = '<?= $baseUrl ?>';
  var BASE_URL = window.BASE_URL;
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          cc: {
            yellow: '#FFB800',
            orange: '#FF5100',
            pink: '#FF006B',
            purple: '#8C30F5',
            blue: '#2D82FF',
            dark: '#0F172A',
            light: '#F8FAFC',
            ice: '#F1F5F9'
          }
        },
        fontFamily: {
          heading: ['Poppins', 'sans-serif'],
          body: ['Inter', 'sans-serif'],
        }
      }
    }
  }
</script>

<style>
  html, body { margin: 0; padding: 0; }
  body { font-family: 'Inter', sans-serif; background: #F8FAFC; overflow-x: clip; }
  h1, h2, h3, h4, .font-heading { font-family: 'Poppins', sans-serif; }

  .scrollbar-none::-webkit-scrollbar { display: none; }
  .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
  input:focus { outline: none; }
  ::selection { background: #FF5100; color: #fff; }

  /* Product Card Hover */
  .product-card {
    transition: transform 0.25s cubic-bezier(.4,0,.2,1), box-shadow 0.25s cubic-bezier(.4,0,.2,1);
    border: 1.5px solid #E2E8F0;
    position: relative;
    overflow: hidden;
    background: #FFFFFF;
    border-radius: 1rem;
    min-width: 0;
    box-sizing: border-box;
  }
  .product-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: #2D82FF;
    opacity: 0;
    transition: opacity 0.25s ease;
  }
  .product-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.08);
    border-color: #2D82FF;
  }
  .product-card:hover::before { opacity: 1; }

  .price-strike { text-decoration: line-through; color: #94A3B8; }

  /* Category Pill Hover */
  .cat-pill {
    transition: all 0.25s cubic-bezier(.4,0,.2,1);
    border: 1.5px solid transparent;
  }
  .cat-pill:hover {
    border-color: #2D82FF;
    background: #E8F0FE;
    transform: translateY(-3px);
  }

  /* Marquee Track Animation */
  .marquee-track { animation: cc-marquee 35s linear infinite; }
  .marquee-track:hover { animation-play-state: paused; }
  @keyframes cc-marquee {
    0% { transform: translateX(0); }
    100% { transform: translateX(-50%); }
  }

  /* Section Heading Accent Line */
  .section-heading {
    position: relative;
    display: inline-block;
  }
  .section-heading::after {
    content: '';
    position: absolute;
    bottom: -4px; left: 0;
    width: 48px; height: 3px;
    border-radius: 2px;
    background: #FF5100;
  }

  /* Sidebar Links */
  .sidebar-link {
    transition: all 0.15s ease;
    border-radius: 8px;
    padding: 8px 10px;
    margin: 2px 0;
  }
  .sidebar-link:hover {
    background: #E8F0FE;
    color: #2D82FF;
    padding-left: 16px;
  }

  @keyframes pulse-badge {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.15); }
  }

  /* Shop-By-Category Strip */
  .category-strip {
    overflow-x: auto;
  }

  /* Amazon Mobile Condensed Single-Row Header Style */
  @media (max-width: 639px) {
    #mainHeader.mobile-condensed #headerLogoWrapper,
    #mainHeader.mobile-condensed #headerAccountWrapper,
    #mainHeader.mobile-condensed #mobileSearchRow,
    #mainHeader.mobile-condensed #categoryStrip {
      display: none !important;
    }
    #mainHeader.mobile-condensed #mobileCondensedSearchForm {
      display: flex !important;
    }
  }

  /* Mobile Drawers Smooth Animations */
  .mobile-side-drawer {
    position: fixed; inset: 0; z-index: 100;
    pointer-events: none; opacity: 0;
    transition: opacity 0.3s ease;
  }
  .mobile-side-drawer.open {
    pointer-events: auto; opacity: 1;
  }
  .drawer-panel-left {
    transform: translateX(-100%);
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .mobile-side-drawer.open .drawer-panel-left {
    transform: translateX(0);
  }
  .drawer-panel-right {
    transform: translateX(100%);
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .mobile-side-drawer.open .drawer-panel-right {
    transform: translateX(0);
  }
</style>
</head>
<body class="text-slate-800 antialiased flex flex-col min-h-screen">

<!-- ================= TOAST NOTIFICATIONS CONTAINER ================= -->
<div id="toast-container" class="fixed top-5 right-5 z-[100] flex flex-col gap-2 pointer-events-none"></div>

<!-- ================= 1. LEFT MOBILE BROWSE DRAWER (AMAZON ☰ STYLE) ================= -->
<div id="mobileBrowseDrawer" class="mobile-side-drawer">
  <div onclick="toggleBrowseDrawer(false)" class="absolute inset-0 bg-black/50 backdrop-blur-sm cursor-pointer"></div>
  <div class="drawer-panel-left absolute inset-y-0 left-0 max-w-[280px] w-full bg-white shadow-2xl flex flex-col h-full z-10">
    
    <!-- Browse Drawer Header (Matching Screenshot Theme) -->
    <div class="bg-gradient-to-r from-[#2D82FF] to-[#8C30F5] text-white p-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-lg font-bold overflow-hidden border border-white/30 shrink-0">
          <span class="material-icons text-white text-xl">grid_view</span>
        </div>
        <div class="min-w-0">
          <p class="font-bold text-sm truncate">Browse Store</p>
          <p class="text-white/80 text-xs truncate">ClickCodex Marketplace</p>
        </div>
      </div>
      <button onclick="toggleBrowseDrawer(false)" class="text-white hover:text-yellow-300 transition p-1 cursor-pointer">
        <span class="material-icons text-xl">close</span>
      </button>
    </div>

    <!-- Browse Drawer Body -->
    <div class="flex-1 overflow-y-auto p-4 space-y-0.5 text-sm">
      
      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 pt-2 pb-1">SHOPPING</p>
      <a href="<?= $baseUrl ?>/" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-semibold bg-blue-50 text-[#2D82FF]">
        <span class="material-icons text-[18px] text-[#2D82FF]">home</span>
        <span>Store Home</span>
        <span class="ml-auto w-1.5 h-1.5 rounded-full bg-[#2D82FF]"></span>
      </a>

      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 pt-4 pb-1">TRENDING</p>
      <a href="<?= $baseUrl ?>/#featured-catalog" onclick="toggleBrowseDrawer(false)" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
        <span class="material-icons text-[18px] text-slate-400">star</span>
        <span>Bestsellers</span>
      </a>
      <a href="<?= $baseUrl ?>/#flash-deals" onclick="toggleBrowseDrawer(false)" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
        <span class="material-icons text-[18px] text-slate-400">bolt</span>
        <span>Deals of the Day</span>
      </a>

      <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 pt-4 pb-1">CATEGORIES</p>
      <?php foreach ($categories as $cat): ?>
        <a href="<?= $baseUrl ?>/category/<?= htmlspecialchars($cat['slug'] ?? '') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
          <span class="material-icons text-[18px] text-slate-400">category</span>
          <span class="truncate"><?= htmlspecialchars($cat['name']) ?></span>
        </a>
      <?php endforeach; ?>

      <div class="border-t border-slate-100 mt-3 pt-2">
        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-3 pt-1 pb-1">COMPANY &amp; SUPPORT</p>
        <a href="<?= $baseUrl ?>/offers" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
          <span class="material-icons text-[18px] text-slate-400">local_offer</span>
          <span>Offers &amp; Deals</span>
        </a>
        <a href="<?= $baseUrl ?>/about" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
          <span class="material-icons text-[18px] text-slate-400">info</span>
          <span>About Us</span>
        </a>
        <a href="<?= $baseUrl ?>/contact" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
          <span class="material-icons text-[18px] text-slate-400">support_agent</span>
          <span>Contact Support</span>
        </a>
        <a href="<?= $baseUrl ?>/faq" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
          <span class="material-icons text-[18px] text-slate-400">quiz</span>
          <span>FAQ &amp; Help</span>
        </a>
        <a href="<?= $baseUrl ?>/terms" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
          <span class="material-icons text-[18px] text-slate-400">gavel</span>
          <span>Terms &amp; Conditions</span>
        </a>
        <a href="<?= $baseUrl ?>/privacy" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
          <span class="material-icons text-[18px] text-slate-400">shield</span>
          <span>Privacy Policy</span>
        </a>
        <?php if ($isAdmin): ?>
          <a href="<?= $baseUrl ?>/admin" class="flex items-center gap-3 px-3 py-2.5 rounded-xl font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900">
            <span class="material-icons text-[18px] text-slate-400">admin_panel_settings</span>
            <span>Admin Portal</span>
          </a>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>

<!-- ================= 2. RIGHT MOBILE ACCOUNT DRAWER (PROFILE 👤 STYLE - MATCHES SCREENSHOT EXACTLY) ================= -->
<div id="mobileAccountDrawer" class="mobile-side-drawer">
  <div onclick="toggleAccountDrawer(false)" class="absolute inset-0 bg-black/50 backdrop-blur-sm cursor-pointer"></div>
  <div class="drawer-panel-right absolute inset-y-0 right-0 max-w-[280px] w-full bg-white shadow-2xl flex flex-col h-full z-10">
    
    <!-- Account Drawer Header (Matches Screenshot Design) -->
    <div class="bg-gradient-to-r from-[#2D82FF] to-[#8C30F5] text-white p-5 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-lg font-bold overflow-hidden border border-white/30 shrink-0">
          <?php if (!empty($_SESSION['user_avatar'])): ?>
            <img src="<?= htmlspecialchars($_SESSION['user_avatar']) ?>" alt="Avatar" class="w-full h-full object-cover">
          <?php else: ?>
            <span><?= !empty($userName) ? strtoupper(mb_substr($userName, 0, 1)) : 'U' ?></span>
          <?php endif; ?>
        </div>
        <div class="min-w-0">
          <p class="font-bold text-sm truncate"><?= htmlspecialchars($isLoggedIn ? $userName : 'Customer') ?></p>
          <p class="text-white/80 text-xs truncate"><?= htmlspecialchars($isLoggedIn ? $userEmail : 'Welcome to ClickCodex') ?></p>
        </div>
      </div>
      <button onclick="toggleAccountDrawer(false)" class="text-white hover:text-yellow-300 transition p-1 cursor-pointer">
        <span class="material-icons">close</span>
      </button>
    </div>

    <!-- Account Drawer Body Navigation -->
    <div class="flex-1 overflow-y-auto p-4 space-y-0.5">
      <?php if ($isLoggedIn): ?>
        <?php include __DIR__ . '/../user/sidebar_nav_items.php'; ?>
      <?php else: ?>
        <div class="p-3 text-center space-y-3">
          <p class="text-xs text-slate-600 font-medium">Please sign in to access your dashboard, orders, wishlist &amp; settings.</p>
          <button onclick="toggleAccountDrawer(false); openUserAuthModal('login');" class="w-full bg-cc-blue text-white font-bold py-2.5 rounded-xl shadow-md flex items-center justify-center gap-2 text-xs cursor-pointer">
            <span class="material-icons text-sm">login</span> Sign In / Register
          </button>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ================= TOP UTILITY BAR (desktop only) ================= -->
<div class="bg-slate-100 text-slate-600 text-xs border-b border-slate-200/60 relative overflow-hidden hidden sm:block">
  <div class="max-w-7xl mx-auto px-4 py-1.5 flex items-center justify-between relative">
    <p class="hidden sm:block text-slate-500 font-medium">Welcome to ClickCodex — India's fastest growing online marketplace</p>
    <div class="flex items-center gap-4 mx-auto sm:mx-0 text-slate-500 font-medium">
      <?php if ($isAdmin): ?>
        <a href="<?= $baseUrl ?>/admin" class="hover:text-cc-blue transition flex items-center gap-1 font-bold text-cc-blue">
          <span class="material-icons text-xs">admin_panel_settings</span> Admin Panel
        </a>
      <?php endif; ?>
      <a href="<?= $baseUrl ?>/cart" class="hover:text-cc-blue transition">Customer Care</a>
      <a href="<?= $baseUrl ?>/wishlist" class="hover:text-cc-blue transition">My Wishlist</a>
    </div>
  </div>
</div>

<!-- ================= MAIN HEADER (AMAZON STYLE MOBILE + DESKTOP) ================= -->
<header id="mainHeader" class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-xs transition-all duration-300">
  
  <!-- Row 1: Top Header Row -->
  <div id="mobileHeaderTopRow" class="max-w-7xl mx-auto px-2.5 sm:px-4 py-2 sm:py-3 flex items-center justify-between gap-1.5 sm:gap-4 transition-all duration-300">

    <!-- Left: Hamburger (Mobile) + Logo Icon -->
    <div class="flex items-center gap-1 sm:gap-2 shrink-0">
      <!-- Mobile Hamburger Button (Amazon ☰ Style -> Opens Left Browse Drawer) -->
      <button onclick="toggleBrowseDrawer(true)" class="sm:hidden text-slate-800 p-1 hover:text-cc-blue transition focus:outline-none cursor-pointer shrink-0" aria-label="Open Browse Menu">
        <span class="material-icons text-2xl">menu</span>
      </button>

      <!-- Brand Logo + Two-Color Company Name -->
      <a href="<?= $baseUrl ?>/" class="flex items-center gap-1.5 sm:gap-2 group shrink-0" title="<?= htmlspecialchars($rawSiteName) ?>">
        <?php if (!empty($siteLogo)): ?>
          <img src="<?= htmlspecialchars($siteLogo) ?>" alt="<?= htmlspecialchars($rawSiteName) ?>" class="h-7 sm:h-9 w-auto max-w-[75px] sm:max-w-none object-contain" onerror="this.style.display='none'">
        <?php else: ?>
          <div class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl bg-cc-blue/10 flex items-center justify-center group-hover:bg-cc-blue/20 transition shrink-0">
            <svg width="20" height="20" viewBox="0 0 100 100">
              <path d="M78 24 A38 38 0 1 0 78 76" fill="none" stroke="#2D82FF" stroke-width="14" stroke-linecap="round"/>
              <path d="M55 38 L44 50 L55 62" fill="none" stroke="#2D82FF" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" opacity="0.85"/>
            </svg>
          </div>
        <?php endif; ?>
        <span class="hidden sm:inline-flex font-heading font-extrabold text-xl sm:text-2xl text-slate-900 leading-none items-center">
          <?= htmlspecialchars($siteFirstName) ?><span class="text-cc-blue"><?= htmlspecialchars($siteSecondName) ?></span>
        </span>
      </a>
    </div>

    <!-- Mobile Center Search Bar with Live Suggestions (Takes maximum available width) -->
    <form action="<?= $baseUrl ?>/search" method="GET" class="sm:hidden flex-1 min-w-0 relative flex items-stretch h-9 rounded-xl border border-slate-300 overflow-visible shadow-2xs focus-within:border-cc-blue focus-within:ring-2 focus-within:ring-cc-blue/10 transition bg-white mx-0.5">
      <div class="relative flex-1 min-w-0 flex items-center">
        <input type="text" name="q" id="headerSearchInputMobile" oninput="handleSearchAutocomplete(this, 'mobile')" onfocus="handleSearchAutocomplete(this, 'mobile')" autocomplete="off" placeholder="Search ClickCodex..."
               class="w-full h-full px-3 text-xs text-slate-800 bg-white focus:outline-none placeholder:text-slate-400 rounded-l-xl" />
        <!-- Mobile Autocomplete Suggestions Panel -->
        <div id="mobileSearchDropdown" class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 hidden overflow-hidden divide-y divide-slate-100 max-h-[320px] overflow-y-auto"></div>
      </div>
      <button type="submit" class="bg-cc-orange hover:bg-orange-600 px-3 flex items-center justify-center transition text-white shrink-0 rounded-r-xl cursor-pointer" aria-label="Search">
        <span class="material-icons text-[18px]">search</span>
      </button>
    </form>

    <!-- Deliver to Location (desktop only) -->
    <div class="cursor-pointer hidden lg:flex flex-col text-slate-700 text-xs leading-tight px-3 border-x border-slate-200 shrink-0 select-none hover:bg-slate-50 py-1 rounded-lg transition">
      <span class="text-slate-400 flex items-center gap-1">
        <span class="material-icons text-xs">location_on</span> Deliver to
      </span>
      <span class="font-bold text-slate-800">India 400001</span>
    </div>

    <!-- Desktop Center Search Bar with Live Autocomplete -->
    <form action="<?= $baseUrl ?>/search" method="GET" class="relative hidden sm:flex flex-1 rounded-xl border border-slate-200 overflow-visible shadow-xs focus-within:border-cc-blue focus-within:ring-2 focus-within:ring-cc-blue/10 transition">
      <select name="category" class="hidden md:block bg-slate-50 text-slate-600 text-xs px-3 border-r border-slate-200 font-medium focus:ring-0 cursor-pointer rounded-l-xl">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= htmlspecialchars($cat['slug'] ?? $cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <div class="relative flex-1 flex items-center">
        <input type="text" name="q" id="headerSearchInputDesktop" oninput="handleSearchAutocomplete(this, 'desktop')" onfocus="handleSearchAutocomplete(this, 'desktop')" autocomplete="off" placeholder="Search for products, brands and more..."
               class="w-full px-4 py-2.5 text-xs text-slate-700 bg-white focus:outline-none placeholder:text-slate-400" />
        <!-- Desktop Autocomplete Suggestions Panel -->
        <div id="desktopSearchDropdown" class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-2xl border border-slate-200 z-50 hidden overflow-hidden divide-y divide-slate-100 max-h-[380px] overflow-y-auto"></div>
      </div>
      <button type="submit" class="bg-cc-blue hover:bg-blue-600 px-5 flex items-center justify-center transition text-white shrink-0 rounded-r-xl cursor-pointer">
        <span class="material-icons text-[20px]">search</span>
      </button>
    </form>

    <!-- Right Side Actions -->
    <div class="flex items-center gap-1.5 sm:gap-4 shrink-0">
      
      <!-- Account / Profile Icon -->
      <?php if ($isLoggedIn): ?>
        <a href="<?= $baseUrl ?>/user/dashboard" onclick="handleAccountClick(event)" class="flex items-center gap-1 text-slate-700 hover:text-cc-blue transition p-1 cursor-pointer">
          <span class="text-[11px] font-bold text-slate-800 max-w-[65px] sm:max-w-[100px] truncate hidden sm:inline"><?= htmlspecialchars(explode(' ', $userName)[0]) ?> ›</span>
          <span class="material-icons text-2xl text-slate-700">account_circle</span>
        </a>
      <?php else: ?>
        <a href="<?= $baseUrl ?>/user/dashboard" onclick="handleAccountClick(event)" class="flex items-center gap-1 text-slate-700 hover:text-cc-blue transition p-1 cursor-pointer">
          <span class="text-[11px] font-bold text-slate-700 hidden sm:inline">Sign In ›</span>
          <span class="material-icons text-2xl text-slate-700">person_outline</span>
        </a>
      <?php endif; ?>

      <!-- Wishlist Link (Desktop) -->
      <a href="<?= $baseUrl ?>/wishlist" class="hidden sm:flex relative flex-col items-center text-xs leading-tight hover:text-cc-blue transition px-2 py-1 rounded-lg hover:bg-slate-50" title="Wishlist">
        <span class="material-icons text-[24px]">favorite_border</span>
        <span id="header-wishlist-badge" class="absolute -top-1 -right-0.5 bg-cc-pink text-white text-[10px] font-bold rounded-full h-4 w-4 flex items-center justify-center shadow-md wishlist-badge-count"><?= $wishlistCount ?></span>
      </a>

      <!-- Cart Icon & Badge -->
      <a href="<?= $baseUrl ?>/cart" class="relative flex items-center p-1 text-slate-800 hover:text-cc-blue transition shrink-0" title="Cart">
        <span class="material-icons text-2xl sm:text-[24px]">shopping_cart</span>
        <span id="header-cart-badge" class="cart-badge absolute -top-1 -right-1 bg-cc-orange text-white text-[9px] font-bold rounded-full h-4 w-4 flex items-center justify-center shadow cart-badge-count"><?= $cartCount ?></span>
      </a>
    </div>

  </div>
  <nav id="categoryStrip" class="category-strip border-t border-slate-200/80 bg-slate-50">
    <ul class="max-w-7xl mx-auto px-3 sm:px-4 flex items-center gap-3 sm:gap-4 overflow-x-auto scrollbar-none text-slate-700 text-xs py-2 sm:py-2.5 whitespace-nowrap">
      <li>
        <a href="<?= $baseUrl ?>/" class="flex items-center gap-1 font-bold text-cc-blue hover:text-blue-700 transition bg-cc-blue/10 px-2.5 py-1 rounded-lg text-[11px] sm:text-xs">
          <span class="material-icons text-[15px]">grid_view</span> All
        </a>
      </li>
      <?php foreach (array_slice($categories, 0, 8) as $cat): ?>
        <li>
          <a href="<?= $baseUrl ?>/category/<?= htmlspecialchars($cat['slug'] ?? '') ?>" class="hover:text-cc-blue transition font-medium text-[11px] sm:text-xs">
            <?= htmlspecialchars($cat['name']) ?>
          </a>
        </li>
      <?php endforeach; ?>
      <li>
        <a href="#flash-deals" class="bg-cc-orange text-white font-bold px-2.5 py-1 rounded-lg hover:bg-orange-600 transition text-[10px] sm:text-[11px] uppercase tracking-wide flex items-center gap-1">
          <span class="material-icons text-[13px]">bolt</span> Deals
        </a>
      </li>
    </ul>
  </nav>

</header>

<!-- ================= ANNOUNCEMENT MARQUEE ================= -->
<div class="bg-cc-orange text-white text-xs py-2 overflow-hidden font-medium shadow-inner">
  <div class="flex marquee-track whitespace-nowrap">
    <div class="flex gap-12 pr-12">
      <span class="flex items-center gap-1.5"><span class="material-icons text-[18px]">local_shipping</span>Free delivery on orders above ₹999</span>
      <span class="flex items-center gap-1.5"><span class="material-icons text-[18px]">credit_card</span>Extra 10% off on UPI payments</span>
      <span class="flex items-center gap-1.5"><span class="material-icons text-[18px]">autorenew</span>7-day easy returns on all products</span>
      <span class="flex items-center gap-1.5"><span class="material-icons text-[18px]">celebration</span>ClickCodex Mega Sale is Live!</span>
    </div>
    <div class="flex gap-12 pr-12" aria-hidden="true">
      <span class="flex items-center gap-1.5"><span class="material-icons text-[18px]">local_shipping</span>Free delivery on orders above ₹999</span>
      <span class="flex items-center gap-1.5"><span class="material-icons text-[18px]">credit_card</span>Extra 10% off on UPI payments</span>
      <span class="flex items-center gap-1.5"><span class="material-icons text-[18px]">autorenew</span>7-day easy returns on all products</span>
      <span class="flex items-center gap-1.5"><span class="material-icons text-[18px]">celebration</span>ClickCodex Mega Sale is Live!</span>
    </div>
  </div>
</div>

<!-- ================= USER AUTH MODAL POPUP ================= -->
<div id="userAuthModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[100] hidden items-center justify-center p-4">
  <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100 animate-in fade-in zoom-in duration-200">
    
    <!-- Modal Header Tabs -->
    <div class="flex border-b border-slate-200 bg-slate-50">
      <button id="authTabLoginBtn" onclick="switchAuthTab('login')" class="flex-1 py-4 text-center font-bold text-sm text-cc-blue border-b-2 border-cc-blue transition">
        Login to Account
      </button>
      <button id="authTabRegisterBtn" onclick="switchAuthTab('register')" class="flex-1 py-4 text-center font-bold text-sm text-slate-500 hover:text-slate-900 transition">
        Create New Account
      </button>
      <button onclick="closeUserAuthModal()" class="px-4 text-slate-400 hover:text-slate-700">
        <span class="material-icons text-xl">close</span>
      </button>
    </div>

    <!-- Alert Container -->
    <div id="authAlert" class="hidden p-3.5 text-xs font-semibold mx-6 mt-4 rounded-xl"></div>

    <!-- Login Tab Content -->
    <form id="authLoginForm" onsubmit="submitLoginAJAX(event)" class="p-6 space-y-4">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Address</label>
        <input type="email" name="email" required placeholder="name@example.com" class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-cc-blue focus:ring-2 focus:ring-cc-blue/10">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Password</label>
        <input type="password" name="password" required placeholder="••••••••" class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-cc-blue focus:ring-2 focus:ring-cc-blue/10">
      </div>
      <button type="submit" id="loginSubmitBtn" class="w-full bg-cc-blue hover:bg-blue-600 text-white font-bold text-xs py-3 rounded-xl transition shadow-md shadow-cc-blue/20 flex items-center justify-center gap-2">
        <span>Login to Store</span>
        <span class="material-icons text-sm">arrow_forward</span>
      </button>
    </form>

    <!-- Register Tab Content -->
    <form id="authRegisterForm" onsubmit="submitRegisterAJAX(event)" class="p-6 space-y-3.5 hidden">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Name</label>
        <input type="text" name="name" required placeholder="Priya Patel" class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-cc-blue focus:ring-2 focus:ring-cc-blue/10">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Address</label>
        <input type="email" name="email" required placeholder="priya@example.com" class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-cc-blue focus:ring-2 focus:ring-cc-blue/10">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mobile Phone (Optional)</label>
        <input type="text" name="phone" placeholder="+91 98765 43210" class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-cc-blue focus:ring-2 focus:ring-cc-blue/10">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Create Password</label>
        <input type="password" name="password" required minlength="6" placeholder="At least 6 characters" class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:border-cc-blue focus:ring-2 focus:ring-cc-blue/10">
      </div>
      <button type="submit" id="registerSubmitBtn" class="w-full bg-cc-orange hover:bg-orange-600 text-white font-bold text-xs py-3 rounded-xl transition shadow-md shadow-cc-orange/20 flex items-center justify-center gap-2">
        <span>Register &amp; Start Shopping</span>
        <span class="material-icons text-sm">person_add</span>
      </button>
    </form>

  </div>
</div>

<!-- ================= GLOBAL FRONTEND AJAX JAVASCRIPT ================= -->
<script>
// Toast Notification Helper
function showToast(message, type = 'success') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `p-3.5 rounded-2xl text-xs font-bold shadow-xl border flex items-center gap-2 transition-all transform translate-y-2 pointer-events-auto ${
    type === 'success' ? 'bg-slate-900 text-white border-slate-800' : 'bg-red-600 text-white border-red-500'
  }`;
  toast.innerHTML = `
    <span class="material-icons text-sm ${type === 'success' ? 'text-green-400' : 'text-white'}">${type === 'success' ? 'check_circle' : 'error'}</span>
    <span>${message}</span>
  `;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-10px)';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

// AJAX Add to Cart
async function addToCartAJAX(productId, buttonElem) {
  const originalHtml = buttonElem.innerHTML;
  buttonElem.disabled = true;
  buttonElem.innerHTML = `<span class="material-icons text-sm animate-spin">refresh</span> Adding...`;

  try {
    const res = await fetch(`${BASE_URL}/cart/add`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: productId, quantity: 1 })
    });
    const data = await res.json();

    if (res.status === 401 || data.require_login) {
      showToast(data.message || 'Please sign in to add items to your cart.', 'warning');
      if (typeof openUserAuthModal === 'function') {
        openUserAuthModal('login');
      } else {
        window.location.href = `${BASE_URL}/login?redirect=cart`;
      }
      return;
    }

    if (data.success) {
      // Update header cart badge
      const badge = document.getElementById('header-cart-badge');
      if (badge) {
        badge.innerText = data.cart_count;
        badge.classList.add('scale-125');
        setTimeout(() => badge.classList.remove('scale-125'), 300);
      }
      showToast(data.message || 'Added to cart!');
    } else {
      showToast(data.message || 'Could not add product to cart.', 'error');
    }
  } catch (err) {
    showToast('Network error while adding product to cart.', 'error');
  } finally {
    buttonElem.disabled = false;
    buttonElem.innerHTML = originalHtml;
  }
}

// Auth Modal Functions
function openUserAuthModal(tab = 'login') {
  const modal = document.getElementById('userAuthModal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
  switchAuthTab(tab);
}

function closeUserAuthModal() {
  const modal = document.getElementById('userAuthModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

function switchAuthTab(tab) {
  const loginForm = document.getElementById('authLoginForm');
  const regForm = document.getElementById('authRegisterForm');
  const loginBtn = document.getElementById('authTabLoginBtn');
  const regBtn = document.getElementById('authTabRegisterBtn');
  const alert = document.getElementById('authAlert');
  alert.classList.add('hidden');

  if (tab === 'login') {
    loginForm.classList.remove('hidden');
    regForm.classList.add('hidden');
    loginBtn.className = 'flex-1 py-4 text-center font-bold text-sm text-cc-blue border-b-2 border-cc-blue transition';
    regBtn.className = 'flex-1 py-4 text-center font-bold text-sm text-slate-500 hover:text-slate-900 transition';
  } else {
    loginForm.classList.add('hidden');
    regForm.classList.remove('hidden');
    regBtn.className = 'flex-1 py-4 text-center font-bold text-sm text-cc-orange border-b-2 border-cc-orange transition';
    loginBtn.className = 'flex-1 py-4 text-center font-bold text-sm text-slate-500 hover:text-slate-900 transition';
  }
}

async function submitLoginAJAX(e) {
  e.preventDefault();
  const form = e.target;
  const alert = document.getElementById('authAlert');
  const submitBtn = document.getElementById('loginSubmitBtn');

  alert.classList.add('hidden');
  submitBtn.disabled = true;
  submitBtn.innerText = 'Signing in...';

  try {
    const res = await fetch(`${BASE_URL}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        email: form.email.value,
        password: form.password.value
      })
    });
    const data = await res.json();

    if (data.success) {
      showToast(data.message || 'Logged in successfully!');
      setTimeout(() => location.reload(), 600);
    } else {
      alert.className = 'p-3.5 text-xs font-semibold mx-6 mt-4 rounded-xl bg-red-50 text-red-600 border border-red-200';
      alert.innerText = data.message || 'Login failed.';
      alert.classList.remove('hidden');
    }
  } catch (err) {
    alert.className = 'p-3.5 text-xs font-semibold mx-6 mt-4 rounded-xl bg-red-50 text-red-600 border border-red-200';
    alert.innerText = 'Network error while logging in.';
    alert.classList.remove('hidden');
  } finally {
    submitBtn.disabled = false;
    submitBtn.innerHTML = `<span>Login to Store</span><span class="material-icons text-sm">arrow_forward</span>`;
  }
}

async function submitRegisterAJAX(e) {
  e.preventDefault();
  const form = e.target;
  const alert = document.getElementById('authAlert');
  const submitBtn = document.getElementById('registerSubmitBtn');

  alert.classList.add('hidden');
  submitBtn.disabled = true;
  submitBtn.innerText = 'Creating account...';

  try {
    const res = await fetch(`${BASE_URL}/auth/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: form.name.value,
        email: form.email.value,
        phone: form.phone.value,
        password: form.password.value
      })
    });
    const data = await res.json();

    if (data.success) {
      showToast(data.message || 'Account created successfully!');
      setTimeout(() => location.reload(), 600);
    } else {
      alert.className = 'p-3.5 text-xs font-semibold mx-6 mt-4 rounded-xl bg-red-50 text-red-600 border border-red-200';
      alert.innerText = data.message || 'Registration failed.';
      alert.classList.remove('hidden');
    }
  } catch (err) {
    alert.className = 'p-3.5 text-xs font-semibold mx-6 mt-4 rounded-xl bg-red-50 text-red-600 border border-red-200';
    alert.innerText = 'Network error while creating account.';
    alert.classList.remove('hidden');
  } finally {
    submitBtn.disabled = false;
    submitBtn.innerHTML = `<span>Register &amp; Start Shopping</span><span class="material-icons text-sm">person_add</span>`;
  }
}

function toggleBrowseDrawer(open) {
  const drawer = document.getElementById('mobileBrowseDrawer');
  if (drawer) {
    if (open) drawer.classList.add('open');
    else drawer.classList.remove('open');
  }
}

function toggleAccountDrawer(open) {
  const drawer = document.getElementById('mobileAccountDrawer');
  if (drawer) {
    if (open) drawer.classList.add('open');
    else drawer.classList.remove('open');
  }
}

function handleAccountClick(e) {
  if (window.innerWidth < 640) {
    e.preventDefault();
    toggleAccountDrawer(true);
  }
}

// Flicker-Free Shop-By-Category Strip & Amazon Mobile Header Scroll Handler
(function() {
  const mainHeader = document.getElementById('mainHeader');
  if (!mainHeader) return;

  let lastScrollY = window.scrollY;
  let scrollUpDistance = 0;
  let ticking = false;
  let isCondensed = false;

  function updateHeaderOnScroll() {
    const currentScrollY = window.scrollY;
    const scrollDelta = currentScrollY - lastScrollY;
    const isMobile = window.innerWidth < 640;

    // Add shadow when scrolled
    if (currentScrollY > 15) {
      mainHeader.classList.add('shadow-md', 'border-slate-300');
      mainHeader.classList.remove('shadow-xs');
    } else {
      mainHeader.classList.remove('shadow-md', 'border-slate-300');
      mainHeader.classList.add('shadow-xs');
    }

    if (isMobile) {
      if (scrollDelta > 0) {
        // Scrolling DOWN
        scrollUpDistance = 0; // Reset scroll up accumulator
        if (!isCondensed && currentScrollY > 70 && scrollDelta > 8) {
          mainHeader.classList.add('mobile-condensed');
          isCondensed = true;
        }
      } else if (scrollDelta < 0) {
        // Scrolling UP
        scrollUpDistance += Math.abs(scrollDelta); // Accumulate upward scroll distance

        // Expand ONLY when near top (scrollY <= 50) OR after significant upward scroll (> 120px)
        if (isCondensed && (currentScrollY <= 50 || scrollUpDistance > 120)) {
          mainHeader.classList.remove('mobile-condensed');
          isCondensed = false;
          scrollUpDistance = 0;
        }
      }

      lastScrollY = currentScrollY;
    } else {
      // On desktop, ensure mobile-condensed is removed
      if (isCondensed) {
        mainHeader.classList.remove('mobile-condensed');
        isCondensed = false;
      }
      lastScrollY = currentScrollY;
    }

    ticking = false;
  }

  window.addEventListener('scroll', function() {
    if (!ticking) {
      window.requestAnimationFrame(updateHeaderOnScroll);
      ticking = true;
    }
  }, { passive: true });

  window.addEventListener('resize', function() {
    if (window.innerWidth >= 640 && isCondensed) {
      mainHeader.classList.remove('mobile-condensed');
      isCondensed = false;
    }
  }, { passive: true });
})();

// Live Search Autocomplete Functionality
let searchDebounceTimer = null;

function handleSearchAutocomplete(inputEl, target) {
  clearTimeout(searchDebounceTimer);
  const q = inputEl.value.trim();
  let dropdown = null;
  if (target === 'desktop') dropdown = document.getElementById('desktopSearchDropdown');
  else if (target === 'mobileCondensed') dropdown = document.getElementById('mobileCondensedSearchDropdown');
  else dropdown = document.getElementById('mobileSearchDropdown');

  if (!dropdown) return;

  if (q.length < 2) {
    dropdown.innerHTML = '';
    dropdown.classList.add('hidden');
    return;
  }

  searchDebounceTimer = setTimeout(async () => {
    try {
      const res = await fetch(`${BASE_URL}/api/search/suggestions?q=${encodeURIComponent(q)}`);
      const data = await res.json();
      
      if (!data.success || (!data.products.length && !data.categories.length)) {
        dropdown.innerHTML = `
          <div class="p-4 text-center text-xs text-slate-500 font-medium">
            No instant suggestions found for "<span class="font-bold text-slate-800">${escapeHTML(q)}</span>".
            <a href="${BASE_URL}/search?q=${encodeURIComponent(q)}" class="block mt-2 font-bold text-cc-blue hover:underline">
              Press Enter to view all results &rarr;
            </a>
          </div>
        `;
        dropdown.classList.remove('hidden');
        return;
      }

      let html = '';

      // Category matches
      if (data.categories && data.categories.length) {
        html += `<div class="p-2.5 bg-slate-50 border-b border-slate-100"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-2">Matching Categories</p><div class="flex flex-wrap gap-1.5 pt-1.5 px-1">`;
        data.categories.forEach(cat => {
          html += `<a href="${BASE_URL}/category/${cat.slug}" class="bg-white hover:bg-cc-blue hover:text-white text-slate-700 font-bold text-[11px] px-2.5 py-1 rounded-lg border border-slate-200 transition">${escapeHTML(cat.name)}</a>`;
        });
        html += `</div></div>`;
      }

      // Product matches
      if (data.products && data.products.length) {
        html += `<div class="p-2 bg-slate-50 border-b border-slate-100"><p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest px-2">Matching Products</p></div>`;
        data.products.forEach(p => {
          const img = p.primary_image || 'https://placehold.co/100x100/f1f5f9/94a3b8?text=No+Image';
          const price = p.sale_price ? parseFloat(p.sale_price).toFixed(2) : parseFloat(p.base_price).toFixed(2);
          html += `
            <a href="${BASE_URL}/product/${p.slug}" class="flex items-center gap-3 p-2.5 hover:bg-blue-50/60 transition group">
              <img src="${img}" alt="${escapeHTML(p.name)}" class="w-10 h-10 object-cover rounded-lg bg-slate-100 border border-slate-200 shrink-0">
              <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-slate-800 group-hover:text-cc-blue transition truncate">${escapeHTML(p.name)}</p>
                <span class="text-[10px] text-slate-400 font-medium">${escapeHTML(p.category_name || '')}</span>
              </div>
              <span class="font-extrabold text-xs text-slate-900 shrink-0">₹${price}</span>
            </a>
          `;
        });
      }

      // View All Link
      html += `
        <a href="${BASE_URL}/search?q=${encodeURIComponent(q)}" class="block p-3 text-center bg-slate-50 hover:bg-slate-100 font-bold text-xs text-cc-blue transition">
          View all results for "${escapeHTML(q)}" &rarr;
        </a>
      `;

      dropdown.innerHTML = html;
      dropdown.classList.remove('hidden');
    } catch (err) {
      console.error(err);
    }
  }, 180);
}

// Close search dropdowns when clicking outside
document.addEventListener('click', function(e) {
  if (!e.target.closest('#mainHeader')) {
    const d1 = document.getElementById('desktopSearchDropdown');
    const d2 = document.getElementById('mobileSearchDropdown');
    if (d1) d1.classList.add('hidden');
    if (d2) d2.classList.add('hidden');
  }
});

function escapeHTML(str) {
  return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>
