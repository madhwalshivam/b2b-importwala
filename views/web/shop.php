<?php
/**
 * Everful Wholesale Rebuilt Shop View (views/web/shop.php)
 * Collapsible Accordions, Active Filter Chips with individual 'x', Live Option Counts,
 * Clean Scroll Affordance, Admin-Managed Dynamic Attributes, Server-Side Combined SQL Filtering.
 */

$pageTitle = $pageHeading ?? 'All Products';
$seoTitle = $seoOptions['title'] ?? ($pageTitle . ' Wholesale | ImportWale');
$title = $seoTitle;
$baseUrl = $seoOptions['canonical'] ?? url('shop');
$listingPathParent = $listingPathParent ?? null;
$listingPathParentUrl = $listingPathParentUrl ?? null;
$listingPathChild = $listingPathChild ?? null;

$activeCatId = (int) (!empty($activeCategory['id']) ? $activeCategory['id'] : ($filters['category_id'] ?? 0));
$activeSubId = (int) (!empty($activeSubcategory['id']) ? $activeSubcategory['id'] : ($filters['subcategory_id'] ?? 0));
$isMobileUA = (bool) preg_match('/Mobile|Android|iPhone|iPad|iPod|IEMobile|BlackBerry|Opera Mini/i', $_SERVER['HTTP_USER_AGENT'] ?? '');
$defaultPerPage = $isMobileUA ? 24 : 25;
$reqPerPage = (int) ($filters['per_page'] ?? $_GET['per_page'] ?? 0);
if ($reqPerPage === 24 || $reqPerPage === 25 || $reqPerPage <= 0) {
  $currentPerPage = $defaultPerPage;
} else {
  $currentPerPage = $reqPerPage;
}
$currentMinPrice = $filters['min_price'] ?? '';
$currentMaxPrice = $filters['max_price'] ?? '';
$currentMinMoq = $filters['min_moq'] ?? '';
$currentMaxMoq = $filters['max_moq'] ?? '';
$searchQuery = $q ?? '';
$selectedAttrs = $filters['attr'] ?? [];
$currentSort = $filters['sort'] ?? $sort ?? $_GET['sort'] ?? 'relevance';

$currentPage = max(1, (int) ($currentPage ?? 1));
$perPage = max(1, (int) ($perPage ?? $defaultPerPage));
$totalCount = $totalItems ?? 0;
$itemsCount = count($results['items'] ?? []);
$startItem = $totalCount > 0 ? (($currentPage - 1) * $perPage + 1) : 0;
$endItem = min($totalCount, $currentPage * $perPage);

$optionCounts = $results['facets']['option_counts'] ?? [];

// Calculate Active Filter Chips
$activeChips = [];

// Category chip is only shown when on generic shop page with explicitly selected category_id filter
if ($baseUrl === url('shop') && !empty($activeCategory)) {
  $activeChips[] = [
    'label' => 'Category: ' . htmlspecialchars($activeCategory['name']),
    'type' => 'category_id',
    'value' => ''
  ];
}

if ($currentMinPrice !== '' || $currentMaxPrice !== '') {
  $pLabel = 'Price: ';
  if ($currentMinPrice !== '' && $currentMaxPrice !== '') {
    $pLabel .= '₹' . $currentMinPrice . '–₹' . $currentMaxPrice;
  } elseif ($currentMinPrice !== '') {
    $pLabel .= 'Above ₹' . $currentMinPrice;
  } else {
    $pLabel .= 'Under ₹' . $currentMaxPrice;
  }
  $activeChips[] = [
    'label' => $pLabel,
    'type' => 'price',
    'value' => ''
  ];
}

if ($currentMinMoq !== '' || $currentMaxMoq !== '') {
  $mLabel = 'MOQ: ';
  if ($currentMinMoq !== '' && $currentMaxMoq !== '') {
    $mLabel .= $currentMinMoq . '–' . $currentMaxMoq . ' pcs';
  } elseif ($currentMinMoq !== '') {
    $mLabel .= $currentMinMoq . '+ pcs';
  } else {
    $mLabel .= '≤ ' . $currentMaxMoq . ' pcs';
  }
  $activeChips[] = [
    'label' => $mLabel,
    'type' => 'moq',
    'value' => ''
  ];
}

// Active chips for dynamic attributes
if (!empty($dynamicFilterAttributes) && !empty($selectedAttrs)) {
  foreach ($dynamicFilterAttributes as $attr) {
    $attrId = $attr['id'];
    $selForAttr = $selectedAttrs[$attrId] ?? [];
    if (!is_array($selForAttr)) {
      $selForAttr = array_filter(array_map('trim', explode(',', (string) $selForAttr)));
    }
    foreach ($attr['options'] as $opt) {
      if (in_array($opt['id'], $selForAttr) || in_array((string) $opt['id'], $selForAttr)) {
        $activeChips[] = [
          'label' => htmlspecialchars($attr['name']) . ': ' . htmlspecialchars($opt['value']),
          'type' => 'attr',
          'attr_id' => $attrId,
          'option_id' => $opt['id']
        ];
      }
    }
  }
}

$activeFilterCount = count($activeChips);
$sidebarOpenByDefault = false;

// Category and subcategory pages show only the open main category.
// Shop and search keep the full tree.
$sidebarScoped = !empty($activeCategory['id']);
$sidebarCategories = $categoriesTree ?? [];
if ($sidebarScoped) {
  $scopeRootId = (int) $activeCategory['id'];
  $activeParentId = (int) ($activeCategory['parent_id'] ?? 0);
  if ($activeParentId > 0) {
    $scopeRootId = $activeParentId;
  }

  $narrowed = [];
  foreach ($sidebarCategories as $treeCat) {
    if ((int) ($treeCat['id'] ?? 0) === $scopeRootId) {
      $narrowed[] = $treeCat;
      break;
    }
  }
  if ($narrowed === []) {
    $activeCategoryId = (int) $activeCategory['id'];
    foreach ($sidebarCategories as $treeCat) {
      foreach ($treeCat['subcategories'] ?? [] as $treeSub) {
        if ((int) ($treeSub['parent_id'] ?? 0) > 0 && (int) ($treeSub['id'] ?? 0) === $activeCategoryId) {
          $narrowed[] = $treeCat;
          break 2;
        }
      }
    }
  }
  if ($narrowed !== []) {
    $sidebarCategories = $narrowed;
  } else {
    $sidebarScoped = false;
  }
}

// Carry price, MOQ, attribute, and sort filters onto category links.
// Category scope stays in the path, so these links never fall back to /shop?category_id=.
$listingFilterQuery = static function (string $path): string {
  $keep = [];
  foreach (['min_price', 'max_price', 'min_moq', 'max_moq', 'sort', 'per_page'] as $key) {
    if (!isset($_GET[$key]) || $_GET[$key] === '' || $_GET[$key] === null) {
      continue;
    }
    if ($key === 'sort' && (string) $_GET[$key] === 'relevance') {
      continue;
    }
    if ($key === 'per_page' && in_array((int) $_GET[$key], [24, 25], true)) {
      continue;
    }
    $keep[$key] = $_GET[$key];
  }
  if (!empty($_GET['attr']) && is_array($_GET['attr'])) {
    $keep['attr'] = $_GET['attr'];
  }
  $qs = http_build_query($keep);
  return $path . ($qs !== '' ? ('?' . $qs) : '');
};
$shopUrl = url('shop');

ob_start();
?>

