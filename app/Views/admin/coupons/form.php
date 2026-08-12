<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php
$isEdit = !empty($coupon['id']);
$formAction = $isEdit ? url("admin/coupons/update/{$coupon['id']}") : url('admin/coupons/store');
?>

<div class="space-y-6 max-w-4xl mx-auto">

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex text-xs text-gray-400 space-x-2 mb-1">
                <a href="<?= url('admin/coupons') ?>" class="hover:text-gray-900 transition">Coupons</a>
                <span>/</span>
                <span class="text-gray-700"><?= $isEdit ? 'Edit Coupon' : 'Create Coupon' ?></span>
            </nav>
            <h1 class="text-xl font-semibold text-gray-900 flex items-center space-x-2">
                <i data-lucide="ticket" class="w-5 h-5 text-red-600"></i>
                <span><?= $isEdit ? 'Edit Coupon: ' . htmlspecialchars($coupon['code']) : 'Create New Coupon' ?></span>
            </h1>
        </div>
        <a href="<?= url('admin/coupons') ?>"
            class="inline-flex items-center space-x-1 text-xs font-semibold text-gray-600 hover:text-gray-900 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Coupons</span>
        </a>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl border border-gray-900 shadow-sm overflow-hidden p-6">
        <form action="<?= $formAction ?>" method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Basic Info Section -->
            <div class="space-y-4">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2">
                    1. Coupon Code & Description</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Code -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Coupon Code <span
                                class="text-red-500">*</span></label>
                        <div class="flex space-x-2">
                            <input type="text" id="coupon_code" name="code"
                                value="<?= htmlspecialchars($coupon['code'] ?? '') ?>" placeholder="e.g. MUDSOR20"
                                uppercase required
                                class="flex-1 h-10 px-3 border border-gray-900 rounded-xl text-xs uppercase font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                            <button type="button" onclick="generateRandomCode()"
                                class="px-3 bg-gray-100 hover:bg-gray-900 text-gray-700 font-semibold text-xs rounded-xl transition flex items-center space-x-1"
                                title="Generate Random Code">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-amber-500"></i>
                                <span>Generate</span>
                            </button>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Status</label>
                        <div class="pt-2">
                            <label class="inline-flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="is_active" value="1" <?= (!isset($coupon['is_active']) || $coupon['is_active'] == 1) ? 'checked' : '' ?>
                                    class="w-4 h-4 rounded text-red-600 focus:ring-red-500">
                                <span class="text-xs font-semibold text-gray-800">Active (Available for
                                    redemption)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Internal Description / Promo
                            Terms</label>
                        <input type="text" name="description"
                            value="<?= htmlspecialchars($coupon['description'] ?? '') ?>"
                            placeholder="e.g. 20% off on crash guards for new users"
                            class="w-full h-10 px-3 border border-gray-900 rounded-xl text-xs focus:outline-none focus:border-red-600">
                    </div>
                </div>
            </div>

            <!-- Discount Configuration Section -->
            <div class="space-y-4 pt-4 border-t border-gray-100">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2">
                    2. Discount Value & Limits</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Discount Type -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Discount Type</label>
                        <select name="discount_type" id="discount_type" onchange="toggleDiscountCap()"
                            class="w-full h-10 px-3 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                            <option value="flat" <?= ($coupon['discount_type'] ?? '') === 'flat' ? 'selected' : '' ?>>Flat
                                ₹ Off</option>
                            <option value="percentage" <?= ($coupon['discount_type'] ?? '') === 'percentage' ? 'selected' : '' ?>>Percentage % Off</option>
                        </select>
                    </div>

                    <!-- Discount Value -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Discount Value <span
                                class="text-red-500">*</span></label>
                        <input type="number" step="0.01" name="discount_value"
                            value="<?= htmlspecialchars($coupon['discount_value'] ?? '') ?>"
                            placeholder="e.g. 150 or 20" required
                            class="w-full h-10 px-3 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                    </div>

                    <!-- Max Discount Cap (Shown for Percentage) -->
                    <div id="cap_container"
                        class="<?= ($coupon['discount_type'] ?? '') === 'percentage' ? '' : 'hidden' ?>">
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Max Discount Cap (₹) <span
                                class="text-gray-400 font-normal">(Optional)</span></label>
                        <input type="number" step="0.01" name="max_discount_cap"
                            value="<?= htmlspecialchars($coupon['max_discount_cap'] ?? '') ?>" placeholder="e.g. 500"
                            class="w-full h-10 px-3 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                    </div>

                    <!-- Min Order Value -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Min Order Value (₹)</label>
                        <input type="number" step="0.01" name="min_order_value"
                            value="<?= htmlspecialchars($coupon['min_order_value'] ?? '0') ?>" placeholder="e.g. 999"
                            class="w-full h-10 px-3 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                    </div>

                    <!-- Total Usage Limit -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Total Usage Limit <span
                                class="text-gray-400 font-normal">(Blank = Unlimited)</span></label>
                        <input type="number" name="usage_limit_total"
                            value="<?= htmlspecialchars($coupon['usage_limit_total'] ?? '') ?>" placeholder="e.g. 100"
                            class="w-full h-10 px-3 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                    </div>

                    <!-- Per User Limit -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Limit Per Customer</label>
                        <input type="number" name="usage_limit_per_user"
                            value="<?= htmlspecialchars($coupon['usage_limit_per_user'] ?? '1') ?>" min="1"
                            placeholder="e.g. 1"
                            class="w-full h-10 px-3 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                    </div>
                </div>
            </div>

            <!-- Date Validity Section -->
            <div class="space-y-4 pt-4 border-t border-gray-100">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2">
                    3. Validity Period</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Valid From</label>
                        <input type="datetime-local" name="valid_from"
                            value="<?= !empty($coupon['valid_from']) ? date('Y-m-d\TH:i', strtotime($coupon['valid_from'])) : date('Y-m-d\TH:i') ?>"
                            class="w-full h-10 px-3 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Valid Until <span
                                class="text-gray-400 font-normal">(Leave blank for no expiration)</span></label>
                        <input type="datetime-local" name="valid_until"
                            value="<?= !empty($coupon['valid_until']) ? date('Y-m-d\TH:i', strtotime($coupon['valid_until'])) : '' ?>"
                            class="w-full h-10 px-3 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                    </div>
                </div>
            </div>

            <!-- Scope Selector Section -->
            <div class="space-y-4 pt-4 border-t border-gray-100">
                <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100 pb-2">
                    4. Applicable Scope</h3>

                <div class="space-y-3">
                    <div class="flex items-center space-x-6">
                        <label class="inline-flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="scope_type" value="all_products" onchange="toggleScopeView()"
                                <?= (empty($coupon['scope_type']) || $coupon['scope_type'] === 'all_products') ? 'checked' : '' ?> class="text-red-600 focus:ring-red-500">
                            <span class="text-xs font-semibold text-gray-800">All Products</span>
                        </label>
                        <label class="inline-flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="scope_type" value="specific_products" onchange="toggleScopeView()"
                                <?= ($coupon['scope_type'] ?? '') === 'specific_products' ? 'checked' : '' ?>
                                class="text-red-600 focus:ring-red-500">
                            <span class="text-xs font-semibold text-gray-800">Specific Products</span>
                        </label>
                        <label class="inline-flex items-center space-x-2 cursor-pointer">
                            <input type="radio" name="scope_type" value="specific_categories"
                                onchange="toggleScopeView()" <?= ($coupon['scope_type'] ?? '') === 'specific_categories' ? 'checked' : '' ?> class="text-red-600 focus:ring-red-500">
                            <span class="text-xs font-semibold text-gray-800">Specific Categories</span>
                        </label>
                    </div>

                    <!-- Products Picker -->
                    <div id="scope_products_container"
                        class="hidden border border-gray-900 rounded-xl p-4 bg-gray-50/50 space-y-2">
                        <label class="block text-xs font-semibold text-gray-700">Select Eligible Products:</label>
                        <div
                            class="max-h-48 overflow-y-auto space-y-1 bg-white p-3 rounded-lg border border-gray-900 text-xs">
                            <?php foreach ($products as $p): ?>
                                <label class="flex items-center space-x-2 hover:bg-gray-50 p-1 rounded cursor-pointer">
                                    <input type="checkbox" name="product_ids[]" value="<?= $p['id'] ?>"
                                        <?= in_array($p['id'], $selectedProductIds, true) ? 'checked' : '' ?>
                                        class="rounded text-red-600 focus:ring-red-500">
                                    <span class="font-semibold text-gray-900"><?= htmlspecialchars($p['name']) ?></span>
                                    <span class="text-gray-400 font-mono text-[10px]">(SKU:
                                        <?= htmlspecialchars($p['sku']) ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Categories Picker -->
                    <div id="scope_categories_container"
                        class="hidden border border-gray-900 rounded-xl p-4 bg-gray-50/50 space-y-2">
                        <label class="block text-xs font-semibold text-gray-700">Select Eligible Categories:</label>
                        <div
                            class="grid grid-cols-2 sm:grid-cols-3 gap-2 bg-white p-3 rounded-lg border border-gray-900 text-xs">
                            <?php foreach ($categories as $cat): ?>
                                <label class="flex items-center space-x-2 hover:bg-gray-50 p-1.5 rounded cursor-pointer">
                                    <input type="checkbox" name="category_ids[]" value="<?= $cat['id'] ?>"
                                        <?= in_array($cat['id'], $selectedCategoryIds, true) ? 'checked' : '' ?>
                                        class="rounded text-red-600 focus:ring-red-500">
                                    <span class="font-semibold text-gray-800"><?= htmlspecialchars($cat['name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
                <a href="<?= url('admin/coupons') ?>"
                    class="h-10 px-5 bg-gray-100 hover:bg-gray-900 text-gray-700 font-semibold text-xs rounded-xl transition flex items-center">Cancel</a>
                <button type="submit"
                    class="h-10 px-6 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl shadow-md transition transform active:scale-95 flex items-center space-x-1">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span><?= $isEdit ? 'Update Coupon' : 'Save Coupon' ?></span>
                </button>
            </div>

        </form>
    </div>

</div>

<script>
    function generateRandomCode() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let result = 'MUDSOR';
        for (let i = 0; i < 4; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('coupon_code').value = result;
    }

    function toggleDiscountCap() {
        const type = document.getElementById('discount_type').value;
        const container = document.getElementById('cap_container');
        if (type === 'percentage') {
            container.classList.remove('hidden');
        } else {
            container.classList.add('hidden');
        }
    }

    function toggleScopeView() {
        const scope = document.querySelector('input[name="scope_type"]:checked').value;
        const prodCont = document.getElementById('scope_products_container');
        const catCont = document.getElementById('scope_categories_container');

        if (scope === 'specific_products') {
            prodCont.classList.remove('hidden');
            catCont.classList.add('hidden');
        } else if (scope === 'specific_categories') {
            catCont.classList.remove('hidden');
            prodCont.classList.add('hidden');
        } else {
            prodCont.classList.add('hidden');
            catCont.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        toggleDiscountCap();
        toggleScopeView();
    });
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>