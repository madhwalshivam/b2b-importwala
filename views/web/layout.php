<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'ImportWala | World-Scale B2B Wholesale Platform') ?></title>
  <link rel="stylesheet" href="<?= asset('css/everful-theme.css') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

  <!-- Top Announcement Bar -->
  <div class="top-announcement-bar">
    <span>🔥 FREE AIR SHIPPING ON ORDERS $100+ (ELIGIBLE WHOLESALE ITEMS ONLY)</span>
  </div>

  <!-- Header Main -->
  <header class="header-container">
    <div class="header-main">
      <a href="<?= url('') ?>" class="brand-logo">
        <span class="highlight">IMPORT</span>WALA <span style="font-size:12px; border:1px solid #f05a29; color:#f05a29; padding:2px 6px; border-radius:4px; margin-left:6px;">WHOLESALE</span>
      </a>

      <form action="<?= url('catalog') ?>" method="GET" class="search-bar-wrapper">
        <select name="category_id" class="search-category-select">
          <option value="">All Categories</option>
          <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
            <?php endforeach; ?>
          <?php endif; ?>
        </select>
        <input type="text" name="q" class="search-input" placeholder="Search 50,000+ wholesale items by name, SKU, or keyword..." value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button type="submit" class="search-submit-btn">Search</button>
      </form>

      <div class="header-actions">
        <a href="<?= url('catalog') ?>" class="header-action-item">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
          <span>Catalog</span>
        </a>
        <a href="<?= url('cart') ?>" class="header-action-item">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
          <span>Cart</span>
          <div class="cart-pill-count" id="headerCartCount">0</div>
        </a>
      </div>
    </div>

    <!-- Category Sub-Nav Bar -->
    <nav class="nav-categories-bar">
      <div class="nav-categories-inner">
        <a href="<?= url('') ?>" class="nav-category-link active">Home</a>
        <a href="<?= url('catalog') ?>" class="nav-category-link">All Products</a>
        <a href="<?= url('catalog?sort=newest') ?>" class="nav-category-link">New Arrivals</a>
        <a href="<?= url('catalog?sort=popular') ?>" class="nav-category-link">Best Sellers</a>
        <?php if (!empty($categories)): ?>
          <?php foreach ($categories as $cat): ?>
            <a href="<?= url('catalog?category_id=' . $cat['id']) ?>" class="nav-category-link"><?= htmlspecialchars($cat['name']) ?></a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </nav>
  </header>

  <!-- Page Dynamic Content -->
  <main class="main-container">
    <?= $content ?>
  </main>

  <!-- Footer -->
  <footer class="footer-container">
    <div class="footer-inner">
      <div class="footer-col">
        <h4>ImportWala Wholesale</h4>
        <p style="font-size:13px; color:#9ca3af; margin-bottom:12px;">World-Scale B2B Wholesale Platform connecting international buyers directly with global manufacturers.</p>
        <p style="font-size:13px; color:#9ca3af;">Email: support@importwala.com</p>
      </div>
      <div class="footer-col">
        <h4>Customer Care</h4>
        <ul>
          <li><a href="<?= url('catalog') ?>">All Products</a></li>
          <li><a href="<?= url('cart') ?>">Shopping Cart</a></li>
          <li><a href="#">Shipping Information</a></li>
          <li><a href="#">Wholesale Verification</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Top Categories</h4>
        <ul>
          <li><a href="<?= url('catalog?category_id=1') ?>">Jewelry & Accessories</a></li>
          <li><a href="<?= url('catalog?category_id=2') ?>">Hats & Headwear</a></li>
          <li><a href="<?= url('catalog?category_id=3') ?>">Stationery & Office</a></li>
          <li><a href="<?= url('catalog?category_id=4') ?>">Socks & Apparel</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      &copy; <?= date('Y') ?> ImportWala Wholesale Inc. All rights reserved. High-Scale B2B Architecture.
    </div>
  </footer>

  <script>
    // Global Cart Pill Updater
    async function updateHeaderCartCount() {
      try {
        const res = await fetch('<?= url('api/cart') ?>');
        const data = await res.json();
        if (data.success && data.cart) {
          document.getElementById('headerCartCount').innerText = data.cart.items_count || 0;
        }
      } catch(e) {}
    }
    updateHeaderCartCount();
  </script>
</body>
</html>