<!-- Shop Page Wrapper -->
<style>
  /* Hide filters before the sidebar HTML paints (CSS below the markup is too late). */
  #shopSidebar {
    display: none;
  }

  @media (max-width: 991px) {
    #shopSidebar {
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0;
      right: auto;
      bottom: 0;
      left: 0;
      width: min(88vw, 360px);
      max-width: 100%;
      height: 100vh;
      height: 100dvh;
      z-index: 1000001;
      transform: translateX(-105%);
      visibility: hidden;
      pointer-events: none;
    }

    #shopSidebar.is-open {
      transform: translateX(0);
      visibility: visible;
      pointer-events: auto;
    }
  }

  @media (min-width: 992px) {
    .shop-main-layout.sidebar-is-open #shopSidebar,
    #shopSidebar.is-open {
      display: block;
    }
  }

  @media (max-width: 767px) {
    .shop-page-wrapper {
      padding-left: 0 !important;
      padding-right: 0 !important;
      padding-bottom: 10px !important;
    }

    .shop-main-layout {
      flex-direction: column !important;
      gap: 16px !important;
    }

    .shop-listing-chrome {
      padding: 4px 10px 6px !important;
      min-width: 0 !important;
      overflow: hidden;
    }

    .shop-chip,
    .shop-chip-clear {
      min-height: 34px !important;
      height: 34px !important;
      font-size: 12px !important;
      border-radius: 8px !important;
    }

    .shop-active-chips {
      flex: 1 1 auto !important;
      min-width: 0 !important;
    }

    .shop-chip-clear {
      display: none !important;
    }

    .shop-top-controls-mobile {
      display: flex !important;
      align-items: center !important;
      width: auto !important;
      flex: 0 0 auto !important;
    }

    .shop-listing-head {
      flex: 0 1 auto !important;
      min-width: 0 !important;
      overflow: hidden !important;
      text-align: left !important;
      justify-content: flex-start !important;
    }

    .shop-listing-chrome:has(.shop-active-chips) .shop-listing-head {
      flex: 0 0 auto !important;
      max-width: 30% !important;
    }

    .shop-listing-h1 {
      display: inline-block !important;
      font-size: 14px !important;
      font-weight: 600 !important;
      line-height: 1.2 !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
      max-width: 100% !important;
      padding-bottom: 3px !important;
      vertical-align: bottom;
    }

    .shop-listing-h1::after {
      height: 1.5px;
    }

    .shop-chrome-btn {
      min-height: 34px !important;
      height: 34px !important;
      font-size: 12px !important;
      padding: 0 11px !important;
      gap: 5px !important;
      flex-shrink: 0 !important;
      border-radius: 8px !important;
      box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
    }

    .shop-chrome-btn svg {
      width: 13px !important;
      height: 13px !important;
    }

    #filterToggleText {
      font-size: 0 !important;
      line-height: 0 !important;
    }

    #filterToggleText::after {
      content: 'Filters';
      font-size: 12px;
      font-weight: 600;
      line-height: 1.2;
      color: inherit;
    }
  }
