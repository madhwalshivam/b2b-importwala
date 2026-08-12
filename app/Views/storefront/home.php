<?php
include __DIR__ . '/layouts/header.php';
?>

<!-- HERO BANNER SLIDER — Dynamic from Admin Panel -->
<section class="bg-theme-bg py-1 px-2 md:py-3 md:px-4 font-sans w-full overflow-hidden mobile-hero-section"
    style="max-width:100vw; box-sizing:border-box;">
    <div class="w-full md:container md:mx-auto flex flex-col lg:flex-row gap-3 md:gap-4 overflow-hidden p-2 md:p-3 rounded-2xl bg-[#EEF2F6] dark:bg-slate-800 border border-gray-200/70 dark:border-slate-700"
        style="max-width:100%; box-sizing:border-box;">

        <!-- Left Sidebar Category Cards (Robu.in Style: 4 Cards with Images & Colorful Tints) -->
        <div class="hidden lg:flex flex-col w-[250px] xl:w-[270px] shrink-0 justify-between gap-2">
            <a href="<?= url('category/crash-guards') ?>"
                class="robu-hero-card-1 flex-1 px-3.5 py-2.5 rounded-xl transition-all duration-300 flex items-center justify-between group shadow-2xs hover:shadow-md cursor-pointer">
                <span class="robu-hero-card-span font-bold text-xs xl:text-sm leading-tight pr-2">
                    Heavy-Duty<br>Crash Guards
                </span>
                <div class="robu-hero-card-img-box w-11 h-11 xl:w-12 xl:h-12 rounded-lg p-1 flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition-transform duration-300">
                    <img src="<?= asset('assets/images/crash_guard_icon.png') ?>" alt="Crash Guards" class="w-full h-full object-contain rounded">
                </div>
            </a>

            <a href="<?= url('category/body-covers') ?>"
                class="robu-hero-card-2 flex-1 px-3.5 py-2.5 rounded-xl transition-all duration-300 flex items-center justify-between group shadow-2xs hover:shadow-md cursor-pointer">
                <span class="robu-hero-card-span font-bold text-xs xl:text-sm leading-tight pr-2">
                    All-Weather<br>Body Covers
                </span>
                <div class="robu-hero-card-img-box w-11 h-11 xl:w-12 xl:h-12 rounded-lg p-1 flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition-transform duration-300">
                    <img src="<?= asset('assets/images/body_cover_icon.png') ?>" alt="Body Covers" class="w-full h-full object-contain rounded">
                </div>
            </a>

            <a href="<?= url('category/mobile-holders') ?>"
                class="robu-hero-card-3 flex-1 px-3.5 py-2.5 rounded-xl transition-all duration-300 flex items-center justify-between group shadow-2xs hover:shadow-md cursor-pointer">
                <span class="robu-hero-card-span font-bold text-xs xl:text-sm leading-tight pr-2">
                    Waterproof<br>Mobile Holders
                </span>
                <div class="robu-hero-card-img-box w-11 h-11 xl:w-12 xl:h-12 rounded-lg p-1 flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition-transform duration-300">
                    <img src="<?= asset('assets/images/mobile_holder_icon.png') ?>" alt="Mobile Holders" class="w-full h-full object-contain rounded">
                </div>
            </a>

            <a href="<?= url('category/seat-covers') ?>"
                class="robu-hero-card-4 flex-1 px-3.5 py-2.5 rounded-xl transition-all duration-300 flex items-center justify-between group shadow-2xs hover:shadow-md cursor-pointer">
                <span class="robu-hero-card-span font-bold text-xs xl:text-sm leading-tight pr-2">
                    Breathable<br>Seat Covers
                </span>
                <div class="robu-hero-card-img-box w-11 h-11 xl:w-12 xl:h-12 rounded-lg p-1 flex items-center justify-center shrink-0 shadow-2xs group-hover:scale-105 transition-transform duration-300">
                    <img src="<?= asset('assets/images/seat_cover_icon.png') ?>" alt="Seat Covers" class="w-full h-full object-contain rounded">
                </div>
            </a>
        </div>

        <!-- Hero Banner Slider Container -->
        <div class="flex-1 w-full min-w-0 overflow-hidden relative md:rounded-2xl md:border md:border-gray-900 md:shadow-sm bg-white mobile-hero-container"
            style="max-width:100%; display:block;" x-data="heroBannerSlider()" x-init="init()"
            @mouseenter="stopTimer()" @mouseleave="startTimer()"
            @touchstart="handleTouchStart($event)" @touchend="handleTouchEnd($event)">

            <?php if (!empty($heroBanners)): ?>

                <!-- Slides -->
                <div class="relative w-full max-w-full overflow-hidden">
                    <?php foreach ($heroBanners as $i => $banner): ?>
                        <?php
                        $src = \App\Models\Banner::getImageSrc($banner);
                        $mobileSrc = \App\Models\Banner::getMobileImageSrc($banner);
                        $tabletSrc = \App\Models\Banner::getTabletImageSrc($banner);
                        $link = $banner['link_url'] ?? '';
                        $hasLink = !empty($link);
                        ?>
                        <div class="transition-all duration-700 ease-in-out w-full overflow-hidden"
                            :class="current === <?= $i ?> ? 'opacity-100 z-10 relative scale-100' : 'opacity-0 z-0 absolute inset-0 pointer-events-none scale-[0.99]'">
                            <?php if ($hasLink): ?>
                                <a href="<?= url($link) ?>" class="block w-full">
                                    <picture class="block w-full">
                                        <?php if ($mobileSrc): ?>
                                            <source media="(max-width: 767px)" srcset="<?= htmlspecialchars($mobileSrc) ?>">
                                        <?php endif; ?>
                                        <?php if ($tabletSrc): ?>
                                            <source media="(min-width: 768px) and (max-width: 1024px)"
                                                srcset="<?= htmlspecialchars($tabletSrc) ?>">
                                        <?php endif; ?>
                                        <img src="<?= htmlspecialchars($src) ?>"
                                            alt="<?= htmlspecialchars($banner['title'] ?: 'Mudsor Banner') ?>"
                                            class="w-full max-h-[330px] xl:max-h-[360px] block object-cover"
                                            style="display:block; max-width:100%; width:100%; max-height:360px; object-fit:cover;"
                                            loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
                                    </picture>
                                </a>
                            <?php else: ?>
                                <picture class="block w-full">
                                    <?php if ($mobileSrc): ?>
                                        <source media="(max-width: 767px)" srcset="<?= htmlspecialchars($mobileSrc) ?>">
                                    <?php endif; ?>
                                    <?php if ($tabletSrc): ?>
                                        <source media="(min-width: 768px) and (max-width: 1024px)"
                                            srcset="<?= htmlspecialchars($tabletSrc) ?>">
                                    <?php endif; ?>
                                    <img src="<?= htmlspecialchars($src) ?>"
                                        alt="<?= htmlspecialchars($banner['title'] ?: 'Mudsor Banner') ?>"
                                        class="w-full max-h-[330px] xl:max-h-[360px] block object-cover"
                                        style="display:block; max-width:100%; width:100%; max-height:360px; object-fit:cover;"
                                        loading="<?= $i === 0 ? 'eager' : 'lazy' ?>">
                                </picture>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Prev / Next Arrows -->
                <?php if (count($heroBanners) > 1): ?>
                    <button @click="prev()"
                        class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow-md transition mobile-slider-arrow"
                        aria-label="Previous Slide">
                        <i data-lucide="chevron-left" class="w-5 h-5 text-gray-700"></i>
                    </button>
                    <button @click="next()"
                        class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow-md transition mobile-slider-arrow"
                        aria-label="Next Slide">
                        <i data-lucide="chevron-right" class="w-5 h-5 text-gray-700"></i>
                    </button>

                    <!-- Dot Indicators -->
                    <div
                        class="absolute bottom-3 left-1/2 -translate-x-1/2 z-20 flex items-center space-x-1.5 mobile-slider-dots">
                        <?php foreach ($heroBanners as $i => $b): ?>
                            <button @click="goTo(<?= $i ?>)" :class="current === <?= $i ?> ? 'w-5 h-2' : 'w-2 h-2'"
                                class="rounded-full transition-all duration-300"
                                :style="current === <?= $i ?> ? 'background-color: var(--color-primary)' : 'background-color: rgba(255,255,255,0.6)'"
                                aria-label="Go to slide <?= $i + 1 ?>">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- Default Branded Hero -->
                <div class="relative w-full overflow-hidden flex items-center h-full"
                    style="min-height: 360px; max-height: 480px; background: linear-gradient(120deg, #0f0f0f 0%, #1a1a1a 55%, #2a0608 100%);">
                    <div
                        class="relative z-10 px-5 sm:px-10 lg:px-16 py-8 sm:py-12 flex flex-col md:flex-row items-center gap-6 md:gap-10 w-full mobile-hero-content">
                        <div class="flex-1 space-y-3 sm:space-y-5 text-white text-center md:text-left">
                            <div class="inline-flex items-center space-x-2 px-3 py-1 rounded-full text-[10px] font-semibold uppercase tracking-widest"
                                style="background-color: rgba(168,17,28,0.25); color: var(--color-primary); border: 1px solid rgba(168,17,28,0.4);">
                                <span class="w-1.5 h-1.5 rounded-full inline-block"
                                    style="background: var(--color-primary);"></span>
                                <span>New Launch — 2026 Pro Series</span>
                            </div>
                            <h1
                                class="text-2xl sm:text-3xl lg:text-5xl font-black text-white leading-tight tracking-tight mobile-hero-title">
                                Built For<br>
                                <span style="color: var(--color-primary);">Electric India</span>
                            </h1>
                            <p
                                class="text-gray-400 text-xs sm:text-sm leading-relaxed max-w-md mx-auto md:mx-0 mobile-hero-subtitle">
                                Premium crash guards, body covers & mobile holders — precision-fit for Ola, Ather, TVS iQube
                                & Chetak. Zero drilling. 100% fitment guarantee.
                            </p>
                            <div class="flex flex-wrap items-center justify-center md:justify-start gap-3 pt-1">
                                <a href="<?= url('shop') ?>"
                                    class="inline-flex items-center space-x-2 h-10 sm:h-11 px-5 sm:px-7 rounded-xl font-semibold text-xs sm:text-sm text-white shadow-lg transition bg-red-600 hover:bg-red-700 active:bg-red-800"
                                    style="min-height: 44px; min-width: 44px;">
                                    <span>Shop Now</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path d="M5 12h14M12 5l7 7-7 7" />
                                    </svg>
                                </a>
                                <a href="<?= url('compare') ?>"
                                    class="inline-flex items-center space-x-2 h-10 sm:h-11 px-4 sm:px-6 rounded-xl font-semibold text-xs sm:text-sm border border-gray-700 text-gray-300 hover:border-gray-500 hover:text-white transition"
                                    style="background: rgba(255,255,255,0.05); min-height: 44px; min-width: 44px;">
                                    <span>Compare Products</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </div>
