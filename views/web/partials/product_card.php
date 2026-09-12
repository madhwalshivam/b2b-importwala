<?php
/**
 * Everful Wholesale Exact Product Card Component (Jumia-style Redesign)
 * Reusable partial across Homepage, Catalog, Search, Best Sellers, Category, Related Products.
 */

if (empty($product) || !is_array($product)) return;

$slug           = htmlspecialchars($product['slug'] ?? $product['id'] ?? '');
$productUrl     = url('product/' . $slug);
$name           = htmlspecialchars($product['name'] ?? $product['title'] ?? 'Wholesale Product');
$basePrice      = (float)($product['price'] ?? $product['base_price'] ?? 0);
$salePrice      = !empty($product['sale_price']) ? (float)$product['sale_price'] : null;
$effectivePrice = $salePrice ?: $basePrice;

$discountPct    = ($salePrice && $basePrice > $salePrice) 
    ? round((($basePrice - $salePrice) / $basePrice) * 100) 
    : 0;

$images         = get_product_images($product);
$sliderImages   = $images;
if (empty($sliderImages)) {
    $sliderImages = [asset('assets/images/placeholder.jpg')];
}
$cardId         = 'pcard_' . ($product['id'] ?? rand(1000, 9999)) . '_' . rand(100, 999);

// Admin-Controlled Flags & Dynamic Distinct Rating
$isNew          = !empty($product['is_new']) || !empty($product['is_new_arrival']);
$prodId         = (int)($product['id'] ?? 0);

if (!empty($product['rating']) && (float)$product['rating'] > 0) {
    $rating = (float)$product['rating'];
} elseif (!empty($product['avg_rating']) && (float)$product['avg_rating'] > 0) {
    $rating = (float)$product['avg_rating'];
} else {
    // Unique deterministic rating per product (e.g. 4.9, 4.7, 4.8, 4.6, 5.0, 4.5)
    $ratingsPool = [4.9, 4.7, 4.8, 4.6, 5.0, 4.5, 4.9, 4.8, 4.7, 4.9, 4.6, 4.8, 5.0, 4.7];
    $rating = $ratingsPool[$prodId % count($ratingsPool)];
}

$reviewCount    = (int)($product['reviews_count'] ?? $product['rating_count'] ?? $product['total_reviews'] ?? (12 + (($prodId ?: 1) * 37) % 350));

// Check if product is currently in wishlist & cart
static $userWishlistProductIds = null;
static $userCartProductIds = null;

if ($userWishlistProductIds === null) {
    if (isset($GLOBALS['initialWishlistProductIds'])) {
        $userWishlistProductIds = $GLOBALS['initialWishlistProductIds'];
    } else {
        $db = \App\Core\Database::getInstance();
        $uId = get_current_user_id();
        $sId = get_current_session_id();
        if ($uId) {
            $st = $db->prepare("SELECT DISTINCT product_id FROM wishlist WHERE user_id = ?");
            $st->execute([$uId]);
        } else {
            $st = $db->prepare("SELECT DISTINCT product_id FROM wishlist WHERE session_id = ?");
            $st->execute([$sId]);
        }
        $userWishlistProductIds = array_map('intval', $st->fetchAll(\PDO::FETCH_COLUMN) ?: []);
    }
}
$isInWishlist = in_array((int)($product['id'] ?? 0), $userWishlistProductIds);

if ($userCartProductIds === null) {
    if (isset($GLOBALS['initialCartProductIds'])) {
        $userCartProductIds = $GLOBALS['initialCartProductIds'];
    } else {
        $db = \App\Core\Database::getInstance();
        $uId = get_current_user_id();
        $sId = get_current_session_id();
        if ($uId) {
            $st = $db->prepare("SELECT DISTINCT product_id FROM cart_items WHERE user_id = ?");
            $st->execute([$uId]);
        } else {
            $st = $db->prepare("SELECT DISTINCT product_id FROM cart_items WHERE session_id = ?");
            $st->execute([$sId]);
        }
        $userCartProductIds = array_map('intval', $st->fetchAll(\PDO::FETCH_COLUMN) ?: []);
    }
}
$mainImage      = $sliderImages[0] ?? asset('assets/images/placeholder.jpg');
$totalImages    = count($sliderImages);
$displayThumbs  = array_slice($sliderImages, 0, 5);
?>

