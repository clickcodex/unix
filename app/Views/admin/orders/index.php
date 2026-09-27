<?php
$pageTitle = 'Orders Manager';
$activeMenu = 'orders';

require_once __DIR__ . '/../layouts/header.php';

$orders = $orderData['orders'] ?? [];
$totalCount = $orderData['totalCount'] ?? 0;
$totalPages = $orderData['totalPages'] ?? 1;
$currentPage = $orderData['currentPage'] ?? 1;

$kpiTotal = $kpiData['totalOrders'] ?? 0;
$kpiToday = $kpiData['todayOrders'] ?? 0;
$kpiRevenue = $kpiData['totalRevenue'] ?? 0;
$kpiPending = $kpiData['pendingCount'] ?? 0;
$kpiUnpaid = $kpiData['unpaidCount'] ?? 0;
$kpiReturns = $kpiData['returnsCount'] ?? 0;

$statusCounts = $statusCounts ?? [];
$totalStatusSum = array_sum($statusCounts) ?: 1;
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Header & Actions -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">Orders Manager</h1>
      <p class="text-slate-500 text-sm mt-1">Manage, fulfill, track, and filter customer order queue</p>
    </div>
    <div class="flex items-center gap-2.5 flex-wrap">
      <a href="<?= $baseUrl ?>/admin/orders/export" id="exportBtn" class="btn-secondary text-sm" target="_blank">
        <span class="material-icons text-[18px]">download</span> Export CSV
      </a>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4">
    <div class="kpi-card p-4">
      <div class="flex items-start justify-between mb-2">
        <div>
          <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Total Orders</p>
          <p class="font-heading text-xl font-extrabold text-slate-900 mt-0.5" id="kpiTotal"><?= number_format($kpiTotal) ?></p>
        </div>
        <div class="w-9 h-9 rounded-lg bg-cc-blue/10 flex items-center justify-center">
          <span class="material-icons text-cc-blue text-[18px]">shopping_bag</span>
        </div>
      </div>
      <div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:#2D82FF;border-radius:0 0 16px 16px"></div>
    </div>

    <div class="kpi-card p-4">
      <div class="flex items-start justify-between mb-2">
        <div>
          <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Today's Orders</p>
          <p class="font-heading text-xl font-extrabold text-cc-orange mt-0.5" id="kpiToday"><?= number_format($kpiToday) ?></p>
        </div>
        <div class="w-9 h-9 rounded-lg bg-cc-orange/10 flex items-center justify-center">
          <span class="material-icons text-cc-orange text-[18px]">today</span>
        </div>
      </div>
      <div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:#FF5100;border-radius:0 0 16px 16px"></div>
    </div>

    <div class="kpi-card p-4">
      <div class="flex items-start justify-between mb-2">
        <div>
          <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Total Revenue</p>
          <p class="font-heading text-xl font-extrabold text-green-600 mt-0.5" id="kpiRevenue">₹<?= number_format($kpiRevenue, 2) ?></p>
        </div>
        <div class="w-9 h-9 rounded-lg bg-green-500/10 flex items-center justify-center">
          <span class="material-icons text-green-600 text-[18px]">account_balance_wallet</span>
        </div>
      </div>
      <div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:#22C55E;border-radius:0 0 16px 16px"></div>
    </div>

    <div class="kpi-card p-4">
      <div class="flex items-start justify-between mb-2">
        <div>
          <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Pending Orders</p>
          <p class="font-heading text-xl font-extrabold text-yellow-600 mt-0.5" id="kpiPending"><?= number_format($kpiPending) ?></p>
        </div>
        <div class="w-9 h-9 rounded-lg bg-yellow-500/10 flex items-center justify-center">
          <span class="material-icons text-yellow-600 text-[18px]">schedule</span>
        </div>
      </div>
      <div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:#EAB308;border-radius:0 0 16px 16px"></div>
    </div>

    <div class="kpi-card p-4">
      <div class="flex items-start justify-between mb-2">
        <div>
          <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Unpaid Orders</p>
          <p class="font-heading text-xl font-extrabold text-red-500 mt-0.5" id="kpiUnpaid"><?= number_format($kpiUnpaid) ?></p>
        </div>
        <div class="w-9 h-9 rounded-lg bg-red-500/10 flex items-center justify-center">
          <span class="material-icons text-red-500 text-[18px]">money_off</span>
        </div>
      </div>
      <div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:#EF4444;border-radius:0 0 16px 16px"></div>
    </div>

    <div class="kpi-card p-4">
      <div class="flex items-start justify-between mb-2">
        <div>
          <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Returns / Refund</p>
          <p class="font-heading text-xl font-extrabold text-cc-purple mt-0.5" id="kpiReturns"><?= number_format($kpiReturns) ?></p>
        </div>
        <div class="w-9 h-9 rounded-lg bg-cc-purple/10 flex items-center justify-center">
          <span class="material-icons text-cc-purple text-[18px]">assignment_return</span>
        </div>
      </div>
      <div style="position:absolute;bottom:0;left:0;right:0;height:3px;background:#8C30F5;border-radius:0 0 16px 16px"></div>
    </div>
  </div>

  <!-- Status Distribution -->
  <div class="section-card p-5 sm:p-6">
    <div class="flex items-center gap-3 mb-4">
      <span class="material-icons text-cc-blue text-[22px]">donut_large</span>
      <div>
        <h2 class="font-heading text-lg font-bold text-slate-900">Order Status Queue</h2>
        <p class="text-xs text-slate-500">Filter queue by order fulfillment stage</p>
      </div>
    </div>
    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-9 gap-2.5" id="statusGrid">
      <?php
      $stConf = [
        'pending' => ['label' => 'Pending', 'color' => '#EAB308', 'bg' => '#FEF3C7', 'icon' => 'schedule'],
        'confirmed' => ['label' => 'Confirmed', 'color' => '#2D82FF', 'bg' => '#DBEAFE', 'icon' => 'check_circle_outline'],
        'processing' => ['label' => 'Processing', 'color' => '#06B6D4', 'bg' => '#ECFEFF', 'icon' => 'autorenew'],
        'shipped' => ['label' => 'Shipped', 'color' => '#6366F1', 'bg' => '#E0E7FF', 'icon' => 'local_shipping'],
        'delivered' => ['label' => 'Delivered', 'color' => '#22C55E', 'bg' => '#DCFCE7', 'icon' => 'done_all'],
        'cancelled' => ['label' => 'Cancelled', 'color' => '#EF4444', 'bg' => '#FFE4E6', 'icon' => 'cancel'],
        'return_requested' => ['label' => 'Return Req.', 'color' => '#F97316', 'bg' => '#FFF7ED', 'icon' => 'assignment_return'],
        'returned' => ['label' => 'Returned', 'color' => '#D97706', 'bg' => '#FEF3C7', 'icon' => 'undo'],
        'refunded' => ['label' => 'Refunded', 'color' => '#94A3B8', 'bg' => '#F1F5F9', 'icon' => 'money_off']
      ];
      foreach ($stConf as $stKey => $conf):
        $cnt = $statusCounts[$stKey] ?? 0;
        $pct = round(($cnt / $totalStatusSum) * 100);
      ?>
      <div class="rounded-xl p-3 text-center cursor-pointer hover:shadow-md transition" 
           style="background:<?= $conf['bg'] ?>;border:1.5px solid <?= $conf['color'] ?>30" 
           onclick="filterByStatus('<?= $stKey ?>')">
        <span class="material-icons text-[20px]" style="color:<?= $conf['color'] ?>"><?= $conf['icon'] ?></span>
        <p class="text-lg font-bold font-heading mt-1" style="color:<?= $conf['color'] ?>"><?= number_format($cnt) ?></p>
        <p class="text-[10px] font-semibold" style="color:<?= $conf['color'] ?>"><?= $conf['label'] ?></p>
        <p class="text-[9px] mono mt-0.5" style="color:<?= $conf['color'] ?>80"><?= $pct ?>%</p>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="mt-4 flex rounded-full overflow-hidden h-3" id="statusBar">
      <?php
      foreach ($stConf as $stKey => $conf):
        $cnt = $statusCounts[$stKey] ?? 0;
        $pct = round(($cnt / $totalStatusSum) * 100, 1);
        if ($pct > 0):
      ?>
        <div style="width:<?= $pct ?>%;background:<?= $conf['color'] ?>" title="<?= $conf['label'] ?>: <?= $cnt ?> (<?= $pct ?>%)"></div>
      <?php
        endif;
      endforeach;
      ?>
    </div>
  </div>

  <!-- Filters & Orders Data Table -->
  <div class="section-card">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
      <div class="flex items-center gap-3">
        <span class="material-icons text-cc-orange text-[22px]">shopping_bag</span>
        <div>
          <h2 class="font-heading text-lg font-bold text-slate-900">All Orders</h2>
          <p class="text-xs text-slate-500">Filter, search, and update order fulfillment status</p>
        </div>
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <select class="status-select text-xs" id="statusFilter" onchange="fetchOrders(1)">
          <option value="all">All Status</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="processing">Processing</option>
          <option value="shipped">Shipped</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
          <option value="return_requested">Return Req.</option>
          <option value="returned">Returned</option>
          <option value="refunded">Refunded</option>
        </select>
        <select class="status-select text-xs" id="paymentFilter" onchange="fetchOrders(1)">
          <option value="all">All Payment</option>
          <option value="paid">Paid</option>
          <option value="unpaid">Unpaid</option>
          <option value="refunded">Refunded</option>
          <option value="partially_refunded">Partial Refund</option>
        </select>
        <input type="date" class="status-select text-xs" id="dateFilter" onchange="fetchOrders(1)" style="padding-right:10px">
        <input type="text" placeholder="Search order #, customer..." class="input-field max-w-[200px] text-xs py-1.5" id="searchFilter" oninput="debounceFetch()">
      </div>
    </div>

    <!-- Table Container -->
    <div class="overflow-x-auto">
      <table class="data-table">
        <thead>
          <tr>
            <th>Order Number</th>
            <th>Customer</th>
            <th>Items</th>
            <th>Total Amount</th>
            <th>Payment Status</th>
            <th>Order Status</th>
            <th>Placed Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="ordersBody">
          <?php if (empty($orders)): ?>
          <tr>
            <td colspan="8" class="text-center py-12">
              <span class="material-icons text-slate-300 text-[40px]">inbox</span>
              <p class="text-slate-500 mt-2 font-medium">No orders found</p>
            </td>
          </tr>
          <?php else: ?>
            <?php foreach ($orders as $o): ?>
            <tr>
              <td>
                <p class="font-semibold text-slate-800 mono text-xs"><?= htmlspecialchars($o['order_number']) ?></p>
              </td>
              <td>
                <div class="flex items-center gap-2.5">
                  <div class="w-8 h-8 rounded-full bg-cc-blue/10 flex items-center justify-center text-cc-blue text-[10px] font-bold shrink-0">
                    <?= $o['user_initials'] ?>
                  </div>
                  <div class="min-w-0">
                    <p class="font-medium text-slate-700 text-sm truncate max-w-[140px]"><?= htmlspecialchars($o['shipping_name']) ?></p>
                    <p class="text-[10px] text-slate-400 mono"><?= htmlspecialchars($o['shipping_phone']) ?></p>
                  </div>
                </div>
              </td>
              <td>
                <p class="text-sm text-slate-600 truncate max-w-[180px]"><?= htmlspecialchars($o['items_summary'] ?: 'Products') ?></p>
              </td>
              <td>
                <p class="font-semibold text-slate-800">₹<?= number_format($o['total_amount'], 2) ?></p>
                <?php if ($o['discount_amount'] > 0): ?>
                  <p class="text-[10px] text-green-600 mono">-₹<?= number_format($o['discount_amount'], 2) ?></p>
                <?php endif; ?>
              </td>
              <td>
                <?php
                $pBadge = [
                  'paid' => 'badge-green',
                  'unpaid' => 'badge-slate',
                  'refunded' => 'badge-red',
                  'partially_refunded' => 'badge-orange'
                ][$o['payment_status']] ?? 'badge-slate';
                ?>
                <span class="badge <?= $pBadge ?>"><?= htmlspecialchars($o['payment_status']) ?></span>
              </td>
              <td>
                <?php
                $sBadge = [
                  'delivered' => 'badge-green',
                  'shipped' => 'badge-blue',
                  'processing' => 'badge-cyan',
                  'confirmed' => 'badge-blue',
                  'pending' => 'badge-yellow',
                  'cancelled' => 'badge-red',
                  'return_requested' => 'badge-orange',
                  'returned' => 'badge-yellow',
                  'refunded' => 'badge-slate'
                ][$o['status']] ?? 'badge-slate';
                ?>
                <span class="badge <?= $sBadge ?>"><?= htmlspecialchars($o['status']) ?></span>
              </td>
              <td>
                <p class="text-sm text-slate-600"><?= date('d M Y', strtotime($o['placed_at'])) ?></p>
                <p class="text-[10px] text-slate-400"><?= date('h:i A', strtotime($o['placed_at'])) ?></p>
              </td>
              <td>
                <div class="flex items-center gap-1">
                  <!-- Uses Encrypted ID for detail view -->
                  <button class="btn-icon" style="width:30px;height:30px" onclick="viewOrderDetail('<?= $o['encrypted_id'] ?>')" title="View Details">
                    <span class="material-icons text-[16px]">visibility</span>
                  </button>
                  <!-- Uses Encrypted ID for status change -->
                  <button class="btn-icon" style="width:30px;height:30px" onclick="openStatusModal('<?= $o['encrypted_id'] ?>', '<?= htmlspecialchars($o['order_number']) ?>', '<?= $o['status'] ?>')" title="Change Status">
                    <span class="material-icons text-[16px]">swap_horiz</span>
                  </button>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination Footer -->
    <div class="flex items-center justify-between px-5 sm:px-6 py-3 border-t border-slate-100">
      <p class="text-xs text-slate-500" id="resultCount">Showing page <?= $currentPage ?> of <?= $totalPages ?> (<?= $totalCount ?> total orders)</p>
      <div class="flex items-center gap-1" id="pagination">
        <?php if ($totalPages > 1): ?>
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <button class="btn-icon <?= $p === $currentPage ? 'bg-cc-blue text-white border-cc-blue' : 'bg-white text-slate-600 border-slate-200' ?>" 
                    style="width:32px;height:32px;font-size:12px;font-weight:700" 
                    onclick="fetchOrders(<?= $p ?>)"><?= $p ?></button>
          <?php endfor; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ================= MODAL: ORDER DETAIL (Encrypted ID Supported) ================= -->
