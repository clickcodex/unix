<?php
$pageTitle = 'Coupons & Offers';
$activeMenu = 'offers';

require_once __DIR__ . '/../layouts/header.php';

$coupons = $coupons ?? [];
$offers = $offers ?? [];
$pagination = $pagination ?? ['current_page' => 1, 'total_pages' => 1, 'total' => 0, 'per_page' => 15];
$kpis = $kpis ?? [];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">Coupons & Offers</h1>
      <p class="text-slate-500 text-sm mt-1">Manage discount codes, promotional offers, and applicability rules</p>
    </div>
    <div class="flex items-center gap-2">
      <button onclick="openCouponModal()" class="btn-primary text-sm">
        <span class="material-icons text-[18px]">confirmation_number</span> New Coupon
      </button>
      <button onclick="openOfferModal()" class="btn-primary text-sm bg-cc-orange hover:bg-orange-600 shadow-cc-orange/20">
        <span class="material-icons text-[18px]">sell</span> New Offer
      </button>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-blue/10 flex items-center justify-center"><span class="material-icons text-cc-blue text-[22px]">confirmation_number</span></div>
        <div><p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Coupons</p>
        <p class="text-xl font-extrabold text-slate-900"><?= number_format($kpis['total_coupons'] ?? 0) ?></p></div>
      </div>
      <p class="text-xs text-slate-500"><strong class="text-emerald-600"><?= $kpis['active_coupons'] ?? 0 ?></strong> active</p>
    </div>
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center"><span class="material-icons text-emerald-500 text-[22px]">redeem</span></div>
        <div><p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Redemptions</p>
        <p class="text-xl font-extrabold text-slate-900"><?= number_format($kpis['total_redemptions'] ?? 0) ?></p></div>
      </div>
      <p class="text-xs text-slate-500">Total coupon uses</p>
    </div>
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-orange/10 flex items-center justify-center"><span class="material-icons text-cc-orange text-[22px]">sell</span></div>
        <div><p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Offers</p>
        <p class="text-xl font-extrabold text-slate-900"><?= number_format($kpis['total_offers'] ?? 0) ?></p></div>
      </div>
      <p class="text-xs text-slate-500"><strong class="text-emerald-600"><?= $kpis['active_offers'] ?? 0 ?></strong> live now</p>
    </div>
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-pink/10 flex items-center justify-center"><span class="material-icons text-cc-pink text-[22px]">timer_off</span></div>
        <div><p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Expired</p>
        <p class="text-xl font-extrabold text-slate-900"><?= number_format($kpis['expired_coupons'] ?? 0) ?></p></div>
      </div>
      <p class="text-xs text-slate-500">Past end date</p>
    </div>
  </div>

  <!-- Tab Switcher -->
  <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl w-fit">
    <button id="tabCoupons" onclick="switchTab('coupons')" class="px-4 py-2 rounded-lg text-sm font-semibold transition bg-white text-slate-800 shadow-sm">
      <span class="material-icons text-[16px] align-middle mr-1">confirmation_number</span> Coupons
    </button>
    <button id="tabOffers" onclick="switchTab('offers')" class="px-4 py-2 rounded-lg text-sm font-semibold transition text-slate-500 hover:text-slate-700">
      <span class="material-icons text-[16px] align-middle mr-1">sell</span> Offers
    </button>
  </div>

  <!-- COUPONS TABLE -->
  <div id="couponsPanel">
    <div class="section-card overflow-hidden">
      <div class="p-4 flex items-center gap-3 border-b border-slate-200">
        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 gap-2 flex-1 max-w-xs focus-within:border-cc-blue transition">
          <span class="material-icons text-slate-400 text-[18px]">search</span>
          <input type="text" id="couponSearch" placeholder="Search codes..." class="bg-transparent text-sm w-full focus:outline-none" oninput="debounceCoupons()">
        </div>
        <select id="couponStatusFilter" class="input-field text-xs w-auto py-2" onchange="fetchCoupons(1)">
          <option value="">All</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="expired">Expired</option>
        </select>
      </div>
      <div class="overflow-x-auto">
        <table class="data-table">
          <thead><tr>
            <th>Code</th><th>Type</th><th>Discount</th><th>Usage</th><th>Validity</th><th>Status</th><th>Actions</th>
          </tr></thead>
          <tbody id="couponsBody">
            <?php foreach ($coupons as $c): ?>
              <?php
                $isExpired = !empty($c['ends_at']) && strtotime($c['ends_at']) < time();
                $badgeClass = $isExpired ? 'badge-red' : ((int)$c['is_active'] === 1 ? 'badge-green' : 'badge-slate');
                $statusLabel = $isExpired ? 'Expired' : ((int)$c['is_active'] === 1 ? 'Active' : 'Inactive');
                $typeBadge = $c['discount_type'] === 'percentage' ? 'badge-purple' : 'badge-blue';
              ?>
              <tr>
                <td><span class="mono font-bold text-sm text-cc-blue"><?= htmlspecialchars($c['code']) ?></span>
                  <p class="text-[10px] text-slate-400 truncate max-w-[160px]"><?= htmlspecialchars($c['description'] ?? '') ?></p></td>
                <td><span class="badge <?= $typeBadge ?> text-[10px]"><?= ucfirst($c['discount_type']) ?></span></td>
                <td><strong class="text-slate-900 mono"><?= $c['discount_type'] === 'percentage' ? $c['discount_value'].'%' : '₹'.number_format($c['discount_value'], 2) ?></strong>
                  <?php if ($c['max_discount']): ?><p class="text-[10px] text-slate-400">Max ₹<?= number_format($c['max_discount']) ?></p><?php endif; ?></td>
                <td><span class="mono text-xs"><?= $c['used_count'] ?><?= $c['usage_limit'] ? '/'.$c['usage_limit'] : '' ?></span></td>
                <td>
                  <?php if ($c['starts_at']): ?><p class="text-[10px] text-slate-500"><?= date('d M Y', strtotime($c['starts_at'])) ?></p><?php endif; ?>
                  <?php if ($c['ends_at']): ?><p class="text-[10px] text-slate-400">→ <?= date('d M Y', strtotime($c['ends_at'])) ?></p><?php endif; ?>
                </td>
                <td>
                  <div class="inline-toggle <?= (int)$c['is_active'] === 1 ? 'on' : '' ?>" onclick="toggleCouponActive('<?= $c['encrypted_id'] ?>', this)" title="Toggle Active"></div>
                </td>
                <td>
                  <div class="flex items-center gap-1">
                    <button onclick="editCoupon('<?= $c['encrypted_id'] ?>')" class="action-btn"><span class="material-icons text-[18px]">edit</span></button>
                    <button onclick="deleteCoupon('<?= $c['encrypted_id'] ?>', '<?= htmlspecialchars($c['code']) ?>')" class="action-btn danger"><span class="material-icons text-[18px]">delete_outline</span></button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($coupons)): ?>
              <tr><td colspan="7" class="text-center py-10"><span class="material-icons text-slate-300 text-[36px]">confirmation_number</span><p class="text-slate-500 mt-2">No coupons found</p></td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- OFFERS PANEL -->
  <div id="offersPanel" class="hidden">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <?php foreach ($offers as $o): ?>
        <?php
          $isLive = (int)$o['is_active'] === 1 && strtotime($o['starts_at']) <= time() && (empty($o['ends_at']) || strtotime($o['ends_at']) >= time());
          $typeColors = ['percentage' => 'cc-purple', 'flat' => 'cc-blue', 'buy_x_get_y' => 'cc-orange', 'combo' => 'cc-pink'];
          $tc = $typeColors[$o['offer_type']] ?? 'cc-blue';
        ?>
        <div class="section-card p-5 hover:shadow-lg transition-shadow">
          <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-2.5">
              <div class="w-10 h-10 rounded-xl bg-<?= $tc ?>/10 flex items-center justify-center">
                <span class="material-icons text-<?= $tc ?> text-[22px]">sell</span>
              </div>
              <div>
                <h4 class="font-heading font-bold text-sm text-slate-900"><?= htmlspecialchars($o['name']) ?></h4>
                <span class="badge badge-<?= $isLive ? 'green' : 'slate' ?> text-[9px]"><?= $isLive ? 'LIVE' : 'INACTIVE' ?></span>
              </div>
            </div>
            <div class="inline-toggle <?= (int)$o['is_active'] === 1 ? 'on' : '' ?>" onclick="toggleOfferActive('<?= $o['encrypted_id'] ?>', this)"></div>
          </div>

          <p class="text-xs text-slate-500 mb-3 line-clamp-2"><?= htmlspecialchars($o['description'] ?? 'No description') ?></p>

          <div class="flex items-center gap-3 text-xs mb-3">
            <div class="px-2 py-1 bg-<?= $tc ?>/10 rounded-lg">
              <span class="font-bold text-<?= $tc ?>"><?= $o['offer_type'] === 'percentage' ? $o['discount_value'].'% OFF' : '₹'.number_format($o['discount_value'],0).' OFF' ?></span>
            </div>
            <?php if ($o['min_order_value']): ?>
              <span class="text-slate-400">Min ₹<?= number_format($o['min_order_value']) ?></span>
            <?php endif; ?>
          </div>

          <div class="flex items-center justify-between text-[10px] text-slate-400 pt-3 border-t border-slate-100">
            <span>Starts: <?= date('d M Y', strtotime($o['starts_at'])) ?></span>
            <span><?= $o['ends_at'] ? 'Ends: '.date('d M Y', strtotime($o['ends_at'])) : 'No expiry' ?></span>
          </div>

          <div class="flex items-center gap-1 mt-3 pt-3 border-t border-slate-100">
            <button onclick="editOffer('<?= $o['encrypted_id'] ?>')" class="action-btn"><span class="material-icons text-[18px]">edit</span></button>
            <button onclick="deleteOffer('<?= $o['encrypted_id'] ?>', '<?= htmlspecialchars($o['name']) ?>')" class="action-btn danger"><span class="material-icons text-[18px]">delete_outline</span></button>
          </div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($offers)): ?>
        <div class="col-span-full text-center py-10">
          <span class="material-icons text-slate-300 text-[40px]">sell</span>
          <p class="text-slate-500 mt-2 font-medium">No offers created yet</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ========== COUPON MODAL ========== -->