<div class="everful-card" id="<?= $cardId ?>" data-product-id="<?= $product['id'] ?? 0 ?>">
  
  <!-- Main Cover Photo Stage -->
  <div class="ef-img-stage">
    
    <a href="<?= $productUrl ?>" class="ef-main-link" style="display: block; width: 100%; height: 100%;">
      <img src="<?= htmlspecialchars($mainImage) ?>" 
           alt="<?= $name ?>" 
           id="mainImg_<?= $cardId ?>" 
           class="ef-main-img" 
           loading="lazy" 
           style="width: 100%; height: 100%; object-fit: cover; display: block;"
           onerror="this.onerror=null; this.src='<?= asset('assets/images/placeholder.jpg') ?>';">
    </a>

    <?php if (!empty($product['match_badge']) && !empty($showMatchBadge)): ?>
      <div style="position: absolute; top: 8px; left: 8px; z-index: 12; pointer-events: none;">
        <span style="background: rgba(0,0,0,0.75); backdrop-filter: blur(4px); color: #ffffff; font-size: 9.5px; font-weight: 700; padding: 2.5px 7.5px; border-radius: 9999px; box-shadow: 0 1px 3px rgba(0,0,0,0.25); display: inline-block;">
          <?= htmlspecialchars($product['match_badge']) ?>
        </span>
      </div>
    <?php endif; ?>

    <!-- Top-Right: Wishlist Heart Icon -->
    <button type="button" 
            class="ef-icon-btn ef-wishlist-btn <?= $isInWishlist ? 'active' : '' ?>" 
            onclick="toggleCardWishlist(<?= $product['id'] ?? 0 ?>, this)" 
            title="<?= $isInWishlist ? 'Remove from Wishlist' : 'Add to Wishlist' ?>">
      <svg class="ef-heart-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="<?= $isInWishlist ? 'fill:#f05a29; stroke:#f05a29;' : '' ?>">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
      </svg>
    </button>

    <!-- Bottom-Right: Quick Add to Cart Circular Icon Button -->
    <button type="button" 
            class="ef-icon-btn ef-cart-btn <?= $isInCart ? 'added in-cart' : '' ?>" 
            onclick="quickAddToCartCard(<?= $product['id'] ?? 0 ?>, 1, this)" 
            title="<?= $isInCart ? 'In Cart (Click to open Cart)' : 'Add to Cart' ?>">
      <svg class="ef-cart-icon" width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <?php if ($isInCart): ?>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        <?php else: ?>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
        <?php endif; ?>
      </svg>
    </button>

  </div>

  <!-- Multi-Image Thumbnails Row Below Cover Photo -->
  <?php if ($totalImages > 1): ?>
    <div class="ef-thumb-row">
      <?php foreach ($displayThumbs as $idx => $tUrl): ?>
        <button type="button" 
                class="ef-thumb-box <?= $idx === 0 ? 'active' : '' ?>" 
                onmouseover="switchCardMainImage('<?= $cardId ?>', '<?= htmlspecialchars($tUrl) ?>', this)"
                onclick="switchCardMainImage('<?= $cardId ?>', '<?= htmlspecialchars($tUrl) ?>', this)">
          <img src="<?= htmlspecialchars($tUrl) ?>" alt="Thumb" onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>';">
        </button>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- Card Body -->
  <div class="ef-card-body">
    
    <!-- Title Line -->
    <a href="<?= $productUrl ?>" class="ef-title-link">
      <h3 class="ef-product-title">
        <?php if ($isNew): ?>
          <span class="ef-badge-new">NEW</span>
        <?php endif; ?>
        <?= $name ?>
      </h3>
    </a>

    <!-- Price & Single Star Rating Line (In 1 Row) -->
    <div class="ef-price-line">
      <div class="ef-price-wrap">
        <span class="ef-price-amount"><?= format_price($effectivePrice) ?></span>
        <?php if ($discountPct > 0): ?>
          <span class="ef-original-price"><?= format_price($basePrice) ?></span>
        <?php endif; ?>
      </div>

      <!-- Single Star Rating Badge (e.g. ★ 4.8) -->
      <div class="ef-rating-single">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        <span><?= number_format($rating, 1) ?></span>
      </div>
    </div>

  </div>

</div>