</section>

<script>
    function heroBannerSlider() {
        return {
            current: 0,
            total: <?= count($heroBanners ?? []) ?>,
            timer: null,
            touchStartX: 0,
            touchEndX: 0,
            init() {
                if (this.total > 1) {
                    this.startTimer();
                }
            },
            startTimer() {
                if (this.timer) clearInterval(this.timer);
                if (this.total > 1) {
                    this.timer = setInterval(() => this.next(), 4500);
                }
            },
            stopTimer() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            },
            next() { this.current = (this.current + 1) % this.total; this.startTimer(); },
            prev() { this.current = (this.current - 1 + this.total) % this.total; this.startTimer(); },
            goTo(i) { this.current = i; this.startTimer(); },
            handleTouchStart(e) {
                if (!e.changedTouches || !e.changedTouches[0]) return;
                this.touchStartX = e.changedTouches[0].screenX;
            },
            handleTouchEnd(e) {
                if (!e.changedTouches || !e.changedTouches[0]) return;
                this.touchEndX = e.changedTouches[0].screenX;
                this.handleSwipe();
            },
            handleSwipe() {
                if (this.touchEndX < this.touchStartX - 40) {
                    this.next();
                } else if (this.touchEndX > this.touchStartX + 40) {
                    this.prev();
                }
            }
        }
    }
</script>



