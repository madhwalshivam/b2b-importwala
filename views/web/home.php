<?php
$title = "ImportWale Wholesale | Direct Global B2B Platform";
ob_start();
?>

<!-- Dynamic Hero Banner Slider Section -->
<div class="hero-banner-wrapper" style="margin:20px 0 32px 0; border-radius:16px; overflow:hidden; position:relative; background:#FAF4F2;">

  <?php if (!empty($heroBanners)): ?>
    <div id="heroBannerSlider" class="hero-slides-container" style="display:flex; transition:transform 0.5s ease-in-out; width:100%;">
      <?php foreach ($heroBanners as $idx => $banner): ?>
        <?php
          $desktopSrc = \App\Models\Banner::getImageSrc($banner);
          $tabletSrc  = \App\Models\Banner::getTabletImageSrc($banner) ?: $desktopSrc;
          $mobileSrc  = \App\Models\Banner::getMobileImageSrc($banner) ?: $desktopSrc;
          $linkUrl    = !empty($banner['link_url']) ? (str_starts_with($banner['link_url'], 'http') ? $banner['link_url'] : url(ltrim($banner['link_url'], '/'))) : '#';
        ?>
        <div class="hero-slide-item" style="min-width:100%; flex-shrink:0; position:relative;">
          <?php if ($linkUrl !== '#'): ?>
            <a href="<?= htmlspecialchars($linkUrl) ?>" style="display:block; width:100%;">
          <?php endif; ?>

          <picture>
            <?php if ($mobileSrc): ?>
              <source media="(max-width: 640px)" srcset="<?= htmlspecialchars($mobileSrc) ?>">
            <?php endif; ?>
            <?php if ($tabletSrc): ?>
              <source media="(max-width: 1024px)" srcset="<?= htmlspecialchars($tabletSrc) ?>">
            <?php endif; ?>
            <img src="<?= htmlspecialchars($desktopSrc) ?>" alt="<?= htmlspecialchars($banner['title'] ?: 'ImportWala Wholesale Banner') ?>" style="width:100%; height:auto; max-height:480px; display:block; object-fit:cover; border-radius:16px;">
          </picture>

          <?php if (!empty($banner['title']) || !empty($banner['subtitle'])): ?>
            <div class="hero-slide-caption" style="position:absolute; bottom:24px; left:24px; right:24px; background:rgba(0,0,0,0.4); backdrop-filter:blur(8px); padding:16px 24px; border-radius:12px; color:#fff;">
              <?php if (!empty($banner['title'])): ?>
                <h3 style="font-size:20px; font-weight:700; margin:0 0 4px 0; color:#fff;"><?= htmlspecialchars($banner['title']) ?></h3>
              <?php endif; ?>
              <?php if (!empty($banner['subtitle'])): ?>
                <p style="font-size:13px; margin:0; opacity:0.9;"><?= htmlspecialchars($banner['subtitle']) ?></p>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <?php if ($linkUrl !== '#'): ?>
            </a>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (count($heroBanners) > 1): ?>
      <!-- Subtle Sleek Slider Arrows -->
      <button type="button" class="hero-arrow-btn hero-arrow-prev" onclick="moveHeroSlide(-1)" aria-label="Previous Slide" style="position:absolute; left:16px; top:50%; transform:translateY(-50%); z-index:10; width:40px; height:40px; border-radius:50%; background:rgba(255,255,255,0.85); backdrop-filter:blur(4px); border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#111;">
        <svg style="width:20px; height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
      </button>
      <button type="button" class="hero-arrow-btn hero-arrow-next" onclick="moveHeroSlide(1)" aria-label="Next Slide" style="position:absolute; right:16px; top:50%; transform:translateY(-50%); z-index:10; width:40px; height:40px; border-radius:50%; background:rgba(255,255,255,0.85); backdrop-filter:blur(4px); border:none; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#111;">
        <svg style="width:20px; height:20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
      </button>

      <!-- Slide Indicator Dots -->
      <div id="heroSlideDots" style="position:absolute; bottom:16px; left:50%; transform:translateX(-50%); z-index:10; display:flex; gap:8px;">
        <?php foreach ($heroBanners as $idx => $b): ?>
          <button type="button" onclick="setHeroSlide(<?= $idx ?>)" class="hero-dot <?= $idx === 0 ? 'active' : '' ?>" style="width:<?= $idx === 0 ? '24px' : '8px' ?>; height:8px; border-radius:4px; background:<?= $idx === 0 ? '#f05a29' : 'rgba(255,255,255,0.6)' ?>; border:none; cursor:pointer; transition:all 0.3s ease;"></button>
        <?php endforeach; ?>
      </div>

      <script>
        (function() {
          let currentHeroIndex = 0;
          const totalHeroSlides = <?= count($heroBanners) ?>;
          const slider = document.getElementById('heroBannerSlider');
          const dotsContainer = document.getElementById('heroSlideDots');
          let autoSlideTimer = null;

          window.moveHeroSlide = function(dir) {
            currentHeroIndex = (currentHeroIndex + dir + totalHeroSlides) % totalHeroSlides;
            updateHeroSlider();
            resetAutoSlide();
          };

          window.setHeroSlide = function(idx) {
            currentHeroIndex = idx;
            updateHeroSlider();
            resetAutoSlide();
          };

          function updateHeroSlider() {
            if (!slider) return;
            slider.style.transform = `translateX(-${currentHeroIndex * 100}%)`;
            if (dotsContainer) {
              const dots = dotsContainer.children;
              for (let i = 0; i < dots.length; i++) {
                if (i === currentHeroIndex) {
                  dots[i].style.width = '24px';
                  dots[i].style.background = '#f05a29';
                } else {
                  dots[i].style.width = '8px';
                  dots[i].style.background = 'rgba(255,255,255,0.6)';
                }
              }
            }
          }

          function resetAutoSlide() {
            if (autoSlideTimer) clearInterval(autoSlideTimer);
            autoSlideTimer = setInterval(function() {
              moveHeroSlide(1);
            }, 5000);
          }

          resetAutoSlide();
        })();
      </script>
    <?php endif; ?>

  <?php else: ?>
    <!-- Fallback Standard Banner if no active DB banners exist -->
    <a href="<?= url('catalog') ?>" style="display:block; width:100%;">
      <img src="<?= asset('images/hero-spooky-banner.png') ?>" alt="ImportWala Wholesale Catalog" style="width:100%; height:auto; display:block; object-fit:cover; border-radius:16px;">
    </a>
  <?php endif; ?>

