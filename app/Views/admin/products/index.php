<?php
$pageTitle = 'Products Manager';
$activeMenu = 'products';

require_once __DIR__ . '/../layouts/header.php';

$products = $productData['products'] ?? [];
$totalCount = $productData['totalCount'] ?? 0;
$totalPages = $productData['totalPages'] ?? 1;
$currentPage = $productData['currentPage'] ?? 1;

$kpiActive = $kpiData['totalActive'] ?? 0;
$kpiFeatured = $kpiData['featuredCount'] ?? 0;
$kpiOOS = $kpiData['outOfStock'] ?? 0;
$kpiDeleted = $kpiData['deletedCount'] ?? 0;
$kpiVariants = $kpiData['withVariants'] ?? 0;
$kpiValuation = $kpiData['valuationSum'] ?? 0;

$categories = $categories ?? [];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">Products Manager</h1>
      <p class="text-slate-500 text-sm mt-1">Manage catalog, inline stock/active toggles, variants &amp; bulk operations</p>
    </div>
    <a href="<?= $baseUrl ?>/admin/products/create" class="btn-primary text-sm shadow-md shadow-cc-blue/20">
      <span class="material-icons text-[18px]">add</span> Add Product
    </a>
  </div>

  <!-- KPI Mini Cards -->
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
    <div class="stat-mini">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-cc-blue/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-blue text-[18px]">inventory_2</span>
        </div>
        <div>
          <p class="text-lg font-bold text-slate-800" id="kpiActive"><?= number_format($kpiActive) ?></p>
          <p class="text-[10px] text-slate-400 font-medium">Active Products</p>
        </div>
      </div>
    </div>

    <div class="stat-mini">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-cc-yellow/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-yellow text-[18px]">star</span>
        </div>
        <div>
          <p class="text-lg font-bold text-slate-800" id="kpiFeatured"><?= number_format($kpiFeatured) ?></p>
          <p class="text-[10px] text-slate-400 font-medium">Featured</p>
        </div>
      </div>
    </div>

    <div class="stat-mini">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-pink text-[18px]">remove_shopping_cart</span>
        </div>
        <div>
          <p class="text-lg font-bold text-slate-800" id="kpiOOS"><?= number_format($kpiOOS) ?></p>
          <p class="text-[10px] text-slate-400 font-medium">Out of Stock</p>
        </div>
      </div>
    </div>

    <div class="stat-mini">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
          <span class="material-icons text-slate-500 text-[18px]">delete</span>
        </div>
        <div>
          <p class="text-lg font-bold text-slate-800" id="kpiDeleted"><?= number_format($kpiDeleted) ?></p>
          <p class="text-[10px] text-slate-400 font-medium">Soft Deleted</p>
        </div>
      </div>
    </div>

    <div class="stat-mini">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-cc-purple/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-purple text-[18px]">style</span>
        </div>
        <div>
          <p class="text-lg font-bold text-slate-800" id="kpiVariants"><?= number_format($kpiVariants) ?></p>
          <p class="text-[10px] text-slate-400 font-medium">With Variants</p>
        </div>
      </div>
    </div>

    <div class="stat-mini">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center shrink-0">
          <span class="material-icons text-green-600 text-[18px]">sell</span>
        </div>
        <div>
          <p class="text-lg font-bold text-slate-800" id="kpiValuation">₹<?= number_format($kpiValuation, 2) ?></p>
          <p class="text-[10px] text-slate-400 font-medium">Total Valuation</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Filters & Bulk Action Bar -->
  <div class="section-card p-4 space-y-3">
    <div class="flex flex-wrap items-center gap-2.5">
      <select id="filterCategory" onchange="fetchProducts(1)" class="form-field w-auto text-xs py-2 pr-8">
        <option value="">All Categories</option>
        <?php foreach ($categories as $cat): ?>
          <option value="<?= htmlspecialchars($cat['name']) ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <select id="filterStatus" onchange="fetchProducts(1)" class="form-field w-auto text-xs py-2 pr-8">
        <option value="">All Status</option>
        <option value="active">Active Only</option>
        <option value="inactive">Inactive Only</option>
        <option value="deleted">Soft Deleted</option>
      </select>
      <select id="filterStock" onchange="fetchProducts(1)" class="form-field w-auto text-xs py-2 pr-8">
        <option value="">All Stock</option>
        <option value="in">In Stock</option>
        <option value="out">Out of Stock</option>
      </select>
      <select id="filterFeatured" onchange="fetchProducts(1)" class="form-field w-auto text-xs py-2 pr-8">
        <option value="">Featured</option>
        <option value="yes">Featured Only</option>
        <option value="no">Non-Featured</option>
      </select>
      <input type="text" placeholder="Search by name, SKU..." class="form-field w-auto max-w-[200px] text-xs py-2" id="searchInput" oninput="debounceProductFetch()">

      <div class="ml-auto text-xs text-slate-400 mono" id="resultCount">Showing <?= count($products) ?> of <?= $totalCount ?></div>
    </div>

    <!-- Bulk Action Toolbar -->
    <div class="bulk-bar bg-cc-blue/5 border border-cc-blue/20 rounded-xl" id="bulkBar">
      <div class="flex flex-wrap items-center gap-2.5">
        <span class="text-sm font-semibold text-cc-blue flex items-center gap-1.5">
          <span class="material-icons text-[18px]">checklist</span>
          <span id="selectedCount">0</span> selected
        </span>
        <button onclick="executeBulk('activate')" class="btn-secondary text-xs py-1.5 px-3"><span class="material-icons text-[14px] text-green-600">toggle_on</span> Set Active</button>
        <button onclick="executeBulk('deactivate')" class="btn-secondary text-xs py-1.5 px-3"><span class="material-icons text-[14px] text-red-500">toggle_off</span> Set Inactive</button>
        <button onclick="executeBulk('stock-in')" class="btn-secondary text-xs py-1.5 px-3"><span class="material-icons text-[14px] text-green-600">add_shopping_cart</span> In Stock</button>
        <button onclick="executeBulk('stock-out')" class="btn-secondary text-xs py-1.5 px-3"><span class="material-icons text-[14px] text-red-500">remove_shopping_cart</span> Out of Stock</button>
        <button onclick="executeBulk('featured')" class="btn-secondary text-xs py-1.5 px-3"><span class="material-icons text-[14px] text-cc-yellow">star</span> Featured</button>
        <button onclick="executeBulk('soft-delete')" class="btn-danger text-xs py-1.5 px-3"><span class="material-icons text-[14px]">delete_outline</span> Soft Delete</button>
        <button onclick="executeBulk('restore')" class="btn-secondary text-xs py-1.5 px-3"><span class="material-icons text-[14px] text-green-600">restore</span> Restore</button>
      </div>
    </div>
  </div>

  <!-- Data Table Section -->
  <div class="section-card">
    <div class="overflow-x-auto max-h-[72vh] overflow-y-auto scrollbar-thin">
      <table class="data-table" id="productsTable">
        <thead>
          <tr>
            <th style="width:36px"><input type="checkbox" class="row-check" onchange="toggleAllRows(this)"></th>
            <th>Product</th>
            <th>Category</th>
            <th>SKU</th>
            <th>Base / Sale Price</th>
            <th>Stock</th>
            <th>Active</th>
            <th>Rating</th>
            <th>Sold</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="productsBody">
          <?php if (empty($products)): ?>
          <tr>
            <td colspan="10" class="text-center py-12 text-slate-400">No products found matching filters.</td>
          </tr>
          <?php else: ?>
            <?php foreach ($products as $p): ?>
            <tr id="row-<?= $p['encrypted_id'] ?>">
              <td><input type="checkbox" class="row-check product-select-check" value="<?= $p['encrypted_id'] ?>" onchange="updateBulkBar()"></td>
              <td>
                <div class="flex items-center gap-2.5">
                  <?php if (!empty($p['primary_image'])): ?>
                    <img src="<?= htmlspecialchars($p['primary_image']) ?>" class="img-thumb">
                  <?php else: ?>
                    <div class="img-thumb bg-slate-100 flex items-center justify-center text-slate-400">
                      <span class="material-icons text-[20px]">inventory_2</span>
                    </div>
                  <?php endif; ?>
                  <div class="min-w-0">
                    <p class="font-medium text-slate-700 text-sm truncate max-w-[170px]"><?= htmlspecialchars($p['name']) ?></p>
                    <p class="text-[10px] text-slate-400 mono">UUID: <?= substr($p['uuid'], 0, 8) ?>...</p>
                  </div>
                </div>
              </td>
              <td><span class="badge badge-blue text-[10px]"><?= htmlspecialchars($p['category_name'] ?: 'General') ?></span></td>
              <td class="mono text-xs font-semibold text-slate-600"><?= htmlspecialchars($p['sku']) ?></td>
              <td>
                <div class="mono text-xs">
                  <span class="<?= $p['sale_price'] ? 'text-slate-400 line-through' : 'text-slate-800 font-semibold' ?>">₹<?= number_format($p['base_price'], 2) ?></span>
                  <?php if ($p['sale_price']): ?>
                    <br><span class="text-green-600 font-semibold">₹<?= number_format($p['sale_price'], 2) ?></span>
                  <?php endif; ?>
                </div>
              </td>
              <td>
                <div class="inline-toggle <?= (int)$p['is_in_stock'] === 1 ? 'on' : '' ?>" onclick="toggleStock('<?= $p['encrypted_id'] ?>', this)">
                  <div class="it-thumb"></div>
                </div>
              </td>
              <td>
                <div class="inline-toggle <?= (int)$p['is_active'] === 1 ? 'on' : '' ?>" onclick="toggleActive('<?= $p['encrypted_id'] ?>', this)">
                  <div class="it-thumb"></div>
                </div>
              </td>
              <td>
                <span class="text-xs">★<span class="font-semibold"><?= number_format($p['average_rating'], 1) ?></span></span>
              </td>
              <td class="mono text-xs font-semibold"><?= number_format($p['total_sold']) ?></td>
              <td>
                <div class="flex items-center gap-1">
                  <!-- Edit product -->
                  <a href="<?= $baseUrl ?>/admin/products/edit/<?= $p['encrypted_id'] ?>" class="action-btn" title="Edit Product">
                    <span class="material-icons text-[16px]">edit</span>
                  </a>
                  <!-- Manage variants -->
                  <a href="<?= $baseUrl ?>/admin/products/variants/<?= $p['encrypted_id'] ?>" class="action-btn text-cc-purple hover:bg-purple-50" title="Manage Variants (<?= $p['variant_count'] ?>)">
                    <span class="material-icons text-[16px]">style</span>
                  </a>
                  <!-- Soft delete -->
                  <button class="action-btn danger" onclick="deleteProduct('<?= $p['encrypted_id'] ?>')" title="Soft Delete">
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

    <!-- Pagination -->
    <div class="flex items-center justify-between px-5 sm:px-6 py-3 border-t border-slate-100">
      <p class="text-xs text-slate-500" id="paginationText">Showing page <?= $currentPage ?> of <?= $totalPages ?> (<?= $totalCount ?> total products)</p>
      <div class="flex items-center gap-1" id="paginationContainer">
        <?php if ($totalPages > 1): ?>
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
            <button class="btn-icon <?= $p === $currentPage ? 'bg-cc-blue text-white border-cc-blue' : 'bg-white text-slate-600 border-slate-200' ?>" 
                    style="width:32px;height:32px;font-size:12px;font-weight:700" 
                    onclick="fetchProducts(<?= $p ?>)"><?= $p ?></button>
          <?php endfor; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ================= MODAL: ADD / EDIT PRODUCT ================= -->
