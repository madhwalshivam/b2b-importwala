<?php
include __DIR__ . '/../layouts/header.php';
?>

<style>
    .custom-scroll-area::-webkit-scrollbar {
        width: 5px;
    }
    .custom-scroll-area::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 8px;
    }
    .custom-scroll-area::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 8px;
    }
    .custom-scroll-area::-webkit-scrollbar-thumb:hover {
        background: #A8111C;
    }
</style>

<div class="bg-theme-bg py-10 min-h-screen font-sans border-b border-gray-900">
    <div class="container mx-auto px-4 space-y-8">

        <!-- Dashboard Welcome Banner -->
        <div
            class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div
                    class="w-14 h-14 rounded-2xl bg-red-50 text-red-600 font-black text-xl flex items-center justify-center border border-red-100">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </div>
                <div>
                    <h1 class="text-xl font-semibold text-gray-900">Welcome,
                        <?= htmlspecialchars($user['name'] ?? 'Customer') ?>
                    </h1>
                    <p class="text-xs text-gray-500 mt-0.5"><?= htmlspecialchars($user['email'] ?? '') ?> • Member
                        Account</p>
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <a href="<?= url('wishlist') ?>"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-900 hover:text-white text-gray-800 font-semibold text-xs rounded-xl transition flex items-center space-x-1.5">
                    <i data-lucide="heart" class="w-4 h-4 text-red-600"></i>
                    <span>Wishlist (<?= count($wishlistItems) ?>)</span>
                </a>
                <a href="<?= url('logout') ?>"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition">
                    Logout
                </a>
            </div>
        </div>

        <!-- Dashboard Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- Left: Order History (Joined Orders Table) -->
            <div class="lg:col-span-8 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900 flex items-center space-x-2">
                        <i data-lucide="package" class="w-5 h-5 text-red-600"></i>
                        <span>My Order History</span>
                    </h2>
                    <span class="text-xs text-gray-500 font-medium"><?= count($orders) ?> Total Orders</span>
                </div>

                <?php if (empty($orders)): ?>
                    <div class="bg-white p-12 rounded-2xl border border-gray-900 text-center space-y-3">
                        <i data-lucide="package-x" class="w-12 h-12 text-gray-300 mx-auto"></i>
                        <h3 class="text-sm font-semibold text-gray-800">No orders placed yet</h3>
                        <p class="text-xs text-gray-500">Explore electric scooter crash guards & accessories to place your
                            first order.</p>
                        <a href="<?= url('shop') ?>"
                            class="inline-block px-6 py-2.5 bg-red-600 text-white font-semibold text-xs rounded-xl hover:bg-red-700 transition">Start
                            Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="space-y-3.5 max-h-[520px] overflow-y-auto pr-2 custom-scroll-area">
                        <?php foreach ($orders as $order): ?>
                            <div
                                class="bg-white p-5 rounded-2xl border border-gray-900 space-y-3 hover:border-gray-300 transition">
                                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-3">
                                    <div>
                                        <span class="text-xs font-mono font-semibold text-red-600">ORDER
                                            #<?= htmlspecialchars($order['order_number']) ?></span>
                                        <p class="text-[11px] text-gray-500 mt-0.5">
                                            <?= date('M d, Y • h:i A', strtotime($order['created_at'])) ?>
                                        </p>
                                    </div>

                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wider <?= $order['order_status'] === 'delivered' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-800' ?>">
                                            <?= htmlspecialchars($order['order_status']) ?>
                                        </span>
                                        <span
                                            class="text-sm font-semibold text-gray-900"><?= format_price($order['total_amount']) ?></span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-xs text-gray-600">
                                    <span>Payment Method: <strong
                                            class="uppercase font-semibold text-gray-800"><?= htmlspecialchars($order['payment_method']) ?></strong>
                                        (<?= htmlspecialchars($order['payment_status']) ?>)</span>
                                    <a href="<?= url('admin/orders/invoice/' . $order['id']) ?>" target="_blank"
                                        class="font-semibold text-red-600 hover:underline flex items-center space-x-1">
                                        <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                        <span>Download Tax Invoice</span>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: Saved Wishlist Preview -->
            <div class="lg:col-span-4 space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-900 flex items-center space-x-2">
                        <i data-lucide="heart" class="w-5 h-5 text-red-600"></i>
                        <span>Saved Wishlist</span>
                    </h2>
                    <a href="<?= url('wishlist') ?>" class="text-xs text-red-600 font-semibold hover:underline">View
                        All</a>
                </div>

                <?php if (empty($wishlistItems)): ?>
                    <div class="bg-white p-8 rounded-2xl border border-gray-900 text-center space-y-2">
                        <i data-lucide="heart-off" class="w-8 h-8 text-gray-300 mx-auto"></i>
                        <p class="text-xs text-gray-500">Your wishlist is empty.</p>
                    </div>
                <?php else: ?>
                    <div class="bg-white p-4 rounded-2xl border border-gray-900 divide-y divide-gray-100 max-h-[520px] overflow-y-auto pr-1 custom-scroll-area">
                        <?php foreach ($wishlistItems as $item): ?>
                            <div class="py-3 flex items-center space-x-3 first:pt-0 last:pb-0">
                                <img src="<?= asset($item['main_image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                                    class="w-12 h-12 object-cover rounded-lg border border-gray-900 shrink-0"
                                    onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-xs font-semibold text-gray-900 truncate">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </h4>
                                    <p class="text-xs font-semibold text-red-600 mt-0.5">
                                        <?= format_price($item['sale_price'] ?: $item['price']) ?>
                                    </p>
                                </div>
                                <a href="<?= url('product/' . $item['slug']) ?>"
                                    class="p-1.5 bg-gray-100 hover:bg-red-600 hover:text-white rounded-lg text-gray-600 transition">
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>