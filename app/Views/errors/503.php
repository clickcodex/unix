<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$baseUrl     = defined('BASE_URL') ? BASE_URL : '';
$siteTitle   = \App\Models\Setting::get('site_name', 'ClickCodex') . ' — Under Maintenance';
$rawLogo     = \App\Models\Setting::get('logo_url') ?: \App\Models\Setting::get('site_logo', '');
$rawFavicon  = \App\Models\Setting::get('favicon_url') ?: \App\Models\Setting::get('site_favicon', '');

$siteLogoRaw    = !empty($siteLogo) ? $siteLogo : $rawLogo;
$siteFaviconRaw = !empty($siteFavicon) ? $siteFavicon : $rawFavicon;

$formatBrandingUrl = function(?string $url, string $baseUrl): string {
    if (empty($url)) return '';
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) return $url;
    $clean = ltrim($url, '/');
    if (strpos($clean, 'public/uploads/') === 0) return $baseUrl . '/' . $clean;
    if (strpos($clean, 'uploads/') === 0) return $baseUrl . '/public/' . $clean;
    return $baseUrl . '/' . $clean;
};

$siteLogo    = $formatBrandingUrl($siteLogoRaw, $baseUrl);
$siteFavicon = $formatBrandingUrl($siteFaviconRaw, $baseUrl);

$supportMail = \App\Models\Setting::get('support_email', \App\Models\Setting::get('contact_email', 'support@clickcodex.com'));
$supportPh   = \App\Models\Setting::get('support_phone', \App\Models\Setting::get('contact_phone', '+91 98765 43210'));
?>
<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($siteTitle) ?></title>
<?php if (!empty($siteFavicon)): ?>
  <link rel="shortcut icon" href="<?= htmlspecialchars($siteFavicon) ?>" type="image/x-icon">
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script>
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
            dark: '#0F172A'
          }
        },
        fontFamily: {
          heading: ['Poppins', 'sans-serif'],
          body: ['Inter', 'sans-serif']
        }
      }
    }
  }