<div class="modal-overlay" id="couponModal">
  <div class="modal-box max-w-lg mx-auto">
    <div class="p-5 border-b border-slate-200 flex items-center justify-between">
      <h3 class="font-heading text-lg font-bold text-slate-900" id="couponModalTitle">New Coupon</h3>
      <button onclick="closeModal('couponModal')" class="p-2 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition"><span class="material-icons">close</span></button>
    </div>
    <form onsubmit="event.preventDefault(); submitCoupon();">
      <div class="p-5 space-y-4">
        <input type="hidden" id="cEditId" value="">
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-xs font-semibold text-slate-600 mb-1">Coupon Code *</label>
            <input type="text" id="cCode" class="input-field mono text-xs uppercase" required placeholder="e.g. SAVE20"></div>
          <div><label class="block text-xs font-semibold text-slate-600 mb-1">Discount Type</label>
            <select id="cDiscountType" class="input-field text-xs"><option value="percentage">Percentage (%)</option><option value="flat">Flat (₹)</option></select></div>
        </div>
        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
          <input type="text" id="cDescription" class="input-field text-xs" placeholder="Short promotional description"></div>
        <div class="grid grid-cols-3 gap-3">
          <div><label class="block text-xs font-semibold text-slate-600 mb-1">Discount Value *</label>
            <input type="number" id="cDiscountValue" step="0.01" class="input-field text-xs mono" required placeholder="20"></div>
          <div><label class="block text-xs font-semibold text-slate-600 mb-1">Min Order (₹)</label>
            <input type="number" id="cMinOrder" step="0.01" class="input-field text-xs mono" placeholder="500"></div>
          <div><label class="block text-xs font-semibold text-slate-600 mb-1">Max Discount (₹)</label>
            <input type="number" id="cMaxDiscount" step="0.01" class="input-field text-xs mono" placeholder="200"></div>
        </div>
        <div class="grid grid-cols-3 gap-3">
          <div><label class="block text-xs font-semibold text-slate-600 mb-1">Usage Limit</label>
            <input type="number" id="cUsageLimit" class="input-field text-xs mono" placeholder="∞"></div>
          <div><label class="block text-xs font-semibold text-slate-600 mb-1">Per-User Limit</label>
            <input type="number" id="cPerUserLimit" class="input-field text-xs mono" value="1"></div>
          <div class="flex items-end pb-1"><label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" id="cIsActive" checked class="row-check"><span class="text-xs font-semibold text-slate-600">Active</span></label></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-xs font-semibold text-slate-600 mb-1">Starts At</label>
            <input type="datetime-local" id="cStartsAt" class="input-field text-xs"></div>
          <div><label class="block text-xs font-semibold text-slate-600 mb-1">Ends At</label>
            <input type="datetime-local" id="cEndsAt" class="input-field text-xs"></div>
        </div>
      </div>
      <div class="p-5 bg-slate-50 border-t border-slate-200 flex gap-3">
        <button type="submit" class="btn-primary flex-1 justify-center"><span class="material-icons text-[18px]">save</span> Save Coupon</button>
        <button type="button" onclick="closeModal('couponModal')" class="btn-secondary flex-1 justify-center">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- ========== OFFER MODAL ========== -->
