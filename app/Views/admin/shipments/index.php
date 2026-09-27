<?php
$pageTitle = 'Shipments & Logistics';
$activeMenu = 'shipments';

require_once __DIR__ . '/../layouts/header.php';

$shipments = $shipments ?? [];
$pagination = $pagination ?? ['current_page' => 1, 'total_pages' => 1, 'total' => 0, 'per_page' => 15];
$kpis = $kpis ?? [];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Header Banner -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">Shipments &amp; Logistics</h1>
      <p class="text-slate-500 text-sm mt-1">Track order dispatches, courier partners, AWB numbers, and delivery states</p>
    </div>
  </div>

  <!-- KPI Metrics Cards -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <!-- Total Shipments -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-blue/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-blue text-[22px]">local_shipping</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Shipments</p>
          <p class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($kpis['total_shipments'] ?? 0) ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500">Dispatched orders</p>
    </div>

    <!-- In Transit -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-yellow/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-yellow text-[22px]">alt_route</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">In Transit</p>
          <p class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format(($kpis['in_transit_count'] ?? 0) + ($kpis['out_for_delivery_count'] ?? 0)) ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500">En route to customers</p>
    </div>

    <!-- Delivered -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-emerald-500 text-[22px]">mark_ic_read_reviews</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Delivered</p>
          <p class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($kpis['delivered_count'] ?? 0) ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500"><strong class="text-emerald-600">Successful</strong> deliveries</p>
    </div>

    <!-- Failed / Returned -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-pink/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-pink text-[22px]">report_problem</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Failed / Returned</p>
          <p class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($kpis['failed_count'] ?? 0) ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500">Exceptions &amp; RTOs</p>
    </div>
  </div>

  <!-- Filters & Search Bar -->
  <div class="section-card p-4">
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
      <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 gap-2 flex-1 w-full sm:max-w-xs focus-within:border-cc-blue focus-within:ring-2 focus-within:ring-cc-blue/10 transition">
        <span class="material-icons text-slate-400 text-[18px]">search</span>
        <input type="text" id="shipmentSearch" placeholder="Search AWB, Courier, Order #..." class="bg-transparent text-sm text-slate-700 w-full focus:outline-none placeholder:text-slate-400" oninput="debounceFetch()">
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <select id="statusFilter" class="input-field text-xs w-auto py-2" onchange="fetchShipments(1)">
          <option value="">All Statuses</option>
          <option value="delivered">Delivered</option>
          <option value="in_transit">In Transit</option>
          <option value="out_for_delivery">Out for Delivery</option>
          <option value="pending">Pending Dispatch</option>
          <option value="failed_attempt">Failed Attempt</option>
          <option value="returned">Returned (RTO)</option>
        </select>
        <select id="carrierFilter" class="input-field text-xs w-auto py-2" onchange="fetchShipments(1)">
          <option value="">All Courier Carriers</option>
          <option value="BlueDart">BlueDart</option>
          <option value="Delhivery">Delhivery</option>
          <option value="India Post">India Post</option>
          <option value="Ecom Express">Ecom Express</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Shipments Data Table -->
  <div class="section-card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="data-table">
        <thead>
          <tr>
            <th>AWB Tracking #</th>
            <th>Order #</th>
            <th>Carrier Partner</th>
            <th>Customer &amp; Address</th>
            <th>Delivery Status</th>
            <th>Dispatched / Est. Delivery</th>
            <th style="width: 100px">Actions</th>
          </tr>
        </thead>
        <tbody id="shipmentsBody">
          <?php if (!empty($shipments)): ?>
            <?php foreach ($shipments as $s): ?>
              <?= renderShipmentRow($s) ?>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="7" class="text-center py-12">
              <span class="material-icons text-slate-300 text-[40px]">local_shipping</span>
              <p class="text-slate-500 mt-2 font-medium">No shipment logs found</p>
            </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination Bar -->
    <div class="px-5 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
      <p class="text-xs text-slate-500">
        Showing <strong class="text-slate-800"><?= count($shipments) ?></strong> of <strong class="text-slate-800"><?= number_format($pagination['total']) ?></strong> shipments
      </p>
      <div class="flex items-center gap-1" id="paginationBar">
        <?php renderShipmentPagination($pagination); ?>
      </div>
    </div>
  </div>
</div>

