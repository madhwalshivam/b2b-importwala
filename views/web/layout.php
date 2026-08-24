<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($title ?? 'ImportWale | World-Scale B2B Wholesale Platform') ?></title>
  <link rel="stylesheet" href="<?= asset('css/everful-theme.css') ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500;1,600&family=Inter:wght@400;500;600;700;800;900&display=swap"
    rel="stylesheet">
</head>

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
        <button type="button" class="camera-search-trigger" onclick="openImageSearchModal()"
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
        </a>
        <a href="<?= url('cart') ?>" class="header-icon-item" title="Cart">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
          </svg>
          <div class="cart-pill-count" id="headerCartCount">1</div>
        </a>
      </div>
    </div>

    <!-- Category Sub-Nav Bar -->
    <nav class="nav-categories-bar">
      <div class="nav-categories-inner">
        <a href="<?= url('') ?>" class="nav-category-link active">Home</a>
        <a href="<?= url('catalog') ?>" class="nav-category-link">
          Categories
          <svg class="dropdown-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
            style="margin-left:3px; display:inline-block; vertical-align:middle;">
            <path d="M6 9l6 6 6-6" />
          </svg>
        </a>
        <a href="<?= url('catalog?sort=newest') ?>" class="nav-category-link">New Arrivals</a>
        <a href="<?= url('catalog?sort=popular') ?>" class="nav-category-link">Best Sellers</a>
        <a href="<?= url('catalog?free_shipping=1') ?>" class="nav-category-link">Free Air Shipping</a>
        <a href="<?= url('catalog?price_drops=1') ?>" class="nav-category-link">Price Drops</a>
        <a href="<?= url('catalog?q=halloween') ?>" class="nav-category-link">Halloween</a>
        <a href="<?= url('blog') ?>" class="nav-category-link">Blog</a>
        <a href="<?= url('support') ?>" class="nav-category-link">
          Support
          <svg class="dropdown-chevron" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"
            style="margin-left:3px; display:inline-block; vertical-align:middle;">
            <path d="M6 9l6 6 6-6" />
          </svg>
        </a>
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
        <p style="font-size:13px; color:#9ca3af; margin-bottom:12px;">World-Scale B2B Wholesale Platform connecting
          international buyers directly with global manufacturers.</p>
        <p style="font-size:13px; color:#9ca3af;">Email: support@importwale.com</p>
      </div>
      <div class="footer-col">
        <h4>Customer Care</h4>
        <ul>
          <li><a href="<?= url('') ?>">Home</a></li>
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
  </script>
</body>

</html>