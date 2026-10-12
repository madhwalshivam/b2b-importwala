<?php
$title = 'Address & Checkout | ImportWale Wholesale';
ob_start();
?>

<!-- Razorpay Standard Checkout JS -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-20 font-sans text-gray-900">

    <!-- ======================================================== -->
    <!-- IMPORTERR STEPPER HEADER: (1) Bag --- (2) Address --- (3) Payment -->
    <!-- ======================================================== -->
    <div class="flex items-center justify-center my-6">
        <div class="flex items-center gap-3 sm:gap-6 text-xs font-semibold select-none">
            <!-- Step 1: Bag -->
            <a href="<?= url('cart') ?>" class="flex items-center gap-2 text-gray-500 hover:text-[#f05a29]">
                <span
                    class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[11px] font-semibold">✓</span>
                <span>Bag</span>
            </a>
            <div class="w-8 sm:w-16 h-0.5 bg-[#f05a29]"></div>

            <!-- Step 2: Address (Active) -->
            <div class="flex items-center gap-2 text-[#f05a29]">
                <span
                    class="w-6 h-6 rounded-full bg-[#f05a29] text-white flex items-center justify-center text-[11px] font-semibold shadow-xs">2</span>
                <span class="font-semibold text-gray-900">Address</span>
            </div>
            <div class="w-8 sm:w-16 h-0.5 bg-gray-200"></div>

            <!-- Step 3: Payment -->
            <div class="flex items-center gap-2 text-gray-400">
                <span
                    class="w-6 h-6 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-[11px] font-semibold">3</span>
                <span>Payment</span>
            </div>
        </div>
    </div>

    <?php if (!empty($checkoutError)): ?>
        <div class="max-w-xl mx-auto bg-white border border-orange-200 rounded-2xl p-6 text-sm text-gray-800">
            <h2 class="text-base font-semibold text-gray-900 mb-2">Checkout unavailable</h2>
            <p><?= htmlspecialchars($checkoutError) ?></p>
            <a href="<?= url('catalog') ?>" class="inline-block mt-4 px-4 py-2 bg-[#f05a29] text-white text-xs font-semibold rounded-xl">Continue browsing</a>
        </div>
    <?php else: ?>
    <form id="checkoutForm" onsubmit="processCheckout(event)" class="flex flex-col lg:flex-row gap-8 items-start">
        <?= csrf_field() ?>
        <input type="hidden" name="source" value="<?= htmlspecialchars($source ?? 'cart') ?>">

        <!-- Left: Shipping Address & Contact Details -->
        <div class="flex-1 space-y-5 w-full">
            <!-- Shipping Card -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                        <span
                            class="w-6 h-6 rounded-full bg-[#f05a29] text-white flex items-center justify-center font-semibold">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                        </span>
                        <span>Shipping &amp; Delivery Address</span>
                    </h3>
                    <span
                        class="text-[10px] bg-emerald-50 text-emerald-700 font-semibold px-2 py-0.5 rounded border border-emerald-200">Verified</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-gray-700 uppercase mb-1">Full Name / Company Name
                            *</label>
                        <input type="text" name="customer_name" value="<?= htmlspecialchars($user['name'] ?? '') ?>"
                            required placeholder="Full name or company name"
                            class="w-full h-11 px-4 bg-gray-50/80 border border-gray-300 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 uppercase mb-1">Mobile / WhatsApp Number
                            *</label>
                        <input type="tel" name="customer_phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                            required placeholder="10-digit mobile number"
                            class="w-full h-11 px-4 bg-gray-50/80 border border-gray-300 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 uppercase mb-1">Email Address *</label>
                        <input type="email" name="customer_email" value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                            required placeholder="Email address"
                            class="w-full h-11 px-4 bg-gray-50/80 border border-gray-300 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-gray-700 uppercase mb-1">Flat / House No., Building /
                            Area *</label>
                        <input type="text" name="shipping_address" required
                            placeholder="House no., street, area"
                            class="w-full h-11 px-4 bg-gray-50/80 border border-gray-300 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 uppercase mb-1">City *</label>
                        <input type="text" name="city" required placeholder="City"
                            class="w-full h-11 px-4 bg-gray-50/80 border border-gray-300 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 uppercase mb-1">State *</label>
                        <select name="state" id="checkoutState" required
                            class="w-full h-11 px-4 bg-gray-50/80 border border-gray-300 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                            <option value="">Select state</option>
                            <?php foreach (($states ?? \App\Services\GstCalculator::STATES) as $stateName): ?>
                                <option value="<?= htmlspecialchars($stateName) ?>"><?= htmlspecialchars($stateName) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 uppercase mb-1">Pincode *</label>
                        <input type="text" name="pincode" required placeholder="6-digit PIN code"
                            class="w-full h-11 px-4 bg-gray-50/80 border border-gray-300 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                    </div>
                </div>

                <?php if (!empty($paymentMessage)): ?>
                    <div class="rounded-xl border border-orange-200 bg-orange-50 px-3 py-2 text-xs text-gray-800"><?= htmlspecialchars($paymentMessage) ?></div>
                <?php endif; ?>
                <?php if (($displayCurrency ?? 'INR') !== 'INR'): ?>
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-700">
                        Your display currency is <?= htmlspecialchars($displayCurrency) ?>. Razorpay charges Indian Rupees only, so this order is billed at the INR catalog total shown here.
                    </div>
                <?php endif; ?>
                <div id="payMessage" class="hidden rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-xs text-gray-800"></div>

                <div class="pt-2 border-t border-gray-100 flex items-center gap-2 text-xs text-gray-600">
                    <input type="checkbox" id="billingCheck" checked
                        class="rounded border-gray-300 text-[#f05a29] focus:ring-0">
                    <label for="billingCheck" class="cursor-pointer select-none">Use same address for billing</label>
                </div>
            </div>

            <!-- Payment Method Selection Card -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-2xs space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
                    <span
                        class="w-6 h-6 rounded-full bg-[#f05a29] text-white flex items-center justify-center font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 8.25h19.5M2.25 9h19.5pt1.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                        </svg>
                    </span>
                    <span>Select Payment Method</span>
                </h3>

                <div
                    class="p-4 bg-orange-50/60 border border-orange-200/80 rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-8 h-8 rounded-lg bg-[#f05a29] text-white flex items-center justify-center font-semibold shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                            </svg>
                        </div>
                        <div>
                            <strong class="text-xs text-gray-900 block font-semibold">Razorpay Express Payment (UPI,
                                Cards, NetBanking)</strong>
                            <span class="text-[11px] text-gray-500">Instant activation with 100% ImportWale Trade
                                Protection</span>
                        </div>
                    </div>
                    <span
                        class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 uppercase">Recommended</span>
                </div>
            </div>
        </div>

        <!-- Right: Importerr Replica Order Summary Sidebar -->
        <div
            class="w-full lg:w-[380px] shrink-0 bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-2xs space-y-4 lg:sticky lg:top-[160px]">
            <div>
                <h3 class="text-base font-semibold text-gray-900">Order Summary</h3>
                <p class="text-[11px] text-gray-400 mt-0.5">Price details of all items in your cart</p>
            </div>

            <!-- Items Preview Box -->
            <div class="space-y-2.5 max-h-48 overflow-y-auto pr-1">
                <?php foreach ($cartItems as $item): ?>
                    <div class="flex items-center gap-3 text-xs p-2 bg-gray-50 rounded-xl border border-gray-100">
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>"
                            class="w-10 h-10 object-cover rounded-lg border border-gray-200 shrink-0 bg-white">
                        <div class="flex-1 min-w-0">
                            <div class="font-semibold text-gray-900 truncate"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="text-[10px] text-gray-500">Qty: <?= $item['quantity'] ?> &bull;
                                ₹<?= number_format($item['unit_price'], 2) ?>/pc</div>
                        </div>
                        <div class="font-semibold text-[#f05a29]">₹<?= number_format($item['item_total'], 2) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="space-y-2.5 text-xs text-gray-600 border-t border-b border-gray-100 py-3">
                <div class="flex justify-between">
                    <span>Item(s) Total</span>
                    <span class="font-semibold text-gray-900" id="checkoutItemsTotal">₹<?= number_format($subtotal, 2) ?></span>
                </div>
                <div id="gstBreakdown" class="space-y-2.5">
                    <?php foreach (($gst['rows'] ?? []) as $gstRow): ?>
                        <div class="flex justify-between">
                            <span><?= htmlspecialchars($gstRow['label']) ?></span>
                            <span class="font-semibold text-gray-900">₹<?= number_format((float) $gstRow['amount'], 2) ?></span>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($gst['rows'])): ?>
                        <div class="flex justify-between">
                            <span>Estimated GST</span>
                            <span class="font-semibold text-gray-900">₹<?= number_format((float) ($tax ?? 0), 2) ?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="flex justify-between">
                    <span>Procurement Charges</span>
                    <span class="font-semibold text-emerald-600">₹0 (Free Shipping)</span>
                </div>
            </div>

            <!-- Grand Total Highlight Box -->
            <div class="bg-orange-50/60 border border-orange-100 p-3.5 rounded-xl flex items-center justify-between">
                <span class="text-xs font-semibold text-gray-900">Grand Total</span>
                <span class="text-xl font-semibold text-[#f05a29]" id="checkoutGrandTotal">₹<?= number_format($total, 2) ?></span>
            </div>

            <!-- Safe & Secure Payments Notice -->
            <div class="space-y-2 text-[11px] text-gray-500 pt-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1 text-emerald-600 font-semibold">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <span>Safe &amp; Secure Payments</span>
                    </div>
                    <span class="text-[10px] text-gray-400 font-semibold flex items-center gap-1">
                        <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.02M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <span>Includes GST</span>
                    </span>
                </div>

                <!-- Payment Badges -->
                <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                    <span class="px-2 py-0.5 bg-white border border-gray-200 rounded inline-flex items-center gap-1 shadow-2xs" title="Mastercard">
                        <svg width="16" height="11" viewBox="0 0 24 16" fill="none"><circle cx="7" cy="8" r="7" fill="#EB001B"/><circle cx="17" cy="8" r="7" fill="#F79E1B"/><path d="M12 2.7A6.97 6.97 0 009.6 8c0 2.2.9 4.2 2.4 5.3A6.97 6.97 0 0014.4 8c0-2.2-.9-4.2-2.4-5.3z" fill="#FF5F00"/></svg>
                        <span class="text-[9px] font-bold text-gray-700">Mastercard</span>
                    </span>
                    <span class="px-2 py-0.5 bg-white border border-gray-200 rounded inline-flex items-center gap-1 shadow-2xs" title="UPI Instant">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M17.4 3.6L12.9 12h3.4l-4.5 8.4 9-9.6h-3.4l4.5-7.2z" fill="#059669"/><path d="M6.6 3.6L2.1 12h3.4l-4.5 8.4 9-9.6H5.5l4.5-7.2z" fill="#0284C7"/></svg>
                        <span class="text-[9px] font-bold text-emerald-700">UPI</span>
                    </span>
                    <span class="px-2 py-0.5 bg-white border border-gray-200 rounded inline-flex items-center gap-1 shadow-2xs" title="Razorpay Gateway">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><path d="M22.43 4.47L10.3 22H5.06l7.85-11.41L7.54 4.47h14.89z" fill="#0C2340"/><path d="M15.42 4.47l-7.88 11.43L4 12.35l6.54-7.88h4.88z" fill="#0284C7"/></svg>
                        <span class="text-[9px] font-bold text-blue-900">Razorpay</span>
                    </span>
                </div>

                <div class="text-[10px] text-gray-400 leading-tight flex items-center gap-1">
                    <svg class="w-3 h-3 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                    </svg>
                    <span>Your payment information is encrypted and secure.</span>
                </div>
            </div>

            <button type="submit" id="payBtn" <?= empty($paymentReady) ? 'disabled' : '' ?>
                class="w-full py-3.5 bg-[#f05a29] hover:bg-[#d94e20] disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer border-0">
                <span>Continue &amp; Pay Now</span>
                <span class="text-sm">&rarr;</span>
            </button>
        </div>

    </form>
    <?php endif; ?>

