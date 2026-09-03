<?php
$title = "ImportWale Wholesale | Direct Global B2B Platform";
ob_start();
?>

<!-- Dynamic Hero Banner Slider Section -->
<div class="hero-banner-wrapper" style="margin:0 -24px 20px -24px; width:calc(100% + 48px); border-radius:0; overflow:hidden; position:relative; background:#FAF4F2;">

  <?php if (!empty($heroBanners)): ?>
    <div id="heroBannerSlider" class="hero-slides-container" style="display:flex; transition:transform 0.5s ease-in-out; width:100%; height:100%;">
      <?php foreach ($heroBanners as $idx => $banner): ?>
        <?php
          $desktopSrc = \App\Models\Banner::getImageSrc($banner);
          $tabletSrc  = \App\Models\Banner::getTabletImageSrc($banner) ?: $desktopSrc;
          $mobileSrc  = \App\Models\Banner::getMobileImageSrc($banner) ?: $desktopSrc;
          $linkUrl    = !empty($banner['link_url']) ? (str_starts_with($banner['link_url'], 'http') ? $banner['link_url'] : url(ltrim($banner['link_url'], '/'))) : '#';
        ?>
        <div class="hero-slide-item" style="min-width:100%; flex-shrink:0; position:relative; height:100%;">
          <?php if ($linkUrl !== '#'): ?>
            <a href="<?= htmlspecialchars($linkUrl) ?>" style="display:block; width:100%; height:100%;">
          <?php endif; ?>

          <picture style="display:block; width:100%; height:100%;">
            <?php if ($mobileSrc): ?>
              <source media="(max-width: 640px)" srcset="<?= htmlspecialchars($mobileSrc) ?>">
            <?php endif; ?>
            <?php if ($tabletSrc): ?>
              <source media="(max-width: 1024px)" srcset="<?= htmlspecialchars($tabletSrc) ?>">
            <?php endif; ?>
            <img src="<?= htmlspecialchars($desktopSrc) ?>" alt="<?= htmlspecialchars($banner['title'] ?: 'ImportWala Wholesale Banner') ?>" style="width:100%; height:100%; display:block; object-fit:cover; object-position:center; border-radius:0;">
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
      <img src="<?= asset('images/hero-spooky-banner.png') ?>" alt="ImportWala Wholesale Catalog" style="width:100%; height:auto; display:block; object-fit:cover; border-radius:0;">
    </a>
  <?php endif; ?>

</div>

<!-- Featured Categories Section (EverfulWholesale UI Architecture) -->
<?php if (!empty($featuredCategories)): ?>
<div class="featured-categories-section">
  
  <!-- Section Title & Navigation Arrows Row -->
  <div class="feat-cats-header">
    <div>
      <h2 class="featured-cats-heading">Featured Categories</h2>
      <p class="featured-cats-subtext">Curated wholesale product categories &bull; Direct factory pricing</p>
    </div>
    <!-- Card Swipe Navigation Arrows -->
    <div class="feat-cards-nav">
      <button type="button" class="card-scroll-btn scroll-left" onclick="scrollFeatCards(-1)" aria-label="Previous Categories" title="Scroll Left">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
      </button>
      <button type="button" class="card-scroll-btn scroll-right" onclick="scrollFeatCards(1)" aria-label="Next Categories" title="Scroll Right">
        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
      </button>
    </div>
  </div>

  <!-- Horizontal Scrollable Pill Tabs Row -->
  <div class="feat-tabs-wrapper">
    
    <!-- Left Scroll Button -->
    <button type="button" class="tab-scroll-btn scroll-left" onclick="scrollFeatTabs(-280)" aria-label="Scroll Left">
      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
    </button>

    <!-- Pill Tabs Container -->
    <div id="featTabsContainer" class="feat-tabs-container">
      <?php foreach ($featuredCategories as $index => $cat): ?>
        <button type="button" 
                class="feat-tab-btn <?= $index === 0 ? 'active' : '' ?>" 
                data-cat-id="<?= $cat['id'] ?>"
                data-cat-name="<?= htmlspecialchars($cat['name']) ?>"
                data-cat-slug="<?= htmlspecialchars($cat['slug']) ?>"
                onclick="switchFeatCategoryTab(this)">
          <?= htmlspecialchars($cat['name']) ?>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- Right Scroll Button -->
    <button type="button" class="tab-scroll-btn scroll-right" onclick="scrollFeatTabs(280)" aria-label="Scroll Right">
      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
    </button>
  </div>

  <!-- Subcategories Single-Row Carousel Container (4 Cards View) -->
  <div id="featSubcategoriesGrid" class="feat-subcat-grid">
    <!-- Grid Content Populated dynamically by JS -->
  </div>