<div class="modal-overlay" id="offerModal">
  <div class="modal-box max-w-lg mx-auto">
    <div class="p-5 border-b border-slate-200 flex items-center justify-between">
      <h3 class="font-heading text-lg font-bold text-slate-900" id="offerModalTitle">New Offer</h3>
      <button onclick="closeModal('offerModal')" class="p-2 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition"><span class="material-icons">close</span></button>
    </div>
    <form onsubmit="event.preventDefault(); submitOffer();">
      <div class="p-5 space-y-4">
        <input type="hidden" id="oEditId" value="">
        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Offer Name *</label>
          <input type="text" id="oName" class="input-field text-xs" required placeholder="e.g. Summer Sale 30% Off"></div>
        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Description</label>
          <textarea id="oDescription" rows="2" class="input-field text-xs resize-none" placeholder="Promotional offer description"></textarea></div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-xs font-semibold text-slate-600 mb-1">Offer Type</label>
            <select id="oOfferType" class="input-field text-xs"><option value="percentage">Percentage</option><option value="flat">Flat Amount</option><option value="buy_x_get_y">Buy X Get Y</option><option value="combo">Combo Deal</option></select></div>
          <div><label class="block text-xs font-semibold text-slate-600 mb-1">Discount Value *</label>
            <input type="number" id="oDiscountValue" step="0.01" class="input-field text-xs mono" required placeholder="30"></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-xs font-semibold text-slate-600 mb-1">Min Order (₹)</label>
            <input type="number" id="oMinOrder" step="0.01" class="input-field text-xs mono" placeholder="Optional"></div>
          <div><label class="block text-xs font-semibold text-slate-600 mb-1">Max Discount Cap (₹)</label>
            <input type="number" id="oMaxCap" step="0.01" class="input-field text-xs mono" placeholder="Optional"></div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div><label class="block text-xs font-semibold text-slate-600 mb-1">Starts At *</label>
            <input type="datetime-local" id="oStartsAt" class="input-field text-xs" required></div>
          <div><label class="block text-xs font-semibold text-slate-600 mb-1">Ends At</label>
            <input type="datetime-local" id="oEndsAt" class="input-field text-xs"></div>
        </div>
        <label class="flex items-center gap-2 cursor-pointer">
          <input type="checkbox" id="oIsActive" checked class="row-check"><span class="text-xs font-semibold text-slate-600">Active</span></label>
      </div>
      <div class="p-5 bg-slate-50 border-t border-slate-200 flex gap-3">
        <button type="submit" class="btn-primary flex-1 justify-center bg-cc-orange hover:bg-orange-600 shadow-cc-orange/20"><span class="material-icons text-[18px]">save</span> Save Offer</button>
        <button type="button" onclick="closeModal('offerModal')" class="btn-secondary flex-1 justify-center">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';
