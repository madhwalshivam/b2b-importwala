<?php
/**
 * ImportWale Verified Wholesale Factories Directory View
 * (views/web/factories.php)
 */

$title = $seoTitle ?? "Verified Wholesale Factories & Manufacturers | ImportWale";
ob_start();
?>

<!-- Factories Directory Wrapper -->
<div class="factories-page-wrapper max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 font-sans">

  <!-- Breadcrumb -->
  <nav class="flex items-center gap-1.5 text-xs text-slate-500 mb-4">
    <a href="<?= url('/') ?>" class="text-slate-500 hover:text-[#f05a29] transition-colors">Home</a>
    <span class="text-slate-300">/</span>
    <span class="text-slate-700 font-medium">Manufacturers Directory</span>
  </nav>

  <!-- Hero Header Banner -->
  <div class="bg-[#FAF4F2] border border-[#F3E5E0] rounded-2xl p-4 sm:p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div class="max-w-2xl">
        <div class="inline-flex items-center gap-1.5 bg-[#FFF5F2] border border-[#FDE8E0] px-2.5 py-1 rounded-full text-[10.5px] font-semibold text-[#f05a29] uppercase tracking-wider mb-2">
          <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h4m-4 0V11m0 0h4"/></svg>
          <span>Verified Global Sourcing</span>
        </div>
        <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight mb-1">
          Verified Global Manufacturers &amp; Factory Profiles
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
          Browse direct factory catalogs sourced from verified international manufacturers and OEM suppliers. Select any manufacturer profile to explore isolated product catalogs and wholesale pricing.
        </p>
      </div>

      <!-- Total Summary Badge -->
      <div class="shrink-0 bg-white border border-[#EFE5E1] px-4 py-2.5 rounded-xl text-center shadow-2xs">
        <div class="text-lg sm:text-xl font-extrabold text-[#f05a29] leading-none" id="factoryCountBadge"><?= count($factories) ?></div>
        <div class="text-[11px] font-medium text-slate-500 mt-0.5">Verified Groups</div>
      </div>
    </div>
  </div>

  <!-- Search & Sort Filter Control Bar -->
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6 bg-white border border-slate-200 rounded-xl p-3 shadow-2xs">
    <!-- Search Box -->
    <div class="relative flex-1 max-w-md">
      <input type="text" id="factorySearchInput" oninput="filterAndSortFactories()" placeholder="Search manufacturer by name..."
        class="w-full h-9 pl-9 pr-3 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900 placeholder-slate-400 outline-none focus:border-[#f05a29] focus:bg-white transition">
      <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
      </svg>
    </div>

    <!-- Sort Dropdown -->
    <div class="flex items-center gap-2 shrink-0">
      <span class="text-xs text-slate-500 font-medium">Sort By:</span>
      <select id="factorySortSelect" onchange="filterAndSortFactories()"
        class="h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 outline-none focus:border-[#f05a29] focus:bg-white transition cursor-pointer">
        <option value="products">Most Products</option>
        <option value="name">Alphabetical (A-Z)</option>
      </select>
    </div>
  </div>

  <!-- Factories Grid (4 cols desktop, 2 tablet, 1 mobile) -->
  <?php if (empty($factories)): ?>
    <div class="text-center py-12 px-4 bg-white border border-slate-200 rounded-2xl">
      <h3 class="text-sm font-semibold text-slate-900 mb-1">No Active Manufacturers</h3>
      <p class="text-xs text-slate-500 mb-4">There are no public factory profiles listed at the moment.</p>
      <a href="<?= url('shop') ?>" class="inline-flex items-center px-4 py-2 bg-[#f05a29] text-white rounded-lg text-xs font-semibold text-decoration-none">Browse All Products</a>
    </div>
  <?php else: ?>
    <div id="factoriesGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
      <?php foreach ($factories as $fi => $f):
        $firstChar = mb_strtoupper(mb_substr(trim($f['name']), 0, 1, 'UTF-8'));
        $slugUrl = url('factories/' . $f['slug']);
      ?>
        <div class="factory-card-item bg-white border border-slate-200 rounded-2xl p-4 sm:p-5 flex flex-col justify-between transition-all duration-200 hover:border-slate-300 hover:shadow-2xs h-full"
          data-name="<?= htmlspecialchars(mb_strtolower($f['name'])) ?>"
          data-products="<?= (int)$f['product_count'] ?>">

          <div>
            <!-- Header: Avatar & Verified Badge -->
            <div class="flex items-center justify-between gap-2 mb-3">
              <!-- Avatar -->
              <?php if (!empty($f['logo_url'])): ?>
                <img src="<?= htmlspecialchars(asset($f['logo_url'])) ?>" alt="<?= htmlspecialchars($f['name']) ?>" class="w-10 h-10 rounded-full object-cover border border-slate-200 shrink-0">
              <?php else: ?>
                <div class="w-10 h-10 rounded-full bg-orange-100/80 text-[#f05a29] font-extrabold flex items-center justify-center text-sm shrink-0 border border-orange-200/60 select-none">
                  <?= htmlspecialchars($firstChar) ?>
                </div>
              <?php endif; ?>

              <!-- Verified Supplier Badge -->
              <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 border border-emerald-200/80 px-2.5 py-0.5 rounded-full uppercase tracking-wide shrink-0">
                Verified
              </span>
            </div>

            <!-- Factory Name -->
            <h2 class="text-sm sm:text-base font-semibold text-slate-900 leading-snug line-clamp-2 mb-1" title="<?= htmlspecialchars($f['name']) ?>">
              <?= htmlspecialchars($f['name']) ?>
            </h2>

            <!-- Total Product Count -->
            <div class="text-xs font-medium text-slate-500 mb-2.5">
              <?= (int)$f['product_count'] ?> Products
            </div>

            <!-- Category Chips (up to 2-3) -->
            <?php if (!empty($f['categories'])): ?>
              <div class="flex flex-wrap gap-1.5 mb-3">
                <?php foreach (array_slice($f['categories'], 0, 3) as $catName): ?>
                  <span class="text-[10.5px] font-medium text-slate-600 bg-slate-100 border border-slate-200/60 px-2 py-0.5 rounded-md truncate max-w-[130px]">
                    <?= htmlspecialchars($catName) ?>
                  </span>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>

            <!-- Product Preview Thumbnails (Up to 3 small square thumbnails) -->
            <?php if (!empty($f['preview_images'])): ?>
              <div class="flex items-center gap-2 mb-4 pt-1 border-t border-slate-100">
                <?php foreach (array_slice($f['preview_images'], 0, 3) as $imgUrl): ?>
                  <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl border border-slate-100 bg-slate-50 overflow-hidden shrink-0">
                    <img src="<?= htmlspecialchars(asset($imgUrl)) ?>" alt="Product preview" class="w-full h-full object-cover">
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <!-- Bottom Explore Catalog Action Button -->
          <a href="<?= $slugUrl ?>" class="w-full py-2 px-3 bg-orange-50/70 hover:bg-orange-100/90 text-[#f05a29] border border-orange-100/80 rounded-xl text-xs font-semibold flex items-center justify-center gap-1 transition-all duration-150 no-underline mt-auto">
            <span>Explore Catalog</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          </a>

        </div>
      <?php endforeach; ?>
    </div>

    <!-- Empty Filter Result State -->
    <div id="noFactoryResults" class="hidden text-center py-10 px-4 bg-white border border-dashed border-slate-300 rounded-2xl mt-4">
      <p class="text-xs sm:text-sm text-slate-500 font-medium">No matching factory profiles found for your search.</p>
    </div>
  <?php endif; ?>

