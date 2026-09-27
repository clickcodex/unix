<?php
$pageTitle = 'User Management System';
$activeMenu = 'users';

require_once __DIR__ . '/../layouts/header.php';

$users = $users ?? [];
$pagination = $pagination ?? ['current_page' => 1, 'limit' => 10, 'total_records' => 0, 'total_pages' => 1];
$kpiTotal = $kpiData['total'] ?? 0;
$kpiActive = $kpiData['active'] ?? 0;
$kpiVerified = $kpiData['verified'] ?? 0;
$kpiVip = $kpiData['vip'] ?? 0;
$roles = $roles ?? [];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Header Banner -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">User Management System</h1>
      <p class="text-slate-500 text-sm mt-1">Manage user accounts, roles, permission access, addresses &amp; customer purchase history</p>
    </div>
    <div>
      <button onclick="openUserModal('add')" class="btn-primary text-sm shadow-md shadow-cc-blue/20">
        <span class="material-icons text-[18px]">person_add</span> Add New User
      </button>
    </div>
  </div>

  <!-- KPI Cards Strip -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
    <div class="stat-mini">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-cc-blue/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-blue text-[20px]">group</span>
        </div>
        <div>
          <p class="text-xl font-bold text-slate-800" id="kpiTotal"><?= number_format($kpiTotal) ?></p>
          <p class="text-[11px] text-slate-400 font-medium">Total Registered Users</p>
        </div>
      </div>
    </div>
    <div class="stat-mini">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center shrink-0">
          <span class="material-icons text-green-600 text-[20px]">check_circle</span>
        </div>
        <div>
          <p class="text-xl font-bold text-slate-800" id="kpiActive"><?= number_format($kpiActive) ?></p>
          <p class="text-[11px] text-slate-400 font-medium">Active Accounts</p>
        </div>
      </div>
    </div>
    <div class="stat-mini">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-cc-purple/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-purple text-[20px]">verified_user</span>
        </div>
        <div>
          <p class="text-xl font-bold text-slate-800" id="kpiVerified"><?= number_format($kpiVerified) ?></p>
          <p class="text-[11px] text-slate-400 font-medium">Verified Accounts</p>
        </div>
      </div>
    </div>
    <div class="stat-mini">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-cc-yellow/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-yellow text-[20px]">star</span>
        </div>
        <div>
          <p class="text-xl font-bold text-slate-800" id="kpiVip"><?= number_format($kpiVip) ?></p>
          <p class="text-[11px] text-slate-400 font-medium">VIP Customers</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Table Card -->
  <div class="section-card">
    
    <!-- Filter Control Bar -->
    <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
      <!-- Search Input -->
      <div class="relative flex-1 max-w-md">
        <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
        <input type="text" id="userSearchInput" placeholder="Search by name, email, phone..." 
               class="form-field pl-9 text-xs" oninput="debounceFilterUsers()">
      </div>

      <!-- Filters & Sorting -->
      <div class="flex flex-wrap items-center gap-2.5">
        <!-- Role Filter -->
        <select id="userRoleFilter" class="form-field text-xs w-auto py-2" onchange="fetchUsersAJAX()">
          <option value="all">All Roles</option>
          <?php foreach ($roles as $r): ?>
            <option value="<?= htmlspecialchars($r['slug']) ?>"><?= htmlspecialchars($r['name']) ?></option>
          <?php endforeach; ?>
        </select>

        <!-- Status Filter -->
        <select id="userStatusFilter" class="form-field text-xs w-auto py-2" onchange="fetchUsersAJAX()">
          <option value="all">All Statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="verified">Verified</option>
          <option value="unverified">Unverified</option>
        </select>

        <!-- Sort Filter -->
        <select id="userSortFilter" class="form-field text-xs w-auto py-2" onchange="fetchUsersAJAX()">
          <option value="newest">Newest Registered</option>
          <option value="oldest">Oldest Registered</option>
          <option value="spent_desc">Highest Spend (₹)</option>
          <option value="orders_desc">Most Orders</option>
          <option value="name_asc">Name A-Z</option>
        </select>

        <!-- Items Per Page -->
        <select id="userLimitFilter" class="form-field text-xs w-auto py-2" onchange="fetchUsersAJAX()">
          <option value="10">10 / page</option>
          <option value="25">25 / page</option>
          <option value="50">50 / page</option>
        </select>
      </div>
    </div>

    <!-- Data Table Grid -->
    <div class="overflow-x-auto">
      <table class="data-table">
        <thead>
          <tr>
            <th>User Info</th>
            <th>Role</th>
            <th>Orders &amp; Lifetime Spend</th>
            <th>Verification</th>
            <th>Account Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="usersBody">
          <?php if (empty($users)): ?>
            <tr>
              <td colspan="6" class="text-center py-12 text-slate-400">No users found matching current filter parameters.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($users as $u): ?>
              <?php
              $initials = strtoupper(substr($u['name'], 0, 2));
              $roleSlug = $u['role_slugs'][0] ?? 'customer';
              $badgeClass = 'badge-slate';
              if ($roleSlug === 'super_admin') $badgeClass = 'badge-purple';
              elseif ($roleSlug === 'admin') $badgeClass = 'badge-blue';
              elseif ($roleSlug === 'staff' || $roleSlug === 'manager') $badgeClass = 'badge-green';
              ?>
              <tr id="user-row-<?= $u['encrypted_id'] ?>">
                <td>
                  <div class="flex items-center gap-3">
                    <?php if (!empty($u['avatar_url'])): ?>
                      <img src="<?= htmlspecialchars($u['avatar_url']) ?>" class="w-9 h-9 rounded-full object-cover border border-slate-200 shrink-0">
                    <?php else: ?>
                      <div class="w-9 h-9 rounded-full bg-cc-blue/10 flex items-center justify-center text-cc-blue font-bold text-xs shrink-0">
                        <?= $initials ?>
                      </div>
                    <?php endif; ?>
                    <div class="min-w-0">
                      <p class="text-sm font-semibold text-slate-800 truncate flex items-center gap-1.5">
                        <?= htmlspecialchars($u['name']) ?>
                        <?php if ((int)$u['is_verified'] === 1): ?>
                          <span class="material-icons text-cc-blue text-[15px]" title="Verified Account">verified</span>
                        <?php endif; ?>
                      </p>
                      <p class="text-[11px] text-slate-400 truncate"><?= htmlspecialchars($u['email']) ?></p>
                      <?php if (!empty($u['phone'])): ?>
                        <p class="text-[10px] text-slate-400 mono"><?= htmlspecialchars($u['phone']) ?></p>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge <?= $badgeClass ?> text-[11px]">
                    <?= htmlspecialchars($u['primary_role']) ?>
                  </span>
                </td>
                <td>
                  <div class="mono text-xs">
                    <p class="font-bold text-slate-900">₹<?= number_format($u['lifetime_value'], 2) ?></p>
                    <p class="text-[11px] text-slate-400"><?= number_format($u['total_orders']) ?> orders</p>
                  </div>
                </td>
                <td>
                  <?php if ((int)$u['is_verified'] === 1): ?>
                    <span class="badge badge-green text-[10px]"><span class="material-icons text-[12px]">check_circle</span> Verified</span>
                  <?php else: ?>
                    <span class="badge badge-red text-[10px]"><span class="material-icons text-[12px]">cancel</span> Unverified</span>
                  <?php endif; ?>
                </td>
                <td>
                  <div class="inline-toggle <?= (int)$u['is_active'] === 1 ? 'on' : '' ?>" 
                       onclick="toggleUserActive('<?= $u['encrypted_id'] ?>', this)" 
                       title="Toggle Active Status">
                    <div class="it-thumb"></div>
                  </div>
                </td>
                <td>
                  <div class="flex items-center gap-1">
                    <!-- Inspect Profile Page -->
                    <a href="<?= $baseUrl ?>/admin/users/view/<?= $u['encrypted_id'] ?>" 
                       class="action-btn text-cc-blue hover:bg-blue-50" 
                       title="View Full User Profile Page">
                      <span class="material-icons text-[16px]">visibility</span>
                    </a>
                    <!-- Edit User -->
                    <button class="action-btn" 
                            onclick="openUserModal('edit', '<?= $u['encrypted_id'] ?>')" 
                            title="Edit User Account">
                      <span class="material-icons text-[16px]">edit</span>
                    </button>
                    <!-- Delete User -->
                    <button class="action-btn danger" 
                            onclick="deleteUserAccount('<?= $u['encrypted_id'] ?>', '<?= htmlspecialchars($u['name']) ?>')" 
                            title="Delete User Account">
                      <span class="material-icons text-[16px]">delete_outline</span>
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination Strip -->
    <div class="p-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
      <div id="paginationInfo">
        Showing <span class="font-bold text-slate-700" id="pgStart">1</span> to <span class="font-bold text-slate-700" id="pgEnd"><?= count($users) ?></span> of <span class="font-bold text-slate-700" id="pgTotal"><?= $pagination['total_records'] ?></span> entries
      </div>
      <div class="flex items-center gap-1.5" id="paginationBtns">
        <!-- Rendered dynamically -->
      </div>
    </div>
  </div>
