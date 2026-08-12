<?php
$title = "ImportWale Wholesale | Direct Global B2B Platform";
ob_start();
?>

<!-- Hero Banner Section (Using Official Custom Banner Image) -->
<div class="hero-banner-wrapper" style="margin:20px 0 36px 0; border-radius:16px; overflow:hidden; position:relative; background:#FAF4F2;">
  
  <!-- Subtle Sleek Slider Arrows -->
  <button type="button" class="hero-arrow-btn hero-arrow-prev" aria-label="Previous Slide">
    <svg style="width:20px; height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
  </button>
  <button type="button" class="hero-arrow-btn hero-arrow-next" aria-label="Next Slide">
    <svg style="width:20px; height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
  </button>

  <a href="<?= url('catalog?q=halloween') ?>" style="display:block; width:100%;">
    <img src="<?= asset('images/hero-spooky-banner.png') ?>" alt="Classic Spooky Mask Collection - Shop Wholesale" style="width:100%; height:auto; display:block; object-fit:cover; border-radius:16px;">
  </a>

</div>

<!-- Featured Products Section -->
<div class="section-header-title">
  <span>Featured Wholesale Products</span>
  <a href="<?= url('catalog') ?>" style="font-size:14px; color:#f05a29; font-weight:600;">View All &rarr;</a>
</div>

<div class="product-grid">
  <?php if (!empty($featuredProducts)): ?>
    <?php foreach ($featuredProducts as $product): ?>
      <div class="product-card">
        <div class="product-card-image-wrapper">
          <img src="<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="product-card-img" loading="lazy">
          <span class="product-badge-moq">MOQ: <?= $product['moq'] ?> pcs</span>
          <?php if (!empty($product['is_best_seller'])): ?>
            <span class="product-badge-shipping">BEST SELLER</span>
          <?php endif; ?>
        </div>
        <div class="product-card-body">
          <a href="<?= url('product/' . htmlspecialchars($product['slug'])) ?>">
            <h3 class="product-title"><?= htmlspecialchars($product['title']) ?></h3>
          </a>
          <div class="product-price-row">
            <span class="product-unit-price">$<?= number_format($product['base_price'], 2) ?></span>
            <span class="product-moq-label">/ piece</span>
          </div>
          <div class="product-tiered-summary">
            Tier Discounts Available
          </div>
          <a href="<?= url('product/' . htmlspecialchars($product['slug'])) ?>" class="btn-add-cart-card" style="text-align:center; display:block;">View Wholesale Tiers</a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Best Sellers Section -->
<div class="section-header-title">
  <span>High-Volume Best Sellers</span>
  <a href="<?= url('catalog?sort=popular') ?>" style="font-size:14px; color:#f05a29; font-weight:600;">View All &rarr;</a>
</div>

<div class="product-grid">
  <?php if (!empty($bestSellers)): ?>
    <?php foreach ($bestSellers as $product): ?>
      <div class="product-card">
        <div class="product-card-image-wrapper">
          <img src="<?= htmlspecialchars($product['main_image']) ?>" alt="<?= htmlspecialchars($product['title']) ?>" class="product-card-img" loading="lazy">
          <span class="product-badge-moq">MOQ: <?= $product['moq'] ?> pcs</span>
        </div>
        <div class="product-card-body">
          <a href="<?= url('product/' . htmlspecialchars($product['slug'])) ?>">
            <h3 class="product-title"><?= htmlspecialchars($product['title']) ?></h3>
          </a>
          <div class="product-price-row">
            <span class="product-unit-price">$<?= number_format($product['base_price'], 2) ?></span>
            <span class="product-moq-label">/ piece</span>
          </div>
          <a href="<?= url('product/' . htmlspecialchars($product['slug'])) ?>" class="btn-add-cart-card" style="text-align:center; display:block;">View Wholesale Tiers</a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
