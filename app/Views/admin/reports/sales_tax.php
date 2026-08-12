<?php
include __DIR__ . '/../layouts/header.php';

// Prepare query string without 'url' param to prevent routing collision
$queryParams = $_GET;
unset($queryParams['url']);
$queryString = http_build_query($queryParams);
?>

<div class="space-y-6 font-sans pb-12">

    <!-- Top Header Banner Card -->
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                <i data-lucide="file-text" class="w-6 h-6"></i>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 text-[10px] font-bold uppercase bg-red-50 text-red-600 rounded-md tracking-wider border border-red-100">
                        Financial Intelligence
                    </span>
                    <span class="text-slate-400 text-xs">•</span>
                    <span class="text-xs text-slate-500 font-medium"><?= htmlspecialchars($filter_label) ?></span>
                </div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight mt-0.5">Monthly Sales & Tax Report</h1>
                <p class="text-xs text-slate-500 mt-0.5">GST tax collection breakdown, gross & net sales summary, and itemized transaction log.</p>
            </div>
        </div>

        <!-- Export Action Buttons -->
        <div class="flex items-center space-x-2 shrink-0">
            <a href="<?= url('admin/reports/sales-tax/pdf?download=1&' . $queryString) ?>" target="_blank"
                class="h-10 px-4 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i data-lucide="file-down" class="w-4 h-4"></i>
                <span>Download PDF</span>
            </a>
            <a href="<?= url('admin/reports/sales-tax/csv?' . $queryString) ?>"
                class="h-10 px-4 bg-slate-900 hover:bg-black text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i data-lucide="file-spreadsheet" class="w-4 h-4"></i>
                <span>Export Excel / CSV</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center space-x-2">
                <i data-lucide="filter" class="w-4 h-4 text-slate-500"></i>
                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Report Filter Criteria</h3>
            </div>
            <?php if (!empty($fromDate) || !empty($toDate) || $selectedMonth !== (int)date('n') || $selectedYear !== (int)date('Y')): ?>
                <a href="<?= url('admin/reports/sales-tax') ?>" class="text-[11px] font-semibold text-red-600 hover:underline flex items-center space-x-1">
                    <i data-lucide="rotate-ccw" class="w-3 h-3"></i>
                    <span>Reset Filters</span>
                </a>
            <?php endif; ?>
        </div>

        <form action="<?= url('admin/reports/sales-tax') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 text-xs">
            
            <!-- Month Dropdown -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Select Month</label>
                <select name="month" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-900 focus:outline-none focus:border-red-600 focus:bg-white transition cursor-pointer">
                    <?php foreach ($months as $mNum => $mName): ?>
                        <option value="<?= $mNum ?>" <?= $selectedMonth === $mNum ? 'selected' : '' ?>>
                            <?= $mName ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Year Dropdown -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Select Year</label>
                <select name="year" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-900 focus:outline-none focus:border-red-600 focus:bg-white transition cursor-pointer">
                    <?php foreach ($years as $y): ?>
                        <option value="<?= $y ?>" <?= $selectedYear === $y ? 'selected' : '' ?>>
                            <?= $y ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Custom From Date -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Custom From Date</label>
                <input type="date" name="from_date" value="<?= htmlspecialchars($fromDate) ?>"
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
            </div>

            <!-- Custom To Date -->
            <div>
                <label class="block font-semibold text-slate-700 mb-1">Custom To Date</label>
                <input type="date" name="to_date" value="<?= htmlspecialchars($toDate) ?>"
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl font-medium text-slate-900 focus:outline-none focus:border-red-600 focus:bg-white transition">
            </div>

            <!-- Filter Submit Button -->
            <div class="flex items-end">
                <button type="submit"
                    class="w-full h-10 px-4 bg-slate-900 hover:bg-black text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-2 cursor-pointer">
                    <i data-lucide="search" class="w-4 h-4"></i>
                    <span>Apply Filter</span>
                </button>
            </div>

        </form>
    </div>

    <!-- SUMMARY METRICS CARDS (4-GRID) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Total Orders Count -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Orders</span>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1"><?= number_format($stats['total_orders']) ?></h3>
                <p class="text-[10px] text-slate-400 mt-0.5">Orders in selected period</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <i data-lucide="shopping-bag" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Card 2: Gross Sales Amount -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Gross Sales</span>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1"><?= format_price($stats['gross_sales']) ?></h3>
                <p class="text-[10px] text-slate-400 mt-0.5">Excludes cancelled orders</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i data-lucide="indian-rupee" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Card 3: Total GST Tax Collected -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">GST Tax Collected</span>
                <h3 class="text-2xl font-extrabold text-red-600 mt-1"><?= format_price($stats['total_tax']) ?></h3>
                <p class="text-[10px] text-slate-400 mt-0.5">Calculated GST portion</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                <i data-lucide="receipt" class="w-6 h-6"></i>
            </div>
        </div>

        <!-- Card 4: Net Sales -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Net Revenue</span>
                <h3 class="text-2xl font-extrabold text-slate-900 mt-1"><?= format_price($stats['net_sales']) ?></h3>
                <p class="text-[10px] text-slate-400 mt-0.5">Sales minus GST tax</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                <i data-lucide="trending-up" class="w-6 h-6"></i>
            </div>
        </div>

    </div>

    <!-- ORDER-WISE BREAKDOWN TABLE -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-slate-900">Order-wise Financial Breakdown</h3>
                <p class="text-xs text-slate-500 mt-0.5">Itemized transaction list for <strong><?= htmlspecialchars($filter_label) ?></strong></p>
            </div>
            <span class="text-xs font-semibold bg-slate-100 text-slate-700 px-3 py-1 rounded-lg border border-slate-200">
                <?= count($orders) ?> Records
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold text-[10px]">
                    <tr>
                        <th class="py-3 px-4 pl-6">Order #</th>
                        <th class="py-3 px-3">Customer Details</th>
                        <th class="py-3 px-3 text-center">HSN Code</th>
                        <th class="py-3 px-3 text-center">GST %</th>
                        <th class="py-3 px-3 text-center">Status</th>
                        <th class="py-3 px-3 text-right">Subtotal</th>
                        <th class="py-3 px-3 text-right">GST Tax (Included)</th>
                        <th class="py-3 px-3 text-right">Shipping</th>
                        <th class="py-3 px-4 text-right pr-6">Gross Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="9" class="p-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <i data-lucide="inbox" class="w-10 h-10 text-slate-300"></i>
                                    <h4 class="text-sm font-semibold text-slate-700">No data available</h4>
                                    <p class="text-xs text-slate-400">No orders were created during the selected date range (<?= htmlspecialchars($filter_label) ?>).</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $ord): ?>
                            <?php $isCancelled = strtolower($ord['order_status'] ?? '') === 'cancelled'; ?>
                            <tr class="hover:bg-slate-50/70 transition <?= $isCancelled ? 'bg-rose-50/40 opacity-75' : '' ?>">
                                <td class="py-3 px-4 pl-6 font-mono font-semibold text-slate-900">
                                    <a href="<?= url('admin/orders/' . $ord['id']) ?>" class="hover:text-red-600 transition">
                                        <?= htmlspecialchars($ord['order_number']) ?>
                                    </a>
                                </td>
                                <td class="py-3 px-3">
                                    <div class="font-semibold text-slate-900"><?= htmlspecialchars($ord['customer_name'] ?? 'N/A') ?></div>
                                    <div class="text-[11px] text-slate-400 font-mono"><?= htmlspecialchars($ord['customer_phone'] ?? '') ?></div>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="font-mono text-xs font-bold bg-slate-100 border border-slate-200 px-2 py-0.5 rounded text-slate-800">
                                        <?= htmlspecialchars($ord['hsn_codes'] ?? '8714.99.90') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <span class="font-mono text-xs font-bold bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded">
                                        <?= htmlspecialchars($ord['gst_rates'] ?? '18%') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-center">
                                    <?php 
                                    $ordSt = strtolower($ord['order_status'] ?? 'pending'); 
                                    $ordClass = 'bg-slate-100 text-slate-700 border-slate-200';
                                    if ($ordSt === 'delivered') {
                                        $ordClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                    } elseif ($ordSt === 'shipped') {
                                        $ordClass = 'bg-blue-50 text-blue-700 border-blue-200';
                                    } elseif ($ordSt === 'cancelled') {
                                        $ordClass = 'bg-rose-50 text-rose-700 border-rose-200';
                                    }
                                    ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] uppercase font-bold border <?= $ordClass ?>">
                                        <?= htmlspecialchars($ord['order_status'] ?? 'pending') ?>
                                    </span>
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-slate-900 font-bold">
                                    <?= format_price($ord['computed_subtotal']) ?>
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-red-600 font-semibold">
                                    <?= format_price($ord['computed_tax']) ?>
                                </td>
                                <td class="py-3 px-3 text-right font-mono text-slate-700 font-semibold">
                                    <?= ((float)($ord['shipping_charge'] ?? 0) <= 0) ? '<span class="text-emerald-700 font-bold bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200 text-[10px]">FREE</span>' : format_price($ord['shipping_charge']) ?>
                                </td>
                                <td class="py-3 px-4 text-right pr-6 font-mono font-bold text-slate-900">
                                    <?= format_price($ord['total_amount']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>

                <!-- Summary Totals Footer Row -->
                <?php if (!empty($orders)): ?>
                    <tfoot class="bg-slate-100/90 border-t-2 border-slate-300 font-bold text-xs text-slate-900">
                        <tr>
                            <td colspan="5" class="py-3.5 px-4 pl-6 text-right uppercase tracking-wider text-[11px]">
                                Summary Totals (Active Orders Only):
                            </td>
                            <td class="py-3.5 px-3 text-right font-mono text-slate-900">
                                <?= format_price($stats['total_subtotal']) ?>
                            </td>
                            <td class="py-3.5 px-3 text-right font-mono text-red-600">
                                <?= format_price($stats['total_tax']) ?>
                            </td>
                            <td class="py-3.5 px-3 text-right font-mono text-slate-900">
                                <?= format_price($stats['total_shipping']) ?>
                            </td>
                            <td class="py-3.5 px-4 text-right pr-6 font-mono text-base text-red-600">
                                <?= format_price($stats['gross_sales']) ?>
                            </td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>