</style>
<div class="shop-page-wrapper"
  data-page-title="<?= htmlspecialchars($seoTitle, ENT_QUOTES, 'UTF-8') ?>"
  style="max-width: 1440px; margin: 0 auto; padding: 0 0 12px 0; font-family: 'Inter', system-ui, -apple-system, sans-serif;">

  <div class="shop-listing-chrome">
    <div class="shop-listing-head">
      <h1 class="shop-listing-h1"><?= htmlspecialchars((string) $pageTitle, ENT_QUOTES, 'UTF-8') ?></h1>
    </div>

    <?php if (!empty($activeChips)): ?>
      <div class="shop-active-chips">
        <?php foreach ($activeChips as $chip): ?>
          <span class="shop-chip">
            <span class="shop-chip-label"><?= $chip['label'] ?></span>
            <button type="button" class="shop-chip-remove"
              onclick="removeFilterChip('<?= $chip['type'] ?>', '<?= $chip['attr_id'] ?? '' ?>', '<?= $chip['option_id'] ?? '' ?>')"
              title="Remove filter" aria-label="Remove filter">&times;</button>
          </span>
        <?php endforeach; ?>
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>" class="shop-chip-clear">Clear all</a>
      </div>
    <?php endif; ?>

    <div class="shop-top-controls-mobile">
      <button type="button" id="filterToggleBtn" onclick="toggleShopSidebar()"
        class="shop-chrome-btn <?= $sidebarOpenByDefault ? 'is-open' : '' ?>">
        <span id="filterToggleIcon">
          <?php if ($sidebarOpenByDefault): ?>
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
          <?php else: ?>
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
          <?php endif; ?>
        </span>
        <span id="filterToggleText"><?= $sidebarOpenByDefault ? 'Hide Filters' : 'Show Filters' ?></span>
      </button>
    </div>

  </div>

  <!-- ============================================================
       2. MAIN LAYOUT (COLLAPSIBLE ACCORDION SIDEBAR + PRODUCT GRID)
       ============================================================ -->
  <div id="shopMainLayout"
    class="shop-main-layout <?= $sidebarOpenByDefault ? 'sidebar-is-open' : 'sidebar-is-closed' ?>"
    style="display: flex; gap: 24px; align-items: start;">

    <div id="shopFilterOverlay" class="shop-filter-overlay" onclick="closeShopFilters()" hidden></div>

    <!-- ============================================================
         LEFT SIDEBAR FILTERS (DESKTOP STICKY + MOBILE DRAWER)
         ============================================================ -->
    <aside id="shopSidebar" class="shop-sidebar <?= $sidebarOpenByDefault ? 'is-open' : '' ?>">

      <div class="shop-sidebar-head">
        <span class="shop-sidebar-head-title">Filters</span>
        <button type="button" class="shop-sidebar-close" onclick="closeShopFilters()" aria-label="Close filters">&times;</button>
      </div>

      <div class="shop-sidebar-body" id="shopSidebarBody">
      <?php if ($activeFilterCount > 0): ?>
        <div class="shop-sidebar-desktop-clear"
          style="display: flex; align-items: center; justify-content: flex-end; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
          <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>"
            style="font-size: 12.5px; font-weight: 600; color: #1e293b; text-decoration: underline;">
            Clear All
          </a>
        </div>
      <?php endif; ?>

      <!-- FILTER FORM -->
      <form id="shopFilterForm" action="<?= htmlspecialchars($baseUrl) ?>" method="GET">
        <?php if (!$sidebarScoped && !empty($filters['category_id'])): ?>
          <input type="hidden" name="category_id" value="<?= (int) $filters['category_id'] ?>">
        <?php endif; ?>
        <?php if (!$sidebarScoped && !empty($filters['subcategory_id'])): ?>
          <input type="hidden" name="subcategory_id" value="<?= (int) $filters['subcategory_id'] ?>">
        <?php endif; ?>
        <?php if (!empty($searchQuery)): ?>
          <input type="hidden" name="q" value="<?= htmlspecialchars($searchQuery) ?>">
        <?php endif; ?>
        <?php if (!empty($activeSection)): ?>
          <input type="hidden" name="section_id" value="<?= $activeSection['id'] ?>">
        <?php endif; ?>

        <!-- ACCORDION 1: CATEGORIES -->
        <div class="shop-accordion-item" style="border-bottom: 1px solid #f1f5f9; padding: 10px 0;">
          <button type="button" class="shop-accordion-header" onclick="toggleAccordion(this)"
            style="width: 100%; display: flex; align-items: center; justify-content: space-between; background: none; border: none; text-align: left; padding: 0; cursor: pointer;">
            <span style="font-size: 13px; font-weight: 500; color: #374151;">Categories</span>
            <svg class="accordion-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              style="transform: rotate(180deg); transition: transform 0.2s;">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <div class="shop-accordion-content" style="margin-top: 10px;">
            <div class="shop-scroll-area" style="display: flex; flex-direction: column; gap: 2px;">
              <?php if ($sidebarScoped): ?>
                <label
                  style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #334155; font-weight: 400; cursor: pointer; padding: 3px 0;">
                  <input type="radio" name="category_nav" value=""
                    onchange="window.location.href='<?= htmlspecialchars($shopUrl, ENT_QUOTES) ?>'"
                    style="accent-color: #f05a29; flex-shrink: 0; width: 14px; height: 14px; cursor: pointer;">
                  <a href="<?= htmlspecialchars($shopUrl) ?>" onclick="event.stopPropagation();"
                    style="color: inherit; text-decoration: none;">Back to all categories</a>
                </label>
              <?php else: ?>
                <label
                  style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: <?= empty($activeCatId) ? '#f05a29' : '#334155' ?>; font-weight: <?= empty($activeCatId) ? '600' : '400' ?>; cursor: pointer; padding: 3px 0;">
                  <input type="radio" name="category_nav" value="" <?= empty($activeCatId) ? 'checked' : '' ?>
                    onchange="window.location.href='<?= htmlspecialchars($shopUrl, ENT_QUOTES) ?>'"
                    style="accent-color: #f05a29; flex-shrink: 0; width: 14px; height: 14px; cursor: pointer;">
                  <span>All Categories</span>
                </label>
              <?php endif; ?>

              <?php if (!empty($sidebarCategories)): ?>
                <?php foreach ($sidebarCategories as $cat): ?>
                  <?php
                  $catCount = (int) ($cat['product_count'] ?? 0);
                  $isCatChecked = ($activeCatId === (int) $cat['id'] && empty($activeSubId));
                  $hasActiveSub = ($activeCatId === (int) $cat['id'] && !empty($activeSubId));
                  $catUrl = $listingFilterQuery(category_url($cat));

                  // Filter subcategories to show only those with products > 0 or currently active
                  $visibleSubcategories = [];
                  if (!empty($cat['subcategories'])) {
                    foreach ($cat['subcategories'] as $sub) {
                      $subId = (int) $sub['id'];
                      $isSubActive = ($activeSubId === $subId || (isset($activeSubcategory['id']) && (int) $activeSubcategory['id'] === $subId));
                      $subCount = (int) ($sub['product_count'] ?? 0);
                      if ($subCount > 0 || $isSubActive) {
                        $visibleSubcategories[] = $sub;
                      }
                    }
                  }

                  // Hide main category if 0 products, 0 visible subcategories, and not active
                  if ($catCount <= 0 && empty($visibleSubcategories) && !$isCatChecked && !$hasActiveSub) {
                    continue;
                  }
                  ?>
                  <div style="display: flex; flex-direction: column;">
                    <label
                      style="display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; font-size: 13px; color: <?= ($isCatChecked || $hasActiveSub) ? '#f05a29' : '#334155' ?>; font-weight: <?= ($isCatChecked || $hasActiveSub) ? '500' : '400' ?>; cursor: pointer; padding: 3px 0; line-height: 1.35;">
                      <span style="display: flex; align-items: flex-start; gap: 8px; flex: 1; min-width: 0;">
                        <input type="radio" name="category_nav" value="<?= $cat['id'] ?>" <?= $isCatChecked ? 'checked' : '' ?> onchange="window.location.href='<?= htmlspecialchars($catUrl, ENT_QUOTES) ?>'"
                          style="accent-color: #f05a29; margin-top: 2px; flex-shrink: 0; width: 14px; height: 14px; cursor: pointer;">
                        <span style="flex: 1; min-width: 0; word-break: break-word; line-height: 1.35;"
                          onclick="window.location.href='<?= htmlspecialchars($catUrl, ENT_QUOTES) ?>'"><?= htmlspecialchars($cat['name']) ?></span>
                      </span>
                    </label>

                    <?php if (!empty($visibleSubcategories)): ?>
                      <div
                        style="margin-left: 20px; padding-left: 8px; border-left: 2px solid #f1f5f9; display: flex; flex-direction: column; gap: 1px; margin-bottom: 4px;">
                        <?php foreach ($visibleSubcategories as $sub): ?>
                          <?php
                          $subId = (int) $sub['id'];
                          $isSubActive = ($activeSubId === $subId || (isset($activeSubcategory['id']) && (int) $activeSubcategory['id'] === $subId));
                          $subUrl = $listingFilterQuery(subcategory_url($cat, $sub));
                          ?>
                          <label onclick="window.location.href='<?= htmlspecialchars($subUrl, ENT_QUOTES) ?>'"
                            style="display: flex; align-items: center; justify-content: space-between; gap: 6px; font-size: 12.5px; color: <?= $isSubActive ? '#f05a29' : '#64748b' ?>; font-weight: <?= $isSubActive ? '500' : '400' ?>; cursor: pointer; padding: 2px 0;">
                            <span style="display: flex; align-items: center; gap: 6px; flex: 1; min-width: 0;">
                              <span
                                style="width: 5px; height: 5px; border-radius: 50%; background: <?= $isSubActive ? '#f05a29' : '#cbd5e1' ?>; flex-shrink: 0;"></span>
                              <a href="<?= htmlspecialchars($subUrl) ?>" onclick="event.stopPropagation();"
                                style="color: inherit; text-decoration: none; flex: 1; min-width: 0; word-break: break-word;"
                                onmouseover="this.style.color='#f05a29'"
                                onmouseout="this.style.color='<?= $isSubActive ? '#f05a29' : '#64748b' ?>'"><?= htmlspecialchars($sub['name']) ?></a>
                            </span>
                          </label>
                        <?php endforeach; ?>
                      </div>
                    <?php endif; ?>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- ACCORDION 3: WHOLESALE PRICE -->
        <div class="shop-accordion-item" style="border-bottom: 1px solid #f1f5f9; padding: 10px 0;">
          <button type="button" class="shop-accordion-header" onclick="toggleAccordion(this)"
            style="width: 100%; display: flex; align-items: center; justify-content: space-between; background: none; border: none; text-align: left; padding: 0; cursor: pointer;">
            <span style="font-size: 13px; font-weight: 500; color: #374151;">Wholesale price (₹)</span>
            <svg class="accordion-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              style="transform: rotate(180deg); transition: transform 0.2s;">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </button>

          <div class="shop-accordion-content" style="margin-top: 10px;">
            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 10px;">
              <input type="number" id="sidebarMinPrice" name="min_price"
                value="<?= htmlspecialchars((string) $currentMinPrice) ?>" placeholder="Min" min="0"
                style="width: 70px; padding: 5px 8px; font-size: 12.5px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;">
              <span style="color: #94a3b8;">–</span>
              <input type="number" id="sidebarMaxPrice" name="max_price"
                value="<?= htmlspecialchars((string) $currentMaxPrice) ?>" placeholder="Max" min="0"
                style="width: 70px; padding: 5px 8px; font-size: 12.5px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;">
              <button type="submit"
                style="padding: 5px 14px; background: #1e293b; color: #ffffff; border: none; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;">OK</button>
            </div>

            <div style="display: flex; flex-direction: column; gap: 2px;">
              <?php
              $priceOptions = [
                ['label' => 'Under ₹100', 'min' => '', 'max' => '100'],
                ['label' => '₹100–₹500', 'min' => '100', 'max' => '500'],
                ['label' => '₹500–₹1,500', 'min' => '500', 'max' => '1500'],
                ['label' => '₹1,500–₹3,000', 'min' => '1500', 'max' => '3000'],
                ['label' => '₹3,000+', 'min' => '3000', 'max' => ''],
              ];
              foreach ($priceOptions as $po):
                $isPriceSelected = ($currentMinPrice == $po['min'] && $currentMaxPrice == $po['max']);
                ?>
                <label
                  style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: <?= $isPriceSelected ? '#f05a29' : '#334155' ?>; font-weight: <?= $isPriceSelected ? '500' : '400' ?>; cursor: pointer; padding: 3px 0;">
                  <input type="checkbox" <?= $isPriceSelected ? 'checked' : '' ?>
                    onclick="setPriceValues('<?= $po['min'] ?>', '<?= $po['max'] ?>', this)"
                    style="accent-color: #f05a29; flex-shrink: 0; width: 14px; height: 14px; cursor: pointer;">
                  <span><?= $po['label'] ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- DYNAMIC ADMIN-MANAGED ATTRIBUTE ACCORDIONS -->
        <?php if (!empty($dynamicFilterAttributes)): ?>
          <?php foreach ($dynamicFilterAttributes as $attr): ?>
            <?php
            $attrId = $attr['id'];
            $selectedOptIds = $selectedAttrs[$attrId] ?? [];
            if (!is_array($selectedOptIds)) {
              $selectedOptIds = array_filter(array_map('trim', explode(',', (string) $selectedOptIds)));
            }

            // Only show options with liveCount > 0 or currently checked
            $visibleOptions = [];
            if (!empty($attr['options']) && is_array($attr['options'])) {
              foreach ($attr['options'] as $opt) {
                $optId = $opt['id'];
                $isChecked = in_array($optId, $selectedOptIds) || in_array((string) $optId, $selectedOptIds);
                $liveCount = $optionCounts[$optId] ?? 0;
                if ($liveCount > 0 || $isChecked) {
                  $visibleOptions[] = [
                    'opt' => $opt,
                    'isChecked' => $isChecked,
                    'liveCount' => $liveCount
                  ];
                }
              }
            }

            // Hide whole accordion if no options are visible
            if (empty($visibleOptions)) {
              continue;
            }
            ?>
            <div class="shop-accordion-item" style="border-bottom: 1px solid #f1f5f9; padding: 10px 0;">
              <button type="button" class="shop-accordion-header" onclick="toggleAccordion(this)"
                style="width: 100%; display: flex; align-items: center; justify-content: space-between; background: none; border: none; text-align: left; padding: 0; cursor: pointer;">
                <span
                  style="font-size: 13px; font-weight: 500; color: #374151;"><?= htmlspecialchars($attr['name']) ?></span>
                <svg class="accordion-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                  style="transform: rotate(180deg); transition: transform 0.2s;">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                </svg>
              </button>

              <div class="shop-accordion-content" style="margin-top: 10px;">
                <div class="shop-scroll-area" style="display: flex; flex-direction: column; gap: 2px;">
                  <?php foreach ($visibleOptions as $item): ?>
                    <?php
                    $opt = $item['opt'];
                    $optId = $opt['id'];
                    $isChecked = $item['isChecked'];
                    $liveCount = $item['liveCount'];
                    ?>
                    <label
                      style="display: flex; align-items: flex-start; gap: 8px; font-size: 13px; color: <?= $isChecked ? '#f05a29' : '#334155' ?>; font-weight: <?= $isChecked ? '500' : '400' ?>; cursor: pointer; padding: 3px 0; line-height: 1.35;">
                      <input type="checkbox" name="attr[<?= $attrId ?>][]" value="<?= $optId ?>" <?= $isChecked ? 'checked' : '' ?> onchange="submitShopFilterForm()"
                        style="accent-color: #f05a29; margin-top: 2px; flex-shrink: 0; width: 14px; height: 14px; cursor: pointer;">
                      <span
                        style="flex: 1; min-width: 0; word-break: break-word; line-height: 1.35;"><?= htmlspecialchars($opt['value']) ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <input type="hidden" name="min_moq" id="sidebarMinMoqInput"
          value="<?= htmlspecialchars((string) $currentMinMoq) ?>">
        <input type="hidden" name="max_moq" id="sidebarMaxMoqInput"
          value="<?= htmlspecialchars((string) $currentMaxMoq) ?>">
        <input type="hidden" name="sort" id="shopSortInput" value="<?= htmlspecialchars($currentSort) ?>">
        <input type="hidden" name="per_page" id="shopPerPageInput" value="<?= $currentPerPage ?>">
        <input type="hidden" name="page" id="shopPageInput" value="1">
      </form>
      </div>

      <div class="shop-sidebar-foot">
        <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>" class="shop-sidebar-clear-btn">Clear all</a>
        <button type="button" class="shop-sidebar-apply-btn" onclick="applyShopFiltersAndClose()">
          Show <?= number_format($totalCount) ?> results
        </button>
      </div>

    </aside>

    <!-- ============================================================
         RIGHT MAIN PRODUCT AREA (DYNAMIC GRID + PAGINATION)
         ============================================================ -->
    <main class="shop-grid-area" style="flex: 1; min-width: 0;">

      <!-- Skeleton Loading Grid -->
      <div id="shopSkeletonGrid"
        style="display: none; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px 12px; margin-bottom: 24px;">
        <?php for ($sk = 0; $sk < 8; $sk++): ?>
          <div
            style="background: #ffffff; border: 1px solid #f1f5f9; border-radius: 14px; overflow: hidden; padding: 12px; display: flex; flex-direction: column; gap: 12px;">
            <div
              style="width: 100%; aspect-ratio: 1/1; background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%); background-size: 200% 100%; animation: skeletonShimmer 1.5s infinite; border-radius: 10px;">
            </div>
            <div style="height: 14px; width: 80%; background: #e2e8f0; border-radius: 4px;"></div>
            <div style="height: 12px; width: 50%; background: #f1f5f9; border-radius: 4px;"></div>
            <div style="height: 20px; width: 40%; background: #cbd5e1; border-radius: 4px;"></div>
          </div>
        <?php endfor; ?>
      </div>

      <!-- Main Product Cards Grid -->
      <div id="shopProductGrid" class="product-grid <?= $sidebarOpenByDefault ? 'cols-4' : 'cols-5' ?>">

        <?php if (!empty($results['items'])): ?>
          <?php foreach ($results['items'] as $product): ?>
            <?php require __DIR__ . '/partials/product_card.php'; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <!-- Zero Results Empty State -->
          <div class="shop-empty-state"
            style="grid-column: 1 / -1; background: #ffffff; padding: 64px 24px; text-align: center; border-radius: 16px; border: 1px dashed #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.02); margin: 20px 0;">
            <div
              style="width: 64px; height: 64px; background: #fff7ed; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; color: #f05a29;">
              <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <h3 style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 8px 0;">No products match your
              filters</h3>
            <p
              style="font-size: 14px; color: #64748b; margin: 0 0 20px 0; max-width: 440px; margin-left: auto; margin-right: auto; line-height: 1.5;">
              We couldn't find any wholesale items matching your selected criteria. Try removing some filters to browse
              all items.
            </p>
            <a href="<?= url('shop') ?>"
              style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 24px; background: #f05a29; color: #ffffff; font-weight: 600; font-size: 13.5px; border-radius: 8px; text-decoration: none; box-shadow: 0 4px 12px rgba(240,90,41,0.25); transition: background 0.2s;"
              onmouseover="this.style.background='#d94819'" onmouseout="this.style.background='#f05a29'">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Clear All Filters
            </a>
          </div>
        <?php endif; ?>
      </div>

      <!-- ============================================================
           3. SERVER-SIDE NUMBERED PAGINATION CONTROLS
           ============================================================ -->
      <?php if ($totalPages > 1): ?>
        <?php
        $buildPageUrl = function ($targetPage) use ($baseUrl, $sidebarScoped) {
          $params = $_GET;
          unset($params['ajax'], $params['category_nav']);
          if ($sidebarScoped) {
            unset($params['category_id'], $params['subcategory_id'], $params['category']);
          }
          if (isset($params['per_page']) && ((int) $params['per_page'] === 24 || (int) $params['per_page'] === 25)) {
            unset($params['per_page']);
          }
          $params['page'] = $targetPage;
          return $baseUrl . (count($params) > 0 ? ('?' . http_build_query($params)) : '');
        };

        // Compute mobile page window (max 5 items plus arrows)
        $mobilePageWindow = (function($cur, $tot) {
          if ($tot <= 5) return range(1, $tot);
          
          if ($cur <= 2) {
              return [1, 2, 3, '...', $tot];
          } elseif ($cur >= $tot - 1) {
              return [1, '...', $tot - 2, $tot - 1, $tot];
          } else {
              return [1, '...', $cur, '...', $tot];
          }
        })($currentPage, $totalPages);
        ?>

        <!-- MOBILE PAGINATION (< 768px) -->
        <nav class="shop-pagination-wrapper shop-pagination-mobile flex md:hidden" aria-label="Mobile page navigation">
          <!-- Previous Button (Icon Only on Mobile) -->
          <?php if ($currentPage > 1): ?>
            <a href="<?= htmlspecialchars($buildPageUrl($currentPage - 1)) ?>"
              onclick="goToShopPage(event, <?= $currentPage - 1 ?>)" class="shop-page-btn shop-page-prev"
              title="Previous Page">
              <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
              </svg>
            </a>
          <?php else: ?>
            <span class="shop-page-btn disabled" title="Previous Page">
              <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
              </svg>
            </span>
          <?php endif; ?>

          <!-- Mobile Page Numbers -->
          <?php foreach ($mobilePageWindow as $p): ?>
            <?php if ($p === '...'): ?>
              <span class="shop-page-ellipsis">...</span>
            <?php elseif ((int) $p === $currentPage): ?>
              <span class="shop-page-btn active"><?= $p ?></span>
            <?php else: ?>
              <a href="<?= htmlspecialchars($buildPageUrl((int) $p)) ?>" onclick="goToShopPage(event, <?= (int) $p ?>)"
                class="shop-page-btn">
                <?= $p ?>
              </a>
            <?php endif; ?>
          <?php endforeach; ?>

          <!-- Next Button (Icon Only on Mobile) -->
          <?php if ($currentPage < $totalPages): ?>
            <a href="<?= htmlspecialchars($buildPageUrl($currentPage + 1)) ?>"
              onclick="goToShopPage(event, <?= $currentPage + 1 ?>)" class="shop-page-btn shop-page-next"
              title="Next Page">
              <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
              </svg>
            </a>
          <?php else: ?>
            <span class="shop-page-btn disabled" title="Next Page">
              <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
              </svg>
            </span>
          <?php endif; ?>
        </nav>

        <!-- DESKTOP PAGINATION (>= 768px) -->
        <nav class="shop-pagination-wrapper shop-pagination-desktop hidden md:flex" aria-label="Desktop page navigation">
          <!-- Previous Button -->
          <?php if ($currentPage > 1): ?>
            <a href="<?= htmlspecialchars($buildPageUrl($currentPage - 1)) ?>"
              onclick="goToShopPage(event, <?= $currentPage - 1 ?>)" class="shop-page-btn shop-page-prev">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              <span class="shop-btn-text">Previous</span>
            </a>
          <?php else: ?>
            <span class="shop-page-btn disabled">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              <span class="shop-btn-text">Previous</span>
            </span>
          <?php endif; ?>

          <!-- Desktop Page Numbers -->
          <?php foreach ($pageWindow as $p): ?>
            <?php if ($p === '...'): ?>
              <span class="shop-page-ellipsis">...</span>
            <?php elseif ((int) $p === $currentPage): ?>
              <span class="shop-page-btn active"><?= $p ?></span>
            <?php else: ?>
              <a href="<?= htmlspecialchars($buildPageUrl((int) $p)) ?>" onclick="goToShopPage(event, <?= (int) $p ?>)"
                class="shop-page-btn">
                <?= $p ?>
              </a>
            <?php endif; ?>
          <?php endforeach; ?>

          <!-- Next Button -->
          <?php if ($currentPage < $totalPages): ?>
            <a href="<?= htmlspecialchars($buildPageUrl($currentPage + 1)) ?>"
              onclick="goToShopPage(event, <?= $currentPage + 1 ?>)" class="shop-page-btn shop-page-next">
              <span class="shop-btn-text">Next</span>
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </a>
          <?php else: ?>
            <span class="shop-page-btn disabled">
              <span class="shop-btn-text">Next</span>
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </span>
          <?php endif; ?>

        </nav>
      <?php endif; ?>

    </main>

  </div>