</div>

<!-- Featured Categories Section (EverfulWholesale UI Architecture) -->
<?php if (!empty($featuredCategories)): ?>
<div class="featured-categories-section" style="margin-bottom: 40px;">
  
  <!-- Section Title & Arrows Row -->
  <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:18px;">
    <h2 class="featured-cats-heading">
      Featured categories
    </h2>
  </div>

  <!-- Horizontal Scrollable Pill Tabs Row -->
  <div class="feat-tabs-wrapper" style="position:relative; display:flex; align-items:center; margin-bottom:24px;">
    
    <!-- Left Scroll Button -->
    <button type="button" class="tab-scroll-btn scroll-left" onclick="scrollFeatTabs(-240)" aria-label="Scroll Left" style="display:flex; width:32px; height:32px; border-radius:50%; background:#ffffff; border:1px solid #e5e7eb; cursor:pointer; align-items:center; justify-content:center; margin-right:8px; flex-shrink:0; box-shadow:0 1px 3px rgba(0,0,0,0.1); opacity:0.4; transition:all 0.2s ease;">
      <svg style="width:16px; height:16px; color:#374151;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
    </button>

    <!-- Pill Tabs Container -->
    <div id="featTabsContainer" style="display:flex; gap:10px; overflow-x:auto; scroll-behavior:smooth; scrollbar-width:none; padding:4px 0; -webkit-overflow-scrolling:touch; width:100%;">
      <?php foreach ($featuredCategories as $index => $cat): ?>
        <button type="button" 
                class="feat-tab-btn <?= $index === 0 ? 'active' : '' ?>" 
                data-cat-id="<?= $cat['id'] ?>"
                data-cat-name="<?= htmlspecialchars($cat['name']) ?>"
                data-cat-slug="<?= htmlspecialchars($cat['slug']) ?>"
                onclick="switchFeatCategoryTab(this)"
                style="padding:10px 22px; border-radius:9999px; font-size:14px; font-weight:500; font-family:system-ui, sans-serif; cursor:pointer; border:none; white-space:nowrap; transition:all 0.2s ease; <?= $index === 0 ? 'background:#111827; color:#ffffff; font-weight:600;' : 'background:#f3f4f6; color:#374151;' ?>">
          <?= htmlspecialchars($cat['name']) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- Right Scroll Button -->
    <button type="button" class="tab-scroll-btn scroll-right" onclick="scrollFeatTabs(240)" aria-label="Scroll Right" style="display:flex; width:32px; height:32px; border-radius:50%; background:#ffffff; border:1px solid #e5e7eb; cursor:pointer; align-items:center; justify-content:center; margin-left:8px; flex-shrink:0; box-shadow:0 1px 3px rgba(0,0,0,0.1); transition:all 0.2s ease;">
      <svg style="width:16px; height:16px; color:#374151;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
    </button>
  </div>

  <!-- Subcategories Grid (4 Cols Desktop / 3 Cols Laptop / 2 Cols Tablet / 1 Mobile) -->
  <div id="featSubcategoriesGrid" class="feat-subcat-grid" style="display:grid; grid-template-columns: repeat(4, 1fr); gap:16px; transition:opacity 0.2s ease;">
    <!-- Grid Content Populated dynamically by JS on Load & Tab Switch -->
  </div>