</div>

<!-- Embedded CSS for Redesigned Featured Categories Section -->
<style>
.featured-categories-section {
  background: #F8FAFC;
  border: 1px solid #E2E8F0;
  border-radius: 20px;
  padding: 24px 20px;
  margin-bottom: 24px;
  box-shadow: none !important;
}

.feat-cats-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}

.featured-cats-heading {
  font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
  font-size: 22px !important;
  font-weight: 600 !important;
  line-height: 1.25 !important;
  letter-spacing: -0.01em !important;
  color: #0F172A !important;
  margin: 0 !important;
}

.featured-cats-subtext {
  font-size: 13px !important;
  color: #64748B !important;
  margin: 4px 0 0 0 !important;
  font-weight: 400 !important;
}

.feat-cards-nav {
  display: flex;
  align-items: center;
  gap: 8px;
}

.card-scroll-btn {
  display: flex;
  width: 34px;
  height: 34px;
  border-radius: 50%;
  background: #FFFFFF;
  border: 1px solid #CBD5E1;
  cursor: pointer;
  align-items: center;
  justify-content: center;
  box-shadow: none !important;
  transition: all 0.2s ease;
  color: #334155;
}

.card-scroll-btn:hover {
  background: #f05a29;
  border-color: #f05a29;
  color: #FFFFFF;
}

/* Scrollable Pill Tabs Wrapper & Circular Buttons */
.feat-tabs-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  margin-bottom: 24px;
}

.tab-scroll-btn {
  display: flex;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: #FFFFFF;
  border: 1px solid #E2E8F0;
  cursor: pointer;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  box-shadow: none !important;
  transition: all 0.2s ease;
  color: #334155;
  z-index: 2;
}

.tab-scroll-btn.scroll-left { margin-right: 8px; }
.tab-scroll-btn.scroll-right { margin-left: 8px; }

.tab-scroll-btn:hover {
  background: #f05a29;
  border-color: #f05a29;
  color: #FFFFFF;
}

.feat-tabs-container {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  scroll-behavior: smooth;
  scrollbar-width: none;
  padding: 4px 0;
  -webkit-overflow-scrolling: touch;
  width: 100%;
}

.feat-tabs-container::-webkit-scrollbar {
  display: none;
}

.feat-tab-btn {
  padding: 8px 20px;
  border-radius: 9999px;
  font-size: 13px;
  font-weight: 600;
  font-family: 'Inter', system-ui, sans-serif;
  cursor: pointer;
  border: 1px solid #E2E8F0;
  background: #FFFFFF;
  color: #475569;
  white-space: nowrap;
  transition: all 0.2s ease;
  box-shadow: none !important;
}

.feat-tab-btn:hover:not(.active) {
  background: #F1F5F9;
  color: #0F172A;
  border-color: #CBD5E1;
}

.feat-tab-btn.active {
  background: #f05a29 !important;
  color: #FFFFFF !important;
  border-color: #f05a29 !important;
  font-weight: 700 !important;
  box-shadow: none !important;
}

/* Subcategories Container: Single Row Horizontal Carousel showing 4 Cards at a time */
.feat-subcat-grid {
  display: flex;
  gap: 16px;
  overflow-x: auto;
  scroll-behavior: smooth;
  scrollbar-width: none;
  -webkit-overflow-scrolling: touch;
  padding: 4px 0;
  transition: opacity 0.25s ease;
}

.feat-subcat-grid::-webkit-scrollbar {
  display: none;
}

/* Amazon-Style Category Card (Desktop Default: 5 Cards visible per row) */
.feat-subcat-card {
  flex: 0 0 calc((100% - 64px) / 5);
  min-width: calc((100% - 64px) / 5);
  display: flex;
  flex-direction: column;
  background: #FFFFFF;
  border-radius: 12px;
  border: 1px solid #E2E8F0;
  padding: 12px;
  text-decoration: none;
  transition: border-color 0.2s ease, transform 0.2s ease;
  box-shadow: none !important;
  box-sizing: border-box;
}

.feat-subcat-card:hover {
  border-color: #f05a29;
  transform: translateY(-2px);
}

