<?php
$pageTitle = 'Notification Manager';
$activeMenu = 'notifications';

require_once __DIR__ . '/../layouts/header.php';

$notifications = $notifications ?? [];
$pagination = $pagination ?? ['current_page' => 1, 'total_pages' => 1, 'total_items' => 0, 'limit' => 15];
$kpis = $kpis ?? ['unread' => 0, 'total' => 0, 'in_app' => 0, 'email' => 0, 'today' => 0];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Header Banner -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">Notification Center</h1>
      <p class="text-slate-500 text-sm mt-1">Manage system alerts, broadcast announcements, and customer notification channels</p>
    </div>
    <div class="flex items-center gap-3">
      <button onclick="markAllRead()" class="btn-secondary text-sm">
        <span class="material-icons text-[18px]">done_all</span> Mark All as Read
      </button>
      <button onclick="openBroadcastModal()" class="btn-primary text-sm shadow-md shadow-cc-blue/20">
        <span class="material-icons text-[18px]">campaign</span> Compose Broadcast
      </button>
    </div>
  </div>

  <!-- KPI Metric Cards Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
    
    <!-- Unread Alerts Card -->
    <div class="kpi-card p-5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Unread Alerts</span>
        <div class="w-10 h-10 rounded-xl bg-cc-pink/10 text-cc-pink flex items-center justify-center">
          <span class="material-icons text-[22px]">mark_email_unread</span>
        </div>
      </div>
      <div class="mt-3 flex items-baseline gap-2">
        <span class="font-heading text-2xl sm:text-3xl font-extrabold text-slate-900" id="kpiUnread"><?= number_format($kpis['unread']) ?></span>
        <span class="text-xs font-semibold text-cc-pink">Action Needed</span>
      </div>
      <div class="mt-2 text-xs text-slate-400">Notifications waiting for review</div>
    </div>

    <!-- Total Dispatched Card -->
    <div class="kpi-card p-5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Notifications</span>
        <div class="w-10 h-10 rounded-xl bg-cc-blue/10 text-cc-blue flex items-center justify-center">
          <span class="material-icons text-[22px]">notifications</span>
        </div>
      </div>
      <div class="mt-3 flex items-baseline gap-2">
        <span class="font-heading text-2xl sm:text-3xl font-extrabold text-slate-900" id="kpiTotal"><?= number_format($kpis['total']) ?></span>
        <span class="text-xs font-semibold text-cc-blue">All Channels</span>
      </div>
      <div class="mt-2 text-xs text-slate-400">System &amp; customer logs count</div>
    </div>

    <!-- In-App Feed Card -->
    <div class="kpi-card p-5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">In-App Feed</span>
        <div class="w-10 h-10 rounded-xl bg-cc-purple/10 text-cc-purple flex items-center justify-center">
          <span class="material-icons text-[22px]">dashboard_customize</span>
        </div>
      </div>
      <div class="mt-3 flex items-baseline gap-2">
        <span class="font-heading text-2xl sm:text-3xl font-extrabold text-slate-900" id="kpiInApp"><?= number_format($kpis['in_app']) ?></span>
        <span class="text-xs font-semibold text-cc-purple">In-App</span>
      </div>
      <div class="mt-2 text-xs text-slate-400">Dashboard &amp; user drawer messages</div>
    </div>

    <!-- Email Notifications Card -->
    <div class="kpi-card p-5">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Email Alerts</span>
        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
          <span class="material-icons text-[22px]">email</span>
        </div>
      </div>
      <div class="mt-3 flex items-baseline gap-2">
        <span class="font-heading text-2xl sm:text-3xl font-extrabold text-slate-900" id="kpiEmail"><?= number_format($kpis['email']) ?></span>
        <span class="text-xs font-semibold text-emerald-600">SMTP Sent</span>
      </div>
      <div class="mt-2 text-xs text-slate-400">Outbound email log entries</div>
    </div>

  </div>

  <!-- Filter & Search Toolbar Card -->
  <div class="section-card p-4 sm:p-5">
    <form id="filterForm" onsubmit="event.preventDefault(); applyFilters(1);" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
      <div class="flex flex-wrap items-center gap-3 flex-1">
        
        <!-- Search Input -->
        <div class="relative min-w-[220px] flex-1">
          <span class="material-icons absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
          <input type="text" id="filterSearch" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" placeholder="Search title, body, user..." class="input-field pl-9 text-xs">
        </div>

        <!-- Channel Filter -->
        <select id="filterChannel" onchange="applyFilters(1)" class="status-select text-xs">
          <option value="all" <?= ($_GET['channel'] ?? '') === 'all' ? 'selected' : '' ?>>All Channels</option>
          <option value="in_app" <?= ($_GET['channel'] ?? '') === 'in_app' ? 'selected' : '' ?>>In-App</option>
          <option value="email" <?= ($_GET['channel'] ?? '') === 'email' ? 'selected' : '' ?>>Email</option>
          <option value="sms" <?= ($_GET['channel'] ?? '') === 'sms' ? 'selected' : '' ?>>SMS</option>
          <option value="push" <?= ($_GET['channel'] ?? '') === 'push' ? 'selected' : '' ?>>Push</option>
        </select>

        <!-- Status Filter -->
        <select id="filterStatus" onchange="applyFilters(1)" class="status-select text-xs">
          <option value="all" <?= ($_GET['status'] ?? '') === 'all' ? 'selected' : '' ?>>All Statuses</option>
          <option value="unread" <?= ($_GET['status'] ?? '') === 'unread' ? 'selected' : '' ?>>Unread Only</option>
          <option value="read" <?= ($_GET['status'] ?? '') === 'read' ? 'selected' : '' ?>>Read Only</option>
        </select>

        <!-- Apply Button -->
        <button type="submit" class="btn-secondary text-xs py-2 px-3">
          <span class="material-icons text-[16px]">filter_list</span> Filter
        </button>
        
        <button type="button" onclick="resetFilters()" class="text-xs text-slate-400 hover:text-cc-pink underline ml-1">Reset</button>
      </div>

      <div class="flex items-center justify-end gap-2 text-xs text-slate-500">
        <span>Rows:</span>
        <select id="filterLimit" onchange="applyFilters(1)" class="status-select text-xs py-1 px-2">
          <option value="15" <?= $pagination['limit'] == 15 ? 'selected' : '' ?>>15</option>
          <option value="25" <?= $pagination['limit'] == 25 ? 'selected' : '' ?>>25</option>
          <option value="50" <?= $pagination['limit'] == 50 ? 'selected' : '' ?>>50</option>
          <option value="100" <?= $pagination['limit'] == 100 ? 'selected' : '' ?>>100</option>
        </select>
      </div>
    </form>
  </div>

  <!-- Bulk Action Floating Bar -->
  <div id="bulkBar" class="bulk-bar bg-slate-900 text-white rounded-2xl flex items-center justify-between gap-4 shadow-xl">
    <div class="flex items-center gap-3">
      <span class="material-icons text-cc-yellow text-[20px]">check_box</span>
      <span class="text-sm font-semibold"><span id="selectedCount">0</span> notifications selected</span>
    </div>
    <div class="flex items-center gap-2">
      <button onclick="executeBulk('mark_read')" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-semibold rounded-lg transition">Mark Read</button>
      <button onclick="executeBulk('mark_unread')" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-xs font-semibold rounded-lg transition">Mark Unread</button>
      <button onclick="executeBulk('delete')" class="px-3 py-1.5 bg-cc-pink/20 hover:bg-cc-pink/30 text-cc-pink text-xs font-semibold rounded-lg transition">Delete Selected</button>
    </div>
  </div>

  <!-- Notifications Data Table -->
  <div class="section-card">
    <div class="overflow-x-auto">
      <table class="data-table">
        <thead>
          <tr>
            <th class="w-10 text-center"><input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" class="row-check"></th>
            <th>Notification Info</th>
            <th>Channel</th>
            <th>Type</th>
            <th>Recipient User</th>
            <th>Created At</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody id="notificationsTableBody">
          <?php if (empty($notifications)): ?>
            <tr>
              <td colspan="8" class="text-center py-12 text-slate-400">
                <span class="material-icons text-4xl mb-2 text-slate-300">notifications_off</span>
                <p class="text-sm font-medium">No notifications found matching filter criteria.</p>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($notifications as $item): ?>
              <tr class="<?= empty($item['is_read']) ? 'bg-blue-50/30 font-medium' : '' ?>" id="row-<?= $item['encrypted_id'] ?>">
                <td class="text-center">
                  <input type="checkbox" value="<?= $item['encrypted_id'] ?>" class="row-check item-check" onchange="updateBulkBar()">
                </td>
                <td>
                  <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 mt-0.5 <?= empty($item['is_read']) ? 'bg-cc-blue/10 text-cc-blue' : 'bg-slate-100 text-slate-400' ?>">
                      <span class="material-icons text-[18px]">
                        <?= $item['channel'] === 'email' ? 'email' : ($item['channel'] === 'sms' ? 'sms' : 'notifications') ?>
                      </span>
                    </div>
                    <div>
                      <p class="text-sm font-semibold text-slate-800 leading-tight"><?= htmlspecialchars($item['title'] ?? 'Untitled Notification') ?></p>
                      <p class="text-xs text-slate-500 line-clamp-1 mt-0.5"><?= htmlspecialchars($item['body'] ?? '') ?></p>
                    </div>
                  </div>
                </td>
                <td>
                  <?php
                    $chBadge = 'badge-slate';
                    if ($item['channel'] === 'email') $chBadge = 'badge-purple';
                    elseif ($item['channel'] === 'in_app') $chBadge = 'badge-blue';
                    elseif ($item['channel'] === 'sms') $chBadge = 'badge-orange';
                    elseif ($item['channel'] === 'push') $chBadge = 'badge-green';
                  ?>
                  <span class="badge <?= $chBadge ?> uppercase text-[10px]"><?= htmlspecialchars($item['channel']) ?></span>
                </td>
                <td>
                  <span class="mono text-xs font-semibold text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md"><?= htmlspecialchars($item['type']) ?></span>
                </td>
                <td>
                  <?php if (!empty($item['user_name'])): ?>
                    <p class="text-xs font-semibold text-slate-700"><?= htmlspecialchars($item['user_name']) ?></p>
                    <p class="text-[11px] text-slate-400"><?= htmlspecialchars($item['user_email']) ?></p>
                  <?php else: ?>
                    <span class="text-xs italic text-slate-400">Broadcast (All Users)</span>
                  <?php endif; ?>
                </td>
                <td class="text-xs text-slate-500 whitespace-nowrap">
                  <?= date('M j, Y H:i', strtotime($item['created_at'])) ?>
                </td>
                <td>
                  <?php if (empty($item['is_read'])): ?>
                    <span class="badge badge-red"><span class="w-1.5 h-1.5 rounded-full bg-cc-pink animate-pulse"></span> Unread</span>
                  <?php else: ?>
                    <span class="badge badge-slate">Read</span>
                  <?php endif; ?>
                </td>
                <td class="text-right whitespace-nowrap">
                  <div class="flex items-center justify-end gap-1">
                    <button onclick="inspectNotification('<?= $item['encrypted_id'] ?>')" class="action-btn" title="View Details">
                      <span class="material-icons text-[18px]">visibility</span>
                    </button>
                    <button onclick="toggleSingleRead('<?= $item['encrypted_id'] ?>')" class="action-btn" title="Toggle Read Status">
                      <span class="material-icons text-[18px]"><?= empty($item['is_read']) ? 'drafts' : 'mark_email_read' ?></span>
                    </button>
                    <button onclick="deleteSingleNotification('<?= $item['encrypted_id'] ?>')" class="action-btn danger" title="Delete">
                      <span class="material-icons text-[18px]">delete</span>
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
    <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
      <p class="text-xs text-slate-500">
        Showing <span class="font-bold text-slate-700"><?= number_format(min($pagination['total_items'], ($pagination['current_page'] - 1) * $pagination['limit'] + 1)) ?></span>
        to <span class="font-bold text-slate-700"><?= number_format(min($pagination['total_items'], $pagination['current_page'] * $pagination['limit'])) ?></span>
        of <span class="font-bold text-slate-700"><?= number_format($pagination['total_items']) ?></span> notifications
      </p>

      <div class="flex items-center gap-1">
        <?php if ($pagination['current_page'] > 1): ?>
          <button onclick="applyFilters(<?= $pagination['current_page'] - 1 ?>)" class="btn-secondary text-xs py-1.5 px-3">Prev</button>
        <?php endif; ?>

        <span class="text-xs font-semibold text-slate-600 px-3 py-1.5 bg-slate-100 rounded-lg">Page <?= $pagination['current_page'] ?> of <?= $pagination['total_pages'] ?></span>

        <?php if ($pagination['current_page'] < $pagination['total_pages']): ?>
          <button onclick="applyFilters(<?= $pagination['current_page'] + 1 ?>)" class="btn-secondary text-xs py-1.5 px-3">Next</button>
        <?php endif; ?>
      </div>
    </div>
  </div>

