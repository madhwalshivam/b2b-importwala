<?php
/**
 * Everful Wholesale Rebuilt Shop View (views/web/shop.php)
 * Collapsible Accordions, Active Filter Chips with individual 'x', Live Option Counts,
 * Clean Scroll Affordance, Admin-Managed Dynamic Attributes, Server-Side Combined SQL Filtering.
 */

$pageTitle = $pageHeading ?? "All Wholesale Products";
$seoTitle = $seoOptions['title'] ?? ($pageTitle . " | ImportWale");
$title = $seoTitle;

$activeCatId = (int)($filters['category_id'] ?? 0);
$activeSubId = (int)($filters['subcategory_id'] ?? 0);
$currentSort = $filters['sort'] ?? 'relevance';
$currentPerPage = (int)($filters['per_page'] ?? 24);
$currentMinPrice = $filters['min_price'] ?? '';
$currentMaxPrice = $filters['max_price'] ?? '';
$currentMinMoq = $filters['min_moq'] ?? '';
$currentMaxMoq = $filters['max_moq'] ?? '';
$searchQuery = $q ?? '';
$selectedAttrs = $filters['attr'] ?? [];

$totalCount = $totalItems ?? 0;
$itemsCount = count($results['items'] ?? []);
$startItem = $totalCount > 0 ? (($currentPage - 1) * $perPage + 1) : 0;
$endItem = min($totalCount, $currentPage * $perPage);

$optionCounts = $results['facets']['option_counts'] ?? [];

// Calculate Active Filter Chips
$activeChips = [];

if (!empty($activeCategory)) {
    $activeChips[] = [
        'label' => 'Category: ' . htmlspecialchars($activeCategory['name']),
        'type'  => 'category_id',
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
        'type'  => 'price',
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
        'type'  => 'moq',
        'value' => ''
    ];
}

// Active chips for dynamic attributes
if (!empty($dynamicFilterAttributes) && !empty($selectedAttrs)) {
    foreach ($dynamicFilterAttributes as $attr) {
        $attrId = $attr['id'];
        $selForAttr = $selectedAttrs[$attrId] ?? [];
        if (!is_array($selForAttr)) {
            $selForAttr = array_filter(array_map('trim', explode(',', (string)$selForAttr)));
        }
        foreach ($attr['options'] as $opt) {
            if (in_array($opt['id'], $selForAttr) || in_array((string)$opt['id'], $selForAttr)) {
                $activeChips[] = [
                    'label' => htmlspecialchars($attr['name']) . ': ' . htmlspecialchars($opt['value']),
                    'type'  => 'attr',
                    'attr_id' => $attrId,
                    'option_id' => $opt['id']
                ];
            }
        }
    }
}

$activeFilterCount = count($activeChips);
$sidebarOpenByDefault = ($activeFilterCount > 0);

ob_start();
?>

