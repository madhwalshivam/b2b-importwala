<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-8 font-sans">

    <!-- Welcome Banner & Date Filter Bar -->
    <div
        class="bg-white dark:bg-slate-800 rounded-2xl p-6 border border-gray-900 dark:border-slate-700 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div>
            <span
                class="inline-block px-3 py-1 bg-red-600 text-white font-semibold text-[10px] uppercase rounded tracking-wider mb-2">
                Mudsor Executive CMS
            </span>
            <h2 class="text-xl lg:text-2xl font-semibold text-gray-900 dark:text-slate-100">Welcome back, Admin

            </h2>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1 font-medium">Here is your live real-time e-commerce
                revenue, inventory,
                and order analytics performance.</p>
        </div>

        <div class="flex items-center space-x-3 shrink-0 flex-wrap gap-2">
            <!-- Real-time Date Filter Dropdown -->
            <form action="<?= url('admin/dashboard') ?>" method="GET"
                class="flex items-center space-x-2 bg-gray-50 dark:bg-slate-700/50 p-1.5 rounded-xl border border-gray-900 dark:border-slate-700">
                <i data-lucide="calendar" class="w-4 h-4 text-gray-500 dark:text-slate-400 ml-1.5"></i>
                <select name="date_range" onchange="this.form.submit()"
                    class="bg-transparent text-gray-900 dark:text-slate-100 text-xs font-semibold focus:outline-none cursor-pointer pr-2">
                    <option value="all" <?= ($dateRange ?? 'all') === 'all' ? 'selected' : '' ?>>All Time</option>
                    <option value="today" <?= ($dateRange ?? '') === 'today' ? 'selected' : '' ?>>Today</option>
                    <option value="7days" <?= ($dateRange ?? '') === '7days' ? 'selected' : '' ?>>Last 7 Days</option>
                    <option value="30days" <?= ($dateRange ?? '') === '30days' ? 'selected' : '' ?>>Last 30 Days</option>
                    <option value="this_month" <?= ($dateRange ?? '') === 'this_month' ? 'selected' : '' ?>>This Month
                    </option>
                    <option value="this_year" <?= ($dateRange ?? '') === 'this_year' ? 'selected' : '' ?>>This Year
                    </option>
                </select>
            </form>

            <a href="<?= url('admin/orders') ?>"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-xs flex items-center space-x-1.5">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                <span>Manage Orders</span>
            </a>
            <a href="<?= url('admin/products') ?>"
                class="px-4 py-2 bg-gray-100 dark:bg-slate-700 hover:bg-gray-900 dark:hover:bg-slate-600 text-gray-800 dark:text-slate-200 font-semibold text-xs rounded-xl border border-gray-900 dark:border-slate-600 transition flex items-center space-x-1.5">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Product</span>
            </a>
        </div>
    </div>

    <!-- Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- 1. Total Sales Revenue -> Links to Live Analytics / Orders -->
        <a href="<?= url('admin/analytics') ?>"
            class="group bg-white dark:bg-slate-800 p-6 rounded-2xl border border-gray-900 dark:border-slate-700 shadow-xs flex items-center justify-between hover:border-red-600 hover:shadow-md transition-all duration-200 cursor-pointer">
            <div>
                <span
                    class="text-[11px] font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider group-hover:text-red-600 transition-colors flex items-center gap-1">
                    <span>Total Sales Revenue</span>
                    <i data-lucide="arrow-up-right"
                        class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                </span>
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-slate-100 mt-1">
                    <?= format_price($stats['total_sales'] ?? 0) ?>
                </h3>
            </div>
            <div
                class="w-12 h-12 bg-red-50 dark:bg-red-950/30 text-red-600 border border-red-100 dark:border-red-900/40 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                <i data-lucide="indian-rupee" class="w-6 h-6"></i>
            </div>
        </a>

        <!-- 2. Total Orders -> Links to Manage Orders -->
        <a href="<?= url('admin/orders') ?>"
            class="group bg-white dark:bg-slate-800 p-6 rounded-2xl border border-gray-900 dark:border-slate-700 shadow-xs flex items-center justify-between hover:border-red-600 hover:shadow-md transition-all duration-200 cursor-pointer">
            <div>
                <span
                    class="text-[11px] font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider group-hover:text-red-600 transition-colors flex items-center gap-1">
                    <span>Total Orders</span>
                    <i data-lucide="arrow-up-right"
                        class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                </span>
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-slate-100 mt-1">
                    <?= number_format($stats['total_orders'] ?? 0) ?>
                </h3>
            </div>
            <div
                class="w-12 h-12 bg-gray-100 dark:bg-slate-700 text-gray-900 dark:text-slate-100 border border-gray-900 dark:border-slate-600 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                <i data-lucide="shopping-cart" class="w-6 h-6"></i>
            </div>
        </a>

        <!-- 3. Total Products -> Links to Products List -->
        <a href="<?= url('admin/products') ?>"
            class="group bg-white dark:bg-slate-800 p-6 rounded-2xl border border-gray-900 dark:border-slate-700 shadow-xs flex items-center justify-between hover:border-red-600 hover:shadow-md transition-all duration-200 cursor-pointer">
            <div>
                <span
                    class="text-[11px] font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider group-hover:text-red-600 transition-colors flex items-center gap-1">
                    <span>Total Products</span>
                    <i data-lucide="arrow-up-right"
                        class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                </span>
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-slate-100 mt-1">
                    <?= number_format($stats['total_products'] ?? 0) ?>
                </h3>
            </div>
            <div
                class="w-12 h-12 bg-red-50 dark:bg-red-950/30 text-red-600 border border-red-100 dark:border-red-900/40 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                <i data-lucide="package" class="w-6 h-6"></i>
            </div>
        </a>

        <!-- 4. Users -> Links to Live Analytics / Users -->
        <a href="<?= url('admin/analytics') ?>"
            class="group bg-white dark:bg-slate-800 p-6 rounded-2xl border border-gray-900 dark:border-slate-700 shadow-xs flex items-center justify-between hover:border-red-600 hover:shadow-md transition-all duration-200 cursor-pointer">
            <div>
                <span
                    class="text-[11px] font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider group-hover:text-red-600 transition-colors flex items-center gap-1">
                    <span>Users</span>
                    <i data-lucide="arrow-up-right"
                        class="w-3 h-3 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                </span>
                <h3 class="text-2xl font-semibold text-gray-900 dark:text-slate-100 mt-1">
                    <?= number_format($stats['total_customers'] ?? 0) ?>
                </h3>
            </div>
            <div
                class="w-12 h-12 bg-gray-100 dark:bg-slate-700 text-gray-900 dark:text-slate-100 border border-gray-900 dark:border-slate-600 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </a>
    </div>

    <!-- Analytics Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Monthly Revenue & Orders Line Chart -->
        <div
            class="lg:col-span-7 xl:col-span-8 bg-white dark:bg-slate-800 p-6 rounded-2xl border border-gray-900 dark:border-slate-700 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-slate-700 pb-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-slate-100">Revenue & Sales Performance</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Live gross revenue and completed
                        customer orders from database</p>
                </div>
                <span
                    class="text-[11px] font-semibold bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-400 px-3 py-1 rounded-full border border-green-200 dark:border-green-800">Live
                    Database Feed</span>
            </div>

            <div class="h-72 relative">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Sales by Category Doughnut Chart with Custom Legend -->
        <div
            class="lg:col-span-5 xl:col-span-4 bg-white dark:bg-slate-800 p-6 rounded-2xl border border-gray-900 dark:border-slate-700 shadow-xs space-y-4 flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-100 dark:border-slate-700 pb-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-slate-100">Category Share</h3>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Real sales breakdown by accessory type
                    </p>
                </div>
                <span
                    class="text-[11px] font-semibold text-gray-600 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 px-2.5 py-1 rounded-lg">
                    <?= count($categoryChart['labels'] ?? []) ?> Categories
                </span>
            </div>

            <!-- Doughnut Canvas Container with Inner Total Overlay -->
            <div class="h-48 relative flex items-center justify-center shrink-0">
                <canvas id="categoryChart"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                    <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Total</span>
                    <span class="text-base font-semibold text-gray-900 dark:text-slate-100"
                        id="categoryChartTotalCount">
                        <?= format_price(array_sum($categoryChart['values'] ?? [0])) ?>
                    </span>
                </div>
            </div>

            <!-- Custom Category Breakdown Legend List -->
            <div class="flex-1 overflow-y-auto max-h-56 pr-1 space-y-1.5 custom-scrollbar" id="categoryLegendList">
                <!-- Dynamically populated by JS -->
            </div>
        </div>

    </div>

    <!-- Recent Orders Section -->
    <div
        class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-900 dark:border-slate-700 p-6 space-y-4 shadow-xs">
        <div class="flex items-center justify-between border-b border-gray-100 dark:border-slate-700 pb-4">
            <div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-slate-100">Recent Customer Orders</h3>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Latest EV accessory purchases across India
                </p>
            </div>
            <a href="<?= url('admin/orders') ?>" class="text-xs font-semibold text-red-600 hover:underline">View All
                Orders →</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse min-w-[700px]">
                <thead>
                    <tr
                        class="bg-gray-50 dark:bg-slate-700/50 border-b border-gray-900 dark:border-slate-700 text-gray-500 dark:text-slate-400 font-semibold uppercase tracking-wider">
                        <th class="p-3">Order #</th>
                        <th class="p-3">Customer</th>
                        <th class="p-3">Total Amount</th>
                        <th class="p-3">Payment</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Date</th>
                        <th class="p-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-slate-700/50">
                    <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="7" class="p-6 text-center text-gray-400 font-medium">No orders received yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $ord): ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-700/40 transition">
                                <td class="p-3 font-mono font-semibold text-gray-900 dark:text-slate-100">
                                    <?= htmlspecialchars($ord['order_number']) ?>
                                </td>
                                <td class="p-3 font-semibold text-gray-800 dark:text-slate-200">
                                    <?= htmlspecialchars($ord['customer_name']) ?>
                                </td>
                                <td class="p-3 font-semibold text-gray-900 dark:text-slate-100">
                                    <?= format_price($ord['total_amount']) ?>
                                </td>
                                <td class="p-3">
                                    <?php
                                    $dashPaySt = strtolower($ord['payment_status'] ?? 'pending');
                                    $dashPayClass = 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/80 dark:text-amber-300 dark:border-amber-800';
                                    if ($dashPaySt === 'paid') {
                                        $dashPayClass = 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/80 dark:text-emerald-300 dark:border-emerald-800';
                                    } elseif ($dashPaySt === 'failed') {
                                        $dashPayClass = 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/80 dark:text-rose-300 dark:border-rose-800';
                                    }
                                    ?>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-semibold uppercase border <?= $dashPayClass ?>">
                                        <?= htmlspecialchars($ord['payment_status'] ?? 'pending') ?>
                                    </span>
                                </td>
                                <td class="p-3">
                                    <?php
                                    $dashOrdSt = strtolower($ord['order_status'] ?? 'pending');
                                    $dashOrdClass = 'bg-gray-100 text-gray-800 border-gray-900 dark:bg-red-950/60 dark:text-red-200 dark:border-red-900/60';
                                    if ($dashOrdSt === 'delivered' || $dashOrdSt === 'confirmed') {
                                        $dashOrdClass = 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/80 dark:text-emerald-300 dark:border-emerald-800';
                                    } elseif ($dashOrdSt === 'pending') {
                                        $dashOrdClass = 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/80 dark:text-amber-300 dark:border-amber-800';
                                    }
                                    ?>
                                    <span
                                        class="px-2.5 py-1 text-[10px] font-semibold rounded-lg uppercase border <?= $dashOrdClass ?>">
                                        <?= htmlspecialchars($ord['order_status']) ?>
                                    </span>
                                </td>
                                <td class="p-3 text-gray-500 dark:text-slate-400">
                                    <?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?>
                                </td>
                                <td class="p-3 text-right">
                                    <a href="<?= url('admin/orders/' . $ord['id']) ?>"
                                        class="px-3 py-1.5 bg-gray-900 dark:bg-slate-700 hover:bg-black dark:hover:bg-slate-600 text-white rounded-lg font-semibold text-xs transition">View
                                        Order</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Real Database Chart.js Initialization Script -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const revLabels = <?= json_encode($revenueChart['labels'] ?? []) ?>;
        const revValues = <?= json_encode($revenueChart['values'] ?? []) ?>;

        const catLabels = <?= json_encode($categoryChart['labels'] ?? []) ?>;
        const catValues = <?= json_encode($categoryChart['values'] ?? []) ?>;

        // Rich modern color palette
        const chartColors = [
            '#DC2626', '#1E293B', '#2563EB', '#D4A017', '#10B981',
            '#8B5CF6', '#F97316', '#06B6D4', '#EC4899', '#64748B'
        ];

        // 1. Revenue & Sales Line Chart
        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        const gradient = ctxRevenue.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(220, 38, 38, 0.22)');
        gradient.addColorStop(1, 'rgba(220, 38, 38, 0.00)');

        const allZeros = revValues.length === 0 || revValues.every(v => v === 0);

        new Chart(ctxRevenue, {
            type: 'bar',
            data: {
                labels: revLabels.length > 0 ? revLabels : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Gross Revenue (₹)',
                    data: revValues.length > 0 ? revValues : [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                    backgroundColor: '#DC2626',
                    borderRadius: 4,
                    borderWidth: 0,
                    barPercentage: 0.5,
                    maxBarThickness: 40
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleFont: { family: 'Inter', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Inter', size: 12 },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (context) {
                                return ' Revenue: ₹' + (context.raw || 0).toLocaleString('en-IN');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        suggestedMax: allZeros ? 1000 : undefined,
                        grid: { color: 'rgba(243, 244, 246, 0.8)' },
                        ticks: {
                            precision: 0,
                            font: { family: 'Inter', size: 11 },
                            callback: function (value) {
                                if (value >= 100000) return '₹' + (value / 100000).toFixed(1) + 'L';
                                if (value >= 1000) return '₹' + (value / 1000).toFixed(0) + 'k';
                                return '₹' + value;
                            }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: {
                            font: { family: 'Inter', size: 11 }
                        }
                    }
                }
            }
        });

        // 2. Category Share Doughnut Chart
        const totalCategoryVal = catValues.reduce((a, b) => a + b, 0) || 1;
        const sliceColors = catLabels.map((_, idx) => chartColors[idx % chartColors.length]);

        const ctxCategory = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctxCategory, {
            type: 'doughnut',
            data: {
                labels: catLabels.length > 0 ? catLabels : ['General Accessories'],
                datasets: [{
                    data: catValues.length > 0 ? catValues : [1],
                    backgroundColor: sliceColors.length > 0 ? sliceColors : ['#DC2626'],
                    borderWidth: 2,
                    borderColor: '#FFFFFF',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '74%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        titleFont: { family: 'Inter', size: 12, weight: 'bold' },
                        bodyFont: { family: 'Inter', size: 11 },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            label: function (context) {
                                const val = context.raw || 0;
                                const pct = Math.round((val / totalCategoryVal) * 100);
                                const formattedVal = '₹' + val.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                return ` ${context.label}: ${formattedVal} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Populate Custom Category Legend List
        const legendContainer = document.getElementById('categoryLegendList');
        if (legendContainer && catLabels.length > 0) {
            legendContainer.innerHTML = catLabels.map((label, idx) => {
                const val = catValues[idx] || 0;
                const pct = Math.round((val / totalCategoryVal) * 100);
                const color = sliceColors[idx];
                const formattedVal = '₹' + val.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                return `
                    <div class="flex items-center justify-between p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-slate-700/50 transition text-xs">
                        <div class="flex items-center space-x-2.5 min-w-0 pr-2">
                            <span class="w-3 h-3 rounded-md shrink-0 shadow-2xs" style="background-color: ${color}"></span>
                            <span class="font-medium text-gray-800 dark:text-slate-200 truncate" title="${label}">${label}</span>
                        </div>
                        <div class="flex items-center space-x-2 shrink-0">
                            <span class="font-semibold text-gray-900 dark:text-slate-100">${formattedVal}</span>
                            <span class="text-[10px] font-semibold text-gray-600 dark:text-slate-300 bg-gray-100 dark:bg-slate-700 px-2 py-0.5 rounded-md">${pct}%</span>
                        </div>
                    </div>
                `;
            }).join('');
        }
    });
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>