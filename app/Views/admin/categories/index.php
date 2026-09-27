<?php
$pageTitle = 'Categories Manager';
$activeMenu = 'categories';

require_once __DIR__ . '/../layouts/header.php';

$categoryTree = $categoryTree ?? [];
$flatCategories = $flatCategories ?? [];
$kpiTotal = $kpiData['total'] ?? 0;
$kpiActive = $kpiData['active'] ?? 0;
$kpiInactive = $kpiData['inactive'] ?? 0;
$kpiMaxDepth = $kpiData['maxDepth'] ?? 3;

$iconOptions = ['folder', 'devices', 'smartphone', 'laptop', 'headset', 'watch', 'checkroom', 'face', 'weekend', 'home', 'sports_esports', 'fitness_center', 'shopping_bag', 'local_offer', 'bolt', 'star'];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">Categories Manager</h1>
      <p class="text-slate-500 text-sm mt-1">Manage catalog structure, icon assets, image uploads &amp; parent_id tree hierarchy</p>
    </div>
    <div class="flex items-center gap-2.5 flex-wrap">
      <button onclick="expandAllTree()" class="btn-secondary text-sm">
        <span class="material-icons text-[18px]">unfold_more</span> Expand All
      </button>
      <button onclick="collapseAllTree()" class="btn-secondary text-sm">
        <span class="material-icons text-[18px]">unfold_less</span> Collapse All
      </button>
      <button onclick="openCategoryModal('add', null)" class="btn-primary text-sm shadow-md shadow-cc-blue/20">
        <span class="material-icons text-[18px]">add</span> Add Root Category
      </button>
    </div>
  </div>

  <!-- KPI Cards -->
  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
    <div class="stat-mini">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-cc-blue/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-blue text-[20px]">folder_open</span>
        </div>
        <div>
          <p class="text-xl font-bold text-slate-800" id="kpiTotal"><?= number_format($kpiTotal) ?></p>
          <p class="text-[11px] text-slate-400 font-medium">Total Categories</p>
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
          <p class="text-[11px] text-slate-400 font-medium">Active</p>
        </div>
      </div>
    </div>
    <div class="stat-mini">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-pink text-[20px]">pause_circle</span>
        </div>
        <div>
          <p class="text-xl font-bold text-slate-800" id="kpiInactive"><?= number_format($kpiInactive) ?></p>
          <p class="text-[11px] text-slate-400 font-medium">Inactive</p>
        </div>
      </div>
    </div>
    <div class="stat-mini">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-cc-purple/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-purple text-[20px]">account_tree</span>
        </div>
        <div>
          <p class="text-xl font-bold text-slate-800"><?= $kpiMaxDepth ?></p>
          <p class="text-[11px] text-slate-400 font-medium">Max Depth</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Main 2-Column Grid: Tree View + Detail Panel -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- LEFT: Tree Hierarchy Container (Colspan 2) -->
    <div class="lg:col-span-2 space-y-4">
      <div class="section-card p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4 pb-3 border-b border-slate-100">
          <div class="flex items-center gap-3">
            <span class="material-icons text-cc-blue text-[22px]">account_tree</span>
            <div>
              <h2 class="font-heading text-lg font-bold text-slate-900">Category Tree Hierarchy</h2>
              <p class="text-xs text-slate-500">Click row to inspect details. Drag handles or use actions to update structure.</p>
            </div>
          </div>
          <div class="w-full sm:w-64">
            <input type="text" placeholder="Search categories..." id="catSearchInput" oninput="filterCategoryTree()" class="form-field text-xs py-2">
          </div>
        </div>

        <!-- Tree Nodes Container -->
        <div id="treeContainer" class="space-y-1 max-h-[70vh] overflow-y-auto scrollbar-thin pr-1">
          <?php
          function renderCategoryNode($node, $baseUrl) {
              $hasChildren = !empty($node['children']);
              $isInactive = (int)$node['is_active'] === 0;
              $paddingLeft = max(8, ($node['depth'] - 1) * 24);
              $iconName = !empty($node['icon']) ? $node['icon'] : ($hasChildren ? 'folder' : 'label');
              ?>
              <div class="category-tree-item" data-name="<?= htmlspecialchars(strtolower($node['name'])) ?>" data-slug="<?= htmlspecialchars(strtolower($node['slug'])) ?>">
                <div class="tree-row flex items-center justify-between p-2.5 rounded-xl border border-slate-100 hover:bg-slate-50 transition cursor-pointer <?= $isInactive ? 'opacity-60 bg-slate-50/50' : '' ?>" 
                     style="padding-left: <?= $paddingLeft ?>px"
                     onclick="selectCategory('<?= $node['encrypted_id'] ?>', this)">
                  
                  <div class="flex items-center gap-2.5 min-w-0">
                    <?php if ($hasChildren): ?>
                      <button type="button" class="tree-toggle text-slate-400 hover:text-slate-700 transition" onclick="toggleTreeNode(this, event)">
                        <span class="material-icons text-[18px]">chevron_right</span>
                      </button>
                    <?php else: ?>
                      <span class="w-4 inline-block"></span>
                    <?php endif; ?>

                    <?php if (!empty($node['image_url'])): ?>
                      <img src="<?= htmlspecialchars($node['image_url']) ?>" class="w-6 h-6 rounded object-cover border border-slate-200 shrink-0">
                    <?php else: ?>
                      <span class="material-icons text-[20px] <?= $hasChildren ? 'text-cc-blue' : 'text-slate-400' ?> shrink-0">
                        <?= htmlspecialchars($iconName) ?>
                      </span>
                    <?php endif; ?>

                    <div class="min-w-0">
                      <p class="text-sm font-semibold text-slate-800 truncate flex items-center gap-2">
                        <?= htmlspecialchars($node['name']) ?>
                        <?php if ($isInactive): ?>
                          <span class="badge badge-red text-[9px] py-0 px-1.5">Inactive</span>
                        <?php endif; ?>
                      </p>
                      <p class="text-[10px] text-slate-400 mono">/<?= htmlspecialchars($node['slug']) ?></p>
                    </div>
                  </div>

                  <!-- Perfectly Aligned Action Buttons Container -->
                  <div class="flex items-center gap-2.5 shrink-0 ml-auto" onclick="event.stopPropagation()">
                    <span class="badge badge-slate text-[10px] mono" title="Products in category">
                      <?= number_format($node['product_count']) ?> prods
                    </span>

                    <div class="inline-toggle <?= (int)$node['is_active'] === 1 ? 'on' : '' ?>" 
                         onclick="toggleCategoryActive('<?= $node['encrypted_id'] ?>', this)" 
                         title="Toggle Active Status">
                      <div class="it-thumb"></div>
                    </div>

                    <div class="flex items-center gap-1">
                      <!-- Add Child Sub-category -->
                      <button class="action-btn text-cc-blue hover:bg-blue-50" 
                              onclick="openCategoryModal('add_child', '<?= $node['encrypted_id'] ?>')" 
                              title="Add Sub-category">
                        <span class="material-icons text-[16px]">create_new_folder</span>
                      </button>
                      <!-- Edit Category -->
                      <button class="action-btn" 
                              onclick="openCategoryModal('edit', '<?= $node['encrypted_id'] ?>')" 
                              title="Edit Category">
                        <span class="material-icons text-[16px]">edit</span>
                      </button>
                      <!-- Delete Category -->
                      <button class="action-btn danger" 
                              onclick="deleteCategory('<?= $node['encrypted_id'] ?>', '<?= htmlspecialchars($node['name']) ?>')" 
                              title="Delete Category">
                        <span class="material-icons text-[16px]">delete_outline</span>
                      </button>
                    </div>
                  </div>
                </div>

                <?php if ($hasChildren): ?>
                  <div class="tree-children pl-4 space-y-1 hidden mt-1 border-l-2 border-slate-100 ml-4">
                    <?php foreach ($node['children'] as $child) { renderCategoryNode($child, $baseUrl); } ?>
                  </div>
                <?php endif; ?>
              </div>
          <?php } ?>

          <?php if (empty($categoryTree)): ?>
            <div class="text-center py-12 text-slate-400">No categories created yet. Click "Add Root Category" to begin.</div>
          <?php else: ?>
            <?php foreach ($categoryTree as $rootNode) { renderCategoryNode($rootNode, $baseUrl); } ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- RIGHT: Category Details Panel (Colspan 1) -->
    <div class="space-y-4">
      <div class="section-card p-5 sticky top-24" id="detailPanel">
        <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
          <span class="material-icons text-cc-purple text-[22px]">info</span>
          <h3 class="font-heading text-base font-bold text-slate-900">Category Overview</h3>
        </div>

        <div id="detailContent">
          <?php if (!empty($categoryTree[0])): 
            $first = $categoryTree[0]; ?>
            <div class="space-y-3 text-xs">
              <div class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl">
                <?php if (!empty($first['image_url'])): ?>
                  <img src="<?= htmlspecialchars($first['image_url']) ?>" class="w-12 h-12 rounded-lg object-cover border border-slate-200 shrink-0">
                <?php else: ?>
                  <div class="w-12 h-12 rounded-lg bg-cc-blue/10 flex items-center justify-center text-cc-blue shrink-0">
                    <span class="material-icons text-2xl"><?= htmlspecialchars($first['icon'] ?: 'folder') ?></span>
                  </div>
                <?php endif; ?>
                <div>
                  <h4 class="font-bold text-slate-900 text-sm"><?= htmlspecialchars($first['name']) ?></h4>
                  <p class="text-slate-400 mono">/<?= htmlspecialchars($first['slug']) ?></p>
                </div>
              </div>

              <div class="space-y-2 pt-2">
                <div class="flex justify-between py-1 border-b border-slate-100">
                  <span class="text-slate-400">Icon:</span>
                  <span class="mono font-semibold text-slate-700 flex items-center gap-1">
                    <span class="material-icons text-[14px] text-cc-blue"><?= htmlspecialchars($first['icon'] ?: 'folder') ?></span>
                    <?= htmlspecialchars($first['icon'] ?: 'folder') ?>
                  </span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                  <span class="text-slate-400">Products Count:</span>
                  <span class="mono font-bold text-cc-blue"><?= number_format($first['product_count']) ?> items</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                  <span class="text-slate-400">Sub-categories:</span>
                  <span class="mono font-bold text-cc-purple"><?= number_format($first['children_count']) ?> children</span>
                </div>
                <div class="flex justify-between py-1 border-b border-slate-100">
                  <span class="text-slate-400">Status:</span>
                  <span class="badge <?= (int)$first['is_active'] === 1 ? 'badge-green' : 'badge-red' ?>">
                    <?= (int)$first['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                  </span>
                </div>
              </div>

              <div class="pt-3">
                <button onclick="openCategoryModal('edit', '<?= $first['encrypted_id'] ?>')" class="btn-primary w-full justify-center text-xs py-2">
                  <span class="material-icons text-[16px]">edit</span> Edit Selected Category
                </button>
              </div>
            </div>
          <?php else: ?>
            <p class="text-slate-400 text-xs text-center py-8">Select any category from the tree hierarchy to view detailed parameters.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- ================= MODAL: ADD / EDIT CATEGORY ================= -->
<div class="modal-overlay" id="categoryModal">
  <div class="modal-box" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
      <h3 class="font-heading text-lg font-bold text-slate-900" id="catModalTitle">Add New Category</h3>
      <button class="btn-icon" onclick="closeCategoryModal()"><span class="material-icons text-[20px]">close</span></button>
    </div>
    <form id="categoryForm" onsubmit="submitCategoryForm(event)" class="p-6 space-y-4">
      <input type="hidden" id="fCategoryEncryptedId" value="">

      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Parent Category</label>
        <select id="fParentCategory" class="form-field text-xs">
          <option value="">-- Root Level Category (No Parent) --</option>
          <?php foreach ($flatCategories as $fc): ?>
            <option value="<?= $fc['encrypted_id'] ?>"><?= htmlspecialchars($fc['name']) ?> (/#<?= $fc['id'] ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Category Name *</label>
          <input type="text" id="fCatName" required placeholder="e.g. Smart Wearables" class="form-field" oninput="autoGenCatSlug(this.value)">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">URL Slug</label>
          <div class="flex items-center gap-1.5">
            <input type="text" id="fCatSlug" placeholder="smart-wearables" class="form-field mono text-xs">
            <button type="button" onclick="autoGenCatSlug(document.getElementById('fCatName').value)" class="btn-secondary text-xs shrink-0 py-2">Auto</button>
          </div>
        </div>
      </div>

      <!-- Icon & Image Section -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
        <!-- Category Icon Selector -->
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Category Material Icon</label>
          <div class="flex items-center gap-2">
            <div class="w-10 h-10 rounded-xl bg-cc-blue/10 border border-cc-blue/20 flex items-center justify-center text-cc-blue shrink-0">
              <span class="material-icons text-xl" id="iconPreview">folder</span>
            </div>
            <input type="text" id="fCatIcon" value="folder" placeholder="folder, devices..." class="form-field text-xs mono" oninput="document.getElementById('iconPreview').textContent = this.value.trim() || 'folder'">
          </div>
          <!-- Preset Icon Choices -->
          <div class="flex items-center gap-1.5 flex-wrap mt-2">
            <?php foreach ($iconOptions as $ico): ?>
              <button type="button" class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-cc-blue/10 hover:text-cc-blue text-slate-600 flex items-center justify-center transition" 
                      onclick="selectPresetIcon('<?= $ico ?>')" title="<?= $ico ?>">
                <span class="material-icons text-[16px]"><?= $ico ?></span>
              </button>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Category Image Upload -->
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Category Image File</label>
          <div class="border-2 border-dashed border-slate-200 hover:border-cc-blue rounded-xl p-3 text-center transition cursor-pointer" onclick="document.getElementById('catImageFileInput').click()">
            <input type="file" id="catImageFileInput" accept="image/*" class="hidden" onchange="uploadCategoryImgFile(this.files[0])">
            <span class="material-icons text-slate-400 text-xl">cloud_upload</span>
            <p class="text-[11px] font-semibold text-slate-700">Click or drag image file</p>
            <div id="catUploadSpinner" class="hidden text-[10px] text-cc-blue font-bold mt-1">Uploading...</div>
          </div>
          <input type="url" id="fCatImage" placeholder="https://images.unsplash.com/..." class="form-field text-xs mono mt-2">
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Description</label>
        <textarea id="fCatDesc" rows="2" placeholder="Category brief overview..." class="form-field text-xs resize-y"></textarea>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Sort Order</label>
        <input type="number" id="fCatSortOrder" value="0" min="0" class="form-field text-xs mono">
      </div>

      <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200">
        <div>
          <p class="text-xs font-bold text-slate-800">Category Active Status</p>
          <p class="text-[10px] text-slate-400">Available to customers in store catalog</p>
        </div>
        <div class="inline-toggle on" id="toggleCatIsActive" onclick="this.classList.toggle('on')">
          <div class="it-thumb"></div>
        </div>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" class="btn-secondary text-sm" onclick="closeCategoryModal()">Cancel</button>
        <button type="submit" id="saveCatBtn" class="btn-primary text-sm">Save Category</button>
      </div>
    </form>
  </div>
</div>

<script>
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';

function selectPresetIcon(icon) {
  document.getElementById('fCatIcon').value = icon;
  document.getElementById('iconPreview').textContent = icon;
}

async function uploadCategoryImgFile(file) {
  if (!file) return;
  const spinner = document.getElementById('catUploadSpinner');
  if (spinner) spinner.classList.remove('hidden');

  const formData = new FormData();
  formData.append('file', file);

  try {
    const res = await fetch(`${BASE_URL}/admin/products/upload-media`, {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById('fCatImage').value = data.url;
    } else {
      alert(data.message || 'Image upload failed');
    }
  } catch(e) {
    alert('Upload failed: ' + e.message);
  }

  if (spinner) spinner.classList.add('hidden');
}

function toggleTreeNode(btn, ev) {
  if (ev) ev.stopPropagation();
  const item = btn.closest('.category-tree-item');
  const children = item.querySelector('.tree-children');
  if (children) {
    children.classList.toggle('hidden');
    const icon = btn.querySelector('.material-icons');
    icon.textContent = children.classList.contains('hidden') ? 'chevron_right' : 'expand_more';
  }
}

function expandAllTree() {
  document.querySelectorAll('.tree-children').forEach(c => c.classList.remove('hidden'));
  document.querySelectorAll('.tree-toggle .material-icons').forEach(i => i.textContent = 'expand_more');
}

function collapseAllTree() {
  document.querySelectorAll('.tree-children').forEach(c => c.classList.add('hidden'));
  document.querySelectorAll('.tree-toggle .material-icons').forEach(i => i.textContent = 'chevron_right');
}

function filterCategoryTree() {
  const query = document.getElementById('catSearchInput').value.toLowerCase().trim();
  document.querySelectorAll('.category-tree-item').forEach(item => {
    const name = item.getAttribute('data-name') || '';
    const slug = item.getAttribute('data-slug') || '';
    if (!query || name.includes(query) || slug.includes(query)) {
      item.classList.remove('hidden');
      if (query) {
        const children = item.querySelector('.tree-children');
        if (children) children.classList.remove('hidden');
      }
    } else {
      item.classList.add('hidden');
    }
  });
}

function autoGenCatSlug(val) {
  const slugInp = document.getElementById('fCatSlug');
  if (slugInp) {
    slugInp.value = val.toLowerCase().trim().replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
  }
}

async function selectCategory(encryptedId, el) {
  document.querySelectorAll('.tree-row').forEach(r => r.classList.remove('selected', 'bg-cc-blue/10'));
  el.classList.add('selected', 'bg-cc-blue/10');

  try {
    const res = await fetch(`${BASE_URL}/admin/categories/detail/${encryptedId}`);
    const data = await res.json();
    if (data.success) {
      renderCategoryDetail(data.category);
    }
  } catch(e) {
    console.error('Failed to fetch category detail:', e);
  }
}

function renderCategoryDetail(c) {
  const panel = document.getElementById('detailContent');
  if (!panel) return;

  const isAct = parseInt(c.is_active) === 1;
  const iconName = c.icon || 'folder';
  const imgTag = c.image_url ? `<img src="${escapeHtml(c.image_url)}" class="w-12 h-12 rounded-lg object-cover border border-slate-200 shrink-0">` : `<div class="w-12 h-12 rounded-lg bg-cc-blue/10 flex items-center justify-center text-cc-blue shrink-0"><span class="material-icons text-2xl">${escapeHtml(iconName)}</span></div>`;

  panel.innerHTML = `
    <div class="space-y-3 text-xs">
      <div class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-xl">
        ${imgTag}
        <div>
          <h4 class="font-bold text-slate-900 text-sm">${escapeHtml(c.name)}</h4>
          <p class="text-slate-400 mono">/${escapeHtml(c.slug)}</p>
        </div>
      </div>

      <div class="space-y-2 pt-2">
        <div class="flex justify-between py-1 border-b border-slate-100">
          <span class="text-slate-400">Parent:</span>
          <span class="font-semibold text-slate-700">${escapeHtml(c.parent_name || 'Root Category')}</span>
        </div>
        <div class="flex justify-between py-1 border-b border-slate-100">
          <span class="text-slate-400">Material Icon:</span>
          <span class="mono font-semibold text-slate-700 flex items-center gap-1">
            <span class="material-icons text-[15px] text-cc-blue">${escapeHtml(iconName)}</span>
            ${escapeHtml(iconName)}
          </span>
        </div>
        <div class="flex justify-between py-1 border-b border-slate-100">
          <span class="text-slate-400">Products Count:</span>
          <span class="mono font-bold text-cc-blue">${Number(c.product_count).toLocaleString()} items</span>
        </div>
        <div class="flex justify-between py-1 border-b border-slate-100">
          <span class="text-slate-400">Sub-categories:</span>
          <span class="mono font-bold text-cc-purple">${Number(c.children_count).toLocaleString()} children</span>
        </div>
        <div class="flex justify-between py-1 border-b border-slate-100">
          <span class="text-slate-400">Status:</span>
          <span class="badge ${isAct ? 'badge-green' : 'badge-red'}">${isAct ? 'Active' : 'Inactive'}</span>
        </div>
        ${c.description ? `<div class="pt-2 text-slate-600 border-t border-slate-100"><p class="text-[11px] font-bold text-slate-400 uppercase">Description:</p><p class="mt-0.5">${escapeHtml(c.description)}</p></div>` : ''}
      </div>

      <div class="pt-3">
        <button onclick="openCategoryModal('edit', '${c.encrypted_id}')" class="btn-primary w-full justify-center text-xs py-2">
          <span class="material-icons text-[16px]">edit</span> Edit Selected Category
        </button>
      </div>
    </div>
  `;
}

function openCategoryModal(mode, encryptedId = null) {
  document.getElementById('categoryForm').reset();
  document.getElementById('fCategoryEncryptedId').value = '';
  document.getElementById('fCatIcon').value = 'folder';
  document.getElementById('iconPreview').textContent = 'folder';
  document.getElementById('toggleCatIsActive').classList.add('on');

  if (mode === 'add') {
    document.getElementById('catModalTitle').textContent = 'Add Root Category';
    document.getElementById('fParentCategory').value = '';
    document.getElementById('categoryModal').classList.add('show');
  } else if (mode === 'add_child') {
    document.getElementById('catModalTitle').textContent = 'Add Sub-Category';
    document.getElementById('fParentCategory').value = encryptedId || '';
    document.getElementById('categoryModal').classList.add('show');
  } else if (mode === 'edit') {
    document.getElementById('catModalTitle').textContent = 'Edit Category';
    fetchCategoryForEdit(encryptedId);
  }
}

async function fetchCategoryForEdit(encryptedId) {
  try {
    const res = await fetch(`${BASE_URL}/admin/categories/detail/${encryptedId}`);
    const data = await res.json();
    if (data.success) {
      const c = data.category;
      document.getElementById('fCategoryEncryptedId').value = c.encrypted_id;
      document.getElementById('fParentCategory').value = c.parent_encrypted_id || '';
      document.getElementById('fCatName').value = c.name;
      document.getElementById('fCatSlug').value = c.slug;
      document.getElementById('fCatIcon').value = c.icon || 'folder';
      document.getElementById('iconPreview').textContent = c.icon || 'folder';
      document.getElementById('fCatDesc').value = c.description || '';
      document.getElementById('fCatImage').value = c.image_url || '';
      document.getElementById('fCatSortOrder').value = c.sort_order || 0;
      
      const toggle = document.getElementById('toggleCatIsActive');
      if (parseInt(c.is_active) === 1) {
        toggle.classList.add('on');
      } else {
        toggle.classList.remove('on');
      }

      document.getElementById('categoryModal').classList.add('show');
    }
  } catch(e) {
    alert('Failed to load category for editing');
  }
}

function closeCategoryModal() {
  document.getElementById('categoryModal').classList.remove('show');
}

async function submitCategoryForm(e) {
  e.preventDefault();

  const encryptedId = document.getElementById('fCategoryEncryptedId').value;
  const isEdit = !!encryptedId;

  const payload = {
    category_id: encryptedId,
    parent_encrypted_id: document.getElementById('fParentCategory').value,
    name: document.getElementById('fCatName').value.trim(),
    slug: document.getElementById('fCatSlug').value.trim(),
    icon: document.getElementById('fCatIcon').value.trim(),
    description: document.getElementById('fCatDesc').value.trim(),
    image_url: document.getElementById('fCatImage').value.trim(),
    sort_order: document.getElementById('fCatSortOrder').value,
    is_active: document.getElementById('toggleCatIsActive').classList.contains('on') ? 1 : 0
  };

  const endpoint = isEdit ? `${BASE_URL}/admin/categories/update` : `${BASE_URL}/admin/categories/store`;

  try {
    const res = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const data = await res.json();
    if (data.success) {
      showToast('success', isEdit ? 'Category updated successfully!' : 'Category created successfully!');
      closeCategoryModal();
      setTimeout(() => window.location.reload(), 600);
    } else {
      showToast('error', data.message || 'Error saving category');
    }
  } catch(err) {
    showToast('error', 'Network error while saving category');
  }
}

async function toggleCategoryActive(encryptedId, el) {
  try {
    const res = await fetch(`${BASE_URL}/admin/categories/toggle-active`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ category_id: encryptedId })
    });

    const data = await res.json();
    if (data.success) {
      el.classList.toggle('on');
      showToast('success', 'Category active status toggled');
    } else {
      showToast('error', data.message || 'Failed to toggle category status');
    }
  } catch(e) {
    showToast('error', 'Error toggling category status');
  }
}

async function deleteCategory(encryptedId, name) {
  if (!confirm(`Are you sure you want to delete category "${name}"? Sub-categories and products will be reassigned.`)) {
    return;
  }

  try {
    const res = await fetch(`${BASE_URL}/admin/categories/delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ category_id: encryptedId })
    });

    const data = await res.json();
    if (data.success) {
      showToast('success', `Category "${name}" deleted successfully`);
      setTimeout(() => window.location.reload(), 600);
    } else {
      showToast('error', data.message || 'Failed to delete category');
    }
  } catch(e) {
    showToast('error', 'Network error while deleting category');
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
