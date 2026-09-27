<?php
$pageTitle = 'Product Variants — ' . htmlspecialchars($product['name']);
$activeMenu = 'products';

require_once __DIR__ . '/../layouts/header.php';

$variants = $variants ?? [];
$attributeGroups = $attributeGroups ?? [];

$totalVariantCount = count($variants);
$activeVariantCount = 0;
$outOfStockCount = 0;
$totalStockUnits = 0;

foreach ($variants as $v) {
    if ((int)$v['is_active'] === 1) $activeVariantCount++;
    if ((int)$v['stock_qty'] <= 0) $outOfStockCount++;
    $totalStockUnits += (int)$v['stock_qty'];
}
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Product Header Banner -->
  <div class="section-card">
    <div class="p-5 sm:p-6">
      <div class="flex flex-col sm:flex-row gap-5">
        <?php if (!empty($product['images'][0]['image_url'])): ?>
          <img src="<?= htmlspecialchars($product['images'][0]['image_url']) ?>" alt="" class="w-20 h-20 rounded-2xl object-cover shrink-0 border border-slate-100">
        <?php else: ?>
          <div class="w-20 h-20 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-400 shrink-0">
            <span class="material-icons text-[32px]">inventory_2</span>
          </div>
        <?php endif; ?>
        <div class="flex-1 min-w-0">
          <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
              <div class="flex items-center gap-2.5 flex-wrap">
                <h1 class="font-heading text-xl sm:text-2xl font-bold text-slate-900"><?= htmlspecialchars($product['name']) ?></h1>
                <?php if ((int)$product['is_active'] === 1): ?>
                  <span class="badge badge-green"><span class="material-icons text-[12px]">check_circle</span> Active</span>
                <?php else: ?>
                  <span class="badge badge-red"><span class="material-icons text-[12px]">cancel</span> Inactive</span>
                <?php endif; ?>
              </div>
              <p class="text-sm text-slate-500 mt-1">
                Base SKU: <span class="mono text-xs bg-slate-100 px-1.5 py-0.5 rounded font-medium"><?= htmlspecialchars($product['sku']) ?></span> &middot; 
                Category: <span class="badge badge-blue text-[11px] py-0.5"><?= htmlspecialchars($product['category_name'] ?: 'General') ?></span> &middot; 
                Base Price: <span class="font-semibold text-slate-700">₹<?= number_format($product['base_price'], 2) ?></span>
              </p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
              <a href="<?= $baseUrl ?>/admin/products" class="btn-secondary text-sm">
                <span class="material-icons text-[18px]">arrow_back</span> Back to Products
              </a>
              <button class="btn-primary text-sm" onclick="openAddVariantModal()">
                <span class="material-icons text-[18px]">add_circle</span> Add Variant
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Stats Strip -->
    <div class="border-t border-slate-100 grid grid-cols-2 sm:grid-cols-4 divide-x divide-slate-100">
      <div class="px-5 py-3.5 text-center">
        <p class="text-xl font-bold text-slate-800 font-heading"><?= $totalVariantCount ?></p>
        <p class="text-[11px] text-slate-400 font-medium">Total Variants</p>
      </div>
      <div class="px-5 py-3.5 text-center">
        <p class="text-xl font-bold text-green-600 font-heading"><?= $activeVariantCount ?></p>
        <p class="text-[11px] text-slate-400 font-medium">Active</p>
      </div>
      <div class="px-5 py-3.5 text-center">
        <p class="text-xl font-bold text-red-500 font-heading"><?= $outOfStockCount ?></p>
        <p class="text-[11px] text-slate-400 font-medium">Out of Stock</p>
      </div>
      <div class="px-5 py-3.5 text-center">
        <p class="text-xl font-bold text-cc-purple font-heading"><?= number_format($totalStockUnits) ?></p>
        <p class="text-[11px] text-slate-400 font-medium">Total Stock Units</p>
      </div>
    </div>
  </div>

  <!-- VARIANTS TABLE -->
  <div class="section-card">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 sm:px-6 py-4 border-b border-slate-100">
      <div class="flex items-center gap-3">
        <span class="material-icons text-cc-orange text-[22px]">widgets</span>
        <div>
          <h2 class="font-heading text-lg font-bold text-slate-900">All Product Variants</h2>
          <p class="text-xs text-slate-500">Manage Color, Size, Storage, price overrides, and stock inventory</p>
        </div>
      </div>
    </div>
    <div class="overflow-x-auto">
      <table class="data-table">
        <thead>
          <tr>
            <th>Variant SKU</th>
            <th>Attributes (Color / Size / Storage)</th>
            <th>Price Override</th>
            <th>Stock Quantity</th>
            <th>Active Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="variantsBody">
          <?php if (empty($variants)): ?>
          <tr>
            <td colspan="6" class="text-center py-12 text-slate-400">No variants created for this product yet. Click "Add Variant" to create one.</td>
          </tr>
          <?php else: ?>
            <?php foreach ($variants as $v): ?>
            <tr id="variant-row-<?= $v['encrypted_id'] ?>">
              <td class="mono text-xs font-bold text-slate-800"><?= htmlspecialchars($v['sku']) ?></td>
              <td>
                <span class="badge badge-purple text-[11px] py-1 px-2.5">
                  <span class="material-icons text-[14px]">palette</span>
                  <?= htmlspecialchars($v['attribute_summary'] ?: 'Default Variant') ?>
                </span>
              </td>
              <td>
                <div class="mono text-xs">
                  <?php if ($v['price_override'] !== null): ?>
                    <span class="font-semibold text-green-600">₹<?= number_format($v['price_override'], 2) ?></span>
                  <?php else: ?>
                    <span class="text-slate-400">₹<?= number_format($product['base_price'], 2) ?> (Base)</span>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <div class="flex items-center gap-2">
                  <span class="mono text-xs font-semibold <?= (int)$v['stock_qty'] > 0 ? 'text-slate-800' : 'text-red-500' ?>">
                    <?= number_format($v['stock_qty']) ?> units
                  </span>
                </div>
              </td>
              <td>
                <div class="inline-toggle <?= (int)$v['is_active'] === 1 ? 'on' : '' ?>" onclick="toggleVariantActive('<?= $v['encrypted_id'] ?>', this)">
                  <div class="it-thumb"></div>
                </div>
              </td>
              <td>
                <div class="flex items-center gap-1">
                  <button class="btn-icon" style="width:30px;height:30px" 
                          onclick="editVariant('<?= $v['encrypted_id'] ?>', '<?= htmlspecialchars($v['sku']) ?>', '<?= $v['price_override'] ?>', '<?= $v['stock_qty'] ?>', '<?= $v['is_active'] ?>', <?= htmlspecialchars(json_encode($v['attribute_ids'] ?? [])) ?>)" 
                          title="Edit Variant">
                    <span class="material-icons text-[16px]">edit</span>
                  </button>
                  <button class="btn-icon danger" style="width:30px;height:30px" onclick="deleteVariant('<?= $v['encrypted_id'] ?>')" title="Delete Variant">
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
  </div>