</div>

<!-- ================= MODAL: ADD / EDIT USER ================= -->
<div class="modal-overlay" id="userModal">
  <div class="modal-box" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
      <h3 class="font-heading text-lg font-bold text-slate-900" id="userModalTitle">Add New User</h3>
      <button class="btn-icon" onclick="closeUserModal()"><span class="material-icons text-[20px]">close</span></button>
    </div>
    <form id="userForm" onsubmit="submitUserForm(event)" class="p-6 space-y-4">
      <input type="hidden" id="fUserEncryptedId" value="">

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Full Name *</label>
          <input type="text" id="fUserName" required placeholder="John Doe" class="form-field">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Email Address *</label>
          <input type="email" id="fUserEmail" required placeholder="john@example.com" class="form-field">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Phone Number</label>
          <input type="text" id="fUserPhone" placeholder="+91 9876543210" class="form-field mono text-xs">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">System Role *</label>
          <select id="fUserRole" required class="form-field text-xs">
            <?php foreach ($roles as $r): ?>
              <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Gender</label>
          <select id="fUserGender" class="form-field text-xs">
            <option value="">-- Unspecified --</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Date of Birth</label>
          <input type="date" id="fUserDob" class="form-field text-xs mono">
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Password</label>
          <input type="password" id="fUserPassword" placeholder="Leave blank to keep existing" class="form-field text-xs">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Avatar Image URL</label>
          <input type="url" id="fUserAvatar" placeholder="https://cdn.example.com/avatar.jpg" class="form-field text-xs mono">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4 p-3 bg-slate-50 rounded-xl border border-slate-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-xs font-bold text-slate-800">Account Active</p>
            <p class="text-[10px] text-slate-400">Can log in &amp; shop</p>
          </div>
          <div class="inline-toggle on" id="toggleUserIsActive" onclick="this.classList.toggle('on')">
            <div class="it-thumb"></div>
          </div>
        </div>

        <div class="flex items-center justify-between border-l border-slate-200 pl-4">
          <div>
            <p class="text-xs font-bold text-slate-800">Email Verified</p>
            <p class="text-[10px] text-slate-400">Verified status badge</p>
          </div>
          <div class="inline-toggle on" id="toggleUserIsVerified" onclick="this.classList.toggle('on')">
            <div class="it-thumb"></div>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" class="btn-secondary text-sm" onclick="closeUserModal()">Cancel</button>
        <button type="submit" id="saveUserBtn" class="btn-primary text-sm">Save User Account</button>
      </div>
    </form>
  </div>