<div class="modal-backdrop" id="detailModal">
  <div class="modal-panel w-full max-w-2xl mx-4" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
      <h3 class="font-heading text-lg font-bold text-slate-900">Order Details</h3>
      <button class="btn-icon" onclick="closeModal('detailModal')"><span class="material-icons text-[20px]">close</span></button>
    </div>
    <div class="p-6" id="detailContent">
      <div class="text-center py-8 text-slate-400">Loading order details...</div>
    </div>
  </div>
</div>

<!-- ================= MODAL: STATUS CHANGE (Encrypted ID Supported) ================= -->
<div class="modal-backdrop" id="statusModal">
  <div class="modal-panel w-full max-w-sm mx-4" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
      <h3 class="font-heading text-lg font-bold text-slate-900">Change Status</h3>
      <button class="btn-icon" onclick="closeModal('statusModal')"><span class="material-icons text-[20px]">close</span></button>
    </div>
    <div class="p-6 space-y-4">
      <p class="text-sm text-slate-500">Order: <span class="font-bold text-slate-800 mono" id="statusOrderNum"></span></p>
      <input type="hidden" id="statusEncryptedId" value="">
      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">New Status</label>
        <select class="input-field" id="newStatus">
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="processing">Processing</option>
          <option value="shipped">Shipped</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
          <option value="return_requested">Return Requested</option>
          <option value="returned">Returned</option>
          <option value="refunded">Refunded</option>
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Status Note</label>
        <textarea class="input-field" rows="2" id="statusNote" placeholder="Optional note for status change..."></textarea>
      </div>
    </div>
    <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl">
      <button type="button" class="btn-secondary" onclick="closeModal('statusModal')">Cancel</button>
      <button type="button" class="btn-primary" id="statusSubmitBtn" onclick="submitStatusUpdate()">Update Status</button>
    </div>
  </div>
