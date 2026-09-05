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
                <span
                    class="w-6 h-6 rounded-full bg-[#f05a29] text-white flex items-center justify-center text-[11px] font-semibold shadow-xs">1</span>
                <span class="font-semibold text-gray-900">Bag</span>
            </div>
            <div class="w-8 sm:w-16 h-0.5 bg-gray-200"></div>

            <!-- Step 2: Address -->
            <div class="flex items-center gap-2 text-gray-400">
                <span
                    class="w-6 h-6 rounded-full bg-gray-200 text-gray-600 flex items-center justify-center text-[11px] font-semibold">2</span>
                <span>Address</span>
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

    <?php if (empty($cartItems)): ?>
        <div
            class="bg-white border border-gray-200 rounded-2xl p-12 text-center max-w-md mx-auto my-8 shadow-2xs space-y-4">
            <div
                class="w-16 h-16 bg-orange-50 text-[#f05a29] rounded-full flex items-center justify-center mx-auto shadow-2xs">
                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-900">Your Shopping Cart is Empty</h2>
            <p class="text-xs text-gray-500">Explore 50,000+ wholesale products and add items to your cart.</p>
            <a href="<?= url('catalog') ?>"
                class="inline-block px-6 py-3 bg-[#f05a29] hover:bg-[#d94e20] text-white text-xs font-semibold rounded-xl shadow-xs transition">Browse
                Wholesale Catalog</a>
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
                                    <img src="<?= htmlspecialchars($item['image']) ?>"
                                        alt="<?= htmlspecialchars($item['name']) ?>"
                                        class="w-16 h-16 object-cover rounded-lg border border-gray-200 shrink-0 bg-white shadow-2xs">

                                    <div class="flex-1 min-w-0 space-y-1">
                                        <div class="flex items-start justify-between gap-2">
                                            <a href="<?= url('product/' . $item['slug']) ?>"
                                                class="text-xs sm:text-sm font-semibold text-gray-900 hover:text-[#f05a29] transition line-clamp-2 leading-snug">
                                                <?= htmlspecialchars($item['name']) ?>
                                            </a>
                                            <span
                                                class="px-2 py-0.5 bg-orange-50 text-[#f05a29] border border-orange-200/80 rounded-md text-[10px] font-semibold uppercase shrink-0">
                                                <?= htmlspecialchars($item['pricing_mode']) ?>
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-gray-400 flex items-center gap-2">
                                            <a href="<?= url('product/' . $item['slug']) ?>"
                                                class="text-gray-500 hover:underline">Show more</a> &bull;
                                            <a href="<?= url('product/' . $item['slug']) ?>"
                                                class="text-[#f05a29] font-medium hover:underline inline-flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                                    stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                                </svg>
                                                <span>Change Variant</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Inner Variant Stepper Box -->
                                <div
                                    class="relative bg-white border border-gray-200 rounded-xl p-3.5 shadow-2xs flex items-center justify-between gap-3">
                                    <!-- Left: Variant Info & Price -->
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="min-w-0 space-y-0.5">
                                            <?php if ($item['variant_title']): ?>
                                                <div
                                                    class="inline-block px-2 py-0.5 bg-gray-100 rounded text-[11px] font-medium text-gray-700 truncate max-w-[160px]">
                                                    Color: <?= htmlspecialchars($item['variant_title']) ?>
                                                </div>
                                            <?php else: ?>
                                                <div
                                                    class="inline-block px-2 py-0.5 bg-gray-100 rounded text-[11px] font-medium text-gray-700">
                                                    Standard Edition
                                                </div>
                                            <?php endif; ?>
                                            <div class="text-xs font-semibold text-[#f05a29]">
                                                ₹<?= number_format($item['unit_price'], 2) ?><span
                                                    class="text-[10px] text-gray-400 font-normal">/piece</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right: Stepper & Stock -->
                                    <div class="text-right shrink-0">
                                        <div
                                            class="flex items-center border border-gray-300 rounded-lg bg-gray-50 h-8 px-1 select-none">
                                            <button type="button" onclick="updatePageCartQty(<?= $item['id'] ?>, -1)"
                                                class="w-6 h-6 flex items-center justify-center text-gray-600 hover:bg-gray-200 rounded transition font-semibold text-xs cursor-pointer border-0 bg-transparent">-</button>
                                            <span id="itemQtyVal_<?= $item['id'] ?>"
                                                class="w-6 text-center text-gray-900 font-semibold text-xs"><?= $item['quantity'] ?></span>
                                            <button type="button" onclick="updatePageCartQty(<?= $item['id'] ?>, 1)"
                                                class="w-6 h-6 flex items-center justify-center text-gray-600 hover:bg-gray-200 rounded transition font-semibold text-xs cursor-pointer border-0 bg-transparent">+</button>
                                        </div>
                                        <div class="text-[9px] text-gray-400 mt-1">in stock</div>
                                    </div>
                                </div>

                                <!-- Remove Card Button -->
                                <div>
                                    <button type="button" onclick="removePageCartItem(<?= $item['id'] ?>)"
                                        class="w-8 h-6 border border-gray-200 rounded-lg text-red-500 hover:bg-red-50 text-xs flex items-center justify-center transition cursor-pointer"
                                        title="Delete item">
                                        ✕
                                    </button>
                                </div>

                            </div>

                            <!-- Right Sub-card: Price Details (5 Cols) -->
                            <div class="md:col-span-5 flex flex-col justify-between space-y-3">
                                <div>
                                    <h4 class="text-xs font-semibold text-gray-900">Price Details</h4>

                                    <div class="flex items-center justify-between text-xs text-gray-600 mt-2">
                                        <span>Mode :</span>
                                        <span
                                            class="font-semibold text-gray-900 capitalize"><?= htmlspecialchars($item['pricing_mode']) ?></span>
                                    </div>

                                    <div class="mt-4 pt-3 border-t border-dashed border-gray-200 space-y-2">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-gray-500">Variants :</span>
                                            <span class="font-semibold text-gray-900">1</span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-gray-500">Total Qty :</span>
                                            <span class="font-semibold text-gray-900"
                                                id="itemQtyText_<?= $item['id'] ?>"><?= $item['quantity'] ?> units</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="pt-3 border-t border-gray-100">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs font-semibold text-gray-900">Total :</span>
                                        <div class="text-right">
                                            <span class="text-sm font-bold text-[#f05a29]"
                                                id="itemTotalVal_<?= $item['id'] ?>">₹<?= number_format($item['item_total'], 2) ?></span>
                                            <div class="text-[10px] text-gray-400 line-through">
                                                ₹<?= number_format($item['item_total'], 2) ?>/piece</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Right Column: Order Summary (1/3 Width) -->
            <div class="space-y-4 bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs h-fit sticky top-24">
                <div class="flex items-center gap-2.5 pb-4 border-b border-gray-100">
                    <div
                        class="w-8 h-8 rounded-lg bg-orange-50 border border-orange-200/80 flex items-center justify-center text-[#f05a29]">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Order Summary</h3>
                        <p class="text-[11px] text-gray-400">Price details of all items in your cart</p>
                    </div>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between text-gray-600">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                            </svg>
                            Item(s) Total
                        </span>
                        <span class="font-semibold text-gray-900"
                            id="summarySubtotal">₹<?= number_format($subtotal, 2) ?></span>
                    </div>

                    <div class="flex items-center justify-between text-gray-600">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124l-1.47-23.706" />
                            </svg>
                            Procurement Charges
                        </span>
                        <span class="font-semibold text-gray-900">₹0</span>
                    </div>

                    <div class="flex items-center justify-between text-gray-600">
                        <span class="flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 14.25l6-6m4.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 6a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                            GST (18%)
                        </span>
                        <span class="font-semibold text-gray-900" id="summaryTaxTotal">₹<?= number_format($tax, 2) ?></span>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-900 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-[#f05a29]"></span>
                            Grand Total
                        </span>
                        <span class="text-lg font-extrabold text-[#f05a29]"
                            id="summaryTotal">₹<?= number_format($total, 2) ?></span>
                    </div>

                    <div class="flex items-center justify-between text-[11px] text-gray-400 pt-1">
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Safe & Secure Payments
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            <span>Includes GST</span>
                        </span>
                    </div>

                    <!-- Payment Badges Row -->
                    <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                        <span
                            class="px-2 py-0.5 bg-white border border-gray-200 rounded inline-flex items-center gap-1 shadow-2xs"
                            title="Mastercard">
                            <svg width="16" height="11" viewBox="0 0 24 16" fill="none">
                                <circle cx="7" cy="8" r="7" fill="#EB001B" />
                                <circle cx="17" cy="8" r="7" fill="#F79E1B" />
                                <path
                                    d="M12 2.7A6.97 6.97 0 009.6 8c0 2.2.9 4.2 2.4 5.3A6.97 6.97 0 0014.4 8c0-2.2-.9-4.2-2.4-5.3z"
                                    fill="#FF5F00" />
                            </svg>
                            <span class="text-[9px] font-bold text-gray-700">Mastercard</span>
                        </span>
                        <span
                            class="px-2 py-0.5 bg-white border border-gray-200 rounded inline-flex items-center gap-1 shadow-2xs"
                            title="UPI Instant">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                <path d="M17.4 3.6L12.9 12h3.4l-4.5 8.4 9-9.6h-3.4l4.5-7.2z" fill="#059669" />
                                <path d="M6.6 3.6L2.1 12h3.4l-4.5 8.4 9-9.6H5.5l4.5-7.2z" fill="#0284C7" />
                            </svg>
                            <span class="text-[9px] font-bold text-emerald-700">UPI</span>
                        </span>
                        <span
                            class="px-2 py-0.5 bg-white border border-gray-200 rounded inline-flex items-center gap-1 shadow-2xs"
                            title="Razorpay Gateway">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none">
                                <path d="M22.43 4.47L10.3 22H5.06l7.85-11.41L7.54 4.47h14.89z" fill="#0C2340" />
                                <path d="M15.42 4.47l-7.88 11.43L4 12.35l6.54-7.88h4.88z" fill="#0284C7" />
                            </svg>
                            <span class="text-[9px] font-bold text-blue-900">Razorpay</span>
                        </span>
                    </div>

                    <div class="text-[10px] text-gray-400 leading-tight flex items-center gap-1 pb-1">
                        <svg class="w-3 h-3 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <span>Your payment information is encrypted and secure.</span>
                    </div>
                </div>

                <!-- Action Buttons: Single Row (Place Order & Send Inquiry side-by-side) -->
                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    <a href="<?= url('checkout') ?>"
                        class="block w-full py-3 bg-[#f05a29] hover:bg-[#d94e20] text-white text-xs font-semibold text-center rounded-xl shadow-xs transition cursor-pointer flex items-center justify-center">
                        Place Order
                    </a>

                    <button type="button" onclick="openCartInquiryModal()"
                        class="block w-full py-3 bg-[#0F172A] hover:bg-black text-white text-xs font-semibold text-center rounded-xl shadow-xs transition cursor-pointer border-0 flex items-center justify-center gap-1.5">

                        <span>Send Inquiry</span>
                    </button>
                </div>
            </div>

        </div>
    <?php endif; ?>

</div>

<?php
// Pre-fill customer account details if logged in
$prefillName = '';
$prefillPhone = '';
$prefillEmail = '';
$currentUserId = get_current_user_id();
if ($currentUserId) {
    $db = \App\Core\Database::getInstance();
    $uStmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $uStmt->execute([$currentUserId]);
    $uRow = $uStmt->fetch(\PDO::FETCH_ASSOC);
    if ($uRow) {
        $prefillName = $uRow['name'] ?? '';
        $prefillPhone = $uRow['phone'] ?? '';
        $prefillEmail = $uRow['email'] ?? '';
    }
}
?>

<!-- ============================================================ -->
<!-- CART BULK QUOTE / INQUIRY MODAL POPUP -->
<!-- ============================================================ -->
<div id="cartInquiryModal"
    class="fixed inset-0 z-[999999] hidden items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs transition-opacity duration-300">
    <div class="bg-white border border-gray-200 rounded-3xl max-w-lg w-full overflow-hidden shadow-2xl transition-all transform scale-95"
        id="cartInquiryModalContainer" onclick="event.stopPropagation()">

        <!-- Header -->
        <div class="px-6 py-4 bg-[#0F172A] text-white flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-[#f05a29] flex items-center justify-center text-white shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold tracking-wide text-white">Send Inquiry</h3>
                    <p class="text-[11px] text-gray-300 mt-0.5">Submit your contact details to receive a custom
                        wholesale quote</p>
                </div>
            </div>
            <button type="button" onclick="closeCartInquiryModal()"
                class="w-7 h-7 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center text-xs transition cursor-pointer border-0">✕</button>
        </div>

        <!-- Form Body -->
        <form id="cartInquiryForm" onsubmit="submitCartInquiryForm(event)" class="p-6 space-y-4 font-sans text-xs">

            <!-- Alert Banner -->
            <div id="cartInquiryAlert" class="hidden p-3 rounded-xl text-xs font-semibold"></div>

            <!-- Cart Preview Callout Box -->
            <div
                class="bg-orange-50/60 border border-orange-200/80 rounded-2xl p-3.5 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <span
                        class="w-7 h-7 rounded-lg bg-[#f05a29] text-white font-bold flex items-center justify-center text-xs shrink-0"><?= count($cartItems ?? []) ?></span>
                    <div>
                        <div class="font-semibold text-gray-900">Items Attached From Cart</div>
                        <div class="text-[11px] text-gray-500">All products in your cart will be sent with this inquiry
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="font-semibold text-[#f05a29] text-sm">₹<?= number_format($total ?? 0, 2) ?></div>
                    <div class="text-[10px] text-gray-400 font-medium">Est. Value</div>
                </div>
            </div>

            <!-- Input: Name (Required) -->
            <div>
                <label class="block font-semibold text-gray-800 mb-1.5">Full Name <span
                        class="text-red-500">*</span></label>
                <input type="text" name="customer_name" required value="<?= htmlspecialchars($prefillName) ?>"
                    placeholder="Enter your full name"
                    class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-xs text-gray-900 font-semibold focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
            </div>

            <!-- Input: Phone Number (Required) -->
            <div>
                <label class="block font-semibold text-gray-800 mb-1.5">Phone / WhatsApp Number <span
                        class="text-red-500">*</span></label>
                <input type="tel" name="phone" required value="<?= htmlspecialchars($prefillPhone) ?>"
                    placeholder="e.g. 9876543210"
                    class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-xs text-gray-900 font-semibold focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
            </div>

            <!-- Input: Additional Message / Notes (Optional) -->
            <div>
                <label class="block font-semibold text-gray-800 mb-1.5">Additional Notes / Custom Message <span
                        class="text-gray-400 font-normal">(Optional)</span></label>
                <textarea name="customer_message" rows="3"
                    placeholder="Add target pricing, delivery location, or specific product customization notes..."
                    class="w-full px-3.5 py-2.5 bg-gray-50 border border-gray-300 rounded-xl text-xs text-gray-900 font-medium focus:outline-none focus:border-[#f05a29] focus:bg-white transition"></textarea>
            </div>

            <!-- Submit & Cancel Buttons -->
            <div class="pt-2 flex items-center gap-3">
                <button type="button" onclick="closeCartInquiryModal()"
                    class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs rounded-xl transition cursor-pointer border-0">
                    Cancel
                </button>
                <button type="submit" id="btnSubmitCartInquiry"
                    class="flex-1 py-3 bg-[#f05a29] hover:bg-[#d94e20] text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer border-0">
                    <svg id="cartInquirySpinner" class="w-4 h-4 animate-spin hidden" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <span id="cartInquiryBtnText">Submit Inquiry &rarr;</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openCartInquiryModal() {
        const modal = document.getElementById('cartInquiryModal');
        const container = document.getElementById('cartInquiryModalContainer');
        const alert = document.getElementById('cartInquiryAlert');
        if (alert) alert.classList.add('hidden');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        if (container) {
            setTimeout(() => {
                container.classList.remove('scale-95');
                container.classList.add('scale-100');
            }, 10);
        }
    }

    function closeCartInquiryModal() {
        const modal = document.getElementById('cartInquiryModal');
        const container = document.getElementById('cartInquiryModalContainer');
        if (container) {
            container.classList.remove('scale-100');
            container.classList.add('scale-95');
        }
        setTimeout(() => {
            if (modal) {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }
        }, 150);
    }

    async function submitCartInquiryForm(e) {
        e.preventDefault();
        const form = e.target;
        const btn = document.getElementById('btnSubmitCartInquiry');
        const btnText = document.getElementById('cartInquiryBtnText');
        const spinner = document.getElementById('cartInquirySpinner');
        const alert = document.getElementById('cartInquiryAlert');

        const formData = new FormData(form);
        const payload = {
            customer_name: formData.get('customer_name'),
            phone: formData.get('phone'),
            customer_message: formData.get('customer_message'),
        };

        if (!payload.customer_name || !payload.phone) {
            if (alert) {
                alert.className = 'p-3 rounded-xl text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 block';
                alert.textContent = 'Please fill in both Name and Phone Number.';
            }
            return;
        }

        if (btn) btn.disabled = true;
        if (spinner) spinner.classList.remove('hidden');
        if (btnText) btnText.textContent = 'Submitting...';

        try {
            const res = await fetch('<?= url('api/cart/inquiry-submit') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            if (data.success) {
                if (alert) {
                    alert.className = 'p-3 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 block';
                    alert.innerHTML = `<strong>Success!</strong> ${data.message} <br/><span class="text-[11px] font-normal text-emerald-700">Inquiry Number: ${data.inquiry_number}</span>`;
                }
                if (btnText) btnText.textContent = 'Submitted!';
                setTimeout(() => {
                    closeCartInquiryModal();
                    if (btn) btn.disabled = false;
                    if (spinner) spinner.classList.add('hidden');
                    if (btnText) btnText.textContent = 'Submit Inquiry \u2192';
                    form.reset();
                }, 2500);
            } else {
                if (alert) {
                    alert.className = 'p-3 rounded-xl text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 block';
                    alert.textContent = data.message || 'Failed to submit inquiry.';
                }
                if (btn) btn.disabled = false;
                if (spinner) spinner.classList.add('hidden');
                if (btnText) btnText.textContent = 'Submit Inquiry \u2192';
            }
        } catch (err) {
            if (alert) {
                alert.className = 'p-3 rounded-xl text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200 block';
                alert.textContent = 'Server error. Please try again.';
            }
            if (btn) btn.disabled = false;
            if (spinner) spinner.classList.add('hidden');
            if (btnText) btnText.textContent = 'Submit Inquiry \u2192';
        }
    }

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

            if (sub) sub.textContent = '₹' + rawSubtotal.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            if (taxTot) taxTot.textContent = '₹' + computedTax.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            if (tot) tot.textContent = '₹' + computedTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 });

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

            if (sub) sub.textContent = '₹' + rawSubtotal.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            if (taxTot) taxTot.textContent = '₹' + computedTax.toLocaleString('en-IN', { minimumFractionDigits: 2 });
            if (tot) tot.textContent = '₹' + computedTotal.toLocaleString('en-IN', { minimumFractionDigits: 2 });

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