let searchTimeout = null;

function switchTab(tab) {
  const cPanel = document.getElementById('couponsPanel');
  const oPanel = document.getElementById('offersPanel');
  const cTab = document.getElementById('tabCoupons');
  const oTab = document.getElementById('tabOffers');

  if (tab === 'coupons') {
    cPanel.classList.remove('hidden'); oPanel.classList.add('hidden');
    cTab.className = 'px-4 py-2 rounded-lg text-sm font-semibold transition bg-white text-slate-800 shadow-sm';
    oTab.className = 'px-4 py-2 rounded-lg text-sm font-semibold transition text-slate-500 hover:text-slate-700';
  } else {
    oPanel.classList.remove('hidden'); cPanel.classList.add('hidden');
    oTab.className = 'px-4 py-2 rounded-lg text-sm font-semibold transition bg-white text-slate-800 shadow-sm';
    cTab.className = 'px-4 py-2 rounded-lg text-sm font-semibold transition text-slate-500 hover:text-slate-700';
  }
}

function closeModal(id) { document.getElementById(id).classList.remove('show'); }
function debounceCoupons() { clearTimeout(searchTimeout); searchTimeout = setTimeout(() => fetchCoupons(1), 300); }

async function fetchCoupons(page) {
  const search = document.getElementById('couponSearch').value;
  const status = document.getElementById('couponStatusFilter').value;
  try {
    const res = await fetch(`${BASE_URL}/admin/coupons?ajax=1&page=${page}&search=${encodeURIComponent(search)}&status=${encodeURIComponent(status)}`);
    const data = await res.json();
    if (data.success) { window.location.reload(); }
  } catch(e) { showToast('error', 'Error fetching coupons'); }
}

