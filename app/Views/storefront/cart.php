<?php
include __DIR__ . '/layouts/header.php';
?>

<div class="bg-theme-bg py-10 min-h-screen border-b border-gray-900">
    <div class="container mx-auto px-4 max-w-7xl">

        <!-- Breadcrumb & Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <nav class="flex text-xs font-semibold text-gray-400 space-x-2 mb-1">
                    <a href="<?= url('/') ?>" class="hover:text-red-600 transition">Home</a>
                    <span>/</span>
                    <span class="text-gray-700">Cart</span>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 tracking-tight">Shopping Cart</h1>
            </div>

            <?php if (!empty($cart['items'])): ?>
                <div
                    class="inline-flex items-center space-x-2 bg-white px-4 py-2 rounded-xl border border-gray-900 shadow-xs text-xs font-semibold text-gray-700">
                    <i data-lucide="shopping-bag" class="w-4 h-4 text-red-600"></i>
                    <span><?= $cart['item_count'] ?? count($cart['items']) ?> Items in Cart</span>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($cart['items'])): ?>
            <!-- EMPTY CART STATE -->
            <div
                class="bg-white rounded-2xl p-12 sm:p-16 text-center border border-gray-900 shadow-sm max-w-2xl mx-auto space-y-4">
                <div
                    class="w-20 h-20 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
                    <i data-lucide="shopping-cart" class="w-10 h-10"></i>
                </div>
                <h2 class="text-xl font-semibold text-gray-900">Your cart is currently empty</h2>
                <p class="text-xs sm:text-sm text-gray-500 max-w-md mx-auto">Explore heavy-duty crash guards, 360° mobile
                    holders, and custom electric scooter accessories designed for Ola, Ather & TVS.</p>
                <div class="pt-2">
                    <a href="<?= url('shop') ?>"
                        class="inline-flex items-center space-x-2 h-12 px-8 bg-red-600 text-white font-semibold text-xs uppercase tracking-wider rounded-xl hover:bg-red-700 transition shadow-md hover:shadow-lg transform active:scale-95">
                        <i data-lucide="store" class="w-4 h-4"></i>
                        <span>Explore Accessories</span>
                    </a>
                </div>
            </div>
        <?php else: ?>

            <!-- CART HAS ITEMS -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

                <!-- Left: Cart Items Table -->
                <div class="lg:col-span-8 bg-white rounded-2xl p-6 border border-gray-900 shadow-xs space-y-4">
                    <div
                        class="hidden sm:grid border-b border-gray-100 pb-3 font-semibold text-[11px] text-gray-400 uppercase tracking-wider grid-cols-12 items-center">
                        <span class="col-span-5">Product Details</span>
                        <span class="col-span-2 text-center">Unit Price</span>
                        <span class="col-span-3 text-center">Quantity</span>
                        <span class="col-span-2 text-right">Subtotal</span>
                    </div>

                    <?php foreach ($cart['items'] as $item): ?>
                        <?php
                        $imgSrc = asset($item['image'] ?? '');
                        $fallbackImg = asset('assets/images/placeholder.jpg');
                        ?>

                        <!-- MOBILE ITEM CARD (sm:hidden) -->
                        <div class="sm:hidden py-4 border-b border-gray-100 space-y-3">
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-16 h-16 bg-gray-50 rounded-xl border border-gray-900 p-1 shrink-0 overflow-hidden shadow-xs flex items-center justify-center">
                                    <img src="<?= htmlspecialchars($imgSrc) ?>"
                                        onerror="this.onerror=null; this.src='<?= $fallbackImg ?>';"
                                        class="w-full h-full object-contain max-w-full max-h-full rounded-lg">
                                </div>
                                <div class="flex-1 min-w-0 pr-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <h4
                                            class="font-semibold text-gray-900 text-xs line-clamp-2 hover:text-red-600 transition">
                                            <a
                                                href="<?= url('product/' . ($item['slug'] ?? $item['id'])) ?>"><?= htmlspecialchars($item['name']) ?></a>
                                        </h4>
                                        <form action="<?= url('cart/remove') ?>" method="POST" class="shrink-0">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="text-gray-400 hover:text-red-600 p-1 transition"
                                                title="Remove product">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                    <?php if (!empty($cart['coupon_code']) && !empty($cart['applicable_product_ids']) && in_array($item['id'], $cart['applicable_product_ids'])): ?>
                                        <span
                                            class="text-[10px] font-semibold text-green-700 bg-green-50 border border-green-200 px-1.5 py-0.5 rounded inline-flex items-center space-x-1 mt-1">
                                            <i data-lucide="tag" class="w-3 h-3 text-green-600"></i>
                                            <span>Discount Applied</span>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <form action="<?= url('cart/update') ?>" method="POST" class="inline-block">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <div
                                        class="inline-flex items-center border border-gray-900 rounded-xl bg-gray-50 p-1 space-x-1 shadow-xs">
                                        <button type="button"
                                            onclick="const i = this.form.querySelector('input[name=\'quantity\']'); i.value = Math.max(0, parseInt(i.value) - 1); this.form.submit();"
                                            class="w-7 h-7 rounded-lg bg-white border border-gray-900 text-gray-700 font-semibold hover:bg-red-50 hover:text-red-600 flex items-center justify-center transition focus:outline-none shadow-2xs"
                                            title="Decrease quantity">
                                            <i data-lucide="minus" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="0"
                                            onchange="this.form.submit()"
                                            class="w-9 h-7 text-center bg-transparent font-semibold text-xs text-gray-900 focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                        <button type="button"
                                            onclick="const i = this.form.querySelector('input[name=\'quantity\']'); i.value = parseInt(i.value) + 1; this.form.submit();"
                                            class="w-7 h-7 rounded-lg bg-white border border-gray-900 text-gray-700 font-semibold hover:bg-green-50 hover:text-green-600 flex items-center justify-center transition focus:outline-none shadow-2xs"
                                            title="Increase quantity">
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </form>

                                <div class="text-right">
                                    <span class="text-[10px] text-gray-400 block font-medium">Subtotal</span>
                                    <span
                                        class="font-semibold text-red-600 text-sm"><?= format_price($item['price'] * $item['quantity']) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- DESKTOP ITEM ROW (hidden sm:grid) -->
                        <div class="hidden sm:grid grid-cols-12 items-center py-5 border-b border-gray-100 gap-3 text-xs">

                            <!-- Product Image & Meta -->
                            <div class="col-span-5 flex items-center space-x-3">
                                <div
                                    class="w-20 h-20 bg-gray-50 rounded-xl border border-gray-900 p-1 shrink-0 overflow-hidden shadow-xs flex items-center justify-center">
                                    <img src="<?= htmlspecialchars($imgSrc) ?>"
                                        onerror="this.onerror=null; this.src='<?= $fallbackImg ?>';"
                                        class="w-full h-full object-contain max-w-full max-h-full rounded-lg">
                                </div>
                                <div class="space-y-1 min-w-0">
                                    <h4
                                        class="font-semibold text-gray-900 text-xs sm:text-sm line-clamp-2 hover:text-red-600 transition">
                                        <a
                                            href="<?= url('product/' . ($item['slug'] ?? $item['id'])) ?>"><?= htmlspecialchars($item['name']) ?></a>
                                    </h4>
                                    <div class="flex items-center space-x-2">
                                        <?php if (!empty($cart['coupon_code']) && !empty($cart['applicable_product_ids']) && in_array($item['id'], $cart['applicable_product_ids'])): ?>
                                            <span
                                                class="text-[10px] font-semibold text-green-700 bg-green-50 border border-green-200 px-1.5 py-0.5 rounded inline-flex items-center space-x-1">
                                                <i data-lucide="tag" class="w-3 h-3 text-green-600"></i>
                                                <span>Discount Applied</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Price -->
                            <div class="col-span-2 text-center font-semibold text-gray-700">
                                <?= format_price($item['price']) ?>
                            </div>

                            <!-- Quantity Interactive Counter (Minus / Plus Buttons) -->
                            <div class="col-span-3 text-center">
                                <form action="<?= url('cart/update') ?>" method="POST" class="inline-block">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <div
                                        class="inline-flex items-center border border-gray-900 rounded-xl bg-gray-50 p-1 space-x-1 shadow-xs">
                                        <button type="button"
                                            onclick="const i = this.form.querySelector('input[name=\'quantity\']'); i.value = Math.max(0, parseInt(i.value) - 1); this.form.submit();"
                                            class="w-8 h-8 rounded-lg bg-white border border-gray-900 text-gray-700 font-semibold hover:bg-red-50 hover:text-red-600 flex items-center justify-center transition focus:outline-none shadow-2xs"
                                            title="Decrease quantity">
                                            <i data-lucide="minus" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="0"
                                            onchange="this.form.submit()"
                                            class="w-10 h-8 text-center bg-transparent font-semibold text-xs text-gray-900 focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none">
                                        <button type="button"
                                            onclick="const i = this.form.querySelector('input[name=\'quantity\']'); i.value = parseInt(i.value) + 1; this.form.submit();"
                                            class="w-8 h-8 rounded-lg bg-white border border-gray-900 text-gray-700 font-semibold hover:bg-green-50 hover:text-green-600 flex items-center justify-center transition focus:outline-none shadow-2xs"
                                            title="Increase quantity">
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Subtotal & Remove -->
                            <div class="col-span-2 text-right space-y-1">
                                <div class="font-semibold text-red-600 text-xs sm:text-sm">
                                    <?= format_price($item['price'] * $item['quantity']) ?>
                                </div>
                                <form action="<?= url('cart/remove') ?>" method="POST" class="inline-block">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <button type="submit"
                                        class="text-gray-400 hover:text-red-600 p-1 rounded hover:bg-red-50 transition inline-flex items-center text-[11px]"
                                        title="Remove product">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5 mr-1"></i>
                                        <span>Remove</span>
                                    </button>
                                </form>
                            </div>

                        </div>
                    <?php endforeach; ?>

                    <div class="pt-4 flex items-center justify-between">
                        <a href="<?= url('shop') ?>"
                            class="inline-flex items-center space-x-2 text-xs font-semibold text-gray-700 hover:text-red-600 transition">
                            <i data-lucide="arrow-left" class="w-4 h-4"></i>
                            <span>Continue Shopping</span>
                        </a>
                    </div>
                </div>

                <!-- Right: Order Summary Sidebar -->
                <div class="lg:col-span-4 bg-white rounded-2xl p-6 border border-gray-900 shadow-xs space-y-6">
                    <h3
                        class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-3 flex items-center justify-between">
                        <span>Order Summary</span>
                        <i data-lucide="shield-check" class="w-4 h-4 text-green-600"></i>
                    </h3>

                    <div class="space-y-3 text-xs">
                        <div class="flex justify-between text-gray-600 font-medium">
                            <span>Subtotal</span>
                            <span class="font-semibold text-gray-900"><?= format_price($cart['subtotal']) ?></span>
                        </div>
                        <div class="flex justify-between text-gray-600 font-medium">
                            <span>Pan-India Express Shipping</span>
                            <span
                                class="font-semibold text-green-600"><?= $cart['shipping_charge'] == 0 ? 'FREE' : format_price($cart['shipping_charge']) ?></span>
                        </div>
                        <div
                            class="bg-red-50/70 border border-red-200/80 text-theme-primary text-[11px] p-2.5 rounded-xl font-medium flex items-center space-x-1.5 mt-1">
                            <i data-lucide="info" class="w-3.5 h-3.5 shrink-0"></i>
                            <span>Prices are <strong>Inclusive of GST</strong>. No additional tax added.</span>
                        </div>

                        <?php if (!empty($cart['discount']) && $cart['discount'] > 0): ?>
                            <div
                                class="flex items-center justify-between text-green-700 font-semibold bg-green-50 border border-green-200 p-2.5 rounded-xl">
                                <div>
                                    <div class="flex items-center space-x-1">
                                        <i data-lucide="ticket" class="w-3.5 h-3.5"></i>
                                        <span>Coupon <?= htmlspecialchars($cart['coupon_code']) ?> Applied</span>
                                    </div>
                                    <span class="text-[10px] text-green-600 font-normal">Discount Savings</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-semibold">-<?= format_price($cart['discount']) ?></span>
                                    <form action="<?= url('cart/remove-coupon') ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="text-gray-400 hover:text-red-600 p-1"
                                            title="Remove Coupon">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="border-t border-gray-900 pt-4 flex justify-between items-baseline">
                            <span class="text-sm font-semibold text-gray-900">Grand Total</span>
                            <span class="text-xl font-black text-red-600"><?= format_price($cart['grand_total']) ?></span>
                        </div>
                    </div>

                    <!-- Coupon Code Input Form -->
                    <?php if (empty($cart['coupon_code'])): ?>
                        <form action="<?= url('cart/apply-coupon') ?>" method="POST"
                            class="space-y-2 pt-2 border-t border-gray-100">
                            <?= csrf_field() ?>
                            <label class="block text-[11px] font-semibold text-gray-700 uppercase tracking-wider">Have a Promo
                                Code?</label>
                            <div class="flex space-x-2">
                                <input type="text" name="code" placeholder="Enter Code (e.g. IMPORTWALE20)"
                                    class="flex-1 h-11 px-3 bg-gray-50 border border-gray-900 rounded-xl text-xs uppercase font-semibold text-gray-900 focus:outline-none focus:border-red-600 transition">
                                <button type="submit"
                                    class="h-11 px-5 bg-gray-900 text-white font-semibold text-xs rounded-xl hover:bg-black transition shadow-xs">Apply</button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div
                            class="pt-2 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500 font-medium">
                            <span>Promo Active: <strong
                                    class="text-gray-900 font-mono"><?= htmlspecialchars($cart['coupon_code']) ?></strong></span>
                            <form action="<?= url('cart/remove-coupon') ?>" method="POST" class="inline">
                                <?= csrf_field() ?>
                                <button type="submit" class="text-red-600 hover:underline font-semibold text-[11px]">Remove
                                    Coupon</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <!-- Checkout Button -->
                    <div class="pt-2">
                        <a href="<?= url('checkout') ?>"
                            class="flex items-center justify-center space-x-2 w-full h-12 bg-red-600 text-white font-semibold text-xs uppercase tracking-wider rounded-xl hover:bg-red-700 transition shadow-md hover:shadow-lg transform active:scale-98">
                            <span>Proceed To Checkout</span>
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </a>
                    </div>

                    <!-- Trust Badges -->
                    <div
                        class="grid grid-cols-3 gap-2 pt-4 border-t border-gray-100 text-center text-[10px] font-semibold text-gray-500">
                        <div class="space-y-1">
                            <i data-lucide="lock" class="w-4 h-4 mx-auto text-gray-400"></i>
                            <p>256-Bit SSL</p>
                        </div>
                        <div class="space-y-1">
                            <i data-lucide="truck" class="w-4 h-4 mx-auto text-gray-400"></i>
                            <p>Fast Shipping</p>
                        </div>
                        <div class="space-y-1">
                            <i data-lucide="award" class="w-4 h-4 mx-auto text-gray-400"></i>
                            <p>100% Genuine</p>
                        </div>
                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>
</div>

<?php
include __DIR__ . '/layouts/footer.php';
?>