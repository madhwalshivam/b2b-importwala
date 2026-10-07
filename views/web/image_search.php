<?php
/**
 * Image Search Results page — ImportWale theme
 */
$title = 'Image Search Results | ImportWala';
$noindex = true;
$canonicalUrl = null; // noindex page — omit canonical self-reference for query URLs

$sid = $sid ?? '';
$invalidToken = !empty($invalidToken);
$products = is_array($products ?? null) ? $products : [];
$totalCount = (int) ($totalCount ?? 0);
$page = max(1, (int) ($page ?? 1));
$perPage = (int) ($perPage ?? 24);
$totalPages = max(1, (int) ($totalPages ?? 1));
$sort = $sort ?? 'best';
$categoryFilter = (int) ($categoryFilter ?? 0);
$categoryOptions = is_array($categoryOptions ?? null) ? $categoryOptions : [];
$hasConfidentMatch = !empty($hasConfidentMatch);
$queryImageUrl = $queryImageUrl ?? null;
$countLabel = ($totalCount === 1)
    ? '1 product found'
    : ((int) $totalCount) . ' products found';

$buildUrl = static function (array $overrides = []) use ($sid, $sort, $categoryFilter, $page): string {
    $params = array_merge([
        'sid' => $sid,
        'sort' => $sort,
        'page' => $page,
    ], $overrides);
    if (!empty($categoryFilter) && !array_key_exists('category', $overrides)) {
        $params['category'] = $categoryFilter;
    } elseif (array_key_exists('category', $overrides) && (int) $overrides['category'] > 0) {
        $params['category'] = (int) $overrides['category'];
    } else {
        unset($params['category']);
    }
    if (isset($params['category']) && (int) $params['category'] <= 0) {
        unset($params['category']);
    }
    if (($params['page'] ?? 1) <= 1) {
        unset($params['page']);
    }
    if (($params['sort'] ?? 'best') === 'best') {
        // keep sort=best explicit for clarity when other params present; omit only if alone
    }
    $qs = http_build_query($params);
    return url('image-search' . ($qs !== '' ? '?' . $qs : ''));
};

