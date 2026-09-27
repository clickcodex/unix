<?php
$pageTitle = 'Returns & Refund Operations';
$activeMenu = 'returns';

require_once __DIR__ . '/../layouts/header.php';

$returns = $returns ?? [];
?>

<div class="p-4 sm:p-6 lg:p-8 space-y-6">

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
      <h1 class="font-heading text-2xl sm:text-3xl font-bold text-slate-900">Returns &amp; Refund Management</h1>
      <p class="text-slate-500 text-sm mt-1">Review customer return requests, inspect returned items, and process refund transactions</p>
    </div>
  </div>

  <!-- Returns Table -->
  <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm text-slate-700">
        <thead class="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-500 uppercase tracking-wider">
          <tr>
            <th class="px-6 py-3.5">Order #</th>
            <th class="px-6 py-3.5">Customer</th>
            <th class="px-6 py-3.5">Total Amount</th>
            <th class="px-6 py-3.5">Return Status</th>
            <th class="px-6 py-3.5">Payment Status</th>
            <th class="px-6 py-3.5">Date Requested</th>
            <th class="px-6 py-3.5 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 font-medium">
          <?php if (empty($returns)): ?>
            <tr>
              <td colspan="7" class="px-6 py-8 text-center text-slate-400">
                <span class="material-icons text-4xl block mb-1">assignment_return</span>
                No pending or processed return requests found.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($returns as $r): ?>
              <tr class="hover:bg-slate-50/80 transition-colors">
                <td class="px-6 py-4 font-mono font-bold text-indigo-600">
                  <a href="<?= BASE_URL ?>/admin/orders/detail/<?= $r['encrypted_id'] ?>" class="hover:underline">
                    #<?= htmlspecialchars($r['order_number']) ?>
                  </a>
                </td>
                <td class="px-6 py-4">
                  <div class="font-semibold text-slate-900"><?= htmlspecialchars($r['customer_name'] ?: 'Guest') ?></div>
                  <div class="text-xs text-slate-500"><?= htmlspecialchars($r['customer_email'] ?: '') ?></div>
                </td>
                <td class="px-6 py-4 font-mono font-bold text-slate-900">
                  ₹<?= number_format($r['total_amount'], 2) ?>
                </td>
                <td class="px-6 py-4">
                  <?php
                    $statusStyles = [
                      'return_requested' => 'bg-amber-50 text-amber-700 border-amber-200',
                      'returned' => 'bg-purple-50 text-purple-700 border-purple-200',
                      'refunded' => 'bg-emerald-50 text-emerald-700 border-emerald-200'
                    ];
                  ?>
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold uppercase tracking-wider border <?= $statusStyles[$r['status']] ?? 'bg-slate-100' ?>">
                    <?= str_replace('_', ' ', htmlspecialchars($r['status'])) ?>
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-semibold capitalize <?= $r['payment_status'] === 'refunded' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' ?>">
                    <?= htmlspecialchars($r['payment_status']) ?>
                  </span>
                </td>
                <td class="px-6 py-4 text-xs text-slate-500">
                  <?= date('M d, Y H:i', strtotime($r['updated_at'])) ?>
                </td>
                <td class="px-6 py-4 text-right space-x-2">
                  <button onclick="openReturnModal('<?= $r['encrypted_id'] ?>', '<?= $r['order_number'] ?>', '<?= $r['status'] ?>')" class="text-indigo-600 hover:text-indigo-900 font-semibold text-xs inline-flex items-center gap-1">
                    <span class="material-icons text-sm">published_with_changes</span> Process Return
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Process Return Modal -->
  <div id="returnModal" class="fixed inset-0 z-50 bg-slate-900/40 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100">
      <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
        <h3 class="font-bold text-slate-900 text-lg">Process Return &amp; Refund</h3>
        <button onclick="closeReturnModal()" class="text-slate-400 hover:text-slate-600"><span class="material-icons">close</span></button>
      </div>
      <form id="returnForm" onsubmit="saveReturnStatus(event)" class="p-6 space-y-4">
        <input type="hidden" id="return_order_id" name="order_id" value="">
        
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Order Reference</label>
          <input type="text" id="return_order_number" readonly class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm bg-slate-50 font-mono font-bold text-indigo-700">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Target Return Status *</label>
          <select id="return_status" name="status" required class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none bg-white">
            <option value="return_requested">Return Requested (Under Review)</option>
            <option value="returned">Item Returned &amp; Inspected</option>
            <option value="refunded">Refund Approved &amp; Executed</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Resolution Note / Refund Details</label>
          <textarea id="return_note" name="note" rows="3" placeholder="Enter inspection findings or refund transaction ID..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
        </div>

        <div class="pt-4 flex justify-end gap-3 border-t border-slate-100">
          <button type="button" onclick="closeReturnModal()" class="px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
          <button type="submit" class="px-4 py-2 text-sm font-medium bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Update Return Status</button>
        </div>
      </form>
    </div>
  </div>

</div>

<script>
function openReturnModal(orderId, orderNumber, currentStatus) {
  document.getElementById('return_order_id').value = orderId;
  document.getElementById('return_order_number').value = '#' + orderNumber;
  document.getElementById('return_status').value = currentStatus;
  document.getElementById('return_note').value = '';
  document.getElementById('returnModal').classList.remove('hidden');
  document.getElementById('returnModal').classList.add('flex');
}

function closeReturnModal() {
  document.getElementById('returnModal').classList.add('hidden');
  document.getElementById('returnModal').classList.remove('flex');
}

function saveReturnStatus(e) {
  e.preventDefault();
  const form = document.getElementById('returnForm');
  const formData = new FormData(form);

  fetch('<?= BASE_URL ?>/admin/returns/update-status', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) location.reload();
    else alert(data.message || 'Error updating return status.');
  })
  .catch(err => alert('Network error.'));
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