function openCouponModal() {
  document.getElementById('couponModalTitle').textContent = 'New Coupon';
  document.getElementById('cEditId').value = '';
  document.getElementById('cCode').value = '';
  document.getElementById('cDescription').value = '';
  document.getElementById('cDiscountType').value = 'percentage';
  document.getElementById('cDiscountValue').value = '';
  document.getElementById('cMinOrder').value = '';
  document.getElementById('cMaxDiscount').value = '';
  document.getElementById('cUsageLimit').value = '';
  document.getElementById('cPerUserLimit').value = '1';
  document.getElementById('cStartsAt').value = '';
  document.getElementById('cEndsAt').value = '';
  document.getElementById('cIsActive').checked = true;
  document.getElementById('couponModal').classList.add('show');
}

async function editCoupon(encryptedId) {
  try {
    const res = await fetch(`${BASE_URL}/admin/coupons/detail/${encryptedId}`);
    const data = await res.json();
    if (data.success && data.coupon) {
      const c = data.coupon;
      document.getElementById('couponModalTitle').textContent = 'Edit Coupon';
      document.getElementById('cEditId').value = c.encrypted_id;
      document.getElementById('cCode').value = c.code;
      document.getElementById('cDescription').value = c.description || '';
      document.getElementById('cDiscountType').value = c.discount_type;
      document.getElementById('cDiscountValue').value = c.discount_value;
      document.getElementById('cMinOrder').value = c.min_order_value || '';
      document.getElementById('cMaxDiscount').value = c.max_discount || '';
      document.getElementById('cUsageLimit').value = c.usage_limit || '';
      document.getElementById('cPerUserLimit').value = c.per_user_limit;
      document.getElementById('cStartsAt').value = c.starts_at ? c.starts_at.replace(' ', 'T').slice(0,16) : '';
      document.getElementById('cEndsAt').value = c.ends_at ? c.ends_at.replace(' ', 'T').slice(0,16) : '';
      document.getElementById('cIsActive').checked = parseInt(c.is_active) === 1;
      document.getElementById('couponModal').classList.add('show');
    }
  } catch(e) { showToast('error', 'Error loading coupon'); }
}

async function submitCoupon() {
  const editId = document.getElementById('cEditId').value;
  const isEdit = !!editId;
  const payload = {
    coupon_id: editId,
    code: document.getElementById('cCode').value,
    description: document.getElementById('cDescription').value,
    discount_type: document.getElementById('cDiscountType').value,
    discount_value: document.getElementById('cDiscountValue').value,
    min_order_value: document.getElementById('cMinOrder').value,
    max_discount: document.getElementById('cMaxDiscount').value,
    usage_limit: document.getElementById('cUsageLimit').value,
    per_user_limit: document.getElementById('cPerUserLimit').value,
    starts_at: document.getElementById('cStartsAt').value.replace('T', ' '),
    ends_at: document.getElementById('cEndsAt').value.replace('T', ' '),
    is_active: document.getElementById('cIsActive').checked ? 1 : 0
  };
  const endpoint = isEdit ? `${BASE_URL}/admin/coupons/update` : `${BASE_URL}/admin/coupons/store`;
  try {
    const res = await fetch(endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
    const data = await res.json();
    if (data.success) {
      showToast('success', data.message);
      closeModal('couponModal');
      setTimeout(() => window.location.reload(), 600);
    } else { showToast('error', data.message); }
  } catch(e) { showToast('error', 'Network error'); }
}

async function toggleCouponActive(encryptedId, el) {
  try {
    const res = await fetch(`${BASE_URL}/admin/coupons/toggle-active`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ coupon_id: encryptedId }) });
    const data = await res.json();
    if (data.success) { el.classList.toggle('on'); showToast('success', 'Coupon status toggled'); }
    else showToast('error', data.message);
  } catch(e) { showToast('error', 'Error toggling coupon'); }
}