</div>

<script>
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';
let searchTimeout;

function openModal(id) { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

document.querySelectorAll('.modal-backdrop').forEach(m => m.addEventListener('click', () => closeModal(m.id)));

function filterByStatus(st) {
  document.getElementById('statusFilter').value = st;
  fetchOrders(1);
}

function debounceFetch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => fetchOrders(1), 300);
}

async function fetchOrders(page = 1) {
  const status = document.getElementById('statusFilter').value;
  const paymentStatus = document.getElementById('paymentFilter').value;
  const date = document.getElementById('dateFilter').value;
  const search = document.getElementById('searchFilter').value;

  const url = `${BASE_URL}/admin/orders?ajax=1&page=${page}&status=${encodeURIComponent(status)}&payment_status=${encodeURIComponent(paymentStatus)}&date=${encodeURIComponent(date)}&search=${encodeURIComponent(search)}`;

  try {
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    if (data.success) {
      renderOrdersTable(data.data);
      renderPagination(data.pagination);
      updateExportLink(status, paymentStatus, date, search);
    }
  } catch(e) {
    console.error('Error fetching orders:', e);
  }
}

function updateExportLink(status, payment, date, search) {
  const btn = document.getElementById('exportBtn');
  if (btn) {
    btn.href = `${BASE_URL}/admin/orders/export?status=${encodeURIComponent(status)}&payment_status=${encodeURIComponent(payment)}&date=${encodeURIComponent(date)}&search=${encodeURIComponent(search)}`;
  }
}

