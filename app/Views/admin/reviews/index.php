<?php
$pageTitle = 'Reviews & Ratings';
$activeMenu = 'reviews';

require_once __DIR__ . '/../layouts/header.php';

$reviews = $reviews ?? [];
$pagination = $pagination ?? ['current_page' => 1, 'total_pages' => 1, 'total' => 0, 'per_page' => 15];
$kpis = $kpis ?? [];

$ratingDist = $kpis['rating_distribution'] ?? [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$totalReviews = (int)($kpis['total_reviews'] ?? 0);
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">Reviews & Ratings</h1>
      <p class="text-slate-500 text-sm mt-1">Moderate customer feedback, ratings, and review images</p>
    </div>
    <div class="flex items-center gap-2">
      <div id="bulkActionsBar" class="hidden flex items-center gap-2">
        <span class="text-xs font-semibold text-slate-600"><span id="selectedCount">0</span> selected</span>
        <button onclick="bulkAction('approve_all')" class="btn-primary text-xs py-1.5 px-3 bg-emerald-500 hover:bg-emerald-600 shadow-none">
          <span class="material-icons text-[14px]">check_circle</span> Approve All
        </button>
        <button onclick="bulkAction('reject_all')" class="btn-primary text-xs py-1.5 px-3 bg-cc-pink hover:bg-pink-700 shadow-none">
          <span class="material-icons text-[14px]">block</span> Reject All
        </button>
        <button onclick="bulkAction('delete_all')" class="btn-danger text-xs py-1.5 px-3">
          <span class="material-icons text-[14px]">delete_outline</span> Delete
        </button>
      </div>
    </div>
  </div>

  <!-- KPI Stats Cards -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <!-- Total Reviews -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 rounded-xl bg-cc-blue/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-blue text-[22px]">reviews</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Reviews</p>
          <p class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($kpis['total_reviews'] ?? 0) ?></p>
        </div>
      </div>
      <div class="flex items-center gap-2 text-xs text-slate-500">
        <span class="material-icons text-emerald-500 text-[14px]">verified</span>
        <span><strong class="text-slate-800"><?= number_format($kpis['verified_count'] ?? 0) ?></strong> verified purchases</span>
      </div>
    </div>

    <!-- Average Rating -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 rounded-xl bg-cc-yellow/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-yellow text-[22px]">star</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Average Rating</p>
          <p class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($kpis['avg_rating'] ?? 0, 1) ?> <span class="text-sm text-slate-400 font-normal">/ 5</span></p>
        </div>
      </div>
      <div class="flex items-center gap-0.5">
        <?php for ($s = 1; $s <= 5; $s++): ?>
          <span class="material-icons text-[16px] <?= $s <= round($kpis['avg_rating'] ?? 0) ? 'text-cc-yellow' : 'text-slate-200' ?>">star</span>
        <?php endfor; ?>
      </div>
    </div>

    <!-- Pending Moderation -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-3">
        <div class="w-10 h-10 rounded-xl bg-cc-orange/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-orange text-[22px]">pending_actions</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Pending Queue</p>
          <p class="text-xl font-extrabold text-slate-900 leading-tight"><?= number_format($kpis['pending_count'] ?? 0) ?></p>
        </div>
      </div>
      <div class="text-xs text-slate-500">
        Requires moderation review
      </div>
    </div>

    <!-- Rating Distribution -->
    <div class="kpi-card p-4 sm:p-5">
      <div class="flex items-center gap-3 mb-2.5">
        <div class="w-10 h-10 rounded-xl bg-cc-purple/10 flex items-center justify-center shrink-0">
          <span class="material-icons text-cc-purple text-[22px]">equalizer</span>
        </div>
        <div class="min-w-0">
          <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Distribution</p>
        </div>
      </div>
      <div class="space-y-1">
        <?php for ($star = 5; $star >= 1; $star--): ?>
          <?php $pct = $totalReviews > 0 ? round(($ratingDist[$star] / $totalReviews) * 100) : 0; ?>
          <div class="flex items-center gap-2 text-[10px]">
            <span class="w-2 text-right font-bold text-slate-500"><?= $star ?></span>
            <span class="material-icons text-cc-yellow text-[11px]">star</span>
            <div class="flex-1 h-1.5 bg-slate-100 rounded-full overflow-hidden">
              <div class="h-full bg-cc-yellow rounded-full" style="width: <?= $pct ?>%"></div>
            </div>
            <span class="w-6 text-right text-slate-400 mono"><?= $ratingDist[$star] ?></span>
          </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>

  <!-- Filters & Search Bar -->
  <div class="section-card p-4">
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
      <div class="flex items-center bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 gap-2 flex-1 w-full sm:max-w-xs focus-within:border-cc-blue focus-within:ring-2 focus-within:ring-cc-blue/10 transition">
        <span class="material-icons text-slate-400 text-[18px]">search</span>
        <input type="text" id="reviewSearch" placeholder="Search reviews, users, products..." class="bg-transparent text-sm text-slate-700 w-full focus:outline-none placeholder:text-slate-400" oninput="debounceFetch()">
      </div>
      <div class="flex items-center gap-2 flex-wrap">
        <select id="statusFilter" class="input-field text-xs w-auto py-2" onchange="fetchReviews(1)">
          <option value="">All Status</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
        <select id="ratingFilter" class="input-field text-xs w-auto py-2" onchange="fetchReviews(1)">
          <option value="">All Ratings</option>
          <option value="5">5 Stars</option>
          <option value="4">4 Stars</option>
          <option value="3">3 Stars</option>
          <option value="2">2 Stars</option>
          <option value="1">1 Star</option>
        </select>
        <select id="verifiedFilter" class="input-field text-xs w-auto py-2" onchange="fetchReviews(1)">
          <option value="">All Purchases</option>
          <option value="1">Verified Only</option>
          <option value="0">Unverified Only</option>
        </select>
        <select id="sortFilter" class="input-field text-xs w-auto py-2" onchange="fetchReviews(1)">
          <option value="r.created_at|DESC">Newest First</option>
          <option value="r.created_at|ASC">Oldest First</option>
          <option value="r.rating|DESC">Highest Rating</option>
          <option value="r.rating|ASC">Lowest Rating</option>
          <option value="r.helpful_count|DESC">Most Helpful</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Reviews Data Table -->
  <div class="section-card overflow-hidden">
    <div class="overflow-x-auto">
      <table class="data-table">
        <thead>
          <tr>
            <th style="width:36px"><input type="checkbox" class="row-check" id="selectAllReviews" onchange="toggleSelectAll(this)"></th>
            <th>Review</th>
            <th>Product</th>
            <th>Rating</th>
            <th>Status</th>
            <th>Helpful</th>
            <th>Date</th>
            <th style="width: 120px">Actions</th>
          </tr>
        </thead>
        <tbody id="reviewsBody">
          <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $r): ?>
              <?= renderReviewRow($r) ?>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center py-12">
              <span class="material-icons text-slate-300 text-[40px]">rate_review</span>
              <p class="text-slate-500 mt-2 font-medium">No reviews found</p>
            </td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination Bar -->
    <div class="px-5 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
      <p class="text-xs text-slate-500">
        Showing <strong class="text-slate-800"><?= count($reviews) ?></strong> of <strong class="text-slate-800"><?= number_format($pagination['total']) ?></strong> reviews
      </p>
      <div class="flex items-center gap-1" id="paginationBar">
        <?php renderPaginationBar($pagination); ?>
      </div>
    </div>
  </div>
