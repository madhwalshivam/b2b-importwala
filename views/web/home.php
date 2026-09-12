<?php
$title = "ImportWale Wholesale | Direct Global B2B Platform";
ob_start();

// Fallback high-converting hero banners if DB banners list is empty
if (empty($heroBanners)) {
    $heroBanners = [
        [
            'title' => 'Factory Direct Wholesale Rates',
            'subtitle' => '10,000+ Trending Global Items & Low MOQ',
            'badge' => 'DIRECT WHOLESALE',
            'desktop_image' => asset('assets/images/hero-wholesale-banner.png'),
            'mobile_image'  => asset('assets/images/hero-wholesale-banner.png'),
            'link_url'      => url('catalog'),
        ],
        [
            'title' => 'New Global Arrivals & Bestsellers',
            'subtitle' => 'Up to 50% Off Direct Factory Prices',
            'badge' => 'NEW ARRIVALS',
            'desktop_image' => asset('images/hero-spooky-banner.png'),
            'mobile_image'  => asset('images/hero-spooky-banner.png'),
            'link_url'      => url('shop'),
        ],
        [
            'title' => 'Verified Global Manufacturers',
            'subtitle' => 'Connect Direct with Certified Factories',
            'badge' => 'VERIFIED FACTORIES',
            'desktop_image' => asset('assets/images/hero-wholesale-banner.png'),
            'mobile_image'  => asset('assets/images/hero-wholesale-banner.png'),
            'link_url'      => url('factories'),
        ],
    ];
}
?>

<!-- Jumia-Style Mobile & Desktop Hero Carousel (Side-Peek & Auto-Slide) -->
<style>
.jumia-hero-carousel-wrapper {
  position: relative;
  width: calc(100% + 28px);
  margin: 14px -14px 16px -14px;
  overflow: hidden;
  padding: 8px 0 12px 0;
  box-sizing: border-box;
  touch-action: pan-y;
  user-select: none;
}

/* Single Hero Banner Modifier (Fills 100% width, no peeking margins) */
.jumia-hero-carousel-wrapper.single-hero-wrapper {
  width: 100% !important;
  margin: 16px 0 18px 0 !important;
  padding: 0 !important;
}

@media (min-width: 768px) {
  .jumia-hero-carousel-wrapper {
    width: calc(100% + 48px);
    margin: 20px -24px 24px -24px;
    padding: 12px 0 16px 0;
  }
  .jumia-hero-carousel-wrapper.single-hero-wrapper {
    width: 100% !important;
    margin: 22px 0 24px 0 !important;
    padding: 0 !important;
  }
}

.jumia-hero-track {
  display: flex;
  transition: transform 380ms cubic-bezier(0.25, 1, 0.5, 1);
  will-change: transform;
  width: 100%;
}

.single-hero-wrapper .jumia-hero-track {
  width: 100% !important;
  transform: none !important;
}

.jumia-hero-slide {
  flex: 0 0 88%;
  max-width: 88%;
  margin: 0 6px;
  border-radius: 10px;
  overflow: hidden;
  position: relative;
  box-sizing: border-box;
  background: #f1f5f9;
  height: 180px;
}

