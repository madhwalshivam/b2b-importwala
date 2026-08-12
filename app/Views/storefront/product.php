<?php
include __DIR__ . '/layouts/header.php';
$productNameClean = htmlspecialchars_decode($product['name'] ?? '');
$productDescClean = htmlspecialchars_decode($product['description'] ?? '');
$regularPrice = (float) ($product['price'] ?? 0);
$salePrice = (float) ($product['sale_price'] ?? 0);
$hasDiscount = $salePrice > 0 && $salePrice < $regularPrice;
$effectivePrice = $hasDiscount ? $salePrice : $regularPrice;
$discountPct = $hasDiscount && $regularPrice > 0 ? round((($regularPrice - $salePrice) / $regularPrice) * 100) : 0;
$savedAmount = $hasDiscount ? ($regularPrice - $salePrice) : 0;

// Cart quantity check for initial state
$inCartQty = (int) ($_SESSION['cart'][$product['id']]['quantity'] ?? 0);

// Global Cart Totals calculation for total items and total price in cart
$globalCartCount = 0;
$globalCartTotal = 0.0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $cItem) {
        $q = (int) ($cItem['quantity'] ?? 1);
        $p = (float) ($cItem['price'] ?? 0);
        $globalCartCount += $q;
        $globalCartTotal += ($q * $p);
    }
}

// Parse Description text into structured blocks
$lines = explode("\n", str_replace("\r", "", $productDescClean));
$introParagraphs = [];
$keyFeatures = [];
$parsedPackageIncludes = [];

$currentSection = 'intro';
foreach ($lines as $line) {
    $trimmed = trim($line);
    if (empty($trimmed))
        continue;

    $lower = strtolower($trimmed);
    if (str_contains($lower, 'key feature') || str_contains($lower, 'highlights') || str_contains($lower, 'features:')) {
        $currentSection = 'features';
        continue;
    }
    if (str_contains($lower, 'package include') || str_contains($lower, "what's in the box") || str_contains($lower, 'box include') || str_contains($lower, 'in the box')) {
        $currentSection = 'package';
        continue;
    }

    if ($currentSection === 'intro') {
        $introParagraphs[] = $trimmed;
    } elseif ($currentSection === 'features') {
        $cleanLine = preg_replace('/^[✅✔\-\*\•\d+\.]+\s*/u', '', $trimmed);
        if (!empty($cleanLine)) {
            $keyFeatures[] = $cleanLine;
        }
    } elseif ($currentSection === 'package') {
        $cleanLine = preg_replace('/^[✅✔\-\*\•\d+\.]+\s*/u', '', $trimmed);
        if (!empty($cleanLine)) {
            $parsedPackageIncludes[] = $cleanLine;
        }
    }
}

// Fallback for package includes from database model if available
$boxItems = !empty($includedItems) ? array_column($includedItems, 'item_name') : $parsedPackageIncludes;
?>

<!-- Breadcrumb Nav -->
<div class="bg-theme-bg border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2.5">
        <nav class="flex text-xs font-medium text-gray-500 space-x-2">
            <a href="<?= url('/') ?>" class="hover:text-red-600 transition">Home</a>
            <span>/</span>
            <a href="<?= url('shop') ?>" class="hover:text-red-600 transition">Shop</a>
            <span>/</span>
            <span class="text-gray-900 font-medium truncate max-w-xs"><?= htmlspecialchars($productNameClean) ?></span>
        </nav>
    </div>
</div>