</script>
<style>
  @keyframes spinSlow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
  @keyframes pulseGlow { 0%,100% { opacity: 0.4; transform: scale(1); } 50% { opacity: 0.8; transform: scale(1.08); } }
  @keyframes float { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
  .animate-spin-slow { animation: spinSlow 18s linear infinite; }
  .animate-pulse-glow { animation: pulseGlow 4s ease-in-out infinite; }
  .animate-float { animation: float 5s ease-in-out infinite; }
</style>
</head>
<body class="h-full bg-slate-950 text-slate-100 font-body flex items-center justify-center p-4 relative overflow-hidden">

  <!-- Background Ambient Glow Orbs -->
  <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-cc-blue/20 rounded-full blur-3xl animate-pulse-glow pointer-events-none"></div>
  <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-cc-purple/20 rounded-full blur-3xl animate-pulse-glow pointer-events-none" style="animation-delay:2s"></div>
  <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-cc-orange/15 rounded-full blur-3xl animate-pulse-glow pointer-events-none" style="animation-delay:1s"></div>

  <!-- Main Card Container -->
  <div class="relative z-10 max-w-2xl w-full bg-slate-900/80 backdrop-blur-xl border border-white/10 rounded-3xl p-6 sm:p-10 shadow-2xl text-center space-y-6 animate-float">

    <!-- Header Logo / Icon -->
    <div class="flex flex-col items-center justify-center">
      <?php if (!empty($siteLogo)): ?>
        <img src="<?= htmlspecialchars($siteLogo) ?>" alt="Logo" class="h-12 w-auto mb-4 object-contain">
      <?php else: ?>
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-cc-blue to-cc-purple flex items-center justify-center shadow-lg shadow-cc-blue/30 mb-4">
          <span class="material-icons text-white text-3xl animate-spin-slow">build</span>
        </div>
      <?php endif; ?>

      <span class="bg-cc-orange/20 text-cc-orange border border-cc-orange/30 text-xs font-bold px-3.5 py-1 rounded-full uppercase tracking-wider inline-flex items-center gap-1.5 shadow-sm">
        <span class="w-2 h-2 rounded-full bg-cc-orange animate-ping"></span> Under Scheduled Maintenance
      </span>
    </div>

    <!-- Heading & Message -->
    <div class="space-y-2">
      <h1 class="font-heading font-extrabold text-2xl sm:text-4xl text-white tracking-tight leading-tight">
        We'll Be Back Shortly!
      </h1>
      <p class="text-slate-300 text-sm sm:text-base leading-relaxed max-w-lg mx-auto font-normal">
        Our online marketplace is currently undergoing system upgrades to enhance performance, security, and your shopping experience.
      </p>
    </div>

    <!-- Countdown Timer Component -->
    <div class="bg-slate-950/60 border border-white/10 rounded-2xl p-4 sm:p-5 max-w-md mx-auto">
      <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest mb-3">Estimated System Readiness</p>
      <div class="grid grid-cols-4 gap-2 text-center">
        <div class="bg-slate-900/90 rounded-xl p-2 sm:p-3 border border-white/5">
          <span id="hours" class="font-heading font-extrabold text-xl sm:text-2xl text-cc-blue">01</span>
          <span class="block text-[10px] text-slate-400 uppercase mt-0.5">Hours</span>
        </div>
        <div class="bg-slate-900/90 rounded-xl p-2 sm:p-3 border border-white/5">
          <span id="minutes" class="font-heading font-extrabold text-xl sm:text-2xl text-cc-purple">45</span>
          <span class="block text-[10px] text-slate-400 uppercase mt-0.5">Minutes</span>
        </div>
        <div class="bg-slate-900/90 rounded-xl p-2 sm:p-3 border border-white/5">
          <span id="seconds" class="font-heading font-extrabold text-xl sm:text-2xl text-cc-pink">30</span>
          <span class="block text-[10px] text-slate-400 uppercase mt-0.5">Seconds</span>
        </div>
        <div class="bg-slate-900/90 rounded-xl p-2 sm:p-3 border border-white/5">
          <span class="font-heading font-extrabold text-xl sm:text-2xl text-cc-yellow">99%</span>
          <span class="block text-[10px] text-slate-400 uppercase mt-0.5">Status</span>
        </div>
      </div>
    </div>

    <!-- Contact & Admin Access -->
    <div class="pt-2 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-400">
      <div class="flex items-center gap-4 flex-wrap justify-center">
        <?php if (!empty($supportMail)): ?>
          <a href="mailto:<?= htmlspecialchars($supportMail) ?>" class="flex items-center gap-1.5 hover:text-white transition">
            <span class="material-icons text-sm text-cc-blue">email</span> <?= htmlspecialchars($supportMail) ?>
          </a>
        <?php endif; ?>
        <?php if (!empty($supportPh)): ?>
          <a href="tel:<?= htmlspecialchars($supportPh) ?>" class="flex items-center gap-1.5 hover:text-white transition">
            <span class="material-icons text-sm text-cc-purple">phone</span> <?= htmlspecialchars($supportPh) ?>
          </a>
        <?php endif; ?>
      </div>

      <!-- Admin Login Link -->
      <a href="<?= $baseUrl ?>/admin/login" class="bg-white/10 hover:bg-white/20 text-white font-bold px-4 py-2 rounded-xl transition flex items-center gap-1.5 border border-white/10 cursor-pointer">
        <span class="material-icons text-sm">admin_panel_settings</span> Admin Login &rarr;
      </a>
    </div>

  </div>

  <script>
    // Live countdown timer script
    let totalSeconds = 6330; // ~1h 45m 30s
    function updateTimer() {
      if (totalSeconds <= 0) return;
      totalSeconds--;
      const h = Math.floor(totalSeconds / 3600);
      const m = Math.floor((totalSeconds % 3600) / 60);
      const s = totalSeconds % 60;
      
      const hEl = document.getElementById('hours');
      const mEl = document.getElementById('minutes');
      const sEl = document.getElementById('seconds');

      if (hEl) hEl.textContent = String(h).padStart(2, '0');
      if (mEl) mEl.textContent = String(m).padStart(2, '0');
      if (sEl) sEl.textContent = String(s).padStart(2, '0');
    }
    setInterval(updateTimer, 1000);
  </script>

</body>
</html>