.single-hero-wrapper .jumia-hero-slide {
  flex: 0 0 100% !important;
  max-width: 100% !important;
  width: 100% !important;
  margin: 0 !important;
  border-radius: 12px;
  height: auto !important;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

@media (min-width: 641px) and (max-width: 1024px) {
  .jumia-hero-slide {
    flex: 0 0 92%;
    max-width: 92%;
    height: 320px;
    border-radius: 14px;
  }
  .single-hero-wrapper .jumia-hero-slide {
    flex: 0 0 100% !important;
    max-width: 100% !important;
    width: 100% !important;
    height: auto !important;
    border-radius: 14px;
  }
}

@media (min-width: 1025px) {
  .jumia-hero-slide {
    flex: 0 0 92%;
    max-width: 92%;
    height: 440px;
    border-radius: 14px;
  }
  .single-hero-wrapper .jumia-hero-slide {
    flex: 0 0 100% !important;
    max-width: 100% !important;
    width: 100% !important;
    height: auto !important;
    border-radius: 16px;
  }
}

.jumia-hero-link {
  display: block;
  width: 100%;
  height: 100%;
  position: relative;
  text-decoration: none;
}

.jumia-hero-pic {
  display: block;
  width: 100%;
  height: 100%;
}

.jumia-hero-img {
  width: 100%;
  height: 100%;
  display: block;
  object-fit: cover;
  object-position: center;
  border-radius: inherit;
}

.single-hero-wrapper .jumia-hero-img {
  width: 100% !important;
  height: auto !important;
  max-height: 460px;
  object-fit: cover;
  display: block;
  border-radius: inherit;
}
</style>

<?php $isSingleHero = (count($heroBanners ?? []) <= 1); ?>
<div class="jumia-hero-carousel-wrapper <?= $isSingleHero ? 'single-hero-wrapper' : '' ?>">
  
  <div id="jumiaHeroTrack" class="jumia-hero-track">
    <?php foreach ($heroBanners as $idx => $banner): ?>
      <?php
        $desktopSrc = !empty($banner['desktop_image']) ? asset($banner['desktop_image']) : (\App\Models\Banner::getImageSrc($banner) ?: asset('assets/images/hero-wholesale-banner.png'));
        $mobileSrc  = !empty($banner['mobile_image']) ? asset($banner['mobile_image']) : (\App\Models\Banner::getMobileImageSrc($banner) ?: $desktopSrc);
        $linkUrl    = !empty($banner['link_url']) ? (str_starts_with($banner['link_url'], 'http') ? $banner['link_url'] : url(ltrim($banner['link_url'], '/'))) : url('catalog');
        $bTitle     = htmlspecialchars($banner['title'] ?? 'Wholesale Banner');
      ?>
      <div class="jumia-hero-slide" data-index="<?= $idx ?>">
        <a href="<?= htmlspecialchars($linkUrl) ?>" class="jumia-hero-link">
          <picture class="jumia-hero-pic">
            <source media="(max-width: 640px)" srcset="<?= htmlspecialchars($mobileSrc) ?>">
            <img src="<?= htmlspecialchars($desktopSrc) ?>" alt="<?= $bTitle ?>" class="jumia-hero-img">
          </picture>
        </a>
      </div>
    <?php endforeach; ?>
  </div>

</div>

<script>
(function() {
  let currentIndex = 0;
  const track = document.getElementById('jumiaHeroTrack');
  const wrapper = document.querySelector('.jumia-hero-carousel-wrapper');
  if (!track || !wrapper) return;

  const slides = track.children;
  const totalSlides = slides.length;
  if (totalSlides <= 1) return;

  let autoTimer = null;

  function getSlideOffset(index) {
    const isMobile = window.innerWidth <= 640;
    const slidePercent = isMobile ? 88 : 92;
    const marginPx = 6;
    const totalSlideWidthCalc = `(${slidePercent}% + ${marginPx * 2}px)`;
    const centerOffsetCalc = `((100% - ${slidePercent}%) / 2 - ${marginPx}px)`;
    return `calc(-${index} * ${totalSlideWidthCalc} + ${centerOffsetCalc})`;
  }

  function updateCarousel() {
    track.style.transform = `translateX(${getSlideOffset(currentIndex)})`;
  }

  function nextSlide() {
    currentIndex = (currentIndex + 1) % totalSlides;
    updateCarousel();
  }

  function resetAutoSlide() {
    if (autoTimer) clearInterval(autoTimer);
    autoTimer = setInterval(nextSlide, 5500);
  }

  // Touch Swipe Handling (Swipe Left / Swipe Right)
  let startX = 0;
  let startY = 0;
  let isSwiping = false;

  wrapper.addEventListener('touchstart', function(e) {
    if (!e.touches || e.touches.length === 0) return;
    startX = e.touches[0].clientX;
    startY = e.touches[0].clientY;
    isSwiping = true;
  }, { passive: true });

  wrapper.addEventListener('touchend', function(e) {
    if (!isSwiping || !e.changedTouches || e.changedTouches.length === 0) return;
    const endX = e.changedTouches[0].clientX;
    const endY = e.changedTouches[0].clientY;
    const dx = endX - startX;
    const dy = endY - startY;
    isSwiping = false;

    // Check if horizontal swipe is dominant and > 30px
    if (Math.abs(dx) > 30 && Math.abs(dx) > Math.abs(dy)) {
      if (dx < 0) {
        // Swiped Left -> Next Slide
        currentIndex = (currentIndex + 1) % totalSlides;
      } else {
        // Swiped Right -> Prev Slide
        currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
      }
      updateCarousel();
      resetAutoSlide();
    }
  }, { passive: true });

  // Handle Resize
  window.addEventListener('resize', updateCarousel);

  // Initialize
  updateCarousel();
  resetAutoSlide();
})();
</script>

<!-- JUMIA-STYLE DEALS SECTIONS (Multiple Sections Support: All Enabled Sections with section_style = deals_row) -->
<?php
$dealsRowSections = [];
$seenDealsSlugs = [];

if (!empty($homepageSections)) {
    foreach ($homepageSections as $secKey => $sec) {
        if (empty($sec)) continue;
        $status = strtolower((string)($sec['status'] ?? 'inactive'));
        if ($status !== 'active' && $status !== 'enabled') continue;

        $style = $sec['section_style'] ?? 'grid';
        $slug = !empty($sec['slug']) ? $sec['slug'] : slugify($sec['title'] ?? $secKey);

        if ($style === 'deals_row' || $slug === 'top-deals' || $secKey === 'featured_deals') {
            if (isset($seenDealsSlugs[$slug])) continue;
            $secProducts = $sec['products'] ?? [];
            if (empty($secProducts)) continue;

            $secTitle = $sec['title'] ?? ucwords(str_replace(['_', '-'], ' ', $secKey));
            $customLink = trim($sec['custom_url'] ?? $sec['custom_link'] ?? '');
            if (!empty($customLink)) {
                $viewAllUrl = (str_starts_with($customLink, 'http://') || str_starts_with($customLink, 'https://')) ? $customLink : url(ltrim($customLink, '/'));
            } else {
                $viewAllUrl = url('section/' . $slug);
            }
            $dealsRowSections[] = [
                'id' => 'sec_' . preg_replace('/[^a-z0-9]/', '_', strtolower($slug)),
                'title' => $secTitle,
                'view_all_url' => $viewAllUrl,
                'products' => $secProducts,
                'sort_order' => (int)($sec['sort_order'] ?? 1)
            ];
            $seenDealsSlugs[$slug] = true;
        }
    }
}

// Fallback for topDealsData if top-deals section wasn't in homepageSections
$topDealsSettings = $topDealsData['settings'] ?? [];
$topDealsProducts = $topDealsData['products'] ?? [];
$tdStatus = strtolower((string)($topDealsSettings['status'] ?? 'active'));
$tdSlug = !empty($topDealsSettings['slug']) ? $topDealsSettings['slug'] : 'top-deals';

if (($tdStatus === 'active' || $tdStatus === 'enabled') && !empty($topDealsProducts) && !isset($seenDealsSlugs[$tdSlug])) {
    $tdTitle = $topDealsSettings['title'] ?? 'Top Deals';
    $tdCustomUrl = trim($topDealsSettings['custom_url'] ?? '');
    if (!empty($tdCustomUrl)) {
        $tdViewAllUrl = (str_starts_with($tdCustomUrl, 'http://') || str_starts_with($tdCustomUrl, 'https://')) ? $tdCustomUrl : url(ltrim($tdCustomUrl, '/'));
    } else {
        $tdViewAllUrl = url('section/' . $tdSlug);
    }
    $dealsRowSections[] = [
        'id' => 'top_deals_standalone',
        'title' => $tdTitle,
        'view_all_url' => $tdViewAllUrl,
        'products' => $topDealsProducts,
        'sort_order' => (int)($topDealsSettings['sort_order'] ?? 1)
    ];
}

// Sort all enabled deals sections by sort_order ASC
usort($dealsRowSections, fn($a, $b) => $a['sort_order'] <=> $b['sort_order']);
?>

<?php if (!empty($dealsRowSections)): ?>
<style>
.jumia-deals-standalone-wrapper {
  position: relative;
  width: calc(100% + 28px);
  margin: 12px -14px 16px -14px;
  padding: 8px 14px 12px 14px;
  box-sizing: border-box;
  font-family: system-ui, -apple-system, sans-serif;
  background: transparent;
}
@media (min-width: 768px) {
  .jumia-deals-standalone-wrapper {
    width: calc(100% + 48px);
    margin: 16px -24px 20px -24px;
    padding: 12px 24px 16px 24px;
  }
}
.jumia-deals-row-track {
  display: flex;
  gap: 10px;
  overflow-x: auto;
  scroll-behavior: smooth;
  scrollbar-width: none;
  -ms-overflow-style: none;
  padding-bottom: 4px;
  -webkit-overflow-scrolling: touch;
}
.jumia-deals-row-track::-webkit-scrollbar {
  display: none;
}
.jumia-deals-card {
  flex: 0 0 calc(38% - 4px);
  min-width: 125px;
  max-width: 155px;
  text-decoration: none;
  color: inherit;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  border-radius: 6px;
  box-sizing: border-box;
}
@media (min-width: 640px) {
  .jumia-deals-card {
    flex: 0 0 170px;
    width: 170px;
    min-width: 170px;
    max-width: 170px;
  }
}
.jumia-deals-img-box {
  position: relative;
  width: 100%;
  aspect-ratio: 1/1;
  background: #f4f4f4;
  border-radius: 6px;
  overflow: hidden;
  margin-bottom: 6px;
}
.jumia-deals-img-box img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}
.jumia-deals-top-nav-btn {
  display: none;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.05);
  backdrop-filter: blur(4px);
  -webkit-backdrop-filter: blur(4px);
  border: none;
  color: #475569;
  box-shadow: none;
  cursor: pointer;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  padding: 0;
  margin: 0;
  flex-shrink: 0;
}
.jumia-deals-top-nav-btn svg {
  width: 12px;
  height: 12px;
}
@media (min-width: 641px) {
  .jumia-deals-top-nav-btn {
    display: inline-flex;
  }
}
.jumia-deals-top-nav-btn:hover {
  background: #f05a29;
  color: #ffffff;
}
</style>

