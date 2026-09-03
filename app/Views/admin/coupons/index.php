<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="space-y-6">

    <!-- Top Header -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span
                    class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-red-50 text-red-600 rounded-lg tracking-wider border border-red-100">
                    Marketing & Discounts
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Promo Coupons</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Discount Coupons & Offers Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Manage promo codes, percentage discounts, flat rate off offers, minimum order values, and category
                rules.
            </p>
        </div>

        <a href="<?= url('admin/coupons/create') ?>"
            class="inline-flex items-center space-x-2 h-10 px-5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Create New Coupon</span>
        </a>
    </div>

    <!-- Flash Notifications -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div
            class="bg-green-50 border border-green-200 text-green-800 text-xs font-semibold px-4 py-3 rounded-xl flex items-center space-x-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-green-600 shrink-0"></i>
            <span><?= htmlspecialchars($_SESSION['flash_success']);
            unset($_SESSION['flash_success']); ?></span>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_error'])): ?>
        <div
            class="bg-red-50 border border-red-200 text-red-800 text-xs font-semibold px-4 py-3 rounded-xl flex items-center space-x-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
            <span><?= htmlspecialchars($_SESSION['flash_error']);
            unset($_SESSION['flash_error']); ?></span>
        </div>
    <?php endif; ?>

    <!-- Coupons Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-5">Coupon Code</th>
                        <th class="py-4 px-5">Discount</th>
                        <th class="py-4 px-5">Scope</th>
                        <th class="py-4 px-5">Min Order</th>
                        <th class="py-4 px-5">Usage (Used / Limit)</th>
                        <th class="py-4 px-5">Validity</th>
                        <th class="py-4 px-5 text-center">Status</th>
                        <th class="py-4 px-5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($coupons)): ?>
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="ticket-slash" class="w-6 h-6"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">No discount coupons created yet.</p>
                                <p class="text-xs text-slate-400 mt-1">Click "Create New Coupon" to set up your first promo offer.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($coupons as $c): ?>
                            <tr class="hover:bg-slate-50/60 transition duration-150">
                                <!-- Code -->
                                <td class="py-4 px-5">
                                    <div class="flex items-center space-x-2">
                                        <span class="font-mono font-bold text-xs bg-red-50 text-red-600 px-3 py-1 rounded-xl border border-red-100 tracking-wider shadow-2xs">
                                            <?= htmlspecialchars($c['code']) ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($c['description'])): ?>
                                        <p class="text-[11px] text-slate-400 mt-1 line-clamp-1">
                                            <?= htmlspecialchars($c['description']) ?>
                                        </p>
                                    <?php endif; ?>
                                </td>

                                <!-- Discount -->
                                <td class="py-4 px-5 font-semibold text-slate-900">
                                    <?php if ($c['discount_type'] === 'percentage'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            <?= (float) $c['discount_value'] ?>% OFF
                                        </span>
                                        <?php if ($c['max_discount_cap']): ?>
                                            <span class="block text-[10px] text-slate-400 font-normal mt-0.5">
                                                Cap: <?= format_price($c['max_discount_cap']) ?>
                                            </span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200/60">
                                            <?= format_price($c['discount_value']) ?> FLAT OFF
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Scope -->
                                <td class="py-4 px-5">
                                    <?php if ($c['scope_type'] === 'specific_products'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200/80">
                                            Products
                                        </span>
                                    <?php elseif ($c['scope_type'] === 'specific_categories'): ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200/80">
                                            Categories
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            All Products
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Min Order -->
                                <td class="py-4 px-5 font-semibold text-slate-800">
                                    <?= $c['min_order_value'] > 0 ? format_price($c['min_order_value']) : '<span class="text-slate-400 font-normal">None</span>' ?>
                                </td>

                                <!-- Usage -->
                                <td class="py-4 px-5">
                                    <a href="<?= url("admin/coupons/usage/{$c['id']}") ?>"
                                        class="hover:text-red-600 font-semibold text-slate-900 inline-flex items-center space-x-1 transition"
                                        title="View redemption report">
                                        <span class="font-bold text-slate-900"><?= (int) $c['usage_count'] ?></span>
                                        <span class="text-slate-400 font-normal">/ <?= $c['usage_limit_total'] !== null ? (int) $c['usage_limit_total'] : '∞' ?></span>
                                    </a>
                                </td>

                                <!-- Validity -->
                                <td class="py-4 px-5 text-[11px] text-slate-500 space-y-0.5">
                                    <div><span class="text-slate-400">From:</span> <?= $c['valid_from'] ? date('d M Y', strtotime($c['valid_from'])) : 'Now' ?></div>
                                    <div><span class="text-slate-400">Until:</span> <?= $c['valid_until'] ? date('d M Y', strtotime($c['valid_until'])) : 'No Expiry' ?></div>
                                </td>

                                <!-- Status Toggle -->
                                <td class="py-4 px-5 text-center">
                                    <form action="<?= url("admin/coupons/toggle/{$c['id']}") ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <button type="submit"
                                            class="px-3 py-1 rounded-full text-[10px] font-bold tracking-wider transition cursor-pointer shadow-2xs <?= $c['is_active'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border border-slate-200 hover:bg-slate-200' ?>">
                                            <?= $c['is_active'] ? 'ACTIVE' : 'INACTIVE' ?>
                                        </button>
                                    </form>
                                </td>

                                <!-- Actions -->
                                <td class="py-4 px-5 text-right">
                                    <div class="flex items-center justify-end space-x-1">
                                        <a href="<?= url("admin/coupons/usage/{$c['id']}") ?>"
                                            class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition"
                                            title="Redemption Log">
                                            <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                                        </a>
                                        <a href="<?= url("admin/coupons/edit/{$c['id']}") ?>"
                                            class="p-2 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-xl transition"
                                            title="Edit Coupon">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <form action="<?= url("admin/coupons/delete/{$c['id']}") ?>" method="POST"
                                            class="inline-block"
                                            onsubmit="return confirm('Are you sure you want to delete this coupon?');">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-xl transition cursor-pointer"
                                                title="Delete Coupon">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>