<!-- ================= SHIPMENT EDIT / UPDATE MODAL ================= -->
<div class="modal-overlay" id="shipmentModal">
  <div class="modal-box max-w-lg mx-auto">
    <div class="p-5 border-b border-slate-200 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-cc-purple/10 flex items-center justify-center text-cc-purple shrink-0">
          <span class="material-icons text-[20px]">local_shipping</span>
        </div>
        <div>
          <h3 class="font-heading text-base font-bold text-slate-900">Update Shipment Logistics</h3>
          <p class="text-xs text-slate-400" id="modalAwbRef">AWB Tracking</p>
        </div>
      </div>
      <button onclick="closeModal('shipmentModal')" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
        <span class="material-icons text-[20px]">close</span>
      </button>
    </div>

    <form id="shipmentForm" onsubmit="event.preventDefault(); saveShipment();">
      <input type="hidden" id="fShipmentId" value="">

      <div class="p-5 space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Carrier Partner Name *</label>
          <select id="fCarrierName" class="input-field text-xs">
            <option value="BlueDart">BlueDart</option>
            <option value="Delhivery">Delhivery</option>
            <option value="India Post">India Post</option>
            <option value="Ecom Express">Ecom Express</option>
            <option value="Shadowfax">Shadowfax</option>
            <option value="Xpressbees">Xpressbees</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Tracking AWB Number *</label>
          <input type="text" id="fTrackingNumber" required placeholder="e.g. BD1234567890" class="input-field text-xs mono">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Live Tracking URL</label>
          <input type="url" id="fTrackingUrl" placeholder="https://track.bluedart.com/BD1234567890" class="input-field text-xs mono">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Logistical Delivery Status</label>
          <select id="fStatus" class="input-field text-xs font-semibold">
            <option value="pending">Pending Dispatch</option>
            <option value="in_transit">In Transit</option>
            <option value="out_for_delivery">Out for Delivery</option>
            <option value="delivered">Delivered</option>
            <option value="failed_attempt">Failed Attempt</option>
            <option value="returned">Returned (RTO)</option>
          </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Shipped Date &amp; Time</label>
            <input type="datetime-local" id="fShippedAt" class="input-field text-xs">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Est. Delivery Date</label>
            <input type="datetime-local" id="fEstimatedDelivery" class="input-field text-xs">
          </div>
        </div>
      </div>

      <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3 rounded-b-2xl">
        <button type="button" onclick="closeModal('shipmentModal')" class="btn-secondary text-xs py-2 px-4">Cancel</button>
        <button type="submit" class="btn-primary text-xs py-2 px-4 shadow-md shadow-cc-blue/20">
          <span class="material-icons text-[16px]">save</span> Update Shipment
        </button>
      </div>
    </form>
  </div>
</div>

<?php
function renderShipmentRow(array $s): string {
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    $statusBadge = match($s['status']) {
        'delivered' => 'badge-green',
        'out_for_delivery' => 'badge-blue',
        'in_transit' => 'badge-yellow',
        'failed_attempt', 'returned' => 'badge-red',
        default => 'badge-slate',
    };

    $shippedDate = $s['shipped_at'] ? date('d M Y', strtotime($s['shipped_at'])) : 'Pending';
    $estDate = $s['estimated_delivery'] ? date('d M Y', strtotime($s['estimated_delivery'])) : 'TBD';

    $eid = htmlspecialchars($s['encrypted_id']);

    return "
      <tr id=\"shipment-row-{$eid}\">
        <td>
          <p class=\"font-mono font-bold text-xs text-slate-800\">".htmlspecialchars($s['tracking_number'] ?: 'N/A')."</p>
          ".($s['tracking_url'] ? "
            <a href=\"".htmlspecialchars($s['tracking_url'])."\" target=\"_blank\" class=\"text-[10px] text-cc-blue font-semibold hover:underline flex items-center gap-0.5 mt-0.5\">
              <span class=\"material-icons text-[12px]\">open_in_new</span> Track Package
            </a>
          " : "")."
        </td>
        <td>
          <a href=\"{$baseUrl}/admin/orders?search=".urlencode($s['order_number'])."\" class=\"font-mono text-xs text-cc-blue font-semibold hover:underline\">
            ".htmlspecialchars($s['order_number'])."
          </a>
        </td>
        <td>
          <span class=\"badge badge-purple text-[10px] font-bold\">".htmlspecialchars($s['carrier_name'])."</span>
        </td>
        <td>
          <p class=\"text-xs font-semibold text-slate-800\">".htmlspecialchars($s['user_name'] ?: 'Customer')."</p>
          <p class=\"text-[10px] text-slate-400 truncate max-w-[180px]\">".htmlspecialchars($s['shipping_address'] ?: '')."</p>
        </td>
        <td>
          <span class=\"badge {$statusBadge} text-[10px]\">".str_replace('_', ' ', strtoupper($s['status']))."</span>
        </td>
        <td>
          <p class=\"text-xs text-slate-600\">Dispatched: <span class=\"mono font-semibold\">{$shippedDate}</span></p>
          <p class=\"text-[10px] text-slate-400 mono\">Est: {$estDate}</p>
        </td>
        <td>
          <div class=\"flex items-center gap-1\">
            <button onclick=\"editShipment('{$eid}')\" class=\"action-btn\" title=\"Update Tracking\">
              <span class=\"material-icons text-[18px]\">edit</span>
            </button>
          </div>
        </td>
      </tr>
    ";
}