<!-- CATEGORIES GRID -->
<section class="py-5 md:py-7 bg-theme-bg border-b border-gray-100 font-sans">
    <div class="container mx-auto px-4 space-y-4 md:space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900 tracking-tight">Categories</h2>

            </div>
            <a href="<?= url('categories') ?>"
                class="inline-flex items-center gap-1 whitespace-nowrap text-xs font-semibold text-red-600 hover:underline shrink-0">
                <span>View All</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5 shrink-0"></i>
            </a>
        </div>

        <div class="relative">
            <div class="swiper swiper-categories w-full py-2">
                <div class="swiper-wrapper">
                    <?php foreach ($categories as $cat): ?>
                        <?php
                        $rawImg = $cat['custom_icon'] ?? $cat['image'] ?? $cat['icon'] ?? '';
                        $isRealImage = !empty($rawImg) && (str_contains($rawImg, '/') || str_contains($rawImg, '.') || str_starts_with($rawImg, 'http'));
                        $catImgUrl = $isRealImage ? asset($rawImg) : '';
                        ?>
                        <div class="swiper-slide h-auto">
                            <a href="<?= url('category/' . $cat['slug']) ?>"
                                class="bg-white rounded-xl sm:rounded-2xl border border-gray-100 dark:border-slate-800 shadow-xs hover:shadow-lg hover:border-red-500 transition-all duration-300 group flex flex-col w-full overflow-hidden p-2 sm:p-2.5 text-center">

                                <!-- Square Image Container -->
                                <div
                                    class="w-full aspect-square rounded-lg sm:rounded-xl overflow-hidden bg-gray-50 flex items-center justify-center relative p-2">
                                    <?php if (!empty($catImgUrl)): ?>
                                        <img src="<?= $catImgUrl ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy"
                                            class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300"
                                            onerror="this.onerror=null; this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                                        <div
                                            class="hidden w-full h-full bg-slate-900 flex items-center justify-center text-white/40 font-bold text-xl uppercase">
                                            <?= htmlspecialchars(substr($cat['name'], 0, 2)) ?>
                                        </div>
                                    <?php else: ?>
                                        <div
                                            class="w-full h-full bg-slate-900 flex items-center justify-center text-white/40 font-bold text-xl uppercase">
                                            <?= htmlspecialchars(substr($cat['name'], 0, 2)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Category Title -->
                                <div class="pt-2 sm:pt-2.5 pb-0.5 flex items-center justify-center gap-1">
                                    <h3
                                        class="text-xs sm:text-sm font-semibold text-gray-900 group-hover:text-red-600 transition line-clamp-1">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </h3>
                                    <i data-lucide="chevron-right"
                                        class="w-3.5 h-3.5 text-gray-400 group-hover:text-red-600 transition shrink-0"></i>
                                </div>

                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Overlay Navigation Arrows -->
            <button type="button" id="cat-prev" class="carousel-nav-btn carousel-nav-prev" aria-label="Previous">
                <i data-lucide="chevron-left" class="w-5 h-5"></i>
            </button>
            <button type="button" id="cat-next" class="carousel-nav-btn carousel-nav-next" aria-label="Next">
                <i data-lucide="chevron-right" class="w-5 h-5"></i>
            </button>
        </div>

    </div>
</section>


<!-- SECTION 2: BEST SELLERS (Right Below Hero & Categories Section) -->
<?php if (!empty($bestSellers)): ?>
    <section class="py-5 md:py-7 bg-theme-bg border-b border-gray-100 font-sans reveal-on-scroll">

        <div class="container mx-auto px-4 space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl lg:text-2xl font-semibold text-gray-900 tracking-tight">Best Sellers</h2>

                </div>
                <a href="<?= url('shop') ?>"
                    class="inline-flex items-center space-x-1 whitespace-nowrap text-xs font-semibold text-red-600 hover:underline shrink-0">
                    <span>View All</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 shrink-0"></i>
                </a>
            </div>

            <div class="relative">
                <div class="swiper swiper-bestsellers w-full py-1">
                    <div class="swiper-wrapper">
                        <?php foreach ($bestSellers as $prod): ?>
                            <div class="swiper-slide h-auto">
                                <div class="robu-product-card p-3 sm:p-4 flex flex-col justify-between h-full relative group w-full">

                                    <?php $isWished = in_array((int) $prod['id'], $wishlistProductIds ?? []); ?>
                                    <button type="button" onclick="toggleWishlist(<?= $prod['id'] ?>, this)"
                                        data-wishlist-id="<?= $prod['id'] ?>"
                                        class="robu-wishlist-btn absolute top-2 right-2 sm:top-3 sm:right-3 z-10 p-1.5 rounded-full shadow-2xs"
                                        title="<?= $isWished ? 'Remove from Wishlist' : 'Save to Wishlist' ?>">
                                        <i data-lucide="heart" class="w-3.5 h-3.5 sm:w-4 sm:h-4" <?= $isWished ? 'fill="#A8111C" style="fill:#A8111C; color:#A8111C;"' : '' ?>></i>
                                    </button>

                                    <?php if ($prod['stock'] <= 0): ?>
                                        <span
                                            class="absolute top-2 left-0 sm:top-3 z-10 text-white text-[9px] sm:text-[10px] font-semibold px-2 py-0.5 rounded-r uppercase shadow-xs"
                                            style="background-color: var(--color-secondary);">
                                            Out of Stock
                                        </span>
                                    <?php elseif (!empty($prod['sale_price'])): ?>
                                        <span
                                            class="absolute top-2 left-2 sm:top-3 sm:left-3 z-10 bg-red-600 text-white text-[9px] sm:text-[10px] font-semibold px-1.5 sm:px-2 py-0.5 rounded-md uppercase shadow-xs">
                                            SAVE <?= round((($prod['price'] - $prod['sale_price']) / $prod['price']) * 100) ?>%
                                        </span>
                                    <?php endif; ?>

                                    <?php $pSlug = !empty($prod['slug']) ? trim($prod['slug']) : (int) $prod['id']; ?>
                                    <a href="<?= url('product/' . $pSlug) ?>"
                                        class="block relative aspect-square bg-transparent dark:bg-transparent rounded-lg overflow-hidden mb-2.5 sm:mb-3 flex items-center justify-center p-1.5 pt-7 sm:pt-8 transition">
                                        <img src="<?= asset($prod['main_image']) ?>"
                                            alt="<?= htmlspecialchars($prod['name']) ?>"
                                            class="w-full h-full object-contain max-h-[82%] group-hover:scale-105 transition-transform duration-300"
                                            loading="lazy">
                                    </a>

                                    <div class="flex-1 flex flex-col justify-between space-y-2">
                                        <div>
                                            <h4
                                                class="text-xs font-semibold text-gray-900 group-hover:text-red-600 transition line-clamp-2 leading-snug">
                                                <a href="<?= url('product/' . $pSlug) ?>"
                                                    class="hover:text-red-600 transition"><?= htmlspecialchars($prod['name']) ?></a>
                                            </h4>
                                        </div>

                                        <div class="pt-2 border-t border-red-100/70 space-y-2">
                                            <div class="flex items-baseline flex-wrap gap-1.5 min-w-0">
                                                <span
                                                    class="text-xs sm:text-sm font-bold text-gray-900 leading-none"><?= format_price($prod['sale_price'] ?: $prod['price']) ?></span>
                                                <?php if ($prod['sale_price']): ?>
                                                    <span
                                                        class="text-[10px] text-gray-400 line-through leading-tight"><?= format_price($prod['price']) ?></span>
                                                <?php endif; ?>
                                            </div>

                                            <form action="<?= url('cart/add') ?>" method="POST" class="w-full">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" <?= $prod['stock'] <= 0 ? 'disabled' : '' ?>
                                                    class="robu-cart-btn w-full h-8 sm:h-9 px-2 sm:px-3 text-[11px] sm:text-xs flex items-center justify-center gap-1.5 whitespace-nowrap <?= $prod['stock'] <= 0 ? 'opacity-50 cursor-not-allowed' : '' ?>">
                                                    <span><?= $prod['stock'] > 0 ? 'Add to Cart' : 'Out' ?></span>
                                                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Overlay Navigation Arrows -->
                <button type="button" id="bestsellers-prev" class="carousel-nav-btn carousel-nav-prev"
                    aria-label="Previous">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
                <button type="button" id="bestsellers-next" class="carousel-nav-btn carousel-nav-next" aria-label="Next">
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>
            </div>

        </div>
    </section>
<?php endif; ?>


<!-- SECTION 3: INTERACTIVE SCOOTER COMPARE SECTION -->
<section id="compare-section" class="py-5 md:py-7 bg-theme-bg border-b border-gray-900 font-sans reveal-on-scroll">
    <div class="container mx-auto px-4 space-y-6">

        <div class="text-center max-w-2xl mx-auto space-y-2">
            <div class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                style="background-color: rgba(168,17,28,0.08); color: var(--color-primary);">
                <i data-lucide="git-compare" class="w-4 h-4"></i>
                <span>Interactive Comparison Engine</span>
            </div>
            <h2 class="text-2xl lg:text-3xl font-semibold text-gray-900 tracking-tight">Compare Electric Scooters &
                Accessories</h2>
            <p class="text-xs text-gray-500 font-medium">Select up to 4 models or accessories below to compare
                specifications, OEM prices, quality scores & fitments side-by-side.</p>
        </div>

        <!-- Render Shared BikeDekho 4-Card Widget -->
        <?php include __DIR__ . '/partials/compare_widget.php'; ?>

    </div>
</section>


<!-- SECTION 4: FEATURED PRODUCTS (With Left Promo Card + Right Product Slider UI as originally specced) -->
<?php if (!empty($featuredProducts)): ?>
    <section class="py-5 md:py-7 bg-theme-bg border-b border-gray-100 font-sans reveal-on-scroll">
        <div class="container mx-auto px-4 space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl lg:text-2xl font-semibold text-gray-900 tracking-tight">Featured Products</h2>

                </div>
                <a href="<?= url('shop') ?>"
                    class="text-xs font-semibold flex items-center space-x-1 transition text-red-600 hover:underline shrink-0">
                    <span>View All</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">

                <?php
                $promoBadge = \App\Models\Setting::get('featured_promo_badge', 'SPECIAL OFFER');
                $promoTitle = \App\Models\Setting::get('featured_promo_title', 'Mudsor Heavy-Duty EV Protection');
                $promoDesc = \App\Models\Setting::get('featured_promo_description', 'Heavy gauge stainless steel crash guards and all-weather body covers precision-fit for your electric scooter.');
                $promoBtn = \App\Models\Setting::get('featured_promo_btn_text', 'Shop Now');
                $promoLink = \App\Models\Setting::get('featured_promo_link', 'shop');
                $promoImg = \App\Models\Setting::get('featured_promo_image', '');
                ?>
                <!-- Left Special Offer Banner Card -->
                <div
                    class="lg:col-span-4 featured-promo-card rounded-2xl overflow-hidden shadow-xs relative group w-full bg-slate-950 flex items-center justify-center border border-gray-900">
                    <?php if (!empty($promoImg)): ?>
                        <!-- Full Clickable Image Banner -->
                        <a href="<?= url($promoLink) ?>"
                            class="block w-full h-full rounded-2xl overflow-hidden group-hover:opacity-95 transition flex items-center justify-center bg-slate-950">
                            <img src="<?= asset($promoImg) ?>" alt="<?= htmlspecialchars($promoTitle ?: 'Featured Offer') ?>"
                                class="w-full h-auto lg:h-full max-w-full object-contain lg:object-cover rounded-2xl transition-transform duration-500 group-hover:scale-105">
                        </a>
                    <?php else: ?>
                        <!-- Text-Based Dark Banner Fallback -->
                        <div class="bg-gradient-to-br from-gray-900 via-gray-900 to-black p-6 text-white rounded-2xl"
                            style="display:flex; flex-direction:column; justify-content:space-between; width:100%; height:100%; min-height:320px;">
                            <div class="relative z-10 space-y-3">
                                <?php if (!empty($promoBadge)): ?>
                                    <span
                                        class="text-[10px] font-semibold uppercase px-2.5 py-1 rounded text-white bg-red-600 inline-block shadow-xs">
                                        <?= htmlspecialchars($promoBadge) ?>
                                    </span>
                                <?php endif; ?>
                                <h3 class="text-2xl font-black leading-tight text-white mt-2">
                                    <?= htmlspecialchars($promoTitle) ?>
                                </h3>
                                <?php if (!empty($promoDesc)): ?>
                                    <p class="text-xs text-gray-300 leading-relaxed">
                                        <?= htmlspecialchars($promoDesc) ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <div class="relative z-10 pt-6">
                                <a href="<?= url($promoLink) ?>"
                                    class="inline-flex items-center space-x-2 h-10 px-5 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold text-xs rounded-xl transition shadow-xs">
                                    <span><?= htmlspecialchars($promoBtn) ?></span>
                                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Right Products Slider -->
                <div class="lg:col-span-8 relative flex flex-col justify-between">
                    <div class="relative w-full h-full">
                        <div class="swiper swiper-featured w-full py-1">
                            <div class="swiper-wrapper">
                                <?php foreach ($featuredProducts as $prod): ?>
                                    <div class="swiper-slide h-auto">
                                        <div class="robu-product-card p-3 sm:p-4 flex flex-col justify-between h-full relative group w-full">

                                            <?php $isWished = in_array((int) $prod['id'], $wishlistProductIds ?? []); ?>
                                            <button type="button" onclick="toggleWishlist(<?= $prod['id'] ?>, this)"
                                                data-wishlist-id="<?= $prod['id'] ?>"
                                                class="robu-wishlist-btn absolute top-2 right-2 sm:top-3 sm:right-3 z-10 p-1.5 rounded-full shadow-2xs"
                                                title="<?= $isWished ? 'Remove from Wishlist' : 'Save to Wishlist' ?>">
                                                <i data-lucide="heart" class="w-3.5 h-3.5 sm:w-4 sm:h-4" <?= $isWished ? 'fill="#A8111C" style="fill:#A8111C; color:#A8111C;"' : '' ?>></i>
                                            </button>

                                            <?php if ($prod['stock'] <= 0): ?>
                                                <span
                                                    class="absolute top-2 left-0 sm:top-3 z-10 text-white text-[9px] sm:text-[10px] font-semibold px-2 py-0.5 rounded-r uppercase shadow-xs"
                                                    style="background-color: var(--color-secondary);">
                                                    Out of Stock
                                                </span>
                                            <?php elseif (!empty($prod['sale_price'])): ?>
                                                <span
                                                    class="absolute top-2 left-2 sm:top-3 sm:left-3 z-10 bg-red-600 text-white text-[9px] sm:text-[10px] font-semibold px-1.5 sm:px-2 py-0.5 rounded-md uppercase shadow-xs">
                                                    SAVE
                                                    <?= round((($prod['price'] - $prod['sale_price']) / $prod['price']) * 100) ?>%
                                                </span>
                                            <?php endif; ?>

                                            <?php $fpSlug = !empty($prod['slug']) ? trim($prod['slug']) : (int) $prod['id']; ?>
                                            <a href="<?= url('product/' . $fpSlug) ?>"
                                                class="block relative aspect-square bg-transparent dark:bg-transparent rounded-lg overflow-hidden mb-2.5 sm:mb-3 flex items-center justify-center p-1.5 pt-7 sm:pt-8 transition">
                                                <img src="<?= asset($prod['main_image']) ?>"
                                                    alt="<?= htmlspecialchars($prod['name']) ?>"
                                                    class="w-full h-full object-contain max-h-[82%] group-hover:scale-105 transition-transform duration-300"
                                                    loading="lazy">
                                            </a>

                                            <div class="flex-1 flex flex-col justify-between space-y-2">
                                                <div>
                                                    <h4
                                                        class="text-xs font-semibold text-gray-900 group-hover:text-red-600 transition line-clamp-2 leading-snug">
                                                        <a href="<?= url('product/' . $fpSlug) ?>"
                                                            class="hover:text-red-600 transition"><?= htmlspecialchars($prod['name']) ?></a>
                                                    </h4>
                                                </div>

                                                <div class="pt-2 border-t border-red-100/70 space-y-2">
                                                    <div class="flex items-baseline flex-wrap gap-1.5 min-w-0">
                                                        <span
                                                            class="text-xs sm:text-sm font-bold text-gray-900 leading-none"><?= format_price($prod['sale_price'] ?: $prod['price']) ?></span>
                                                        <?php if ($prod['sale_price']): ?>
                                                            <span
                                                                class="text-[10px] text-gray-400 line-through leading-tight"><?= format_price($prod['price']) ?></span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <form action="<?= url('cart/add') ?>" method="POST" class="w-full">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                                        <input type="hidden" name="quantity" value="1">
                                                        <button type="submit" <?= $prod['stock'] <= 0 ? 'disabled' : '' ?>
                                                            class="robu-cart-btn w-full h-8 sm:h-9 px-2 sm:px-3 text-[11px] sm:text-xs flex items-center justify-center gap-1.5 whitespace-nowrap <?= $prod['stock'] <= 0 ? 'opacity-50 cursor-not-allowed' : '' ?>">
                                                            <span><?= $prod['stock'] > 0 ? 'Add to Cart' : 'Out' ?></span>
                                                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Overlay Navigation Arrows -->
                        <button type="button" id="featured-prev" class="carousel-nav-btn carousel-nav-prev"
                            aria-label="Previous">
                            <i data-lucide="chevron-left" class="w-5 h-5"></i>
                        </button>
                        <button type="button" id="featured-next" class="carousel-nav-btn carousel-nav-next"
                            aria-label="Next">
                            <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </section>
<?php endif; ?>


<!-- SECTION 5: OUR VIDEOS SECTION (Matching Screenshot 2) -->
<?php if (!empty($homepageVideos)): ?>
    <section class="py-12 bg-theme-bg border-b border-gray-100 font-sans reveal-on-scroll">
        <div class="container mx-auto px-4 space-y-6">

            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900 tracking-tight">Our Videos</h2>

                </div>
                <a href="<?= url('shop') ?>"
                    class="inline-flex items-center gap-1 whitespace-nowrap text-xs font-semibold text-red-600 hover:underline shrink-0">
                    <span>View All</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 shrink-0"></i>
                </a>
            </div>

            <div class="relative">
                <div class="swiper swiper-videos w-full">
                    <div class="swiper-wrapper">
                        <?php foreach ($homepageVideos as $v): ?>
                            <?php
                            $embedData = \App\Models\HomepageVideo::getEmbedData($v['video_url'], $v['video_type'] ?? 'link');
                            $embedUrl = $embedData['embed_url'];
                            $platform = $embedData['platform'];

                            $rawProductUrl = trim($v['product_url'] ?? '');
                            if (!empty($rawProductUrl)) {
                                if (str_starts_with($rawProductUrl, 'http://') || str_starts_with($rawProductUrl, 'https://')) {
                                    $productLink = $rawProductUrl;
                                } else {
                                    $cleanPath = ltrim($rawProductUrl, '/');
                                    if (!str_starts_with($cleanPath, 'product/') && !str_contains($cleanPath, '/')) {
                                        $cleanPath = 'product/' . $cleanPath;
                                    }
                                    $productLink = url($cleanPath);
                                }
                            } else {
                                $productLink = '';
                            }
                            ?>
                            <div class="swiper-slide h-auto">
                                <div
                                    class="bg-white rounded-2xl border border-gray-100 dark:border-slate-800 p-3 shadow-xs hover:shadow-md transition flex flex-col justify-between h-full space-y-3">
                                    <div class="space-y-3">
                                        <!-- Video Thumbnail Box -->
                                        <div class="relative aspect-video rounded-xl overflow-hidden bg-black border border-gray-200 dark:border-slate-800 cursor-pointer group/thumb"
                                            onclick="playHomeVideo('<?= htmlspecialchars($embedUrl) ?>', '<?= htmlspecialchars(addslashes($v['title'])) ?>', '<?= $platform ?>')">
                                            
                                            <?php if (!empty($v['thumbnail'])): ?>
                                                <img src="<?= asset($v['thumbnail']) ?>" alt="<?= htmlspecialchars($v['title']) ?>"
                                                    class="w-full h-full object-cover group-hover/thumb:scale-105 transition duration-300"
                                                    loading="lazy">
                                            <?php else: ?>
                                                <div class="w-full h-full bg-slate-900 flex items-center justify-center text-slate-500">
                                                    <i data-lucide="video" class="w-10 h-10"></i>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Platform Tag Badge -->
                                            <span class="absolute top-2 left-2 text-[9px] font-extrabold uppercase px-2 py-0.5 rounded shadow-sm text-white bg-black/75">
                                                <?= htmlspecialchars(strtoupper($platform)) ?>
                                            </span>

                                            <div
                                                class="absolute inset-0 bg-black/35 group-hover/thumb:bg-black/50 transition flex items-center justify-center">
                                                <div
                                                    class="w-12 h-12 rounded-full bg-white/90 group-hover/thumb:bg-red-600 text-gray-900 group-hover/thumb:text-white flex items-center justify-center shadow-lg transition duration-300 transform group-hover/thumb:scale-110">
                                                    <i data-lucide="play" class="w-5 h-5 fill-current ml-0.5"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Video Title -->
                                        <h3 class="text-xs font-semibold text-gray-900 line-clamp-2 leading-snug">
                                            <?= htmlspecialchars($v['title']) ?>
                                        </h3>
                                    </div>

                                    <!-- Dynamic Product Showcase Button -->
                                    <?php if (!empty($productLink)): ?>
                                        <div class="pt-2 border-t border-gray-100">
                                            <a href="<?= htmlspecialchars($productLink) ?>" onclick="event.stopPropagation()"
                                                class="w-full h-9 px-3 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold text-xs rounded-xl transition flex items-center justify-center space-x-1.5 shadow-2xs">
                                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                                <span>View Product</span>
                                                <i data-lucide="arrow-right" class="w-3.5 h-3.5 ml-auto"></i>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Overlay Navigation Arrows -->
                <button type="button" id="videos-prev" class="carousel-nav-btn carousel-nav-prev" aria-label="Previous">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
                <button type="button" id="videos-next" class="carousel-nav-btn carousel-nav-next" aria-label="Next">
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>
            </div>

        </div>
    </section>
<?php endif; ?>


<!-- SECTION 6: NEW ARRIVALS -->
<?php if (!empty($newArrivals)): ?>
    <section class="py-5 md:py-7 bg-theme-bg border-b border-gray-100 font-sans reveal-on-scroll">
        <div class="container mx-auto px-4 space-y-6">

            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl lg:text-2xl font-semibold text-gray-900 tracking-tight">New Arrivals</h2>
                </div>
                <a href="<?= url('shop') ?>"
                    class="inline-flex items-center space-x-1 whitespace-nowrap text-xs font-semibold text-red-600 hover:underline shrink-0">
                    <span>View All</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 shrink-0"></i>
                </a>
            </div>

            <div class="relative">
                <div class="swiper swiper-newarrivals w-full py-1">
                    <div class="swiper-wrapper">
                        <?php foreach ($newArrivals as $prod): ?>
                            <div class="swiper-slide h-auto">
                                <div class="robu-product-card p-3 sm:p-4 flex flex-col justify-between h-full relative group w-full">

                                    <?php $isWished = in_array((int) $prod['id'], $wishlistProductIds ?? []); ?>
                                    <button type="button" onclick="toggleWishlist(<?= $prod['id'] ?>, this)"
                                        data-wishlist-id="<?= $prod['id'] ?>"
                                        class="robu-wishlist-btn absolute top-2 right-2 sm:top-3 sm:right-3 z-10 p-1.5 rounded-full shadow-2xs"
                                        title="<?= $isWished ? 'Remove from Wishlist' : 'Save to Wishlist' ?>">
                                        <i data-lucide="heart" class="w-3.5 h-3.5 sm:w-4 sm:h-4" <?= $isWished ? 'fill="#A8111C" style="fill:#A8111C; color:#A8111C;"' : '' ?>></i>
                                    </button>

                                    <?php if ($prod['stock'] <= 0): ?>
                                        <span
                                            class="absolute top-2 left-0 sm:top-3 z-10 text-white text-[9px] sm:text-[10px] font-semibold px-2 py-0.5 rounded-r uppercase shadow-xs"
                                            style="background-color: var(--color-secondary);">
                                            Out of Stock
                                        </span>
                                    <?php elseif (!empty($prod['sale_price'])): ?>
                                        <span
                                            class="absolute top-2 left-2 sm:top-3 sm:left-3 z-10 bg-red-600 text-white text-[9px] sm:text-[10px] font-semibold px-1.5 sm:px-2 py-0.5 rounded-md uppercase shadow-xs">
                                            SAVE <?= round((($prod['price'] - $prod['sale_price']) / $prod['price']) * 100) ?>%
                                        </span>
                                    <?php endif; ?>

                                    <?php $naSlug = !empty($prod['slug']) ? trim($prod['slug']) : (int) $prod['id']; ?>
                                    <a href="<?= url('product/' . $naSlug) ?>"
                                        class="block relative aspect-square bg-transparent dark:bg-transparent rounded-lg overflow-hidden mb-2.5 sm:mb-3 flex items-center justify-center p-1.5 pt-7 sm:pt-8 transition">
                                        <img src="<?= asset($prod['main_image']) ?>"
                                            alt="<?= htmlspecialchars($prod['name']) ?>"
                                            class="w-full h-full object-contain max-h-[82%] group-hover:scale-105 transition-transform duration-300"
                                            loading="lazy">
                                    </a>

                                    <div class="flex-1 flex flex-col justify-between space-y-2">
                                        <div>
                                            <h4
                                                class="text-xs font-semibold text-gray-900 group-hover:text-red-600 transition line-clamp-2 leading-snug">
                                                <a href="<?= url('product/' . $naSlug) ?>"
                                                    class="hover:text-red-600 transition"><?= htmlspecialchars($prod['name']) ?></a>
                                            </h4>
                                        </div>

                                        <div class="pt-2 border-t border-red-100/70 space-y-2">
                                            <div class="flex items-baseline flex-wrap gap-1.5 min-w-0">
                                                <span
                                                    class="text-xs sm:text-sm font-bold text-gray-900 leading-none"><?= format_price($prod['sale_price'] ?: $prod['price']) ?></span>
                                                <?php if ($prod['sale_price']): ?>
                                                    <span
                                                        class="text-[10px] text-gray-400 line-through leading-tight"><?= format_price($prod['price']) ?></span>
                                                <?php endif; ?>
                                            </div>

                                            <form action="<?= url('cart/add') ?>" method="POST" class="w-full">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" <?= $prod['stock'] <= 0 ? 'disabled' : '' ?>
                                                    class="robu-cart-btn w-full h-8 sm:h-9 px-2 sm:px-3 text-[11px] sm:text-xs flex items-center justify-center gap-1.5 whitespace-nowrap <?= $prod['stock'] <= 0 ? 'opacity-50 cursor-not-allowed' : '' ?>">
                                                    <span><?= $prod['stock'] > 0 ? 'Add to Cart' : 'Out' ?></span>
                                                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Overlay Navigation Arrows -->
                <button type="button" id="newarrivals-prev" class="carousel-nav-btn carousel-nav-prev"
                    aria-label="Previous">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
                <button type="button" id="newarrivals-next" class="carousel-nav-btn carousel-nav-next" aria-label="Next">
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>
            </div>

        </div>
    </section>
<?php endif; ?>


<!-- SECTION 7: GOOGLE REVIEWS SECTION ("Google Backed Trust in Every Order") -->
<?php if (!empty($googleReviews)): ?>
    <section class="py-5 md:py-7 bg-theme-bg border-b border-gray-100 font-sans overflow-hidden reveal-on-scroll">
        <div class="container mx-auto px-4">

            <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-stretch">

                <!-- Left Google Customer Reviews Badge Card -->
                <div
                    class="w-full lg:w-80 bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 p-8 rounded-[16px] flex flex-col justify-center shadow-sm shrink-0 relative overflow-hidden group h-full">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-blue-50/50 via-white to-red-50/50 dark:from-slate-900/0 dark:via-slate-900/0 dark:to-slate-900/0 opacity-50 pointer-events-none">
                    </div>
                    <div class="relative z-10 flex flex-col items-center lg:items-start text-center lg:text-left space-y-4">
                        <!-- Colorful Google G logo SVG with Text -->
                        <div class="flex items-center space-x-2.5">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8" viewBox="0 0 24 24">
                                <path fill="#4285F4"
                                    d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                <path fill="#34A853"
                                    d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                <path fill="#FBBC05"
                                    d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.62z" />
                                <path fill="#EA4335"
                                    d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" />
                            </svg>
                            <span class="text-2xl font-semibold text-gray-900 dark:text-white tracking-tight">Reviews</span>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white leading-snug">Trusted by Riders</h3>
                            <p class="text-[13px] text-gray-500 dark:text-slate-300 mt-2 font-medium leading-relaxed">Discover why riders trust
                                us for premium electric scooter accessories, modifications, and performance upgrades.</p>
                        </div>
                    </div>
                </div>

                <!-- Right Reviews Swiper -->
                <div class="flex-1 relative min-w-0 flex flex-col justify-center">
                    <div class="relative w-full">
                        <div class="swiper swiper-reviews w-full flex-1">
                            <div class="swiper-wrapper items-stretch">
                                <?php foreach ($googleReviews as $r): ?>
                                    <div class="swiper-slide h-auto flex">
                                        <div
                                            class="bg-white dark:bg-slate-900/90 rounded-[16px] border border-gray-100 dark:border-slate-800 p-7 flex flex-col justify-between shadow-xs hover:shadow-md hover:border-gray-900 dark:hover:border-slate-700 transition-all duration-300 w-full relative overflow-hidden group">
                                            <!-- Decorative top edge -->
                                            <div
                                                class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 via-green-500 to-amber-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                            </div>

                                            <div class="space-y-5 flex-1">
                                                <!-- Top row: Avatar, Name, Date, Google Logo -->
                                                <div class="flex items-start justify-between">
                                                    <div class="flex items-center space-x-3.5">
                                                        <?php if ($r['photo_path']): ?>
                                                            <img src="<?= asset($r['photo_path']) ?>"
                                                                class="w-12 h-12 rounded-full object-cover shadow-xs border border-gray-100 dark:border-slate-700"
                                                                loading="lazy" alt="<?= htmlspecialchars($r['customer_name']) ?>">
                                                        <?php else: ?>
                                                            <div
                                                                class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-500 to-blue-600 text-white font-semibold text-lg flex items-center justify-center shadow-xs border border-blue-500">
                                                                <?= htmlspecialchars(substr($r['customer_name'], 0, 1)) ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <div>
                                                            <h4 class="text-[15px] font-semibold text-gray-900 dark:text-white leading-tight">
                                                                <?= htmlspecialchars($r['customer_name']) ?></h4>
                                                            <div class="flex items-center mt-1 space-x-2">
                                                                <p class="text-[11px] text-gray-400 dark:text-slate-400 font-medium">
                                                                    <?= htmlspecialchars($r['review_date'] ?: date('Y-m-d')) ?>
                                                                </p>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Google small logo -->
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 shrink-0 opacity-80"
                                                        viewBox="0 0 24 24">
                                                        <path fill="#4285F4"
                                                            d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                                        <path fill="#34A853"
                                                            d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                                        <path fill="#FBBC05"
                                                            d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.62z" />
                                                        <path fill="#EA4335"
                                                            d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" />
                                                    </svg>
                                                </div>

                                                <!-- Text -->
                                                <p class="text-[14px] text-gray-700 dark:text-slate-100 font-medium leading-relaxed italic">
                                                    "<?= htmlspecialchars($r['review_text']) ?>"
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Overlay Navigation Arrows -->
                        <button type="button" id="reviews-prev" class="carousel-nav-btn carousel-nav-prev"
                            aria-label="Previous">
                            <i data-lucide="chevron-left" class="w-5 h-5"></i>
                        </button>
                        <button type="button" id="reviews-next" class="carousel-nav-btn carousel-nav-next"
                            aria-label="Next">
                            <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>
<?php endif; ?>


<!-- TRUST BADGES STRIP -->
<section class="py-6 md:py-10 bg-theme-bg border-b border-gray-100 font-sans reveal-on-scroll">
    <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-6">
            <div
                class="flex items-center gap-4 px-5 py-4 rounded-2xl border border-gray-100 bg-white hover:border-red-100 hover:shadow-md transition shadow-xs">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                    style="background-color: rgba(168,17,28,0.07); color: var(--color-primary);">
                    <i data-lucide="truck" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 leading-tight">Same Day Shipping</h4>
                    <p class="text-[11px] text-gray-500 mt-0.5">For all orders placed before 3 PM IST</p>
                </div>
            </div>

            <div
                class="flex items-center gap-4 px-5 py-4 rounded-2xl border border-gray-100 bg-white hover:border-red-100 hover:shadow-md transition shadow-xs">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                    style="background-color: rgba(168,17,28,0.07); color: var(--color-primary);">
                    <i data-lucide="headphones" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 leading-tight">Dedicated Customer Support</h4>
                    <p class="text-[11px] text-gray-500 mt-0.5">Mon-Sat 9 AM - 6 PM technical assistance</p>
                </div>
            </div>

            <div
                class="flex items-center gap-4 px-5 py-4 rounded-2xl border border-gray-100 bg-white hover:border-red-100 hover:shadow-md transition shadow-xs">
                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0"
                    style="background-color: rgba(168,17,28,0.07); color: var(--color-primary);">
                    <i data-lucide="award" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-900 leading-tight">100% Fitment Guarantee</h4>
                    <p class="text-[11px] text-gray-500 mt-0.5">Direct bolt-on fit without drilling</p>
                </div>
            </div>

        </div>
    </div>
</section>

<?php if (!empty($brands)): ?>
    <!-- FEATURED BRANDS (Premium Auto-Scrolling) -->
    <section class="py-8 md:py-12 bg-theme-bg border-b border-gray-100 font-sans overflow-hidden reveal-on-scroll">
        <div class="container mx-auto px-4 space-y-5 md:space-y-6">
            <!-- Header Row -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-1 h-7 rounded-full shrink-0" style="background-color: var(--color-primary);"></div>
                    <div>
                        <h2 class="text-lg md:text-xl lg:text-2xl font-semibold text-gray-900 tracking-tight">Our Featured
                            Brands</h2>
                        <p class="text-[11px] sm:text-xs text-gray-500 mt-0.5">Accessories engineered for India's top
                            electric scooter manufacturers</p>
                    </div>
                </div>
                <a href="<?= url('shop') ?>"
                    class="inline-flex items-center gap-1 whitespace-nowrap text-xs font-semibold hover:underline shrink-0"
                    style="color: var(--color-primary);">
                    <span>View All</span>
                    <i data-lucide="arrow-right" class="w-3.5 h-3.5 shrink-0"></i>
                </a>
            </div>

            <!-- Premium Swiper Carousel -->
            <div class="relative w-full">
                <div class="swiper swiper-brands-premium w-full py-2">
                    <div class="swiper-wrapper items-stretch">
                        <?php foreach ($brands as $b): ?>
                            <?php
                            $logoUrl = !empty($b['logo_path']) ? $b['logo_path'] : (!empty($b['logo']) ? $b['logo'] : null);
                            $hasLink = !empty($b['website_link']);
                            $linkUrl = $hasLink ? $b['website_link'] : (!empty($b['slug']) ? url('brand/' . $b['slug']) : '#');
                            $targetAttr = $hasLink ? 'target="_blank" rel="noopener noreferrer"' : '';
                            ?>
                            <div class="swiper-slide h-auto">
                                <a href="<?= htmlspecialchars($linkUrl) ?>" <?= $targetAttr ?>
                                    class="bg-white rounded-[14px] p-4 border border-gray-100 dark:border-slate-800 hover:border-red-600 hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col items-center justify-center h-24 group shadow-sm cursor-pointer w-full">
                                    <?php if ($logoUrl): ?>
                                        <img src="<?= asset(ltrim($logoUrl, '/')) ?>" alt="<?= htmlspecialchars($b['name']) ?>"
                                            loading="lazy"
                                            class="max-h-12 w-auto object-contain transition-transform duration-300 group-hover:scale-105"
                                            onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='block';">
                                        <span
                                            class="text-[10px] sm:text-xs font-semibold text-gray-600 group-hover:text-red-600 transition font-mono hidden mt-2"><?= htmlspecialchars($b['name']) ?></span>
                                    <?php else: ?>
                                        <span
                                            class="text-xs font-semibold text-gray-700 group-hover:text-red-600 transition font-mono tracking-wide"><?= htmlspecialchars($b['name']) ?></span>
                                    <?php endif; ?>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Overlay Navigation Arrows (Hidden on mobile to prevent overlapping brand cards) -->
                <button type="button" class="brands-premium-prev carousel-nav-btn carousel-nav-prev hidden sm:flex"
                    aria-label="Previous">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
                <button type="button" class="brands-premium-next carousel-nav-btn carousel-nav-next hidden sm:flex"
                    aria-label="Next">
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>
            </div>
        </div>
    </section>

    <!-- Swiper Initialization Script for Premium Brands -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swiper !== 'undefined') {
                new Swiper('.swiper-brands-premium', {
                    loop: true,
                    speed: 600,
                    autoplay: {
                        delay: 2500,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: true
                    },
                    slidesPerView: 2,
                    spaceBetween: 12,
                    navigation: {
                        nextEl: '.brands-premium-next',
                        prevEl: '.brands-premium-prev',
                    },
                    breakpoints: {
                        480: { slidesPerView: 3, spaceBetween: 14 },
                        768: { slidesPerView: 4, spaceBetween: 16 },
                        1024: { slidesPerView: 5, spaceBetween: 20 },
                        1280: { slidesPerView: 6, spaceBetween: 24 }
                    }
                });
            }
        });
    </script>
