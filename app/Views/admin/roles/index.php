<?php
$pageTitle = 'Role & Permissions Matrix';
$activeMenu = 'roles';

require_once __DIR__ . '/../layouts/header.php';

$roles = $roles ?? [];
$permissionsGrouped = $permissionsGrouped ?? [];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">Role &amp; Permissions Management</h1>
      <p class="text-slate-500 text-sm mt-1">Configure staff access control lists (ACL) and fine-grained module permissions</p>
    </div>
    <div>
      <button onclick="openNewRoleModal()" class="btn-primary text-sm shadow-md shadow-cc-blue/20 flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium">
        <span class="material-icons text-[18px]">add_moderator</span> Create New Role
      </button>
    </div>
  </div>

  <!-- Roles Grid -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <?php foreach ($roles as $r): ?>
      <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm space-y-4 hover:border-indigo-300 transition-colors">
        <div class="flex items-start justify-between">
          <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
            <span class="material-icons">admin_panel_settings</span>
          </div>
          <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-semibold bg-slate-100 text-slate-700">
            <?= htmlspecialchars($r['slug']) ?>
          </span>
        </div>
        <div>
          <h3 class="font-bold text-slate-900 text-base"><?= htmlspecialchars($r['name']) ?></h3>
          <p class="text-xs text-slate-500 mt-0.5"><?= $r['user_count'] ?> active staff users assigned</p>
        </div>
        <div class="pt-2 border-t border-slate-100">
          <button onclick="openPermissionMatrix('<?= $r['encrypted_id'] ?>', '<?= htmlspecialchars($r['name'], ENT_QUOTES) ?>')" class="w-full text-center px-3 py-2 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 flex items-center justify-center gap-1">
            <span class="material-icons text-[16px]">tune</span> Configure Permissions
          </button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Permission Matrix Modal -->
  <div id="permModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl max-h-[85vh] flex flex-col overflow-hidden border border-slate-100">
      <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
        <div>
          <h3 class="font-bold text-slate-900 text-lg">Configure Permissions</h3>
          <p id="permRoleTitle" class="text-xs text-slate-500"></p>
        </div>
        <button onclick="closePermModal()" class="text-slate-400 hover:text-slate-600"><span class="material-icons">close</span></button>
      </div>
      
      <form id="permForm" onsubmit="saveRolePermissions(event)" class="flex-1 overflow-y-auto p-6 space-y-6">
        <input type="hidden" id="perm_role_id" name="role_id" value="">

        <?php foreach ($permissionsGrouped as $module => $perms): ?>
          <div class="bg-slate-50/70 border border-slate-200/80 rounded-xl p-4 space-y-3">
            <h4 class="font-bold text-xs font-mono uppercase text-indigo-700 tracking-wider flex items-center gap-1.5">
              <span class="material-icons text-[16px]">grid_view</span> <?= htmlspecialchars($module) ?> Module
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
              <?php foreach ($perms as $p): ?>
                <label class="flex items-center gap-2 p-2 rounded-lg bg-white border border-slate-200 cursor-pointer hover:border-indigo-300">
                  <input type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" class="perm-checkbox w-4 h-4 text-indigo-600 rounded border-slate-300 focus:ring-indigo-500">
                  <span class="text-xs font-semibold text-slate-800"><?= htmlspecialchars($p['name']) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="pt-4 flex justify-end gap-3 border-t border-slate-100 sticky bottom-0 bg-white p-4 -mx-6 -mb-6 border-t shadow-lg">
          <button type="button" onclick="closePermModal()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
          <button type="submit" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-md">Save Permissions</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Create Role Modal -->
  <div id="newRoleModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100">
      <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
        <h3 class="font-bold text-slate-900 text-lg">Create Staff Role</h3>
        <button onclick="closeNewRoleModal()" class="text-slate-400 hover:text-slate-600"><span class="material-icons">close</span></button>
      </div>
      <form id="newRoleForm" onsubmit="saveNewRole(event)" class="p-6 space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Role Display Name *</label>
          <input type="text" name="name" required placeholder="e.g. Order Dispatch Manager" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Role Key Identifier (Slug) *</label>
          <input type="text" name="slug" required placeholder="e.g. dispatch_manager" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>
        <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
          <button type="button" onclick="closeNewRoleModal()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
          <button type="submit" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Create Role</button>
        </div>
      </form>
    </div>
  </div>

</div>

<script>
function openPermissionMatrix(roleId, roleName) {
  document.getElementById('perm_role_id').value = roleId;
  document.getElementById('permRoleTitle').innerText = 'Editing permission map for role: ' + roleName;
  
  // Uncheck all checkboxes first
  document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);

  fetch('<?= BASE_URL ?>/admin/roles/permissions?role_id=' + roleId)
    .then(r => r.json())
    .then(data => {
      if (data.success && data.permission_ids) {
        const activeIds = data.permission_ids.map(String);
        document.querySelectorAll('.perm-checkbox').forEach(cb => {
          if (activeIds.includes(cb.value)) cb.checked = true;
        });
      }
      document.getElementById('permModal').classList.remove('hidden');
      document.getElementById('permModal').classList.add('flex');
    });
}

function closePermModal() {
  document.getElementById('permModal').classList.add('hidden');
  document.getElementById('permModal').classList.remove('flex');
}

function saveRolePermissions(e) {
  e.preventDefault();
  const form = document.getElementById('permForm');
  const formData = new FormData(form);

  fetch('<?= BASE_URL ?>/admin/roles/update-permissions', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      alert('Role permissions updated successfully!');
      closePermModal();
    } else {
      alert(data.message || 'Failed to update role permissions.');
    }
  });
}

function openNewRoleModal() {
  document.getElementById('newRoleForm').reset();
  document.getElementById('newRoleModal').classList.remove('hidden');
  document.getElementById('newRoleModal').classList.add('flex');
}

function closeNewRoleModal() {
  document.getElementById('newRoleModal').classList.add('hidden');
  document.getElementById('newRoleModal').classList.remove('flex');
}

function saveNewRole(e) {
  e.preventDefault();
  const form = document.getElementById('newRoleForm');
  const formData = new FormData(form);

  fetch('<?= BASE_URL ?>/admin/roles/store', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) location.reload();
    else alert(data.message || 'Error creating role.');
  });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