</div>

<!-- ================= MODAL: USER PROFILE INSPECTOR ================= -->
<div class="modal-overlay" id="userProfileModal">
  <div class="modal-box max-w-xl" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
      <h3 class="font-heading text-lg font-bold text-slate-900">User Profile Overview</h3>
      <button class="btn-icon" onclick="closeUserProfileModal()"><span class="material-icons text-[20px]">close</span></button>
    </div>
    <div class="p-6 space-y-4 text-xs" id="userProfileContent">
      <div class="text-center py-8 text-slate-400">Loading user profile parameters...</div>
    </div>
  </div>
</div>

<script>
if (typeof BASE_URL === 'undefined') {
  var BASE_URL = '<?= $baseUrl ?>';
}
let currentPage = <?= $pagination['current_page'] ?>;
let totalPages = <?= $pagination['total_pages'] ?>;
let filterTimeout = null;

function debounceFilterUsers() {
  clearTimeout(filterTimeout);
  filterTimeout = setTimeout(() => {
    currentPage = 1;
    fetchUsersAJAX();
  }, 300);
}

async function fetchUsersAJAX(page = currentPage) {
  currentPage = page;
  const search = document.getElementById('userSearchInput').value.trim();
  const role = document.getElementById('userRoleFilter').value;
  const status = document.getElementById('userStatusFilter').value;
  const sort = document.getElementById('userSortFilter').value;
  const limit = document.getElementById('userLimitFilter').value;

  const url = `${BASE_URL}/admin/users?ajax=1&page=${currentPage}&limit=${limit}&search=${encodeURIComponent(search)}&role=${role}&status=${status}&sort=${sort}`;

  try {
    const res = await fetch(url);
    const data = await res.json();
    if (data.success) {
      renderUsersTable(data.users);
      renderPagination(data.pagination);
      updateKPIs(data.kpis);
    }
  } catch(e) {
    console.error('AJAX fetch users error:', e);
  }
}

