<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- ================= MAIN CONTENT ================= -->
<main class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">

  <!-- Header & Title Banner -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
    <div class="flex items-center gap-4">
      <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center text-white shadow-lg shadow-slate-900/20 shrink-0">
        <span class="material-icons text-3xl">history</span>
      </div>
      <div>
        <h1 class="text-2xl font-bold font-heading text-slate-800">System Audit Log Trail</h1>
        <p class="text-sm text-slate-500 mt-0.5">Immutable record of administrative actions, system settings changes, logins and data modifications.</p>
      </div>
    </div>
    <div class="flex items-center gap-3 shrink-0">
      <a href="<?= $baseUrl ?>/admin/dashboard" class="btn-secondary">
        <span class="material-icons text-[18px]">arrow_back</span> Dashboard
      </a>
      <a href="<?= $baseUrl ?>/admin/audit/export?<?= http_build_query($_GET) ?>" class="btn-primary">
        <span class="material-icons text-[18px]">download</span> Export CSV
      </a>
    </div>
  </div>

  <!-- KPI Cards Grid -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Events -->
    <div class="stat-mini">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Audit Logs</span>
        <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center">
          <span class="material-icons text-[20px]">receipt_long</span>
        </div>
      </div>
      <p class="text-2xl font-extrabold font-heading text-slate-800 mt-2"><?= number_format($kpiData['total_logs'] ?? 0) ?></p>
      <p class="text-xs text-slate-400 mt-1">Recorded audit events</p>
    </div>

    <!-- Today's Events -->
    <div class="stat-mini">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Recorded Today</span>
        <div class="w-9 h-9 rounded-xl bg-cc-blue/10 text-cc-blue flex items-center justify-center">
          <span class="material-icons text-[20px]">today</span>
        </div>
      </div>
      <p class="text-2xl font-extrabold font-heading text-slate-800 mt-2"><?= number_format($kpiData['today_logs'] ?? 0) ?></p>
      <p class="text-xs text-slate-400 mt-1">Actions in last 24 hours</p>
    </div>

    <!-- Active Users -->
    <div class="stat-mini">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Active Actors</span>
        <div class="w-9 h-9 rounded-xl bg-cc-purple/10 text-cc-purple flex items-center justify-center">
          <span class="material-icons text-[20px]">manage_accounts</span>
        </div>
      </div>
      <p class="text-2xl font-extrabold font-heading text-slate-800 mt-2"><?= number_format($kpiData['total_users'] ?? 0) ?></p>
      <p class="text-xs text-slate-400 mt-1">Distinct user accounts logged</p>
    </div>

    <!-- Security & System Actions -->
    <div class="stat-mini">
      <div class="flex items-center justify-between">
        <span class="text-xs font-bold uppercase tracking-wider text-slate-400">System & Security</span>
        <div class="w-9 h-9 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center">
          <span class="material-icons text-[20px]">shield</span>
        </div>
      </div>
      <p class="text-2xl font-extrabold font-heading text-slate-800 mt-2"><?= number_format(($kpiData['security_actions'] ?? 0) + ($kpiData['system_actions'] ?? 0)) ?></p>
      <p class="text-xs text-slate-400 mt-1">Settings & critical modifications</p>
    </div>
  </div>

  <!-- Filter & Search Bar -->
  <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm space-y-4">
    <form method="GET" action="<?= $baseUrl ?>/admin/audit" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">

      <!-- Keyword Search -->
      <div class="lg:col-span-2">
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Search Keyword</label>
        <div class="relative">
          <span class="material-icons absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
          <input type="text" name="search" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Search event, model, IP, user name..." class="form-field pl-9">
        </div>
      </div>

      <!-- Event Category Filter -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Event Action</label>
        <select name="event" class="form-field">
          <option value="">All Event Types</option>
          <?php foreach ($eventCategories as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>" <?= ($filters['event'] ?? '') === $cat ? 'selected' : '' ?>>
              <?= htmlspecialchars($cat) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Date From -->
      <div>
        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">From Date</label>
        <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>" class="form-field">
      </div>

      <!-- Date To -->
      <div class="flex items-center gap-2">
        <div class="w-full">
          <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">To Date</label>
          <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>" class="form-field">
        </div>
        <button type="submit" title="Apply Filter" class="btn-primary py-2 px-3 self-end shrink-0">
          <span class="material-icons text-[18px]">filter_list</span>
        </button>
      </div>

    </form>
  </div>

  <!-- Audit Log Data Table -->
  <div class="section-card">
    <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
      <div class="flex items-center gap-2">
        <span class="material-icons text-slate-500 text-[20px]">table_rows</span>
        <h2 class="text-sm font-bold text-slate-700 uppercase tracking-wider">Audit Records (<?= number_format($logData['totalCount']) ?>)</h2>
      </div>
      <span class="text-xs text-slate-500">Page <?= $logData['currentPage'] ?> of <?= $logData['totalPages'] ?></span>
    </div>

    <div class="overflow-x-auto">
      <table class="data-table">
        <thead>
          <tr>
            <th class="w-16">ID</th>
            <th>Timestamp</th>
            <th>User / Actor</th>
            <th>Event Action</th>
            <th>Target Model</th>
            <th>IP Address</th>
            <th class="text-right">Details</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($logData['logs'])): ?>
            <tr>
              <td colspan="7" class="text-center py-12 text-slate-400">
                <span class="material-icons text-4xl block mb-2 opacity-50">search_off</span>
                No audit log records match your filter criteria.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($logData['logs'] as $log): ?>
              <?php
                // Badge color styling based on event category
                $badgeClass = 'badge-slate';
                $ev = strtolower($log['event']);
                if (strpos($ev, 'login') !== false || strpos($ev, 'create') !== false) {
                    $badgeClass = 'badge-green';
                } elseif (strpos($ev, 'delete') !== false || strpos($ev, 'revoke') !== false || strpos($ev, 'fail') !== false) {
                    $badgeClass = 'badge-red';
                } elseif (strpos($ev, 'update') !== false || strpos($ev, 'edit') !== false || strpos($ev, 'setting') !== false) {
                    $badgeClass = 'badge-blue';
                } elseif (strpos($ev, 'export') !== false) {
                    $badgeClass = 'badge-purple';
                }

                $userName = $log['user_name'] ?? 'System';
                $userEmail = $log['user_email'] ?? 'Automated / Guest';
                $words = explode(' ', $userName);
                $initials = strtoupper(substr($words[0] ?? 'S', 0, 1) . substr($words[1] ?? '', 0, 1));
              ?>
              <tr>
                <td class="font-mono text-xs font-semibold text-slate-400">#<?= $log['id'] ?></td>
                <td class="text-xs text-slate-600 whitespace-nowrap">
                  <?= date('M d, Y · H:i:s', strtotime($log['created_at'])) ?>
                </td>
                <td>
                  <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[11px] shrink-0">
                      <?= $initials ?>
                    </div>
                    <div>
                      <p class="text-xs font-bold text-slate-800 leading-tight"><?= htmlspecialchars($userName) ?></p>
                      <p class="text-[11px] text-slate-400 leading-tight"><?= htmlspecialchars($userEmail) ?></p>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge <?= $badgeClass ?> font-mono text-[11px]">
                    <?= htmlspecialchars($log['event']) ?>
                  </span>
                </td>
                <td>
                  <?php if (!empty($log['model'])): ?>
                    <span class="text-xs font-medium text-slate-700">
                      <?= htmlspecialchars($log['model']) ?> 
                      <?php if ($log['model_id']): ?>
                        <span class="font-mono text-slate-400">#<?= $log['model_id'] ?></span>
                      <?php endif; ?>
                    </span>
                  <?php else: ?>
                    <span class="text-xs text-slate-400">—</span>
                  <?php endif; ?>
                </td>
                <td class="font-mono text-xs text-slate-500">
                  <?= htmlspecialchars($log['ip_address'] ?? '127.0.0.1') ?>
                </td>
                <td class="text-right">
                  <button type="button" onclick="viewLogDetails('<?= $log['encrypted_id'] ?>')" title="View Payload" class="btn-icon">
                    <span class="material-icons text-[18px]">visibility</span>
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination Footer -->
    <?php if ($logData['totalPages'] > 1): ?>
      <div class="p-4 border-t border-slate-200 flex items-center justify-between bg-slate-50">
        <p class="text-xs text-slate-500">Showing page <?= $logData['currentPage'] ?> of <?= $logData['totalPages'] ?></p>
        <div class="flex items-center gap-1.5">
          <?php for ($p = 1; $p <= $logData['totalPages']; $p++): ?>
            <?php if ($p == 1 || $p == $logData['totalPages'] || abs($p - $logData['currentPage']) <= 2): ?>
              <?php
                $queryParams = $_GET;
                $queryParams['page'] = $p;
                $pageUrl = $baseUrl . '/admin/audit?' . http_build_query($queryParams);
              ?>
              <a href="<?= $pageUrl ?>" class="w-8 h-8 rounded-lg text-xs font-semibold flex items-center justify-center transition <?= $p == $logData['currentPage'] ? 'bg-cc-blue text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' ?>">
                <?= $p ?>
              </a>
            <?php endif; ?>
          <?php endfor; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>

