<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between bg-white p-6 rounded-3xl shadow-sm border border-gray-900">
        <div>
            <span class="text-xs font-semibold text-red-600 uppercase">Order Details</span>
            <h2 class="text-xl font-black text-gray-900"><?= htmlspecialchars($order['order_number']) ?></h2>
        </div>
        <div class="flex space-x-2">
            <a href="<?= url('admin/orders/invoice/' . $order['id']) ?>" target="_blank"
                class="px-4 py-2 bg-gray-900 text-white font-semibold text-xs rounded-xl hover:bg-black transition flex items-center space-x-1">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Print GST Tax Invoice</span>
            </a>
            <a href="<?= url('admin/orders') ?>"
                class="px-4 py-2 bg-gray-100 text-gray-700 font-semibold text-xs rounded-xl hover:bg-gray-900 transition">Back
                to List</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <!-- Status Update Form -->
        <div class="bg-white p-6 rounded-3xl shadow-sm border border-gray-900 space-y-4 text-xs">
            <h3 class="font-black text-gray-900 text-sm border-b pb-2">Update Order Lifecycle</h3>

            <form action="<?= url('admin/orders/update-status/' . $order['id']) ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Order Status</label>
                    <select name="order_status"
                        class="w-full p-3 bg-gray-50 border border-gray-300 rounded-xl font-semibold">
                        <option value="pending" <?= in_array($order['order_status'], ['pending']) ? 'selected' : '' ?>>
                            Pending</option>
                        <option value="confirmed" <?= in_array($order['order_status'], ['confirmed']) ? 'selected' : '' ?>>Confirmed</option>
                        <option value="packed" <?= in_array($order['order_status'], ['packed']) ? 'selected' : '' ?>>
                            Packed</option>
                        <option value="shipped" <?= in_array($order['order_status'], ['shipped']) ? 'selected' : '' ?>>
                            Shipped</option>
                        <option value="delivered" <?= in_array($order['order_status'], ['completed', 'delivered']) ? 'selected' : '' ?>>Delivered / Completed</option>
                        <option value="cancelled" <?= in_array($order['order_status'], ['cancelled']) ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Shipping Status</label>
                    <select name="shipping_status"
                        class="w-full p-3 bg-gray-50 border border-gray-300 rounded-xl font-semibold">
                        <option value="not_shipped" <?= ($order['shipping_status'] ?? '') === 'not_shipped' ? 'selected' : '' ?>>NOT SHIPPED</option>
                        <option value="processing" <?= ($order['shipping_status'] ?? '') === 'processing' ? 'selected' : '' ?>>PROCESSING</option>
                        <option value="shipped" <?= ($order['shipping_status'] ?? '') === 'shipped' ? 'selected' : '' ?>>
                            SHIPPED</option>
                        <option value="delivered" <?= ($order['shipping_status'] ?? '') === 'delivered' ? 'selected' : '' ?>>DELIVERED</option>
                        <option value="cancelled" <?= ($order['shipping_status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>CANCELLED</option>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Payment Status</label>
                    <select name="payment_status"
                        class="w-full p-3 bg-gray-50 border border-gray-300 rounded-xl font-semibold">
                        <option value="pending" <?= strtolower($order['payment_status'] ?? '') === 'pending' ? 'selected' : '' ?>>PENDING</option>
                        <option value="paid" <?= strtolower($order['payment_status'] ?? '') === 'paid' ? 'selected' : '' ?>>PAID</option>
                        <option value="failed" <?= strtolower($order['payment_status'] ?? '') === 'failed' ? 'selected' : '' ?>>FAILED</option>
                    </select>
                </div>

                <button type="submit"
                    class="w-full py-3 bg-red-600 text-white font-semibold rounded-xl hover:bg-red-700 transition shadow-xs">Update
                    Status & Shipping</button>
            </form>

            <!-- Shiprocket Fulfillment Controls -->
            <div class="border-t pt-4 space-y-3">
                <h4 class="font-semibold text-gray-900 text-xs">Shiprocket Integration</h4>
                <div class="space-y-1 text-[11px] text-gray-600">
                    <div><strong>Shipping Status:</strong> <span
                            class="uppercase font-semibold text-gray-800"><?= htmlspecialchars($order['shipping_status'] ?? 'not_shipped') ?></span>
                    </div>
                    <div><strong>Shipment ID:</strong> <span
                            class="font-mono"><?= htmlspecialchars($order['shiprocket_shipment_id'] ?? 'N/A') ?></span>
                    </div>
                    <div><strong>AWB Code:</strong> <span
                            class="font-mono text-red-600 font-semibold"><?= htmlspecialchars($order['awb_code'] ?? 'N/A') ?></span>
                    </div>
                </div>

                <div class="flex flex-col space-y-2 pt-1">
                    <form action="<?= url('admin/orders/retry-shiprocket/' . $order['id']) ?>" method="POST">
                        <?= csrf_field() ?>
                        <button type="submit"
                            class="w-full py-2 bg-slate-900 hover:bg-black text-white font-semibold rounded-xl text-xs flex items-center justify-center space-x-1.5 cursor-pointer">
                            <i data-lucide="refresh-cw" class="w-3.5 h-3.5"></i>
                            <span><?= empty($order['shiprocket_order_id']) ? 'Push to Shiprocket' : 'Retry Shiprocket Push' ?></span>
                        </button>
                    </form>

                    <?php if (!empty($order['shiprocket_shipment_id']) && strtolower($order['shipping_status'] ?? '') !== 'cancelled'): ?>
                        <form action="<?= url('admin/orders/cancel-shipment/' . $order['id']) ?>" method="POST"
                            data-confirm="Are you sure you want to cancel this Shiprocket shipment?">
                            <?= csrf_field() ?>
                            <button type="submit"
                                class="w-full py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-semibold rounded-xl text-xs flex items-center justify-center space-x-1.5 cursor-pointer">
                                <i data-lucide="ban" class="w-3.5 h-3.5"></i>
                                <span>Cancel Shiprocket Shipment</span>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>


        <!-- Customer & Shipping Address Info -->
        <div class="md:col-span-2 bg-white p-6 rounded-3xl shadow-sm border border-gray-900 space-y-4 text-xs">
            <h3 class="font-black text-gray-900 text-sm border-b pb-2">Customer & Shipping Information</h3>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="text-gray-400 font-semibold block">Customer Name</span>
                    <span
                        class="font-semibold text-gray-900 text-sm"><?= htmlspecialchars($order['customer_name']) ?></span>
                </div>
                <div>
                    <span class="text-gray-400 font-semibold block">Contact Phone</span>
                    <span
                        class="font-semibold text-gray-900 text-sm"><?= htmlspecialchars($order['customer_phone']) ?></span>
                </div>
                <div>
                    <span class="text-gray-400 font-semibold block">Email Address</span>
                    <span class="font-semibold text-gray-900"><?= htmlspecialchars($order['customer_email']) ?></span>
                </div>
                <div>
                    <span class="text-gray-400 font-semibold block">GSTIN / Company Name</span>
                    <span class="font-semibold text-gray-900"><?= htmlspecialchars($order['gstin'] ?: 'N/A') ?></span>
                </div>
            </div>

            <div class="border-t pt-3">
                <span class="text-gray-400 font-semibold block mb-1">Delivery Address</span>
                <p class="text-gray-800 leading-relaxed bg-gray-50 p-3 rounded-xl border border-gray-900">
                    <?= htmlspecialchars($shippingAddress['address_line1'] ?? '') ?>,
                    <?= htmlspecialchars($shippingAddress['address_line2'] ?? '') ?><br>
                    <?= htmlspecialchars($shippingAddress['city'] ?? '') ?>,
                    <?= htmlspecialchars($shippingAddress['state'] ?? '') ?> -
                    <strong><?= htmlspecialchars($shippingAddress['pincode'] ?? '') ?></strong>
                </p>
            </div>
        </div>

    </div>

    <!-- Order Items Breakdown Table -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-900 p-6 space-y-4">
        <h3 class="font-black text-gray-900 text-sm border-b pb-2">Purchased Items</h3>

        <div class="overflow-x-auto text-xs">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 border-b text-gray-500 font-semibold uppercase">
                        <th class="p-3">Item</th>
                        <th class="p-3">HSN Code</th>
                        <th class="p-3">SKU</th>
                        <th class="p-3">Unit Price (Incl. GST)</th>
                        <th class="p-3">GST %</th>
                        <th class="p-3">GST Tax (Included)</th>
                        <th class="p-3">Qty</th>
                        <th class="p-3 text-right">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item):
                        $grossPrice = (float) ($item['price'] ?? 0);
                        $gstPercent = (float) ($item['tax_percent'] ?? 18);
                        $qty = (int) ($item['quantity'] ?? 1);
                        $unitBase = $grossPrice / (1 + ($gstPercent / 100));
                        $unitTax = $grossPrice - $unitBase;
                        $lineTaxAmt = isset($item['tax_amount']) && (float) $item['tax_amount'] > 0 ? (float) $item['tax_amount'] : ($unitTax * $qty);
                        ?>
                        <tr class="border-b">
                            <td class="p-3 font-semibold text-gray-900"><?= htmlspecialchars($item['product_name']) ?></td>
                            <td class="p-3 font-mono font-semibold text-gray-700">
                                <?= htmlspecialchars($item['hsn_code'] ?? '8714.99.90') ?></td>
                            <td class="p-3 font-mono text-gray-500"><?= htmlspecialchars($item['sku']) ?></td>
                            <td class="p-3 font-mono font-semibold"><?= format_price($grossPrice) ?></td>
                            <td class="p-3 font-mono"><?= number_format($gstPercent, 0) ?>%</td>
                            <td class="p-3 font-mono text-red-600 font-semibold"><?= format_price($lineTaxAmt) ?></td>
                            <td class="p-3 font-semibold"><?= $qty ?></td>
                            <td class="p-3 text-right font-black font-mono"><?= format_price($item['total_amount']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>