/* Amazon Inner Image Frame */
.feat-subcat-img-box {
  width: 100%;
  aspect-ratio: 1 / 1;
  border-radius: 8px;
  background: #F8FAFC;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 10px;
  padding: 5px;
  box-sizing: border-box;
  border: 1px solid #F1F5F9;
}

.feat-subcat-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 6px;
  transition: transform 0.3s ease;
}

.feat-subcat-card:hover .feat-subcat-img {
  transform: scale(1.04);
}

/* Amazon-Style Card Info */
.feat-subcat-info {
  display: flex;
  flex-direction: column;
  gap: 3px;
  text-align: left;
}

.feat-subcat-title {
  font-size: 13px;
  font-weight: 700;
  color: #0F172A;
  line-height: 1.35;
  font-family: 'Inter', system-ui, sans-serif;
  margin: 0;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.feat-subcat-link {
  font-size: 11.5px;
  font-weight: 600;
  color: #f05a29;
  display: inline-flex;
  align-items: center;
  gap: 3px;
}

.feat-subcat-card:hover .feat-subcat-link {
  text-decoration: underline;
}

/* Responsive Cards per Row Breakpoints */
@media (max-width: 1280px) {
  /* Laptop screens: 4 Cards per row */
  .feat-subcat-card {
    flex: 0 0 calc((100% - 48px) / 4);
    min-width: calc((100% - 48px) / 4);
    padding: 12px;
  }
}

@media (max-width: 1024px) {
  /* Tablet screens: 3 Cards per row */
  .feat-subcat-card {
    flex: 0 0 calc((100% - 32px) / 3);
    min-width: calc((100% - 32px) / 3);
    padding: 11px;
  }
}

@media (max-width: 640px) {
  /* Mobile screens: 2 Cards per row */
  .featured-categories-section {
    padding: 20px 14px;
    border-radius: 16px;
  }
  .feat-subcat-card {
    flex: 0 0 calc((100% - 12px) / 2);
    min-width: calc((100% - 12px) / 2);
    padding: 10px;
  }
  .feat-subcat-title {
    font-size: 12px;
  }
  .feat-subcat-link {
    font-size: 11px;
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

    const fallbackSvg = "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='200' height='200' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='1.5'><rect x='3' y='3' width='18' height='18' rx='4' fill='%23f1f5f9'/><path d='M8.5 10a1.5 1.5 0 100-3 1.5 1.5 0 000 3z'/><path d='M21 15l-5-5L5 21'/></svg>";

    // Card #1: View All [Category Name] (Amazon Style)
    const viewAllUrl = '<?= url("catalog") ?>?q=' + encodeURIComponent(category.name.toLowerCase());
    const catRawImg = category.main_category_image || category.image || (category.subcategories && category.subcategories.length > 0 ? category.subcategories[0].image : null);
    const catImgUrl = catRawImg ? getFullAssetUrl(catRawImg) : fallbackSvg;

    html += `
      <a href="${viewAllUrl}" class="feat-subcat-card">
        <div class="feat-subcat-img-box">
          <img src="${catImgUrl}" alt="View All ${escapeHtml(category.name)}" class="feat-subcat-img" loading="lazy" onerror="this.onerror=null; this.src='${fallbackSvg}';">
        </div>
        <div class="feat-subcat-info">
          <h3 class="feat-subcat-title">View All ${escapeHtml(category.name)}</h3>
          <span class="feat-subcat-link">Explore all items &rsaquo;</span>
        </div>
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
            <div class="feat-subcat-info">
              <h3 class="feat-subcat-title">${escapeHtml(sub.name)}</h3>
              <span class="feat-subcat-link">Shop now &rsaquo;</span>
            </div>
          </a>
        `;
      });
    }

    grid.innerHTML = html;
    grid.style.opacity = '1';
    grid.scrollLeft = 0;
  }, 120);
}

function scrollFeatCards(direction) {
  const container = document.getElementById('featSubcategoriesGrid');
  if (!container) return;
  const amount = container.clientWidth * 0.75 * direction;
  container.scrollBy({ left: amount, behavior: 'smooth' });
}

