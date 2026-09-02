<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-5xl mx-auto space-y-6 pb-12">

    <!-- Top Header Banner Card -->
    <div
        class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                <i data-lucide="sliders" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl font-semibold text-slate-900 tracking-tight">Website & General Settings</h1>
                <p class="text-xs text-slate-500 mt-0.5">Manage business identity, GST legal details, shipping rates,
                    and storefront social links.</p>
            </div>
        </div>
    </div>

    <!-- Settings Form -->
    <form action="<?= url('admin/settings/update') ?>" method="POST" class="space-y-6">
        <?= csrf_field() ?>

        <!-- SECTION 1: Company & GST Legal Details -->
        <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200 shadow-xs space-y-5">
            <div class="flex items-center space-x-2.5 border-b border-slate-100 pb-3">
                <i data-lucide="building-2" class="w-5 h-5 text-red-600"></i>
                <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">1. Company & GST Legal Details
                </h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">Brand Name <span
                            class="text-red-600">*</span></label>
                    <input type="text" name="site_name"
                        value="<?= htmlspecialchars($settings['site_name'] ?? 'Mudsor') ?>" required
                        class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">Company Legal Name (GST Registered)</label>
                    <input type="text" name="company_legal_name"
                        value="<?= htmlspecialchars($settings['company_legal_name'] ?? 'Rughwani Enterprises') ?>"
                        class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">Owner Name</label>
                    <input type="text" name="owner_name"
                        value="<?= htmlspecialchars($settings['owner_name'] ?? 'Jass Rughwani') ?>"
                        class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-red-600 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">GSTIN Number</label>
                    <input type="text" name="gstin"
                        value="<?= htmlspecialchars($settings['gstin'] ?? '07FLOPR6641L1Z8') ?>"
                        class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-mono uppercase font-semibold text-slate-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">Support Phone Number</label>
                    <input type="text" name="contact_phone"
                        value="<?= htmlspecialchars($settings['contact_phone'] ?? '+91 9217714452') ?>"
                        class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-red-600 focus:bg-white transition">
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">Support Email Address</label>
                    <input type="email" name="contact_email"
                        value="<?= htmlspecialchars($settings['contact_email'] ?? 'mudsorinfo@gmail.com') ?>"
                        class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-red-600 focus:bg-white transition">
                </div>
            </div>
        </div>

        <!-- SECTION 2: Shipping & Tax Rates -->
        <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200 shadow-xs space-y-5">
            <div class="flex items-center space-x-2.5 border-b border-slate-100 pb-3">
                <i data-lucide="truck" class="w-5 h-5 text-red-600"></i>
                <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">2. Shipping & Tax Rates</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">Free Shipping Order Threshold (₹)</label>
                    <input type="number" name="free_shipping_threshold"
                        value="<?= htmlspecialchars($settings['free_shipping_threshold'] ?? '999') ?>"
                        class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
                    <p class="text-[10px] text-slate-400 mt-1">Orders above this amount get free shipping.</p>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">Standard Courier Charge (₹)</label>
                    <input type="number" name="shipping_charge"
                        value="<?= htmlspecialchars($settings['shipping_charge'] ?? '79') ?>"
                        class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
                    <p class="text-[10px] text-slate-400 mt-1">Flat rate charge for orders below threshold.</p>
                </div>

                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">Default GST Rate (%)</label>
                    <input type="number" name="tax_rate" value="<?= htmlspecialchars($settings['tax_rate'] ?? '18') ?>"
                        class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
                    <p class="text-[10px] text-slate-400 mt-1">Default percentage applied for invoice tax computation.
                    </p>
                </div>
            </div>
        </div>

        <!-- SECTION 3: Cloud Storage & Media Assets -->
        <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200 shadow-xs space-y-5">
            <div class="flex items-center space-x-2.5 border-b border-slate-100 pb-3">
                <i data-lucide="cloud" class="w-5 h-5 text-red-600"></i>
                <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">3. Cloud Storage & Media
                    Assets</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                <div>
                    <label class="block font-semibold text-slate-700 mb-1.5">Cloudflare R2 Bucket Name</label>
                    <input type="text" name="cloudflare_r2_bucket"
                        value="<?= htmlspecialchars($settings['cloudflare_r2_bucket'] ?? 'mudsor-assets') ?>"
                        class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-mono text-slate-800 focus:outline-none focus:border-red-600 focus:bg-white transition">
                    <p class="text-[10px] text-slate-400 mt-1">S3-compatible bucket name for storing product media.</p>
                </div>
            </div>
        </div>

        <!-- SECTION 4: Social Media & Storefront Links -->
        <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200 shadow-xs space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <div class="flex items-center space-x-2.5">
                    <i data-lucide="share-2" class="w-5 h-5 text-red-600"></i>
                    <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">4. Social Media & Header
                        Links</h3>
                </div>
                <p class="text-xs text-slate-500 mt-1">Configure profile links and toggle visibility on the storefront
                    header and footer.</p>
            </div>

            <?php
            $socials = [
                ['key' => 'social_instagram', 'show_key' => 'show_social_instagram', 'label' => 'Instagram', 'placeholder' => 'https://instagram.com/yourpage'],
                ['key' => 'social_youtube', 'show_key' => 'show_social_youtube', 'label' => 'YouTube', 'placeholder' => 'https://youtube.com/@yourchannel'],
                ['key' => 'social_facebook', 'show_key' => 'show_social_facebook', 'label' => 'Facebook', 'placeholder' => 'https://facebook.com/yourpage'],
                ['key' => 'social_twitter', 'show_key' => 'show_social_twitter', 'label' => 'Twitter / X', 'placeholder' => 'https://x.com/yourhandle'],
                ['key' => 'social_whatsapp', 'show_key' => 'show_social_whatsapp', 'label' => 'WhatsApp', 'placeholder' => 'https://wa.me/919217714452'],
                ['key' => 'social_linkedin', 'show_key' => 'show_social_linkedin', 'label' => 'LinkedIn', 'placeholder' => 'https://linkedin.com/company/yourcompany'],
            ];
            ?>

            <div class="space-y-3 text-xs">
                <?php foreach ($socials as $s): ?>
                    <?php $isEnabled = ($settings[$s['show_key']] ?? '0') === '1'; ?>
                    <div
                        class="flex items-center gap-3.5 p-3.5 bg-slate-50 rounded-xl border border-slate-200/80 hover:border-slate-300 transition">
                        <!-- Toggle -->
                        <label class="relative inline-flex items-center cursor-pointer shrink-0"
                            title="Show / Hide <?= $s['label'] ?> on storefront">
                            <input type="hidden" name="<?= $s['show_key'] ?>" value="0">
                            <input type="checkbox" name="<?= $s['show_key'] ?>" value="1" class="sr-only peer" <?= $isEnabled ? 'checked' : '' ?>>
                            <div
                                class="w-9 h-5 bg-slate-300 peer-focus:ring-2 peer-focus:ring-red-300 rounded-full peer peer-checked:bg-red-600 transition after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:after:translate-x-4">
                            </div>
                        </label>
                        <!-- Label -->
                        <span class="font-semibold text-slate-800 w-24 shrink-0"><?= $s['label'] ?></span>
                        <!-- URL Input -->
                        <input type="url" name="<?= $s['key'] ?>"
                            value="<?= htmlspecialchars($settings[$s['key']] ?? '') ?>"
                            placeholder="<?= $s['placeholder'] ?>"
                            class="flex-1 h-9 px-3.5 bg-white border border-slate-200 rounded-lg font-mono text-xs text-slate-800 focus:outline-none focus:border-red-600 focus:ring-1 focus:ring-red-100 transition">
                    </div>
                <?php endforeach; ?>
            </div>
            <!-- SECTION 5: WhatsApp B2B & Wishlist Settings -->
            <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200 shadow-xs space-y-5">
                <div class="border-b border-slate-100 pb-3">
                    <div class="flex items-center space-x-2.5">
                        <i data-lucide="message-circle" class="w-5 h-5 text-emerald-600"></i>
                        <h3 class="text-sm font-semibold text-slate-900 uppercase tracking-wider">5. WhatsApp B2B &
                            Wishlist Settings</h3>
                    </div>
                    <p class="text-xs text-slate-500 mt-1">Configure business WhatsApp number, max wishlist limits, and
                        customizable enquiry message templates.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">WhatsApp Business Phone Number <span
                                class="text-red-600">*</span></label>
                        <input type="text" name="whatsapp_business_number"
                            value="<?= htmlspecialchars($settings['whatsapp_business_number'] ?? '919217714452') ?>"
                            required placeholder="e.g. 919217714452 (country code + number without spaces or +)"
                            class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-mono font-semibold text-slate-900 focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                        <p class="text-[10px] text-slate-400 mt-1">Format: Country code + phone number without + or
                            spaces (e.g., 919217714452).</p>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Wishlist Maximum Items Limit <span
                                class="text-red-600">*</span></label>
                        <input type="number" name="wishlist_max_limit" min="1" max="1000"
                            value="<?= htmlspecialchars($settings['wishlist_max_limit'] ?? '100') ?>" required
                            class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-900 focus:outline-none focus:border-emerald-600 focus:bg-white transition">
                        <p class="text-[10px] text-slate-400 mt-1">Maximum products allowed per user/guest wishlist
                            (default: 100).</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-slate-700 mb-1.5">Single-Product WhatsApp Enquiry
                            Template</label>
                        <textarea name="whatsapp_single_product_template" rows="4"
                            class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl font-mono text-xs text-slate-800 focus:outline-none focus:border-emerald-600 focus:bg-white transition"><?= htmlspecialchars($settings['whatsapp_single_product_template'] ?? "Hi, I want to enquire about this product:\n*Product:* {product_name}\n*SKU:* {sku}\n*URL:* {product_url}\n\nPlease share wholesale price & availability details.") ?></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Available placeholders: <code
                                class="bg-slate-100 px-1 rounded">{product_name}</code>, <code
                                class="bg-slate-100 px-1 rounded">{product_url}</code>, <code
                                class="bg-slate-100 px-1 rounded">{sku}</code>, <code
                                class="bg-slate-100 px-1 rounded">{price}</code>.</p>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-slate-700 mb-1.5">Wishlist Bulk WhatsApp Enquiry
                            Template</label>
                        <textarea name="whatsapp_wishlist_template" rows="5"
                            class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-xl font-mono text-xs text-slate-800 focus:outline-none focus:border-emerald-600 focus:bg-white transition"><?= htmlspecialchars($settings['whatsapp_wishlist_template'] ?? "Hi, I am interested in wholesale pricing for the following wishlist items:\n\n{product_list}\n\nPlease provide a bulk quotation and delivery timeline.") ?></textarea>
                        <p class="text-[10px] text-slate-400 mt-1">Available placeholders: <code
                                class="bg-slate-100 px-1 rounded">{product_list}</code> (automatically formatted with
                            product names & links).</p>
                    </div>
                </div>
            </div>

            <!-- Submit Button Row -->
            <div class="flex items-center justify-end pt-2">
                <button type="submit"
                    class="h-11 px-7 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center space-x-2 shrink-0 cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save Global Settings</span>
                </button>
            </div>

    </form>

</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>