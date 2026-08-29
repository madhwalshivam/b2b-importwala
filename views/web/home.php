<?php
$title = "ImportWale Wholesale | Direct Global B2B Platform";
ob_start();
?>

<!-- Dynamic Hero Banner Slider Section -->
<div class="hero-banner-wrapper" style="margin:0 -24px 32px -24px; width:calc(100% + 48px); border-radius:0; overflow:hidden; position:relative; background:#FAF4F2;">

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
.featured-cats-heading,
.cc-section-title,
.section-header-title span {
  font-family: 'Inter', system-ui, -apple-system, sans-serif !important;
  font-size: 22px !important;
  font-weight: 700 !important;
  line-height: 1.25 !important;
  letter-spacing: -0.015em !important;
  color: #111827 !important;
  margin: 0 !important;
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
     THE IMPORTWALE EXPERIENCE BANNER (User Provided Luxury Feature Section)
     ============================================================ -->
<div class="importwale-experience-banner" style="background: linear-gradient(135deg, #f05a29 0%, #e04b1c 100%); border-radius: 0; padding: 46px 28px; margin: 36px -24px 48px -24px; width: calc(100% + 48px); color: #ffffff; text-align: center; box-shadow: 0 8px 30px rgba(240, 90, 41, 0.25);">
  
  <!-- Main Inter Heading (Reduced Size 24px) -->
  <h2 style="font-family: 'Inter', system-ui, -apple-system, sans-serif !important; font-size: 24px; font-weight: 800; color: #ffffff; margin: 0 0 28px 0; letter-spacing: -0.01em; -webkit-font-smoothing: antialiased;">
    The ImportWale Experience
  </h2>

  <!-- 5 Feature Points Grid -->
  <div class="exp-grid" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 20px; align-items: start; max-width: 1200px; margin: 0 auto 36px auto;">
    
    <!-- Point 1: B2B Pricing -->
    <div class="exp-item" style="display: flex; flex-direction: column; align-items: center; text-align: center;">
      <div class="exp-icon-circle" style="width: 56px; height: 56px; border-radius: 50%; border: 1.5px solid rgba(255, 255, 255, 0.85); display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="1" x2="12" y2="23"></line>
          <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
        </svg>
      </div>
      <h4 style="font-family: 'Inter', system-ui, sans-serif; font-size: 15px; font-weight: 700; color: #ffffff; margin: 0 0 6px 0; line-height: 1.25;">
        B2B Pricing
      </h4>
      <p style="font-family: 'Inter', system-ui, sans-serif; font-size: 12.5px; font-weight: 400; color: rgba(255, 255, 255, 0.82); margin: 0; line-height: 1.35;">
        Competitive pricing for business &amp; bulk requirements.
      </p>
    </div>

    <!-- Point 2: Bulk Orders Welcome -->
    <div class="exp-item" style="display: flex; flex-direction: column; align-items: center; text-align: center;">
      <div class="exp-icon-circle" style="width: 56px; height: 56px; border-radius: 50%; border: 1.5px solid rgba(255, 255, 255, 0.85); display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
          <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
          <line x1="12" y1="22.08" x2="12" y2="12"></line>
        </svg>
      </div>
      <h4 style="font-family: 'Inter', system-ui, sans-serif; font-size: 15px; font-weight: 700; color: #ffffff; margin: 0 0 6px 0; line-height: 1.25;">
        Bulk Orders Welcome
      </h4>
      <p style="font-family: 'Inter', system-ui, sans-serif; font-size: 12.5px; font-weight: 400; color: rgba(255, 255, 255, 0.82); margin: 0; line-height: 1.35;">
        Flexible quantities for wholesale and business requirements.
      </p>
    </div>

    <!-- Point 3: Quality Products -->
    <div class="exp-item" style="display: flex; flex-direction: column; align-items: center; text-align: center;">
      <div class="exp-icon-circle" style="width: 56px; height: 56px; border-radius: 50%; border: 1.5px solid rgba(255, 255, 255, 0.85); display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
          <path d="m9 12 2 2 4-4"></path>
        </svg>
      </div>
      <h4 style="font-family: 'Inter', system-ui, sans-serif; font-size: 15px; font-weight: 700; color: #ffffff; margin: 0 0 6px 0; line-height: 1.25;">
        Quality Products
      </h4>
      <p style="font-family: 'Inter', system-ui, sans-serif; font-size: 12.5px; font-weight: 400; color: rgba(255, 255, 255, 0.82); margin: 0; line-height: 1.35;">
        Reliable products built for regular business requirements.
      </p>
    </div>

    <!-- Point 4: Easy Inquiry -->
    <div class="exp-item" style="display: flex; flex-direction: column; align-items: center; text-align: center;">
      <div class="exp-icon-circle" style="width: 56px; height: 56px; border-radius: 50%; border: 1.5px solid rgba(255, 255, 255, 0.85); display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <line x1="22" y1="2" x2="11" y2="13"></line>
          <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
        </svg>
      </div>
      <h4 style="font-family: 'Inter', system-ui, sans-serif; font-size: 15px; font-weight: 700; color: #ffffff; margin: 0 0 6px 0; line-height: 1.25;">
        Easy Inquiry
      </h4>
      <p style="font-family: 'Inter', system-ui, sans-serif; font-size: 12.5px; font-weight: 400; color: rgba(255, 255, 255, 0.82); margin: 0; line-height: 1.35;">
        Send your requirement directly and our team will get back to you.
      </p>
    </div>

    <!-- Point 5: Business Support -->
    <div class="exp-item" style="display: flex; flex-direction: column; align-items: center; text-align: center;">
      <div class="exp-icon-circle" style="width: 56px; height: 56px; border-radius: 50%; border: 1.5px solid rgba(255, 255, 255, 0.85); display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
        </svg>
      </div>
      <h4 style="font-family: 'Inter', system-ui, sans-serif; font-size: 15px; font-weight: 700; color: #ffffff; margin: 0 0 6px 0; line-height: 1.25;">
        Business Support
      </h4>
      <p style="font-family: 'Inter', system-ui, sans-serif; font-size: 12.5px; font-weight: 400; color: rgba(255, 255, 255, 0.82); margin: 0; line-height: 1.35;">
        Dedicated support for product, quantity and bulk requirements.
      </p>
    </div>

  </div>

  <!-- Join Now Button -->
  <div>
    <a href="<?= url('catalog') ?>" class="exp-join-btn" style="display: inline-block; padding: 11px 36px; background: #ffffff; border-radius: 8px; font-family: 'Inter', system-ui, sans-serif; font-size: 14px; font-weight: 700; color: #f05a29; text-decoration: none; transition: all 0.2s ease; box-shadow: 0 4px 12px rgba(0,0,0,0.12);">
      Join Now
    </a>
  </div>

</div>

<!-- Media query styling for ImportWale Experience Section -->
<style>
.exp-join-btn:hover {
  background: #FAF4F2 !important;
  color: #d8481b !important;
  transform: translateY(-1px);
}

@media (max-width: 1024px) {
  .exp-grid {
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 24px !important;
  }
}

@media (max-width: 768px) {
  .importwale-experience-banner {
    margin-left: -16px !important;
    margin-right: -16px !important;
    width: calc(100% + 32px) !important;
    padding: 36px 18px !important;
  }
}

@media (max-width: 640px) {
  .exp-grid {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 20px 16px !important;
  }
}
</style>

<!-- Collection Cards CSS -->
<style>
.collection-cards-section {
  margin: 0 0 48px 0;
}

.cc-header-row {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  margin-bottom: 20px;
  gap: 12px;
}

.cc-section-title {
  font-family: 'Inter', system-ui, sans-serif;
  font-size: 24px;
  font-weight: 700;
  color: #111827;
  margin: 0 0 2px 0;
  letter-spacing: -0.025em;
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
  box-shadow: 0 12px 32px rgba(0,0,0,0.1);
  border-color: #e5e7eb;
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
  font-weight: 700;
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

<div class="product-grid">
  <?php if (!empty($bestSellers)): ?>
    <?php foreach ($bestSellers as $product): ?>
      <?php require __DIR__ . '/partials/product_card.php'; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