</div>

<!-- ========== REVIEW DETAIL MODAL ========== -->
<div class="modal-overlay" id="reviewDetailModal">
  <div class="modal-box max-w-2xl mx-auto">
    <div class="p-5 border-b border-slate-200 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-cc-blue/10 flex items-center justify-center text-cc-blue">
          <span class="material-icons text-[22px]">rate_review</span>
        </div>
        <div>
          <h3 class="font-heading text-lg font-bold text-slate-900">Review Inspector</h3>
          <p class="text-xs text-slate-400" id="detailReviewId">—</p>
        </div>
      </div>
      <button onclick="closeModal('reviewDetailModal')" class="p-2 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
        <span class="material-icons">close</span>
      </button>
    </div>
    <div class="p-5 space-y-5" id="reviewDetailContent">
      <div class="text-center py-10 text-slate-400">
        <span class="material-icons animate-spin text-[24px]">autorenew</span>
        <p class="text-xs mt-2">Loading review detail...</p>
      </div>
    </div>
  </div>
</div>

<?php
// PHP helper to render a review row
function renderReviewRow(array $r): string {
    $statusBadge = match($r['status']) {
        'approved' => 'badge-green',
        'rejected' => 'badge-red',
        default => 'badge-yellow',
    };
    $statusLabel = ucfirst($r['status']);
    $isVerified = (int)$r['is_verified_purchase'] === 1;
    $avatar = !empty($r['user_avatar'])
        ? '<img src="'.htmlspecialchars($r['user_avatar']).'" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">'
        : '<div class="w-8 h-8 rounded-full bg-cc-purple/10 flex items-center justify-center text-cc-purple font-bold text-xs shrink-0">'.strtoupper(substr($r['user_name'] ?? 'U', 0, 1)).'</div>';

    $stars = '';
    for ($s = 1; $s <= 5; $s++) {
        $stars .= '<span class="material-icons text-[14px] '.($s <= (int)$r['rating'] ? 'text-cc-yellow' : 'text-slate-200').'">star</span>';
    }

    $imgIcon = ((int)($r['image_count'] ?? 0)) > 0
        ? '<span class="material-icons text-[13px] text-cc-blue ml-1" title="Has images">photo_camera</span>'
        : '';

    $date = date('d M Y', strtotime($r['created_at']));
    $time = date('h:i A', strtotime($r['created_at']));

    $eid = htmlspecialchars($r['encrypted_id']);

    return "
      <tr id=\"review-row-{$eid}\">
        <td><input type=\"checkbox\" class=\"row-check review-check\" data-id=\"{$eid}\" onchange=\"updateBulkBar()\"></td>
        <td>
          <div class=\"flex items-center gap-2.5\">
            {$avatar}
            <div class=\"min-w-0\">
              <p class=\"text-sm font-semibold text-slate-800 truncate flex items-center gap-1\">
                ".htmlspecialchars($r['user_name'] ?? 'Unknown')."
                ".($isVerified ? '<span class="material-icons text-cc-blue text-[14px]" title="Verified Purchase">verified</span>' : '')."
              </p>
              <p class=\"text-[11px] text-slate-500 truncate max-w-[220px] flex items-center\">
                ".htmlspecialchars($r['title'] ?? 'No title')."{$imgIcon}
              </p>
            </div>
          </div>
        </td>
        <td>
          <p class=\"text-xs font-medium text-slate-700 truncate max-w-[160px]\">".htmlspecialchars($r['product_name'] ?? '—')."</p>
        </td>
        <td>
          <div class=\"flex items-center gap-0.5\">{$stars}</div>
        </td>
        <td>
          <span class=\"badge {$statusBadge} text-[10px]\">{$statusLabel}</span>
        </td>
        <td>
          <span class=\"text-xs font-semibold text-slate-700 mono\">{$r['helpful_count']}</span>
        </td>
        <td>
          <p class=\"text-xs text-slate-600\">{$date}</p>
          <p class=\"text-[10px] text-slate-400 mono\">{$time}</p>
        </td>
        <td>
          <div class=\"flex items-center gap-1\">
            <button onclick=\"viewReviewDetail('{$eid}')\" class=\"action-btn\" title=\"View Detail\">
              <span class=\"material-icons text-[18px]\">visibility</span>
            </button>
            <button onclick=\"quickStatus('{$eid}', 'approved')\" class=\"action-btn\" title=\"Approve\">
              <span class=\"material-icons text-[18px] text-emerald-500\">check_circle</span>
            </button>
            <button onclick=\"quickStatus('{$eid}', 'rejected')\" class=\"action-btn\" title=\"Reject\">
              <span class=\"material-icons text-[18px] text-cc-orange\">block</span>
            </button>
            <button onclick=\"deleteReview('{$eid}')\" class=\"action-btn danger\" title=\"Delete\">
              <span class=\"material-icons text-[18px]\">delete_outline</span>
            </button>
          </div>
        </td>
      </tr>
    ";
}

