<?php
include __DIR__ . '/layouts/header.php';

$nameVal = htmlspecialchars($savedAddress['full_name'] ?? $userData['name'] ?? '');
$emailVal = htmlspecialchars($savedAddress['email'] ?? $userData['email'] ?? '');
$phoneVal = htmlspecialchars($savedAddress['phone'] ?? $userData['phone'] ?? '');
$addr1Val = htmlspecialchars($savedAddress['address_line1'] ?? '');
$addr2Val = htmlspecialchars($savedAddress['address_line2'] ?? '');
$landmarkVal = htmlspecialchars($savedAddress['landmark'] ?? '');
$cityVal = htmlspecialchars($savedAddress['city'] ?? '');
$stateVal = htmlspecialchars($savedAddress['state'] ?? '');
$pincodeVal = htmlspecialchars($savedAddress['pincode'] ?? '');
$countryVal = htmlspecialchars($savedAddress['country'] ?? 'India');
?>

<div class="bg-theme-bg py-5 sm:py-8 min-h-screen border-b border-gray-900 font-sans"
    x-data="{ editingAddress: <?= empty($savedAddress) ? 'true' : 'false' ?>, isSubmitting: false }">
    <div class="container mx-auto px-3 sm:px-4 max-w-5xl">

        <h1 class="text-[20px] sm:text-[30px] font-semibold text-gray-900 leading-[1.2] mb-4 sm:mb-6">Checkout</h1>

        <?php if ($flashErr = (new App\Core\Session())->getFlash('error')): ?>
            <div
                class="bg-red-50 border border-red-200 text-red-700 text-xs font-semibold p-3.5 rounded-xl mb-4 sm:mb-6 flex items-center space-x-2 shadow-xs">
                <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 shrink-0"></i>
                <span><?= htmlspecialchars($flashErr) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($flashSuc = (new App\Core\Session())->getFlash('success')): ?>
            <div
                class="bg-green-50 border border-green-200 text-green-700 text-xs font-semibold p-3.5 rounded-xl mb-4 sm:mb-6 flex items-center space-x-2 shadow-xs">
                <i data-lucide="check-circle" class="w-4 h-4 text-green-600 shrink-0"></i>
                <span><?= htmlspecialchars($flashSuc) ?></span>
            </div>
        <?php endif; ?>

        <form action="<?= url('checkout/process') ?>" method="POST" id="checkout-form" @submit="isSubmitting = true"
            class="grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-8">
            <?= csrf_field() ?>

            <!-- Customer & Delivery Address Form -->
            <div class="lg:col-span-7 bg-white rounded-[10px] p-4 sm:p-6 lg:p-8 border border-gray-900 space-y-5">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-base font-semibold text-gray-900 flex items-center space-x-2">
                        <i data-lucide="truck" class="w-4 h-4 text-red-600"></i>
                        <span>1. Shipping & Contact Details</span>
                    </h3>
                    <?php if (!empty($savedAddress)): ?>
                        <button type="button" @click="editingAddress = !editingAddress"
                            class="text-xs font-semibold text-red-600 hover:text-red-700 hover:underline flex items-center space-x-1">
                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                            <span x-text="editingAddress ? 'Use Saved Address' : 'Edit Address'">Edit Address</span>
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Saved Address Preview Card (When Logged-in & Has Saved Address) -->
                <?php if (!empty($savedAddress)): ?>
                    <div x-show="!editingAddress"
                        class="bg-gray-50/80 border border-gray-900 rounded-xl p-4 space-y-2 text-xs">
                        <div class="flex items-center space-x-2">
                            <span
                                class="font-semibold text-gray-900 text-sm"><?= htmlspecialchars($savedAddress['full_name']) ?></span>
                            <span
                                class="bg-red-50 text-red-700 border border-red-200 text-[10px] font-semibold px-2 py-0.5 rounded-full uppercase">Default
                                Saved Address</span>
                        </div>
                        <p class="text-gray-700 leading-snug">
                            <?= htmlspecialchars($savedAddress['address_line1']) ?>
                            <?= !empty($savedAddress['address_line2']) ? ', ' . htmlspecialchars($savedAddress['address_line2']) : '' ?>
                            <?= !empty($savedAddress['landmark']) ? ' (Landmark: ' . htmlspecialchars($savedAddress['landmark']) . ')' : '' ?>,
                            <?= htmlspecialchars($savedAddress['city']) ?>, <?= htmlspecialchars($savedAddress['state']) ?>
                            - <span class="font-semibold"><?= htmlspecialchars($savedAddress['pincode']) ?></span>,
                            <?= htmlspecialchars($savedAddress['country'] ?? 'India') ?>
                        </p>
                        <p class="text-gray-500 text-[11px] pt-1 border-t border-gray-900/60">
                            <strong>Phone:</strong> <?= htmlspecialchars($savedAddress['phone']) ?> &nbsp;|&nbsp;
                            <strong>Email:</strong> <?= htmlspecialchars($savedAddress['email']) ?>
                        </p>
                    </div>
                <?php endif; ?>


                <!-- Complete Address Form Fields -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs"
                    x-show="editingAddress || <?= empty($savedAddress) ? 'true' : 'false' ?>">
                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-gray-700 mb-1">Full Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Jass Rughwani" value="<?= $nameVal ?>"
                            class="w-full h-12 px-4 bg-gray-50 border border-gray-900 rounded-[10px] focus:outline-none focus:border-gray-900 font-medium">
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Email Address *</label>
                        <input type="email" name="email" required placeholder="name@domain.com" value="<?= $emailVal ?>"
                            class="w-full h-12 px-4 bg-gray-50 border border-gray-900 rounded-[10px] focus:outline-none focus:border-gray-900 font-medium">
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Mobile Phone Number * (10 Digits)</label>
                        <input type="tel" name="phone" required placeholder="9876543210" maxlength="10"
                            value="<?= $phoneVal ?>"
                            class="w-full h-12 px-4 bg-gray-50 border border-gray-900 rounded-[10px] focus:outline-none focus:border-gray-900 font-medium">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-gray-700 mb-1">Address Line 1 *</label>
                        <input type="text" name="address_line1" required
                            placeholder="House/Flat No., Building Name, Street" value="<?= $addr1Val ?>"
                            class="w-full h-12 px-4 bg-gray-50 border border-gray-900 rounded-[10px] focus:outline-none focus:border-gray-900 font-medium">
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Address Line 2 (Optional)</label>
                        <input type="text" name="address_line2" placeholder="Apartment / Suite / Locality"
                            value="<?= $addr2Val ?>"
                            class="w-full h-12 px-4 bg-gray-50 border border-gray-900 rounded-[10px] focus:outline-none focus:border-gray-900 font-medium">
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Landmark (Optional)</label>
                        <input type="text" name="landmark" placeholder="e.g. Near City Hospital"
                            value="<?= $landmarkVal ?>"
                            class="w-full h-12 px-4 bg-gray-50 border border-gray-900 rounded-[10px] focus:outline-none focus:border-gray-900 font-medium">
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">City / Town *</label>
                        <input type="text" name="city" required placeholder="New Delhi" value="<?= $cityVal ?>"
                            class="w-full h-12 px-4 bg-gray-50 border border-gray-900 rounded-[10px] focus:outline-none focus:border-gray-900 font-medium">
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">State *</label>
                        <input type="text" name="state" required placeholder="Delhi" value="<?= $stateVal ?>"
                            class="w-full h-12 px-4 bg-gray-50 border border-gray-900 rounded-[10px] focus:outline-none focus:border-gray-900 font-medium">
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Pincode * (6 Digits)</label>
                        <input type="text" name="pincode" required placeholder="110039" maxlength="6"
                            value="<?= $pincodeVal ?>"
                            class="w-full h-12 px-4 bg-gray-50 border border-gray-900 rounded-[10px] focus:outline-none focus:border-gray-900 font-medium">
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Country *</label>
                        <input type="text" name="country" required readonly value="<?= $countryVal ?>"
                            class="w-full h-12 px-4 bg-gray-100 border border-gray-900 rounded-[10px] text-gray-600 font-semibold cursor-not-allowed">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block font-semibold text-gray-700 mb-1">Order Notes / Delivery Instructions
                            (Optional)</label>
                        <textarea name="order_notes" rows="2" placeholder="e.g. Leave package with security guard"
                            class="w-full p-3 bg-gray-50 border border-gray-900 rounded-[10px] focus:outline-none focus:border-gray-900 font-medium"></textarea>
                    </div>
                </div>

                <!-- Hidden inputs if not editing to ensure form submit passes saved address -->
                <?php if (!empty($savedAddress)): ?>
                    <template x-if="!editingAddress">
                        <div>
                            <input type="hidden" name="name" value="<?= $nameVal ?>">
                            <input type="hidden" name="email" value="<?= $emailVal ?>">
                            <input type="hidden" name="phone" value="<?= $phoneVal ?>">
                            <input type="hidden" name="address_line1" value="<?= $addr1Val ?>">
                            <input type="hidden" name="address_line2" value="<?= $addr2Val ?>">
                            <input type="hidden" name="landmark" value="<?= $landmarkVal ?>">
                            <input type="hidden" name="city" value="<?= $cityVal ?>">
                            <input type="hidden" name="state" value="<?= $stateVal ?>">
                            <input type="hidden" name="pincode" value="<?= $pincodeVal ?>">
                            <input type="hidden" name="country" value="<?= $countryVal ?>">
                        </div>
                    </template>
                <?php endif; ?>

                <!-- B2B GST Details Option -->
                <div class="border-t border-gray-100 pt-4 space-y-3" x-data="{ wantGst: false }">
                    <label class="flex items-center space-x-2 text-xs font-semibold text-gray-700 cursor-pointer">
                        <input type="checkbox" x-model="wantGst" class="rounded text-red-600 focus:ring-red-500">
                        <span>Add Business GSTIN for Tax Credit (Optional)</span>
                    </label>

                    <div x-show="wantGst" class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-2" x-cloak>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Company Name</label>
                            <input type="text" name="company_name" placeholder="Rughwani Enterprises"
                                class="w-full h-12 px-4 bg-gray-50 border border-gray-900 rounded-[10px]">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">GSTIN Number</label>
                            <input type="text" name="gstin" placeholder="07FLOPR6641L1Z8"
                                class="w-full h-12 px-4 bg-gray-50 border border-gray-900 rounded-[10px] uppercase">
                        </div>
                    </div>
                </div>

                <!-- Payment Method Options -->
                <div class="border-t border-gray-100 pt-4 space-y-3">
                    <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wider">2. Select Payment Method
                    </h3>

                    <div class="space-y-2 text-xs">
                        <label
                            class="flex items-center space-x-3 p-4 bg-gray-50 border border-gray-900 rounded-[10px] cursor-pointer hover:bg-gray-100 transition">
                            <input type="radio" name="payment_method" value="cod" checked
                                class="text-red-600 focus:ring-red-500">
                            <div>
                                <span class="font-semibold text-gray-900 block">Cash On Delivery (COD)</span>
                                <span class="text-gray-500">Pay cash directly to courier agent upon parcel
                                    delivery.</span>
                            </div>
                        </label>

                        <label
                            class="flex items-center space-x-3 p-4 bg-gray-50 border border-gray-900 rounded-[10px] cursor-pointer hover:bg-gray-100 transition">
                            <input type="radio" name="payment_method" value="razorpay"
                                class="text-red-600 focus:ring-red-500">
                            <div>
                                <span class="font-semibold text-gray-900 block">Online Payment (UPI, Credit/Debit Card,
                                    Net Banking)</span>
                                <span class="text-gray-500">Secured by Razorpay / PhonePe Payment Gateway.</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Order Review Sidebar -->
            <div class="lg:col-span-5 bg-white rounded-[10px] p-4 sm:p-6 lg:p-8 border border-gray-900 space-y-5 h-fit">
                <h3 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-3">3. Order Summary</h3>

                <div class="space-y-3 max-h-60 overflow-y-auto pr-1">
                    <?php foreach ($cart['items'] as $item): ?>
                        <div class="flex items-center space-x-3 text-xs border-b border-gray-100 pb-2">
                            <img src="<?= $item['image'] ?>"
                                class="w-12 h-12 object-contain bg-gray-50 p-1 rounded-[10px] border border-gray-900 shrink-0">
                            <div class="flex-1">
                                <h5 class="font-semibold text-gray-900 line-clamp-1"><?= htmlspecialchars($item['name']) ?>
                                </h5>
                                <span class="text-gray-500">Qty: <?= $item['quantity'] ?> ×
                                    <?= format_price($item['price']) ?></span>
                            </div>
                            <span
                                class="font-semibold text-gray-900"><?= format_price($item['price'] * $item['quantity']) ?></span>
                        </div>
                    <?php endforeach; ?>

                </div>

                <div class="border-t border-gray-900 pt-4 space-y-2 text-xs">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-semibold text-gray-900"><?= format_price($cart['subtotal']) ?></span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Pan-India Courier Shipping</span>
                        <span
                            class="font-semibold text-gray-900"><?= $cart['shipping_charge'] == 0 ? 'FREE' : format_price($cart['shipping_charge']) ?></span>
                    </div>
                    <div
                        class="bg-red-50/70 border border-red-200/80 text-theme-primary text-[11px] p-2 rounded-lg font-medium flex items-center space-x-1.5 mt-1">
                        <i data-lucide="info" class="w-3.5 h-3.5 shrink-0"></i>
                        <span>Price <strong>Inclusive of GST</strong></span>
                    </div>
                    <?php if ($cart['discount'] > 0): ?>
                        <div class="flex justify-between text-green-700 font-semibold">
                            <span>Discount</span>
                            <span>-<?= format_price($cart['discount']) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="border-t border-gray-900 pt-3 flex justify-between text-sm font-semibold text-gray-900">
                        <span>Total Payable</span>
                        <span class="text-red-600"><?= format_price($cart['grand_total']) ?></span>
                    </div>
                </div>

                <button type="submit" id="btn-place-order" :disabled="isSubmitting"
                    class="w-full h-12 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold text-xs rounded-[10px] transition flex items-center justify-center space-x-2 shadow-xs disabled:opacity-50 cursor-pointer">
                    <span x-text="isSubmitting ? 'Processing Order...' : 'Place Order Now'">Place Order Now</span>
                    <i data-lucide="arrow-right" class="w-4 h-4" x-show="!isSubmitting"></i>
                </button>
            </div>

        </form>

    </div>