function switchFeatCategoryTab(btn) {
  const allBtns = document.querySelectorAll('.feat-tab-btn');
  allBtns.forEach(b => {
    b.classList.remove('active');
  });

  btn.classList.add('active');

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

<?php if (!empty($collectionCards)): ?>
<!-- ============================================================
     TRENDING COLLECTIONS CARDS SECTION
     ============================================================ -->
<div class="collection-cards-section">

  <!-- Section Header -->
  <div class="cc-header-row">
    <div>
      <h2 class="cc-section-title">Trending Collections</h2>
      <p class="cc-section-sub">Curated wholesale product collections · Updated daily</p>
    </div>
    <div class="cc-nav-arrows">
      <button type="button" onclick="scrollCollections(-1)" class="cc-arrow-btn" aria-label="Scroll Left">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
      </button>
      <button type="button" onclick="scrollCollections(1)" class="cc-arrow-btn" aria-label="Scroll Right">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="18" height="18"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
      </button>
    </div>
  </div>

  <!-- Scrollable Cards Track -->
  <div class="cc-track-wrapper">
    <div class="cc-track" id="ccTrack">
      <?php foreach ($collectionCards as $card):
        $products = $card['products'] ?? [];
        $productCount = count($products);
        // Take first 4 products for the collage
        $collageProducts = array_slice($products, 0, 4);
        $rawLink = trim($card['link_url'] ?? '');
        if (empty($rawLink) || $rawLink === '/catalog' || $rawLink === 'catalog') {
            $cardLink = url('catalog?collection_id=' . $card['id']);
        } elseif (str_starts_with($rawLink, 'http://') || str_starts_with($rawLink, 'https://')) {
            $cardLink = $rawLink;
        } else {
            $cardLink = url(ltrim($rawLink, '/'));
        }
      ?>
      <a href="<?= htmlspecialchars($cardLink) ?>" class="cc-card" title="<?= htmlspecialchars($card['title']) ?>">

        <!-- Badge -->
        <?php if (!empty($card['badge_text'])): ?>
          <div class="cc-badge"><?= htmlspecialchars($card['badge_text']) ?></div>
        <?php endif; ?>

        <!-- Product Image Collage -->
        <div class="cc-collage">
          <?php if (!empty($card['image'])): ?>
            <!-- Single cover image if set -->
            <div class="cc-collage-full">
              <img src="<?= htmlspecialchars(asset($card['image'])) ?>" alt="<?= htmlspecialchars($card['title']) ?>" loading="lazy" onerror="this.style.opacity='0'">
            </div>
          <?php elseif (count($collageProducts) >= 4): ?>
            <!-- 2x2 product collage -->
            <div class="cc-collage-grid">
              <?php foreach ($collageProducts as $cp): ?>
                <div class="cc-collage-cell">
                  <img src="<?= htmlspecialchars(asset($cp['main_image'] ?? 'assets/images/placeholder.jpg')) ?>"
                       alt="<?= htmlspecialchars($cp['name']) ?>" loading="lazy"
                       onerror="this.src='<?= asset('assets/images/placeholder.jpg') ?>'">
                </div>
              <?php endforeach; ?>
            </div>
          <?php elseif (count($collageProducts) >= 2): ?>
            <!-- 2 products side by side -->
            <div class="cc-collage-duo">
              <?php foreach (array_slice($collageProducts, 0, 2) as $cp): ?>
                <div class="cc-collage-cell">
                  <img src="<?= htmlspecialchars(asset($cp['main_image'] ?? 'assets/images/placeholder.jpg')) ?>"
                       alt="<?= htmlspecialchars($cp['name']) ?>" loading="lazy">
                </div>
              <?php endforeach; ?>
            </div>
          <?php elseif (!empty($collageProducts)): ?>
            <!-- Single product image -->
            <div class="cc-collage-full">
              <img src="<?= htmlspecialchars(asset($collageProducts[0]['main_image'] ?? 'assets/images/placeholder.jpg')) ?>"
                   alt="<?= htmlspecialchars($collageProducts[0]['name']) ?>" loading="lazy">
            </div>
          <?php else: ?>
            <!-- Placeholder -->
            <div class="cc-collage-full cc-placeholder">
              <svg fill="none" stroke="#d1d5db" viewBox="0 0 24 24" width="48" height="48">
                <rect x="3" y="3" width="18" height="18" rx="3" fill="#f3f4f6"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8.5 10a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM21 15l-5-5L5 21"/>
              </svg>
            </div>
          <?php endif; ?>
        </div>

        <!-- Card Info -->
        <div class="cc-info">
          <?php if (!empty($card['subtitle'])): ?>
            <p class="cc-subtitle"><?= htmlspecialchars($card['subtitle']) ?></p>
          <?php endif; ?>
          <h3 class="cc-title"><?= htmlspecialchars($card['title']) ?></h3>
          <?php if ($productCount > 0): ?>
            <span class="cc-count"><?= $productCount ?> products</span>
          <?php endif; ?>
        </div>

        <!-- Hover Overlay Arrow -->
        <div class="cc-hover-arrow">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" width="20" height="20">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
          </svg>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ============================================================
     THE IMPORTWALE EXPERIENCE BANNER (Premium Feature Section)
     ============================================================ -->
<div class="exp-section-wrapper" style="margin: 24px 0; position: relative;">

  <div class="importwale-experience-banner" style="position: relative; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 24px; padding: 32px 24px; text-align: center;">
    
    <!-- Main Container -->
    <div style="position: relative; z-index: 2; max-width: 1240px; margin: 0 auto;">
      
      <!-- Subtitle Badge & Main Heading -->
      <div style="margin-bottom: 32px;">
        <span class="exp-badge" style="display: inline-block; font-family: 'Inter', system-ui, sans-serif; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: #f05a29; background: #FFF5F2; padding: 6px 18px; border-radius: 99px; border: 1px solid #FDE8E0; margin-bottom: 12px;">
          Why Choose Us
        </span>
        <h2 style="font-family: 'Inter', system-ui, -apple-system, sans-serif !important; font-size: 26px; font-weight: 600; color: #0F172A; margin: 0; letter-spacing: -0.01em; line-height: 1.25;">
          The ImportWale Experience
        </h2>
      </div>

      <!-- 5 Feature Points Cards Grid -->
      <div class="exp-grid">
        
        <!-- Card 1: B2B Pricing -->
        <div class="exp-card">
          <div class="exp-icon-wrap">
            <div class="exp-icon-circle">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f05a29" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="1" x2="12" y2="23"></line>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
              </svg>
            </div>
          </div>
          <h4 class="exp-card-title">B2B Pricing</h4>
          <p class="exp-card-desc">Competitive pricing tailored for business &amp; bulk requirements.</p>
        </div>

        <!-- Card 2: Bulk Orders Welcome -->
        <div class="exp-card">
          <div class="exp-icon-wrap">
            <div class="exp-icon-circle">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f05a29" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                <line x1="12" y1="22.08" x2="12" y2="12"></line>
              </svg>
            </div>
          </div>
          <h4 class="exp-card-title">Bulk Orders Welcome</h4>
          <p class="exp-card-desc">Flexible order quantities for wholesale and growing businesses.</p>
        </div>

        <!-- Card 3: Quality Products -->
        <div class="exp-card">
          <div class="exp-icon-wrap">
            <div class="exp-icon-circle">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f05a29" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                <path d="m9 12 2 2 4-4"></path>
              </svg>
            </div>
          </div>
          <h4 class="exp-card-title">Quality Products</h4>
          <p class="exp-card-desc">Reliable, quality-verified products built for high commercial standards.</p>
        </div>

        <!-- Card 4: Easy Inquiry -->
        <div class="exp-card">
          <div class="exp-icon-wrap">
            <div class="exp-icon-circle">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f05a29" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13"></line>
                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
              </svg>
            </div>
          </div>
          <h4 class="exp-card-title">Easy Inquiry</h4>
          <p class="exp-card-desc">Directly submit requirements and get quick response from our team.</p>
        </div>

        <!-- Card 5: Business Support -->
        <div class="exp-card">
          <div class="exp-icon-wrap">
            <div class="exp-icon-circle">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#f05a29" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
              </svg>
            </div>
          </div>
          <h4 class="exp-card-title">Business Support</h4>
          <p class="exp-card-desc">Dedicated account assistance for product sourcing &amp; bulk support.</p>
        </div>

      </div>

      <!-- Action-Oriented CTA Button -->
      <div style="margin-top: 32px;">
        <a href="<?= url('catalog') ?>" class="exp-join-btn">
          <span>Explore Catalog</span>
          <svg class="exp-btn-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"></line>
            <polyline points="12 5 19 12 12 19"></polyline>
          </svg>
        </a>
      </div>

    </div>
  </div>

</div>

<!-- Styling for ImportWale Experience Section -->
<style>
.exp-grid {
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  gap: 18px;
  align-items: stretch;
  max-width: 1240px;
  margin: 0 auto;
}

.exp-card {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  background: #ffffff !important;
  border: 1px solid #E2E8F0 !important;
  border-radius: 16px !important;
  padding: 26px 18px 22px 18px !important;
  box-shadow: none !important; /* SHADOW REMOVED AS REQUESTED */
  transition: all 0.22s ease !important;
  position: relative;
  overflow: hidden;
}

.exp-card:hover {
  background: #ffffff !important;
  border-color: #CBD5E1 !important;
  transform: translateY(-3px) !important;
  box-shadow: none !important;
}

.exp-icon-wrap {
  position: relative;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.exp-icon-circle {
  width: 54px !important;
  height: 54px !important;
  border-radius: 50% !important;
  background: #FFF5F2 !important;
  border: 1px solid #FDE8E0 !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  position: relative;
  z-index: 1;
  box-shadow: none !important;
  transition: all 0.22s ease !important;
}

.exp-card:hover .exp-icon-circle {
  transform: scale(1.06) !important;
  background: #f05a29 !important;
  border-color: #f05a29 !important;
}

.exp-card:hover .exp-icon-circle svg {
  stroke: #ffffff !important;
}

.exp-card-title {
  font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
  font-size: 15px !important;
  font-weight: 700 !important;
  color: #0F172A !important;
  margin: 0 0 6px 0 !important;
  line-height: 1.3 !important;
}

.exp-card-desc {
  font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
  font-size: 12.5px !important;
  color: #64748B !important;
  margin: 0 !important;
  line-height: 1.5 !important;
}

.exp-join-btn {
  display: inline-flex !important;
  align-items: center !important;
  gap: 8px !important;
  padding: 12px 28px !important;
  background: #f05a29 !important;
  color: #ffffff !important;
  text-decoration: none !important;
  border-radius: 99px !important;
  font-family: 'Inter', system-ui, sans-serif !important;
  font-size: 14px !important;
  font-weight: 700 !important;
  box-shadow: none !important; /* NO SHADOW */
  transition: all 0.22s ease !important;
}

.exp-join-btn:hover {
  background: #d8481b !important;
  transform: translateY(-2px) !important;
}

@media (max-width: 1024px) {
  .exp-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 640px) {
  .exp-grid {
    grid-template-columns: 1fr;
  }
  .exp-card {
    padding: 20px;
  }
}
</style>

<!-- Collection Cards CSS -->
<style>
.collection-cards-section {
  margin: 0 0 24px 0;
}

.cc-header-row {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-bottom: 20px;
  gap: 12px;
}

.cc-section-title {
  font-family: 'Inter', system-ui, sans-serif !important;
  font-size: 22px !important;
  font-weight: 500 !important;
  color: #111827 !important;
  margin: 0 0 2px 0 !important;
  letter-spacing: -0.01em !important;
}

.cc-section-sub {
  font-size: 13px;
  color: #6b7280;
  margin: 0;
  font-weight: 400;
}

.cc-nav-arrows {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-shrink: 0;
}

.cc-arrow-btn {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #ffffff;
  border: 1.5px solid #e5e7eb;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #374151;
  transition: all 0.2s ease;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}

.cc-arrow-btn:hover {
  background: #111827;
  border-color: #111827;
  color: #ffffff;
  box-shadow: 0 4px 12px rgba(17,24,39,0.15);
}

.cc-track-wrapper {
  overflow: hidden;
  position: relative;
}

.cc-track {
  display: flex;
  gap: 16px;
  overflow-x: auto;
  scroll-behavior: smooth;
  scrollbar-width: none;
  -webkit-overflow-scrolling: touch;
  padding-bottom: 8px;
  scroll-snap-type: x mandatory;
}

.cc-track::-webkit-scrollbar { display: none; }

.cc-card {
  flex-shrink: 0;
  width: 220px;
  background: #f9fafb;
  border: 1.5px solid #f0f0f0;
  border-radius: 20px;
  overflow: hidden;
  text-decoration: none;
  display: flex;
  flex-direction: column;
  position: relative;
  transition: all 0.25s ease;
  cursor: pointer;
  scroll-snap-align: start;
}

.cc-card:hover {
  transform: translateY(-4px);
  box-shadow: none !important;
  border-color: #cbd5e1;
  background: #ffffff;
}

.cc-badge {
  position: absolute;
  top: 12px;
  left: 12px;
  z-index: 2;
  background: #f05a29;
  color: #ffffff;
  font-size: 9px;
  font-weight: 700;
  letter-spacing: 0.08em;
  text-transform: uppercase;
  padding: 3px 8px;
  border-radius: 20px;
  font-family: system-ui, sans-serif;
  box-shadow: 0 2px 6px rgba(240,90,41,0.35);
}

/* Collage Area */
.cc-collage {
  width: 100%;
  height: 180px;
  overflow: hidden;
  position: relative;
  background: #f0f1f3;
}

.cc-collage-full {
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.cc-collage-full img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.35s ease;
}

.cc-card:hover .cc-collage-full img {
  transform: scale(1.04);
}

.cc-collage-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: 1fr 1fr;
  width: 100%;
  height: 100%;
  gap: 2px;
}

.cc-collage-duo {
  display: grid;
  grid-template-columns: 1fr 1fr;
  width: 100%;
  height: 100%;
  gap: 2px;
}

.cc-collage-cell {
  overflow: hidden;
  background: #e5e7eb;
}

.cc-collage-cell img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.35s ease;
}