function renderPaginationBar(array $p): void {
    $cur = $p['current_page'];
    $total = $p['total_pages'];
    if ($total <= 1) return;

    $prevDisabled = $cur <= 1 ? 'opacity-40 pointer-events-none' : 'hover:bg-slate-200';
    echo "<button onclick=\"fetchReviews(".($cur-1).")\" class=\"w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center text-xs {$prevDisabled} transition\"><span class=\"material-icons text-[16px]\">chevron_left</span></button>";

    $start = max(1, $cur - 2);
    $end = min($total, $cur + 2);
    for ($i = $start; $i <= $end; $i++) {
        $isActive = ($i === $cur) ? 'bg-cc-blue text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200';
        echo "<button onclick=\"fetchReviews({$i})\" class=\"w-8 h-8 rounded-lg {$isActive} text-xs font-semibold flex items-center justify-center transition\">{$i}</button>";
    }

    $nextDisabled = $cur >= $total ? 'opacity-40 pointer-events-none' : 'hover:bg-slate-200';
    echo "<button onclick=\"fetchReviews(".($cur+1).")\" class=\"w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center text-xs {$nextDisabled} transition\"><span class=\"material-icons text-[16px]\">chevron_right</span></button>";
}
?>

<script>
var BASE_URL = window.BASE_URL || '<?= $baseUrl ?>';
let searchTimeout = null;

