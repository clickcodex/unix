<?php
$pageTitle = 'User Profile: ' . htmlspecialchars($user['name'] ?? 'User Profile');
$activeMenu = 'users';

require_once __DIR__ . '/../layouts/header.php';

$u = $user ?? [];
$initials = strtoupper(substr($u['name'] ?? 'U', 0, 2));
$isVer = (int)($u['is_verified'] ?? 0) === 1;
$isAct = (int)($u['is_active'] ?? 0) === 1;

$roleSlug = $u['role_slugs'][0] ?? 'customer';
$badgeClass = 'badge-slate';
if ($roleSlug === 'super_admin') $badgeClass = 'badge-purple';
elseif ($roleSlug === 'admin') $badgeClass = 'badge-blue';
elseif ($roleSlug === 'staff' || $roleSlug === 'manager') $badgeClass = 'badge-green';

$orders = $u['orders'] ?? [];
$addresses = $u['addresses'] ?? [];
$reviews = $u['reviews'] ?? [];
$auditTrail = $u['audit_trail'] ?? [];
$orderSummary = $u['order_summary'] ?? ['total_orders' => 0, 'lifetime_value' => 0];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Breadcrumbs & Back Navigation -->
  <div class="flex items-center justify-between">
    <div class="flex items-center gap-2 text-xs text-slate-500">
      <a href="<?= $baseUrl ?>/admin/users" class="text-slate-400 hover:text-cc-blue font-medium flex items-center gap-1">
        <span class="material-icons text-[16px]">arrow_back</span> Back to Users List
      </a>
      <span class="text-slate-300">/</span>
      <span class="text-slate-700 font-bold"><?= htmlspecialchars($u['name'] ?? 'Profile') ?></span>
    </div>
  </div>

  <!-- User Profile Header Card -->
  <div class="section-card p-6 bg-white flex flex-col md:flex-row md:items-center justify-between gap-6 border-l-4 border-l-cc-blue">
    <div class="flex items-start sm:items-center gap-4">
      <?php if (!empty($u['avatar_url'])): ?>
        <img src="<?= htmlspecialchars($u['avatar_url']) ?>" class="w-16 h-16 rounded-2xl object-cover border-2 border-slate-200 shadow-sm shrink-0">
      <?php else: ?>
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-cc-blue to-blue-700 text-white flex items-center justify-center font-extrabold text-xl shadow-md shrink-0">
          <?= $initials ?>
        </div>
      <?php endif; ?>

      <div class="space-y-1">
        <div class="flex flex-wrap items-center gap-2">
          <h1 class="font-heading text-xl sm:text-2xl font-bold text-slate-900"><?= htmlspecialchars($u['name']) ?></h1>
          <?php if ($isVer): ?>
            <span class="material-icons text-cc-blue text-[20px]" title="Verified Account">verified</span>
          <?php endif; ?>
          <span class="badge <?= $badgeClass ?> text-xs font-bold"><?= htmlspecialchars($u['primary_role'] ?? 'Customer') ?></span>
          <span class="badge <?= $isAct ? 'badge-green' : 'badge-red' ?> text-xs font-bold"><?= $isAct ? 'Active Account' : 'Account Disabled' ?></span>
        </div>

        <p class="text-xs text-slate-500 font-mono flex flex-wrap items-center gap-3">
          <span><strong class="text-slate-700">Email:</strong> <?= htmlspecialchars($u['email']) ?></span>
          <span>&bull;</span>
          <span><strong class="text-slate-700">Phone:</strong> <?= !empty($u['phone']) ? htmlspecialchars($u['phone']) : 'Not set' ?></span>
          <span>&bull;</span>
          <span><strong class="text-slate-700">UUID:</strong> <?= htmlspecialchars($u['uuid'] ?? 'N/A') ?></span>
        </p>

        <p class="text-[11px] text-slate-400">
          Registered on <?= date('M j, Y \a\t g:i A', strtotime($u['created_at'] ?? 'now')) ?>
        </p>
      </div>
    </div>

    <div class="flex items-center gap-2 shrink-0">
      <button onclick="toggleUserActivePage('<?= $u['encrypted_id'] ?>')" class="btn-secondary text-xs py-2 px-3">
        <span class="material-icons text-[16px]"><?= $isAct ? 'block' : 'check_circle' ?></span>
        <?= $isAct ? 'Disable Account' : 'Activate Account' ?>
      </button>
      <a href="<?= $baseUrl ?>/admin/users" class="btn-primary text-xs py-2 px-4 shadow-sm">
        <span class="material-icons text-[16px]">people</span> Manage All Users
      </a>
    </div>
  </div>

  <!-- KPI Metric Cards -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    <div class="kpi-card p-4">
      <span class="text-xs font-bold text-slate-400 uppercase">Lifetime Spend</span>
      <p class="font-heading text-2xl font-extrabold text-green-600 mt-1">₹<?= number_format($orderSummary['lifetime_value'] ?? 0, 2) ?></p>
      <span class="text-[11px] text-slate-400">Total completed transactions</span>
    </div>
    <div class="kpi-card p-4">
      <span class="text-xs font-bold text-slate-400 uppercase">Total Orders</span>
      <p class="font-heading text-2xl font-extrabold text-slate-900 mt-1"><?= number_format(count($orders)) ?></p>
      <span class="text-[11px] text-slate-400">Placed in store</span>
    </div>
    <div class="kpi-card p-4">
      <span class="text-xs font-bold text-slate-400 uppercase">Saved Addresses</span>
      <p class="font-heading text-2xl font-extrabold text-cc-blue mt-1"><?= number_format(count($addresses)) ?></p>
      <span class="text-[11px] text-slate-400">Delivery locations</span>
    </div>
    <div class="kpi-card p-4">
      <span class="text-xs font-bold text-slate-400 uppercase">Reviews Submitted</span>
      <p class="font-heading text-2xl font-extrabold text-cc-purple mt-1"><?= number_format(count($reviews)) ?></p>
      <span class="text-[11px] text-slate-400">Product feedback</span>
    </div>
  </div>

  <!-- Tabbed Detail Sections -->
  <div class="space-y-6">

    <!-- ORDER HISTORY SECTION -->
    <div class="section-card p-5">
      <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
        <div class="flex items-center gap-2">
          <span class="material-icons text-cc-blue text-[20px]">shopping_bag</span>
          <h3 class="font-heading text-base font-bold text-slate-900">Order History (<?= count($orders) ?>)</h3>
        </div>
      </div>

      <?php if (empty($orders)): ?>
        <div class="text-center py-8 text-slate-400 text-xs">
          <span class="material-icons text-3xl mb-1 text-slate-300">remove_shopping_cart</span>
          <p>No orders placed yet by this customer.</p>
        </div>
      <?php else: ?>
        <div class="overflow-x-auto">
          <table class="data-table">
            <thead>
              <tr>
                <th>Order Number</th>
                <th>Date</th>
                <th>Total Amount</th>
                <th>Payment Status</th>
                <th>Order Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($orders as $o): ?>
                <tr>
                  <td class="font-bold text-slate-900 mono text-xs"><?= htmlspecialchars($o['order_number']) ?></td>
                  <td class="text-slate-500 text-xs"><?= date('M j, Y g:i A', strtotime($o['created_at'])) ?></td>
                  <td class="font-extrabold text-slate-900 mono text-xs">₹<?= number_format($o['grand_total'], 2) ?></td>
                  <td>
                    <span class="badge <?= $o['payment_status'] === 'paid' ? 'badge-green' : 'badge-slate' ?> uppercase text-[10px]">
                      <?= htmlspecialchars($o['payment_status'] ?? 'pending') ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge badge-blue uppercase text-[10px]"><?= htmlspecialchars($o['status']) ?></span>
                  </td>
                  <td>
                    <div class="flex items-center gap-2">
                      <button onclick="openOrderQuickModal('<?= $o['encrypted_id'] ?>')" class="btn-primary text-xs py-1 px-2.5 shadow-sm">
                        <span class="material-icons text-[14px]">visibility</span> Quick Popup Details
                      </button>
                      <a href="<?= $baseUrl ?>/admin/orders?search=<?= urlencode($o['order_number']) ?>" class="btn-secondary text-xs py-1 px-2.5">Full Order Page</a>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>

    <!-- SAVED ADDRESSES SECTION -->
    <div class="section-card p-5">
      <div class="flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
        <span class="material-icons text-emerald-600 text-[20px]">location_on</span>
        <h3 class="font-heading text-base font-bold text-slate-900">Saved Shipping &amp; Delivery Addresses (<?= count($addresses) ?>)</h3>
      </div>

      <?php if (empty($addresses)): ?>
        <div class="text-center py-6 text-slate-400 text-xs">No saved delivery addresses.</div>
      <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <?php foreach ($addresses as $a): ?>
            <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-1">
              <div class="flex items-center justify-between">
                <span class="font-bold text-slate-800 text-xs"><?= htmlspecialchars($a['recipient_name'] ?? $u['name']) ?></span>
                <span class="badge badge-slate text-[10px] uppercase font-bold"><?= htmlspecialchars($a['label'] ?? 'Home') ?></span>
              </div>
              <p class="text-xs text-slate-600"><?= htmlspecialchars($a['address_line1']) ?></p>
              <?php if (!empty($a['address_line2'])): ?>
                <p class="text-xs text-slate-600"><?= htmlspecialchars($a['address_line2']) ?></p>
              <?php endif; ?>
              <p class="text-xs text-slate-500 font-mono"><?= htmlspecialchars($a['city']) ?>, <?= htmlspecialchars($a['state']) ?> - <?= htmlspecialchars($a['postal_code']) ?></p>
              <p class="text-[11px] text-slate-400 mono pt-1">Phone: <?= htmlspecialchars($a['phone'] ?? 'N/A') ?></p>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- PRODUCT REVIEWS & AUDIT TRAIL GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

      <!-- Product Reviews -->
      <div class="section-card p-5">
        <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
          <div class="flex items-center gap-2">
            <span class="material-icons text-cc-yellow text-[20px]">rate_review</span>
            <h3 class="font-heading text-base font-bold text-slate-900">Submitted Reviews (<?= count($reviews) ?>)</h3>
          </div>
        </div>

        <?php if (empty($reviews)): ?>
          <div class="text-center py-6 text-slate-400 text-xs">No reviews submitted by this user.</div>
        <?php else: ?>
          <div class="space-y-3">
            <?php foreach ($reviews as $r): ?>
              <?php
              $revText = $r['body'] ?? $r['comment'] ?? $r['review'] ?? $r['title'] ?? '';
              $revTitle = !empty($r['title']) ? $r['title'] : ($r['product_name'] ?? 'Product');
              ?>
              <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200 text-xs space-y-2">
                <div class="flex items-center justify-between">
                  <span class="font-bold text-slate-800 line-clamp-1"><?= htmlspecialchars($revTitle) ?></span>
                  <div class="flex items-center text-cc-yellow text-[14px]">
                    <?php for ($i=1;$i<=5;$i++): ?>
                      <span class="material-icons text-[14px]"><?= $i <= (int)$r['rating'] ? 'star' : 'star_border' ?></span>
                    <?php endfor; ?>
                  </div>
                </div>
                <p class="text-slate-600 text-xs italic">"<?= htmlspecialchars($revText) ?>"</p>
                <div class="flex items-center justify-between pt-1 border-t border-slate-200/60">
                  <span class="text-[10px] text-slate-400"><?= date('M j, Y', strtotime($r['created_at'])) ?></span>
                  <button onclick='openReviewQuickModal(<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="btn-secondary text-[11px] py-0.5 px-2 font-semibold">
                    <span class="material-icons text-[13px]">info</span> Review Popup Details
                  </button>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

      <!-- Audit Trail Log -->
      <div class="section-card p-5">
        <div class="flex items-center gap-2 mb-4 border-b border-slate-100 pb-3">
          <span class="material-icons text-cc-purple text-[20px]">history</span>
          <h3 class="font-heading text-base font-bold text-slate-900">Account Activity Log (<?= count($auditTrail) ?>)</h3>
        </div>

        <?php if (empty($auditTrail)): ?>
          <div class="text-center py-6 text-slate-400 text-xs">No activity recorded for this user.</div>
        <?php else: ?>
          <div class="space-y-2">
            <?php foreach ($auditTrail as $ad): ?>
              <div class="p-2.5 bg-slate-50 rounded-lg border border-slate-100 text-xs flex items-center justify-between">
                <div>
                  <p class="font-bold text-slate-800 mono"><?= htmlspecialchars($ad['event']) ?></p>
                  <p class="text-[10px] text-slate-400"><?= date('M j, Y g:i:s A', strtotime($ad['created_at'])) ?></p>
                </div>
                <span class="mono text-[10px] text-slate-400"><?= htmlspecialchars($ad['ip_address'] ?? '127.0.0.1') ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>

    </div>

  </div>