<?php foreach ($dealsRowSections as $dSec): ?>
  <?php $trackId = 'track_' . $dSec['id']; ?>
  <div class="jumia-deals-standalone-wrapper">
    
    <!-- Section Header Row: Title on Left, See All & Top Nav Arrows on Right -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; padding: 0 2px;">
      <h2 style="font-size: 17px; font-weight: 600; color: #282828; margin: 0; line-height: 1.2;">
        <?= htmlspecialchars($dSec['title']) ?>
      </h2>

      <div style="display: flex; align-items: center; gap: 8px;">
        <a href="<?= $dSec['view_all_url'] ?>" style="font-size: 13px; font-weight: 600; color: #f05a29; text-decoration: none; display: inline-flex; align-items: center; gap: 2px;">
          <span>See All</span>
        </a>

        <!-- Top Right Navigation Arrows beside See All (Mobile Compact) -->
        <div style="display: inline-flex; align-items: center; gap: 4px;">
          <button type="button" class="jumia-deals-top-nav-btn" onclick="document.getElementById('<?= $trackId ?>').scrollBy({ left: -280, behavior: 'smooth' })" title="Scroll Left" aria-label="Previous">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="15 18 9 12 15 6"></polyline>
            </svg>
          </button>
          <button type="button" class="jumia-deals-top-nav-btn" onclick="document.getElementById('<?= $trackId ?>').scrollBy({ left: 280, behavior: 'smooth' })" title="Scroll Right" aria-label="Next">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Single Horizontal Scrollable Row -->
    <div style="position: relative;">
      <div id="<?= $trackId ?>" class="jumia-deals-row-track">
        <?php foreach ($dSec['products'] as $prod): ?>
          <?php
            $pSlug = !empty($prod['slug']) ? trim($prod['slug']) : (int)$prod['id'];
            $hasDiscount = !empty($prod['sale_price']) && $prod['price'] > $prod['sale_price'];
            $discountPercent = $hasDiscount ? round((($prod['price'] - $prod['sale_price']) / $prod['price']) * 100) : 0;
            $displayPrice = $prod['sale_price'] ?: $prod['price'];
            $mainImg = asset($prod['main_image'] ?? 'assets/images/placeholder.jpg');
          ?>
          <a href="<?= url('product/' . $pSlug) ?>" class="jumia-deals-card">
            
            <!-- Square Image Container (Full Bleed Image, Fill Entire Box) -->
            <div class="jumia-deals-img-box">
              <?php if ($hasDiscount): ?>
                <span style="position: absolute; top: 4px; right: 4px; z-index: 2; background: #fef3e6; color: #f68b1e; font-weight: 700; font-size: 11px; padding: 2px 6px; border-radius: 4px;">
                  -<?= $discountPercent ?>%
                </span>
              <?php endif; ?>
              <img src="<?= $mainImg ?>" alt="<?= htmlspecialchars($prod['name']) ?>" loading="lazy">
            </div>

            <!-- Title & Price Block -->
            <div style="display: flex; flex-direction: column; gap: 2px;">
              <h3 style="font-size: 13px; font-weight: 400; color: #282828; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.3;">
                <?= htmlspecialchars($prod['name']) ?>
              </h3>
              <div style="display: flex; flex-direction: row; align-items: baseline; gap: 6px; flex-wrap: wrap;">
                <span style="font-size: 15px; font-weight: 700; color: #282828; line-height: 1.2;">
                  <?= format_price($displayPrice) ?>
                </span>
                <?php if ($hasDiscount): ?>
                  <span style="font-size: 12px; font-weight: 400; color: #757575; text-decoration: line-through; line-height: 1.2;">
                    <?= format_price($prod['price']) ?>
                  </span>
                <?php endif; ?>
              </div>
            </div>

          </a>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