function debounceFetch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => fetchReviews(1), 300);
}

async function fetchReviews(page = 1) {
  const status = document.getElementById('statusFilter').value;
  const rating = document.getElementById('ratingFilter').value;
  const verified = document.getElementById('verifiedFilter').value;
  const search = document.getElementById('reviewSearch').value;
  const sortVal = document.getElementById('sortFilter').value.split('|');

  const url = `${BASE_URL}/admin/reviews?ajax=1&page=${page}&status=${encodeURIComponent(status)}&rating=${encodeURIComponent(rating)}&verified=${encodeURIComponent(verified)}&search=${encodeURIComponent(search)}&sort=${encodeURIComponent(sortVal[0])}&dir=${encodeURIComponent(sortVal[1] || 'DESC')}`;

  try {
    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
    const data = await res.json();
    if (data.success) {
      renderReviewsTable(data.data);
      renderPagination(data.pagination);
    }
  } catch(e) {
    showToast('error', 'Error fetching reviews');
  }
}

function renderReviewsTable(reviews) {
  const tbody = document.getElementById('reviewsBody');
  if (!reviews || reviews.length === 0) {
    tbody.innerHTML = `<tr><td colspan="8" class="text-center py-12"><span class="material-icons text-slate-300 text-[40px]">rate_review</span><p class="text-slate-500 mt-2 font-medium">No reviews found</p></td></tr>`;
    return;
  }

  let html = '';
  reviews.forEach(r => {
    const statusBadge = { approved: 'badge-green', rejected: 'badge-red', pending: 'badge-yellow' }[r.status] || 'badge-yellow';
    const isVerified = parseInt(r.is_verified_purchase) === 1;
    const initials = (r.user_name || 'U').charAt(0).toUpperCase();
    const avatar = r.user_avatar
      ? `<img src="${escapeHtml(r.user_avatar)}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">`
      : `<div class="w-8 h-8 rounded-full bg-cc-purple/10 flex items-center justify-center text-cc-purple font-bold text-xs shrink-0">${initials}</div>`;

    let stars = '';
    for (let s = 1; s <= 5; s++) {
      stars += `<span class="material-icons text-[14px] ${s <= parseInt(r.rating) ? 'text-cc-yellow' : 'text-slate-200'}">star</span>`;
    }

    const imgIcon = parseInt(r.image_count) > 0 ? '<span class="material-icons text-[13px] text-cc-blue ml-1" title="Has images">photo_camera</span>' : '';
    const date = new Date(r.created_at);
    const dateStr = date.toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    const timeStr = date.toLocaleTimeString('en-IN', { hour: '2-digit', minute: '2-digit', hour12: true });

    html += `
      <tr id="review-row-${r.encrypted_id}">
        <td><input type="checkbox" class="row-check review-check" data-id="${r.encrypted_id}" onchange="updateBulkBar()"></td>
        <td>
          <div class="flex items-center gap-2.5">
            ${avatar}
            <div class="min-w-0">
              <p class="text-sm font-semibold text-slate-800 truncate flex items-center gap-1">
                ${escapeHtml(r.user_name || 'Unknown')}
                ${isVerified ? '<span class="material-icons text-cc-blue text-[14px]" title="Verified Purchase">verified</span>' : ''}
              </p>
              <p class="text-[11px] text-slate-500 truncate max-w-[220px] flex items-center">
                ${escapeHtml(r.title || 'No title')}${imgIcon}
              </p>
            </div>
          </div>
        </td>
        <td><p class="text-xs font-medium text-slate-700 truncate max-w-[160px]">${escapeHtml(r.product_name || '—')}</p></td>
        <td><div class="flex items-center gap-0.5">${stars}</div></td>
        <td><span class="badge ${statusBadge} text-[10px]">${r.status.charAt(0).toUpperCase() + r.status.slice(1)}</span></td>
        <td><span class="text-xs font-semibold text-slate-700 mono">${r.helpful_count}</span></td>
        <td>
          <p class="text-xs text-slate-600">${dateStr}</p>
          <p class="text-[10px] text-slate-400 mono">${timeStr}</p>
        </td>
        <td>
          <div class="flex items-center gap-1">
            <button onclick="viewReviewDetail('${r.encrypted_id}')" class="action-btn" title="View Detail"><span class="material-icons text-[18px]">visibility</span></button>
            <button onclick="quickStatus('${r.encrypted_id}', 'approved')" class="action-btn" title="Approve"><span class="material-icons text-[18px] text-emerald-500">check_circle</span></button>
            <button onclick="quickStatus('${r.encrypted_id}', 'rejected')" class="action-btn" title="Reject"><span class="material-icons text-[18px] text-cc-orange">block</span></button>
            <button onclick="deleteReview('${r.encrypted_id}')" class="action-btn danger" title="Delete"><span class="material-icons text-[18px]">delete_outline</span></button>
          </div>
        </td>
      </tr>
    `;
  });
  tbody.innerHTML = html;
}

