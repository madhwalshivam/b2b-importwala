<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6 font-sans">

    <!-- Top Header -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span
                    class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-red-50 text-red-600 rounded-lg tracking-wider border border-red-100">
                    Fulfillment Center
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Customer Orders</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Customer Orders Management</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Track, process, and fulfill incoming customer orders, manage shipping status, and print tax invoices.
            </p>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <form action="<?= url('admin/orders') ?>" method="GET"
        class="bg-white p-4 rounded-2xl border border-gray-900 flex flex-wrap items-center gap-4 text-xs shadow-xs">
        <div class="relative flex-1 min-w-[240px]">
            <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>"
                placeholder="Search Order # or Customer Phone..."
                class="w-full h-11 pl-10 pr-4 bg-gray-50 border border-gray-900 rounded-xl focus:outline-none focus:border-red-600 focus:bg-white font-semibold text-gray-900 text-xs transition">
        </div>

        <div class="w-full sm:w-auto">
            <select name="status" onchange="this.form.submit()"
                class="w-full sm:w-auto h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600 text-xs cursor-pointer">
                <option value="">All Order Statuses</option>
                <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="confirmed" <?= ($status ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                <option value="packed" <?= ($status ?? '') === 'packed' ? 'selected' : '' ?>>Packed</option>
                <option value="shipped" <?= ($status ?? '') === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                <option value="delivered" <?= ($status ?? '') === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
            </select>
        </div>

        <button type="submit"
            class="h-11 px-6 bg-gray-900 hover:bg-black text-white font-semibold text-xs rounded-xl transition shadow-xs flex items-center space-x-1.5 cursor-pointer">
            <i data-lucide="filter" class="w-3.5 h-3.5"></i>
            <span>Filter Orders</span>
        </button>
    </form>

    <!-- Orders Table -->
    <div class="bg-white rounded-2xl border border-gray-900 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse min-w-[900px]">
                <thead>
                    <tr
                        class="bg-gray-50/80 dark:bg-gray-900 border-b border-gray-900 dark:border-gray-700 text-gray-500 dark:text-gray-300 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="p-4 pl-6">Order #</th>
                        <th class="p-4">Customer Name</th>
                        <th class="p-4">Phone</th>
                        <th class="p-4">Total Amount</th>
                        <th class="p-4">Payment</th>
                        <th class="p-4">Shipping Status</th>
                        <th class="p-4">Order Date</th>
                        <th class="p-4 pr-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-400 font-medium">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <i data-lucide="inbox" class="w-8 h-8 text-gray-300"></i>
                                    <span>No orders found matching your filter criteria.</span>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $ord): ?>
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <td class="p-4 pl-6 font-mono font-semibold text-gray-900">
                                    <a href="<?= url('admin/orders/' . $ord['id']) ?>" class="hover:text-red-600 transition">
                                        <?= htmlspecialchars($ord['order_number']) ?>
                                    </a>
                                </td>
                                <td class="p-4 font-semibold text-gray-900">
                                    <?= htmlspecialchars($ord['customer_name']) ?>
                                </td>
                                <td class="p-4 text-gray-600 font-mono">
                                    <?= htmlspecialchars($ord['customer_phone']) ?>
                                </td>
                                <td class="p-4 font-semibold text-gray-900">
                                    <?= format_price($ord['total_amount']) ?>
                                </td>
                                <td class="p-4 font-medium">
                                    <?php 
                                    $paySt = strtolower($ord['payment_status'] ?? 'pending'); 
                                    $payBadgeClass = 'bg-amber-50 text-amber-800 border-amber-200 dark:bg-amber-950/80 dark:text-amber-300 dark:border-amber-800';
                                    if ($paySt === 'paid') {
                                        $payBadgeClass = 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/80 dark:text-emerald-300 dark:border-emerald-800';
                                    } elseif ($paySt === 'failed') {
                                        $payBadgeClass = 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/80 dark:text-rose-300 dark:border-rose-800';
                                    }
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] uppercase font-bold border tracking-wider <?= $payBadgeClass ?>">
                                        <?= htmlspecialchars($ord['payment_status'] ?? 'pending') ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <?php
                                    $shipSt = strtolower($ord['shipping_status'] ?? 'not_shipped');
                                    $shipClass = 'bg-gray-100 text-gray-800 border-gray-900 dark:bg-red-950/60 dark:text-red-200 dark:border-red-900/60';
                                    if ($shipSt === 'delivered') {
                                        $shipClass = 'bg-emerald-50 text-emerald-800 border-emerald-200 dark:bg-emerald-950/80 dark:text-emerald-300 dark:border-emerald-800';
                                    } elseif ($shipSt === 'shipped') {
                                        $shipClass = 'bg-indigo-50 text-indigo-800 border-indigo-200 dark:bg-indigo-950/80 dark:text-indigo-300 dark:border-indigo-800';
                                    } elseif ($shipSt === 'processing') {
                                        $shipClass = 'bg-blue-50 text-blue-800 border-blue-200 dark:bg-blue-950/80 dark:text-blue-300 dark:border-blue-800';
                                    } elseif ($shipSt === 'cancelled') {
                                        $shipClass = 'bg-rose-50 text-rose-800 border-rose-200 dark:bg-rose-950/80 dark:text-rose-300 dark:border-rose-800';
                                    }
                                    ?>
                                    <span class="inline-flex items-center px-2.5 py-1 text-[10px] font-bold rounded-lg border uppercase tracking-wider w-max <?= $shipClass ?>">
                                        <?= htmlspecialchars(str_replace('_', ' ', $shipSt)) ?>
                                    </span>
                                </td>
                                <td class="p-4 text-gray-500 font-medium">
                                    <?= date('d M Y, h:i A', strtotime($ord['created_at'])) ?>
                                </td>
                                <td class="p-4 pr-6 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center justify-end space-x-2">
                                        <a href="<?= url('admin/orders/' . $ord['id']) ?>"
                                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-xl font-semibold text-xs transition shadow-2xs">
                                            <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                            <span>Manage</span>
                                        </a>
                                        <a href="<?= url('admin/orders/invoice/' . $ord['id']) ?>" target="_blank"
                                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-900 text-gray-800 hover:text-white border border-gray-900 hover:border-gray-900 rounded-xl font-semibold text-xs transition shadow-2xs">
                                            <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                            <span>Invoice</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100">
            <?= $paginator->render() ?>
        </div>
    </div>

</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>