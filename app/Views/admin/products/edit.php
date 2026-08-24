<?php
include __DIR__ . '/../layouts/header.php';
$productNameClean = htmlspecialchars_decode($product['name'] ?? '');
$productDescClean = htmlspecialchars_decode($product['description'] ?? '');
?>

<div class="max-w-5xl mx-auto space-y-6 font-sans pb-12">
    <!-- Header Bar -->
    <div class="flex items-center justify-between bg-white p-6 rounded-2xl border border-gray-900 shadow-xs">
        <div class="flex items-center space-x-3">
            <a href="<?= url('admin/products') ?>"
                class="w-10 h-10 bg-gray-100 text-gray-700 hover:bg-gray-900 hover:text-white rounded-xl transition flex items-center justify-center shrink-0"
                title="Back to Products List">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h2 class="text-lg font-semibold text-gray-900 leading-snug">Edit Product:
                    <?= htmlspecialchars($productNameClean) ?>
                </h2>
                <p class="text-xs text-gray-500 mt-0.5 font-medium">Unified single-page product manager (all changes
                    save with one button below)</p>
            </div>
        </div>
    </div>

    <!-- ONE SINGLE UNIFIED FORM (SPEC 7) -->
    <form action="<?= url('admin/products/update/' . $product['id']) ?>" method="POST" enctype="multipart/form-data"
        class="space-y-6" id="unified-product-form">
        <?= csrf_field() ?>

        <!-- SECTION 1: BASIC INFORMATION & PRICING -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-5 shadow-xs">
            <div class="flex items-center space-x-2 border-b border-gray-100 pb-3">
                <i data-lucide="package" class="w-4 h-4 text-red-600"></i>
                <h3 class="font-semibold text-sm text-gray-900">Basic Details &amp; Pricing</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                <div class="sm:col-span-2">
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Product Name *</label>
                    <input type="text" name="name" required value="<?= htmlspecialchars($productNameClean) ?>"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl focus:outline-none focus:border-red-600 font-semibold text-gray-900">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">SKU Number *</label>
                    <input type="text" name="sku" required value="<?= htmlspecialchars($product['sku']) ?>"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl uppercase font-mono font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Primary Category *</label>
                    <select id="mainCategorySelect" name="category_id" required onchange="loadSubcategories(this.value)"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:bg-white text-xs focus:outline-none focus:border-[#f05a29]">
                        <option value="">-- Select Primary Category --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(htmlspecialchars_decode($cat['name'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Sub-category</label>
                    <select id="subCategorySelect" name="subcategory_id"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:bg-white text-xs focus:outline-none focus:border-[#f05a29]">
                        <option value="">-- Select Sub-category (Optional) --</option>
                        <?php if (!empty($subcategories)): ?>
                            <?php foreach ($subcategories as $sub): ?>
                                <option value="<?= $sub['id'] ?>" <?= (!empty($product['subcategory_id']) && $product['subcategory_id'] == $sub['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sub['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Primary Scooter Brand</label>
                    <select name="brand_id"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:bg-white text-xs focus:outline-none focus:border-[#f05a29]">
                        <option value="">-- Select Primary Brand --</option>
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>" <?= ($product['brand_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars(htmlspecialchars_decode($b['name'])) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Retail Single-Piece Price (₹) *</label>
                    <input type="number" step="0.01" name="price" required value="<?= $product['price'] ?>"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29]">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Retail Discounted Sale Price (₹)</label>
                    <input type="number" step="0.01" name="sale_price" value="<?= $product['sale_price'] ?>"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29]">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Inventory Stock Units *</label>
                    <input type="number" name="stock" required value="<?= $product['stock'] ?>" min="0"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29]">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Status</label>
                    <select name="status"
                        class="w-full h-11 px-3 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29]">
                        <option value="active" <?= ($product['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">HSN Code</label>
                    <input type="text" name="hsn_code" value="<?= htmlspecialchars($product['hsn_code'] ?? '8714.99.90') ?>" placeholder="e.g. 8714.99.90"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-mono font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29]">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">GST Rate (%) *</label>
                    <select name="tax_percent" required
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:bg-white text-xs focus:outline-none focus:border-[#f05a29]">
                        <?php $currentGst = (float)($product['tax_percent'] ?? 18); ?>
                        <option value="0" <?= $currentGst == 0 ? 'selected' : '' ?>>0% (Exempt / Nil Rated)</option>
                        <option value="5" <?= $currentGst == 5 ? 'selected' : '' ?>>5% GST</option>
                        <option value="12" <?= $currentGst == 12 ? 'selected' : '' ?>>12% GST</option>
                        <option value="18" <?= $currentGst == 18 ? 'selected' : '' ?>>18% GST (Default)</option>
                        <option value="28" <?= $currentGst == 28 ? 'selected' : '' ?>>28% GST</option>
                    </select>
                </div>
            </div>
            <p class="text-[11px] text-gray-500 font-medium italic mt-2">
                * All product prices are treated as <strong>GST INCLUSIVE</strong>.
            </p>
        </div>

        <!-- SECTION 1B: B2B WHOLESALE PRICING & TIER DISCOUNTS -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-5 shadow-xs">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center space-x-2">
                    <i data-lucide="tag" class="w-4 h-4 text-[#f05a29]"></i>
                    <h3 class="font-bold text-sm text-gray-900">B2B Wholesale Pricing &amp; Volume Discounts</h3>
                </div>
                <button type="button" onclick="addTierRow()"
                    class="px-3.5 py-1.5 bg-orange-50 text-[#f05a29] border border-orange-200 font-bold text-xs rounded-xl hover:bg-orange-100 transition flex items-center space-x-1 cursor-pointer">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Add Tier Row</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Wholesale Base Price (₹) *</label>
                    <input type="number" step="0.01" name="base_price" value="<?= (float)($product['base_price'] ?? $product['price']) ?>" placeholder="e.g. 350.00"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29]">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Minimum Order Quantity (MOQ) *</label>
                    <input type="number" name="moq" value="<?= (int)($product['moq'] ?? 1) ?>" min="1"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29]">
                </div>
            </div>

            <div>
                <label class="block font-bold text-gray-700 uppercase mb-2 text-xs">Volume Discount Tiers (Min Qty &rarr; Unit Price)</label>
                <div class="overflow-x-auto border border-gray-200 rounded-xl">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-700 font-bold uppercase tracking-wider text-[10px] border-b border-gray-200">
                                <th class="py-2.5 px-3">Min Qty</th>
                                <th class="py-2.5 px-3">Max Qty (Blank for &infin;)</th>
                                <th class="py-2.5 px-3">Wholesale Unit Price (₹)</th>
                                <th class="py-2.5 px-3 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tieredPricesBody" class="divide-y divide-gray-100">
                            <?php if (!empty($tieredPrices)): ?>
                                <?php foreach ($tieredPrices as $tier): ?>
                                    <tr>
                                        <td class="py-2 px-3">
                                            <input type="number" name="tier_min_qty[]" value="<?= (int)$tier['min_qty'] ?>" min="1" required class="w-full h-9 px-3 bg-gray-50 border border-gray-300 rounded-lg text-xs font-semibold">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="number" name="tier_max_qty[]" value="<?= $tier['max_qty'] !== null ? (int)$tier['max_qty'] : '' ?>" placeholder="&infin;" class="w-full h-9 px-3 bg-gray-50 border border-gray-300 rounded-lg text-xs font-semibold">
                                        </td>
                                        <td class="py-2 px-3">
                                            <input type="number" step="0.01" name="tier_unit_price[]" value="<?= (float)$tier['unit_price'] ?>" required class="w-full h-9 px-3 bg-gray-50 border border-gray-300 rounded-lg text-xs font-semibold">
                                        </td>
                                        <td class="py-2 px-3 text-right">
                                            <button type="button" onclick="this.closest('tr').remove()" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- SECTION 1B: WARRANTY & OEM COMPARISON DATA -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-5 shadow-xs">
            <div class="flex items-center space-x-2 border-b border-gray-100 pb-3">
                <i data-lucide="git-compare" class="w-4 h-4 text-red-600"></i>
                <h3 class="font-semibold text-sm text-gray-900">Warranty &amp; OEM Comparison Data</h3>
                <span class="text-[10px] bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded font-semibold ml-auto">Shown in Compare Page</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                <!-- Mudsor Warranty -->
                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Mudsor Warranty (Months)</label>
                    <input type="number" name="warranty_months" id="edit_warranty_months"
                        value="<?= (int)($product['warranty_months'] ?? 12) ?>"
                        min="0" max="120" placeholder="e.g. 12"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                    <p class="text-[10px] text-gray-500 mt-1">Mudsor product ki warranty (months mein)</p>
                </div>

                <!-- OEM Price -->
                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">OEM / Company Price (₹)
                        <span class="text-gray-400 font-normal normal-case">(vehicle brand ka price)</span>
                    </label>
                    <input type="number" step="0.01" name="oem_price" id="edit_oem_price"
                        value="<?= (float)($product['oem_price'] ?? 0) ?: '' ?>"
                        placeholder="Auto-estimated if blank"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                    <p class="text-[10px] text-gray-500 mt-1">Blank raho to auto-estimate (price × 1.4x) hoga</p>
                </div>

                <!-- OEM Warranty -->
                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">OEM Warranty (Months)</label>
                    <input type="number" name="oem_warranty_months" id="edit_oem_warranty"
                        value="<?= (int)($product['oem_warranty_months'] ?? 6) ?>"
                        min="0" max="60" placeholder="e.g. 6"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                    <p class="text-[10px] text-gray-500 mt-1">Vehicle brand ki warranty (usually 3–12 months)</p>
                </div>

                <!-- OEM Material -->
                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">OEM Material</label>
                    <input type="text" name="oem_material"
                        value="<?= htmlspecialchars($product['oem_material'] ?? 'Standard Steel / Plastic') ?>"
                        placeholder="e.g. Standard Steel / Plastic"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <!-- Live Preview -->
                <div class="sm:col-span-2 bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                    <p class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider mb-2">🏆 Compare Page Preview (Live)</p>
                    <div class="grid grid-cols-3 text-[11px] font-semibold gap-2">
                        <div class="text-gray-500">Mudsor Price: <span class="text-gray-900" id="prev_mudsor_price">₹<?= number_format((float)($product['sale_price'] ?: $product['price']), 0) ?></span></div>
                        <div class="text-gray-500">OEM Price: <span class="text-emerald-700" id="prev_oem_price">auto</span></div>
                        <div class="text-emerald-700" id="prev_saving">Saving: calculating...</div>
                    </div>
                    <div class="grid grid-cols-2 text-[11px] font-semibold gap-2 mt-1.5">
                        <div class="text-gray-500">Mudsor Warranty: <span class="text-gray-900" id="prev_m_warranty">—</span></div>
                        <div class="text-gray-500">OEM Warranty: <span class="text-gray-900" id="prev_o_warranty">—</span></div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function() {
            var mudsorPrice = <?= (float)($product['sale_price'] ?: $product['price']) ?>;
            function updatePreview() {
                var oemVal = parseFloat(document.getElementById('edit_oem_price').value) || 0;
                var oemPrice = oemVal > 0 ? oemVal : Math.round(mudsorPrice * 1.4);
                var saving = oemPrice - mudsorPrice;
                var savingPct = oemPrice > 0 ? Math.round((saving / oemPrice) * 100) : 0;
                document.getElementById('prev_oem_price').textContent = '₹' + Math.round(oemPrice).toLocaleString('en-IN') + (oemVal <= 0 ? ' (est.)' : '');
                document.getElementById('prev_saving').textContent = saving > 0
                    ? '✅ Save ' + savingPct + '% (₹' + Math.round(saving).toLocaleString('en-IN') + ')'
                    : 'No saving vs OEM';
                var mw = document.getElementById('edit_warranty_months').value;
                var ow = document.getElementById('edit_oem_warranty').value;
                document.getElementById('prev_m_warranty').textContent = mw ? mw + ' Months' : '—';
                document.getElementById('prev_o_warranty').textContent = ow ? ow + ' Months' : '—';
            }
            ['edit_oem_price', 'edit_warranty_months', 'edit_oem_warranty'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) el.addEventListener('input', updatePreview);
            });
            updatePreview();
        })();
        </script>

        <!-- SECTION 2: CATEGORIES & BRANDS -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-5 shadow-xs">
            <div class="flex items-center space-x-2 border-b border-gray-100 pb-3">
                <i data-lucide="tag" class="w-4 h-4 text-red-600"></i>
                <h3 class="font-semibold text-sm text-gray-900">Categories &amp; Multi-Brand Mapping</h3>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                <!-- MULTIPLE CATEGORIES -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-900 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="font-semibold text-gray-900 text-xs uppercase tracking-wider">Product
                            Categories</label>
                        <span
                            class="text-[10px] bg-red-600 text-white px-2 py-0.5 rounded font-semibold">MULTI-SELECT</span>
                    </div>
                    <div
                        class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 bg-white rounded-lg border border-gray-900">
                        <?php foreach ($categories as $cat): ?>
                            <label
                                class="flex items-center space-x-2 p-1.5 hover:bg-gray-50 rounded cursor-pointer font-semibold text-gray-800">
                                <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>" <?= in_array($cat['id'], $selectedCategoryIds) ? 'checked' : '' ?>
                                    class="rounded text-red-600 focus:ring-red-500">
                                <span class="truncate"><?= htmlspecialchars(htmlspecialchars_decode($cat['name'])) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- MULTIPLE BRANDS -->
                <div class="bg-gray-50 p-4 rounded-xl border border-gray-900 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="font-semibold text-gray-900 text-xs uppercase tracking-wider">Scooter
                            Brands</label>
                        <span
                            class="text-[10px] bg-gray-900 text-white px-2 py-0.5 rounded font-semibold">MULTI-SELECT</span>
                    </div>
                    <div
                        class="grid grid-cols-2 gap-2 max-h-48 overflow-y-auto p-2 bg-white rounded-lg border border-gray-900">
                        <?php foreach ($brands as $b): ?>
                            <label
                                class="flex items-center space-x-2 p-1.5 hover:bg-gray-50 rounded cursor-pointer font-semibold text-gray-800">
                                <input type="checkbox" name="brands[]" value="<?= $b['id'] ?>" <?= in_array($b['id'], $selectedBrandIds) ? 'checked' : '' ?> class="rounded text-red-600 focus:ring-red-500">
                                <span class="truncate"><?= htmlspecialchars(htmlspecialchars_decode($b['name'])) ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 3: UNIFIED PRODUCT IMAGES SECTION (SPEC 7) -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-5 shadow-xs" id="gallery-manager">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center space-x-2">
                        <i data-lucide="images" class="w-4 h-4 text-red-600"></i>
                        <span>Product Images (Cover / Primary Star Mark)</span>
                    </h3>
                    <p class="text-[11px] text-gray-500 mt-0.5">Select radio button ★ to set Cover Image • Upload new
                        image files or paste URLs below</p>
                </div>
                <span class="text-xs font-semibold bg-red-600 text-white px-3 py-1 rounded-lg">
                    <?= count($galleryImages) ?> Images
                </span>
            </div>

            <!-- Existing Images Grid with Primary Radio Mark -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <?php foreach ($galleryImages as $img): ?>
                    <?php $imgUrl = $img['image_url'] ?: $img['image_path']; ?>
                    <div class="gallery-item group relative rounded-xl border-2 bg-gray-50 overflow-hidden p-2 space-y-2 <?= $img['is_primary'] ? 'border-red-500 bg-red-50/20' : 'border-gray-900' ?>"
                        data-id="<?= $img['id'] ?>">
                        <div class="relative aspect-square rounded-lg overflow-hidden border border-gray-900">
                            <img src="<?= asset($imgUrl) ?>" class="w-full h-full object-cover"
                                onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'">
                        </div>
                        <div class="flex items-center justify-between text-xs pt-1">
                            <label
                                class="flex items-center space-x-1 cursor-pointer font-semibold text-[11px] text-gray-800">
                                <input type="radio" name="primary_image_id" value="<?= $img['id'] ?>" <?= $img['is_primary'] ? 'checked' : '' ?> class="text-red-600 focus:ring-red-500">
                                <span>★ Cover</span>
                            </label>
                            <button type="button" onclick="galleryDelete(<?= $img['id'] ?>, this)"
                                class="text-red-600 font-semibold hover:text-red-800" title="Delete Image">&times;</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Upload / Paste New Images -->
            <div class="bg-gray-50 rounded-xl border border-gray-900 p-4 space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Upload New Image Files:</label>
                        <input type="file" name="gallery_images[]" accept="image/*" multiple
                            class="w-full p-2 bg-white border border-gray-900 rounded-xl text-xs">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Or Paste Image URLs (one per
                            line):</label>
                        <textarea name="gallery_urls" rows="2" placeholder="https://example.com/photo1.jpg"
                            class="w-full p-2.5 bg-white border border-gray-900 rounded-xl text-xs font-mono focus:outline-none focus:border-red-600"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION: PRODUCT DEMO VIDEO & COVER THUMBNAIL -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-4 shadow-xs" x-data="{ videoSource: '<?= (!empty($product['video_url']) && str_contains($product['video_url'], '/uploads/videos/')) ? 'upload' : 'url' ?>' }">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center space-x-2">
                    <i data-lucide="video" class="w-4 h-4 text-red-600"></i>
                    <h3 class="font-semibold text-sm text-gray-900">Product Video &amp; Demo Media (Optional)</h3>
                </div>
                <div class="flex bg-gray-100 p-1 rounded-lg text-xs font-semibold space-x-1">
                    <button type="button" @click="videoSource = 'url'"
                        :class="videoSource === 'url' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                        class="px-3 py-1 rounded-md transition">Video Link / URL</button>
                    <button type="button" @click="videoSource = 'upload'"
                        :class="videoSource === 'upload' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                        class="px-3 py-1 rounded-md transition">Upload Video File</button>
                </div>
            </div>

            <!-- Hidden input for client-side HTML5 Canvas extracted frame base64 fallback -->
            <input type="hidden" name="auto_video_thumbnail_base64" id="edit-auto-thumb-base64">

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                <!-- Video Source Field -->
                <div class="space-y-2">
                    <div x-show="videoSource === 'url'" class="space-y-1">
                        <label class="block font-semibold text-gray-700 uppercase">Video URL Link</label>
                        <input type="url" name="video_url" value="<?= htmlspecialchars($product['video_url'] ?? '') ?>"
                            placeholder="https://www.youtube.com/watch?v=... or MP4 URL"
                            class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-red-600">
                        <p class="text-[11px] text-gray-500">YouTube, Instagram, Facebook, or MP4 link</p>
                    </div>

                    <div x-show="videoSource === 'upload'" class="space-y-1" x-cloak>
                        <label class="block font-semibold text-gray-700 uppercase">Select New Video File (.mp4, .webm)</label>
                        <input type="file" name="video_file" accept="video/mp4,video/webm"
                            onchange="extractFrameThumbnail(this, 'edit')"
                            class="w-full p-2 bg-gray-50 border border-gray-900 rounded-xl text-xs">
                        <p class="text-[11px] text-gray-500">Auto-extracts frame at 1-sec mark for poster thumbnail if no custom cover photo is uploaded.</p>
                    </div>
                </div>

                <!-- Cover Photo (Manual Override & Auto-Preview) -->
                <div class="space-y-2">
                    <label class="block font-semibold text-gray-700 uppercase">Cover Photo / Video Poster (Manual Override)</label>
                    <input type="file" name="video_thumbnail" accept="image/*"
                        class="w-full p-2 bg-gray-50 border border-gray-900 rounded-xl text-xs">
                    <p class="text-[11px] text-gray-500">Optional custom cover image. Overrides auto-generated video frame thumbnail.</p>

                    <!-- Effective Cover Preview Display -->
                    <?php 
                        $effectivePoster = \App\Helpers\VideoThumbnailHelper::resolveThumbnail(
                            $product['video_thumbnail'] ?? null,
                            $product['auto_video_thumbnail'] ?? null,
                            $product['video_url'] ?? null
                        );
                    ?>
                    <div id="edit-poster-preview-box" class="pt-2 flex items-center space-x-3 <?= empty($effectivePoster) ? 'hidden' : '' ?>">
                        <div class="relative w-24 aspect-video rounded-lg overflow-hidden bg-black border border-gray-300 shrink-0">
                            <img id="edit-poster-preview-img" src="<?= $effectivePoster ?>" class="w-full h-full object-cover" onerror="this.style.display='none'">
                            <span class="absolute bottom-0.5 right-0.5 text-[8px] bg-black/80 text-white font-bold px-1 rounded">POSTER</span>
                        </div>
                        <div class="text-[11px] text-gray-500 font-medium">
                            <span id="edit-poster-type-label" class="font-semibold text-gray-800">
                                <?= !empty($product['video_thumbnail']) ? '✓ Manual Cover Photo' : (!empty($product['auto_video_thumbnail']) ? '✓ Auto-Generated Frame' : 'No Cover Set') ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function extractFrameThumbnail(fileInput, mode) {
                if (!fileInput.files || !fileInput.files[0]) return;
                const file = fileInput.files[0];
                if (!file.type.startsWith('video/')) return;

                const video = document.createElement('video');
                video.preload = 'metadata';
                video.src = URL.createObjectURL(file);
                video.muted = true;
                video.playsInline = true;

                video.onloadeddata = function() {
                    video.currentTime = Math.min(1.0, (video.duration || 2) / 2);
                };

                video.onseeked = function() {
                    const canvas = document.createElement('canvas');
                    canvas.width = video.videoWidth || 640;
                    canvas.height = video.videoHeight || 360;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

                    const base64Data = canvas.toDataURL('image/jpeg', 0.85);
                    const hiddenInput = document.getElementById(mode + '-auto-thumb-base64');
                    if (hiddenInput) hiddenInput.value = base64Data;

                    const previewImg = document.getElementById(mode + '-poster-preview-img');
                    const previewBox = document.getElementById(mode + '-poster-preview-box');
                    const label = document.getElementById(mode + '-poster-type-label');

                    if (previewImg) {
                        previewImg.src = base64Data;
                        previewImg.style.display = 'block';
                    }
                    if (label) {
                        label.innerText = '⚡ Auto-Extracted Frame Preview';
                    }
                    if (previewBox) {
                        previewBox.classList.remove('hidden');
                    }

                    URL.revokeObjectURL(video.src);
                };
            }
        </script>

        <!-- SECTION 4: BADGES & DESCRIPTION -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-5 shadow-xs">
            <div class="flex items-center space-x-2 border-b border-gray-100 pb-3">
                <i data-lucide="file-text" class="w-4 h-4 text-red-600"></i>
                <h3 class="font-semibold text-sm text-gray-900">Description &amp; Promotional Badges</h3>
            </div>

            <div class="space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-red-600 uppercase mb-2">Promotional Badges</label>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <label
                            class="flex items-center space-x-2 bg-gray-50 p-3 rounded-xl border border-gray-900 cursor-pointer font-semibold hover:border-red-600 transition">
                            <input type="checkbox" name="is_featured" value="1" <?= !empty($product['is_featured']) ? 'checked' : '' ?> class="rounded text-red-600 focus:ring-red-500 w-4 h-4">
                            <span>Featured Deal</span>
                        </label>
                        <label
                            class="flex items-center space-x-2 bg-gray-50 p-3 rounded-xl border border-gray-900 cursor-pointer font-semibold hover:border-red-600 transition">
                            <input type="checkbox" name="is_best_seller" value="1" <?= !empty($product['is_best_seller']) ? 'checked' : '' ?> class="rounded text-red-600 focus:ring-red-500 w-4 h-4">
                            <span>Best Seller</span>
                        </label>
                        <label
                            class="flex items-center space-x-2 bg-gray-50 p-3 rounded-xl border border-gray-900 cursor-pointer font-semibold hover:border-red-600 transition">
                            <input type="checkbox" name="is_new_arrival" value="1" <?= !empty($product['is_new_arrival']) ? 'checked' : '' ?> class="rounded text-red-600 focus:ring-red-500 w-4 h-4">
                            <span>New Arrival</span>
                        </label>
                        <label
                            class="flex items-center space-x-2 bg-gray-50 p-3 rounded-xl border border-gray-900 cursor-pointer font-semibold hover:border-red-600 transition">
                            <input type="checkbox" name="is_flash_sale" value="1" <?= !empty($product['is_flash_sale']) ? 'checked' : '' ?> class="rounded text-red-600 focus:ring-red-500 w-4 h-4">
                            <span>Flash Sale</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 uppercase mb-1">Product Description</label>
                    <textarea name="description" rows="5" placeholder="Enter detailed product description..."
                        class="w-full p-4 bg-gray-50 border border-gray-900 rounded-xl focus:outline-none focus:border-red-600 text-xs font-medium text-gray-900"><?= htmlspecialchars(htmlspecialchars_decode($product['description'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>

        <!-- SECTION 5: FREQUENTLY BOUGHT TOGETHER BUNDLE ITEMS -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 space-y-6 shadow-xs"
            x-data="adminRelatedProductsComponent(<?= (int) $product['id'] ?>, <?= htmlspecialchars(json_encode(array_map(fn($p) => ['id' => (int) $p['id'], 'name' => $p['name'], 'slug' => $p['slug'] ?? '', 'sku' => $p['sku'], 'price' => format_price($p['sale_price'] ?: $p['price']), 'main_image' => asset($p['main_image'])], $frequentlyBought ?? [])), ENT_QUOTES, 'UTF-8') ?>)">

            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div class="flex items-center space-x-2">
                    <i data-lucide="layers" class="w-4 h-4 text-red-600"></i>
                    <h3 class="font-semibold text-sm text-gray-900">Frequently Bought Together Bundle Items</h3>
                </div>
                <span class="text-[11px] text-gray-400 font-medium">Max 5 bundle items • Bidirectionally
                    Synchronized</span>
            </div>

            <!-- Hidden Fields for Form Submission -->
            <input type="hidden" name="frequently_bought" :value="frequentlyBoughtItems.map(i => i.id).join(',')">

            <!-- 1. FREQUENTLY BOUGHT TOGETHER BUNDLE SELECTOR -->
            <div class="space-y-3 pt-2">
                <label class="block text-xs font-semibold text-gray-800 uppercase tracking-wider">
                    Search Products (<span x-text="frequentlyBoughtItems.length"></span>/5)
                </label>

                <!-- Search Input with Dropdown -->
                <div class="relative" @click.away="searchResultsBought = []; isSearching = false;">
                    <div class="relative">
                        <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3 text-gray-400"></i>
                        <input type="text" x-model="searchQueryBought" @focus="searchProducts()"
                            @input.debounce.300ms="searchProducts()" @keydown.enter.prevent=""
                            placeholder="Search bundle items by name, SKU, category, or brand..."
                            class="w-full h-10 pl-10 pr-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:bg-white focus:outline-none focus:border-red-600 transition">

                        <!-- Loading Indicator -->
                        <div x-show="isSearching" class="absolute right-3 top-3">
                            <i data-lucide="loader-2" class="w-4 h-4 text-gray-400 animate-spin"></i>
                        </div>
                    </div>

                    <!-- Dropdown Results -->
                    <div x-show="searchResultsBought.length > 0 || (searchQueryBought.trim() !== '' && !isSearching && searchResultsBought.length === 0)"
                        class="absolute z-30 top-11 left-0 right-0 bg-white border border-gray-900 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-gray-100 text-xs">

                        <!-- Empty State -->
                        <div x-show="searchQueryBought.trim() !== '' && !isSearching && searchResultsBought.length === 0"
                            class="p-4 text-center text-gray-500 italic">
                            No products found matching your search.
                        </div>

                        <!-- Results List -->
                        <template x-for="p in searchResultsBought" :key="p.id">
                            <div class="p-2.5 flex items-center justify-between hover:bg-gray-50 transition cursor-pointer"
                                @click="addBoughtProduct(p)">
                                <div class="flex items-center space-x-3">
                                    <img :src="p.main_image"
                                        @error="$el.src='<?= asset('assets/images/mudsor-logo.png') ?>'"
                                        class="w-8 h-8 rounded-lg object-contain bg-gray-50 border border-gray-900 p-0.5">
                                    <div>
                                        <p class="font-semibold text-gray-900 leading-tight" x-text="p.name"></p>
                                        <p class="text-[10px] text-gray-400 font-mono"
                                            x-text="'SKU: ' + (p.sku || 'N/A') + ' • ' + p.price"></p>
                                    </div>
                                </div>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md bg-gray-900 text-white">+
                                    Add</span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Selected List (Sortable via JS logic or just listed if sorting is native in backend) -->
                <div
                    class="min-h-[60px] bg-gray-50 p-3 rounded-xl border border-dashed border-gray-300 flex flex-col gap-2">
                    <template x-if="frequentlyBoughtItems.length === 0">
                        <p class="text-xs text-gray-400 italic py-1 px-2 text-center w-full">No bundle items selected.
                            Add products that customers frequently purchase together with this item.</p>
                    </template>

                    <template x-for="(p, idx) in frequentlyBoughtItems" :key="p.id">
                        <div
                            class="bg-white border border-gray-900 rounded-xl p-3 flex items-center justify-between shadow-2xs hover:border-gray-400 transition text-xs select-none group">
                            <div class="flex items-center space-x-3">
                                <div class="cursor-move text-gray-300 group-hover:text-gray-500 transition px-1">
                                    <i data-lucide="grip-vertical" class="w-4 h-4"></i>
                                </div>
                                <img :src="p.main_image"
                                    @error="$el.src='<?= asset('assets/images/mudsor-logo.png') ?>'"
                                    class="w-10 h-10 rounded-lg object-contain bg-gray-50 border border-gray-100 p-1">
                                <div>
                                    <p class="font-semibold text-gray-900 truncate" x-text="p.name"></p>
                                    <p class="text-[10px] text-gray-500 font-mono" x-text="p.price"></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 border-l border-gray-100 pl-3">
                                <a :href="'<?= url('product/') ?>' + (p.slug || p.id)" target="_blank"
                                    title="View Product Page"
                                    class="w-8 h-8 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600 transition flex items-center justify-center">
                                    <i data-lucide="external-link" class="w-4 h-4"></i>
                                </a>
                                <button type="button" @click="removeBoughtProduct(p.id)" title="Remove Item"
                                    class="w-8 h-8 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition flex items-center justify-center">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- ONE SINGLE UNIFIED SAVE BUTTON (SPEC 7) -->
        <div class="flex justify-end pt-4 sticky bottom-4">
            <button type="submit"
                class="h-14 px-12 bg-red-600 hover:bg-red-700 text-white font-semibold text-sm rounded-2xl transition shadow-lg flex items-center space-x-3 cursor-pointer">
                <i data-lucide="check-circle-2" class="w-5 h-5"></i>
                <span>Save All Product Changes</span>
            </button>
        </div>
    </form>
</div>

<script>
    const PRODUCT_ID = <?= (int) $product['id'] ?>;
    const CSRF_TOKEN = '<?= csrf_token() ?>';
    const BASE_URL = '<?= url('') ?>';

    function adminRelatedProductsComponent(currentProductId, initialBought) {
        return {
            productId: currentProductId,
            frequentlyBoughtItems: initialBought || [],
            searchQueryBought: '',
            searchResultsBought: [],
            isSearching: false,
            async searchProducts() {
                if (!this.searchQueryBought.trim()) {
                    this.searchResultsBought = [];
                    return;
                }

                this.isSearching = true;

                try {
                    const res = await fetch(`<?= url('admin/homepage-sections/search-products') ?>?q=${encodeURIComponent(this.searchQueryBought)}`);
                    const data = await res.json();
                    if (data.success && data.items) {
                        // Filter out the current product AND products already added to the bundle (No duplicate results)
                        this.searchResultsBought = data.items.filter(i => {
                            return i.id !== this.productId && !this.frequentlyBoughtItems.some(b => b.id === i.id);
                        });
                    }
                } catch (err) {
                    console.error('Search error:', err);
                } finally {
                    this.isSearching = false;
                }
            },
            addBoughtProduct(p) {
                if (!this.frequentlyBoughtItems.some(i => i.id === p.id) && this.frequentlyBoughtItems.length < 5) {
                    this.frequentlyBoughtItems.push(p);
                }
                this.searchResultsBought = [];
                this.searchQueryBought = '';
            },
            removeBoughtProduct(id) {
                this.frequentlyBoughtItems = this.frequentlyBoughtItems.filter(i => i.id !== id);
            }
        };
    }

    function apiPost(url, body) {
        const fd = new FormData();
        Object.entries(body).forEach(([k, v]) => fd.append(k, v));
        fd.append('_token', CSRF_TOKEN);
        return fetch(url, { method: 'POST', body: fd }).then(r => r.json());
    }

    function galleryDelete(imageId, btn) {
        const itemCard = btn.closest('.gallery-item');
        const imgEl = itemCard ? itemCard.querySelector('img') : null;
        const imgSrc = imgEl ? imgEl.src : null;

        showConfirmModal({
            title: 'Delete Product Image?',
            message: 'Are you sure you want to remove this image from the product gallery? This action cannot be undone.',
            previewImg: imgSrc,
            confirmText: 'Delete Image',
            onConfirm: async () => {
                const data = await apiPost(`${BASE_URL}/admin/products/gallery-delete/${imageId}`, {});
                if (data.success) {
                    if (itemCard) itemCard.remove();
                    showToast('Product image removed successfully!');
                } else {
    function loadSubcategories(categoryId) {
        const select = document.getElementById('subCategorySelect');
        if (!select) return;
        select.innerHTML = '<option value="">Loading...</option>';
        if (!categoryId) {
            select.innerHTML = '<option value="">-- Select Sub-category (Optional) --</option>';
            return;
        }
        fetch('<?= url("admin/subcategories/by-category/") ?>' + categoryId)
            .then(res => res.json())
            .then(data => {
                select.innerHTML = '<option value="">-- Select Sub-category (Optional) --</option>';
                if (data.success && data.subcategories) {
                    data.subcategories.forEach(sub => {
                        select.innerHTML += `<option value="${sub.id}">${sub.name}</option>`;
                    });
                }
            })
            .catch(() => {
                select.innerHTML = '<option value="">-- Select Sub-category (Optional) --</option>';
            });
    }

    function addTierRow() {
        const tbody = document.getElementById('tieredPricesBody');
        if (!tbody) return;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="py-2 px-3">
                <input type="number" name="tier_min_qty[]" required min="1" placeholder="e.g. 10" class="w-full h-9 px-3 bg-gray-50 border border-gray-300 rounded-lg text-xs font-semibold">
            </td>
            <td class="py-2 px-3">
                <input type="number" name="tier_max_qty[]" placeholder="e.g. 50 (or leave blank)" class="w-full h-9 px-3 bg-gray-50 border border-gray-300 rounded-lg text-xs font-semibold">
            </td>
            <td class="py-2 px-3">
                <input type="number" step="0.01" name="tier_unit_price[]" required placeholder="e.g. 320.00" class="w-full h-9 px-3 bg-gray-50 border border-gray-300 rounded-lg text-xs font-semibold">
            </td>
            <td class="py-2 px-3 text-right">
                <button type="button" onclick="this.closest('tr').remove()" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        if (window.lucide) lucide.createIcons();
    }
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>