</div>

<!-- Styles for Dynamic 4-col vs 5-col Grid & Custom Scrollbar -->
<style>
  /* ============================================================
     RESPONSIVE PAGINATION & GRID STYLES
     ============================================================ */
  .shop-pagination-wrapper {
    margin-top: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    flex-wrap: nowrap;
    width: 100%;
    max-width: 100%;
    padding: 0 12px;
    box-sizing: border-box;
  }

  .shop-pagination-wrapper .shop-page-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 38px;
    height: 38px;
    padding: 0 10px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 9999px;
    color: #334155;
    font-size: 13.5px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s ease;
    box-sizing: border-box;
    cursor: pointer;
  }

  .shop-pagination-wrapper .shop-page-btn:hover {
    border-color: #f05a29;
    color: #f05a29;
    background: #fff7ed;
  }

  .shop-pagination-wrapper .shop-page-btn.active {
    background: #f05a29 !important;
    border-color: #f05a29 !important;
    color: #ffffff !important;
    font-weight: 700 !important;
    box-shadow: 0 2px 6px rgba(240, 90, 41, 0.3) !important;
  }

  .shop-pagination-wrapper .shop-page-btn.disabled {
    background: #f8fafc !important;
    border-color: #e2e8f0 !important;
    color: #cbd5e1 !important;
    opacity: 0.5 !important;
    cursor: default !important;
    pointer-events: none !important;
  }

  .shop-pagination-wrapper .shop-page-ellipsis {
    width: 16px;
    min-width: 16px;
    padding: 0;
    color: #94a3b8;
    font-size: 13px;
    font-weight: 600;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    margin: 0;
  }

  @media (max-width: 767px) {
    /* Hide desktop pagination on mobile */
    .shop-pagination-desktop {
      display: none !important;
    } 

    .shop-pagination-mobile {
      margin-top: 24px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      gap: 6px !important;
      flex-wrap: nowrap !important;
      width: 100% !important;
      max-width: 100% !important;
      padding: 0 12px !important;
      box-sizing: border-box !important;
      overflow: hidden !important;
    }

    .shop-pagination-mobile .shop-page-btn {
      min-width: 36px !important;
      width: 36px !important;
      height: 36px !important;
      padding: 0 !important;
      font-size: 14px !important;
      font-weight: 600 !important;
      border-radius: 50% !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      flex-shrink: 0 !important;
    }

    .shop-pagination-mobile .shop-page-btn.shop-page-prev,
    .shop-pagination-mobile .shop-page-btn.shop-page-next,
    .shop-pagination-mobile .shop-page-btn.disabled {
      padding: 0 !important;
      min-width: 36px !important;
      width: 36px !important;
      height: 36px !important;
    }
  }

  @media (min-width: 768px) {
    /* Hide mobile pagination on desktop */
    .shop-pagination-mobile {
      display: none !important;
    }
  }

  @media (max-width: 360px) {
    .shop-pagination-mobile {
      gap: 4px !important;
      padding: 0 12px !important;
    }

    .shop-pagination-mobile .shop-page-btn,
    .shop-pagination-mobile .shop-page-btn.shop-page-prev,
    .shop-pagination-mobile .shop-page-btn.shop-page-next,
    .shop-pagination-mobile .shop-page-btn.disabled {
      min-width: 32px !important;
      width: 32px !important;
      height: 32px !important;
      font-size: 13px !important;
    }

    .shop-pagination-mobile .shop-page-ellipsis {
      width: 14px !important;
      min-width: 14px !important;
      font-size: 12px !important;
    }
  }

  .shop-page-wrapper {
    --shop-listing-chrome: 52px;
    --shop-sidebar-bottom-gap: 16px;
  }

  .shop-listing-chrome {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 10px;
    flex-wrap: nowrap;
    width: 100%;
    margin: 0;
    padding: 6px 16px 8px;
    box-sizing: border-box;
    min-width: 0;
  }

  .shop-listing-head {
    flex: 0 1 auto;
    min-width: 0;
    margin: 0;
    padding: 0;
    text-align: left;
    box-sizing: border-box;
  }

  .shop-listing-chrome:has(.shop-active-chips) .shop-listing-head {
    flex: 0 0 auto;
    max-width: 34%;
    min-width: 0;
  }

  .shop-listing-h1 {
    position: relative;
    display: block;
    margin: 0;
    max-width: 100%;
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    font-size: 22px;
    font-weight: 600;
    letter-spacing: -0.03em;
    color: #f05a29;
    line-height: 1.15;
    padding-bottom: 5px;
    box-shadow: none;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }

  .shop-listing-h1::after {
    content: '';
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    height: 2px;
    background: #f05a29;
    border-radius: 2px;
  }

  .shop-listing-path {
    display: none !important;
  }

  .shop-chrome-btn {
    font-family: 'Inter', system-ui, sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    outline: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 7px 14px;
    min-height: 36px;
    transition: border-color 0.15s ease, color 0.15s ease, background 0.15s ease, box-shadow 0.15s ease;
  }

  .shop-chrome-btn:hover {
    border-color: #f05a29;
    color: #f05a29;
  }

  .shop-chrome-btn.is-open {
    background: #fff7ed;
    border-color: #f05a29;
    color: #f05a29;
  }

  .shop-top-controls-mobile {
    flex: 0 0 auto;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: nowrap;
    min-width: 0;
    margin-left: auto;
  }

  .shop-active-chips {
    flex: 1 1 auto;
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: nowrap;
    min-width: 0;
    overflow-x: auto;
    overflow-y: hidden;
    scrollbar-width: none;
    -webkit-overflow-scrolling: touch;
  }

  .shop-active-chips::-webkit-scrollbar {
    display: none;
  }

  .shop-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    flex: 0 0 auto;
    max-width: min(280px, 70vw);
    height: 36px;
    min-height: 36px;
    padding: 0 8px 0 12px;
    box-sizing: border-box;
    font-family: 'Inter', system-ui, sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    white-space: nowrap;
  }

  .shop-chip-label {
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .shop-chip-remove {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 18px;
    height: 18px;
    padding: 0;
    margin: 0;
    border: none;
    border-radius: 999px;
    background: transparent;
    color: #64748b;
    font-size: 15px;
    font-weight: 600;
    line-height: 1;
    cursor: pointer;
  }

  .shop-chip:hover {
    border-color: #f05a29;
    color: #f05a29;
  }

  .shop-chip:hover .shop-chip-remove {
    color: #f05a29;
  }

  .shop-chip-clear {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    height: 36px;
    min-height: 36px;
    padding: 0 12px;
    box-sizing: border-box;
    font-family: 'Inter', system-ui, sans-serif;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    text-decoration: none;
    white-space: nowrap;
  }

  .shop-chip-clear:hover {
    border-color: #f05a29;
    color: #f05a29;
  }

  .shop-page-wrapper,
  .shop-main-layout,
  .shop-grid-area {
    overflow: visible;
    transform: none;
  }

  .shop-scroll-area {
    max-height: none !important;
    overflow: visible !important;
  }

  html.shop-filters-scroll-lock,
  html.shop-filters-scroll-lock body {
    overflow: hidden !important;
    overscroll-behavior: none;
  }

  .shop-filter-overlay {
    display: none;
  }

  #shopSidebar {
    background: #ffffff;
  }

  #shopSidebar,
  .shop-sidebar-body {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
  }

  #shopSidebar::-webkit-scrollbar,
  .shop-sidebar-body::-webkit-scrollbar {
    width: 5px;
  }

  #shopSidebar::-webkit-scrollbar-thumb,
  .shop-sidebar-body::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
  }

  #shopSidebar::-webkit-scrollbar-thumb:hover,
  .shop-sidebar-body::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
  }

  #shopSidebar::-webkit-scrollbar-track,
  .shop-sidebar-body::-webkit-scrollbar-track {
    background: transparent;
  }

  .shop-main-layout.sidebar-is-open .product-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
  }

  .shop-main-layout.sidebar-is-closed .product-grid {
    grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
  }

  @keyframes skeletonShimmer {
    0% {
      background-position: -200% 0;
    }

    100% {
      background-position: 200% 0;
    }
  }

  @media (min-width: 992px) {
    #shopSidebar {
      width: 250px;
      flex: 0 0 250px;
      flex-shrink: 0;
      display: none;
      box-sizing: border-box;
      padding: 8px 12px 12px 4px;
      position: sticky;
      top: calc(var(--header-height, 140px) + 16px);
      height: calc(100vh - var(--header-height, 140px) - var(--shop-listing-chrome, 52px) - var(--shop-sidebar-bottom-gap, 16px));
      height: calc(100dvh - var(--header-height, 140px) - var(--shop-listing-chrome, 52px) - var(--shop-sidebar-bottom-gap, 16px));
      max-height: calc(100vh - var(--header-height, 140px) - var(--shop-listing-chrome, 52px) - var(--shop-sidebar-bottom-gap, 16px));
      max-height: calc(100dvh - var(--header-height, 140px) - var(--shop-listing-chrome, 52px) - var(--shop-sidebar-bottom-gap, 16px));
      min-height: 0;
      overflow-x: hidden;
      overflow-y: auto;
      overscroll-behavior: contain;
      scrollbar-gutter: stable;
      align-self: flex-start;
    }

    .shop-main-layout.sidebar-is-open #shopSidebar,
    #shopSidebar.is-open {
      display: block;
    }

    .shop-sidebar-head,
    .shop-sidebar-foot,
    .shop-filter-overlay {
      display: none !important;
    }

    .shop-sidebar-body {
      overflow: visible;
      max-height: none;
    }
  }

  @media (max-width: 991px) {
    .shop-filter-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.55);
      z-index: 1000000;
      border: 0;
      padding: 0;
      margin: 0;
    }

    .shop-filter-overlay.is-open {
      display: block;
    }

    #shopSidebar {
      position: fixed;
      top: 0;
      right: auto;
      bottom: 0;
      left: 0;
      width: min(88vw, 360px);
      max-width: 100%;
      height: 100vh;
      height: 100dvh;
      max-height: 100vh;
      max-height: 100dvh;
      display: flex;
      flex-direction: column;
      z-index: 1000001;
      transform: translateX(-105%);
      visibility: hidden;
      pointer-events: none;
      transition: transform 0.22s ease, visibility 0.22s ease;
      padding: 0;
      padding-bottom: env(safe-area-inset-bottom, 0px);
      overflow: hidden;
      overscroll-behavior: contain;
      box-shadow: none;
    }

    #shopSidebar.is-open {
      transform: translateX(0);
      visibility: visible;
      pointer-events: auto;
    }

    .shop-sidebar-head {
      flex-shrink: 0;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      padding: 14px 16px;
      border-bottom: 1px solid #e2e8f0;
      background: #ffffff;
    }

    .shop-sidebar-head-title {
      font-size: 16px;
      font-weight: 500;
      color: #0f172a;
    }

    #shopSidebar .shop-sidebar-close {
      background: none;
      border: none;
      font-size: 26px;
      line-height: 1;
      color: #64748b;
      cursor: pointer;
      padding: 0 4px;
      min-height: 36px;
      min-width: 36px;
    }

    .shop-sidebar-body {
      flex: 1 1 auto;
      min-height: 0;
      overflow-x: hidden;
      overflow-y: auto;
      -webkit-overflow-scrolling: touch;
      overscroll-behavior: contain;
      padding: 8px 16px 16px;
    }

    .shop-sidebar-desktop-clear {
      display: none !important;
    }

    .shop-sidebar-foot {
      flex-shrink: 0;
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 12px 16px;
      border-top: 1px solid #e2e8f0;
      background: #ffffff;
    }

    .shop-sidebar-clear-btn {
      flex: 1;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 44px;
      padding: 10px 12px;
      border: 1px solid #cbd5e1;
      border-radius: 8px;
      color: #1e293b;
      font-size: 13.5px;
      font-weight: 600;
      text-decoration: none;
      background: #ffffff;
    }

    .shop-sidebar-apply-btn {
      flex: 1.4;
      min-height: 44px;
      padding: 10px 12px;
      border: none;
      border-radius: 8px;
      background: #f05a29;
      color: #ffffff;
      font-size: 13.5px;
      font-weight: 600;
      cursor: pointer;
    }

    .shop-main-layout.sidebar-is-open .product-grid,
    .shop-main-layout.sidebar-is-closed .product-grid {
      grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }
  }

  @media (max-width: 359px) {
    #shopSidebar {
      width: 100% !important;
    }
  }

  @media (max-width: 991px) and (min-width: 768px) {
    .shop-listing-chrome {
      padding: 6px 12px 8px;
      column-gap: 8px;
    }

    .shop-listing-h1 {
      font-size: 18px;
    }

    .shop-chrome-btn {
      padding: 6px 12px;
      font-size: 12.5px;
      min-height: 34px;
    }
  }

  @media (max-width: 767px) {
    .shop-listing-chrome {
      display: flex;
      flex-wrap: nowrap;
      align-items: center;
      justify-content: flex-start;
      padding: 4px 10px 6px;
      gap: 6px;
      min-width: 0;
      overflow: hidden;
    }

    .shop-listing-head {
      flex: 0 1 auto;
      min-width: 0;
      overflow: hidden;
      text-align: left;
      padding: 0;
    }

    .shop-listing-chrome:has(.shop-active-chips) .shop-listing-head {
      flex: 0 0 auto;
      max-width: 30%;
    }

    .shop-listing-h1 {
      display: inline-block !important;
      font-size: 14px !important;
      font-weight: 600 !important;
      line-height: 1.2 !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
      max-width: 100% !important;
      padding-bottom: 3px !important;
      vertical-align: bottom;
    }

    .shop-top-controls-mobile {
      flex: 0 0 auto;
      margin-left: auto;
    }

    .shop-active-chips {
      flex: 1 1 auto;
      min-width: 0;
    }

    .shop-chip,
    .shop-chip-clear {
      height: 34px;
      min-height: 34px;
      font-size: 12px;
      border-radius: 8px;
    }

    .shop-chip-clear {
      display: none;
    }

    .shop-main-layout.sidebar-is-open .product-grid,
    .shop-main-layout.sidebar-is-closed .product-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
  }

  @media (max-width: 1200px) and (min-width: 992px) {

    .shop-main-layout.sidebar-is-closed .product-grid,
    .shop-main-layout.sidebar-is-open .product-grid {
      grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }
  }

  @media (max-width: 768px) {
    .shop-main-layout.sidebar-is-closed .product-grid,
    .shop-main-layout.sidebar-is-open .product-grid,
    .product-grid {
      display: grid !important;
      grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
      gap: 12px !important;
      padding: 0 12px 16px !important;
      width: 100% !important;
      max-width: 100% !important;
      box-sizing: border-box !important;
      align-items: stretch !important;
    }
  }

  @media (max-width: 480px) {
    .shop-main-layout.sidebar-is-closed .product-grid,
    .shop-main-layout.sidebar-is-open .product-grid,
    .product-grid {
      display: grid !important;
      grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
      gap: 12px !important;
      padding: 0 12px 16px !important;
      width: 100% !important;
      max-width: 100% !important;
      box-sizing: border-box !important;
      align-items: stretch !important;
    }
  }
