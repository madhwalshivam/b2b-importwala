<?php
include __DIR__ . '/layouts/header.php';
?>

<div class="bg-theme-bg py-6 sm:py-10 min-h-screen font-sans border-b border-gray-900">
    <div class="container mx-auto px-4 space-y-4 sm:space-y-6">

        <!-- Breadcrumbs -->
        <nav class="text-xs text-gray-500 flex items-center space-x-2">
            <a href="<?= url('/') ?>" class="hover:text-red-600">Home</a>
            <span>/</span>
            <a href="<?= url('account') ?>" class="hover:text-red-600">My Account</a>
            <span>/</span>
            <span class="font-semibold text-gray-900 dark:text-white">Saved Wishlist</span>
        </nav>

        <div class="flex items-center justify-between flex-wrap gap-2">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">My Saved Wishlist</h1>
                <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">Your saved accessories with instant fitment alerts</p>
            </div>
            <span class="text-xs font-semibold bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 px-3 py-1 rounded-full border border-red-100 dark:border-red-900/40">
                <?= count($products) ?> Saved Items
            </span>
        </div>

        <div id="wishlist-empty-container"
            class="bg-white dark:bg-slate-800 p-8 sm:p-16 rounded-2xl border border-gray-200 dark:border-slate-700 text-center space-y-3 <?= !empty($products) ? 'hidden' : '' ?>">
            <i data-lucide="heart" class="w-12 h-12 text-gray-300 dark:text-slate-600 mx-auto"></i>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-slate-200">Your wishlist is currently empty</h3>
            <p class="text-xs text-gray-500 dark:text-slate-400">Click the heart icon on any product card to save items for later.</p>
            <a href="<?= url('shop') ?>"
                class="inline-block px-6 py-2.5 bg-red-600 text-white font-semibold text-xs rounded-xl hover:bg-red-700 transition">Explore
                Accessories</a>
        </div>

        <div id="wishlist-grid-container"
            class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 <?= empty($products) ? 'hidden' : '' ?>">
            <?php foreach ($products as $prod): ?>
                <div data-wishlist-card="<?= $prod['id'] ?>"
                    class="robu-product-card p-3 sm:p-4 flex flex-col justify-between h-full relative group w-full">

                    <!-- Remove Wishlist Button -->
                    <button type="button" onclick="toggleWishlist(<?= $prod['id'] ?>, this)"
                        data-wishlist-id="<?= $prod['id'] ?>"
                        class="robu-wishlist-btn absolute top-2 right-2 sm:top-3 sm:right-3 z-10 p-1.5 rounded-full shadow-2xs"
                        title="Remove from Wishlist">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-red-600"></i>
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

                    <?php $wSlug = !empty($prod['slug']) ? trim($prod['slug']) : (int) $prod['id']; ?>
                    <a href="<?= url('product/' . $wSlug) ?>"
                        class="block relative aspect-square bg-transparent dark:bg-transparent rounded-lg overflow-hidden mb-2.5 sm:mb-3 flex items-center justify-center p-1.5 pt-7 sm:pt-8 transition">
                        <img src="<?= asset($prod['main_image']) ?>" alt="<?= htmlspecialchars($prod['name']) ?>"
                            class="w-full h-full object-contain max-h-[82%] group-hover:scale-105 transition-transform duration-300"
                            onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'">
                    </a>

                    <div class="flex-1 flex flex-col justify-between space-y-2">
                        <div>
                            <h4
                                class="text-xs font-semibold text-gray-900 group-hover:text-red-600 transition line-clamp-2 leading-snug">
                                <a href="<?= url('product/' . $wSlug) ?>"><?= htmlspecialchars($prod['name']) ?></a>
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
            <?php endforeach; ?>
        </div>

    </div>
</div>

<?php
include __DIR__ . '/layouts/footer.php';
?>