<?php endforeach; ?>
<?php endif; ?>

<!-- Featured Categories Section (EverfulWholesale UI Architecture) -->
<?php if (!empty($featuredCategories)): ?>
<div class="featured-categories-section">
  
<!-- Section Title Row -->
  <div class="feat-cats-header">
    <div>
      <h2 class="featured-cats-heading">Featured Categories</h2>
      <p class="featured-cats-subtext">Curated wholesale product categories &bull; Direct factory pricing</p>
    </div>
  </div>

  <!-- Horizontal Scrollable Pill Tabs Row (Clean Swipeable Tabs) -->
  <div class="feat-tabs-wrapper">
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
  </div>

  <!-- Subcategories Single-Row Carousel Container (4 Cards View) -->
  <div id="featSubcategoriesGrid" class="feat-subcat-grid">
    <!-- Grid Content Populated dynamically by JS -->
  </div>

  <!-- Subcategories Bottom Navigation Controls (Borderless Floating Arrows) -->
  <div class="feat-subcat-bottom-controls">
    <button type="button" class="feat-subcat-nav-btn prev" onclick="scrollFeatCards(-1)" aria-label="Previous Categories" title="Scroll Left">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0F172A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
    </button>
    <button type="button" class="feat-subcat-nav-btn next" onclick="scrollFeatCards(1)" aria-label="Next Categories" title="Scroll Right">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0F172A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
    </button>
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

