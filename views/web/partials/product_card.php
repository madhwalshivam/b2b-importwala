<?php
/**
 * Everful Wholesale Exact Product Card Component
 * Reusable partial across Homepage, Catalog, Search, Best Sellers, Category, Related Products.
 */

if (empty($product) || !is_array($product)) return;

$slug           = htmlspecialchars($product['slug'] ?? $product['id'] ?? '');
$productUrl     = url('product/' . $slug);
$name           = htmlspecialchars($product['name'] ?? $product['title'] ?? 'Wholesale Product');
$moq            = (int)($product['moq'] ?? 1);
$basePrice      = (float)($product['price'] ?? $product['base_price'] ?? 0);
$salePrice      = !empty($product['sale_price']) ? (float)$product['sale_price'] : null;
$effectivePrice = $salePrice ?: $basePrice;

$discountPct    = ($salePrice && $basePrice > $salePrice) 
    ? round((($basePrice - $salePrice) / $basePrice) * 100) 
    : 0;

$images         = get_product_images($product);
$totalImages    = count($images);
$mainImage      = $images[0] ?? asset('assets/images/placeholder.jpg');
$cardId         = 'pcard_' . ($product['id'] ?? rand(1000, 9999)) . '_' . rand(100, 999);

// Thumbnails setup: show up to 5 images (or 4 + remaining badge if > 5)
$maxThumbsToShow = ($totalImages == 5) ? 5 : 4;
$displayThumbs  = array_slice($images, 0, $maxThumbsToShow);
$remainingCount = max(0, $totalImages - $maxThumbsToShow);
$categoryName   = htmlspecialchars($product['category_name'] ?? 'Wholesale');

// Admin-Controlled Flags & Sold Count
$isBestSeller   = !empty($product['is_best_seller']);
$isNew          = !empty($product['is_new']) || !empty($product['is_new_arrival']);
$totalSold      = (int)($product['total_sold'] ?? $product['sales_count'] ?? 0);

// Check if product is currently in wishlist
static $userWishlistProductIds = null;
if ($userWishlistProductIds === null) {
    $db = \App\Core\Database::getInstance();
    if (session_status() === PHP_SESSION_NONE) @session_start();
    $uId = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $sId = $_SESSION['guest_wishlist_session_id'] ?? session_id();
    if ($uId) {
        $st = $db->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
        $st->execute([$uId]);
    } else {
        $st = $db->prepare("SELECT product_id FROM wishlist WHERE session_id = ?");
        $st->execute([$sId]);
    }
    $userWishlistProductIds = $st->fetchAll(\PDO::FETCH_COLUMN) ?: [];
}
$isInWishlist = in_array((int)($product['id'] ?? 0), $userWishlistProductIds);

// Check if product is currently in session inquiry list
static $userInquiryProductIds = null;
if ($userInquiryProductIds === null) {
    if (session_status() === PHP_SESSION_NONE) @session_start();
    $userInquiryProductIds = [];
    if (!empty($_SESSION['inquiry_list']) && is_array($_SESSION['inquiry_list'])) {
        foreach ($_SESSION['inquiry_list'] as $item) {
            if (!empty($item['product_id'])) {
                $userInquiryProductIds[] = (int)$item['product_id'];
            }
        }
    }
}
$isInInquiry = in_array((int)($product['id'] ?? 0), $userInquiryProductIds);
?>

<div class="everful-card" id="<?= $cardId ?>" data-product-id="<?= $product['id'] ?? 0 ?>">
  
  <!-- Main Image Stage -->
  <div class="ef-img-stage">
    
    <a href="<?= $productUrl ?>" class="ef-main-link">
      <img src="<?= htmlspecialchars($mainImage) ?>" 
           alt="<?= $name ?>" 
           id="mainImg_<?= $cardId ?>" 
           class="ef-main-img" 
           loading="lazy" 
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
            title="Add to Wishlist">
      <svg class="ef-heart-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
      </svg>
    </button>

    <!-- Bottom-Left: Camera / Visual Search Icon -->
    <button type="button" 
            class="ef-icon-btn ef-camera-btn" 
            onclick="triggerVisualSearchModal(<?= $product['id'] ?? 0 ?>, '<?= addslashes($name) ?>', '<?= htmlspecialchars($mainImage) ?>')" 
            title="Visually Similar Products">
      <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
      </svg>
    </button>

    <!-- Bottom-Right: Quick Add to Cart Button -->
    <button type="button" 
            class="ef-icon-btn ef-cart-btn" 
            onclick="quickAddToCartCard(<?= $product['id'] ?? 0 ?>, 1, this)" 
            title="Add to Cart">
      <svg class="ef-cart-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/>
      </svg>
    </button>

  </div>

  <!-- Thumbnails Row Below Image -->
  <div class="ef-thumb-row">
    <?php if ($totalImages > 1): ?>
      <?php foreach ($displayThumbs as $idx => $tUrl): ?>
        <button type="button" 
                class="ef-thumb-box <?= $idx === 0 ? 'active' : '' ?>" 
                onmouseover="switchCardMainImage('<?= $cardId ?>', '<?= htmlspecialchars($tUrl) ?>', this)"
                onclick="switchCardMainImage('<?= $cardId ?>', '<?= htmlspecialchars($tUrl) ?>', this)">
          <img src="<?= htmlspecialchars($tUrl) ?>" alt="Thumb" onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>';">
        </button>
      <?php endforeach; ?>
      <?php if ($remainingCount > 0): ?>
        <button type="button" 
                class="ef-thumb-box ef-thumb-more"
                onclick="openGlobalGalleryModal(<?= htmlspecialchars(json_encode($images, JSON_HEX_QUOT | JSON_HEX_TAG)) ?>, 4, '<?= $name ?>')"
                title="View all <?= $totalImages ?> images">
          +<?= $remainingCount ?>
        </button>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <!-- Card Body -->
  <div class="ef-card-body">
    
    <!-- Title Line with Dynamic Admin-Controlled Badges -->
    <a href="<?= $productUrl ?>" class="ef-title-link">
      <h3 class="ef-product-title">
        <?php if ($isNew): ?>
          <span class="ef-badge-new" style="background: #f05a29; color: #ffffff; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 4px; text-transform: uppercase; margin-right: 4px; display: inline-block;">NEW</span>
        <?php endif; ?>
        <?= $name ?>
      </h3>
    </a>

    <!-- Price & Discount Line -->
    <div class="ef-price-line">
      <?php if ($discountPct > 0): ?>
        <span class="ef-discount-tag">-<?= $discountPct ?>%</span>
      <?php endif; ?>
      <span class="ef-price-amount"><?= format_price($effectivePrice) ?></span>
    </div>

    <!-- MOQ & Sales Line -->
    <div class="ef-moq-line">
      <?= $moq > 1 ? "MOQ: {$moq} pcs" : "No MOQ" ?> &bull; <?= number_format($totalSold) ?>+ sold
    </div>

    <!-- Free Shipping Pill -->
    <?php if (!isset($product['is_free_shipping']) || !empty($product['is_free_shipping'])): ?>
      <div class="ef-tag-pills">
        <span class="ef-shipping-pill">Free Shipping</span>
      </div>
    <?php endif; ?>

  </div>

</div>