</div>

<!-- ORDER QUICK POPUP MODAL -->
<div id="orderQuickModal" class="modal-backdrop hidden">
  <div class="modal-box max-w-2xl w-full p-6">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
      <div class="flex items-center gap-2">
        <span class="material-icons text-cc-blue text-[22px]">shopping_bag</span>
        <h3 class="font-heading font-bold text-base text-slate-900" id="orderModalTitle">Order Quick Details</h3>
      </div>
      <button onclick="closeOrderQuickModal()" class="text-slate-400 hover:text-slate-600"><span class="material-icons">close</span></button>
    </div>

    <div id="orderModalBody" class="space-y-4 text-xs">
      <div class="text-center py-8 text-slate-400">Fetching order details...</div>
    </div>

    <div class="mt-6 flex justify-end">
      <button onclick="closeOrderQuickModal()" class="btn-secondary text-xs px-4 py-2">Close Popup</button>
    </div>
  </div>
</div>

<!-- REVIEW QUICK POPUP MODAL -->
<div id="reviewQuickModal" class="modal-backdrop hidden">
  <div class="modal-box max-w-md w-full p-6">
    <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
      <div class="flex items-center gap-2">
        <span class="material-icons text-cc-yellow text-[22px]">rate_review</span>
        <h3 class="font-heading font-bold text-base text-slate-900">Product Review Quick Details</h3>
      </div>
      <button onclick="closeReviewQuickModal()" class="text-slate-400 hover:text-slate-600"><span class="material-icons">close</span></button>
    </div>

    <div id="reviewModalBody" class="space-y-4 text-xs">
      <!-- Dynamic Payload -->
    </div>

    <div class="mt-6 flex items-center justify-between pt-3 border-t border-slate-100" id="reviewModalActions">
      <button onclick="closeReviewQuickModal()" class="btn-secondary text-xs px-4 py-2">Close Popup</button>
    </div>
  </div>
