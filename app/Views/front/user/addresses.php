<?php
require_once __DIR__ . '/../layouts/header.php';

$user      = $user      ?? [];
$addresses = $addresses ?? [];
$baseUrl   = $baseUrl   ?? (defined('BASE_URL') ? BASE_URL : '');
$total     = count($addresses);
?>

<style>
  body { background: #F1F5F9; }
  @keyframes fadeUp { from{opacity:0;transform:translateY(12px)} to{opacity:1;transform:translateY(0)} }
  .fade-up { animation: fadeUp .35s ease-out both; }
  @keyframes scaleIn { from{transform:scale(.95);opacity:0} to{transform:scale(1);opacity:1} }
  .scale-in { animation: scaleIn .2s ease-out both; }
  @keyframes slideInRight { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
  @keyframes slideOutRight { from{transform:translateX(0);opacity:1} to{transform:translateX(100%);opacity:0} }
  .toast-in { animation: slideInRight .35s ease-out forwards; }
  .toast-out { animation: slideOutRight .3s ease-in forwards; }

  .address-card {
    border: 1.5px solid #E2E8F0; border-radius: 16px; background: #fff;
    transition: all .25s ease; position: relative; overflow: hidden;
  }
  .address-card:hover { border-color: #2D82FF; box-shadow: 0 12px 24px -6px rgba(15,23,42,.08); transform: translateY(-2px); }
  .address-card.is-default-card { border-color: #2D82FF; background: #F8FAFC; }
  .address-card.is-default-card::before {
    content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3.5px;
    background: linear-gradient(90deg, #2D82FF, #8C30F5);
  }

  .form-input {
    width: 100%; border: 1.5px solid #E2E8F0; border-radius: 12px;
    padding: 10px 14px; font-size: 14px; transition: all .15s ease; outline: none; background: #fff;
  }
  .form-input:focus { border-color: #2D82FF; box-shadow: 0 0 0 3px rgba(45,130,255,.1); }

  .btn-primary {
    background: #2D82FF; color: #fff; font-weight: 700; font-size: 13px;
    padding: 10px 20px; border-radius: 12px; transition: all .2s ease;
    display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border: none;
  }
  .btn-primary:hover { background: #1D6FE0; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(45,130,255,.3); }

  .btn-outline {
    border: 1.5px solid #E2E8F0; color: #475569; font-weight: 600; font-size: 12px;
    padding: 7px 14px; border-radius: 10px; transition: all .15s ease;
    display: inline-flex; align-items: center; gap: 4px; background: #fff; cursor: pointer;
  }
  .btn-outline:hover { border-color: #2D82FF; color: #2D82FF; background: #F0F6FF; }

  .label-pill {
    padding: 6px 16px; border-radius: 10px; border: 1.5px solid #E2E8F0;
    font-size: 12px; font-weight: 600; color: #64748B; cursor: pointer; transition: all .15s ease; user-select: none;
  }
  .label-pill-active { border-color: #2D82FF; background: #F0F6FF; color: #2D82FF; }

  .modal-scroll::-webkit-scrollbar { width:4px; }
  .modal-scroll::-webkit-scrollbar-thumb { background:#CBD5E1; border-radius:4px; }
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
    <span class="text-slate-800 font-semibold">Saved Addresses</span>
  </nav>

  <div class="flex gap-6">

    <!-- Sidebar -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content -->
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
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
              <span class="material-icons text-[#2D82FF] text-xl">location_on</span>
            </div>
            <div>
              <h1 class="font-bold text-xl sm:text-2xl text-slate-900">Saved Addresses</h1>
              <p class="text-sm text-slate-400 mt-0.5"><span id="address-count"><?= $total ?></span> saved location<?= $total !== 1 ? 's' : '' ?></p>
            </div>
          </div>
          <div>
            <button onclick="openAddressModal()" class="btn-primary">
              <span class="material-icons text-base">add_location_alt</span> Add New Address
            </button>
          </div>
        </div>
      </div>

      <!-- Search Bar (if more than 2 addresses) -->
      <?php if ($total > 2): ?>
      <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-3 fade-up" style="animation-delay:.06s">
        <div class="relative">
          <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-base">search</span>
          <input type="text" id="address-search" oninput="filterAddresses()" placeholder="Search by recipient name, city, state, or pincode..."
                 class="w-full pl-9 pr-4 py-2 text-xs border border-slate-200 rounded-lg outline-none focus:border-[#2D82FF]">
        </div>
      </div>
      <?php endif; ?>

      <!-- Addresses Grid -->
      <div id="addresses-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-2 gap-4 fade-up" style="animation-delay:.1s">
        <!-- Rendered dynamically via JS from PHP array -->
      </div>

      <!-- Empty State -->
      <div id="empty-state" class="hidden fade-up">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-10 sm:p-16 text-center">
          <div class="w-24 h-24 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-5">
            <span class="material-icons text-5xl text-slate-300">wrong_location</span>
          </div>
          <h3 class="font-bold text-xl text-slate-800 mb-2">No addresses saved yet</h3>
          <p class="text-sm text-slate-500 max-w-sm mx-auto mb-6 leading-relaxed">Save your delivery locations for faster checkout experience.</p>
          <button onclick="openAddressModal()" class="btn-primary text-sm px-7 py-3 rounded-xl">
            <span class="material-icons text-[18px]">add_location_alt</span> Add First Address
          </button>
        </div>
      </div>

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

<!-- ADD / EDIT ADDRESS MODAL -->
<div id="address-modal" class="fixed inset-0 z-[80] items-center justify-center overflow-hidden hidden" style="display:none!important">
  <div onclick="closeAddressModal()" class="absolute inset-0 bg-black/50 cursor-pointer" style="backdrop-filter:blur(4px)"></div>
  <div class="bg-white rounded-2xl max-w-lg w-[92%] shadow-2xl relative z-10 p-6 max-h-[90vh] flex flex-col scale-in">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4 shrink-0">
      <h3 id="modal-title" class="font-bold text-base text-slate-900 flex items-center gap-2">
        <span class="material-icons text-[#2D82FF]">edit_location</span> Add New Address
      </h3>
      <button onclick="closeAddressModal()" class="text-slate-400 hover:text-slate-700 transition"><span class="material-icons">close</span></button>
    </div>

    <form id="address-form" onsubmit="saveAddress(event)" class="space-y-3.5 overflow-y-auto modal-scroll pr-1 flex-1">
      <input type="hidden" id="addr-enc-id" value="">

      <!-- Label Selector -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Address Type</label>
        <div class="flex gap-2">
          <button type="button" onclick="setLabel('Home')" id="label-Home" class="label-pill label-pill-active">🏠 Home</button>
          <button type="button" onclick="setLabel('Office')" id="label-Office" class="label-pill">🏢 Office</button>
          <button type="button" onclick="setLabel('Other')" id="label-Other" class="label-pill">📍 Other</button>
        </div>
        <input type="hidden" id="addr-label" value="Home">
      </div>

      <!-- Recipient Name & Phone Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Recipient Name <span class="text-red-500">*</span></label>
          <input type="text" id="addr-name" required placeholder="Full Name" class="form-input">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Phone Number <span class="text-red-500">*</span></label>
          <input type="tel" id="addr-phone" required placeholder="Mobile Number" class="form-input">
        </div>
      </div>

      <!-- Address Line 1 & Line 2 -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Flat / House No. / Building / Street <span class="text-red-500">*</span></label>
        <input type="text" id="addr-line1" required placeholder="House No., Building Name, Street" class="form-input">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Area / Sector / Landmark</label>
        <input type="text" id="addr-line2" placeholder="Near landmark, sector etc." class="form-input">
      </div>

      <!-- City, State, Pincode Grid -->
      <div class="grid grid-cols-3 gap-2.5">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">City <span class="text-red-500">*</span></label>
          <input type="text" id="addr-city" required placeholder="City" class="form-input">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">State <span class="text-red-500">*</span></label>
          <input type="text" id="addr-state" required placeholder="State" class="form-input">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Pincode <span class="text-red-500">*</span></label>
          <input type="text" id="addr-pincode" required maxlength="6" placeholder="6 digits" class="form-input">
        </div>
      </div>

      <!-- Country -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Country</label>
        <input type="text" id="addr-country" value="India" class="form-input" readonly>
      </div>

      <!-- Set Default Checkbox -->
      <div class="flex items-center gap-2 pt-1">
        <input type="checkbox" id="addr-default" class="w-4 h-4 rounded accent-[#2D82FF] cursor-pointer">
        <label for="addr-default" class="text-xs font-semibold text-slate-700 cursor-pointer">Set as default delivery address</label>
      </div>

      <!-- Action Buttons -->
      <div class="pt-3 border-t border-slate-100 flex gap-3 justify-end shrink-0">
        <button type="button" onclick="closeAddressModal()" class="btn-outline">Cancel</button>
        <button type="submit" class="btn-primary" id="save-addr-btn">
          <span class="material-icons text-base">save</span> Save Address
        </button>
      </div>
    </form>
  </div>
</div>

<!-- CONFIRM DELETE MODAL -->
<div id="delete-modal" class="fixed inset-0 z-[80] items-center justify-center overflow-hidden hidden" style="display:none!important">
  <div onclick="closeDeleteModal()" class="absolute inset-0 bg-black/50 cursor-pointer" style="backdrop-filter:blur(4px)"></div>
  <div class="bg-white rounded-2xl max-w-sm w-[90%] shadow-2xl relative z-10 p-6 scale-in">
    <button onclick="closeDeleteModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 transition"><span class="material-icons">close</span></button>
    <div class="text-center">
      <div class="w-14 h-14 mx-auto rounded-full bg-red-50 flex items-center justify-center mb-4">
        <span class="material-icons text-red-500 text-2xl">delete_outline</span>
      </div>
      <h3 class="font-bold text-lg text-slate-800 mb-1">Delete Address?</h3>
      <p class="text-sm text-slate-500 mb-5">This location will be removed from your saved addresses.</p>
      <div class="flex gap-3">
        <button onclick="closeDeleteModal()" class="flex-1 border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold py-2.5 rounded-xl transition text-sm">Cancel</button>
        <button id="confirm-del-btn" class="flex-1 bg-red-500 hover:bg-red-600 text-white font-semibold py-2.5 rounded-xl transition text-sm">Delete</button>
      </div>
    </div>
  </div>
</div>

<script>
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';
let ADDRESSES = <?= json_encode(array_values($addresses)) ?>;
let pendingDeleteId = null;

function showToast(msg, type='success'){
  const c = document.getElementById('toast-container');
  if(!c) return;
  const cls = {success:'bg-emerald-600', error:'bg-red-500', info:'bg-[#2D82FF]', warning:'bg-amber-500'};
  const ico = {success:'check_circle', error:'error', info:'info', warning:'warning'};
  const t = document.createElement('div');
  t.className = `toast-in pointer-events-auto flex items-center gap-3 ${cls[type]||cls.info} text-white px-5 py-3.5 rounded-xl shadow-2xl text-sm font-medium`;
  t.innerHTML = `<span class="material-icons text-lg">${ico[type]||'info'}</span><span class="flex-1">${msg}</span>`;
  c.appendChild(t);
  setTimeout(()=>{ t.classList.replace('toast-in','toast-out'); setTimeout(()=>t.remove(),300); },3500);
}

// ============================================================
// RENDER ADDRESSES
// ============================================================
function renderAddresses(list = ADDRESSES) {
  const grid = document.getElementById('addresses-grid');
  const empty = document.getElementById('empty-state');
  const countEl = document.getElementById('address-count');

  if (countEl) countEl.textContent = ADDRESSES.length;

  if (ADDRESSES.length === 0) {
    grid.innerHTML = '';
    grid.classList.add('hidden');
    empty.classList.remove('hidden');
    return;
  }
  empty.classList.add('hidden');
  grid.classList.remove('hidden');

  grid.innerHTML = list.map(item => {
    const encId = item.encrypted_id || item.id;
    const isDef = parseInt(item.is_default || 0) === 1;
    const labelIco = {Home:'home', Office:'work', Other:'location_on'}[item.label] || 'location_on';

    return `
    <div class="address-card p-5 ${isDef ? 'is-default-card' : ''} scale-in" id="card-${encId}">
      <!-- Top Label & Badges -->
      <div class="flex items-center justify-between gap-2 mb-3">
        <div class="flex items-center gap-2">
          <span class="material-icons text-[#2D82FF] text-base">${labelIco}</span>
          <span class="font-bold text-sm text-slate-800">${item.label || 'Home'}</span>
        </div>
        ${isDef ? '<span class="bg-[#2D82FF] text-white text-[10px] font-extrabold px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-xs">DEFAULT</span>' : ''}
      </div>

      <!-- Recipient & Contact -->
      <div class="space-y-1 mb-3">
        <p class="font-bold text-sm text-slate-900">${item.recipient_name || ''}</p>
        <p class="text-xs text-slate-500 flex items-center gap-1">
          <span class="material-icons text-xs text-slate-400">phone</span> ${item.phone || ''}
        </p>
      </div>

      <!-- Address Text -->
      <p class="text-xs text-slate-600 leading-relaxed mb-4 border-t border-slate-100 pt-3">
        ${item.address_line1 || ''}${item.address_line2 ? ', ' + item.address_line2 : ''}<br>
        ${item.city || ''}, ${item.state || ''} — <strong>${item.postal_code || ''}</strong><br>
        ${item.country || 'India'}
      </p>

      <!-- Action Buttons -->
      <div class="flex items-center justify-between gap-2 pt-2 border-t border-slate-100 flex-wrap">
        <div>
          ${!isDef ? `<button onclick="setDefaultAddress('${encId}')" class="text-xs font-semibold text-[#2D82FF] hover:underline flex items-center gap-1">
            <span class="material-icons text-sm">star_border</span> Set Default
          </button>` : ''}
        </div>
        <div class="flex items-center gap-2 ml-auto">
          <button onclick="editAddress('${encId}')" class="btn-outline py-1.5 px-3">
            <span class="material-icons text-sm">edit</span> Edit
          </button>
          <button onclick="confirmDelete('${encId}')" class="btn-outline py-1.5 px-3 !text-red-500 hover:!bg-red-50 hover:!border-red-200">
            <span class="material-icons text-sm">delete_outline</span>
          </button>
        </div>
      </div>
    </div>`;
  }).join('');
}

function filterAddresses() {
  const q = (document.getElementById('address-search')?.value || '').trim().toLowerCase();
  if (!q) { renderAddresses(ADDRESSES); return; }
  const filtered = ADDRESSES.filter(a =>
    (a.recipient_name || '').toLowerCase().includes(q) ||
    (a.city || '').toLowerCase().includes(q) ||
    (a.state || '').toLowerCase().includes(q) ||
    (a.postal_code || '').toLowerCase().includes(q) ||
    (a.label || '').toLowerCase().includes(q)
  );
  renderAddresses(filtered);
}

// ============================================================
// MODAL CONTROLS & LABEL SELECTION
// ============================================================
function setLabel(lbl) {
  document.getElementById('addr-label').value = lbl;
  ['Home', 'Office', 'Other'].forEach(l => {
    const btn = document.getElementById('label-' + l);
    if (btn) {
      if (l === lbl) btn.className = 'label-pill label-pill-active';
      else btn.className = 'label-pill';
    }
  });
}

function openAddressModal(addr = null) {
  const modal = document.getElementById('address-modal');
  const title = document.getElementById('modal-title');
  const form  = document.getElementById('address-form');

  form.reset();
  if (addr) {
    title.innerHTML = '<span class="material-icons text-[#2D82FF]">edit_location</span> Edit Address';
    document.getElementById('addr-enc-id').value = addr.encrypted_id || addr.id;
    setLabel(addr.label || 'Home');
    document.getElementById('addr-name').value    = addr.recipient_name || '';
    document.getElementById('addr-phone').value   = addr.phone || '';
    document.getElementById('addr-line1').value   = addr.address_line1 || '';
    document.getElementById('addr-line2').value   = addr.address_line2 || '';
    document.getElementById('addr-city').value    = addr.city || '';
    document.getElementById('addr-state').value   = addr.state || '';
    document.getElementById('addr-pincode').value = addr.postal_code || '';
    document.getElementById('addr-default').checked = parseInt(addr.is_default || 0) === 1;
  } else {
    title.innerHTML = '<span class="material-icons text-[#2D82FF]">add_location_alt</span> Add New Address';
    document.getElementById('addr-enc-id').value = '';
    setLabel('Home');
    document.getElementById('addr-country').value = 'India';
    document.getElementById('addr-default').checked = ADDRESSES.length === 0;
  }

  modal.style.display = 'flex';
  modal.classList.remove('hidden');
}

function closeAddressModal() {
  const modal = document.getElementById('address-modal');
  modal.style.display = 'none';
  modal.classList.add('hidden');
}

function editAddress(encId) {
  const addr = ADDRESSES.find(a => (a.encrypted_id || a.id) == encId);
  if (addr) openAddressModal(addr);
}

// ============================================================
// AJAX SAVE / UPDATE ADDRESS
// ============================================================
function saveAddress(e) {
  e.preventDefault();
  const encId   = document.getElementById('addr-enc-id').value;
  const isEdit  = !!encId;
  const url     = isEdit ? `${BASE_URL}/user/addresses/update` : `${BASE_URL}/user/addresses/store`;

  const payload = {
    address_id: encId,
    label:          document.getElementById('addr-label').value,
    recipient_name: document.getElementById('addr-name').value.trim(),
    phone:          document.getElementById('addr-phone').value.trim(),
    address_line1:  document.getElementById('addr-line1').value.trim(),
    address_line2:  document.getElementById('addr-line2').value.trim(),
    city:           document.getElementById('addr-city').value.trim(),
    state:          document.getElementById('addr-state').value.trim(),
    postal_code:    document.getElementById('addr-pincode').value.trim(),
    country:        document.getElementById('addr-country').value.trim(),
    is_default:     document.getElementById('addr-default').checked ? 1 : 0
  };

  fetch(url, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload)
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      closeAddressModal();
      showToast(data.message, 'success');
      setTimeout(() => location.reload(), 600);
    } else {
      showToast(data.message || 'Failed to save address.', 'error');
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}

// ============================================================
// AJAX DELETE ADDRESS
// ============================================================
function confirmDelete(encId) {
  pendingDeleteId = encId;
  const modal = document.getElementById('delete-modal');
  document.getElementById('confirm-del-btn').onclick = executeDelete;
  modal.style.display = 'flex';
  modal.classList.remove('hidden');
}

function closeDeleteModal() {
  const modal = document.getElementById('delete-modal');
  modal.style.display = 'none';
  modal.classList.add('hidden');
}

function executeDelete() {
  closeDeleteModal();
  fetch(`${BASE_URL}/user/addresses/delete`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ address_id: pendingDeleteId })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      ADDRESSES = ADDRESSES.filter(a => (a.encrypted_id || a.id) != pendingDeleteId);
      renderAddresses();
      showToast('Address deleted.', 'info');
    } else {
      showToast(data.message || 'Failed to delete address.', 'error');
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}

// ============================================================
// AJAX SET DEFAULT ADDRESS
// ============================================================
function setDefaultAddress(encId) {
  fetch(`${BASE_URL}/user/addresses/set-default`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ address_id: encId })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      ADDRESSES.forEach(a => {
        a.is_default = (a.encrypted_id || a.id) == encId ? 1 : 0;
      });
      renderAddresses();
      showToast('Default delivery address updated!', 'success');
    } else {
      showToast(data.message || 'Could not update default address.', 'error');
    }
  })
  .catch(() => showToast('Network connection issue.', 'error'));
}

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
  renderAddresses();
});

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeAddressModal();
    closeDeleteModal();
  }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
