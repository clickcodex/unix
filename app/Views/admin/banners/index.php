<?php
$pageTitle = 'Banners & Promotional Media';
$activeMenu = 'banners';

require_once __DIR__ . '/../layouts/header.php';

$banners = $banners ?? [];
$pagination = $pagination ?? ['current_page' => 1, 'total_pages' => 1, 'total' => 0, 'per_page' => 15];
$kpis = $kpis ?? [];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Header Banner -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">Banners &amp; Media Assets</h1>
      <p class="text-slate-500 text-sm mt-1">Manage hero sliders, promotional banners, and category media campaigns</p>
    </div>
    <div class="flex items-center gap-3">
      <button onclick="openBannerModal()" class="btn-primary text-sm shadow-md shadow-cc-blue/20">
        <span class="material-icons text-[18px]">add_photo_alternate</span> Add New Banner
      </button>
    </div>
  </div>

  <!-- KPI Metrics Cards -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <!-- Total Banners -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-blue/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-blue text-[22px]">wallpaper</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Banners</p>
          <p class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($kpis['total_banners'] ?? 0) ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500">
        <strong class="text-emerald-600"><?= number_format($kpis['active_banners'] ?? 0) ?></strong> active campaigns
      </p>
    </div>

    <!-- Hero Sliders -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-purple/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-purple text-[22px]">view_carousel</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Hero Sliders</p>
          <p class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($kpis['hero_sliders'] ?? 0) ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500">Homepage top slider</p>
    </div>

    <!-- Offer Banners -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-orange/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-orange text-[22px]">campaign</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Offer Banners</p>
          <p class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($kpis['offer_banners'] ?? 0) ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500">Promotions & deals</p>
    </div>

    <!-- Category Banners -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-pink/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-pink text-[22px]">category</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Category Banners</p>
          <p class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($kpis['category_banners'] ?? 0) ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500">Category headers</p>
    </div>
  </div>

  <!-- Filters & Search Bar -->
  <div class="section-card p-4">
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
      <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 gap-2 flex-1 w-full sm:max-w-xs focus-within:border-cc-blue focus-within:ring-2 focus-within:ring-cc-blue/10 transition">
        <span class="material-icons text-slate-400 text-[18px]">search</span>
        <input type="text" id="bannerSearch" placeholder="Search title, subtitle, link..." class="bg-transparent text-sm text-slate-700 w-full focus:outline-none placeholder:text-slate-400" oninput="debounceFetch()">
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <select id="typeFilter" class="input-field text-xs w-auto py-2" onchange="fetchBanners(1)">
          <option value="">All Banner Types</option>
          <option value="hero_slider">Hero Slider</option>
          <option value="offer_banner">Offer Banner</option>
          <option value="category_banner">Category Banner</option>
          <option value="custom">Custom</option>
        </select>
        <select id="statusFilter" class="input-field text-xs w-auto py-2" onchange="fetchBanners(1)">
          <option value="">All Statuses</option>
          <option value="1">Active Only</option>
          <option value="0">Inactive Only</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Banners Grid Display -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="bannersGrid">
    <?php if (!empty($banners)): ?>
      <?php foreach ($banners as $b): ?>
        <?= renderBannerCard($b) ?>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-span-full text-center py-12 bg-white rounded-2xl border border-slate-200">
        <span class="material-icons text-slate-300 text-[48px]">wallpaper</span>
        <p class="text-slate-500 font-medium mt-2 text-sm">No banners found</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Pagination Bar -->
  <div class="px-5 py-4 bg-white border border-slate-200 rounded-2xl flex items-center justify-between">
    <p class="text-xs text-slate-500">
      Showing <strong class="text-slate-800"><?= count($banners) ?></strong> of <strong class="text-slate-800"><?= number_format($pagination['total']) ?></strong> banners
    </p>
    <div class="flex items-center gap-1" id="paginationBar">
      <?php renderBannerPagination($pagination); ?>
    </div>
  </div>
</div>