.cc-card:hover .cc-collage-cell img {
  transform: scale(1.05);
}

.cc-placeholder {
  background: #f3f4f6;
}

/* Info */
.cc-info {
  padding: 14px 16px 16px;
  flex: 1;
}

.cc-subtitle {
  font-size: 10px;
  color: #f05a29;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  margin: 0 0 4px 0;
  font-family: system-ui, sans-serif;
}

.cc-title {
  font-size: 14px;
  font-weight: 600 !important;
  color: #111827;
  margin: 0 0 6px 0;
  line-height: 1.3;
  font-family: 'Inter', system-ui, sans-serif;
}

.cc-count {
  font-size: 11px;
  color: #9ca3af;
  font-weight: 500;
  background: #f3f4f6;
  padding: 2px 8px;
  border-radius: 99px;
  display: inline-block;
}

/* Hover arrow */
.cc-hover-arrow {
  position: absolute;
  bottom: 16px;
  right: 16px;
  width: 30px;
  height: 30px;
  background: #111827;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  opacity: 0;
  transform: translateX(-4px);
  transition: all 0.22s ease;
}

.cc-card:hover .cc-hover-arrow {
  opacity: 1;
  transform: translateX(0);
}

@media (max-width: 640px) {
  .cc-card { width: 180px; }
  .cc-collage { height: 150px; }
  .cc-section-title { font-size: 20px; }
  .cc-nav-arrows { display: none; }
}
</style>

