<?php
include __DIR__ . '/layouts/header.php';
?>

<div class="bg-theme-bg py-10 min-h-screen border-b border-gray-900 font-sans">
    <div class="container mx-auto px-4 space-y-8">

        <!-- Breadcrumb & Header -->
        <div class="space-y-3">
            <nav class="text-xs text-gray-500 flex items-center space-x-2">
                <a href="<?= url('/') ?>" class="hover:text-red-600 transition">Home</a>
                <span>/</span>
                <span class="font-semibold text-gray-900">Categories</span>
            </nav>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl lg:text-3xl font-semibold text-gray-900 tracking-tight">All Accessory Categories
                    </h1>
                    <p class="text-xs text-gray-500 mt-1">Explore our wide selection of EV protection, mounts, covers,
                        and spare parts</p>
                </div>
                <a href="<?= url('shop') ?>"
                    class="inline-flex items-center space-x-2 h-10 px-5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl shadow-xs transition shrink-0">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span>Browse All Shop Directory</span>
                </a>
            </div>
        </div>

        <!-- CATEGORIES GRID -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-6">
            <?php foreach ($categories as $cat): ?>
                <?php
                $rawImg = $cat['custom_icon'] ?? $cat['image'] ?? $cat['icon'] ?? '';
                $isRealImage = !empty($rawImg) && (str_contains($rawImg, '/') || str_contains($rawImg, '.') || str_starts_with($rawImg, 'http'));
                $catImgUrl = $isRealImage ? asset($rawImg) : '';
                ?>
                <a href="<?= url('category/' . $cat['slug']) ?>"
                    class="bg-white rounded-xl sm:rounded-2xl border border-gray-900 shadow-xs hover:shadow-lg hover:border-red-500 transition-all duration-300 group flex flex-col w-full overflow-hidden p-2.5 sm:p-3 text-center">

                    <!-- Square Image Container -->
                    <div
                        class="w-full aspect-square rounded-lg sm:rounded-xl overflow-hidden bg-gray-50 flex items-center justify-center relative p-2">
                        <?php if (!empty($catImgUrl)): ?>
                            <img src="<?= $catImgUrl ?>" alt="<?= htmlspecialchars($cat['name']) ?>" loading="lazy"
                                class="w-full h-full object-contain group-hover:scale-105 transition-transform duration-300"
                                onerror="this.onerror=null; this.classList.add('hidden'); this.nextElementSibling.classList.remove('hidden');">
                            <div
                                class="hidden w-full h-full bg-slate-900 flex items-center justify-center text-white/40 font-bold text-xl uppercase">
                                <?= htmlspecialchars(substr($cat['name'], 0, 2)) ?>
                            </div>
                        <?php else: ?>
                            <div
                                class="w-full h-full bg-slate-900 flex items-center justify-center text-white/40 font-bold text-xl uppercase">
                                <?= htmlspecialchars(substr($cat['name'], 0, 2)) ?>
                            </div>
                        <?php endif; ?>

                        <!-- Product Count Badge -->
                        <span
                            class="absolute top-2 left-2 text-[9px] sm:text-[10px] font-semibold text-gray-700 bg-white/90 backdrop-blur-xs px-2 py-0.5 rounded-md shadow-xs">
                            <?= $cat['product_count'] ?? 0 ?> Products
                        </span>
                    </div>

                    <!-- Category Title -->
                    <div class="pt-2 sm:pt-2.5 pb-0.5 flex items-center justify-center gap-1">
                        <h3
                            class="text-xs sm:text-sm font-semibold text-gray-900 group-hover:text-red-600 transition line-clamp-1">
                            <?= htmlspecialchars($cat['name']) ?>
                        </h3>
                        <i data-lucide="chevron-right"
                            class="w-3.5 h-3.5 text-gray-400 group-hover:text-red-600 transition shrink-0"></i>
                    </div>

                </a>
            <?php endforeach; ?>
        </div>

    </div>
</div>

<?php
include __DIR__ . '/layouts/footer.php';
?>