</div>

<script>
    const CHECKOUT_CSRF = <?= json_encode(csrf_token()) ?>;
    let checkoutBusy = false;

    function inr(amount) {
        return '₹' + Number(amount || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function paintGst(data) {
        const box = document.getElementById('gstBreakdown');
        const items = document.getElementById('checkoutItemsTotal');
        const grand = document.getElementById('checkoutGrandTotal');
        if (items) items.textContent = inr(data.items_total);
        if (grand) grand.textContent = inr(data.grand_total);
        if (!box) return;
        const rows = Array.isArray(data.rows) ? data.rows : [];
        box.innerHTML = rows.map((row) => (
            '<div class="flex justify-between"><span>' + row.label + '</span><span class="font-semibold text-gray-900">' + inr(row.amount) + '</span></div>'
        )).join('');
    }

    const checkoutState = document.getElementById('checkoutState');
    if (checkoutState) {
        checkoutState.addEventListener('change', async () => {
            const body = new URLSearchParams();
            body.append('_csrf_token', CHECKOUT_CSRF);
            body.append('state', checkoutState.value);
            body.append('source', document.querySelector('#checkoutForm [name="source"]')?.value || 'cart');
            try {
                const res = await fetch('<?= url('checkout/gst-quote') ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CHECKOUT_CSRF },
                    body
                });
                const data = await res.json();
                if (data.success) paintGst(data);
            } catch (err) {}
        });
    }

    function setPayMessage(text) {
        const box = document.getElementById('payMessage');
        if (!box) return;
        if (!text) {
            box.classList.add('hidden');
            box.textContent = '';
            return;
        }
        box.textContent = text;
        box.classList.remove('hidden');
    }

    function resetPayBtn() {
        const payBtn = document.getElementById('payBtn');
        if (!payBtn) return;
        payBtn.disabled = false;
        payBtn.innerHTML = 'Continue &amp; Pay Now &rarr;';
        checkoutBusy = false;
    }

    async function processCheckout(e) {
        e.preventDefault();
        if (checkoutBusy) return;
        const payBtn = document.getElementById('payBtn');
        if (!payBtn || payBtn.disabled) return;
        checkoutBusy = true;
        payBtn.disabled = true;
        payBtn.innerHTML = 'Initializing Payment...';
        setPayMessage('');

        const form = document.getElementById('checkoutForm');
        const formData = new FormData(form);

        try {
            const res = await fetch('<?= url('checkout/create-order') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CHECKOUT_CSRF },
                body: formData
            });
            const data = await res.json();

            if (!data.success) {
                setPayMessage(data.message || 'Payment could not be started.');
                resetPayBtn();
                return;
            }

            const options = {
                "key": data.razorpay_key_id,
                "amount": data.amount,
                "currency": "INR",
                "name": "ImportWale Wholesale",
                "description": "Order #" + data.order_number,
                "order_id": data.razorpay_order_id,
                "handler": async function (response) {
                    payBtn.innerHTML = 'Verifying Payment...';
                    const verifyPayload = new URLSearchParams();
                    verifyPayload.append('_csrf_token', CHECKOUT_CSRF);
                    verifyPayload.append('order_id', data.order_id);
                    verifyPayload.append('source', formData.get('source') || 'cart');
                    verifyPayload.append('razorpay_order_id', response.razorpay_order_id || '');
                    verifyPayload.append('razorpay_payment_id', response.razorpay_payment_id || '');
                    verifyPayload.append('razorpay_signature', response.razorpay_signature || '');

                    const vRes = await fetch('<?= url('checkout/razorpay-verify') ?>', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CHECKOUT_CSRF },
                        body: verifyPayload
                    });
                    const vData = await vRes.json();
                    if (vData.success && vData.redirect_url) {
                        window.location.href = vData.redirect_url;
                    } else {
                        setPayMessage(vData.message || 'Payment verification failed. You can try again.');
                        resetPayBtn();
                    }
                },
                "prefill": {
                    "name": data.customer_name,
                    "email": data.customer_email,
                    "contact": data.customer_phone
                },
                "theme": { "color": "#f05a29" },
                "modal": {
                    "ondismiss": function () {
                        setPayMessage('Payment window closed. Your order is still unpaid — you can try again.');
                        resetPayBtn();
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function (response) {
                const reason = (response.error && response.error.description) ? response.error.description : 'Payment was declined.';
                const failBody = new URLSearchParams();
                failBody.append('_csrf_token', CHECKOUT_CSRF);
                failBody.append('razorpay_order_id', data.razorpay_order_id);
                failBody.append('razorpay_payment_id', (response.error && response.error.metadata && response.error.metadata.payment_id) ? response.error.metadata.payment_id : '');
                failBody.append('reason', reason);
                fetch('<?= url('checkout/payment-failed') ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': CHECKOUT_CSRF },
                    body: failBody
                });
                setPayMessage(reason + ' You can try again.');
                resetPayBtn();
            });
            rzp.open();
        } catch (err) {
            setPayMessage('Connection error. Please try again.');
            resetPayBtn();
        }
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>