function renderOrdersTable(orders) {
  const tbody = document.getElementById('ordersBody');
  if (!orders || orders.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-12"><span class="material-icons text-slate-300 text-[40px]">inbox</span><p class="text-slate-500 mt-2 font-medium">No orders found</p></td></tr>`;
    return;
  }

  let html = '';
  orders.forEach(o => {
    const pBadge = { paid: 'badge-green', unpaid: 'badge-slate', refunded: 'badge-red', partially_refunded: 'badge-orange' }[o.payment_status] || 'badge-slate';
    const sBadge = { delivered: 'badge-green', shipped: 'badge-blue', processing: 'badge-cyan', confirmed: 'badge-blue', pending: 'badge-yellow', cancelled: 'badge-red', return_requested: 'badge-orange', returned: 'badge-yellow', refunded: 'badge-slate' }[o.status] || 'badge-slate';

    const placedDate = new Date(o.placed_at).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    const placedTime = new Date(o.placed_at).toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true });

    html += `
      <tr>
        <td>
          <p class="font-semibold text-slate-800 mono text-xs">${escapeHtml(o.order_number)}</p>
        </td>
        <td>
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-full bg-cc-blue/10 flex items-center justify-center text-cc-blue text-[10px] font-bold shrink-0">
              ${escapeHtml(o.user_initials || 'U')}
            </div>
            <div class="min-w-0">
              <p class="font-medium text-slate-700 text-sm truncate max-w-[140px]">${escapeHtml(o.shipping_name)}</p>
              <p class="text-[10px] text-slate-400 mono">${escapeHtml(o.shipping_phone)}</p>
            </div>
          </div>
        </td>
        <td><p class="text-sm text-slate-600 truncate max-w-[180px]">${escapeHtml(o.items_summary || 'Products')}</p></td>
        <td>
          <p class="font-semibold text-slate-800">₹${Number(o.total_amount).toLocaleString('en-IN', {minimumFractionDigits: 2})}</p>
          ${o.discount_amount > 0 ? `<p class="text-[10px] text-green-600 mono">-₹${Number(o.discount_amount).toLocaleString('en-IN')}</p>` : ''}
        </td>
        <td><span class="badge ${pBadge}">${escapeHtml(o.payment_status)}</span></td>
        <td><span class="badge ${sBadge}">${escapeHtml(o.status)}</span></td>
        <td>
          <p class="text-sm text-slate-600">${placedDate}</p>
          <p class="text-[10px] text-slate-400">${placedTime}</p>
        </td>
        <td>
          <div class="flex items-center gap-1">
            <button class="btn-icon" style="width:30px;height:30px" onclick="viewOrderDetail('${o.encrypted_id}')" title="View Details">
              <span class="material-icons text-[16px]">visibility</span>
            </button>
            <button class="btn-icon" style="width:30px;height:30px" onclick="openStatusModal('${o.encrypted_id}', '${escapeHtml(o.order_number)}', '${o.status}')" title="Change Status">
              <span class="material-icons text-[16px]">swap_horiz</span>
            </button>
          </div>
        </td>
      </tr>
    `;
  });

  tbody.innerHTML = html;
}