<!-- ================= BANNER CREATE / EDIT MODAL ================= -->
<div class="modal-overlay" id="bannerModal">
  <div class="modal-box max-w-xl mx-auto">
    <div class="p-5 border-b border-slate-200 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl bg-cc-blue/10 flex items-center justify-center text-cc-blue shrink-0">
          <span class="material-icons text-[20px]">add_photo_alternate</span>
        </div>
        <div>
          <h3 class="font-heading text-base font-bold text-slate-900" id="bannerModalTitle">Add New Banner</h3>
          <p class="text-xs text-slate-400">Configure visual slide asset and navigation details</p>
        </div>
      </div>
      <button onclick="closeModal('bannerModal')" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
        <span class="material-icons text-[20px]">close</span>
      </button>
    </div>

    <form id="bannerForm" onsubmit="event.preventDefault(); saveBanner();">
      <input type="hidden" id="fBannerId" value="">

      <div class="p-5 space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Banner Title *</label>
          <input type="text" id="fTitle" required placeholder="e.g. Summer Sale 2024" class="input-field">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Subtitle / Tagline</label>
          <input type="text" id="fSubtitle" placeholder="e.g. Up to 40% off on Electronics" class="input-field text-xs">
        </div>

        <!-- Desktop Banner Image URL & Dropzone Upload -->
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Desktop Image URL *</label>
          <div class="flex items-center gap-2">
            <input type="text" id="fImageUrl" required placeholder="https://cdn.store.com/banner.jpg" class="input-field text-xs mono" oninput="previewImage('desktopPreview', this.value)">
            <button type="button" onclick="document.getElementById('desktopFileInput').click()" class="btn-secondary text-xs shrink-0 py-2">
              <span class="material-icons text-[16px]">cloud_upload</span> Upload
            </button>
            <input type="file" id="desktopFileInput" accept="image/*" class="hidden" onchange="uploadBannerImage(this.files[0], 'fImageUrl', 'desktopPreview')">
          </div>
          <div id="desktopPreview" class="mt-2 hidden rounded-xl overflow-hidden border border-slate-200 bg-slate-50 h-28 flex items-center justify-center">
            <img src="" class="h-full w-full object-cover">
          </div>
        </div>

        <!-- Mobile Banner Image URL & Dropzone Upload -->
        <div>
          <label class="block text-xs font-semibold text-slate-600 mb-1">Mobile Image URL (Optional)</label>
          <div class="flex items-center gap-2">
            <input type="text" id="fMobileImageUrl" placeholder="https://cdn.store.com/banner-mobile.jpg" class="input-field text-xs mono" oninput="previewImage('mobilePreview', this.value)">
            <button type="button" onclick="document.getElementById('mobileFileInput').click()" class="btn-secondary text-xs shrink-0 py-2">
              <span class="material-icons text-[16px]">smartphone</span> Upload
            </button>
            <input type="file" id="mobileFileInput" accept="image/*" class="hidden" onchange="uploadBannerImage(this.files[0], 'fMobileImageUrl', 'mobilePreview')">
          </div>
          <div id="mobilePreview" class="mt-2 hidden rounded-xl overflow-hidden border border-slate-200 bg-slate-50 h-24 w-36 mx-auto flex items-center justify-center">
            <img src="" class="h-full w-full object-cover">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Banner Type</label>
            <select id="fBannerType" class="input-field text-xs">
              <option value="hero_slider">Hero Slider</option>
              <option value="offer_banner">Offer Banner</option>
              <option value="category_banner">Category Banner</option>
              <option value="custom">Custom</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Placement Region</label>
            <input type="text" id="fPlacement" value="homepage_top" placeholder="e.g. homepage_top" class="input-field text-xs mono">
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Target Link URL</label>
            <input type="text" id="fLinkUrl" placeholder="/offers/summer-sale" class="input-field text-xs mono">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Link Target</label>
            <select id="fLinkTarget" class="input-field text-xs">
              <option value="_self">Same Window (_self)</option>
              <option value="_blank">New Window (_blank)</option>
            </select>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Sort Order Rank</label>
            <input type="number" id="fSortOrder" value="1" min="0" class="input-field text-xs mono">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Starts At</label>
            <input type="datetime-local" id="fStartsAt" class="input-field text-xs">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-600 mb-1">Ends At</label>
            <input type="datetime-local" id="fEndsAt" class="input-field text-xs">
          </div>
        </div>

        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between cursor-pointer" onclick="toggleModalActiveSwitch()">
          <div>
            <p class="text-xs font-bold text-slate-800">is_active</p>
            <p class="text-[10px] text-slate-400">Available to customers in store storefront</p>
          </div>
          <div class="inline-toggle on" id="fToggleIsActive">
            <div class="it-thumb"></div>
          </div>
        </div>
      </div>

      <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end gap-3 rounded-b-2xl">
        <button type="button" onclick="closeModal('bannerModal')" class="btn-secondary text-xs py-2 px-4">Cancel</button>
        <button type="submit" class="btn-primary text-xs py-2 px-4 shadow-md shadow-cc-blue/20">
          <span class="material-icons text-[16px]">save</span> Save Banner
        </button>
      </div>
    </form>
  </div>
