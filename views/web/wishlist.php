<?php
$title = 'My Wishlist | ImportWale Wholesale';
$items = !empty($products) ? $products : (!empty($wishlistItems) ? $wishlistItems : []);
$wishlistCount = count($items);
ob_start();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-20 font-sans text-gray-900">

    <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
        <h1 class="text-2xl font-semibold text-gray-900 tracking-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-red-500 fill-current" viewBox="0 0 24 24">
                <path
                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
            <span>My Wishlist</span>
            <span class="text-sm font-normal text-gray-500">(<?= $wishlistCount ?> saved items)</span>
        </h1>
        <a href="<?= url('catalog') ?>"
            class="text-xs font-semibold text-[#f05a29] hover:underline flex items-center gap-1">
            <span>&larr; Continue Sourcing</span>
        </a>
    </div>

    <?php if (empty($items)): ?>
        <div
            class="bg-white border border-gray-200 rounded-2xl p-12 text-center max-w-md mx-auto my-8 shadow-2xs space-y-4">
            <div class="w-16 h-16 bg-rose-50 text-red-500 rounded-full flex items-center justify-center mx-auto shadow-2xs">
                <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24">
                    <path
                        d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-900">Your Wishlist is Empty</h2>
            <p class="text-xs text-gray-500">Save products to your wishlist while browsing our catalog to review or buy
                later.</p>
            <a href="<?= url('catalog') ?>"
                class="inline-block px-6 py-3 bg-[#f05a29] hover:bg-[#d94e20] text-white text-xs font-semibold rounded-xl shadow-xs transition">Browse
                Wholesale Catalog</a>
        </div>
    <?php else: ?>
        <!-- Unified Product Grid (Exact Site-wide 5-Col Grid Style) -->
        <div class="product-grid" style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 16px 12px; width: 100%;">
            <?php foreach ($items as $product): ?>
                <?php 
                $pId = $product['id'] ?? $product['product_id'] ?? 0;
                ?>
                <div id="wishlistCard_<?= $pId ?>" class="wishlist-card-wrapper transition-all duration-300">
                    <?php require __DIR__ . '/partials/product_card.php'; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
    // Smoothly remove item card from wishlist page when heart is clicked
    const origToggleCardWishlist = window.toggleCardWishlist;
    window.toggleCardWishlist = async function(productId, btn) {
        if (typeof origToggleCardWishlist === 'function') {
            const result = await origToggleCardWishlist(productId, btn);
            const card = document.getElementById('wishlistCard_' + productId);
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                setTimeout(() => card.remove(), 250);
            }
            return result;
        }
    };
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>