</div>

<script>
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';

async function toggleUserActivePage(encryptedId) {
  try {
    const res = await fetch(`${BASE_URL}/admin/users/toggle-active`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: encryptedId })
    });
    const data = await res.json();
    if (data.success) {
      window.location.reload();
    } else {
      alert(data.message || 'Failed to update account status');
    }
  } catch(e) {
    alert('Network error');
  }
}

// ORDER QUICK POPUP MODAL LOGIC
async function openOrderQuickModal(encryptedId) {
  const modal = document.getElementById('orderQuickModal');
  const body = document.getElementById('orderModalBody');
  body.innerHTML = '<div class="text-center py-8 text-slate-400">Loading order items &amp; totals...</div>';
  modal.classList.remove('hidden');
  modal.classList.add('flex');

  try {
    const res = await fetch(`${BASE_URL}/admin/orders/detail/${encryptedId}`);
    const data = await res.json();
    if (data.success) {
      const o = data.order;
      const items = o.items || [];

      let itemsHtml = '';
      if (items.length > 0) {
        itemsHtml = items.map(item => {
          const pName = item.product_name || item.name || 'Product';
          const pSku = item.product_sku || '';
          const uPrice = parseFloat(item.unit_price || item.sale_price || 0);
          const lTotal = parseFloat(item.line_total || item.total_price || item.subtotal || (uPrice * (item.quantity || 1)));
          const imgHtml = item.image_url
            ? `<img src="${escapeHtml(item.image_url)}" alt="${escapeHtml(pName)}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 shrink-0" onerror="this.onerror=null;this.parentNode.innerHTML='<div class=\\\'w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 shrink-0\\\'><span class=\\\'material-icons text-[16px]\\\'>inventory_2</span></div>';">`
            : `<div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 shrink-0"><span class="material-icons text-[16px]">inventory_2</span></div>`;

          return `
            <tr class="border-b border-slate-100 hover:bg-slate-50/60 transition">
              <td class="py-2.5">
                <div class="flex items-center gap-2.5">
                  ${imgHtml}
                  <div>
                    <p class="font-semibold text-slate-800 leading-snug">${escapeHtml(pName)}</p>
                    ${pSku ? `<p class="text-[10px] text-slate-400 mono">${escapeHtml(pSku)}</p>` : ''}
                  </div>
                </div>
              </td>
              <td class="py-2.5 text-center font-mono">${item.quantity || 1}</td>
              <td class="py-2.5 text-right font-mono">₹${uPrice.toFixed(2)}</td>
              <td class="py-2.5 text-right font-bold font-mono">₹${lTotal.toFixed(2)}</td>
            </tr>
          `;
        }).join('');
      } else {
        itemsHtml = `
          <tr>
            <td colspan="4" class="py-6 text-center text-slate-400 text-xs">
              <span class="material-icons text-xl text-slate-300 block mb-1">remove_shopping_cart</span>
              No items recorded for this order.
            </td>
          </tr>
        `;
      }

      body.innerHTML = `
        <div class="grid grid-cols-2 gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl">
          <div>
            <p class="text-[10px] text-slate-400 uppercase font-bold">Order Number</p>
            <p class="font-bold text-slate-900 mono text-sm">${escapeHtml(o.order_number)}</p>
            <p class="text-[10px] text-slate-400 mt-1">${new Date(o.created_at).toLocaleString()}</p>
          </div>
          <div class="text-right">
            <span class="badge ${o.payment_status === 'paid' ? 'badge-green' : 'badge-slate'} uppercase text-[10px]">${escapeHtml(o.payment_status || 'pending')}</span>
            <span class="badge badge-blue uppercase text-[10px] ml-1">${escapeHtml(o.status)}</span>
            <p class="font-extrabold text-slate-900 text-base mono mt-2">₹${parseFloat(o.grand_total).toFixed(2)}</p>
          </div>
        </div>

        <div>
          <h4 class="font-bold text-slate-800 mb-2">Order Items (${items.length})</h4>
          <table class="w-full text-xs">
            <thead>
              <tr class="text-slate-400 font-bold border-b border-slate-200 uppercase text-[10px]">
                <th class="text-left py-1.5">Item Name</th>
                <th class="text-center py-1.5">Qty</th>
                <th class="text-right py-1.5">Price</th>
                <th class="text-right py-1.5">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              ${itemsHtml.length > 0 ? itemsHtml : '<tr><td colspan="4" class="text-center py-4 text-slate-400">No item details available.</td></tr>'}
            </tbody>
          </table>
        </div>

        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs space-y-1">
          <p class="font-bold text-slate-800">Shipping Details</p>
          <p class="text-slate-600">${escapeHtml(o.shipping_name || '')} &middot; ${escapeHtml(o.shipping_phone || '')}</p>
          <p class="text-slate-500">${escapeHtml(o.shipping_address || 'Standard Address')}</p>
        </div>
      `;
    } else {
      body.innerHTML = `<div class="text-center py-6 text-red-500">${escapeHtml(data.message || 'Error loading order')}</div>`;
    }
  } catch(e) {
    body.innerHTML = '<div class="text-center py-6 text-red-500">Failed to connect to order server.</div>';
  }
}

