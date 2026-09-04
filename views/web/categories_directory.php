<?php
/**
 * ImportWale Wholesale Categories & Subcategories Directory View
 * (views/web/categories_directory.php)
 * Dynamic database-driven category & subcategory listing page matching website theme.
 */

$title = $seoTitle ?? "All Wholesale Categories & Subcategories | ImportWale";
ob_start();
?>

<!-- Categories Directory Wrapper -->
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

<!-- Client-side Search Filter JavaScript -->
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
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