function renderPagination(p) {
  const container = document.getElementById('pagination');
  const countEl = document.getElementById('resultCount');

  countEl.textContent = `Showing page ${p.currentPage} of ${p.totalPages} (${p.totalCount} total orders)`;

  if (p.totalPages <= 1) {
    container.innerHTML = '';
    return;
  }

  let html = '';
  for (let i = 1; i <= p.totalPages; i++) {
    html += `<button class="btn-icon ${i === p.currentPage ? 'bg-cc-blue text-white border-cc-blue' : 'bg-white text-slate-600 border-slate-200'}" 
                     style="width:32px;height:32px;font-size:12px;font-weight:700" 
                     onclick="fetchOrders(${i})">${i}</button>`;
  }
  container.innerHTML = html;
}

async function viewOrderDetail(encryptedId) {
  openModal('detailModal');
  const content = document.getElementById('detailContent');
  content.innerHTML = '<div class="text-center py-8 text-slate-400">Loading order details...</div>';

  try {
    const res = await fetch(`${BASE_URL}/admin/orders/detail/${encryptedId}`);
    const data = await res.json();
    if (data.success) {
      renderDetailModalContent(data.order);
    } else {
      content.innerHTML = `<div class="p-6 text-center text-red-500">${escapeHtml(data.message)}</div>`;
    }
  } catch(e) {
    content.innerHTML = '<div class="p-6 text-center text-red-500">Failed to load order details.</div>';
  }
}

