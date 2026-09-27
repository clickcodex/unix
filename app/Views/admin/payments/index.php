<?php
$pageTitle = 'Payment Transactions';
$activeMenu = 'payments';

require_once __DIR__ . '/../layouts/header.php';

$payments = $payments ?? [];
$pagination = $pagination ?? ['current_page' => 1, 'total_pages' => 1, 'total' => 0, 'per_page' => 15];
$kpis = $kpis ?? [];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Header Banner -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">Payment Transactions</h1>
      <p class="text-slate-500 text-sm mt-1">Audit payment gateway logs, transactions, revenue, and process refunds</p>
    </div>
  </div>

  <!-- KPI Metrics Cards -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <!-- Total Revenue -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-emerald-500 text-[22px]">payments</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Revenue</p>
          <p class="text-xl font-extrabold text-slate-900 leading-tight">₹<?= number_format($kpis['total_revenue'] ?? 0, 2) ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500">Successful payment volume</p>
    </div>

    <!-- Success Count -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-blue/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-blue text-[22px]">check_circle</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Successful Txns</p>
          <p class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($kpis['success_count'] ?? 0) ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500">Processed payments</p>
    </div>

    <!-- Pending / Initiated -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-yellow/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-yellow text-[22px]">hourglass_empty</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pending Txns</p>
          <p class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($kpis['pending_count'] ?? 0) ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500">Awaiting gateway response</p>
    </div>

    <!-- Refunded / Failed -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-pink/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-pink text-[22px]">published_with_changes</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Refunded / Failed</p>
          <p class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format(($kpis['refunded_count'] ?? 0) + ($kpis['failed_count'] ?? 0)) ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500">
        <strong class="text-cc-pink"><?= $kpis['refunded_count'] ?? 0 ?></strong> refunded
      </p>
    </div>
  </div>

  <!-- Filters & Search Bar -->
  <div class="section-card p-4">
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
      <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 gap-2 flex-1 w-full sm:max-w-xs focus-within:border-cc-blue focus-within:ring-2 focus-within:ring-cc-blue/10 transition">
        <span class="material-icons text-slate-400 text-[18px]">search</span>
        <input type="text" id="paymentSearch" placeholder="Search Txn ID, Order #, Customer..." class="bg-transparent text-sm text-slate-700 w-full focus:outline-none placeholder:text-slate-400" oninput="debounceFetch()">
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <select id="statusFilter" class="input-field text-xs w-auto py-2" onchange="fetchPayments(1)">
          <option value="">All Statuses</option>
          <option value="success">Success</option>
          <option value="pending">Pending</option>
          <option value="failed">Failed</option>
          <option value="refunded">Refunded</option>
        </select>
        <select id="gatewayFilter" class="input-field text-xs w-auto py-2" onchange="fetchPayments(1)">
          <option value="">All Gateways</option>
          <option value="razorpay">Razorpay</option>
          <option value="stripe">Stripe</option>
          <option value="phonepe">PhonePe</option>
          <option value="cod">Cash on Delivery (COD)</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Payments Data Table -->
  <div class="section-card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="data-table">
        <thead>
          <tr>
            <th>Txn Reference ID</th>
            <th>Order #</th>
            <th>Customer</th>
            <th>Gateway</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Processed At</th>
            <th style="width: 100px">Actions</th>
          </tr>
        </thead>
        <tbody id="paymentsBody">
          <?php if (!empty($payments)): ?>
            <?php foreach ($payments as $p): ?>
              <?= renderPaymentRow($p) ?>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center py-12">
              <span class="material-icons text-slate-300 text-[40px]">account_balance_wallet</span>
              <p class="text-slate-500 mt-2 font-medium">No payment transactions found</p>
            </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination Bar -->
    <div class="px-5 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
      <p class="text-xs text-slate-500">
        Showing <strong class="text-slate-800"><?= count($payments) ?></strong> of <strong class="text-slate-800"><?= number_format($pagination['total']) ?></strong> transactions
      </p>
      <div class="flex items-center gap-1" id="paginationBar">
        <?php renderPaymentPagination($pagination); ?>
      </div>
    </div>
  </div>
</div>

<!-- ================= PAYMENT DETAIL MODAL ================= -->
<div class="modal-overlay" id="paymentDetailModal">
  <div class="modal-box max-w-xl mx-auto">
    <div class="p-5 border-b border-slate-200 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-cc-blue/10 flex items-center justify-center text-cc-blue shrink-0">
          <span class="material-icons text-[20px]">receipt_long</span>
        </div>
        <div>
          <h3 class="font-heading text-base font-bold text-slate-900">Transaction Inspector</h3>
          <p class="text-xs text-slate-400" id="modalTxnRef">—</p>
        </div>
      </div>
      <button onclick="closeModal('paymentDetailModal')" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
        <span class="material-icons text-[20px]">close</span>
      </button>
    </div>

    <div class="p-5 space-y-4" id="paymentDetailContent">
      <div class="text-center py-8 text-slate-400">
        <span class="material-icons animate-spin text-[24px]">autorenew</span>
        <p class="text-xs mt-2">Loading transaction details...</p>
      </div>
    </div>
  </div>
