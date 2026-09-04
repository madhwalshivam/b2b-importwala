<?php
include __DIR__ . '/layouts/header.php';
$items = is_array($products) && isset($products['items']) ? $products['items'] : (is_array($products) ? $products : []);
$currentPage = $currentPage ?? (is_array($products) ? ($products['current_page'] ?? 1) : 1);
$lastPage = $lastPage ?? (is_array($products) ? ($products['last_page'] ?? 1) : 1);
$total = $total ?? (is_array($products) ? ($products['total'] ?? count($items)) : count($items));
?>

<!-- SECTION DETAIL CONTAINER -->
<main class="py-6 md:py-10 bg-theme-bg font-sans min-h-[60vh]">
    <div class="container mx-auto px-4 space-y-6">

        <!-- Breadcrumbs -->
        <nav class="flex items-center space-x-2 text-xs font-semibold text-gray-500 overflow-x-auto pb-1">
            <a href="<?= url('/') ?>" class="hover:text-red-600 transition">Home</a>
            <i data-lucide="chevron-right" class="w-3.5 h-3.5 shrink-0 text-gray-400"></i>
            <span class="text-gray-900 line-clamp-1"><?= htmlspecialchars($section['title'] ?? 'Section') ?></span>
        </nav>

        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-gray-900 to-slate-800 rounded-2xl p-6 md:p-8 text-white shadow-xs relative overflow-hidden flex flex-col md:flex-row items-start md:items-center justify-between gap-4 border border-gray-800">
            <div class="space-y-1.5 relative z-10">
                <span class="inline-block px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-red-600/90 text-white shadow-xs">
                    Homepage Collection
                </span>
                <h1 class="text-2xl md:text-3xl font-black text-white tracking-tight">
                    <?= htmlspecialchars($section['title'] ?? 'Section Details') ?>
                </h1>
                <?php if (!empty($section['subtitle'])): ?>
                    <p class="text-xs md:text-sm text-gray-300 max-w-2xl font-medium">
                        <?= htmlspecialchars($section['subtitle']) ?>
                    </p>
                <?php endif; ?>
            </div>
            <div class="text-xs font-semibold text-gray-300 bg-white/10 backdrop-blur-md px-4 py-2 rounded-xl border border-white/10 shrink-0">
                Total Products: <span class="text-white font-bold text-sm ml-1"><?= $total ?></span>
            </div>
        </div>

        <?php if (empty($items)): ?>
            <!-- EMPTY STATE -->
            <div class="py-16 text-center space-y-4 max-w-md mx-auto bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 p-8 shadow-xs">
                <div class="w-16 h-16 bg-gray-50 dark:bg-slate-700 text-gray-400 rounded-full flex items-center justify-center mx-auto border border-gray-200 shadow-xs">
                    <i data-lucide="package-search" class="w-8 h-8"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">No Products In This Section</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                    We currently don't have products listed under <?= htmlspecialchars($section['title'] ?? 'this section') ?>. Please explore our full store catalog!
                </p>
                <div class="pt-2">
                    <a href="<?= url('shop') ?>" class="inline-flex items-center space-x-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-xs">
                        <span>Browse Shop</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                </div>
            </div>
        <?php else: ?>

            <!-- PRODUCT GRID -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-5">
                <?php foreach ($items as $prod): ?>
                    <div class="robu-product-card p-3 sm:p-4 flex flex-col justify-between h-full relative group w-full bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-xs hover:shadow-md transition">

                        <?php $isWished = in_array((int)$prod['id'], $wishlistProductIds ?? []); ?>
                        <button type="button" onclick="toggleWishlist(<?= $prod['id'] ?>, this)"
                            data-wishlist-id="<?= $prod['id'] ?>"
                            class="robu-wishlist-btn absolute top-2 right-2 sm:top-3 sm:right-3 z-10 p-1.5 rounded-full shadow-2xs bg-white dark:bg-slate-700"
                            title="<?= $isWished ? 'Remove from Wishlist' : 'Save to Wishlist' ?>">
                            <i data-lucide="heart" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-gray-600 dark:text-gray-300" <?= $isWished ? 'fill="#A8111C" style="fill:#A8111C; color:#A8111C;"' : '' ?>></i>
                        </button>

                        <?php if ($prod['stock'] <= 0): ?>
                            <span class="absolute top-2 left-0 sm:top-3 z-10 text-white text-[9px] sm:text-[10px] font-semibold px-2 py-0.5 rounded-r uppercase shadow-xs bg-gray-800">
                                Out of Stock
                            </span>
                        <?php elseif (!empty($prod['sale_price']) && $prod['price'] > $prod['sale_price']): ?>
                            <span class="absolute top-2 left-2 sm:top-3 sm:left-3 z-10 bg-red-600 text-white text-[9px] sm:text-[10px] font-semibold px-1.5 sm:px-2 py-0.5 rounded-md uppercase shadow-xs">
                                SAVE <?= round((($prod['price'] - $prod['sale_price']) / $prod['price']) * 100) ?>%
                            </span>
                        <?php endif; ?>

                        <?php $pSlug = !empty($prod['slug']) ? trim($prod['slug']) : (int)$prod['id']; ?>
                        <a href="<?= url('product/' . $pSlug) ?>" class="block relative aspect-square rounded-lg overflow-hidden mb-2.5 sm:mb-3 flex items-center justify-center p-1.5 pt-7 sm:pt-8 transition">
                            <img src="<?= asset($prod['main_image']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>"
                                class="w-full h-full object-contain max-h-[82%] group-hover:scale-105 transition-transform duration-300"
                                loading="lazy">
                        </a>

                        <div class="flex-1 flex flex-col justify-between space-y-2">
                            <div>
                                <h4 class="text-xs font-semibold text-gray-900 dark:text-white group-hover:text-red-600 transition line-clamp-2 leading-snug">
                                    <a href="<?= url('product/' . $pSlug) ?>" class="hover:text-red-600 transition">
                                        <?= htmlspecialchars($prod['name']) ?>
                                    </a>
                                </h4>
                            </div>

                            <div class="pt-2 border-t border-gray-100 dark:border-slate-700 space-y-2">
                                <div class="flex items-baseline flex-wrap gap-1.5 min-w-0">
                                    <span class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white leading-none">
                                        <?= format_price($prod['sale_price'] ?: $prod['price']) ?>
                                    </span>
                                    <?php if ($prod['sale_price']): ?>
                                        <span class="text-[10px] text-gray-400 line-through leading-tight">
                                            <?= format_price($prod['price']) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <form action="<?= url('cart/add') ?>" method="POST" class="w-full">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="product_id" value="<?= $prod['id'] ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" <?= $prod['stock'] <= 0 ? 'disabled' : '' ?>
                                        class="robu-cart-btn w-full h-8 sm:h-9 px-2 sm:px-3 text-[11px] sm:text-xs flex items-center justify-center gap-1.5 whitespace-nowrap rounded-xl bg-red-600 hover:bg-red-700 text-white font-semibold transition <?= $prod['stock'] <= 0 ? 'opacity-50 cursor-not-allowed' : '' ?>">
                                        <span><?= $prod['stock'] > 0 ? 'Add to Cart' : 'Out of Stock' ?></span>
                                        <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- PAGINATION -->
            <?php if ($lastPage > 1): ?>
                <div class="flex items-center justify-center pt-8 space-x-2">
                    <?php if ($currentPage > 1): ?>
                        <a href="<?= url('section/' . $section['slug'] . '?page=' . ($currentPage - 1)) ?>"
                            class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-xs font-semibold text-gray-700 dark:text-gray-200 rounded-xl hover:border-red-600 transition flex items-center gap-1">
                            <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            <span>Previous</span>
                        </a>
                    <?php endif; ?>

                    <div class="flex items-center space-x-1.5">
                        <?php for ($p = 1; $p <= $lastPage; $p++): ?>
                            <?php if ($p === $currentPage): ?>
                                <span class="w-9 h-9 flex items-center justify-center bg-red-600 text-white text-xs font-bold rounded-xl shadow-xs">
                                    <?= $p ?>
                                </span>
                            <?php else: ?>
                                <a href="<?= url('section/' . $section['slug'] . '?page=' . $p) ?>"
                                    class="w-9 h-9 flex items-center justify-center bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-xs font-semibold text-gray-700 dark:text-gray-200 rounded-xl hover:border-red-600 transition">
                                    <?= $p ?>
                                </a>
                            <?php endif; ?>
                        <?php endfor; ?>
                    </div>

                    <?php if ($currentPage < $lastPage): ?>
                        <a href="<?= url('section/' . $section['slug'] . '?page=' . ($currentPage + 1)) ?>"
                            class="px-3.5 py-2 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-xs font-semibold text-gray-700 dark:text-gray-200 rounded-xl hover:border-red-600 transition flex items-center gap-1">
                            <span>Next</span>
                            <i data-lucide="chevron-right" class="w-4 h-4"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</main>

<?php include __DIR__ . '/layouts/footer.php'; ?>