</div>

<!-- Inspection Modal -->
<div id="inspectModal" class="modal-backdrop">
  <div class="modal-panel w-full max-w-xl p-6 relative">
    <button onclick="closeModal('inspectModal')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600"><span class="material-icons">close</span></button>
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-xl bg-cc-blue/10 text-cc-blue flex items-center justify-center">
        <span class="material-icons text-[22px]">notifications_active</span>
      </div>
      <div>
        <h3 class="font-heading text-lg font-bold text-slate-900" id="mTitle">Notification Payload</h3>
        <p class="text-xs text-slate-400" id="mType">Type: system_notice</p>
      </div>
    </div>
    <div class="space-y-4 text-xs border-t border-slate-100 pt-4">
      <div>
        <span class="font-bold text-slate-600 block mb-1">Message Content</span>
        <div id="mBody" class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 leading-relaxed"></div>
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <span class="font-bold text-slate-600 block">Recipient</span>
          <span id="mRecipient" class="text-slate-800">Broadcast</span>
        </div>
        <div>
          <span class="font-bold text-slate-600 block">Channel / Sent At</span>
          <span id="mSentAt" class="text-slate-800">-</span>
        </div>
      </div>
      <div>
        <span class="font-bold text-slate-600 block mb-1">Payload JSON Data</span>
        <pre id="mDataJson" class="p-3 bg-slate-900 text-emerald-400 font-mono text-[11px] rounded-xl overflow-x-auto">{}</pre>
      </div>
    </div>
    <div class="mt-6 flex justify-end">
      <button onclick="closeModal('inspectModal')" class="btn-secondary text-xs">Close Inspector</button>
    </div>
  </div>
