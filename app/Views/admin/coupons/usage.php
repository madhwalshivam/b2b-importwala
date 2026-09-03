<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex items-center justify-between">
        <div>
            <nav class="flex text-xs text-gray-400 space-x-2 mb-1">
                <a href="<?= url('admin/coupons') ?>" class="hover:text-gray-900 transition">Coupons</a>
                <span>/</span>
                <span class="text-gray-700">Redemption History</span>
            </nav>
            <h1 class="text-xl font-semibold text-gray-900 flex items-center space-x-2">
                <i data-lucide="bar-chart-2" class="w-5 h-5 text-red-600"></i>
                <span>Redemption Log: <span
                        class="font-mono text-red-600 bg-red-50 px-2 py-0.5 rounded border border-red-200 uppercase"><?= htmlspecialchars($coupon['code']) ?></span></span>
            </h1>
        </div>
        <a href="<?= url('admin/coupons') ?>"
            class="inline-flex items-center space-x-1 text-xs font-semibold text-gray-600 hover:text-gray-900 transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Coupons</span>
        </a>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-1">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Redemptions</span>
            <div class="text-2xl font-extrabold text-slate-900"><?= count($redemptions) ?></div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-1">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Discount Type</span>
            <div class="text-lg font-bold text-slate-800 uppercase">
                <?= $coupon['discount_type'] === 'percentage' ? ((float) $coupon['discount_value'] . '% OFF') : (format_price($coupon['discount_value']) . ' FLAT') ?>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-xs space-y-1">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Scope</span>
            <div class="text-lg font-bold text-slate-800 capitalize">
                <?= str_replace('_', ' ', $coupon['scope_type']) ?>
            </div>
        </div>
    </div>

    <!-- Usage Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-[11px] font-bold text-slate-500 uppercase tracking-wider">
                        <th class="py-4 px-5">Used Date & Time</th>
                        <th class="py-4 px-5">Order Reference</th>
                        <th class="py-4 px-5">Customer</th>
                        <th class="py-4 px-5">Order Total</th>
                        <th class="py-4 px-5 text-right">Discount Given</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    <?php if (empty($redemptions)): ?>
                        <tr>
                            <td colspan="5" class="py-16 text-center text-slate-400">
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="inbox" class="w-6 h-6"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">No customer redemptions logged for this coupon yet.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($redemptions as $r): ?>
                            <tr class="hover:bg-slate-50/60 transition duration-150">
                                <td class="py-4 px-5 font-semibold text-slate-600">
                                    <?= date('d M Y, h:i A', strtotime($r['used_at'])) ?>
                                </td>
                                <td class="py-4 px-5 font-bold text-slate-900">
                                    <?= !empty($r['order_number']) ? htmlspecialchars($r['order_number']) : ('#Order ' . $r['order_id']) ?>
                                </td>
                                <td class="py-4 px-5">
                                    <div class="font-semibold text-slate-800">
                                        <?= htmlspecialchars($r['customer_name'] ?? 'Guest Customer') ?>
                                    </div>
                                    <div class="text-[10px] text-slate-400">
                                        <?= htmlspecialchars($r['customer_email'] ?? ($r['session_id'] ? 'Session: ' . substr($r['session_id'], 0, 12) . '...' : '')) ?>
                                    </div>
                                </td>
                                <td class="py-4 px-5 font-semibold text-slate-700">
                                    <?= !empty($r['total_amount']) ? format_price($r['total_amount']) : '-' ?>
                                </td>
                                <td class="py-4 px-5 text-right font-bold text-emerald-600">
                                    -<?= format_price($r['discount_applied']) ?>
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