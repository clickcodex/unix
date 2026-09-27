<?php
$isEdit = !empty($product['id']);
$pageTitle = $isEdit ? 'Edit Product — ' . htmlspecialchars($product['name']) : 'Create Product';
$activeMenu = 'products';

require_once __DIR__ . '/../layouts/header.php';

$categories = $categories ?? [];
$units = $units ?? [];
$allTags = $allTags ?? [];
$productImages = $product['images'] ?? [];
$productTags = $product['tags'] ?? [];
$productSpecs = $product['specifications'] ?? [];
$productVideoUrl = !empty($product['videos'][0]['video_url']) ? $product['videos'][0]['video_url'] : ($product['video_url'] ?? '');

$assignedTagIds = array_column($productTags, 'id');
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Header Banner -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900"><?= $isEdit ? 'Edit Product Entity' : 'Create New Product' ?></h1>
      <p class="text-slate-500 text-sm mt-1">Configure catalog database properties, pricing, logistics, tags, and media assets</p>
    </div>
    <div class="flex items-center gap-3">
      <a href="<?= $baseUrl ?>/admin/products" class="btn-secondary text-sm">
        <span class="material-icons text-[18px]">arrow_back</span> Back to Catalog
      </a>
      <button type="button" onclick="saveProduct()" class="btn-primary text-sm shadow-md shadow-cc-blue/20">
        <span class="material-icons text-[18px]">save</span> <?= $isEdit ? 'Update Entity Database' : 'Save Product' ?>
      </button>
    </div>
  </div>

  <form id="productForm" onsubmit="event.preventDefault(); saveProduct();">
    <input type="hidden" id="fProductId" value="<?= $isEdit ? \App\Helpers\SecurityHelper::encryptId($product['id']) : '' ?>">

    <!-- 2-Column Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- ===== LEFT COLUMN: CORE ENTITY DATA (Colspan 2) ===== -->
      <div class="lg:col-span-2 space-y-6">

        <!-- Basic Information Card -->
        <div class="section-card p-5 sm:p-6">
          <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
            <div class="w-9 h-9 rounded-xl bg-cc-blue/10 flex items-center justify-center text-cc-blue shrink-0">
              <span class="material-icons text-[20px]">info</span>
            </div>
            <div>
              <h3 class="font-heading text-base font-bold text-slate-900">Basic Information</h3>
              <p class="text-xs text-slate-400">Core database parameters of the product entity</p>
            </div>
          </div>

          <div class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Product Name *</label>
                <input type="text" id="fName" required value="<?= htmlspecialchars($product['name'] ?? '') ?>" placeholder="e.g. Wireless Noise-Canceling Headphones" class="input-field" oninput="autoGenSlug(this.value)">
              </div>
              <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">SKU (Stock Keeping Unit) *</label>
                <input type="text" id="fSku" required value="<?= htmlspecialchars($product['sku'] ?? '') ?>" placeholder="e.g. CC-EAR-001" class="input-field mono">
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">URL Slug</label>
              <div class="flex items-center gap-2">
                <input type="text" id="fSlug" value="<?= htmlspecialchars($product['slug'] ?? '') ?>" placeholder="wireless-noise-canceling-headphones" class="input-field mono text-xs">
                <button type="button" onclick="autoGenSlug(document.getElementById('fName').value)" class="btn-secondary text-xs shrink-0 py-2">Auto</button>
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Short Description</label>
              <textarea id="fShortDescription" rows="2" placeholder="Brief high-impact product summary..." class="input-field text-xs resize-y"><?= htmlspecialchars($product['short_description'] ?? '') ?></textarea>
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">Full Specifications &amp; Story Description</label>
              <textarea id="fDescription" rows="6" placeholder="Detailed product narrative, markup instructions, or feature list..." class="input-field text-xs resize-y"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <!-- Product Images & Video Assets Card -->
        <div class="section-card p-5 sm:p-6">
          <div class="flex items-center gap-3 mb-5 border-b border-slate-100 pb-4">
            <div class="w-9 h-9 rounded-xl bg-cc-pink/10 flex items-center justify-center text-cc-pink shrink-0">
              <span class="material-icons text-[20px]">photo_library</span>
            </div>
            <div>
              <h3 class="font-heading text-base font-bold text-slate-900">Product Images &amp; Video Assets</h3>
              <p class="text-xs text-slate-400">Insert and organize visual media arrays and video streams</p>
            </div>
          </div>

          <!-- Image File Dropzone -->
          <div class="border-2 border-dashed border-slate-200 hover:border-cc-blue rounded-2xl p-6 text-center transition cursor-pointer mb-5 bg-slate-50/50 hover:bg-slate-50"
               onclick="document.getElementById('imageFileInput').click()"
               ondragover="event.preventDefault()"
               ondrop="handleImageDrop(event)">
            <input type="file" id="imageFileInput" accept="image/*" multiple class="hidden" onchange="uploadImageFiles(this.files)">
            <div class="w-12 h-12 rounded-full bg-cc-blue/10 flex items-center justify-center text-cc-blue mx-auto mb-2">
              <span class="material-icons text-2xl">cloud_upload</span>
            </div>
            <p class="text-xs font-bold text-slate-800">Click or Drag Image Files Here</p>
            <p class="text-[11px] text-slate-400 mt-0.5">JPG, PNG, WEBP up to 10MB per file</p>
            <div id="imageUploadSpinner" class="hidden mt-2 text-xs font-semibold text-cc-blue flex items-center justify-center gap-1">
              <span class="material-icons animate-spin text-[16px]">autorenew</span> Uploading images...
            </div>
          </div>

          <!-- Uploaded Image Cards Grid -->
          <div id="imageCardsContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-6">
            <?php if (!empty($productImages)): ?>
              <?php foreach ($productImages as $idx => $img): ?>
                <div class="relative group border border-slate-200 rounded-xl overflow-hidden bg-white shadow-sm image-card-item" data-url="<?= htmlspecialchars($img['image_url']) ?>">
                  <img src="<?= htmlspecialchars($img['image_url']) ?>" class="w-full h-24 object-cover">
                  <?php if ((int)$img['is_primary'] === 1): ?>
                    <span class="absolute top-1.5 left-1.5 bg-cc-blue text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow">Primary</span>
                  <?php endif; ?>
                  <div class="p-1.5 bg-slate-50 flex items-center justify-between border-t border-slate-100 text-[10px]">
                    <button type="button" onclick="setPrimaryImage(this)" class="text-cc-blue font-bold hover:underline">Set Primary</button>
                    <button type="button" onclick="removeImageCard(this)" class="text-red-500 font-bold hover:underline">Delete</button>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <!-- Video Stream Section -->
          <div class="pt-4 border-t border-slate-100">
            <label class="block text-xs font-bold text-slate-700 mb-2">Product Video Asset</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 items-start">
              <div class="border-2 border-dashed border-cc-purple/30 bg-cc-purple/5 hover:bg-cc-purple/10 rounded-2xl p-4 text-center transition cursor-pointer"
                   onclick="document.getElementById('videoFileInput').click()">
                <input type="file" id="videoFileInput" accept="video/*" class="hidden" onchange="uploadVideoFile(this.files[0])">
                <span class="material-icons text-cc-purple text-2xl">video_call</span>
                <p class="text-xs font-semibold text-slate-800">Upload Video File</p>
                <p class="text-[10px] text-slate-400">MP4, WEBM up to 50MB</p>
                <div id="videoUploadSpinner" class="hidden mt-2 text-xs font-semibold text-cc-purple flex items-center justify-center gap-1">
                  <span class="material-icons animate-spin text-[14px]">autorenew</span> Uploading video...
                </div>
              </div>
              <div class="space-y-2">
                <input type="url" id="fVideoUrl" value="<?= htmlspecialchars($productVideoUrl) ?>" placeholder="https://cdn.example.com/video.mp4" class="input-field text-xs mono" oninput="previewVideoUrl(this.value)">
                <div id="videoPreviewBox" class="hidden rounded-xl overflow-hidden border border-slate-200 bg-slate-900 mt-2">
                  <video id="videoPreviewPlayer" controls class="w-full h-32 object-cover"></video>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Custom Attributes Card (Product Specifications) -->
        <div class="section-card p-5 sm:p-6">
          <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-cc-purple/10 flex items-center justify-center text-cc-purple shrink-0">
                <span class="material-icons text-[20px]">tune</span>
              </div>
              <div>
                <h3 class="font-heading text-base font-bold text-slate-900">Custom Attributes</h3>
                <p class="text-xs text-slate-400">Specifications stored in schema properties key-value mappings</p>
              </div>
            </div>
            <button type="button" onclick="addSpecRow()" class="flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold px-3 py-1.5 rounded-lg transition">
              <span class="material-icons text-[16px]">add</span> Add Parameter
            </button>
          </div>

          <div id="specRowsContainer" class="space-y-3">
            <?php if (!empty($productSpecs)): ?>
              <?php foreach ($productSpecs as $spec): ?>
                <div class="spec-row flex items-center gap-2">
                  <button type="button" onclick="removeSpecRow(this)" class="w-7 h-7 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition shrink-0">
                    <span class="material-icons text-[16px]">close</span>
                  </button>
                  <select class="spec-group-input input-field text-xs w-36">
                    <option value="Connectivity" <?= ($spec['spec_group'] === 'Connectivity') ? 'selected' : '' ?>>Connectivity</option>
                    <option value="Audio" <?= ($spec['spec_group'] === 'Audio') ? 'selected' : '' ?>>Audio</option>
                    <option value="Build" <?= ($spec['spec_group'] === 'Build') ? 'selected' : '' ?>>Build</option>
                    <option value="Battery" <?= ($spec['spec_group'] === 'Battery') ? 'selected' : '' ?>>Battery</option>
                    <option value="Display" <?= ($spec['spec_group'] === 'Display') ? 'selected' : '' ?>>Display</option>
                    <option value="General" <?= ($spec['spec_group'] === 'General' || empty($spec['spec_group'])) ? 'selected' : '' ?>>General</option>
                  </select>
                  <input type="text" class="spec-key-input input-field text-xs" value="<?= htmlspecialchars($spec['spec_key']) ?>" placeholder="Key (e.g. Bluetooth Standard)">
                  <input type="text" class="spec-val-input input-field text-xs" value="<?= htmlspecialchars($spec['spec_value']) ?>" placeholder="Value (e.g. Bluetooth 5.3)">
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- SKU Variants Card -->
        <div class="section-card p-5 sm:p-6">
          <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 rounded-xl bg-cc-orange/10 flex items-center justify-center text-cc-orange shrink-0">
                <span class="material-icons text-[20px]">layers</span>
              </div>
              <div>
                <h3 class="font-heading text-base font-bold text-slate-900">SKU Variants</h3>
                <p class="text-xs text-slate-400">Override base item prices and track distinct stock inventories</p>
              </div>
            </div>
            <?php if ($isEdit): ?>
              <a href="<?= $baseUrl ?>/admin/products/variants/<?= \App\Helpers\SecurityHelper::encryptId($product['id']) ?>" class="btn-primary text-xs py-1.5 px-3 bg-cc-purple hover:bg-purple-700">
                <span class="material-icons text-[16px]">tune</span> Manage Variants Matrix
              </a>
            <?php endif; ?>
          </div>

          <div id="variantRowsSummary" class="space-y-2">
            <?php if (!empty($product['variants'])): ?>
              <?php foreach ($product['variants'] as $v): ?>
                <div class="flex items-center justify-between p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                  <div class="flex items-center gap-2">
                    <span class="mono font-bold text-slate-800"><?= htmlspecialchars($v['sku']) ?></span>
                    <span class="badge badge-purple text-[10px]"><?= htmlspecialchars($v['attribute_summary'] ?: 'Variant') ?></span>
                  </div>
                  <div class="flex items-center gap-4 mono">
                    <span>Price: <strong class="text-slate-900"><?= $v['price_override'] !== null ? '₹'.number_format($v['price_override'], 2) : 'Base Price' ?></strong></span>
                    <span>Stock: <strong class="<?= (int)$v['stock_qty'] > 0 ? 'text-slate-800' : 'text-red-500' ?>"><?= number_format($v['stock_qty']) ?> units</strong></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center py-6 text-slate-400 text-xs">
                No SKU variants configured for this product yet.
                <?php if ($isEdit): ?>
                  <br><a href="<?= $baseUrl ?>/admin/products/variants/<?= \App\Helpers\SecurityHelper::encryptId($product['id']) ?>" class="text-cc-purple font-semibold hover:underline mt-1 inline-block">Click here to add variants</a>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>

      </div>

      <!-- ===== RIGHT COLUMN: SYSTEM PARAMETERS & METADATA (Colspan 1) ===== -->
      <div class="space-y-6">

        <!-- Status & Visibility Toggle Card -->
        <div class="section-card p-5">
          <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
            <span class="material-icons text-cc-blue text-[20px]">toggle_on</span>
            <h4 class="font-heading font-bold text-sm text-slate-800">Publish Flags</h4>
          </div>

          <div class="space-y-3">
            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 hover:bg-slate-100/70 transition cursor-pointer" onclick="toggleSwitch('toggleIsActive')">
              <div>
                <p class="text-xs font-bold text-slate-800">is_active</p>
                <p class="text-[10px] text-slate-400">Available to customers in store</p>
              </div>
              <div class="inline-toggle <?= (!$isEdit || (int)$product['is_active'] === 1) ? 'on' : '' ?>" id="toggleIsActive">
                <div class="it-thumb"></div>
              </div>
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 hover:bg-slate-100/70 transition cursor-pointer" onclick="toggleSwitch('toggleIsFeatured')">
              <div>
                <p class="text-xs font-bold text-slate-800">is_featured</p>
                <p class="text-[10px] text-slate-400">Display in top galleries</p>
              </div>
              <div class="inline-toggle <?= ($isEdit && (int)$product['is_featured'] === 1) ? 'on' : '' ?>" id="toggleIsFeatured">
                <div class="it-thumb"></div>
              </div>
            </div>

            <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-200 hover:bg-slate-100/70 transition cursor-pointer" onclick="toggleSwitch('toggleIsInStock')">
              <div>
                <p class="text-xs font-bold text-slate-800">is_in_stock</p>
                <p class="text-[10px] text-slate-400">Global stock visibility</p>
              </div>
              <div class="inline-toggle <?= (!$isEdit || (int)$product['is_in_stock'] === 1) ? 'on' : '' ?>" id="toggleIsInStock">
                <div class="it-thumb"></div>
              </div>
            </div>
          </div>

          <div class="mt-4 pt-4 border-t border-slate-100 space-y-3">
            <div>
              <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">CATEGORY_ID (FK RELATION)</label>
              <select id="fCategory" class="input-field text-xs" required>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= \App\Helpers\SecurityHelper::encryptId($cat['id']) ?>" <?= ($isEdit && (int)$product['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>>
                    <?= $cat['id'] ?> — <?= htmlspecialchars($cat['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div>
              <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">SORT_ORDER (PRIORITY RANK)</label>
              <input type="number" id="fSortOrder" value="<?= htmlspecialchars($product['sort_order'] ?? '10') ?>" min="0" class="input-field text-xs mono">
            </div>
          </div>
        </div>

        <!-- Financial Matrix Card -->
        <div class="section-card p-5">
          <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
            <span class="material-icons text-cc-yellow text-[20px]">payments</span>
            <h4 class="font-heading font-bold text-sm text-slate-800">Financial Matrix</h4>
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">BASE COST PRICE <span class="mono font-normal">DECIMAL</span></label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-xs font-semibold text-slate-400">₹</span>
                <input type="number" id="fCostPrice" value="<?= htmlspecialchars($product['cost_price'] ?? '') ?>" step="0.01" class="input-field pl-7 text-xs mono" oninput="calculateMargin()" placeholder="1200.00">
              </div>
            </div>

            <div>
              <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">STORE BASE MSRP <span class="mono font-normal">DECIMAL</span> *</label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-xs font-semibold text-slate-400">₹</span>
                <input type="number" id="fBasePrice" required value="<?= htmlspecialchars($product['base_price'] ?? '') ?>" step="0.01" class="input-field pl-7 text-xs mono" oninput="calculateMargin()" placeholder="2999.00">
              </div>
            </div>

            <div>
              <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">SALE DISCOUNTED PRICE <span class="mono font-normal">DECIMAL</span></label>
              <div class="relative">
                <span class="absolute left-3 top-2 text-xs font-semibold text-slate-400">₹</span>
                <input type="number" id="fSalePrice" value="<?= htmlspecialchars($product['sale_price'] ?? '') ?>" step="0.01" class="input-field pl-7 text-xs mono" oninput="calculateMargin()" placeholder="2499.00">
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">CURRENCY CODE</label>
                <input type="text" id="fCurrency" value="<?= htmlspecialchars($product['currency'] ?? 'INR') ?>" class="input-field text-xs font-semibold mono" readonly>
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">TAX RATE (%)</label>
                <input type="number" id="fTaxRate" value="<?= htmlspecialchars($product['tax_rate'] ?? '18.00') ?>" step="0.01" class="input-field text-xs mono">
              </div>
            </div>

            <div id="marginContainer" class="p-3 bg-slate-50 rounded-xl border border-slate-200 mt-2 space-y-1.5">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Financial Margin Metrics</p>
              <div class="flex justify-between items-center text-xs text-slate-600">
                <span>Net Estimated Profit:</span>
                <span id="profitAmount" class="font-bold text-slate-900 text-sm">₹0.00</span>
              </div>
              <div class="flex justify-between items-center text-xs text-slate-600">
                <span>Profit Margin %:</span>
                <span id="marginPercentage" class="font-bold text-emerald-600 text-sm">0%</span>
              </div>
              <div class="flex justify-between items-center text-xs text-slate-600">
                <span>Investment Markup %:</span>
                <span id="markupPercentage" class="font-bold text-indigo-600 text-xs">0%</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Logistical Details & Dynamic Units -->
        <div class="section-card p-5">
          <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
            <span class="material-icons text-cc-purple text-[20px]">local_shipping</span>
            <h4 class="font-heading font-bold text-sm text-slate-800">Dimensions &amp; Units</h4>
          </div>

          <div class="space-y-3 text-xs">
            <div class="grid grid-cols-2 gap-2">
              <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">UNIT_ID (FK RELATION)</label>
                <select id="fUnitId" class="input-field text-xs">
                  <option value="">Select unit</option>
                  <?php foreach ($units as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= ($isEdit && (int)($product['unit_id'] ?? 0) === (int)$u['id']) ? 'selected' : '' ?>>
                      <?= $u['id'] ?> — <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['symbol']) ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">UNIT_VALUE</label>
                <input type="number" id="fUnitValue" value="<?= htmlspecialchars($product['unit_value'] ?? '1.00') ?>" step="0.01" class="input-field text-xs mono">
              </div>
            </div>

            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
              <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">WEIGHT (G)</label>
                <input type="number" id="fWeightGrams" value="<?= htmlspecialchars($product['weight_grams'] ?? '') ?>" class="input-field text-xs mono" placeholder="e.g. 50">
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">LENGTH (MM)</label>
                <input type="number" id="fLengthMm" value="<?= htmlspecialchars($product['length_mm'] ?? '') ?>" class="input-field text-xs mono" placeholder="e.g. 60">
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">WIDTH (MM)</label>
                <input type="number" id="fWidthMm" value="<?= htmlspecialchars($product['width_mm'] ?? '') ?>" class="input-field text-xs mono" placeholder="e.g. 45">
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-500 uppercase mb-0.5">HEIGHT (MM)</label>
                <input type="number" id="fHeightMm" value="<?= htmlspecialchars($product['height_mm'] ?? '') ?>" class="input-field text-xs mono" placeholder="e.g. 25">
              </div>
            </div>
          </div>
        </div>

        <!-- Product Assigned Tags Component -->
        <div class="section-card p-5">
          <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
            <div class="flex items-center gap-3">
              <span class="material-icons text-cc-pink text-[20px]">sell</span>
              <h4 class="font-heading font-bold text-sm text-slate-800">Assigned Tags</h4>
            </div>
            <button type="button" onclick="addTagRow()" class="flex items-center gap-1 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold px-2.5 py-1 rounded-md transition">
              <span class="material-icons text-[14px]">add</span> Add Tag
            </button>
          </div>

          <div id="tagRowsContainer" class="space-y-3">
            <?php if (!empty($productTags)): ?>
              <?php foreach ($productTags as $t): ?>
                <div class="tag-row flex items-center gap-2">
                  <button type="button" onclick="removeTagRow(this)" class="w-7 h-7 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition shrink-0">
                    <span class="material-icons text-[16px]">close</span>
                  </button>
                  <select class="tag-select-input input-field text-xs">
                    <?php foreach ($allTags as $at): ?>
                      <option value="<?= $at['id'] ?>" <?= ((int)$t['id'] === (int)$at['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($at['name']) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                  <span class="w-5 h-5 rounded shrink-0 shadow-sm" style="background-color: <?= htmlspecialchars($t['color_hex'] ?: '#2D82FF') ?>"></span>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- SEO Fields & Live Google Snippet Preview Box -->
        <div class="section-card p-5">
          <div class="flex items-center gap-3 mb-4 pb-3 border-b border-slate-100">
            <span class="material-icons text-emerald-500 text-[20px]">travel_explore</span>
            <h4 class="font-heading font-bold text-sm text-slate-800">SEO Fields</h4>
          </div>

          <div class="space-y-3 text-xs">
            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">meta_title <span class="mono text-slate-400 font-normal">VARCHAR(200)</span></label>
              <input type="text" id="fMetaTitle" value="<?= htmlspecialchars($product['meta_title'] ?? '') ?>" placeholder="Buy Product Online at Best Prices" class="input-field text-xs" oninput="syncSeoPreview()">
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-600 mb-1">meta_description <span class="mono text-slate-400 font-normal">VARCHAR(500)</span></label>
              <textarea id="fMetaDescription" rows="3" placeholder="SEO snippet overview description..." class="input-field text-xs resize-none" oninput="syncSeoPreview()"><?= htmlspecialchars($product['meta_description'] ?? '') ?></textarea>
            </div>

            <!-- Live Google Snippet Widget -->
            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl space-y-1 mt-2">
              <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Live Google Snippet Preview</p>
              <div class="text-[11px] text-slate-500 truncate" id="seoPreviewUrl">https://clickcodex.com/products/<?= htmlspecialchars($product['slug'] ?? 'product-url') ?></div>
              <div class="text-[14px] text-indigo-700 font-medium hover:underline cursor-pointer truncate leading-tight" id="seoPreviewTitle">
                <?= htmlspecialchars($product['meta_title'] ?: ($product['name'] ?? 'Product Title')) ?>
              </div>
              <div class="text-[11px] text-slate-600 line-clamp-2 leading-relaxed" id="seoPreviewDesc">
                <?= htmlspecialchars($product['meta_description'] ?: ($product['short_description'] ?? 'Product overview details...')) ?>
              </div>
            </div>
          </div>
        </div>

        <!-- SUBMIT ACTION BOARD -->
        <div class="p-5 bg-slate-50 border border-slate-200 rounded-2xl flex flex-col gap-2.5">
          <button type="button" onclick="saveProduct()" class="w-full bg-cc-blue hover:bg-blue-600 text-white font-bold py-3 rounded-xl transition shadow-md flex items-center justify-center gap-2">
            <span class="material-icons text-[18px]">save</span>
            <span><?= $isEdit ? 'Update Entity Database' : 'Create Product' ?></span>
          </button>
          <a href="<?= $baseUrl ?>/admin/products" class="w-full text-center py-2.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs rounded-xl transition">
            Cancel Edits
          </a>
        </div>

      </div>
    </div>
  </form>
</div>

<script>
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';
const ALL_TAGS = <?= json_encode($allTags) ?>;

function autoGenSlug(name) {
  const slugInput = document.getElementById('fSlug');
  if (slugInput) {
    slugInput.value = name.toLowerCase().trim().replace(/[^a-z0-9 -]/g, '').replace(/\s+/g, '-').replace(/-+/g, '-');
    syncSeoPreview();
  }
}

function toggleSwitch(id) {
  const el = document.getElementById(id);
  if (el) el.classList.toggle('on');
}

function calculateMargin() {
  const cost = parseFloat(document.getElementById('fCostPrice').value) || 0;
  const base = parseFloat(document.getElementById('fBasePrice').value) || 0;
  const sale = parseFloat(document.getElementById('fSalePrice').value) || 0;
  const effectivePrice = sale > 0 ? sale : base;

  const profit = effectivePrice - cost;
  const marginPct = effectivePrice > 0 ? ((profit / effectivePrice) * 100).toFixed(1) : 0;
  const markupPct = cost > 0 ? ((profit / cost) * 100).toFixed(1) : 0;

  document.getElementById('profitAmount').textContent = '₹' + profit.toFixed(2);
  document.getElementById('marginPercentage').textContent = marginPct + '%';
  document.getElementById('markupPercentage').textContent = markupPct + '%';
}

function syncSeoPreview() {
  const slug = document.getElementById('fSlug').value || 'product-url';
  const title = document.getElementById('fMetaTitle').value || document.getElementById('fName').value || 'Product Title';
  const desc = document.getElementById('fMetaDescription').value || document.getElementById('fShortDescription').value || 'Product description details...';

  document.getElementById('seoPreviewUrl').textContent = `https://clickcodex.com/products/${slug}`;
  document.getElementById('seoPreviewTitle').textContent = title;
  document.getElementById('seoPreviewDesc').textContent = desc;
}

function uploadImageFiles(files) {
  if (!files || files.length === 0) return;
  const spinner = document.getElementById('imageUploadSpinner');
  if (spinner) spinner.classList.remove('hidden');

  let uploadedCount = 0;
  Array.from(files).forEach(file => {
    const formData = new FormData();
    formData.append('file', file);

    fetch(`${BASE_URL}/admin/products/upload-media`, {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(data => {
      uploadedCount++;
      if (data.success) {
        addImageCard(data.url);
      }
      if (uploadedCount === files.length && spinner) {
        spinner.classList.add('hidden');
      }
    })
    .catch(e => {
      uploadedCount++;
      if (uploadedCount === files.length && spinner) {
        spinner.classList.add('hidden');
      }
    });
  });
}

function handleImageDrop(e) {
  e.preventDefault();
  if (e.dataTransfer && e.dataTransfer.files) {
    uploadImageFiles(e.dataTransfer.files);
  }
}

function addImageCard(url) {
  const container = document.getElementById('imageCardsContainer');
  const card = document.createElement('div');
  card.className = 'relative group border border-slate-200 rounded-xl overflow-hidden bg-white shadow-sm image-card-item';
  card.setAttribute('data-url', url);
  card.innerHTML = `
    <img src="${url}" class="w-full h-24 object-cover">
    <div class="p-1.5 bg-slate-50 flex items-center justify-between border-t border-slate-100 text-[10px]">
      <button type="button" onclick="setPrimaryImage(this)" class="text-cc-blue font-bold hover:underline">Set Primary</button>
      <button type="button" onclick="removeImageCard(this)" class="text-red-500 font-bold hover:underline">Delete</button>
    </div>
  `;
  container.appendChild(card);
}

function setPrimaryImage(btn) {
  document.querySelectorAll('.image-card-item').forEach(c => {
    const badge = c.querySelector('.badge-primary-image');
    if (badge) badge.remove();
  });
  const card = btn.closest('.image-card-item');
  const badge = document.createElement('span');
  badge.className = 'absolute top-1.5 left-1.5 bg-cc-blue text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow badge-primary-image';
  badge.textContent = 'Primary';
  card.appendChild(badge);

  // Move card to first position
  card.parentNode.insertBefore(card, card.parentNode.firstChild);
}

function removeImageCard(btn) {
  btn.closest('.image-card-item').remove();
}

function uploadVideoFile(file) {
  if (!file) return;
  const spinner = document.getElementById('videoUploadSpinner');
  if (spinner) spinner.classList.remove('hidden');

  const formData = new FormData();
  formData.append('file', file);

  fetch(`${BASE_URL}/admin/products/upload-media`, {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      document.getElementById('fVideoUrl').value = data.url;
      previewVideoUrl(data.url);
    }
    if (spinner) spinner.classList.add('hidden');
  })
  .catch(e => {
    if (spinner) spinner.classList.add('hidden');
  });
}

function previewVideoUrl(url) {
  const box = document.getElementById('videoPreviewBox');
  const player = document.getElementById('videoPreviewPlayer');
  if (url && (url.endsWith('.mp4') || url.endsWith('.webm') || url.includes('/uploads/'))) {
    player.src = url;
    box.classList.remove('hidden');
  } else {
    box.classList.add('hidden');
  }
}

// Custom Specification Rows Logic
function addSpecRow(group = 'General', key = '', val = '') {
  const container = document.getElementById('specRowsContainer');
  const div = document.createElement('div');
  div.className = 'spec-row flex items-center gap-2';
  div.innerHTML = `
    <button type="button" onclick="removeSpecRow(this)" class="w-7 h-7 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition shrink-0">
      <span class="material-icons text-[16px]">close</span>
    </button>
    <select class="spec-group-input input-field text-xs w-36">
      <option value="Connectivity" ${group==='Connectivity'?'selected':''}>Connectivity</option>
      <option value="Audio" ${group==='Audio'?'selected':''}>Audio</option>
      <option value="Build" ${group==='Build'?'selected':''}>Build</option>
      <option value="Battery" ${group==='Battery'?'selected':''}>Battery</option>
      <option value="Display" ${group==='Display'?'selected':''}>Display</option>
      <option value="General" ${group==='General'?'selected':''}>General</option>
    </select>
    <input type="text" class="spec-key-input input-field text-xs" value="${escapeHtml(key)}" placeholder="Key (e.g. Bluetooth Standard)">
    <input type="text" class="spec-val-input input-field text-xs" value="${escapeHtml(val)}" placeholder="Value (e.g. Bluetooth 5.3)">
  `;
  container.appendChild(div);
}

function removeSpecRow(btn) {
  btn.closest('.spec-row').remove();
}

// Tag Rows Logic
function addTagRow(selectedId = '') {
  const container = document.getElementById('tagRowsContainer');
  const div = document.createElement('div');
  div.className = 'tag-row flex items-center gap-2';

  let optionsHtml = ALL_TAGS.map(t => `<option value="${t.id}" ${parseInt(selectedId)===parseInt(t.id)?'selected':''}>${escapeHtml(t.name)}</option>`).join('');

  div.innerHTML = `
    <button type="button" onclick="removeTagRow(this)" class="w-7 h-7 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500 flex items-center justify-center transition shrink-0">
      <span class="material-icons text-[16px]">close</span>
    </button>
    <select class="tag-select-input input-field text-xs">
      ${optionsHtml}
    </select>
    <span class="w-5 h-5 rounded shrink-0 shadow-sm bg-cc-blue"></span>
  `;
  container.appendChild(div);
}

function removeTagRow(btn) {
  btn.closest('.tag-row').remove();
}

async function saveProduct() {
  const productId = document.getElementById('fProductId').value;
  const isEdit = !!productId;

  // Gather Images
  const images = [];
  document.querySelectorAll('#imageCardsContainer .image-card-item').forEach(card => {
    const url = card.getAttribute('data-url');
    if (url) images.push(url);
  });

  // Gather Specifications
  const specifications = [];
  document.querySelectorAll('#specRowsContainer .spec-row').forEach(row => {
    const group = row.querySelector('.spec-group-input').value;
    const key = row.querySelector('.spec-key-input').value.trim();
    const val = row.querySelector('.spec-val-input').value.trim();
    if (key) {
      specifications.push({ group: group, key: key, value: val });
    }
  });

  // Gather Tags
  const tagIds = [];
  document.querySelectorAll('#tagRowsContainer .tag-select-input').forEach(sel => {
    if (sel.value) tagIds.push(parseInt(sel.value));
  });

  const payload = {
    product_id: productId,
    name: document.getElementById('fName').value.trim(),
    sku: document.getElementById('fSku').value.trim(),
    slug: document.getElementById('fSlug').value.trim(),
    short_description: document.getElementById('fShortDescription').value.trim(),
    description: document.getElementById('fDescription').value.trim(),
    category_encrypted_id: document.getElementById('fCategory').value,
    base_price: document.getElementById('fBasePrice').value,
    sale_price: document.getElementById('fSalePrice').value,
    cost_price: document.getElementById('fCostPrice').value,
    currency: document.getElementById('fCurrency').value,
    tax_rate: document.getElementById('fTaxRate').value,
    unit_id: document.getElementById('fUnitId').value,
    unit_value: document.getElementById('fUnitValue').value,
    weight_grams: document.getElementById('fWeightGrams').value,
    length_mm: document.getElementById('fLengthMm').value,
    width_mm: document.getElementById('fWidthMm').value,
    height_mm: document.getElementById('fHeightMm').value,
    sort_order: document.getElementById('fSortOrder').value,
    meta_title: document.getElementById('fMetaTitle').value.trim(),
    meta_description: document.getElementById('fMetaDescription').value.trim(),
    is_active: document.getElementById('toggleIsActive').classList.contains('on') ? 1 : 0,
    is_featured: document.getElementById('toggleIsFeatured').classList.contains('on') ? 1 : 0,
    is_in_stock: document.getElementById('toggleIsInStock').classList.contains('on') ? 1 : 0,
    images: images,
    video_url: document.getElementById('fVideoUrl').value.trim(),
    specifications: specifications,
    tag_ids: tagIds
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
      showToast('success', isEdit ? 'Product entity updated successfully!' : 'Product created successfully!');
      setTimeout(() => {
        window.location.href = `${BASE_URL}/admin/products`;
      }, 800);
    } else {
      showToast('error', data.message || 'Error saving product');
    }
  } catch(e) {
    showToast('error', 'Network error while saving product');
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

calculateMargin();
if (document.getElementById('fVideoUrl').value) {
  previewVideoUrl(document.getElementById('fVideoUrl').value);
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