</div>

<?php
function renderPaymentRow(array $p): string {
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    $statusBadge = match($p['status']) {
        'success' => 'badge-green',
        'refunded' => 'badge-purple',
        'failed' => 'badge-red',
        default => 'badge-yellow',
    };

    $gatewayBadge = match(strtolower($p['gateway'])) {
        'razorpay' => 'badge-blue',
        'stripe' => 'badge-purple',
        'phonepe' => 'badge-cyan',
        'cod' => 'badge-orange',
        default => 'badge-slate',
    };

    $date = date('d M Y', strtotime($p['created_at']));
    $time = date('h:i A', strtotime($p['created_at']));

    $eid = htmlspecialchars($p['encrypted_id']);

    return "
      <tr id=\"payment-row-{$eid}\">
        <td>
          <p class=\"font-mono font-bold text-xs text-slate-800\">".htmlspecialchars($p['gateway_txn_id'] ?: 'TXN-'.$p['id'])."</p>
        </td>
        <td>
          <a href=\"{$baseUrl}/admin/orders?search=".urlencode($p['order_number'])."\" class=\"font-mono text-xs text-cc-blue font-semibold hover:underline\">
            ".htmlspecialchars($p['order_number'])."
          </a>
        </td>
        <td>
          <p class=\"text-xs font-semibold text-slate-800\">".htmlspecialchars($p['user_name'] ?: 'Customer')."</p>
          <p class=\"text-[11px] text-slate-400 truncate max-w-[140px]\">".htmlspecialchars($p['user_email'] ?: '')."</p>
        </td>
        <td>
          <span class=\"badge {$gatewayBadge} text-[10px] uppercase\">".htmlspecialchars($p['gateway'])."</span>
        </td>
        <td>
          <p class=\"mono font-bold text-sm text-slate-900\">₹".number_format($p['amount'], 2)."</p>
        </td>
        <td>
          <span class=\"badge {$statusBadge} text-[10px]\">".ucfirst($p['status'])."</span>
        </td>
        <td>
          <p class=\"text-xs text-slate-600\">{$date}</p>
          <p class=\"text-[10px] text-slate-400 mono\">{$time}</p>
        </td>
        <td>
          <div class=\"flex items-center gap-1\">
            <button onclick=\"viewPaymentDetail('{$eid}')\" class=\"action-btn\" title=\"View Details\">
              <span class=\"material-icons text-[18px]\">visibility</span>
            </button>
            ".($p['status'] === 'success' ? "
              <button onclick=\"updatePaymentStatus('{$eid}', 'refunded')\" class=\"action-btn danger\" title=\"Process Refund\">
                <span class=\"material-icons text-[18px]\">undo</span>
              </button>
            " : "")."
          </div>
        </td>
      </tr>
    ";
}

function renderPaymentPagination(array $p): void {
    $cur = $p['current_page'];
    $total = $p['total_pages'];
    if ($total <= 1) return;

    $prevDisabled = $cur <= 1 ? 'opacity-40 pointer-events-none' : 'hover:bg-slate-200';
    echo "<button onclick=\"fetchPayments(".($cur-1).")\" class=\"w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center text-xs {$prevDisabled} transition\"><span class=\"material-icons text-[16px]\">chevron_left</span></button>";

    $start = max(1, $cur - 2);
    $end = min($total, $cur + 2);
    for ($i = $start; $i <= $end; $i++) {
        $isActive = ($i === $cur) ? 'bg-cc-blue text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200';
        echo "<button onclick=\"fetchPayments({$i})\" class=\"w-8 h-8 rounded-lg {$isActive} text-xs font-semibold flex items-center justify-center transition\">{$i}</button>";
    }

    $nextDisabled = $cur >= $total ? 'opacity-40 pointer-events-none' : 'hover:bg-slate-200';
    echo "<button onclick=\"fetchPayments(".($cur+1).")\" class=\"w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center text-xs {$nextDisabled} transition\"><span class=\"material-icons text-[16px]\">chevron_right</span></button>";
}
?>

<script>
var BASE_URL = window.BASE_URL || '<?= defined("BASE_URL") ? BASE_URL : "" ?>';
let searchTimeout = null;

