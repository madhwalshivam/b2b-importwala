<?php
include __DIR__ . '/layouts/header.php';
$logoUrl = !empty($brand['logo_path']) ? $brand['logo_path'] : (!empty($brand['logo']) ? $brand['logo'] : null);
?>



<!-- PRODUCTS CONTAINER -->
<main class="py-10 bg-theme-bg font-sans min-h-[60vh]">
    <div class="container mx-auto px-4">

        <?php if (empty($products)): ?>
            <!-- EMPTY STATE: 0 Products for this brand -->
            <div
                class="py-16 text-center space-y-4 max-w-md mx-auto bg-white rounded-2xl border border-gray-900 p-8 shadow-xs">
                <div
                    class="w-16 h-16 bg-gray-50 text-gray-400 rounded-full flex items-center justify-center mx-auto border border-gray-900 shadow-xs">
                    <i data-lucide="package-search" class="w-8 h-8"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900">No Products Available
                </h3>
                <p class="text-xs text-gray-500 font-medium">We currently don't have any accessories assigned to this brand.
                    Please check back soon or explore our other EV brands!</p>
                <div class="pt-2 flex justify-center space-x-3">
                    <a href="<?= url('brands') ?>"
                        class="px-5 py-2.5 bg-gray-100 hover:bg-gray-900 text-gray-800 font-semibold text-xs rounded-xl transition">
                        ← Back to All Brands
                    </a>
                    <a href="<?= url('shop') ?>"
                        class="px-5 py-2.5 bg-theme-primary text-white font-semibold text-xs rounded-xl hover:bg-theme-primary-dark transition shadow-xs">
                        Explore Full Store
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div x-data="{ mobileFilterOpen: false }">
                <!-- Mobile Filter Toggle Bar (<1024px) -->
                <?php
                $activeCount = 0;
                if (!empty($filters['category_slug']))
                    $activeCount++;
                if (!empty($filters['model_slug']))
                    $activeCount++;
                if (!empty($filters['sort']) && $filters['sort'] !== 'newest')
                    $activeCount++;
                ?>
                <div
                    class="lg:hidden flex items-center justify-between bg-white p-3.5 rounded-2xl border border-gray-900 shadow-xs mb-4">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4 text-red-600"></i>
                        <span class="text-xs font-semibold text-gray-900">Filter Products</span>
                        <?php if ($activeCount > 0): ?>
                            <span
                                class="bg-red-600 text-white text-[10px] font-semibold px-2 py-0.5 rounded-full"><?= $activeCount ?>
                                Active</span>
                        <?php endif; ?>
                    </div>
                    <button type="button" @click="mobileFilterOpen = !mobileFilterOpen"
                        class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition shadow-xs cursor-pointer">
                        <span x-text="mobileFilterOpen ? 'Hide Filters' : 'Show Filters'">Show Filters</span>
                        <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300"
                            :class="mobileFilterOpen ? 'rotate-180' : ''"></i>
                    </button>
                </div>

                <div class="flex flex-col lg:flex-row gap-8">

                    <!-- Filter Sidebar -->
                    <aside :class="mobileFilterOpen ? 'block' : 'hidden lg:block'"
                        class="w-full lg:w-64 bg-white p-6 rounded-2xl border border-gray-900 space-y-6 h-fit shrink-0 shadow-xs">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <h3 class="font-semibold text-gray-900 text-sm">Filter Products</h3>
                            <a href="<?= url('brand/' . $brand['slug']) ?>"
                                class="text-[11px] font-semibold text-red-600 hover:underline">Reset</a>
                        </div>

                        <form action="<?= url('brand/' . $brand['slug']) ?>" method="GET" class="space-y-5 text-xs">
                            <input type="hidden" name="brand_id" value="<?= $brand['id'] ?>">

                            <!-- Category Filter -->
                            <?php if (!empty($categories)): ?>
                                <div class="space-y-2">
                                    <label class="block text-xs font-semibold text-gray-700 uppercase">Category</label>
                                    <select name="category" @change="$el.form.submit()"
                                        class="w-full h-11 px-3 bg-gray-50 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-gray-900">
                                        <option value="">All Categories</option>
                                        <?php foreach ($categories as $cat): ?>
                                            <option value="<?= $cat['slug'] ?>" <?= ($filters['category_slug'] ?? '') === $cat['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <!-- Scooter Model Compatibility Filter -->
                            <?php if (!empty($allModels)): ?>
                                <div class="space-y-2">
                                    <label class="block text-xs font-semibold text-gray-700 uppercase">Scooter Model</label>
                                    <select name="model" @change="$el.form.submit()"
                                        class="w-full h-11 px-3 bg-gray-50 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-gray-900">
                                        <option value="">All Scooter Models</option>
                                        <?php foreach ($allModels as $m): ?>
                                            <?php if ($m['brand_id'] == $brand['id']): ?>
                                                <option value="<?= $m['slug'] ?>" <?= ($filters['model_slug'] ?? '') === $m['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($m['name']) ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            <?php endif; ?>

                            <!-- Sort By -->
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-gray-700 uppercase">Sort By</label>
                                <select name="sort" @change="$el.form.submit()"
                                    class="w-full h-11 px-3 bg-gray-50 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-gray-900">
                                    <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>
                                        Newest
                                        Arrivals</option>
                                    <option value="price_low" <?= ($filters['sort'] ?? '') === 'price_low' ? 'selected' : '' ?>>
                                        Price: Low to High</option>
                                    <option value="price_high" <?= ($filters['sort'] ?? '') === 'price_high' ? 'selected' : '' ?>>
                                        Price: High to Low</option>
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full h-11 bg-theme-primary hover:bg-theme-primary-dark text-white font-semibold text-xs rounded-xl transition shadow-xs">Apply
                                Filters</button>
                        </form>
                    </aside>

                    <!-- Products Grid Area -->
                    <div class="flex-1 space-y-6">
                        <div
                            class="bg-white p-4 rounded-2xl border border-gray-900 flex items-center justify-between shadow-xs">
                            <span class="text-xs text-gray-500 font-medium">Showing <strong
                                    class="text-gray-900"><?= count($products) ?></strong> of <strong
                                    class="text-gray-900"><?= $paginator->getTotal() ?></strong> products</span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6">
                            <?php foreach ($products as $prod): ?>
                                <?php $pSlug = !empty($prod['slug']) ? trim($prod['slug']) : (int) $prod['id']; ?>
                                <div
                                    class="robu-product-card p-3 sm:p-4 flex flex-col justify-between h-full relative group w-full">
                                    <div
                                        class="relative aspect-square bg-transparent dark:bg-transparent overflow-hidden border-b border-red-100 p-2 pt-7 sm:pt-8 flex items-center justify-center">
                                        <a href="<?= url('product/' . $pSlug) ?>"
                                            class="w-full h-full flex items-center justify-center">
                                            <img src="<?= asset($prod['main_image']) ?>"
                                                alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy"
                                                class="w-full h-full object-contain max-h-[82%] group-hover:scale-105 transition-transform duration-300"
                                                onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'">
                                        </a>
                                        <?php if (!empty($prod['sale_price'])): ?>
                                            <span
                                                class="absolute top-2 left-2 sm:top-3 sm:left-3 z-10 bg-red-600 text-white text-[9px] sm:text-[10px] font-semibold px-1.5 sm:px-2 py-0.5 rounded uppercase shadow-xs">
                                                SAVE <?= round((($prod['price'] - $prod['sale_price']) / $prod['price']) * 100) ?>%
                                            </span>
                                        <?php endif; ?>

                                        <!-- Wishlist Button -->
                                        <?php $isWished = in_array((int) $prod['id'], $wishlistProductIds ?? []); ?>
                                        <button type="button" onclick="toggleWishlist(<?= $prod['id'] ?>, this)"
                                            data-wishlist-id="<?= $prod['id'] ?>"
                                            class="robu-wishlist-btn absolute top-2 right-2 sm:top-3 sm:right-3 z-10 p-1.5 rounded-full shadow-2xs"
                                            title="<?= $isWished ? 'Remove from Wishlist' : 'Save to Wishlist' ?>">
                                            <i data-lucide="heart" class="w-3.5 h-3.5 sm:w-4 sm:h-4" <?= $isWished ? 'fill="#A8111C" style="fill:#A8111C; color:#A8111C;"' : '' ?>></i>
                                        </button>
                                    </div>

                                    <div class="pt-2 sm:pt-3 flex-1 flex flex-col justify-between space-y-2">
                                        <div>
                                            <h3 class="text-xs font-semibold text-gray-900 group-hover:text-red-600 transition line-clamp-2 leading-snug">
                                                <a href="<?= url('product/' . $pSlug) ?>"
                                                    class="hover:text-red-600 transition"><?= htmlspecialchars($prod['name']) ?></a>
                                            </h3>
                                        </div>

                                        <div class="pt-2 border-t border-red-100/70 space-y-2">
                                            <div class="flex items-baseline flex-wrap gap-1.5 min-w-0">
                                                <span
                                                    class="text-xs sm:text-sm font-bold text-gray-900 leading-none"><?= format_price($prod['sale_price'] ?: $prod['price']) ?></span>
                                                <?php if (!empty($prod['sale_price'])): ?>
                                                    <span
                                                        class="text-[10px] text-gray-400 line-through leading-tight"><?= format_price($prod['price']) ?></span>
                                                <?php endif; ?>
                                            </div>

                                            <form action="<?= url('cart/add') ?>" method="POST" class="w-full">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit"
                                                    class="robu-cart-btn w-full h-8 sm:h-9 px-2 sm:px-3 text-[11px] sm:text-xs flex items-center justify-center gap-1.5 whitespace-nowrap">
                                                    <span>Add to Cart</span>
                                                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Server-Side Pagination Links -->
                        <?php if ($paginator->hasPages()): ?>
                            <div class="pt-6 border-t border-gray-900">
                                <?= $paginator->render() ?>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php
include __DIR__ . '/layouts/footer.php';
?>