/* Subcategories Bottom Carousel Navigation Controls */
.feat-subcat-bottom-controls {
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  gap: 16px !important;
  margin-top: 14px !important;
}

.feat-subcat-nav-btn {
  display: inline-flex !important;
  align-items: center !important;
  justify-content: center !important;
  background: transparent !important;
  border: none !important;
  box-shadow: none !important;
  color: #0F172A !important;
  cursor: pointer !important;
  padding: 4px !important;
  transition: opacity 0.2s ease !important;
}

.feat-subcat-nav-btn:active {
  opacity: 0.6 !important;
}

/* Scrollable Pill Tabs Wrapper */
.feat-tabs-wrapper {
  position: relative;
  display: flex;
  align-items: center;
  margin-bottom: 24px;
}

.feat-tabs-container {
  display: flex !important;
  gap: 8px !important;
  overflow-x: auto !important;
  scroll-behavior: smooth !important;
  scrollbar-width: none !important;
  padding: 4px 0 !important;
  -webkit-overflow-scrolling: touch !important;
  width: 100% !important;
  flex: 1 !important;
  min-width: 0 !important;
}

.feat-tabs-container::-webkit-scrollbar {
  display: none !important;
}

.feat-tab-btn {
  flex-shrink: 0 !important;
  white-space: nowrap !important;
  width: auto !important;
  min-width: max-content !important;
  padding: 8px 18px !important;
  border-radius: 9999px !important;
  font-size: 13px !important;
  font-weight: 600 !important;
  font-family: 'Inter', system-ui, sans-serif !important;
  cursor: pointer !important;
  border: 1px solid #E2E8F0 !important;
  background: #FFFFFF !important;
  color: #475569 !important;
  transition: all 0.2s ease !important;
  box-shadow: none !important;
}

