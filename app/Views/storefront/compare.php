<?php
include __DIR__ . '/layouts/header.php';

// Helper function to resolve spec value for a product
function getProductSpecValue(array $product, string $key): ?string
{
    $lowerKey = strtolower(trim($key));

    switch ($lowerKey) {
        case 'material':
            return !empty($product['material']) ? (string) $product['material'] : null;
        case 'finish':
            return !empty($product['finish']) ? (string) $product['finish'] : null;
        case 'weight':
            return !empty($product['weight_grams']) ? (string) $product['weight_grams'] . ' g' : null;
        case 'warranty':
            return !empty($product['warranty_months']) ? (string) $product['warranty_months'] . ' Months' : (!empty($product['warranty_info']) ? (string) $product['warranty_info'] : null);
        case 'hsn code':
            return !empty($product['hsn_code']) ? (string) $product['hsn_code'] : null;
        case 'gst tax rate':
            return !empty($product['tax_percent']) ? (string) $product['tax_percent'] . '%' : null;
    }

    if (!empty($product['specifications']) && is_array($product['specifications'])) {
        foreach ($product['specifications'] as $spec) {
            if (isset($spec['spec_key']) && strcasecmp(trim($spec['spec_key']), $key) === 0) {
                return !empty($spec['spec_value']) ? (string) $spec['spec_value'] : null;
            }
        }
    }

    return null;
}

// Calculate lowest price among compared products
$lowestPrice = null;
if (!empty($products) && count($products) > 1) {
    $prices = array_map(fn($p) => (float) ($p['sale_price'] ?: $p['price']), $products);
    $lowestPrice = min($prices);
}
?>

