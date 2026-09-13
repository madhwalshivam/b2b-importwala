<?php
$title = 'My Wishlist | ImportWale Wholesale';
$items = !empty($products) ? $products : (!empty($wishlistItems) ? $wishlistItems : []);
$wishlistCount = count($items);
ob_start();
?>

<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pt-4 sm:pt-8 pb-24 font-sans text-gray-900">

    <!-- Header Section (Responsive for Mobile) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-gray-200 pb-3 sm:pb-4 mb-4 sm:mb-6 gap-2">
        <div class="flex items-center gap-2 flex-wrap">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-red-500 fill-current shrink-0" viewBox="0 0 24 24">
                    <path
                        d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
                <span>My Wishlist</span>
            </h1>
            <span id="wishlistCountText" class="text-xs sm:text-sm font-semibold text-gray-500 bg-gray-100 px-2.5 py-0.5 rounded-full border border-gray-200/80">
                (<?= $wishlistCount ?> saved item<?= $wishlistCount !== 1 ? 's' : '' ?>)
            </span>
        </div>
        <a href="<?= url('catalog') ?>"
            class="text-xs font-bold text-[#f05a29] hover:underline flex items-center gap-1 self-start sm:self-auto">
            <span>&larr; Continue Sourcing</span>
        </a>
    </div>

    <!-- Empty Wishlist State -->
    <div id="emptyWishlistContainer" style="display: <?= empty($items) ? 'block' : 'none' ?>;"
        class="bg-white border border-gray-200 rounded-2xl p-8 sm:p-12 text-center max-w-md mx-auto my-8 shadow-2xs space-y-4">
        <div class="w-16 h-16 bg-rose-50 text-red-500 rounded-full flex items-center justify-center mx-auto shadow-2xs">
            <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24">
                <path
                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
        </div>
        <h2 class="text-lg font-semibold text-gray-900">Your Wishlist is Empty</h2>
        <p class="text-xs text-gray-500">Save products to your wishlist while browsing our catalog to review or buy later.</p>
        <a href="<?= url('catalog') ?>"
            class="inline-block px-6 py-3 bg-[#f05a29] hover:bg-[#d94e20] text-white text-xs font-semibold rounded-xl shadow-xs transition">Browse Wholesale Catalog</a>
    </div>

    <!-- Responsive Product Grid (2-Col Mobile, 5-Col Desktop) -->
    <div id="wishlistGridContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2.5 sm:gap-4" style="display: <?= !empty($items) ? 'grid' : 'none' ?>;">
        <?php foreach ($items as $product): ?>
            <?php 
            $pId = $product['id'] ?? $product['product_id'] ?? 0;
            ?>
            <div id="wishlistCard_<?= $pId ?>" class="wishlist-card-wrapper transition-all duration-300">
                <?php require __DIR__ . '/partials/product_card.php'; ?>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<script>
    window.onWishlistToggled = function(productId, isAdded, count) {
        if (!isAdded) {
            const card = document.getElementById('wishlistCard_' + productId);
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                card.style.transition = 'all 0.25s ease';
                setTimeout(() => {
                    card.remove();
                    const remCards = document.querySelectorAll('.wishlist-card-wrapper');
                    const realCount = (typeof count === 'number') ? count : remCards.length;
                    
                    const countEl = document.getElementById('wishlistCountText');
                    if (countEl) {
                        countEl.textContent = `(${realCount} saved item${realCount !== 1 ? 's' : ''})`;
                    }

                    if (realCount === 0 || remCards.length === 0) {
                        const grid = document.getElementById('wishlistGridContainer');
                        const empty = document.getElementById('emptyWishlistContainer');
                        if (grid) grid.style.display = 'none';
                        if (empty) empty.style.display = 'block';
                    }
                }, 250);
            }
        }
    };
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>