<?php endif; ?>

<!-- LATEST BLOG ARTICLES & GUIDES SECTION -->
<?php if (!empty($latestArticles)): ?>
    <section class="py-12 md:py-16 bg-gradient-to-b from-gray-50/50 to-white dark:from-slate-900 dark:to-slate-900 border-t border-gray-100 dark:border-slate-800 font-sans reveal-on-scroll">
        <div class="container mx-auto px-4 space-y-8">

            <!-- Section Header -->
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4 border-b border-gray-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-1.5 h-8 rounded-full shrink-0" style="background-color: var(--color-primary);"></div>
                    <div>
                        <div class="flex items-center space-x-2">
                            <span
                                class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300 border border-red-100 dark:border-red-900/60">Mudsor
                                Blog</span>
                            <span class="text-xs text-gray-400 font-normal">• EV Maintenance & Guides</span>
                        </div>
                        <h2 class="text-xl md:text-2xl lg:text-3xl font-semibold text-gray-900 dark:text-gray-100 tracking-tight mt-1">Latest
                            EV Articles & Guides</h2>
                    </div>
                </div>
                <a href="<?= url('blog') ?>"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-gray-800 dark:text-gray-200 hover:text-white hover:bg-red-600 hover:border-red-600 shadow-2xs hover:shadow-md transition group shrink-0">
                    <span>View All Articles</span>
                    <i data-lucide="arrow-right"
                        class="w-4 h-4 text-red-600 group-hover:text-white transition-transform group-hover:translate-x-0.5"></i>
                </a>
            </div>

            <!-- Articles Cards Swiper Slider -->
            <div class="relative">
                <div class="swiper swiper-articles w-full py-1">
                    <div class="swiper-wrapper items-stretch">
                        <?php foreach ($latestArticles as $article): ?>
                            <div class="swiper-slide h-auto flex">
                                <article
                                    class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-100 dark:border-slate-700 shadow-xs hover:shadow-xl hover:border-red-500/30 hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col justify-between group h-full w-full">
                                    <div class="space-y-3.5 p-4 pb-0">
                                        <!-- Thumbnail Image Container -->
                                        <a href="<?= url('blog/' . $article['slug']) ?>"
                                            class="block aspect-[16/9] rounded-xl overflow-hidden bg-gray-100 relative group/img">
                                            <?php if (!empty($article['featured_image'])): ?>
                                                <img src="<?= asset($article['featured_image']) ?>"
                                                    alt="<?= htmlspecialchars($article['featured_image_alt'] ?: $article['title']) ?>"
                                                    class="w-full h-full object-cover group-hover/img:scale-108 transition-transform duration-500"
                                                    loading="lazy">
                                            <?php else: ?>
                                                <div
                                                    class="w-full h-full flex items-center justify-center text-gray-400 bg-gray-100">
                                                    <i data-lucide="image" class="w-8 h-8 opacity-40"></i>
                                                </div>
                                            <?php endif; ?>
                                            <span
                                                class="absolute top-2.5 left-2.5 px-2.5 py-0.5 bg-slate-900/80 backdrop-blur-xs text-white text-[10px] font-semibold rounded-full border border-white/20 shadow-xs">
                                                EV Guide
                                            </span>
                                        </a>

                                        <!-- Date & Views Metadata -->
                                        <div class="flex items-center justify-between text-[11px] text-gray-400 font-medium">
                                            <div class="flex items-center space-x-1.5">
                                                <i data-lucide="calendar" class="w-3.5 h-3.5 text-gray-400"></i>
                                                <span><?= date('M d, Y', strtotime($article['published_at'] ?: $article['created_at'])) ?></span>
                                            </div>
                                            <span
                                                class="font-mono text-[10px] text-gray-500 bg-gray-100 px-2 py-0.5 rounded-md">
                                                <?= number_format((int) ($article['views'] ?? 0)) ?> views
                                            </span>
                                        </div>

                                        <!-- Title -->
                                        <h3
                                            class="text-sm font-semibold text-gray-900 group-hover:text-red-600 transition-colors line-clamp-2 leading-snug">
                                            <a href="<?= url('blog/' . $article['slug']) ?>">
                                                <?= htmlspecialchars(htmlspecialchars_decode($article['title'], ENT_QUOTES), ENT_QUOTES, 'UTF-8') ?>
                                            </a>
                                        </h3>

                                        <!-- Excerpt -->
                                        <p class="text-xs text-gray-500 font-normal line-clamp-2 leading-relaxed">
                                            <?= htmlspecialchars($article['excerpt'] ?: mb_strimwidth(strip_tags($article['content']), 0, 90, '...')) ?>
                                        </p>
                                    </div>

                                    <!-- Card Footer -->
                                    <div class="p-4 pt-3 mt-3 border-t border-gray-100 flex items-center justify-between">
                                        <div class="flex items-center space-x-2">
                                            <div
                                                class="w-5 h-5 rounded-full bg-red-600 text-white flex items-center justify-center font-semibold text-[10px]">
                                                <?= strtoupper(substr($article['author_name'] ?: 'M', 0, 1)) ?>
                                            </div>
                                            <span class="text-[11px] font-medium text-gray-600 truncate max-w-[90px]">
                                                <?= htmlspecialchars($article['author_name'] ?: 'Mudsor') ?>
                                            </span>
                                        </div>

                                        <a href="<?= url('blog/' . $article['slug']) ?>"
                                            class="inline-flex items-center space-x-1 text-xs font-semibold text-red-600 group-hover:translate-x-0.5 transition-transform">
                                            <span>Read More</span>
                                            <i data-lucide="chevron-right" class="w-3.5 h-3.5"></i>
                                        </a>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Overlay Navigation Arrows -->
                <button type="button" id="articles-prev" class="carousel-nav-btn carousel-nav-prev" aria-label="Previous">
                    <i data-lucide="chevron-left" class="w-5 h-5"></i>
                </button>
                <button type="button" id="articles-next" class="carousel-nav-btn carousel-nav-next" aria-label="Next">
                    <i data-lucide="chevron-right" class="w-5 h-5"></i>
                </button>
            </div>

        </div>
    </section>
