<?php
$title = 'Order Confirmed #' . htmlspecialchars($order['order_number']) . ' | ImportWale';
ob_start();
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 pt-10 pb-20 font-sans text-gray-900">

    <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center shadow-2xs space-y-6">

        <div
            class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto text-3xl font-semibold">
            ✓</div>

        <div>
            <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 tracking-tight">Order Confirmed!</h1>
            <p class="text-xs text-gray-500 mt-1">Thank you for your order. We have received your payment via Razorpay.
            </p>
        </div>

        <div
            class="bg-gray-50 rounded-xl p-4 border border-gray-100 text-xs flex flex-wrap items-center justify-around gap-4 text-left">
            <div>
                <span class="text-gray-400 block text-[11px]">Order Number:</span>
                <strong class="text-gray-900 font-semibold"><?= htmlspecialchars($order['order_number']) ?></strong>
            </div>
            <div>
                <span class="text-gray-400 block text-[11px]">Payment Status:</span>
                <span
                    class="px-2 py-0.5 bg-emerald-50 text-emerald-700 font-semibold rounded uppercase text-[10px]">PAID</span>
            </div>
            <div>
                <span class="text-gray-400 block text-[11px]">Total Paid:</span>
                <strong
                    class="text-[#f05a29] font-semibold text-sm">₹<?= number_format((float) $order['total_amount'], 2) ?></strong>
            </div>
        </div>

        <!-- Shipping & Receipt Details -->
        <div class="text-left border-t border-gray-100 pt-5 space-y-4">
            <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wider">Order Items:</h3>
            <div class="space-y-2">
                <?php foreach ($items as $item): ?>
                    <div
                        class="flex items-center justify-between text-xs p-3 bg-gray-50/70 rounded-lg border border-gray-100">
                        <div>
                            <div class="font-semibold text-gray-900"><?= htmlspecialchars($item['product_name']) ?></div>
                            <div class="text-[11px] text-gray-400">SKU: <?= htmlspecialchars($item['sku']) ?> &bull; Qty:
                                <?= $item['quantity'] ?></div>
                        </div>
                        <div class="font-semibold text-gray-900">₹<?= number_format((float) $item['total_amount'], 2) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="pt-2 text-xs text-gray-600">
                <strong class="text-gray-900 block mb-1">Shipping Address:</strong>
                <?php
                $addrObj = json_decode($order['shipping_address'], true);
                $addrStr = is_array($addrObj) ? (($addrObj['address'] ?? '') . ', ' . ($addrObj['city'] ?? '') . ', ' . ($addrObj['state'] ?? '') . ' - ' . ($addrObj['pincode'] ?? '')) : $order['shipping_address'];
                ?>
                <div><?= htmlspecialchars($order['customer_name']) ?>
                    (<?= htmlspecialchars($order['customer_phone']) ?>)</div>
                <div><?= htmlspecialchars($addrStr) ?></div>
            </div>
        </div>

        <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="<?= url('catalog') ?>"
                class="w-full sm:w-auto px-6 py-3 bg-[#f05a29] hover:bg-[#d94e20] text-white text-xs font-semibold rounded-xl shadow-xs transition">
                Continue Shopping
            </a>
        </div>

    </div>

</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>