<script>
(function() {
  let ccScrollStep = 0;
  function getTrack() { return document.getElementById('ccTrack'); }

  window.scrollCollections = function(dir) {
    const track = getTrack();
    if (!track) return;
    const cardWidth = track.querySelector('.cc-card')?.offsetWidth || 236;
    track.scrollBy({ left: dir * (cardWidth + 16) * 2, behavior: 'smooth' });
  };
})();
</script>

</div><!-- /collection-cards-section -->
<?php endif; // collectionCards ?>

<!-- Featured Products Section -->
<div class="section-header-title">
  <span>Featured Wholesale Products</span>
  <a href="<?= url('catalog') ?>" style="font-size:14px; color:#f05a29; font-weight:600;">View All &rarr;</a>
</div>

<div class="product-grid">
  <?php if (!empty($featuredProducts)): ?>
    <?php foreach ($featuredProducts as $product): ?>
      <?php require __DIR__ . '/partials/product_card.php'; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Best Sellers Section -->
<div class="section-header-title">
  <span>High-Volume Best Sellers</span>
  <a href="<?= url('catalog?sort=popular') ?>" style="font-size:14px; color:#f05a29; font-weight:600;">View All &rarr;</a>
</div>

<div class="product-grid" style="margin-bottom: 24px;">
  <?php if (!empty($bestSellers)): ?>
    <?php foreach ($bestSellers as $product): ?>
      <?php require __DIR__ . '/partials/product_card.php'; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Customer Reviews / Testimonials Section (Everful Style) -->