function renderUsersTable(users) {
  const tbody = document.getElementById('usersBody');
  if (!users || users.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-12 text-slate-400">No users found matching current filter parameters.</td></tr>`;
    return;
  }

  tbody.innerHTML = users.map(u => {
    const initials = (u.name || 'U').substring(0, 2).toUpperCase();
    const roleSlug = (u.role_slugs && u.role_slugs[0]) ? u.role_slugs[0] : 'customer';
    let badgeClass = 'badge-slate';
    if (roleSlug === 'super_admin') badgeClass = 'badge-purple';
    else if (roleSlug === 'admin') badgeClass = 'badge-blue';
    else if (roleSlug === 'staff' || roleSlug === 'manager') badgeClass = 'badge-green';

    const isAct = parseInt(u.is_active) === 1;
    const isVer = parseInt(u.is_verified) === 1;
    const avatarTag = u.avatar_url ? `<img src="${escapeHtml(u.avatar_url)}" class="w-9 h-9 rounded-full object-cover border border-slate-200 shrink-0">` : `<div class="w-9 h-9 rounded-full bg-cc-blue/10 flex items-center justify-center text-cc-blue font-bold text-xs shrink-0">${initials}</div>`;

    return `
      <tr id="user-row-${u.encrypted_id}">
        <td>
          <div class="flex items-center gap-3">
            ${avatarTag}
            <div class="min-w-0">
              <p class="text-sm font-semibold text-slate-800 truncate flex items-center gap-1.5">
                ${escapeHtml(u.name)}
                ${isVer ? '<span class="material-icons text-cc-blue text-[15px]" title="Verified Account">verified</span>' : ''}
              </p>
              <p class="text-[11px] text-slate-400 truncate">${escapeHtml(u.email)}</p>
              ${u.phone ? `<p class="text-[10px] text-slate-400 mono">${escapeHtml(u.phone)}</p>` : ''}
            </div>
          </div>
        </td>
        <td>
          <span class="badge ${badgeClass} text-[11px]">${escapeHtml(u.primary_role)}</span>
        </td>
        <td>
          <div class="mono text-xs">
            <p class="font-bold text-slate-900">₹${Number(u.lifetime_value).toLocaleString(undefined, {minimumFractionDigits: 2})}</p>
            <p class="text-[11px] text-slate-400">${Number(u.total_orders).toLocaleString()} orders</p>
          </div>
        </td>
        <td>
          <span class="badge ${isVer ? 'badge-green' : 'badge-red'} text-[10px]">
            <span class="material-icons text-[12px]">${isVer ? 'check_circle' : 'cancel'}</span> ${isVer ? 'Verified' : 'Unverified'}
          </span>
        </td>
        <td>
          <div class="inline-toggle ${isAct ? 'on' : ''}" onclick="toggleUserActive('${u.encrypted_id}', this)" title="Toggle Active Status">
            <div class="it-thumb"></div>
          </div>
        </td>
        <td>
          <div class="flex items-center gap-1">
            <a href="${BASE_URL}/admin/users/view/${u.encrypted_id}" class="action-btn text-cc-blue hover:bg-blue-50" title="View Full User Profile Page"><span class="material-icons text-[16px]">visibility</span></a>
            <button class="action-btn" onclick="openUserModal('edit', '${u.encrypted_id}')" title="Edit User Account"><span class="material-icons text-[16px]">edit</span></button>
            <button class="action-btn danger" onclick="deleteUserAccount('${u.encrypted_id}', '${escapeHtml(u.name)}')" title="Delete User Account"><span class="material-icons text-[16px]">delete_outline</span></button>
          </div>
        </td>
      </tr>
    `;
  }).join('');
}

