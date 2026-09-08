<?php
include __DIR__ . '/layouts/header.php';
?>

<!-- FACTORIES LISTING PAGE HEADER -->
<div class="bg-theme-bg border-b border-gray-200/80 py-10 font-sans">
    <div class="container mx-auto px-4 text-center space-y-2">
        <span
            class="inline-block px-3 py-1 bg-red-100 text-red-700 font-semibold text-[10px] uppercase rounded-full tracking-wider">
            Verified Global Manufacturers &amp; Suppliers
        </span>
        <h1 class="text-2xl lg:text-3xl font-semibold text-gray-900 tracking-tight">Factory Direct Catalogs</h1>
        <p class="text-xs text-gray-500 max-w-xl mx-auto font-medium">
            Browse isolated product catalogs sourced direct from verified global manufacturers and primary suppliers with low MOQ and bulk pricing.
        </p>
    </div>
</div>

<!-- FACTORIES GRID CONTAINER -->
<main class="py-12 bg-theme-bg font-sans min-h-[50vh]">
    <div class="container mx-auto px-4">

        <?php if (empty($factories)): ?>
            <!-- Empty State -->
            <div
                class="py-16 text-center space-y-4 max-w-md mx-auto bg-gray-50 rounded-2xl border border-gray-200 p-8 shadow-xs">
                <div
                    class="w-16 h-16 bg-white text-gray-400 rounded-full flex items-center justify-center mx-auto border border-gray-200 shadow-xs">
                    <i data-lucide="factory" class="w-8 h-8 text-gray-400"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900">No Factories Listed</h3>
                <p class="text-xs text-gray-500 font-medium">No factory manufacturer catalogs are listed at the moment. Please check back soon!</p>
                <a href="<?= url('shop') ?>"
                    class="inline-flex items-center space-x-2 px-5 py-2.5 bg-theme-primary text-white font-semibold text-xs rounded-xl hover:bg-theme-primary-dark transition shadow-xs">
                    <i data-lucide="store" class="w-4 h-4"></i>
                    <span>Browse All Wholesale Products</span>
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <?php foreach ($factories as $f): ?>
                    <?php $targetUrl = url('factory/' . $f['factory_code']); ?>
                    <a href="<?= $targetUrl ?>"
                        class="bg-white rounded-2xl p-6 border border-gray-200 hover:border-theme-primary transition-all duration-200 flex flex-col justify-between group shadow-xs hover:shadow-md space-y-4">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md border border-gray-200">
                                    <?= htmlspecialchars($f['factory_code']) ?>
                                </span>
                                <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200 uppercase">
                                    Verified Supplier
                                </span>
                            </div>
                            <h3 class="text-sm font-bold text-gray-900 group-hover:text-theme-primary transition leading-snug">
                                <?= htmlspecialchars($f['name']) ?>
                            </h3>
                            <?php if (!empty($f['source_platform'])): ?>
                                <div class="text-[11px] text-gray-500 font-medium flex items-center gap-1">
                                    <i data-lucide="globe" class="w-3.5 h-3.5 text-gray-400"></i>
                                    <span>Source: <?= htmlspecialchars($f['source_platform']) ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="pt-3 border-t border-gray-100 flex items-center justify-between">
                            <span class="text-xs font-semibold text-gray-700">
                                <?= (int)($f['product_count'] ?? 0) ?> Products
                            </span>
                            <span class="text-xs font-semibold text-theme-primary group-hover:translate-x-1 transition flex items-center space-x-1">
                                <span>View Catalog</span>
                                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php
include __DIR__ . '/layouts/footer.php';
?>
