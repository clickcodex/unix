<?php
$pageTitle = 'Media Library & Asset Management';
$activeMenu = 'media';

require_once __DIR__ . '/../layouts/header.php';

$mediaList = $mediaData['media'] ?? [];
$pagination = [
    'currentPage' => $mediaData['currentPage'] ?? 1,
    'totalPages' => $mediaData['totalPages'] ?? 1,
    'totalCount' => $mediaData['totalCount'] ?? 0,
    'perPage' => $mediaData['perPage'] ?? 24
];
$kpis = $kpis ?? $kpiData ?? [];
$folders = $folders ?? ['general'];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Header Section -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900 flex items-center gap-3">
        <span class="p-2 rounded-xl bg-cc-blue/10 text-cc-blue flex items-center justify-center">
          <span class="material-icons text-2xl">photo_library</span>
        </span>
        Media Library &amp; Assets
      </h1>
      <p class="text-slate-500 text-sm mt-1">Upload, organize, inspect, and manage files across your e-commerce platform</p>
    </div>
    <div class="flex items-center gap-3">
      <button onclick="toggleUploadZone()" class="btn-primary text-sm shadow-md shadow-cc-blue/20 flex items-center gap-2">
        <span class="material-icons text-[18px]">cloud_upload</span>
        <span>Upload Assets</span>
      </button>
    </div>
  </div>

  <!-- KPI Metrics Cards -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <!-- Total Files -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-blue/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-blue text-[22px]">folder_zip</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Assets</p>
          <p id="kpiTotalFiles" class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($kpis['total_files'] ?? 0) ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500">All uploaded media files</p>
    </div>

    <!-- Images -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-purple/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-purple text-[22px]">image</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Images</p>
          <p id="kpiTotalImages" class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($kpis['total_images'] ?? 0) ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500">PNG, JPG, WEBP, SVG</p>
    </div>

    <!-- Documents -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-cc-orange/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-orange text-[22px]">description</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Documents</p>
          <p id="kpiTotalDocuments" class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($kpis['total_documents'] ?? 0) ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500">PDFs, CSVs &amp; Files</p>
    </div>

    <!-- Total Storage -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-emerald-600 text-[22px]">storage</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Disk Storage</p>
          <p id="kpiTotalStorage" class="text-xl font-extrabold text-slate-900 leading-tight"><?= htmlspecialchars($kpis['formatted_total_size'] ?? '0 B') ?></p>
        </div>
      </div>
      <p class="text-xs text-slate-500">Total disk space used</p>
    </div>
  </div>

  <!-- Upload Dropzone Area (Collapsible / Toggleable) -->
  <div id="uploadContainer" class="hidden transition-all duration-300">
    <div class="section-card p-6 border-2 border-dashed border-cc-blue/40 bg-gradient-to-br from-slate-50 to-blue-50/30 rounded-2xl relative">
      <button onclick="toggleUploadZone()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-1">
        <span class="material-icons text-xl">close</span>
      </button>
      
      <form id="mediaUploadForm" enctype="multipart/form-data" class="space-y-4">
        <div class="flex flex-col items-center justify-center text-center p-6 cursor-pointer" id="dropzone" onclick="document.getElementById('fileInput').click()">
          <div class="w-16 h-16 rounded-2xl bg-cc-blue/10 text-cc-blue flex items-center justify-center mb-3 shadow-inner">
            <span class="material-icons text-3xl">cloud_upload</span>
          </div>
          <h3 class="text-lg font-bold text-slate-800 mb-1">Drag &amp; drop files here or click to browse</h3>
          <p class="text-xs text-slate-500 max-w-md">
            Supports Images (JPG, PNG, WEBP, SVG, GIF), Documents (PDF, DOCX, CSV, TXT), Audio/Video &amp; ZIP files up to 50MB each.
          </p>
          <input type="file" id="fileInput" name="files[]" multiple class="hidden" onchange="handleFileSelect(event)">
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2 border-t border-slate-200/80">
          <div class="flex items-center gap-3 w-full sm:w-auto">
            <label for="uploadFolder" class="text-xs font-semibold text-slate-600 shrink-0">Upload Folder:</label>
            <select id="uploadFolder" name="folder" class="input-field text-xs py-1.5 px-3 bg-white">
              <option value="general">general</option>
              <option value="products">products</option>
              <option value="banners">banners</option>
              <option value="categories">categories</option>
              <option value="documents">documents</option>
              <option value="custom">+ Create New Folder</option>
            </select>
            <input type="text" id="customFolderInput" placeholder="New folder name..." class="hidden input-field text-xs py-1.5 px-3 bg-white w-36">
          </div>

          <div id="fileSelectedCount" class="text-xs font-medium text-slate-500">No files selected</div>

          <button type="submit" id="startUploadBtn" class="btn-primary text-xs py-2 px-5 disabled:opacity-50" disabled>
            <span class="material-icons text-[16px]">publish</span> Start Upload
          </button>
        </div>

        <!-- Upload Progress Bar -->
        <div id="uploadProgressContainer" class="hidden space-y-1 pt-2">
          <div class="flex justify-between text-xs text-slate-600 font-medium">
            <span id="uploadProgressText">Uploading...</span>
            <span id="uploadProgressPercent">0%</span>
          </div>
          <div class="w-full bg-slate-200 rounded-full h-2.5 overflow-hidden">
            <div id="uploadProgressBar" class="bg-cc-blue h-2.5 rounded-full transition-all duration-200" style="width: 0%"></div>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Filters & Toolbar Section -->
  <div class="section-card p-4">
    <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
      
      <!-- Left Filters -->
      <div class="flex flex-wrap items-center gap-3 flex-1">
        <!-- Search Input -->
        <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 gap-2 min-w-[220px] flex-1 max-w-xs focus-within:border-cc-blue focus-within:ring-2 focus-within:ring-cc-blue/10 transition">
          <span class="material-icons text-slate-400 text-[18px]">search</span>
          <input type="text" id="mediaSearch" placeholder="Search by name, alt text..." class="bg-transparent text-sm text-slate-700 w-full focus:outline-none placeholder:text-slate-400" oninput="debounceFetch()">
        </div>

        <!-- Type Filter -->
        <select id="typeFilter" class="input-field text-xs py-2 w-auto bg-slate-50" onchange="fetchMedia(1)">
          <option value="">All File Types</option>
          <option value="image">Images</option>
          <option value="document">Documents</option>
          <option value="video">Videos</option>
          <option value="audio">Audio</option>
          <option value="archive">Archives</option>
        </select>

        <!-- Folder Filter -->
        <select id="folderFilter" class="input-field text-xs py-2 w-auto bg-slate-50" onchange="fetchMedia(1)">
          <option value="">All Folders</option>
          <?php foreach ($folders as $f): ?>
            <option value="<?= htmlspecialchars($f) ?>"><?= htmlspecialchars(ucfirst($f)) ?></option>
          <?php endforeach; ?>
        </select>

        <!-- Sort Filter -->
        <select id="sortFilter" class="input-field text-xs py-2 w-auto bg-slate-50" onchange="fetchMedia(1)">
          <option value="newest">Sort: Newest First</option>
          <option value="oldest">Sort: Oldest First</option>
          <option value="name_asc">Name: A to Z</option>
          <option value="name_desc">Name: Z to A</option>
          <option value="size_desc">Size: Largest First</option>
          <option value="size_asc">Size: Smallest First</option>
        </select>
      </div>

      <!-- Right View Switcher & Actions -->
      <div class="flex items-center gap-2 self-end md:self-auto">
        <div class="bg-slate-100 p-1 rounded-xl flex items-center gap-1 border border-slate-200">
          <button id="viewGridBtn" onclick="switchView('grid')" title="Grid View" class="p-1.5 rounded-lg text-cc-blue bg-white shadow-sm transition">
            <span class="material-icons text-[18px]">grid_view</span>
          </button>
          <button id="viewTableBtn" onclick="switchView('table')" title="Table View" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 transition">
            <span class="material-icons text-[18px]">format_list_bulleted</span>
          </button>
        </div>
      </div>

    </div>
  </div>

  <!-- Bulk Action Floating Bar (Hidden by default) -->
  <div id="bulkBar" class="hidden sticky top-20 z-40 bg-slate-900 text-white rounded-2xl p-3 px-5 shadow-2xl flex items-center justify-between transition-all duration-300">
    <div class="flex items-center gap-3 text-xs sm:text-sm font-medium">
      <span class="material-icons text-cc-yellow">check_circle</span>
      <span><strong id="selectedCount">0</strong> items selected</span>
    </div>
    <div class="flex items-center gap-3">
      <button onclick="clearSelection()" class="text-xs text-slate-400 hover:text-white transition">Deselect All</button>
      <button onclick="confirmBulkDelete()" class="btn-danger text-xs py-1.5 px-3 flex items-center gap-1 bg-rose-600 hover:bg-rose-700">
        <span class="material-icons text-[16px]">delete</span> Delete Selected
      </button>
    </div>
  </div>

  <!-- Media Items Container -->
  <div id="mediaContainer">
    
    <!-- GRID VIEW CONTAINER -->
    <div id="gridView" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <?php if (empty($mediaList)): ?>
        <div class="col-span-full py-16 text-center bg-white rounded-2xl border border-slate-200">
          <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
            <span class="material-icons text-3xl">photo_library</span>
          </div>
          <h3 class="text-base font-bold text-slate-700">No media assets found</h3>
          <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Upload images or documents to populate your central media library.</p>
          <button onclick="toggleUploadZone()" class="mt-4 btn-primary text-xs">
            <span class="material-icons text-[16px]">cloud_upload</span> Upload Now
          </button>
        </div>
      <?php else: ?>
        <?php foreach ($mediaList as $item): ?>
          <?= renderGridCard($item) ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- TABLE VIEW CONTAINER (Hidden initially) -->
    <div id="tableView" class="hidden section-card overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-[11px] font-bold text-slate-400 uppercase tracking-wider">
              <th class="p-3 pl-4 w-10">
                <input type="checkbox" id="selectAllHeader" onchange="toggleSelectAll(this)" class="rounded border-slate-300 text-cc-blue focus:ring-cc-blue">
              </th>
              <th class="p-3">Preview</th>
              <th class="p-3">Filename</th>
              <th class="p-3">Folder</th>
              <th class="p-3">Type</th>
              <th class="p-3">Dimensions</th>
              <th class="p-3">Size</th>
              <th class="p-3">Uploaded</th>
              <th class="p-3 text-right pr-4">Actions</th>
            </tr>
          </thead>
          <tbody id="tableBody" class="divide-y divide-slate-100 text-sm">
            <?php foreach ($mediaList as $item): ?>
              <?= renderTableRow($item) ?>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- Pagination Controls -->
  <div id="paginationContainer" class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-200">
    <p class="text-xs text-slate-500" id="paginationInfo">
      Showing page <strong><?= $pagination['currentPage'] ?></strong> of <strong><?= $pagination['totalPages'] ?></strong> (<?= number_format($pagination['totalCount']) ?> assets total)
    </p>
    <div class="flex items-center gap-2" id="paginationButtons">
      <button onclick="fetchMedia(<?= max(1, $pagination['currentPage'] - 1) ?>)" class="btn-secondary text-xs px-3 py-1.5" <?= $pagination['currentPage'] <= 1 ? 'disabled' : '' ?>>
        <span class="material-icons text-[16px]">chevron_left</span> Prev
      </button>
      <span class="text-xs font-semibold text-slate-700 px-2"><?= $pagination['currentPage'] ?> / <?= $pagination['totalPages'] ?></span>
      <button onclick="fetchMedia(<?= min($pagination['totalPages'], $pagination['currentPage'] + 1) ?>)" class="btn-secondary text-xs px-3 py-1.5" <?= $pagination['currentPage'] >= $pagination['totalPages'] ? 'disabled' : '' ?>>
        Next <span class="material-icons text-[16px]">chevron_right</span>
      </button>
    </div>
  </div>