</main>


<!-- ================= DETAIL MODAL ================= -->
<div class="modal-overlay" id="logDetailModal">
  <div class="modal-box max-w-2xl">
    <div class="flex items-center justify-between p-5 border-b border-slate-200">
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center text-slate-600">
          <span class="material-icons text-[20px]">info</span>
        </div>
        <h3 class="font-bold text-slate-800 text-lg">Audit Event Payload Details</h3>
      </div>
      <button type="button" onclick="closeDetailModal()" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
        <span class="material-icons">close</span>
      </button>
    </div>

    <div class="p-6 space-y-6 text-sm" id="modalBodyContent">
      <div class="flex items-center justify-center py-8 text-slate-400 gap-2">
        <span class="material-icons animate-spin">sync</span> Loading audit event payload...
      </div>
    </div>

    <div class="p-4 border-t border-slate-200 bg-slate-50 flex justify-end">
      <button type="button" onclick="closeDetailModal()" class="btn-secondary">Close</button>
    </div>
  </div>
</div>


<script>
  async function viewLogDetails(encryptedId) {
    const modal = document.getElementById('logDetailModal');
    const body = document.getElementById('modalBodyContent');
    if (!modal || !body) return;

    modal.classList.add('show');
    body.innerHTML = `
      <div class="flex items-center justify-center py-8 text-slate-400 gap-2">
        <span class="material-icons animate-spin">sync</span> Loading audit event payload...
      </div>
    `;

    try {
      const response = await fetch('<?= $baseUrl ?>/admin/audit/detail/' + encryptedId);
      const res = await response.json();

      if (res.success && res.log) {
        const log = res.log;
        
        let oldJsonHtml = log.old_data_parsed ? `<pre class="bg-slate-900 text-emerald-400 p-3 rounded-xl text-xs overflow-x-auto font-mono">${escapeHtml(JSON.stringify(log.old_data_parsed, null, 2))}</pre>` : '<p class="text-slate-400 italic text-xs">None</p>';
        let newJsonHtml = log.new_data_parsed ? `<pre class="bg-slate-900 text-sky-400 p-3 rounded-xl text-xs overflow-x-auto font-mono">${escapeHtml(JSON.stringify(log.new_data_parsed, null, 2))}</pre>` : '<p class="text-slate-400 italic text-xs">None</p>';

        body.innerHTML = `
          <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200 text-xs">
            <div>
              <span class="block text-slate-400 uppercase font-bold tracking-wider">Event Action</span>
              <span class="font-mono font-bold text-cc-blue text-sm">${escapeHtml(log.event)}</span>
            </div>
            <div>
              <span class="block text-slate-400 uppercase font-bold tracking-wider">Timestamp</span>
              <span class="font-semibold text-slate-700">${escapeHtml(log.created_at)}</span>
            </div>
            <div>
              <span class="block text-slate-400 uppercase font-bold tracking-wider">User / Actor</span>
              <span class="font-semibold text-slate-700">${escapeHtml(log.user_name || 'System / Anonymous')}</span>
            </div>
            <div>
              <span class="block text-slate-400 uppercase font-bold tracking-wider">IP Address</span>
              <span class="font-mono text-slate-700">${escapeHtml(log.ip_address || '127.0.0.1')}</span>
            </div>
            <div>
              <span class="block text-slate-400 uppercase font-bold tracking-wider">Target Model</span>
              <span class="font-semibold text-slate-700">${escapeHtml(log.model || 'N/A')} ${log.model_id ? '#' + log.model_id : ''}</span>
            </div>
            <div>
              <span class="block text-slate-400 uppercase font-bold tracking-wider">User Agent</span>
              <span class="text-slate-500 truncate block" title="${escapeHtml(log.user_agent || 'Unknown')}">${escapeHtml(log.user_agent || 'Unknown')}</span>
            </div>
          </div>

          <div class="space-y-3">
            <h4 class="font-bold text-slate-800 text-xs uppercase tracking-wider">Data Payload State Changes</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">Previous State (Old Data)</label>
                ${oldJsonHtml}
              </div>
              <div>
                <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">New State (Updated Data)</label>
                ${newJsonHtml}
              </div>
            </div>
          </div>
        `;
      } else {
        body.innerHTML = `<div class="text-rose-500 py-4 text-center">${escapeHtml(res.message || 'Failed to load audit record details.')}</div>`;
      }
    } catch (err) {
      console.error(err);
      body.innerHTML = `<div class="text-rose-500 py-4 text-center">An error occurred while fetching audit details.</div>`;
    }
  }

  function closeDetailModal() {
    const modal = document.getElementById('logDetailModal');
    if (modal) modal.classList.remove('show');
  }

  function escapeHtml(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