<?php require __DIR__ . '/partials/testimonials_section.php'; ?>

<!-- Swiper 11 CSS & JS Bundle for Blog Carousel -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<!-- HOMEPAGE BLOG CAROUSEL SECTION (Placed Directly Below Reviews / Testimonials) -->
<?php if (!empty($latestArticles)): ?>
    <style>
        .swiper-articles .swiper-wrapper {
            display: flex;
            align-items: stretch;
        }
        .swiper-articles .swiper-slide {
            height: auto;
            display: flex;
            flex-shrink: 0;
        }
        @media (max-width: 639px) {
            .swiper-articles .swiper-slide { width: 85% !important; margin-right: 12px; }
        }
        @media (min-width: 640px) and (max-width: 1023px) {
            .swiper-articles .swiper-slide { width: 48% !important; margin-right: 16px; }
        }
        @media (min-width: 1024px) {
            .swiper-articles .swiper-slide { width: calc(25% - 15px) !important; margin-right: 20px; }
        }
        .swiper-articles .swiper-slide:last-child { margin-right: 0 !important; }
    </style>

    <div class="blog-carousel-section" style="margin-top: 36px; margin-bottom: 36px;">
        <!-- Section Header: Exact match with other homepage sections -->
        <div class="section-header-title">
            <span>From the ImportWale Journal</span>
            <a href="<?= url('blog') ?>" style="font-size:14px; color:#f05a29; font-weight:600; text-decoration: underline; text-underline-offset: 3px;">Visit our blog</a>
        </div>

        <!-- Articles Cards Swiper Slider with Hover Navigation Arrows -->
        <div class="relative group/carousel">
            <div class="swiper swiper-articles w-full py-1 overflow-hidden">
                <div class="swiper-wrapper">
                    <?php foreach ($latestArticles as $article): ?>
                        <div class="swiper-slide">
                            <a href="<?= url('blog/' . $article['slug']) ?>"
                                class="group block w-full h-full flex flex-col justify-between transition-all duration-300">
                                <div>
                                    <!-- Thumbnail Image Container (Fixed Height & Aspect Ratio) -->
                                    <div class="aspect-[4/3] h-44 sm:h-40 md:h-44 w-full rounded-2xl overflow-hidden bg-[#f3f4f6] relative shrink-0">
                                        <?php if (!empty($article['featured_image'])): ?>
                                            <img src="<?= asset($article['featured_image']) ?>"
                                                alt="<?= htmlspecialchars($article['featured_image_alt'] ?: $article['title']) ?>"
                                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                                onerror="this.style.display='none'; this.nextElementSibling.classList.remove('hidden');"
                                                loading="lazy">
                                            <div class="hidden w-full h-full flex items-center justify-center text-gray-400 bg-[#f3f4f6]">
                                                <svg class="w-10 h-10 opacity-30 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                            </div>
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center text-gray-400 bg-[#f3f4f6]">
                                                <svg class="w-10 h-10 opacity-30 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Article Title -->
                                    <h3 class="group-hover:text-[#f05a29] transition-colors duration-200 line-clamp-2" style="font-size: 15px; font-weight: 700; color: #111827; margin-top: 10px; line-height: 1.35;">
                                        <?= htmlspecialchars(htmlspecialchars_decode($article['title'], ENT_QUOTES), ENT_QUOTES, 'UTF-8') ?>
                                    </h3>

                                    <!-- Short Excerpt -->
                                    <p class="line-clamp-2" style="font-size: 13px; color: #6b7280; margin-top: 4px; line-height: 1.5;">
                                        <?= htmlspecialchars($article['excerpt'] ?: mb_strimwidth(strip_tags($article['content']), 0, 90, '...')) ?>
                                    </p>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Circular Navigation Arrows -->
            <button type="button" id="articles-prev"
                class="absolute left-1 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white border border-gray-200 text-gray-800 shadow-md opacity-0 group-hover/carousel:opacity-100 transition duration-300 flex items-center justify-center hover:bg-[#f05a29] hover:text-white hover:border-[#f05a29] cursor-pointer"
                aria-label="Previous">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            </button>
            <button type="button" id="articles-next"
                class="absolute right-1 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white border border-gray-200 text-gray-800 shadow-md opacity-0 group-hover/carousel:opacity-100 transition duration-300 flex items-center justify-center hover:bg-[#f05a29] hover:text-white hover:border-[#f05a29] cursor-pointer"
                aria-label="Next">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </button>
        </div>
    </div>

    <!-- Initialize Articles Swiper -->
    <script>
        function initArticlesSwiper() {
            if (typeof Swiper !== 'undefined' && document.querySelector('.swiper-articles')) {
                new Swiper('.swiper-articles', {
                    slidesPerView: 1.1,
                    spaceBetween: 12,
                    grabCursor: true,
                    navigation: {
                        nextEl: '#articles-next',
                        prevEl: '#articles-prev',
                    },
                    breakpoints: {
                        480: { slidesPerView: 2, spaceBetween: 14 },
                        768: { slidesPerView: 3, spaceBetween: 16 },
                        1024: { slidesPerView: 4, spaceBetween: 20 }
                    }
                });
            }
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initArticlesSwiper);
        } else {
            initArticlesSwiper();
        }
    </script>
<?php endif; ?>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
