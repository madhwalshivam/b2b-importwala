<?php
include __DIR__ . '/layouts/header.php';
?>

<!-- BRANDS LISTING PAGE HEADER -->
<div class="bg-theme-bg border-b border-gray-900 py-10 font-sans">
    <div class="container mx-auto px-4 text-center space-y-2">
        <span
            class="inline-block px-3 py-1 bg-red-100 text-red-700 font-semibold text-[10px] uppercase rounded-full tracking-wider">
            EV Manufacturers Directory
        </span>
        <h1 class="text-2xl lg:text-3xl font-semibold text-gray-900 tracking-tight">Electric Scooter Brands</h1>
        <p class="text-xs text-gray-500 max-w-xl mx-auto font-medium">
            Explore custom-fit accessories, stainless steel crash guards, and all-weather body covers engineered for
            India's leading EV scooter brands.
        </p>
    </div>
</div>

<!-- BRANDS GRID CONTAINER -->
<main class="py-12 bg-theme-bg font-sans min-h-[50vh]">
    <div class="container mx-auto px-4">

        <?php if (empty($brands)): ?>
            <!-- Empty State if no active brands -->
            <div
                class="py-16 text-center space-y-4 max-w-md mx-auto bg-gray-50 rounded-2xl border border-gray-900 p-8 shadow-xs">
                <div
                    class="w-16 h-16 bg-white text-gray-400 rounded-full flex items-center justify-center mx-auto border border-gray-900 shadow-xs">
                    <i data-lucide="shield-off" class="w-8 h-8"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900">No Brands Available</h3>
                <p class="text-xs text-gray-500 font-medium">No active brand manufacturers are listed at the moment. Please
                    check back soon!</p>
                <a href="<?= url('shop') ?>"
                    class="inline-flex items-center space-x-2 px-5 py-2.5 bg-theme-primary text-white font-semibold text-xs rounded-xl hover:bg-theme-primary-dark transition shadow-xs">
                    <i data-lucide="store" class="w-4 h-4"></i>
                    <span>Browse All Accessories</span>
                </a>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                <?php foreach ($brands as $b): ?>
                    <?php
                    $logoUrl = !empty($b['logo_path']) ? $b['logo_path'] : (!empty($b['logo']) ? $b['logo'] : null);
                    $targetUrl = url('brand/' . ($b['slug'] ?? $b['id']));
                    ?>
                    <a href="<?= $targetUrl ?>"
                        class="bg-gray-50 hover:bg-white rounded-2xl p-6 border border-gray-900 hover:border-theme-primary transition-all duration-200 flex flex-col items-center justify-center h-32 group shadow-xs hover:shadow-md">
                        <?php if ($logoUrl): ?>
                            <img src="<?= asset(ltrim($logoUrl, '/')) ?>" alt="<?= htmlspecialchars($b['name']) ?>" loading="lazy"
                                class="max-h-16 w-auto object-contain transition-transform group-hover:scale-105"
                                onerror="this.style.display='none'; if(this.nextElementSibling) this.nextElementSibling.style.display='block';">
                            <span
                                class="text-sm font-semibold text-gray-800 group-hover:text-theme-primary transition font-mono hidden text-center"><?= htmlspecialchars($b['name']) ?></span>
                        <?php else: ?>
                            <span
                                class="text-sm font-semibold text-gray-800 group-hover:text-theme-primary transition font-mono text-center"><?= htmlspecialchars($b['name']) ?></span>
                        <?php endif; ?>

                        <span
                            class="text-[10px] font-semibold text-gray-400 group-hover:text-theme-primary transition mt-2 flex items-center space-x-1">
                            <span>Browse Products</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php
include __DIR__ . '/layouts/footer.php';
?>