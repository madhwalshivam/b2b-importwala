<?php
/**
 * ImportWale Wholesale Categories & Subcategories Directory View
 * (views/web/categories_directory.php)
 * Dynamic database-driven category & subcategory listing page matching website theme.
 * Mobile view features Alibaba-style 2-panel category browser layout.
 */

$title = $seoTitle ?? "All Wholesale Categories & Subcategories | ImportWale";
ob_start();
?>

<!-- ==========================================================================
     1. DESKTOP / TABLET CATEGORIES VIEW (> 768px)
     ========================================================================== -->
<div class="desktop-categories-view">
  <style>
  @media (max-width: 640px) {
    #categoriesGrid {
      grid-template-columns: 1fr !important;
      gap: 14px !important;
    }
    .categories-page-wrapper {
      padding: 10px 12px 24px 12px !important;
    }
  }
  </style>
  <div class="categories-page-wrapper" style="max-width: 1440px; margin: 0 auto; padding: 16px 20px 32px 20px; font-family: 'Inter', system-ui, -apple-system, sans-serif;">

    <!-- Breadcrumb -->
    <nav style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #64748b; margin-bottom: 16px;">
      <a href="<?= url('/') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#f05a29';" onmouseout="this.style.color='#64748b';">Home</a>
      <span style="color: #cbd5e1;">/</span>
      <span style="color: #334155; font-weight: 500;">Categories Directory</span>
    </nav>

    <!-- Hero Header Banner -->
    <div style="background: #FAF4F2; border: 1px solid #F3E5E0; border-radius: 14px; padding: 18px 22px; margin-bottom: 24px; position: relative; overflow: hidden;">
      <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 16px; position: relative; z-index: 1;">
        <div style="max-width: 680px;">
          <div style="display: inline-flex; align-items: center; gap: 5px; background: #FFF5F2; border: 1px solid #FDE8E0; padding: 2px 9px; border-radius: 9999px; font-size: 10.5px; font-weight: 600; color: #f05a29; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 6px;">
            <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
            <span>Direct Factory Catalog</span>
          </div>
          <h1 style="font-size: 21px; font-weight: 600; color: #1e293b; margin: 0 0 4px 0; letter-spacing: -0.01em; line-height: 1.3;">
            All Wholesale Categories & Subcategories
          </h1>
          <p style="font-size: 12.5px; color: #64748b; margin: 0; line-height: 1.5; font-weight: 400;">
            Browse our complete catalog of wholesale product lines. Select any main category or subcategory to view live products, factory-direct prices, and low MOQs.
          </p>
        </div>

        <!-- Quick Metrics & Search Filter Box -->
        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 10px; flex-shrink: 0;">
          <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 5px 12px; text-align: center;">
              <div style="font-size: 14px; font-weight: 700; color: #f05a29; line-height: 1;"><?= number_format($totalCategories) ?></div>
              <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.02em; margin-top: 2px;">Categories</div>
            </div>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 5px 12px; text-align: center;">
              <div style="font-size: 14px; font-weight: 700; color: #1e293b; line-height: 1;"><?= number_format($totalSubcategories) ?></div>
              <div style="font-size: 10px; font-weight: 500; color: #64748b; text-transform: uppercase; letter-spacing: 0.02em; margin-top: 2px;">Subcategories</div>
            </div>
          </div>

          <!-- Live Quick Filter Input -->
          <div style="position: relative; width: 100%; max-width: 280px;">
            <input type="text" id="categorySearchInput" onkeyup="filterCategoriesPage()" placeholder="Search category or subcategory..." style="width: 100%; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 9999px; padding: 7px 14px 7px 32px; font-size: 12px; color: #1e293b; outline: none; font-family: 'Inter', system-ui, sans-serif; transition: all 0.2s ease;" onfocus="this.style.borderColor='#f05a29';" onblur="this.style.borderColor='#cbd5e1';">
            <svg style="position: absolute; left: 11px; top: 50%; transform: translateY(-50%); width: 13px; height: 13px; color: #94a3b8; pointer-events: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          </div>
        </div>
      </div>
    </div>

    <!-- CATEGORIES GRID -->
    <?php if (!empty($categories)): ?>
      <div id="categoriesGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px; align-items: stretch;">
        <?php foreach ($categories as $cat): ?>
          <?php
            $catUrl = category_url($cat);
            $rawImg = $cat['custom_icon'] ?? $cat['image'] ?? $cat['icon'] ?? '';
            $isRealImage = !empty($rawImg) && (str_contains($rawImg, '/') || str_contains($rawImg, '.') || str_starts_with($rawImg, 'http'));
            $catImgUrl = $isRealImage ? asset($rawImg) : '';
            $subcategories = $cat['subcategories'] ?? [];
            $subCount = count($subcategories);
          ?>

          <!-- Category Card -->
          <div class="category-card-item" data-search-text="<?= htmlspecialchars(strtolower($cat['name'] . ' ' . implode(' ', array_column($subcategories, 'name')))) ?>" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; transition: all 0.2s ease;" onmouseover="this.style.borderColor='#f05a29'; this.style.boxShadow='0 4px 12px -2px rgba(0, 0, 0, 0.05)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
            
            <div>
              <!-- Header Row: Image/Icon + Name + Product Count -->
              <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px; padding-bottom: 10px; border-bottom: 1px solid #f1f5f9;">
                
                <div style="display: flex; align-items: center; gap: 10px;">
                  <!-- Category Image or Styled Icon Fallback -->
                  <a href="<?= $catUrl ?>" style="text-decoration: none; flex-shrink: 0;">
                    <?php if (!empty($catImgUrl)): ?>
                      <img src="<?= htmlspecialchars($catImgUrl) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy" style="width: 40px; height: 40px; object-fit: cover; border-radius: 10px; border: 1px solid #f1f5f9; background: #fafafa;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                      <div style="display: none; width: 40px; height: 40px; background: #FFF5F2; border: 1px solid #FDE8E0; border-radius: 10px; align-items: center; justify-content: center; color: #f05a29; font-weight: 600; font-size: 14px; text-transform: uppercase;">
                        <?= htmlspecialchars(substr($cat['name'], 0, 2)) ?>
                      </div>
                    <?php else: ?>
                      <div style="display: flex; width: 40px; height: 40px; background: #FFF5F2; border: 1px solid #FDE8E0; border-radius: 10px; align-items: center; justify-content: center; color: #f05a29; font-weight: 600; font-size: 14px; text-transform: uppercase;">
                        <?= htmlspecialchars(substr($cat['name'], 0, 2)) ?>
                      </div>
                    <?php endif; ?>
                  </a>

                  <div>
                    <h2 style="font-size: 14.5px; font-weight: 600; color: #1e293b; margin: 0 0 1px 0; line-height: 1.3;">
                      <a href="<?= $catUrl ?>" style="color: #1e293b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#f05a29';" onmouseout="this.style.color='#1e293b';">
                        <?= htmlspecialchars($cat['name']) ?>
                      </a>
                    </h2>
                    <?php if (!empty($cat['description'])): ?>
                      <p style="font-size: 11.5px; color: #64748b; margin: 0; font-weight: 400; display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden;">
                        <?= htmlspecialchars($cat['description']) ?>
                      </p>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Item Count Badge -->
                <span style="display: inline-flex; align-items: center; padding: 2px 8px; background: #fff7ed; border: 1px solid #ffedd5; color: #ea580c; border-radius: 9999px; font-size: 11px; font-weight: 500; flex-shrink: 0;">
                  <?= number_format($cat['product_count']) ?> items
                </span>
              </div>

              <!-- Subcategories Section -->
              <div style="margin-bottom: 12px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                  <span style="font-size: 10.5px; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.03em;">
                    Subcategories (<?= $subCount ?>)
                  </span>
                  <?php if ($subCount > 0): ?>
                    <span style="font-size: 10.5px; color: #cbd5e1; font-weight: 400;">• Select to filter</span>
                  <?php endif; ?>
                </div>

                <?php if (!empty($subcategories)): ?>
                  <div style="display: flex; flex-wrap: wrap; gap: 5px;">
                    <?php foreach ($subcategories as $sub): ?>
                      <?php $subUrl = subcategory_url($cat, $sub); ?>
                      <a href="<?= $subUrl ?>" style="display: inline-flex; align-items: center; gap: 3px; padding: 4px 10px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 9999px; font-size: 11.5px; font-weight: 400; color: #475569; text-decoration: none; transition: all 0.2s ease;" onmouseover="this.style.background='#fff7ed'; this.style.borderColor='#fdba74'; this.style.color='#f05a29';" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0'; this.style.color='#475569';">
                        <span><?= htmlspecialchars($sub['name']) ?></span>
                        <?php if (!empty($sub['product_count']) && $sub['product_count'] > 0): ?>
                          <span style="font-size: 10px; font-weight: 500; color: #94a3b8;">(<?= $sub['product_count'] ?>)</span>
                        <?php endif; ?>
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php else: ?>
                  <div style="font-size: 11.5px; color: #94a3b8; font-style: italic; background: #f8fafc; border: 1px dashed #e2e8f0; border-radius: 8px; padding: 6px 10px; font-weight: 400;">
                    Direct category items available
                  </div>
                <?php endif; ?>
              </div>

            </div>

            <!-- Footer Action Button -->
            <div style="padding-top: 10px; border-top: 1px solid #f1f5f9;">
              <a href="<?= $catUrl ?>" style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; color: #f05a29; text-decoration: none; transition: gap 0.2s;" onmouseover="this.style.gap='7px';" onmouseout="this.style.gap='4px';">
                <span>Browse <?= htmlspecialchars($cat['name']) ?> Wholesale</span>
                <span>&rarr;</span>
              </a>
            </div>

          </div>
        <?php endforeach; ?>
      </div>

      <!-- No Search Results Found Notice -->
      <div id="noCatResults" style="display: none; text-align: center; padding: 40px 20px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; margin-top: 16px;">
        <div style="width: 44px; height: 44px; background: #fff7ed; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; color: #f05a29; margin-bottom: 8px;">
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
        <h3 style="font-size: 15px; font-weight: 600; color: #1e293b; margin: 0 0 2px 0;">No matching category found</h3>
        <p style="font-size: 12px; color: #64748b; margin: 0;">Try searching for another keyword or clear the search bar.</p>
      </div>

    <?php else: ?>
      <!-- Empty State -->
      <div style="text-align: center; padding: 60px 20px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px;">
        <h3 style="font-size: 16px; font-weight: 600; color: #1e293b; margin: 0 0 4px 0;">No categories available</h3>
        <p style="font-size: 12.5px; color: #64748b; margin: 0 0 14px 0;">Please add active categories from the admin panel.</p>
        <a href="<?= url('shop') ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; background: #f05a29; color: #ffffff; border-radius: 9999px; font-size: 12.5px; font-weight: 600; text-decoration: none;">
          Browse All Shop Catalog
        </a>
      </div>
    <?php endif; ?>

  </div>
