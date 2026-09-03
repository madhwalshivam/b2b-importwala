<?php
// ============================================================
// PRODUCT DETAIL PAGE — Importerr.com Exact Replica UI
// ============================================================
$title = htmlspecialchars($product['name'] ?? 'Product') . ' | ImportWale Wholesale';
$productName = htmlspecialchars($product['name'] ?? 'Wholesale Product');
$sku = htmlspecialchars($product['sku'] ?? 'N/A');
$canonicalUrl = url('product/' . ($product['slug'] ?? $product['id']));
$initialVariantCode = $selectedVariantCode ?? $_GET['variant'] ?? '';
$moq = (int) ($product['moq'] ?? 1);
$descHtml = !empty($product['description']) ? htmlspecialchars_decode($product['description']) : '';

// Gallery
$gallery = !empty($galleryImages) ? $galleryImages : [asset('assets/images/placeholder.jpg')];
$mainImage = $gallery[0];
$totalImgs = count($gallery);

// Initial Prices from variants
$wholesaleStartPrice = (float) ($minWholesalePrice ?? $product['price'] ?? 0);
$onePieceStartPrice = (float) ($minOnePiecePrice ?? $product['sale_price'] ?? $wholesaleStartPrice);

// Delivery window: +7 to +10 days from current date
$delivStart = (new DateTime())->modify('+7 days')->format('d M Y');
$delivEnd = (new DateTime())->modify('+10 days')->format('d M Y');

// WhatsApp
$waNumber = preg_replace('/[^0-9]/', '', $whatsappNumber ?? '919217714452');

// Specifications strictly from admin panel DB
$specs = $specifications ?? [];

// Related
$related = $relatedProducts ?? [];
$prodTiers = $productTiers ?? [];
$varTiersMap = $variantTiersMap ?? [];

$variantsList = $variants ?? [];
if (empty($variantsList)) {
    $variantsList = [
        [
            'id' => null,
            'variant_code' => $product['sku'] ?? '',
            'attribute_label' => 'Edition',
            'attribute_value' => 'Standard Model (' . ($product['name'] ?? 'Main Item') . ')',
            'stock_quantity' => (int) ($product['stock_quantity'] ?? 100),
            'wholesale_price' => (float) ($product['wholesale_price'] ?: $product['price']),
            'one_piece_price' => (float) ($product['one_piece_price'] ?: $product['price']),
            'image_url' => $product['main_image'],
            'weight' => $product['weight'] ?? '',
            'dimensions' => $product['dimensions'] ?? '',
        ]
    ];
}
$variants = $variantsList;
$varCount = count($variants);

$variantsJsonData = array_map(function ($v) use ($mainImage, $prodTiers, $varTiersMap) {
    $vId = (int) $v['id'];
    $vTiers = !empty($varTiersMap[$vId]) ? $varTiersMap[$vId] : $prodTiers;
    return [
        'id' => $vId,
        'code' => $v['variant_code'] ?? '',
        'label' => $v['attribute_label'] ?? 'Color',
        'value' => $v['attribute_value'] ?? '',
        'stock' => (int) $v['stock_quantity'],
        'wholesale_price' => (float) $v['wholesale_price'],
        'one_piece_price' => (float) $v['one_piece_price'],
        'image' => !empty($v['image_url']) ? asset($v['image_url']) : $mainImage,
        'weight' => $v['weight'] ?? '',
        'dimensions' => $v['dimensions'] ?? '',
        'is_active' => (int) ($v['is_active'] ?? 1),
        'tiers' => $vTiers
    ];
}, $variants ?? []);

ob_start();
?>