function renderPagination(p) {
  const bar = document.getElementById('paginationBar');
  if (!bar || p.total_pages <= 1) { if (bar) bar.innerHTML = ''; return; }

  let html = '';
  const prev = p.current_page > 1 ? '' : 'opacity-40 pointer-events-none';
  html += `<button onclick="fetchReviews(${p.current_page - 1})" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center text-xs ${prev} transition"><span class="material-icons text-[16px]">chevron_left</span></button>`;

  const start = Math.max(1, p.current_page - 2);
  const end = Math.min(p.total_pages, p.current_page + 2);
  for (let i = start; i <= end; i++) {
    const active = i === p.current_page ? 'bg-cc-blue text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200';
    html += `<button onclick="fetchReviews(${i})" class="w-8 h-8 rounded-lg ${active} text-xs font-semibold flex items-center justify-center transition">${i}</button>`;
  }

  const next = p.current_page >= p.total_pages ? 'opacity-40 pointer-events-none' : '';
  html += `<button onclick="fetchReviews(${p.current_page + 1})" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center text-xs ${next} transition"><span class="material-icons text-[16px]">chevron_right</span></button>`;
  bar.innerHTML = html;

  // Update count text
  const countEl = bar.parentElement.querySelector('p');
  if (countEl) {
    const shown = Math.min(p.per_page, p.total - (p.current_page - 1) * p.per_page);
    countEl.innerHTML = `Showing <strong class="text-slate-800">${shown}</strong> of <strong class="text-slate-800">${p.total.toLocaleString()}</strong> reviews`;
  }
}

