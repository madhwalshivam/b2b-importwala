<?php
/**
 * Everful Wholesale Rebuilt Section Detail View (views/web/section_detail.php)
 * Clean Shop-Style UI layout for Homepage Sections View All pages (/section/{slug})
 */

$pageTitle = $pageHeading ?? $section['title'] ?? "Wholesale Section";
$seoTitle = $seoTitle ?? ($pageTitle . " | ImportWale");
$title = $seoTitle;

$items = $products ?? [];
$totalCount = $total ?? count($items);
$currentPage = $currentPage ?? 1;
$lastPage = $lastPage ?? 1;
$perPage = $perPage ?? 24;

$startItem = $totalCount > 0 ? (($currentPage - 1) * $perPage + 1) : 0;
$endItem = min($totalCount, $currentPage * $perPage);

ob_start();
?>

<div class="section-detail-page-wrapper" style="max-width: 1440px; margin: 0 auto; padding: 16px 20px 32px 20px; font-family: 'Inter', system-ui, -apple-system, sans-serif;">

  <!-- Breadcrumb Navigation -->
  <nav style="display: flex; align-items: center; gap: 8px; font-size: 12.5px; font-weight: 500; color: #64748b; margin-bottom: 16px;">
    <a href="<?= url('') ?>" style="color: #64748b; text-decoration: none;" onmouseover="this.style.color='#f05a29'" onmouseout="this.style.color='#64748b'">Home</a>
    <span>&rsaquo;</span>
    <span style="color: #0f172a; font-weight: 600;"><?= htmlspecialchars($pageTitle) ?></span>
  </nav>

  <!-- Page Header Banner (Clean Light Everful Style) -->
  <div style="background: #FAF4F2; border: 1px solid #F3E5E0; border-radius: 16px; padding: 24px 28px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
    <div>
      <div style="display: inline-flex; align-items: center; gap: 6px; background: #FFF5F2; border: 1px solid #FDE8E0; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 700; color: #f05a29; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
        <span>Homepage Section</span>
      </div>
      <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; margin: 0 0 6px 0; letter-spacing: -0.01em;">
        <?= htmlspecialchars($pageTitle) ?>
      </h1>
      <?php if (!empty($section['subtitle'])): ?>
        <p style="font-size: 13.5px; color: #64748b; margin: 0; font-weight: 400; max-width: 680px; line-height: 1.5;">
          <?= htmlspecialchars($section['subtitle']) ?>
        </p>
      <?php endif; ?>
    </div>

    <div style="background: #ffffff; border: 1px solid #E2E8F0; padding: 8px 16px; border-radius: 12px; font-size: 13px; font-weight: 600; color: #475569; display: inline-flex; align-items: center; gap: 6px;">
      <span>Total Products:</span>
      <span style="color: #f05a29; font-weight: 700; font-size: 14px;"><?= number_format($totalCount) ?></span>
    </div>
  </div>

  <!-- Top Controls Bar (Result Count & Sort Dropdown) -->
  <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; padding: 4px 0;">
    <span style="font-size: 13.5px; color: #64748b; font-weight: 500;">
      Showing <?= $startItem ?>–<?= $endItem ?> of <?= number_format($totalCount) ?> items
    </span>

    <div style="display: flex; align-items: center; gap: 12px;">
      <a href="<?= url('shop') ?>" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 9999px; font-size: 13px; font-weight: 600; color: #334155; text-decoration: none; transition: all 0.2s ease;" onmouseover="this.style.borderColor='#f05a29'; this.style.color='#f05a29';" onmouseout="this.style.borderColor='#cbd5e1'; this.style.color='#334155';">
        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
        Browse All Shop
      </a>
    </div>
  </div>

  <!-- Product Grid (Exact Shop 5-Col Grid Style) -->
  <div class="product-grid" style="display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 16px 12px; width: 100%;">
    <?php if (!empty($items)): ?>
      <?php foreach ($items as $product): ?>
        <?php require __DIR__ . '/partials/product_card.php'; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <!-- Zero Results Empty State -->
      <div style="grid-column: 1 / -1; background: #ffffff; padding: 64px 24px; text-align: center; border-radius: 16px; border: 1px dashed #cbd5e1; margin: 20px 0;">
        <div style="width: 64px; height: 64px; background: #fff7ed; border-radius: 9999px; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 16px; color: #f05a29;">
          <svg width="32" height="32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
        </div>
        <h3 style="font-size: 20px; font-weight: 700; color: #0f172a; margin: 0 0 8px 0;">No products in this section</h3>
        <p style="font-size: 14px; color: #64748b; margin: 0 0 20px 0; max-width: 440px; margin-left: auto; margin-right: auto; line-height: 1.5;">
          There are currently no active products assigned to <?= htmlspecialchars($pageTitle) ?>. Explore our catalog to browse all available items!
        </p>
        <a href="<?= url('shop') ?>" style="display: inline-flex; align-items: center; gap: 8px; padding: 11px 24px; background: #f05a29; color: #ffffff; font-weight: 600; font-size: 13.5px; border-radius: 8px; text-decoration: none; box-shadow: 0 4px 12px rgba(240,90,41,0.25);">
          Explore Full Catalog
        </a>
      </div>
    <?php endif; ?>
  </div>

  <!-- Numbered Pagination Controls -->
  <?php if ($lastPage > 1): ?>
    <nav aria-label="Page navigation" style="margin-top: 36px; display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap;">
      
      <?php
      $secSlug = $section['slug'] ?: $section['section_key'];
      $buildPageUrl = function($pNum) use ($secSlug) {
        return url('section/' . $secSlug) . '?page=' . $pNum;
      };
      ?>

      <!-- Previous Button -->
      <?php if ($currentPage > 1): ?>
        <a href="<?= $buildPageUrl($currentPage - 1) ?>" style="display: inline-flex; align-items: center; gap: 4px; padding: 8px 14px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 9999px; color: #1e293b; font-size: 13px; font-weight: 600; text-decoration: none;">
          &larr; Previous
        </a>
      <?php endif; ?>

      <!-- Page Numbers -->
      <?php for ($p = 1; $p <= $lastPage; $p++): ?>
        <?php if ($p === $currentPage): ?>
          <span style="display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 38px; padding: 0 10px; background: #f05a29; border: 1px solid #f05a29; border-radius: 9999px; color: #ffffff; font-size: 13.5px; font-weight: 700;">
            <?= $p ?>
          </span>
        <?php else: ?>
          <a href="<?= $buildPageUrl($p) ?>" style="display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 38px; padding: 0 10px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 9999px; color: #334155; font-size: 13.5px; font-weight: 600; text-decoration: none;">
            <?= $p ?>
          </a>
        <?php endif; ?>
      <?php endfor; ?>

      <!-- Next Button -->
      <?php if ($currentPage < $lastPage): ?>
        <a href="<?= $buildPageUrl($currentPage + 1) ?>" style="display: inline-flex; align-items: center; gap: 4px; padding: 8px 14px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 9999px; color: #1e293b; font-size: 13px; font-weight: 600; text-decoration: none;">
          Next &rarr;
        </a>
      <?php endif; ?>

    </nav>
  <?php endif; ?>

</div>

<!-- Responsive Grid Breakdown -->
<style>
@media (max-width: 1200px) {
  .product-grid {
    grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
  }
}
@media (max-width: 900px) {
  .product-grid {
    grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
  }
}
@media (max-width: 640px) {
  .product-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
  }
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
