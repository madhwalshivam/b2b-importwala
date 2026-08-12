<?php
$title = "ImportWala | Direct Global Wholesale Platform";
ob_start();
?>

<!-- Hero Banner Section -->
<div class="hero-banner-grid">
  <div class="hero-main-card">
    <div class="hero-tag">⚡ DIRECT FACTORY WHOLESALE</div>
    <h1 class="hero-title">Trending Wholesale Accessories & Apparel</h1>
    <p class="hero-subtitle">Factory-direct prices, low MOQ, global express shipping. Save up to 40% on bulk volume orders.</p>
    <div>
      <a href="/catalog" class="btn-primary-orange">Shop Wholesale Catalog &rarr;</a>
    </div>
  </div>

  <div class="hero-side-card">
    <div>
      <span style="background:#fff2ed; color:#f05a29; font-weight:700; font-size:12px; padding:4px 8px; border-radius:4px;">VOLUME DISCOUNTS</span>
      <h3 style="font-size:20px; font-weight:800; margin:12px 0 8px 0; color:#111827;">Tiered Wholesale Rates</h3>
      <p style="font-size:13px; color:#4b5563;">Buy more, save more automatically at checkout. Instant MOQ tier calculations.</p>
    </div>
    <div style="margin-top:20px;">
      <a href="/catalog?sort=popular" style="color:#f05a29; font-weight:700; font-size:14px;">Browse Best Sellers &rarr;</a>
    </div>
  </div>
</div>

<!-- Featured Products Section -->
<div class="section-header-title">
  <span>Featured Wholesale Products</span>
  <a href="/catalog" style="font-size:14px; color:#f05a29; font-weight:600;">View All &rarr;</a>
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
          <a href="/product/<?= htmlspecialchars($product['slug']) ?>">
            <h3 class="product-title"><?= htmlspecialchars($product['title']) ?></h3>
          </a>
          <div class="product-price-row">
            <span class="product-unit-price">$<?= number_format($product['base_price'], 2) ?></span>
            <span class="product-moq-label">/ piece</span>
          </div>
          <div class="product-tiered-summary">
            Tier Discounts Available
          </div>
          <a href="/product/<?= htmlspecialchars($product['slug']) ?>" class="btn-add-cart-card" style="text-align:center; display:block;">View Wholesale Tiers</a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Best Sellers Section -->
<div class="section-header-title">
  <span>High-Volume Best Sellers</span>
  <a href="/catalog?sort=popular" style="font-size:14px; color:#f05a29; font-weight:600;">View All &rarr;</a>
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
          <a href="/product/<?= htmlspecialchars($product['slug']) ?>">
            <h3 class="product-title"><?= htmlspecialchars($product['title']) ?></h3>
          </a>
          <div class="product-price-row">
            <span class="product-unit-price">$<?= number_format($product['base_price'], 2) ?></span>
            <span class="product-moq-label">/ piece</span>
          </div>
          <a href="/product/<?= htmlspecialchars($product['slug']) ?>" class="btn-add-cart-card" style="text-align:center; display:block;">View Wholesale Tiers</a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