</div>

<?php
function renderBannerCard(array $b): string {
    $typeBadge = match($b['banner_type']) {
        'hero_slider' => 'badge-purple',
        'offer_banner' => 'badge-orange',
        'category_banner' => 'badge-pink',
        default => 'badge-blue',
    };

    $isActive = (int)$b['is_active'] === 1;
    $eid = htmlspecialchars($b['encrypted_id']);

    return "
      <div class=\"section-card relative overflow-hidden bg-white group flex flex-col justify-between border border-slate-200 hover:shadow-lg transition-all duration-200\" id=\"banner-card-{$eid}\">
        <div>
          <!-- Image Banner Header -->
          <div class=\"relative h-36 w-full overflow-hidden bg-slate-900\">
            <img src=\"".htmlspecialchars($b['image_url'])."\" class=\"w-full h-full object-cover group-hover:scale-105 transition-transform duration-300\">
            <div class=\"absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-transparent\"></div>
            
            <div class=\"absolute top-3 left-3 flex items-center gap-1.5\">
              <span class=\"badge {$typeBadge} text-[10px] shadow\">".htmlspecialchars($b['banner_type'])."</span>
              <span class=\"badge badge-slate text-[10px] shadow\">Rank #".(int)$b['sort_order']."</span>
            </div>

            <div class=\"absolute top-3 right-3\">
              <div class=\"inline-toggle ".($isActive ? 'on' : '')." shadow\" onclick=\"toggleBannerActive('{$eid}', this)\" title=\"Toggle Active Status\">
                <div class=\"it-thumb\"></div>
              </div>
            </div>

            <div class=\"absolute bottom-3 left-3 right-3 text-white\">
              <h4 class=\"font-heading text-sm font-bold truncate\">".htmlspecialchars($b['title'])."</h4>
              ".($b['subtitle'] ? "<p class=\"text-[11px] text-slate-200 truncate\">".htmlspecialchars($b['subtitle'])."</p>" : "")."
            </div>
          </div>

          <!-- Body Info -->
          <div class=\"p-4 space-y-2 text-xs text-slate-600\">
            <div class=\"flex items-center justify-between text-[11px]\">
              <span class=\"text-slate-400 font-semibold\">Placement:</span>
              <span class=\"mono font-bold text-slate-700\">".htmlspecialchars($b['placement'] ?: 'homepage_top')."</span>
            </div>

            ".($b['link_url'] ? "
              <div class=\"flex items-center justify-between text-[11px]\">
                <span class=\"text-slate-400 font-semibold\">Target:</span>
                <a href=\"".htmlspecialchars($b['link_url'])."\" target=\"".htmlspecialchars($b['link_target'])."\" class=\"text-cc-blue font-mono font-semibold hover:underline truncate max-w-[180px]\">
                  ".htmlspecialchars($b['link_url'])."
                </a>
              </div>
            " : "")."

            <div class=\"flex items-center justify-between text-[10px] text-slate-400 pt-2 border-t border-slate-100\">
              <span>Starts: ".($b['starts_at'] ? date('d M Y', strtotime($b['starts_at'])) : 'Immediate')."</span>
              <span>Ends: ".($b['ends_at'] ? date('d M Y', strtotime($b['ends_at'])) : 'Never')."</span>
            </div>
          </div>
        </div>

        <!-- Action Bar -->
        <div class=\"p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between\">
          <span class=\"badge ".($isActive ? 'badge-green' : 'badge-red')." text-[9px]\">
            ".($isActive ? 'Active Campaign' : 'Inactive')."
          </span>

          <div class=\"flex items-center gap-1\">
            <button onclick=\"editBanner('{$eid}')\" class=\"action-btn\" title=\"Edit Banner\">
              <span class=\"material-icons text-[18px]\">edit</span>
            </button>
            <button onclick=\"deleteBanner('{$eid}', '".htmlspecialchars(addslashes($b['title']))."')\" class=\"action-btn danger\" title=\"Delete Banner\">
              <span class=\"material-icons text-[18px]\">delete_outline</span>
            </button>
          </div>
        </div>
      </div>
    ";
}