async function viewReviewDetail(encryptedId) {
  document.getElementById('reviewDetailModal').classList.add('show');
  document.getElementById('detailReviewId').textContent = 'Loading...';
  document.getElementById('reviewDetailContent').innerHTML = '<div class="text-center py-10 text-slate-400"><span class="material-icons animate-spin text-[24px]">autorenew</span><p class="text-xs mt-2">Loading review detail...</p></div>';

  try {
    const res = await fetch(`${BASE_URL}/admin/reviews/detail/${encryptedId}`);
    const data = await res.json();
    if (data.success && data.review) {
      renderReviewDetail(data.review);
    } else {
      document.getElementById('reviewDetailContent').innerHTML = '<p class="text-center text-red-500 py-10">Failed to load review</p>';
    }
  } catch(e) {
    document.getElementById('reviewDetailContent').innerHTML = '<p class="text-center text-red-500 py-10">Network error</p>';
  }
}

function renderReviewDetail(r) {
  document.getElementById('detailReviewId').textContent = `Review #${r.id} — ${r.status.toUpperCase()}`;

  const isVerified = parseInt(r.is_verified_purchase) === 1;
  const initials = (r.user_name || 'U').charAt(0).toUpperCase();

  let stars = '';
  for (let s = 1; s <= 5; s++) {
    stars += `<span class="material-icons text-[18px] ${s <= parseInt(r.rating) ? 'text-cc-yellow' : 'text-slate-200'}">star</span>`;
  }

  let imagesHtml = '';
  if (r.images && r.images.length > 0) {
    imagesHtml = `
      <div class="mt-4">
        <p class="text-xs font-bold text-slate-500 uppercase mb-2">Review Images (${r.images.length})</p>
        <div class="flex gap-2 flex-wrap">
          ${r.images.map(img => `<img src="${escapeHtml(img.image_url)}" class="w-20 h-20 rounded-xl object-cover border border-slate-200 hover:scale-105 transition cursor-pointer" onclick="window.open('${escapeHtml(img.image_url)}', '_blank')">`).join('')}
        </div>
      </div>
    `;
  }

  const statusBadge = { approved: 'badge-green', rejected: 'badge-red', pending: 'badge-yellow' }[r.status] || 'badge-yellow';

  document.getElementById('reviewDetailContent').innerHTML = `
    <!-- User & Product Info -->
    <div class="flex items-start gap-4 p-4 bg-slate-50 rounded-xl border border-slate-200">
      <div class="w-11 h-11 rounded-full bg-cc-purple/10 flex items-center justify-center text-cc-purple font-bold text-sm shrink-0">${initials}</div>
      <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2 mb-1">
          <p class="text-sm font-bold text-slate-900">${escapeHtml(r.user_name || 'Unknown')}</p>
          ${isVerified ? '<span class="badge badge-green text-[9px]"><span class="material-icons text-[11px]">verified</span> Verified Purchase</span>' : '<span class="badge badge-slate text-[9px]">Unverified</span>'}
          <span class="badge ${statusBadge} text-[9px]">${r.status.toUpperCase()}</span>
        </div>
        <p class="text-xs text-slate-500">${escapeHtml(r.user_email || '')}</p>
        <p class="text-[11px] text-slate-400 mt-1">Product: <strong class="text-slate-700">${escapeHtml(r.product_name || '—')}</strong></p>
      </div>
    </div>

    <!-- Rating & Title -->
    <div>
      <div class="flex items-center gap-2 mb-2">
        <div class="flex items-center gap-0.5">${stars}</div>
        <span class="text-xs text-slate-400 mono">${r.rating}/5</span>
        <span class="text-xs text-slate-400">•</span>
        <span class="text-xs text-slate-400">${r.helpful_count} helpful votes</span>
      </div>
      <h4 class="text-base font-bold text-slate-900">${escapeHtml(r.title || 'No title')}</h4>
    </div>

    <!-- Review Body -->
    <div class="p-4 bg-slate-50 border border-slate-200 rounded-xl">
      <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">${escapeHtml(r.body || 'No review content provided.')}</p>
    </div>

    ${imagesHtml}

    <!-- Metadata -->
    <div class="grid grid-cols-2 gap-3 text-xs">
      <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
        <p class="font-bold text-slate-400 uppercase text-[10px] mb-0.5">Created At</p>
        <p class="text-slate-700 mono">${new Date(r.created_at).toLocaleString('en-IN')}</p>
      </div>
      <div class="p-3 bg-slate-50 rounded-lg border border-slate-100">
        <p class="font-bold text-slate-400 uppercase text-[10px] mb-0.5">Last Updated</p>
        <p class="text-slate-700 mono">${new Date(r.updated_at).toLocaleString('en-IN')}</p>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex gap-2 pt-2 border-t border-slate-200">
      <button onclick="quickStatus('${r.encrypted_id}', 'approved'); closeModal('reviewDetailModal');" class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2.5 rounded-xl text-sm transition flex items-center justify-center gap-1.5">
        <span class="material-icons text-[16px]">check_circle</span> Approve
      </button>
      <button onclick="quickStatus('${r.encrypted_id}', 'rejected'); closeModal('reviewDetailModal');" class="flex-1 bg-cc-orange hover:bg-orange-600 text-white font-bold py-2.5 rounded-xl text-sm transition flex items-center justify-center gap-1.5">
        <span class="material-icons text-[16px]">block</span> Reject
      </button>
      <button onclick="deleteReview('${r.encrypted_id}'); closeModal('reviewDetailModal');" class="flex-1 bg-slate-100 hover:bg-red-50 text-slate-600 hover:text-red-600 font-bold py-2.5 rounded-xl text-sm transition flex items-center justify-center gap-1.5 border border-slate-200">
        <span class="material-icons text-[16px]">delete_outline</span> Delete
      </button>
    </div>
  `;
}