</div>

<!-- ASSET DETAIL & EDIT MODAL -->
<div id="detailModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full overflow-hidden border border-slate-100 flex flex-col md:flex-row max-h-[90vh]">
    
    <!-- Left Asset Preview Pane -->
    <div class="bg-slate-950 md:w-1/2 p-6 flex flex-col items-center justify-center relative min-h-[260px]">
      <button onclick="closeDetailModal()" class="md:hidden absolute top-3 right-3 text-white/60 hover:text-white bg-white/10 rounded-full p-1">
        <span class="material-icons text-xl">close</span>
      </button>
      
      <div id="modalPreviewContainer" class="w-full h-full flex items-center justify-center max-h-[380px] overflow-hidden">
        <!-- Rendered via JS -->
      </div>
    </div>

    <!-- Right Asset Info & Edit Pane -->
    <div class="md:w-1/2 p-6 flex flex-col justify-between space-y-4 overflow-y-auto">
      <div>
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
          <h3 class="font-heading font-bold text-slate-900 text-lg">Asset Details</h3>
          <button onclick="closeDetailModal()" class="hidden md:block text-slate-400 hover:text-slate-600 p-1">
            <span class="material-icons text-xl">close</span>
          </button>
        </div>

        <!-- Quick Copy Actions -->
        <div class="space-y-2 mb-4">
          <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Public Asset URL</label>
          <div class="flex items-center gap-2">
            <input type="text" id="modalPublicUrl" readonly class="input-field text-xs bg-slate-50 text-slate-600 py-1.5 font-mono select-all">
            <button onclick="copyToClipboard(document.getElementById('modalPublicUrl').value, 'Public URL')" class="btn-primary text-xs py-1.5 px-3 shrink-0 flex items-center gap-1">
              <span class="material-icons text-[14px]">content_copy</span> Copy
            </button>
          </div>
        </div>

        <!-- Edit Metadata Form -->
        <form id="mediaEditForm" onsubmit="handleEditSubmit(event)" class="space-y-3">
          <input type="hidden" id="modalMediaId" name="id">

          <div>
            <label for="modalOriginalName" class="text-xs font-semibold text-slate-700">Display Name / Title</label>
            <input type="text" id="modalOriginalName" name="original_name" required class="input-field text-xs py-2 mt-1">
          </div>

          <div>
            <label for="modalAltText" class="text-xs font-semibold text-slate-700">Alt Text (SEO &amp; Accessibility)</label>
            <input type="text" id="modalAltText" name="alt_text" placeholder="Description of image..." class="input-field text-xs py-2 mt-1">
          </div>

          <div>
            <label for="modalFolder" class="text-xs font-semibold text-slate-700">Organize Folder</label>
            <input type="text" id="modalFolder" name="folder" class="input-field text-xs py-2 mt-1">
          </div>

          <div class="grid grid-cols-2 gap-3 pt-2 text-xs text-slate-500 bg-slate-50 p-3 rounded-xl border border-slate-100">
            <div>
              <span class="block text-[10px] uppercase font-bold text-slate-400">File Size</span>
              <span id="modalSize" class="font-semibold text-slate-700">-</span>
            </div>
            <div>
              <span class="block text-[10px] uppercase font-bold text-slate-400">Dimensions</span>
              <span id="modalDimensions" class="font-semibold text-slate-700">-</span>
            </div>
            <div>
              <span class="block text-[10px] uppercase font-bold text-slate-400">MIME Type</span>
              <span id="modalMime" class="font-semibold text-slate-700 truncate block">-</span>
            </div>
            <div>
              <span class="block text-[10px] uppercase font-bold text-slate-400">Uploaded</span>
              <span id="modalDate" class="font-semibold text-slate-700">-</span>
            </div>
          </div>

          <button type="submit" class="btn-primary text-xs w-full py-2 flex items-center justify-center gap-1 mt-2">
            <span class="material-icons text-[16px]">save</span> Save Changes
          </button>
        </form>
      </div>

      <!-- Modal Footer Actions -->
      <div class="flex items-center justify-between border-t border-slate-100 pt-3 mt-4">
        <a id="modalDownloadBtn" href="#" download target="_blank" class="btn-secondary text-xs py-1.5 px-3 flex items-center gap-1">
          <span class="material-icons text-[16px]">download</span> Download
        </a>
        <button onclick="deleteSingleFromModal()" class="btn-danger text-xs py-1.5 px-3 flex items-center gap-1 bg-rose-600 hover:bg-rose-700">
          <span class="material-icons text-[16px]">delete</span> Delete Asset
        </button>
      </div>

    </div>
  </div>
