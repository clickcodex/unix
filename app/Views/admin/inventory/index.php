<?php
$pageTitle = 'Stock Overview & Inventory Movements';
$activeMenu = 'inventory';

require_once __DIR__ . '/../layouts/header.php';

$movements = $movements ?? [];
$lowStockProducts = $lowStockProducts ?? [];
$products = $products ?? [];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">Inventory &amp; Stock Movements</h1>
      <p class="text-slate-500 text-sm mt-1">Track stock levels, low-stock alerts, and manual inventory adjustments (In/Out/Adjustments)</p>
    </div>
    <div>
      <button onclick="openStockModal()" class="btn-primary text-sm shadow-md shadow-cc-blue/20 flex items-center gap-1.5 px-4 py-2.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 font-medium">
        <span class="material-icons text-[18px]">add_box</span> Adjust Stock
      </button>
    </div>
  </div>

  <!-- Low Stock Alert Banner -->
  <?php if (!empty($lowStockProducts)): ?>
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
          <span class="material-icons">warning</span>
        </div>
        <div>
          <h4 class="font-bold text-amber-900 text-sm"><?= count($lowStockProducts) ?> Products Low in Stock</h4>
          <p class="text-amber-700 text-xs mt-0.5">These items have reach critical inventory threshold (<= 10 units remaining).</p>
        </div>
      </div>
      <div class="flex gap-2">
        <?php foreach (array_slice($lowStockProducts, 0, 3) as $lp): ?>
          <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-white border border-amber-200 text-xs font-semibold text-amber-800">
            <?= htmlspecialchars($lp['name']) ?>: <strong class="text-rose-600"><?= $lp['stock_qty'] ?></strong> left
          </span>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- Movements Table -->
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
      <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
        <span class="material-icons text-indigo-600">history</span> Inventory Movement Log
      </h3>
      <span class="text-xs font-medium text-slate-500">Showing last 100 transactions</span>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm text-slate-700">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
          <tr>
            <th class="px-6 py-3.5">Date &amp; Time</th>
            <th class="px-6 py-3.5">Product</th>
            <th class="px-6 py-3.5">Movement Type</th>
            <th class="px-6 py-3.5">Quantity</th>
            <th class="px-6 py-3.5">Reference</th>
            <th class="px-6 py-3.5">Note</th>
            <th class="px-6 py-3.5">Logged By</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 font-medium">
          <?php if (empty($movements)): ?>
            <tr>
              <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                <span class="material-icons text-4xl block mb-1">inventory_2</span>
                No inventory movements logged yet.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($movements as $m): ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-4 text-xs text-slate-500">
                  <?= date('M d, Y H:i', strtotime($m['created_at'])) ?>
                </td>
                <td class="px-6 py-4 font-semibold text-slate-900">
                  <?= htmlspecialchars($m['product_name'] ?? 'Product #' . $m['product_id']) ?>
                </td>
                <td class="px-6 py-4">
                  <?php 
                    $typeClasses = [
                      'in' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                      'out' => 'bg-rose-50 text-rose-700 border-rose-200',
                      'adjustment' => 'bg-blue-50 text-blue-700 border-blue-200',
                      'return' => 'bg-purple-50 text-purple-700 border-purple-200'
                    ];
                  ?>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold uppercase tracking-wider border <?= $typeClasses[$m['movement_type']] ?? 'bg-slate-100 text-slate-700' ?>">
                    <?= htmlspecialchars($m['movement_type']) ?>
                  </span>
                </td>
                <td class="px-6 py-4 font-mono font-bold text-base <?= in_array($m['movement_type'], ['in', 'return']) ? 'text-emerald-600' : 'text-rose-600' ?>">
                  <?= in_array($m['movement_type'], ['in', 'return']) ? '+' : '-' ?><?= abs($m['quantity']) ?>
                </td>
                <td class="px-6 py-4 text-xs font-mono text-slate-600">
                  <?= htmlspecialchars($m['reference_type'] ?: 'N/A') ?>
                </td>
                <td class="px-6 py-4 text-xs text-slate-500 max-w-xs truncate">
                  <?= htmlspecialchars($m['note'] ?: '-') ?>
                </td>
                <td class="px-6 py-4 text-xs text-slate-700 font-semibold">
                  <?= htmlspecialchars($m['actor_name'] ?: 'System') ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Stock Adjustment Modal -->
<div id="stockModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center p-4">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
      <h3 class="font-bold text-slate-900 text-lg">Record Stock Movement</h3>
      <button onclick="closeStockModal()" class="text-slate-400 hover:text-slate-600"><span class="material-icons">close</span></button>
    </div>
    <form id="stockForm" onsubmit="saveStockMovement(event)" class="p-6 space-y-4">
      
      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Select Product *</label>
        <select id="product_id" name="product_id" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
          <option value="">-- Choose Product --</option>
          <?php foreach ($products as $p): ?>
            <option value="<?= $p['encrypted_id'] ?>"><?= htmlspecialchars($p['name']) ?> (Current Stock: <?= $p['stock_qty'] ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Movement Type *</label>
          <select id="movement_type" name="movement_type" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
            <option value="in">In (Stock Received)</option>
            <option value="out">Out (Dispatch/Damaged)</option>
            <option value="adjustment">Adjustment (+/-)</option>
            <option value="return">Customer Return</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Quantity *</label>
          <input type="number" id="quantity" name="quantity" min="1" required placeholder="e.g. 50" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none font-mono">
        </div>
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Reference Reason</label>
        <input type="text" id="reference_type" name="reference_type" placeholder="e.g. Supplier PO #1042, Manual Audit" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
      </div>

      <div>
        <label class="block text-xs font-semibold text-slate-700 mb-1">Note / Description</label>
        <textarea id="note" name="note" rows="2" placeholder="Additional details..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
      </div>

      <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
        <button type="button" onclick="closeStockModal()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
        <button type="submit" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Record Adjustment</button>
      </div>
    </form>
  </div>
</div>

<script>
function openStockModal() {
  document.getElementById('stockForm').reset();
  document.getElementById('stockModal').classList.remove('hidden');
  document.getElementById('stockModal').classList.add('flex');
}

function closeStockModal() {
  document.getElementById('stockModal').classList.add('hidden');
  document.getElementById('stockModal').classList.remove('flex');
}

function saveStockMovement(e) {
  e.preventDefault();
  const form = document.getElementById('stockForm');
  const formData = new FormData(form);

  fetch('<?= BASE_URL ?>/admin/inventory/record', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) location.reload();
    else alert(data.message || 'Error recording inventory movement.');
  })
  .catch(err => alert('Network error.'));
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