</div>


<!-- ==========================================================================
     2. MOBILE ALIBABA-STYLE TWO-PANEL CATEGORY BROWSER (< 768px)
     ========================================================================== -->
<div class="mobile-categories-view">
  <!-- Top Shared Header Bar -->
  <div class="mobile-cat-header">
    <h1 class="mobile-cat-header-title">Categories</h1>
  </div>

  <!-- Main Body Layout: Left Sidebar + Right Panel -->
  <div class="mobile-cat-body">

    <!-- LEFT SIDEBAR (~28% Width, Sticky/Scrollable) -->
    <div class="mobile-cat-sidebar" id="mobileCatSidebar">
      <!-- 1st Tab: For You -->
      <div class="mobile-sidebar-item active" id="tab-sidebar-foryou" onclick="switchMobileCatTab('foryou')">
        For you
      </div>

      <!-- Main Category Tabs -->
      <?php foreach ($categories as $cat): ?>
        <div class="mobile-sidebar-item" id="tab-sidebar-cat-<?= $cat['id'] ?>" onclick="switchMobileCatTab('cat-<?= $cat['id'] ?>')">
          <?= htmlspecialchars($cat['name']) ?>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- RIGHT PANEL (~72% Width, Independently Scrollable) -->
    <div class="mobile-cat-content" id="mobileCatContent">

      <!-- TAB PANEL 1: FOR YOU / RECOMMENDATIONS -->
      <div class="mobile-cat-panel active" id="panel-foryou">

        <!-- Section Title -->
        <h2 class="mobile-section-title">Recommendations</h2>

        <!-- 3-Column Recommendations Grid -->
        <div class="mobile-grid-3col">

          <!-- Recommendations from main categories & subcategories -->
          <?php
          $recCount = 0;
          foreach ($categories as $cat):
            if ($recCount >= 11) break;
            $catUrl = category_url($cat);
            $rawImg = $cat['sample_image'] ?? $cat['custom_icon'] ?? $cat['image'] ?? '';
            $isRealImage = !empty($rawImg) && (str_contains($rawImg, '/') || str_contains($rawImg, '.') || str_starts_with($rawImg, 'http'));
            $imgUrl = $isRealImage ? asset($rawImg) : '';
            $isHot = ($recCount % 2 === 1);
            $recCount++;
          ?>
            <a href="<?= $catUrl ?>" class="mobile-grid-item">
              <div class="mobile-thumb-circle">
                <?php if (!empty($imgUrl)): ?>
                  <img src="<?= htmlspecialchars($imgUrl) ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                  <div class="mobile-thumb-fallback" style="display:none;"><?= htmlspecialchars(substr($cat['name'], 0, 2)) ?></div>
                <?php else: ?>
                  <div class="mobile-thumb-fallback"><?= htmlspecialchars(substr($cat['name'], 0, 2)) ?></div>
                <?php endif; ?>
              </div>
              <span class="mobile-item-label"><?= htmlspecialchars($cat['name']) ?></span>
            </a>
          <?php endforeach; ?>
        </div>

      </div>

      <!-- TAB PANELS FOR EACH CATEGORY -->
      <?php foreach ($categories as $cat): ?>
        <?php
          $catUrl = category_url($cat);
          $subcategories = $cat['subcategories'] ?? [];
        ?>
        <div class="mobile-cat-panel" id="panel-cat-<?= $cat['id'] ?>">
          <div class="mobile-category-panel-header">
            <h2 class="mobile-category-panel-title"><?= htmlspecialchars($cat['name']) ?></h2>
            <a href="<?= $catUrl ?>" class="mobile-category-browse-link">View All &rarr;</a>
          </div>

          <?php if (!empty($subcategories)): ?>
            <!-- 3-Column Subcategory Grid -->
            <div class="mobile-grid-3col">
              <?php foreach ($subcategories as $sub): ?>
                <?php
                  $subUrl = subcategory_url($cat, $sub);
                  $subRawImg = $sub['sample_image'] ?? $sub['image'] ?? '';
                  $subIsRealImg = !empty($subRawImg) && (str_contains($subRawImg, '/') || str_contains($subRawImg, '.') || str_starts_with($subRawImg, 'http'));
                  $subImgUrl = $subIsRealImg ? asset($subRawImg) : '';
                ?>
                <a href="<?= $subUrl ?>" class="mobile-grid-item">
                  <div class="mobile-thumb-circle">
                    <?php if (!empty($subImgUrl)): ?>
                      <img src="<?= htmlspecialchars($subImgUrl) ?>" alt="<?= htmlspecialchars($sub['name']) ?>" loading="lazy" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                      <div class="mobile-thumb-fallback" style="display:none;"><?= htmlspecialchars(substr($sub['name'], 0, 2)) ?></div>
                    <?php else: ?>
                      <div class="mobile-thumb-fallback"><?= htmlspecialchars(substr($sub['name'], 0, 2)) ?></div>
                    <?php endif; ?>
                  </div>
                  <span class="mobile-item-label"><?= htmlspecialchars($sub['name']) ?></span>
                </a>
              <?php endforeach; ?>

              <!-- View All Tile (Icon Tile) -->
              <a href="<?= $catUrl ?>" class="mobile-grid-item">
                <div class="mobile-thumb-circle mobile-view-all-circle">
                  <svg width="24" height="24" fill="none" stroke="#6B7280" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                  </svg>
                </div>
                <span class="mobile-item-label" style="font-weight: 700;">View all</span>
              </a>
            </div>
          <?php else: ?>
            <!-- Fallback if no subcategories exist for this category -->
            <div style="padding: 24px 12px; text-align: center; color: #6B7280; font-size: 13px;">
              <p>Direct category items available in this line.</p>
              <a href="<?= $catUrl ?>" style="display: inline-block; margin-top: 10px; padding: 8px 16px; background: #F05A29; color: #fff; border-radius: 20px; font-weight: 600; text-decoration: none; font-size: 12px;">Browse <?= htmlspecialchars($cat['name']) ?></a>
            </div>
          <?php endif; ?>

          <!-- Get Product Inspiration Header & Products -->
          <?php if (!empty($inspirationProducts)): ?>
            <h3 class="mobile-inspiration-title">Get product inspiration</h3>
            <div class="mobile-inspiration-grid">
              <?php foreach ($inspirationProducts as $ip): ?>
                <?php $ipUrl = url('product/' . $ip['slug']); ?>
                <a href="<?= $ipUrl ?>" class="mobile-inspiration-card">
                  <div class="mobile-inspiration-img-wrap">
                    <img src="<?= asset($ip['main_image']) ?>" alt="<?= htmlspecialchars($ip['name']) ?>" loading="lazy">
                  </div>
                  <div class="mobile-inspiration-name"><?= htmlspecialchars($ip['name']) ?></div>
                  <div class="mobile-inspiration-price">
                    ₹<?= number_format($ip['sale_price'] ?? $ip['base_price'] ?? $ip['price'] ?? 0, 2) ?>
                  </div>
                  <?php if (!empty($ip['moq'])): ?>
                    <div class="mobile-inspiration-moq">MOQ: <?= $ip['moq'] ?> pcs</div>
                  <?php endif; ?>
                </a>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

    </div>
  </div>
