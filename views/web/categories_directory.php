<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
  <div class="mb-8 text-center max-w-3xl mx-auto">
    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">Wholesale Product Categories</h1>
    <p class="mt-3 text-base text-gray-600">Explore our complete catalog of factory-direct wholesale products across all categories and subcategories.</p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php foreach ($categories as $cat): ?>
      <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-xs hover:shadow-md transition group">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-xl font-bold text-gray-900 group-hover:text-[#f05a29] transition">
            <a href="<?= category_url($cat) ?>"><?= htmlspecialchars($cat['name']) ?></a>
          </h2>
          <span class="text-xs font-semibold px-2.5 py-1 bg-orange-50 text-[#f05a29] rounded-full">
            <?= (int)($cat['product_count'] ?? 0) ?> items
          </span>
        </div>

        <?php if (!empty($cat['description'])): ?>
          <p class="text-xs text-gray-500 mb-4 line-clamp-2"><?= htmlspecialchars($cat['description']) ?></p>
        <?php endif; ?>

        <?php if (!empty($cat['subcategories'])): ?>
          <div class="space-y-1.5 border-t border-gray-100 pt-3">
            <div class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Subcategories</div>
            <div class="flex flex-wrap gap-2">
              <?php foreach ($cat['subcategories'] as $sub): ?>
                <a href="<?= subcategory_url($cat, $sub) ?>" 
                   class="inline-block text-xs font-medium text-gray-700 hover:text-[#f05a29] bg-gray-50 hover:bg-orange-50 px-3 py-1.5 rounded-lg border border-gray-200/60 transition">
                  <?= htmlspecialchars($sub['name']) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <div class="mt-4 pt-3 border-t border-gray-100">
          <a href="<?= category_url($cat) ?>" class="inline-flex items-center text-xs font-bold text-[#f05a29] hover:underline gap-1">
            Browse <?= htmlspecialchars($cat['name']) ?> Wholesale &rarr;
          </a>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
