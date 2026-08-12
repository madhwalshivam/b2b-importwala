<?php
include __DIR__ . '/layouts/header.php';
?>

<div class="bg-theme-bg py-8 min-h-screen border-b border-gray-900 font-sans" x-data="{ mobileFilterOpen: false }">
    <div class="container mx-auto px-4">

        <!-- Breadcrumbs -->
        <nav class="text-xs text-gray-500 mb-6 flex items-center space-x-2">
            <a href="<?= url('/') ?>" class="hover:text-red-600">Home</a>
            <span>/</span>
            <span class="font-semibold text-gray-900">Shop Accessories</span>
        </nav>

        <!-- Mobile Filter Toggle Bar (<1024px) -->
        <?php
        $activeCount = 0;
        if (!empty($filters['min_price']) || !empty($filters['max_price']))
            $activeCount++;
        if (!empty($filters['category_slug']))
            $activeCount++;
        if (!empty($filters['brand_slug']))
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
                <span class="text-xs font-semibold text-gray-900">Filter Accessories</span>
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

            <!-- Sidebar Filters -->
            <aside :class="mobileFilterOpen ? 'block' : 'hidden lg:block'"
                class="w-full lg:w-72 bg-white p-6 rounded-2xl border border-gray-900 shrink-0 h-fit space-y-6 shadow-xs">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="font-semibold text-gray-900 text-sm flex items-center space-x-2">
                        <i data-lucide="sliders-horizontal" class="w-4 h-4 text-red-600"></i>
                        <span>Filter Accessories</span>
                    </h3>
                    <a href="<?= url('shop') ?>" class="text-xs text-red-600 font-semibold hover:underline">Clear
                        All</a>
                </div>

                <form action="<?= url('shop') ?>" method="GET" class="space-y-6">

                    <!-- Search Input -->
                    <?php if (!empty($filters['search'])): ?>
                        <input type="hidden" name="search" value="<?= htmlspecialchars($filters['search']) ?>">
                    <?php endif; ?>

                    <!-- Price Range Filter (Dynamic Min & Max Inputs + Quick Presets) -->
                    <div class="space-y-3 bg-gray-50 p-4 rounded-xl border border-gray-900">
                        <label class="block text-xs font-semibold text-gray-900 uppercase tracking-wider">Price Range
                            (₹)</label>

                        <div class="flex items-center space-x-2">
                            <input type="number" name="min_price" placeholder="Min ₹"
                                value="<?= htmlspecialchars($filters['min_price'] ?? '') ?>"
                                class="w-1/2 h-10 px-3 bg-white border border-gray-900 rounded-lg text-xs font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                            <span class="text-gray-400 font-semibold">-</span>
                            <input type="number" name="max_price" placeholder="Max ₹"
                                value="<?= htmlspecialchars($filters['max_price'] ?? '') ?>"
                                class="w-1/2 h-10 px-3 bg-white border border-gray-900 rounded-lg text-xs font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                        </div>

                        <!-- Price Quick Presets -->
                        <div class="flex flex-wrap gap-1.5 pt-1">
                            <a href="<?= url('shop?min_price=0&max_price=499') ?>"
                                class="text-[10px] font-semibold bg-white hover:bg-red-600 hover:text-white px-2.5 py-1 rounded-md border border-gray-900 text-gray-700 transition">Under
                                ₹500</a>
                            <a href="<?= url('shop?min_price=500&max_price=1499') ?>"
                                class="text-[10px] font-semibold bg-white hover:bg-red-600 hover:text-white px-2.5 py-1 rounded-md border border-gray-900 text-gray-700 transition">₹500
                                - ₹1,500</a>
                            <a href="<?= url('shop?min_price=1500&max_price=2999') ?>"
                                class="text-[10px] font-semibold bg-white hover:bg-red-600 hover:text-white px-2.5 py-1 rounded-md border border-gray-900 text-gray-700 transition">₹1,500
                                - ₹3,000</a>
                            <a href="<?= url('shop?min_price=3000') ?>"
                                class="text-[10px] font-semibold bg-white hover:bg-red-600 hover:text-white px-2.5 py-1 rounded-md border border-gray-900 text-gray-700 transition">Above
                                ₹3,000</a>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Category</label>
                        <select name="category"
                            @change="if ($el.value) { window.location.href = '<?= url('category/') ?>' + $el.value; } else { window.location.href = '<?= url('shop') ?>'; }"
                            class="w-full h-11 px-3 bg-gray-50 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-gray-900">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['slug'] ?>" <?= ($filters['category_slug'] ?? '') === $cat['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Scooter Brand Filter -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Scooter Brand</label>
                        <select name="brand"
                            @change="if ($el.value) { window.location.href = '<?= url('brand/') ?>' + $el.value; } else { window.location.href = '<?= url('shop') ?>'; }"
                            class="w-full h-11 px-3 bg-gray-50 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-gray-900">
                            <option value="">All Scooter Brands</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= $b['slug'] ?>" <?= ($filters['brand_slug'] ?? '') === $b['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Scooter Model Filter -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Scooter Model</label>
                        <select name="model" @change="$el.form.submit()"
                            class="w-full h-11 px-3 bg-gray-50 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-gray-900">
                            <option value="">All Scooter Models</option>
                            <?php foreach ($allModels as $m): ?>
                                <option value="<?= $m['slug'] ?>" <?= ($filters['model_slug'] ?? '') === $m['slug'] ? 'selected' : '' ?>><?= htmlspecialchars($m['brand_name'] . ' - ' . $m['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Sorting Filter -->
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Sort By</label>
                        <select name="sort" @change="$el.form.submit()"
                            class="w-full h-11 px-3 bg-gray-50 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-gray-900">
                            <option value="newest" <?= ($filters['sort'] ?? '') === 'newest' ? 'selected' : '' ?>>Newest
                                Arrivals</option>
                            <option value="price_low" <?= ($filters['sort'] ?? '') === 'price_low' ? 'selected' : '' ?>>
                                Price: Low to High</option>
                            <option value="price_high" <?= ($filters['sort'] ?? '') === 'price_high' ? 'selected' : '' ?>>
                                Price: High to Low</option>
                            <option value="popular" <?= ($filters['sort'] ?? '') === 'popular' ? 'selected' : '' ?>>Most
                                Popular</option>
                        </select>
                    </div>

                    <button type="submit"
                        class="w-full h-11 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-xs">Apply
                        Filters</button>

                </form>
            </aside>

            <!-- Product Grid Area -->
            <main class="flex-1">

                <!-- Filter Title Banner -->
                <div
                    class="bg-white p-6 rounded-2xl border border-gray-900 mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 shadow-xs">
                    <div>
                        <h1 class="text-xl font-semibold text-gray-900">
                            <?php
                            if ($selectedModel)
                                echo "Accessories for " . htmlspecialchars($selectedModel['name']);
                            elseif ($selectedBrand)
                                echo htmlspecialchars($selectedBrand['name']) . " Accessories";
                            elseif ($selectedCategory)
                                echo htmlspecialchars($selectedCategory['name']);
                            else
                                echo "All Electric Scooter Accessories";
                            ?>
                        </h1>
                        <p class="text-xs text-gray-500 mt-1 font-medium">Showing <?= count($products) ?> of
                            <?= $paginator->getTotal() ?> accessories
                        </p>
                    </div>
                </div>

                <!-- Products Grid -->
                <?php if (empty($products)): ?>
                    <div class="bg-white p-12 rounded-2xl border border-gray-900 text-center space-y-3 shadow-xs">
                        <i data-lucide="package-search" class="w-12 h-12 text-gray-300 mx-auto"></i>
                        <h3 class="text-base font-semibold text-gray-800">No matching accessories found</h3>
                        <p class="text-xs text-gray-500">Try clearing your price or category filters to view available
                            products.</p>
                        <a href="<?= url('shop') ?>"
                            class="inline-block h-11 leading-[44px] px-6 bg-red-600 text-white font-semibold text-xs rounded-xl hover:bg-red-700 transition">View
                            All Products</a>
                    </div>
                <?php else: ?>
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
                                            alt="<?= htmlspecialchars($prod['name']) ?>"
                                            class="w-full h-full object-contain max-h-[82%] group-hover:scale-105 transition-transform duration-300"
                                            onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'">
                                    </a>

                                    <?php if (!empty($prod['sale_price'])): ?>
                                        <span
                                            class="absolute top-2 left-2 sm:top-3 sm:left-3 z-10 bg-red-600 text-white text-[9px] sm:text-[10px] font-semibold px-1.5 sm:px-2 py-0.5 rounded-md uppercase shadow-xs">SAVE
                                            <?= round((($prod['price'] - $prod['sale_price']) / $prod['price']) * 100) ?>%</span>
                                    <?php endif; ?>

                                    <!-- Wishlist Heart Button -->
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
                                            <?php if ($prod['sale_price']): ?>
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
                    <div class="mt-8">
                        <?= $paginator->render() ?>
                    </div>

                <?php endif; ?>

            </main>

        </div>
    </div>
</div>

<?php
include __DIR__ . '/layouts/footer.php';
?>