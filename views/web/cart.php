<?php
$title = 'Shopping Cart | ImportWale Wholesale';
ob_start();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-20 font-sans text-gray-900">

    <!-- ======================================================== -->
    <!-- STEPPER HEADER: (1) Bag --- (2) Address --- (3) Payment -->
    <!-- ======================================================== -->
    <div class="flex items-center justify-center my-6">
        <div class="flex items-center gap-3 sm:gap-6 text-xs font-semibold select-none">
            <!-- Step 1: Bag (Active) -->
            <div class="flex items-center gap-2 text-[#f05a29]">
                <span class="w-6 h-6 rounded-full bg-[#f05a29] text-white flex items-center justify-center text-[11px] font-bold shadow-xs">1</span>
                <span class="font-bold text-gray-900">Bag</span>
            </div>
            <div class="w-8 sm:w-16 h-0.5 bg-gray-200"></div>

            <!-- Step 2: Address -->
            <div class="flex items-center gap-2 text-gray-400">
                <span class="w-6 h-6 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-[11px] font-bold">2</span>
                <span>Address</span>
            </div>
            <div class="w-8 sm:w-16 h-0.5 bg-gray-200"></div>

            <!-- Step 3: Payment -->
            <div class="flex items-center gap-2 text-gray-400">
                <span class="w-6 h-6 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-[11px] font-bold">3</span>
                <span>Payment</span>
            </div>
        </div>
    </div>

    <?php if (empty($cartItems)): ?>
        <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center max-w-md mx-auto my-8 shadow-2xs space-y-4">
            <div class="w-16 h-16 bg-orange-50 text-[#f05a29] rounded-full flex items-center justify-center mx-auto shadow-2xs">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
            </div>
            <h2 class="text-lg font-bold text-gray-900">Your Shopping Cart is Empty</h2>
            <p class="text-xs text-gray-500">Explore 50,000+ wholesale products and add items to your cart.</p>
            <a href="<?= url('catalog') ?>" class="inline-block px-6 py-3 bg-[#f05a29] hover:bg-[#d94e20] text-white text-xs font-bold rounded-xl shadow-xs transition">Browse Wholesale Catalog</a>
        </div>
    <?php else: ?>
        <div class="flex flex-col lg:flex-row gap-6 items-start">
            
            <!-- Left Column: Product Cards -->
            <div class="flex-1 space-y-5 w-full">
                <?php foreach ($cartItems as $item): ?>
                    <!-- Product Card Split Layout -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs" id="cartItemRow_<?= $item['id'] ?>">
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                            
                            <!-- Left Sub-card: Product & Variant Info (7 Cols) -->
                            <div class="md:col-span-7 space-y-4 pr-0 md:pr-4 md:border-r md:border-gray-100">
                                
                                <!-- Top Row: Image, Title, Variant Link, Mode Badge -->
                                <div class="flex items-start gap-3">
                                    <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-16 h-16 object-cover rounded-lg border border-gray-200 shrink-0 bg-white shadow-2xs">
                                    
                                    <div class="flex-1 min-w-0 space-y-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <a href="<?= url('product/' . $item['slug']) ?>" class="text-xs sm:text-sm font-bold text-gray-900 hover:text-[#f05a29] transition line-clamp-2 leading-snug">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </a>
                                            <span class="px-2 py-0.5 bg-orange-50 text-[#f05a29] border border-orange-200/80 rounded-md text-[10px] font-bold uppercase shrink-0">
                                                <?= htmlspecialchars($item['pricing_mode']) ?>
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-gray-400 flex items-center gap-2">
                                            <a href="<?= url('product/' . $item['slug']) ?>" class="text-gray-500 hover:underline">Show more</a> &bull; 
                                            <a href="<?= url('product/' . $item['slug']) ?>" class="text-[#f05a29] font-medium hover:underline inline-flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/></svg>
                                                <span>Change Variant</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Inner Variant Stepper Box -->
                                <div class="relative bg-white border border-gray-200 rounded-xl p-3.5 shadow-2xs flex items-center justify-between gap-3">
                                    <!-- Left: Variant Info & Price -->
                                    <div class="flex items-center gap-3 min-w-0">
                                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="Variant" class="w-10 h-10 object-cover rounded border border-gray-200 shrink-0 bg-white">
                                        <div class="min-w-0 space-y-0.5">
                                            <?php if ($item['variant_title']): ?>
                                                <div class="inline-block px-2 py-0.5 bg-gray-100 rounded text-[11px] font-medium text-gray-700 truncate max-w-[160px]">
                                                    Color: <?= htmlspecialchars($item['variant_title']) ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="inline-block px-2 py-0.5 bg-gray-100 rounded text-[11px] font-medium text-gray-700">
                                                    Standard Edition
                                                </div>
                                            <?php endif; ?>
                                            <div class="text-xs font-bold text-[#f05a29]">
                                                ₹<?= number_format($item['unit_price'], 2) ?><span class="text-[10px] text-gray-400 font-normal">/piece</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right: Stepper & Stock -->
                                    <div class="text-right shrink-0">
                                        <div class="flex items-center border border-gray-300 rounded-lg bg-gray-50 h-8 px-1 select-none">
                                            <button type="button" onclick="updatePageCartQty(<?= $item['id'] ?>, -1)" class="w-6 h-6 flex items-center justify-center text-gray-600 hover:bg-gray-200 rounded transition font-bold text-xs cursor-pointer border-0 bg-transparent">-</button>
                                            <span id="itemQtyVal_<?= $item['id'] ?>" class="w-6 text-center text-gray-900 font-bold text-xs"><?= $item['quantity'] ?></span>
                                            <button type="button" onclick="updatePageCartQty(<?= $item['id'] ?>, 1)" class="w-6 h-6 flex items-center justify-center text-gray-600 hover:bg-gray-200 rounded transition font-bold text-xs cursor-pointer border-0 bg-transparent">+</button>
                                        </div>
                                        <div class="text-[9px] text-gray-400 mt-1">in stock</div>
                                    </div>
                                </div>

                                <!-- Remove Card Button -->
                                <div>
                                    <button type="button" onclick="removePageCartItem(<?= $item['id'] ?>)" class="w-8 h-6 border border-gray-200 rounded-lg text-red-500 hover:bg-red-50 text-xs flex items-center justify-center transition cursor-pointer" title="Delete item">
                                        ✕
                                    </button>
                                </div>

                            </div>

                            <!-- Right Sub-card: Price Details (5 Cols) -->
                            <div class="md:col-span-5 flex flex-col justify-between space-y-3">
                                <div>
                                    <h4 class="text-xs font-bold text-gray-900">Price Details</h4>
                                    
                                    <div class="flex items-center justify-between text-xs text-gray-600 mt-2">
                                        <span>Mode :</span>
                                        <span class="font-semibold text-gray-900 capitalize"><?= htmlspecialchars($item['pricing_mode']) ?></span>
                                    </div>

                                    <!-- Variants & Qty Summary Box -->
                                    <div class="border border-gray-200 rounded-xl p-3 bg-gray-50/50 space-y-1.5 my-3 text-xs">
                                        <div class="flex justify-between text-gray-600">
                                            <span>Variants :</span>
                                            <span class="font-bold text-gray-900">1</span>
                                        </div>
                                        <div class="flex justify-between text-gray-600">
                                            <span>Total Qty :</span>
                                            <span class="font-bold text-gray-900" id="itemQtyText_<?= $item['id'] ?>"><?= $item['quantity'] ?> units</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Line -->
                                <div class="pt-2 border-t border-gray-100 flex items-baseline justify-between">
                                    <span class="text-xs font-bold text-gray-900">Total :</span>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-[#f05a29]" id="itemTotalVal_<?= $item['id'] ?>">₹<?= number_format($item['item_total'], 2) ?></div>
                                        <div class="text-[10px] text-gray-400">₹<?= number_format($item['unit_price'], 2) ?>/piece</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Right Column: Order Summary Sidebar -->
            <div class="w-full lg:w-[360px] shrink-0 bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-2xs space-y-4 lg:sticky lg:top-24">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-orange-50 text-[#f05a29] flex items-center justify-center font-bold shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Order Summary</h3>
                        <p class="text-[11px] text-gray-400">Price details of all items in your cart</p>
                    </div>
                </div>

                <div class="space-y-3 text-xs text-gray-600 border-t border-b border-gray-100 py-3.5">
                    <div class="flex justify-between items-center">
                        <span class="flex items-center gap-1.5 text-gray-600">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                            <span>Item(s) Total</span>
                        </span>
                        <span class="font-bold text-gray-900" id="summarySubtotal">₹<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="flex items-center gap-1.5 text-gray-600">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/></svg>
                            <span>Procurement Charges</span>
                        </span>
                        <span class="font-bold text-gray-900">₹0</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="flex items-center gap-1.5 text-gray-600">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7.5h6m-6 3.75h6m-6 3.75h6M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                            <span>GST (18%)</span>
                        </span>
                        <span class="font-bold text-gray-900" id="summaryTaxTotal">₹<?= number_format($tax, 2) ?></span>
                    </div>
                </div>

                <!-- Grand Total Highlight Box -->
                <div class="bg-orange-50/60 border border-orange-100 p-3.5 rounded-xl flex items-center justify-between shadow-2xs">
                    <span class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-[#f05a29]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5pt1.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                        <span>Grand Total</span>
                    </span>
                    <span class="text-xl font-bold text-[#f05a29]" id="summaryTotal">₹<?= number_format($total, 2) ?></span>
                </div>

                <!-- Safe & Secure Payments Notice -->
                <div class="space-y-2 text-[11px] text-gray-500 pt-1">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1 text-emerald-600 font-bold">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                            <span>Safe &amp; Secure Payments</span>
                        </div>
                        <span class="text-[10px] text-gray-400 font-semibold flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.02M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                            <span>Includes GST</span>
                        </span>
                    </div>

                    <!-- Payment Badges Row -->
                    <div class="flex items-center gap-1 flex-wrap pt-0.5">
                        <span class="text-[9px] px-1.5 py-0.5 bg-blue-50 text-blue-700 font-extrabold rounded">VISA</span>
                        <span class="text-[9px] px-1.5 py-0.5 bg-red-50 text-red-700 font-extrabold rounded">Mastercard</span>
                        <span class="text-[9px] px-1.5 py-0.5 bg-amber-50 text-amber-700 font-extrabold rounded">RuPay</span>
                        <span class="text-[9px] px-1.5 py-0.5 bg-emerald-50 text-emerald-700 font-extrabold rounded">UPI</span>
                        <span class="text-[9px] px-1.5 py-0.5 bg-sky-50 text-sky-700 font-extrabold rounded">Amex</span>
                        <span class="text-[9px] px-1.5 py-0.5 bg-gray-100 text-gray-600 font-bold rounded">+ More</span>
                    </div>

                    <div class="text-[10px] text-gray-400 leading-tight flex items-center gap-1">
                        <svg class="w-3 h-3 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        <span>Your payment information is encrypted and secure.</span>
                    </div>
                </div>

                <a href="<?= url('checkout') ?>" class="block w-full py-3.5 bg-[#f05a29] hover:bg-[#d94e20] text-white text-xs font-bold text-center rounded-xl shadow-xs transition cursor-pointer">
                    Place Order
                </a>
            </div>

        </div>
    <?php endif; ?>

</div>

<script>
    async function updatePageCartQty(cartItemId, delta) {
        const span = document.getElementById('itemQtyVal_' + cartItemId);
        if (!span) return;
        let currentQty = parseInt(span.textContent) || 1;
        let newQty = currentQty + delta;
        if (newQty <= 0) {
            removePageCartItem(cartItemId);
            return;
        }

        const payload = new URLSearchParams();
        payload.append('cart_item_id', cartItemId);
        payload.append('quantity', newQty);

        const res = await fetch('<?= url('cart/update') ?>', { method: 'POST', body: payload });
        const data = await res.json();
        if (data.success) {
            span.textContent = newQty;
            const sub = document.getElementById('summarySubtotal');
            const taxTot = document.getElementById('summaryTaxTotal');
            const tot = document.getElementById('summaryTotal');

            const itemVal = document.getElementById('itemTotalVal_' + cartItemId);
            const itemQtyText = document.getElementById('itemQtyText_' + cartItemId);

            const updatedItem = data.items.find(i => i.id == cartItemId);
            if (updatedItem) {
                if (itemVal) itemVal.textContent = '₹' + parseFloat(updatedItem.item_total).toFixed(2);
                if (itemQtyText) itemQtyText.textContent = newQty + ' units';
            }

            const rawSubtotal = parseFloat(data.subtotal.replace(/,/g, '')) || 0;
            const computedTax = rawSubtotal * 0.18;
            const computedTotal = rawSubtotal + computedTax;

            if (sub) sub.textContent = '₹' + rawSubtotal.toLocaleString('en-IN', {minimumFractionDigits:2});
            if (taxTot) taxTot.textContent = '₹' + computedTax.toLocaleString('en-IN', {minimumFractionDigits:2});
            if (tot) tot.textContent = '₹' + computedTotal.toLocaleString('en-IN', {minimumFractionDigits:2});

            if (typeof updateHeaderCartBadge === 'function') {
                updateHeaderCartBadge(data.cart_count);
            }
        }
    }

    async function removePageCartItem(cartItemId) {
        const payload = new URLSearchParams();
        payload.append('cart_item_id', cartItemId);
        const res = await fetch('<?= url('cart/remove') ?>', { method: 'POST', body: payload });
        const data = await res.json();
        if (data.success) {
            const row = document.getElementById('cartItemRow_' + cartItemId);
            if (row) row.remove();

            const sub = document.getElementById('summarySubtotal');
            const taxTot = document.getElementById('summaryTaxTotal');
            const tot = document.getElementById('summaryTotal');

            const rawSubtotal = parseFloat(data.subtotal.replace(/,/g, '')) || 0;
            const computedTax = rawSubtotal * 0.18;
            const computedTotal = rawSubtotal + computedTax;

            if (sub) sub.textContent = '₹' + rawSubtotal.toLocaleString('en-IN', {minimumFractionDigits:2});
            if (taxTot) taxTot.textContent = '₹' + computedTax.toLocaleString('en-IN', {minimumFractionDigits:2});
            if (tot) tot.textContent = '₹' + computedTotal.toLocaleString('en-IN', {minimumFractionDigits:2});

            if (typeof updateHeaderCartBadge === 'function') {
                updateHeaderCartBadge(data.cart_count);
            }
            if (data.cart_count === 0) {
                location.reload();
            }
        }
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