ob_start();
?>
<style>
  .isr-page { max-width: 1280px; margin: 0 auto; padding: 12px 12px 96px; font-family: Inter, system-ui, sans-serif; color: #0f172a; }
  @media (min-width: 640px) { .isr-page { padding: 18px 20px 48px; } }

  .isr-header {
    background: linear-gradient(135deg, #fff7ed 0%, #ffffff 55%);
    border: 1px solid #ffedd5;
    border-radius: 14px;
    padding: 10px 12px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px 12px;
    margin-bottom: 14px;
  }
  .isr-header-left { display: flex; align-items: center; gap: 10px; flex: 1 1 180px; min-width: 0; }
  .isr-text { min-width: 0; flex: 1; }
  .isr-thumb {
    width: 48px; height: 48px; border-radius: 10px; object-fit: cover;
    border: 1.5px solid #f05a29; background: #fff; flex-shrink: 0;
  }
  h1.isr-title { font-size: 13.5px; font-weight: 600; color: #0f172a; margin: 0; line-height: 1.2; }
  .isr-title-short { display: none; }
  .isr-sub { font-size: 11px; color: #94a3b8; margin: 2px 0 0; font-weight: 400; }
  .isr-sub-mobile { display: none; }
  .isr-chips { display: none; flex-wrap: wrap; gap: 6px; align-items: center; }
  @media (min-width: 900px) { .isr-chips { display: flex; } }
  .isr-change-btn svg { width: 14px; height: 14px; flex-shrink: 0; display: none; }
  .isr-chip {
    font-size: 10px; font-weight: 500; padding: 3px 9px; border-radius: 999px;
    border: 1px solid #fed7aa; background: #fff; color: #c2410c;
  }
  .isr-chip.is-best { background: #f05a29; color: #fff; border-color: #f05a29; font-weight: 600; }

  .isr-header-right {
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    margin-left: auto; flex: 1 1 auto; justify-content: flex-end;
  }

  .isr-filters {
    display: flex; align-items: center; gap: 6px; flex-wrap: nowrap;
  }
  .isr-filter-field {
    display: inline-flex; align-items: center; gap: 5px;
  }
  .isr-filter-field label {
    font-size: 10.5px; font-weight: 400; color: #94a3b8; white-space: nowrap;
  }
  .isr-select {
    appearance: none;
    background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E") right 8px center no-repeat;
    border: 1px solid #e2e8f0; border-radius: 7px;
    padding: 0 24px 0 8px;
    font-size: 11px; font-weight: 400; color: #475569;
    height: 28px; line-height: 28px; min-width: 118px; max-width: 150px;
    box-sizing: border-box;
  }
  .isr-select:focus { outline: none; border-color: #fdba74; }

  .isr-actions {
    display: inline-flex; align-items: center; gap: 8px;
  }
  .isr-count {
    font-size: 11px; font-weight: 400; color: #64748b; white-space: nowrap;
    height: 28px; display: inline-flex; align-items: center;
    background: transparent; border: none; padding: 0;
  }
  .isr-count > span:first-child { color: #f05a29; font-weight: 600; }
  .isr-count-lbl { color: #64748b; font-weight: 400; }
  .isr-change-btn {
    display: inline-flex; align-items: center; justify-content: center;
    padding: 0 12px; height: 28px; border-radius: 7px; background: #f05a29; color: #fff;
    font-size: 11px; font-weight: 500; border: none; cursor: pointer;
    box-shadow: none; white-space: nowrap; box-sizing: border-box;
  }
  .isr-change-btn:hover { background: #e04f20; }

  /* —— Mobile only (phones ≤767px). 768px+ keeps desktop rules above. —— */
  @media (max-width: 767px) {
    .isr-page { padding: 12px 12px 96px; }

    .isr-header {
      display: grid;
      grid-template-columns: minmax(0, 1fr) auto;
      column-gap: 10px;
      row-gap: 10px;
      align-items: center;
      padding: 10px 12px;
      border-radius: 12px;
      margin-bottom: 12px;
      background: #fffaf7;
      border: 1px solid #ffedd5;
      box-shadow: none;
    }

    .isr-header-left {
      grid-column: 1;
      grid-row: 1;
      display: flex;
      align-items: center;
      gap: 10px;
      flex: unset;
      min-width: 0;
      margin: 0;
    }
    .isr-thumb {
      width: 48px;
      height: 48px;
      border-radius: 8px;
      border: 1px solid #fed7aa;
      flex-shrink: 0;
    }
    .isr-text { flex: 1; min-width: 0; }
    /* h1.isr-title beats theme h1:not([class*="text-"]) clamp (equal specificity, later wins) */
    h1.isr-title {
      font-size: 15px;
      font-weight: 600;
      line-height: 1.25;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .isr-title-full { display: inline; }
    .isr-title-short { display: none; }
    .isr-sub-desktop { display: none; }
    .isr-sub-mobile {
      display: block;
      margin: 2px 0 0;
      font-size: 12px;
      font-weight: 400;
      color: #94a3b8;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .isr-chips { display: none; }

    .isr-header-right {
      display: contents;
      margin: 0;
    }

    .isr-actions {
      grid-column: 2;
      grid-row: 1;
      display: flex;
      align-items: center;
      flex-shrink: 0;
      gap: 0;
      width: auto;
    }
    .isr-count { display: none; } /* count shown in .isr-sub-mobile */

    /* min-height overrides theme/everful 44px touch targets for this compact card only */
    .isr-header .isr-change-btn {
      height: 32px;
      min-height: 32px;
      padding: 6px 12px;
      font-size: 12px;
      font-weight: 500;
      border-radius: 8px;
      background: #fff7ed;
      color: #c2410c;
      border: 1px solid #fed7aa;
      gap: 5px;
      width: auto;
      box-shadow: none;
    }
    .isr-header .isr-change-btn svg { display: block; }
    .isr-header .isr-change-btn:hover { background: #ffedd5; color: #9a3412; }

    .isr-filters {
      grid-column: 1 / -1;
      grid-row: 2;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      width: 100%;
      margin: 0;
    }
    .isr-filter-field {
      display: block;
      min-width: 0;
    }
    .isr-filter-field label { display: none; }
    .isr-header .isr-select {
      width: 100%;
      max-width: none;
      min-width: 0;
      height: 36px;
      min-height: 36px;
      line-height: 36px;
      font-size: 13px;
      font-weight: 400;
      border-radius: 8px;
      border: 1px solid #e2e8f0;
      padding: 0 28px 0 10px;
      background-position: right 10px center;
    }

    .isr-note {
      padding: 8px 10px;
      font-size: 11px;
      margin-bottom: 12px;
      border-radius: 8px;
    }

    .isr-grid { gap: 8px; }
  }

  /* ≤390px: short title — full phrase truncates next to Change Image */
  @media (max-width: 390px) {
    .isr-title-full { display: none; }
    .isr-title-short { display: inline; }
  }

  /* Very narrow phones: button below text, full width */
  @media (max-width: 340px) {
    .isr-header {
      grid-template-columns: 1fr;
    }
    .isr-header-left { grid-column: 1; grid-row: 1; }
    .isr-actions { grid-column: 1; grid-row: 2; width: 100%; }
    .isr-filters { grid-column: 1; grid-row: 3; }
    .isr-header .isr-change-btn { width: 100%; height: 32px; min-height: 32px; }
  }

  .isr-note {
    background: #fff7ed; border: 1px solid #ffedd5; color: #9a3412;
    border-radius: 10px; padding: 8px 12px; font-size: 11.5px; font-weight: 400; margin-bottom: 12px;
  }

  .isr-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
  }
  @media (min-width: 640px) { .isr-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; } }
  @media (min-width: 1024px) { .isr-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
  @media (min-width: 1280px) { .isr-grid { grid-template-columns: repeat(5, minmax(0, 1fr)); } }

  .isr-card-wrap { position: relative; border-radius: 14px; }
  .isr-best-badge {
    position: absolute; top: 8px; left: 8px; z-index: 5;
    background: #f05a29; color: #fff; font-size: 10px; font-weight: 600;
    padding: 3px 8px; border-radius: 999px; letter-spacing: 0.02em;
    box-shadow: 0 2px 6px rgba(240, 90, 41, 0.35);
    pointer-events: none;
  }

  .isr-empty {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
    padding: 36px 20px; text-align: center;
  }
  .isr-empty h2 { font-size: 16px; font-weight: 600; margin: 0 0 8px; }
  .isr-empty p { font-size: 12.5px; color: #64748b; margin: 0 0 18px; font-weight: 400; }

  .isr-pager {
    display: flex; justify-content: center; align-items: center; gap: 8px;
    margin-top: 22px; flex-wrap: wrap;
  }
  .isr-pager a, .isr-pager span {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 34px; height: 34px; padding: 0 10px; border-radius: 8px;
    border: 1px solid #e2e8f0; background: #fff; color: #64748b;
    font-size: 12px; font-weight: 500; text-decoration: none;
  }
  .isr-pager a:hover { border-color: #f05a29; color: #f05a29; }
  .isr-pager .is-active { background: #f05a29; border-color: #f05a29; color: #fff; }
  .isr-pager .is-disabled { opacity: 0.45; pointer-events: none; }
</style>

<div class="isr-page">

  <?php if ($invalidToken): ?>
    <div class="isr-empty">
      <h2>This image search expired</h2>
      <p>Searches are kept for 7 days. Upload a new photo to find matching wholesale products.</p>
      <button type="button" class="isr-change-btn" onclick="typeof triggerVisualSearchModal==='function'&&triggerVisualSearchModal()">
        Upload a new image
      </button>
    </div>
  <?php else: ?>

    <!-- Header: title + sort/category + actions (one compact strip) -->
    <form class="isr-header" method="get" action="<?= url('image-search') ?>" id="isrFilterForm">
      <input type="hidden" name="sid" value="<?= htmlspecialchars($sid) ?>">

      <div class="isr-header-left">
        <?php if ($queryImageUrl): ?>
          <img class="isr-thumb" src="<?= htmlspecialchars($queryImageUrl) ?>" alt="Your uploaded image" width="48" height="48">
        <?php endif; ?>
        <div class="isr-text">
          <h1 class="isr-title">
            <span class="isr-title-full">Results for your image</span>
            <span class="isr-title-short">Image results</span>
          </h1>
          <p class="isr-sub isr-sub-desktop">Visually similar products from our catalog</p>
          <p class="isr-sub isr-sub-mobile"><?= htmlspecialchars($countLabel) ?></p>
        </div>
      </div>

      <div class="isr-chips">
        <?php if ($hasConfidentMatch): ?>
          <span class="isr-chip is-best">Best Match</span>
        <?php endif; ?>
        <span class="isr-chip">Similar Style</span>
      </div>

      <div class="isr-header-right">
        <div class="isr-filters">
          <div class="isr-filter-field">
            <label for="isrSort">Sort by</label>
            <select class="isr-select" name="sort" id="isrSort" onchange="this.form.submit()">
              <option value="best" <?= $sort === 'best' ? 'selected' : '' ?>>Best match</option>
              <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price low to high</option>
              <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price high to low</option>
              <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
            </select>
          </div>
          <?php if (!empty($categoryOptions)): ?>
          <div class="isr-filter-field">
            <label for="isrCat">Category</label>
            <select class="isr-select" name="category" id="isrCat" onchange="this.form.submit()">
              <option value="0">All categories</option>
              <?php foreach ($categoryOptions as $opt): ?>
                <option value="<?= (int) $opt['id'] ?>" <?= $categoryFilter === (int) $opt['id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars($opt['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
        </div>

        <div class="isr-actions">
          <div class="isr-count"><span><?= (int) $totalCount ?></span><span class="isr-count-lbl"><?= $totalCount === 1 ? ' product found' : ' products found' ?></span></div>
          <button type="button" class="isr-change-btn" onclick="typeof triggerVisualSearchModal==='function'&&triggerVisualSearchModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Change Image
          </button>
        </div>
      </div>
    </form>

    <?php if (!$hasConfidentMatch): ?>
      <div class="isr-note">Showing closest visually similar products. For a tighter match, try a clear product photo on a plain background.</div>
    <?php endif; ?>

    <!-- Product grid -->
    <?php if (empty($products)): ?>
      <div class="isr-empty">
        <h2>No products to show</h2>
        <p>Try uploading a clearer product photo on a plain background.</p>
        <button type="button" class="isr-change-btn" onclick="typeof triggerVisualSearchModal==='function'&&triggerVisualSearchModal()">
          Upload a new image
        </button>
      </div>
    <?php else: ?>
      <div class="isr-grid">
        <?php foreach ($products as $product):
            $isBest = !empty($product['_is_best_match']);
            ?>
          <div class="isr-card-wrap<?= $isBest ? ' is-best' : '' ?>">
            <?php if ($isBest): ?>
              <div class="isr-best-badge">Best Match</div>
            <?php endif; ?>
            <?php require __DIR__ . '/partials/product_card.php'; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($totalPages > 1): ?>
        <div class="isr-pager">
          <a class="<?= $page <= 1 ? 'is-disabled' : '' ?>"
             href="<?= htmlspecialchars($buildUrl(['page' => max(1, $page - 1)])) ?>">Prev</a>
          <?php
          $start = max(1, $page - 2);
          $end = min($totalPages, $page + 2);
          for ($i = $start; $i <= $end; $i++):
          ?>
            <a class="<?= $i === $page ? 'is-active' : '' ?>"
               href="<?= htmlspecialchars($buildUrl(['page' => $i])) ?>"><?= $i ?></a>
          <?php endfor; ?>
          <a class="<?= $page >= $totalPages ? 'is-disabled' : '' ?>"
             href="<?= htmlspecialchars($buildUrl(['page' => min($totalPages, $page + 1)])) ?>">Next</a>
        </div>
      <?php endif; ?>
    <?php endif; ?>

  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
