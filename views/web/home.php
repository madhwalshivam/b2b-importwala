<?php
$title = "Everful Wholesale | Back To School Campus Trends";
ob_start();
?>

<!-- Soft Blush-Pink Hero Banner Section (Everful Reference Style) -->
<div class="hero-banner-wrapper">
  
  <!-- Navigation Slider Arrows -->
  <button type="button" class="hero-arrow-btn hero-arrow-prev" aria-label="Previous Slide">
    <svg style="width:20px; height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
  </button>
  <button type="button" class="hero-arrow-btn hero-arrow-next" aria-label="Next Slide">
    <svg style="width:20px; height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
  </button>

  <div class="hero-banner-inner">
    <!-- Left Column (40% Text Content) -->
    <div class="hero-text-col">
      <h1 class="hero-headline-eyebrow">Back to school:</h1>
      <div class="hero-headline-italic">top-selling</div>
      <div class="hero-headline-italic">campus trends</div>

      <p class="hero-subtext">
        Discover our curated collection of aesthetic stationeries, pastel notebooks, and trendy accessories with low MOQs and factory-direct pricing.
      </p>

      <a href="<?= url('catalog') ?>" class="btn-hero-cta">
        EXPLORE CATALOG &rarr;
      </a>
    </div>

    <!-- Right Column (60% Asymmetric Product Collage) -->
    <div class="hero-collage-col">
      <div class="collage-container">
        <!-- Main Large Cutout (Notebook) -->
        <div class="collage-item-main">
          <img src="https://images.unsplash.com/photo-1583485088034-697b5bc54ccd?auto=format&fit=crop&w=600&q=80" alt="Pastel Notebook Planner" title="Pastel Notebook Planner">
        </div>
        <!-- Top Right Sub-Item (Pens / Accessories) -->
        <div class="collage-item-sub">
          <img src="https://images.unsplash.com/photo-1586350977771-b3b0abd50c82?auto=format&fit=crop&w=500&q=80" alt="Gel Pens & Highlighters" title="Gel Pens Set">
        </div>
        <!-- Bottom Right Sub-Item (Pouch / Backpack Accessories) -->
        <div class="collage-item-sub">
          <img src="https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=500&q=80" alt="Accessories & Pouch" title="Accessories">
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Featured Products Section -->
<div class="section-header-title">
  <span>Trending Campus Wholesale Items</span>
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