</div>

<!-- TOAST NOTIFICATION CONTAINER -->
<div id="toastContainer" class="fixed bottom-5 right-5 z-50 space-y-2 pointer-events-none"></div>

<?php
/**
 * PHP Helper function to render Grid Cards
 */
function renderGridCard($item) {
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
    $id = htmlspecialchars($item['encrypted_id'] ?? $item['id']);
    $url = htmlspecialchars($item['public_url']);
    $name = htmlspecialchars($item['original_name']);
    $size = htmlspecialchars($item['formatted_size']);
    $isImage = !empty($item['is_image']);
    $ext = strtoupper($item['file_extension'] ?? 'FILE');
    $folder = htmlspecialchars($item['folder'] ?? 'general');

    ob_start();
    ?>
    <div class="media-card group relative bg-white rounded-2xl border border-slate-200/90 overflow-hidden hover:border-cc-blue/40 hover:shadow-lg transition-all duration-300 flex flex-col justify-between" data-id="<?= $id ?>">
      <!-- Selection Checkbox & Badge -->
      <div class="absolute top-2 left-2 right-2 z-10 flex items-center justify-between pointer-events-none">
        <input type="checkbox" value="<?= $id ?>" onchange="handleItemSelect(this)" class="media-checkbox pointer-events-auto rounded border-slate-300 text-cc-blue focus:ring-cc-blue shadow-sm">
        <span class="px-2 py-0.5 text-[9px] font-bold tracking-wider rounded-full bg-slate-900/70 text-white backdrop-blur-sm shadow-sm"><?= $ext ?></span>
      </div>

      <!-- Thumbnail Preview Box -->
      <div class="w-full aspect-square bg-slate-900/5 flex items-center justify-center relative overflow-hidden cursor-pointer" onclick="openDetailModal('<?= $id ?>')">
        <?php if ($isImage): ?>
          <img src="<?= $url ?>" alt="<?= $name ?>" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" onerror="handleImageError(this, '<?= addslashes(getFileIcon($item['mime_type'])) ?>', '<?= addslashes($ext) ?>')">
        <?php else: ?>
          <div class="text-slate-400 group-hover:text-cc-blue transition-colors flex flex-col items-center gap-1 p-2">
            <span class="material-icons text-4xl"><?= getFileIcon($item['mime_type']) ?></span>
            <span class="text-[10px] font-semibold text-slate-500 truncate max-w-[90px]"><?= $ext ?></span>
          </div>
        <?php endif; ?>

        <!-- Quick Hover Action Overlay -->
        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2 p-2">
          <button onclick="event.stopPropagation(); copyToClipboard('<?= $url ?>', 'Asset URL')" class="w-8 h-8 rounded-full bg-white text-slate-700 hover:text-cc-blue flex items-center justify-center shadow-md transition" title="Copy URL">
            <span class="material-icons text-[16px]">content_copy</span>
          </button>
          <button onclick="event.stopPropagation(); openDetailModal('<?= $id ?>')" class="w-8 h-8 rounded-full bg-white text-slate-700 hover:text-cc-purple flex items-center justify-center shadow-md transition" title="Inspect Details">
            <span class="material-icons text-[16px]">visibility</span>
          </button>
        </div>
      </div>

      <!-- Card Metadata Footer -->
      <div class="p-3 bg-white border-t border-slate-100">
        <h4 class="text-xs font-semibold text-slate-800 truncate" title="<?= $name ?>"><?= $name ?></h4>
        <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1">
          <span class="truncate max-w-[80px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-500"><?= $folder ?></span>
          <span><?= $size ?></span>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * PHP Helper function to render Table Rows
 */
function renderTableRow($item) {
    $id = htmlspecialchars($item['encrypted_id'] ?? $item['id']);
    $url = htmlspecialchars($item['public_url']);
    $name = htmlspecialchars($item['original_name']);
    $size = htmlspecialchars($item['formatted_size']);
    $isImage = !empty($item['is_image']);
    $ext = strtoupper($item['file_extension'] ?? 'FILE');
    $folder = htmlspecialchars($item['folder'] ?? 'general');
    $dim = (!empty($item['width']) && !empty($item['height'])) ? "{$item['width']} × {$item['height']} px" : '-';
    $date = date('M d, Y', strtotime($item['created_at']));

    ob_start();
    ?>
    <tr class="hover:bg-slate-50/80 transition media-table-row" data-id="<?= $id ?>">
      <td class="p-3 pl-4">
        <input type="checkbox" value="<?= $id ?>" onchange="handleItemSelect(this)" class="media-checkbox rounded border-slate-300 text-cc-blue focus:ring-cc-blue">
      </td>
      <td class="p-3">
        <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center cursor-pointer" onclick="openDetailModal('<?= $id ?>')">
          <?php if ($isImage): ?>
            <img src="<?= $url ?>" alt="<?= $name ?>" class="w-full h-full object-cover" onerror="handleTableImageError(this, '<?= addslashes(getFileIcon($item['mime_type'])) ?>')">
          <?php else: ?>
            <span class="material-icons text-slate-400 text-xl"><?= getFileIcon($item['mime_type']) ?></span>
          <?php endif; ?>
        </div>
      </td>
      <td class="p-3">
        <p class="font-semibold text-slate-800 text-xs truncate max-w-xs cursor-pointer hover:text-cc-blue" onclick="openDetailModal('<?= $id ?>')"><?= $name ?></p>
        <span class="text-[10px] text-slate-400 font-mono"><?= htmlspecialchars($item['filename']) ?></span>
      </td>
      <td class="p-3">
        <span class="inline-block bg-slate-100 text-slate-600 text-[10px] font-semibold px-2 py-0.5 rounded-full"><?= $folder ?></span>
      </td>
      <td class="p-3 text-xs text-slate-500 font-mono"><?= htmlspecialchars($item['mime_type']) ?></td>
      <td class="p-3 text-xs text-slate-500"><?= $dim ?></td>
      <td class="p-3 text-xs font-semibold text-slate-700"><?= $size ?></td>
      <td class="p-3 text-xs text-slate-400"><?= $date ?></td>
      <td class="p-3 text-right pr-4">
        <div class="flex items-center justify-end gap-1">
          <button onclick="copyToClipboard('<?= $url ?>', 'Asset URL')" class="p-1.5 text-slate-400 hover:text-cc-blue rounded-lg hover:bg-slate-100 transition" title="Copy Link">
            <span class="material-icons text-[18px]">content_copy</span>
          </button>
          <button onclick="openDetailModal('<?= $id ?>')" class="p-1.5 text-slate-400 hover:text-cc-purple rounded-lg hover:bg-slate-100 transition" title="Edit Metadata">
            <span class="material-icons text-[18px]">edit</span>
          </button>
          <button onclick="deleteSingleMedia('<?= $id ?>')" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Delete">
            <span class="material-icons text-[18px]">delete</span>
          </button>
        </div>
      </td>
    </tr>
    <?php
    return ob_get_clean();
}

/**
 * Material Icon mapper based on mime type
 */
function getFileIcon($mime) {
    if (strpos($mime, 'pdf') !== false) return 'picture_as_pdf';
    if (strpos($mime, 'word') !== false || strpos($mime, 'document') !== false) return 'article';
    if (strpos($mime, 'sheet') !== false || strpos($mime, 'excel') !== false || strpos($mime, 'csv') !== false) return 'grid_on';
    if (strpos($mime, 'zip') !== false || strpos($mime, 'compressed') !== false) return 'folder_zip';
    if (strpos($mime, 'video') !== false) return 'movie';
    if (strpos($mime, 'audio') !== false) return 'audiotrack';
    return 'insert_drive_file';
}
?>

<!-- JAVASCRIPT LOGIC -->
<script>
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';
let selectedMediaIds = new Set();
let currentViewMode = 'grid';
let debounceTimer = null;
let currentDetailId = null;
let selectedUploadFiles = [];

document.addEventListener('DOMContentLoaded', () => {
  setupFolderSelector();
  setupDragAndDrop();
});

// Toggle upload dropzone visibility
function toggleUploadZone() {
  const container = document.getElementById('uploadContainer');
  container.classList.toggle('hidden');
}

function setupFolderSelector() {
  const select = document.getElementById('uploadFolder');
  const customInput = document.getElementById('customFolderInput');
  select.addEventListener('change', () => {
    if (select.value === 'custom') {
      customInput.classList.remove('hidden');
      customInput.focus();
    } else {
      customInput.classList.add('hidden');
    }
  });
}

// Drag & drop dropzone handling
function setupDragAndDrop() {
  const dropzone = document.getElementById('dropzone');
  
  ['dragenter', 'dragover'].forEach(eventName => {
    dropzone.addEventListener(eventName, (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropzone.classList.add('bg-cc-blue/5', 'border-cc-blue');
    }, false);
  });

  ['dragleave', 'drop'].forEach(eventName => {
    dropzone.addEventListener(eventName, (e) => {
      e.preventDefault();
      e.stopPropagation();
      dropzone.classList.remove('bg-cc-blue/5', 'border-cc-blue');
    }, false);
  });

  dropzone.addEventListener('drop', (e) => {
    const dt = e.dataTransfer;
    const files = dt.files;
    if (files && files.length > 0) {
      document.getElementById('fileInput').files = files;
      handleFileSelect({ target: { files: files } });
    }
  });
}

function handleFileSelect(e) {
  const files = e.target.files;
  selectedUploadFiles = Array.from(files);
  const startBtn = document.getElementById('startUploadBtn');
  const countText = document.getElementById('fileSelectedCount');

  if (selectedUploadFiles.length > 0) {
    countText.innerHTML = `<strong>${selectedUploadFiles.length}</strong> file(s) ready to upload`;
    startBtn.disabled = false;
  } else {
    countText.innerText = 'No files selected';
    startBtn.disabled = true;
  }
}

// AJAX Form Upload
document.getElementById('mediaUploadForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  if (selectedUploadFiles.length === 0) return;

  const formData = new FormData();
  selectedUploadFiles.forEach(file => {
    formData.append('files[]', file);
  });

  const selectFolder = document.getElementById('uploadFolder').value;
  const customFolder = document.getElementById('customFolderInput').value.trim();
  const folder = selectFolder === 'custom' ? (customFolder || 'general') : selectFolder;
  formData.append('folder', folder);

  const progressContainer = document.getElementById('uploadProgressContainer');
  const progressBar = document.getElementById('uploadProgressBar');
  const progressPercent = document.getElementById('uploadProgressPercent');
  const progressText = document.getElementById('uploadProgressText');
  const startBtn = document.getElementById('startUploadBtn');

  progressContainer.classList.remove('hidden');
  startBtn.disabled = true;

  const xhr = new XMLHttpRequest();
  xhr.open('POST', BASE_URL + '/admin/media/upload', true);

  xhr.upload.onprogress = function(e) {
    if (e.lengthComputable) {
      const percentComplete = Math.round((e.loaded / e.total) * 100);
      progressBar.style.width = percentComplete + '%';
      progressPercent.innerText = percentComplete + '%';
      progressText.innerText = percentComplete < 100 ? 'Uploading assets...' : 'Processing media...';
    }
  };

  xhr.onload = function() {
    if (xhr.status === 200) {
      try {
        const res = JSON.parse(xhr.responseText);
        if (res.success) {
          showToast(res.message, 'success');
          // Reset form & reload gallery
          document.getElementById('mediaUploadForm').reset();
          selectedUploadFiles = [];
          document.getElementById('fileSelectedCount').innerText = 'No files selected';
          progressContainer.classList.add('hidden');
          toggleUploadZone();
          fetchMedia(1);
        } else {
          showToast(res.message || 'Upload error', 'error');
          if (res.errors && res.errors.length) {
            alert(res.errors.join('\n'));
          }
        }
      } catch (err) {
        showToast('Invalid response from server.', 'error');
      }
    } else {
      showToast('HTTP error ' + xhr.status, 'error');
    }
    startBtn.disabled = false;
  };

  xhr.onerror = function() {
    showToast('Network error during file upload.', 'error');
    startBtn.disabled = false;
  };

  xhr.send(formData);
});