</div>

<!-- Help Modal for Mobile Categories Page -->
<div class="mobile-cat-help-modal" id="categoryHelpModal" onclick="closeCategoryHelpModal(event)">
  <div class="mobile-cat-help-box" onclick="event.stopPropagation()">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #E5E7EB;">
      <h3 style="font-size: 16px; font-weight: 700; color: #111827; margin: 0;">Category Navigation</h3>
      <button type="button" onclick="closeCategoryHelpModal()" style="background: none; border: none; font-size: 18px; cursor: pointer; color: #9CA3AF;">✕</button>
    </div>
    <div style="font-size: 13px; color: #4B5563; line-height: 1.5;">
      <p style="margin-bottom: 8px;"><strong>• Tap any category on the left sidebar</strong> to view its subcategories and recommendations.</p>
      <p style="margin-bottom: 8px;"><strong>• Tap "For you"</strong> for curated trending items across all factory-direct product lines.</p>
      <p style="margin: 0;"><strong>• Tap "View all"</strong> inside any category grid to explore the full catalog page.</p>
    </div>
    <button type="button" onclick="closeCategoryHelpModal()" style="width: 100%; margin-top: 16px; padding: 10px; background: #F05A29; color: #FFF; border: none; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;">Got it</button>
  </div>
</div>

