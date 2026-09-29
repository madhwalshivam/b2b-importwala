<?php
/**
 * ImportWale Verified Wholesale Factories Directory View
 * (views/web/factories.php)
 * Compact & Clean B2B Factory Directory (Light typography).
 */

$title = $seoTitle ?? "Verified Wholesale Factories & Manufacturers | ImportWale";
ob_start();
?>

<!-- Factories Directory Wrapper -->
<div class="factories-page-wrapper max-w-[1440px] mx-auto px-2.5 sm:px-6 py-2 sm:py-4 pb-20 sm:pb-12 font-sans">

  <!-- Compact Header Bar: Title + Count + Search & Sort in single row -->
  <div class="mb-3 sm:mb-4 bg-white border border-slate-200/90 rounded-xl p-2.5 sm:p-3 shadow-2xs">
    <div class="flex items-center justify-between gap-2 mb-2 pb-1.5 border-b border-slate-100">
      <div class="flex items-center gap-1.5">
        <h1 class="text-sm sm:text-base md:text-lg font-bold text-slate-800 leading-none">Verified Manufacturers</h1>
      </div>
      <p class="text-xs text-slate-400 font-normal hidden md:block">Source direct from verified OEM manufacturers &amp;
        wholesale suppliers</p>
    </div>

    <!-- Single Row Search & Sort Controls on Mobile & Desktop -->
    <div class="flex flex-row items-center gap-2">
      <!-- Search Field (Flex-1) -->
      <div class="relative flex-1 min-w-0">
        <input type="text" id="factorySearchInput" oninput="filterAndSortFactories()" placeholder="Search factory..."
          class="w-full h-8 sm:h-9 pl-8 sm:pl-9 pr-2.5 bg-slate-50 border border-slate-200/80 rounded-lg text-[11px] sm:text-xs text-slate-800 font-normal placeholder-slate-400 outline-none focus:border-[#f05a29] focus:bg-white transition-all">
        <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 sm:left-3 top-1/2 -translate-y-1/2 pointer-events-none"
          fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>

      <!-- Sort Dropdown (Compact, Same Row) -->
      <div class="flex items-center gap-1 shrink-0">
        <span class="text-[11px] sm:text-xs text-slate-500 font-normal hidden xs:inline">Sort:</span>
        <select id="factorySortSelect" onchange="filterAndSortFactories()"
          class="h-8 sm:h-9 px-1.5 sm:px-2.5 bg-slate-50 border border-slate-200/80 rounded-lg text-[11px] sm:text-xs font-normal text-slate-700 outline-none focus:border-[#f05a29] focus:bg-white transition cursor-pointer max-w-[125px] sm:max-w-none">
          <option value="products">Most Products</option>
          <option value="name">A-Z Name</option>
        </select>
      </div>
    </div>
  </div>

  <!-- Factories Grid (2 cols mobile, 4 cols desktop) -->
  <?php if (empty($factories)): ?>
    <div class="text-center py-10 px-4 bg-white border border-slate-200 rounded-xl shadow-2xs">
      <h3 class="text-sm font-semibold text-slate-900 mb-1">No Active Manufacturers</h3>
      <p class="text-xs text-slate-500 mb-3 font-normal">There are no public factory profiles listed at the moment.</p>
      <a href="<?= url('shop') ?>"
        class="inline-flex items-center px-4 py-2 bg-[#f05a29] hover:bg-[#d8481b] text-white rounded-lg text-xs font-medium text-decoration-none transition-colors">Browse
        All Products</a>
    </div>
  <?php else: ?>
    <div id="factoriesGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2 sm:gap-3.5">
      <?php foreach ($factories as $fi => $f):
        $slugUrl = url('factories/' . $f['slug']);
        ?>
        <div
          class="factory-card-item bg-white border border-slate-200/90 rounded-xl p-2.5 sm:p-3.5 flex flex-col justify-between transition-all duration-200 hover:border-[#f05a29] hover:shadow-md h-full group"
          data-name="<?= htmlspecialchars(mb_strtolower($f['name'])) ?>" data-products="<?= (int) $f['product_count'] ?>">

          <div>
            <?php if (!empty($f['logo_url'])): ?>
              <img src="<?= htmlspecialchars(asset($f['logo_url'])) ?>" alt="<?= htmlspecialchars($f['name']) ?>"
                class="h-4 sm:h-5 object-contain max-w-[90px] mb-1">
            <?php endif; ?>

            <!-- Factory Name with Green Verified Tick -->
            <div class="flex items-center gap-1 mb-1">
              <h2
                class="text-[11px] sm:text-xs font-semibold text-slate-900 leading-tight line-clamp-1 group-hover:text-[#f05a29] transition-colors"
                title="<?= htmlspecialchars($f['name']) ?>">
                <?= htmlspecialchars($f['name']) ?>
              </h2>
              <svg class="w-3 h-3 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"
                title="Verified Manufacturer">
                <path fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                  clip-rule="evenodd" />
              </svg>
            </div>

            <!-- Product Count & Category Line -->
            <div class="flex items-center gap-1 text-[10px] sm:text-[11px] text-slate-500 mb-2 overflow-hidden font-normal">
              <span class="font-medium text-[#f05a29] shrink-0"><?= (int) $f['product_count'] ?> Prods</span>
              <?php if (!empty($f['categories'])): ?>
                <span class="text-slate-300 shrink-0">•</span>
                <span
                  class="text-slate-500 truncate font-normal"><?= htmlspecialchars(implode(', ', array_slice($f['categories'], 0, 1))) ?></span>
              <?php endif; ?>
            </div>

            <!-- 4 Product Preview Thumbnails Grid -->
            <div class="grid grid-cols-4 gap-1 mb-2.5 pt-1.5 border-t border-slate-100">
              <?php
              $previews = array_slice($f['preview_images'] ?? [], 0, 4);
              foreach ($previews as $imgUrl):
                ?>
                <div
                  class="aspect-square rounded-md border border-slate-200/80 bg-slate-50 overflow-hidden shadow-2xs group-hover:border-slate-300 transition-colors">
                  <img src="<?= htmlspecialchars(asset($imgUrl)) ?>" alt="Product preview" class="w-full h-full object-cover">
                </div>
              <?php endforeach; ?>
              <?php for ($i = count($previews); $i < 4; $i++): ?>
                <div
                  class="aspect-square rounded-md border border-dashed border-slate-200 bg-slate-50/50 flex items-center justify-center text-slate-300">
                  <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                </div>
              <?php endfor; ?>
            </div>
          </div>

          <!-- Explore Catalog Button -->
          <a href="<?= $slugUrl ?>"
            class="w-full py-1 px-2 bg-orange-50 hover:bg-[#f05a29] text-[#f05a29] hover:text-white border border-orange-200/80 hover:border-[#f05a29] rounded-md text-[10px] sm:text-[11px] font-medium flex items-center justify-center gap-1 transition-all duration-150 no-underline mt-auto">
            <span>Explore Catalog</span>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </a>

        </div>
      <?php endforeach; ?>
    </div>

    <!-- Empty Filter Result State -->
    <div id="noFactoryResults"
      class="hidden text-center py-8 px-4 bg-white border border-dashed border-slate-300 rounded-xl mt-3">
      <p class="text-xs text-slate-500 font-normal">No matching factory profiles found for your search.</p>
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
  }
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>