function renderPagination(pg) {
  currentPage = pg.current_page;
  totalPages = pg.total_pages;

  const start = pg.total_records > 0 ? (pg.current_page - 1) * pg.limit + 1 : 0;
  const end = Math.min(pg.current_page * pg.limit, pg.total_records);

  document.getElementById('pgStart').textContent = start;
  document.getElementById('pgEnd').textContent = end;
  document.getElementById('pgTotal').textContent = pg.total_records;

  const btns = document.getElementById('paginationBtns');
  let html = `<button onclick="fetchUsersAJAX(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''} class="btn-secondary text-xs px-2.5 py-1 disabled:opacity-40">Prev</button>`;

  for (let i = 1; i <= totalPages; i++) {
    if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
      html += `<button onclick="fetchUsersAJAX(${i})" class="btn-secondary text-xs px-3 py-1 ${i === currentPage ? 'bg-cc-blue text-white hover:bg-blue-600 font-bold border-cc-blue' : ''}">${i}</button>`;
    } else if (i === currentPage - 2 || i === currentPage + 2) {
      html += `<span class="px-1 text-slate-400">...</span>`;
    }
  }

  html += `<button onclick="fetchUsersAJAX(${currentPage + 1})" ${currentPage >= totalPages ? 'disabled' : ''} class="btn-secondary text-xs px-2.5 py-1 disabled:opacity-40">Next</button>`;
  btns.innerHTML = html;
}

function updateKPIs(k) {
  if (!k) return;
  document.getElementById('kpiTotal').textContent = Number(k.total).toLocaleString();
  document.getElementById('kpiActive').textContent = Number(k.active).toLocaleString();
  document.getElementById('kpiVerified').textContent = Number(k.verified).toLocaleString();
  document.getElementById('kpiVip').textContent = Number(k.vip).toLocaleString();
}

async function inspectUserProfile(encryptedId) {
  document.getElementById('userProfileContent').innerHTML = '<div class="text-center py-8 text-slate-400">Loading user profile parameters...</div>';
  document.getElementById('userProfileModal').classList.add('show');

  try {
    const res = await fetch(`${BASE_URL}/admin/users/detail/${encryptedId}`);
    const data = await res.json();
    if (data.success) {
      const u = data.user;
      const addrs = u.addresses || [];
      const sess = u.sessions || [];

      document.getElementById('userProfileContent').innerHTML = `
        <div class="flex items-center gap-4 p-4 bg-slate-50 border border-slate-200 rounded-xl">
          ${u.avatar_url ? `<img src="${escapeHtml(u.avatar_url)}" class="w-14 h-14 rounded-full object-cover border border-slate-200">` : `<div class="w-14 h-14 rounded-full bg-cc-blue/10 flex items-center justify-center text-cc-blue font-bold text-base">${(u.name||'U').substring(0,2).toUpperCase()}</div>`}
          <div>
            <h4 class="font-bold text-slate-900 text-sm flex items-center gap-1.5">
              ${escapeHtml(u.name)}
              ${parseInt(u.is_verified) === 1 ? '<span class="material-icons text-cc-blue text-[16px]">verified</span>' : ''}
            </h4>
            <p class="text-slate-400 mono">${escapeHtml(u.email)} &middot; ${u.phone ? escapeHtml(u.phone) : 'No Phone'}</p>
            <p class="text-[10px] text-slate-400 mono mt-0.5">UUID: ${escapeHtml(u.uuid)}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3 pt-2">
          <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
            <span class="text-slate-400 text-[10px] uppercase font-bold">Total Orders</span>
            <p class="text-base font-bold text-slate-900 mono mt-0.5">${Number(u.order_summary.total_orders || 0).toLocaleString()}</p>
          </div>
          <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl">
            <span class="text-slate-400 text-[10px] uppercase font-bold">Lifetime Value</span>
            <p class="text-base font-bold text-green-600 mono mt-0.5">₹${Number(u.order_summary.lifetime_value || 0).toLocaleString(undefined, {minimumFractionDigits: 2})}</p>
          </div>
        </div>

        <div class="space-y-2 pt-2 border-t border-slate-100">
          <h5 class="font-bold text-slate-800">Saved Delivery Addresses (${addrs.length})</h5>
          ${addrs.length === 0 ? '<p class="text-slate-400 text-[11px]">No addresses saved.</p>' : addrs.map(a => `
            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-[11px]">
              <p class="font-bold text-slate-800">${escapeHtml(a.recipient_name)} (${escapeHtml(a.label || 'Home')})</p>
              <p class="text-slate-500">${escapeHtml(a.address_line1)}, ${escapeHtml(a.city)}, ${escapeHtml(a.state)} - ${escapeHtml(a.postal_code)}</p>
            </div>
          `).join('')}
        </div>
      `;
    }
  } catch(e) {
    document.getElementById('userProfileContent').innerHTML = '<div class="text-center py-8 text-red-500">Failed to load user profile.</div>';
  }
}

