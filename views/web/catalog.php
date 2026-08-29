<?php
if (!empty($collectionCard['title'])) {
  $pageHeading = htmlspecialchars($collectionCard['title']);
  $title = $pageHeading . " | ImportWala";
} elseif (!empty($q)) {
  $pageHeading = "Search Results for \"" . htmlspecialchars($q) . "\"";
  $title = $pageHeading . " | ImportWala";
} elseif (!empty($filters['category_id']) && !empty($categories)) {
  $foundCat = null;
  foreach ($categories as $c) {
    if ($c['id'] == $filters['category_id']) {
      $foundCat = $c['name'];
      break;
    }
  }
  $pageHeading = $foundCat ? htmlspecialchars($foundCat) : "Wholesale Catalog";
  $title = $pageHeading . " | ImportWala";
} else {
  $pageHeading = "Wholesale Catalog";
  $title = "Wholesale Catalog | ImportWala";
}
ob_start();

$currentSort = $filters['sort'] ?? 'relevance';
$activeCategory = $filters['category_id'] ?? null;
?>

<div class="catalog-page-wrapper" style="margin-top: 0; margin-bottom: 50px;">

  <!-- ============================================================
       1. HERO BANNER (User Provided Luxury Wholesale Image Banner)
       ============================================================ -->
  <div class="everful-collection-banner"
    style="width: 100%; border-radius: 16px; margin: 12px 0 24px 0; overflow: hidden; position: relative; box-shadow: 0 4px 20px rgba(0,0,0,0.08); background: #0f172a;">

    <img src="<?= asset('assets/images/hero-wholesale-banner.png') ?>" alt="ImportWala Wholesale Banner"
      style="width: 100%; height: 260px; display: block; object-fit: cover; object-position: center; border-radius: 16px;">

    <!-- High-Contrast Text Overlay -->
    <div
      style="position: absolute; inset: 0; background: linear-gradient(90deg, rgba(15,23,42,0.85) 0%, rgba(15,23,42,0.68) 45%, rgba(15,23,42,0.15) 100%); padding: 36px 40px; display: flex; flex-direction: column; justify-content: center; z-index: 2;">
      <h1 class="catalog-heading"
        style="font-family: 'Inter', system-ui, -apple-system, sans-serif !important; font-size: 28px; font-weight: 700; color: #ffffff; margin: 0 0 8px 0; letter-spacing: -0.015em; text-shadow: 0 2px 4px rgba(0,0,0,0.4);">
        <?= $pageHeading ?>
      </h1>
      <p
        style="font-size: 14.5px; line-height: 1.5; color: rgba(255, 255, 255, 0.92); margin: 0 0 20px 0; max-width: 580px; font-weight: 400; font-family: 'Inter', system-ui, sans-serif; text-shadow: 0 1px 3px rgba(0,0,0,0.5);">
        Discover top-rated wholesale products, direct factory pricing, and trending global styles for your business.
        10,000+ new items added daily with low MOQ.
      </p>
      <div>

      </div>
    </div>

  </div>

  <!-- ============================================================
       2. TOP HORIZONTAL FILTER TOOLBAR
       ============================================================ -->
  <div id="catalogProductsSection" class="catalog-top-toolbar"
    style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; padding: 12px 16px; background: #ffffff; border: 1px solid #f1f5f9; border-radius: 14px; box-shadow: 0 1px 4px rgba(0,0,0,0.02);">

    <!-- Left Side: Main Category Select Filter -->
    <div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0;">
      <div style="position: relative; flex-shrink: 0;">
        <select onchange="if(this.value) window.location.href=this.value;"
          style="appearance: none; -webkit-appearance: none; background: #f1f5f9; color: #1e293b; font-size: 13px; font-weight: 600; padding: 8px 32px 8px 16px; border-radius: 9999px; border: 1px solid #e2e8f0; cursor: pointer; outline: none; font-family: 'Poppins', system-ui, sans-serif; transition: all 0.2s ease;">
          <option value="<?= url('catalog') ?>" <?= empty($activeCategory) ? 'selected' : '' ?>>Show Filters</option>
          <option value="<?= url('catalog') ?>">All Categories</option>
          <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= url('catalog?category_id=' . $cat['id']) ?>" <?= ($activeCategory == $cat['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
              </option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <svg
          style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #475569; pointer-events: none;"
          fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 9l-7 7-7-7" />
        </svg>
      </div>
    </div>

    <!-- Right Side: Result Count & Sort Dropdown -->
    <div style="display: flex; align-items: center; gap: 16px; flex-shrink: 0;">
      <span style="font-size: 13px; color: #64748b; font-weight: 500; white-space: nowrap;">
        Showing <strong><?= count($results['items'] ?? []) ?></strong> of <strong><?= $results['total'] ?? 0 ?></strong>
        results
      </span>

      <form action="<?= url('catalog') ?>" method="GET" style="margin: 0;">
        <?php if (!empty($q)): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
        <?php if (!empty($filters['category_id'])): ?><input type="hidden" name="category_id"
            value="<?= $filters['category_id'] ?>"><?php endif; ?>
        <?php if (!empty($filters['collection_id'])): ?><input type="hidden" name="collection_id"
            value="<?= $filters['collection_id'] ?>"><?php endif; ?>

        <div style="position: relative; display: inline-block;">
          <select name="sort" onchange="this.form.submit()"
            style="appearance: none; -webkit-appearance: none; background: #ffffff; color: #0f172a; font-size: 13px; font-weight: 600; padding: 8px 32px 8px 14px; border-radius: 9999px; border: 1px solid #cbd5e1; cursor: pointer; outline: none; font-family: 'Poppins', system-ui, sans-serif;">
            <option value="relevance" <?= ($currentSort === 'relevance') ? 'selected' : '' ?>>Featured</option>
            <option value="price_asc" <?= ($currentSort === 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
            <option value="price_desc" <?= ($currentSort === 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
            <option value="popular" <?= ($currentSort === 'popular') ? 'selected' : '' ?>>Best Sellers</option>
            <option value="newest" <?= ($currentSort === 'newest') ? 'selected' : '' ?>>Newest Arrivals</option>
          </select>
          <svg
            style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #64748b; pointer-events: none;"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </div>
      </form>
    </div>

  </div>

  <!-- ============================================================
       3. FULL-WIDTH 5-COLUMN PRODUCT GRID
       ============================================================ -->
  <div class="product-grid">
    <?php if (!empty($results['items'])): ?>
      <?php foreach ($results['items'] as $product): ?>
        <?php require __DIR__ . '/partials/product_card.php'; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <div
        style="grid-column: 1 / -1; background: #ffffff; padding: 60px 20px; text-align: center; border-radius: 14px; border: 1px solid #f1f5f9; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
        <svg style="width: 48px; height: 48px; color: #cbd5e1; margin-bottom: 12px;" fill="none" stroke="currentColor"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <h3 class="catalog-heading"
          style="font-family: 'Poppins', system-ui, sans-serif !important; font-size: 20px; font-weight: 600; color: #1e293b; margin: 0 0 6px 0;">
          No Wholesale Products Found</h3>
        <p style="font-size: 14px; color: #64748b; margin: 0 0 16px 0;">Try adjusting your search keywords or category
          filters.</p>
        <a href="<?= url('catalog') ?>"
          style="display: inline-block; padding: 10px 24px; background: #f05a29; color: #ffffff; font-weight: 600; font-size: 13px; border-radius: 8px; text-decoration: none;">View
          All Wholesale Products</a>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>