<?php
/**
 * ImportWale Product Card Component (Importerr-style)
 * Features: multi-image thumbnails, +N VARIANTS badge, 1-line title, theme-colored price,
 * wishlist heart (top-right) and add-to-cart (bottom-right) icon buttons on image.
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

$images        = get_product_images($product);
$sliderImages  = $images;
if (empty($sliderImages)) {
    $sliderImages = [asset('assets/images/placeholder.jpg')];
}
$cardId        = 'pcard_' . ($product['id'] ?? rand(1000, 9999)) . '_' . rand(100, 999);

$isNew   = !empty($product['is_new']) || !empty($product['is_new_arrival']);
$prodId  = (int)($product['id'] ?? 0);

if (!empty($product['rating']) && (float)$product['rating'] > 0) {
    $rating = (float)$product['rating'];
} elseif (!empty($product['avg_rating']) && (float)$product['avg_rating'] > 0) {
    $rating = (float)$product['avg_rating'];
} else {
    $ratingsPool = [4.9, 4.7, 4.8, 4.6, 5.0, 4.5, 4.9, 4.8, 4.7, 4.9, 4.6, 4.8, 5.0, 4.7];
    $rating = $ratingsPool[$prodId % count($ratingsPool)];
}

// ── Wishlist & Cart status ──
static $userWishlistProductIds = null;
static $userCartProductIds     = null;

if ($userWishlistProductIds === null) {
    if (isset($GLOBALS['initialWishlistProductIds'])) {
        $userWishlistProductIds = $GLOBALS['initialWishlistProductIds'];
    } else {
        $db  = \App\Core\Database::getInstance();
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
$isInWishlist = in_array($prodId, $userWishlistProductIds);

if ($userCartProductIds === null) {
    if (isset($GLOBALS['initialCartProductIds'])) {
        $userCartProductIds = $GLOBALS['initialCartProductIds'];
    } else {
        $db  = \App\Core\Database::getInstance();
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
$isInCart = in_array($prodId, $userCartProductIds);

$mainImage     = $sliderImages[0] ?? asset('assets/images/placeholder.jpg');
$totalImages   = count($sliderImages);
// Limit to 6 images max on card for performance
$cardImages    = array_slice($sliderImages, 0, 6);

// ── Real Variant Count (product_colors first, fallback to product_variants) ──
static $variantCountCache = [];
if ($prodId > 0 && !isset($variantCountCache[$prodId])) {
    try {
        $dbv    = \App\Core\Database::getInstance();
        $vcStmt = $dbv->prepare("SELECT COUNT(*) FROM `product_colors` WHERE `product_id` = ?");
        $vcStmt->execute([$prodId]);
        $cnt = (int)$vcStmt->fetchColumn();
        if ($cnt === 0) {
            $pvStmt = $dbv->prepare("SELECT COUNT(*) FROM `product_variants` WHERE `product_id` = ? AND `is_active` = 1");
            $pvStmt->execute([$prodId]);
            $cnt = (int)$pvStmt->fetchColumn();
        }
        $variantCountCache[$prodId] = $cnt;
    } catch (\Throwable $e) {
        $variantCountCache[$prodId] = 0;
    }
}
$variantCount = ($prodId > 0) ? ($variantCountCache[$prodId] ?? 0) : 0;
?>

<div class="everful-card product-card" id="<?= $cardId ?>" data-product-id="<?= $prodId ?>">

  <!-- Horizontal Swipe Image Slider -->
  <div class="ef-swipe-wrap">
    <div class="ef-swipe-track" id="track_<?= $cardId ?>">
      <?php foreach ($cardImages as $sIdx => $sUrl): ?>
        <a href="<?= $productUrl ?>" class="ef-swipe-slide" draggable="false">
          <img src="<?= htmlspecialchars($sUrl) ?>"
               alt="<?= $name ?>"
               loading="<?= $sIdx === 0 ? 'eager' : 'lazy' ?>"
               draggable="false"
               onerror="this.onerror=null;this.src='<?= asset('assets/images/placeholder.jpg') ?>';">
        </a>
      <?php endforeach; ?>
    </div>

    <?php if ($totalImages > 1): ?>
    <div class="ef-swipe-dots" id="dots_<?= $cardId ?>">
      <?php for ($d = 0; $d < min($totalImages, 6); $d++): ?>
        <span class="ef-swipe-dot<?= $d === 0 ? ' active' : '' ?>"></span>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
<?php if ($totalImages > 1): ?>
<script>(function(){
  var tr=document.getElementById('track_<?= $cardId ?>');
  var dots=document.querySelectorAll('#dots_<?= $cardId ?> .ef-swipe-dot');
  if(!tr||!dots.length)return;
  tr.addEventListener('scroll',function(){
    var idx=Math.round(tr.scrollLeft/tr.offsetWidth);
    dots.forEach(function(d,i){d.classList.toggle('active',i===idx);});
  },{passive:true});
})();</script>
<?php endif; ?>


  <!-- Card Body -->
  <div class="ef-card-body">

    <!-- Variant Badge (Importerr-style: +N VARIANTS) -->
    <?php if ($variantCount > 0): ?>
      <div class="ef-variant-badge">+<?= $variantCount ?> VARIANT<?= $variantCount > 1 ? 'S' : '' ?></div>
    <?php endif; ?>

    <!-- Title: single line, ellipsis -->
    <a href="<?= $productUrl ?>" class="ef-title-link">
      <h3 class="ef-product-title">
        <?php if ($isNew): ?><span class="ef-badge-new">NEW</span><?php endif; ?>
        <?= $name ?>
      </h3>
    </a>

    <!-- Price & Star Rating -->
    <div class="ef-price-line">
      <div class="ef-price-wrap">
        <span class="ef-price-amount"><?= format_price($effectivePrice) ?></span>
        <?php if ($discountPct > 0): ?>
          <span class="ef-original-price"><?= format_price($basePrice) ?></span>
        <?php endif; ?>
      </div>
      <div class="ef-rating-single">
        <svg width="12" height="12" viewBox="0 0 24 24" fill="#f59e0b" stroke="#f59e0b">
          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
        </svg>
        <span><?= number_format($rating, 1) ?></span>
      </div>
    </div>

  </div>

</div>