function closeUserProfileModal() {
  document.getElementById('userProfileModal').classList.remove('show');
}

function openUserModal(mode, encryptedId = null) {
  document.getElementById('userForm').reset();
  document.getElementById('fUserEncryptedId').value = '';
  document.getElementById('toggleUserIsActive').classList.add('on');
  document.getElementById('toggleUserIsVerified').classList.add('on');

  if (mode === 'add') {
    document.getElementById('userModalTitle').textContent = 'Add New User';
    document.getElementById('userModal').classList.add('show');
  } else if (mode === 'edit') {
    document.getElementById('userModalTitle').textContent = 'Edit User Account';
    fetchUserForEdit(encryptedId);
  }
}

async function fetchUserForEdit(encryptedId) {
  try {
    const res = await fetch(`${BASE_URL}/admin/users/detail/${encryptedId}`);
    const data = await res.json();
    if (data.success) {
      const u = data.user;
      document.getElementById('fUserEncryptedId').value = u.encrypted_id;
      document.getElementById('fUserName').value = u.name;
      document.getElementById('fUserEmail').value = u.email;
      document.getElementById('fUserPhone').value = u.phone || '';
      document.getElementById('fUserGender').value = u.gender || '';
      document.getElementById('fUserDob').value = u.date_of_birth || '';
      document.getElementById('fUserAvatar').value = u.avatar_url || '';

      if (u.roles && u.roles[0]) {
        document.getElementById('fUserRole').value = u.roles[0].id;
      }

      const toggleAct = document.getElementById('toggleUserIsActive');
      if (parseInt(u.is_active) === 1) toggleAct.classList.add('on'); else toggleAct.classList.remove('on');

      const toggleVer = document.getElementById('toggleUserIsVerified');
      if (parseInt(u.is_verified) === 1) toggleVer.classList.add('on'); else toggleVer.classList.remove('on');

      document.getElementById('userModal').classList.add('show');
    }
  } catch(e) {
    alert('Failed to load user data');
  }
}

function closeUserModal() {
  document.getElementById('userModal').classList.remove('show');
}

async function submitUserForm(e) {
  e.preventDefault();
  const encryptedId = document.getElementById('fUserEncryptedId').value;
  const isEdit = !!encryptedId;

  const payload = {
    user_id: encryptedId,
    name: document.getElementById('fUserName').value.trim(),
    email: document.getElementById('fUserEmail').value.trim(),
    phone: document.getElementById('fUserPhone').value.trim(),
    role_id: document.getElementById('fUserRole').value,
    gender: document.getElementById('fUserGender').value,
    date_of_birth: document.getElementById('fUserDob').value,
    password: document.getElementById('fUserPassword').value,
    avatar_url: document.getElementById('fUserAvatar').value.trim(),
    is_active: document.getElementById('toggleUserIsActive').classList.contains('on') ? 1 : 0,
    is_verified: document.getElementById('toggleUserIsVerified').classList.contains('on') ? 1 : 0
  };

  const endpoint = isEdit ? `${BASE_URL}/admin/users/update` : `${BASE_URL}/admin/users/store`;

  try {
    const res = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      showToast('success', isEdit ? 'User account updated successfully!' : 'User account created successfully!');
      closeUserModal();
      fetchUsersAJAX();
    } else {
      showToast('error', data.message || 'Error saving user account');
    }
  } catch(err) {
    showToast('error', 'Network error while saving user account');
  }
}

async function toggleUserActive(encryptedId, el) {
  try {
    const res = await fetch(`${BASE_URL}/admin/users/toggle-active`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: encryptedId })
    });
    const data = await res.json();
    if (data.success) {
      el.classList.toggle('on');
      showToast('success', 'User active status toggled');
    } else {
      showToast('error', data.message || 'Failed to toggle user status');
    }
  } catch(e) {
    showToast('error', 'Error toggling user status');
  }
}

async function deleteUserAccount(encryptedId, name) {
  if (!confirm(`Are you sure you want to delete user account "${name}"?`)) return;

  try {
    const res = await fetch(`${BASE_URL}/admin/users/delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ user_id: encryptedId })
    });
    const data = await res.json();
    if (data.success) {
      showToast('success', `User "${name}" deleted successfully`);
      fetchUsersAJAX();
    } else {
      showToast('error', data.message || 'Failed to delete user account');
    }
  } catch(e) {
    showToast('error', 'Network error while deleting user account');
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

// Initial Pagination render
renderPagination(<?= json_encode($pagination) ?>);
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
