<?php
include __DIR__ . '/layouts/header.php';
?>

<div class="bg-theme-bg py-12 min-h-screen flex items-center justify-center">
    <div class="container mx-auto px-4 max-w-2xl">
        <div class="bg-white rounded-3xl p-8 lg:p-12 text-center shadow-xl border border-gray-900 space-y-6">

            <div
                class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
                <i data-lucide="check-circle" class="w-10 h-10"></i>
            </div>

            <div>
                <span class="text-xs font-semibold text-red-600 uppercase tracking-widest">Order Confirmed</span>
                <h1 class="text-3xl font-black text-gray-900 mt-1">Thank You For Shopping With ImportWale!</h1>
                <p class="text-xs text-gray-500 mt-2">Your order number is <strong
                        class="text-gray-900"><?= htmlspecialchars($order['order_number']) ?></strong></p>
            </div>

            <div class="bg-gray-50 p-6 rounded-2xl border border-gray-900 text-left text-xs space-y-3">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">Customer Name:</span>
                    <span class="font-semibold text-gray-900"><?= htmlspecialchars($order['customer_name']) ?></span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">Shipping Phone:</span>
                    <span class="font-semibold text-gray-900"><?= htmlspecialchars($order['customer_phone']) ?></span>
                </div>
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-500">Payment Status:</span>
                    <span
                        class="font-semibold text-gray-900 uppercase text-green-700"><?= htmlspecialchars($order['payment_status']) ?>
                        (<?= strtoupper($order['payment_method']) ?>)</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Total Paid Amount:</span>
                    <span class="font-black text-red-600 text-sm"><?= format_price($order['total_amount']) ?></span>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 pt-2">
                <a href="<?= url('admin/orders/invoice/' . $order['id']) ?>" target="_blank"
                    class="flex-1 py-3 bg-gray-900 text-white font-semibold text-xs rounded-xl hover:bg-black transition flex items-center justify-center space-x-1">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Print GST Tax Invoice</span>
                </a>
                <a href="<?= url('shop') ?>"
                    class="flex-1 py-3 bg-red-600 text-white font-semibold text-xs rounded-xl shadow hover:bg-red-700 transition">
                    Continue Shopping
                </a>
            </div>

        </div>
    </div>
</div>

<?php
include __DIR__ . '/layouts/footer.php';
?>