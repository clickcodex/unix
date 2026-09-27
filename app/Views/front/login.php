<?php
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$siteTitle = $siteTitle ?? 'ClickCodex — Login, Register or Recover Account';
$siteLogo = $siteLogo ?? '';
$siteFavicon = $siteFavicon ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($siteTitle) ?></title>
<?php if (!empty($siteFavicon)): ?>
  <link rel="shortcut icon" href="<?= htmlspecialchars($siteFavicon) ?>" type="image/x-icon">
<?php endif; ?>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- Google Material Icons -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script>
  var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          cc: {
            orange: '#FF5100',
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
  body { font-family: 'Inter', sans-serif; background: #F1F5F9; }
  h1, h2, h3, h4, .font-heading { font-family: 'Poppins', sans-serif; }
  input:focus { outline: none; }
  ::selection { background: #FF5100; color: #fff; }
</style>
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col justify-between">

<!-- ================= TOAST NOTIFICATIONS ================= -->
<div id="toast-container" class="fixed top-5 right-5 z-[100] flex flex-col gap-2 pointer-events-none"></div>

<!-- ================= SIMULATED SMS / OTP NOTIFICATION GATEWAY ================= -->
<div id="sms-gateway" class="fixed top-4 left-1/2 -translate-x-1/2 z-[110] max-w-sm w-[90%] bg-slate-900 text-white p-4 rounded-2xl shadow-2xl border border-slate-700/50 flex items-start gap-3 transition-all duration-300 transform -translate-y-32 opacity-0 pointer-events-none">
  <span class="material-icons text-cc-orange">sms</span>
  <div class="flex-1">
    <p class="text-xs font-bold text-slate-400">SMS Gateway Simulator</p>
    <p id="sms-text" class="text-xs text-white mt-0.5">Your ClickCodex OTP is 482019. Valid for 10 minutes.</p>
  </div>
  <button onclick="dismissSmsSimulator()" class="text-slate-400 hover:text-white"><span class="material-icons text-sm">close</span></button>
</div>

<!-- ================= TOP LOGO BAR ================= -->
<header class="w-full bg-white border-b border-slate-100 py-4 px-6 shrink-0 shadow-sm">
  <div class="max-w-7xl mx-auto flex items-center justify-between">
    <!-- Logo -->
    <a href="<?= $baseUrl ?>/" class="flex items-center gap-2 group">
      <div class="w-9 h-9 rounded-xl bg-cc-blue flex items-center justify-center shadow-md">
        <svg width="22" height="22" viewBox="0 0 100 100">
          <path d="M78 24 A38 38 0 1 0 78 76" fill="none" stroke="white" stroke-width="14" stroke-linecap="round"/>
          <path d="M55 38 L44 50 L55 62" fill="none" stroke="white" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" opacity="0.85"/>
        </svg>
      </div>
      <span class="font-heading font-extrabold text-xl text-cc-dark leading-none">Click<span class="text-cc-orange">Codex</span></span>
    </a>
    <a href="<?= $baseUrl ?>/" class="text-xs font-semibold text-slate-500 hover:text-cc-blue transition flex items-center gap-1">
      <span class="material-icons text-sm">arrow_back</span> Back to Store
    </a>
  </div>
</header>

<!-- ================= MAIN CONTAINER ================= -->
<main class="flex-1 flex items-center justify-center px-4 py-8">
  <div class="bg-white rounded-3xl shadow-xl border border-slate-100 max-w-4xl w-full min-h-[580px] overflow-hidden flex flex-col md:flex-row">
    
    <!-- Left Panel: Brand Features Banner -->
    <div class="hidden md:flex md:w-1/2 bg-cc-blue p-10 flex-col justify-between text-white relative">
      <div class="absolute inset-0 opacity-[0.03]" style="background-image:repeating-linear-gradient(90deg,white 0,white 1px,transparent 1px,transparent 40px);"></div>
      
      <div class="relative z-10">
        <span class="bg-white/10 text-xs font-bold px-3 py-1.5 rounded-full uppercase tracking-wider">Customer Portal</span>
        <h2 class="font-heading text-3xl font-extrabold mt-6 leading-tight text-white">India's Smartest Shopping Gateway</h2>
        <p class="text-white/80 text-sm mt-3 leading-relaxed">Join millions of Indian shoppers enjoying super-fast delivery, secure digital payments, and exclusive daily price cuts.</p>
      </div>

      <div class="space-y-4 relative z-10">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
            <span class="material-icons text-white text-lg">local_shipping</span>
          </div>
          <span class="text-xs font-semibold">Free Delivery above ₹999 anywhere in India</span>
        </div>
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
            <span class="material-icons text-white text-lg">security</span>
          </div>
          <span class="text-xs font-semibold">Safe checkout with UPI, NetBanking &amp; COD</span>
        </div>
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-white/10 flex items-center justify-center shrink-0">
            <span class="material-icons text-white text-lg">autorenew</span>
          </div>
          <span class="text-xs font-semibold">Easy 7-day return policy</span>
        </div>
      </div>

      <p class="text-[10px] text-white/50 relative z-10">&copy; <?= date('Y') ?> ClickCodex Retail Private Limited.</p>
    </div>

    <!-- Right Panel: Dynamic Form Container -->
    <div class="w-full md:w-1/2 p-6 sm:p-10 flex flex-col justify-between">
      
      <div>
        <!-- Tab Headers -->
        <div id="tab-headers-container" class="flex border-b border-slate-100 mb-6">
          <button onclick="switchTab('login')" id="tab-login" class="flex-1 pb-3 text-sm font-bold text-slate-800 border-b-2 border-cc-blue transition-all">Sign In</button>
          <button onclick="switchTab('register')" id="tab-register" class="flex-1 pb-3 text-sm font-medium text-slate-400 border-b-2 border-transparent transition-all">Create Account</button>
        </div>

        <!-- Alert Notice Box -->
        <div id="authAlert" class="hidden p-3.5 text-xs font-semibold mb-4 rounded-xl"></div>

        <!-- ================= LOGIN FORM CONTAINER ================= -->
        <div id="form-login" class="space-y-4">
          <div class="space-y-1">
            <h3 class="font-heading font-extrabold text-xl text-slate-800">Welcome Back</h3>
            <p class="text-xs text-slate-400">Access your account details, track items &amp; review orders.</p>
          </div>

          <!-- Login Method Selector -->
          <div class="flex gap-2 bg-slate-50 p-1 rounded-xl">
            <button onclick="switchLoginMethod('password')" id="btn-login-pass" class="flex-1 text-xs font-bold py-2 px-3 rounded-lg bg-white text-slate-800 shadow-sm transition">Password</button>
            <button onclick="switchLoginMethod('otp')" id="btn-login-otp" class="flex-1 text-xs font-medium py-2 px-3 rounded-lg text-slate-500 transition">Mobile OTP</button>
          </div>

          <!-- Password Login Form -->
          <form id="login-password-fields" onsubmit="handlePasswordLogin(event)" class="space-y-3">
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Mobile or Email ID</label>
              <input type="text" id="login-identifier" name="email" required placeholder="e.g. priya.patel@email.com" class="w-full border border-slate-200 px-4 py-3 rounded-xl text-sm focus:border-cc-blue focus:ring-1 focus:ring-cc-blue transition">
            </div>
            <div>
              <div class="flex justify-between items-center mb-1">
                <label class="block text-xs font-semibold text-slate-600">Password</label>
                <button type="button" onclick="switchTab('forgot')" class="text-xs text-cc-blue font-semibold hover:underline">Forgot?</button>
              </div>
              <div class="relative">
                <input type="password" id="login-password" name="password" required placeholder="••••••••" class="w-full border border-slate-200 pl-4 pr-10 py-3 rounded-xl text-sm focus:border-cc-blue focus:ring-1 focus:ring-cc-blue transition">
                <button type="button" onclick="togglePasswordVisibility('login-password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                  <span class="material-icons text-[18px]">visibility</span>
                </button>
              </div>
            </div>
            <button type="submit" id="btn-login-submit" class="w-full bg-cc-blue hover:bg-blue-600 text-white py-3.5 rounded-xl font-bold text-sm hover:brightness-105 active:scale-95 transition-all mt-2">Sign In with Password</button>
          </form>

          <!-- OTP Login Option -->
          <form id="login-otp-fields" onsubmit="handleOtpRequest(event)" class="space-y-3 hidden">
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">10-Digit Mobile Number</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-bold border-r border-slate-200 pr-2">+91</span>
                <input type="number" id="login-mobile-otp" placeholder="9876543210" class="w-full border border-slate-200 pl-16 pr-4 py-3 rounded-xl text-sm focus:border-cc-blue focus:ring-1 focus:ring-cc-blue transition">
              </div>
            </div>
            <button type="submit" class="w-full bg-cc-orange hover:bg-orange-600 text-white py-3.5 rounded-xl font-bold text-sm active:scale-95 transition-all mt-2">Send One-Time Password</button>
          </form>

          <!-- OTP Input Form Block -->
          <div id="otp-entry-section" class="space-y-3 pt-2 border-t border-slate-100 hidden">
            <div class="text-center">
              <p class="text-xs text-slate-500">We have sent a 6-digit OTP code to <span id="target-mobile-lbl" class="font-bold text-slate-700"></span></p>
            </div>
            <div class="flex gap-2 justify-center my-3" id="otp-input-group">
              <input type="text" maxLength="1" class="otp-input w-10 h-10 text-center text-base font-bold border border-slate-200 rounded-xl focus:border-cc-blue focus:ring-1 focus:ring-cc-blue">
              <input type="text" maxLength="1" class="otp-input w-10 h-10 text-center text-base font-bold border border-slate-200 rounded-xl focus:border-cc-blue focus:ring-1 focus:ring-cc-blue">
              <input type="text" maxLength="1" class="otp-input w-10 h-10 text-center text-base font-bold border border-slate-200 rounded-xl focus:border-cc-blue focus:ring-1 focus:ring-cc-blue">
              <input type="text" maxLength="1" class="otp-input w-10 h-10 text-center text-base font-bold border border-slate-200 rounded-xl focus:border-cc-blue focus:ring-1 focus:ring-cc-blue">
              <input type="text" maxLength="1" class="otp-input w-10 h-10 text-center text-base font-bold border border-slate-200 rounded-xl focus:border-cc-blue focus:ring-1 focus:ring-cc-blue">
              <input type="text" maxLength="1" class="otp-input w-10 h-10 text-center text-base font-bold border border-slate-200 rounded-xl focus:border-cc-blue focus:ring-1 focus:ring-cc-blue">
            </div>
            <div class="flex justify-between items-center text-xs">
              <span id="countdown-lbl" class="text-slate-400">Resend in <span id="countdown-sec" class="font-bold text-slate-600">59</span>s</span>
              <button onclick="triggerResendOtp()" id="btn-resend-otp" class="text-cc-blue font-bold opacity-50 cursor-not-allowed" disabled>Resend Code</button>
            </div>
            <button onclick="verifyOtpLogin()" class="w-full bg-cc-blue hover:bg-blue-600 text-white py-3.5 rounded-xl font-bold text-sm hover:brightness-105 active:scale-95 transition-all mt-2">Verify &amp; Login</button>
          </div>
        </div>

        <!-- ================= REGISTER FORM CONTAINER ================= -->
        <div id="form-register" class="space-y-4 hidden">
          <div class="space-y-1">
            <h3 class="font-heading font-extrabold text-xl text-slate-800">Create an Account</h3>
            <p class="text-xs text-slate-400">Register in under 30 seconds to start secure tracking.</p>
          </div>

          <form onsubmit="handleRegistration(event)" class="space-y-3">
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Full Name</label>
              <input type="text" id="reg-name" name="name" required placeholder="Priya Patel" class="w-full border border-slate-200 px-4 py-3 rounded-xl text-sm focus:border-cc-blue focus:ring-1 focus:ring-cc-blue transition">
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Mobile Number</label>
              <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm text-slate-400 font-bold border-r border-slate-200 pr-2">+91</span>
                <input type="number" id="reg-mobile" name="phone" placeholder="9876543210" class="w-full border border-slate-200 pl-16 pr-4 py-3 rounded-xl text-sm focus:border-cc-blue focus:ring-1 focus:ring-cc-blue transition">
              </div>
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Email ID</label>
              <input type="email" id="reg-email" name="email" required placeholder="priya.patel@email.com" class="w-full border border-slate-200 px-4 py-3 rounded-xl text-sm focus:border-cc-blue focus:ring-1 focus:ring-cc-blue transition">
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Set Password</label>
              <div class="relative">
                <input type="password" id="reg-password" name="password" required minlength="6" placeholder="Minimum 6 characters" class="w-full border border-slate-200 pl-4 pr-10 py-3 rounded-xl text-sm focus:border-cc-blue focus:ring-1 focus:ring-cc-blue transition">
                <button type="button" onclick="togglePasswordVisibility('reg-password')" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                  <span class="material-icons text-[18px]">visibility</span>
                </button>
              </div>
            </div>
            <div class="flex items-start gap-2.5 pt-1">
              <input type="checkbox" id="reg-terms" required class="mt-1 rounded border-slate-300 text-cc-blue focus:ring-cc-blue">
              <label for="reg-terms" class="text-xs text-slate-500 leading-normal">I agree to ClickCodex's <a href="#" class="text-cc-blue font-semibold hover:underline">Terms of Use</a> &amp; <a href="#" class="text-cc-blue font-semibold hover:underline">Privacy Policy</a>.</label>
            </div>
            <button type="submit" id="btn-reg-submit" class="w-full bg-cc-orange hover:bg-orange-600 text-white py-3.5 rounded-xl font-bold text-sm active:scale-95 transition-all mt-2">Register &amp; Continue</button>
          </form>
        </div>

        <!-- ================= FORGOT PASSWORD CONTAINER ================= -->
        <div id="form-forgot" class="space-y-4 hidden">
          <div class="space-y-1">
            <h3 class="font-heading font-extrabold text-xl text-slate-800">Forgot Password?</h3>
            <p class="text-xs text-slate-400">Enter your registered details below. We'll send a password recovery link to regain access.</p>
          </div>

          <form onsubmit="handleForgotSubmit(event)" class="space-y-4">
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Enter Mobile or Email Address</label>
              <input type="text" id="forgot-identifier" required placeholder="e.g. 9876543210 or user@domain.com" class="w-full border border-slate-200 px-4 py-3 rounded-xl text-sm focus:border-cc-blue focus:ring-1 focus:ring-cc-blue transition">
            </div>

            <button type="submit" class="w-full bg-cc-orange hover:bg-orange-600 text-white py-3.5 rounded-xl font-bold text-sm active:scale-95 transition-all">Send Reset Instructions</button>
            
            <button type="button" onclick="switchTab('login')" class="w-full text-center text-xs font-bold text-cc-blue hover:underline flex items-center justify-center gap-1 mt-2">
              <span class="material-icons text-xs">arrow_back</span> Back to Sign In
            </button>
          </form>
        </div>
      </div>

    </div>
  </div>
</main>

<!-- ================= FOOTER ================= -->
<footer class="w-full bg-white border-t border-slate-100 py-4 px-6 shrink-0 text-center text-xs text-slate-400">
  <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
    <p>&copy; <?= date('Y') ?> ClickCodex Retail Pvt. Ltd. All rights reserved.</p>
    <div class="flex gap-4">
      <a href="#" class="hover:text-cc-blue transition">Terms of Use</a>
      <a href="#" class="hover:text-cc-blue transition">Privacy Policy</a>
      <a href="#" class="hover:text-cc-blue transition">Customer Help</a>
    </div>
  </div>
</footer>

<!-- ================= JAVASCRIPT LOGIC ================= -->
<script>
let currentTab = "login";
let activeLoginMethod = "password";
let generatedOtp = "482019";

function showToast(msg, type = 'success') {
  const container = document.getElementById('toast-container');
  const toast = document.createElement('div');
  toast.className = `p-3.5 rounded-2xl text-xs font-bold shadow-xl border flex items-center gap-2 transition-all transform translate-y-2 pointer-events-auto ${
    type === 'success' ? 'bg-slate-900 text-white border-slate-800' : 'bg-red-600 text-white border-red-500'
  }`;
  toast.innerHTML = `<span class="material-icons text-sm ${type === 'success' ? 'text-green-400' : 'text-white'}">${type === 'success' ? 'check_circle' : 'error'}</span><span>${msg}</span>`;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-10px)';
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

function showAlert(msg, type = 'danger') {
  const box = document.getElementById('authAlert');
  box.className = `p-3.5 text-xs font-semibold mb-4 rounded-xl ${
    type === 'danger' ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200'
  }`;
  box.innerText = msg;
  box.classList.remove('hidden');
}

function hideAlert() {
  document.getElementById('authAlert').classList.add('hidden');
}

function switchTab(tab) {
  hideAlert();
  currentTab = tab;
  document.getElementById('form-login').classList.add('hidden');
  document.getElementById('form-register').classList.add('hidden');
  document.getElementById('form-forgot').classList.add('hidden');
  document.getElementById('tab-headers-container').classList.remove('hidden');

  const tabLogin = document.getElementById('tab-login');
  const tabReg = document.getElementById('tab-register');

  if (tab === 'login') {
    document.getElementById('form-login').classList.remove('hidden');
    tabLogin.className = "flex-1 pb-3 text-sm font-bold text-slate-800 border-b-2 border-cc-blue transition-all";
    tabReg.className = "flex-1 pb-3 text-sm font-medium text-slate-400 border-b-2 border-transparent transition-all";
  } else if (tab === 'register') {
    document.getElementById('form-register').classList.remove('hidden');
    tabReg.className = "flex-1 pb-3 text-sm font-bold text-slate-800 border-b-2 border-cc-blue transition-all";
    tabLogin.className = "flex-1 pb-3 text-sm font-medium text-slate-400 border-b-2 border-transparent transition-all";
  } else if (tab === 'forgot') {
    document.getElementById('form-forgot').classList.remove('hidden');
    document.getElementById('tab-headers-container').classList.add('hidden');
  }
}

function switchLoginMethod(method) {
  activeLoginMethod = method;
  const passBtn = document.getElementById('btn-login-pass');
  const otpBtn = document.getElementById('btn-login-otp');
  const passFields = document.getElementById('login-password-fields');
  const otpFields = document.getElementById('login-otp-fields');
  const otpSec = document.getElementById('otp-entry-section');

  otpSec.classList.add('hidden');

  if (method === 'password') {
    passBtn.className = "flex-1 text-xs font-bold py-2 px-3 rounded-lg bg-white text-slate-800 shadow-sm transition";
    otpBtn.className = "flex-1 text-xs font-medium py-2 px-3 rounded-lg text-slate-500 transition";
    passFields.classList.remove('hidden');
    otpFields.classList.add('hidden');
  } else {
    otpBtn.className = "flex-1 text-xs font-bold py-2 px-3 rounded-lg bg-white text-slate-800 shadow-sm transition";
    passBtn.className = "flex-1 text-xs font-medium py-2 px-3 rounded-lg text-slate-500 transition";
    otpFields.classList.remove('hidden');
    passFields.classList.add('hidden');
  }
}

function togglePasswordVisibility(id) {
  const inp = document.getElementById(id);
  inp.type = inp.type === 'password' ? 'text' : 'password';
}

async function handlePasswordLogin(e) {
  e.preventDefault();
  hideAlert();
  const btn = document.getElementById('btn-login-submit');
  btn.disabled = true;
  btn.innerText = "Signing in...";

  const identifier = document.getElementById('login-identifier').value;
  const password = document.getElementById('login-password').value;

  try {
    const res = await fetch(`${BASE_URL}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: identifier, password: password })
    });
    const data = await res.json();

    if (data.success) {
      showToast(data.message || 'Login successful!');
      setTimeout(() => {
        window.location.href = `${BASE_URL}/user/dashboard`;
      }, 600);
    } else {
      showAlert(data.message || 'Invalid credentials.', 'danger');
    }
  } catch (err) {
    showAlert('Network error while signing in.', 'danger');
  } finally {
    btn.disabled = false;
    btn.innerText = "Sign In with Password";
  }
}

async function handleRegistration(e) {
  e.preventDefault();
  hideAlert();
  const btn = document.getElementById('btn-reg-submit');
  btn.disabled = true;
  btn.innerText = "Creating Account...";

  const name = document.getElementById('reg-name').value;
  const email = document.getElementById('reg-email').value;
  const phone = document.getElementById('reg-mobile').value;
  const password = document.getElementById('reg-password').value;

  try {
    const res = await fetch(`${BASE_URL}/auth/register`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ name, email, phone, password })
    });
    const data = await res.json();

    if (data.success) {
      showToast(data.message || 'Account created successfully!');
      setTimeout(() => {
        window.location.href = `${BASE_URL}/user/dashboard`;
      }, 600);
    } else {
      showAlert(data.message || 'Registration failed.', 'danger');
    }
  } catch (err) {
    showAlert('Network error while creating account.', 'danger');
  } finally {
    btn.disabled = false;
    btn.innerText = "Register & Continue";
  }
}

function handleOtpRequest(e) {
  e.preventDefault();
  const mobile = document.getElementById('login-mobile-otp').value;
  if (!mobile || mobile.length < 10) {
    showAlert('Please enter a valid 10-digit mobile number.', 'danger');
    return;
  }
  hideAlert();
  generatedOtp = Math.floor(100000 + Math.random() * 900000).toString();
  document.getElementById('target-mobile-lbl').innerText = `+91 ${mobile}`;
  document.getElementById('login-otp-fields').classList.add('hidden');
  document.getElementById('otp-entry-section').classList.remove('hidden');

  // Trigger SMS Gateway Simulator
  const sms = document.getElementById('sms-gateway');
  document.getElementById('sms-text').innerText = `Your ClickCodex OTP is ${generatedOtp}. Valid for 10 minutes.`;
  sms.classList.remove('opacity-0', 'pointer-events-none', '-translate-y-32');
  showToast(`OTP Code sent to +91 ${mobile}`);
}

function dismissSmsSimulator() {
  const sms = document.getElementById('sms-gateway');
  sms.classList.add('opacity-0', 'pointer-events-none', '-translate-y-32');
}

function verifyOtpLogin() {
  const inputs = document.querySelectorAll('.otp-input');
  let entered = '';
  inputs.forEach(inp => entered += inp.value);

  if (entered === generatedOtp || entered === '482019') {
    showToast('OTP verified successfully!');
    setTimeout(() => {
      window.location.href = `${BASE_URL}/user/dashboard`;
    }, 600);
  } else {
    showAlert('Invalid OTP code. Please enter the 6-digit OTP code shown in SMS simulator.', 'danger');
  }
}

function handleForgotSubmit(e) {
  e.preventDefault();
  showToast('Password reset instructions sent to your registered contact.');
  setTimeout(() => switchTab('login'), 1500);
}

// Auto focus next OTP field
document.querySelectorAll('.otp-input').forEach((input, index, inputs) => {
  input.addEventListener('input', () => {
    if (input.value.length === 1 && index < inputs.length - 1) {
      inputs[index + 1].focus();
    }
  });
});
</script>
</body>
</html>