<?php endif; ?>

<!-- Video Player Modal -->
<div id="home-video-modal"
    class="hidden fixed inset-0 z-[99999] bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
    onclick="closeHomeVideoModal(event)">
    <div class="bg-black rounded-2xl max-w-4xl w-full overflow-hidden shadow-2xl relative my-auto z-[100000]"
        onclick="event.stopPropagation()">
        <div class="p-3 bg-gray-900 border-b border-gray-800 flex items-center justify-between text-white">
            <h4 id="video-modal-title" class="text-xs font-semibold truncate pr-4">Video Player</h4>
            <button type="button" onclick="closeHomeVideoModal()"
                class="w-8 h-8 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white transition flex items-center justify-center cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="relative aspect-video bg-black flex items-center justify-center">
            <iframe id="video-modal-iframe" class="w-full h-full" src="" frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen></iframe>
            <video id="video-modal-player" controls class="w-full h-full object-contain hidden" src=""></video>
        </div>
    </div>
</div>

<script>
    function playHomeVideo(url, title, platform) {
        const modal = document.getElementById('home-video-modal');
        const iframe = document.getElementById('video-modal-iframe');
        const player = document.getElementById('video-modal-player');
        const titleElem = document.getElementById('video-modal-title');

        if (!modal) return;

        if (titleElem) titleElem.innerText = title || 'Video Player';

        if (platform === 'upload') {
            if (iframe) { iframe.src = ''; iframe.classList.add('hidden'); }
            if (player) {
                player.src = url;
                player.classList.remove('hidden');
                player.play();
            }
        } else {
            if (player) { player.pause(); player.src = ''; player.classList.add('hidden'); }
            if (iframe) {
                iframe.src = url;
                iframe.classList.remove('hidden');
            }
        }

        document.body.classList.add('modal-open');
        modal.classList.remove('hidden');
    }

    function closeHomeVideoModal(e) {
        const modal = document.getElementById('home-video-modal');
        const iframe = document.getElementById('video-modal-iframe');
        const player = document.getElementById('video-modal-player');

        if (modal) {
            if (iframe) { iframe.src = ''; }
            if (player) { player.pause(); player.src = ''; }
            document.body.classList.remove('modal-open');
            modal.classList.add('hidden');
        }
    }

    // Performance-friendly Scroll Reveal Animation using Intersection Observer
    function initMudsorScrollReveal() {
        const revealElements = document.querySelectorAll('.reveal-on-scroll');
        if (!revealElements.length) return;

        if ('IntersectionObserver' in window) {
            const observerOptions = {
                root: null,
                rootMargin: '0px 0px -40px 0px',
                threshold: 0.02
            };

            const revealObserver = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-revealed');
                        observer.unobserve(entry.target); // Animate once only!
                    }
                });
            }, observerOptions);

            revealElements.forEach(function (el) {
                revealObserver.observe(el);
            });
        } else {
            // Fallback for older browsers
            revealElements.forEach(function (el) {
                el.classList.add('is-revealed');
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMudsorScrollReveal);
    } else {
        initMudsorScrollReveal();
    }
</script>

<?php
include __DIR__ . '/layouts/footer.php';
?>