function closeOrderQuickModal() {
  const modal = document.getElementById('orderQuickModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

// REVIEW QUICK POPUP MODAL LOGIC
function openReviewQuickModal(review) {
  const modal = document.getElementById('reviewQuickModal');
  const body = document.getElementById('reviewModalBody');
  const actions = document.getElementById('reviewModalActions');

  let stars = '';
  for (let i = 1; i <= 5; i++) {
    stars += `<span class="material-icons text-[18px] ${i <= parseInt(review.rating) ? 'text-cc-yellow' : 'text-slate-200'}">star</span>`;
  }

  const isApproved = (review.status === 'approved' || parseInt(review.is_approved) === 1);
  const commentText = review.body || review.comment || review.title || 'No text provided.';
  const reviewTitle = review.title || review.product_name || 'Product Review';
  const newStatusTarget = isApproved ? 'rejected' : 'approved';

  body.innerHTML = `
    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1">
      <span class="text-[10px] text-slate-400 uppercase font-bold">Target Product / Review Title</span>
      <h4 class="font-bold text-slate-900 text-sm">${escapeHtml(reviewTitle)}</h4>
    </div>

    <div>
      <span class="text-[10px] text-slate-400 uppercase font-bold">Customer Rating</span>
      <div class="flex items-center gap-1 mt-0.5">${stars}</div>
    </div>

    <div>
      <span class="text-[10px] text-slate-400 uppercase font-bold">Review Comment Body</span>
      <p class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-slate-700 italic mt-1">"${escapeHtml(commentText)}"</p>
    </div>

    <div class="flex items-center justify-between text-slate-500">
      <span>Status: <strong class="${isApproved ? 'text-green-600' : 'text-amber-600'}">${isApproved ? 'Approved' : 'Pending / Rejected'}</strong></span>
      <span>${new Date(review.created_at).toLocaleDateString()}</span>
    </div>
  `;

  actions.innerHTML = `
    <button onclick="closeReviewQuickModal()" class="btn-secondary text-xs px-4 py-2">Close Popup</button>
    <button onclick="updateReviewStatusQuick('${review.encrypted_id || review.id}', '${newStatusTarget}')" class="${isApproved ? 'bg-amber-500 text-white hover:bg-amber-600' : 'btn-primary'} text-xs px-4 py-2 rounded-xl font-bold transition">
      ${isApproved ? 'Reject Review' : 'Approve Review'}
    </button>
  `;

  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function closeReviewQuickModal() {
  const modal = document.getElementById('reviewQuickModal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

async function updateReviewStatusQuick(id, newStatus) {
  try {
    const res = await fetch(`${BASE_URL}/admin/reviews/update-status`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ review_id: id, status: newStatus })
    });
    const data = await res.json();
    if (data.success) {
      closeReviewQuickModal();
      window.location.reload();
    } else {
      alert(data.message || 'Failed to update review status');
    }
  } catch(e) {
    alert('Error updating review status');
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
