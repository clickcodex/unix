<?php
$pageTitle = 'Measurement Units Manager';
$activeMenu = 'units';

require_once __DIR__ . '/../layouts/header.php';

$units = $units ?? [];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">Units Management</h1>
      <p class="text-slate-500 text-sm mt-1">Manage standard product measurement metrics (Weight, Volume, Length, Area, Count)</p>
    </div>
    <div>
      <button onclick="openUnitModal('add')" class="btn-primary text-sm shadow-md shadow-cc-blue/20 flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium">
        <span class="material-icons text-[18px]">add</span> Add New Unit
      </button>
    </div>
  </div>

  <!-- Units Table Card -->
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm text-slate-700">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
          <tr>
            <th class="px-6 py-3.5">Unit Name</th>
            <th class="px-6 py-3.5">Symbol</th>
            <th class="px-6 py-3.5">Unit Type</th>
            <th class="px-6 py-3.5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 font-medium">
          <?php if (empty($units)): ?>
            <tr>
              <td colspan="4" class="px-6 py-8 text-center text-slate-400">
                <span class="material-icons text-4xl block mb-1">square_foot</span>
                No measurement units configured yet.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($units as $u): ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-4 font-semibold text-slate-900">
                  <?= htmlspecialchars($u['name']) ?>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-slate-100 text-slate-800 border border-slate-200">
                    <?= htmlspecialchars($u['symbol']) ?>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 capitalize">
                    <?= htmlspecialchars($u['unit_type']) ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                  <button onclick="editUnit(<?= htmlspecialchars(json_encode($u)) ?>)" class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs inline-flex items-center gap-1">
                    <span class="material-icons text-sm">edit</span> Edit
                  </button>
                  <button onclick="deleteUnit('<?= $u['encrypted_id'] ?>', '<?= htmlspecialchars($u['name'], ENT_QUOTES) ?>')" class="text-rose-600 hover:text-rose-900 font-semibold text-xs inline-flex items-center gap-1">
                    <span class="material-icons text-sm">delete</span> Delete
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal for Add / Edit Unit -->
<div id="unitModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
      <h3 id="modalTitle" class="font-bold text-slate-900 text-lg">Add Measurement Unit</h3>
      <button onclick="closeUnitModal()" class="text-slate-400 hover:text-slate-600"><span class="material-icons">close</span></button>
    </div>
    <form id="unitForm" onsubmit="saveUnit(event)" class="p-6 space-y-4">
      <input type="hidden" id="unit_id" name="unit_id" value="">
      
      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Name *</label>
        <input type="text" id="name" name="name" required placeholder="e.g. Kilogram, Litre, Pieces" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Symbol *</label>
        <input type="text" id="symbol" name="symbol" required placeholder="e.g. kg, L, pcs" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Unit Type *</label>
        <select id="unit_type" name="unit_type" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
          <option value="weight">Weight</option>
          <option value="volume">Volume</option>
          <option value="count">Count / Quantity</option>
          <option value="length">Length</option>
          <option value="area">Area</option>
        </select>
      </div>

      <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
        <button type="button" onclick="closeUnitModal()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save Unit</button>
      </div>
    </form>
  </div>
</div>

<script>
function openUnitModal(mode) {
  document.getElementById('unitForm').reset();
  document.getElementById('unit_id').value = '';
  document.getElementById('modalTitle').innerText = mode === 'add' ? 'Add Measurement Unit' : 'Edit Measurement Unit';
  document.getElementById('unitModal').classList.remove('hidden');
  document.getElementById('unitModal').classList.add('flex');
}

function closeUnitModal() {
  document.getElementById('unitModal').classList.add('hidden');
  document.getElementById('unitModal').classList.remove('flex');
}

function editUnit(unit) {
  openUnitModal('edit');
  document.getElementById('unit_id').value = unit.encrypted_id;
  document.getElementById('name').value = unit.name;
  document.getElementById('symbol').value = unit.symbol;
  document.getElementById('unit_type').value = unit.unit_type;
}

function saveUnit(e) {
  e.preventDefault();
  const form = document.getElementById('unitForm');
  const formData = new FormData(form);
  const isEdit = !!document.getElementById('unit_id').value;
  const url = '<?= BASE_URL ?>/admin/units/' + (isEdit ? 'update' : 'store');

  fetch(url, {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      location.reload();
    } else {
      alert(data.message || 'Error saving unit.');
    }
  })
  .catch(err => alert('Network error.'));
}

function deleteUnit(id, name) {
  if (!confirm('Are you sure you want to delete unit "' + name + '"?')) return;
  fetch('<?= BASE_URL ?>/admin/units/delete', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ unit_id: id })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) location.reload();
    else alert(data.message || 'Failed to delete unit.');
  });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
