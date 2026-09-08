<?php
/**
 * ImportWale Verified Wholesale Factories Directory View
 * (views/web/factories.php)
 */

$title = $seoTitle ?? "Verified Wholesale Factories & Manufacturers | ImportWale";
ob_start();
?>

<!-- Factories Directory Wrapper -->
<div class="factories-page-wrapper" style="max-width: 1440px; margin: 0 auto; padding: 16px 20px 32px 20px; font-family: 'Inter', system-ui, -apple-system, sans-serif;">

  <!-- Breadcrumb -->
  <nav style="display: flex; align-items: center; gap: 6px; font-size: 12px; color: #64748b; margin-bottom: 16px;">
    <a href="<?= url('/') ?>" style="color: #64748b; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#f05a29';" onmouseout="this.style.color='#64748b';">Home</a>
    <span style="color: #cbd5e1;">/</span>
    <span style="color: #334155; font-weight: 500;">Manufacturers Directory</span>
  </nav>

  <!-- Hero Header Banner -->
  <div style="background: #FAF4F2; border: 1px solid #F3E5E0; border-radius: 14px; padding: 18px 22px; margin-bottom: 24px; position: relative; overflow: hidden;">
    <div style="display: flex; align-items: flex-start; justify-space-between; flex-wrap: wrap; gap: 16px; position: relative; z-index: 1;">
      <div style="max-width: 680px;">
        <div style="display: inline-flex; align-items: center; gap: 5px; background: #FFF5F2; border: 1px solid #FDE8E0; padding: 2px 9px; border-radius: 9999px; font-size: 10.5px; font-weight: 600; color: #f05a29; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 6px;">
          <svg width="11" height="11" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0h4"/></svg>
          <span>Verified Global Sourcing</span>
        </div>
        <h1 style="font-size: 21px; font-weight: 600; color: #1e293b; margin: 0 0 4px 0; letter-spacing: -0.01em; line-height: 1.3;">
          Verified Global Manufacturers &amp; Factory Profiles
        </h1>
        <p style="font-size: 12.5px; color: #64748b; margin: 0; line-height: 1.5; font-weight: 400;">
          Browse direct factory catalogs sourced from verified international manufacturers and OEM suppliers. Select any manufacturer profile to explore isolated product catalogs and wholesale pricing.
        </p>
      </div>

      <!-- Search Filter Box -->
      <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 10px; flex-shrink: 0;">
        <div style="position: relative; min-width: 260px;">
          <input type="text" id="factorySearchInput" onkeyup="filterFactoryCards()" placeholder="Search factory name or code..." style="width: 100%; height: 36px; padding: 0 12px 0 34px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 12px; color: #0f172a; outline: none; box-sizing: border-box;">
          <svg width="14" height="14" fill="none" stroke="#94a3b8" viewBox="0 0 24 24" style="position: absolute; left: 10px; top: 11px;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        </div>
      </div>
    </div>
  </div>

  <!-- Factories Grid -->
  <?php if (empty($factories)): ?>
    <div style="text-align: center; padding: 48px 20px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px;">
      <h3 style="font-size: 15px; font-weight: 600; color: #1e293b; margin-bottom: 6px;">No Active Manufacturers</h3>
      <p style="font-size: 13px; color: #64748b; margin-bottom: 16px;">There are no public factory profiles listed at the moment.</p>
      <a href="<?= url('shop') ?>" style="display: inline-flex; align-items: center; padding: 8px 16px; background: #f05a29; color: #ffffff; border-radius: 8px; font-size: 12px; font-weight: 600; text-decoration: none;">Browse All Products</a>
    </div>
  <?php else: ?>
    <div id="factoriesGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px;">
      <?php foreach ($factories as $f): ?>
        <div class="factory-card-item" data-search="<?= htmlspecialchars(strtolower($f['name'] . ' ' . $f['factory_code'] . ' ' . ($f['source_platform'] ?? ''))) ?>" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; display: flex; flex-direction: column; justify-content: space-between; gap: 16px; transition: all 0.2s ease;">
          <div>
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
              <span style="font-family: monospace; font-size: 11px; font-weight: 700; color: #475569; background: #f1f5f9; border: 1px solid #e2e8f0; padding: 2px 7px; border-radius: 6px;">
                <?= htmlspecialchars($f['factory_code']) ?>
              </span>
              <span style="font-size: 10px; font-weight: 700; color: #059669; background: #ecfdf5; border: 1px solid #a7f3d0; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase;">
                Verified
              </span>
            </div>

            <h2 style="font-size: 15px; font-weight: 600; color: #0f172a; margin: 0 0 6px 0; line-height: 1.35;">
              <?= htmlspecialchars($f['name']) ?>
            </h2>

            <?php if (!empty($f['source_platform'])): ?>
              <div style="font-size: 11.5px; color: #64748b; font-weight: 500;">
                Platform: <span style="color: #334155; font-weight: 600;"><?= htmlspecialchars($f['source_platform']) ?></span>
              </div>
            <?php endif; ?>
          </div>

          <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 12px; border-top: 1px solid #f1f5f9;">
            <span style="font-size: 12px; font-weight: 600; color: #475569;">
              <?= (int)($f['product_count'] ?? 0) ?> Products
            </span>
            <a href="<?= url('factory/' . $f['factory_code']) ?>" style="display: inline-flex; align-items: center; gap: 4px; font-size: 12px; font-weight: 600; color: #f05a29; text-decoration: none;">
              <span>Explore Catalog</span>
              <svg width="12" height="12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div id="noFactoryResults" style="display: none; text-align: center; padding: 32px 16px; background: #ffffff; border: 1px dashed #cbd5e1; border-radius: 12px; margin-top: 16px;">
      <p style="font-size: 13px; color: #64748b; margin: 0;">No matching factory profiles found.</p>
    </div>
  <?php endif; ?>

</div>

<script>
function filterFactoryCards() {
  const input = document.getElementById('factorySearchInput');
  const query = (input ? input.value : '').toLowerCase().trim();
  const items = document.querySelectorAll('.factory-card-item');
  let visibleCount = 0;

  items.forEach(item => {
    const searchText = item.getAttribute('data-search') || '';
    if (!query || searchText.includes(query)) {
      item.style.display = 'flex';
      visibleCount++;
    } else {
      item.style.display = 'none';
    }
  });

  const noRes = document.getElementById('noFactoryResults');
  if (noRes) {
    noRes.style.display = (visibleCount === 0 && query.length > 0) ? 'block' : 'none';
  }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
