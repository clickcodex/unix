</div>
<!-- ================= MAIN AREA CLOSING ================= -->

<!-- ================= GLOBAL JS & TOAST NOTIFICATIONS ================= -->
<script>
  function toggleSidebar() {
    const s = document.getElementById('sidebar');
    const o = document.getElementById('sidebarOverlay');
    if (s && o) {
      s.classList.toggle('mobile-open');
      o.classList.toggle('show');
      document.body.style.overflow = s.classList.contains('mobile-open') ? 'hidden' : '';
    }
  }

  function collapseSidebar() {
    const s = document.getElementById('sidebar');
    const m = document.getElementById('mainArea');
    const i = document.getElementById('collapseIcon');
    if (s && m && i) {
      s.classList.toggle('collapsed');
      m.classList.toggle('expanded');
      i.textContent = s.classList.contains('collapsed') ? 'chevron_right' : 'menu_open';
    }
  }

  function showToast(type, message) {
    let container = document.getElementById('toastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = 'toastContainer';
      container.className = 'toast-container';
      document.body.appendChild(container);
    }
    const item = document.createElement('div');
    let icon = 'info';
    let bgClass = 'toast-info';
    if (type === 'success') { icon = 'check_circle'; bgClass = 'toast-success'; }
    else if (type === 'error' || type === 'danger') { icon = 'error'; bgClass = 'toast-error'; }

    item.className = `toast-item ${bgClass}`;
    item.innerHTML = `<span class="material-icons text-[18px]">${icon}</span> <span>${String(message).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</span>`;
    container.appendChild(item);

    setTimeout(() => {
      item.classList.add('removing');
      setTimeout(() => item.remove(), 300);
    }, 3500);
  }
</script>

<?php if (!empty($_SESSION['flash_success'])): ?>
  <script>showToast('success', <?= json_encode($_SESSION['flash_success']) ?>);</script>
  <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
  <script>showToast('error', <?= json_encode($_SESSION['flash_error']) ?>);</script>
  <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

</body>
</html>