</div>

<!-- Compose Broadcast Modal -->
<div id="broadcastModal" class="modal-backdrop">
  <div class="modal-panel w-full max-w-lg p-6 relative">
    <button onclick="closeModal('broadcastModal')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600"><span class="material-icons">close</span></button>
    <div class="flex items-center gap-3 mb-4">
      <div class="w-10 h-10 rounded-xl bg-cc-pink/10 text-cc-pink flex items-center justify-center">
        <span class="material-icons text-[22px]">campaign</span>
      </div>
      <div>
        <h3 class="font-heading text-lg font-bold text-slate-900">Compose Broadcast</h3>
        <p class="text-xs text-slate-400">Dispatch announcement to active users or admins</p>
      </div>
    </div>

    <form id="broadcastForm" onsubmit="event.preventDefault(); submitBroadcast();" class="space-y-4 text-xs">
      <div>
        <label class="block font-semibold text-slate-600 mb-1">Target Audience *</label>
        <select id="bTarget" class="input-field text-xs">
          <option value="all">All Active Customers &amp; Users</option>
          <option value="admins">Super Admins Only</option>
        </select>
      </div>

      <div>
        <label class="block font-semibold text-slate-600 mb-1">Delivery Channel *</label>
        <select id="bChannel" class="input-field text-xs">
          <option value="in_app">In-App Notification Feed</option>
          <option value="email">Email Dispatch (SMTP)</option>
          <option value="push">Web Push Notification</option>
        </select>
      </div>

      <div>
        <label class="block font-semibold text-slate-600 mb-1">Announcement Title *</label>
        <input type="text" id="bTitle" required placeholder="e.g. Flash Sale Announcement / Maintenance Notice" class="input-field text-xs">
      </div>

      <div>
        <label class="block font-semibold text-slate-600 mb-1">Message Body *</label>
        <textarea id="bBody" required rows="4" placeholder="Enter broadcast message description..." class="input-field text-xs resize-y"></textarea>
      </div>

      <div class="mt-6 flex justify-end gap-3 pt-3 border-t border-slate-100">
        <button type="button" onclick="closeModal('broadcastModal')" class="btn-secondary text-xs">Cancel</button>
        <button type="submit" class="btn-primary text-xs shadow-md shadow-cc-blue/20">
          <span class="material-icons text-[16px]">send</span> Dispatch Broadcast
        </button>
      </div>
    </form>
  </div>