</div>

<!-- Embedded CSS for Featured Categories Section -->
<style>
.featured-cats-heading {
  font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
  font-size: 24px;
  font-weight: 700;
  line-height: 1.25;
  letter-spacing: -0.025em;
  color: #111827;
  margin: 0;
  -webkit-font-smoothing: antialiased;
}

#featTabsContainer::-webkit-scrollbar {
  display: none;
}

.feat-tab-btn:hover:not(.active) {
  background: #e5e7eb !important;
  color: #111827 !important;
}

.feat-subcat-card {
  display: flex;
  align-items: center;
  padding: 16px 20px;
  background: #f4f5f7;
  border-radius: 16px;
  text-decoration: none;
  transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
  min-height: 104px;
}

.feat-subcat-card:hover {
  transform: translateY(-2px);
  background: #ebeef2;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.feat-subcat-img-box {
  width: 76px;
  height: 76px;
  border-radius: 14px;
  overflow: hidden;
  background: #e5e7eb;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-right: 16px;
  flex-shrink: 0;
}

.feat-subcat-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 14px;
}

.feat-subcat-title {
  font-size: 15px;
  font-weight: 600;
  color: #111827;
  line-height: 1.35;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  margin: 0;
}

@media (max-width: 1200px) {
  .feat-subcat-grid {
    grid-template-columns: repeat(3, 1fr) !important;
  }
}

@media (max-width: 768px) {
  .feat-subcat-grid {
    grid-template-columns: repeat(2, 1fr) !important;
  }
}

@media (max-width: 480px) {
  .feat-subcat-grid {
    grid-template-columns: repeat(1, 1fr) !important;
    gap: 12px !important;
  }
  .feat-subcat-card {
    padding: 14px 16px;
  }
  .feat-subcat-img-box {
    width: 64px;
    height: 64px;
    margin-right: 12px;
  }
  .feat-subcat-title {
    font-size: 14px;
  }
}
</style>

