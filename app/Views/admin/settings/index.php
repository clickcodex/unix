<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- ================= MAIN CONTENT ================= -->
<main class="p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto space-y-6">

  <!-- Header & Title Banner -->
  <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
    <div class="flex items-center gap-4">
      <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-cc-blue to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-cc-blue/20 shrink-0">
        <span class="material-icons text-3xl">settings</span>
      </div>
      <div>
        <h1 class="text-2xl font-bold font-heading text-slate-800">Website & Store Settings</h1>
        <p class="text-sm text-slate-500 mt-0.5">Configure store branding, logo, favicon, SEO, currency, shipping rules and policies.</p>
      </div>
    </div>
    <div class="flex items-center gap-3 shrink-0">
      <a href="<?= $baseUrl ?>/admin/dashboard" class="btn-secondary">
        <span class="material-icons text-[18px]">arrow_back</span> Back to Dashboard
      </a>
      <button type="button" onclick="submitActiveTabForm()" class="btn-primary">
        <span class="material-icons text-[18px]">save</span> Save All Changes
      </button>
    </div>
  </div>

  <!-- Settings Grid Layout (Sidebar Tabs + Form Body) -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

    <!-- Category Tabs Navigation -->
    <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200 p-3 shadow-sm space-y-1 sticky top-24">
      <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider px-3 py-2">Configuration Groups</p>
      
      <button type="button" onclick="switchTab('general')" id="tab-btn-general" class="tab-btn active flex items-center gap-3 w-full px-3.5 py-3 rounded-xl text-sm font-semibold transition text-left">
        <span class="material-icons text-[20px]">store</span>
        <span>General Store</span>
      </button>

      <button type="button" onclick="switchTab('branding')" id="tab-btn-branding" class="tab-btn flex items-center gap-3 w-full px-3.5 py-3 rounded-xl text-sm font-semibold transition text-left">
        <span class="material-icons text-[20px]">palette</span>
        <span>Branding & Logo</span>
      </button>

      <button type="button" onclick="switchTab('seo')" id="tab-btn-seo" class="tab-btn flex items-center gap-3 w-full px-3.5 py-3 rounded-xl text-sm font-semibold transition text-left">
        <span class="material-icons text-[20px]">search</span>
        <span>SEO & Analytics</span>
      </button>

      <button type="button" onclick="switchTab('shipping')" id="tab-btn-shipping" class="tab-btn flex items-center gap-3 w-full px-3.5 py-3 rounded-xl text-sm font-semibold transition text-left">
        <span class="material-icons text-[20px]">local_shipping</span>
        <span>Shipping & Tax</span>
      </button>

      <button type="button" onclick="switchTab('orders')" id="tab-btn-orders" class="tab-btn flex items-center gap-3 w-full px-3.5 py-3 rounded-xl text-sm font-semibold transition text-left">
        <span class="material-icons text-[20px]">shopping_cart</span>
        <span>Orders & Reviews</span>
      </button>

      <button type="button" onclick="switchTab('mail')" id="tab-btn-mail" class="tab-btn flex items-center gap-3 w-full px-3.5 py-3 rounded-xl text-sm font-semibold transition text-left">
        <span class="material-icons text-[20px]">mark_email_read</span>
        <span>SMTP & Email</span>
      </button>

      <button type="button" onclick="switchTab('payment')" id="tab-btn-payment" class="tab-btn flex items-center gap-3 w-full px-3.5 py-3 rounded-xl text-sm font-semibold transition text-left">
        <span class="material-icons text-[20px] text-purple-600">payments</span>
        <span>Payment & UPI Settings</span>
      </button>
    </div>

    <!-- Setting Form Panels -->
    <div class="lg:col-span-9 bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
      
      <form id="settingsForm" action="<?= $baseUrl ?>/admin/settings/update" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="group_name" id="activeGroupInput" value="general">

        <!-- ================= 1. GENERAL STORE SETTINGS ================= -->
        <div id="tab-panel-general" class="tab-panel space-y-6">
          <div class="border-b border-slate-200 pb-4">
            <h2 class="text-lg font-bold font-heading text-slate-800 flex items-center gap-2">
              <span class="material-icons text-cc-blue">store</span> General Store Profile
            </h2>
            <p class="text-xs text-slate-500">Basic marketplace information displayed across your storefront.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Store Name *</label>
              <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" required class="form-field" placeholder="e.g. ClickCodex Store">
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tagline / Slogan</label>
              <input type="text" name="site_tagline" value="<?= htmlspecialchars($settings['site_tagline'] ?? '') ?>" class="form-field" placeholder="e.g. Premium Tech Marketplace">
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Support Contact Email</label>
              <input type="email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" class="form-field" placeholder="support@domain.com">
            </div>
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Support Contact Phone</label>
              <input type="text" name="contact_phone" value="<?= htmlspecialchars($settings['contact_phone'] ?? '') ?>" class="form-field" placeholder="+91 98765 43210">
            </div>
            <div class="sm:col-span-2">
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Store Address</label>
              <textarea name="store_address" rows="2" class="form-field" placeholder="Full physical store address..."><?= htmlspecialchars($settings['store_address'] ?? '') ?></textarea>
            </div>
          </div>

          <!-- Maintenance Mode Toggle -->
          <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                <span class="material-icons">build</span>
              </div>
              <div>
                <p class="text-sm font-bold text-amber-900">Maintenance Mode</p>
                <p class="text-xs text-amber-700">When enabled, storefront visitors will see a maintenance page.</p>
              </div>
            </div>
            <label class="inline-toggle <?= !empty($settings['maintenance_mode']) ? 'on' : '' ?>" id="toggle-maintenance_mode" onclick="toggleSwitch('maintenance_mode')">
              <span class="it-thumb"></span>
              <input type="checkbox" name="maintenance_mode" id="input-maintenance_mode" value="1" <?= !empty($settings['maintenance_mode']) ? 'checked' : '' ?> class="hidden">
            </label>
          </div>
        </div>


        <!-- ================= 2. BRANDING & MEDIA SETTINGS (LOGO & FAVICON UPLOADER) ================= -->
        <?php
          $formatBrandingPreview = function(?string $url, string $baseUrl, string $fallback): string {
              if (empty($url)) return $fallback;
              if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0) {
                  if (strpos($url, '/uploads/') !== false && strpos($url, '/public/uploads/') === false) {
                      return str_replace('/uploads/', '/public/uploads/', $url);
                  }
                  return $url;
              }
              $clean = ltrim($url, '/');
              if (strpos($clean, 'public/uploads/') === 0) return $baseUrl . '/' . $clean;
              if (strpos($clean, 'uploads/') === 0) return $baseUrl . '/public/' . $clean;
              return $baseUrl . '/' . $clean;
          };

          $rawLogo = $settings['logo_url'] ?? '';
          $logoPreview = $formatBrandingPreview($rawLogo, $baseUrl, 'https://via.placeholder.com/200x60?text=Upload+Logo');

          $rawFavicon = $settings['favicon_url'] ?? '';
          $faviconPreview = $formatBrandingPreview($rawFavicon, $baseUrl, 'https://via.placeholder.com/32x32?text=Favicon');
        ?>
        <div id="tab-panel-branding" class="tab-panel hidden space-y-6">
          <div class="border-b border-slate-200 pb-4">
            <h2 class="text-lg font-bold font-heading text-slate-800 flex items-center gap-2">
              <span class="material-icons text-cc-purple">palette</span> Store Branding & Media Assets
            </h2>
            <p class="text-xs text-slate-500">Upload high-res store logo and site favicon or specify external URLs.</p>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            <!-- 1. STORE LOGO UPLOAD BOX -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-4 relative">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <span class="material-icons text-cc-blue text-[18px]">photo_library</span> Website Logo
                  </h3>
                  <p class="text-xs text-slate-500">Formats: PNG, SVG, WEBP, JPG (Max 5MB)</p>
                </div>
                <span class="px-2.5 py-1 bg-cc-blue/10 text-cc-blue rounded-lg text-[11px] font-bold uppercase tracking-wider">Primary</span>
              </div>

              <!-- Preview Frame -->
              <div class="h-28 bg-white border border-dashed border-slate-300 rounded-xl flex items-center justify-center p-3 relative group overflow-hidden">
                <img src="<?= htmlspecialchars($logoPreview) ?>" id="preview-logo_url" class="max-h-full max-w-full object-contain transition group-hover:scale-105">
                <div id="spinner-logo" class="hidden absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center gap-2 text-xs font-semibold text-cc-blue">
                  <span class="material-icons animate-spin">sync</span> Uploading Logo...
                </div>
              </div>

              <!-- File Input & Upload Action -->
              <div class="space-y-3">
                <div class="flex items-center gap-2">
                  <label class="btn-primary cursor-pointer flex-1 justify-center py-2 text-xs">
                    <span class="material-icons text-[16px]">cloud_upload</span> Choose & Upload Logo
                    <input type="file" name="logo_file" id="file-logo" accept="image/*" onchange="uploadBrandingFile('logo')" class="hidden">
                  </label>
                  <?php if (!empty($settings['logo_url'])): ?>
                    <button type="button" onclick="clearBranding('logo')" title="Remove Logo" class="btn-danger p-2">
                      <span class="material-icons text-[16px]">delete</span>
                    </button>
                  <?php endif; ?>
                </div>

                <div>
                  <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Direct Logo Image URL</label>
                  <input type="text" name="logo_url" id="input-logo_url" value="<?= htmlspecialchars($settings['logo_url'] ?? '') ?>" oninput="updateImagePreview('logo_url')" class="form-field text-xs font-mono" placeholder="https://domain.com/uploads/branding/logo.png">
                </div>
              </div>
            </div>

            <!-- 2. FAVICON UPLOAD BOX -->
            <div class="bg-slate-50 border border-slate-200 rounded-2xl p-5 space-y-4 relative">
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <span class="material-icons text-cc-yellow text-[18px]">tab</span> Website Favicon Icon
                  </h3>
                  <p class="text-xs text-slate-500">Formats: ICO, PNG, SVG (Recommended 32×32 or 64×64)</p>
                </div>
                <span class="px-2.5 py-1 bg-amber-100 text-amber-800 rounded-lg text-[11px] font-bold uppercase tracking-wider">Browser Icon</span>
              </div>

              <!-- Preview Frame -->
              <div class="h-28 bg-white border border-dashed border-slate-300 rounded-xl flex items-center justify-center p-3 relative group overflow-hidden">
                <div class="flex items-center gap-3 bg-slate-100 px-4 py-2 rounded-xl border border-slate-200 shadow-inner">
                  <img src="<?= htmlspecialchars($faviconPreview) ?>" id="preview-favicon_url" class="w-8 h-8 object-contain transition group-hover:scale-110">
                  <span class="text-xs text-slate-600 font-semibold truncate max-w-[120px]"><?= htmlspecialchars($settings['site_name'] ?? 'ClickCodex') ?></span>
                </div>
                <div id="spinner-favicon" class="hidden absolute inset-0 bg-white/80 backdrop-blur-sm flex items-center justify-center gap-2 text-xs font-semibold text-cc-blue">
                  <span class="material-icons animate-spin">sync</span> Uploading Favicon...
                </div>
              </div>

              <!-- File Input & Upload Action -->
              <div class="space-y-3">
                <div class="flex items-center gap-2">
                  <label class="btn-primary cursor-pointer flex-1 justify-center py-2 text-xs">
                    <span class="material-icons text-[16px]">cloud_upload</span> Choose & Upload Favicon
                    <input type="file" name="favicon_file" id="file-favicon" accept="image/x-icon,image/png,image/svg+xml,image/vnd.microsoft.icon" onchange="uploadBrandingFile('favicon')" class="hidden">
                  </label>
                  <?php if (!empty($settings['favicon_url'])): ?>
                    <button type="button" onclick="clearBranding('favicon')" title="Remove Favicon" class="btn-danger p-2">
                      <span class="material-icons text-[16px]">delete</span>
                    </button>
                  <?php endif; ?>
                </div>

                <div>
                  <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Direct Favicon URL</label>
                  <input type="text" name="favicon_url" id="input-favicon_url" value="<?= htmlspecialchars($settings['favicon_url'] ?? '') ?>" oninput="updateImagePreview('favicon_url')" class="form-field text-xs font-mono" placeholder="https://domain.com/uploads/branding/favicon.ico">
                </div>
              </div>
            </div>

            <!-- Footer Copyright Statement -->
            <div class="lg:col-span-2">
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Footer Copyright Statement</label>
              <input type="text" name="footer_copyright" value="<?= htmlspecialchars($settings['footer_copyright'] ?? '') ?>" class="form-field" placeholder="© 2026 ClickCodex Inc. All rights reserved.">
            </div>

          </div>
        </div>


        <!-- ================= 3. SEO & ANALYTICS SETTINGS ================= -->
        <div id="tab-panel-seo" class="tab-panel hidden space-y-6">
          <div class="border-b border-slate-200 pb-4">
            <h2 class="text-lg font-bold font-heading text-slate-800 flex items-center gap-2">
              <span class="material-icons text-cc-orange">search</span> Search Engine Optimization (SEO)
            </h2>
            <p class="text-xs text-slate-500">Configure global metadata tags and web analytics integration.</p>
          </div>

          <div class="space-y-4">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Default Meta Title Tag</label>
              <input type="text" name="meta_title" value="<?= htmlspecialchars($settings['meta_title'] ?? '') ?>" class="form-field" placeholder="Title tag for search engines">
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Meta Description</label>
              <textarea name="meta_description" rows="3" class="form-field" placeholder="Summarize your marketplace for Google search results..."><?= htmlspecialchars($settings['meta_description'] ?? '') ?></textarea>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Meta Keywords (Comma separated)</label>
              <input type="text" name="meta_keywords" value="<?= htmlspecialchars($settings['meta_keywords'] ?? '') ?>" class="form-field" placeholder="shopping, electronics, tech, ecommerce">
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Google Analytics Tag ID (GA4)</label>
              <input type="text" name="google_analytics_id" value="<?= htmlspecialchars($settings['google_analytics_id'] ?? '') ?>" class="form-field font-mono" placeholder="G-XXXXXXXXXX">
            </div>
          </div>
        </div>


        <!-- ================= 4. SHIPPING & TAX SETTINGS ================= -->
        <div id="tab-panel-shipping" class="tab-panel hidden space-y-6">
          <div class="border-b border-slate-200 pb-4">
            <h2 class="text-lg font-bold font-heading text-slate-800 flex items-center gap-2">
              <span class="material-icons text-emerald-600">local_shipping</span> Currency, Shipping & Tax Rules
            </h2>
            <p class="text-xs text-slate-500">Set base store currency, tax rates and free shipping threshold.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Currency Code</label>
              <select name="currency" class="form-field">
                <option value="INR" <?= ($settings['currency'] ?? '') === 'INR' ? 'selected' : '' ?>>INR (Indian Rupee - ₹)</option>
                <option value="USD" <?= ($settings['currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD (US Dollar - $)</option>
                <option value="EUR" <?= ($settings['currency'] ?? '') === 'EUR' ? 'selected' : '' ?>>EUR (Euro - €)</option>
                <option value="GBP" <?= ($settings['currency'] ?? '') === 'GBP' ? 'selected' : '' ?>>GBP (British Pound - £)</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Currency Symbol</label>
              <input type="text" name="currency_symbol" value="<?= htmlspecialchars($settings['currency_symbol'] ?? '') ?>" class="form-field text-center font-bold" placeholder="₹">
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Tax Rate (%)</label>
              <input type="number" step="0.01" name="tax_rate" value="<?= htmlspecialchars($settings['tax_rate'] ?? '18') ?>" class="form-field" placeholder="18.00">
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Flat Standard Shipping Fee (<?= htmlspecialchars($settings['currency_symbol'] ?? '₹') ?>)</label>
              <input type="number" step="0.01" name="default_shipping_fee" value="<?= htmlspecialchars($settings['default_shipping_fee'] ?? '99') ?>" class="form-field" placeholder="99.00">
            </div>

            <div class="sm:col-span-2">
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Free Shipping Minimum Threshold (<?= htmlspecialchars($settings['currency_symbol'] ?? '₹') ?>)</label>
              <input type="number" step="0.01" name="free_shipping_above" value="<?= htmlspecialchars($settings['free_shipping_above'] ?? '999') ?>" class="form-field" placeholder="999.00">
            </div>
          </div>

          <!-- Tax Inclusive Toggle -->
          <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-center justify-between">
            <div>
              <p class="text-sm font-bold text-slate-800">Prices Tax Inclusive</p>
              <p class="text-xs text-slate-500">When enabled, catalog product prices already include the tax rate.</p>
            </div>
            <label class="inline-toggle <?= !empty($settings['tax_inclusive']) ? 'on' : '' ?>" id="toggle-tax_inclusive" onclick="toggleSwitch('tax_inclusive')">
              <span class="it-thumb"></span>
              <input type="checkbox" name="tax_inclusive" id="input-tax_inclusive" value="1" <?= !empty($settings['tax_inclusive']) ? 'checked' : '' ?> class="hidden">
            </label>
          </div>
        </div>


        <!-- ================= 5. ORDERS & REVIEWS SETTINGS ================= -->
        <div id="tab-panel-orders" class="tab-panel hidden space-y-6">
          <div class="border-b border-slate-200 pb-4">
            <h2 class="text-lg font-bold font-heading text-slate-800 flex items-center gap-2">
              <span class="material-icons text-sky-600">shopping_cart</span> Order Numbering & Review Policies
            </h2>
            <p class="text-xs text-slate-500">System order prefixes, low stock alerts and review auto-approvals.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Order Code Prefix</label>
              <input type="text" name="order_prefix" value="<?= htmlspecialchars($settings['order_prefix'] ?? 'ORD') ?>" class="form-field font-mono uppercase" placeholder="ORD">
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Low Stock Warning Limit</label>
              <input type="number" name="low_stock_threshold" value="<?= htmlspecialchars($settings['low_stock_threshold'] ?? '5') ?>" class="form-field" placeholder="5">
            </div>
          </div>

          <!-- Review Auto-Approve Toggle -->
          <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 flex items-center justify-between">
            <div>
              <p class="text-sm font-bold text-slate-800">Auto-Approve Customer Reviews</p>
              <p class="text-xs text-slate-500">Automatically publish customer reviews without requiring manual admin approval.</p>
            </div>
            <label class="inline-toggle <?= !empty($settings['review_auto_approve']) ? 'on' : '' ?>" id="toggle-review_auto_approve" onclick="toggleSwitch('review_auto_approve')">
              <span class="it-thumb"></span>
              <input type="checkbox" name="review_auto_approve" id="input-review_auto_approve" value="1" <?= !empty($settings['review_auto_approve']) ? 'checked' : '' ?> class="hidden">
            </label>
          </div>
        </div>


        <!-- ================= 6. SMTP & MAIL SETTINGS ================= -->
        <div id="tab-panel-mail" class="tab-panel hidden space-y-6">
          <div class="border-b border-slate-200 pb-4">
            <h2 class="text-lg font-bold font-heading text-slate-800 flex items-center gap-2">
              <span class="material-icons text-indigo-600">mark_email_read</span> SMTP Mail Server Settings
            </h2>
            <p class="text-xs text-slate-500">Configure outbound email delivery servers for customer notifications.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">SMTP Server Host</label>
              <input type="text" name="smtp_host" value="<?= htmlspecialchars($settings['smtp_host'] ?? '') ?>" class="form-field font-mono" placeholder="smtp.mailtrap.io">
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">SMTP Port</label>
              <input type="number" name="smtp_port" value="<?= htmlspecialchars($settings['smtp_port'] ?? '587') ?>" class="form-field font-mono" placeholder="587">
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">SMTP Username</label>
              <input type="text" name="smtp_username" value="<?= htmlspecialchars($settings['smtp_username'] ?? '') ?>" class="form-field font-mono" placeholder="username">
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">SMTP Password</label>
              <input type="password" name="smtp_password" value="<?= htmlspecialchars($settings['smtp_password'] ?? '') ?>" class="form-field font-mono" placeholder="••••••••">
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Encryption</label>
              <select name="smtp_encryption" class="form-field">
                <option value="tls" <?= ($settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : '' ?>>TLS (Recommended)</option>
                <option value="ssl" <?= ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' ?>>SSL</option>
                <option value="none" <?= ($settings['smtp_encryption'] ?? '') === 'none' ? 'selected' : '' ?>>None</option>
              </select>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Sender Email Address</label>
              <input type="email" name="mail_from_address" value="<?= htmlspecialchars($settings['mail_from_address'] ?? '') ?>" class="form-field" placeholder="noreply@domain.com">
            </div>
          </div>
        </div>

        <!-- Footer Action Toolbar -->
        <div class="border-t border-slate-200 pt-6 mt-8 flex items-center justify-between">
          <button type="reset" class="btn-secondary">Reset Changes</button>
          <button type="submit" class="btn-primary">
            <span class="material-icons text-[18px]">save</span> Save Settings
          </button>
        <!-- ================= 7. PAYMENT & PHONEPE UPI SETTINGS ================= -->
        <div id="tab-panel-payment" class="tab-panel hidden space-y-6">
          <div class="border-b border-slate-200 pb-4">
            <h2 class="text-lg font-bold font-heading text-slate-800 flex items-center gap-2">
              <span class="material-icons text-purple-600">payments</span> Payment Gateway &amp; UPI Configuration
            </h2>
            <p class="text-xs text-slate-500">Configure your Store UPI VPA ID (for QR & Direct UPI payments) and PhonePe PG Credentials.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Store Merchant UPI VPA ID *</label>
              <input type="text" name="upi_vpa_id" value="<?= htmlspecialchars($settings['upi_vpa_id'] ?? 'clickcodex@ybl') ?>" required class="form-field font-mono font-bold text-purple-700" placeholder="e.g. yourname@ybl, company@upi, 9876543210@paytm">
              <p class="text-[11px] text-slate-400 mt-1">This VPA ID generates the live UPI QR code for customer payments.</p>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">UPI Merchant Display Name *</label>
              <input type="text" name="upi_merchant_name" value="<?= htmlspecialchars($settings['upi_merchant_name'] ?? 'ClickCodex Marketplace') ?>" required class="form-field" placeholder="e.g. ClickCodex Store">
            </div>

            <div class="sm:col-span-2 border-t border-slate-100 pt-4">
              <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2 mb-3">
                <span class="w-6 h-6 rounded-md bg-[#5F259F] text-white flex items-center justify-center font-black text-xs">पे</span>
                PhonePe Payment Gateway Settings
              </h3>
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">PhonePe Merchant ID (MID)</label>
              <input type="text" name="phonepe_merchant_id" value="<?= htmlspecialchars($settings['phonepe_merchant_id'] ?? 'PGTESTPAYUAT') ?>" class="form-field font-mono" placeholder="PGTESTPAYUAT or Live MID">
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">PhonePe Salt Key</label>
              <input type="password" name="phonepe_salt_key" value="<?= htmlspecialchars($settings['phonepe_salt_key'] ?? '') ?>" class="form-field font-mono" placeholder="PhonePe Salt Key">
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">PhonePe Salt Index</label>
              <input type="number" name="phonepe_salt_index" value="<?= htmlspecialchars($settings['phonepe_salt_index'] ?? 1) ?>" class="form-field font-mono" placeholder="1">
            </div>

            <div>
              <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">PhonePe Mode</label>
              <select name="phonepe_env" class="form-field font-bold">
                <option value="sandbox" <?= ($settings['phonepe_env'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' ?>>Sandbox (Testing Mode)</option>
                <option value="production" <?= ($settings['phonepe_env'] ?? '') === 'production' ? 'selected' : '' ?>>Production (Live Money Mode)</option>
              </select>
            </div>
          </div>
        </div>

      </form>
    </div>

  </div>

</main>

<style>
  .tab-btn{color:#64748B;background:transparent}
  .tab-btn:hover{color:#0F172A;background:#F8FAFC}
  .tab-btn.active{color:#2D82FF;background:rgba(45,130,255,.08)}
</style>

<script>
  function switchTab(groupName) {
    document.querySelectorAll('.tab-panel').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));

    const panel = document.getElementById('tab-panel-' + groupName);
    const btn = document.getElementById('tab-btn-' + groupName);
    const activeInput = document.getElementById('activeGroupInput');

    if (panel && btn) {
      panel.classList.remove('hidden');
      btn.classList.add('active');
    }
    if (activeInput) {
      activeInput.value = groupName;
    }
  }

  function toggleSwitch(key) {
    const toggleEl = document.getElementById('toggle-' + key);
    const inputEl = document.getElementById('input-' + key);

    if (toggleEl && inputEl) {
      inputEl.checked = !inputEl.checked;
      toggleEl.classList.toggle('on', inputEl.checked);
    }
  }

  function updateImagePreview(key) {
    const input = document.getElementById('input-' + key);
    const preview = document.getElementById('preview-' + key);
    if (input && preview) {
      let url = input.value.trim();
      if (url && url.includes('/uploads/') && !url.includes('/public/uploads/')) {
        url = url.replace('/uploads/', '/public/uploads/');
      }
      preview.src = url || (key === 'logo_url' ? 'https://via.placeholder.com/200x60?text=Logo' : 'https://via.placeholder.com/32x32?text=Favicon');
    }
  }

  function submitActiveTabForm() {
    document.getElementById('settingsForm').submit();
  }

  /**
   * Async AJAX Upload Handler for Branding files (Logo / Favicon)
   */
  async function uploadBrandingFile(type) {
    const fileInput = document.getElementById('file-' + type);
    const spinner = document.getElementById('spinner-' + type);
    const preview = document.getElementById('preview-' + type + '_url');
    const urlInput = document.getElementById('input-' + type + '_url');

    if (!fileInput || !fileInput.files.length) return;

    const file = fileInput.files[0];
    const formData = new FormData();
    formData.append('branding_type', type);
    formData.append('file', file);

    if (spinner) spinner.classList.remove('hidden');

    try {
      const response = await fetch('<?= $baseUrl ?>/admin/settings/upload-branding', {
        method: 'POST',
        body: formData
      });

      const result = await response.json();

      if (result.success && result.url) {
        if (urlInput) urlInput.value = result.url;
        if (preview) preview.src = result.url;
        showToast('success', result.message || (type.toUpperCase() + ' uploaded successfully!'));
      } else {
        showToast('error', result.message || ('Failed to upload ' + type));
      }
    } catch (err) {
      console.error(err);
      showToast('error', 'Error uploading file. Please try again.');
    } finally {
      if (spinner) spinner.classList.add('hidden');
      fileInput.value = ''; // Reset file input
    }
  }

  function clearBranding(type) {
    const urlInput = document.getElementById('input-' + type + '_url');
    const preview = document.getElementById('preview-' + type + '_url');

    if (urlInput) urlInput.value = '';
    if (preview) preview.src = type === 'logo' ? 'https://via.placeholder.com/200x60?text=Upload+Logo' : 'https://via.placeholder.com/32x32?text=Favicon';
    showToast('info', ucfirst(type) + ' cleared. Click "Save Settings" to save changes.');
  }

  function ucfirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
  }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
