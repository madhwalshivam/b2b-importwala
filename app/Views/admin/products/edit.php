<?php
include __DIR__ . '/../layouts/header.php';
$productNameClean = htmlspecialchars_decode($product['name'] ?? '');
$productDescClean = htmlspecialchars_decode($product['description'] ?? '');
?>

<!-- CACHE BUSTER: <?= time() ?> -->
<style>
    /* FORCE ZERO GAP WITH TOP WHOLESALE ADMIN HEADER ON PAGE LOAD */
    main {
        padding-top: 0 !important;
    }

    .no-scrollbar::-webkit-scrollbar {
        display: none !important;
    }
    .no-scrollbar {
        -ms-overflow-style: none !important;
        scrollbar-width: none !important;
    }
    .sticky-header-solid {
        background-color: #ffffff !important;
        background: #ffffff !important;
        opacity: 1 !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }
</style>

<script>
    function scrollTabsNav(offset) {
        var nav = document.getElementById('productTabsNav');
        if (nav) {
            try {
                nav.scrollBy({ left: offset, behavior: 'smooth' });
            } catch(e) {}
            nav.scrollLeft += offset;
        }
    }
</script>

<div class="max-w-6xl mx-auto font-sans pb-16" x-data="{ activeTab: 'basic', isFormDirty: false }">
    <?php
    $inquiryModel = new \App\Models\Inquiry();
    $inqStats = $inquiryModel->getProductInquiryStats($product['id']);
    ?>

    <!-- MAIN FORM WRAPPER -->
    <form action="<?= url('admin/products/update/' . $product['id']) ?>" method="POST" enctype="multipart/form-data"
        class="space-y-4" id="unified-product-form" @input="isFormDirty = true" @change="isFormDirty = true">

        <!-- UNIFIED ZERO-GAP STICKY TOP HEADER (NAVBAR + TABS + SAVE BUTTON) -->
        <div class="sticky top-0 z-40 !mt-0 -mx-6 px-6 py-2.5 sticky-header-solid border-b border-slate-200/90 shadow-2xs mb-4"
             style="background-color: #ffffff !important; opacity: 1 !important; margin-top: 0 !important;">
            <?= csrf_field() ?>
            <div class="max-w-6xl mx-auto space-y-2">
                <!-- Top Row: Back Link + Inquiry Pill + Save Button -->
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-2">
                    <!-- Left: Back to Products -->
                    <a href="<?= url('admin/products') ?>"
                        class="flex items-center space-x-2 text-xs font-bold text-slate-700 hover:text-slate-900 transition">
                        <div class="w-6 h-6 bg-slate-100 rounded-lg flex items-center justify-center text-slate-600">
                            <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                        </div>
                        <span>Back to Products</span>
                    </a>

                    <!-- Right: Inquiry Counter & Primary Save Button -->
                    <div class="flex items-center space-x-2">
                        <a href="<?= url('admin/inquiries?search=' . urlencode($product['sku'])) ?>"
                            class="flex items-center space-x-2 bg-slate-50 hover:bg-slate-100 border border-slate-200/80 px-2.5 py-1 rounded-lg transition text-xs shrink-0"
                            title="View Customer Inquiries">
                            <i data-lucide="message-square" class="w-3.5 h-3.5 text-orange-600"></i>
                            <span class="text-[11px] font-bold text-slate-700">Inquiries: <span class="text-slate-900"><?= number_format((int)$inqStats['total_inquiries']) ?></span></span>
                            <span class="text-slate-300">•</span>
                            <span class="text-[11px] font-bold text-orange-700"><?= number_format((int)$inqStats['total_requested_units']) ?> Pcs</span>
                        </a>

                        <button type="submit"
                            class="h-7 px-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition flex items-center space-x-1 shrink-0 cursor-pointer shadow-2xs">
                            <i data-lucide="check" class="w-3.5 h-3.5"></i>
                            <span>Save Product</span>
                        </button>
                    </div>
                </div>

                <!-- Bottom Row: Clean Single-Row Tab Bar -->
                <div class="flex items-center gap-1.5 relative">
                    <!-- Left Scroll Arrow -->
                    <button type="button" onclick="scrollTabsNav(-300)"
                        class="w-6 h-6 rounded-lg bg-slate-900 text-white hover:bg-black flex items-center justify-center shrink-0 transition cursor-pointer z-10 active:scale-95"
                        title="Scroll Left">
                        <span class="text-xs font-bold leading-none select-none pointer-events-none">&lsaquo;</span>
                    </button>

                    <!-- Tab Buttons Strip -->
                    <nav class="flex items-center space-x-1.5 overflow-x-auto no-scrollbar scroll-smooth py-0.5 w-full" id="productTabsNav">
                        <button type="button" @click="activeTab = 'basic'" id="tab-btn-basic"
                            :class="activeTab === 'basic' ? 'bg-slate-900 text-white font-bold' : 'bg-white text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200/80 font-semibold'"
                            class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1 text-xs shrink-0 cursor-pointer select-none">
                            <i data-lucide="package" class="w-3.5 h-3.5"></i>
                            <span>1. Basic Info</span>
                        </button>

                        <button type="button" @click="activeTab = 'b2b'" id="tab-btn-b2b"
                            :class="activeTab === 'b2b' ? 'bg-slate-900 text-white font-bold' : 'bg-white text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200/80 font-semibold'"
                            class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1 text-xs shrink-0 cursor-pointer select-none">
                            <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                            <span>2. B2B Pricing</span>
                            <?php if (!empty($tieredPrices)): ?>
                                <span :class="activeTab === 'b2b' ? 'bg-orange-500 text-white' : 'bg-orange-100 text-orange-700'"
                                    class="px-1.5 py-0.5 text-[9px] rounded-md font-bold transition"><?= count($tieredPrices) ?></span>
                            <?php endif; ?>
                        </button>

                        <button type="button" @click="activeTab = 'oem'" id="tab-btn-oem"
                            :class="activeTab === 'oem' ? 'bg-slate-900 text-white font-bold' : 'bg-white text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200/80 font-semibold'"
                            class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1 text-xs shrink-0 cursor-pointer select-none">
                            <i data-lucide="git-compare" class="w-3.5 h-3.5"></i>
                            <span>3. Warranty &amp; OEM</span>
                        </button>

                        <button type="button" @click="activeTab = 'categories'" id="tab-btn-categories"
                            :class="activeTab === 'categories' ? 'bg-slate-900 text-white font-bold' : 'bg-white text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200/80 font-semibold'"
                            class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1 text-xs shrink-0 cursor-pointer select-none">
                            <i data-lucide="folder-tree" class="w-3.5 h-3.5"></i>
                            <span>4. Categories &amp; Brands</span>
                        </button>

                        <button type="button" @click="activeTab = 'media'" id="tab-btn-media"
                            :class="activeTab === 'media' ? 'bg-slate-900 text-white font-bold' : 'bg-white text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200/80 font-semibold'"
                            class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1 text-xs shrink-0 cursor-pointer select-none">
                            <i data-lucide="images" class="w-3.5 h-3.5"></i>
                            <span>5. Images &amp; Media</span>
                            <span id="tabMediaBadge" :class="activeTab === 'media' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600'"
                                class="px-1.5 py-0.5 text-[9px] rounded-md font-bold transition"><?= count($galleryImages) ?></span>
                        </button>

                        <button type="button" @click="activeTab = 'selling'" id="tab-btn-selling"
                            :class="activeTab === 'selling' ? 'bg-slate-900 text-white font-bold' : 'bg-white text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200/80 font-semibold'"
                            class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1 text-xs shrink-0 cursor-pointer select-none">
                            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
                            <span>6. Bundles &amp; Badges</span>
                        </button>

                        <button type="button" @click="activeTab = 'specs'" id="tab-btn-specs"
                            :class="activeTab === 'specs' ? 'bg-slate-900 text-white font-bold' : 'bg-white text-slate-600 hover:text-slate-900 hover:bg-slate-100 border border-slate-200/80 font-semibold'"
                            class="px-3 py-1.5 rounded-lg transition flex items-center space-x-1 text-xs shrink-0 cursor-pointer select-none">
                            <i data-lucide="layers" class="w-3.5 h-3.5"></i>
                            <span>7. Specs &amp; Variants</span>
                            <?php if (!empty($variants)): ?>
                                <span id="tabVariantsBadge" :class="activeTab === 'specs' ? 'bg-slate-700 text-white' : 'bg-slate-100 text-slate-600'"
                                    class="px-1.5 py-0.5 text-[9px] rounded-md font-bold transition"><?= count($variants) ?></span>
                            <?php endif; ?>
                        </button>
                    </nav>

                    <!-- Right Scroll Arrow -->
                    <button type="button" onclick="scrollTabsNav(300)"
                        class="w-6 h-6 rounded-lg bg-slate-900 text-white hover:bg-black flex items-center justify-center shrink-0 transition cursor-pointer z-10 active:scale-95"
                        title="Scroll Right">
                        <span class="text-xs font-bold leading-none select-none pointer-events-none">&rsaquo;</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 1: BASIC INFO & PRICING -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'basic'" class="space-y-5">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 space-y-5 shadow-xs">
                <div class="flex items-center space-x-2 border-b border-slate-100 pb-3">
                    <i data-lucide="package" class="w-4 h-4 text-slate-700"></i>
                    <h3 class="font-bold text-sm text-slate-900">Basic Details &amp; Pricing</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-slate-700 uppercase mb-1">Product Name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required value="<?= htmlspecialchars($productNameClean) ?>"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-slate-900 focus:bg-white font-semibold text-slate-900 transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">SKU Code <span class="text-rose-500">*</span></label>
                        <input type="text" name="sku" required value="<?= htmlspecialchars($product['sku']) ?>"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl uppercase font-mono font-bold text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Status <span class="text-rose-500">*</span></label>
                        <select name="status"
                            class="w-full h-11 px-3 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white transition">
                            <option value="active" <?= ($product['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                            <option value="inactive" <?= ($product['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Primary Category <span class="text-rose-500">*</span></label>
                        <select id="mainCategorySelect" name="category_id" required onchange="loadSubcategories(this.value)"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-900 focus:bg-white text-xs focus:outline-none focus:border-slate-900 transition">
                            <option value="">-- Select Primary Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(htmlspecialchars_decode($cat['name'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Sub-category</label>
                        <select id="subCategorySelect" name="subcategory_id"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-900 focus:bg-white text-xs focus:outline-none focus:border-slate-900 transition">
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
                        <label class="block font-bold text-slate-700 uppercase mb-1">Primary Scooter Brand</label>
                        <select name="brand_id"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-900 focus:bg-white text-xs focus:outline-none focus:border-slate-900 transition">
                            <option value="">-- Select Primary Brand --</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= $b['id'] ?>" <?= ($product['brand_id'] ?? '') == $b['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars(htmlspecialchars_decode($b['name'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Inventory Stock Units <span class="text-rose-500">*</span></label>
                        <input type="number" name="stock" required value="<?= $product['stock'] ?>" min="0"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Retail Single-Piece Price (₹) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" name="price" required value="<?= $product['price'] ?>"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Retail Discounted Sale Price (₹)</label>
                        <input type="number" step="0.01" name="sale_price" value="<?= $product['sale_price'] ?>"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">HSN Code</label>
                        <input type="text" name="hsn_code"
                            value="<?= htmlspecialchars($product['hsn_code'] ?? '8714.99.90') ?>"
                            placeholder="e.g. 8714.99.90"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl font-mono font-bold text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">GST Rate (%) <span class="text-rose-500">*</span></label>
                        <select name="tax_percent" required
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-900 focus:bg-white text-xs focus:outline-none focus:border-slate-900 transition">
                            <?php $currentGst = (float) ($product['tax_percent'] ?? 18); ?>
                            <option value="0" <?= $currentGst == 0 ? 'selected' : '' ?>>0% (Exempt / Nil Rated)</option>
                            <option value="5" <?= $currentGst == 5 ? 'selected' : '' ?>>5% GST</option>
                            <option value="12" <?= $currentGst == 12 ? 'selected' : '' ?>>12% GST</option>
                            <option value="18" <?= $currentGst == 18 ? 'selected' : '' ?>>18% GST (Default)</option>
                            <option value="28" <?= $currentGst == 28 ? 'selected' : '' ?>>28% GST</option>
                        </select>
                    </div>
                </div>

                <div class="pt-2">
                    <label class="block font-bold text-slate-700 uppercase mb-1 text-xs">Product Detailed Description</label>
                    <textarea name="description" rows="5" placeholder="Enter detailed product description..."
                        class="w-full p-4 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-slate-900 focus:bg-white text-xs font-medium text-slate-900 transition"><?= htmlspecialchars(htmlspecialchars_decode($product['description'] ?? '')) ?></textarea>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 2: B2B PRICING & WHOLESALE TIERS -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'b2b'" x-cloak class="space-y-5">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 space-y-5 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="tag" class="w-4 h-4 text-slate-700"></i>
                        <h3 class="font-bold text-sm text-slate-900">B2B Wholesale Pricing &amp; Volume Discounts</h3>
                    </div>
                    <button type="button" onclick="addTierRow()"
                        class="px-3.5 py-1.5 bg-slate-900 text-white font-semibold text-xs rounded-xl hover:bg-black transition flex items-center space-x-1 cursor-pointer">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                        <span>Add Tier Row</span>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Wholesale Base Price (₹) <span class="text-rose-500">*</span></label>
                        <input type="number" step="0.01" name="base_price"
                            value="<?= (float) ($product['base_price'] ?? $product['price']) ?>" placeholder="e.g. 350.00"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">Minimum Order Quantity (MOQ) <span class="text-rose-500">*</span></label>
                        <input type="number" name="moq" value="<?= (int) ($product['moq'] ?? 1) ?>" min="1"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white transition">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 uppercase mb-2 text-xs">Volume Discount Tiers (Min Qty &rarr; Unit Price)</label>
                    <div class="overflow-x-auto border border-slate-200 rounded-xl">
                        <table class="w-full text-xs text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 text-slate-700 font-bold uppercase tracking-wider text-[10px] border-b border-slate-200">
                                    <th class="py-2.5 px-3">Min Qty</th>
                                    <th class="py-2.5 px-3">Max Qty (Blank for &infin;)</th>
                                    <th class="py-2.5 px-3">Wholesale Unit Price (₹)</th>
                                    <th class="py-2.5 px-3 text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody id="tieredPricesBody" class="divide-y divide-slate-100">
                                <?php if (!empty($tieredPrices)): ?>
                                    <?php foreach ($tieredPrices as $tier): ?>
                                        <tr>
                                            <td class="py-2 px-3">
                                                <input type="number" name="tier_min_qty[]" value="<?= (int) $tier['min_qty'] ?>"
                                                    min="1" required
                                                    class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold">
                                            </td>
                                            <td class="py-2 px-3">
                                                <input type="number" name="tier_max_qty[]"
                                                    value="<?= $tier['max_qty'] !== null ? (int) $tier['max_qty'] : '' ?>"
                                                    placeholder="&infin;"
                                                    class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold">
                                            </td>
                                            <td class="py-2 px-3">
                                                <input type="number" step="0.01" name="tier_unit_price[]"
                                                    value="<?= (float) $tier['unit_price'] ?>" required
                                                    class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold">
                                            </td>
                                            <td class="py-2 px-3 text-right">
                                                <button type="button" onclick="this.closest('tr').remove()"
                                                    class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg transition"><i data-lucide="trash-2"
                                                        class="w-4 h-4"></i></button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 3: WARRANTY & OEM COMPARISON -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'oem'" x-cloak class="space-y-5">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 space-y-5 shadow-xs">
                <div class="flex items-center space-x-2 border-b border-slate-100 pb-3">
                    <i data-lucide="git-compare" class="w-4 h-4 text-slate-700"></i>
                    <h3 class="font-bold text-sm text-slate-900">Warranty &amp; OEM Comparison Data</h3>
                    <span class="text-[10px] bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-bold ml-auto">Compare Page Enabled</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">ImportWale Warranty (Months)</label>
                        <input type="number" name="warranty_months" id="edit_warranty_months"
                            value="<?= (int) ($product['warranty_months'] ?? 12) ?>" min="0" max="120" placeholder="e.g. 12"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white transition">
                        <p class="text-[10px] text-slate-500 mt-1">ImportWale product warranty in months.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">OEM / Company Price (₹)</label>
                        <input type="number" step="0.01" name="oem_price" id="edit_oem_price"
                            value="<?= (float) ($product['oem_price'] ?? 0) ?: '' ?>" placeholder="Auto-estimated if blank"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white transition">
                        <p class="text-[10px] text-slate-500 mt-1">Vehicle manufacturer price (defaults to 1.4x).</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">OEM Warranty (Months)</label>
                        <input type="number" name="oem_warranty_months" id="edit_oem_warranty"
                            value="<?= (int) ($product['oem_warranty_months'] ?? 6) ?>" min="0" max="60" placeholder="e.g. 6"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 uppercase mb-1">OEM Material</label>
                        <input type="text" name="oem_material"
                            value="<?= htmlspecialchars($product['oem_material'] ?? 'Standard Steel / Plastic') ?>"
                            placeholder="e.g. Standard Steel / Plastic"
                            class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl font-bold text-slate-900 focus:outline-none focus:border-slate-900 focus:bg-white transition">
                    </div>

                    <div class="sm:col-span-2 bg-slate-50 border border-slate-200 rounded-xl p-4">
                        <p class="text-[11px] font-bold text-slate-900 uppercase tracking-wider mb-2">🏆 Compare Card Preview (Live)</p>
                        <div class="grid grid-cols-3 text-[11px] font-bold gap-2">
                            <div class="text-slate-500">ImportWale Price: <span class="text-slate-900" id="prev_mudsor_price">₹<?= number_format((float) ($product['sale_price'] ?: $product['price']), 0) ?></span></div>
                            <div class="text-slate-500">OEM Price: <span class="text-slate-900" id="prev_oem_price">auto</span></div>
                            <div class="text-emerald-700" id="prev_saving">Calculating...</div>
                        </div>
                        <div class="grid grid-cols-2 text-[11px] font-bold gap-2 mt-1.5">
                            <div class="text-slate-500">ImportWale Warranty: <span class="text-slate-900" id="prev_m_warranty">—</span></div>
                            <div class="text-slate-500">OEM Warranty: <span class="text-slate-900" id="prev_o_warranty">—</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 4: CATEGORIES & BRAND MAPPING -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'categories'" x-cloak class="space-y-5">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 space-y-5 shadow-xs">
                <div class="flex items-center space-x-2 border-b border-slate-100 pb-3">
                    <i data-lucide="folder-tree" class="w-4 h-4 text-slate-700"></i>
                    <h3 class="font-bold text-sm text-slate-900">Categories &amp; Multi-Brand Mapping</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-slate-900 text-xs uppercase tracking-wider">Product Categories</label>
                            <span class="text-[10px] bg-slate-900 text-white px-2 py-0.5 rounded font-bold">MULTI-SELECT</span>
                        </div>
                        <input type="text" onkeyup="filterCheckboxList(this, 'category-list')" placeholder="Search categories..."
                            class="w-full h-8 px-3 bg-white border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:border-slate-900">
                        <div id="category-list" class="grid grid-cols-1 gap-1 max-h-56 overflow-y-auto p-2 bg-white rounded-lg border border-slate-200">
                            <?php foreach ($categories as $cat): ?>
                                <label class="checkbox-item flex items-center space-x-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer font-bold text-slate-800 text-xs">
                                    <input type="checkbox" name="categories[]" value="<?= $cat['id'] ?>" <?= in_array($cat['id'], $selectedCategoryIds) ? 'checked' : '' ?>
                                        class="rounded text-slate-900 focus:ring-slate-900 w-4 h-4">
                                    <span class="truncate"><?= htmlspecialchars(htmlspecialchars_decode($cat['name'])) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="font-bold text-slate-900 text-xs uppercase tracking-wider">Scooter Brands</label>
                            <span class="text-[10px] bg-slate-900 text-white px-2 py-0.5 rounded font-bold">MULTI-SELECT</span>
                        </div>
                        <input type="text" onkeyup="filterCheckboxList(this, 'brand-list')" placeholder="Search brands..."
                            class="w-full h-8 px-3 bg-white border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:border-slate-900">
                        <div id="brand-list" class="grid grid-cols-1 gap-1 max-h-56 overflow-y-auto p-2 bg-white rounded-lg border border-slate-200">
                            <?php foreach ($brands as $b): ?>
                                <label class="checkbox-item flex items-center space-x-2 p-1.5 hover:bg-slate-50 rounded cursor-pointer font-bold text-slate-800 text-xs">
                                    <input type="checkbox" name="brands[]" value="<?= $b['id'] ?>" <?= in_array($b['id'], $selectedBrandIds) ? 'checked' : '' ?>
                                        class="rounded text-slate-900 focus:ring-slate-900 w-4 h-4">
                                    <span class="truncate"><?= htmlspecialchars(htmlspecialchars_decode($b['name'])) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 5: IMAGES & MEDIA -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'media'" x-cloak class="space-y-5">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 space-y-5 shadow-xs" id="gallery-manager">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 flex items-center space-x-2">
                            <i data-lucide="images" class="w-4 h-4 text-slate-700"></i>
                            <span>Product Gallery &amp; Cover Selector</span>
                        </h3>
                        <p class="text-[11px] text-slate-500 mt-0.5">Click radio button ★ to choose main product cover photo.</p>
                    </div>
                    <span id="galleryCountBadge" class="text-xs font-bold bg-slate-900 text-white px-3 py-1 rounded-lg">
                        <?= count($galleryImages) ?> Images Total
                    </span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3" id="galleryGrid">
                    <?php foreach ($galleryImages as $img): ?>
                        <?php $imgUrl = $img['image_url'] ?: $img['image_path']; ?>
                        <div class="gallery-item group relative rounded-xl border-2 bg-slate-50 overflow-hidden p-1.5 space-y-1.5 transition-all <?= $img['is_primary'] ? 'border-emerald-500 bg-emerald-50/20' : 'border-slate-200 hover:border-slate-400' ?>"
                            data-id="<?= $img['id'] ?>">
                            <div class="relative aspect-square rounded-lg overflow-hidden border border-slate-200 bg-white">
                                <img src="<?= asset($imgUrl) ?>" class="w-full h-full object-contain p-1"
                                    onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'">
                                <?php if ($img['is_primary']): ?>
                                    <span class="absolute top-1 left-1 bg-emerald-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow-2xs cover-badge">COVER</span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center justify-between text-xs px-0.5 pt-0.5">
                                <label class="flex items-center space-x-1 cursor-pointer font-bold text-[10px] text-slate-800">
                                    <input type="radio" name="primary_image_id" value="<?= $img['id'] ?>" <?= $img['is_primary'] ? 'checked' : '' ?> onchange="setPrimaryCover(<?= $img['id'] ?>)" class="text-slate-900 focus:ring-slate-900">
                                    <span>★ Cover</span>
                                </label>
                                <button type="button" onclick="confirmGalleryDelete(<?= $img['id'] ?>, this)"
                                    class="text-rose-600 font-bold hover:text-rose-800 p-0.5 cursor-pointer" title="Delete Image">&times;</button>
                            </div>

                            <!-- INLINE CONFIRMATION OVERLAY FOR PHOTO DELETE -->
                            <div class="gallery-confirm-overlay hidden absolute inset-0 bg-slate-900/90 text-white p-2 flex flex-col items-center justify-center text-center space-y-2 z-20 transition-all rounded-xl">
                                <p class="text-[11px] font-bold leading-tight">Delete photo?</p>
                                <div class="flex items-center space-x-1.5">
                                    <button type="button" onclick="executeGalleryDelete(<?= $img['id'] ?>, this)"
                                        class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px] rounded-lg transition cursor-pointer shadow-2xs">Delete</button>
                                    <button type="button" onclick="cancelGalleryDelete(this)"
                                        class="px-2.5 py-1 bg-slate-700 hover:bg-slate-800 text-slate-200 font-bold text-[10px] rounded-lg transition cursor-pointer">Cancel</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 space-y-3">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Upload New Gallery Photos:</label>
                            <input type="file" name="gallery_images[]" accept="image/*" multiple
                                class="w-full p-2 bg-white border border-slate-200 rounded-xl text-xs">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Or Paste Image URLs (one per line):</label>
                            <textarea name="gallery_urls" rows="2" placeholder="https://example.com/photo1.jpg"
                                class="w-full p-2.5 bg-white border border-slate-200 rounded-xl text-xs font-mono focus:outline-none focus:border-slate-900 transition"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 space-y-4 shadow-xs"
                x-data="{ videoSource: '<?= (!empty($product['video_url']) && str_contains($product['video_url'], '/uploads/videos/')) ? 'upload' : 'url' ?>' }">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="video" class="w-4 h-4 text-slate-700"></i>
                        <h3 class="font-bold text-sm text-slate-900">Product Video &amp; Media Poster</h3>
                    </div>
                    <div class="flex bg-slate-100 p-1 rounded-lg text-xs font-semibold space-x-1">
                        <button type="button" @click="videoSource = 'url'"
                            :class="videoSource === 'url' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3 py-1 rounded-md transition">URL Link</button>
                        <button type="button" @click="videoSource = 'upload'"
                            :class="videoSource === 'upload' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-3 py-1 rounded-md transition">Upload File</button>
                    </div>
                </div>

                <input type="hidden" name="auto_video_thumbnail_base64" id="edit-auto-thumb-base64">

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
                    <div class="space-y-2">
                        <div x-show="videoSource === 'url'" class="space-y-1">
                            <label class="block font-bold text-slate-700 uppercase">Video URL Link</label>
                            <input type="url" name="video_url" value="<?= htmlspecialchars($product['video_url'] ?? '') ?>"
                                placeholder="https://www.youtube.com/watch?v=... or MP4 URL"
                                class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-slate-900 transition">
                            <p class="text-[11px] text-slate-500">Supports YouTube, Instagram, Facebook, or raw MP4 links.</p>
                        </div>

                        <div x-show="videoSource === 'upload'" class="space-y-1" x-cloak>
                            <label class="block font-bold text-slate-700 uppercase">Select Video File (.mp4, .webm)</label>
                            <input type="file" name="video_file" accept="video/mp4,video/webm"
                                onchange="extractFrameThumbnail(this, 'edit')"
                                class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block font-bold text-slate-700 uppercase">Custom Poster Cover Photo</label>
                        <input type="file" name="video_thumbnail" accept="image/*"
                            class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl text-xs">

                        <?php
                        $effectivePoster = \App\Helpers\VideoThumbnailHelper::resolveThumbnail(
                            $product['video_thumbnail'] ?? null,
                            $product['auto_video_thumbnail'] ?? null,
                            $product['video_url'] ?? null
                        );
                        ?>
                        <div id="edit-poster-preview-box"
                            class="pt-2 flex items-center space-x-3 <?= empty($effectivePoster) ? 'hidden' : '' ?>">
                            <div class="relative w-24 aspect-video rounded-lg overflow-hidden bg-black border border-slate-300 shrink-0">
                                <img id="edit-poster-preview-img" src="<?= $effectivePoster ?>"
                                    class="w-full h-full object-cover" onerror="this.style.display='none'">
                                <span class="absolute bottom-0.5 right-0.5 text-[8px] bg-black/80 text-white font-bold px-1 rounded">POSTER</span>
                            </div>
                            <div class="text-[11px] text-slate-500 font-medium">
                                <span id="edit-poster-type-label" class="font-bold text-slate-800">
                                    <?= !empty($product['video_thumbnail']) ? '✓ Custom Poster Photo' : (!empty($product['auto_video_thumbnail']) ? '✓ Auto Extracted Frame' : 'No Poster Set') ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 6: SELLING SETTINGS & SINGLE BUNDLE MANAGER -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'selling'" x-cloak class="space-y-5">
            <div class="bg-white p-6 rounded-2xl border border-slate-200 space-y-5 shadow-xs">
                <div class="flex items-center space-x-2 border-b border-slate-100 pb-3">
                    <i data-lucide="shield-check" class="w-4 h-4 text-slate-700"></i>
                    <h3 class="font-bold text-sm text-slate-900">Promotional Flags &amp; B2B Badges</h3>
                </div>

                <div class="space-y-4 text-xs">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <label class="flex items-center space-x-2 bg-slate-50 p-3 rounded-xl border border-slate-200 cursor-pointer font-bold hover:border-slate-900 transition">
                            <input type="checkbox" name="is_new" value="1" <?= (!empty($product['is_new']) || !empty($product['is_new_arrival'])) ? 'checked' : '' ?>
                                class="rounded text-slate-900 focus:ring-slate-900 w-4 h-4">
                            <span>New Product</span>
                        </label>
                        <label class="flex items-center space-x-2 bg-slate-50 p-3 rounded-xl border border-slate-200 cursor-pointer font-bold hover:border-slate-900 transition">
                            <input type="checkbox" name="is_free_shipping" value="1" <?= (!isset($product['is_free_shipping']) || !empty($product['is_free_shipping'])) ? 'checked' : '' ?>
                                class="rounded text-slate-900 focus:ring-slate-900 w-4 h-4">
                            <span>Free Delivery</span>
                        </label>
                        <label class="flex items-center space-x-2 bg-slate-50 p-3 rounded-xl border border-slate-200 cursor-pointer font-bold hover:border-slate-900 transition">
                            <input type="checkbox" name="is_featured" value="1" <?= !empty($product['is_featured']) ? 'checked' : '' ?>
                                class="rounded text-slate-900 focus:ring-slate-900 w-4 h-4">
                            <span>Featured Deal</span>
                        </label>
                        <label class="flex items-center space-x-2 bg-slate-50 p-3 rounded-xl border border-slate-200 cursor-pointer font-bold hover:border-slate-900 transition">
                            <input type="checkbox" name="is_flash_sale" value="1" <?= !empty($product['is_flash_sale']) ? 'checked' : '' ?>
                                class="rounded text-slate-900 focus:ring-slate-900 w-4 h-4">
                            <span>Flash Sale</span>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block font-bold text-slate-700 uppercase mb-1">Total Sold Count Display</label>
                            <input type="number" name="total_sold" min="0"
                                value="<?= (int) ($product['total_sold'] ?? $product['sales_count'] ?? 0) ?>"
                                placeholder="e.g. 1250"
                                class="w-full h-11 px-4 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:outline-none focus:border-slate-900 transition">
                            <p class="text-[11px] text-slate-500 mt-1">Displayed on storefront as "{Total Sold}+ sold".</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl border border-slate-200 space-y-5 shadow-xs">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="layers" class="w-4 h-4 text-slate-700"></i>
                        <h3 class="font-bold text-sm text-slate-900">Frequently Bought Together Bundle Items</h3>
                    </div>
                    <span class="text-[11px] text-slate-400 font-semibold">Max 5 bundle items</span>
                </div>

                <input type="hidden" id="frequently_bought_input" name="frequently_bought" value="">

                <div class="space-y-3 pt-1">
                    <label class="block text-xs font-bold text-slate-800 uppercase tracking-wider">
                        Search &amp; Add Bundle Products (<span id="bundleCountBadge">0</span>/5)
                    </label>

                    <div class="relative z-30" id="bundleSearchWrapper">
                        <div class="relative">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3 text-slate-400"></i>
                            <input type="text" id="bundleSearchInput" oninput="doBundleSearch(this.value)"
                                onfocus="doBundleSearch(this.value)" onkeyup="doBundleSearch(this.value)"
                                placeholder="Search products by name, SKU, category, or brand..."
                                class="w-full h-10 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-slate-900 transition">
                        </div>

                        <div id="bundleSearchResults"
                            class="hidden absolute z-50 top-11 left-0 right-0 bg-white border border-slate-200 rounded-xl shadow-2xl max-h-72 overflow-y-auto divide-y divide-slate-100 text-xs">
                        </div>
                    </div>

                    <div id="bundleSelectedContainer"
                        class="min-h-[60px] bg-slate-50 p-3 rounded-xl border border-dashed border-slate-300 flex flex-col gap-2">
                    </div>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- TAB 7: SPECS & VARIANTS -->
        <!-- ========================================================================= -->
        <div x-show="activeTab === 'specs'" x-cloak class="space-y-5">
            <!-- PRODUCT VARIANTS TABLE CONTAINER -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase bg-slate-100 text-slate-800 rounded-md border border-slate-200">
                                Multi-Variant System
                            </span>
                            <span class="text-xs font-semibold text-slate-400">•</span>
                            <span id="variantCountText" class="text-xs font-semibold text-slate-500"><?= count($variants ?? []) ?> Variants Configured</span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mt-1">Product Variants &amp; Dual Pricing</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Manage attributes, stock, wholesale, and one-piece pricing per variant.</p>
                    </div>
                    <button type="button" id="btnOpenVariantModal" data-action="add-variant" onclick="window.openVariantModal(); return false;"
                        class="px-4 py-2 bg-slate-900 hover:bg-black text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="plus-circle" class="w-4 h-4 pointer-events-none"></i> <span class="pointer-events-none">Add Variant</span>
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-4">Code</th>
                                <th class="py-3 px-4">Attribute &amp; Value</th>
                                <th class="py-3 px-4 text-center">Stock</th>
                                <th class="py-3 px-4 text-right">Wholesale Price (₹)</th>
                                <th class="py-3 px-4 text-right">One-Piece Price (₹)</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="variantsTableBody" class="divide-y divide-slate-100 text-slate-700">
                            <?php if (empty($variants)): ?>
                                <tr id="noVariantsRow">
                                    <td colspan="7" class="py-8 text-center text-slate-400">
                                        No variants configured yet. Click "Add Variant" above to add color/size variations.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($variants as $v): ?>
                                    <tr class="hover:bg-slate-50 transition variant-row" data-variant-id="<?= $v['id'] ?>">
                                        <td class="py-3 px-4 font-mono font-bold text-slate-900 v-code"><?= htmlspecialchars($v['variant_code'] ?: 'N/A') ?></td>
                                        <td class="py-3 px-4">
                                            <div class="font-bold text-slate-900 v-val"><?= htmlspecialchars($v['attribute_value']) ?></div>
                                            <div class="text-[10px] text-slate-400 v-lbl"><?= htmlspecialchars($v['attribute_label']) ?> <?= !empty($v['weight']) ? '&bull; ' . htmlspecialchars($v['weight']) : '' ?></div>
                                        </td>
                                        <td class="py-3 px-4 text-center font-bold text-slate-900 v-stock"><?= $v['stock_quantity'] ?></td>
                                        <td class="py-3 px-4 text-right font-bold text-slate-900 v-wprice">₹<?= number_format((float)$v['wholesale_price'], 2) ?></td>
                                        <td class="py-3 px-4 text-right font-bold text-emerald-600 v-oprice">₹<?= number_format((float)$v['one_piece_price'], 2) ?></td>
                                        <td class="py-3 px-4 text-center v-status">
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full border <?= $v['is_active'] ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' ?>">
                                                <?= $v['is_active'] ? 'Active' : 'Disabled' ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                                            <button type="button" data-action="edit-variant" data-variant="<?= htmlspecialchars(json_encode($v, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>"
                                                onclick="window.openEditVariantFromBtn(this); return false;"
                                                class="p-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg transition border border-slate-200 cursor-pointer" title="Edit Variant">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5 pointer-events-none"></i>
                                            </button>
                                            <button type="button" data-action="delete-variant" data-variant-id="<?= $v['id'] ?>"
                                                onclick="window.confirmDeleteVariant(<?= $v['id'] ?>, this); return false;"
                                                class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg transition border border-rose-200 cursor-pointer" title="Delete Variant">
                                                <i data-lucide="trash-2" class="w-3.5 h-3.5 pointer-events-none"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TECHNICAL SPECIFICATIONS TABLE CONTAINER -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-4 gap-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Technical Specifications</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Filterable key-value parameters displayed on storefront detail pages.</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <input type="text" onkeyup="filterSpecsTable(this.value)" placeholder="Search specs..."
                            class="h-9 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium focus:outline-none focus:border-slate-900">
                        <button type="button" data-action="add-spec" onclick="window.openAddSpecModal(); return false;"
                            class="px-3.5 py-2 bg-slate-900 hover:bg-black text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer shrink-0">
                            <i data-lucide="plus-circle" class="w-4 h-4 pointer-events-none"></i> <span class="pointer-events-none">Add Spec</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-[10px] font-bold text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-4 w-1/3">Specification Key</th>
                                <th class="py-3 px-4">Specification Value</th>
                                <th class="py-3 px-4 text-right w-24">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="specsTableBody" class="divide-y divide-slate-100">
                            <?php if (empty($specifications)): ?>
                                <tr id="noSpecsRow">
                                    <td colspan="3" class="py-6 text-center text-slate-400">
                                        No specifications added yet. Click "+ Add Spec" above to add parameters.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($specifications as $s): ?>
                                    <tr class="hover:bg-slate-50 transition spec-row" data-spec-id="<?= $s['id'] ?>">
                                        <td class="py-3 px-4 font-bold text-slate-900 spec-key-cell"><?= htmlspecialchars($s['spec_key']) ?></td>
                                        <td class="py-3 px-4 font-medium text-slate-700 spec-value-cell"><?= htmlspecialchars($s['spec_value']) ?></td>
                                        <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                                            <button type="button" data-action="edit-spec" data-spec-id="<?= $s['id'] ?>"
                                                onclick="window.openEditSpecModal(<?= $s['id'] ?>, this); return false;"
                                                class="p-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg transition border border-slate-200 cursor-pointer" title="Edit Specification">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5 pointer-events-none"></i>
                                            </button>
                                            <button type="button" data-action="delete-spec" data-spec-id="<?= $s['id'] ?>"
                                                onclick="window.confirmDeleteSpec(<?= $s['id'] ?>, this); return false;"
                                                class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg transition border border-rose-200 cursor-pointer" title="Delete Specification">
                                                <i data-lucide="trash-2" class="w-4 h-4 pointer-events-none"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- ========================================================================= -->
<!-- 1. VARIANT MODAL POPUP -->
<!-- ========================================================================= -->
<div id="variantModal" style="display: none; z-index: 9999;"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center hidden p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate-scale-in">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 class="text-sm font-bold text-slate-900" id="variantModalTitle">Add Product Variant</h3>
            <button type="button" onclick="window.closeVariantModal()" class="text-slate-400 hover:text-slate-700 transition cursor-pointer">✕</button>
        </div>

        <form id="variantForm" onsubmit="window.submitVariantForm(event)" class="p-6 space-y-4 text-xs">
            <input type="hidden" id="v_variant_id" value="0">

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Attribute Label <span class="text-rose-500">*</span></label>
                    <input type="text" id="v_attribute_label" required placeholder="e.g. Color, Size, Style"
                        value="Color / Style" class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Attribute Value <span class="text-rose-500">*</span></label>
                    <input type="text" id="v_attribute_value" required placeholder="e.g. Onyx Black, XL"
                        class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Variant SKU / Code</label>
                    <input type="text" id="v_variant_code" placeholder="e.g. VAR-001"
                        class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold font-mono">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Stock Quantity</label>
                    <input type="number" id="v_stock_quantity" min="0" value="50"
                        class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-900 mb-1">Wholesale Price (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" id="v_wholesale_price" required placeholder="0.00"
                        class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold">
                </div>
                <div>
                    <label class="block font-bold text-slate-900 mb-1">One-Piece Price (₹) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" id="v_one_piece_price" required placeholder="0.00"
                        class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold text-emerald-700">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Weight (Optional)</label>
                    <input type="text" id="v_weight" placeholder="e.g. 0.85 kg"
                        class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium">
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Dimensions (Optional)</label>
                    <input type="text" id="v_dimensions" placeholder="e.g. 15x8x4 cm"
                        class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium">
                </div>
            </div>

            <!-- DUAL OPTION FOR VARIANT IMAGE: LINK OR UPLOAD -->
            <div x-data="{ varImgTab: 'url' }" class="space-y-1.5 bg-slate-50 p-3 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between">
                    <label class="block font-bold text-slate-700 text-xs uppercase tracking-wider">Variant Image (Optional)</label>
                    <div class="flex bg-slate-200/70 p-0.5 rounded-lg text-[10px] font-bold space-x-1">
                        <button type="button" @click="varImgTab = 'url'"
                            :class="varImgTab === 'url' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-2.5 py-0.5 rounded-md transition cursor-pointer select-none">URL Link</button>
                        <button type="button" @click="varImgTab = 'upload'"
                            :class="varImgTab === 'upload' ? 'bg-white text-slate-900 shadow-2xs' : 'text-slate-600 hover:text-slate-900'"
                            class="px-2.5 py-0.5 rounded-md transition cursor-pointer select-none">Upload File</button>
                    </div>
                </div>

                <!-- Option A: URL Link -->
                <div x-show="varImgTab === 'url'">
                    <input type="text" id="v_image_url" placeholder="e.g. https://example.com/photo.jpg or uploads/..."
                        class="w-full h-9 px-3 bg-white border border-slate-200 rounded-lg text-xs font-mono focus:outline-none focus:border-slate-900 transition">
                    <p class="text-[10px] text-slate-500 mt-1">Paste image URL link from web or server.</p>
                </div>

                <!-- Option B: Upload File -->
                <div x-show="varImgTab === 'upload'" x-cloak>
                    <input type="file" id="v_image_file" accept="image/*"
                        class="w-full p-1 bg-white border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:border-slate-900 transition">
                    <p class="text-[10px] text-slate-500 mt-1">Upload image file directly from computer (.jpg, .png, .webp).</p>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" id="v_is_active" checked class="w-4 h-4 text-slate-900 rounded border-slate-300">
                <label for="v_is_active" class="font-bold text-slate-700">Is Active (Visible on storefront)</label>
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="window.closeVariantModal()"
                    class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition cursor-pointer">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-slate-900 text-white font-bold rounded-xl shadow-xs hover:bg-black transition cursor-pointer">Save Variant</button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- 2. SPECIFICATION MODAL POPUP -->
<!-- ========================================================================= -->
<div id="specModal" style="display: none; z-index: 9999;"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center hidden p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-scale-in">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 class="text-sm font-bold text-slate-900" id="specModalTitle">Add Product Specification</h3>
            <button type="button" onclick="window.closeSpecModal()" class="text-slate-400 hover:text-slate-700 transition cursor-pointer">✕</button>
        </div>

        <form id="specForm" onsubmit="window.submitSpecForm(event)" class="p-6 space-y-4 text-xs">
            <input type="hidden" id="s_spec_id" value="0">

            <div>
                <label class="block font-bold text-slate-700 mb-1 uppercase tracking-wider">Specification Key <span class="text-rose-500">*</span></label>
                <input type="text" id="s_spec_key" required placeholder="e.g. Material, Jewellery Type, Gender"
                    class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:outline-none focus:border-slate-900 transition">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1 uppercase tracking-wider">Specification Value <span class="text-rose-500">*</span></label>
                <input type="text" id="s_spec_value" required placeholder="e.g. Stainless Steel 316L, Earring, Women"
                    class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:outline-none focus:border-slate-900 transition">
            </div>

            <div class="pt-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" onclick="window.closeSpecModal()"
                    class="px-4 py-2 bg-slate-100 text-slate-700 font-bold rounded-xl hover:bg-slate-200 transition cursor-pointer">Cancel</button>
                <button type="submit" class="px-5 py-2 bg-slate-900 text-white font-bold rounded-xl shadow-xs hover:bg-black transition cursor-pointer">Save Specification</button>
            </div>
        </form>
    </div>
</div>

<script>
    window.PRODUCT_ID = <?= (int) $product['id'] ?>;
    window.CSRF_TOKEN = window.CSRF_TOKEN || '<?= csrf_token() ?>';
    window.BASE_URL = window.BASE_URL || '<?= url('') ?>';

    var PRODUCT_ID = window.PRODUCT_ID;
    var CSRF_TOKEN = window.CSRF_TOKEN;
    var BASE_URL = window.BASE_URL;

    // TOAST NOTIFICATION HELPER
    function showToast(msg, type = 'success') {
        if (window.showToast && window.showToast !== showToast) {
            window.showToast(msg, type);
            return;
        }
        if (window.MudsorToast) {
            window.MudsorToast.show(msg, type);
            return;
        }
        alert(msg);
    }

    // FALLBACK JS SWITCH TAB
    function switchTab(tabId) {
        if (window.Alpine) {
            const root = document.querySelector('[x-data]');
            if (root && root._x_dataStack) {
                root._x_dataStack[0].activeTab = tabId;
                return;
            }
        }
        document.querySelectorAll('.tab-pane').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('bg-slate-900', 'text-white', 'shadow-2xs');
            btn.classList.add('text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-100');
        });

        const activePane = document.getElementById('tab-content-' + tabId);
        const activeBtn = document.getElementById('tab-btn-' + tabId);
        if (activePane) activePane.classList.remove('hidden');
        if (activeBtn) {
            activeBtn.classList.remove('text-slate-600', 'hover:text-slate-900', 'hover:bg-slate-100');
            activeBtn.classList.add('bg-slate-900', 'text-white', 'shadow-2xs');
        }
    }
    window.switchTab = switchTab;

    // CHECKBOX SEARCH FILTER
    function filterCheckboxList(inputEl, containerId) {
        const query = (inputEl.value || '').toLowerCase();
        const container = document.getElementById(containerId);
        if (!container) return;
        const labels = container.querySelectorAll('.checkbox-item');
        labels.forEach(lbl => {
            const txt = lbl.textContent.toLowerCase();
            lbl.style.display = txt.includes(query) ? 'flex' : 'none';
        });
    }
    window.filterCheckboxList = filterCheckboxList;

    // SPECS TABLE SEARCH FILTER
    function filterSpecsTable(query) {
        const q = (query || '').toLowerCase();
        const rows = document.querySelectorAll('#specsTableBody .spec-row');
        rows.forEach(r => {
            const keyTxt = (r.querySelector('.spec-key-cell')?.textContent || '').toLowerCase();
            const valTxt = (r.querySelector('.spec-value-cell')?.textContent || '').toLowerCase();
            r.style.display = (keyTxt.includes(q) || valTxt.includes(q)) ? '' : 'none';
        });
    }
    window.filterSpecsTable = filterSpecsTable;

    // VIDEO FRAME EXTRACTION
    function extractFrameThumbnail(fileInput, mode) {
        if (!fileInput.files || !fileInput.files[0]) return;
        const file = fileInput.files[0];
        if (!file.type.startsWith('video/')) return;

        const video = document.createElement('video');
        video.preload = 'metadata';
        video.src = URL.createObjectURL(file);
        video.muted = true;
        video.playsInline = true;

        video.onloadeddata = function () {
            video.currentTime = Math.min(1.0, (video.duration || 2) / 2);
        };

        video.onseeked = function () {
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
    window.extractFrameThumbnail = extractFrameThumbnail;

    // FREQUENTLY BOUGHT TOGETHER BUNDLE MANAGER
    let selectedBundleItems = <?= json_encode(array_map(fn($p) => [
        'id' => (int) $p['id'],
        'name' => htmlspecialchars_decode($p['name'] ?? ''),
        'sku' => $p['sku'] ?? '',
        'price' => format_price(($p['sale_price'] ?? 0) > 0 ? $p['sale_price'] : ($p['price'] ?? 0)),
        'main_image' => asset($p['main_image'] ?? 'assets/images/placeholder.jpg')
    ], $frequentlyBought ?? []), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

    let bundleSearchTimeout = null;
    let searchMap = {};

    function renderSelectedBundleItems() {
        const container = document.getElementById('bundleSelectedContainer');
        const hiddenInput = document.getElementById('frequently_bought_input');
        const countBadge = document.getElementById('bundleCountBadge');

        if (hiddenInput) hiddenInput.value = selectedBundleItems.map(i => i.id).join(',');
        if (countBadge) countBadge.textContent = selectedBundleItems.length;

        if (!container) return;

        if (selectedBundleItems.length === 0) {
            container.innerHTML = `<p class="text-xs text-slate-400 italic py-2 px-2 text-center w-full">No bundle items selected. Search above to add items frequently bought together with this product.</p>`;
            return;
        }

        container.innerHTML = selectedBundleItems.map(p => `
            <div class="bg-white border border-slate-200 rounded-xl p-3 flex items-center justify-between shadow-2xs hover:border-slate-400 transition text-xs select-none group">
                <div class="flex items-center space-x-3">
                    <img src="${p.main_image}" onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'" class="w-10 h-10 rounded-lg object-contain bg-slate-50 border border-slate-100 p-1">
                    <div>
                        <p class="font-bold text-slate-900 truncate">${p.name}</p>
                        <p class="text-[10px] text-slate-500 font-mono">${p.price}</p>
                    </div>
                </div>
                <button type="button" onclick="removeBundleItem(${p.id})" title="Remove Item"
                    class="w-8 h-8 rounded-lg hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition flex items-center justify-center border-0 cursor-pointer">
                    <i data-lucide="trash-2" class="w-4 h-4 text-rose-500"></i>
                </button>
            </div>
        `).join('');

        if (window.lucide) lucide.createIcons();
    }

    function removeBundleItem(id) {
        selectedBundleItems = selectedBundleItems.filter(i => i.id !== id);
        renderSelectedBundleItems();
    }
    window.removeBundleItem = removeBundleItem;

    function addBundleItemById(id) {
        const item = searchMap[id];
        if (!item) return;
        if (selectedBundleItems.length >= 5) {
            showToast('Maximum 5 bundle items allowed.', 'error');
            return;
        }
        if (!selectedBundleItems.some(i => i.id === item.id)) {
            selectedBundleItems.push(item);
            renderSelectedBundleItems();
        }
        const drop = document.getElementById('bundleSearchResults');
        if (drop) drop.classList.add('hidden');
        const input = document.getElementById('bundleSearchInput');
        if (input) input.value = '';
    }
    window.addBundleItemById = addBundleItemById;

    function doBundleSearch(val) {
        const drop = document.getElementById('bundleSearchResults');
        if (!drop) return;

        const q = (val || '').trim();
        drop.innerHTML = '<div class="p-3 text-center text-slate-400 font-medium animate-pulse">Searching products...</div>';
        drop.classList.remove('hidden');

        clearTimeout(bundleSearchTimeout);
        bundleSearchTimeout = setTimeout(async () => {
            try {
                const res = await fetch(`<?= url('admin/products/search-api') ?>?q=${encodeURIComponent(q)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!res.ok) {
                    drop.innerHTML = `<div class="p-3 text-center text-rose-500 italic">Server error (${res.status}). Please refresh page.</div>`;
                    return;
                }

                const data = await res.json();

                if (data.success && data.items && data.items.length > 0) {
                    const filtered = data.items.filter(i => !selectedBundleItems.some(b => b.id === i.id));
                    if (filtered.length > 0) {
                        searchMap = {};
                        drop.innerHTML = filtered.map(p => {
                            searchMap[p.id] = p;
                            const safeName = (p.name || '').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                            const safeSku = (p.sku || 'N/A').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                            return `
                                <div class="p-3 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer"
                                    onclick="addBundleItemById(${p.id})">
                                    <div class="flex items-center space-x-3 min-w-0">
                                        <img src="${p.main_image}" onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'" class="w-9 h-9 rounded-lg object-contain bg-slate-50 border border-slate-200 p-0.5 shrink-0">
                                        <div class="min-w-0">
                                            <p class="font-bold text-slate-900 leading-tight truncate">${safeName}</p>
                                            <p class="text-[11px] text-slate-500 font-mono mt-0.5">SKU: ${safeSku} • <span class="text-slate-900 font-bold">${p.price}</span></p>
                                        </div>
                                    </div>
                                    <span class="text-[11px] font-bold px-2.5 py-1 rounded-lg bg-slate-900 hover:bg-black text-white transition shrink-0 ml-2">+ Add</span>
                                </div>
                            `;
                        }).join('');
                        drop.classList.remove('hidden');
                        return;
                    }
                }
                drop.innerHTML = `<div class="p-4 text-center text-slate-500 italic">No matching products found.</div>`;
                drop.classList.remove('hidden');
            } catch (e) {
                console.error('Search error:', e);
                drop.innerHTML = `<div class="p-3 text-center text-rose-500 italic">Search error: ${e.message}</div>`;
            }
        }, 120);
    }
    window.doBundleSearch = doBundleSearch;

    document.addEventListener('click', function (e) {
        const wrap = document.getElementById('bundleSearchWrapper');
        const drop = document.getElementById('bundleSearchResults');
        if (wrap && drop && !wrap.contains(e.target)) {
            drop.classList.add('hidden');
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        renderSelectedBundleItems();
    });

    // SAFE OEM PREVIEW CALCULATOR (NULL-CHECKED)
    (function () {
        var mudsorPrice = <?= (float) ($product['sale_price'] ?: $product['price']) ?>;
        function updatePreview() {
            var oemEl = document.getElementById('edit_oem_price');
            var oemVal = oemEl ? (parseFloat(oemEl.value) || 0) : 0;
            var oemPrice = oemVal > 0 ? oemVal : Math.round(mudsorPrice * 1.4);
            var saving = oemPrice - mudsorPrice;
            var savingPct = oemPrice > 0 ? Math.round((saving / oemPrice) * 100) : 0;

            var prevOem = document.getElementById('prev_oem_price');
            if (prevOem) {
                prevOem.textContent = '₹' + Math.round(oemPrice).toLocaleString('en-IN') + (oemVal <= 0 ? ' (est.)' : '');
            }

            var prevSaving = document.getElementById('prev_saving');
            if (prevSaving) {
                prevSaving.textContent = saving > 0
                    ? '✅ Save ' + savingPct + '% (₹' + Math.round(saving).toLocaleString('en-IN') + ')'
                    : 'No saving vs OEM';
            }

            var mwEl = document.getElementById('edit_warranty_months');
            var owEl = document.getElementById('edit_oem_warranty');
            var mw = mwEl ? mwEl.value : '';
            var ow = owEl ? owEl.value : '';

            var prevM = document.getElementById('prev_m_warranty');
            var prevO = document.getElementById('prev_o_warranty');
            if (prevM) prevM.textContent = mw ? mw + ' Months' : '—';
            if (prevO) prevO.textContent = ow ? ow + ' Months' : '—';
        }

        ['edit_oem_price', 'edit_warranty_months', 'edit_oem_warranty'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('input', updatePreview);
        });
        updatePreview();
    })();

    // API & AJAX HELPERS
    function apiPost(url, body) {
        const fd = new FormData();
        Object.entries(body).forEach(([k, v]) => fd.append(k, v));
        fd.append('_token', CSRF_TOKEN);
        fd.append('_csrf_token', CSRF_TOKEN);
        return fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-Token': CSRF_TOKEN },
            body: fd
        }).then(r => r.json());
    }
    window.apiPost = apiPost;

    // GALLERY IMAGE MANAGEMENT (DELETE & COVER SET)
    function confirmGalleryDelete(imageId, btn) {
        const totalItems = document.querySelectorAll('#galleryGrid .gallery-item').length;
        if (totalItems <= 1) {
            showToast('At least 1 product photo is required. Cannot delete the last remaining image.', 'error');
            return;
        }
        const itemCard = btn.closest('.gallery-item');
        const overlay = itemCard.querySelector('.gallery-confirm-overlay');
        if (overlay) overlay.classList.remove('hidden');
    }
    window.confirmGalleryDelete = confirmGalleryDelete;

    function cancelGalleryDelete(btn) {
        const overlay = btn.closest('.gallery-confirm-overlay');
        if (overlay) overlay.classList.add('hidden');
    }
    window.cancelGalleryDelete = cancelGalleryDelete;

    function executeGalleryDelete(imageId, btn) {
        const itemCard = btn.closest('.gallery-item');
        const overlay = btn.closest('.gallery-confirm-overlay');

        apiPost(`${BASE_URL}/admin/products/gallery-delete/${imageId}`, {}).then(data => {
            if (data.success) {
                itemCard.style.transform = 'scale(0.8)';
                itemCard.style.opacity = '0';
                setTimeout(() => {
                    itemCard.remove();
                    updateGalleryCountBadge();

                    if (data.new_primary_id) {
                        const newCard = document.querySelector(`.gallery-item[data-id="${data.new_primary_id}"]`);
                        if (newCard) {
                            newCard.classList.remove('border-slate-200', 'hover:border-slate-400');
                            newCard.classList.add('border-emerald-500', 'bg-emerald-50/20');

                            const radio = newCard.querySelector('input[type="radio"]');
                            if (radio) radio.checked = true;

                            const aspectBox = newCard.querySelector('.relative.aspect-square');
                            if (aspectBox && !aspectBox.querySelector('.cover-badge')) {
                                aspectBox.insertAdjacentHTML('beforeend', `<span class="absolute top-1 left-1 bg-emerald-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow-2xs cover-badge">COVER</span>`);
                            }
                        }
                    }
                }, 180);
                showToast(data.message || 'Photo deleted successfully', 'success');
            } else {
                if (overlay) overlay.classList.add('hidden');
                showToast(data.message || 'Error deleting photo', 'error');
            }
        }).catch(err => {
            if (overlay) overlay.classList.add('hidden');
            showToast('Network error while deleting image.', 'error');
        });
    }
    window.executeGalleryDelete = executeGalleryDelete;

    function updateGalleryCountBadge() {
        const count = document.querySelectorAll('#galleryGrid .gallery-item').length;
        const mainBadge = document.getElementById('galleryCountBadge');
        const tabBadge = document.getElementById('tabMediaBadge');
        if (mainBadge) mainBadge.textContent = `${count} Images Total`;
        if (tabBadge) tabBadge.textContent = count;
    }
    window.updateGalleryCountBadge = updateGalleryCountBadge;

    function setPrimaryCover(imageId) {
        apiPost(`${BASE_URL}/admin/products/gallery-set-primary/${imageId}`, {}).then(data => {
            if (data.success) {
                document.querySelectorAll('.gallery-item').forEach(card => {
                    const isTarget = parseInt(card.getAttribute('data-id')) === imageId;
                    const aspectBox = card.querySelector('.relative.aspect-square');
                    const badge = card.querySelector('.cover-badge');

                    if (isTarget) {
                        card.classList.remove('border-slate-200', 'hover:border-slate-400');
                        card.classList.add('border-emerald-500', 'bg-emerald-50/20');
                        if (!badge && aspectBox) {
                            aspectBox.insertAdjacentHTML('beforeend', `<span class="absolute top-1 left-1 bg-emerald-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow-2xs cover-badge">COVER</span>`);
                        }
                    } else {
                        card.classList.remove('border-emerald-500', 'bg-emerald-50/20');
                        card.classList.add('border-slate-200', 'hover:border-slate-400');
                        if (badge) badge.remove();
                    }
                });
                showToast('Cover photo updated', 'success');
            } else {
                showToast(data.message || 'Error setting cover photo', 'error');
            }
        });
    }
    window.setPrimaryCover = setPrimaryCover;

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
    window.loadSubcategories = loadSubcategories;

    function addTierRow() {
        const tbody = document.getElementById('tieredPricesBody');
        if (!tbody) return;
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td class="py-2 px-3">
                <input type="number" name="tier_min_qty[]" required min="1" placeholder="e.g. 10" class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold">
            </td>
            <td class="py-2 px-3">
                <input type="number" name="tier_max_qty[]" placeholder="e.g. 50 (or leave blank)" class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold">
            </td>
            <td class="py-2 px-3">
                <input type="number" step="0.01" name="tier_unit_price[]" required placeholder="e.g. 320.00" class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bold">
            </td>
            <td class="py-2 px-3 text-right">
                <button type="button" onclick="this.closest('tr').remove()" class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
            </td>
        `;
        tbody.appendChild(tr);
        if (window.lucide) lucide.createIcons();
    }
    window.addTierRow = addTierRow;

    // =========================================================================
    // 1. VARIANTS MODAL POPUP (ADD, EDIT, DELETE)
    // =========================================================================
    function openVariantModal() {
        const modal = document.getElementById('variantModal');
        if (!modal) return;

        const titleEl = document.getElementById('variantModalTitle');
        const idEl = document.getElementById('v_variant_id');
        const labelEl = document.getElementById('v_attribute_label');
        const valEl = document.getElementById('v_attribute_value');
        const codeEl = document.getElementById('v_variant_code');
        const stockEl = document.getElementById('v_stock_quantity');
        const wPriceEl = document.getElementById('v_wholesale_price');
        const oPriceEl = document.getElementById('v_one_piece_price');
        const weightEl = document.getElementById('v_weight');
        const dimEl = document.getElementById('v_dimensions');
        const imgEl = document.getElementById('v_image_url');
        const fileInp = document.getElementById('v_image_file');
        const activeEl = document.getElementById('v_is_active');

        if (titleEl) titleEl.innerText = 'Add Product Variant';
        if (idEl) idEl.value = '0';
        if (labelEl) labelEl.value = 'Color / Style';
        if (valEl) valEl.value = '';
        if (codeEl) codeEl.value = '';
        if (stockEl) stockEl.value = '50';
        if (wPriceEl) wPriceEl.value = '<?= (float)($product['price'] ?? 0) ?>';
        if (oPriceEl) oPriceEl.value = '<?= (float)($product['sale_price'] ?: $product['price'] ?: 0) ?>';
        if (weightEl) weightEl.value = '';
        if (dimEl) dimEl.value = '';
        if (imgEl) imgEl.value = '';
        if (fileInp) fileInp.value = '';
        if (activeEl) activeEl.checked = true;

        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }
    window.openVariantModal = openVariantModal;

    function openEditVariantFromBtn(btn) {
        const jsonStr = btn.getAttribute('data-variant');
        if (!jsonStr) return;
        try {
            const v = JSON.parse(jsonStr);
            editVariant(v);
        } catch(e) {
            console.error('Error parsing variant JSON:', e);
            showToast('Error reading variant details', 'error');
        }
    }
    window.openEditVariantFromBtn = openEditVariantFromBtn;

    function editVariant(v) {
        const modal = document.getElementById('variantModal');
        if (!modal) return;

        const titleEl = document.getElementById('variantModalTitle');
        const idEl = document.getElementById('v_variant_id');
        const labelEl = document.getElementById('v_attribute_label');
        const valEl = document.getElementById('v_attribute_value');
        const codeEl = document.getElementById('v_variant_code');
        const stockEl = document.getElementById('v_stock_quantity');
        const wPriceEl = document.getElementById('v_wholesale_price');
        const oPriceEl = document.getElementById('v_one_piece_price');
        const weightEl = document.getElementById('v_weight');
        const dimEl = document.getElementById('v_dimensions');
        const imgEl = document.getElementById('v_image_url');
        const fileInp = document.getElementById('v_image_file');
        const activeEl = document.getElementById('v_is_active');

        if (titleEl) titleEl.innerText = 'Edit Product Variant';
        if (idEl) idEl.value = v.id || 0;
        if (labelEl) labelEl.value = v.attribute_label || 'Variant';
        if (valEl) valEl.value = v.attribute_value || '';
        if (codeEl) codeEl.value = v.variant_code || '';
        if (stockEl) stockEl.value = v.stock_quantity || 0;
        if (wPriceEl) wPriceEl.value = v.wholesale_price || 0;
        if (oPriceEl) oPriceEl.value = v.one_piece_price || 0;
        if (weightEl) weightEl.value = v.weight || '';
        if (dimEl) dimEl.value = v.dimensions || '';
        if (imgEl) imgEl.value = v.image_url || '';
        if (fileInp) fileInp.value = '';
        if (activeEl) activeEl.checked = parseInt(v.is_active) === 1;

        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }
    window.editVariant = editVariant;

    function closeVariantModal() {
        const modal = document.getElementById('variantModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    }
    window.closeVariantModal = closeVariantModal;

    async function submitVariantForm(e) {
        if (e) e.preventDefault();
        const fd = new FormData();
        fd.append('_token', CSRF_TOKEN);
        fd.append('_csrf_token', CSRF_TOKEN);
        fd.append('variant_id', document.getElementById('v_variant_id').value);
        fd.append('attribute_label', document.getElementById('v_attribute_label').value);
        fd.append('attribute_value', document.getElementById('v_attribute_value').value);
        fd.append('variant_code', document.getElementById('v_variant_code').value);
        fd.append('stock_quantity', document.getElementById('v_stock_quantity').value);
        fd.append('wholesale_price', document.getElementById('v_wholesale_price').value);
        fd.append('one_piece_price', document.getElementById('v_one_piece_price').value);
        fd.append('weight', document.getElementById('v_weight').value);
        fd.append('dimensions', document.getElementById('v_dimensions').value);
        fd.append('image_url', document.getElementById('v_image_url').value);
        fd.append('is_active', document.getElementById('v_is_active').checked ? 1 : 0);

        const fileInp = document.getElementById('v_image_file');
        if (fileInp && fileInp.files && fileInp.files[0]) {
            fd.append('variant_image_file', fileInp.files[0]);
        }

        try {
            const res = await fetch(`${BASE_URL}/admin/products/${PRODUCT_ID}/variants/save`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': CSRF_TOKEN },
                body: fd
            });
            const d = await res.json();
            if (d.success) {
                closeVariantModal();
                showToast(d.message || 'Variant saved successfully', 'success');
                setTimeout(() => location.reload(), 300);
            } else {
                showToast(d.message || 'Error saving variant', 'error');
            }
        } catch(err) {
            showToast('Network error while saving variant', 'error');
        }
    }
    window.submitVariantForm = submitVariantForm;

    function confirmDeleteVariant(vid, btn) {
        const tr = btn ? btn.closest('tr') : document.querySelector(`tr[data-variant-id="${vid}"]`);
        const doDelete = async () => {
            const payload = new URLSearchParams();
            payload.append('_token', CSRF_TOKEN);
            payload.append('_csrf_token', CSRF_TOKEN);

            try {
                const res = await fetch(`${BASE_URL}/admin/products/variants/delete/${vid}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': CSRF_TOKEN },
                    body: payload
                });
                const data = await res.json();
                if (data.success) {
                    if (tr) {
                        tr.style.transform = 'scale(0.95)';
                        tr.style.opacity = '0';
                        setTimeout(() => tr.remove(), 200);
                    }
                    showToast(data.message || 'Variant deleted successfully', 'success');
                } else {
                    showToast(data.message || 'Error deleting variant', 'error');
                }
            } catch(err) {
                showToast('Network error while deleting variant', 'error');
            }
        };

        if (window.showConfirmModal) {
            window.showConfirmModal({
                title: 'Delete Variant',
                message: 'Are you sure you want to delete this variant permanently?',
                confirmText: 'Delete',
                onConfirm: doDelete
            });
        } else if (confirm('Are you sure you want to delete this variant permanently?')) {
            doDelete();
        }
    }
    window.confirmDeleteVariant = confirmDeleteVariant;

    // =========================================================================
    // 2. SPECIFICATION MODAL POPUP (ADD, EDIT, DELETE)
    // =========================================================================
    function openAddSpecModal() {
        const modal = document.getElementById('specModal');
        if (!modal) return;
        const titleEl = document.getElementById('specModalTitle');
        const idEl = document.getElementById('s_spec_id');
        const keyEl = document.getElementById('s_spec_key');
        const valEl = document.getElementById('s_spec_value');

        if (titleEl) titleEl.innerText = 'Add Product Specification';
        if (idEl) idEl.value = '0';
        if (keyEl) keyEl.value = '';
        if (valEl) valEl.value = '';

        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }
    window.openAddSpecModal = openAddSpecModal;

    function openEditSpecModal(specId, btn) {
        const modal = document.getElementById('specModal');
        if (!modal) return;
        const tr = btn ? btn.closest('tr') : document.querySelector(`tr[data-spec-id="${specId}"]`);
        const key = tr ? (tr.querySelector('.spec-key-cell')?.textContent.trim() || '') : '';
        const val = tr ? (tr.querySelector('.spec-value-cell')?.textContent.trim() || '') : '';

        const titleEl = document.getElementById('specModalTitle');
        const idEl = document.getElementById('s_spec_id');
        const keyEl = document.getElementById('s_spec_key');
        const valEl = document.getElementById('s_spec_value');

        if (titleEl) titleEl.innerText = 'Edit Product Specification';
        if (idEl) idEl.value = specId;
        if (keyEl) keyEl.value = key;
        if (valEl) valEl.value = val;

        modal.style.display = 'flex';
        modal.classList.remove('hidden');
    }
    window.openEditSpecModal = openEditSpecModal;

    function closeSpecModal() {
        const modal = document.getElementById('specModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }
    }
    window.closeSpecModal = closeSpecModal;

    async function submitSpecForm(e) {
        if (e) e.preventDefault();
        const specId = parseInt(document.getElementById('s_spec_id').value) || 0;
        const key = document.getElementById('s_spec_key').value.trim();
        const val = document.getElementById('s_spec_value').value.trim();

        if (!key || !val) {
            showToast('Both key and value are required.', 'error');
            return;
        }

        const payload = new URLSearchParams();
        payload.append('_token', CSRF_TOKEN);
        payload.append('_csrf_token', CSRF_TOKEN);
        payload.append('spec_id', specId);
        payload.append('spec_key', key);
        payload.append('spec_value', val);

        try {
            const res = await fetch(`${BASE_URL}/admin/products/${PRODUCT_ID}/specs/save`, {
                method: 'POST',
                headers: { 'X-CSRF-Token': CSRF_TOKEN },
                body: payload
            });
            const d = await res.json();
            if (d.success) {
                closeSpecModal();
                showToast(d.message || 'Specification saved successfully', 'success');

                const tbody = document.getElementById('specsTableBody');
                const noMsg = document.getElementById('noSpecsRow');
                if (noMsg) noMsg.remove();

                if (specId > 0) {
                    const existingRow = tbody.querySelector(`tr[data-spec-id="${specId}"]`);
                    if (existingRow) {
                        existingRow.querySelector('.spec-key-cell').textContent = key;
                        existingRow.querySelector('.spec-value-cell').textContent = val;
                    }
                } else {
                    const newId = d.spec_id || Date.now();
                    const tr = document.createElement('tr');
                    tr.className = 'hover:bg-slate-50 transition spec-row';
                    tr.setAttribute('data-spec-id', newId);
                    tr.innerHTML = `
                        <td class="py-3 px-4 font-bold text-slate-900 spec-key-cell">${escapeHtml(key)}</td>
                        <td class="py-3 px-4 font-medium text-slate-700 spec-value-cell">${escapeHtml(val)}</td>
                        <td class="py-3 px-4 text-right space-x-1 whitespace-nowrap">
                            <button type="button" data-action="edit-spec" data-spec-id="${newId}"
                                onclick="window.openEditSpecModal(${newId}, this); return false;"
                                class="p-1.5 bg-slate-100 text-slate-700 hover:bg-slate-200 rounded-lg transition border border-slate-200 cursor-pointer" title="Edit Specification">
                                <i data-lucide="edit-3" class="w-3.5 h-3.5 pointer-events-none"></i>
                            </button>
                            <button type="button" data-action="delete-spec" data-spec-id="${newId}"
                                onclick="window.confirmDeleteSpec(${newId}, this); return false;"
                                class="p-1.5 bg-rose-50 text-rose-600 hover:bg-rose-100 rounded-lg transition border border-rose-200 cursor-pointer" title="Delete Specification">
                                <i data-lucide="trash-2" class="w-4 h-4 pointer-events-none"></i>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(tr);
                    if (window.lucide) lucide.createIcons();
                }
            } else {
                showToast(d.message || 'Error saving specification', 'error');
            }
        } catch(err) {
            showToast('Network error while saving specification', 'error');
        }
    }
    window.submitSpecForm = submitSpecForm;

    function confirmDeleteSpec(specId, btn) {
        const tr = btn ? btn.closest('tr') : document.querySelector(`tr[data-spec-id="${specId}"]`);
        const doDelete = async () => {
            const payload = new URLSearchParams();
            payload.append('_token', CSRF_TOKEN);
            payload.append('_csrf_token', CSRF_TOKEN);

            try {
                const res = await fetch(`${BASE_URL}/admin/products/specs/delete/${specId}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': CSRF_TOKEN },
                    body: payload
                });
                const data = await res.json();
                if (data.success) {
                    if (tr) {
                        tr.style.transform = 'scale(0.95)';
                        tr.style.opacity = '0';
                        setTimeout(() => tr.remove(), 180);
                    }
                    showToast(data.message || 'Specification deleted successfully', 'success');
                } else {
                    showToast(data.message || 'Error deleting specification', 'error');
                }
            } catch(err) {
                showToast('Network error while deleting specification', 'error');
            }
        };

        if (window.showConfirmModal) {
            window.showConfirmModal({
                title: 'Delete Specification',
                message: 'Delete this specification permanently?',
                confirmText: 'Delete',
                onConfirm: doDelete
            });
        } else if (confirm('Delete this specification permanently?')) {
            doDelete();
        }
    }
    window.confirmDeleteSpec = confirmDeleteSpec;

    function escapeHtml(str) {
        return (str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    window.escapeHtml = escapeHtml;

    // EVENT DELEGATION LISTENER: GUARANTEES BUTTON CLICKS WORK ACROSS TAB SWITCHES AND DYNAMIC DOM MODIFICATIONS
    document.addEventListener('click', function (e) {
        // 1. Add Variant
        const addVarBtn = e.target.closest('[data-action="add-variant"]');
        if (addVarBtn) {
            window.openVariantModal();
            return;
        }

        // 2. Edit Variant
        const editVarBtn = e.target.closest('[data-action="edit-variant"]');
        if (editVarBtn) {
            window.openEditVariantFromBtn(editVarBtn);
            return;
        }

        // 3. Delete Variant
        const delVarBtn = e.target.closest('[data-action="delete-variant"]');
        if (delVarBtn) {
            const vid = parseInt(delVarBtn.getAttribute('data-variant-id'));
            if (vid > 0) window.confirmDeleteVariant(vid, delVarBtn);
            return;
        }

        // 4. Add Spec
        const addSpecBtn = e.target.closest('[data-action="add-spec"]');
        if (addSpecBtn) {
            window.openAddSpecModal();
            return;
        }

        // 5. Edit Spec
        const editSpecBtn = e.target.closest('[data-action="edit-spec"]');
        if (editSpecBtn) {
            const specId = parseInt(editSpecBtn.getAttribute('data-spec-id'));
            if (specId > 0) window.openEditSpecModal(specId, editSpecBtn);
            return;
        }

        // 6. Delete Spec
        const delSpecBtn = e.target.closest('[data-action="delete-spec"]');
        if (delSpecBtn) {
            const specId = parseInt(delSpecBtn.getAttribute('data-spec-id'));
            if (specId > 0) window.confirmDeleteSpec(specId, delSpecBtn);
            return;
        }
    });
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>