<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="<?= csrf_token() ?>">
  <title><?= htmlspecialchars($title ?? 'ImportWale | World-Scale B2B Wholesale Platform') ?></title>

  <!-- Tailwind CSS CDN (required for all utility classes across the site) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#f05a29',
            'primary-dark': '#d8481b',
          },
          fontFamily: {
            sans: ['Inter', 'system-ui', 'sans-serif'],
          },
        }
      },
      corePlugins: {
        preflight: false, /* Don't override existing everful-theme.css base styles */
      }
    }
  </script>

  <!-- Site Stylesheets -->
  <link rel="stylesheet" href="<?= asset('css/everful-theme.css') ?>?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= asset('css/product-card-unified.css') ?>?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= asset('css/theme.css') ?>?v=<?= time() ?>">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@400;500;600;700;800&display=swap"
    rel="stylesheet">

  <style>
    /* ============================================================
       GLOBAL BRAND ORANGE SCROLLBAR
       ============================================================ */
    ::-webkit-scrollbar {
      width: 5px;
      height: 5px;
    }
    ::-webkit-scrollbar-track {
      background: transparent;
    }
    ::-webkit-scrollbar-thumb {
      background: #CBD5E1;
      border-radius: 9999px;
    }
    ::-webkit-scrollbar-thumb:hover {
      background: #94A3B8;
    }
    * {
      scrollbar-width: thin;
      scrollbar-color: #CBD5E1 transparent;
    }

    /* ============================================================
       GLOBAL SVG / ICON SAFETY RESET
       Prevents inline SVGs from expanding to full container width
       when no explicit Tailwind w-* / h-* class is present.
       ============================================================ */
    svg:not([width]):not([height]):not(.no-size-reset) {
      width: 1em;
      height: 1em;
    }

    /* Product detail layout helpers */
    .font-sans { font-family: 'Inter', system-ui, sans-serif; }

    /* Custom scrollbar for variant list */
    .custom-scrollbar::-webkit-scrollbar { width: 4px; }
    .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* Prose styles for product description */
    .prose { color: #374151; line-height: 1.7; }
    .prose p { margin-bottom: 0.75rem; }
    .prose ul, .prose ol { padding-left: 1.5rem; margin-bottom: 0.75rem; }
    .prose li { margin-bottom: 0.25rem; }
    .prose strong { font-weight: 700; }
    .prose h2, .prose h3, .prose h4 { font-weight: 700; margin: 1rem 0 0.5rem; }
  </style>
</head>

<?php
$db = \App\Core\Database::getInstance();
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
$userId = !empty($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
$sessionId = $_SESSION['guest_wishlist_session_id'] ?? session_id();
if ($userId) {
    $wStmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE user_id = ?");
    $wStmt->execute([$userId]);
} else {
    $wStmt = $db->prepare("SELECT COUNT(*) FROM wishlist WHERE session_id = ?");
    $wStmt->execute([$sessionId]);
}
$initialWishlistCount = (int)$wStmt->fetchColumn();
?>

<body>

  <!-- Top Announcement Bar (Dynamic Full-Bar Clickable Marquee) -->
  <?php
  $db = \App\Core\Database::getInstance();
  $announcementStmt = $db->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
  $announcement = $announcementStmt ? $announcementStmt->fetch() : null;
  ?>

  <?php if (!empty($announcement) && !empty($announcement['is_active'])): ?>
    <?php
    $msgText = htmlspecialchars(htmlspecialchars_decode($announcement['message']));
    $ctaLink = !empty($announcement['cta_link']) ? url(ltrim($announcement['cta_link'], '/')) : '#';
    $repeatedBlock = $msgText . ' &nbsp;&nbsp;&bull;&nbsp;&nbsp; ' . $msgText . ' &nbsp;&nbsp;&bull;&nbsp;&nbsp; ' . $msgText . ' &nbsp;&nbsp;&bull;&nbsp;&nbsp; ' . $msgText . ' &nbsp;&nbsp;&bull;&nbsp;&nbsp; ';
    ?>
    <style>
      @keyframes announcementLoopAnimation {
        0% {
          transform: translateX(0);
        }

        100% {
          transform: translateX(-50%);
        }
      }

      .announcement-moving-track {
        display: inline-flex !important;
        align-items: center !important;
        white-space: nowrap !important;
        animation: announcementLoopAnimation 50s linear infinite !important;
        will-change: transform;
      }

      .announcement-moving-wrapper:hover .announcement-moving-track {
        animation-play-state: paused !important;
      }
    </style>

    <div class="top-announcement-bar" id="topAnnouncementBar"
      style="background:#FFF2ED; color:#D8481B; height:38px; min-height:38px; max-height:38px; display:flex !important; align-items:center !important; justify-content:space-between !important; padding:0 12px 0 16px; border-bottom:1px solid #FFE2E2; overflow:hidden !important; font-size:12.5px; font-weight:500; white-space:nowrap !important; line-height:38px;">

      <!-- Entire Announcement Bar Clickable Link -->
      <a href="<?= $ctaLink ?>" class="announcement-moving-wrapper"
        style="flex:1; overflow:hidden !important; white-space:nowrap !important; position:relative; display:flex !important; align-items:center !important; margin-right:12px; height:38px; text-decoration:none !important; color:#D8481B !important; cursor:pointer;"
        title="Click to view offer">
        <div class="announcement-moving-track">
          <span
            style="white-space:nowrap !important; font-weight:500; font-size:12.5px; color:#D8481B; display:inline-block !important;"><?= $repeatedBlock ?></span>
          <span aria-hidden="true"
            style="white-space:nowrap !important; font-weight:500; font-size:12.5px; color:#D8481B; display:inline-block !important;"><?= $repeatedBlock ?></span>
        </div>
      </a>

      <!-- Close Button Only -->
      <div
        style="display:flex !important; align-items:center !important; flex-shrink:0; background:#FFF2ED; height:100%; padding-left:8px; z-index:10;">
        <button type="button" onclick="document.getElementById('topAnnouncementBar').style.display='none'"
          title="Close Announcement"
          style="background:none !important; border:none !important; outline:none !important; box-shadow:none !important; font-size:14px; color:#9CA3AF; cursor:pointer; padding:2px 4px; line-height:1;"
          onmouseover="this.style.color='#111827';" onmouseout="this.style.color='#9CA3AF';">
          ✕
        </button>
      </div>

    </div>
  <?php endif; ?>

  <!-- Header Main -->
  <header class="header-container">
    <div class="header-main">
      <!-- Official ImportWale Logo (Double Sized) -->
      <a href="<?= url('') ?>" class="brand-logo">
        <img src="<?= asset('images/importwale-logo.png') ?>" alt="IMPORTWALE" class="brand-logo-img">
      </a>

      <!-- Search Bar with Camera & Voice Search -->
      <form action="<?= url('catalog') ?>" method="GET" class="search-bar-wrapper">
        <input type="text" name="q" id="headerSearchInput" class="search-input"
          placeholder="Search 50,000+ wholesale items by name, SKU, or keyword..."
          value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">

        <!-- Voice Search Microphone Icon Button + Tooltip -->
        <button type="button" class="voice-search-trigger" id="voiceSearchBtn" onclick="startVoiceSearch()"
          aria-label="Search by Voice" title="Search by Voice (Click to Speak)"
          style="background:none !important; border:none !important; outline:none !important; box-shadow:none !important; padding:4px; display:inline-flex; align-items:center; justify-content:center; cursor:pointer;">
          <svg style="width:19px; height:19px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
              d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
          </svg>
          <div class="camera-tooltip">Search by Voice</div>
        </button>

        <!-- Camera Search Icon Button + Tooltip -->
        <button type="button" class="camera-search-trigger" onclick="triggerVisualSearchModal()"
          aria-label="Search by Image">
          <svg style="width:20px; height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
              d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
            <circle cx="12" cy="13" r="3" />
          </svg>
          <div class="camera-tooltip">Search by Image</div>
        </button>

        <button type="submit" class="search-submit-btn">
          <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </button>
      </form>

      <!-- RFQ Get a Custom Quote Button -->
      <button type="button" id="rfqOpenBtn" onclick="openRfqModal()"
        style="display:inline-flex; align-items:center; gap:7px; background:var(--primary-color,#f05a29); color:#fff; font-family:var(--font-sans); font-size:13px; font-weight:700; padding:9px 18px; border:none; border-radius:8px; cursor:pointer; white-space:nowrap; transition:background .2s,transform .15s;"
        onmouseover="this.style.background='#d8481b'; this.style.transform='translateY(-1px)'"
        onmouseout="this.style.background='#f05a29'; this.style.transform='translateY(0)'">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
        Get a Custom Quote
      </button>

      <div class="header-actions">
        <!-- Ship to / Language / Currency Popover Wrapper -->
        <div class="ship-to-popover-wrapper">
          <button type="button" class="ship-to-trigger-btn" onclick="toggleShipToPopover(event)" id="shipToTriggerBtn">
            <span id="triggerLangText">EN</span> - <span id="triggerCurrText">USD</span>
            <svg class="dropdown-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
              style="margin-left:3px; display:inline-block; vertical-align:middle;">
              <path d="M6 9l6 6 6-6" />
            </svg>
          </button>

          <!-- Popover Dropdown Menu -->
          <div class="ship-to-popover-menu" id="shipToPopoverMenu">
            <div class="popover-field-group">
              <label class="popover-label">Ship to:</label>
              <select id="popoverCountrySelect" class="popover-select" onchange="onShipToCountryChange(this.value)">
                <option value="US">🇺🇸 United States</option>
                <option value="IN">🇮🇳 India</option>
                <option value="GB">🇬🇧 United Kingdom</option>
                <option value="EU">🇪🇺 European Union</option>
                <option value="CA">🇨🇦 Canada</option>
                <option value="AU">🇦🇺 Australia</option>
              </select>
            </div>

            <div class="popover-field-group">
              <label class="popover-label">Language:</label>
              <select id="popoverLanguageSelect" class="popover-select" onchange="onLanguageChange(this.value)">
                <option value="EN">English</option>
                <option value="HI">Hindi</option>
                <option value="ES">Spanish</option>
                <option value="FR">French</option>
                <option value="DE">German</option>
              </select>
            </div>

            <div class="popover-field-group">
              <label class="popover-label">Currency:</label>
              <select id="popoverCurrencySelect" class="popover-select">
                <option value="USD">USD ($)</option>
                <option value="INR">INR (₹)</option>
                <option value="GBP">GBP (£)</option>
                <option value="EUR">EUR (€)</option>
                <option value="CAD">CAD ($)</option>
                <option value="AUD">AUD ($)</option>
              </select>
            </div>

            <button type="button" class="btn-save-popover" onclick="saveShipToPreference()">Save</button>
          </div>
        </div>

        <a href="<?= url('account') ?>" class="header-icon-item" title="Account">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg>
        </a>
        <a href="<?= url('wishlist') ?>" class="header-icon-item" title="Wishlist">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
          </svg>
          <div class="cart-pill-count" id="headerWishlistCount" style="background:#f05a29; display:<?= $initialWishlistCount > 0 ? 'flex' : 'none' ?>;"><?= $initialWishlistCount ?></div>
        </a>
        <button type="button" onclick="openCartDrawer()" class="header-icon-item cursor-pointer" title="Cart" style="background:transparent; border:none; outline:none;">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
          </svg>
          <div class="cart-pill-count" id="headerCartCount" style="background:#f05a29; display:none;">0</div>
        </button>
        <a href="<?= url('inquiry') ?>" class="header-icon-item" title="My Inquiry">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <div class="cart-pill-count" id="headerInquiryCount" style="background:#f05a29; display:none;">0</div>
        </a>
      </div>
    </div>

    <!-- Category Sub-Nav Bar (Fully Database Driven) -->
    <?php
    $navModel = new \App\Models\NavLink();
    $dynamicNavTree = $navModel->getTree(true); // Fetch active links (is_active = 1) ordered by sort_order ASC
    $currentUri = trim($_SERVER['REQUEST_URI'] ?? '', '/');
    ?>
    <nav class="nav-categories-bar">
      <div class="nav-categories-inner">
        <?php foreach ($dynamicNavTree as $navItem): ?>
          <?php
          $cleanPath = ltrim($navItem['url'], '/');
          $targetUrl = url($cleanPath);
          $hasChildren = !empty($navItem['children']) || $navItem['type'] === 'dropdown';
          $targetAttr = !empty($navItem['open_in_new_tab']) ? 'target="_blank" rel="noopener"' : '';
          
          // Determine active tab class
          $isActiveClass = '';
          if (($navItem['url'] === '/' || $navItem['url'] === '') && ($currentUri === '' || str_contains($currentUri, 'importwala/index.php'))) {
              $isActiveClass = 'active';
          } elseif (!empty($cleanPath) && str_contains($currentUri, $cleanPath)) {
              $isActiveClass = 'active';
          }
          ?>

          <div class="nav-item-dropdown-wrapper <?= $hasChildren ? 'has-dropdown' : '' ?>">
            <a href="<?= $targetUrl ?>" class="nav-category-link <?= $isActiveClass ?>" <?= $targetAttr ?>>
              <?= htmlspecialchars($navItem['label']) ?>
              <?php if ($hasChildren): ?>
                <svg class="dropdown-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                  stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
                  style="margin-left:3px; display:inline-block; vertical-align:middle;">
                  <path d="M6 9l6 6 6-6" />
                </svg>
              <?php endif; ?>
            </a>

            <?php if (!empty($navItem['children'])): ?>
              <div class="nav-sub-dropdown-menu">
                <?php foreach ($navItem['children'] as $childItem): ?>
                  <?php
                  $childCleanPath = ltrim($childItem['url'], '/');
                  $childUrl = url($childCleanPath);
                  $childTargetAttr = !empty($childItem['open_in_new_tab']) ? 'target="_blank" rel="noopener"' : '';
                  ?>
                  <a href="<?= $childUrl ?>" class="nav-sub-dropdown-item" <?= $childTargetAttr ?>>
                    <?= htmlspecialchars($childItem['label']) ?>
                  </a>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </nav>
  </header>

  <!-- Page Dynamic Content -->
  <main class="main-container">
    <?= $content ?>
  </main>

  <!-- Image Search Modal Popup -->
  <div class="image-search-overlay" id="imageSearchModalOverlay"
    onclick="if(event.target === this) closeImageSearchModal()">
    <div class="image-search-modal">
      <button type="button" class="image-search-modal-close" onclick="closeImageSearchModal()"
        aria-label="Close Modal">✕</button>
      <h2 class="image-search-title">See something you love? Search by image!</h2>

      <div class="image-search-dropzone" id="dropzoneArea"
        onclick="document.getElementById('imageSearchFileInput').click()">
        <svg class="dropzone-upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
        </svg>
        <p class="dropzone-text">Paste an image, drag & drop, or upload an image to find similar products.</p>
        <button type="button" class="btn-upload-image">Upload image</button>
        <input type="file" id="imageSearchFileInput" accept="image/*" style="display:none;"
          onchange="handleFileSelect(this)">
        <img id="imageSearchPreview" class="image-search-preview" alt="Preview">
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="footer-container">
    <div class="footer-inner">
      <div class="footer-col">
        <h4>ImportWale Wholesale</h4>
        <p style="font-size:13px; color:#9ca3af; margin-bottom:10px;">World-Scale B2B Wholesale Platform connecting international buyers directly with global manufacturers.</p>
        <p style="font-size:13px; color:#9ca3af; margin-bottom:4px;">✉ Email: <a href="mailto:support@importwale.com" style="color:#f05a29; text-decoration:none;">support@importwale.com</a></p>
        <p style="font-size:13px; color:#9ca3af;">📞 Phone/WhatsApp: <a href="https://wa.me/919217714452" style="color:#10b981; text-decoration:none;">+91 92177 14452</a></p>
      </div>
      <div class="footer-col">
        <h4>Support & Help</h4>
        <ul>
          <li><a href="<?= url('support') ?>">Help Center & FAQs</a></li>
          <li><a href="<?= url('contact-us') ?>">Contact Support</a></li>
          <li><a href="<?= url('shipping-policy') ?>">Shipping & Air Freight Policy</a></li>
          <li><a href="<?= url('refund-policy') ?>">Refund & Replacement Policy</a></li>
          <li><a href="<?= url('cancellation-policy') ?>">Order Cancellation Policy</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Company & Legal</h4>
        <ul>
          <li><a href="<?= url('') ?>">Home</a></li>
          <li><a href="<?= url('catalog') ?>">All Wholesale Products</a></li>
          <li><a href="<?= url('about-us') ?>">About ImportWale</a></li>
          <li><a href="<?= url('terms-and-conditions') ?>">Terms & Conditions</a></li>
          <li><a href="<?= url('privacy-policy') ?>">Privacy Policy</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4>Top Categories</h4>
        <ul>
          <li><a href="<?= url('catalog?category=jewelry') ?>">Jewelry & Accessories</a></li>
          <li><a href="<?= url('catalog?category=hats') ?>">Hats & Headwear</a></li>
          <li><a href="<?= url('catalog?category=stationery') ?>">Stationery & Office</a></li>
          <li><a href="<?= url('catalog?category=socks') ?>">Socks & Apparel</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom">
      &copy; <?= date('Y') ?> ImportWale Wholesale Inc. All rights reserved. High-Scale B2B Architecture.
    </div>
  </footer>

  <!-- Floating Need Help Widget -->
  <a href="<?= url('support') ?>" class="floating-need-help-btn" id="needHelpBtn">
    Need Help?
  </a>

  <script>
    // Country to Currency Mapping
    const countryToCurrencyMap = {
      'US': 'USD',
      'IN': 'INR',
      'GB': 'GBP',
      'EU': 'EUR',
      'CA': 'CAD',
      'AU': 'AUD'
    };

    // Auto-update Currency dropdown when "Ship to" Country changes!
    function onShipToCountryChange(countryCode) {
      const targetCurrency = countryToCurrencyMap[countryCode] || 'USD';
      const currencySelect = document.getElementById('popoverCurrencySelect');
      if (currencySelect) {
        currencySelect.value = targetCurrency;
      }
    }

    function toggleShipToPopover(e) {
      e.stopPropagation();
      const menu = document.getElementById('shipToPopoverMenu');
      menu.classList.toggle('active');
    }

    document.addEventListener('click', function (e) {
      const wrapper = document.querySelector('.ship-to-popover-wrapper');
      if (wrapper && !wrapper.contains(e.target)) {
        const menu = document.getElementById('shipToPopoverMenu');
        if (menu) menu.classList.remove('active');
      }
    });

    async function saveShipToPreference() {
      const country = document.getElementById('popoverCountrySelect').value;
      const language = document.getElementById('popoverLanguageSelect').value;
      const currency = document.getElementById('popoverCurrencySelect').value;

      document.getElementById('triggerLangText').innerText = language;
      document.getElementById('triggerCurrText').innerText = currency;

      try {
        await fetch('<?= url('api/currency/set') ?>', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ country, language, currency })
        });
      } catch (e) { }

      document.getElementById('shipToPopoverMenu').classList.remove('active');
    }

    // Global Cart Pill Updater
    async function updateHeaderCartCount() {
      try {
        const res = await fetch('<?= url('api/cart') ?>');
        const data = await res.json();
        if (data.success && data.cart) {
          document.getElementById('headerCartCount').innerText = data.cart.items_count || 1;
        }
      } catch (e) { }
    }
    updateHeaderCartCount();

    // Image Search Modal Functions
    function openImageSearchModal() {
      document.getElementById('imageSearchModalOverlay').classList.add('active');
    }

    function closeImageSearchModal() {
      document.getElementById('imageSearchModalOverlay').classList.remove('active');
    }

    function handleFileSelect(input) {
      if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
          const preview = document.getElementById('imageSearchPreview');
          preview.src = e.target.result;
          preview.style.display = 'block';

          setTimeout(() => {
            closeImageSearchModal();
            window.location.href = '<?= url('catalog?q=spooky') ?>';
          }, 800);
        };
        reader.readAsDataURL(input.files[0]);
      }
    }

    // Drag and drop & paste event listeners
    const dropzone = document.getElementById('dropzoneArea');
    if (dropzone) {
      ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => { e.preventDefault(); dropzone.classList.add('dragover'); }, false);
      });
      ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => { e.preventDefault(); dropzone.classList.remove('dragover'); }, false);
      });
      dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length) {
          document.getElementById('imageSearchFileInput').files = files;
          handleFileSelect(document.getElementById('imageSearchFileInput'));
        }
      });
    }

    document.addEventListener('paste', (e) => {
      if (document.getElementById('imageSearchModalOverlay').classList.contains('active')) {
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (let index in items) {
          const item = items[index];
          if (item.kind === 'file') {
            const blob = item.getAsFile();
            const reader = new FileReader();
            reader.onload = function (evt) {
              const preview = document.getElementById('imageSearchPreview');
              preview.src = evt.target.result;
              preview.style.display = 'block';
              setTimeout(() => {
                closeImageSearchModal();
                window.location.href = '<?= url('catalog?q=spooky') ?>';
              }, 800);
            };
            reader.readAsDataURL(blob);
          }
        }
      }
    });

    // Voice Search Microphone Speech Recognition Handler
    function startVoiceSearch() {
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      const btn = document.getElementById('voiceSearchBtn');
      const input = document.getElementById('headerSearchInput') || document.querySelector('.search-input');
      const form = input ? input.closest('form') : null;

      if (!SpeechRecognition) {
        alert("Voice search is not supported on this browser. Please use Google Chrome, Microsoft Edge, or Safari.");
        return;
      }

      const recognition = new SpeechRecognition();
      recognition.lang = 'en-IN';
      recognition.interimResults = true;
      recognition.maxAlternatives = 1;

      if (btn) {
        btn.classList.add('mic-listening');
        btn.setAttribute('title', 'Listening... Speak your search query now');
      }

      if (input) {
        input.placeholder = "🎙️ Listening... Speak now...";
        input.focus();
      }

      recognition.onresult = function (event) {
        let transcript = '';
        for (let i = event.resultIndex; i < event.results.length; i++) {
          transcript += event.results[i][0].transcript;
        }
        if (input) {
          input.value = transcript;
        }
      };

      recognition.onspeechend = function () {
        recognition.stop();
        if (btn) {
          btn.classList.remove('mic-listening');
          btn.setAttribute('title', 'Search by Voice (Click to Speak)');
        }
        if (input) {
          input.placeholder = "Search 50,000+ wholesale items by name, SKU, or keyword...";
        }
        if (input && input.value.trim() !== '') {
          setTimeout(function () {
            if (form) form.submit();
          }, 400);
        }
      };

      recognition.onerror = function (event) {
        recognition.stop();
        if (btn) {
          btn.classList.remove('mic-listening');
          btn.setAttribute('title', 'Search by Voice (Click to Speak)');
        }
        if (input) {
          input.placeholder = "Search 50,000+ wholesale items by name, SKU, or keyword...";
        }
        if (event.error === 'no-speech') {
          alert("No speech was detected. Please click the microphone icon again.");
        } else if (event.error === 'not-allowed') {
          alert("Microphone permission was denied. Please allow microphone access in your browser settings.");
        }
      };

      try {
        recognition.start();
      } catch (err) {
        console.error("Speech recognition start error:", err);
      }
    }

    // Interactive Toggle Inquiry List Handler (Add & Remove with Animation)
    window.toggleCardInquiry = async function(productId, moq, btn) {
      if (!productId || !btn) return;

      // Trigger pop animation
      btn.classList.remove('ef-btn-pop');
      void btn.offsetWidth; // trigger reflow
      btn.classList.add('ef-btn-pop');

      try {
        const res = await fetch('<?= url("api/inquiry/toggle") ?>', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ product_id: productId, quantity: moq || 1 })
        });
        const data = await res.json();

        if (data.success) {
          if (data.status === 'added') {
            btn.classList.add('active');
            btn.setAttribute('title', 'In Inquiry (Click to remove)');
            btn.innerHTML = '<svg class="ef-inquiry-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
          } else if (data.status === 'removed') {
            btn.classList.remove('active');
            btn.setAttribute('title', 'Add to Inquiry');
            btn.innerHTML = '<svg class="ef-inquiry-icon" width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>';
          }
          if (typeof updateHeaderInquiryCount === 'function') {
            updateHeaderInquiryCount(data.total_products);
          }
        } else {
          alert(data.message || 'Could not update inquiry.');
        }
      } catch(e) {
        console.error("Inquiry toggle error:", e);
      }
    };

    window.quickAddToInquiry = function(productId, moq, btn) {
      return toggleCardInquiry(productId, moq, btn);
    };

    window.updateHeaderInquiryCount = async function(overrideCount) {
      const badge = document.getElementById('headerInquiryCount');
      if (!badge) return;
      if (typeof overrideCount === 'number') {
        badge.innerText = overrideCount;
        badge.style.display = overrideCount > 0 ? 'flex' : 'none';
        return;
      }
      try {
        const res = await fetch('<?= url("api/inquiry") ?>');
        const data = await res.json();
        const cnt = data.total_products || 0;
        badge.innerText = cnt;
        badge.style.display = cnt > 0 ? 'flex' : 'none';
      } catch(e){}
    };
    document.addEventListener('DOMContentLoaded', () => updateHeaderInquiryCount());

    window.quickAddToCart = async function(productId, moq, btn) {
      if (!productId) return;
      const originalHtml = btn.innerHTML;
      btn.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2" stroke-dasharray="32" stroke-dashoffset="10"/></svg>';
      try {
        const res = await fetch('<?= url("api/cart/add") ?>', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ product_id: productId, quantity: moq || 1 })
        });
        const data = await res.json();
        if (data.success) {
          btn.style.background = '#10b981';
          btn.style.color = '#ffffff';
          btn.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
          if (typeof updateHeaderCartCount === 'function') updateHeaderCartCount();
          setTimeout(() => {
            btn.style.background = '';
            btn.style.color = '';
            btn.innerHTML = originalHtml;
          }, 1500);
        } else {
          alert(data.message || 'Could not add product to cart.');
          btn.innerHTML = originalHtml;
        }
      } catch(e) {
        btn.innerHTML = originalHtml;
      }
    };

    window.toggleCardWishlist = async function(productId, btn) {
      if (!productId) return;
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
      try {
        const res = await fetch('<?= url("wishlist/toggle") ?>', {
          method: 'POST',
          headers: { 
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: new URLSearchParams({
            product_id: productId,
            _csrf_token: csrfToken
          })
        });
        const data = await res.json();
        
        if (data.status === 'limit_reached') {
          alert(data.message || 'You can add a maximum of 100 products to your wishlist.');
          return;
        }

        if (data.status === 'added') {
          btn.classList.add('active');
          btn.style.transform = 'scale(1.25)';
          setTimeout(() => btn.style.transform = '', 200);
        } else if (data.status === 'removed') {
          btn.classList.remove('active');
        }

        if (typeof updateHeaderWishlistCount === 'function') {
          updateHeaderWishlistCount(data.count);
        }
      } catch(e) {
        btn.classList.toggle('active');
      }
    };

    window.updateHeaderWishlistCount = function(count) {
      const badge = document.getElementById('headerWishlistCount');
      if (!badge) return;
      badge.innerText = count || 0;
      badge.style.display = (count && count > 0) ? 'flex' : 'none';
    };
  </script>
  <!-- Global Gallery Lightbox Modal -->
  <?php require __DIR__ . '/partials/gallery_modal.php'; ?>
  <!-- Global Visual Search Modal -->
  <?php require __DIR__ . '/partials/visual_search_modal.php'; ?>

  <!-- ============================================================
       RFQ Modal — Importwala Unique Design
       ============================================================ -->
  <style>
    /* ---- Overlay ---- */
    #rfqModalOverlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(15,23,42,0.6);
      backdrop-filter: blur(6px);
      z-index: 99999;
      align-items: center;
      justify-content: center;
      padding: 12px;
      overflow-y: auto;
    }
    #rfqModalOverlay.rfq-open { display: flex; }

    /* ---- Dialog ---- */
    #rfqModal {
      background: #fff;
      border-radius: 20px;
      width: 100%;
      max-width: 680px;
      max-height: 92vh;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      animation: rfqPop .25s cubic-bezier(.34,1.56,.64,1);
    }
    @keyframes rfqPop {
      from { opacity:0; transform: scale(.92); }
      to   { opacity:1; transform: scale(1); }
    }

    /* ---- Header ---- */
    .rfq-hd {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 18px 22px 16px;
      border-bottom: 1px solid #f1f5f9;
      flex-shrink: 0;
    }
    .rfq-hd-badge {
      background: linear-gradient(135deg,#f05a29,#ff8c5a);
      color: #fff;
      font-size: 10px;
      font-weight: 800;
      letter-spacing: .8px;
      text-transform: uppercase;
      padding: 4px 10px;
      border-radius: 20px;
    }
    .rfq-hd-title {
      font-size: 17px;
      font-weight: 800;
      color: #0f172a;
      flex: 1;
      line-height: 1.2;
    }
    .rfq-hd-sub {
      font-size: 11px;
      color: #94a3b8;
      font-weight: 500;
      margin-top: 2px;
    }
    .rfq-hd-close {
      background: #f1f5f9;
      border: none;
      color: #64748b;
      width: 34px; height: 34px;
      border-radius: 50%;
      font-size: 16px;
      cursor: pointer;
      display: flex; align-items:center; justify-content:center;
      transition: background .15s, color .15s;
      flex-shrink: 0;
    }
    .rfq-hd-close:hover { background: #fee2e2; color: #ef4444; }

    /* ---- Progress Bar Stepper ---- */
    .rfq-progress-wrap {
      padding: 14px 22px 0;
      flex-shrink: 0;
    }
    .rfq-progress-labels {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;
    }
    .rfq-progress-label {
      font-size: 11px;
      font-weight: 700;
      color: #cbd5e1;
      display: flex;
      align-items: center;
      gap: 5px;
      transition: color .3s;
    }
    .rfq-progress-label.active { color: #f05a29; }
    .rfq-progress-label.done   { color: #10b981; }
    .rfq-progress-num {
      width: 18px; height: 18px;
      border-radius: 50%;
      background: #e2e8f0;
      color: #94a3b8;
      font-size: 10px;
      font-weight: 800;
      display: inline-flex; align-items:center; justify-content:center;
      transition: all .3s;
    }
    .rfq-progress-label.active .rfq-progress-num { background: #f05a29; color: #fff; }
    .rfq-progress-label.done   .rfq-progress-num { background: #10b981; color: #fff; }
    .rfq-progress-bar-track {
      height: 5px;
      background: #f1f5f9;
      border-radius: 10px;
      overflow: hidden;
      margin-bottom: 16px;
    }
    .rfq-progress-bar-fill {
      height: 100%;
      background: linear-gradient(90deg, #f05a29, #ff8c5a);
      border-radius: 10px;
      transition: width .4s cubic-bezier(.4,0,.2,1);
    }
    /* Mobile: hide labels, keep bar */
    @media (max-width:500px) {
      .rfq-progress-labels { display:none; }
      .rfq-progress-bar-track { margin-bottom: 8px; }
    }
    .rfq-mobile-step {
      display: none;
      font-size: 12px;
      font-weight: 700;
      color: #f05a29;
      padding: 6px 22px 10px;
    }
    @media (max-width:500px) { .rfq-mobile-step { display:block; } }

    /* ---- Form Wrapper ---- */
    #rfqForm {
      display: flex;
      flex-direction: column;
      flex: 1;
      min-height: 0;
      overflow: hidden;
    }

    /* ---- Scrollable Body ---- */
    .rfq-body {
      padding: 6px 22px 20px;
      overflow-y: auto;
      flex: 1;
      min-height: 0;
      -webkit-overflow-scrolling: touch;
    }
    .rfq-body::-webkit-scrollbar {
      width: 6px;
    }
    .rfq-body::-webkit-scrollbar-track {
      background: #f1f5f9;
      border-radius: 10px;
    }
    .rfq-body::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 10px;
    }
    .rfq-body::-webkit-scrollbar-thumb:hover {
      background: #f05a29;
    }

    /* ---- Section heading ---- */
    .rfq-section-title {
      font-size: 13px;
      font-weight: 800;
      color: #334155;
      padding: 12px 0 10px;
      display: flex;
      align-items: center;
      gap: 8px;
      border-bottom: 1.5px dashed #e2e8f0;
      margin-bottom: 16px;
    }
    .rfq-section-icon {
      width: 28px; height: 28px;
      background: #fff7ed;
      border-radius: 8px;
      display: flex; align-items:center; justify-content:center;
      color: #f05a29;
      flex-shrink: 0;
    }

    /* ---- Fields ---- */
    .rfq-field { margin-bottom: 14px; }
    .rfq-label {
      display: block;
      font-size: 12px;
      font-weight: 700;
      color: #475569;
      margin-bottom: 5px;
      letter-spacing: .2px;
    }
    .rfq-req { color: #f05a29; }
    .rfq-opt { color: #94a3b8; font-weight: 500; font-size: 11px; }
    .rfq-input, .rfq-select, .rfq-textarea {
      width: 100%;
      padding: 10px 13px;
      border: 1.5px solid #e2e8f0;
      border-radius: 10px;
      font-size: 13px;
      font-family: inherit;
      color: #1e293b;
      background: #f8fafc;
      outline: none;
      transition: border-color .2s, background .2s;
      box-sizing: border-box;
    }
    .rfq-input:focus, .rfq-select:focus, .rfq-textarea:focus {
      border-color: #f05a29;
      background: #fff;
      box-shadow: 0 0 0 3px rgba(240,90,41,.08);
    }
    .rfq-input.rfq-err, .rfq-select.rfq-err, .rfq-textarea.rfq-err {
      border-color: #ef4444;
      background: #fff5f5;
    }
    .rfq-errmsg { font-size:11px; color:#ef4444; margin-top:4px; display:none; }
    .rfq-textarea { resize:vertical; min-height:76px; }

    .rfq-g2 { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
    .rfq-g3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:12px; }
    @media (max-width:480px) { .rfq-g2,.rfq-g3 { grid-template-columns:1fr; } }

    /* Phone + Price prefix inputs */
    .rfq-prefix-wrap {
      display: flex;
      border: 1.5px solid #e2e8f0;
      border-radius: 10px;
      overflow: hidden;
      background: #f8fafc;
      transition: border-color .2s;
    }
    .rfq-prefix-wrap:focus-within { border-color: #f05a29; background: #fff; box-shadow: 0 0 0 3px rgba(240,90,41,.08); }
    .rfq-prefix-wrap.rfq-err { border-color: #ef4444; }
    .rfq-prefix-tag {
      padding: 10px 12px;
      background: #f1f5f9;
      font-size: 13px;
      font-weight: 700;
      color: #475569;
      border-right: 1.5px solid #e2e8f0;
      white-space: nowrap;
      flex-shrink: 0;
    }
    .rfq-prefix-input {
      flex: 1;
      border: none !important;
      outline: none !important;
      box-shadow: none !important;
      background: transparent !important;
      padding: 10px 12px;
      font-size: 13px;
      font-family: inherit;
      color: #1e293b;
    }

    /* Dropzone */
    .rfq-dz {
      border: 2px dashed #e2e8f0;
      border-radius: 12px;
      padding: 18px;
      text-align: center;
      cursor: pointer;
      background: #f8fafc;
      transition: all .2s;
    }
    .rfq-dz:hover, .rfq-dz.rfq-dz-over {
      border-color: #f05a29;
      background: #fff8f5;
    }
    .rfq-dz-icon { color: #f05a29; margin-bottom: 6px; }
    .rfq-dz-txt  { font-size: 13px; color: #64748b; }
    .rfq-dz-txt b { color: #f05a29; }
    .rfq-dz-hint { font-size: 11px; color: #94a3b8; margin-top: 3px; }
    #rfqFileInput { display:none; }
    .rfq-thumbs { display:flex; flex-wrap:wrap; gap:8px; margin-top:10px; }
    .rfq-thumb {
      position:relative; width:68px; height:68px;
      border-radius:10px; overflow:hidden;
      border: 2px solid #e2e8f0;
    }
    .rfq-thumb img { width:100%; height:100%; object-fit:cover; }
    .rfq-thumb-del {
      position:absolute; top:2px; right:2px;
      background:rgba(0,0,0,.55); color:#fff;
      border:none; width:17px; height:17px; border-radius:50%;
      font-size:10px; cursor:pointer;
      display:flex; align-items:center; justify-content:center;
    }

    /* GST toggle */
    .rfq-toggle-row { display:flex; gap:10px; }
    .rfq-toggle-btn {
      flex:1; padding:9px 10px;
      border: 1.5px solid #e2e8f0;
      border-radius:10px;
      cursor:pointer;
      font-size:12px; font-weight:700;
      color:#64748b;
      display:flex; align-items:center; gap:6px; justify-content:center;
      transition: all .15s;
      background: #f8fafc;
    }
    .rfq-toggle-btn:has(input:checked) {
      border-color: #f05a29;
      background: #fff8f5;
      color: #f05a29;
    }
    .rfq-toggle-btn input { accent-color: #f05a29; }

    /* ---- Footer ---- */
    .rfq-ft {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 10px;
      padding: 14px 22px;
      border-top: 1px solid #f1f5f9;
      background: #f8fafc;
      flex-shrink: 0;
      border-radius: 0 0 20px 20px;
    }
    .rfq-btn {
      display: inline-flex; align-items:center; gap:7px;
      padding: 10px 22px;
      border-radius: 10px;
      font-size: 13px; font-weight: 700; font-family:inherit;
      border: none; cursor: pointer;
      transition: background .18s, transform .12s;
    }
    .rfq-btn:disabled { opacity:.55; cursor:not-allowed; }
    .rfq-btn-primary  { background:#f05a29; color:#fff; }
    .rfq-btn-primary:hover:not(:disabled)  { background:#d8481b; }
    .rfq-btn-ghost    { background:#fff; color:#64748b; border:1.5px solid #e2e8f0; }
    .rfq-btn-ghost:hover { background:#f1f5f9; }

    /* ---- Success ---- */
    .rfq-success-wrap {
      display:none;
      padding: 44px 22px;
      text-align:center;
    }
    .rfq-success-ring {
      width:80px; height:80px; border-radius:50%;
      border: 3px solid #10b981;
      display:flex; align-items:center; justify-content:center;
      margin: 0 auto 20px;
      animation: rfqRing .5s ease forwards;
    }
    @keyframes rfqRing {
      0%   { transform:scale(0); opacity:0; }
      70%  { transform:scale(1.1); }
      100% { transform:scale(1); opacity:1; }
    }
    .rfq-success-h { font-size:19px; font-weight:800; color:#0f172a; margin-bottom:8px; }
    .rfq-success-p { font-size:13px; color:#64748b; line-height:1.7; }
    .rfq-success-tag {
      display:inline-block; margin-top:16px;
      background:#fff7ed; color:#f05a29;
      font-size:12px; font-weight:700;
      padding:6px 16px; border-radius:20px;
      border:1.5px solid #fed7aa;
    }
  </style>

  <!-- RFQ Modal Markup -->
  <div id="rfqModalOverlay" onclick="if(event.target===this)closeRfqModal()" role="dialog" aria-modal="true">
    <div id="rfqModal">

      <!-- Header -->
      <div class="rfq-hd">
        <div style="flex:1;">
          <div class="rfq-hd-title">Custom Sourcing Request</div>
          <div class="rfq-hd-sub">Fill 3 quick steps — our team will get back within 24hrs</div>
        </div>
        <button class="rfq-hd-close" onclick="closeRfqModal()" aria-label="Close">&#x2715;</button>
      </div>

      <!-- Progress bar stepper -->
      <div class="rfq-progress-wrap" id="rfqProgressWrap">
        <div class="rfq-progress-labels">
          <span class="rfq-progress-label active" id="rfqLbl1">
            <span class="rfq-progress-num" id="rfqNum1">1</span> Product Info
          </span>
          <span class="rfq-progress-label" id="rfqLbl2">
            <span class="rfq-progress-num" id="rfqNum2">2</span> Your Details
          </span>
          <span class="rfq-progress-label" id="rfqLbl3">
            <span class="rfq-progress-num" id="rfqNum3">3</span> Business
          </span>
        </div>
        <div class="rfq-progress-bar-track">
          <div class="rfq-progress-bar-fill" id="rfqBarFill" style="width:33%;"></div>
        </div>
      </div>
      <div class="rfq-mobile-step" id="rfqMobileLbl">Step 1 of 3 — Product Info</div>

      <!-- Scrollable form body -->
      <form id="rfqForm" novalidate>
        <div class="rfq-body" id="rfqBodyScroll">

          <!-- ===== STEP 1 ===== -->
          <div id="rfqStep1">
            <div class="rfq-section-title">
              <div class="rfq-section-icon">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/></svg>
              </div>
              What product are you looking for?
            </div>

            <div class="rfq-field">
              <label class="rfq-label">Product Name <span class="rfq-req">*</span></label>
              <input class="rfq-input" id="rfq_product_name" name="product_name" type="text"
                placeholder="e.g. Bluetooth Earbuds, Stainless Steel Bottles, LED Strip Lights…">
              <div class="rfq-errmsg" id="err_product_name"></div>
            </div>

            <div class="rfq-field">
              <label class="rfq-label">Reference Link <span class="rfq-opt">(Optional)</span></label>
              <input class="rfq-input" id="rfq_ref_link" name="product_reference_link" type="url"
                placeholder="Amazon, AliExpress, Instagram, or any product URL">
            </div>

            <div class="rfq-field">
              <label class="rfq-label">Reference Photos <span class="rfq-opt">(Optional — max 4)</span></label>
              <div class="rfq-dz" id="rfqDz"
                   onclick="document.getElementById('rfqFileInput').click()"
                   ondragover="rfqDragOver(event)" ondragleave="rfqDragLeave(event)" ondrop="rfqDrop(event)">
                <div class="rfq-dz-icon">
                  <svg width="28" height="28" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </div>
                <div class="rfq-dz-txt"><b>Click to browse</b> or drop images here</div>
                <div class="rfq-dz-hint">PNG · JPG · WEBP &nbsp;|&nbsp; Max 5 MB each</div>
              </div>
              <input type="file" id="rfqFileInput" name="reference_photos[]" accept="image/png,image/jpeg,image/webp" multiple>
              <div class="rfq-thumbs" id="rfqThumbs"></div>
              <div class="rfq-errmsg" id="err_photos"></div>
            </div>

            <div class="rfq-g2">
              <div class="rfq-field" style="margin-bottom:0">
                <label class="rfq-label">Quantity <span class="rfq-req">*</span></label>
                <input class="rfq-input" id="rfq_quantity" name="quantity" type="number" min="1" placeholder="e.g. 500">
                <div class="rfq-errmsg" id="err_quantity"></div>
              </div>
              <div class="rfq-field" style="margin-bottom:0">
                <label class="rfq-label">Unit <span class="rfq-req">*</span></label>
                <select class="rfq-select" id="rfq_unit" name="unit">
                  <option value="">Select Unit</option>
                  <option>Pcs</option><option>Dozen</option><option>Box</option>
                  <option>Carton</option><option>Kg</option><option>Set</option>
                  <option>Meter</option><option>Litre</option>
                </select>
                <div class="rfq-errmsg" id="err_unit"></div>
              </div>
            </div>

            <div class="rfq-g3" style="margin-top:14px;">
              <div class="rfq-field" style="margin-bottom:0">
                <label class="rfq-label">Target Price / Unit <span class="rfq-req">*</span></label>
                <div class="rfq-prefix-wrap" id="rfqPriceW">
                  <div class="rfq-prefix-tag">&#8377;</div>
                  <input class="rfq-prefix-input" id="rfq_target_price" name="target_price" type="number" min="0" step="0.01" placeholder="e.g. 450">
                </div>
                <div class="rfq-errmsg" id="err_target_price"></div>
              </div>
              <div class="rfq-field" style="margin-bottom:0">
                <label class="rfq-label">Total Budget <span class="rfq-req">*</span></label>
                <select class="rfq-select" id="rfq_budget" name="overall_budget">
                  <option value="">Select Range</option>
                  <option>Under &#8377;50,000</option>
                  <option>&#8377;50K – 2 Lakh</option>
                  <option>&#8377;2 – 10 Lakh</option>
                  <option>&#8377;10 – 50 Lakh</option>
                  <option>Above &#8377;50 Lakh</option>
                </select>
                <div class="rfq-errmsg" id="err_budget"></div>
              </div>
              <div class="rfq-field" style="margin-bottom:0">
                <label class="rfq-label">Purpose <span class="rfq-req">*</span></label>
                <select class="rfq-select" id="rfq_purpose" name="sourcing_purpose">
                  <option value="">Select</option>
                  <option>Resale</option><option>Personal Use</option>
                  <option>Gifting</option><option>Business Bulk Order</option>
                  <option>Export</option><option>Other</option>
                </select>
                <div class="rfq-errmsg" id="err_purpose"></div>
              </div>
            </div>

            <div class="rfq-field" style="margin-top:14px;">
              <label class="rfq-label">Specifications <span class="rfq-opt">(Optional)</span></label>
              <textarea class="rfq-textarea" id="rfq_specs" name="specifications"
                placeholder="Color, material, print, packaging, certifications…"></textarea>
            </div>
          </div>

          <!-- ===== STEP 2 ===== -->
          <div id="rfqStep2" style="display:none;">
            <div class="rfq-section-title">
              <div class="rfq-section-icon">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
              </div>
              How can we reach you?
            </div>

            <div class="rfq-g2">
              <div class="rfq-field">
                <label class="rfq-label">Full Name <span class="rfq-req">*</span></label>
                <input class="rfq-input" id="rfq_full_name" name="full_name" type="text" placeholder="Your name">
                <div class="rfq-errmsg" id="err_full_name"></div>
              </div>
              <div class="rfq-field">
                <label class="rfq-label">WhatsApp Number <span class="rfq-req">*</span></label>
                <div class="rfq-prefix-wrap" id="rfqPhoneW">
                  <div class="rfq-prefix-tag">+91</div>
                  <input class="rfq-prefix-input" id="rfq_phone" name="phone" type="tel" maxlength="10"
                    placeholder="10-digit number" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10)">
                </div>
                <div class="rfq-errmsg" id="err_phone"></div>
              </div>
            </div>
            <div class="rfq-g2">
              <div class="rfq-field">
                <label class="rfq-label">Email Address <span class="rfq-req">*</span></label>
                <input class="rfq-input" id="rfq_email" name="email" type="email" placeholder="you@example.com">
                <div class="rfq-errmsg" id="err_email"></div>
              </div>
              <div class="rfq-field">
                <label class="rfq-label">Pincode <span class="rfq-req">*</span></label>
                <input class="rfq-input" id="rfq_pincode" name="pincode" type="text" maxlength="6"
                  placeholder="6-digit pincode" oninput="this.value=this.value.replace(/\D/g,'').slice(0,6)">
                <div class="rfq-errmsg" id="err_pincode"></div>
              </div>
            </div>
          </div>

          <!-- ===== STEP 3 ===== -->
          <div id="rfqStep3" style="display:none;">
            <div class="rfq-section-title">
              <div class="rfq-section-icon">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
              </div>
              A bit about your business
            </div>

            <div class="rfq-g2">
              <div class="rfq-field">
                <label class="rfq-label">Business Type <span class="rfq-req">*</span></label>
                <select class="rfq-select" id="rfq_biz_type" name="business_type">
                  <option value="">Select Type</option>
                  <option>Reseller</option><option>Retailer</option>
                  <option>Wholesaler</option><option>D2C Brand</option>
                  <option>Startup</option><option>Manufacturer</option>
                  <option>Distributor</option><option>Other</option>
                </select>
                <div class="rfq-errmsg" id="err_biz_type"></div>
              </div>
              <div class="rfq-field">
                <label class="rfq-label">GST Registered? <span class="rfq-req">*</span></label>
                <div class="rfq-toggle-row">
                  <label class="rfq-toggle-btn">
                    <input type="radio" name="has_gst" value="yes">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Yes, I have GST
                  </label>
                  <label class="rfq-toggle-btn">
                    <input type="radio" name="has_gst" value="no">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    No GST yet
                  </label>
                </div>
                <div class="rfq-errmsg" id="err_has_gst"></div>
              </div>
            </div>

            <div class="rfq-field">
              <label class="rfq-label">Additional Instructions <span class="rfq-opt">(Optional)</span></label>
              <textarea class="rfq-textarea" id="rfq_comments" name="additional_comments"
                placeholder="Timeline, branding requirements, any other details…"></textarea>
            </div>
          </div>

          <!-- ===== SUCCESS ===== -->
          <div class="rfq-success-wrap" id="rfqSuccess">
            <div class="rfq-success-ring">
              <svg width="32" height="32" fill="none" stroke="#10b981" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="rfq-success-h">Request Submitted! 🎉</div>
            <div class="rfq-success-p">
              Our sourcing team will review your request and reach out on<br>
              <strong>WhatsApp / Email within 24 hours</strong> with a custom quote.
            </div>
            <div class="rfq-success-tag">📦 Importwala — Sourcing Made Easy</div>
            <br>
            <button type="button" class="rfq-btn rfq-btn-primary" style="margin-top:10px;" onclick="closeRfqModal()">Close Window</button>
          </div>

        </div><!-- /rfq-body -->

        <!-- Footer -->
        <div class="rfq-ft" id="rfqFt">
          <button type="button" class="rfq-btn rfq-btn-ghost" id="rfqBackBtn" style="display:none;" onclick="rfqBack()">
            &#8592; Back
          </button>
          <button type="button" class="rfq-btn rfq-btn-primary" id="rfqNextBtn" onclick="rfqNext()">
            Continue &nbsp;&#8594;
          </button>
          <button type="submit" class="rfq-btn rfq-btn-primary" id="rfqSubmitBtn" style="display:none;">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            Submit Request
          </button>
        </div>

      </form>
    </div><!-- /rfqModal -->
  </div><!-- /rfqModalOverlay -->

  <script>
  // Ensure global modal functions are defined on window immediately
  window.openRfqModal = function () {
    var overlay = document.getElementById('rfqModalOverlay');
    if (!overlay) return;
    overlay.classList.add('rfq-open');
    document.body.style.overflow = 'hidden';
    if (typeof window.rfqResetModal === 'function') window.rfqResetModal();
  };
  window.closeRfqModal = function () {
    var overlay = document.getElementById('rfqModalOverlay');
    if (overlay) overlay.classList.remove('rfq-open');
    document.body.style.overflow = '';
  };
  document.addEventListener('keydown', function(e){ if(e.key==='Escape') closeRfqModal(); });

  (function () {
    var step = 1, rfqFiles = [], MAX_P = 4, MAX_S = 5*1024*1024;
    var mobileLabels = ['Product Info','Your Details','Business'];

    window.rfqResetModal = function() {
      step = 1; rfqFiles = [];
      var form = document.getElementById('rfqForm');
      if (form) form.reset();
      var thumbs = document.getElementById('rfqThumbs');
      if (thumbs) thumbs.innerHTML = '';
      var pw = document.getElementById('rfqProgressWrap'); if (pw) pw.style.display = 'block';
      var ml = document.getElementById('rfqMobileLbl');    if (ml) ml.style.display = '';
      var sc = document.getElementById('rfqSuccess');       if (sc) sc.style.display = 'none';
      renderStep();
    };

    window.rfqNext = function () {
      if (!validateStep(step)) return;
      if (step < 3) { step++; renderStep(); }
    };
    window.rfqBack = function () {
      if (step > 1) { step--; renderStep(); }
    };

    function renderStep() {
      // Panels
      [1,2,3].forEach(function(i){
        var el = document.getElementById('rfqStep'+i);
        if (el) el.style.display = (i===step)?'block':'none';
      });
      var sc = document.getElementById('rfqSuccess'); if (sc) sc.style.display = 'none';

      // Buttons
      var ft = document.getElementById('rfqFt');         if (ft) ft.style.display = 'flex';
      var bb = document.getElementById('rfqBackBtn');   if (bb) bb.style.display = step > 1 ? 'inline-flex' : 'none';
      var nb = document.getElementById('rfqNextBtn');   if (nb) nb.style.display = step < 3 ? 'inline-flex' : 'none';
      var sb = document.getElementById('rfqSubmitBtn'); if (sb) sb.style.display = step === 3 ? 'inline-flex' : 'none';

      // Progress bar
      var pct = { 1:'33%', 2:'66%', 3:'100%' };
      var bf = document.getElementById('rfqBarFill'); if (bf) bf.style.width = pct[step];

      // Labels
      [1,2,3].forEach(function(i){
        var lbl = document.getElementById('rfqLbl'+i);
        var num = document.getElementById('rfqNum'+i);
        if (lbl) {
          lbl.className = 'rfq-progress-label';
          if (i < step)  { lbl.classList.add('done');   if (num) num.innerHTML = '&#10003;'; }
          else if (i === step) { lbl.classList.add('active'); if (num) num.innerHTML = i; }
          else           { if (num) num.innerHTML = i; }
        }
      });

      // Mobile label
      var ml = document.getElementById('rfqMobileLbl');
      if (ml) ml.textContent = 'Step '+step+' of 3 \u2014 '+mobileLabels[step-1];

      // Scroll body to top
      var b = document.getElementById('rfqBodyScroll');
      if (b) b.scrollTop = 0;
    }

    /* ---- Validation ---- */
    function validateStep(s) {
      clearErrors();
      var ok = true;

      if (s===1) {
        var pn = g('rfq_product_name');
        if (!pn || !pn.value.trim())                  { err('product_name','Product name is required.'); ok=false; }
        var qEl = g('rfq_quantity');
        var q = qEl ? parseInt(qEl.value) : 0;
        if (!q||q<1)                                  { err('quantity','Enter a valid quantity.'); ok=false; }
        var uEl = g('rfq_unit');
        if (!uEl || !uEl.value)                       { err('unit','Please select a unit.'); ok=false; }
        var tpEl = g('rfq_target_price');
        var tp = tpEl ? tpEl.value : '';
        if (tp===''||isNaN(parseFloat(tp)))           { err('target_price','Enter target price.'); ww('rfqPriceW'); ok=false; }
        var bEl = g('rfq_budget');
        if (!bEl || !bEl.value)                       { err('budget','Select a budget range.'); ok=false; }
        var purEl = g('rfq_purpose');
        if (!purEl || !purEl.value)                   { err('purpose','Select a purpose.'); ok=false; }
      }
      if (s===2) {
        var fnEl = g('rfq_full_name');
        if (!fnEl || !fnEl.value.trim())              { err('full_name','Full name is required.'); ok=false; }
        var pEl = g('rfq_phone');
        var ph = pEl ? pEl.value.replace(/\D/g,'') : '';
        if (ph.length!==10)                           { err('phone','Enter a valid 10-digit number.'); ww('rfqPhoneW'); ok=false; }
        var eEl = g('rfq_email');
        var em = eEl ? eEl.value.trim() : '';
        if (!em||!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)) { err('email','Enter a valid email.'); ok=false; }
        var pinEl = g('rfq_pincode');
        var pin = pinEl ? pinEl.value.trim() : '';
        if (!/^\d{6}$/.test(pin))                    { err('pincode','Enter a valid 6-digit pincode.'); ok=false; }
      }
      if (s===3) {
        var btEl = g('rfq_biz_type');
        if (!btEl || !btEl.value)                     { err('biz_type','Select your business type.'); ok=false; }
        if (!document.querySelector('input[name="has_gst"]:checked')) { err('has_gst','Please select GST status.'); ok=false; }
      }
      return ok;
    }
    function g(id){ return document.getElementById(id); }
    function err(k,m){
      var el=document.getElementById('err_'+k);
      if(el){ el.textContent=m; el.style.display='block'; }
      var inp=document.getElementById('rfq_'+k);
      if(inp) inp.classList.add('rfq-err');
    }
    function ww(id){ var el=document.getElementById(id); if(el) el.classList.add('rfq-err'); }
    function clearErrors(){
      document.querySelectorAll('.rfq-errmsg').forEach(function(e){ e.style.display='none'; });
      document.querySelectorAll('.rfq-err').forEach(function(e){ e.classList.remove('rfq-err'); });
    }

    /* ---- File upload ---- */
    var fileInput = g('rfqFileInput');
    if (fileInput) {
      fileInput.addEventListener('change', function(){ addFiles(this.files); this.value=''; });
    }
    window.rfqDragOver = function(e){ e.preventDefault(); var dz = g('rfqDz'); if (dz) dz.classList.add('rfq-dz-over'); };
    window.rfqDragLeave= function(){ var dz = g('rfqDz'); if (dz) dz.classList.remove('rfq-dz-over'); };
    window.rfqDrop     = function(e){ e.preventDefault(); var dz = g('rfqDz'); if (dz) dz.classList.remove('rfq-dz-over'); addFiles(e.dataTransfer.files); };

    function addFiles(list) {
      var eEl = g('err_photos'); if (eEl) eEl.style.display='none';
      for (var i=0;i<list.length;i++) {
        if (rfqFiles.length>=MAX_P){ if (eEl) { eEl.textContent='Max 4 photos allowed.'; eEl.style.display='block'; } break; }
        var f=list[i];
        if (f.size>MAX_S){ if (eEl) { eEl.textContent='"'+f.name+'" exceeds 5 MB.'; eEl.style.display='block'; } continue; }
        if (!/^image\/(jpeg|png|webp)$/.test(f.type)){ if (eEl) { eEl.textContent='"'+f.name+'" must be PNG/JPG/WEBP.'; eEl.style.display='block'; } continue; }
        rfqFiles.push(f);
      }
      renderThumbs();
    }
    function renderThumbs(){
      var c=g('rfqThumbs'); if (!c) return;
      c.innerHTML='';
      rfqFiles.forEach(function(f,i){
        var url=URL.createObjectURL(f);
        var d=document.createElement('div'); d.className='rfq-thumb';
        d.innerHTML='<img src="'+url+'" alt="Photo '+(i+1)+'">'
          +'<button type="button" class="rfq-thumb-del" onclick="rfqDel('+i+')" title="Remove">&#x2715;</button>';
        c.appendChild(d);
      });
    }
    window.rfqDel=function(i){ rfqFiles.splice(i,1); renderThumbs(); };

    /* ---- Submit ---- */
    var rfqFormEl = g('rfqForm');
    if (rfqFormEl) {
      rfqFormEl.addEventListener('submit', async function(e){
        e.preventDefault();
        if (!validateStep(3)) return;
        var btn=g('rfqSubmitBtn');
        if (btn) {
          btn.disabled=true;
          btn.innerHTML='<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg> Sending…';
        }
        var fd=new FormData(this);
        fd.delete('reference_photos[]');
        rfqFiles.forEach(function(f){ fd.append('reference_photos[]',f,f.name); });
        try {
          var res=await fetch('<?= url('api/rfq/submit') ?>',{method:'POST',body:fd});
          var data=await res.json();
          if (data.success) {
            [1,2,3].forEach(function(i){ var sEl = g('rfqStep'+i); if (sEl) sEl.style.display='none'; });
            var ftEl = g('rfqFt'); if (ftEl) ftEl.style.display='none';
            var pwEl = g('rfqProgressWrap'); if (pwEl) pwEl.style.display='none';
            var mlEl = g('rfqMobileLbl'); if (mlEl) mlEl.style.display='none';
            var scEl = g('rfqSuccess'); if (scEl) scEl.style.display='block';
            rfqFiles=[]; this.reset(); var th = g('rfqThumbs'); if (th) th.innerHTML=''; step=1;
          } else {
            alert(data.message||'Submission failed. Please try again.');
            if (btn) {
              btn.disabled=false;
              btn.innerHTML='<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Submit Request';
            }
          }
        } catch(ex){
          alert('Network error. Please try again.');
          if (btn) {
            btn.disabled=false;
            btn.innerHTML='<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg> Submit Request';
          }
        }
      });
    }

    // Clear errors on change
    document.querySelectorAll('#rfqForm input,#rfqForm select,#rfqForm textarea').forEach(function(el){
      el.addEventListener('input',function(){ this.classList.remove('rfq-err'); });
    });

  })();
  </script>

  <!-- Slide-Over Mini Cart Drawer -->
  <div id="cartDrawerBackdrop" onclick="closeCartDrawer()" class="fixed inset-0 bg-black/60 z-[999990] hidden transition-opacity duration-300 backdrop-blur-xs"></div>
  <div id="cartDrawer" class="fixed top-0 right-0 h-full w-full sm:w-[400px] bg-white z-[999999] shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out flex flex-col font-sans">
      <div class="p-4 border-b border-gray-200 flex items-center justify-between bg-gray-50">
          <div class="flex items-center gap-2">
              <svg class="w-5 h-5 text-[#f05a29]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
              <h3 class="font-bold text-sm text-gray-900">Your Shopping Cart (<span id="drawerCountText">0</span>)</h3>
          </div>
          <button onclick="closeCartDrawer()" class="p-1 text-gray-400 hover:text-gray-700 text-lg cursor-pointer border-0 bg-transparent">✕</button>
      </div>

      <div id="drawerItemsWrap" class="flex-1 overflow-y-auto p-4 space-y-3">
          <!-- Rendered dynamically -->
      </div>

      <div class="p-4 border-t border-gray-200 bg-gray-50 space-y-3">
          <div class="flex justify-between items-baseline text-xs">
              <span class="text-gray-600 font-semibold">Subtotal:</span>
              <span class="text-base font-bold text-[#f05a29]" id="drawerSubtotalText">₹0.00</span>
          </div>
          <p class="text-[10px] text-gray-400">Taxes &amp; shipping calculated at checkout</p>

          <div class="grid grid-cols-2 gap-2">
              <a href="<?= url('cart') ?>" class="py-2.5 px-3 bg-gray-100 hover:bg-gray-200 text-gray-900 font-bold text-xs rounded-xl text-center transition">View Cart</a>
              <a href="<?= url('checkout') ?>" class="py-2.5 px-3 bg-[#f05a29] hover:bg-[#d94e20] text-white font-bold text-xs rounded-xl text-center transition shadow-xs">Checkout &rarr;</a>
          </div>

          <!-- Request Bulk Quote / RFQ Button in Side Cart -->
          <button type="button" onclick="openRfqModal()" class="w-full py-2.5 px-3 bg-[#0F172A] hover:bg-black text-white font-bold text-xs rounded-xl text-center transition flex items-center justify-center gap-2 cursor-pointer border-0 shadow-xs">
              <svg class="w-4 h-4 text-[#f05a29]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              <span>Request Bulk Quote / Send Inquiry</span>
          </button>
      </div>
  </div>

  <!-- Toast Notification Popup -->
  <div id="cartToast" class="fixed bottom-6 right-6 z-[999999] hidden items-center gap-3 bg-gray-900 text-white px-4 py-3 rounded-xl shadow-2xl text-xs font-semibold border border-gray-700 transition transform duration-300">
      <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
      <span id="cartToastText">Item added to cart</span>
  </div>

  <script>
      async function quickAddToCartCard(productId, moq, btn) {
          const payload = new URLSearchParams();
          payload.append('product_id', productId);
          payload.append('quantity', moq || 1);
          payload.append('pricing_mode', 'wholesale');

          try {
              const res = await fetch('<?= url('cart/add') ?>', { method: 'POST', body: payload });
              const data = await res.json();
              if (data.success) {
                  if (typeof updateHeaderCartBadge === 'function') {
                      updateHeaderCartBadge(data.cart_count);
                  }
                  if (typeof renderCartDrawerUI === 'function') {
                      renderCartDrawerUI(data.items, data.subtotal, data.cart_count);
                  }
                  if (typeof showCartToast === 'function') {
                      showCartToast('Item added to cart');
                  }
              }
          } catch(e){}
      }
      async function fetchCartData() {
          try {
              const res = await fetch('<?= url('cart/data') ?>');
              const data = await res.json();
              if (data.success) {
                  updateHeaderCartBadge(data.count);
                  renderCartDrawerUI(data.items, data.subtotal, data.count);
                  if (typeof updateOrderSummarySidebar === 'function') {
                      updateOrderSummarySidebar(data.count, data.subtotal);
                  }
                  if (typeof syncExistingCartToSteppers === 'function') {
                      syncExistingCartToSteppers(data.items);
                  }
              }
          } catch(e){}
      }

      function updateHeaderCartBadge(count) {
          const badge = document.getElementById('headerCartCount');
          if (badge) {
              if (count > 0) {
                  badge.textContent = count;
                  badge.style.display = 'flex';
              } else {
                  badge.style.display = 'none';
              }
          }
      }

      function renderCartDrawerUI(items, subtotal, count) {
          const countEl = document.getElementById('drawerCountText');
          if (countEl) countEl.textContent = count || 0;

          const subEl = document.getElementById('drawerSubtotalText');
          if (subEl) subEl.textContent = '₹' + (subtotal || '0.00');

          const wrap = document.getElementById('drawerItemsWrap');
          if (!wrap) return;

          if (!items || items.length === 0) {
              wrap.innerHTML = `
                  <div class="text-center py-12 text-gray-400 space-y-2">
                      <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                      <div class="text-xs font-semibold">Your cart is empty</div>
                  </div>
              `;
              return;
          }

          let html = '';
          items.forEach(item => {
              html += `
                  <div class="flex items-center gap-3 p-2.5 bg-white border border-gray-200 rounded-xl">
                      <img src="${item.image}" class="w-12 h-12 object-cover rounded-lg border border-gray-100 shrink-0">
                      <div class="flex-1 min-w-0">
                          <div class="font-bold text-xs text-gray-900 truncate">${item.name}</div>
                          <div class="text-[10px] text-gray-500">${item.variant_title ? item.variant_title + ' &bull; ' : ''} Qty: ${item.quantity}</div>
                          <div class="text-xs font-bold text-[#f05a29]">₹${parseFloat(item.item_total).toFixed(2)}</div>
                      </div>
                      <button onclick="removeDrawerCartItem(${item.id})" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition cursor-pointer border-0 bg-transparent" title="Remove item">
                          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                          </svg>
                      </button>
                  </div>
              `;
          });
          wrap.innerHTML = html;
      }

      function openCartDrawer() {
          fetchCartData();
          document.getElementById('cartDrawerBackdrop').classList.remove('hidden');
          document.getElementById('cartDrawer').classList.remove('translate-x-full');
      }

      function closeCartDrawer() {
          document.getElementById('cartDrawerBackdrop').classList.add('hidden');
          document.getElementById('cartDrawer').classList.add('translate-x-full');
      }

      function showCartToast(msg) {
          const toast = document.getElementById('cartToast');
          const txt = document.getElementById('cartToastText');
          if (toast && txt) {
              txt.textContent = msg;
              toast.classList.remove('hidden');
              toast.classList.add('flex');
              setTimeout(() => {
                  toast.classList.add('hidden');
                  toast.classList.remove('flex');
              }, 3500);
          }
      }

      async function removeDrawerCartItem(itemId) {
          const payload = new URLSearchParams();
          payload.append('cart_item_id', itemId);
          const res = await fetch('<?= url('cart/remove') ?>', { method: 'POST', body: payload });
          const data = await res.json();
          if (data.success) {
              updateHeaderCartBadge(data.cart_count);
              renderCartDrawerUI(data.items, data.subtotal, data.cart_count);
          }
      }

      document.addEventListener('DOMContentLoaded', fetchCartData);
  </script>
</body>

</html>