<script>
    window.compareProductsData = <?= json_encode($products, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

<div class="bg-gray-50 py-8 min-h-screen border-b border-gray-200 font-sans overflow-x-hidden" x-data="{
    products: window.compareProductsData || [],
    highlightDiff: false
}">
    <div class="container mx-auto px-3 sm:px-4 space-y-8">

        <!-- Breadcrumbs & Header -->
        <div
            class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-gray-200 shadow-xs">
            <div>
                <nav class="text-xs text-gray-500 mb-1 flex items-center space-x-2">
                    <a href="<?= url('/') ?>" class="hover:text-red-600 font-medium">Home</a>
                    <span>/</span>
                    <a href="<?= url('shop') ?>" class="hover:text-red-600 font-medium">Shop</a>
                    <span>/</span>
                    <span class="font-semibold text-gray-900">Product Comparison</span>
                </nav>
                <h1 class="text-2xl font-semibold text-gray-900 leading-tight">Side-by-Side Product Comparison</h1>
                <p class="text-xs text-gray-500 mt-1 font-medium">Compare prices, descriptions, and specifications of
                    your selected EV accessories</p>
            </div>

            <div class="flex items-center space-x-3">
                <a href="<?= url('shop') ?>"
                    class="h-10 px-4 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition shadow-sm flex items-center space-x-2">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Browse All Products</span>
                </a>
            </div>
        </div>

        <!-- 4-CARD COMPARE SELECTOR WIDGET AT TOP -->
        <div class="space-y-4">
            <h3 class="text-xs font-semibold text-gray-700 uppercase tracking-wider flex items-center space-x-2">
                <i data-lucide="sliders" class="w-4 h-4 text-red-600"></i>
                <span>Select Products to Compare:</span>
            </h3>
            <?php include __DIR__ . '/partials/compare_widget.php'; ?>
        </div>

        <!-- MAIN COMPARISON CONTENT -->
        <?php if (empty($products) || count($products) < 2): ?>

            <!-- EMPTY STATE WHEN < 2 PRODUCTS ARE SELECTED -->
            <div
                class="bg-white rounded-3xl p-8 sm:p-12 border border-gray-200 text-center space-y-5 max-w-2xl mx-auto my-6 shadow-sm">
                <div
                    class="w-20 h-20 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto border-2 border-dashed border-red-200">
                    <i data-lucide="git-compare" class="w-10 h-10"></i>
                </div>
                <div class="space-y-2">
                    <h3 class="text-xl font-semibold text-gray-900">Add at least 2 products to compare</h3>
                    <p class="text-xs text-gray-500 max-w-md mx-auto leading-relaxed">
                        Select scooter brands and products from the dropdown boxes above to dynamically view title, price,
                        description, and specifications side-by-side.
                    </p>
                </div>
                <div class="pt-2">
                    <a href="<?= url('shop') ?>"
                        class="inline-flex items-center space-x-2 px-6 py-3 bg-gray-900 hover:bg-black text-white font-semibold text-xs rounded-xl shadow-md transition">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        <span>Explore Shop Catalog</span>
                    </a>
                </div>
            </div>

        <?php else: ?>

            <!-- TOOLBAR & COMPARISON TABLE (2+ PRODUCTS) -->
            <div class="space-y-4">

                <!-- Toolbar Controls -->
                <div
                    class="bg-white p-4 rounded-2xl border border-gray-200 flex flex-wrap items-center justify-between gap-4 shadow-xs">
                    <div class="flex items-center space-x-4">
                        <label
                            class="flex items-center space-x-2 text-xs font-semibold text-gray-700 cursor-pointer select-none">
                            <input type="checkbox" x-model="highlightDiff"
                                class="w-4 h-4 rounded text-red-600 focus:ring-red-500 border-gray-300">
                            <span>Highlight Differences</span>
                        </label>
                    </div>

                    <div class="flex items-center space-x-2 text-xs text-gray-500 font-semibold">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-red-600"></span>
                        <span><?= count($products) ?> Products Compared</span>
                    </div>
                </div>

                <!-- DYNAMIC MATRIX TABLE -->
                <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm">
                    <div class="compare-table-scroll-wrapper overflow-x-auto"
                        style="-webkit-overflow-scrolling: touch; max-width: 100%;">
                        <?php
                        $productCount = count($products);
                        // Dynamic min-width: 140px label col + 160px per product on mobile
                        $mobileMin = 140 + ($productCount * 160);
                        ?>
                        <table class="w-full text-left border-collapse" style="min-width: <?= $mobileMin ?>px;">

                            <!-- STICKY HEADER ROW -->
                            <thead>
                                <tr>
                                    <th
                                        class="p-3 sm:p-4 w-28 sm:w-40 md:w-60 align-top bg-gray-900 text-white font-semibold text-xs uppercase tracking-wider sticky top-0 left-0 z-30 shadow-md">
                                        <div class="flex items-center space-x-1 sm:space-x-2">
                                            <i data-lucide="layers" class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-red-500"></i>
                                            <span class="hidden sm:inline">Product Details</span>
                                            <span class="sm:hidden">Details</span>
                                        </div>
                                    </th>

                                    <?php foreach ($products as $p): ?>
                                        <?php
                                        $effectivePrice = (float) ($p['sale_price'] ?: $p['price']);
                                        $isLowestPrice = ($lowestPrice !== null && count($products) > 1 && abs($effectivePrice - $lowestPrice) < 0.01);
                                        ?>
                                        <th
                                            class="p-3 sm:p-5 w-40 sm:w-56 md:w-72 align-top border-l border-gray-800 bg-gray-900 text-white sticky top-0 z-20 shadow-md">
                                            <div class="space-y-3 relative">

                                                <!-- Remove Button -->
                                                <button type="button" onclick="removeCompareProduct(<?= $p['id'] ?>)"
                                                    class="absolute -top-1 -right-1 text-gray-400 hover:text-red-400 p-1 transition"
                                                    title="Remove from comparison">
                                                    <i data-lucide="x" class="w-5 h-5"></i>
                                                </button>

                                                <!-- Product Main Image -->
                                                <div
                                                    class="w-20 h-20 sm:w-28 sm:h-28 mx-auto bg-white rounded-xl p-1 sm:p-2 border border-gray-700 flex items-center justify-center shadow-xs overflow-hidden">
                                                    <img src="<?= str_starts_with($p['main_image'], 'http') ? $p['main_image'] : asset(ltrim($p['main_image'], '/')) ?>"
                                                        alt="<?= htmlspecialchars($p['name']) ?>"
                                                        class="max-w-full max-h-full object-contain">
                                                </div>

                                                <!-- Brand & Title -->
                                                <div class="text-center space-y-1">
                                                    <span
                                                        class="text-[10px] font-semibold text-red-400 uppercase tracking-wider block">
                                                        <?= htmlspecialchars($p['brand_name'] ?? 'ImportWale') ?>
                                                    </span>
                                                    <h3
                                                        class="text-xs sm:text-sm font-semibold text-white line-clamp-2 leading-snug">
                                                        <a href="<?= url('product/' . $p['slug']) ?>"
                                                            class="hover:text-red-400 transition">
                                                            <?= htmlspecialchars($p['name']) ?>
                                                        </a>
                                                    </h3>
                                                </div>

                                                <!-- Price Section -->
                                                <div class="text-center space-y-1 pt-1 border-t border-gray-800">
                                                    <div class="flex items-center justify-center space-x-2">
                                                        <span class="text-base sm:text-lg font-extrabold text-white">
                                                            <?= format_price($effectivePrice) ?>
                                                        </span>
                                                        <?php if (!empty($p['sale_price'])): ?>
                                                            <span class="text-xs text-gray-400 line-through">
                                                                <?= format_price($p['price']) ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if ($isLowestPrice): ?>
                                                        <span
                                                            class="inline-block bg-emerald-600 text-white text-[10px] px-2.5 py-0.5 rounded-full font-semibold uppercase tracking-wider shadow-xs">
                                                            🏆 Best Value
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <!-- Action Buttons -->
                                                <div class="space-y-2 pt-2">
                                                    <!-- Add to Cart Form -->
                                                    <form action="<?= url('cart/add') ?>" method="POST">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                                        <input type="hidden" name="quantity" value="1">
                                                        <button type="submit"
                                                            class="w-full h-9 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center space-x-1.5">
                                                            <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                                            <span>Add To Cart</span>
                                                        </button>
                                                    </form>

                                                    <!-- Buy Now Form -->
                                                    <form action="<?= url('cart/add') ?>" method="POST">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                                        <input type="hidden" name="quantity" value="1">
                                                        <input type="hidden" name="buy_now" value="1">
                                                        <button type="submit"
                                                            class="w-full h-8 bg-gray-800 hover:bg-gray-700 text-gray-200 font-semibold text-[11px] rounded-xl transition flex items-center justify-center space-x-1 border border-gray-700">
                                                            <i data-lucide="zap" class="w-3 h-3 text-amber-400"></i>
                                                            <span>Buy Now</span>
                                                        </button>
                                                    </form>
                                                </div>

                                            </div>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>

                            <!-- TABLE BODY: PRICE, DESCRIPTION & REAL SPECIFICATIONS -->
                            <tbody class="divide-y divide-gray-200 text-xs font-medium text-gray-800">

                                <!-- ROW 1: PRICE & TAX DETAILS -->
                                <tr class="hover:bg-gray-50/50">
                                    <td
                                        class="p-2 sm:p-4 font-semibold text-gray-900 bg-gray-100/60 uppercase tracking-wider sticky left-0 z-10 text-[10px] sm:text-xs w-28 sm:w-auto">
                                        Price &amp; Tax
                                    </td>
                                    <?php foreach ($products as $p): ?>
                                        <?php $effectivePrice = (float) ($p['sale_price'] ?: $p['price']); ?>
                                        <td class="p-2 sm:p-4 border-l border-gray-200 align-top space-y-1">
                                            <div class="font-semibold text-sm text-gray-900">
                                                <?= format_price($effectivePrice) ?>
                                            </div>
                                            <?php if (!empty($p['sale_price'])): ?>
                                                <div class="text-[11px] text-green-700 font-semibold">
                                                    Discounted from <?= format_price($p['price']) ?>
                                                </div>
                                            <?php endif; ?>
                                            <div class="text-[10px] text-gray-500">
                                                Incl. <?= (float) ($p['tax_percent'] ?? 18) ?>% GST
                                                <?php if (!empty($p['hsn_code'])): ?>
                                                    (HSN: <?= htmlspecialchars($p['hsn_code']) ?>)
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>

                                <!-- ROW 2: DESCRIPTION -->
                                <tr class="hover:bg-gray-50/50">
                                    <td
                                        class="p-2 sm:p-4 font-semibold text-gray-900 bg-gray-100/60 uppercase tracking-wider sticky left-0 z-10 text-[10px] sm:text-xs w-28 sm:w-auto">
                                        Description
                                    </td>
                                    <?php foreach ($products as $p): ?>
                                        <td
                                            class="p-2 sm:p-4 border-l border-gray-200 align-top text-gray-600 leading-relaxed text-[11px] sm:text-xs">
                                            <?php
                                            $cleanDesc = trim(strip_tags($p['description'] ?? ''));
                                            ?>
                                            <?php if (!empty($cleanDesc)): ?>
                                                <p class="line-clamp-4 font-normal">
                                                    <?= htmlspecialchars($cleanDesc) ?>
                                                </p>
                                                <a href="<?= url('product/' . $p['slug']) ?>"
                                                    class="inline-block text-[11px] font-semibold text-red-600 hover:underline mt-1.5">
                                                    Read full description &rarr;
                                                </a>
                                            <?php else: ?>
                                                <span class="text-gray-400 font-normal">—</span>
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>

                                <!-- DYNAMIC SPECIFICATION ROWS (UNION OF SPECIFICATIONS) -->
                                <?php if (!empty($specKeys)): ?>
                                    <?php foreach ($specKeys as $key): ?>
                                        <?php
                                        // Check if values differ across products
                                        $rowValues = [];
                                        $allSame = true;
                                        $firstValue = null;
                                        foreach ($products as $idx => $p) {
                                            $val = getProductSpecValue($p, $key);
                                            $rowValues[$idx] = $val;
                                            if ($idx === 0) {
                                                $firstValue = $val;
                                            } else {
                                                if ($val !== $firstValue) {
                                                    $allSame = false;
                                                }
                                            }
                                        }
                                        ?>
                                        <tr
                                            :class="highlightDiff && !<?= $allSame ? 'true' : 'false' ?> ? 'bg-amber-50/70' : 'hover:bg-gray-50/50'">
                                            <td
                                                class="p-2 sm:p-4 font-semibold text-gray-900 bg-gray-100/60 uppercase tracking-wider sticky left-0 z-10 text-[10px] sm:text-xs w-28 sm:w-auto">
                                                <?= htmlspecialchars($key) ?>
                                            </td>
                                            <?php foreach ($products as $idx => $p): ?>
                                                <?php $val = $rowValues[$idx]; ?>
                                                <td class="p-2 sm:p-4 border-l border-gray-200 align-top">
                                                    <?php if ($val !== null && $val !== ''): ?>
                                                        <span class="font-semibold text-gray-900">
                                                            <?= htmlspecialchars($val) ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-gray-400 font-normal">—</span>
                                                    <?php endif; ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                            </tbody>
                        </table>
                    </div>

                    <!-- ============================================================ -->
                    <!-- OEM vs IMPORTWALE — Direct Comparison (Always Shown)             -->
                    <!-- ============================================================ -->
                    <div class="space-y-4">
                        <!-- Section Header -->
                        <div class="flex items-center space-x-3 bg-white p-4 rounded-2xl border border-gray-200 shadow-xs">
                            <div
                                class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-600 to-red-700 text-white flex items-center justify-center shadow-md shrink-0">
                                <i data-lucide="git-compare" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h2 class="text-sm font-semibold text-gray-900 leading-tight">OEM vs ImportWale — Price & Value
                                    Comparison</h2>
                                <p class="text-[11px] text-gray-500 font-medium mt-0.5">
                                    <strong>OEM</strong> = Original company part price &nbsp;·&nbsp;
                                    <strong class="text-red-600">ImportWale</strong> = Our price (sasta + better quality)
                                </p>
                            </div>
                        </div>

                        <!-- Per-product OEM cards — one card per compared product -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($products as $pIdx => $p):
                                $mudsorPrice = (float) ($p['sale_price'] ?: $p['price']);
                                $oemPriceRaw = (float) ($p['oem_price'] ?? 0);
                                $isOemEstimated = ($oemPriceRaw <= 0);
                                // Auto-estimate OEM as 40% higher if not set
                                $oemPrice = $isOemEstimated ? round($mudsorPrice * 1.4) : $oemPriceRaw;

                                $priceSaving = $oemPrice - $mudsorPrice;
                                $savingPct = $oemPrice > 0 ? round(($priceSaving / $oemPrice) * 100) : 0;

                                $mudsorWarranty = (int) ($p['warranty_months'] ?? 12);
                                $oemWarranty = (int) ($p['oem_warranty_months'] ?? 0);
                                if ($oemWarranty <= 0)
                                    $oemWarranty = 6; // default OEM = 6 months
                                $warrantyMultiplier = ($oemWarranty > 0 && $mudsorWarranty > 0)
                                    ? round($mudsorWarranty / $oemWarranty, 1) : null;

                                $mudsorMaterial = !empty($p['material']) ? $p['material'] : 'Heavy-Duty Steel / ABS';
                                $oemMaterial = !empty($p['oem_material']) ? $p['oem_material'] : 'Standard Steel / Plastic';

                                $mudsorFinish = !empty($p['finish']) ? $p['finish'] : 'UV-Resistant Powder Coat';
                                $oemFinish = !empty($p['oem_finish']) ? $p['oem_finish'] : 'Basic Factory Paint';

                                $mudsorFitment = 'Precision Engineered Fit';
                                $oemFitment = !empty($p['oem_fitment']) ? $p['oem_fitment'] : 'Standard OEM Fit';
                                ?>
                                <div
                                    class="bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-sm flex flex-col">

                                    <!-- Card Header: Product Name + Image -->
                                    <div class="bg-gray-900 px-4 py-3 flex items-center space-x-3">
                                        <div
                                            class="w-10 h-10 bg-white rounded-lg overflow-hidden flex items-center justify-center p-0.5 shrink-0 border border-gray-800">
                                            <img src="<?= str_starts_with($p['main_image'], 'http') ? $p['main_image'] : asset(ltrim($p['main_image'], '/')) ?>"
                                                alt="" class="max-w-full max-h-full object-contain">
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[10px] text-red-400 font-semibold uppercase tracking-wider">Product
                                                <?= $pIdx + 1 ?></p>
                                            <h3 class="text-xs font-semibold text-white line-clamp-2 leading-snug">
                                                <?= htmlspecialchars($p['name']) ?></h3>
                                        </div>
                                    </div>

                                    <!-- Column Headers -->
                                    <div
                                        class="grid grid-cols-3 text-[10px] font-semibold uppercase tracking-wider border-b border-gray-200">
                                        <div class="col-span-1 p-2.5 bg-gray-100 text-gray-500">Feature</div>
                                        <div
                                            class="col-span-1 p-2.5 bg-gray-50 text-gray-600 text-center border-l border-gray-200">
                                            OEM / Company
                                        </div>
                                        <div
                                            class="col-span-1 p-2.5 bg-emerald-50 text-emerald-700 text-center border-l border-gray-200">
                                            🏷️ ImportWale
                                        </div>
                                    </div>

                                    <!-- Comparison Rows -->
                                    <div class="divide-y divide-gray-100 flex-1">

                                        <!-- ROW: Price -->
                                        <div class="grid grid-cols-3 text-xs">
                                            <div
                                                class="col-span-1 px-3 py-3 bg-gray-50 font-semibold text-gray-600 uppercase tracking-wider text-[10px] flex items-center">
                                                <i data-lucide="tag" class="w-3 h-3 mr-1 text-gray-400 shrink-0"></i>Price
                                            </div>
                                            <div class="col-span-1 px-3 py-3 border-l border-gray-100 text-center">
                                                <div class="font-semibold text-gray-700 text-sm leading-tight">
                                                    <?= format_price($oemPrice) ?>
                                                </div>
                                                <?php if ($isOemEstimated): ?>
                                                    <div class="text-[9px] text-orange-500 font-semibold mt-0.5">Est. market price
                                                    </div>
                                                <?php else: ?>
                                                    <div class="text-[9px] text-gray-400 mt-0.5">Company MRP</div>
                                                <?php endif; ?>
                                            </div>
                                            <div
                                                class="col-span-1 px-3 py-3 border-l border-gray-100 text-center bg-emerald-50/60">
                                                <div class="font-extrabold text-emerald-700 text-sm leading-tight">
                                                    <?= format_price($mudsorPrice) ?>
                                                </div>
                                                <?php if ($priceSaving > 0): ?>
                                                    <div class="text-[9px] text-emerald-600 font-semibold mt-0.5">
                                                        ✅ Save <?= $savingPct ?>% (<?= format_price($priceSaving) ?>)
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- ROW: Warranty -->
                                        <div class="grid grid-cols-3 text-xs">
                                            <div
                                                class="col-span-1 px-3 py-3 bg-gray-50 font-semibold text-gray-600 uppercase tracking-wider text-[10px] flex items-center">
                                                <i data-lucide="shield-check"
                                                    class="w-3 h-3 mr-1 text-gray-400 shrink-0"></i>Warranty
                                            </div>
                                            <div class="col-span-1 px-3 py-3 border-l border-gray-100 text-center">
                                                <div class="font-semibold text-gray-700"><?= $oemWarranty ?> Months</div>
                                                <div class="text-[9px] text-gray-400 mt-0.5">OEM Standard</div>
                                            </div>
                                            <div
                                                class="col-span-1 px-3 py-3 border-l border-gray-100 text-center bg-emerald-50/60">
                                                <div class="font-extrabold text-emerald-700"><?= $mudsorWarranty ?> Months</div>
                                                <?php if ($warrantyMultiplier !== null && $warrantyMultiplier > 1): ?>
                                                    <div class="text-[9px] text-emerald-600 font-semibold mt-0.5">✅
                                                        <?= $warrantyMultiplier ?>x More</div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- ROW: Material -->
                                        <div class="grid grid-cols-3 text-xs">
                                            <div
                                                class="col-span-1 px-3 py-3 bg-gray-50 font-semibold text-gray-600 uppercase tracking-wider text-[10px] flex items-center">
                                                <i data-lucide="layers" class="w-3 h-3 mr-1 text-gray-400 shrink-0"></i>Material
                                            </div>
                                            <div class="col-span-1 px-3 py-3 border-l border-gray-100 text-center">
                                                <div class="font-semibold text-gray-600 leading-snug text-[11px]">
                                                    <?= htmlspecialchars($oemMaterial) ?></div>
                                            </div>
                                            <div
                                                class="col-span-1 px-3 py-3 border-l border-gray-100 text-center bg-emerald-50/60">
                                                <div class="font-semibold text-emerald-800 leading-snug text-[11px]">
                                                    <?= htmlspecialchars($mudsorMaterial) ?></div>
                                                <div class="text-[9px] text-emerald-600 font-semibold mt-0.5">✅ Upgraded</div>
                                            </div>
                                        </div>

                                        <!-- ROW: Finish -->
                                        <div class="grid grid-cols-3 text-xs">
                                            <div
                                                class="col-span-1 px-3 py-3 bg-gray-50 font-semibold text-gray-600 uppercase tracking-wider text-[10px] flex items-center">
                                                <i data-lucide="sparkles" class="w-3 h-3 mr-1 text-gray-400 shrink-0"></i>Finish
                                            </div>
                                            <div class="col-span-1 px-3 py-3 border-l border-gray-100 text-center">
                                                <div class="font-semibold text-gray-600 leading-snug text-[11px]">
                                                    <?= htmlspecialchars($oemFinish) ?></div>
                                            </div>
                                            <div
                                                class="col-span-1 px-3 py-3 border-l border-gray-100 text-center bg-emerald-50/60">
                                                <div class="font-semibold text-emerald-800 leading-snug text-[11px]">
                                                    <?= htmlspecialchars($mudsorFinish) ?></div>
                                                <div class="text-[9px] text-emerald-600 font-semibold mt-0.5">✅ Better</div>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- Card Footer: Winner Badge -->
                                    <div
                                        class="bg-gradient-to-r from-emerald-600 to-emerald-700 px-4 py-2.5 flex items-center justify-between">
                                        <div class="flex items-center space-x-1.5">
                                            <i data-lucide="trophy" class="w-3.5 h-3.5 text-yellow-300"></i>
                                            <span class="text-white text-[11px] font-semibold">
                                                <?php
                                                $wins = [];
                                                if ($priceSaving > 0)
                                                    $wins[] = format_price($priceSaving) . ' sasta';
                                                if ($warrantyMultiplier > 1)
                                                    $wins[] = $warrantyMultiplier . 'x warranty';
                                                echo 'ImportWale: ' . (count($wins) ? implode(' · ', $wins) : 'Better Value');
                                                ?>
                                            </span>
                                        </div>
                                        <a href="<?= url('product/' . $p['slug']) ?>"
                                            class="text-white/90 text-[10px] font-semibold hover:text-white transition underline underline-offset-2">
                                            Buy Now →
                                        </a>
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Disclaimer -->
                        <p class="text-[10px] text-gray-400 text-center font-medium">
                            * OEM = Original Equipment Manufacturer — vehicle brand (Ola, TVS, Honda, etc.) ka original
                            spare part price.
                            ImportWale = hamare aftermarket parts jo saste hain aur better quality dete hain.
                            <?php if (array_filter($products, fn($p) => !((float) ($p['oem_price'] ?? 0) > 0))): ?>
                                &nbsp;† Est. prices are market estimates.
                            <?php endif; ?>
                        </p>
                    </div>

                </div>

            <?php endif; ?>

        </div>
    </div>

    <script>
        function removeCompareProduct(pid) {
            fetch('<?= url('compare/toggle') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: 'product_id=' + pid + '&_csrf_token=<?= csrf_token() ?>'
            })
                .then(r => r.json())
                .then(() => location.reload());
        }
    </script>

    <?php
    include __DIR__ . '/layouts/footer.php';
    ?>