<!-- Shop Page Wrapper -->
<div class="shop-page-wrapper" style="max-width: 1440px; margin: 0 auto; padding: 16px 20px 12px 20px; font-family: 'Inter', system-ui, -apple-system, sans-serif;">

  <!-- ============================================================
       1. TOP CONTROL BAR (TOGGLE BTN + CHIPS + RESULT COUNT + SORT)
       ============================================================ -->
  <div class="shop-top-bar" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; padding: 8px 0;">
    
    <!-- Left: Filter Toggle Button -->
    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
      <button type="button" id="filterToggleBtn" onclick="toggleShopSidebar()" class="<?= $sidebarOpenByDefault ? 'is-open' : '' ?>" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 20px; border-radius: 9999px; font-size: 13.5px; font-weight: 600; cursor: pointer; transition: all 0.2s ease; <?= $sidebarOpenByDefault ? 'background: #1e293b; color: #ffffff; border: 1px solid #1e293b;' : 'background: #ffffff; color: #1e293b; border: 1px solid #cbd5e1;' ?>">
        <span id="filterToggleIcon">
          <?php if ($sidebarOpenByDefault): ?>
            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
          <?php else: ?>
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
          <?php endif; ?>
        </span>
        <span id="filterToggleText"><?= $sidebarOpenByDefault ? 'Hide Filters' : 'Show Filters' ?></span>
      </button>

      <!-- REMOVABLE FILTER CHIPS/TAGS -->
      <?php if (!empty($activeChips)): ?>
        <div class="shop-active-chips" style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
          <?php foreach ($activeChips as $chip): ?>
            <span class="shop-chip" style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; background: #fff7ed; border: 1px solid #fdba74; color: #ea580c; border-radius: 9999px; font-size: 12px; font-weight: 600;">
              <span><?= $chip['label'] ?></span>
              <button type="button" onclick="removeFilterChip('<?= $chip['type'] ?>', '<?= $chip['attr_id'] ?? '' ?>', '<?= $chip['option_id'] ?? '' ?>')" style="background: none; border: none; color: #ea580c; font-size: 14px; font-weight: 700; cursor: pointer; padding: 0 2px; line-height: 1;" title="Remove filter">&times;</button>
            </span>
          <?php endforeach; ?>

          <a href="<?= url('shop') ?>" style="font-size: 12.5px; font-weight: 600; color: #ef4444; text-decoration: none; margin-left: 4px;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
            Clear All
          </a>
        </div>
      <?php endif; ?>

    </div>

    <!-- Right: Result Count + Sort Dropdown -->
    <div style="display: flex; align-items: center; gap: 16px;">
      <span style="font-size: 13.5px; color: #64748b; font-weight: 500; white-space: nowrap;">
        <?= $startItem ?>-<?= $endItem ?> of <?= number_format($totalCount) ?> results
      </span>

      <div style="position: relative; display: inline-block;">
        <select id="sortSelect" onchange="changeSort(this.value)" style="appearance: none; -webkit-appearance: none; background: #ffffff; color: #0f172a; font-size: 13px; font-weight: 600; padding: 7px 34px 7px 16px; border-radius: 9999px; border: 1px solid #cbd5e1; cursor: pointer; outline: none; font-family: 'Inter', system-ui, sans-serif;">
          <option value="relevance" <?= $currentSort === 'relevance' ? 'selected' : '' ?>>Featured</option>
          <option value="price_asc" <?= ($currentSort === 'price_asc' || $currentSort === 'price_low_high') ? 'selected' : '' ?>>Price: Low to High</option>
          <option value="price_desc" <?= ($currentSort === 'price_desc' || $currentSort === 'price_high_low') ? 'selected' : '' ?>>Price: High to Low</option>
          <option value="newest" <?= $currentSort === 'newest' ? 'selected' : '' ?>>Newest</option>
          <option value="popular" <?= $currentSort === 'popular' ? 'selected' : '' ?>>Best Selling</option>
        </select>
        <svg style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); width: 14px; height: 14px; color: #64748b; pointer-events: none;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
      </div>
    </div>

  </div>

  <!-- ============================================================
       2. MAIN LAYOUT (COLLAPSIBLE ACCORDION SIDEBAR + PRODUCT GRID)
       ============================================================ -->
  <div id="shopMainLayout" class="shop-main-layout <?= $sidebarOpenByDefault ? 'sidebar-is-open' : 'sidebar-is-closed' ?>" style="display: flex; gap: 24px; align-items: start;">

    <!-- ============================================================
         LEFT SIDEBAR FILTERS (SINGLE SIDEBAR SCROLL + VIEWPORT FIT)
         ============================================================ -->
    <aside id="shopSidebar" style="width: 250px; flex-shrink: 0; display: <?= $sidebarOpenByDefault ? 'block' : 'none' ?>; background: #ffffff; padding: 4px 10px 12px 0; position: sticky; top: 80px; max-height: calc(100vh - 95px); overflow-y: auto; overscroll-behavior: contain;">
      
      <!-- Sidebar Header -->
      <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px solid #f1f5f9;">
        <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">
          <?= $activeFilterCount ?> filter<?= $activeFilterCount !== 1 ? 's' : '' ?> applied
        </span>
        <a href="<?= url('shop') ?>" style="font-size: 12.5px; font-weight: 600; color: #1e293b; text-decoration: underline;">
          Clear All
        </a>
      </div>

      <!-- FILTER FORM -->
      <form id="shopFilterForm" action="<?= url('shop') ?>" method="GET">
        <?php if (!empty($searchQuery)): ?>
          <input type="hidden" name="q" value="<?= htmlspecialchars($searchQuery) ?>">
        <?php endif; ?>

        <!-- ACCORDION 1: CATEGORIES -->
        <div class="shop-accordion-item" style="border-bottom: 1px solid #f1f5f9; padding: 10px 0;">
          <button type="button" class="shop-accordion-header" onclick="toggleAccordion(this)" style="width: 100%; display: flex; align-items: center; justify-content: space-between; background: none; border: none; text-align: left; padding: 0; cursor: pointer;">
            <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">Categories</span>
            <svg class="accordion-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transform: rotate(180deg); transition: transform 0.2s;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
          </button>
          
          <div class="shop-accordion-content" style="margin-top: 10px;">
            <div class="shop-scroll-area" style="display: flex; flex-direction: column; gap: 2px;">
              <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: <?= empty($activeCatId) ? '#f05a29' : '#334155' ?>; font-weight: <?= empty($activeCatId) ? '600' : '400' ?>; cursor: pointer; padding: 3px 0;">
                <input type="radio" name="category_id" value="" <?= empty($activeCatId) ? 'checked' : '' ?> onchange="this.form.submit()" style="accent-color: #f05a29; flex-shrink: 0; width: 14px; height: 14px; cursor: pointer;">
                <span>All Categories</span>
              </label>

              <?php if (!empty($categoriesTree)): ?>
                <?php foreach ($categoriesTree as $cat): ?>
                  <?php $isCatChecked = ($activeCatId === (int)$cat['id']); ?>
                  <label style="display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; font-size: 13px; color: <?= $isCatChecked ? '#f05a29' : '#334155' ?>; font-weight: <?= $isCatChecked ? '600' : '400' ?>; cursor: pointer; padding: 3px 0; line-height: 1.35;">
                    <span style="display: flex; align-items: flex-start; gap: 8px; flex: 1; min-width: 0;">
                      <input type="radio" name="category_id" value="<?= $cat['id'] ?>" <?= $isCatChecked ? 'checked' : '' ?> onchange="this.form.submit()" style="accent-color: #f05a29; margin-top: 2px; flex-shrink: 0; width: 14px; height: 14px; cursor: pointer;">
                      <span style="flex: 1; min-width: 0; word-break: break-word; line-height: 1.35;"><?= htmlspecialchars($cat['name']) ?></span>
                    </span>
                    <span style="flex-shrink: 0; font-size: 11px; color: #94a3b8; font-weight: 500; background: #f8fafc; padding: 1px 6px; border-radius: 9999px; border: 1px solid #e2e8f0; margin-left: 4px; display: inline-block; min-width: 18px; text-align: center; margin-top: 1px;"><?= $cat['product_count'] ?? 0 ?></span>
                  </label>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- ACCORDION 2: MIN MOQ -->
        <div class="shop-accordion-item" style="border-bottom: 1px solid #f1f5f9; padding: 10px 0;">
          <button type="button" class="shop-accordion-header" onclick="toggleAccordion(this)" style="width: 100%; display: flex; align-items: center; justify-content: space-between; background: none; border: none; text-align: left; padding: 0; cursor: pointer;">
            <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">Min MOQ</span>
            <svg class="accordion-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transform: rotate(180deg); transition: transform 0.2s;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
          </button>

          <div class="shop-accordion-content" style="margin-top: 10px;">
            <div style="display: flex; flex-direction: column; gap: 2px;">
              <?php
              $moqOptions = [
                ['label' => 'No MOQ', 'min' => '', 'max' => '1'],
                ['label' => '2–5 pcs', 'min' => '2', 'max' => '5'],
                ['label' => '6–10 pcs', 'min' => '6', 'max' => '10'],
                ['label' => '10–25 pcs', 'min' => '10', 'max' => '25'],
                ['label' => '25+ pcs', 'min' => '25', 'max' => ''],
              ];
              foreach ($moqOptions as $mo):
                $isMoqSelected = ($currentMinMoq == $mo['min'] && $currentMaxMoq == $mo['max']);
              ?>
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: <?= $isMoqSelected ? '#f05a29' : '#334155' ?>; font-weight: <?= $isMoqSelected ? '600' : '400' ?>; cursor: pointer; padding: 3px 0;">
                  <input type="checkbox" <?= $isMoqSelected ? 'checked' : '' ?> onclick="setMoqValues('<?= $mo['min'] ?>', '<?= $mo['max'] ?>', this)" style="accent-color: #f05a29; flex-shrink: 0; width: 14px; height: 14px; cursor: pointer;">
                  <span><?= $mo['label'] ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <!-- ACCORDION 3: WHOLESALE PRICE -->
        <div class="shop-accordion-item" style="border-bottom: 1px solid #f1f5f9; padding: 10px 0;">
          <button type="button" class="shop-accordion-header" onclick="toggleAccordion(this)" style="width: 100%; display: flex; align-items: center; justify-content: space-between; background: none; border: none; text-align: left; padding: 0; cursor: pointer;">
            <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">Wholesale price (₹)</span>
            <svg class="accordion-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transform: rotate(180deg); transition: transform 0.2s;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
          </button>

          <div class="shop-accordion-content" style="margin-top: 10px;">
            <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 10px;">
              <input type="number" id="sidebarMinPrice" name="min_price" value="<?= htmlspecialchars((string)$currentMinPrice) ?>" placeholder="Min" min="0" style="width: 70px; padding: 5px 8px; font-size: 12.5px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;">
              <span style="color: #94a3b8;">–</span>
              <input type="number" id="sidebarMaxPrice" name="max_price" value="<?= htmlspecialchars((string)$currentMaxPrice) ?>" placeholder="Max" min="0" style="width: 70px; padding: 5px 8px; font-size: 12.5px; border: 1px solid #cbd5e1; border-radius: 6px; outline: none;">
              <button type="submit" style="padding: 5px 14px; background: #1e293b; color: #ffffff; border: none; border-radius: 6px; font-size: 12px; font-weight: 700; cursor: pointer;">OK</button>
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
                <label style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: <?= $isPriceSelected ? '#f05a29' : '#334155' ?>; font-weight: <?= $isPriceSelected ? '600' : '400' ?>; cursor: pointer; padding: 3px 0;">
                  <input type="checkbox" <?= $isPriceSelected ? 'checked' : '' ?> onclick="setPriceValues('<?= $po['min'] ?>', '<?= $po['max'] ?>', this)" style="accent-color: #f05a29; flex-shrink: 0; width: 14px; height: 14px; cursor: pointer;">
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
                $selectedOptIds = array_filter(array_map('trim', explode(',', (string)$selectedOptIds)));
            }
            ?>
            <div class="shop-accordion-item" style="border-bottom: 1px solid #f1f5f9; padding: 10px 0;">
              <button type="button" class="shop-accordion-header" onclick="toggleAccordion(this)" style="width: 100%; display: flex; align-items: center; justify-content: space-between; background: none; border: none; text-align: left; padding: 0; cursor: pointer;">
                <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;"><?= htmlspecialchars($attr['name']) ?></span>
                <svg class="accordion-arrow" width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transform: rotate(180deg); transition: transform 0.2s;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
              </button>

              <div class="shop-accordion-content" style="margin-top: 10px;">
                <div class="shop-scroll-area" style="display: flex; flex-direction: column; gap: 2px;">
                  <?php foreach ($attr['options'] as $opt): ?>
                    <?php
                    $optId = $opt['id'];
                    $isChecked = in_array($optId, $selectedOptIds) || in_array((string)$optId, $selectedOptIds);
                    $liveCount = $optionCounts[$optId] ?? 0;
                    ?>
                    <label style="display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; font-size: 13px; color: <?= $isChecked ? '#f05a29' : '#334155' ?>; font-weight: <?= $isChecked ? '600' : '400' ?>; cursor: pointer; padding: 3px 0; line-height: 1.35;">
                      <span style="display: flex; align-items: flex-start; gap: 8px; flex: 1; min-width: 0;">
                        <input type="checkbox" name="attr[<?= $attrId ?>][]" value="<?= $optId ?>" <?= $isChecked ? 'checked' : '' ?> onchange="this.form.submit()" style="accent-color: #f05a29; margin-top: 2px; flex-shrink: 0; width: 14px; height: 14px; cursor: pointer;">
                        <span style="flex: 1; min-width: 0; word-break: break-word; line-height: 1.35;"><?= htmlspecialchars($opt['value']) ?></span>
                      </span>
                      <span style="flex-shrink: 0; font-size: 11px; color: #94a3b8; font-weight: 500; background: #f8fafc; padding: 1px 6px; border-radius: 9999px; border: 1px solid #e2e8f0; margin-left: 4px; display: inline-block; min-width: 18px; text-align: center; margin-top: 1px;"><?= $liveCount ?></span>
                    </label>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <input type="hidden" name="min_moq" id="sidebarMinMoqInput" value="<?= htmlspecialchars((string)$currentMinMoq) ?>">
        <input type="hidden" name="max_moq" id="sidebarMaxMoqInput" value="<?= htmlspecialchars((string)$currentMaxMoq) ?>">
        <input type="hidden" name="sort" id="shopSortInput" value="<?= htmlspecialchars($currentSort) ?>">
        <input type="hidden" name="per_page" id="shopPerPageInput" value="<?= $currentPerPage ?>">
        <input type="hidden" name="page" id="shopPageInput" value="1">
      </form>

    </aside>

    <!-- ============================================================
         RIGHT MAIN PRODUCT AREA (DYNAMIC GRID + PAGINATION)
         ============================================================ -->
    <main class="shop-grid-area" style="flex: 1; min-width: 0;">

      <!-- Skeleton Loading Grid -->
      <div id="shopSkeletonGrid" style="display: none; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px 12px; margin-bottom: 24px;">
        <?php for ($sk = 0; $sk < 8; $sk++): ?>
          <div style="background: #ffffff; border: 1px solid #f1f5f9; border-radius: 14px; overflow: hidden; padding: 12px; display: flex; flex-direction: column; gap: 12px;">
            <div style="width: 100%; aspect-ratio: 1/1; background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%); background-size: 200% 100%; animation: skeletonShimmer 1.5s infinite; border-radius: 10px;"></div>
            <div style="height: 14px; width: 80%; background: #e2e8f0; border-radius: 4px;"></div>
            <div style="height: 12px; width: 50%; background: #f1f5f9; border-radius: 4px;"></div>
            <div style="height: 20px; width: 40%; background: #cbd5e1; border-radius: 4px;"></div>
          </div>
        <?php endfor; ?>
      </div>

      <!-- Main Product Cards Grid -->
      <div id="shopProductGrid" class="product-grid <?= $sidebarOpenByDefault ? 'cols-4' : 'cols-5' ?>" style="display: grid; gap: 16px 12px; width: 100%;">
        <?php if (!empty($results['items'])): ?>
          <?php foreach ($results['items'] as $product): ?>
            <?php require __DIR__ . '/partials/product_card.php'; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <!-- Zero Results Empty State -->
          <div class="shop-empty-state" style="grid-column: 1 / -1; background: #ffffff; padding: 64px 24px; text-align: center; border-radius: 16px; border: 1px dashed #cbd5e1; box-shadow: 0 2px 8px rgba(0,0,0,0.02); margin: 20px 0;">
            <div style="width: 64px; height: 64px; background: #fff7ed; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; color: #f05a29;">
              <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <h3 style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 8px 0;">No products match your filters</h3>
            <p style="font-size: 14px; color: #64748b; margin: 0 0 20px 0; max-width: 440px; margin-left: auto; margin-right: auto; line-height: 1.5;">
              We couldn't find any wholesale items matching your selected criteria. Try removing some filters to browse all items.
            </p>
            <a href="<?= url('shop') ?>" style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 24px; background: #f05a29; color: #ffffff; font-weight: 600; font-size: 13.5px; border-radius: 8px; text-decoration: none; box-shadow: 0 4px 12px rgba(240,90,41,0.25); transition: background 0.2s;" onmouseover="this.style.background='#d94819'" onmouseout="this.style.background='#f05a29'">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
              Clear All Filters
            </a>
          </div>
        <?php endif; ?>
      </div>

      <!-- ============================================================
           3. SERVER-SIDE NUMBERED PAGINATION CONTROLS
           ============================================================ -->
      <?php if ($totalPages > 1): ?>
        <nav class="shop-pagination-wrapper" aria-label="Page navigation" style="margin-top: 36px; display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap;">
          
          <?php
          $buildPageUrl = function($targetPage) use ($filters, $searchQuery) {
            $params = $_GET;
            $params['page'] = $targetPage;
            return url('shop') . '?' . http_build_query($params);
          };
          ?>

          <!-- Previous Button -->
          <?php if ($currentPage > 1): ?>
            <a href="<?= $buildPageUrl($currentPage - 1) ?>" onclick="goToShopPage(event, <?= $currentPage - 1 ?>)" class="shop-page-btn shop-page-prev" style="display: inline-flex; align-items: center; gap: 4px; padding: 8px 14px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 9999px; color: #1e293b; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.15s ease;" onmouseover="this.style.borderColor='#94a3b8'; this.style.background='#f8fafc';" onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#ffffff';">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
              Previous
            </a>
          <?php else: ?>
            <span class="shop-page-btn disabled" style="display: inline-flex; align-items: center; gap: 4px; padding: 8px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 9999px; color: #cbd5e1; font-size: 13px; font-weight: 500; cursor: not-allowed;">
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
              Previous
            </span>
          <?php endif; ?>

          <!-- Page Numbers -->
          <?php foreach ($pageWindow as $p): ?>
            <?php if ($p === '...'): ?>
              <span style="padding: 8px 10px; color: #94a3b8; font-size: 14px; font-weight: 600;">...</span>
            <?php elseif ((int)$p === $currentPage): ?>
              <span class="shop-page-btn active" style="display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 38px; padding: 0 10px; background: #f05a29; border: 1px solid #f05a29; border-radius: 9999px; color: #ffffff; font-size: 13.5px; font-weight: 700; box-shadow: 0 2px 6px rgba(240,90,41,0.3);">
                <?= $p ?>
              </span>
            <?php else: ?>
              <a href="<?= $buildPageUrl((int)$p) ?>" onclick="goToShopPage(event, <?= (int)$p ?>)" class="shop-page-btn" style="display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 38px; padding: 0 10px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 9999px; color: #334155; font-size: 13.5px; font-weight: 600; text-decoration: none; transition: all 0.15s ease;" onmouseover="this.style.borderColor='#f05a29'; this.style.color='#f05a29'; this.style.background='#fff7ed';" onmouseout="this.style.borderColor='#cbd5e1'; this.style.color='#334155'; this.style.background='#ffffff';">
                <?= $p ?>
              </a>
            <?php endif; ?>
          <?php endforeach; ?>

          <!-- Next Button -->
          <?php if ($currentPage < $totalPages): ?>
            <a href="<?= $buildPageUrl($currentPage + 1) ?>" onclick="goToShopPage(event, <?= $currentPage + 1 ?>)" class="shop-page-btn shop-page-next" style="display: inline-flex; align-items: center; gap: 4px; padding: 8px 14px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 9999px; color: #1e293b; font-size: 13px; font-weight: 600; text-decoration: none; transition: all 0.15s ease;" onmouseover="this.style.borderColor='#94a3b8'; this.style.background='#f8fafc';" onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#ffffff';">
              Next
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
          <?php else: ?>
            <span class="shop-page-btn disabled" style="display: inline-flex; align-items: center; gap: 4px; padding: 8px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 9999px; color: #cbd5e1; font-size: 13px; font-weight: 500; cursor: not-allowed;">
              Next
              <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </span>
          <?php endif; ?>

        </nav>
      <?php endif; ?>

    </main>

  </div>