function renderShipmentPagination(array $p): void {
    $cur = $p['current_page'];
    $total = $p['total_pages'];
    if ($total <= 1) return;

    $prevDisabled = $cur <= 1 ? 'opacity-40 pointer-events-none' : 'hover:bg-slate-200';
    echo "<button onclick=\"fetchShipments(".($cur-1).")\" class=\"w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center text-xs {$prevDisabled} transition\"><span class=\"material-icons text-[16px]\">chevron_left</span></button>";

    $start = max(1, $cur - 2);
    $end = min($total, $cur + 2);
    for ($i = $start; $i <= $end; $i++) {
        $isActive = ($i === $cur) ? 'bg-cc-blue text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200';
        echo "<button onclick=\"fetchShipments({$i})\" class=\"w-8 h-8 rounded-lg {$isActive} text-xs font-semibold flex items-center justify-center transition\">{$i}</button>";
    }

    $nextDisabled = $cur >= $total ? 'opacity-40 pointer-events-none' : 'hover:bg-slate-200';
    echo "<button onclick=\"fetchShipments(".($cur+1).")\" class=\"w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center text-xs {$nextDisabled} transition\"><span class=\"material-icons text-[16px]\">chevron_right</span></button>";
}
?>

<script>
var BASE_URL = window.BASE_URL || '<?= defined("BASE_URL") ? BASE_URL : "" ?>';
let searchTimeout = null;

function debounceFetch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => fetchShipments(1), 300);
}

async function fetchShipments(page = 1) {
  const status = document.getElementById('statusFilter').value;
  const carrier = document.getElementById('carrierFilter').value;
  const search = document.getElementById('shipmentSearch').value;

  const url = `${BASE_URL}/admin/shipments?ajax=1&page=${page}&status=${encodeURIComponent(status)}&carrier=${encodeURIComponent(carrier)}&search=${encodeURIComponent(search)}`;

  try {
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    if (data.success) {
      window.location.reload();
    }
  } catch(e) {
    showToast('error', 'Error fetching shipments');
  }
}

async function editShipment(encryptedId) {
  try {
    const res = await fetch(`${BASE_URL}/admin/shipments/detail/${encryptedId}`);
    const data = await res.json();
    if (data.success && data.shipment) {
      const s = data.shipment;
      document.getElementById('modalAwbRef').textContent = `AWB: ${s.tracking_number || 'N/A'}`;
      document.getElementById('fShipmentId').value = s.encrypted_id;
      document.getElementById('fCarrierName').value = s.carrier_name || 'BlueDart';
      document.getElementById('fTrackingNumber').value = s.tracking_number || '';
      document.getElementById('fTrackingUrl').value = s.tracking_url || '';
      document.getElementById('fStatus').value = s.status || 'pending';
      document.getElementById('fShippedAt').value = s.shipped_at ? s.shipped_at.replace(' ', 'T').slice(0, 16) : '';
      document.getElementById('fEstimatedDelivery').value = s.estimated_delivery ? s.estimated_delivery.replace(' ', 'T').slice(0, 16) : '';

      document.getElementById('shipmentModal').classList.add('show');
    } else {
      showToast('error', data.message || 'Failed to load shipment');
    }
  } catch(e) {
    showToast('error', 'Network error loading shipment');
  }
}

async function saveShipment() {
  const shipmentId = document.getElementById('fShipmentId').value;

  const payload = {
    shipment_id: shipmentId,
    carrier_name: document.getElementById('fCarrierName').value,
    tracking_number: document.getElementById('fTrackingNumber').value.trim(),
    tracking_url: document.getElementById('fTrackingUrl').value.trim(),
    status: document.getElementById('fStatus').value,
    shipped_at: document.getElementById('fShippedAt').value.replace('T', ' '),
    estimated_delivery: document.getElementById('fEstimatedDelivery').value.replace('T', ' ')
  };

  try {
    const res = await fetch(`${BASE_URL}/admin/shipments/update`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      showToast('success', 'Shipment tracking updated successfully!');
      closeModal('shipmentModal');
      setTimeout(() => window.location.reload(), 600);
    } else {
      showToast('error', data.message || 'Error updating shipment');
    }
  } catch(e) {
    showToast('error', 'Network error updating shipment');
  }
}

function closeModal(id) {
  document.getElementById(id).classList.remove('show');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
