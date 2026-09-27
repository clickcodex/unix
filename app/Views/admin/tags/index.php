<?php
$pageTitle = 'Product Tags Manager';
$activeMenu = 'tags';

require_once __DIR__ . '/../layouts/header.php';

$tags = $tags ?? [];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">Product Tags Management</h1>
      <p class="text-slate-500 text-sm mt-1">Configure product highlights, badge categories (Featured, Best Seller, Trending) and color themes</p>
    </div>
    <div>
      <button onclick="openTagModal('add')" class="btn-primary text-sm shadow-md shadow-cc-blue/20 flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium">
        <span class="material-icons text-[18px]">add</span> Add New Tag
      </button>
    </div>
  </div>

  <!-- Tags Table Card -->
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm text-slate-700">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
          <tr>
            <th class="px-6 py-3.5">Tag Name</th>
            <th class="px-6 py-3.5">Slug</th>
            <th class="px-6 py-3.5">Type</th>
            <th class="px-6 py-3.5">Badge Preview</th>
            <th class="px-6 py-3.5">Assigned Products</th>
            <th class="px-6 py-3.5">Status</th>
            <th class="px-6 py-3.5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 font-medium">
          <?php if (empty($tags)): ?>
            <tr>
              <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                <span class="material-icons text-4xl block mb-1">label</span>
                No product tags configured yet.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($tags as $t): ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-4 font-semibold text-slate-900">
                  <?= htmlspecialchars($t['name']) ?>
                </td>
                <td class="px-6 py-4 text-xs font-mono text-slate-500">
                  <?= htmlspecialchars($t['slug']) ?>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700 capitalize">
                    <?= str_replace('_', ' ', htmlspecialchars($t['tag_type'])) ?>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-bold text-white shadow-xs" style="background-color: <?= htmlspecialchars($t['color_hex'] ?: '#4F46E5') ?>;">
                    <span class="material-icons text-[14px] mr-1">local_offer</span>
                    <?= htmlspecialchars($t['name']) ?>
                  </span>
                </td>
                <td class="px-6 py-4 font-semibold text-slate-800">
                  <?= (int)$t['product_count'] ?> products
                </td>
                <td class="px-6 py-4">
                  <button onclick="toggleTagStatus('<?= $t['encrypted_id'] ?>')" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium <?= $t['is_active'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' ?>">
                    <span class="w-1.5 h-1.5 rounded-full <?= $t['is_active'] ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
                    <?= $t['is_active'] ? 'Active' : 'Inactive' ?>
                  </button>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                  <button onclick="editTag(<?= htmlspecialchars(json_encode($t)) ?>)" class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs inline-flex items-center gap-1">
                    <span class="material-icons text-sm">edit</span> Edit
                  </button>
                  <button onclick="deleteTag('<?= $t['encrypted_id'] ?>', '<?= htmlspecialchars($t['name'], ENT_QUOTES) ?>')" class="text-rose-600 hover:text-rose-900 font-semibold text-xs inline-flex items-center gap-1">
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

<!-- Tag Modal -->
<div id="tagModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
      <h3 id="tagModalTitle" class="font-bold text-slate-900 text-lg">Add Product Tag</h3>
      <button onclick="closeTagModal()" class="text-slate-400 hover:text-slate-600"><span class="material-icons">close</span></button>
    </div>
    <form id="tagForm" onsubmit="saveTag(event)" class="p-6 space-y-4">
      <input type="hidden" id="tag_id" name="tag_id" value="">
      
      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Tag Name *</label>
        <input type="text" id="tag_name" name="name" required placeholder="e.g. Best Seller, Summer Special" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Slug</label>
        <input type="text" id="tag_slug" name="slug" placeholder="Auto-generated if left empty" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Tag Type *</label>
          <select id="tag_type" name="tag_type" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
            <option value="featured">Featured</option>
            <option value="top_rated">Top Rated</option>
            <option value="new_arrival">New Arrival</option>
            <option value="best_seller">Best Seller</option>
            <option value="trending">Trending</option>
            <option value="offer">Offer / Deal</option>
            <option value="limited_edition">Limited Edition</option>
            <option value="custom">Custom Tag</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Badge Color</label>
          <div class="flex items-center gap-2">
            <input type="color" id="color_hex" name="color_hex" value="#4F46E5" class="h-9 w-12 rounded border border-slate-300 p-0.5 cursor-pointer">
            <span id="colorHexText" class="text-xs font-mono text-slate-600">#4F46E5</span>
          </div>
        </div>
      </div>

      <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
        <button type="button" onclick="closeTagModal()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Save Tag</button>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('color_hex').addEventListener('input', function(e) {
  document.getElementById('colorHexText').innerText = e.target.value;
});

function openTagModal(mode) {
  document.getElementById('tagForm').reset();
  document.getElementById('tag_id').value = '';
  document.getElementById('colorHexText').innerText = '#4F46E5';
  document.getElementById('tagModalTitle').innerText = mode === 'add' ? 'Add Product Tag' : 'Edit Product Tag';
  document.getElementById('tagModal').classList.remove('hidden');
  document.getElementById('tagModal').classList.add('flex');
}

function closeTagModal() {
  document.getElementById('tagModal').classList.add('hidden');
  document.getElementById('tagModal').classList.remove('flex');
}

function editTag(tag) {
  openTagModal('edit');
  document.getElementById('tag_id').value = tag.encrypted_id;
  document.getElementById('tag_name').value = tag.name;
  document.getElementById('tag_slug').value = tag.slug;
  document.getElementById('tag_type').value = tag.tag_type;
  document.getElementById('color_hex').value = tag.color_hex || '#4F46E5';
  document.getElementById('colorHexText').innerText = tag.color_hex || '#4F46E5';
}

function saveTag(e) {
  e.preventDefault();
  const form = document.getElementById('tagForm');
  const formData = new FormData(form);
  const isEdit = !!document.getElementById('tag_id').value;
  const url = '<?= BASE_URL ?>/admin/tags/' + (isEdit ? 'update' : 'store');

  fetch(url, {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) location.reload();
    else alert(data.message || 'Error saving tag.');
  })
  .catch(err => alert('Network error.'));
}

function toggleTagStatus(id) {
  fetch('<?= BASE_URL ?>/admin/tags/toggle-active', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ tag_id: id })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) location.reload();
    else alert(data.message || 'Failed to toggle status.');
  });
}

function deleteTag(id, name) {
  if (!confirm('Are you sure you want to delete tag "' + name + '"?')) return;
  fetch('<?= BASE_URL ?>/admin/tags/delete', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ tag_id: id })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) location.reload();
    else alert(data.message || 'Failed to delete tag.');
  });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