<div class="modal-overlay" id="productModal">
  <div class="modal-box" onclick="event.stopPropagation()">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
      <h3 class="font-heading text-lg font-bold text-slate-900" id="modalTitle">Add New Product</h3>
      <button class="btn-icon" onclick="closeProductModal()"><span class="material-icons text-[20px]">close</span></button>
    </div>
    <form id="productForm" onsubmit="submitProductForm(event)" class="p-6 space-y-4">
      <input type="hidden" id="formEncryptedId" value="">

      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Product Name *</label>
        <input type="text" id="pName" required placeholder="e.g. TrueSound Pro Wireless Earbuds" class="form-field">
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">SKU Code</label>
          <input type="text" id="pSku" placeholder="Leave blank to auto-generate" class="form-field mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Category *</label>
          <select id="pCategory" required class="form-field">
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['encrypted_id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Base Price (₹) *</label>
          <input type="number" step="0.01" id="pBasePrice" required placeholder="2999.00" class="form-field mono">
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-500 mb-1">Sale Price (₹)</label>
          <input type="number" step="0.01" id="pSalePrice" placeholder="1499.00 (Optional)" class="form-field mono">
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-500 mb-1">Primary Image URL</label>
        <input type="url" id="pImage" placeholder="https://images.unsplash.com/photo-..." class="form-field">
      </div>

      <div class="flex items-center gap-6 pt-2">
        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
          <input type="checkbox" id="pIsActive" checked class="row-check"> Active Status
        </label>
        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
          <input type="checkbox" id="pIsInStock" checked class="row-check"> In Stock State
        </label>
        <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700">
          <input type="checkbox" id="pIsFeatured" class="row-check"> Featured Product
        </label>
      </div>

      <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
        <button type="button" class="btn-secondary text-sm" onclick="closeProductModal()">Cancel</button>
        <button type="submit" id="saveProductBtn" class="btn-primary text-sm">Save Product</button>
      </div>
    </form>
  </div>
</div>

<script>
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';
let productSearchTimeout;

function openProductModal() {
  document.getElementById('modalTitle').textContent = 'Add New Product';
  document.getElementById('formEncryptedId').value = '';
  document.getElementById('productForm').reset();
  document.getElementById('pIsActive').checked = true;
  document.getElementById('pIsInStock').checked = true;
  document.getElementById('productModal').classList.add('show');
}

function closeProductModal() {
  document.getElementById('productModal').classList.remove('show');
}

function debounceProductFetch() {
  clearTimeout(productSearchTimeout);
  productSearchTimeout = setTimeout(() => fetchProducts(1), 300);
}

function showToast(msg, type = 'success') {
  const container = document.getElementById('toastContainer');
  if (!container) return;
  const item = document.createElement('div');
  item.className = `toast-item toast-${type}`;
  item.innerHTML = `<span class="material-icons text-[18px]">${type === 'success' ? 'check_circle' : 'error'}</span><span>${escapeHtml(msg)}</span>`;
  container.appendChild(item);
  setTimeout(() => {
    item.classList.add('removing');
    setTimeout(() => item.remove(), 300);
  }, 3000);
}

async function fetchProducts(page = 1) {
  const category = document.getElementById('filterCategory').value;
  const status = document.getElementById('filterStatus').value;
  const stock = document.getElementById('filterStock').value;
  const featured = document.getElementById('filterFeatured').value;
  const search = document.getElementById('searchInput').value;

  const url = `${BASE_URL}/admin/products?ajax=1&page=${page}&category=${encodeURIComponent(category)}&status=${encodeURIComponent(status)}&stock=${encodeURIComponent(stock)}&featured=${encodeURIComponent(featured)}&search=${encodeURIComponent(search)}`;

  try {
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    if (data.success) {
      renderProductsTable(data.data);
      renderPagination(data.pagination);
      if (data.kpis) updateKPIs(data.kpis);
    }
  } catch(e) {
    console.error('Fetch products error:', e);
  }
}

function renderProductsTable(products) {
  const tbody = document.getElementById('productsBody');
  if (!products || products.length === 0) {
    tbody.innerHTML = `<tr><td colspan="10" class="text-center py-12 text-slate-400">No products found matching filters.</td></tr>`;
    return;
  }

  let html = '';
  products.forEach(p => {
    const isStockOn = parseInt(p.is_in_stock) === 1;
    const isActiveOn = parseInt(p.is_active) === 1;
    const imgTag = p.primary_image ? `<img src="${escapeHtml(p.primary_image)}" class="img-thumb">` : `<div class="img-thumb bg-slate-100 flex items-center justify-center text-slate-400"><span class="material-icons text-[20px]">inventory_2</span></div>`;

    html += `
      <tr id="row-${p.encrypted_id}">
        <td><input type="checkbox" class="row-check product-select-check" value="${p.encrypted_id}" onchange="updateBulkBar()"></td>
        <td>
          <div class="flex items-center gap-2.5">
            ${imgTag}
            <div class="min-w-0">
              <p class="font-medium text-slate-700 text-sm truncate max-w-[170px]">${escapeHtml(p.name)}</p>
              <p class="text-[10px] text-slate-400 mono">UUID: ${escapeHtml(p.uuid).substring(0, 8)}...</p>
            </div>
          </div>
        </td>
        <td><span class="badge badge-blue text-[10px]">${escapeHtml(p.category_name || 'General')}</span></td>
        <td class="mono text-xs font-semibold text-slate-600">${escapeHtml(p.sku)}</td>
        <td>
          <div class="mono text-xs">
            <span class="${p.sale_price ? 'text-slate-400 line-through' : 'text-slate-800 font-semibold'}">₹${Number(p.base_price).toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>
            ${p.sale_price ? `<br><span class="text-green-600 font-semibold">₹${Number(p.sale_price).toLocaleString('en-IN', {minimumFractionDigits: 2})}</span>` : ''}
          </div>
        </td>
        <td>
          <div class="inline-toggle ${isStockOn ? 'on' : ''}" onclick="toggleStock('${p.encrypted_id}', this)">
            <div class="it-thumb"></div>
          </div>
        </td>
        <td>
          <div class="inline-toggle ${isActiveOn ? 'on' : ''}" onclick="toggleActive('${p.encrypted_id}', this)">
            <div class="it-thumb"></div>
          </div>
        </td>
        <td><span class="text-xs">★<span class="font-semibold">${Number(p.average_rating).toFixed(1)}</span></span></td>
        <td class="mono text-xs font-semibold">${Number(p.total_sold).toLocaleString()}</td>
        <td>
          <div class="flex items-center gap-1">
            <a href="${BASE_URL}/admin/products/edit/${p.encrypted_id}" class="action-btn" title="Edit Product">
              <span class="material-icons text-[16px]">edit</span>
            </a>
            <a href="${BASE_URL}/admin/products/variants/${p.encrypted_id}" class="action-btn text-cc-purple hover:bg-purple-50" title="Manage Variants (${p.variant_count})">
              <span class="material-icons text-[16px]">style</span>
            </a>
            <button class="action-btn danger" onclick="deleteProduct('${p.encrypted_id}')" title="Soft Delete">
              <span class="material-icons text-[16px]">delete_outline</span>
            </button>
          </div>
        </td>
      </tr>
    `;
  });

  tbody.innerHTML = html;
}

function renderPagination(p) {
  document.getElementById('paginationText').textContent = `Showing page ${p.currentPage} of ${p.totalPages} (${p.totalCount} total products)`;
  const container = document.getElementById('paginationContainer');
  if (p.totalPages <= 1) { container.innerHTML = ''; return; }

  let html = '';
  for (let i = 1; i <= p.totalPages; i++) {
    html += `<button class="btn-icon ${i === p.currentPage ? 'bg-cc-blue text-white border-cc-blue' : 'bg-white text-slate-600 border-slate-200'}" 
                     style="width:32px;height:32px;font-size:12px;font-weight:700" 
                     onclick="fetchProducts(${i})">${i}</button>`;
  }
  container.innerHTML = html;
}

function updateKPIs(k) {
  if (k.totalActive !== undefined) document.getElementById('kpiActive').textContent = Number(k.totalActive).toLocaleString();
  if (k.featuredCount !== undefined) document.getElementById('kpiFeatured').textContent = Number(k.featuredCount).toLocaleString();
  if (k.outOfStock !== undefined) document.getElementById('kpiOOS').textContent = Number(k.outOfStock).toLocaleString();
  if (k.deletedCount !== undefined) document.getElementById('kpiDeleted').textContent = Number(k.deletedCount).toLocaleString();
  if (k.withVariants !== undefined) document.getElementById('kpiVariants').textContent = Number(k.withVariants).toLocaleString();
  if (k.valuationSum !== undefined) document.getElementById('kpiValuation').textContent = '₹' + Number(k.valuationSum).toLocaleString('en-IN', {minimumFractionDigits: 2});
}

async function toggleActive(encryptedId, el) {
  try {
    const res = await fetch(`${BASE_URL}/admin/products/toggle-active`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: encryptedId })
    });
    const data = await res.json();
    if (data.success) {
      el.classList.toggle('on');
      showToast('Active state toggled', 'success');
    }
  } catch(e) {
    showToast('Failed to toggle active state', 'error');
  }
}