</div>

<script>
let currentNotifications = <?= json_encode($notifications) ?>;

function applyFilters(page = 1) {
  const search = document.getElementById('filterSearch').value.trim();
  const channel = document.getElementById('filterChannel').value;
  const status = document.getElementById('filterStatus').value;
  const limit = document.getElementById('filterLimit').value;

  const query = new URLSearchParams({ search, channel, status, page, limit, ajax: '1' });
  window.location.href = `${BASE_URL}/admin/notifications?` + new URLSearchParams({ search, channel, status, page, limit });
}

function resetFilters() {
  window.location.href = `${BASE_URL}/admin/notifications`;
}

function toggleSelectAll(master) {
  document.querySelectorAll('.item-check').forEach(chk => chk.checked = master.checked);
  updateBulkBar();
}

function updateBulkBar() {
  const selected = document.querySelectorAll('.item-check:checked');
  const count = selected.length;
  const bulkBar = document.getElementById('bulkBar');
  document.getElementById('selectedCount').innerText = count;

  if (count > 0) {
    bulkBar.classList.add('show');
  } else {
    bulkBar.classList.remove('show');
  }
}

async function executeBulk(action) {
  const selected = Array.from(document.querySelectorAll('.item-check:checked')).map(c => c.value);
  if (selected.length === 0) return;

  if (action === 'delete' && !confirm(`Are you sure you want to delete ${selected.length} selected notification(s)?`)) {
    return;
  }

  try {
    const res = await fetch(`${BASE_URL}/admin/notifications/bulk`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, ids: selected })
    });
    const data = await res.json();
    if (data.success) {
      showToast('success', data.message);
      setTimeout(() => location.reload(), 600);
    } else {
      showToast('error', data.message);
    }
  } catch(e) {
    showToast('error', 'Bulk action failed');
  }
}

