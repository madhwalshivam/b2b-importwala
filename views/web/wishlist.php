<?php
$title = 'My Wishlist | ImportWale Wholesale';
ob_start();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-8 pb-20 font-sans text-gray-900">

    <div class="flex items-center justify-between border-b border-gray-200 pb-4 mb-6">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-red-500 fill-current" viewBox="0 0 24 24"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
            <span>My Wishlist</span>
            <span class="text-sm font-normal text-gray-500">(<?= $wishlistCount ?> saved items)</span>
        </h1>
        <a href="<?= url('catalog') ?>" class="text-xs font-semibold text-[#f05a29] hover:underline flex items-center gap-1">
            <span>&larr; Continue Sourcing</span>
        </a>
    </div>

    <?php if (empty($wishlistItems)): ?>
        <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center max-w-md mx-auto my-8 shadow-2xs space-y-4">
            <div class="w-16 h-16 bg-rose-50 text-red-500 rounded-full flex items-center justify-center mx-auto shadow-2xs">
                <svg class="w-8 h-8 fill-current" viewBox="0 0 24 24"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z"/></svg>
            </div>
            <h2 class="text-lg font-bold text-gray-900">Your Wishlist is Empty</h2>
            <p class="text-xs text-gray-500">Save products to your wishlist while browsing our catalog to review or buy later.</p>
            <a href="<?= url('catalog') ?>" class="inline-block px-6 py-3 bg-[#f05a29] hover:bg-[#d94e20] text-white text-xs font-bold rounded-xl shadow-xs transition">Browse Wholesale Catalog</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach ($wishlistItems as $item): ?>
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-2xs hover:shadow-md transition flex flex-col group" id="wishlistCard_<?= $item['product_id'] ?>">
                    <div class="relative bg-gray-50 aspect-square overflow-hidden">
                        <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        <button type="button" onclick="removeFromWishlistPage(<?= $item['product_id'] ?>)" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 shadow-md flex items-center justify-center text-red-500 hover:bg-white hover:scale-110 transition cursor-pointer border-0" title="Remove from wishlist">
                            ✕
                        </button>
                    </div>

                    <div class="p-4 flex-1 flex flex-col justify-between space-y-3">
                        <div>
                            <a href="<?= url('product/' . $item['slug']) ?>" class="text-xs font-bold text-gray-900 hover:text-[#f05a29] transition line-clamp-2">
                                <?= htmlspecialchars($item['name']) ?>
                            </a>
                            <div class="text-[11px] text-gray-400 font-mono mt-1">SKU: <?= htmlspecialchars($item['sku']) ?></div>
                        </div>

                        <div class="flex items-center gap-1.5 pt-2 border-t border-gray-100">
                            <a href="<?= url('product/' . $item['slug']) ?>" class="flex-1 py-2 bg-[#f05a29] hover:bg-[#d94e20] text-white text-xs font-bold rounded-xl text-center transition shadow-2xs">
                                View Details
                            </a>
                            <button type="button" onclick="openRfqModal()" class="py-2 px-2.5 bg-gray-900 hover:bg-black text-white text-xs font-bold rounded-xl transition cursor-pointer border-0" title="Request Bulk Quote / Send Inquiry">
                                Send RFQ
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<script>
    async function removeFromWishlistPage(productId) {
        const payload = new URLSearchParams();
        payload.append('product_id', productId);

        const res = await fetch('<?= url('wishlist/toggle') ?>', { method: 'POST', body: payload });
        const data = await res.json();
        if (data.success) {
            const card = document.getElementById('wishlistCard_' + productId);
            if (card) card.remove();
            if (typeof updateHeaderWishlistBadge === 'function') {
                updateHeaderWishlistBadge(data.count);
            } else {
                location.reload();
            }
        }
    }
</script>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>
