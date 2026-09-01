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
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-white flex items-center justify-center text-[11px] font-bold">✓</span>
                <span>Bag</span>
            </a>
            <div class="w-8 sm:w-16 h-0.5 bg-[#f05a29]"></div>

            <!-- Step 2: Address (Active) -->
            <div class="flex items-center gap-2 text-[#f05a29]">
                <span class="w-6 h-6 rounded-full bg-[#f05a29] text-white flex items-center justify-center text-[11px] font-bold shadow-xs">2</span>
                <span class="font-bold text-gray-900">Address</span>
            </div>
            <div class="w-8 sm:w-16 h-0.5 bg-gray-200"></div>

            <!-- Step 3: Payment -->
            <div class="flex items-center gap-2 text-gray-400">
                <span class="w-6 h-6 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-[11px] font-bold">3</span>
                <span>Payment</span>
            </div>
        </div>
    </div>

    <form id="checkoutForm" onsubmit="processCheckout(event)" class="flex flex-col lg:flex-row gap-8 items-start">
        
        <!-- Left: Shipping Address & Contact Details -->
        <div class="flex-1 space-y-5 w-full">
            <!-- Shipping Card -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-2xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-full bg-[#f05a29] text-white flex items-center justify-center font-bold">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z"/></svg>
                        </span>
                        <span>Shipping &amp; Delivery Address</span>
                    </h3>
                    <span class="text-[10px] bg-emerald-50 text-emerald-700 font-bold px-2 py-0.5 rounded border border-emerald-200">Verified</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div class="sm:col-span-2">
                        <label class="block font-bold text-gray-700 uppercase mb-1">Full Name / Company Name *</label>
                        <input type="text" name="customer_name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required placeholder="e.g. Shivam Madhwal / Apex Trading Co." class="w-full h-11 px-4 bg-gray-50/80 border border-gray-300 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Mobile / WhatsApp Number *</label>
                        <input type="tel" name="customer_phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required placeholder="e.g. 9876543210" class="w-full h-11 px-4 bg-gray-50/80 border border-gray-300 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Email Address *</label>
                        <input type="email" name="customer_email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required placeholder="e.g. shivam@example.com" class="w-full h-11 px-4 bg-gray-50/80 border border-gray-300 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-bold text-gray-700 uppercase mb-1">Flat / House No., Building / Area *</label>
                        <input type="text" name="shipping_address" required placeholder="9b sec 1 vaishali ghaziabad, Near gurudwara" class="w-full h-11 px-4 bg-gray-50/80 border border-gray-300 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">City *</label>
                        <input type="text" name="city" required placeholder="e.g. Ghaziabad" class="w-full h-11 px-4 bg-gray-50/80 border border-gray-300 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">State *</label>
                        <input type="text" name="state" required placeholder="e.g. Uttar Pradesh" class="w-full h-11 px-4 bg-gray-50/80 border border-gray-300 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 uppercase mb-1">Pincode *</label>
                        <input type="text" name="pincode" required placeholder="e.g. 201010" class="w-full h-11 px-4 bg-gray-50/80 border border-gray-300 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100 flex items-center gap-2 text-xs text-gray-600">
                    <input type="checkbox" id="billingCheck" checked class="rounded border-gray-300 text-[#f05a29] focus:ring-0">
                    <label for="billingCheck" class="cursor-pointer select-none">Use same address for billing</label>
                </div>
            </div>

            <!-- Payment Method Selection Card -->
            <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-2xs space-y-4">
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2 border-b border-gray-100 pb-3">
                    <span class="w-6 h-6 rounded-full bg-[#f05a29] text-white flex items-center justify-center font-bold">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5pt1.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z"/></svg>
                    </span>
                    <span>Select Payment Method</span>
                </h3>

                <div class="p-4 bg-orange-50/60 border border-orange-200/80 rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#f05a29] text-white flex items-center justify-center font-bold shrink-0">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"/></svg>
                        </div>
                        <div>
                            <strong class="text-xs text-gray-900 block font-bold">Razorpay Express Payment (UPI, Cards, NetBanking)</strong>
                            <span class="text-[11px] text-gray-500">Instant activation with 100% ImportWale Trade Protection</span>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 uppercase">Recommended</span>
                </div>
            </div>
        </div>

        <!-- Right: Importerr Replica Order Summary Sidebar -->
        <div class="w-full lg:w-[380px] shrink-0 bg-white border border-gray-200 rounded-2xl p-5 sm:p-6 shadow-2xs space-y-4 lg:sticky lg:top-24">
            <div>
                <h3 class="text-base font-bold text-gray-900">Order Summary</h3>
                <p class="text-[11px] text-gray-400 mt-0.5">Price details of all items in your cart</p>
            </div>

            <!-- Items Preview Box -->
            <div class="space-y-2.5 max-h-48 overflow-y-auto pr-1">
                <?php foreach ($cartItems as $item): ?>
                    <div class="flex items-center gap-3 text-xs p-2 bg-gray-50 rounded-xl border border-gray-100">
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-10 h-10 object-cover rounded-lg border border-gray-200 shrink-0 bg-white">
                        <div class="flex-1 min-w-0">
                            <div class="font-bold text-gray-900 truncate"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="text-[10px] text-gray-500">Qty: <?= $item['quantity'] ?> &bull; ₹<?= number_format($item['unit_price'], 2) ?>/pc</div>
                        </div>
                        <div class="font-bold text-[#f05a29]">₹<?= number_format($item['item_total'], 2) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="space-y-2.5 text-xs text-gray-600 border-t border-b border-gray-100 py-3">
                <div class="flex justify-between">
                    <span>Item(s) Total</span>
                    <span class="font-bold text-gray-900">₹<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Procurement Charges</span>
                    <span class="font-bold text-emerald-600">₹0 (Free Shipping)</span>
                </div>
                <div class="flex justify-between">
                    <span>Estimated GST (18%)</span>
                    <span class="font-bold text-gray-900">₹<?= number_format($tax, 2) ?></span>
                </div>
            </div>

            <!-- Grand Total Highlight Box -->
            <div class="bg-orange-50/60 border border-orange-100 p-3.5 rounded-xl flex items-center justify-between">
                <span class="text-xs font-bold text-gray-900">Grand Total</span>
                <span class="text-xl font-bold text-[#f05a29]">₹<?= number_format($total, 2) ?></span>
            </div>

            <!-- Safe & Secure Payments Notice -->
            <div class="space-y-2 text-[11px] text-gray-500 pt-1">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1 text-emerald-600 font-bold">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                        <span>Safe &amp; Secure Payments</span>
                    </div>
                    <span class="text-[10px] text-gray-400 font-semibold">⚙️ Includes GST</span>
                </div>

                <!-- Payment Badges -->
                <div class="flex items-center gap-1 flex-wrap pt-0.5">
                    <span class="text-[9px] px-1.5 py-0.5 bg-blue-50 text-blue-700 font-extrabold rounded">VISA</span>
                    <span class="text-[9px] px-1.5 py-0.5 bg-red-50 text-red-700 font-extrabold rounded">Mastercard</span>
                    <span class="text-[9px] px-1.5 py-0.5 bg-amber-50 text-amber-700 font-extrabold rounded">RuPay</span>
                    <span class="text-[9px] px-1.5 py-0.5 bg-emerald-50 text-emerald-700 font-extrabold rounded">UPI</span>
                    <span class="text-[9px] px-1.5 py-0.5 bg-sky-50 text-sky-700 font-extrabold rounded">Amex</span>
                </div>

                <div class="text-[10px] text-gray-400 leading-tight">
                    🔒 Your payment information is encrypted and secure.
                </div>
            </div>

            <button type="submit" id="payBtn" class="w-full py-3.5 bg-[#f05a29] hover:bg-[#d94e20] text-white text-xs font-bold rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer border-0">
                <span>Continue &amp; Pay Now</span>
                <span class="text-sm">&rarr;</span>
            </button>
        </div>

    </form>

</div>

<script>
    async function processCheckout(e) {
        e.preventDefault();
        const payBtn = document.getElementById('payBtn');
        payBtn.disabled = true;
        payBtn.innerHTML = 'Initializing Payment...';

        const form = document.getElementById('checkoutForm');
        const formData = new FormData(form);

        try {
            const res = await fetch('<?= url('checkout/create-order') ?>', {
                method: 'POST',
                body: formData
            });
            const data = await res.json();

            if (!data.success) {
                alert(data.message || 'Error initializing checkout');
                payBtn.disabled = false;
                payBtn.innerHTML = 'Continue &amp; Pay Now &rarr;';
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
                    verifyPayload.append('order_id', data.order_id);
                    verifyPayload.append('razorpay_order_id', response.razorpay_order_id || data.razorpay_order_id);
                    verifyPayload.append('razorpay_payment_id', response.razorpay_payment_id || ('pay_mock_' + Math.random().toString(36).substring(7)));
                    verifyPayload.append('razorpay_signature', response.razorpay_signature || ('mock_sig_' + Math.random().toString(36).substring(7)));

                    const vRes = await fetch('<?= url('checkout/razorpay-verify') ?>', {
                        method: 'POST',
                        body: verifyPayload
                    });
                    const vData = await vRes.json();
                    if (vData.success) {
                        window.location.href = vData.redirect_url;
                    } else {
                        alert(vData.message || 'Payment verification failed');
                        payBtn.disabled = false;
                        payBtn.innerHTML = 'Continue &amp; Pay Now &rarr;';
                    }
                },
                "prefill": {
                    "name": data.customer_name,
                    "email": data.customer_email,
                    "contact": data.customer_phone
                },
                "theme": {
                    "color": "#f05a29"
                },
                "modal": {
                    "ondismiss": function() {
                        payBtn.disabled = false;
                        payBtn.innerHTML = 'Continue &amp; Pay Now &rarr;';
                    }
                }
            };

            const rzp = new Razorpay(options);
            rzp.on('payment.failed', function (response){
                alert("Payment failed: " + (response.error.description || 'Transaction declined'));
                payBtn.disabled = false;
                payBtn.innerHTML = 'Continue &amp; Pay Now &rarr;';
            });
            rzp.open();

        } catch (err) {
            alert('Connection error. Please try again.');
            payBtn.disabled = false;
            payBtn.innerHTML = 'Continue &amp; Pay Now &rarr;';
        }
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