function renderBannerPagination(array $p): void {
    $cur = $p['current_page'];
    $total = $p['total_pages'];
    if ($total <= 1) return;

    $prevDisabled = $cur <= 1 ? 'opacity-40 pointer-events-none' : 'hover:bg-slate-200';
    echo "<button onclick=\"fetchBanners(".($cur-1).")\" class=\"w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center text-xs {$prevDisabled} transition\"><span class=\"material-icons text-[16px]\">chevron_left</span></button>";

    $start = max(1, $cur - 2);
    $end = min($total, $cur + 2);
    for ($i = $start; $i <= $end; $i++) {
        $isActive = ($i === $cur) ? 'bg-cc-blue text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200';
        echo "<button onclick=\"fetchBanners({$i})\" class=\"w-8 h-8 rounded-lg {$isActive} text-xs font-semibold flex items-center justify-center transition\">{$i}</button>";
    }

    $nextDisabled = $cur >= $total ? 'opacity-40 pointer-events-none' : 'hover:bg-slate-200';
    echo "<button onclick=\"fetchBanners(".($cur+1).")\" class=\"w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center text-xs {$nextDisabled} transition\"><span class=\"material-icons text-[16px]\">chevron_right</span></button>";
}
?>

<script>
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';
let searchTimeout = null;

function debounceFetch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => fetchBanners(1), 300);
}

function toggleModalActiveSwitch() {
  document.getElementById('fToggleIsActive').classList.toggle('on');
}

function previewImage(containerId, url) {
  const container = document.getElementById(containerId);
  if (!container) return;
  const img = container.querySelector('img');
  if (url && url.trim() !== '') {
    img.src = url;
    container.classList.remove('hidden');
  } else {
    container.classList.add('hidden');
  }
}

async function uploadBannerImage(file, inputId, previewId) {
  if (!file) return;
  const formData = new FormData();
  formData.append('file', file);

  try {
    const res = await fetch(`${BASE_URL}/admin/products/upload-media`, {
      method: 'POST',
      body: formData
    });
    const data = await res.json();
    if (data.success) {
      document.getElementById(inputId).value = data.url;
      previewImage(previewId, data.url);
      showToast('success', 'Image uploaded successfully');
    } else {
      showToast('error', data.message || 'Image upload failed');
    }
  } catch(e) {
    showToast('error', 'Network error during image upload');
  }
}

async function fetchBanners(page = 1) {
  const type = document.getElementById('typeFilter').value;
  const status = document.getElementById('statusFilter').value;
  const search = document.getElementById('bannerSearch').value;

  const url = `${BASE_URL}/admin/banners?ajax=1&page=${page}&type=${encodeURIComponent(type)}&status=${encodeURIComponent(status)}&search=${encodeURIComponent(search)}`;

  try {
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    if (data.success) {
      window.location.reload();
    }
  } catch(e) {
    showToast('error', 'Error fetching banners');
  }
}

function openBannerModal() {
  document.getElementById('bannerModalTitle').textContent = 'Add New Banner';
  document.getElementById('fBannerId').value = '';
  document.getElementById('fTitle').value = '';
  document.getElementById('fSubtitle').value = '';
  document.getElementById('fImageUrl').value = '';
  document.getElementById('fMobileImageUrl').value = '';
  document.getElementById('fBannerType').value = 'hero_slider';
  document.getElementById('fPlacement').value = 'homepage_top';
  document.getElementById('fLinkUrl').value = '';
  document.getElementById('fLinkTarget').value = '_self';
  document.getElementById('fSortOrder').value = '1';
  document.getElementById('fStartsAt').value = '';
  document.getElementById('fEndsAt').value = '';
  document.getElementById('fToggleIsActive').classList.add('on');

  previewImage('desktopPreview', '');
  previewImage('mobilePreview', '');

  document.getElementById('bannerModal').classList.add('show');
}