<!-- ============================================================ -->
<!-- MAIN PRODUCT LAYOUT -->
<!-- ============================================================ -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6 pb-16 font-sans text-gray-900">
    <div class="flex flex-col lg:flex-row gap-6 items-start">

        <!-- ======================================================= -->
        <!-- LEFT — IMAGE GALLERY (Sticky on Scroll) -->
        <!-- ======================================================= -->
        <div class="w-full lg:w-[420px] xl:w-[450px] shrink-0 lg:sticky lg:top-24 self-start">
            <!-- Main Image Card -->
            <div class="relative bg-white rounded-2xl border border-gray-200 overflow-hidden shadow-2xs group/mainimg"
                style="aspect-ratio:1/1;">
                <img id="mainProductImage" src="<?= htmlspecialchars($mainImage) ?>" alt="<?= $productName ?>"
                    class="w-full h-full object-cover transition duration-300 cursor-zoom-in"
                    onclick="openLightbox(this.src)">

                <!-- Floating Wishlist Heart Button Overlaid on Main Image (No Border) -->
                <button type="button" id="floatingWishlistBtn" onclick="toggleDetailWishlist()"
                    class="absolute top-3 right-3 z-20 w-9 h-9 rounded-full bg-white/90 backdrop-blur-xs shadow-md flex items-center justify-center transition duration-200 hover:scale-110 hover:bg-white cursor-pointer border-0 outline-none"
                    title="Save to Wishlist">
                    <svg id="floatingWishlistIcon" class="w-4.5 h-4.5 text-gray-400 transition" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                </button>

                <?php if ($totalImgs > 1): ?>
                    <!-- Left Slide Arrow -->
                    <button onclick="prevImage()" type="button" aria-label="Previous Image"
                        class="absolute left-2.5 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 hover:bg-white text-gray-800 hover:text-[#f05a29] shadow-md flex items-center justify-center border-0 focus:outline-none transition-all cursor-pointer z-10 opacity-90 hover:opacity-100 hover:scale-105">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>

                    <!-- Right Slide Arrow -->
                    <button onclick="nextImage()" type="button" aria-label="Next Image"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-white/90 hover:bg-white text-gray-800 hover:text-[#f05a29] shadow-md flex items-center justify-center border-0 focus:outline-none transition-all cursor-pointer z-10 opacity-90 hover:opacity-100 hover:scale-105">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                <?php endif; ?>
            </div>

            <!-- Thumbnail Strip with Borderless Scroll Buttons -->
            <?php if ($totalImgs > 1): ?>
                <style>
                    #thumbStrip::-webkit-scrollbar {
                        display: none !important;
                    }
                </style>
                <div class="relative flex items-center mt-3">
                    <button onclick="scrollThumbs('left')" type="button" aria-label="Scroll Left"
                        class="shrink-0 w-7 h-7 rounded-full bg-white text-gray-700 hover:text-[#f05a29] shadow-xs flex items-center justify-center border-0 focus:outline-none transition cursor-pointer mr-1 z-10">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                    </button>

                    <div class="flex gap-2.5 overflow-x-auto py-1 scroll-smooth flex-1 [scrollbar-width:none] [-ms-overflow-style:none]"
                        id="thumbStrip">
                        <?php foreach ($gallery as $idx => $imgUrl): ?>
                            <button onclick="switchImage(<?= $idx ?>, '<?= htmlspecialchars($imgUrl) ?>')"
                                class="thumb-btn shrink-0 w-16 h-16 rounded-xl overflow-hidden transition-all focus:outline-none cursor-pointer <?= $idx === 0 ? 'border-2 border-[#f05a29]' : 'border-2 border-gray-200 hover:border-gray-400' ?>"
                                data-idx="<?= $idx ?>">
                                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Thumb <?= $idx + 1 ?>"
                                    class="w-full h-full object-cover" loading="lazy">
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <button onclick="scrollThumbs('right')" type="button" aria-label="Scroll Right"
                        class="shrink-0 w-7 h-7 rounded-full bg-white text-gray-700 hover:text-[#f05a29] shadow-xs flex items-center justify-center border-0 focus:outline-none transition cursor-pointer ml-1 z-10">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                        </svg>
                    </button>
                </div>
            <?php endif; ?>
        </div>

        <!-- ======================================================= -->
        <!-- CENTER — PRODUCT DETAILS & PRICING -->
        <!-- ======================================================= -->
        <div class="flex-1 min-w-0 space-y-5">

            <!-- Product Title Card Container -->
            <div class="bg-white border border-gray-200 rounded-2xl p-5 shadow-2xs space-y-4">
                <h1 class="text-sm sm:text-base font-medium text-gray-800 leading-snug tracking-normal">
                    <?= $productName ?>
                </h1>

                <!-- Pricing Box inside Title Card -->
                <div class="bg-[#F8FAFC] border border-gray-100 rounded-xl p-3.5 space-y-3">
                    <!-- One-Piece / Wholesale Toggle -->
                    <div class="bg-gray-200/70 rounded-full p-1 max-w-[300px] flex items-center justify-between">
                        <button id="btnOnePiece" onclick="setPricingMode('onepiece')" type="button"
                            class="flex-1 py-1.5 px-3 text-xs font-semibold rounded-full text-center border-0 focus:outline-none transition-all duration-200 cursor-pointer text-gray-600">
                            One-Piece
                        </button>

                        <div onclick="togglePricingMode()"
                            class="w-8 h-4 bg-[#f05a29] rounded-full flex items-center px-0.5 cursor-pointer shrink-0 mx-1">
                            <div id="toggleSwitchDot"
                                class="w-3 h-3 bg-white rounded-full transition-transform duration-200 translate-x-4">
                            </div>
                        </div>

                        <button id="btnWholesale" onclick="setPricingMode('wholesale')" type="button"
                            class="flex-1 py-1.5 px-3 text-xs font-semibold rounded-full text-center border-0 focus:outline-none transition-all duration-200 cursor-pointer bg-black text-white">
                            Wholesale
                        </button>
                    </div>

                    <!-- Single Price Display (One-Piece Mode) -->
                    <div id="singlePriceRow" class="pt-0.5 hidden">
                        <div
                            class="text-xl sm:text-2xl font-semibold text-[#f05a29] flex items-baseline gap-0.5 whitespace-nowrap">
                            <span>₹</span><span id="priceDisplay"><?= number_format($onePieceStartPrice, 2) ?></span>
                            <span class="text-xs text-gray-500 font-normal ml-1">/ piece</span>
                        </div>
                    </div>

                    <!-- Tiered Volume Pricing Cards (Wholesale Mode) -->
                    <div id="wholesaleTierContainer" class="pt-0.5 space-y-2">
                        <div id="tierCardsRow"
                            class="flex items-center gap-2.5 overflow-x-auto pb-1 scroll-smooth [scrollbar-width:none]">
                            <?php
                            $initialTiers = !empty($variantsJsonData[0]['tiers']) ? $variantsJsonData[0]['tiers'] : ($productTiers ?? []);
                            if (empty($initialTiers)) {
                                $initialTiers = [['min_qty' => 1, 'max_qty' => null, 'unit_price' => $wholesaleStartPrice]];
                            }
                            ?>
                            <?php foreach ($initialTiers as $tIdx => $t): ?>
                                <?php
                                $tMin = (int) $t['min_qty'];
                                $tMax = !empty($t['max_qty']) ? (int) $t['max_qty'] : null;
                                $tPrice = (float) $t['unit_price'];
                                $rangeLabel = $tMax ? "{$tMin}-{$tMax} piece" : "≥ {$tMin} piece";
                                ?>
                                <div class="tier-card p-2.5 rounded-xl border transition-all text-center min-w-[105px] shrink-0 <?= $tIdx === 0 ? 'border-[#f05a29] bg-orange-50/40 shadow-2xs' : 'border-gray-200 bg-white hover:border-gray-300' ?>"
                                    data-tier-idx="<?= $tIdx ?>">
                                    <div
                                        class="text-sm sm:text-base font-semibold <?= $tIdx === 0 ? 'text-[#f05a29]' : 'text-gray-900' ?>">
                                        ₹<?= number_format($tPrice, 0) ?> <span
                                            class="text-[10px] font-normal text-gray-500">/ piece</span>
                                    </div>
                                    <div
                                        class="text-[11px] <?= $tIdx === 0 ? 'text-gray-800 font-semibold' : 'text-gray-500 font-medium' ?> mt-0.5">
                                        <?= $rangeLabel ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div
                            class="text-[11px] text-gray-500 bg-amber-50/80 border border-amber-200/80 px-3 py-1.5 rounded-lg flex items-center gap-1">
                            <span>This is the <strong class="text-gray-800">Product price</strong> only. Procurement,
                                taxes, duties & are charged separately.</span>
                        </div>
                    </div>

                    <!-- Estimated Delivery Card -->
                    <div
                        class="bg-white border border-gray-200/80 rounded-xl px-3 py-2 flex items-center gap-2 text-xs text-gray-700">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                        <span>Get it between <strong class="font-semibold text-emerald-700"><?= $delivStart ?> -
                                <?= $delivEnd ?></strong></span>
                    </div>
                </div>
            </div>

            <!-- Need Help? Strip with SVG WhatsApp Icon -->
            <div class="bg-white border border-gray-200 rounded-2xl px-4 py-3 flex items-center justify-between">
                <div>
                    <div class="text-xs font-semibold text-gray-900">Need Help?</div>
                    <div class="text-[11px] text-gray-400">Mon to Sat (9:30AM to 6:00PM)</div>
                </div>
                <a id="helpWhatsappBtn"
                    href="https://wa.me/<?= $waNumber ?>?text=<?= urlencode('Hi, I need help with: ' . $productName) ?>"
                    target="_blank"
                    class="flex items-center gap-1.5 px-3.5 py-1.5 border border-emerald-500 text-emerald-600 hover:bg-emerald-50 text-xs font-semibold rounded-full transition">
                    <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                        <path
                            d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                    </svg>
                    Chat Now
                </a>
            </div>

            <!-- Custom Scrollbar Style for Variants List -->
            <style>
                #variantsList::-webkit-scrollbar {
                    width: 4px;
                }

                #variantsList::-webkit-scrollbar-track {
                    background: transparent;
                }

                #variantsList::-webkit-scrollbar-thumb {
                    background: #CBD5E1;
                    border-radius: 4px;
                }

                #variantsList::-webkit-scrollbar-thumb:hover {
                    background: #94A3B8;
                }
            </style>

            <!-- Variant List (Expandable Color/Size Container - Max Height 3 Rows Scrollable) -->
            <?php if (!empty($variants)): ?>
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-2xs">
                    <!-- Scrollable Container: Capped to 3 rows max-height (~235px), slide/scroll for 4+ variants -->
                    <div class="divide-y divide-gray-100 max-h-[235px] overflow-y-auto scroll-smooth" id="variantsList">
                        <?php foreach ($variants as $vi => $v):
                            $vWholesale = (float) $v['wholesale_price'];
                            $vOnePiece = (float) $v['one_piece_price'];
                            $vStock = (int) $v['stock_quantity'];
                            $vName = htmlspecialchars($v['attribute_value'] ?? 'Variant ' . ($vi + 1));
                            $vCode = htmlspecialchars($v['variant_code'] ?? '');
                            $vDim = htmlspecialchars($v['dimensions'] ?? '');
                            $vImg = !empty($v['image_url']) ? asset($v['image_url']) : $mainImage;
                            $vWeight = htmlspecialchars($v['weight'] ?? '');
                            ?>
                            <div class="variant-row flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-gray-50/80 transition border-b border-gray-100 last:border-0"
                                data-variant-idx="<?= $vi ?>" data-wholesale="<?= $vWholesale ?>"
                                data-onepiece="<?= $vOnePiece ?>" data-name="<?= $vName ?>"
                                data-img="<?= htmlspecialchars($vImg) ?>" onclick="selectAmazonVariant(<?= $vi ?>)">

                                <div
                                    class="w-12 h-12 rounded-lg border border-gray-200 overflow-hidden shrink-0 bg-white shadow-2xs">
                                    <img src="<?= htmlspecialchars($vImg) ?>" alt="<?= $vName ?>"
                                        class="w-full h-full object-cover">
                                </div>

                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-semibold text-gray-900 truncate"><?= $vName ?></span>
                                    </div>
                                    <?php if ($vWeight || $vDim): ?>
                                        <div class="text-[11px] text-gray-400 mt-0.5 flex flex-wrap items-center gap-1">
                                            <?php if ($vWeight): ?><span>Wt: <?= $vWeight ?></span><?php endif; ?>
                                            <?php if ($vWeight && $vDim): ?> &bull; <?php endif; ?>
                                            <?php if ($vDim): ?><span>Fit: <?= $vDim ?></span><?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Stepper & Price Section -->
                                <div class="flex items-center gap-2.5 shrink-0">
                                    <!-- Quantity Stepper (- 0 +) -->
                                    <div class="flex items-center border border-gray-200 rounded-lg bg-gray-50 overflow-hidden text-xs font-semibold select-none"
                                        onclick="event.stopPropagation()">
                                        <button type="button" onclick="updateVariantQty(<?= $vi ?>, -1)"
                                            class="w-6 h-6 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition cursor-pointer border-0 bg-transparent">-</button>
                                        <span id="vQtyVal_<?= $vi ?>" class="w-5 text-center text-gray-800 text-[11px]">0</span>
                                        <button type="button" onclick="updateVariantQty(<?= $vi ?>, 1)"
                                            class="w-6 h-6 flex items-center justify-center text-gray-500 hover:bg-gray-200 transition cursor-pointer border-0 bg-transparent">+</button>
                                    </div>

                                    <div class="text-right">
                                        <div class="text-xs font-semibold text-[#f05a29] variant-price-display"
                                            data-wholesale="<?= number_format($vWholesale, 2) ?>"
                                            data-onepiece="<?= number_format($vOnePiece, 2) ?>">
                                            ₹<?= number_format($vWholesale, 2) ?>
                                        </div>
                                        <div class="text-[10px] text-gray-400">/piece</div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- ====================================================== -->
            <!-- ONLINE ORDER ACTION BOX -->
            <!-- ====================================================== -->
            <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-2xs">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <!-- Quantity Input Stepper -->
                    <div
                        class="flex items-center justify-between border border-gray-300 rounded-xl bg-gray-50 h-11 px-2 select-none shrink-0">
                        <button type="button" onclick="changeDetailQty(-1)"
                            class="w-8 h-8 flex items-center justify-center text-gray-700 hover:bg-gray-200 rounded-lg transition font-semibold text-sm cursor-pointer border-0 bg-transparent">-</button>
                        <input type="number" id="detailQtyInput" value="1" min="1" onchange="onDetailQtyInputChange()"
                            class="w-12 text-center text-gray-900 font-semibold text-xs bg-transparent border-0 focus:outline-none">
                        <button type="button" onclick="changeDetailQty(1)"
                            class="w-8 h-8 flex items-center justify-center text-gray-700 hover:bg-gray-200 rounded-lg transition font-semibold text-sm cursor-pointer border-0 bg-transparent">+</button>
                    </div>

                    <!-- Add to Cart Button -->
                    <button type="button" id="mainAddToCartBtn" onclick="addToCartFromDetail()"
                        class="flex-1 h-11 bg-[#f05a29] hover:bg-[#d94e20] text-white font-semibold text-xs rounded-xl shadow-xs transition inline-flex items-center justify-center gap-2 cursor-pointer border-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <span>Add to Cart</span>
                    </button>

                    <!-- Buy Now Button -->
                    <button type="button" onclick="buyNowFromDetail()"
                        class="px-5 h-11 bg-gray-900 hover:bg-black text-white font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer border-0">
                        Buy Now
                    </button>
                </div>
            </div>

            <!-- Navy Blue RFQ Banner -->
            <div class="bg-[#0F172A] rounded-2xl p-4 text-white flex items-center justify-between gap-4 shadow-xs">
                <div>
                    <div class="text-xs font-semibold text-white">Need a Better Price?</div>
                    <div class="text-[11px] text-gray-300 mt-0.5">Share your required quantity &amp; target price for a
                        tailored quote.</div>
                </div>
                <button onclick="openRfqWithProducts()"
                    class="shrink-0 px-5 py-2.5 bg-[#f05a29] hover:bg-[#d94818] text-white text-xs font-semibold rounded-full border-0 focus:outline-none transition cursor-pointer">
                    Request for Quote
                </button>
            </div>

            <!-- ====================================================== -->
            <!-- PRODUCT SPECIFICATIONS (IMPORTERR EXACT REPLICA UI) -->
            <!-- ====================================================== -->
            <?php if (!empty($specs)): ?>
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-2xs">
                    <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                        <h2 class="text-xs sm:text-sm font-semibold text-gray-900 uppercase tracking-wider">Product
                            Specifications</h2>
                    </div>

                    <div class="divide-y divide-gray-100 text-xs">
                        <?php foreach ($specs as $si => $s): ?>
                            <div
                                class="spec-row flex items-center justify-between px-5 py-3 transition <?php echo ($si >= 5) ? 'hidden spec-row-extra' : ''; ?> <?php echo ($si % 2 === 1) ? 'bg-gray-50/40' : 'bg-white'; ?>">
                                <div class="w-1/2 sm:w-5/12 text-gray-600 font-medium pr-4 truncate">
                                    <?= htmlspecialchars($s['spec_key'] ?? '') ?>
                                </div>
                                <div
                                    class="w-1/2 sm:w-7/12 text-gray-900 font-semibold text-right sm:text-left leading-relaxed">
                                    <?= htmlspecialchars($s['spec_value'] ?? '') ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (count($specs) > 5): ?>
                        <div
                            class="py-5 px-4 text-center bg-gray-50/40 border-t border-gray-100 flex items-center justify-center">
                            <button type="button" id="toggleSpecsBtn" onclick="toggleAllSpecs()"
                                style="display: inline-flex; align-items: center; justify-content: center; height: 36px; padding: 0 22px; background-color: #ffffff; border: 1px solid #d1d5db; border-radius: 9999px; font-size: 12px; font-weight: 600; color: #374151; cursor: pointer; outline: none; transition: all 0.15s ease-in-out; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                                class="hover:bg-gray-50 hover:border-gray-400 shrink-0">
                                <span id="specsBtnText">Show all specifications</span>
                                <svg id="specsChevron"
                                    class="w-3.5 h-3.5 transition-transform duration-200 text-gray-500 shrink-0 ml-2"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                </svg>
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- ====================================================== -->
            <!-- PRODUCT DESCRIPTION -->
            <!-- ====================================================== -->
            <?php if ($descHtml): ?>
                <?php
                $formattedDesc = $descHtml;
                // If not raw HTML (no tags like <p>, <div>, <ul>), auto-format text & bullets
                if (!preg_match('/<[a-z][\s\S]*>/i', $descHtml)) {
                    $lines = array_filter(explode("\n", str_replace("\r", "", $descHtml)));
                    $blocks = [];
                    $bulletItems = [];

                    foreach ($lines as $line) {
                        $trimmed = trim($line);
                        if (empty($trimmed))
                            continue;

                        if (str_starts_with($trimmed, '•') || str_starts_with($trimmed, '-') || str_starts_with($trimmed, '*')) {
                            $bulletItems[] = trim(ltrim($trimmed, '•-* '));
                        } else {
                            if (!empty($bulletItems)) {
                                $blocks[] = ['type' => 'bullets', 'items' => $bulletItems];
                                $bulletItems = [];
                            }
                            $blocks[] = ['type' => 'text', 'content' => $trimmed];
                        }
                    }
                    if (!empty($bulletItems)) {
                        $blocks[] = ['type' => 'bullets', 'items' => $bulletItems];
                    }

                    ob_start();
                    ?>
                    <div class="space-y-4">
                        <?php foreach ($blocks as $block): ?>
                            <?php if ($block['type'] === 'text'): ?>
                                <p class="text-xs sm:text-sm text-gray-700 leading-relaxed font-sans">
                                    <?= nl2br(htmlspecialchars($block['content'])) ?>
                                </p>
                            <?php elseif ($block['type'] === 'bullets'): ?>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 my-3">
                                    <?php foreach ($block['items'] as $item): ?>
                                        <?php
                                        $parts = explode(':', $item, 2);
                                        $bTitle = count($parts) > 1 ? trim($parts[0]) : '';
                                        $bBody = count($parts) > 1 ? trim($parts[1]) : $item;
                                        ?>
                                        <div class="p-3 bg-gray-50/80 rounded-xl border border-gray-200/80 flex items-start gap-2.5">
                                            <div
                                                class="w-5 h-5 rounded-full bg-orange-100 text-[#f05a29] flex items-center justify-center font-semibold text-[10px] shrink-0 mt-0.5">
                                                ✓</div>
                                            <div class="text-xs text-gray-700 leading-snug">
                                                <?php if ($bTitle): ?>
                                                    <strong
                                                        class="text-gray-900 font-semibold block mb-0.5"><?= htmlspecialchars($bTitle) ?></strong>
                                                <?php endif; ?>
                                                <span><?= htmlspecialchars($bBody) ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php
                    $formattedDesc = ob_get_clean();
                }
                ?>
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-2xs">
                    <div class="px-5 py-4 bg-gray-50/50 flex items-center justify-between cursor-pointer select-none"
                        onclick="toggleProductDesc()">
                        <h2
                            class="text-xs sm:text-sm font-semibold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-[#f05a29] shrink-0" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            <span>Product Description</span>
                        </h2>
                        <button type="button"
                            style="display: inline-flex; align-items: center; justify-content: center; height: 32px; padding: 0 16px; background-color: #ffffff; border: 1px solid #d1d5db; border-radius: 9999px; font-size: 12px; font-weight: 600; color: #374151; cursor: pointer; outline: none; transition: all 0.15s ease-in-out; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);"
                            class="hover:bg-gray-50 hover:border-gray-400 shrink-0">
                            <span id="descHeaderBtnText">Show description</span>
                            <svg id="descChevron"
                                class="w-3.5 h-3.5 transition-transform duration-200 text-gray-500 shrink-0 ml-1.5"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                    </div>
                    <!-- Content hidden / closed by default -->
                    <div id="productDescContent"
                        class="hidden p-5 sm:p-6 text-xs sm:text-sm text-gray-700 leading-relaxed font-sans prose prose-sm max-w-none border-t border-gray-100">
                        <?= $formattedDesc ?>
                    </div>
                </div>
            <?php endif; ?>

        </div><!-- end center column -->

        <!-- ======================================================= -->
        <!-- RIGHT — ORDER SUMMARY & PROTECTION SIDEBAR (Sticky) -->
        <!-- ======================================================= -->
        <div class="w-full lg:w-[280px] xl:w-[300px] shrink-0 space-y-4 lg:sticky lg:top-24 self-start">

            <!-- Importerr Order Summary Card -->
            <div class="bg-white border border-gray-200 rounded-2xl p-4 shadow-2xs space-y-3">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                    <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wider">Order Summary</h3>
                    <div class="flex items-center gap-1.5">
                        <span
                            class="px-2 py-0.5 bg-gray-100 text-gray-600 text-[10px] font-semibold rounded uppercase">WHOLESALE</span>
                        <span
                            class="px-2 py-0.5 bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-semibold rounded uppercase">MOQ
                            1</span>
                    </div>
                </div>

                <div class="space-y-2 text-xs">
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Quantity</span>
                        <span class="font-semibold text-gray-900" id="summaryQtyText">0 units</span>
                    </div>
                    <div class="flex items-center justify-between text-gray-600">
                        <span>Gross Total Amount</span>
                        <span class="font-semibold text-[#f05a29] text-sm" id="summaryTotalText">₹0.00</span>
                    </div>
                </div>

                <button type="button" onclick="buyNowFromDetail()"
                    class="w-full py-3 bg-gray-900 hover:bg-black text-white font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer border-0">
                    Buy Now
                </button>
            </div>

            <!-- Importerr Order Protection Card -->
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-2xs">
                <div class="px-4 py-3 bg-[#0F172A] text-white flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#f05a29] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    <span class="text-xs font-semibold tracking-wide">ImportWale <span
                            class="font-normal text-gray-300 text-[10px] uppercase">order protection</span></span>
                </div>

                <div class="p-4 space-y-3 text-xs">
                    <!-- Secure Payments (Importerr Replica) -->
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                        </svg>
                        <div>
                            <div class="font-semibold text-gray-900 flex items-center gap-1 flex-wrap">
                                <span>Secure payments*</span>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-0.5">Every payment you make on ImportWale is secured
                                with strict SSL encryption.</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-100"></div>

                    <!-- Delivery -->
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-[#f05a29] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8.25 18.75a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 01-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 00-3.213-9.193 2.056 2.056 0 00-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 00-10.026 0 1.106 1.106 0 00-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                        </svg>
                        <div>
                            <div class="font-semibold text-gray-900">Delivery arranged by ImportWale*</div>
                            <p class="text-[11px] text-gray-400 mt-0.5">Expect your order to be delivered before
                                scheduled dates.</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-100"></div>

                    <!-- Easy Return -->
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        <div>
                            <div class="font-semibold text-gray-900">Easy Return*</div>
                            <p class="text-[11px] text-gray-400 mt-0.5">Make free local returns for defects on
                                qualifying request.</p>
                        </div>
                    </div>

                    <div class="border-t border-gray-100"></div>

                    <!-- Money-back -->
                    <div class="flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24"
                            stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                        </svg>
                        <div>
                            <div class="font-semibold text-gray-900">Full Money-back protection*</div>
                            <p class="text-[11px] text-gray-400 mt-0.5">Claim a refund if your order doesn't ship or is
                                missing.</p>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-2.5 text-[10px] text-gray-400 leading-relaxed">
                        Only orders placed and paid through <strong>ImportWale</strong> can enjoy free protection by
                        <strong>Trade Assurance.</strong>
                    </div>
                </div>
            </div>

        </div><!-- end right sidebar -->

    </div><!-- end 3-column flex -->
</div><!-- end main container -->

<!-- ============================================================ -->
<!-- LIGHTBOX MODAL -->
<!-- ============================================================ -->
<div id="lightboxModal" class="fixed inset-0 bg-black/90 z-[100] hidden items-center justify-center p-4"
    onclick="closeLightbox()">
    <button onclick="closeLightbox()"
        class="absolute top-4 right-4 w-10 h-10 bg-white/10 hover:bg-white/20 rounded-full flex items-center justify-center text-white text-xl transition z-10">✕</button>
    <img id="lightboxImg" src="" alt="" class="max-w-full max-h-full object-contain rounded-xl shadow-2xl"
        onclick="event.stopPropagation()">
</div>

<!-- ============================================================ -->
<!-- JAVASCRIPT -->
<!-- ============================================================ -->
<script>
    const WHATSAPP_NUMBER = '<?= $waNumber ?>';
    const PRODUCT_NAME = '<?= addslashes($productName) ?>';
    const PRODUCT_URL = '<?= $canonicalUrl ?>';
    const WS_START = <?= $wholesaleStartPrice ?>;
    const OP_START = <?= $onePieceStartPrice ?>;

    let currentMode = 'wholesale';
    let selectedVariantEl = null;

    function setPricingMode(mode) {
        currentMode = mode;
        const btnOP = document.getElementById('btnOnePiece');
        const btnWS = document.getElementById('btnWholesale');
        const switchDot = document.getElementById('toggleSwitchDot');
        const singlePriceRow = document.getElementById('singlePriceRow');
        const wholesaleTierContainer = document.getElementById('wholesaleTierContainer');
        const priceEl = document.getElementById('priceDisplay');

        if (mode === 'wholesale') {
            if (btnWS) btnWS.className = 'flex-1 py-1.5 px-3 text-xs font-semibold rounded-full text-center border-0 focus:outline-none transition-all duration-200 cursor-pointer bg-black text-white';
            if (btnOP) btnOP.className = 'flex-1 py-1.5 px-3 text-xs font-semibold rounded-full text-center border-0 focus:outline-none transition-all duration-200 cursor-pointer text-gray-600';
            if (switchDot) switchDot.className = 'w-3 h-3 bg-white rounded-full transition-transform duration-200 translate-x-4';

            if (singlePriceRow) singlePriceRow.classList.add('hidden');
            if (wholesaleTierContainer) wholesaleTierContainer.classList.remove('hidden');
        } else {
            if (btnOP) btnOP.className = 'flex-1 py-1.5 px-3 text-xs font-semibold rounded-full text-center border-0 focus:outline-none transition-all duration-200 cursor-pointer bg-black text-white';
            if (btnWS) btnWS.className = 'flex-1 py-1.5 px-3 text-xs font-semibold rounded-full text-center border-0 focus:outline-none transition-all duration-200 cursor-pointer text-gray-600';
            if (switchDot) switchDot.className = 'w-3 h-3 bg-white rounded-full transition-transform duration-200 translate-x-0';

            if (singlePriceRow) singlePriceRow.classList.remove('hidden');
            if (wholesaleTierContainer) wholesaleTierContainer.classList.add('hidden');

            let p = OP_START;
            if (typeof VARIANTS_LIST !== 'undefined' && VARIANTS_LIST[selectedVariantIndex]) {
                p = VARIANTS_LIST[selectedVariantIndex].one_piece_price || OP_START;
            } else if (selectedVariantEl) {
                p = parseFloat(selectedVariantEl.dataset.onepiece) || OP_START;
            }
            if (priceEl) priceEl.textContent = formatNum(p);
        }

        document.querySelectorAll('.variant-price-display').forEach(el => {
            const ws = parseFloat(el.dataset.wholesale) || 0;
            const op = parseFloat(el.dataset.onepiece) || 0;
            el.textContent = '₹' + formatNum(mode === 'wholesale' ? ws : op);
        });
    }

    function renderVariantTiers(tiers) {
        const row = document.getElementById('tierCardsRow');
        if (!row) return;

        if (!tiers || tiers.length === 0) {
            const p = (VARIANTS_LIST && VARIANTS_LIST[selectedVariantIndex]) ? VARIANTS_LIST[selectedVariantIndex].wholesale_price : WS_START;
            tiers = [{ min_qty: 1, max_qty: null, unit_price: p }];
        }

        let html = '';
        tiers.forEach((t, i) => {
            const min = parseInt(t.min_qty);
            const max = t.max_qty ? parseInt(t.max_qty) : null;
            const price = parseFloat(t.unit_price);
            const label = max ? `${min}-${max} piece` : `≥ ${min} piece`;
            const isActive = (i === 0);

            html += `
                <div class="tier-card p-2.5 rounded-xl border transition-all text-center min-w-[105px] shrink-0 ${isActive ? 'border-[#f05a29] bg-orange-50/40 shadow-2xs' : 'border-gray-200 bg-white hover:border-gray-300'}" data-tier-idx="${i}">
                    <div class="text-sm sm:text-base font-semibold ${isActive ? 'text-[#f05a29]' : 'text-gray-900'}">
                        ₹${formatNumNoDec(price)} <span class="text-[10px] font-normal text-gray-500">/ piece</span>
                    </div>
                    <div class="text-[11px] ${isActive ? 'text-gray-800 font-semibold' : 'text-gray-500 font-medium'} mt-0.5">${label}</div>
                </div>
            `;
        });

        row.innerHTML = html;
    }

    function formatNumNoDec(n) {
        return parseFloat(n).toLocaleString('en-IN', { maximumFractionDigits: 0 });
    }

    function togglePricingMode() {
        setPricingMode(currentMode === 'wholesale' ? 'onepiece' : 'wholesale');
    }

    function formatNum(n) {
        return parseFloat(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // (Removed unused duplicate updateVariantQty)

    function toggleAllSpecs() {
        const extras = document.querySelectorAll('.spec-row-extra');
        const txtEl = document.getElementById('specsBtnText');
        const chevron = document.getElementById('specsChevron');
        if (!extras || extras.length === 0) return;

        const isCollapsed = extras[0].classList.contains('hidden');
        extras.forEach(el => el.classList.toggle('hidden'));

        if (isCollapsed) {
            if (txtEl) txtEl.textContent = 'Show less specifications';
            if (chevron) chevron.classList.add('rotate-180');
        } else {
            if (txtEl) txtEl.textContent = 'Show all specifications';
            if (chevron) chevron.classList.remove('rotate-180');
        }
    }

    function toggleProductDesc() {
        const content = document.getElementById('productDescContent');
        const txtEl = document.getElementById('descHeaderBtnText');
        const chevron = document.getElementById('descChevron');
        if (!content) return;

        const isHidden = content.classList.contains('hidden');
        content.classList.toggle('hidden');

        if (isHidden) {
            if (txtEl) txtEl.textContent = 'Hide description';
            if (chevron) chevron.classList.add('rotate-180');
        } else {
            if (txtEl) txtEl.textContent = 'Show description';
            if (chevron) chevron.classList.remove('rotate-180');
        }
    }

    function selectVariant(el) {
        document.querySelectorAll('.variant-row').forEach(r => {
            r.classList.remove('bg-orange-50/40', 'border-l-3', 'border-l-[#f05a29]');
        });
        el.classList.add('bg-orange-50/40', 'border-l-3', 'border-l-[#f05a29]');
        selectedVariantEl = el;

        const vImg = el.dataset.img;
        if (vImg) {
            const mainImg = document.getElementById('mainProductImage');
            if (mainImg) mainImg.src = vImg;
        }

        // Dynamically update single price display when variant selected
        const vWholesale = parseFloat(el.dataset.wholesale) || 0;
        const vOnePiece = parseFloat(el.dataset.onepiece) || 0;
        const targetPrice = (currentMode === 'wholesale') ? (vWholesale || WS_START) : (vOnePiece || OP_START);
        const priceEl = document.getElementById('priceDisplay');
        if (priceEl && targetPrice > 0) priceEl.textContent = formatNum(targetPrice);
    }

    const GALLERY_IMAGES = <?= json_encode(array_values($gallery)) ?>;
    let currentImgIdx = 0;

    function switchImage(idx, src) {
        currentImgIdx = idx;
        const mainImg = document.getElementById('mainProductImage');
        if (mainImg) mainImg.src = src;
        document.querySelectorAll('.thumb-btn').forEach(btn => {
            const isActive = parseInt(btn.dataset.idx) === idx;
            btn.className = `thumb-btn shrink-0 w-16 h-16 rounded-xl border-2 overflow-hidden transition-all border-0 focus:outline-none cursor-pointer ${isActive ? 'border-2 border-[#f05a29]' : 'border-2 border-gray-200 hover:border-gray-400'}`;
        });
        const activeThumb = document.querySelector(`.thumb-btn[data-idx="${idx}"]`);
        if (activeThumb) {
            activeThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    }

    function nextImage() {
        if (!GALLERY_IMAGES || GALLERY_IMAGES.length <= 1) return;
        currentImgIdx = (currentImgIdx + 1) % GALLERY_IMAGES.length;
        switchImage(currentImgIdx, GALLERY_IMAGES[currentImgIdx]);
    }

    function prevImage() {
        if (!GALLERY_IMAGES || GALLERY_IMAGES.length <= 1) return;
        currentImgIdx = (currentImgIdx - 1 + GALLERY_IMAGES.length) % GALLERY_IMAGES.length;
        switchImage(currentImgIdx, GALLERY_IMAGES[currentImgIdx]);
    }

    function scrollThumbs(dir) {
        const strip = document.getElementById('thumbStrip');
        if (strip) {
            strip.scrollBy({ left: dir === 'left' ? -140 : 140, behavior: 'smooth' });
        }
    }

    function openLightbox(src) {
        const modal = document.getElementById('lightboxModal');
        const img = document.getElementById('lightboxImg');
        img.src = src;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeLightbox() {
        const modal = document.getElementById('lightboxModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    window.rfqGetProductContextFromPage = function () {
        const activeMode = typeof currentMode !== 'undefined' ? currentMode : 'wholesale';
        const detailQty = parseInt(document.getElementById('detailQtyInput')?.value) || 1;
        const mainImg = document.getElementById('mainProductImage')?.src || <?= json_encode($mainImage) ?>;

        const vars = (typeof VARIANTS_LIST !== 'undefined' && VARIANTS_LIST) ? VARIANTS_LIST.map((v) => {
            return {
                id: v.id,
                code: v.code,
                label: v.label,
                value: v.value,
                stock: v.stock,
                wholesale_price: v.wholesale_price,
                one_piece_price: v.one_piece_price,
                image: v.image,
                checked: false,
                qty: 0
            };
        }) : [];

        return {
            id: <?= (int) $product['id'] ?>,
            name: <?= json_encode($product['name'] ?? '') ?>,
            sku: <?= json_encode($product['sku'] ?? '') ?>,
            moq: <?= (int) ($product['moq'] ?? 1) ?>,
            url: window.location.href,
            main_image: mainImg,
            gallery: (typeof GALLERY_IMAGES !== 'undefined' && GALLERY_IMAGES) ? GALLERY_IMAGES : [mainImg],
            pricingMode: activeMode,
            selectedVariantIndex: (typeof selectedVariantIndex !== 'undefined') ? selectedVariantIndex : 0,
            variants: vars
        };
    };

    function openRfqWithProducts() {
        const prodData = window.rfqGetProductContextFromPage();
        if (typeof openRfqModal === 'function') {
            openRfqModal(prodData);
        } else {
            window.open(`https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent('Hi, I want a quote for: ' + PRODUCT_NAME)}`, '_blank');
        }
    }

    const VARIANTS_LIST = <?= json_encode($variantsJsonData) ?>;
    let selectedVariantIndex = 0;

    function selectAmazonVariant(idx) {
        if (!VARIANTS_LIST || !VARIANTS_LIST[idx]) return;
        selectedVariantIndex = idx;
        const v = VARIANTS_LIST[idx];

        // 1. Update Swatch Styles
        document.querySelectorAll('.amazon-swatch-btn').forEach((btn, i) => {
            const isOos = VARIANTS_LIST[i].stock <= 0;
            if (i === idx) {
                btn.className = `amazon-swatch-btn relative flex items-center gap-2 px-2.5 py-1.5 rounded-xl border-2 border-[#f05a29] bg-orange-50/30 ring-2 ring-orange-100 shadow-2xs transition-all cursor-pointer focus:outline-none select-none ${isOos ? 'opacity-50 grayscale bg-gray-50' : ''}`;
            } else {
                btn.className = `amazon-swatch-btn relative flex items-center gap-2 px-2.5 py-1.5 rounded-xl border border-gray-200 hover:border-gray-400 bg-white transition-all cursor-pointer focus:outline-none select-none ${isOos ? 'opacity-50 grayscale bg-gray-50' : ''}`;
            }
        });

        // 2. Update Header Titles & Badges
        const titleEl = document.getElementById('selectedVariantTitle');
        if (titleEl) titleEl.textContent = v.value;

        const codeBadge = document.getElementById('selectedVariantCodeBadge');
        if (codeBadge) codeBadge.textContent = v.code || '';

        // 3. Update Stock Status
        const stockBadge = document.getElementById('variantStockStatusText');
        const mainStockBadge = document.getElementById('activeStockBadge');
        if (v.stock > 0) {
            if (stockBadge) {
                stockBadge.textContent = 'In stock';
                stockBadge.className = 'text-[11px] font-semibold text-emerald-600';
            }
            if (mainStockBadge) {
                mainStockBadge.textContent = 'In Stock';
                mainStockBadge.className = 'font-semibold text-emerald-600';
            }
        } else {
            if (stockBadge) {
                stockBadge.textContent = 'Out of Stock';
                stockBadge.className = 'text-[11px] font-semibold text-red-500';
            }
            if (mainStockBadge) {
                mainStockBadge.textContent = 'Out of Stock';
                mainStockBadge.className = 'font-semibold text-red-500';
            }
        }

        // 4. Update Main Product Image
        if (v.image) {
            const mainImg = document.getElementById('mainProductImage');
            if (mainImg) mainImg.src = v.image;
        }

        // 5. Update Tier Pricing Cards for selected variant
        if (v && v.tiers) {
            renderVariantTiers(v.tiers);
        }

        // 5. Dynamic Price Update for current active mode
        const targetPrice = (currentMode === 'wholesale') ? (v.wholesale_price || WS_START) : (v.one_piece_price || OP_START);
        const priceEl = document.getElementById('priceDisplay');
        if (priceEl && targetPrice > 0) priceEl.textContent = formatNum(targetPrice);

        // 6. Update URL to clean SEO route without page reload
        const baseUrl = <?= json_encode($canonicalUrl) ?>;
        if (v.code) {
            const cleanUrl = baseUrl + '/' + encodeURIComponent(v.code);
            window.history.replaceState(null, '', cleanUrl);
        } else {
            window.history.replaceState(null, '', baseUrl);
        }

        // 7. Sync Bottom Accordion if present
        const bottomRow = document.querySelector(`.variant-row[data-variant-idx="${idx}"]`);
        if (bottomRow) {
            document.querySelectorAll('.variant-row').forEach(r => {
                r.classList.remove('bg-orange-50/40', 'border-l-4', 'border-l-[#f05a29]');
            });
            bottomRow.classList.add('bg-orange-50/40', 'border-l-4', 'border-l-[#f05a29]');
        }
    }

    function toggleVariantRow(el) {
        const idx = parseInt(el.dataset.variantIdx);
        if (!isNaN(idx)) selectAmazonVariant(idx);

        const item = el.closest('.variant-item');
        if (!item) return;
        const drawer = item.querySelector('.variant-drawer');
        const chevron = item.querySelector('.variant-chevron');

        document.querySelectorAll('.variant-drawer').forEach(d => {
            if (d !== drawer) d.classList.add('hidden');
        });
        document.querySelectorAll('.variant-chevron').forEach(c => {
            if (c !== chevron) c.classList.remove('rotate-180');
        });

        if (drawer) {
            drawer.classList.toggle('hidden');
            if (chevron) chevron.classList.toggle('rotate-180');
        }
    }

    function openVariantModalDetails(name, wholesale, onepiece, stock, img) {
        document.getElementById('vModalTitle').textContent = name;
        document.getElementById('vModalImg').src = img;
        document.getElementById('vModalStock').textContent = parseInt(stock) > 0 ? 'In stock' : 'Out of stock';
        document.getElementById('vModalWholesale').textContent = `₹${formatNum(parseFloat(wholesale))}`;
        document.getElementById('vModalOnePiece').textContent = `₹${formatNum(parseFloat(onepiece))}`;
        const waText = encodeURIComponent(`Hi, I am interested in variant: ${name} of product: ${PRODUCT_NAME}`);
        document.getElementById('vModalWaBtn').href = `https://wa.me/${WHATSAPP_NUMBER}?text=${waText}`;
        const modal = document.getElementById('variantQuickViewModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeVariantQuickView() {
        const modal = document.getElementById('variantQuickViewModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    document.addEventListener('DOMContentLoaded', () => {
        setPricingMode('wholesale');

        // Check for deep-linked variant from route or URL param
        let urlVariantCode = <?= json_encode($initialVariantCode ?? '') ?>;
        if (!urlVariantCode) {
            const urlParams = new URLSearchParams(window.location.search);
            urlVariantCode = urlParams.get('variant');
            if (!urlVariantCode) {
                const pathSegments = window.location.pathname.split('/').filter(Boolean);
                const prodIndex = pathSegments.indexOf('product');
                if (prodIndex !== -1 && pathSegments.length > prodIndex + 2) {
                    urlVariantCode = decodeURIComponent(pathSegments[prodIndex + 2]);
                }
            }
        }

        if (urlVariantCode && VARIANTS_LIST && VARIANTS_LIST.length > 0) {
            const foundIdx = VARIANTS_LIST.findIndex(v => v.code && v.code.toLowerCase() === urlVariantCode.toLowerCase());
            if (foundIdx !== -1) {
                selectAmazonVariant(foundIdx);
                return;
            }
        }

        checkDetailWishlistStatus();
    });

    // ========================================================
    // CART & WISHLIST DETAIL FUNCTIONS
    // ========================================================
    let cartSyncDebounceTimers = {};

    function updateVariantQty(vi, delta) {
        const span = document.getElementById('vQtyVal_' + vi);
        if (!span) return;
        let val = parseInt(span.textContent) || 0;
        val = Math.max(0, val + delta);
        span.textContent = val;

        // Auto Sync with Cart via AJAX in background on stepper click!
        if (cartSyncDebounceTimers[vi]) clearTimeout(cartSyncDebounceTimers[vi]);
        cartSyncDebounceTimers[vi] = setTimeout(() => {
            syncVariantToCart(vi, val);
        }, 300);
    }

    async function syncVariantToCart(vi, qty) {
        if (!VARIANTS_LIST || !VARIANTS_LIST[vi]) return;
        const v = VARIANTS_LIST[vi];
        const span = document.getElementById('vQtyVal_' + vi);
        const oldVal = span ? span.textContent : '0';

        const payload = new URLSearchParams();
        payload.append('product_id', <?= (int) $product['id'] ?>);
        if (v.id) payload.append('variant_id', v.id);
        payload.append('quantity', qty);
        payload.append('set_exact_qty', '1');
        payload.append('pricing_mode', currentMode);

        try {
            const res = await fetch('<?= url('cart/add') ?>', { method: 'POST', body: payload });
            const data = await res.json();
            if (data.success) {
                if (typeof updateHeaderCartBadge === 'function') {
                    updateHeaderCartBadge(data.cart_count);
                }
                updateOrderSummarySidebar(data.cart_count, data.subtotal);
                if (typeof renderCartDrawerUI === 'function') {
                    renderCartDrawerUI(data.items, data.subtotal, data.cart_count);
                }
                if (typeof showCartToast === 'function') {
                    showCartToast(qty > 0 ? 'Cart updated!' : 'Item removed from cart');
                }
            } else {
                if (span) span.textContent = oldVal;
                if (typeof showCartToast === 'function') {
                    showCartToast(data.message || 'Failed to update cart');
                }
            }
        } catch (e) {
            if (span) span.textContent = oldVal;
            if (typeof showCartToast === 'function') {
                showCartToast('Network error while updating cart');
            }
        }
    }

    function handleCartRemoveResponse(data) {
        if (!data || !VARIANTS_LIST) return;
        const currentProductId = <?= (int) $product['id'] ?>;
        if (parseInt(data.product_id) !== currentProductId) return;
        if (data.pricing_mode && data.pricing_mode !== currentMode) return;

        if (data.variant_id) {
            const vi = VARIANTS_LIST.findIndex(v => parseInt(v.id) === parseInt(data.variant_id));
            if (vi !== -1) {
                const span = document.getElementById('vQtyVal_' + vi);
                if (span) span.textContent = data.cart_qty !== undefined ? data.cart_qty : 0;
            }
        }
    }

    let initialCartSynced = false;
    function syncExistingCartToSteppers(items) {
        if (initialCartSynced || !VARIANTS_LIST || !items) return;
        initialCartSynced = true;
        const currentProductId = <?= (int) $product['id'] ?>;
        items.forEach(item => {
            if (parseInt(item.product_id) === currentProductId) {
                const vi = VARIANTS_LIST.findIndex(v => parseInt(v.id) === parseInt(item.variant_id));
                if (vi !== -1) {
                    const span = document.getElementById('vQtyVal_' + vi);
                    if (span) span.textContent = item.quantity;
                }
            }
        });
    }

    function updateOrderSummarySidebar(count, subtotalStr) {
        const qtyEl = document.getElementById('summaryQtyText');
        const totalEl = document.getElementById('summaryTotalText');
        if (qtyEl) qtyEl.textContent = (count || 0) + ' units';
        if (totalEl) totalEl.textContent = '₹' + (subtotalStr || '0.00');
    }

    function changeDetailQty(delta) {
        const inp = document.getElementById('detailQtyInput');
        if (!inp) return;
        let val = parseInt(inp.value) || 1;
        val = Math.max(1, val + delta);
        inp.value = val;
    }

    function onDetailQtyInputChange() {
        const inp = document.getElementById('detailQtyInput');
        if (!inp) return;
        let val = parseInt(inp.value) || 1;
        if (val < 1) val = 1;
        inp.value = val;
    }

    let detailAddToCartInFlight = false;
    async function addToCartFromDetail() {
        if (detailAddToCartInFlight) return;
        detailAddToCartInFlight = true;

        const qtyInp = document.getElementById('detailQtyInput');
        let qty = parseInt(qtyInp ? qtyInp.value : 1);
        if (isNaN(qty) || qty < 1) qty = 1;

        const payload = new URLSearchParams();
        payload.append('product_id', <?= (int) $product['id'] ?>);
        payload.append('quantity', qty);
        payload.append('pricing_mode', currentMode);

        try {
            const res = await fetch('<?= url('cart/add') ?>', { method: 'POST', body: payload });
            const data = await res.json();
            if (data.success) {
                if (typeof showCartToast === 'function') {
                    showCartToast('Item added to cart!');
                } else {
                    alert('Added to cart!');
                }
                if (typeof updateHeaderCartBadge === 'function') {
                    updateHeaderCartBadge(data.cart_count);
                }
                updateOrderSummarySidebar(data.cart_count, data.subtotal);
                if (typeof renderCartDrawerUI === 'function') {
                    renderCartDrawerUI(data.items, data.subtotal, data.cart_count);
                }
            } else {
                alert(data.message || 'Could not add to cart');
            }
        } catch (e) {
            alert('Error adding to cart');
        } finally {
            detailAddToCartInFlight = false;
        }
    }

    async function buyNowFromDetail() {
        await addToCartFromDetail();
        window.location.href = '<?= url('checkout') ?>';
    }

    async function checkDetailWishlistStatus() {
        try {
            const res = await fetch('<?= url('wishlist/status?product_id=' . $product['id']) ?>');
            const data = await res.json();
            if (data.success && data.saved) {
                setDetailWishlistActive(true);
            }
            if (data.count !== undefined && document.getElementById('headerWishlistCount')) {
                const wBadge = document.getElementById('headerWishlistCount');
                if (data.count > 0) {
                    wBadge.textContent = data.count;
                    wBadge.style.display = 'flex';
                } else {
                    wBadge.style.display = 'none';
                }
            }
        } catch (e) { }
    }

    async function toggleDetailWishlist() {
        const payload = new URLSearchParams();
        payload.append('product_id', <?= (int) $product['id'] ?>);
        try {
            const res = await fetch('<?= url('wishlist/toggle') ?>', { method: 'POST', body: payload });
            const data = await res.json();
            if (data.success) {
                setDetailWishlistActive(data.saved);
                if (data.count !== undefined && document.getElementById('headerWishlistCount')) {
                    const wBadge = document.getElementById('headerWishlistCount');
                    if (data.count > 0) {
                        wBadge.textContent = data.count;
                        wBadge.style.display = 'flex';
                    } else {
                        wBadge.style.display = 'none';
                    }
                }
                if (typeof showCartToast === 'function') {
                    showCartToast(data.message);
                }
            }
        } catch (e) { }
    }

    function setDetailWishlistActive(saved) {
        const icon = document.getElementById('detailWishlistIcon');
        const floatIcon = document.getElementById('floatingWishlistIcon');
        const txt = document.getElementById('detailWishlistText');
        const btn = document.getElementById('detailWishlistBtn');
        const floatBtn = document.getElementById('floatingWishlistBtn');

        if (saved) {
            if (icon) {
                icon.setAttribute('fill', '#ef4444');
                icon.classList.remove('text-gray-400');
                icon.classList.add('text-red-500');
            }
            if (floatIcon) {
                floatIcon.setAttribute('fill', '#ef4444');
                floatIcon.classList.remove('text-gray-400');
                floatIcon.classList.add('text-red-500');
            }
            if (txt) txt.textContent = 'Saved in Wishlist';
            if (btn) btn.classList.add('border-red-200', 'bg-rose-50/50');
            if (floatBtn) floatBtn.classList.add('border-red-200');
        } else {
            if (icon) {
                icon.setAttribute('fill', 'none');
                icon.classList.remove('text-red-500');
                icon.classList.add('text-gray-400');
            }
            if (floatIcon) {
                floatIcon.setAttribute('fill', 'none');
                floatIcon.classList.remove('text-red-500');
                floatIcon.classList.add('text-gray-400');
            }
            if (txt) txt.textContent = 'Save to Wishlist';
            if (btn) btn.classList.remove('border-red-200', 'bg-rose-50/50');
            if (floatBtn) floatBtn.classList.remove('border-red-200');
        }
    }
</script>

<!-- VARIANT QUICK VIEW MODAL -->
<div id="variantQuickViewModal"
    class="fixed inset-0 z-[9999] bg-black/60 backdrop-blur-xs items-center justify-center p-4 hidden">
    <div
        class="bg-white border border-gray-200 rounded-2xl max-w-md w-full p-6 space-y-4 shadow-2xl relative animate-scale-in">
        <button type="button" onclick="closeVariantQuickView()"
            class="absolute top-4 right-4 w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 text-gray-600 flex items-center justify-center font-semibold text-sm border-0 cursor-pointer">
            ✕
        </button>
        <div class="flex items-center gap-4 border-b border-gray-100 pb-4">
            <div class="w-16 h-16 rounded-xl border border-gray-200 overflow-hidden shrink-0 bg-white">
                <img id="vModalImg" src="" class="w-full h-full object-cover">
            </div>
            <div>
                <h4 id="vModalTitle" class="text-base font-semibold text-gray-900"></h4>
                <div id="vModalStock" class="text-xs text-emerald-600 font-semibold mt-0.5"></div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200/80">
                <div class="text-[10px] text-gray-400 font-semibold uppercase">Wholesale Price</div>
                <div id="vModalWholesale" class="text-base font-semibold text-[#f05a29]"></div>
            </div>
            <div class="bg-gray-50 p-3 rounded-xl border border-gray-200/80">
                <div class="text-[10px] text-gray-400 font-semibold uppercase">One-Piece Price</div>
                <div id="vModalOnePiece" class="text-base font-semibold text-emerald-600"></div>
            </div>
        </div>
        <div class="pt-2 flex items-center gap-2">
            <a id="vModalWaBtn" href="#" target="_blank"
                class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-full text-center flex items-center justify-center gap-1.5 transition border-0">
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                    <path
                        d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
                </svg>
                Inquire for this Variant
            </a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>