</div>

<!-- Styles for Dynamic 4-col vs 5-col Grid & Custom Scrollbar -->
<style>
#shopSidebar::-webkit-scrollbar,
.shop-scroll-area::-webkit-scrollbar {
  width: 5px;
}
#shopSidebar::-webkit-scrollbar-thumb,
.shop-scroll-area::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
#shopSidebar::-webkit-scrollbar-thumb:hover,
.shop-scroll-area::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
#shopSidebar::-webkit-scrollbar-track,
.shop-scroll-area::-webkit-scrollbar-track {
  background: transparent;
}

.shop-main-layout.sidebar-is-open .product-grid {
  grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
}

.shop-main-layout.sidebar-is-closed .product-grid {
  grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
}

@keyframes skeletonShimmer {
  0% { background-position: -200% 0; }
  100% { background-position: 200% 0; }
}

@media (max-width: 1200px) {
  .shop-main-layout.sidebar-is-closed .product-grid,
  .shop-main-layout.sidebar-is-open .product-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
  }
}

@media (max-width: 768px) {
  #shopSidebar {
    width: 100% !important;
    position: static !important;
  }
  .shop-main-layout.sidebar-is-closed .product-grid,
  .shop-main-layout.sidebar-is-open .product-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
  }
}

@media (max-width: 480px) {
  .shop-main-layout.sidebar-is-closed .product-grid,
  .shop-main-layout.sidebar-is-open .product-grid {
    grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
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

function toggleShopSidebar() {
  const sb = document.getElementById('shopSidebar');
  const layout = document.getElementById('shopMainLayout');
  const btn = document.getElementById('filterToggleBtn');
  const textSpan = document.getElementById('filterToggleText');
  const iconSpan = document.getElementById('filterToggleIcon');

  if (!sb || !layout || !btn) return;

  const isOpen = (sb.style.display === 'block');

  if (isOpen) {
    sb.style.display = 'none';
    layout.classList.remove('sidebar-is-open');
    layout.classList.add('sidebar-is-closed');
    btn.style.background = '#ffffff';
    btn.style.color = '#1e293b';
    btn.style.border = '1px solid #cbd5e1';
    if (textSpan) textSpan.innerText = 'Show Filters';
    if (iconSpan) iconSpan.innerHTML = '<svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>';
  } else {
    sb.style.display = 'block';
    layout.classList.remove('sidebar-is-closed');
    layout.classList.add('sidebar-is-open');
    btn.style.background = '#1e293b';
    btn.style.color = '#ffffff';
    btn.style.border = '1px solid #1e293b';
    if (textSpan) textSpan.innerText = 'Hide Filters';
    if (iconSpan) iconSpan.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>';
  }
}

function removeFilterChip(type, attrId, optId) {
  const form = document.getElementById('shopFilterForm');
  if (type === 'category_id') {
    const r = form.querySelector('input[name="category_id"][value=""]');
    if (r) r.checked = true;
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
  const formData = new FormData(form);
  const params = new URLSearchParams();

  for (const [key, value] of formData.entries()) {
    if (value !== '' && value !== null) {
      params.append(key, value);
    }
  }

  const targetUrl = form.action + '?' + params.toString();
  
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
    return res.text();
  })
  .then(html => {
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const newContent = doc.querySelector('.shop-page-wrapper');
    const currentContainer = document.querySelector('.shop-page-wrapper');

    if (newContent && currentContainer) {
      currentContainer.innerHTML = newContent.innerHTML;
    } else {
      window.location.href = targetUrl;
    }
  })
  .catch(() => {
    window.location.href = targetUrl;
  });
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