// View mode switcher (Grid vs Table)
function switchView(mode) {
  currentViewMode = mode;
  const gridView = document.getElementById('gridView');
  const tableView = document.getElementById('tableView');
  const gridBtn = document.getElementById('viewGridBtn');
  const tableBtn = document.getElementById('viewTableBtn');

  if (mode === 'grid') {
    gridView.classList.remove('hidden');
    tableView.classList.add('hidden');
    gridBtn.className = 'p-1.5 rounded-lg text-cc-blue bg-white shadow-sm transition';
    tableBtn.className = 'p-1.5 rounded-lg text-slate-400 hover:text-slate-700 transition';
  } else {
    gridView.classList.add('hidden');
    tableView.classList.remove('hidden');
    tableBtn.className = 'p-1.5 rounded-lg text-cc-blue bg-white shadow-sm transition';
    gridBtn.className = 'p-1.5 rounded-lg text-slate-400 hover:text-slate-700 transition';
  }
}

// Debounced search
function debounceFetch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    fetchMedia(1);
  }, 350);
}

// Fetch Paginated Filtered Media via AJAX
function fetchMedia(page = 1) {
  const search = document.getElementById('mediaSearch').value.trim();
  const type = document.getElementById('typeFilter').value;
  const folder = document.getElementById('folderFilter').value;
  const sort = document.getElementById('sortFilter').value;

  const url = `${BASE_URL}/admin/media?ajax=1&page=${page}&type=${encodeURIComponent(type)}&folder=${encodeURIComponent(folder)}&sort=${encodeURIComponent(sort)}&search=${encodeURIComponent(search)}`;

  fetch(url)
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        renderMediaGallery(res.data);
        updatePaginationUI(res.pagination);
        if (res.kpis) {
          updateKpiUI(res.kpis);
        }
      }
    })
    .catch(err => console.error('Fetch media failed:', err));
}

