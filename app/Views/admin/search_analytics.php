<?php
$title = 'Search Analytics & Customer Demand';
include __DIR__ . '/layouts/header.php';
$analytics = $analytics ?? [];
?>

<div class="space-y-6">

  <!-- Header Banner -->
  <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 rounded-3xl p-6 sm:p-8 text-white shadow-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    <div>
      <h1 class="font-heading font-extrabold text-2xl sm:text-3xl text-white flex items-center gap-3">
        <span class="material-icons text-cc-yellow text-3xl sm:text-4xl">analytics</span>
        Search Analytics &amp; Customer Demand
      </h1>
      <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-xl">
        Monitor real-time customer search behavior, track top trending keywords, and analyze unfound search queries to optimize catalog inventory.
      </p>
    </div>
    <a href="<?= $baseUrl ?>/admin/products" class="bg-cc-blue hover:bg-blue-600 text-white font-bold text-xs px-4 py-2.5 rounded-xl transition shadow-md shrink-0 flex items-center gap-2">
      <span class="material-icons text-base">inventory_2</span> Manage Products
    </a>
  </div>

  <!-- KPI Metrics Row -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    
    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-blue-50 text-cc-blue flex items-center justify-center font-bold text-xl shrink-0">
        <span class="material-icons text-2xl">search</span>
      </div>
      <div>
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Searches</p>
        <h3 class="font-extrabold text-2xl text-slate-900 mt-0.5"><?= number_format($analytics['totalSearches'] ?? 0) ?></h3>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xl shrink-0">
        <span class="material-icons text-2xl">tag</span>
      </div>
      <div>
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Unique Keywords</p>
        <h3 class="font-extrabold text-2xl text-slate-900 mt-0.5"><?= number_format($analytics['uniqueQueries'] ?? 0) ?></h3>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xl shrink-0">
        <span class="material-icons text-2xl">warning_amber</span>
      </div>
      <div>
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Zero Results (Demand)</p>
        <h3 class="font-extrabold text-2xl text-amber-600 mt-0.5"><?= number_format($analytics['zeroResultCount'] ?? 0) ?></h3>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs flex items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xl shrink-0">
        <span class="material-icons text-2xl">today</span>
      </div>
      <div>
        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Searches Today</p>
        <h3 class="font-extrabold text-2xl text-slate-900 mt-0.5"><?= number_format($analytics['searchesToday'] ?? 0) ?></h3>
      </div>
    </div>

  </div>

  <!-- Tables Section -->
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <!-- Top Searched Keywords Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
          <span class="material-icons text-cc-blue text-base">trending_up</span> Top Searched Keywords
        </h3>
        <span class="text-[11px] text-slate-400 font-medium">Most Popular</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
            <tr>
              <th class="py-3 px-4">#</th>
              <th class="py-3 px-4">Keyword Query</th>
              <th class="py-3 px-4 text-center">Searches</th>
              <th class="py-3 px-4 text-center">Avg Results</th>
              <th class="py-3 px-4 text-right">Last Searched</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium">
            <?php if (!empty($analytics['topQueries'])): ?>
              <?php foreach ($analytics['topQueries'] as $idx => $item): ?>
                <tr class="hover:bg-slate-50 transition">
                  <td class="py-3 px-4 text-slate-400 font-bold"><?= $idx + 1 ?></td>
                  <td class="py-3 px-4 font-bold text-slate-800">
                    <a href="<?= $baseUrl ?>/search?q=<?= urlencode($item['query']) ?>" target="_blank" class="hover:text-cc-blue flex items-center gap-1">
                      <?= htmlspecialchars($item['query']) ?>
                      <span class="material-icons text-[12px] text-slate-400">open_in_new</span>
                    </a>
                  </td>
                  <td class="py-3 px-4 text-center">
                    <span class="bg-blue-50 text-cc-blue font-bold px-2 py-0.5 rounded-full text-[11px]">
                      <?= (int)$item['search_count'] ?>
                    </span>
                  </td>
                  <td class="py-3 px-4 text-center text-slate-600">
                    <?= (int)$item['avg_results'] ?>
                  </td>
                  <td class="py-3 px-4 text-right text-slate-400 text-[11px]">
                    <?= date('M d, H:i', strtotime($item['last_searched'])) ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="py-6 text-center text-slate-400">No search logs recorded yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Zero Results Search Queries Table (Unfound Customer Demand) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="p-5 border-b border-slate-100 flex items-center justify-between">
        <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
          <span class="material-icons text-amber-500 text-base">search_off</span> Searches With 0 Results
        </h3>
        <span class="text-[11px] text-amber-600 font-bold bg-amber-50 px-2 py-0.5 rounded-full">Missing Stock Demand</span>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
          <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
            <tr>
              <th class="py-3 px-4">#</th>
              <th class="py-3 px-4">Unfound Query</th>
              <th class="py-3 px-4 text-center">Times Searched</th>
              <th class="py-3 px-4 text-right">Last Attempt</th>
              <th class="py-3 px-4 text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100 font-medium">
            <?php if (!empty($analytics['zeroResultsList'])): ?>
              <?php foreach ($analytics['zeroResultsList'] as $idx => $item): ?>
                <tr class="hover:bg-slate-50 transition">
                  <td class="py-3 px-4 text-slate-400 font-bold"><?= $idx + 1 ?></td>
                  <td class="py-3 px-4 font-bold text-amber-700">
                    <?= htmlspecialchars($item['query']) ?>
                  </td>
                  <td class="py-3 px-4 text-center">
                    <span class="bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded-full text-[11px]">
                      <?= (int)$item['search_count'] ?>
                    </span>
                  </td>
                  <td class="py-3 px-4 text-right text-slate-400 text-[11px]">
                    <?= date('M d, H:i', strtotime($item['last_searched'])) ?>
                  </td>
                  <td class="py-3 px-4 text-right">
                    <a href="<?= $baseUrl ?>/admin/products/create?name=<?= urlencode($item['query']) ?>" class="bg-cc-blue hover:bg-blue-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-lg transition inline-flex items-center gap-1">
                      <span class="material-icons text-xs">add</span> Add Product
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="5" class="py-6 text-center text-slate-400">Awesome! All customer search queries have matching products.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>

  <!-- Recent Searches Live Feed -->
  <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
    <div class="p-5 border-b border-slate-100 flex items-center justify-between">
      <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
        <span class="material-icons text-indigo-500 text-base">history</span> Recent Live Search Activity
      </h3>
      <span class="text-[11px] text-slate-400 font-medium">Realtime Audit Stream</span>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead class="bg-slate-50 text-slate-400 font-bold uppercase text-[10px] tracking-wider border-b border-slate-100">
          <tr>
            <th class="py-3 px-4">Timestamp</th>
            <th class="py-3 px-4">User / Session</th>
            <th class="py-3 px-4">Search Query</th>
            <th class="py-3 px-4 text-center">Results Returned</th>
            <th class="py-3 px-4 text-right">Status</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 font-medium">
          <?php if (!empty($analytics['recentLog'])): ?>
            <?php foreach ($analytics['recentLog'] as $log): ?>
              <tr class="hover:bg-slate-50 transition">
                <td class="py-3 px-4 text-slate-500 font-mono text-[11px]">
                  <?= date('Y-m-d H:i:s', strtotime($log['searched_at'])) ?>
                </td>
                <td class="py-3 px-4">
                  <?php if (!empty($log['user_name'])): ?>
                    <span class="font-bold text-slate-800"><?= htmlspecialchars($log['user_name']) ?></span>
                    <span class="text-slate-400 text-[10px] block"><?= htmlspecialchars($log['user_email']) ?></span>
                  <?php else: ?>
                    <span class="text-slate-400 italic">Guest Customer</span>
                  <?php endif; ?>
                </td>
                <td class="py-3 px-4 font-bold text-slate-800">
                  <?= htmlspecialchars($log['query']) ?>
                </td>
                <td class="py-3 px-4 text-center">
                  <span class="font-bold <?= (int)$log['result_count'] > 0 ? 'text-emerald-600' : 'text-red-500' ?>">
                    <?= (int)$log['result_count'] ?>
                  </span>
                </td>
                <td class="py-3 px-4 text-right">
                  <?php if ((int)$log['result_count'] > 0): ?>
                    <span class="bg-emerald-50 text-emerald-600 font-bold text-[10px] px-2 py-0.5 rounded-full">Success</span>
                  <?php else: ?>
                    <span class="bg-red-50 text-red-600 font-bold text-[10px] px-2 py-0.5 rounded-full">0 Results</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="5" class="py-6 text-center text-slate-400">No recent search logs available.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php include __DIR__ . '/layouts/footer.php'; ?>