.feat-tab-btn:hover:not(.active) {
  background: #F1F5F9 !important;
  color: #0F172A !important;
  border-color: #CBD5E1 !important;
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

@media (max-width: 768px) {
  /* Compact Section Container & Spacing */
  .featured-categories-section {
    padding: 16px 12px !important;
    border-radius: 16px !important;
    margin-bottom: 20px !important;
  }

  .feat-cats-header {
    margin-bottom: 12px !important;
  }

  .featured-cats-heading {
    font-size: 18px !important;
    line-height: 1.2 !important;
  }

  .featured-cats-subtext {
    font-size: 11.5px !important;
    margin-top: 2px !important;
  }

  /* Compact Pill Tabs Wrapper */
  .feat-tabs-wrapper {
    margin-bottom: 12px !important;
  }

  /* Compact Category Tab Pills (Fit 3-4 on screen) */
  .feat-tabs-container {
    gap: 6px !important;
    padding: 2px 0 !important;
  }

  .feat-tab-btn {
    padding: 5px 12px !important;
    font-size: 12.5px !important;
    font-weight: 500 !important;
    border-radius: 9999px !important;
  }

  .feat-tab-btn.active {
    font-weight: 600 !important;
  }

  /* Subcategory Grid & Cards */
  .feat-subcat-grid {
    gap: 10px !important;
  }

  .feat-subcat-card {
    flex: 0 0 calc((100% - 10px) / 2) !important;
    min-width: calc((100% - 10px) / 2) !important;
    padding: 8px !important;
    border-radius: 10px !important;
  }

  .feat-subcat-img-box {
    margin-bottom: 6px !important;
    padding: 3px !important;
    border-radius: 8px !important;
  }

  .feat-subcat-info {
    gap: 2px !important;
  }

  /* Proportional Medium-Weight Card Labels */
  .feat-subcat-title {
    font-size: 12.5px !important;
    font-weight: 500 !important;
    line-height: 1.3 !important;
    color: #0F172A !important;
  }

  .feat-subcat-link {
    font-size: 10.5px !important;
    font-weight: 500 !important;
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
    const viewAllUrl = '<?= url("category") ?>/' + (category.slug || encodeURIComponent(category.name.toLowerCase()));
    const catRawImg = category.main_category_image || category.image || (category.subcategories && category.subcategories.length > 0 ? category.subcategories[0].image : null);
    const catImgUrl = catRawImg ? getFullAssetUrl(catRawImg) : fallbackSvg;

    html += `
      <a href="${viewAllUrl}" class="feat-subcat-card">
        <div class="feat-subcat-img-box">
          <img src="${catImgUrl}" alt="${escapeHtml(category.name)}" class="feat-subcat-img" loading="lazy" onerror="this.onerror=null; this.src='${fallbackSvg}';">
        </div>
        <div class="feat-subcat-info">
          <h3 class="feat-subcat-title">All ${escapeHtml(category.name)}</h3>
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
            $cardLink = url('collection/' . $card['id']);
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

<!-- Dynamic Admin-Managed Homepage Sections -->
<?php if (!empty($homepageSections)): ?>
  <?php foreach ($homepageSections as $secKey => $sec): ?>
    <?php
      if (empty($sec) || (isset($sec['status']) && $sec['status'] !== 'active' && $sec['status'] !== 'enabled')) continue;
      $secSlug = !empty($sec['slug']) ? $sec['slug'] : slugify($sec['title'] ?? $secKey);
      $secStyle = $sec['section_style'] ?? 'grid';
      if ($secStyle === 'deals_row' || isset($seenDealsSlugs[$secSlug])) continue;

      $secProducts = $sec['products'] ?? [];
      if (empty($secProducts)) continue;
      $secTitle = $sec['title'] ?? ucwords(str_replace(['_', '-'], ' ', $secKey));
      $secSubtitle = $sec['subtitle'] ?? '';
      $customLink = trim($sec['custom_url'] ?? $sec['custom_link'] ?? '');
      if (!empty($customLink)) {
          $viewAllUrl = (str_starts_with($customLink, 'http://') || str_starts_with($customLink, 'https://'))
              ? $customLink
              : url(ltrim($customLink, '/'));
      } else {
          $viewAllUrl = url('section/' . $secSlug);
      }
    ?>
    <div class="section-header-title">
      <div>
        <span><?= htmlspecialchars($secTitle) ?></span>
      </div>
      <a href="<?= $viewAllUrl ?>" style="font-size:14px; color:#f05a29; font-weight:600;">View All &rarr;</a>
    </div>

    <div class="product-grid hp-section-grid" style="margin-bottom: 12px;">
      <?php foreach ($secProducts as $pIdx => $product): ?>
        <div class="hp-product-item">
          <?php require __DIR__ . '/partials/product_card.php'; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endforeach; ?>
<?php else: ?>
  <!-- Fallback if no dynamic sections configured -->
  <?php if (!empty($featuredProducts)): ?>
    <div class="section-header-title">
      <span>Featured Wholesale Products</span>
      <a href="<?= url('catalog') ?>" style="font-size:14px; color:#f05a29; font-weight:600;">View All &rarr;</a>
    </div>
    <div class="product-grid hp-section-grid" style="margin-bottom: 24px;">
      <?php foreach ($featuredProducts as $product): ?>
        <div class="hp-product-item">
          <?php require __DIR__ . '/partials/product_card.php'; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($bestSellers)): ?>
    <div class="section-header-title">
      <span>High-Volume Best Sellers</span>
      <a href="<?= url('catalog?sort=popular') ?>" style="font-size:14px; color:#f05a29; font-weight:600;">View All &rarr;</a>
    </div>
    <div class="product-grid hp-section-grid" style="margin-bottom: 24px;">
      <?php foreach ($bestSellers as $product): ?>
        <div class="hp-product-item">
          <?php require __DIR__ . '/partials/product_card.php'; ?>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
<?php endif; ?>

<!-- ============================================================
     THE IMPORTWALE EXPERIENCE BANNER (Why Choose Us Section)
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

      <!-- 5 Feature Points Cards Grid & Mobile Carousel -->
      <div class="exp-carousel-wrapper">
        <div class="exp-grid" id="expCarouselGrid">
          
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
      </div>

      <!-- Mobile Bottom Controls: Left Arrow - Dots - Right Arrow -->
      <div class="exp-bottom-controls">
        <button type="button" class="exp-nav-btn exp-nav-prev" onclick="scrollExpCarousel(-1)" aria-label="Previous Feature">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0F172A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
        </button>

        <div class="exp-dots-container" id="expDotsContainer">
          <span class="exp-dot active" onclick="goToExpSlide(0)"></span>
          <span class="exp-dot" onclick="goToExpSlide(1)"></span>
          <span class="exp-dot" onclick="goToExpSlide(2)"></span>
          <span class="exp-dot" onclick="goToExpSlide(3)"></span>
          <span class="exp-dot" onclick="goToExpSlide(4)"></span>
        </div>

        <button type="button" class="exp-nav-btn exp-nav-next" onclick="scrollExpCarousel(1)" aria-label="Next Feature">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0F172A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
        </button>
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

.exp-carousel-wrapper {
  position: relative;
  width: 100%;
}

.exp-bottom-controls {
  display: none !important;
}

@media (max-width: 1024px) and (min-width: 768px) {
  .exp-grid {
    grid-template-columns: repeat(3, 1fr);
  }
}

@media (max-width: 767px) {
  .exp-bottom-controls {
    display: none !important;
  }

  .exp-nav-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    color: #0F172A !important;
    cursor: pointer !important;
    padding: 4px !important;
    transition: opacity 0.2s ease !important;
  }
  .exp-nav-btn:active {
    opacity: 0.6 !important;
  }

  .exp-grid {
    display: flex !important;
    flex-direction: row !important;
    overflow-x: auto !important;
    scroll-behavior: smooth !important;
    scroll-snap-type: x mandatory !important;
    scrollbar-width: none !important;
    -webkit-overflow-scrolling: touch !important;
    gap: 0 !important;
    width: 100% !important;
    margin: 0 !important;
    padding: 4px 0 !important;
  }
  .exp-grid::-webkit-scrollbar {
    display: none !important;
  }

  .exp-card {
    flex: 0 0 100% !important;
    min-width: 100% !important;
    max-width: 100% !important;
    scroll-snap-align: start !important;
    box-sizing: border-box !important;
    padding: 24px 20px !important;
  }

  .exp-dots-container {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 6px !important;
    margin: 0 !important;
  }
  .exp-dot {
    width: 8px !important;
    height: 8px !important;
    border-radius: 50% !important;
    background: #CBD5E1 !important;
    transition: all 0.25s ease !important;
    cursor: pointer !important;
    display: inline-block !important;
  }
  .exp-dot.active {
    background: #f05a29 !important;
    width: 22px !important;
    border-radius: 99px !important;
  }
}
</style>

<script>
function scrollExpCarousel(direction) {
  const container = document.getElementById('expCarouselGrid');
  if (!container) return;
  const cardWidth = container.clientWidth;
  container.scrollBy({ left: direction * cardWidth, behavior: 'smooth' });
}

function goToExpSlide(index) {
  const container = document.getElementById('expCarouselGrid');
  if (!container) return;
  const cardWidth = container.clientWidth;
  container.scrollTo({ left: index * cardWidth, behavior: 'smooth' });
}

function updateExpDots() {
  const container = document.getElementById('expCarouselGrid');
  const dots = document.querySelectorAll('#expDotsContainer .exp-dot');
  if (!container || !dots.length) return;
  const scrollPos = container.scrollLeft;
  const cardWidth = container.clientWidth;
  const activeIndex = Math.round(scrollPos / cardWidth);
  dots.forEach((dot, idx) => {
    if (idx === activeIndex) {
      dot.classList.add('active');
    } else {
      dot.classList.remove('active');
    }
  });
}

document.addEventListener('DOMContentLoaded', function() {
  const container = document.getElementById('expCarouselGrid');
  if (container) {
    container.addEventListener('scroll', updateExpDots, { passive: true });
  }
});
</script>

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