function renderDetailModalContent(o) {
  const content = document.getElementById('detailContent');

  let itemsHtml = '';
  if (o.items && o.items.length > 0) {
    o.items.forEach(it => {
      const pName = it.product_name || it.name || 'Product';
      const pSku = it.product_sku || '';
      const uPrice = Number(it.unit_price || it.sale_price || 0);
      const lTotal = Number(it.line_total || it.total_price || (uPrice * (it.quantity || 1)));
      const imgHtml = it.image_url 
        ? `<img src="${escapeHtml(it.image_url)}" alt="${escapeHtml(pName)}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0" onerror="this.onerror=null;this.parentNode.innerHTML='<div class=\\\'w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 shrink-0\\\'><span class=\\\'material-icons text-[18px]\\\'>inventory_2</span></div>';">`
        : `<div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 shrink-0"><span class="material-icons text-[18px]">inventory_2</span></div>`;

      itemsHtml += `
        <tr class="hover:bg-slate-50/60 transition">
          <td class="px-4 py-3 text-sm text-slate-700">
            <div class="flex items-center gap-3">
              ${imgHtml}
              <div>
                <p class="font-medium text-slate-900 leading-snug">${escapeHtml(pName)}</p>
                ${pSku ? `<p class="text-[11px] text-slate-400 mono mt-0.5">${escapeHtml(pSku)}</p>` : ''}
              </div>
            </div>
          </td>
          <td class="px-4 py-3 text-sm text-center font-medium text-slate-600">${it.quantity || 1}</td>
          <td class="px-4 py-3 text-sm text-right mono text-slate-600">₹${uPrice.toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
          <td class="px-4 py-3 text-sm text-right font-semibold text-slate-800 mono">₹${lTotal.toLocaleString('en-IN', {minimumFractionDigits: 2})}</td>
        </tr>
      `;
    });
  } else {
    itemsHtml = `
      <tr>
        <td colspan="4" class="px-4 py-8 text-center text-slate-400 text-xs font-medium">
          <span class="material-icons text-2xl text-slate-300 block mb-1">remove_shopping_cart</span>
          No order items recorded for this order.
        </td>
      </tr>
    `;
  }

  content.innerHTML = `
    <div class="space-y-6">
      <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
          <p class="font-heading text-xl font-bold text-slate-900 mono">${escapeHtml(o.order_number)}</p>
          <p class="text-xs text-slate-400 mt-0.5">Placed on ${new Date(o.placed_at).toLocaleString()}</p>
        </div>
        <div class="flex items-center gap-2">
          <span class="badge badge-blue">${escapeHtml(o.status)}</span>
          <span class="badge badge-green">${escapeHtml(o.payment_status)}</span>
        </div>
      </div>

      <!-- Customer & Address -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-slate-50 rounded-xl p-4">
          <p class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold mb-2">Customer Info</p>
          <p class="font-semibold text-slate-800 text-sm">${escapeHtml(o.user_name || o.shipping_name)}</p>
          <p class="text-xs text-slate-500">${escapeHtml(o.user_email || 'No email')}</p>
          <p class="text-xs text-slate-500">${escapeHtml(o.shipping_phone)}</p>
        </div>
        <div class="bg-slate-50 rounded-xl p-4">
          <p class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold mb-2">Shipping Address</p>
          <p class="text-sm text-slate-700 font-medium">${escapeHtml(o.shipping_name)}</p>
          <p class="text-xs text-slate-500">${escapeHtml(o.shipping_line1)}</p>
          <p class="text-xs text-slate-500">${escapeHtml(o.shipping_city)}, ${escapeHtml(o.shipping_state)} - ${escapeHtml(o.shipping_postal)}</p>
        </div>
      </div>

      <!-- Financials Breakdown -->
      <div class="bg-slate-50 rounded-xl p-4">
        <p class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold mb-3">Financial Summary</p>
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
          <div><p class="text-[10px] text-slate-400">Subtotal</p><p class="text-sm font-bold text-slate-800 mono">₹${Number(o.subtotal).toFixed(2)}</p></div>
          <div><p class="text-[10px] text-slate-400">Discount</p><p class="text-sm font-bold text-green-600 mono">-₹${Number(o.discount_amount).toFixed(2)}</p></div>
          <div><p class="text-[10px] text-slate-400">Shipping</p><p class="text-sm font-bold text-slate-800 mono">₹${Number(o.shipping_charge).toFixed(2)}</p></div>
          <div><p class="text-[10px] text-slate-400">Tax</p><p class="text-sm font-bold text-slate-800 mono">₹${Number(o.tax_amount).toFixed(2)}</p></div>
          <div class="bg-white rounded-lg p-2 text-center"><p class="text-[10px] text-slate-400">Total</p><p class="text-base font-extrabold text-slate-900 mono">₹${Number(o.total_amount).toFixed(2)}</p></div>
        </div>
      </div>

      <!-- Items Table -->
      <div>
        <p class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold mb-2">Order Items</p>
        <div class="border border-slate-200 rounded-xl overflow-hidden">
          <table class="w-full">
            <thead>
              <tr class="bg-slate-50">
                <th class="text-[11px] text-slate-500 font-semibold px-4 py-2 text-left">Product</th>
                <th class="text-[11px] text-slate-500 font-semibold px-4 py-2 text-center">Qty</th>
                <th class="text-[11px] text-slate-500 font-semibold px-4 py-2 text-right">Unit Price</th>
                <th class="text-[11px] text-slate-500 font-semibold px-4 py-2 text-right">Line Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">${itemsHtml}</tbody>
          </table>
        </div>
      </div>
    </div>
  `;
}