</div>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('checkout-form');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            const paymentMethod = form.querySelector('input[name="payment_method"]:checked')?.value || 'cod';

            if (paymentMethod === 'razorpay') {
                e.preventDefault();

                const formData = new FormData(form);

                fetch('<?= url("api/checkout/create-order") ?>', {
                    method: 'POST',
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) {
                            alert(data.message || 'Unable to start payment. Please try again.');
                            if (window.Alpine) {
                                const alpineEl = document.querySelector('[x-data]');
                                if (alpineEl && alpineEl._x_dataStack) alpineEl._x_dataStack[0].isSubmitting = false;
                            }
                            return;
                        }

                        // Launch Razorpay Standard Checkout Modal
                        const options = {
                            "key": data.key_id,
                            "amount": data.amount,
                            "currency": data.currency || "INR",
                            "name": "Mudsor",
                            "description": "Order #" + data.order_number,
                            "order_id": data.razorpay_order_id,
                            "handler": function (response) {
                                // Send payment_id, order_id, signature to backend for HMAC verification
                                fetch('<?= url("api/checkout/verify-payment") ?>', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                                    body: new URLSearchParams({
                                        'razorpay_order_id': response.razorpay_order_id,
                                        'razorpay_payment_id': response.razorpay_payment_id,
                                        'razorpay_signature': response.razorpay_signature
                                    })
                                })
                                    .then(r => r.json())
                                    .then(vData => {
                                        if (vData.success) {
                                            window.location.href = vData.redirect_url;
                                        } else {
                                            alert(vData.message || 'Payment verification failed. Please contact support.');
                                            if (window.Alpine) {
                                                const alpineEl = document.querySelector('[x-data]');
                                                if (alpineEl && alpineEl._x_dataStack) alpineEl._x_dataStack[0].isSubmitting = false;
                                            }
                                        }
                                    })
                                    .catch(err => {
                                        alert('Network error verifying payment. Please contact support.');
                                        if (window.Alpine) {
                                            const alpineEl = document.querySelector('[x-data]');
                                            if (alpineEl && alpineEl._x_dataStack) alpineEl._x_dataStack[0].isSubmitting = false;
                                        }
                                    });
                            },
                            "modal": {
                                "ondismiss": function () {
                                    alert('Payment cancelled. Your order has not been placed.');
                                    if (window.Alpine) {
                                        const alpineEl = document.querySelector('[x-data]');
                                        if (alpineEl && alpineEl._x_dataStack) alpineEl._x_dataStack[0].isSubmitting = false;
                                    }
                                }
                            },
                            "prefill": {
                                "name": data.customer_name,
                                "email": data.customer_email,
                                "contact": data.customer_phone
                            },
                            "theme": {
                                "color": "#A8111C"
                            }
                        };

                        try {
                            const rzp = new Razorpay(options);
                            rzp.on('payment.failed', function (response) {
                                alert('Payment failed. Please try again.');
                                if (window.Alpine) {
                                    const alpineEl = document.querySelector('[x-data]');
                                    if (alpineEl && alpineEl._x_dataStack) alpineEl._x_dataStack[0].isSubmitting = false;
                                }
                            });
                            rzp.open();
                        } catch (err) {
                            alert('Payment gateway could not be loaded. Please try again.');
                            if (window.Alpine) {
                                const alpineEl = document.querySelector('[x-data]');
                                if (alpineEl && alpineEl._x_dataStack) alpineEl._x_dataStack[0].isSubmitting = false;
                            }
                        }
                    })
                    .catch(err => {
                        alert('Unable to start payment. Please try again.');
                        if (window.Alpine) {
                            const alpineEl = document.querySelector('[x-data]');
                            if (alpineEl && alpineEl._x_dataStack) alpineEl._x_dataStack[0].isSubmitting = false;
                        }
                    });
            }
        });
    });
</script>

<?php
include __DIR__ . '/layouts/footer.php';
?>