</style>

<!-- Interactivity Script -->
<script>
  function toggleAccordion(btnElem) {
    const content = btnElem.nextElementSibling;
    const arrow = btnElem.querySelector('.accordion-arrow');
    if (content) {
      const isHidden = (content.style.display === 'none');
      content.style.display = isHidden ? 'block' : 'none';
      if (arrow) {
        arrow.style.transform = isHidden ? 'rotate(180deg)' : 'rotate(0deg)';
      }
    }
  }

  function isShopFilterDrawer() {
    return window.matchMedia('(max-width: 991px)').matches;
  }

  function lockShopPageScroll() {
    if (document.documentElement.classList.contains('shop-filters-scroll-lock')) {
      return;
    }
    const y = window.scrollY || window.pageYOffset || 0;
    document.documentElement.classList.add('shop-filters-scroll-lock');
    document.body.classList.add('shop-filters-scroll-lock');
    document.body.style.position = 'fixed';
    document.body.style.top = '-' + y + 'px';
    document.body.style.left = '0';
    document.body.style.right = '0';
    document.body.style.width = '100%';
    document.body.style.overflow = 'hidden';
    document.documentElement.style.overflow = 'hidden';
    document.body.dataset.shopScrollY = String(y);
  }

  function unlockShopPageScroll() {
    const wasLocked = document.documentElement.classList.contains('shop-filters-scroll-lock')
      || document.body.classList.contains('shop-filters-scroll-lock')
      || document.body.style.position === 'fixed';
    const y = parseInt(document.body.dataset.shopScrollY || '0', 10);
    document.documentElement.classList.remove('shop-filters-scroll-lock');
    document.body.classList.remove('shop-filters-scroll-lock');
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    document.body.style.width = '';
    document.body.style.overflow = '';
    document.documentElement.style.overflow = '';
    delete document.body.dataset.shopScrollY;
    if (wasLocked && !Number.isNaN(y)) {
      window.scrollTo(0, y);
    }
  }

  function syncFilterToggleUi(isOpen) {
    const btn = document.getElementById('filterToggleBtn');
    const textSpan = document.getElementById('filterToggleText');
    const iconSpan = document.getElementById('filterToggleIcon');
    if (!btn) return;
    if (isOpen && !isShopFilterDrawer()) {
      btn.classList.add('is-open');
      if (textSpan) textSpan.innerText = 'Hide Filters';
      if (iconSpan) iconSpan.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>';
    } else {
      btn.classList.remove('is-open');
      if (textSpan) textSpan.innerText = 'Show Filters';
      if (iconSpan) iconSpan.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>';
    }
  }

  function setShopFiltersOpen(isOpen, restoreScroll) {
    const sb = document.getElementById('shopSidebar');
    const layout = document.getElementById('shopMainLayout');
    const overlay = document.getElementById('shopFilterOverlay');
    if (!sb || !layout) return;

    window.__shopFiltersOpen = !!isOpen;
    sb.classList.toggle('is-open', !!isOpen);
    if (overlay) {
      overlay.classList.toggle('is-open', !!isOpen && isShopFilterDrawer());
      overlay.hidden = !(isOpen && isShopFilterDrawer());
    }

    if (isShopFilterDrawer()) {
      layout.classList.remove('sidebar-is-open');
      layout.classList.add('sidebar-is-closed');
      if (isOpen) {
        lockShopPageScroll();
        const body = document.getElementById('shopSidebarBody');
        if (body && typeof restoreScroll === 'number') {
          body.scrollTop = restoreScroll;
        }
      } else {
        unlockShopPageScroll();
      }
    } else {
      unlockShopPageScroll();
      if (isOpen) {
        layout.classList.add('sidebar-is-open');
        layout.classList.remove('sidebar-is-closed');
      } else {
        layout.classList.remove('sidebar-is-open');
        layout.classList.add('sidebar-is-closed');
      }
    }

    syncFilterToggleUi(!!isOpen);
  }

  function openShopFilters(restoreScroll) {
    setShopFiltersOpen(true, restoreScroll);
  }

  function closeShopFilters() {
    setShopFiltersOpen(false);
  }

  function toggleShopSidebar() {
    const sb = document.getElementById('shopSidebar');
    const isOpen = !!(sb && sb.classList.contains('is-open')) || !!window.__shopFiltersOpen;
    setShopFiltersOpen(!isOpen);
  }

  function applyShopFiltersAndClose() {
    window.__shopKeepDrawerOpen = false;
    closeShopFilters();
    submitShopFilterForm();
  }

  function restoreShopFiltersAfterAjax(keepOpen, savedScroll) {
    if (keepOpen) {
      setShopFiltersOpen(true, savedScroll);
    } else {
      setShopFiltersOpen(false);
    }
    syncShopListingChrome();
  }

  function syncShopListingChrome() {
    const wrapper = document.querySelector('.shop-page-wrapper');
    const chromeEl = document.querySelector('.shop-listing-chrome');
    if (!wrapper || !chromeEl) {
      return;
    }
    const wrapCs = window.getComputedStyle(wrapper);
    const chrome = chromeEl.offsetHeight + (parseFloat(wrapCs.paddingTop) || 0);
    wrapper.style.setProperty('--shop-listing-chrome', Math.max(0, Math.round(chrome)) + 'px');
  }

  function removeFilterChip(type, attrId, optId) {
    const form = document.getElementById('shopFilterForm');
    if (type === 'category_id' || type === 'subcategory_id') {
      if (type === 'subcategory_id' && '<?= !empty($activeCategory) ? category_url($activeCategory) : '' ?>') {
        window.location.href = '<?= !empty($activeCategory) ? category_url($activeCategory) : url("shop") ?>';
        return;
      }
      window.location.href = '<?= url("shop") ?>';
      return;
    } else if (type === 'price') {
      const minI = form.querySelector('input[name="min_price"]');
      const maxI = form.querySelector('input[name="max_price"]');
      if (minI) minI.value = '';
      if (maxI) maxI.value = '';
    } else if (type === 'moq') {
      document.getElementById('sidebarMinMoqInput').value = '';
      document.getElementById('sidebarMaxMoqInput').value = '';
    } else if (type === 'attr') {
      const chk = form.querySelector(`input[name="attr[${attrId}][]"][value="${optId}"]`);
      if (chk) chk.checked = false;
    }
    document.getElementById('shopPageInput').value = 1;
    submitShopFilterForm();
  }

  function setMoqValues(minVal, maxVal, checkboxElem) {
    if (checkboxElem && !checkboxElem.checked) {
      document.getElementById('sidebarMinMoqInput').value = '';
      document.getElementById('sidebarMaxMoqInput').value = '';
    } else {
      document.getElementById('sidebarMinMoqInput').value = minVal;
      document.getElementById('sidebarMaxMoqInput').value = maxVal;
    }
    document.getElementById('shopPageInput').value = 1;
    submitShopFilterForm();
  }

  function setPriceValues(minVal, maxVal, checkboxElem) {
    if (checkboxElem && !checkboxElem.checked) {
      document.getElementById('sidebarMinPrice').value = '';
      document.getElementById('sidebarMaxPrice').value = '';
    } else {
      document.getElementById('sidebarMinPrice').value = minVal;
      document.getElementById('sidebarMaxPrice').value = maxVal;
    }
    document.getElementById('shopPageInput').value = 1;
    submitShopFilterForm();
  }

  function changeSort(sortVal) {
    document.getElementById('shopSortInput').value = sortVal;
    document.getElementById('shopPageInput').value = 1;
    submitShopFilterForm();
  }

  function goToShopPage(event, pageNum) {
    if (event) event.preventDefault();
    document.getElementById('shopPageInput').value = pageNum;
    submitShopFilterForm();
  }

  function submitShopFilterForm() {
    const form = document.getElementById('shopFilterForm');
    if (!form) return;
    const formData = new FormData(form);
    const params = new URLSearchParams();

    for (const [key, value] of formData.entries()) {
      if (key === 'category_nav') continue;
      if (value !== '' && value !== null) {
        params.append(key, value);
      }
    }

    const queryString = params.toString();
    const targetUrl = form.action + (queryString ? ('?' + queryString) : '');
    const keepOpen = window.__shopKeepDrawerOpen !== false && !!window.__shopFiltersOpen && isShopFilterDrawer();
    const bodyEl = document.getElementById('shopSidebarBody');
    const savedScroll = bodyEl ? bodyEl.scrollTop : 0;
    const keepDesktop = !isShopFilterDrawer() && !!window.__shopFiltersOpen;
    window.__shopKeepDrawerOpen = true;
    window.__shopFilterReq = (window.__shopFilterReq || 0) + 1;
    const reqId = window.__shopFilterReq;

    const grid = document.getElementById('shopProductGrid');
    const skeleton = document.getElementById('shopSkeletonGrid');
    if (grid && skeleton) {
      grid.style.opacity = '0.3';
      skeleton.style.display = 'grid';
    }

    window.history.pushState({}, '', targetUrl);

    fetch(targetUrl, {
      headers: {
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
      .then(res => {
        if (!res.ok) throw new Error('Network error');
        const contentType = res.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
          return res.json().then(data => data.html || '');
        }
        return res.text();
      })
      .then(html => {
        if (reqId !== window.__shopFilterReq) {
          return;
        }
        if (!html) {
          unlockShopPageScroll();
          window.location.href = targetUrl;
          return;
        }
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newContent = doc.querySelector('.shop-page-wrapper');
        const currentContainer = document.querySelector('.shop-page-wrapper');

        if (newContent && currentContainer) {
          const newTitle = newContent.getAttribute('data-page-title') || doc.title;
          currentContainer.innerHTML = newContent.innerHTML;
          if (newTitle) {
            document.title = newTitle;
          }
          restoreShopFiltersAfterAjax(keepOpen || keepDesktop, savedScroll);
        } else {
          unlockShopPageScroll();
          window.location.href = targetUrl;
        }
      })
      .catch(() => {
        unlockShopPageScroll();
        window.location.href = targetUrl;
      });
  }

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && window.__shopFiltersOpen && isShopFilterDrawer()) {
      closeShopFilters();
    }
  });

  window.addEventListener('resize', function () {
    if (typeof window.adjustTopHeaderSpacer === 'function') {
      window.adjustTopHeaderSpacer();
    }
    syncShopListingChrome();
    if (window.__shopFiltersOpen) {
      setShopFiltersOpen(true);
    } else if (document.documentElement.classList.contains('shop-filters-scroll-lock')) {
      unlockShopPageScroll();
    }
  });

  function initShopListingUi() {
    setShopFiltersOpen(false);
    syncShopListingChrome();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initShopListingUi);
  } else {
    initShopListingUi();
  }

  window.addEventListener('pagehide', unlockShopPageScroll);
  window.addEventListener('beforeunload', unlockShopPageScroll);
  window.addEventListener('pageshow', function () {
    setShopFiltersOpen(false);
    unlockShopPageScroll();
    syncShopListingChrome();
  });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>