// Render dynamic gallery HTML
function renderMediaGallery(items) {
  const gridView = document.getElementById('gridView');
  const tableBody = document.getElementById('tableBody');

  if (!items || items.length === 0) {
    gridView.innerHTML = `
      <div class="col-span-full py-16 text-center bg-white rounded-2xl border border-slate-200">
        <div class="w-16 h-16 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
          <span class="material-icons text-3xl">photo_library</span>
        </div>
        <h3 class="text-base font-bold text-slate-700">No media assets match your search</h3>
        <p class="text-xs text-slate-400 mt-1">Try clearing filters or search terms.</p>
      </div>`;
    tableBody.innerHTML = `<tr><td colspan="9" class="p-8 text-center text-slate-400 text-xs">No media assets found.</td></tr>`;
    return;
  }

  let gridHtml = '';
  let tableHtml = '';

  items.forEach(item => {
    const id = item.encrypted_id || item.id;
    const isChecked = selectedMediaIds.has(String(id)) ? 'checked' : '';
    const isImg = item.is_image;
    const ext = (item.file_extension || 'file').toUpperCase();
    const folder = item.folder || 'general';
    const dim = (item.width && item.height) ? `${item.width} × ${item.height} px` : '-';
    const icon = getFileIconJs(item.mime_type);

    // Grid HTML
    gridHtml += `
      <div class="media-card group relative bg-white rounded-2xl border border-slate-200/90 overflow-hidden hover:border-cc-blue/40 hover:shadow-lg transition-all duration-300 flex flex-col justify-between" data-id="${id}">
        <div class="absolute top-2 left-2 right-2 z-10 flex items-center justify-between pointer-events-none">
          <input type="checkbox" value="${id}" ${isChecked} onchange="handleItemSelect(this)" class="media-checkbox pointer-events-auto rounded border-slate-300 text-cc-blue focus:ring-cc-blue shadow-sm">
          <span class="px-2 py-0.5 text-[9px] font-bold tracking-wider rounded-full bg-slate-900/70 text-white backdrop-blur-sm shadow-sm">${ext}</span>
        </div>
        <div class="w-full aspect-square bg-slate-900/5 flex items-center justify-center relative overflow-hidden cursor-pointer" onclick="openDetailModal('${id}')">
          ${isImg 
            ? `<img src="${item.public_url}" alt="${item.original_name}" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" onerror="handleImageError(this, '${icon}', '${ext}')">`
            : `<div class="text-slate-400 group-hover:text-cc-blue transition-colors flex flex-col items-center gap-1 p-2">
                 <span class="material-icons text-4xl">${icon}</span>
                 <span class="text-[10px] font-semibold text-slate-500 truncate max-w-[90px]">${ext}</span>
               </div>`
          }
          <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center gap-2 p-2">
            <button onclick="event.stopPropagation(); copyToClipboard('${item.public_url}', 'Asset URL')" class="w-8 h-8 rounded-full bg-white text-slate-700 hover:text-cc-blue flex items-center justify-center shadow-md transition" title="Copy URL">
              <span class="material-icons text-[16px]">content_copy</span>
            </button>
            <button onclick="event.stopPropagation(); openDetailModal('${id}')" class="w-8 h-8 rounded-full bg-white text-slate-700 hover:text-cc-purple flex items-center justify-center shadow-md transition" title="Inspect Details">
              <span class="material-icons text-[16px]">visibility</span>
            </button>
          </div>
        </div>
        <div class="p-3 bg-white border-t border-slate-100">
          <h4 class="text-xs font-semibold text-slate-800 truncate" title="${item.original_name}">${item.original_name}</h4>
          <div class="flex items-center justify-between text-[10px] text-slate-400 mt-1">
            <span class="truncate max-w-[80px] bg-slate-100 px-1.5 py-0.5 rounded text-slate-500">${folder}</span>
            <span>${item.formatted_size}</span>
          </div>
        </div>
      </div>`;

    // Table HTML
    tableHtml += `
      <tr class="hover:bg-slate-50/80 transition media-table-row" data-id="${id}">
        <td class="p-3 pl-4">
          <input type="checkbox" value="${id}" ${isChecked} onchange="handleItemSelect(this)" class="media-checkbox rounded border-slate-300 text-cc-blue focus:ring-cc-blue">
        </td>
        <td class="p-3">
          <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center cursor-pointer" onclick="openDetailModal('${id}')">
            ${isImg ? `<img src="${item.public_url}" alt="${item.original_name}" class="w-full h-full object-cover" onerror="handleTableImageError(this, '${icon}')">` : `<span class="material-icons text-slate-400 text-xl">${icon}</span>`}
          </div>
        </td>
        <td class="p-3">
          <p class="font-semibold text-slate-800 text-xs truncate max-w-xs cursor-pointer hover:text-cc-blue" onclick="openDetailModal('${id}')">${item.original_name}</p>
          <span class="text-[10px] text-slate-400 font-mono">${item.filename}</span>
        </td>
        <td class="p-3">
          <span class="inline-block bg-slate-100 text-slate-600 text-[10px] font-semibold px-2 py-0.5 rounded-full">${folder}</span>
        </td>
        <td class="p-3 text-xs text-slate-500 font-mono">${item.mime_type}</td>
        <td class="p-3 text-xs text-slate-500">${dim}</td>
        <td class="p-3 text-xs font-semibold text-slate-700">${item.formatted_size}</td>
        <td class="p-3 text-xs text-slate-400">${item.created_at ? item.created_at.substring(0, 10) : '-'}</td>
        <td class="p-3 text-right pr-4">
          <div class="flex items-center justify-end gap-1">
            <button onclick="copyToClipboard('${item.public_url}', 'Asset URL')" class="p-1.5 text-slate-400 hover:text-cc-blue rounded-lg hover:bg-slate-100 transition" title="Copy Link">
              <span class="material-icons text-[18px]">content_copy</span>
            </button>
            <button onclick="openDetailModal('${id}')" class="p-1.5 text-slate-400 hover:text-cc-purple rounded-lg hover:bg-slate-100 transition" title="Edit Metadata">
              <span class="material-icons text-[18px]">edit</span>
            </button>
            <button onclick="deleteSingleMedia('${id}')" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Delete">
              <span class="material-icons text-[18px]">delete</span>
            </button>
          </div>
        </td>
      </tr>`;
  });

  gridView.innerHTML = gridHtml;
  tableBody.innerHTML = tableHtml;
}

function getFileIconJs(mime) {
  if (!mime) return 'insert_drive_file';
  if (mime.includes('pdf')) return 'picture_as_pdf';
  if (mime.includes('word') || mime.includes('document')) return 'article';
  if (mime.includes('sheet') || mime.includes('excel') || mime.includes('csv')) return 'grid_on';
  if (mime.includes('zip') || mime.includes('compressed')) return 'folder_zip';
  if (mime.includes('video')) return 'movie';
  if (mime.includes('audio')) return 'audiotrack';
  return 'insert_drive_file';
}

function updatePaginationUI(p) {
  const info = document.getElementById('paginationInfo');
  const btnContainer = document.getElementById('paginationButtons');

  info.innerHTML = `Showing page <strong>${p.currentPage}</strong> of <strong>${p.totalPages}</strong> (${p.totalCount.toLocaleString()} assets total)`;

  const prevDisabled = p.currentPage <= 1 ? 'disabled' : '';
  const nextDisabled = p.currentPage >= p.totalPages ? 'disabled' : '';
  const prevPage = Math.max(1, p.currentPage - 1);
  const nextPage = Math.min(p.totalPages, p.currentPage + 1);

  btnContainer.innerHTML = `
    <button onclick="fetchMedia(${prevPage})" class="btn-secondary text-xs px-3 py-1.5" ${prevDisabled}>
      <span class="material-icons text-[16px]">chevron_left</span> Prev
    </button>
    <span class="text-xs font-semibold text-slate-700 px-2">${p.currentPage} / ${p.totalPages}</span>
    <button onclick="fetchMedia(${nextPage})" class="btn-secondary text-xs px-3 py-1.5" ${nextDisabled}>
      Next <span class="material-icons text-[16px]">chevron_right</span>
    </button>`;
}

// Checkbox selection & bulk actions
function handleItemSelect(cb) {
  if (cb.checked) {
    selectedMediaIds.add(cb.value);
  } else {
    selectedMediaIds.delete(cb.value);
  }
  updateBulkBar();
}

function toggleSelectAll(masterCb) {
  const checkboxes = document.querySelectorAll('.media-checkbox');
  checkboxes.forEach(cb => {
    cb.checked = masterCb.checked;
    if (masterCb.checked) {
      selectedMediaIds.add(cb.value);
    } else {
      selectedMediaIds.delete(cb.value);
    }
  });
  updateBulkBar();
}

function clearSelection() {
  selectedMediaIds.clear();
  document.querySelectorAll('.media-checkbox').forEach(cb => cb.checked = false);
  const master = document.getElementById('selectAllHeader');
  if (master) master.checked = false;
  updateBulkBar();
}

function updateBulkBar() {
  const bar = document.getElementById('bulkBar');
  const countEl = document.getElementById('selectedCount');
  countEl.innerText = selectedMediaIds.size;

  if (selectedMediaIds.size > 0) {
    bar.classList.remove('hidden');
  } else {
    bar.classList.add('hidden');
  }
}

function confirmBulkDelete() {
  if (selectedMediaIds.size === 0) return;

  if (!confirm(`Are you sure you want to permanently delete ${selectedMediaIds.size} media asset(s)? This action cannot be undone.`)) {
    return;
  }

  const formData = new FormData();
  selectedMediaIds.forEach(id => formData.append('ids[]', id));

  fetch(BASE_URL + '/admin/media/bulk-delete', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      showToast(res.message, 'success');
      clearSelection();
      fetchMedia(1);
    } else {
      showToast(res.message || 'Bulk delete failed', 'error');
    }
  })
  .catch(err => showToast('Network request failed', 'error'));
}

// Single item delete
function deleteSingleMedia(id) {
  if (!confirm('Are you sure you want to delete this media file?')) return;

  const formData = new FormData();
  formData.append('id', id);

  fetch(BASE_URL + '/admin/media/delete', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      showToast(res.message, 'success');
      if (currentDetailId === id) closeDetailModal();
      fetchMedia(1);
    } else {
      showToast(res.message || 'Delete failed', 'error');
    }
  })
  .catch(err => showToast('Network request failed', 'error'));
}

function deleteSingleFromModal() {
  if (currentDetailId) {
    deleteSingleMedia(currentDetailId);
  }
}

// Open Detail Modal
function openDetailModal(id) {
  currentDetailId = id;
  fetch(`${BASE_URL}/admin/media/detail/${id}`)
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        const d = res.data;
        document.getElementById('modalMediaId').value = d.encrypted_id || d.id;
        document.getElementById('modalOriginalName').value = d.original_name;
        document.getElementById('modalAltText').value = d.alt_text || '';
        document.getElementById('modalFolder').value = d.folder || 'general';
        document.getElementById('modalPublicUrl').value = d.public_url;
        document.getElementById('modalSize').innerText = d.formatted_size;
        document.getElementById('modalDimensions').innerText = (d.width && d.height) ? `${d.width} × ${d.height} px` : '-';
        document.getElementById('modalMime').innerText = d.mime_type;
        document.getElementById('modalDate').innerText = d.created_at ? d.created_at.substring(0, 10) : '-';
        document.getElementById('modalDownloadBtn').href = d.public_url;

        // Render Preview Box
        const previewBox = document.getElementById('modalPreviewContainer');
        if (d.is_image) {
          previewBox.innerHTML = `<img src="${d.public_url}" alt="${d.original_name}" class="max-h-[360px] w-auto max-w-full object-contain rounded-lg shadow-lg" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'text-center text-white/70 p-6\\'><span class=\\'material-icons text-6xl text-white/50 mb-2\\'>broken_image</span><p class=\\'font-bold text-sm text-white\\'>Preview unavailable</p></div>'">`;
        } else if (d.mime_type.includes('video')) {
          previewBox.innerHTML = `<video src="${d.public_url}" controls class="max-h-[360px] w-full rounded-lg shadow-lg"></video>`;
        } else {
          previewBox.innerHTML = `
            <div class="text-center text-white/70 p-6">
              <span class="material-icons text-6xl text-white/50 mb-2">${getFileIconJs(d.mime_type)}</span>
              <p class="font-bold text-sm text-white">${d.original_name}</p>
              <p class="text-xs text-white/50 mt-1">${d.mime_type}</p>
            </div>`;
        }

        document.getElementById('detailModal').classList.remove('hidden');
      } else {
        showToast(res.message || 'Asset not found', 'error');
      }
    })
    .catch(err => showToast('Failed to load asset details', 'error'));
}