</div>

<!-- Client-Side Live Search & Sorting Script -->
<script>
function filterAndSortFactories() {
  const searchInput = document.getElementById('factorySearchInput');
  const sortSelect = document.getElementById('factorySortSelect');
  const grid = document.getElementById('factoriesGrid');
  if (!grid) return;

  const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
  const sortVal = sortSelect ? sortSelect.value : 'products';

  const items = Array.from(grid.querySelectorAll('.factory-card-item'));
  let visibleCount = 0;

  items.forEach(item => {
    const name = item.getAttribute('data-name') || '';
    if (!query || name.includes(query)) {
      item.style.display = 'flex';
      visibleCount++;
    } else {
      item.style.display = 'none';
    }
  });

  // Sort visible items
  items.sort((a, b) => {
    if (sortVal === 'name') {
      const nameA = a.getAttribute('data-name') || '';
      const nameB = b.getAttribute('data-name') || '';
      return nameA.localeCompare(nameB);
    } else {
      const prodA = parseInt(a.getAttribute('data-products')) || 0;
      const prodB = parseInt(b.getAttribute('data-products')) || 0;
      return prodB - prodA;
    }
  });

  // Re-append sorted elements into grid
  items.forEach(item => grid.appendChild(item));

  const noRes = document.getElementById('noFactoryResults');
  if (noRes) {
    noRes.style.display = (visibleCount === 0 && query.length > 0) ? 'block' : 'none';
  }

  const badge = document.getElementById('factoryCountBadge');
  if (badge) {
    badge.textContent = visibleCount;
  }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