async function toggleStock(encryptedId, el) {
  try {
    const res = await fetch(`${BASE_URL}/admin/products/toggle-stock`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: encryptedId })
    });
    const data = await res.json();
    if (data.success) {
      el.classList.toggle('on');
      showToast('Stock state toggled', 'success');
    }
  } catch(e) {
    showToast('Failed to toggle stock state', 'error');
  }
}

async function editProduct(encryptedId) {
  try {
    const res = await fetch(`${BASE_URL}/admin/products/detail/${encryptedId}`);
    const data = await res.json();
    if (data.success) {
      const p = data.product;
      document.getElementById('modalTitle').textContent = 'Edit Product';
      document.getElementById('formEncryptedId').value = p.encrypted_id;
      document.getElementById('pName').value = p.name;
      document.getElementById('pSku').value = p.sku;
      document.getElementById('pCategory').value = p.category_encrypted_id;
      document.getElementById('pBasePrice').value = p.base_price;
      document.getElementById('pSalePrice').value = p.sale_price || '';
      document.getElementById('pImage').value = (p.images && p.images.length) ? p.images[0].image_url : '';
      document.getElementById('pIsActive').checked = parseInt(p.is_active) === 1;
      document.getElementById('pIsInStock').checked = parseInt(p.is_in_stock) === 1;
      document.getElementById('pIsFeatured').checked = parseInt(p.is_featured) === 1;
      document.getElementById('productModal').classList.add('show');
    }
  } catch(e) {
    showToast('Failed to fetch product details', 'error');
  }
}