async function editBanner(encryptedId) {
  try {
    const res = await fetch(`${BASE_URL}/admin/banners/detail/${encryptedId}`);
    const data = await res.json();
    if (data.success && data.banner) {
      const b = data.banner;
      document.getElementById('bannerModalTitle').textContent = 'Edit Banner Entity';
      document.getElementById('fBannerId').value = b.encrypted_id;
      document.getElementById('fTitle').value = b.title;
      document.getElementById('fSubtitle').value = b.subtitle || '';
      document.getElementById('fImageUrl').value = b.image_url;
      document.getElementById('fMobileImageUrl').value = b.mobile_image_url || '';
      document.getElementById('fBannerType').value = b.banner_type;
      document.getElementById('fPlacement').value = b.placement || 'homepage_top';
      document.getElementById('fLinkUrl').value = b.link_url || '';
      document.getElementById('fLinkTarget').value = b.link_target || '_self';
      document.getElementById('fSortOrder').value = b.sort_order;
      document.getElementById('fStartsAt').value = b.starts_at ? b.starts_at.replace(' ', 'T').slice(0, 16) : '';
      document.getElementById('fEndsAt').value = b.ends_at ? b.ends_at.replace(' ', 'T').slice(0, 16) : '';

      if (parseInt(b.is_active) === 1) {
        document.getElementById('fToggleIsActive').classList.add('on');
      } else {
        document.getElementById('fToggleIsActive').classList.remove('on');
      }

      previewImage('desktopPreview', b.image_url);
      previewImage('mobilePreview', b.mobile_image_url || '');

      document.getElementById('bannerModal').classList.add('show');
    } else {
      showToast('error', data.message || 'Failed to load banner details');
    }
  } catch(e) {
    showToast('error', 'Network error loading banner');
  }
}

async function saveBanner() {
  const bannerId = document.getElementById('fBannerId').value;
  const isEdit = !!bannerId;

  const payload = {
    banner_id: bannerId,
    title: document.getElementById('fTitle').value.trim(),
    subtitle: document.getElementById('fSubtitle').value.trim(),
    image_url: document.getElementById('fImageUrl').value.trim(),
    mobile_image_url: document.getElementById('fMobileImageUrl').value.trim(),
    banner_type: document.getElementById('fBannerType').value,
    placement: document.getElementById('fPlacement').value.trim(),
    link_url: document.getElementById('fLinkUrl').value.trim(),
    link_target: document.getElementById('fLinkTarget').value,
    sort_order: document.getElementById('fSortOrder').value,
    starts_at: document.getElementById('fStartsAt').value.replace('T', ' '),
    ends_at: document.getElementById('fEndsAt').value.replace('T', ' '),
    is_active: document.getElementById('fToggleIsActive').classList.contains('on') ? 1 : 0
  };

  const endpoint = isEdit ? `${BASE_URL}/admin/banners/update` : `${BASE_URL}/admin/banners/store`;

  try {
    const res = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (data.success) {
      showToast('success', isEdit ? 'Banner updated successfully!' : 'Banner created successfully!');
      closeModal('bannerModal');
      setTimeout(() => window.location.reload(), 600);
    } else {
      showToast('error', data.message || 'Error saving banner');
    }
  } catch(e) {
    showToast('error', 'Network error while saving banner');
  }
}

async function toggleBannerActive(encryptedId, el) {
  try {
    const res = await fetch(`${BASE_URL}/admin/banners/toggle-active`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ banner_id: encryptedId })
    });
    const data = await res.json();
    if (data.success) {
      el.classList.toggle('on');
      showToast('success', 'Banner status toggled');
    } else {
      showToast('error', data.message || 'Failed to toggle status');
    }
  } catch(e) {
    showToast('error', 'Network error toggling banner status');
  }
}

async function deleteBanner(encryptedId, title) {
  if (!confirm(`Are you sure you want to delete banner "${title}"?`)) return;

  try {
    const res = await fetch(`${BASE_URL}/admin/banners/delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ banner_id: encryptedId })
    });
    const data = await res.json();
    if (data.success) {
      showToast('success', 'Banner deleted successfully');
      setTimeout(() => window.location.reload(), 600);
    } else {
      showToast('error', data.message || 'Failed to delete banner');
    }
  } catch(e) {
    showToast('error', 'Network error while deleting banner');
  }
}

function closeModal(id) {
  document.getElementById(id).classList.remove('show');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