function debounceFetch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => fetchPayments(1), 300);
}

async function fetchPayments(page = 1) {
  const status = document.getElementById('statusFilter').value;
  const gateway = document.getElementById('gatewayFilter').value;
  const search = document.getElementById('paymentSearch').value;

  const url = `${BASE_URL}/admin/payments?ajax=1&page=${page}&status=${encodeURIComponent(status)}&gateway=${encodeURIComponent(gateway)}&search=${encodeURIComponent(search)}`;

  try {
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    if (data.success) {
      window.location.reload();
    }
  } catch(e) {
    showToast('error', 'Error fetching payment transactions');
  }
}

async function viewPaymentDetail(encryptedId) {
  document.getElementById('paymentDetailModal').classList.add('show');
  document.getElementById('modalTxnRef').textContent = 'Loading...';
  document.getElementById('paymentDetailContent').innerHTML = '<div class="text-center py-8 text-slate-400"><span class="material-icons animate-spin text-[24px]">autorenew</span><p class="text-xs mt-2">Loading transaction details...</p></div>';

  try {
    const res = await fetch(`${BASE_URL}/admin/payments/detail/${encryptedId}`);
    const data = await res.json();
    if (data.success && data.payment) {
      const p = data.payment;
      document.getElementById('modalTxnRef').textContent = p.gateway_txn_id || `TXN-${p.id}`;

      let statusBadge = { success: 'badge-green', refunded: 'badge-purple', failed: 'badge-red' }[p.status] || 'badge-yellow';

      document.getElementById('paymentDetailContent').innerHTML = `
        <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl space-y-2">
          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-400 font-semibold">Gateway:</span>
            <span class="badge badge-blue text-[10px] uppercase">${escapeHtml(p.gateway)}</span>
          </div>
          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-400 font-semibold">Order Number:</span>
            <span class="mono font-bold text-cc-blue">${escapeHtml(p.order_number)}</span>
          </div>
          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-400 font-semibold">Customer:</span>
            <span class="font-semibold text-slate-800">${escapeHtml(p.user_name)} (${escapeHtml(p.user_email)})</span>
          </div>
          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-400 font-semibold">Amount Paid:</span>
            <span class="mono font-bold text-slate-900 text-sm">₹${Number(p.amount).toLocaleString('en-IN', {minimumFractionDigits:2})} ${p.currency || 'INR'}</span>
          </div>
          <div class="flex items-center justify-between text-xs">
            <span class="text-slate-400 font-semibold">Status:</span>
            <span class="badge ${statusBadge} text-[10px]">${p.status.toUpperCase()}</span>
          </div>
          <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-200">
            <span class="text-slate-400 font-semibold">Created At:</span>
            <span class="mono text-slate-600">${new Date(p.created_at).toLocaleString('en-IN')}</span>
          </div>
        </div>

        ${p.payload ? `
          <div>
            <label class="block text-xs font-bold text-slate-600 mb-1">Gateway Payload Log</label>
            <pre class="p-3 bg-slate-900 text-slate-200 rounded-xl text-[11px] mono overflow-x-auto max-h-40">${escapeHtml(p.payload)}</pre>
          </div>
        ` : ''}

        <div class="flex items-center justify-end gap-3 pt-2">
          ${p.status === 'success' ? `
            <button onclick="updatePaymentStatus('${p.encrypted_id}', 'refunded'); closeModal('paymentDetailModal');" class="btn-danger text-xs py-2 px-4">
              <span class="material-icons text-[16px]">undo</span> Process Refund
            </button>
          ` : ''}
          <button onclick="closeModal('paymentDetailModal')" class="btn-secondary text-xs py-2 px-4">Close</button>
        </div>
      `;
    } else {
      document.getElementById('paymentDetailContent').innerHTML = '<p class="text-center text-red-500 py-8">Failed to load transaction details.</p>';
    }
  } catch(e) {
    document.getElementById('paymentDetailContent').innerHTML = '<p class="text-center text-red-500 py-8">Network error.</p>';
  }
}

async function updatePaymentStatus(encryptedId, status) {
  if (status === 'refunded' && !confirm('Are you sure you want to mark this transaction as REFUNDED?')) return;

  try {
    const res = await fetch(`${BASE_URL}/admin/payments/update-status`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ payment_id: encryptedId, status: status })
    });
    const data = await res.json();
    if (data.success) {
      showToast('success', data.message);
      setTimeout(() => window.location.reload(), 600);
    } else {
      showToast('error', data.message || 'Failed to update transaction status');
    }
  } catch(e) {
    showToast('error', 'Network error updating payment status');
  }
}

function closeModal(id) {
  document.getElementById(id).classList.remove('show');
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