function closeModal(id) {
  document.getElementById(id).classList.remove('show');
}

async function quickStatus(encryptedId, status) {
  try {
    const res = await fetch(`${BASE_URL}/admin/reviews/update-status`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ review_id: encryptedId, status: status })
    });
    const data = await res.json();
    if (data.success) {
      showToast('success', data.message || `Review ${status} successfully`);
      fetchReviews(1);
    } else {
      showToast('error', data.message || 'Failed to update status');
    }
  } catch(e) {
    showToast('error', 'Network error updating review status');
  }
}

async function deleteReview(encryptedId) {
  if (!confirm('Are you sure you want to permanently delete this review?')) return;

  try {
    const res = await fetch(`${BASE_URL}/admin/reviews/delete`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ review_id: encryptedId })
    });
    const data = await res.json();
    if (data.success) {
      showToast('success', 'Review deleted successfully');
      fetchReviews(1);
    } else {
      showToast('error', data.message || 'Failed to delete review');
    }
  } catch(e) {
    showToast('error', 'Network error while deleting review');
  }
}

function toggleSelectAll(master) {
  document.querySelectorAll('.review-check').forEach(cb => {
    cb.checked = master.checked;
  });
  updateBulkBar();
}

function updateBulkBar() {
  const checked = document.querySelectorAll('.review-check:checked');
  const bar = document.getElementById('bulkActionsBar');
  const countEl = document.getElementById('selectedCount');
  if (checked.length > 0) {
    bar.classList.remove('hidden');
    bar.classList.add('flex');
    countEl.textContent = checked.length;
  } else {
    bar.classList.add('hidden');
    bar.classList.remove('flex');
  }
}

async function bulkAction(action) {
  const checked = document.querySelectorAll('.review-check:checked');
  const ids = Array.from(checked).map(cb => cb.getAttribute('data-id'));

  if (ids.length === 0) {
    showToast('error', 'No reviews selected');
    return;
  }

  if (action === 'delete_all' && !confirm(`Delete ${ids.length} selected reviews permanently?`)) return;

  try {
    const res = await fetch(`${BASE_URL}/admin/reviews/bulk-action`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: action, ids: ids })
    });
    const data = await res.json();
    if (data.success) {
      showToast('success', data.message);
      document.getElementById('selectAllReviews').checked = false;
      updateBulkBar();
      fetchReviews(1);
    } else {
      showToast('error', data.message || 'Bulk action failed');
    }
  } catch(e) {
    showToast('error', 'Network error during bulk action');
  }
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
