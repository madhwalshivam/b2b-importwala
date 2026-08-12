<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'ImportWala | World-Scale B2B Wholesale Platform') ?></title>
  <link rel="stylesheet" href="<?= asset('css/everful-theme.css') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

  <!-- Top Announcement Bar -->
  <div class="top-announcement-bar" id="topAnnouncementBar">
    <div></div>
    <div class="top-announcement-content">
      <span>FREE SHIPPING ON $100+ (</span>
      <a href="<?= url('catalog') ?>">ELIGIBLE ITEMS ONLY</a>
      <span>)</span>
    </div>
    <button type="button" class="announcement-close-btn" onclick="document.getElementById('topAnnouncementBar').style.display='none'">✕</button>
  </div>

  <!-- Header Main -->
  <header class="header-container">
    <div class="header-main">
      <a href="<?= url('') ?>" class="brand-logo">
        <span style="letter-spacing:1px;">EVERFUL</span>
      </a>

      <form action="<?= url('catalog') ?>" method="GET" class="search-bar-wrapper">
        <input type="text" name="q" class="search-input" placeholder="crew socks" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
        <button type="button" style="background:none; border:none; color:#888; cursor:pointer; padding:0 8px;" title="Search by image">
          <svg style="width:20px; height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><circle cx="12" cy="13" r="3"/></svg>
        </button>
        <button type="submit" class="search-submit-btn">
          <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </button>
      </form>

      <div class="header-actions">
        <select class="currency-selector">
          <option value="USD">EN - USD ˅</option>
          <option value="INR">HI - INR ˅</option>
          <option value="EUR">EU - EUR ˅</option>
        </select>
        <a href="<?= url('account') ?>" class="header-icon-item" title="Account">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
        </a>
        <a href="<?= url('wishlist') ?>" class="header-icon-item" title="Wishlist">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
        </a>
        <a href="<?= url('cart') ?>" class="header-icon-item" title="Cart">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
          <div class="cart-pill-count" id="headerCartCount">1</div>
        </a>
      </div>
    </div>

    <!-- Category Sub-Nav Bar -->
    <nav class="nav-categories-bar">
      <div class="nav-categories-inner">
        <a href="<?= url('catalog?category_id=1') ?>" class="nav-category-link">Category ˅</a>
        <a href="<?= url('catalog?sort=newest') ?>" class="nav-category-link">New Arrivals</a>
        <a href="<?= url('catalog?sort=popular') ?>" class="nav-category-link">Best Sellers</a>
        <a href="<?= url('catalog?free_shipping=1') ?>" class="nav-category-link">Free Air Shipping</a>
        <a href="<?= url('catalog?price_drops=1') ?>" class="nav-category-link">Price Drops</a>
        <a href="<?= url('catalog?tag=halloween') ?>" class="nav-category-link">Halloween</a>
        <a href="<?= url('catalog?tag=trends') ?>" class="nav-category-link">2026 Autumn Trends</a>
        <a href="<?= url('catalog?type=solutions') ?>" class="nav-category-link">Solutions ˅</a>
        <a href="<?= url('catalog?type=business') ?>" class="nav-category-link">Business Types ˅</a>
        <a href="<?= url('blog') ?>" class="nav-category-link">Blog</a>
        <a href="<?= url('support') ?>" class="nav-category-link">Support ˅</a>
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
        <h4>Everful Wholesale</h4>
        <p style="font-size:13px; color:#9ca3af; margin-bottom:12px;">World-Scale B2B Wholesale Platform connecting international buyers directly with global manufacturers.</p>
        <p style="font-size:13px; color:#9ca3af;">Email: helpdesk@everfulwholesale.com</p>
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
      &copy; <?= date('Y') ?> Everful Wholesale Inc. All rights reserved. High-Scale B2B Architecture.
    </div>
  </footer>

  <script>
    // Global Cart Pill Updater
    async function updateHeaderCartCount() {
      try {
        const res = await fetch('<?= url('api/cart') ?>');
        const data = await res.json();
        if (data.success && data.cart) {
          document.getElementById('headerCartCount').innerText = data.cart.items_count || 1;
        }
      } catch(e) {}
    }
    updateHeaderCartCount();
  </script>
</body>
</html>