function closeDetailModal() {
  document.getElementById('detailModal').classList.add('hidden');
  currentDetailId = null;
}

// Edit Form submit
function handleEditSubmit(e) {
  e.preventDefault();
  const form = document.getElementById('mediaEditForm');
  const formData = new FormData(form);

  fetch(BASE_URL + '/admin/media/update', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      showToast(res.message, 'success');
      closeDetailModal();
      fetchMedia(1);
    } else {
      showToast(res.message || 'Update failed', 'error');
    }
  })
  .catch(err => showToast('Failed to save media metadata', 'error'));
}

// Utility: Copy to Clipboard
function copyToClipboard(text, label = 'Content') {
  if (!navigator.clipboard) {
    const input = document.createElement('textarea');
    input.value = text;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    showToast(`${label} copied to clipboard!`, 'info');
    return;
  }

  navigator.clipboard.writeText(text).then(() => {
    showToast(`${label} copied to clipboard!`, 'info');
  }).catch(() => {
    showToast('Failed to copy text', 'error');
  });
}

// Toast notification helper
function showToast(message, type = 'info') {
  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  
  let bgClass = 'bg-slate-900 text-white';
  let icon = 'info';

  if (type === 'success') {
    bgClass = 'bg-emerald-600 text-white';
    icon = 'check_circle';
  } else if (type === 'error') {
    bgClass = 'bg-rose-600 text-white';
    icon = 'error';
  }

  toast.className = `${bgClass} text-xs font-semibold px-4 py-3 rounded-xl shadow-2xl flex items-center gap-2 transform transition-all duration-300 translate-y-4 opacity-0 pointer-events-auto`;
  toast.innerHTML = `<span class="material-icons text-[18px]">${icon}</span><span>${message}</span>`;

  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.remove('translate-y-4', 'opacity-0');
  }, 10);

  setTimeout(() => {
    toast.classList.add('opacity-0', 'translate-y-2');
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

// KPI live updater
function updateKpiUI(kpis) {
  if (!kpis) return;
  const f = document.getElementById('kpiTotalFiles');
  const img = document.getElementById('kpiTotalImages');
  const doc = document.getElementById('kpiTotalDocuments');
  const st = document.getElementById('kpiTotalStorage');
  if (f) f.innerText = Number(kpis.total_files || 0).toLocaleString();
  if (img) img.innerText = Number(kpis.total_images || 0).toLocaleString();
  if (doc) doc.innerText = Number(kpis.total_documents || 0).toLocaleString();
  if (st) st.innerText = kpis.formatted_total_size || '0 B';
}

// Image load error fallback handlers
function handleImageError(img, icon = 'photo', ext = 'IMG') {
  img.onerror = null;
  const parent = img.parentElement;
  if (!parent) return;
  img.remove();
  const fallback = document.createElement('div');
  fallback.className = 'text-slate-400 group-hover:text-cc-blue transition-colors flex flex-col items-center justify-center gap-1 p-2 w-full h-full bg-slate-100/90';
  fallback.innerHTML = `<span class="material-icons text-4xl text-slate-400">${icon || 'image'}</span><span class="text-[10px] font-semibold text-slate-500 truncate max-w-[90px] uppercase">${ext || 'FILE'}</span>`;
  parent.prepend(fallback);
}

function handleTableImageError(img, icon = 'photo') {
  img.onerror = null;
  const parent = img.parentElement;
  if (!parent) return;
  img.remove();
  const fallback = document.createElement('span');
  fallback.className = 'material-icons text-slate-400 text-xl';
  fallback.innerText = icon || 'image';
  parent.appendChild(fallback);
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
