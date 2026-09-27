<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<main class="max-w-5xl mx-auto px-4 py-12 space-y-10">
  <div class="text-center space-y-3 max-w-2xl mx-auto">
    <h1 class="text-3xl font-extrabold text-slate-900">Get in Touch</h1>
    <p class="text-slate-600 text-sm">Have a question regarding an order or product? Fill out the form below and our customer support team will assist you.</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
    <div class="space-y-6">
      <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-2">
        <span class="material-icons text-indigo-600">email</span>
        <h4 class="font-bold text-slate-900">Email Us</h4>
        <p class="text-xs text-slate-500">support@clickcodex.com</p>
      </div>

      <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-2">
        <span class="material-icons text-indigo-600">phone</span>
        <h4 class="font-bold text-slate-900">Call Us</h4>
        <p class="text-xs text-slate-500">+91 1800-123-4567 (Mon-Sat, 9AM - 6PM)</p>
      </div>

      <div class="bg-white border border-slate-200 rounded-2xl p-6 space-y-2">
        <span class="material-icons text-indigo-600">location_on</span>
        <h4 class="font-bold text-slate-900">Headquarters</h4>
        <p class="text-xs text-slate-500">ClickCodex Marketplace, Tech Hub Tower, MP, India</p>
      </div>
    </div>

    <div class="md:col-span-2 bg-white border border-slate-200 rounded-2xl p-8 shadow-sm">
      <form id="contactForm" onsubmit="submitContact(event)" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Your Name *</label>
            <input type="text" name="name" required placeholder="John Doe" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
          </div>
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address *</label>
            <input type="email" name="email" required placeholder="john@example.com" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
          </div>
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Subject</label>
          <input type="text" name="subject" placeholder="Order inquiry #..." class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none">
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1">Message *</label>
          <textarea name="message" rows="4" required placeholder="How can we help you?" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
        </div>

        <button type="submit" class="w-full py-3 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 transition-colors shadow-md">
          Send Message
        </button>
      </form>
    </div>
  </div>
</main>

<script>
function submitContact(e) {
  e.preventDefault();
  const form = document.getElementById('contactForm');
  const formData = new FormData(form);

  fetch('<?= BASE_URL ?>/contact/submit', {
    method: 'POST',
    body: formData
  })
  .then(r => r.json())
  .then(data => {
    alert(data.message);
    if (data.success) form.reset();
  });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
