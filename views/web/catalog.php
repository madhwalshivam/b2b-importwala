<?php
$title = "Wholesale Catalog | ImportWala";
ob_start();
?>

<div style="display: grid; grid-template-columns: 260px 1fr; gap: 28px; margin-top: 10px;">

  <!-- Faceted Filter Sidebar -->
  <aside style="background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:20px; height:fit-content;">
    <h3 style="font-size:16px; font-weight:800; margin-bottom:16px; color:#111827;">Filter By Category</h3>
    <ul style="list-style:none; margin-bottom:24px;">
      <li style="margin-bottom:8px;">
        <a href="/catalog" style="font-size:13px; font-weight:<?= empty($filters['category_id']) ? '700; color:#f05a29' : '500; color:#4b5563' ?>;">All Categories</a>
      </li>
      <?php if (!empty($categories)): ?>
        <?php foreach ($categories as $cat): ?>
          <li style="margin-bottom:8px;">
            <a href="/catalog?category_id=<?= $cat['id'] ?>" style="font-size:13px; font-weight:<?= ($filters['category_id'] == $cat['id']) ? '700; color:#f05a29' : '500; color:#4b5563' ?>;">
              <?= htmlspecialchars($cat['name']) ?>
            </a>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>
    </ul>

    <h3 style="font-size:16px; font-weight:800; margin-bottom:16px; color:#111827;">Sort Products</h3>
    <form action="/catalog" method="GET">
      <?php if (!empty($q)): ?><input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>"><?php endif; ?>
      <?php if (!empty($filters['category_id'])): ?><input type="hidden" name="category_id" value="<?= $filters['category_id'] ?>"><?php endif; ?>
      
      <select name="sort" onchange="this.form.submit()" style="width:100%; padding:10px; border-radius:6px; border:1px solid #d1d5db; font-size:13px; outline:none; margin-bottom:20px;">
        <option value="relevance" <?= ($filters['sort'] == 'relevance') ? 'selected' : '' ?>>Most Relevant</option>
        <option value="popular" <?= ($filters['sort'] == 'popular') ? 'selected' : '' ?>>Best Sellers</option>
        <option value="price_asc" <?= ($filters['sort'] == 'price_asc') ? 'selected' : '' ?>>Price: Low to High</option>
        <option value="price_desc" <?= ($filters['sort'] == 'price_desc') ? 'selected' : '' ?>>Price: High to Low</option>
        <option value="newest" <?= ($filters['sort'] == 'newest') ? 'selected' : '' ?>>Newest Arrivals</option>
      </select>
    </form>
  </aside>

  <!-- Product Search Results Main Grid -->
  <div>
    <div style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
      <h2 style="font-size:22px; font-weight:800; color:#111827;">
        <?php if (!empty($q)): ?>
          Search Results for "<?= htmlspecialchars($q) ?>"
        <?php else: ?>
          Wholesale Products
        <?php endif; ?>
      </h2>
      <span style="font-size:13px; color:#6b7280; font-weight:600;">Showing <?= count($results['items'] ?? []) ?> of <?= $results['total'] ?? 0 ?> items</span>
    </div>

    <div class="product-grid">
      <?php if (!empty($results['items'])): ?>
        <?php foreach ($results['items'] as $product): ?>
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
      <?php else: ?>
        <div style="grid-column: 1 / -1; background:#fff; padding:48px; text-align:center; border-radius:8px; border:1px solid #e5e7eb;">
          <h3 style="font-size:18px; font-weight:700; color:#374151; margin-bottom:8px;">No wholesale items found</h3>
          <p style="font-size:14px; color:#6b7280;">Try searching for another keyword or selecting a different category.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