<!-- Client-side Interactive Scripts -->
<script>
function filterCategoriesPage() {
  const query = (document.getElementById('categorySearchInput').value || '').toLowerCase().trim();
  const items = document.querySelectorAll('.category-card-item');
  let visibleCount = 0;

  items.forEach(item => {
    const searchText = item.getAttribute('data-search-text') || '';
    if (!query || searchText.includes(query)) {
      item.style.display = 'flex';
      visibleCount++;
    } else {
      item.style.display = 'none';
    }
  });

  const noRes = document.getElementById('noCatResults');
  if (noRes) {
    noRes.style.display = (visibleCount === 0 && query.length > 0) ? 'block' : 'none';
  }
}

function switchMobileCatTab(tabId) {
  // Update sidebar active states
  const sidebarItems = document.querySelectorAll('.mobile-sidebar-item');
  sidebarItems.forEach(item => item.classList.remove('active'));

  const targetTab = document.getElementById('tab-sidebar-' + tabId);
  if (targetTab) {
    targetTab.classList.add('active');
  }

  // Update right panel active states
  const panels = document.querySelectorAll('.mobile-cat-panel');
  panels.forEach(panel => panel.classList.remove('active'));

  const targetPanel = document.getElementById('panel-' + tabId);
  if (targetPanel) {
    targetPanel.classList.add('active');
  }

  // Reset right panel scroll to top
  const contentContainer = document.getElementById('mobileCatContent');
  if (contentContainer) {
    contentContainer.scrollTop = 0;
  }
}

function openCategoryHelpModal() {
  const modal = document.getElementById('categoryHelpModal');
  if (modal) modal.classList.add('active');
}

function closeCategoryHelpModal(e) {
  const modal = document.getElementById('categoryHelpModal');
  if (modal) modal.classList.remove('active');
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