async function deleteCoupon(encryptedId, code) {
  if (!confirm(`Delete coupon "${code}"?`)) return;
  try {
    const res = await fetch(`${BASE_URL}/admin/coupons/delete`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ coupon_id: encryptedId }) });
    const data = await res.json();
    if (data.success) { showToast('success', data.message); setTimeout(() => window.location.reload(), 600); }
    else showToast('error', data.message);
  } catch(e) { showToast('error', 'Network error'); }
}

// === OFFERS ===
function openOfferModal() {
  document.getElementById('offerModalTitle').textContent = 'New Offer';
  document.getElementById('oEditId').value = '';
  document.getElementById('oName').value = '';
  document.getElementById('oDescription').value = '';
  document.getElementById('oOfferType').value = 'percentage';
  document.getElementById('oDiscountValue').value = '';
  document.getElementById('oMinOrder').value = '';
  document.getElementById('oMaxCap').value = '';
  document.getElementById('oStartsAt').value = '';
  document.getElementById('oEndsAt').value = '';
  document.getElementById('oIsActive').checked = true;
  document.getElementById('offerModal').classList.add('show');
}

async function editOffer(encryptedId) {
  try {
    const res = await fetch(`${BASE_URL}/admin/coupons/offer-detail/${encryptedId}`);
    const data = await res.json();
    if (data.success && data.offer) {
      const o = data.offer;
      document.getElementById('offerModalTitle').textContent = 'Edit Offer';
      document.getElementById('oEditId').value = o.encrypted_id;
      document.getElementById('oName').value = o.name;
      document.getElementById('oDescription').value = o.description || '';
      document.getElementById('oOfferType').value = o.offer_type;
      document.getElementById('oDiscountValue').value = o.discount_value;
      document.getElementById('oMinOrder').value = o.min_order_value || '';
      document.getElementById('oMaxCap').value = o.max_discount_cap || '';
      document.getElementById('oStartsAt').value = o.starts_at ? o.starts_at.replace(' ', 'T').slice(0,16) : '';
      document.getElementById('oEndsAt').value = o.ends_at ? o.ends_at.replace(' ', 'T').slice(0,16) : '';
      document.getElementById('oIsActive').checked = parseInt(o.is_active) === 1;
      document.getElementById('offerModal').classList.add('show');
    }
  } catch(e) { showToast('error', 'Error loading offer'); }
}

async function submitOffer() {
  const editId = document.getElementById('oEditId').value;
  const isEdit = !!editId;
  const payload = {
    offer_id: editId,
    name: document.getElementById('oName').value,
    description: document.getElementById('oDescription').value,
    offer_type: document.getElementById('oOfferType').value,
    discount_value: document.getElementById('oDiscountValue').value,
    min_order_value: document.getElementById('oMinOrder').value,
    max_discount_cap: document.getElementById('oMaxCap').value,
    starts_at: document.getElementById('oStartsAt').value.replace('T', ' '),
    ends_at: document.getElementById('oEndsAt').value.replace('T', ' '),
    is_active: document.getElementById('oIsActive').checked ? 1 : 0
  };
  const endpoint = isEdit ? `${BASE_URL}/admin/coupons/offer-update` : `${BASE_URL}/admin/coupons/offer-store`;
  try {
    const res = await fetch(endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
    const data = await res.json();
    if (data.success) {
      showToast('success', data.message);
      closeModal('offerModal');
      setTimeout(() => window.location.reload(), 600);
    } else { showToast('error', data.message); }
  } catch(e) { showToast('error', 'Network error'); }
}

async function toggleOfferActive(encryptedId, el) {
  try {
    const res = await fetch(`${BASE_URL}/admin/coupons/offer-toggle-active`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ offer_id: encryptedId }) });
    const data = await res.json();
    if (data.success) { el.classList.toggle('on'); showToast('success', 'Offer status toggled'); }
    else showToast('error', data.message);
  } catch(e) { showToast('error', 'Error toggling offer'); }
}

async function deleteOffer(encryptedId, name) {
  if (!confirm(`Delete offer "${name}"?`)) return;
  try {
    const res = await fetch(`${BASE_URL}/admin/coupons/offer-delete`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ offer_id: encryptedId }) });
    const data = await res.json();
    if (data.success) { showToast('success', data.message); setTimeout(() => window.location.reload(), 600); }
    else showToast('error', data.message);
  } catch(e) { showToast('error', 'Network error'); }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
