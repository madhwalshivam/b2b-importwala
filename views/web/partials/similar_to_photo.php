<?php
/**
 * "Similar to your photo" / Related products rail for the product detail page.
 *
 * Expects (from ProductDetailController):
 *   $imageSearchSimilar  — array of products from image-search session (may be empty/null)
 *   $fromImageSearch     — bool
 *   $relatedProducts     — category/subcategory related products
 *   $visuallySimilar     — existing visual-similar items (optional fallback)
 *   $__mainProduct       — main product (restored after card loop)
 */

$__mainProduct = $__mainProduct ?? ($product ?? null);
$fromImageSearch = !empty($fromImageSearch);
$sessionItems = is_array($imageSearchSimilar ?? null) ? $imageSearchSimilar : null;

// Only render this rail when the visitor arrived from image search.
// Normal PDP visits keep the existing "Similar Products" block below.
if (!$fromImageSearch) {
    return;
}

$railItems = [];
$railTitle = 'Related products';

if (is_array($sessionItems) && count($sessionItems) > 0) {
    $railItems = array_slice($sessionItems, 0, 12);
    $railTitle = 'Similar to your photo';
} else {
    // Session missing/expired — category/subcategory related (never leave empty if possible)
    $related = $relatedProducts ?? ($related ?? []);
    if (!empty($related) && is_array($related)) {
        $railItems = array_slice($related, 0, 12);
        $railTitle = 'Related products';
    } elseif (!empty($visuallySimilar) && is_array($visuallySimilar)) {
        $railItems = array_slice($visuallySimilar, 0, 12);
        $railTitle = 'Related products';
    }
}

// Exclude current product if present
$currentId = (int) (($__mainProduct['id'] ?? $product['id'] ?? 0));
if ($currentId > 0 && !empty($railItems)) {
    $railItems = array_values(array_filter($railItems, static function ($p) use ($currentId) {
        return (int) ($p['id'] ?? 0) !== $currentId;
    }));
    $railItems = array_slice($railItems, 0, 12);
}

if (empty($railItems)) {
    return;
}
?>
<div class="mt-2 sm:mt-4 bg-white p-3 sm:p-4 md:p-6 md:rounded-2xl" id="similarToPhotoSection">
    <h2 class="text-xs sm:text-sm md:text-base font-bold text-gray-900 tracking-tight mb-3">
        <?= htmlspecialchars($railTitle) ?>
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 sm:gap-3">
        <?php foreach ($railItems as $simItem):
            $product = $simItem;
            require __DIR__ . '/product_card.php';
        endforeach; ?>
    </div>
</div>
<?php
// Restore main product after card loop overwrote $product
if (!empty($__mainProduct)) {
    $product = $__mainProduct;
}
?>