function openStatusModal(encryptedId, orderNumber, currentStatus) {
  document.getElementById('statusEncryptedId').value = encryptedId;
  document.getElementById('statusOrderNum').textContent = orderNumber;
  document.getElementById('newStatus').value = currentStatus;
  document.getElementById('statusNote').value = '';
  openModal('statusModal');
}

function showToast(msg, type = 'success') {
  const c = { success: '#16A34A', error: '#DC2626', info: '#2D82FF', warning: '#D97706' };
  const i = { success: 'check_circle', error: 'error', info: 'info', warning: 'warning' };
  const t = document.createElement('div');
  t.className = 'toast';
  t.style.background = c[type] || c.info;
  t.innerHTML = `<span class="material-icons text-[20px]">${i[type] || 'info'}</span> ${escapeHtml(msg)}`;
  document.body.appendChild(t);
  setTimeout(() => {
    t.style.opacity = '0';
    t.style.transition = 'opacity 0.3s ease';
    setTimeout(() => t.remove(), 300);
  }, 3000);
}

async function fetchOrders(page = 1) {
  const status = document.getElementById('statusFilter').value;
  const paymentStatus = document.getElementById('paymentFilter').value;
  const date = document.getElementById('dateFilter').value;
  const search = document.getElementById('searchFilter').value;

  const url = `${BASE_URL}/admin/orders?ajax=1&page=${page}&status=${encodeURIComponent(status)}&payment_status=${encodeURIComponent(paymentStatus)}&date=${encodeURIComponent(date)}&search=${encodeURIComponent(search)}`;

  try {
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    if (data.success) {
      renderOrdersTable(data.data);
      renderPagination(data.pagination);
      if (data.kpis) updateKPIs(data.kpis);
      if (data.statusCounts) updateStatusGrid(data.statusCounts);
      updateExportLink(status, paymentStatus, date, search);
    }
  } catch(e) {
    console.error('Error fetching orders:', e);
  }
}

