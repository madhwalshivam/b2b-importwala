<?php
include __DIR__ . '/layouts/header.php';
?>

<div class="max-w-7xl mx-auto space-y-6 font-sans"
    x-data="{ userModal: false, selectedUser: null, userOrdersList: [] }">

    <!-- Title Banner & Live Online Indicator -->
    <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between bg-white p-6 rounded-2xl border border-gray-900 shadow-xs gap-4">
        <div>
            <div class="flex items-center space-x-3">
                <h2 class="text-xl font-semibold text-gray-900 leading-snug">Live Analytics & Performance Intelligence
                </h2>
                <span
                    class="inline-flex items-center space-x-1.5 bg-green-50 text-green-700 text-xs font-semibold px-3 py-1 rounded-full border border-green-200">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    <span>LIVE DATA</span>
                </span>
            </div>
            <p class="text-xs text-gray-500 mt-1 font-medium">Real-time session tracking, revenue trends, customer
                purchase metrics & cart abandonment audit</p>
        </div>

        <!-- Date Range Filter Form -->
        <form action="<?= url('admin/analytics') ?>" method="GET"
            class="flex flex-wrap items-center gap-2 text-xs font-semibold">
            <select name="range" onchange="this.form.submit()"
                class="h-10 px-3 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none">
                <option value="today" <?= $preset === 'today' ? 'selected' : '' ?>>Today</option>
                <option value="this_week" <?= $preset === 'this_week' ? 'selected' : '' ?>>This Week</option>
                <option value="this_month" <?= $preset === 'this_month' ? 'selected' : '' ?>>This Month</option>
                <option value="custom" <?= $preset === 'custom' ? 'selected' : '' ?>>Custom Date Range</option>
            </select>

            <?php if ($preset === 'custom'): ?>
                <input type="date" name="start_date" value="<?= htmlspecialchars($startDate ?? '') ?>"
                    class="h-10 px-3 bg-gray-50 border border-gray-900 rounded-xl font-medium text-gray-900">
                <span class="text-gray-400 font-semibold">to</span>
                <input type="date" name="end_date" value="<?= htmlspecialchars($endDate ?? '') ?>"
                    class="h-10 px-3 bg-gray-50 border border-gray-900 rounded-xl font-medium text-gray-900">
                <button type="submit"
                    class="h-10 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition">Apply</button>
            <?php endif; ?>
        </form>
    </div>

    <!-- 4 KPI Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- Live Active Sessions -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-2">
            <div class="flex items-center justify-between text-gray-500">
                <span class="text-xs font-semibold uppercase tracking-wider">Live Users Online</span>
                <div class="w-8 h-8 rounded-xl bg-green-50 text-green-600 flex items-center justify-center">
                    <i data-lucide="users" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-3xl font-semibold text-gray-900"><?= number_format($liveOnlineCount) ?></p>
            <p class="text-[11px] text-gray-500 font-medium">Active in last 5 minutes</p>
        </div>

        <!-- Total Orders in Range -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-2">
            <div class="flex items-center justify-between text-gray-500">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Orders</span>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-3xl font-semibold text-gray-900"><?= number_format($metrics['total_orders']) ?></p>
            <p class="text-[11px] text-gray-500 font-medium">For selected period</p>
        </div>

        <!-- Total Revenue in Range -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-2">
            <div class="flex items-center justify-between text-gray-500">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Revenue</span>
                <div class="w-8 h-8 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                    <i data-lucide="indian-rupee" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-3xl font-semibold text-gray-900"><?= format_price($metrics['total_revenue']) ?></p>
            <p class="text-[11px] text-gray-500 font-medium">Gross sales revenue</p>
        </div>

        <!-- Average Order Value -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-2">
            <div class="flex items-center justify-between text-gray-500">
                <span class="text-xs font-semibold uppercase tracking-wider">Avg. Order Value</span>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </div>
            </div>
            <p class="text-3xl font-semibold text-gray-900">
                <?= format_price($metrics['total_orders'] > 0 ? ($metrics['total_revenue'] / $metrics['total_orders']) : 0) ?>
            </p>
            <p class="text-[11px] text-gray-500 font-medium">Per order average</p>
        </div>

    </div>

    <!-- Chart.js Trends Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Orders Trend Chart -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-4">
            <h3 class="font-semibold text-gray-900 text-sm flex items-center space-x-2">
                <i data-lucide="line-chart" class="w-4 h-4 text-red-600"></i>
                <span>Orders Trend Over Time</span>
            </h3>
            <div class="h-64">
                <canvas id="ordersChart"></canvas>
            </div>
        </div>

        <!-- Revenue Trend Chart -->
        <div class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-4">
            <h3 class="font-semibold text-gray-900 text-sm flex items-center space-x-2">
                <i data-lucide="bar-chart-3" class="w-4 h-4 text-green-600"></i>
                <span>Revenue Trend (₹) Over Time</span>
            </h3>
            <div class="h-64">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

    </div>

    <!-- Per-User Purchase History Table -->
    <div class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
                <h3 class="font-semibold text-gray-900 text-sm flex items-center space-x-2">
                    <i data-lucide="user-check" class="w-4 h-4 text-gray-700"></i>
                    <span>Per-User Purchase History & Lifetime Value</span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5 font-medium">Click on any user to inspect their individual order
                    details</p>
            </div>

            <form action="<?= url('admin/analytics') ?>" method="GET" class="flex items-center space-x-2 text-xs">
                <input type="text" name="user_search" value="<?= htmlspecialchars($userSearch) ?>"
                    placeholder="Search user name/email..."
                    class="h-9 px-3 bg-gray-50 border border-gray-900 rounded-xl font-medium text-gray-900 focus:outline-none focus:border-gray-900">
                <button type="submit"
                    class="h-9 px-3 bg-gray-900 text-white font-semibold rounded-xl hover:bg-black transition">Search</button>
            </form>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-900 text-xs">
            <table class="w-full text-left">
                <thead class="bg-gray-50 font-semibold text-gray-700 border-b border-gray-900 uppercase tracking-wider">
                    <tr>
                        <th class="p-3">Customer</th>
                        <th class="p-3">Email</th>
                        <th class="p-3 text-center">Orders Count</th>
                        <th class="p-3 text-right">Lifetime Spend</th>
                        <th class="p-3">Last Order Date</th>
                        <th class="p-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 font-semibold text-gray-800">
                    <?php if (empty($userPurchases)): ?>
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-400">No user purchase records found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($userPurchases as $up): ?>
                            <tr class="hover:bg-gray-50/50">
                                <td class="p-3 font-semibold text-gray-900"><?= htmlspecialchars($up['name']) ?></td>
                                <td class="p-3 text-gray-600">
                                    <span><?= htmlspecialchars($up['email']) ?></span>
                                </td>
                                <td class="p-3 text-center">
                                    <span
                                        class="px-2.5 py-0.5 bg-gray-100 text-gray-800 rounded-md font-semibold text-xs"><?= $up['order_count'] ?></span>
                                </td>
                                <td class="p-3 text-right font-semibold text-gray-900"><?= format_price($up['total_spent']) ?>
                                </td>
                                <td class="p-3 text-gray-500 text-[11px]"><?= $up['last_order_date'] ?: 'N/A' ?></td>
                                <td class="p-3 text-center">
                                    <button @click="
                                        fetch('<?= url('admin/analytics/user-orders/') ?>' + <?= $up['id'] ?>)
                                        .then(r => r.json())
                                        .then(d => { selectedUser = d.user; userOrdersList = d.orders; userModal = true; });
                                    "
                                        class="h-8 px-3 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white font-semibold rounded-lg transition text-xs">
                                        View Orders
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>



    <!-- User Order History Drill-Down Modal -->
    <div x-show="userModal" class="relative z-[99999]"
        x-effect="document.body.classList.toggle('modal-open', userModal)" x-cloak>
        <div class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs transition-opacity z-[99999] flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
            @click="userModal = false">
            <div class="bg-white rounded-2xl max-w-3xl w-full p-6 space-y-4 shadow-2xl border border-gray-900 my-auto z-[100000] max-h-[90vh] sm:max-h-[85vh] overflow-y-auto flex flex-col"
                @click.stop>
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h3 class="text-base font-semibold text-gray-900"
                            x-text="selectedUser ? selectedUser.name + ' - Order History' : 'User Orders'"></h3>
                        <p class="text-xs text-gray-500"
                            x-text="selectedUser ? selectedUser.email + ' | Phone: ' + (selectedUser.phone || 'N/A') : ''">
                        </p>
                    </div>
                    <button @click="userModal = false" class="text-gray-400 hover:text-gray-600"><i data-lucide="x"
                            class="w-5 h-5"></i></button>
                </div>

                <div class="max-h-96 overflow-y-auto space-y-3">
                    <template x-if="userOrdersList.length === 0">
                        <p class="text-xs text-gray-400 text-center py-8">No order records found for this user.</p>
                    </template>

                    <template x-for="ord in userOrdersList" :key="ord.id">
                        <div
                            class="p-4 bg-gray-50 rounded-xl border border-gray-900 flex items-center justify-between text-xs font-semibold">
                            <div>
                                <span class="font-semibold text-gray-900" x-text="'Order #' + ord.order_number"></span>
                                <span class="text-gray-500 text-[11px] block" x-text="ord.created_at"></span>
                            </div>
                            <div>
                                <span
                                    class="px-2 py-0.5 rounded text-[10px] font-semibold uppercase bg-blue-100 text-blue-800"
                                    x-text="ord.order_status"></span>
                            </div>
                            <div class="text-right">
                                <span class="font-semibold text-gray-900 text-sm"
                                    x-text="'₹' + ord.total_amount"></span>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="flex justify-end pt-2 border-t border-gray-100">
                    <button @click="userModal = false"
                        class="h-9 px-5 bg-gray-900 text-white font-semibold text-xs rounded-xl hover:bg-black">Close</button>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js Script Initialization -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rawChartData = <?= json_encode($chartData) ?>;

        const labels = rawChartData.map(d => d.order_date);
        const orderCounts = rawChartData.map(d => parseInt(d.count));
        const revenues = rawChartData.map(d => parseFloat(d.revenue));

        // Orders Line Chart
        const ctxOrders = document.getElementById('ordersChart').getContext('2d');
        new Chart(ctxOrders, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Orders Count',
                    data: orderCounts,
                    borderColor: '#DC2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });

        // Revenue Bar Chart
        const ctxRevenue = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctxRevenue, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Revenue (₹)',
                    data: revenues,
                    backgroundColor: '#16A34A',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    });
</script>

<?php
include __DIR__ . '/layouts/footer.php';
?>