<div class="bg-theme-bg py-4 sm:py-6 font-sans min-h-[70vh] pb-24" x-data="{
        cartQty: <?= $inCartQty ?>,
        globalCartItems: <?= $globalCartCount ?>,
        globalCartTotal: <?= $globalCartTotal ?>,
        stock: <?= (int) ($product['stock'] ?? 0) ?>,
        loading: false,
        toastMsg: '',
        showToast: false,
        csrfToken: '<?= csrf_token() ?>',

        triggerToast(msg) {
            this.toastMsg = msg;
            this.showToast = true;
            setTimeout(() => { this.showToast = false; }, 3000);
        },

        async addToCart() {
            if (this.loading) return;
            if (this.stock <= 0) {
                this.triggerToast('This product is currently out of stock.');
                return;
            }
            this.loading = true;
            try {
                const formData = new FormData();
                formData.append('_csrf_token', this.csrfToken);
                formData.append('product_id', '<?= $product['id'] ?>');
                formData.append('quantity', 1);

                const res = await fetch('<?= url('cart/add') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: formData
                });
                const data = await res.json();
                if (data.success || res.ok) {
                    this.cartQty = 1;
                    this.updateGlobalCart(data.cart);
                } else {
                    this.triggerToast(data.message || 'Error adding to cart.');
                }
            } catch (e) {
                console.error(e);
                this.triggerToast('Connection error. Please try again.');
            } finally {
                this.loading = false;
            }
        },

        updateGlobalCart(cart) {
            if (!cart) return;
            this.globalCartItems = cart.item_count || 0;
            this.globalCartTotal = cart.grand_total || cart.gross_subtotal || 0;
            if (typeof renderCartDrawerUI === 'function') {
                renderCartDrawerUI(cart);
            }
            const badges = document.querySelectorAll('#header-cart-count, .cart-count-badge');
            const count = cart.item_count || 0;
            badges.forEach(b => {
                b.innerText = count;
                if (count > 0) b.classList.remove('hidden');
                else b.classList.add('hidden');
            });
        },

        async increaseQty() {
            if (this.loading) return;
            if (this.cartQty >= this.stock) {
                this.triggerToast('Only ' + this.stock + ' item(s) available in stock.');
                return;
            }
            this.loading = true;
            const targetQty = this.cartQty + 1;
            try {
                const formData = new FormData();
                formData.append('_csrf_token', this.csrfToken);
                formData.append('product_id', '<?= $product['id'] ?>');
                formData.append('quantity', targetQty);

                const res = await fetch('<?= url('cart/update') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: formData
                });
                const data = await res.json();
                if (data.success || res.ok) {
                    this.cartQty = targetQty;
                    this.updateGlobalCart(data.cart);
                } else {
                    this.triggerToast(data.message || 'Error updating quantity.');
                }
            } catch (e) {
                console.error(e);
                this.triggerToast('Connection error. Please try again.');
            } finally {
                this.loading = false;
            }
        },

        async decreaseQty() {
            if (this.loading) return;
            this.loading = true;
            const targetQty = this.cartQty - 1;
            try {
                const formData = new FormData();
                formData.append('_csrf_token', this.csrfToken);
                formData.append('product_id', '<?= $product['id'] ?>');
                formData.append('quantity', targetQty);

                const res = await fetch('<?= url('cart/update') ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': this.csrfToken
                    },
                    body: formData
                });
                const data = await res.json();
                if (data.success || res.ok) {
                    this.cartQty = targetQty < 0 ? 0 : targetQty;
                    this.updateGlobalCart(data.cart);
                } else {
                    this.triggerToast(data.message || 'Error updating quantity.');
                }
            } catch (e) {
                console.error(e);
                this.triggerToast('Connection error. Please try again.');
            } finally {
                this.loading = false;
            }
        }
    }">

    <!-- Stock Limit Toast Alert -->
    <div x-show="showToast" x-transition
        class="fixed top-20 left-1/2 -translate-x-1/2 z-[100] bg-gray-900 text-white font-medium text-xs sm:text-sm px-4 py-3 rounded-2xl shadow-2xl flex items-center space-x-2 border border-gray-700">
        <i data-lucide="alert-circle" class="w-4 h-4 text-amber-400 shrink-0"></i>
        <span x-text="toastMsg"></span>
    </div>

    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

        <!-- MAIN PRODUCT CARD CONTAINER -->
        <div
            class="bg-white rounded-2xl p-4 sm:p-6 lg:p-8 border border-gray-200 grid grid-cols-1 lg:grid-cols-12 gap-5 lg:gap-8 items-start shadow-xs">

            <!-- LEFT: PRODUCT IMAGE GALLERY WITH TOUCH SWIPE SLIDER (NO THUMBNAILS BELOW) -->
            <?php
            $firstUrl = !empty($galleryImages) ? $galleryImages[0]['url'] : asset($product['main_image']);
            ?>
            <div class="lg:col-span-6" id="product-gallery-wrapper"
                x-data="productGalleryComponent(<?= htmlspecialchars(json_encode($firstUrl), ENT_QUOTES, 'UTF-8') ?>, <?= htmlspecialchars($galleryJson ?? '[]', ENT_QUOTES, 'UTF-8') ?>)">

                <!-- Main Hero Image Container with Touch Swipe Slider & Navigation Arrows -->
                <div class="relative overflow-hidden rounded-2xl bg-white border border-gray-100 aspect-square group select-none touch-pan-y min-h-[260px] sm:min-h-[400px]"
                    @touchstart="handleTouchStart($event)" @touchend="handleTouchEnd($event)">

                    <img :src="allImages[currentIndex] ? allImages[currentIndex].url : '<?= asset('assets/images/placeholder.jpg') ?>'"
                        alt="<?= htmlspecialchars($productNameClean) ?>"
                        class="w-full h-full object-contain p-2 sm:p-4 transition-all duration-300 ease-out select-none"
                        onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'">

                    <!-- Previous / Next Slider Arrows (Transparent background) -->
                    <template x-if="allImages.length > 1">
                        <div>
                            <button type="button" @click.stop="prevImage()"
                                class="absolute left-2 top-1/2 -translate-y-1/2 z-20 w-9 h-9 bg-transparent text-gray-700 hover:text-red-600 transition cursor-pointer flex items-center justify-center"
                                title="Previous Image">
                                <i data-lucide="chevron-left" class="w-6 h-6"></i>
                            </button>
                            <button type="button" @click.stop="nextImage()"
                                class="absolute right-2 top-1/2 -translate-y-1/2 z-20 w-9 h-9 bg-transparent text-gray-700 hover:text-red-600 transition cursor-pointer flex items-center justify-center"
                                title="Next Image">
                                <i data-lucide="chevron-right" class="w-6 h-6"></i>
                            </button>
                        </div>
                    </template>

                    <!-- Top-Right Wishlist & Share Overlay Buttons -->
                    <div class="absolute top-3 right-3 z-20 flex items-center space-x-2">
                        <?php $isWished = in_array((int) $product['id'], $wishlistProductIds ?? []); ?>
                        <button type="button" onclick="toggleWishlist(<?= (int) $product['id'] ?>, this)"
                            data-wishlist-id="<?= (int) $product['id'] ?>"
                            class="w-9 h-9 rounded-full bg-white/90 backdrop-blur-md shadow-md border border-gray-200 flex items-center justify-center text-gray-700 hover:text-red-600 transition cursor-pointer"
                            title="<?= $isWished ? 'Remove from Wishlist' : 'Save to Wishlist' ?>">
                            <i data-lucide="heart" class="w-4 h-4" <?= $isWished ? 'fill="#A8111C" style="fill:#A8111C; color:#A8111C;"' : '' ?>></i>
                        </button>
                        <button type="button"
                            onclick="navigator.clipboard.writeText(window.location.href); alert('Product link copied to clipboard!')"
                            class="w-9 h-9 rounded-full bg-white/90 backdrop-blur-md shadow-md border border-gray-200 flex items-center justify-center text-gray-700 hover:text-gray-900 transition cursor-pointer"
                            title="Share Product">
                            <i data-lucide="share-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

            </div>

            <!-- RIGHT: SUMMARY -->
            <?php $stockQty = (int)($product['stock'] ?? 0); ?>
            <div class="lg:col-span-6 flex flex-col gap-3">

                <!-- 1. PRODUCT TITLE -->
                <h1 class="text-base sm:text-lg lg:text-xl font-normal text-gray-800 leading-snug tracking-tight">
                    <?= htmlspecialchars($productNameClean) ?>
                </h1>

                <!-- 2. PRICE (left) + RATING (right) inline — Blinkit style -->
                <div class="flex items-start justify-between gap-3">
                    <!-- Price left block -->
                    <div class="flex flex-col gap-0.5">
                        <div class="flex items-baseline gap-2 flex-wrap">
                            <span class="text-2xl font-semibold text-gray-900 tracking-tight"><?= format_price($effectivePrice) ?></span>
                            <?php if ($hasDiscount): ?>
                                <span class="text-sm text-gray-400 line-through font-light"><?= format_price($regularPrice) ?></span>
                                <?php if ($discountPct > 0): ?>
                                    <span class="text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100"><?= $discountPct ?>% OFF</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                        <span class="text-[11px] text-gray-400 font-normal">Inclusive of all taxes</span>
                    </div>

                    <!-- Rating badge right -->
                    <button type="button"
                        @click="document.getElementById('reviews-accordion')?.scrollIntoView({behavior:'smooth'})"
                        class="shrink-0 inline-flex items-center gap-1 bg-gray-50 border border-gray-200 rounded-xl px-2.5 py-1.5 hover:border-gray-300 transition cursor-pointer">
                        <?php $ratingAvg = (float)($product['rating_avg'] ?? 0); ?>
                        <?php if ($ratingAvg > 0): ?>
                            <span class="text-xs font-semibold text-gray-800"><?= number_format($ratingAvg, 1) ?></span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-amber-400 fill-amber-400" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <span class="text-[10px] text-gray-400 font-normal">(<?= (int)$product['review_count'] ?>)</span>
                        <?php else: ?>
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 text-gray-300 fill-gray-300" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            <span class="text-[10px] text-gray-400 font-normal">No reviews</span>
                        <?php endif; ?>
                    </button>
                </div>

                <!-- 3. STOCK STATUS -->
                <?php if ($stockQty > 0 && $stockQty <= 20): ?>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-amber-700 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3m0 3h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            Only <?= $stockQty ?> left!
                        </span>
                        <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full rounded-full bg-amber-400" style="width:<?= min(100, ($stockQty / 20) * 100) ?>%"></div>
                        </div>
                    </div>
                <?php elseif ($stockQty > 20): ?>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                        <span class="text-[11px] font-medium text-emerald-700">In Stock</span>
                    </div>
                <?php else: ?>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                        <span class="text-[11px] font-medium text-red-600">Out of Stock</span>
                    </div>
                <?php endif; ?>

                <!-- 4. TRUST BADGES (all screens) -->
                <div class="grid grid-cols-4 gap-2 py-2 border-t border-b border-gray-100">
                    <div class="flex flex-col items-center gap-1 text-center py-2 px-1 rounded-xl bg-gray-50 border border-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        <span class="text-[10px] font-normal text-gray-400 leading-tight">All India<br>Delivery</span>
                    </div>
                    <div class="flex flex-col items-center gap-1 text-center py-2 px-1 rounded-xl bg-gray-50 border border-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span class="text-[10px] font-normal text-gray-400 leading-tight">Easy<br>Returns</span>
                    </div>
                    <div class="flex flex-col items-center gap-1 text-center py-2 px-1 rounded-xl bg-gray-50 border border-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span class="text-[10px] font-normal text-gray-400 leading-tight">COD<br>Available</span>
                    </div>
                    <div class="flex flex-col items-center gap-1 text-center py-2 px-1 rounded-xl bg-gray-50 border border-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span class="text-[10px] font-normal text-gray-400 leading-tight">Secure<br>Payment</span>
                    </div>
                </div>

                <!-- 5. SIMILAR / FREQUENTLY BOUGHT (all screens) -->
                <?php if (!empty($frequentlyBought)): ?>
                    <div class="flex items-center gap-3 overflow-x-auto scroll-smooth flex-nowrap py-1 px-0.5 scrollbar-thin snap-x snap-mandatory touch-pan-x min-w-0"
                        style="-webkit-overflow-scrolling: touch; overscroll-behavior-x: contain;">
                        <?php foreach ($frequentlyBought as $fb): ?>
                            <?php
                            $fbPrice = (float) ($fb['sale_price'] ?: $fb['price']);
                            $fbSlug = !empty($fb['slug']) ? trim($fb['slug']) : (int) $fb['id'];
                            $fbImg = !empty($fb['main_image']) ? asset($fb['main_image']) : asset('assets/images/mudsor-logo.png');
                            ?>
                            <a href="<?= url('product/' . $fbSlug) ?>" target="_self"
                                class="group/card bg-white rounded-2xl border border-gray-200 hover:border-gray-300 p-2.5 text-center transition shadow-2xs hover:shadow-md flex flex-col items-center w-28 sm:w-32 shrink-0 cursor-pointer relative snap-start">
                                <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gray-50 rounded-xl p-1 overflow-hidden flex items-center justify-center border border-gray-100 group-hover/card:scale-105 transition duration-200">
                                    <img src="<?= $fbImg ?>"
                                        onerror="this.onerror=null;this.src='<?= asset('assets/images/mudsor-logo.png') ?>';"
                                        alt="<?= htmlspecialchars($fb['name']) ?>" class="w-full h-full object-contain">
                                </div>
                                <p class="text-[10px] font-medium text-gray-600 transition truncate w-full mt-1.5"
                                    title="<?= htmlspecialchars($fb['name']) ?>">
                                    <?= htmlspecialchars($fb['name']) ?>
                                </p>
                                <span class="text-[11px] font-semibold text-gray-800 block mt-0.5"><?= format_price($fbPrice) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- 6. DESKTOP ONLY: Add to Cart Button -->
                <div class="hidden lg:flex items-center gap-3 pt-1">
                    <button type="button" x-show="cartQty <= 0" @click="addToCart()"
                        :disabled="loading || stock <= 0"
                        class="h-12 px-8 bg-red-600 hover:bg-red-700 text-white font-medium text-sm rounded-xl transition shadow-md hover:shadow-lg flex items-center justify-center space-x-2 disabled:bg-gray-400 cursor-pointer tracking-wider uppercase min-w-[220px]">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-white"></i>
                        <span x-text="loading ? 'Adding...' : (stock > 0 ? 'Add to cart' : 'Out of stock')"></span>
                    </button>
                    <div x-show="cartQty > 0" x-cloak
                        class="h-12 px-3 bg-red-600 text-white font-medium text-base rounded-xl shadow-md flex items-center justify-between space-x-4 w-44 select-none">
                        <button type="button" @click.stop="decreaseQty()" :disabled="loading"
                            class="w-9 h-9 rounded-lg hover:bg-red-700 text-white font-medium text-xl flex items-center justify-center cursor-pointer transition">-</button>
                        <span class="font-medium text-white text-lg" x-text="cartQty"></span>
                        <button type="button" @click.stop="increaseQty()" :disabled="loading"
                            class="w-9 h-9 rounded-lg hover:bg-red-700 text-white font-medium text-xl flex items-center justify-center cursor-pointer transition">+</button>
                    </div>
                </div>

            </div>
        </div>


        <!-- ACCORDION SECTION: ONLY 2 CARDS (PRODUCT DESCRIPTION & CUSTOMER REVIEWS) -->
        <div class="space-y-4" x-data="{ openAccordion: 'description' }">

            <!-- 1. Product Description Accordion Card -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-2xs overflow-hidden transition"
                id="description-accordion">
                <button type="button" @click="openAccordion = openAccordion === 'description' ? null : 'description'"
                    class="w-full px-6 py-4 flex items-center justify-between text-left font-medium text-sm text-gray-800 bg-gray-50/50 hover:bg-gray-50 transition cursor-pointer select-none">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-8 h-8 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                            <i data-lucide="file-text" class="w-4 h-4"></i>
                        </div>
                        <span>Product Description &amp; Specifications</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 transition-transform duration-200"
                        :class="openAccordion === 'description' ? 'rotate-180 text-red-600' : ''"></i>
                </button>

                <div x-show="openAccordion === 'description'" x-collapse
                    class="p-6 border-t border-gray-100 text-sm text-gray-600 leading-relaxed space-y-6">
                    <!-- Intro Paragraphs -->
                    <?php if (!empty($introParagraphs)): ?>
                        <div class="space-y-3">
                            <?php foreach ($introParagraphs as $p): ?>
                                <p><?= htmlspecialchars($p) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p><?= nl2br($productDescClean) ?></p>
                    <?php endif; ?>

                    <!-- Key Features -->
                    <?php if (!empty($keyFeatures)): ?>
                        <div class="pt-4 border-t border-gray-100 space-y-3">
                            <h4 class="font-medium text-xs uppercase tracking-wider text-gray-800">Key Features &amp;
                                Benefits</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs sm:text-sm">
                                <?php foreach ($keyFeatures as $kf): ?>
                                    <div class="flex items-start space-x-3 bg-gray-50 p-3 rounded-xl border border-gray-200">
                                        <div
                                            class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 font-bold text-xs mt-0.5">
                                            ✓
                                        </div>
                                        <span class="text-gray-700 font-normal leading-snug"><?= htmlspecialchars($kf) ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Specifications Table & Compatibility -->
                    <?php if (!empty($compatibleScooters) || !empty($specifications)): ?>
                        <div class="pt-4 border-t border-gray-100 space-y-4">
                            <?php if (!empty($compatibleScooters)): ?>
                                <div class="space-y-2">
                                    <h4 class="font-medium text-xs uppercase tracking-wider text-gray-800">Verified Vehicle
                                        Compatibility</h4>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 text-xs">
                                        <?php foreach ($compatibleScooters as $cs): ?>
                                            <div
                                                class="bg-gray-50 px-3 py-2 rounded-xl border border-gray-200 font-normal text-xs text-gray-700 flex items-center space-x-2">
                                                <span class="w-2 h-2 rounded-full bg-red-600 shrink-0"></span>
                                                <span class="truncate"><?= htmlspecialchars($cs['brand_name'] ?? '') ?>
                                                    <?= htmlspecialchars($cs['model_name'] ?? $cs['name'] ?? '') ?></span>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($specifications)): ?>
                                <div class="space-y-2">
                                    <h4 class="font-medium text-xs uppercase tracking-wider text-gray-800">Technical
                                        Specifications</h4>
                                    <div class="overflow-x-auto rounded-xl border border-gray-200">
                                        <table class="w-full text-xs text-left">
                                            <tbody>
                                                <?php foreach ($specifications as $idx => $spec): ?>
                                                    <tr
                                                        class="<?= $idx % 2 === 0 ? 'bg-gray-50/60' : 'bg-white' ?> border-b last:border-0 border-gray-100">
                                                        <th class="px-4 py-3 font-medium text-gray-800 w-1/3">
                                                            <?= htmlspecialchars($spec['spec_name'] ?? $spec['key'] ?? '') ?>
                                                        </th>
                                                        <td class="px-4 py-3 text-gray-600 font-normal">
                                                            <?= htmlspecialchars($spec['spec_value'] ?? $spec['value'] ?? '') ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Package Contents -->
                    <?php if (!empty($boxItems)): ?>
                        <div class="pt-4 border-t border-gray-100 space-y-3">
                            <h4 class="font-medium text-xs uppercase tracking-wider text-gray-800">Package Contents</h4>
                            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                <?php foreach ($boxItems as $bi): ?>
                                    <li
                                        class="flex items-center space-x-2.5 bg-gray-50 p-3 rounded-xl border border-gray-200 font-normal text-gray-700 text-xs">
                                        <i data-lucide="check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                                        <span>1x <?= htmlspecialchars($bi) ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. Customer Reviews Card -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-2xs overflow-hidden transition"
                id="reviews-accordion" x-data="{ showReviewForm: false }">
                <button type="button" @click="openAccordion = openAccordion === 'reviews' ? null : 'reviews'"
                    class="w-full px-6 py-4 flex items-center justify-between text-left font-medium text-sm text-gray-800 bg-gray-50/50 hover:bg-gray-50 transition cursor-pointer select-none">
                    <div class="flex items-center space-x-3">
                        <div
                            class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <i data-lucide="star" class="w-4 h-4 text-amber-500 fill-amber-500"></i>
                        </div>
                        <span>Customer Reviews &amp; Ratings (<?= count($reviews) ?>)</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 transition-transform duration-200"
                        :class="openAccordion === 'reviews' ? 'rotate-180 text-red-600' : ''"></i>
                </button>

                <div x-show="openAccordion === 'reviews'" x-collapse class="p-6 border-t border-gray-100 space-y-6">
                    <div
                        class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-6 border-b border-gray-100">
                        <div>
                            <h3 class="text-base font-medium text-gray-800 flex items-center space-x-2">
                                <i data-lucide="star" class="w-5 h-5 text-amber-500 fill-amber-500"></i>
                                <span>Customer Reviews &amp; Ratings</span>
                            </h3>
                            <p class="text-xs text-gray-500 mt-1">Verified buyer reviews &amp; ratings for
                                <?= htmlspecialchars($productNameClean) ?></p>
                        </div>

                        <button type="button" @click="showReviewForm = !showReviewForm"
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium text-xs rounded-xl transition shadow-xs flex items-center space-x-2 cursor-pointer shrink-0">
                            <i data-lucide="message-square-plus" class="w-4 h-4"></i>
                            <span x-text="showReviewForm ? 'Cancel Review' : 'Write a Product Review'">Write a Product
                                Review</span>
                        </button>
                    </div>

                    <!-- Overall Rating Breakdown Card -->
                    <div
                        class="grid grid-cols-1 md:grid-cols-3 gap-6 bg-gray-50 p-5 rounded-2xl border border-gray-200">
                        <div
                            class="flex flex-col items-center justify-center text-center p-4 bg-white rounded-xl border border-gray-200">
                            <span
                                class="text-3xl font-bold text-gray-800"><?= number_format((float) ($product['rating_avg'] ?? 4.8), 1) ?></span>
                            <div class="my-1.5">
                                <?= render_star_rating($product['rating_avg'] ?? 4.8, 'w-4 h-4') ?>
                            </div>
                            <span class="text-xs font-normal text-gray-500">Based on
                                <?= (int) ($product['review_count'] ?? count($reviews)) ?> verified reviews</span>
                        </div>

                        <div class="md:col-span-2 space-y-2 justify-center flex flex-col">
                            <?php
                            $totalR = count($reviews);
                            $ratingCounts = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
                            foreach ($reviews as $r) {
                                $rt = (int) $r['rating'];
                                if (isset($ratingCounts[$rt]))
                                    $ratingCounts[$rt]++;
                            }
                            for ($star = 5; $star >= 1; $star--):
                                $cnt = $ratingCounts[$star];
                                $pct = $totalR > 0 ? round(($cnt / $totalR) * 100) : ($star >= 4 ? ($star == 5 ? 82 : 18) : 0);
                                ?>
                                <div class="flex items-center space-x-3 text-xs">
                                    <span class="w-12 font-medium text-gray-600 shrink-0 flex items-center space-x-1">
                                        <span><?= $star ?>★</span>
                                    </span>
                                    <div class="flex-1 bg-gray-200 h-2 rounded-full overflow-hidden">
                                        <div class="bg-amber-400 h-full rounded-full transition-all duration-300"
                                            style="width: <?= $pct ?>%"></div>
                                    </div>
                                    <span class="w-16 text-right font-normal text-gray-500 shrink-0 text-[11px]"><?= $cnt ?>
                                        (<?= $pct ?>%)</span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Review Submission Form -->
                    <div x-show="showReviewForm" x-transition
                        class="bg-red-50/40 p-5 rounded-2xl border border-red-200 space-y-4">
                        <h4 class="font-bold text-gray-900 text-xs flex items-center space-x-2">
                            <i data-lucide="pen-tool" class="w-4 h-4 text-red-600"></i>
                            <span>Write Your Product Review</span>
                        </h4>
                        <form action="<?= url('product/review/add') ?>" method="POST" class="space-y-4">
                            <?= csrf_field() ?>
                            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Your Full Name *</label>
                                    <input type="text" name="customer_name" required placeholder="e.g. Rahul Sharma"
                                        class="w-full h-10 px-3.5 bg-white border border-gray-300 rounded-xl text-xs text-gray-900 focus:outline-none focus:border-red-600">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Star Rating *</label>
                                    <select name="rating" required
                                        class="w-full h-10 px-3.5 bg-white border border-gray-300 rounded-xl text-xs text-gray-900 font-semibold focus:outline-none focus:border-red-600">
                                        <option value="5">★★★★★ (5 Stars - Excellent)</option>
                                        <option value="4">★★★★☆ (4 Stars - Very Good)</option>
                                        <option value="3">★★★☆☆ (3 Stars - Good)</option>
                                        <option value="2">★★☆☆☆ (2 Stars - Fair)</option>
                                        <option value="1">★☆☆☆☆ (1 Star - Poor)</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Review Headline / Title
                                    (Optional)</label>
                                <input type="text" name="title"
                                    placeholder="e.g. Perfect fit for Ather 450X, very sturdy!"
                                    class="w-full h-10 px-3.5 bg-white border border-gray-300 rounded-xl text-xs text-gray-900 focus:outline-none focus:border-red-600">
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Detailed Review / Feedback
                                    *</label>
                                <textarea name="comment" required rows="3" placeholder="Share your experience..."
                                    class="w-full p-3.5 bg-white border border-gray-300 rounded-xl text-xs text-gray-900 focus:outline-none focus:border-red-600"></textarea>
                            </div>

                            <button type="submit"
                                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-xs flex items-center space-x-2 cursor-pointer">
                                <i data-lucide="send" class="w-4 h-4"></i>
                                <span>Submit Verified Review</span>
                            </button>
                        </form>
                    </div>

                    <!-- Approved Reviews Listing -->
                    <?php if (!empty($reviews)): ?>
                        <div class="space-y-4 pt-2">
                            <?php foreach ($reviews as $rev): ?>
                                <div class="bg-gray-50/50 p-4 rounded-xl border border-gray-200 space-y-2">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <div
                                                class="w-8 h-8 rounded-full bg-red-100 text-red-700 font-bold text-xs flex items-center justify-center">
                                                <?= strtoupper(substr($rev['customer_name'] ?? 'U', 0, 1)) ?>
                                            </div>
                                            <div>
                                                <h5 class="text-xs font-bold text-gray-900">
                                                    <?= htmlspecialchars($rev['customer_name']) ?></h5>
                                                <span
                                                    class="text-[10px] text-gray-400"><?= date('d M Y', strtotime($rev['created_at'])) ?></span>
                                            </div>
                                        </div>
                                        <div>
                                            <?= render_star_rating($rev['rating'], 'w-3.5 h-3.5') ?>
                                        </div>
                                    </div>

                                    <?php if (!empty($rev['title'])): ?>
                                        <h6 class="text-xs font-bold text-gray-900"><?= htmlspecialchars($rev['title']) ?></h6>
                                    <?php endif; ?>

                                    <p class="text-xs text-gray-700 leading-relaxed"><?= htmlspecialchars($rev['comment']) ?>
                                    </p>

                                    <?php if (!empty($rev['admin_reply'])): ?>
                                        <div class="mt-2 p-3 bg-red-50/50 border border-red-200/60 rounded-lg text-xs space-y-1">
                                            <p class="font-semibold text-red-700 text-[11px] flex items-center space-x-1">
                                                <i data-lucide="shield-check" class="w-3.5 h-3.5 text-red-600"></i>
                                                <span>Mudsor Official Reply:</span>
                                            </p>
                                            <p class="text-gray-700 italic"><?= htmlspecialchars($rev['admin_reply']) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8 bg-gray-50 rounded-2xl border border-gray-200 space-y-2">
                            <i data-lucide="message-square" class="w-8 h-8 text-gray-300 mx-auto"></i>
                            <p class="text-xs font-semibold text-gray-600">No customer reviews yet for this product.</p>
                            <p class="text-[11px] text-gray-400">Be the first to leave a review!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- RELATED / RECOMMENDED PRODUCTS GRID (PROJECT STANDARD PRODUCT CARD UI) -->
        <?php if (!empty($relatedProducts)): ?>
            <div class="pt-6 sm:pt-10 space-y-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base sm:text-lg lg:text-xl font-medium text-gray-900 tracking-tight">You May Also
                            Like</h3>

                    </div>
                    <a href="<?= url('shop') ?>"
                        class="text-xs font-semibold text-red-600 hover:text-red-700 transition flex items-center space-x-1">
                        <span>Explore Shop</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4">
                    <?php foreach (array_slice($relatedProducts, 0, 4) as $rp): ?>
                        <?php
                        $rpSlug = !empty($rp['slug']) ? trim($rp['slug']) : (int) $rp['id'];
                        $isRpWished = in_array((int) $rp['id'], $wishlistProductIds ?? []);
                        $rpPrice = (float) ($rp['sale_price'] ?: $rp['price']);
                        $rpOrigPrice = (float) $rp['price'];
                        $hasRpDiscount = !empty($rp['sale_price']) && $rpOrigPrice > $rpPrice;
                        $rpSavePct = $hasRpDiscount ? round((($rpOrigPrice - $rpPrice) / $rpOrigPrice) * 100) : 0;
                        ?>
                        <div class="robu-product-card p-3 sm:p-4 flex flex-col justify-between h-full relative group w-full">
                            <!-- Wishlist Button -->
                            <button type="button" onclick="toggleWishlist(<?= (int) $rp['id'] ?>, this)"
                                data-wishlist-id="<?= (int) $rp['id'] ?>"
                                class="robu-wishlist-btn absolute top-2 right-2 sm:top-3 sm:right-3 z-10 p-1.5 rounded-full shadow-2xs cursor-pointer"
                                title="<?= $isRpWished ? 'Remove from Wishlist' : 'Save to Wishlist' ?>">
                                <i data-lucide="heart" class="w-3.5 h-3.5 sm:w-4 sm:h-4" <?= $isRpWished ? 'fill="#A8111C" style="fill:#A8111C; color:#A8111C;"' : '' ?>></i>
                            </button>

                            <!-- Stock Badge / Discount Badge -->
                            <?php if ($rp['stock'] <= 0): ?>
                                <span
                                    class="absolute top-2 left-0 sm:top-3 z-10 text-white text-[9px] sm:text-[10px] font-semibold px-2 py-0.5 rounded-r uppercase shadow-xs bg-gray-600">
                                    Out of Stock
                                </span>
                            <?php elseif ($hasRpDiscount && $rpSavePct > 0): ?>
                                <span
                                    class="absolute top-2 left-2 sm:top-3 sm:left-3 z-10 bg-red-600 text-white text-[9px] sm:text-[10px] font-semibold px-1.5 sm:px-2 py-0.5 rounded-md uppercase shadow-xs">
                                    SAVE <?= $rpSavePct ?>%
                                </span>
                            <?php endif; ?>

                            <!-- Product Image -->
                            <a href="<?= url('product/' . $rpSlug) ?>"
                                class="block relative aspect-square bg-transparent rounded-lg overflow-hidden mb-2.5 sm:mb-3 flex items-center justify-center p-1.5 pt-7 sm:pt-8 transition">
                                <img src="<?= asset($rp['main_image']) ?>" alt="<?= htmlspecialchars($rp['name']) ?>"
                                    class="w-full h-full object-contain max-h-[82%] group-hover:scale-105 transition-transform duration-300"
                                    loading="lazy" onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'">
                            </a>

                            <!-- Details & Add to Cart -->
                            <div class="flex-1 flex flex-col justify-between space-y-2">
                                <div>
                                    <h4
                                        class="text-xs font-semibold text-gray-900 group-hover:text-red-600 transition line-clamp-2 leading-snug">
                                        <a href="<?= url('product/' . $rpSlug) ?>"
                                            class="hover:text-red-600 transition"><?= htmlspecialchars($rp['name']) ?></a>
                                    </h4>
                                </div>

                                <div class="pt-2 border-t border-red-100/70 space-y-2">
                                    <div class="flex items-baseline flex-wrap gap-1.5 min-w-0">
                                        <span
                                            class="text-xs sm:text-sm font-bold text-gray-900 leading-none"><?= format_price($rpPrice) ?></span>
                                        <?php if ($hasRpDiscount): ?>
                                            <span
                                                class="text-[10px] text-gray-400 line-through leading-tight"><?= format_price($rpOrigPrice) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <form action="<?= url('cart/add') ?>" method="POST" class="w-full">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="product_id" value="<?= $rp['id'] ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <button type="submit" <?= $rp['stock'] <= 0 ? 'disabled' : '' ?>
                                            class="robu-cart-btn w-full h-8 sm:h-9 px-2 sm:px-3 text-[11px] sm:text-xs flex items-center justify-center gap-1.5 whitespace-nowrap cursor-pointer <?= $rp['stock'] <= 0 ? 'opacity-50 cursor-not-allowed' : '' ?>">
                                            <span><?= $rp['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?></span>
                                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- BLINKIT-EXACT FLOATING VIEW CART BAR IN MUDSOR RED THEME (Appears only when cartQty > 0, MOBILE/TABLET ONLY) -->
        <div x-show="cartQty > 0" x-cloak x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-y-full opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-full opacity-0"
            class="lg:hidden fixed bottom-[65px] left-0 right-0 z-[55] max-w-7xl mx-auto px-4 sm:px-6">
            <a href="<?= url('cart') ?>"
                class="bg-red-600 hover:bg-red-700 text-white shadow-2xl rounded-xl sm:rounded-2xl p-2.5 sm:p-3 flex items-center justify-between transition cursor-pointer border border-red-500/40">
                <!-- Left: Shopping Cart Icon + Total Cart Items + Total Cart Grand Price -->
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center text-white shrink-0">
                        <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                    </div>
                    <div class="leading-tight">
                        <span class="text-[11px] font-normal text-white/90 block"
                            x-text="globalCartItems + (globalCartItems === 1 ? ' item' : ' items')"></span>
                        <span class="text-sm font-semibold text-white block tracking-tight"
                            x-text="'₹' + parseFloat(globalCartTotal).toFixed(2)"></span>
                    </div>
                </div>

                <!-- Right: View Cart ▸ -->
                <div
                    class="flex items-center space-x-1 font-medium text-xs sm:text-sm text-white tracking-wide uppercase bg-white/20 px-3 py-1.5 rounded-lg hover:bg-white/30 transition">
                    <span>View Cart</span>
                    <i data-lucide="chevron-right" class="w-4 h-4 text-white"></i>
                </div>
            </a>
        </div>

        <!-- BLINKIT-STYLE HIGH-CONVERTING FIXED BOTTOM ACTION BAR WITH TICKET STAMP PRICE BADGE IN RED THEME (MOBILE/TABLET ONLY) -->
        <div
            class="lg:hidden fixed bottom-0 left-0 right-0 z-[60] bg-white border-t border-gray-200 shadow-2xl py-2.5 px-4 sm:px-6">
            <div class="max-w-7xl mx-auto flex items-center justify-between gap-3">
                <!-- Left: Blinkit Ticket Stamp Sale Price Badge in Red, Regular MRP Line-Through, and Inclusive of all taxes -->
                <div class="flex flex-col justify-center min-w-0">
                    <div class="flex items-center space-x-2 flex-wrap">
                        <!-- Ticket Stamp Ticket Badge in MUDSOR Red Theme (Blinkit style) -->
                        <span
                            class="inline-flex items-center bg-red-600 text-white font-semibold text-sm sm:text-base px-2.5 py-0.5 rounded shadow-xs relative border border-red-700 tracking-tight">
                            <?= format_price($effectivePrice) ?>
                        </span>
                        <?php if ($hasDiscount): ?>
                            <span class="text-xs text-gray-500 line-through font-mono">MRP
                                <?= format_price($regularPrice) ?></span>
                        <?php endif; ?>
                    </div>
                    <span class="text-[10px] text-gray-500 font-normal block mt-0.5">Inclusive of all taxes</span>
                </div>

                <!-- Right: Add to Cart Button with Lighter/Clean Font Weight that Transforms into Inline Controller [- 1 +] -->
                <div class="shrink-0">
                    <!-- State 1: Solid BRAND RED Add to Cart Button with Lighter Font Weight (When cartQty <= 0) -->
                    <button type="button" x-show="cartQty <= 0" @click="addToCart()" :disabled="loading || stock <= 0"
                        class="h-10 sm:h-11 px-5 sm:px-8 bg-red-600 hover:bg-red-700 text-white font-medium text-xs sm:text-xs rounded-xl transition shadow-md hover:shadow-lg flex items-center justify-center disabled:bg-gray-400 cursor-pointer tracking-wider uppercase">
                        <span x-text="loading ? 'Adding...' : (stock > 0 ? 'Add to cart' : 'Out of stock')"></span>
                    </button>

                    <!-- State 2: Dynamic Inline Quantity Controller (When cartQty > 0) -->
                    <div x-show="cartQty > 0" x-cloak
                        class="h-11 sm:h-12 px-2 bg-red-600 text-white font-medium text-sm sm:text-base rounded-xl shadow-md flex items-center justify-between space-x-3 w-32 sm:w-40 select-none">
                        <button type="button" @click.stop="decreaseQty()" :disabled="loading"
                            class="w-9 h-9 rounded-lg hover:bg-red-700 text-white font-medium text-lg flex items-center justify-center cursor-pointer transition">
                            -
                        </button>
                        <span class="font-medium text-white text-base sm:text-lg" x-text="cartQty"></span>
                        <button type="button" @click.stop="increaseQty()" :disabled="loading"
                            class="w-9 h-9 rounded-lg hover:bg-red-700 text-white font-medium text-lg flex items-center justify-center cursor-pointer transition">
                            +
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function productGalleryComponent(firstUrl, imagesJson) {
            let images = [];
            if (Array.isArray(imagesJson) && imagesJson.length > 0) {
                images = imagesJson;
            } else if (typeof imagesJson === 'object' && imagesJson !== null) {
                images = Object.values(imagesJson);
            }
            if (images.length === 0 && firstUrl) {
                images = [{ url: firstUrl, is_video: false }];
            }
            return {
                currentIndex: 0,
                allImages: images,
                touchStartX: 0,
                touchEndX: 0,

                nextImage() {
                    if (this.allImages.length <= 1) return;
                    this.currentIndex = (this.currentIndex + 1) % this.allImages.length;
                },
                prevImage() {
                    if (this.allImages.length <= 1) return;
                    this.currentIndex = (this.currentIndex - 1 + this.allImages.length) % this.allImages.length;
                },
                handleTouchStart(e) {
                    if (e.changedTouches && e.changedTouches.length > 0) {
                        this.touchStartX = e.changedTouches[0].screenX;
                    }
                },
                handleTouchEnd(e) {
                    if (e.changedTouches && e.changedTouches.length > 0) {
                        this.touchEndX = e.changedTouches[0].screenX;
                        if (this.touchStartX - this.touchEndX > 30) {
                            this.nextImage();
                        } else if (this.touchEndX - this.touchStartX > 30) {
                            this.prevImage();
                        }
                    }
                }
            };
        }
    </script>

    <?php
    include __DIR__ . '/layouts/footer.php';
    ?>