async function submitProductForm(e) {
  e.preventDefault();
  const encryptedId = document.getElementById('formEncryptedId').value;
  const isEdit = !!encryptedId;

  const payload = {
    product_id: encryptedId,
    name: document.getElementById('pName').value.trim(),
    sku: document.getElementById('pSku').value.trim(),
    category_encrypted_id: document.getElementById('pCategory').value,
    base_price: document.getElementById('pBasePrice').value,
    sale_price: document.getElementById('pSalePrice').value,
    image_url: document.getElementById('pImage').value.trim(),
    is_active: document.getElementById('pIsActive').checked ? 1 : 0,
    is_in_stock: document.getElementById('pIsInStock').checked ? 1 : 0,
    is_featured: document.getElementById('pIsFeatured').checked ? 1 : 0
  };

  const endpoint = isEdit ? `${BASE_URL}/admin/products/update` : `${BASE_URL}/admin/products/store`;

  try {
    const res = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      closeProductModal();
      showToast(data.message, 'success');
      fetchProducts(1);
    } else {
      showToast(data.message || 'Error saving product', 'error');
    }
  } catch(e) {
    showToast('Network error saving product', 'error');
  }
}

async function deleteProduct(encryptedId) {
  if (!confirm('Are you sure you want to soft delete this product?')) return;
  try {
    const res = await fetch(`${BASE_URL}/admin/products/delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_id: encryptedId })
    });
    const data = await res.json();
    if (data.success) {
      showToast('Product soft-deleted', 'success');
      fetchProducts(1);
    }
  } catch(e) {
    showToast('Failed to delete product', 'error');
  }
}

function toggleAllRows(master) {
  document.querySelectorAll('.product-select-check').forEach(cb => cb.checked = master.checked);
  updateBulkBar();
}

function updateBulkBar() {
  const selected = document.querySelectorAll('.product-select-check:checked');
  const bar = document.getElementById('bulkBar');
  document.getElementById('selectedCount').textContent = selected.length;
  if (selected.length > 0) {
    bar.classList.add('show');
  } else {
    bar.classList.remove('show');
  }
}

async function executeBulk(action) {
  const selected = Array.from(document.querySelectorAll('.product-select-check:checked')).map(cb => cb.value);
  if (!selected.length) return;

  try {
    const res = await fetch(`${BASE_URL}/admin/products/bulk-action`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ product_ids: selected, action: action })
    });
    const data = await res.json();
    if (data.success) {
      showToast(data.message, 'success');
      fetchProducts(1);
    }
  } catch(e) {
    showToast('Failed to execute bulk action', 'error');
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