async function markAllRead() {
  try {
    const res = await fetch(`${BASE_URL}/admin/notifications/mark-all-read`, { method: 'POST' });
    const data = await res.json();
    if (data.success) {
      showToast('success', data.message);
      setTimeout(() => location.reload(), 600);
    } else {
      showToast('error', data.message);
    }
  } catch(e) {
    showToast('error', 'Failed to mark all as read');
  }
}

async function toggleSingleRead(id) {
  try {
    const res = await fetch(`${BASE_URL}/admin/notifications/mark-read`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) {
      showToast('success', data.message);
      setTimeout(() => location.reload(), 600);
    } else {
      showToast('error', data.message);
    }
  } catch(e) {
    showToast('error', 'Toggle status failed');
  }
}

async function deleteSingleNotification(id) {
  if (!confirm('Are you sure you want to delete this notification?')) return;

  try {
    const res = await fetch(`${BASE_URL}/admin/notifications/delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (data.success) {
      showToast('success', data.message);
      document.getElementById(`row-${id}`)?.remove();
    } else {
      showToast('error', data.message);
    }
  } catch(e) {
    showToast('error', 'Delete failed');
  }
}

function inspectNotification(id) {
  const item = currentNotifications.find(n => n.encrypted_id === id);
  if (!item) return;

  document.getElementById('mTitle').innerText = item.title || 'Untitled Notification';
  document.getElementById('mType').innerText = `Type: ${item.type} | Channel: ${item.channel.toUpperCase()}`;
  document.getElementById('mBody').innerText = item.body || 'No message content body provided.';
  document.getElementById('mRecipient').innerText = item.user_name ? `${item.user_name} (${item.user_email})` : 'Broadcast (All Users)';
  document.getElementById('mSentAt').innerText = item.created_at;
  document.getElementById('mDataJson').innerText = JSON.stringify(item.data_decoded || {}, null, 2);

  document.getElementById('inspectModal').classList.add('show');
}

function openBroadcastModal() {
  document.getElementById('broadcastModal').classList.add('show');
}

function closeModal(id) {
  document.getElementById(id).classList.remove('show');
}

async function submitBroadcast() {
  const payload = {
    target: document.getElementById('bTarget').value,
    channel: document.getElementById('bChannel').value,
    title: document.getElementById('bTitle').value.trim(),
    body: document.getElementById('bBody').value.trim()
  };

  try {
    const res = await fetch(`${BASE_URL}/admin/notifications/send`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      showToast('success', data.message);
      closeModal('broadcastModal');
      setTimeout(() => location.reload(), 600);
    } else {
      showToast('error', data.message);
    }
  } catch(e) {
    showToast('error', 'Failed to dispatch broadcast');
  }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
