<?php
$baseUrl     = defined('BASE_URL') ? BASE_URL : '';
$rawLogo     = \App\Models\Setting::get('logo_url') ?: \App\Models\Setting::get('site_logo', '');
$rawFavicon  = \App\Models\Setting::get('favicon_url') ?: \App\Models\Setting::get('site_favicon', '');

$formatBrandingUrl = function(?string $url, string $baseUrl): string {
    if (empty($url)) return '';
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) return $url;
    $clean = ltrim($url, '/');
    if (strpos($clean, 'public/uploads/') === 0) return $baseUrl . '/' . $clean;
    if (strpos($clean, 'uploads/') === 0) return $baseUrl . '/public/' . $clean;
    return $baseUrl . '/' . $clean;
};

$siteLogo    = $formatBrandingUrl($rawLogo, $baseUrl);
$siteFavicon = $formatBrandingUrl($rawFavicon, $baseUrl);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login — ClickCodex</title>
<?php if (!empty($siteFavicon)): ?>
  <link rel="shortcut icon" href="<?= htmlspecialchars($siteFavicon) ?>" type="image/x-icon">
<?php endif; ?>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
  body { font-family: 'Inter', sans-serif; }
  h1, h2, h3, h4, .font-heading { font-family: 'Poppins', sans-serif; }
  input:focus { outline: none; }
  ::selection { background: #FF5100; color: #fff; }

  /* Animated gradient background */
  .bg-mesh {
    background: #0F172A;
    position: relative;
    overflow: hidden;
  }
  .bg-mesh::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: 
      radial-gradient(ellipse 600px 600px at 20% 30%, rgba(45, 130, 255, 0.15) 0%, transparent 70%),
      radial-gradient(ellipse 500px 500px at 80% 20%, rgba(140, 48, 245, 0.12) 0%, transparent 70%),
      radial-gradient(ellipse 400px 400px at 60% 80%, rgba(255, 81, 0, 0.10) 0%, transparent 70%),
      radial-gradient(ellipse 350px 350px at 10% 90%, rgba(255, 0, 107, 0.08) 0%, transparent 70%);
    animation: mesh-drift 20s ease-in-out infinite alternate;
  }
  @keyframes mesh-drift {
    0% { transform: translate(0, 0) rotate(0deg); }
    33% { transform: translate(30px, -20px) rotate(1deg); }
    66% { transform: translate(-20px, 15px) rotate(-1deg); }
    100% { transform: translate(10px, -10px) rotate(0.5deg); }
  }

  /* Floating orbs */
  .orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(60px);
    opacity: 0.3;
    animation: orb-float linear infinite;
  }
  .orb-1 { width: 300px; height: 300px; background: #2D82FF; top: 10%; left: 5%; animation-duration: 18s; }
  .orb-2 { width: 250px; height: 250px; background: #8C30F5; top: 60%; right: 10%; animation-duration: 22s; animation-delay: -5s; }
  .orb-3 { width: 200px; height: 200px; background: #FF5100; bottom: 15%; left: 40%; animation-duration: 15s; animation-delay: -10s; }
  @keyframes orb-float {
    0%, 100% { transform: translateY(0) translateX(0); }
    25% { transform: translateY(-30px) translateX(15px); }
    50% { transform: translateY(10px) translateX(-20px); }
    75% { transform: translateY(-15px) translateX(10px); }
  }

  /* Grid pattern overlay */
  .grid-pattern {
    position: absolute;
    inset: 0;
    background-image: 
      linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
    background-size: 60px 60px;
    pointer-events: none;
  }

  /* Glass card */
  .glass-card {
    background: rgba(255, 255, 255, 0.03);
    backdrop-filter: blur(40px);
    -webkit-backdrop-filter: blur(40px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 
      0 25px 50px -12px rgba(0, 0, 0, 0.5),
      inset 0 1px 0 rgba(255, 255, 255, 0.05);
  }

  /* Input styling */
  .form-input {
    background: rgba(255, 255, 255, 0.05);
    border: 1.5px solid rgba(255, 255, 255, 0.1);
    color: white;
    transition: all 0.25s cubic-bezier(.4,0,.2,1);
    font-size: 0.9375rem;
  }
  .form-input::placeholder {
    color: rgba(255, 255, 255, 0.3);
  }
  .form-input:hover {
    border-color: rgba(255, 255, 255, 0.2);
    background: rgba(255, 255, 255, 0.07);
  }
  .form-input:focus {
    border-color: #2D82FF;
    background: rgba(45, 130, 255, 0.05);
    box-shadow: 0 0 0 3px rgba(45, 130, 255, 0.15);
  }
  .form-input.error {
    border-color: #FF006B;
    box-shadow: 0 0 0 3px rgba(255, 0, 107, 0.15);
  }
  .form-input.success {
    border-color: #22C55E;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
  }

  /* Submit button */
  .btn-submit {
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(.4,0,.2,1);
  }
  .btn-submit::before {
    content: '';
    position: absolute;
    top: 0; left: -100%;
    width: 100%; height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
    transition: left 0.5s ease;
  }
  .btn-submit:hover::before {
    left: 100%;
  }
  .btn-submit:active {
    transform: scale(0.98);
  }
  .btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
    transform: none;
  }
  .btn-submit:disabled::before {
    display: none;
  }

  /* Spinner */
  .spinner {
    width: 20px;
    height: 20px;
    border: 2.5px solid rgba(255,255,255,0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.7s linear infinite;
    display: none;
  }
  .btn-submit.loading .spinner { display: block; }
  .btn-submit.loading .btn-text { display: none; }
  @keyframes spin { to { transform: rotate(360deg); } }

  /* Strength bar */
  .strength-bar {
    height: 3px;
    border-radius: 2px;
    transition: all 0.3s ease;
    background: rgba(255,255,255,0.1);
  }
  .strength-bar .fill {
    height: 100%;
    border-radius: 2px;
    transition: width 0.3s ease, background 0.3s ease;
    width: 0%;
  }

  /* Feature card hover */
  .feature-item {
    transition: all 0.25s cubic-bezier(.4,0,.2,1);
  }
  .feature-item:hover {
    background: rgba(255,255,255,0.06);
    transform: translateX(4px);
  }

  /* Toast */
  .toast {
    animation: toast-in 0.4s cubic-bezier(.4,0,.2,1) forwards;
  }
  .toast.removing {
    animation: toast-out 0.3s cubic-bezier(.4,0,.2,1) forwards;
  }
  @keyframes toast-in {
    from { transform: translateX(100%); opacity: 0; }
    to { transform: translateX(0); opacity: 1; }
  }
  @keyframes toast-out {
    from { transform: translateX(0); opacity: 1; }
    to { transform: translateX(100%); opacity: 0; }
  }

  /* Logo pulse */
  .logo-ring {
    animation: logo-pulse 3s ease-in-out infinite;
  }
  @keyframes logo-pulse {
    0%, 100% { box-shadow: 0 0 0 0 rgba(45, 130, 255, 0.3); }
    50% { box-shadow: 0 0 0 12px rgba(45, 130, 255, 0); }
  }

  /* Typing cursor */
  .cursor-blink {
    animation: blink 1s step-end infinite;
  }
  @keyframes blink {
    50% { opacity: 0; }
  }

  /* Particle canvas */
  #particle-canvas {
    position: absolute;
    inset: 0;
    pointer-events: none;
  }

  /* Error message animation */
  .error-msg {
    animation: shake-in 0.35s ease;
  }
  @keyframes shake-in {
    0%, 100% { transform: translateX(0); }
    20% { transform: translateX(-6px); }
    40% { transform: translateX(6px); }
    60% { transform: translateX(-4px); }
    80% { transform: translateX(4px); }
  }

  /* Checkbox */
  .custom-check {
    width: 18px;
    height: 18px;
    border: 1.5px solid rgba(255,255,255,0.2);
    border-radius: 5px;
    background: rgba(255,255,255,0.05);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    flex-shrink: 0;
  }
  .custom-check.checked {
    background: #2D82FF;
    border-color: #2D82FF;
  }
  .custom-check .check-icon {
    color: white;
    font-size: 14px;
    opacity: 0;
    transform: scale(0.5);
    transition: all 0.2s ease;
  }
  .custom-check.checked .check-icon {
    opacity: 1;
    transform: scale(1);
  }

  /* Security badge glow */
  .security-glow {
    animation: sec-glow 2.5s ease-in-out infinite alternate;
  }
  @keyframes sec-glow {
    from { text-shadow: 0 0 4px rgba(34,197,94,0.3); }
    to { text-shadow: 0 0 12px rgba(34,197,94,0.6); }
  }
</style>
</head>
<body class="bg-[#0F172A] min-h-screen flex items-center justify-center p-4 sm:p-6 antialiased relative">

<!-- ================= FIXED BACKGROUND CONTAINER ================= -->
<div class="fixed inset-0 z-0 pointer-events-none overflow-hidden">
  <div class="bg-mesh absolute inset-0"></div>
  <canvas id="particle-canvas" class="absolute inset-0"></canvas>
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>
  <div class="grid-pattern"></div>
</div>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-5 right-5 z-[200] flex flex-col gap-3 pointer-events-none"></div>

<!-- ================= MAIN LAYOUT ================= -->
<div class="relative z-10 w-full max-w-[1100px] grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-0 items-center">

  <!-- ===== LEFT PANEL — Branding & Features ===== -->
  <div class="hidden lg:flex flex-col justify-center px-10 xl:px-14 py-12">
    
    <!-- Logo -->
    <div class="flex items-center gap-3 mb-10">
      <?php if (!empty($siteLogo)): ?>
        <img src="<?= htmlspecialchars($siteLogo) ?>" alt="Logo" class="h-10 w-auto object-contain">
      <?php else: ?>
        <div class="w-12 h-12 rounded-2xl bg-cc-blue/20 border border-cc-blue/30 flex items-center justify-center logo-ring">
          <svg width="30" height="30" viewBox="0 0 100 100">
            <path d="M78 24 A38 38 0 1 0 78 76" fill="none" stroke="white" stroke-width="14" stroke-linecap="round"/>
            <path d="M55 38 L44 50 L55 62" fill="none" stroke="white" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" opacity="0.85"/>
          </svg>
        </div>
        <span class="font-heading font-extrabold text-2xl text-white">Click<span class="text-cc-yellow">Codex</span></span>
      <?php endif; ?>
    </div>

    <!-- Heading -->
    <h1 class="font-heading text-3xl xl:text-4xl font-extrabold text-white leading-tight mb-3">
      Admin<br>Control Center
    </h1>
    <p class="text-white/40 text-sm leading-relaxed mb-10 max-w-sm">
      Manage products, track orders, analyze performance, and control every aspect of your marketplace from one powerful dashboard.
    </p>


    <!-- Feature List -->
    <div class="space-y-1">
      <div class="feature-item flex items-center gap-4 px-4 py-3 rounded-xl cursor-default">
        <div class="w-10 h-10 rounded-xl bg-cc-blue/15 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-blue text-[20px]">inventory_2</span>
        </div>
        <div>
          <p class="text-white text-sm font-semibold">Product Management</p>
          <p class="text-white/35 text-xs mt-0.5">Add, edit, categorize & bulk upload</p>
        </div>
      </div>
      <div class="feature-item flex items-center gap-4 px-4 py-3 rounded-xl cursor-default">
        <div class="w-10 h-10 rounded-xl bg-cc-orange/15 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-orange text-[20px]">local_shipping</span>
        </div>
        <div>
          <p class="text-white text-sm font-semibold">Order Fulfillment</p>
          <p class="text-white/35 text-xs mt-0.5">Track, process & manage shipments</p>
        </div>
      </div>
      <div class="feature-item flex items-center gap-4 px-4 py-3 rounded-xl cursor-default">
        <div class="w-10 h-10 rounded-xl bg-cc-purple/15 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-purple text-[20px]">bar_chart</span>
        </div>
        <div>
          <p class="text-white text-sm font-semibold">Analytics & Reports</p>
          <p class="text-white/35 text-xs mt-0.5">Revenue, traffic & conversion insights</p>
        </div>
      </div>
      <div class="feature-item flex items-center gap-4 px-4 py-3 rounded-xl cursor-default">
        <div class="w-10 h-10 rounded-xl bg-cc-pink/15 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-pink text-[20px]">group</span>
        </div>
        <div>
          <p class="text-white text-sm font-semibold">User Management</p>
          <p class="text-white/35 text-xs mt-0.5">Customers, staff & role permissions</p>
        </div>
      </div>
    </div>

    <!-- Stats row -->
    <div class="flex gap-6 mt-10 pt-8 border-t border-white/5">
      <div>
        <p class="font-heading font-extrabold text-2xl text-white" id="stat-products">0</p>
        <p class="text-white/30 text-xs mt-0.5">Products Listed</p>
      </div>
      <div>
        <p class="font-heading font-extrabold text-2xl text-white" id="stat-orders">0</p>
        <p class="text-white/30 text-xs mt-0.5">Orders Placed</p>
      </div>
      <div>
        <p class="font-heading font-extrabold text-2xl text-white" id="stat-revenue">0</p>
        <p class="text-white/30 text-xs mt-0.5">Revenue Active</p>
      </div>
    </div>
  </div>

  <!-- ===== RIGHT PANEL — Login Form ===== -->
  <div class="w-full max-w-md mx-auto lg:mx-0 lg:ml-auto lg:mr-10 xl:mr-14">
    <div class="glass-card rounded-3xl p-7 sm:p-9">

      <!-- Mobile-only logo -->
      <div class="flex lg:hidden items-center justify-center gap-2.5 mb-7">
        <div class="w-10 h-10 rounded-xl bg-cc-blue/20 border border-cc-blue/30 flex items-center justify-center">
          <svg width="24" height="24" viewBox="0 0 100 100">
            <path d="M78 24 A38 38 0 1 0 78 76" fill="none" stroke="white" stroke-width="14" stroke-linecap="round"/>
            <path d="M55 38 L44 50 L55 62" fill="none" stroke="white" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" opacity="0.85"/>
          </svg>
        </div>
        <span class="font-heading font-extrabold text-xl text-white">Click<span class="text-cc-yellow">Codex</span></span>
        <span class="text-[10px] bg-cc-orange text-white font-bold px-2 py-0.5 rounded-md uppercase tracking-wider ml-1">Admin</span>
      </div>

      <!-- Header -->
      <div class="text-center mb-7">
        <div class="inline-flex items-center gap-1.5 bg-white/5 border border-white/10 rounded-full px-3.5 py-1.5 text-xs text-white/50 font-medium mb-4">
          <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
          Admin Portal Authentication
        </div>
        <h2 class="font-heading text-2xl font-bold text-white">Welcome Back</h2>
        <p class="text-white/40 text-sm mt-1.5" id="typed-text"></p>
      </div>

      <!-- Login Form -->
      <form id="login-form" onsubmit="handleLogin(event)" novalidate autocomplete="off">

        <!-- Role Selector -->
        <div class="mb-5">
          <label class="block text-xs font-semibold text-white/50 mb-2 uppercase tracking-wider">Access Scope</label>
          <div class="grid grid-cols-2 gap-2">
            <button type="button" onclick="selectRole('admin', this)" class="role-btn active-role flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold transition border border-cc-blue bg-cc-blue/15 text-white">
              <span class="material-icons text-[18px]">admin_panel_settings</span>
              Super Admin / Admin
            </button>
            <button type="button" onclick="selectRole('staff', this)" class="role-btn flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold transition border border-white/10 bg-white/5 text-white/50 hover:bg-white/10 hover:text-white/70">
              <span class="material-icons text-[18px]">manage_accounts</span>
              Staff / Ops
            </button>
          </div>
          <input type="hidden" id="selected-role" value="admin">
        </div>

        <!-- Email -->
        <div class="mb-4">
          <label for="admin-email" class="block text-xs font-semibold text-white/50 mb-2 uppercase tracking-wider">Email Address</label>
          <div class="relative">
            <span class="material-icons absolute left-3.5 top-1/2 -translate-y-1/2 text-white/25 text-[20px]">mail</span>
            <input 
              type="email" 
              id="admin-email" 
              placeholder="admin@clickcodex.in"
              class="form-input w-full pl-11 pr-10 py-3.5 rounded-xl"
              oninput="validateField('email')"
              onfocus="clearFieldError('email')"
            />
            <span id="email-status-icon" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[20px] hidden">
              <span class="material-icons"></span>
            </span>
          </div>
          <p id="email-error" class="text-cc-pink text-xs mt-1.5 hidden error-msg flex items-center gap-1">
            <span class="material-icons text-[14px]">error</span>
            <span></span>
          </p>
        </div>

        <!-- Password -->
        <div class="mb-4">
          <label for="admin-password" class="block text-xs font-semibold text-white/50 mb-2 uppercase tracking-wider">Password</label>
          <div class="relative">
            <span class="material-icons absolute left-3.5 top-1/2 -translate-y-1/2 text-white/25 text-[20px]">lock</span>
            <input 
              type="password" 
              id="admin-password" 
              placeholder="Enter your password"
              class="form-input w-full pl-11 pr-20 py-3.5 rounded-xl"
              oninput="validateField('password')"
              onfocus="clearFieldError('password')"
            />
            <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition p-0.5 rounded-lg" aria-label="Toggle password visibility">
              <span id="pw-toggle-icon" class="material-icons text-[20px]">visibility_off</span>
            </button>
          </div>
          <div class="mt-2" id="pw-strength-wrapper" style="display:none;">
            <div class="strength-bar">
              <div class="fill" id="pw-strength-fill"></div>
            </div>
            <p id="pw-strength-text" class="text-[11px] mt-1 text-white/30"></p>
          </div>
          <p id="password-error" class="text-cc-pink text-xs mt-1.5 hidden error-msg flex items-center gap-1">
            <span class="material-icons text-[14px]">error</span>
            <span></span>
          </p>
        </div>

        <!-- Remember -->
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center gap-2.5 cursor-pointer select-none" onclick="toggleRemember()">
            <div class="custom-check" id="remember-check">
              <span class="material-icons check-icon">check</span>
            </div>
            <span class="text-sm text-white/50">Remember me</span>
          </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" id="submit-btn" class="btn-submit w-full bg-cc-blue hover:bg-blue-600 text-white font-bold py-4 rounded-xl flex items-center justify-center gap-2 text-base shadow-lg shadow-cc-blue/20">
          <span class="btn-text flex items-center gap-2">
            <span class="material-icons text-[20px]">login</span>
            Sign In to Admin Dashboard
          </span>
          <div class="spinner"></div>
        </button>
      </form>

      <!-- Security Footer -->
      <div class="flex items-center justify-center gap-4 mt-7 pt-6 border-t border-white/5">
        <div class="flex items-center gap-1.5 text-green-400/70 security-glow">
          <span class="material-icons text-[14px]">verified_user</span>
          <span class="text-[11px] font-medium">256-bit SSL</span>
        </div>
        <div class="w-px h-3 bg-white/10"></div>
        <div class="flex items-center gap-1.5 text-white/30">
          <span class="material-icons text-[14px]">fingerprint</span>
          <span class="text-[11px] font-medium">RBAC Protected</span>
        </div>
      </div>
    </div>

    <!-- Back to store link -->
    <p class="text-center mt-5">
      <a href="<?= $baseUrl ?>/" class="text-white/30 hover:text-white/60 text-xs transition flex items-center justify-center gap-1.5">
        <span class="material-icons text-[16px]">arrow_back</span>
        Back to ClickCodex Store
      </a>
    </p>
  </div>
</div>

<script>
  const BASE_URL = '<?= $baseUrl ?>';
  let selectedRole = 'admin';
  let isRemembered = false;

  const typedEl = document.getElementById('typed-text');
  const phrases = [
    'Enter your admin credentials to continue',
    'Restricted to Super Admin, Admin & Staff',
    'ClickCodex Admin Command Center'
  ];
  let phraseIdx = 0, charIdx = 0, isDeleting = false;

  function typeLoop() {
    const current = phrases[phraseIdx];
    if (!isDeleting) {
      typedEl.textContent = current.substring(0, charIdx + 1);
      charIdx++;
      if (charIdx === current.length) {
        setTimeout(() => { isDeleting = true; typeLoop(); }, 2200);
        return;
      }
    } else {
      typedEl.textContent = current.substring(0, charIdx - 1);
      charIdx--;
      if (charIdx === 0) {
        isDeleting = false;
        phraseIdx = (phraseIdx + 1) % phrases.length;
      }
    }
    typedEl.innerHTML += '<span class="cursor-blink">|</span>';
    setTimeout(typeLoop, isDeleting ? 35 : 60);
  }
  typeLoop();

  function animateCounter(id, target, suffix = '') {
    const el = document.getElementById(id);
    if (!el) return;
    let current = 0;
    const step = Math.max(1, Math.floor(target / 50));
    const interval = setInterval(() => {
      current += step;
      if (current >= target) {
        current = target;
        clearInterval(interval);
      }
      el.textContent = current.toLocaleString('en-IN') + suffix;
    }, 30);
  }
  setTimeout(() => animateCounter('stat-products', 12847, '+'), 300);
  setTimeout(() => animateCounter('stat-orders', 3256), 500);
  setTimeout(() => animateCounter('stat-revenue', 478, 'k'), 700);

  const canvas = document.getElementById('particle-canvas');
  const ctx = canvas.getContext('2d');
  let particles = [];
  let mouseX = -1000, mouseY = -1000;

  function resizeCanvas() {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
  }
  resizeCanvas();
  window.addEventListener('resize', resizeCanvas);
  document.addEventListener('mousemove', (e) => { mouseX = e.clientX; mouseY = e.clientY; });

  class Particle {
    constructor() { this.reset(); }
    reset() {
      this.x = Math.random() * canvas.width;
      this.y = Math.random() * canvas.height;
      this.size = Math.random() * 1.5 + 0.5;
      this.speedX = (Math.random() - 0.5) * 0.3;
      this.speedY = (Math.random() - 0.5) * 0.3;
      this.opacity = Math.random() * 0.4 + 0.1;
    }
    update() {
      this.x += this.speedX;
      this.y += this.speedY;
      const dx = this.x - mouseX;
      const dy = this.y - mouseY;
      const dist = Math.sqrt(dx * dx + dy * dy);
      if (dist < 120) {
        const force = (120 - dist) / 120 * 0.8;
        this.x += (dx / dist) * force;
        this.y += (dy / dist) * force;
      }
      if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
      if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
    }
    draw() {
      ctx.beginPath();
      ctx.arc(this.x, this.y, Math.max(0.1, this.size), 0, Math.PI * 2);
      ctx.fillStyle = `rgba(45, 130, 255, ${this.opacity})`;
      ctx.fill();
    }
  }

  const particleCount = Math.min(80, Math.floor((canvas.width * canvas.height) / 15000));
  for (let i = 0; i < particleCount; i++) { particles.push(new Particle()); }

  function drawConnections() {
    for (let i = 0; i < particles.length; i++) {
      for (let j = i + 1; j < particles.length; j++) {
        const dx = particles[i].x - particles[j].x;
        const dy = particles[i].y - particles[j].y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < 150) {
          ctx.beginPath();
          ctx.moveTo(particles[i].x, particles[i].y);
          ctx.lineTo(particles[j].x, particles[j].y);
          ctx.strokeStyle = `rgba(45, 130, 255, ${0.06 * (1 - dist / 150)})`;
          ctx.lineWidth = 0.5;
          ctx.stroke();
        }
      }
    }
  }

  function animateParticles() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    particles.forEach(p => { p.update(); p.draw(); });
    drawConnections();
    requestAnimationFrame(animateParticles);
  }

  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    animateParticles();
  }

  function selectRole(role, btn) {
    selectedRole = role;
    document.getElementById('selected-role').value = role;
    document.querySelectorAll('.role-btn').forEach(b => {
      b.classList.remove('active-role');
      b.classList.remove('border-cc-blue', 'bg-cc-blue/15', 'text-white');
      b.classList.add('border-white/10', 'bg-white/5', 'text-white/50');
    });
    btn.classList.add('active-role', 'border-cc-blue', 'bg-cc-blue/15', 'text-white');
    btn.classList.remove('border-white/10', 'bg-white/5', 'text-white/50');
  }

  function togglePassword() {
    const input = document.getElementById('admin-password');
    const icon = document.getElementById('pw-toggle-icon');
    if (input.type === 'password') {
      input.type = 'text';
      icon.textContent = 'visibility';
    } else {
      input.type = 'password';
      icon.textContent = 'visibility_off';
    }
  }

  function toggleRemember() {
    isRemembered = !isRemembered;
    document.getElementById('remember-check').classList.toggle('checked', isRemembered);
  }

  function validateField(field) {
    if (field === 'email') {
      const input = document.getElementById('admin-email');
      const val = input.value.trim();
      const icon = document.getElementById('email-status-icon');
      
      if (val === '') {
        input.classList.remove('success', 'error');
        icon.classList.add('hidden');
        return false;
      }
      
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (emailRegex.test(val)) {
        input.classList.remove('error');
        input.classList.add('success');
        icon.classList.remove('hidden');
        icon.querySelector('.material-icons').textContent = 'check_circle';
        icon.querySelector('.material-icons').style.color = '#22C55E';
        return true;
      } else {
        input.classList.remove('success');
        input.classList.add('error');
        icon.classList.remove('hidden');
        icon.querySelector('.material-icons').textContent = 'error';
        icon.querySelector('.material-icons').style.color = '#FF006B';
        return false;
      }
    }
    
    if (field === 'password') {
      const input = document.getElementById('admin-password');
      const val = input.value;
      const wrapper = document.getElementById('pw-strength-wrapper');
      const fill = document.getElementById('pw-strength-fill');
      const text = document.getElementById('pw-strength-text');
      
      if (val === '') {
        wrapper.style.display = 'none';
        input.classList.remove('success', 'error');
        return false;
      }
      
      wrapper.style.display = 'block';
      let score = 0;
      if (val.length >= 6) score++;
      if (val.length >= 10) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;
      
      const levels = [
        { width: '10%', color: '#FF006B', label: 'Very Weak' },
        { width: '25%', color: '#FF5100', label: 'Weak' },
        { width: '50%', color: '#FFB800', label: 'Fair' },
        { width: '75%', color: '#2D82FF', label: 'Strong' },
        { width: '100%', color: '#22C55E', label: 'Very Strong' }
      ];
      
      const level = levels[Math.min(score, 4)];
      fill.style.width = level.width;
      fill.style.background = level.color;
      text.textContent = level.label;
      text.style.color = level.color;
      
      input.classList.remove('success', 'error');
      if (score >= 3) {
        input.classList.add('success');
        return true;
      } else if (score > 0) {
        input.classList.add('error');
        return false;
      }
      return false;
    }
    return false;
  }

  function clearFieldError(field) {
    const errorEl = document.getElementById(field + '-error');
    if (errorEl) { errorEl.classList.add('hidden'); }
  }

  function showFieldError(field, message) {
    const input = document.getElementById('admin-' + field);
    const errorEl = document.getElementById(field + '-error');
    input.classList.add('error');
    input.classList.remove('success');
    errorEl.classList.remove('hidden');
    errorEl.querySelector('span:last-child').textContent = message;
    errorEl.style.animation = 'none';
    errorEl.offsetHeight;
    errorEl.style.animation = '';
  }

  async function handleLogin(e) {
    e.preventDefault();
    
    const email = document.getElementById('admin-email').value.trim();
    const password = document.getElementById('admin-password').value;
    
    let hasError = false;
    
    if (!email) {
      showFieldError('email', 'Email address is required');
      hasError = true;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      showFieldError('email', 'Please enter a valid email address');
      hasError = true;
    }
    
    if (!password) {
      showFieldError('password', 'Password is required');
      hasError = true;
    }
    
    if (hasError) return;
    
    const btn = document.getElementById('submit-btn');
    btn.classList.add('loading');
    btn.disabled = true;

    try {
      const response = await fetch(`${BASE_URL}/admin/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json'
        },
        body: JSON.stringify({
          email: email,
          password: password,
          remember: isRemembered,
          role: selectedRole
        })
      });

      const data = await response.json();

      btn.classList.remove('loading');
      btn.disabled = false;

      if (data.success) {
        showToast('success', data.message);
        setTimeout(() => {
          window.location.href = data.redirect || `${BASE_URL}/admin/dashboard`;
        }, 1000);
      } else {
        showToast('error', data.message);
        showFieldError('password', data.message);
        const form = document.getElementById('login-form');
        form.style.animation = 'none';
        form.offsetHeight;
        form.style.animation = 'shake-in 0.35s ease';
      }
    } catch (err) {
      btn.classList.remove('loading');
      btn.disabled = false;
      showToast('error', 'Network error. Please try again.');
    }
  }

  function showToast(type, message) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    
    const icons = {
      success: 'check_circle',
      error: 'error',
      info: 'info',
      warning: 'warning'
    };
    const colors = {
      success: 'border-green-500/30 bg-green-500/10',
      error: 'border-cc-pink/30 bg-cc-pink/10',
      info: 'border-cc-blue/30 bg-cc-blue/10',
      warning: 'border-cc-yellow/30 bg-cc-yellow/10'
    };
    const iconColors = {
      success: 'text-green-400',
      error: 'text-cc-pink',
      info: 'text-cc-blue',
      warning: 'text-cc-yellow'
    };
    
    toast.className = `toast pointer-events-auto flex items-start gap-3 px-4 py-3.5 rounded-xl border backdrop-blur-xl shadow-2xl max-w-sm ${colors[type]}`;
    toast.innerHTML = `
      <span class="material-icons ${iconColors[type]} text-[20px] mt-0.5 shrink-0">${icons[type]}</span>
      <p class="text-sm text-white/90 leading-relaxed">${message}</p>
      <button onclick="this.closest('.toast').classList.add('removing'); setTimeout(() => this.closest('.toast').remove(), 300)" class="text-white/30 hover:text-white/60 transition shrink-0 ml-2 mt-0.5">
        <span class="material-icons text-[16px]">close</span>
      </button>
    `;
    
    container.appendChild(toast);
    setTimeout(() => {
      if (toast.parentNode) {
        toast.classList.add('removing');
        setTimeout(() => toast.remove(), 300);
      }
    }, 5000);
  }
</script>
</body>
</html>