<script>
const featuredCategoriesData = <?= json_encode($featuredCategories, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const BASE_URL = '<?= url("") ?>';

function getFullAssetUrl(path) {
  if (!path) return '';
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('//') || path.startsWith('data:')) return path;
  return BASE_URL + '/' + path.replace(/^\//, '');
}

function renderSubcategoriesForCategory(catId) {
  const grid = document.getElementById('featSubcategoriesGrid');
  if (!grid) return;

  const category = featuredCategoriesData.find(c => parseInt(c.id) === parseInt(catId)) || featuredCategoriesData[0];
  if (!category) return;

  grid.style.opacity = '0';

  setTimeout(() => {
    let html = '';

    const fallbackSvg = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='1.5'><rect x='3' y='3' width='18' height='18' rx='4' fill='%23f3f4f6'/><path d='M8.5 10a1.5 1.5 0 100-3 1.5 1.5 0 000 3z'/><path d='M21 15l-5-5L5 21'/></svg>";

    // Card #1: View All [Category Name] (Uploaded Main Category Image from Admin Panel)
    const viewAllUrl = '<?= url("catalog") ?>?q=' + encodeURIComponent(category.name.toLowerCase());
    const catRawImg = category.main_category_image || category.image || (category.subcategories && category.subcategories.length > 0 ? category.subcategories[0].image : null);
    const catImgUrl = catRawImg ? getFullAssetUrl(catRawImg) : fallbackSvg;

    html += `
      <a href="${viewAllUrl}" class="feat-subcat-card">
        <div class="feat-subcat-img-box">
          <img src="${catImgUrl}" alt="View All ${escapeHtml(category.name)}" class="feat-subcat-img" loading="lazy" onerror="this.onerror=null; this.src='${fallbackSvg}';">
        </div>
        <h3 class="feat-subcat-title">View All ${escapeHtml(category.name)}</h3>
      </a>
    `;

    if (category.subcategories && category.subcategories.length > 0) {
      category.subcategories.forEach(sub => {
        const targetUrl = sub.link_url.startsWith('/') ? '<?= url("") ?>' + sub.link_url.replace(/^\//, '') : sub.link_url;
        const imgUrl = sub.image ? getFullAssetUrl(sub.image) : fallbackSvg;

        html += `
          <a href="${targetUrl}" class="feat-subcat-card">
            <div class="feat-subcat-img-box">
              <img src="${imgUrl}" alt="${escapeHtml(sub.name)}" class="feat-subcat-img" loading="lazy" onerror="this.onerror=null; this.src='${fallbackSvg}';">
            </div>
            <h3 class="feat-subcat-title">${escapeHtml(sub.name)}</h3>
          </a>
        `;
      });
    }

    grid.innerHTML = html;
    grid.style.opacity = '1';
  }, 120);
}

function switchFeatCategoryTab(btn) {
  const allBtns = document.querySelectorAll('.feat-tab-btn');
  allBtns.forEach(b => {
    b.classList.remove('active');
    b.style.background = '#f3f4f6';
    b.style.color = '#374151';
    b.style.fontWeight = '500';
  });

  btn.classList.add('active');
  btn.style.background = '#111827';
  btn.style.color = '#ffffff';
  btn.style.fontWeight = '600';

  if (btn.scrollIntoView) {
    btn.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
  }

  const catId = btn.getAttribute('data-cat-id');
  renderSubcategoriesForCategory(catId);
  setTimeout(updateFeatTabArrows, 300);
}

function scrollFeatTabs(amount) {
  const container = document.getElementById('featTabsContainer');
  if (container) {
    container.scrollBy({ left: amount, behavior: 'smooth' });
    setTimeout(updateFeatTabArrows, 300);
  }
}

function updateFeatTabArrows() {
  const container = document.getElementById('featTabsContainer');
  const leftBtn = document.querySelector('.tab-scroll-btn.scroll-left');
  const rightBtn = document.querySelector('.tab-scroll-btn.scroll-right');
  if (!container) return;

  const scrollLeft = container.scrollLeft;
  const maxScroll = container.scrollWidth - container.clientWidth;

  if (leftBtn) {
    leftBtn.style.display = 'flex';
    leftBtn.style.opacity = scrollLeft > 10 ? '1' : '0.3';
    leftBtn.style.pointerEvents = scrollLeft > 10 ? 'auto' : 'none';
  }
  if (rightBtn) {
    rightBtn.style.display = 'flex';
    rightBtn.style.opacity = scrollLeft < maxScroll - 10 ? '1' : '0.3';
    rightBtn.style.pointerEvents = scrollLeft < maxScroll - 10 ? 'auto' : 'none';
  }
}

function escapeHtml(str) {
  return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

document.addEventListener('DOMContentLoaded', function() {
  if (featuredCategoriesData && featuredCategoriesData.length > 0) {
    renderSubcategoriesForCategory(featuredCategoriesData[0].id);
  }
  const container = document.getElementById('featTabsContainer');
  if (container) {
    container.addEventListener('scroll', updateFeatTabArrows);
    window.addEventListener('resize', updateFeatTabArrows);
    updateFeatTabArrows();
  }
});
</script>
<?php endif; ?>

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
