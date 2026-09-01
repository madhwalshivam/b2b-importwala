<?php
$title = 'Orders Management | ImportWale Admin';
include __DIR__ . '/../layouts/header.php';
?>

<main class="p-6 max-w-7xl mx-auto space-y-6">

    <!-- Header & Filter Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200 shadow-2xs">
        <div>
            <h1 class="text-xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <i data-lucide="shopping-bag" class="w-6 h-6 text-[#f05a29]"></i>
                <span>Direct Online Orders</span>
            </h1>
            <p class="text-xs text-gray-500 mt-1">Manage Razorpay paid and online checkout orders</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="<?= url('admin/orders') ?>" class="px-3.5 py-2 text-xs font-semibold rounded-xl border <?= empty($currentStatus) ? 'bg-[#f05a29] text-white border-[#f05a29]' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' ?>">All</a>
            <a href="<?= url('admin/orders?status=confirmed') ?>" class="px-3.5 py-2 text-xs font-semibold rounded-xl border <?= $currentStatus === 'confirmed' ? 'bg-[#f05a29] text-white border-[#f05a29]' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' ?>">Confirmed</a>
            <a href="<?= url('admin/orders?status=shipped') ?>" class="px-3.5 py-2 text-xs font-semibold rounded-xl border <?= $currentStatus === 'shipped' ? 'bg-[#f05a29] text-white border-[#f05a29]' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' ?>">Shipped</a>
            <a href="<?= url('admin/orders?status=delivered') ?>" class="px-3.5 py-2 text-xs font-semibold rounded-xl border <?= $currentStatus === 'delivered' ? 'bg-[#f05a29] text-white border-[#f05a29]' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100' ?>">Delivered</a>
        </div>
    </div>

    <!-- Orders Table Card -->
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-2xs">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 text-gray-700 font-bold uppercase tracking-wider text-[11px] border-b border-gray-200">
                        <th class="py-3.5 px-4">Order #</th>
                        <th class="py-3.5 px-4">Customer Details</th>
                        <th class="py-3.5 px-4">Total Amount</th>
                        <th class="py-3.5 px-4">Payment Status</th>
                        <th class="py-3.5 px-4">Order Status</th>
                        <th class="py-3.5 px-4">Date</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-gray-800 font-medium">
                    <?php if (!empty($orders)): ?>
                        <?php foreach ($orders as $o): ?>
                            <tr class="hover:bg-gray-50/60 transition">
                                <td class="py-3.5 px-4 font-bold text-gray-900">
                                    <?= htmlspecialchars($o['order_number']) ?>
                                    <?php if (!empty($o['razorpay_payment_id'])): ?>
                                        <div class="text-[10px] text-gray-400 font-mono mt-0.5"><?= htmlspecialchars($o['razorpay_payment_id']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-semibold text-gray-900"><?= htmlspecialchars($o['customer_name']) ?></div>
                                    <div class="text-[11px] text-gray-500"><?= htmlspecialchars($o['customer_phone']) ?></div>
                                </td>
                                <td class="py-3.5 px-4 font-bold text-[#f05a29] text-sm">
                                    ₹<?= number_format((float)$o['total_amount'], 2) ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if ($o['payment_status'] === 'paid'): ?>
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-full font-bold text-[10px] uppercase">Paid</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-1 bg-amber-50 text-amber-700 border border-amber-200 rounded-full font-bold text-[10px] uppercase"><?= htmlspecialchars($o['payment_status']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2.5 py-1 bg-blue-50 text-blue-700 border border-blue-200 rounded-full font-bold text-[10px] uppercase"><?= htmlspecialchars($o['order_status']) ?></span>
                                </td>
                                <td class="py-3.5 px-4 text-gray-500 text-[11px]">
                                    <?= date('d M Y, h:i A', strtotime($o['created_at'])) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right space-x-1">
                                    <button type="button" onclick="viewOrderDetails(<?= (int)$o['id'] ?>)" class="px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-lg text-xs transition inline-flex items-center gap-1 cursor-pointer">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                        <span>View</span>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="py-8 text-center text-gray-400 font-medium">No direct online orders found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Order Details Modal -->
<div id="orderDetailsModal" class="fixed inset-0 bg-black/60 z-[100] hidden items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full p-6 space-y-4 shadow-2xl relative">
        <button onclick="closeOrderModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-700 text-lg">✕</button>
        <h2 class="text-lg font-bold text-gray-900 border-b border-gray-100 pb-3 flex items-center gap-2">
            <i data-lucide="package" class="w-5 h-5 text-[#f05a29]"></i>
            <span>Order Details #<span id="modalOrderNum"></span></span>
        </h2>

        <div class="grid grid-cols-2 gap-4 text-xs bg-gray-50 p-4 rounded-xl border border-gray-100">
            <div>
                <strong class="text-gray-900 block font-bold mb-1">Customer Info:</strong>
                <div id="modalCustomer"></div>
            </div>
            <div>
                <strong class="text-gray-900 block font-bold mb-1">Shipping Address:</strong>
                <div id="modalAddress"></div>
            </div>
        </div>

        <div>
            <strong class="text-xs text-gray-900 block font-bold mb-2">Ordered Items:</strong>
            <div id="modalItems" class="space-y-2 max-h-48 overflow-y-auto pr-1"></div>
        </div>

        <div class="flex items-center justify-between border-t border-gray-100 pt-3 text-xs">
            <div>
                <label class="block text-gray-600 font-semibold mb-1">Update Order Status:</label>
                <select id="modalStatusSelect" class="h-9 px-3 bg-gray-50 border border-gray-300 rounded-lg text-xs font-semibold">
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            <button type="button" onclick="saveOrderStatus()" class="px-5 h-9 bg-[#f05a29] hover:bg-[#d94e20] text-white font-bold rounded-lg shadow-xs transition">Save Status</button>
        </div>
    </div>
</div>

<script>
    let activeModalOrderId = null;

    async function viewOrderDetails(orderId) {
        activeModalOrderId = orderId;
        const res = await fetch('<?= url('admin/orders/view/') ?>' + orderId);
        const data = await res.json();
        if (!data.success) {
            alert('Error loading order');
            return;
        }

        const o = data.order;
        document.getElementById('modalOrderNum').textContent = o.order_number;
        document.getElementById('modalCustomer').innerHTML = `
            <div>${o.customer_name}</div>
            <div>Phone: ${o.customer_phone}</div>
            <div>Email: ${o.customer_email || 'N/A'}</div>
        `;
        let addrStr = o.shipping_address;
        try {
            const parsed = JSON.parse(o.shipping_address);
            if (parsed && typeof parsed === 'object') {
                addrStr = `${parsed.address || ''}, ${parsed.city || ''}, ${parsed.state || ''} - ${parsed.pincode || ''}`;
            }
        } catch(e){}

        document.getElementById('modalAddress').innerHTML = `
            <div>${addrStr}</div>
        `;

        let itemsHtml = '';
        data.items.forEach(item => {
            itemsHtml += `
                <div class="flex items-center justify-between p-2.5 bg-white border border-gray-200 rounded-lg">
                    <div>
                        <div class="font-bold text-gray-900">${item.product_name}</div>
                        <div class="text-[10px] text-gray-400">SKU: ${item.sku} &bull; Qty: ${item.quantity}</div>
                    </div>
                    <div class="font-bold text-gray-900">₹${parseFloat(item.total_amount).toFixed(2)}</div>
                </div>
            `;
        });
        document.getElementById('modalItems').innerHTML = itemsHtml;
        document.getElementById('modalStatusSelect').value = o.order_status;

        const modal = document.getElementById('orderDetailsModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeOrderModal() {
        document.getElementById('orderDetailsModal').classList.add('hidden');
        document.getElementById('orderDetailsModal').classList.remove('flex');
    }

    async function saveOrderStatus() {
        if (!activeModalOrderId) return;
        const status = document.getElementById('modalStatusSelect').value;
        const payload = new URLSearchParams();
        payload.append('order_id', activeModalOrderId);
        payload.append('order_status', status);
        payload.append('payment_status', status === 'delivered' ? 'paid' : 'paid');

        const res = await fetch('<?= url('admin/orders/update-status') ?>', {
            method: 'POST',
            body: payload
        });
        const d = await res.json();
        if (d.success) {
            alert('Order status updated');
            location.reload();
        } else {
            alert(d.message || 'Error updating status');
        }
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>