</div>

<!-- ================= MODAL: ADD / EDIT VARIANT ================= -->
<div class="modal-overlay" id="variantModal">
  <div class="modal-box w-full max-w-lg mx-4" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
      <h3 class="font-heading text-lg font-bold text-slate-900" id="variantModalTitle">Add New Variant</h3>
      <button class="btn-icon" onclick="closeVariantModal()"><span class="material-icons text-[20px]">close</span></button>
    </div>
    <form id="variantForm" onsubmit="submitVariantForm(event)" class="p-6 space-y-4">
      <input type="hidden" id="vProductEncryptedId" value="<?= \App\Helpers\SecurityHelper::encryptId($product['id']) ?>">
      <input type="hidden" id="vEncryptedId" value="">

      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Variant SKU *</label>
        <input type="text" id="vSku" required placeholder="e.g. CC-EAR-001-BLK-XL" class="input-field mono">
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Price Override (₹)</label>
          <input type="number" step="0.01" id="vPriceOverride" placeholder="Optional" class="input-field mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Stock Quantity *</label>
          <input type="number" id="vStockQty" required min="0" value="50" class="input-field mono">
        </div>
      </div>

      <!-- Variant Attributes (Color, Size, Storage, Material) -->
      <div class="space-y-3 pt-3 border-t border-slate-100">
        <label class="block text-xs font-bold text-slate-700">Variant Attributes (Color, Size, Storage, etc.)</label>
        <div class="grid grid-cols-2 gap-3">
          <?php foreach ($attributeGroups as $group): ?>
            <div>
              <label class="block text-[11px] font-semibold text-slate-500 mb-1"><?= htmlspecialchars($group['name']) ?></label>
              <select class="input-field text-xs variant-attr-select" data-group-id="<?= $group['id'] ?>">
                <option value="">-- None --</option>
                <?php foreach ($group['attributes'] as $attr): ?>
                  <option value="<?= $attr['id'] ?>"><?= htmlspecialchars($attr['value']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="flex items-center gap-2 pt-2">
        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
          <input type="checkbox" id="vIsActive" checked class="row-check"> Variant Active
        </label>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" class="btn-secondary" onclick="closeVariantModal()">Cancel</button>
        <button type="submit" class="btn-primary">Save Variant</button>
      </div>
    </form>
  </div>
</div>

<script>
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';

function openAddVariantModal() {
  document.getElementById('variantModalTitle').textContent = 'Add New Variant';
  document.getElementById('vEncryptedId').value = '';
  document.getElementById('vSku').value = '';
  document.getElementById('vPriceOverride').value = '';
  document.getElementById('vStockQty').value = '50';
  document.getElementById('vIsActive').checked = true;

  // Reset attribute selects
  document.querySelectorAll('.variant-attr-select').forEach(sel => sel.value = '');

  document.getElementById('variantModal').classList.add('show');
}

function editVariant(encryptedId, sku, price, stock, isActive, attributeIds) {
  document.getElementById('variantModalTitle').textContent = 'Edit Variant';
  document.getElementById('vEncryptedId').value = encryptedId;
  document.getElementById('vSku').value = sku;
  document.getElementById('vPriceOverride').value = (price !== 'null' && price !== null) ? price : '';
  document.getElementById('vStockQty').value = stock;
  document.getElementById('vIsActive').checked = parseInt(isActive) === 1;

  // Pre-select attributes
  const attrSet = new Set(attributeIds || []);
  document.querySelectorAll('.variant-attr-select').forEach(sel => {
    sel.value = '';
    Array.from(sel.options).forEach(opt => {
      if (opt.value && attrSet.has(parseInt(opt.value))) {
        sel.value = opt.value;
      }
    });
  });

  document.getElementById('variantModal').classList.add('show');
}

function closeVariantModal() {
  document.getElementById('variantModal').classList.remove('show');
}

async function submitVariantForm(e) {
  e.preventDefault();
  const encryptedVariantId = document.getElementById('vEncryptedId').value;
  const encryptedProductId = document.getElementById('vProductEncryptedId').value;
  const isEdit = !!encryptedVariantId;

  // Gather selected attribute IDs
  const selectedAttrIds = [];
  document.querySelectorAll('.variant-attr-select').forEach(sel => {
    if (sel.value) {
      selectedAttrIds.push(parseInt(sel.value));
    }
  });

  const payload = {
    product_id: encryptedProductId,
    variant_id: encryptedVariantId,
    sku: document.getElementById('vSku').value.trim(),
    price_override: document.getElementById('vPriceOverride').value,
    stock_qty: document.getElementById('vStockQty').value,
    is_active: document.getElementById('vIsActive').checked ? 1 : 0,
    attribute_ids: selectedAttrIds
  };

  const endpoint = isEdit ? `${BASE_URL}/admin/products/variants/update` : `${BASE_URL}/admin/products/variants/store`;

  try {
    const res = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });

    const data = await res.json();
    if (data.success) {
      showToast('success', isEdit ? 'Variant updated successfully!' : 'Variant created successfully!');
      closeVariantModal();
      setTimeout(() => window.location.reload(), 600);
    } else {
      showToast('error', data.message || 'Error saving variant');
    }
  } catch(e) {
    showToast('error', 'Network error while saving variant');
  }
}

async function toggleVariantActive(encryptedVariantId, el) {
  try {
    const res = await fetch(`${BASE_URL}/admin/products/variants/toggle-active`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ variant_id: encryptedVariantId })
    });
    const data = await res.json();
    if (data.success) {
      el.classList.toggle('on');
      showToast('success', 'Variant active status toggled');
    } else {
      showToast('error', data.message || 'Failed to toggle variant status');
    }
  } catch(e) {
    showToast('error', 'Error toggling variant status');
  }
}

async function deleteVariant(encryptedVariantId) {
  if (!confirm('Are you sure you want to delete this variant?')) return;

  try {
    const res = await fetch(`${BASE_URL}/admin/products/variants/delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ variant_id: encryptedVariantId })
    });
    const data = await res.json();
    if (data.success) {
      showToast('success', 'Variant deleted successfully');
      setTimeout(() => window.location.reload(), 600);
    } else {
      showToast('error', data.message || 'Failed to delete variant.');
    }
  } catch(e) {
    showToast('error', 'Network error while deleting variant.');
  }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
