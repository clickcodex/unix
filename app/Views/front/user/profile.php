<?php
require_once __DIR__ . '/../layouts/header.php';

$user     = $user     ?? [];
$baseUrl  = $baseUrl  ?? (defined('BASE_URL') ? BASE_URL : '');
$initials = !empty($user['name']) ? strtoupper(mb_substr($user['name'], 0, 1)) : 'U';
?>

<style>
  body { background: #F1F5F9; }
  @keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
  .fade-up { animation: fadeUp .35s ease-out both; }
  @keyframes slideInRight { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
  @keyframes slideOutRight { from{transform:translateX(0);opacity:1} to{transform:translateX(100%);opacity:0} }
  .toast-in { animation: slideInRight .35s ease-out forwards; }
  .toast-out { animation: slideOutRight .3s ease-in forwards; }

  .profile-card { border: 1.5px solid #E2E8F0; border-radius: 16px; background: #fff; }
  .form-input {
    width: 100%; border: 1.5px solid #E2E8F0; border-radius: 12px;
    padding: 10px 14px; font-size: 14px; transition: all .15s ease;
    outline: none; background: #fff;
  }
  .form-input:focus { border-color: #2D82FF; box-shadow: 0 0 0 3px rgba(45,130,255,.1); }
  .form-input:disabled, .form-input[readonly] { background: #F8FAFC; color: #94A3B8; cursor: not-allowed; }

  .btn-save {
    background: #2D82FF; color: #fff; font-weight: 700; font-size: 13px;
    padding: 11px 24px; border-radius: 12px; transition: all .2s ease;
    display: inline-flex; items-center; gap: 8px; cursor: pointer; border: none;
  }
  .btn-save:hover { background: #1D6FE0; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(45,130,255,.3); }
  .btn-save:active { transform: translateY(0); }
</style>

<!-- Toast Container -->
<div id="toast-container" class="fixed top-5 right-5 z-[200] flex flex-col gap-2.5 pointer-events-none" style="max-width:380px"></div>

<main class="max-w-7xl mx-auto px-4 py-6 pb-24 md:pb-6">

  <!-- Breadcrumb -->
  <nav class="flex items-center gap-2 text-xs text-slate-500 mb-5">
    <a href="<?= $baseUrl ?>/" class="hover:text-[#2D82FF] transition">Home</a>
    <span class="material-icons text-[13px]">chevron_right</span>
    <a href="<?= $baseUrl ?>/user/dashboard" class="hover:text-[#2D82FF] transition">My Account</a>
    <span class="material-icons text-[13px]">chevron_right</span>
    <span class="text-slate-800 font-semibold">Edit Profile</span>
  </nav>

  <div class="flex gap-6">

    <!-- Sidebar -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content (Same Sizing as Dashboard/Cart/Orders) -->
    <div class="flex-1 min-w-0 space-y-4">

      <!-- Mobile Sidebar Toggle Button -->
      <button onclick="toggleMobileSidebar(true)" class="lg:hidden flex items-center justify-between w-full bg-white border border-slate-200 p-3.5 rounded-2xl shadow-xs text-xs font-bold text-slate-800 hover:bg-slate-50 transition">
        <div class="flex items-center gap-2">
          <span class="material-icons text-[#2D82FF] text-lg">menu</span>
          <span>Account Navigation Menu</span>
        </div>
        <span class="bg-[#2D82FF]/10 text-[#2D82FF] text-[10px] font-extrabold px-2.5 py-1 rounded-lg">MENU</span>
      </button>

      <!-- Page Header -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 fade-up">
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
              <span class="material-icons text-[#2D82FF] text-xl">person</span>
            </div>
            <div>
              <h1 class="font-bold text-xl sm:text-2xl text-slate-900">Edit Profile</h1>
              <p class="text-sm text-slate-400 mt-0.5">Manage your personal information and security settings</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Avatar & Account Summary Header -->
      <div class="bg-gradient-to-br from-[#2D82FF] via-[#5B4FD9] to-[#0F172A] rounded-2xl p-6 text-white shadow-md fade-up" style="animation-delay:.06s">
        <div class="flex flex-col sm:flex-row items-center sm:items-center gap-5 text-center sm:text-left">
          
          <!-- Avatar Container with Camera Overlay -->
          <div class="relative group shrink-0">
            <div id="avatar-display" class="w-24 h-24 rounded-2xl bg-white/20 backdrop-blur-sm border-2 border-white/30 flex items-center justify-center text-4xl font-extrabold shadow-inner overflow-hidden relative cursor-pointer" onclick="triggerAvatarUpload()">
              <?php if (!empty($user['avatar_url'])): ?>
                <img id="avatar-preview-img" src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="Profile Picture" class="w-full h-full object-cover">
              <?php else: ?>
                <span id="avatar-preview-initials"><?= $initials ?></span>
              <?php endif; ?>
              <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold gap-1">
                <span class="material-icons text-lg">photo_camera</span>
              </div>
            </div>
            <!-- Upload Icon Badge -->
            <button onclick="triggerAvatarUpload()" type="button" class="absolute -bottom-1 -right-1 bg-white text-slate-800 p-1.5 rounded-full shadow-lg hover:bg-slate-100 transition border border-slate-200" title="Upload new photo">
              <span class="material-icons text-sm text-[#2D82FF]">photo_camera</span>
            </button>
            <input type="file" id="avatar-input" accept="image/jpeg,image/png,image/webp,image/gif" onchange="uploadAvatarFile(this)" class="hidden">
          </div>

          <!-- User Metadata & Upload Actions -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-1">
              <h2 class="font-extrabold text-xl sm:text-2xl truncate" id="profile-display-name"><?= htmlspecialchars($user['name'] ?? 'Customer') ?></h2>
              <span class="bg-white/20 text-white text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider">✓ Verified Customer</span>
            </div>
            <p class="text-white/70 text-sm truncate"><?= htmlspecialchars($user['email'] ?? '') ?></p>
            
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mt-3">
              <button onclick="triggerAvatarUpload()" type="button" class="bg-white/20 hover:bg-white/30 text-white text-xs font-bold px-3 py-1.5 rounded-xl transition inline-flex items-center gap-1">
                <span class="material-icons text-sm">upload</span> Upload Photo
              </button>
              <button onclick="removeAvatarFile()" id="remove-avatar-btn" type="button" class="<?= empty($user['avatar_url']) ? 'hidden' : '' ?> bg-red-500/30 hover:bg-red-500/50 text-white text-xs font-bold px-3 py-1.5 rounded-xl transition inline-flex items-center gap-1">
                <span class="material-icons text-sm">delete</span> Remove
              </button>
              <span class="text-[11px] text-white/50 hidden sm:inline">JPG, PNG, WEBP max 5MB</span>
            </div>
          </div>

        </div>
      </div>

      <!-- Grid: Personal Info + Password -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 fade-up" style="animation-delay:.1s">

        <!-- Personal Info Form (2 Cols on lg) -->
        <div class="lg:col-span-2 space-y-5">

          <!-- Section 1: Personal Details -->
          <div class="profile-card p-6 shadow-sm">
            <div class="flex items-center gap-2.5 border-b border-slate-100 pb-4 mb-5">
              <span class="material-icons text-[#2D82FF] text-xl">badge</span>
              <div>
                <h2 class="font-bold text-base text-slate-900">Personal Information</h2>
                <p class="text-xs text-slate-400">Update your name, contact details, and personal info</p>
              </div>
            </div>

            <form id="profile-form" onsubmit="saveProfile(event)" class="space-y-4">
              <!-- Full Name -->
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Full Name <span class="text-red-500">*</span></label>
                <input type="text" id="prof-name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required
                       placeholder="Enter your full name" class="form-input">
              </div>

              <!-- Email (Readonly) -->
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address</label>
                <div class="relative">
                  <input type="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" readonly
                         class="form-input pr-10">
                  <span class="material-icons absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">lock</span>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Email address cannot be changed for account security.</p>
              </div>

              <!-- Phone & Gender Grid -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Phone Number</label>
                  <input type="tel" id="prof-phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                         placeholder="e.g. +91 9876543210" class="form-input">
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Gender</label>
                  <select id="prof-gender" class="form-input cursor-pointer">
                    <option value="">Select Gender</option>
                    <option value="male" <?= strtolower($user['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                    <option value="female" <?= strtolower($user['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                    <option value="other" <?= strtolower($user['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                  </select>
                </div>
              </div>

              <!-- Date of Birth -->
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Date of Birth</label>
                <input type="date" id="prof-dob" value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>" class="form-input">
              </div>

              <!-- Action Button -->
              <div class="pt-2 flex justify-end">
                <button type="submit" class="btn-save">
                  <span class="material-icons text-base">save</span> Save Changes
                </button>
              </div>
            </form>
          </div>

          <!-- Section 2: Security & Change Password -->
          <div class="profile-card p-6 shadow-sm">
            <div class="flex items-center gap-2.5 border-b border-slate-100 pb-4 mb-5">
              <span class="material-icons text-purple-600 text-xl">lock_reset</span>
              <div>
                <h2 class="font-bold text-base text-slate-900">Change Password</h2>
                <p class="text-xs text-slate-400">Ensure your account is using a strong, unique password</p>
              </div>
            </div>

            <form id="password-form" onsubmit="changePassword(event)" class="space-y-4">
              <!-- Current Password -->
              <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Current Password <span class="text-red-500">*</span></label>
                <div class="relative">
                  <input type="password" id="pwd-current" required placeholder="Enter current password" class="form-input pr-10">
                  <button type="button" onclick="togglePasswordVisibility('pwd-current', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <span class="material-icons text-sm">visibility_off</span>
                  </button>
                </div>
              </div>

              <!-- New Password & Confirm Grid -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">New Password <span class="text-red-500">*</span></label>
                  <div class="relative">
                    <input type="password" id="pwd-new" required minlength="6" placeholder="Min. 6 characters" class="form-input pr-10">
                    <button type="button" onclick="togglePasswordVisibility('pwd-new', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                      <span class="material-icons text-sm">visibility_off</span>
                    </button>
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Confirm New Password <span class="text-red-500">*</span></label>
                  <div class="relative">
                    <input type="password" id="pwd-confirm" required minlength="6" placeholder="Re-enter new password" class="form-input pr-10">
                    <button type="button" onclick="togglePasswordVisibility('pwd-confirm', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                      <span class="material-icons text-sm">visibility_off</span>
                    </button>
                  </div>
                </div>
              </div>

              <!-- Action Button -->
              <div class="pt-2 flex justify-end">
                <button type="submit" class="btn-save !bg-purple-600 hover:!bg-purple-700">
                  <span class="material-icons text-base">key</span> Update Password
                </button>
              </div>
            </form>
          </div>

        </div><!-- /Left Column -->

        <!-- Right Side: Account Overview & Tips -->
        <div class="space-y-5">

          <!-- Account Meta Card -->
          <div class="profile-card p-5 shadow-sm space-y-4">
            <h3 class="font-bold text-sm text-slate-900 flex items-center gap-2">
              <span class="material-icons text-[#2D82FF] text-lg">shield</span> Account Details
            </h3>

            <div class="space-y-3 text-xs divide-y divide-slate-100">
              <div class="flex justify-between pt-1">
                <span class="text-slate-400">Account ID</span>
                <span class="font-mono font-bold text-slate-800">#<?= sprintf('%06d', $user['id'] ?? 0) ?></span>
              </div>
              <div class="flex justify-between pt-2">
                <span class="text-slate-400">Account Status</span>
                <span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded">Active</span>
              </div>
              <div class="flex justify-between pt-2">
                <span class="text-slate-400">Email Verification</span>
                <span class="font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">Verified</span>
              </div>
              <div class="flex justify-between pt-2">
                <span class="text-slate-400">Primary Role</span>
                <span class="font-semibold text-slate-700"><?= htmlspecialchars($user['primary_role'] ?? 'Customer') ?></span>
              </div>
            </div>
          </div>

          <!-- Security Tips Card -->
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border border-blue-100 p-5 space-y-3">
            <h3 class="font-bold text-sm text-blue-900 flex items-center gap-2">
              <span class="material-icons text-[#2D82FF] text-lg">security</span> Security Best Practices
            </h3>
            <ul class="text-xs text-blue-800 space-y-2 leading-relaxed">
              <li class="flex items-start gap-1.5">
                <span class="material-icons text-blue-500 text-sm mt-0.5">check_circle</span>
                Use a password with at least 8 characters including numbers and symbols.
              </li>
              <li class="flex items-start gap-1.5">
                <span class="material-icons text-blue-500 text-sm mt-0.5">check_circle</span>
                Never share your login credentials or OTPs with anyone.
              </li>
              <li class="flex items-start gap-1.5">
                <span class="material-icons text-blue-500 text-sm mt-0.5">check_circle</span>
                Keep your phone number updated for order updates & recovery.
              </li>
            </ul>
          </div>

        </div><!-- /Right Column -->

      </div><!-- /Grid -->

    </div><!-- /Main Content -->
  </div><!-- /Flex -->
</main>

<!-- Mobile Bottom Nav -->
<div class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-slate-200 z-50 flex justify-around py-2 shadow-2xl">
  <a href="<?= $baseUrl ?>/" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">home</span><span class="text-[9px] font-semibold">Home</span>
  </a>
  <a href="<?= $baseUrl ?>/user/orders" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">shopping_bag</span><span class="text-[9px] font-semibold">Orders</span>
  </a>
  <a href="<?= $baseUrl ?>/wishlist" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">favorite_border</span><span class="text-[9px] font-semibold">Wishlist</span>
  </a>
  <a href="<?= $baseUrl ?>/cart" class="flex flex-col items-center text-slate-500 hover:text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">shopping_cart</span><span class="text-[9px] font-semibold">Cart</span>
  </a>
  <button onclick="toggleMobileSidebar(true)" class="flex flex-col items-center text-[#2D82FF] py-1 transition">
    <span class="material-icons text-2xl">person</span><span class="text-[9px] font-semibold">Account</span>
  </button>
</div>

<script>
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';

function showToast(msg, type='success'){
  const c = document.getElementById('toast-container');
  if (!c) return;
  const cls = {success:'bg-emerald-600', error:'bg-red-500', info:'bg-[#2D82FF]', warning:'bg-amber-500'};
  const ico = {success:'check_circle', error:'error', info:'info', warning:'warning'};
  const t = document.createElement('div');
  t.className = `toast-in pointer-events-auto flex items-center gap-3 ${cls[type]||cls.info} text-white px-5 py-3.5 rounded-xl shadow-2xl text-sm font-medium`;
  t.innerHTML = `<span class="material-icons text-lg">${ico[type]||'info'}</span><span class="flex-1">${msg}</span>`;
  c.appendChild(t);
  setTimeout(()=>{ t.classList.replace('toast-in','toast-out'); setTimeout(()=>t.remove(),300); },3500);
}

function togglePasswordVisibility(inputId, btn) {
  const input = document.getElementById(inputId);
  const icon = btn.querySelector('.material-icons');
  if (input.type === 'password') {
    input.type = 'text';
    icon.textContent = 'visibility';
  } else {
    input.type = 'password';
    icon.textContent = 'visibility_off';
  }
}

function saveProfile(e) {
  e.preventDefault();
  const name   = document.getElementById('prof-name').value.trim();
  const phone  = document.getElementById('prof-phone').value.trim();
  const gender = document.getElementById('prof-gender').value;
  const dob    = document.getElementById('prof-dob').value;

  if (!name) {
    showToast('Please enter your full name.', 'warning');
    return;
  }

  fetch(`${BASE_URL}/user/profile/update`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      name: name,
      phone: phone,
      gender: gender,
      date_of_birth: dob
    })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(data.message || 'Profile updated successfully!', 'success');
    } else {
      showToast(data.message || 'Failed to update profile.', 'error');
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}

function changePassword(e) {
  e.preventDefault();
  const current = document.getElementById('pwd-current').value;
  const newPwd  = document.getElementById('pwd-new').value;
  const confirm = document.getElementById('pwd-confirm').value;

  if (!current || !newPwd) {
    showToast('Please fill in both current and new password.', 'warning');
    return;
  }

  if (newPwd !== confirm) {
    showToast('New passwords do not match.', 'warning');
    return;
  }

  if (newPwd.length < 6) {
    showToast('New password must be at least 6 characters.', 'warning');
    return;
  }

  fetch(`${BASE_URL}/user/password/update`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      current_password: current,
      new_password: newPwd
    })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(data.message || 'Password updated successfully!', 'success');
      document.getElementById('password-form').reset();
    } else {
      showToast(data.message || 'Failed to update password.', 'error');
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}

// ============================================================
// PROFILE PICTURE UPLOAD HANDLERS
// ============================================================
function triggerAvatarUpload() {
  document.getElementById('avatar-input').click();
}

function uploadAvatarFile(input) {
  if (!input.files || !input.files[0]) return;

  const file = input.files[0];
  if (file.size > 5 * 1024 * 1024) {
    showToast('Image size exceeds 5MB limit.', 'warning');
    return;
  }

  const formData = new FormData();
  formData.append('avatar', file);

  showToast('Uploading profile picture…', 'info');

  fetch(`${BASE_URL}/user/avatar/upload`, {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success && data.avatar_url) {
      showToast(data.message || 'Profile picture updated!', 'success');
      
      // Update Hero Avatar
      const display = document.getElementById('avatar-display');
      display.innerHTML = `
        <img id="avatar-preview-img" src="${data.avatar_url}" alt="Profile Picture" class="w-full h-full object-cover">
        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold gap-1">
          <span class="material-icons text-lg">photo_camera</span>
        </div>
      `;

      // Update Sidebar Avatars
      document.querySelectorAll('.sidebar-avatar-img').forEach(img => img.src = data.avatar_url);
      document.querySelectorAll('.sidebar-avatar-initials').forEach(init => {
        const parent = init.parentElement;
        parent.innerHTML = `<img src="${data.avatar_url}" alt="Avatar" class="w-full h-full object-cover sidebar-avatar-img">`;
      });

      document.getElementById('remove-avatar-btn')?.classList.remove('hidden');
    } else {
      showToast(data.message || 'Failed to upload image.', 'error');
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}

function removeAvatarFile() {
  if (!confirm('Remove your profile picture?')) return;

  fetch(`${BASE_URL}/user/avatar/remove`, {
    method: 'POST'
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast(data.message || 'Profile picture removed.', 'info');
      setTimeout(() => location.reload(), 500);
    } else {
      showToast(data.message || 'Failed to remove picture.', 'error');
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