function updateKPIs(kpis) {
  if (kpis.totalOrders !== undefined) document.getElementById('kpiTotal').textContent = Number(kpis.totalOrders).toLocaleString();
  if (kpis.todayOrders !== undefined) document.getElementById('kpiToday').textContent = Number(kpis.todayOrders).toLocaleString();
  if (kpis.totalRevenue !== undefined) document.getElementById('kpiRevenue').textContent = '₹' + Number(kpis.totalRevenue).toLocaleString('en-IN', {minimumFractionDigits: 2});
  if (kpis.pendingCount !== undefined) document.getElementById('kpiPending').textContent = Number(kpis.pendingCount).toLocaleString();
  if (kpis.unpaidCount !== undefined) document.getElementById('kpiUnpaid').textContent = Number(kpis.unpaidCount).toLocaleString();
  if (kpis.returnsCount !== undefined) document.getElementById('kpiReturns').textContent = Number(kpis.returnsCount).toLocaleString();
}

function updateStatusGrid(counts) {
  const stConf = {
    pending: { label: 'Pending', color: '#EAB308', bg: '#FEF3C7', icon: 'schedule' },
    confirmed: { label: 'Confirmed', color: '#2D82FF', bg: '#DBEAFE', icon: 'check_circle_outline' },
    processing: { label: 'Processing', color: '#06B6D4', bg: '#ECFEFF', icon: 'autorenew' },
    shipped: { label: 'Shipped', color: '#6366F1', bg: '#E0E7FF', icon: 'local_shipping' },
    delivered: { label: 'Delivered', color: '#22C55E', bg: '#DCFCE7', icon: 'done_all' },
    cancelled: { label: 'Cancelled', color: '#EF4444', bg: '#FFE4E6', icon: 'cancel' },
    return_requested: { label: 'Return Req.', color: '#F97316', bg: '#FFF7ED', icon: 'assignment_return' },
    returned: { label: 'Returned', color: '#D97706', bg: '#FEF3C7', icon: 'undo' },
    refunded: { label: 'Refunded', color: '#94A3B8', bg: '#F1F5F9', icon: 'money_off' }
  };

  const totalSum = Object.values(counts).reduce((a, b) => a + b, 0) || 1;
  const gridEl = document.getElementById('statusGrid');
  const barEl = document.getElementById('statusBar');

  if (!gridEl || !barEl) return;

  let gridHtml = '';
  let barHtml = '';

  Object.keys(stConf).forEach(stKey => {
    const conf = stConf[stKey];
    const cnt = counts[stKey] || 0;
    const pct = Math.round((cnt / totalSum) * 100);

    gridHtml += `
      <div class="rounded-xl p-3 text-center cursor-pointer hover:shadow-md transition" 
           style="background:${conf.bg};border:1.5px solid ${conf.color}30" 
           onclick="filterByStatus('${stKey}')">
        <span class="material-icons text-[20px]" style="color:${conf.color}">${conf.icon}</span>
        <p class="text-lg font-bold font-heading mt-1" style="color:${conf.color}">${cnt.toLocaleString()}</p>
        <p class="text-[10px] font-semibold" style="color:${conf.color}">${conf.label}</p>
        <p class="text-[9px] mono mt-0.5" style="color:${conf.color}80">${pct}%</p>
      </div>
    `;

    if (pct > 0) {
      barHtml += `<div style="width:${pct}%;background:${conf.color}" title="${conf.label}: ${cnt} (${pct}%)"></div>`;
    }
  });

  gridEl.innerHTML = gridHtml;
  barEl.innerHTML = barHtml;
}

async function submitStatusUpdate() {
  const encryptedId = document.getElementById('statusEncryptedId').value;
  const newStatus = document.getElementById('newStatus').value;
  const note = document.getElementById('statusNote').value;
  const updateBtn = document.getElementById('statusSubmitBtn');

  if (updateBtn) {
    updateBtn.disabled = true;
    updateBtn.innerHTML = '<span class="material-icons animate-spin text-[18px]">autorenew</span> Updating...';
  }

  try {
    const res = await fetch(`${BASE_URL}/admin/orders/update-status`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ order_id: encryptedId, new_status: newStatus, note: note })
    });

    const data = await res.json();
    if (data.success) {
      closeModal('statusModal');
      showToast('success', data.message || 'Order status updated successfully.');
      fetchOrders(1);
    } else {
      showToast('error', data.message || 'Failed to update order status.');
    }
  } catch(e) {
    showToast('error', 'Network error while updating status.');
  } finally {
    if (updateBtn) {
      updateBtn.disabled = false;
      updateBtn.innerHTML = 'Update Status';
    }
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
