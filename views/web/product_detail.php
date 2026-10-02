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

$cartWishlistState = get_cart_and_wishlist_state();
$initialCartCount = $cartWishlistState['cart_count'];
$initialWishlistProductIds = $cartWishlistState['wishlist_product_ids'] ?? [];
$isWished = in_array((int)($product['id'] ?? 0), $initialWishlistProductIds);

// Safe Description Formatting
function formatProductDescription($text) {
    if (empty($text)) return '';
    $text = strip_tags($text);
    $text = preg_replace('/(\s*)([✦•])(\s*)/u', "\n$2 ", $text);
    $text = trim($text);
    $lines = explode("\n", $text);
    
    $intro = [];
    $features = [];
    $specs = [];
    $taglines = [];
    
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        
        if (mb_strpos($line, '✦') === 0) {
            $line = trim(mb_substr($line, 1));
            $parts = explode(':', $line, 2);
            if (count($parts) === 2) {
                $specs[] = ['key' => trim($parts[0]), 'value' => trim($parts[1])];
            } else {
                $specs[] = ['key' => $line, 'value' => ''];
            }
        } elseif (mb_strpos($line, '•') === 0) {
            $features[] = trim(mb_substr($line, 1));
        } else {
            if (empty($features) && empty($specs)) {
                $intro[] = htmlspecialchars($line);
            } else {
                $taglines[] = htmlspecialchars($line);
            }
        }
    }
    
    $html = '';
    if (!empty($intro)) {
        $html .= '<p style="margin:0 0 12px 0; font-size:13px; line-height:1.6; color:#333;">' . implode('<br>', $intro) . '</p>';
    }
    if (!empty($specs)) {
        $html .= '<div style="margin-bottom:12px; display:flex; flex-direction:column; gap:4px;">';
        foreach ($specs as $s) {
            if ($s['value'] === '') {
                $html .= '<div style="font-size:12.5px;"><span style="color:#888;">' . htmlspecialchars($s['key']) . '</span></div>';
            } else {
                $html .= '<div style="font-size:12.5px;"><span style="color:#888; font-weight:400; margin-right:4px;">' . htmlspecialchars($s['key']) . ':</span> <span style="color:#222; font-weight:500;">' . htmlspecialchars($s['value']) . '</span></div>';
            }
        }
        $html .= '</div>';
    }
    if (!empty($features)) {
        $html .= '<ul style="margin:0 0 12px 0; padding-left:18px; list-style-type:disc; font-size:13px; line-height:1.6; color:#333;">';
        foreach ($features as $f) {
            $html .= '<li style="margin-bottom:4px;">' . htmlspecialchars($f) . '</li>';
        }
        $html .= '</ul>';
    }
    if (!empty($taglines)) {
        $html .= '<p style="margin:0; font-size:12.5px; color:#888; font-style:italic;">' . implode('<br>', $taglines) . '</p>';
    }
    return $html;
}

$descHtml = formatProductDescription($product['description'] ?? '');

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
$waNumber = preg_replace('/[^0-9]/', '', $whatsappNumber ?? '919540317079');

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

$isDoubleMode = ($product['variation_mode'] ?? '') === 'double';
$groupedColors = [];
if ($isDoubleMode) {
    foreach ($variants as $vi => $v) {
        $parts = explode(' - ', $v['attribute_value'] ?? '');
        $colorName = trim($parts[0] ?? $v['attribute_value']);
        if (strtolower($colorName) === 'default' || $colorName === '') {
            continue;
        }
        $vWs = (float) $v['wholesale_price'];
        $vStock = (int) $v['stock_quantity'];

        if (!isset($groupedColors[$colorName])) {
            $groupedColors[$colorName] = [
                'name'      => $colorName,
                'image'     => !empty($v['image_url']) ? asset($v['image_url']) : $mainImage,
                'min_price' => $vWs,
                'max_price' => $vWs,
                'size_count'=> 1,
                'all_oos'   => ($vStock <= 0),
            ];
        } else {
            if ($vWs > 0 && ($groupedColors[$colorName]['min_price'] == 0 || $vWs < $groupedColors[$colorName]['min_price'])) {
                $groupedColors[$colorName]['min_price'] = $vWs;
            }
            if ($vWs > $groupedColors[$colorName]['max_price']) {
                $groupedColors[$colorName]['max_price'] = $vWs;
            }
            $groupedColors[$colorName]['size_count']++;
            if ($vStock > 0) $groupedColors[$colorName]['all_oos'] = false;
        }
    }
}
$optionsCount = $isDoubleMode ? count($groupedColors) : $varCount;

$variantsJsonData = array_map(function ($v) use ($mainImage, $prodTiers, $varTiersMap, $isDoubleMode) {
    $vId = (int) $v['id'];
    $vTiers = !empty($varTiersMap[$vId]) ? $varTiersMap[$vId] : [];
    
    $colorKey = '';
    $sizeKey  = $v['attribute_value'] ?? '';
    if ($isDoubleMode) {
        $parts    = explode(' - ', $v['attribute_value'] ?? '');
        $colorKey = trim($parts[0] ?? '');
        $sizeKey  = isset($parts[1]) ? trim(implode(' - ', array_slice($parts, 1))) : trim($parts[0] ?? '');
    }
    return [
        'id'              => $vId,
        'code'            => $v['variant_code'] ?? $v['sku'] ?? '',
        'label'           => $v['attribute_label'] ?? 'Color',
        'value'           => $v['attribute_value'] ?? '',
        'color'           => $colorKey,
        'size'            => $sizeKey,
        'stock'           => (int) ($v['stock_quantity'] ?? 0),
        'wholesale_price' => (float) ($v['wholesale_price'] ?? 0),
        'one_piece_price' => (float) ($v['one_piece_price'] ?? 0),
        'compare_at_price'=> (float) ($v['compare_at_price'] ?? $v['mrp'] ?? 0),
        'image'           => !empty($v['image_url']) ? asset($v['image_url']) : $mainImage,
        'weight'          => $v['weight'] ?? '',
        'dimensions'      => $v['dimensions'] ?? '',
        'is_active'       => (int) ($v['is_active'] ?? 1),
        'tiers'           => $vTiers
    ];
}, $variants ?? []);

ob_start();
?>

<style>
/* Gallery Main Container — Strictly Locked 1:1 Aspect Ratio */
.product-cover-card {
    position: relative !important;
    width: 100% !important;
    aspect-ratio: 1 / 1 !important;
    overflow: hidden !important;
    background-color: #f8fafc !important;
    box-sizing: border-box !important;
}
.product-cover-card img {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    display: block !important;
    transition: opacity 0.15s ease-in-out;
}
@media (max-width: 767px) {
    .floating-need-help-btn { display: none !important; }
    .product-cover-card { border-radius: 0 !important; }
    #topHeaderWrapper { display: none !important; }
}

@media (min-width: 768px) {
    .mobile-custom-header { display: none !important; }
    
    .product-page {
        max-width: 1440px;
        margin: 0 auto;
        padding: 20px 24px 40px;
        box-sizing: border-box;
    }
    
    .product-top {
        display: grid !important;
        grid-template-columns: 340px minmax(0, 1fr);
        gap: 24px;
        align-items: start;
    }
    .product-main {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .product-gallery, .product-side {
        position: sticky;
        top: 120px;
        align-self: start;
    }
    .product-specs, .product-description {
        min-width: 0;
        max-width: 100%;
        box-sizing: border-box;
        background: #fff;
        border-radius: 12px;
        padding: 16px 20px;
    }
    .product-specs .spec-list {
        display: grid;
        grid-template-columns: 38% minmax(0, 1fr) !important;
        column-gap: 12px;
    }
    .product-specs dt, .product-specs dd {
        overflow-wrap: anywhere;
        min-width: 0;
    }

    .product-gallery {
        grid-column: 1;
        grid-row: 1;
        width: 100%;
    }
    .product-main {
        grid-column: 2;
        grid-row: 1;
    }
    .product-side {
        display: none !important;
    }
}

@media (min-width: 1024px) {
    .product-top {
        grid-template-columns: 340px minmax(0, 1fr) 280px;
    }
    .product-side {
        display: flex !important;
        grid-column: 3;
        grid-row: 1;
        flex-direction: column;
    }
    .product-side > div {
        flex: auto;
    }
}

@media (min-width: 1280px) {
    .product-top {
        grid-template-columns: 440px minmax(0, 1fr) 340px;
    }
}

.footer-bottom {
    padding-right: 160px !important;
}

.thumb-btn {
    flex: 0 0 calc((100% - 32px) / 5) !important;
    width: calc((100% - 32px) / 5) !important;
    aspect-ratio: 1 / 1 !important;
    border-radius: 8px !important;
    overflow: hidden !important;
    padding: 0 !important;
    box-sizing: border-box !important;
    border: 2px solid transparent !important;
    background: #f8fafc !important;
}
.thumb-btn.is-active {
    border-color: #f05a29 !important;
}

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.spec-list {
    display: grid;
    grid-template-columns: 42% 1fr;
    column-gap: 8px;
    row-gap: 8px;
    margin: 0;
    padding: 0;
}
.spec-list dt, .spec-list dd {
    margin: 0;
    padding: 9px 12px;
    font-size: 12.5px;
    line-height: 1.35;
    min-width: 0;
    overflow-wrap: anywhere;
    border-radius: 8px;
    display: flex;
    align-items: center;
}
.spec-list dt { 
    color: #1e293b; 
    font-weight: 700; 
    background-color: #f8fafc;
    border: 1.5px solid #cbd5e1;
}
.spec-list dd { 
    color: #0f172a; 
    font-weight: 800; 
    text-align: left; 
    background-color: #ffffff;
    border: 1.5px solid #e2e8f0;
}
@media (prefers-color-scheme: dark) {
    .spec-list dt { background-color: #1e293b; border-color: #334155; color: #f8fafc; }
    .spec-list dd { background-color: #0f172a; border-color: #1e293b; color: #ffffff; }
}

.view-more-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 500;
    color: #f05a29;
    background: none;
    border: none;
    padding: 14px 0;
    line-height: 1;
    width: 100%;
    cursor: pointer;
}
.view-more-btn svg {
    width: 12px;
    height: 12px;
    stroke: currentColor;
    transition: transform 0.2s;
}
.view-more-btn.expanded svg {
    transform: rotate(180deg);
}

.price-mode {
    display: inline-flex;
    align-items: center;
    gap: 2px;
    padding: 3px;
    height: auto;
    width: auto;
    max-width: 100%;
    flex: 0 0 auto;
    background: #f1f1f1;
    border: 1px solid #e3e3e3;
    border-radius: 999px;
    overflow: visible;
    box-sizing: border-box;
}
.price-mode__btn {
    all: unset;
    box-sizing: border-box;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 26px;
    padding: 0 12px;
    font-size: 12px;
    font-weight: 500;
    line-height: 1;
    letter-spacing: 0;
    white-space: nowrap;
    color: #777;
    border-radius: 999px;
    cursor: pointer;
    transition: background .18s, color .18s;
}
.price-mode__btn.is-active {
    background: #1f2a3c;
    color: #fff;
    font-weight: 600;
}
</style>

<div class="product-page w-full mx-auto px-0 md:px-4 py-0 md:py-6 font-sans text-gray-900">
    <div class="product-top w-full flex flex-col items-start gap-0 md:gap-6">

        <!-- LEFT — IMAGE GALLERY -->
        <div class="product-gallery w-full bg-white md:rounded-2xl relative overflow-hidden">
            <div class="mobile-custom-header md:hidden absolute top-0 left-0 w-full z-50 flex items-center justify-between p-3" style="background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, transparent 100%);">
                <button type="button" onclick="window.history.back()" class="w-8 h-8 rounded-full bg-black/30 flex items-center justify-center text-white cursor-pointer backdrop-blur-sm border-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <button type="button" onclick="openCartDrawer()" class="w-8 h-8 rounded-full bg-black/30 flex items-center justify-center text-white cursor-pointer backdrop-blur-sm border-0 relative">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    <div class="absolute -top-1 -right-1 bg-[#f05a29] text-[9px] w-4 h-4 rounded-full flex items-center justify-center font-bold" id="mobileCustomCartCount" style="display: <?= ($initialCartCount ?? 0) > 0 ? 'flex' : 'none' ?>"><?= (int)($initialCartCount ?? 0) ?></div>
                </button>
            </div>

            <!-- Main Image Card -->
            <div id="mainImgCardWrapper" class="product-cover-card relative w-full aspect-square bg-slate-50 overflow-hidden group/mainimg">
                <img id="mainProductImage" src="<?= htmlspecialchars($mainImage) ?>" alt="<?= $productName ?>"
                    width="600" height="600"
                    class="w-full h-full object-cover cursor-zoom-in"
                    onclick="openLightbox(this.src)">
            </div>

            <!-- Thumbnail Strip -->
            <?php if ($totalImgs > 1): ?>
                <div class="relative bg-white px-3 py-2 border-t border-gray-100">
                    <div class="flex gap-2 overflow-x-auto scroll-smooth snap-x snap-mandatory no-scrollbar py-1 items-center" id="thumbStrip">
                        <?php foreach ($gallery as $idx => $imgUrl): ?>
                            <button type="button" onclick="switchImage(<?= $idx ?>, '<?= htmlspecialchars(addslashes($imgUrl)) ?>')"
                                class="thumb-btn flex-shrink-0 snap-start <?= $idx === 0 ? 'is-active' : '' ?>"
                                data-idx="<?= $idx ?>">
                                <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Thumbnail <?= $idx+1 ?>" class="w-full h-full object-cover pointer-events-none" loading="lazy">
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- MIDDLE — INFO BOX -->
        <div class="product-main w-full flex flex-col gap-1 sm:gap-2 min-w-0">
            <div class="product-info w-full flex flex-col gap-1 sm:gap-2 mt-0 bg-[#f5f5f5] min-w-0">

                <!-- Title & Price Block -->
                <div class="bg-white p-3 sm:p-4 md:p-5 md:rounded-2xl shadow-sm border-b border-gray-100 md:border-0">
                    <!-- Title & Stats -->
                    <div class="flex gap-2 justify-between items-start mb-2">
                        <h1 class="text-xs font-semibold text-gray-800 leading-snug flex-1" id="selectedVariantTitle">
                            <?= $productName ?>
                        </h1>
                        <div class="flex flex-col items-end flex-shrink-0 pl-2">
                            <div class="text-[9px] sm:text-[10px] text-gray-500 font-medium whitespace-nowrap bg-gray-50 px-1.5 py-0.5 rounded">4K+ sold</div>
                        </div>
                    </div>

                    <?php
                    $initialTiers = !empty($variantsJsonData[0]['tiers']) ? $variantsJsonData[0]['tiers'] : ($productTiers ?? []);
                    if (empty($initialTiers)) {
                        $initialTiers = [['min_qty' => $moq, 'max_qty' => null, 'unit_price' => $wholesaleStartPrice]];
                    }
                    $tierPrices = array_column($initialTiers, 'unit_price');
                    if (!empty($tierPrices)) {
                        $minP = min($tierPrices);
                        $maxP = max($tierPrices);
                        $isRange = ($minP != $maxP);
                        $wholesaleDisplayPrice = $isRange ? rtrim(rtrim(number_format($minP, 2), '0'), '.') . ' - ' . rtrim(rtrim(number_format($maxP, 2), '0'), '.') : rtrim(rtrim(number_format($minP, 2), '0'), '.');
                    } else {
                        $isRange = false;
                        $wholesaleDisplayPrice = rtrim(rtrim(number_format($wholesaleStartPrice, 2), '0'), '.');
                    }
                    
                    $hasSinglePrice = (isset($onePieceStartPrice) && $onePieceStartPrice > 0);
                    $singleDisplayPrice = rtrim(rtrim(number_format($onePieceStartPrice ?? $wholesaleStartPrice, 2), '0'), '.');
                    ?>
                    <!-- Price & Toggle Row -->
                    <div class="flex items-center justify-between mb-2 gap-[8px] flex-wrap">
                        <!-- Price Box (DEFAULT MODE: SINGLE PRICE) -->
                        <div id="bigOrangePriceContainer" class="flex flex-col shrink-0">
                            <div class="flex items-baseline gap-1.5 font-bold tracking-tight" style="color: #f05a29; font-size: 18px; line-height: 1.1;">
                                <span class="text-xs pb-0.5">₹</span>
                                <span id="priceDisplay" data-wsprice="<?= htmlspecialchars($wholesaleDisplayPrice) ?>" data-wsisrange="<?= $isRange ? '1' : '0' ?>" data-opprice="<?= htmlspecialchars($singleDisplayPrice) ?>"><?= htmlspecialchars($singleDisplayPrice) ?></span>
                                <span id="compareAtPriceDisplay" class="text-xs text-gray-400 line-through font-normal ml-1" style="display:none;"></span>
                                <span class="text-[9px] sm:text-[10px] text-gray-500 font-medium pb-0.5 whitespace-nowrap" id="priceSuffix">/ piece</span>
                            </div>
                            <div class="text-[9px] sm:text-[10px] text-gray-400 font-medium mt-0.5" id="singlePriceRow" style="display:block;">No MOQ for single piece</div>
                        </div>

                        <?php if ($hasSinglePrice): ?>
                        <!-- Pricing Mode Toggle (DEFAULT: Single Active) -->
                        <div class="price-mode" role="tablist">
                            <button type="button" class="price-mode__btn" id="btnWholesale" onclick="setPricingMode('wholesale')">Wholesale</button>
                            <button type="button" class="price-mode__btn is-active" id="btnOnePiece" onclick="setPricingMode('onepiece')">Single</button>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Horizontal Tiers Strip (Hidden by default in Single Mode) -->
                    <div id="wholesaleTierContainer" class="hidden">
                        <div class="flex overflow-x-auto gap-2 no-scrollbar pb-1" id="tierCardsRow">
                            <?php
                            foreach ($initialTiers as $tIdx => $t):
                                $tMin = (int) $t['min_qty'];
                                $tMax = !empty($t['max_qty']) ? (int) $t['max_qty'] : null;
                                $tPrice = (float) $t['unit_price'];
                                $rangeLabel = $tMax ? "{$tMin}-{$tMax}" : "≥{$tMin}";
                            ?>
                                <div class="flex flex-col w-16 sm:w-20 flex-shrink-0">
                                    <span class="text-xs sm:text-sm font-bold text-gray-800">₹<?= rtrim(rtrim(number_format($tPrice, 2), '0'), '.') ?></span>
                                    <span class="text-[9px] sm:text-[10px] text-gray-500"><?= $rangeLabel ?> pcs</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Info Rows (Shipping, Factory) -->
                <div class="bg-white px-3 sm:px-4 py-2 md:rounded-2xl shadow-sm flex flex-col mb-1 text-[11px] sm:text-xs">
                    <a href="#" class="py-2 border-b border-gray-50 flex items-start gap-2 md:grid" style="grid-template-columns: 90px 1fr 20px;">
                        <div class="mt-0.5 text-gray-400 flex-shrink-0 w-16 md:w-auto">Shipping</div>
                        <div class="flex-1 md:col-span-1 text-gray-700 min-w-0">
                            <div class="font-semibold text-orange-600 mb-0.5 md:whitespace-nowrap md:overflow-hidden md:text-ellipsis" style="color: #f05a29;">Standard <span class="text-gray-800 font-normal">Consolidation to India</span></div>
                            <div class="text-gray-500 mb-0.5">ETA: <?= $delivStart ?> - <?= $delivEnd ?> days</div>
                            <div class="text-gray-500">Fees applied at checkout</div>
                        </div>
                        <div class="text-gray-400 mt-0.5 md:text-right">›</div>
                    </a>
                    
                    <a href="#" class="py-2 flex items-start gap-2 md:grid" style="grid-template-columns: 90px 1fr 20px;">
                        <div class="mt-0.5 text-gray-400 flex-shrink-0 w-16 md:w-auto">Factory</div>
                        <div class="flex-1 md:col-span-1 text-gray-700 min-w-0">
                            <div class="font-medium text-gray-800 mb-0.5 flex items-center gap-1 md:whitespace-nowrap md:overflow-hidden md:text-ellipsis">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" style="color: #f05a29;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                Direct Factory Supply
                            </div>
                            <div class="text-gray-500">Ship from Guangdong Province</div>
                        </div>
                        <div class="text-gray-400 mt-0.5 md:text-right">›</div>
                    </a>
                </div>

                <!-- Compact Variant Selector -->
                <?php if (!empty($variants)): ?>
                    <div class="bg-white p-3 sm:p-4 md:p-5 md:rounded-2xl shadow-sm mb-1" id="variantSelectorBox">
                        <?php if ($isDoubleMode && !empty($groupedColors)): ?>
                            <!-- Colors -->
                            <div class="mb-3">
                                <div class="text-[11px] sm:text-xs font-bold text-gray-800 mb-1.5">Color <span class="text-gray-400 font-normal ml-1"><?= count($groupedColors) ?> options</span></div>
                                <div class="flex gap-1.5 overflow-x-auto no-scrollbar pb-1" id="colorCardsGrid">
                                    <?php foreach ($groupedColors as $colorName => $colorData): ?>
                                        <button type="button"
                                            class="color-card flex-shrink-0 flex items-center justify-center p-0.5 rounded border border-gray-200 transition-all cursor-pointer relative"
                                            data-color="<?= htmlspecialchars($colorName) ?>"
                                            data-color-image="<?= htmlspecialchars($colorData['image']) ?>"
                                            onclick="selectColorCard('<?= htmlspecialchars(addslashes($colorName)) ?>')">
                                            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-sm overflow-hidden bg-gray-100">
                                                <img src="<?= htmlspecialchars($colorData['image']) ?>" class="w-full h-full object-cover">
                                            </div>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Sizes -->
                            <div id="sizeChipsSection">
                                <div class="text-[11px] sm:text-xs font-bold text-gray-800 mb-1.5">Size</div>
                                <div id="sizePlaceholder" class="text-[10px] sm:text-[11px] text-gray-400">Select a color to see sizes</div>
                                <div id="sizeChipsContainer" class="hidden flex-wrap gap-1.5" style="display:none;">
                                    <?php foreach ($variants as $vi => $v): 
                                        $parts = explode(' - ', $v['attribute_value'] ?? '');
                                        $rowColor = trim($parts[0] ?? '');
                                        $sizeName = isset($parts[1]) ? trim(implode(' - ', array_slice($parts, 1))) : htmlspecialchars($v['attribute_value']);
                                    ?>
                                        <button type="button"
                                            class="size-chip px-2.5 py-1 sm:px-3 sm:py-1.5 rounded bg-gray-50 border border-gray-200 text-[10px] sm:text-[11px] font-medium text-gray-700 transition-all cursor-pointer text-center"
                                            data-variant-idx="<?= $vi ?>"
                                            data-color="<?= htmlspecialchars($rowColor) ?>"
                                            data-wholesale="<?= (float) $v['wholesale_price'] ?>"
                                            data-img="<?= !empty($v['image_url']) ? asset($v['image_url']) : $mainImage ?>"
                                            style="display:none;"
                                            onclick="selectAmazonVariant(<?= $vi ?>)">
                                            <span><?= $sizeName ?></span>
                                            <span class="hidden"></span>
                                            <span id="vQtyVal_<?= $vi ?>" class="hidden">0</span>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Single Mode Variants -->
                            <div class="text-[11px] sm:text-xs font-bold text-gray-800 mb-1.5">Variants <span class="text-gray-400 font-normal ml-1"><?= $varCount ?> options</span></div>
                            <div class="flex flex-col gap-1.5 max-h-56 overflow-y-auto no-scrollbar">
                                <?php foreach ($variants as $vi => $v): 
                                    $vImg = !empty($v['image_url']) ? asset($v['image_url']) : $mainImage;
                                ?>
                                    <button type="button"
                                        class="variant-row w-full px-2.5 py-1.5 sm:px-3 sm:py-2 rounded bg-gray-50 border border-gray-100 text-[10px] sm:text-[11px] font-medium text-gray-700 transition-all cursor-pointer flex items-center gap-2"
                                        data-variant-idx="<?= $vi ?>"
                                        data-wholesale="<?= (float) $v['wholesale_price'] ?>"
                                        data-img="<?= htmlspecialchars($vImg) ?>"
                                        onclick="selectAmazonVariant(<?= $vi ?>)">
                                        <?php if ($vImg != $mainImage): ?>
                                            <img src="<?= htmlspecialchars($vImg) ?>" class="w-6 h-6 sm:w-8 sm:h-8 rounded overflow-hidden object-cover border border-gray-200">
                                        <?php endif; ?>
                                        <span class="flex-1 text-left"><?= htmlspecialchars($v['attribute_value']) ?></span>
                                        <span id="vQtyVal_<?= $vi ?>" class="hidden">0</span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Specs Box -->
            <?php 
            $hiddenKeys = ['item number', 'main downstream platform', 'source platform', 'source product id/url', 'import date', 'status', 'product sku'];
            $validSpecs = [];
            $seenKeys = [];
            foreach ($specs ?? [] as $s) {
                $k = trim($s['spec_key'] ?? '');
                $v = trim($s['spec_value'] ?? '');
                $kl = strtolower($k);
                
                if ($v === '' || strtolower($v) === 'n/a' || strtolower($v) === 'none') continue;
                if (in_array($kl, $hiddenKeys) || strpos($kl, 'manufacturer') !== false) continue;
                
                if (($kl === 'color' && in_array('metal color', $seenKeys)) || ($kl === 'metal color' && in_array('color', $seenKeys))) continue;
                
                if ($kl === 'kind' && strtolower($v) === 'unisex\'s') {
                    $k = 'Gender';
                    $v = 'Unisex';
                    $kl = 'gender';
                }
                
                if (in_array($kl, $seenKeys)) continue;
                $seenKeys[] = $kl;
                $validSpecs[] = ['key' => $k, 'value' => $v];
            }
            if (!empty($validSpecs)): 
            ?>
            <div class="product-specs mx-3 sm:mx-0 bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden mb-4 p-3.5 sm:p-4">
                <h2 class="text-sm sm:text-base font-bold text-black mb-3 m-0" style="color: #000000 !important;">Specifications</h2>
                <div class="relative">
                    <div id="specsContent" class="overflow-hidden transition-all duration-300 relative" style="max-height: 195px;">
                        <dl class="spec-list">
                            <?php foreach ($validSpecs as $s): ?>
                                <dt><?= htmlspecialchars($s['key']) ?></dt>
                                <dd><?= htmlspecialchars($s['value']) ?></dd>
                            <?php endforeach; ?>
                        </dl>
                        <div id="specsFade" class="absolute bottom-0 left-0 right-0 h-[32px] bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
                    </div>
                </div>
                <button type="button" id="specsToggleBtn" onclick="toggleSpecs()" class="view-more-btn hidden mt-2" style="color: #f05a29; font-weight: 600;">
                    <span>View More</span>
                    <svg viewBox="0 0 24 24" fill="none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </button>
            </div>
            <script>
            function toggleSpecs() {
                var content = document.getElementById('specsContent');
                var btn = document.getElementById('specsToggleBtn');
                var fade = document.getElementById('specsFade');
                var text = btn.querySelector('span');
                if (content.style.maxHeight !== 'none') {
                    content.style.maxHeight = 'none';
                    if (fade) fade.style.display = 'none';
                    text.innerHTML = 'View Less';
                    btn.classList.add('expanded');
                } else {
                    content.style.maxHeight = '195px';
                    if (fade) fade.style.display = 'block';
                    text.innerHTML = 'View More';
                    btn.classList.remove('expanded');
                }
            }
            document.addEventListener('DOMContentLoaded', function() {
                var content = document.getElementById('specsContent');
                var btn = document.getElementById('specsToggleBtn');
                var fade = document.getElementById('specsFade');
                if (content && content.scrollHeight > 200) {
                    btn.classList.remove('hidden');
                } else if (content) {
                    content.style.maxHeight = 'none';
                    if (fade) fade.style.display = 'none';
                }
            });
            </script>
            <?php endif; ?>

            <!-- Description Box -->
            <?php if ($descHtml): ?>
                <div class="product-description mx-3 sm:mx-0 bg-white border border-gray-200 rounded-2xl shadow-xs overflow-hidden mb-4 p-3.5 sm:p-4">
                    <h2 class="text-sm sm:text-base font-bold text-black mb-3 m-0" style="color: #000000 !important;">Product Details</h2>
                    <div class="relative">
                        <div id="descContent" class="overflow-hidden transition-all duration-300 relative" style="max-height: 9em;">
                            <div class="text-[13px] text-gray-800 leading-relaxed font-sans max-w-none pb-1 font-normal">
                                <?= $descHtml ?>
                            </div>
                            <div id="descFade" class="absolute bottom-0 left-0 right-0 h-[32px] bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
                        </div>
                    </div>
                    <button type="button" id="descToggleBtn" onclick="toggleDesc()" class="view-more-btn hidden mt-2" style="color: #f05a29; font-weight: 600;">
                        <span>View More</span>
                        <svg viewBox="0 0 24 24" fill="none"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                </div>
                <script>
                function toggleDesc() {
                    var content = document.getElementById('descContent');
                    var btn = document.getElementById('descToggleBtn');
                    var fade = document.getElementById('descFade');
                    var text = btn.querySelector('span');
                    if (content.style.maxHeight !== 'none') {
                        content.style.maxHeight = 'none';
                        if (fade) fade.style.display = 'none';
                        text.innerHTML = 'View Less';
                        btn.classList.add('expanded');
                    } else {
                        content.style.maxHeight = '9em';
                        if (fade) fade.style.display = 'block';
                        text.innerHTML = 'View More';
                        btn.classList.remove('expanded');
                    }
                }
                document.addEventListener('DOMContentLoaded', function() {
                    var content = document.getElementById('descContent');
                    var btn = document.getElementById('descToggleBtn');
                    var fade = document.getElementById('descFade');
                    if (content && content.scrollHeight > content.clientHeight + 5) {
                        btn.classList.remove('hidden');
                    } else if (content) {
                        content.style.maxHeight = 'none';
                        if (fade) fade.style.display = 'none';
                    }
                });
                </script>
            <?php endif; ?>

        </div> <!-- End product-main -->

        <!-- RIGHT — ORDER SUMMARY SIDEBAR (Desktop only) -->
        <div class="product-side hidden lg:flex lg:flex-col w-full lg:w-[300px] xl:w-[320px] shrink-0 lg:sticky lg:top-24 gap-4">
            <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="font-bold text-sm text-gray-900">ORDER SUMMARY</h3>
                    <div class="flex items-center gap-1.5">
                        <span class="px-2 py-0.5 bg-slate-800 text-white text-[10px] font-bold tracking-wider rounded">WHOLESALE</span>
                        <span class="px-2 py-0.5 bg-orange-100 text-orange-600 text-[10px] font-bold rounded">MOQ <?= $moq ?></span>
                    </div>
                </div>

                <div class="flex items-center justify-between py-2.5 border-t border-gray-100 text-xs">
                    <span class="font-medium text-gray-600">Quantity</span>
                    <div class="flex items-center gap-2">
                        <button onclick="changeDetailQty(-1)" class="w-6 h-6 flex items-center justify-center bg-gray-100 text-gray-600 rounded cursor-pointer hover:bg-gray-200">-</button>
                        <input type="number" id="detailQtyInput" value="<?= $moq ?>" min="<?= $moq ?>" class="w-12 h-6 text-center border border-gray-200 rounded text-xs font-bold text-gray-900 no-spinners" onchange="onDetailQtyInputChange()">
                        <button onclick="changeDetailQty(1)" class="w-6 h-6 flex items-center justify-center bg-gray-100 text-gray-600 rounded cursor-pointer hover:bg-gray-200">+</button>
                    </div>
                </div>

                <div class="flex items-center justify-between py-2.5 border-t border-b border-gray-100 mb-4 text-xs">
                    <span class="font-medium text-gray-600">Gross Total Amount</span>
                    <span class="font-bold text-gray-900 text-sm" id="sidebarTotalDisplay">&#8377;0.00</span>
                </div>

                <div class="flex flex-col gap-2">
                    <button onclick="if(typeof openRfqWithProducts === 'function'){openRfqWithProducts();}else{openRfqModal(null,true);}" class="w-full h-11 bg-[#f05a29] hover:bg-[#d8481b] text-white font-bold text-sm rounded shadow-sm transition cursor-pointer border-0 flex items-center justify-center gap-2">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        Get Quote
                    </button>
                    <button onclick="addToCartFromDetail()" class="w-full h-11 bg-white border border-gray-300 hover:bg-gray-50 text-gray-800 font-bold text-sm rounded shadow-sm transition cursor-pointer flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        Add to Cart
                    </button>
                </div>
            </div>

            <!-- ImportWale Order Protection Box -->
            <div class="bg-slate-50 border border-gray-200 rounded-xl p-4 text-xs">
                <div class="flex items-center gap-1.5 font-bold text-gray-900 mb-3">
                    <span style="color:#f05a29;">&#9673;</span>
                    <span><span style="color:#f05a29;" class="font-bold">ImportWale</span> <span class="font-semibold">ORDER PROTECTION</span></span>
                </div>
                <div class="space-y-2.5 text-gray-600 text-[11px]">
                    <div>
                        <div class="font-semibold text-gray-800 flex items-center gap-1">
                            <svg class="w-3 h-3 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Secure payments*
                        </div>
                        <div class="pl-4">Every payment you make on ImportWale is secured with strict SSL encryption.</div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    
    <!-- Similar Products -->
    <?php if (!empty($visuallySimilar)): ?>
        <div class="mt-2 sm:mt-4 bg-white p-3 sm:p-4 md:p-6 md:rounded-2xl">
            <h2 class="text-xs sm:text-sm md:text-base font-bold text-gray-900 tracking-tight mb-3">Similar Products</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2 sm:gap-3">
                <?php foreach ($visuallySimilar as $simItem): 
                    $product = $simItem;
                    require __DIR__ . '/partials/product_card.php';
                endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- JAVASCRIPT LOGIC -->
<script>
    function openDrawer(id) {
        const d = document.getElementById(id);
        if(d) { d.classList.remove('hidden'); setTimeout(() => d.classList.add('open'), 10); }
    }
    function closeDrawer(id) {
        const d = document.getElementById(id);
        if(d) { d.classList.remove('open'); setTimeout(() => d.classList.add('hidden'), 300); }
    }

    const WHATSAPP_NUMBER = <?= json_encode($waNumber ?? '') ?>;
    const PRODUCT_NAME = <?= json_encode($productName ?? '') ?>;
    const PRODUCT_URL = <?= json_encode($canonicalUrl ?? '') ?>;
    const PRODUCT_ID = <?= (int)($product['id'] ?? 0) ?>;
    window.PRODUCT_ID = PRODUCT_ID;
    const WS_START = <?= (float)($wholesaleStartPrice ?? 0) ?>;
    const OP_START = <?= (float)($onePieceStartPrice ?? 0) ?>;

    // DEFAULT MODE: Single (onepiece)
    let currentMode = 'onepiece';
    let selectedVariantEl = null;

    const VARIANTS_LIST = <?= json_encode($variantsJsonData ?? []) ?>;
    let selectedVariantIndex = null;
    let currentColorSelection = '';

    function formatNum(n) {
        const num = parseFloat(n);
        if (isNaN(num)) return '0.00';
        return num.toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function setPricingMode(mode) {
        currentMode = mode;
        const btnOP = document.getElementById('btnOnePiece');
        const btnWS = document.getElementById('btnWholesale');
        const wholesaleTierContainer = document.getElementById('wholesaleTierContainer');
        const singlePriceRow = document.getElementById('singlePriceRow');
        const priceSuffix = document.getElementById('priceSuffix');

        if (mode === 'wholesale') {
            if (btnWS) btnWS.classList.add('is-active');
            if (btnOP) btnOP.classList.remove('is-active');
            if (singlePriceRow) singlePriceRow.style.display = 'none';
            if (priceSuffix) priceSuffix.textContent = '≥<?= $moq ?>pcs';
            if (wholesaleTierContainer) wholesaleTierContainer.classList.remove('hidden');
        } else {
            if (btnOP) btnOP.classList.add('is-active');
            if (btnWS) btnWS.classList.remove('is-active');
            if (singlePriceRow) singlePriceRow.style.display = 'block';
            if (priceSuffix) priceSuffix.textContent = '/ piece';
            if (wholesaleTierContainer) wholesaleTierContainer.classList.add('hidden');
        }

        if (selectedVariantIndex !== null && VARIANTS_LIST[selectedVariantIndex]) {
            updatePriceDisplayForVariant(VARIANTS_LIST[selectedVariantIndex]);
        } else {
            const priceEl = document.getElementById('priceDisplay');
            if (priceEl) {
                const wsStr = priceEl.dataset.wsprice || formatNum(WS_START);
                const opStr = priceEl.dataset.opprice || formatNum(OP_START || WS_START);
                priceEl.textContent = (mode === 'wholesale') ? wsStr : opStr;
            }
        }
    }

    function renderVariantTiers(tiers) {
        const row = document.getElementById('tierCardsRow');
        if (!row) return;

        if (!tiers || tiers.length === 0) {
            const p = (selectedVariantIndex !== null && VARIANTS_LIST && VARIANTS_LIST[selectedVariantIndex]) 
                ? (VARIANTS_LIST[selectedVariantIndex].wholesale_price || WS_START) 
                : WS_START;
            tiers = [{ min_qty: <?= $moq ?>, max_qty: null, unit_price: p }];
        }

        let html = '';
        tiers.forEach((t, i) => {
            const min = parseInt(t.min_qty);
            const max = t.max_qty ? parseInt(t.max_qty) : null;
            const price = parseFloat(t.unit_price);
            const label = max ? `${min}-${max} piece` : `≥ ${min} piece`;

            html += `
                <div class="tier-card px-2 py-1.5 sm:px-2.5 sm:py-2 rounded-lg border border-gray-200 bg-white transition-all text-center min-w-[85px] sm:min-w-[95px] shrink-0" data-tier-idx="${i}" data-min="${min}" data-max="${max || 9999999}" data-price="${price}">
                    <div class="tier-price text-xs sm:text-[13px] font-bold text-gray-900 leading-tight">
                        ₹${formatNum(price)} <span class="tier-suffix text-[9.5px] sm:text-[10px] font-normal text-gray-400">/ piece</span>
                    </div>
                    <div class="tier-label text-[10px] sm:text-[11px] text-gray-500 font-medium mt-0.5">${label}</div>
                </div>
            `;
        });

        row.innerHTML = html;
        if (currentAtcQty > 0) updateTierHighlightAndPrice(currentAtcQty);
    }
    
    function updateTierHighlightAndPrice(qty) {
        if (currentMode !== 'wholesale') return;
        const cards = document.querySelectorAll('.tier-card');
        if (!cards.length) return;
        
        let activePrice = null;
        cards.forEach(card => {
            const min = parseInt(card.dataset.min);
            const max = parseInt(card.dataset.max);
            if (qty >= min && qty <= max) {
                card.classList.add('border-[#f05a29]', 'bg-orange-50', 'shadow-sm');
                card.classList.remove('border-gray-200', 'bg-white');
                card.querySelector('.tier-price').classList.add('text-[#f05a29]');
                card.querySelector('.tier-price').classList.remove('text-gray-900');
                activePrice = card.dataset.price;
            } else {
                card.classList.remove('border-[#f05a29]', 'bg-orange-50', 'shadow-sm');
                card.classList.add('border-gray-200', 'bg-white');
                card.querySelector('.tier-price').classList.remove('text-[#f05a29]');
                card.querySelector('.tier-price').classList.add('text-gray-900');
            }
        });
        
        if (activePrice) {
            const priceEl = document.getElementById('priceDisplay');
            if (priceEl) priceEl.textContent = formatNum(activePrice);
        }
    }

    function updatePriceDisplayForVariant(v) {
        const priceEl = document.getElementById('priceDisplay');
        const compareEl = document.getElementById('compareAtPriceDisplay');
        let displayStr = '';

        if (!v) {
            if (priceEl) priceEl.textContent = formatNum(currentMode === 'wholesale' ? WS_START : OP_START);
            if (compareEl) compareEl.style.display = 'none';
            return;
        }

        if (currentMode === 'wholesale') {
            if (v.tiers && v.tiers.length > 0) {
                const prices = v.tiers.map(t => parseFloat(t.unit_price));
                const minP = Math.min(...prices);
                const maxP = Math.max(...prices);
                displayStr = (minP !== maxP) ? formatNum(minP) + ' - ' + formatNum(maxP) : formatNum(minP);
            } else {
                const priceVal = (v.wholesale_price && v.wholesale_price > 0) ? v.wholesale_price : WS_START;
                displayStr = formatNum(priceVal);
            }
        } else {
            const priceVal = (v.one_piece_price && v.one_piece_price > 0) ? v.one_piece_price : (v.wholesale_price || OP_START);
            displayStr = formatNum(priceVal);
        }

        if (priceEl && displayStr) priceEl.textContent = displayStr;

        // Compare-at / MRP Price logic
        if (compareEl) {
            const cmpPrice = parseFloat(v.compare_at_price || 0);
            const currentPriceVal = (currentMode === 'wholesale') ? (v.wholesale_price || WS_START) : (v.one_piece_price || OP_START);
            if (cmpPrice > currentPriceVal) {
                compareEl.textContent = '₹' + formatNum(cmpPrice);
                compareEl.style.display = 'inline';
            } else {
                compareEl.style.display = 'none';
            }
        }
    }

    function selectAmazonVariant(idx) {
        if (!VARIANTS_LIST || !VARIANTS_LIST[idx]) return;

        // Deselect toggle logic
        if (selectedVariantIndex === idx) {
            if (<?= !empty($isDoubleMode) ? 'true' : 'false' ?> && currentColorSelection) {
                const savedColor = VARIANTS_LIST[idx].color || '';
                selectedVariantIndex = null;
                restoreMainProductState();
                selectColorCard(savedColor);
            } else {
                restoreMainProductState();
            }
            return;
        }

        selectedVariantIndex = idx;
        const v = VARIANTS_LIST[idx];

        // 1. Update UI active styles
        if (<?= $isDoubleMode ? 'true' : 'false' ?>) {
            document.querySelectorAll('.size-chip').forEach(chip => {
                const chipIdx = parseInt(chip.dataset.variantIdx);
                const chipV = VARIANTS_LIST[chipIdx];
                const isOos = chipV && chipV.stock <= 0;
                if (chipIdx === idx) {
                    chip.className = `size-chip relative inline-flex flex-col items-center justify-center px-3 min-h-[32px] sm:min-h-[34px] py-1 rounded-lg border-2 border-[#f05a29] bg-orange-50/40 transition-all duration-150 select-none text-center cursor-pointer`;
                } else {
                    chip.className = `size-chip relative inline-flex flex-col items-center justify-center px-3 min-h-[32px] sm:min-h-[34px] py-1 rounded-lg border border-gray-200 bg-white transition-all duration-150 select-none text-center ${isOos ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-gray-400'}`;
                }
            });
        } else {
            document.querySelectorAll('.variant-row').forEach((row, i) => {
                const isOos = VARIANTS_LIST[i] && VARIANTS_LIST[i].stock <= 0;
                const check = row.querySelector('.variant-check');
                if (i === idx) {
                    row.className = `variant-row w-full px-2.5 py-1.5 sm:px-3 sm:py-2 rounded-xl border-2 border-[#f05a29] bg-orange-50/20 text-[10px] sm:text-[11px] font-medium text-gray-700 transition-all cursor-pointer flex items-center gap-2 ${isOos ? 'opacity-50 grayscale' : ''}`;
                    if (check) { check.classList.remove('hidden'); check.style.display = 'flex'; }
                } else {
                    row.className = `variant-row w-full px-2.5 py-1.5 sm:px-3 sm:py-2 rounded-xl border border-gray-100 bg-gray-50 text-[10px] sm:text-[11px] font-medium text-gray-700 transition-all cursor-pointer flex items-center gap-2 ${isOos ? 'opacity-50 grayscale' : 'hover:border-gray-300'}`;
                    if (check) { check.classList.add('hidden'); check.style.display = ''; }
                }
            });
        }

        // 2. Title & Code Badge
        const titleEl = document.getElementById('selectedVariantTitle');
        if (titleEl) titleEl.textContent = v.value ? (PRODUCT_NAME + ' - ' + v.value) : PRODUCT_NAME;

        const codeBadge = document.getElementById('selectedVariantCodeBadge');
        if (codeBadge) codeBadge.textContent = v.code || '';

        // 3. Stock Status
        const stockBadge = document.getElementById('variantStockStatusText');
        const mainStockBadge = document.getElementById('activeStockBadge');
        if (v.stock > 0) {
            if (stockBadge) { stockBadge.textContent = 'In stock'; stockBadge.className = 'text-[11px] font-semibold text-emerald-600'; }
            if (mainStockBadge) { mainStockBadge.textContent = 'In Stock'; mainStockBadge.className = 'font-semibold text-emerald-600'; }
        } else {
            if (stockBadge) { stockBadge.textContent = 'Out of Stock'; stockBadge.className = 'text-[11px] font-semibold text-red-500'; }
            if (mainStockBadge) { mainStockBadge.textContent = 'Out of Stock'; mainStockBadge.className = 'font-semibold text-red-500'; }
        }

        // 4. Main Product Image
        if (v.image) {
            const mainImg = document.getElementById('mainProductImage');
            if (mainImg) mainImg.src = v.image;
        }

        // 5. Tier Pricing Cards
        renderVariantTiers(v.tiers && v.tiers.length > 0 ? v.tiers : []);

        // 6. Dynamic Price & Compare-at Price Update
        updatePriceDisplayForVariant(v);

        // 7. Update URL clean state
        if (v.code) {
            const cleanUrl = PRODUCT_URL + '/' + encodeURIComponent(v.code);
            window.history.replaceState(null, '', cleanUrl);
        } else {
            window.history.replaceState(null, '', PRODUCT_URL);
        }

        // 8. Update ATC Stepper
        const span = document.getElementById('vQtyVal_' + idx);
        currentAtcQty = span ? (parseInt(span.textContent) || 0) : 0;
        if (typeof updateAtcStepperUI === 'function') {
            updateAtcStepperUI();
        }
    }

    function _applyColorCardStyle(colorName) {
        document.querySelectorAll('.color-card').forEach(card => {
            if (card.dataset.color === colorName) {
                card.classList.add('border-2', 'border-[#f05a29]', 'bg-orange-50/20');
                card.classList.remove('border-gray-200');
            } else {
                card.classList.remove('border-2', 'border-[#f05a29]', 'bg-orange-50/20');
                card.classList.add('border-gray-200');
            }
        });
    }

    function _resetSizeChipStyles() {
        document.querySelectorAll('.size-chip').forEach(chip => {
            const chipIdx = parseInt(chip.dataset.variantIdx);
            const chipV = VARIANTS_LIST && VARIANTS_LIST[chipIdx];
            const isOos = chipV && chipV.stock <= 0;
            chip.className = `size-chip relative inline-flex flex-col items-center justify-center px-3 min-h-[32px] sm:min-h-[34px] py-1 rounded-lg border border-gray-200 bg-white transition-all duration-150 select-none text-center ${isOos ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-gray-400'}`;
        });
    }

    function restoreMainProductState() {
        selectedVariantIndex = null;
        currentColorSelection = '';

        document.querySelectorAll('.color-card').forEach(card => {
            card.classList.remove('border-2', 'border-[#f05a29]', 'bg-orange-50/20');
            card.classList.add('border-gray-200');
        });

        const sizeContainer = document.getElementById('sizeChipsContainer');
        const sizePlaceholder = document.getElementById('sizePlaceholder');
        if (sizeContainer) sizeContainer.style.display = 'none';
        if (sizePlaceholder) sizePlaceholder.style.display = '';

        _resetSizeChipStyles();

        document.querySelectorAll('.variant-row').forEach((row, i) => {
            const isOos = VARIANTS_LIST[i] && VARIANTS_LIST[i].stock <= 0;
            row.className = `variant-row w-full px-2.5 py-1.5 sm:px-3 sm:py-2 rounded-xl border border-gray-100 bg-gray-50 text-[10px] sm:text-[11px] font-medium text-gray-700 transition-all cursor-pointer flex items-center gap-2 ${isOos ? 'opacity-50 grayscale' : 'hover:border-gray-300'}`;
            const check = row.querySelector('.variant-check');
            if (check) { check.classList.add('hidden'); check.style.display = ''; }
        });

        const mainImg = document.getElementById('mainProductImage');
        if (mainImg) mainImg.src = <?= json_encode($mainImage) ?>;

        updatePriceDisplayForVariant(null);

        const titleEl = document.getElementById('selectedVariantTitle');
        if (titleEl) titleEl.textContent = <?= json_encode($product['name'] ?? '') ?>;
        
        window.history.replaceState(null, '', <?= json_encode($canonicalUrl ?? '') ?>);
    }

    function selectColorCard(colorName) {
        if (currentColorSelection === colorName) {
            restoreMainProductState();
            return;
        }

        currentColorSelection = colorName;
        selectedVariantIndex = null;

        _applyColorCardStyle(colorName);

        const colorVars = VARIANTS_LIST.filter(v => v.color === colorName);
        if (colorVars.length > 0) {
            const mainImg = document.getElementById('mainProductImage');
            if (mainImg && colorVars[0].image) mainImg.src = colorVars[0].image;

            const prices = colorVars.map(v => (currentMode === 'wholesale' ? v.wholesale_price : (v.one_piece_price || v.wholesale_price))).filter(p => p > 0);
            if (prices.length > 0) {
                const minP = Math.min(...prices);
                const maxP = Math.max(...prices);
                const priceStr = (minP !== maxP) ? formatNum(minP) + ' - ' + formatNum(maxP) : formatNum(minP);
                const priceEl = document.getElementById('priceDisplay');
                if (priceEl) priceEl.textContent = priceStr;
            }
        }

        const sizeContainer = document.getElementById('sizeChipsContainer');
        const sizePlaceholder = document.getElementById('sizePlaceholder');
        if (sizeContainer) {
            sizeContainer.style.display = 'flex';
            sizeContainer.style.flexWrap = 'wrap';
            sizeContainer.style.gap = '8px';
        }
        if (sizePlaceholder) sizePlaceholder.style.display = 'none';

        document.querySelectorAll('.size-chip').forEach(chip => {
            chip.style.display = (chip.dataset.color === colorName) ? 'inline-flex' : 'none';
        });

        _resetSizeChipStyles();
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Set DEFAULT MODE to 'onepiece' (Single Price)
        setPricingMode('onepiece');

        let urlVariantCode = <?= json_encode($initialVariantCode ?? '') ?>;
        if (!urlVariantCode) {
            const urlParams = new URLSearchParams(window.location.search);
            urlVariantCode = urlParams.get('variant');
        }

        if (urlVariantCode && VARIANTS_LIST && VARIANTS_LIST.length > 0) {
            const foundIdx = VARIANTS_LIST.findIndex(v => v.code && v.code.toLowerCase() === urlVariantCode.toLowerCase());
            if (foundIdx !== -1) {
                selectAmazonVariant(foundIdx);
                const chip = document.querySelector(`.size-chip[data-variant-idx="${foundIdx}"]`);
                if (chip && chip.dataset.color) {
                    selectColorCard(chip.dataset.color);
                }
            }
        }
    });

    let currentAtcQty = 0;
    let atcInFlight = false;
    let isProductWishlisted = <?= $isWished ? 'true' : 'false' ?>;

    function handleAddToCartClick(source) {
        if (<?= $isDoubleMode ? 'true' : 'false' ?> && currentColorSelection && selectedVariantIndex === null) {
            alert('Please select a Size');
            return;
        }
        let minQty = (currentMode === 'wholesale') ? <?= $moq ?> : 1;
        currentAtcQty = minQty;
        updateAtcStepperUI();
        addToCartFromDetail(currentAtcQty);
    }

    function updateAtcStepperUI() {
        if (typeof updateTierHighlightAndPrice === 'function') {
            updateTierHighlightAndPrice(currentAtcQty);
        }
        
        const mobileInit = document.getElementById('mobileAtcBtnInitial');
        const mobileStep = document.getElementById('mobileAtcStepper');
        const mobileQty = document.getElementById('mobileAtcQtyText');

        if (currentAtcQty > 0) {
            if (mobileInit) mobileInit.classList.add('hidden');
            if (mobileStep) {
                mobileStep.classList.remove('hidden');
                mobileStep.classList.add('flex');
            }
            if (mobileQty) mobileQty.textContent = currentAtcQty;
        } else {
            if (mobileInit) mobileInit.classList.remove('hidden');
            if (mobileStep) {
                mobileStep.classList.add('hidden');
                mobileStep.classList.remove('flex');
            }
        }
    }

    async function changeAtcQty(delta) {
        if (atcInFlight) return;

        let minQty = (currentMode === 'wholesale') ? parseInt(<?= $moq ?>) : 1;
        if (isNaN(minQty)) minQty = 1;
        
        let parsedCurrent = parseInt(currentAtcQty);
        if (isNaN(parsedCurrent)) parsedCurrent = 0;
        
        let newQty = parsedCurrent + parseInt(delta);

        if (parseInt(delta) > 0 && newQty < minQty) {
            newQty = minQty;
        } else if (parseInt(delta) < 0 && newQty < minQty) {
            newQty = 0;
        }

        // Stock check
        let maxStock = 999999;
        if (selectedVariantIndex !== null && VARIANTS_LIST[selectedVariantIndex]) {
            maxStock = parseInt(VARIANTS_LIST[selectedVariantIndex].stock) || 999999;
        } else {
            maxStock = <?= (int)($product['stock_quantity'] ?? 999999) ?>;
        }

        if (parseInt(delta) > 0 && maxStock > 0 && newQty > maxStock) {
            if (typeof showCartToast === 'function') showCartToast('Maximum available stock reached (' + maxStock + ')');
            return;
        }

        const isRemoval = (newQty <= 0);
        atcInFlight = true;

        const minusBtn = document.getElementById('mobileAtcMinusBtn');
        const plusBtn = document.getElementById('mobileAtcPlusBtn');
        if (minusBtn) minusBtn.disabled = true;
        if (plusBtn) plusBtn.disabled = true;

        const previousQty = currentAtcQty;

        if (isRemoval) {
            currentAtcQty = 0;
            updateAtcStepperUI();
            
            const pId = <?= (int)($product['id'] ?? 0) ?>;
            const payload = new URLSearchParams();
            payload.append('product_id', pId);
            if (selectedVariantIndex !== null && VARIANTS_LIST[selectedVariantIndex]) {
                payload.append('variant_id', VARIANTS_LIST[selectedVariantIndex].id);
            }

            try {
                const res = await fetch('<?= url('cart/remove') ?>', { method: 'POST', body: payload });
                const data = await res.json();
                if (data.success) {
                    const cCount = data.cart_count || data.count || 0;
                    if (typeof updateHeaderCartBadge === 'function') updateHeaderCartBadge(cCount);
                    if (typeof renderCartDrawerUI === 'function') renderCartDrawerUI(data.items, data.subtotal, cCount);
                    if (typeof showCartToast === 'function') showCartToast('Item removed from cart');
                } else {
                    currentAtcQty = previousQty;
                    updateAtcStepperUI();
                    if (typeof showCartToast === 'function') showCartToast(data.message || 'Could not remove item');
                }
            } catch (e) {
                currentAtcQty = previousQty;
                updateAtcStepperUI();
                if (typeof showCartToast === 'function') showCartToast('Network error while removing item');
            } finally {
                atcInFlight = false;
                if (minusBtn) minusBtn.disabled = false;
                if (plusBtn) plusBtn.disabled = false;
            }
        } else {
            currentAtcQty = newQty;
            updateAtcStepperUI();

            const pId = <?= (int)($product['id'] ?? 0) ?>;
            const payload = new URLSearchParams();
            payload.append('product_id', pId);
            if (selectedVariantIndex !== null && VARIANTS_LIST[selectedVariantIndex]) {
                payload.append('variant_id', VARIANTS_LIST[selectedVariantIndex].id);
            }
            payload.append('quantity', newQty);
            payload.append('set_exact_qty', '1');
            payload.append('pricing_mode', currentMode);

            try {
                const res = await fetch('<?= url('cart/add') ?>', { method: 'POST', body: payload });
                const data = await res.json();
                if (data.success) {
                    const cCount = data.cart_count || data.count || 0;
                    if (typeof updateHeaderCartBadge === 'function') updateHeaderCartBadge(cCount);
                    if (typeof renderCartDrawerUI === 'function') renderCartDrawerUI(data.items, data.subtotal, cCount);
                    if (typeof window.syncProductDetailCartState === 'function') window.syncProductDetailCartState(data.items, cCount);
                } else {
                    currentAtcQty = previousQty;
                    updateAtcStepperUI();
                    if (typeof showCartToast === 'function') showCartToast(data.message || 'Could not update quantity');
                }
            } catch (e) {
                currentAtcQty = previousQty;
                updateAtcStepperUI();
                if (typeof showCartToast === 'function') showCartToast('Network error while updating quantity');
            } finally {
                atcInFlight = false;
                if (minusBtn) minusBtn.disabled = false;
                if (plusBtn) plusBtn.disabled = false;
            }
        }
    }

    async function addToCartFromDetail(overrideQty = null) {
        const pId = <?= (int)($product['id'] ?? 0) ?>;
        let qty = overrideQty !== null ? overrideQty : 1;

        const payload = new URLSearchParams();
        payload.append('product_id', pId);
        if (selectedVariantIndex !== null && VARIANTS_LIST[selectedVariantIndex]) {
            payload.append('variant_id', VARIANTS_LIST[selectedVariantIndex].id);
        }
        payload.append('quantity', qty);
        payload.append('set_exact_qty', '1');
        payload.append('pricing_mode', currentMode);

        try {
            const res = await fetch('<?= url('cart/add') ?>', { method: 'POST', body: payload });
            const data = await res.json();
            if (data.success) {
                const cCount = data.cart_count || data.count || 0;
                if (typeof updateHeaderCartBadge === 'function') updateHeaderCartBadge(cCount);
                if (typeof renderCartDrawerUI === 'function') renderCartDrawerUI(data.items, data.subtotal, cCount);
                if (typeof window.syncProductDetailCartState === 'function') window.syncProductDetailCartState(data.items, cCount);
                if (typeof showCartToast === 'function') showCartToast('Item added to cart');
            } else {
                currentAtcQty = 0;
                updateAtcStepperUI();
                if (typeof showCartToast === 'function') showCartToast(data.message || 'Could not add to cart');
            }
        } catch (e) {
            currentAtcQty = 0;
            updateAtcStepperUI();
            if (typeof showCartToast === 'function') showCartToast('Error adding item to cart');
        }
    }

    window.syncProductDetailCartState = function(items, count) {
        if (!items || !Array.isArray(items)) return;
        const pId = <?= (int)($product['id'] ?? 0) ?>;
        let matchingItem = null;
        if (selectedVariantIndex !== null && VARIANTS_LIST[selectedVariantIndex]) {
            const vId = VARIANTS_LIST[selectedVariantIndex].id;
            matchingItem = items.find(i => parseInt(i.product_id) === pId && parseInt(i.variant_id) === parseInt(vId));
        } else {
            matchingItem = items.find(i => parseInt(i.product_id) === pId);
        }

        if (matchingItem) {
            currentAtcQty = parseInt(matchingItem.quantity) || 1;
        } else {
            currentAtcQty = 0;
        }
        updateAtcStepperUI();
    };

    async function toggleDetailWishlist(btnEl) {
        const pId = <?= (int)($product['id'] ?? 0) ?>;
        if (!pId) return;

        if (btnEl) btnEl.style.pointerEvents = 'none';

        const iconEl = document.getElementById('bottomWishlistIcon');
        if (iconEl) {
            iconEl.classList.add('scale-125');
            setTimeout(() => iconEl.classList.remove('scale-125'), 150);
        }

        const payload = new URLSearchParams();
        payload.append('product_id', pId);

        try {
            const res = await fetch('<?= url('wishlist/toggle') ?>', { method: 'POST', body: payload });
            const data = await res.json();

            if (data.success) {
                isProductWishlisted = !!data.saved;
                updateWishlistUI();
                if (typeof showCartToast === 'function') {
                    showCartToast(data.message || (isProductWishlisted ? 'Added to wishlist' : 'Removed from wishlist'));
                }
            } else if (data.message) {
                if (typeof showCartToast === 'function') showCartToast(data.message);
            }
        } catch (e) {
            console.error('Wishlist toggle error', e);
        } finally {
            if (btnEl) btnEl.style.pointerEvents = 'auto';
        }
    }

    function updateWishlistUI() {
        const iconEl = document.getElementById('bottomWishlistIcon');
        if (iconEl) {
            if (isProductWishlisted) {
                iconEl.setAttribute('fill', '#f05a29');
                iconEl.setAttribute('stroke', '#f05a29');
            } else {
                iconEl.setAttribute('fill', 'none');
                iconEl.setAttribute('stroke', '#6B7280');
            }
        }
    }

    function changeDetailQty(delta) {
        const inp = document.getElementById('detailQtyInput');
        if (!inp) return;
        let val = parseInt(inp.value) || 1;
        let minQty = (currentMode === 'wholesale') ? <?= $moq ?> : 1;
        val = Math.max(minQty, val + delta);
        inp.value = val;
        updateDetailQtyTotal(val);
    }

    function onDetailQtyInputChange() {
        const inp = document.getElementById('detailQtyInput');
        if (!inp) return;
        let val = parseInt(inp.value) || 1;
        let minQty = (currentMode === 'wholesale') ? <?= $moq ?> : 1;
        if (val < minQty) val = minQty;
        inp.value = val;
        updateDetailQtyTotal(val);
    }

    function updateDetailQtyTotal(val) {
        let price = (selectedVariantIndex !== null && VARIANTS_LIST[selectedVariantIndex])
            ? (currentMode === 'wholesale' ? VARIANTS_LIST[selectedVariantIndex].wholesale_price : (VARIANTS_LIST[selectedVariantIndex].one_piece_price || VARIANTS_LIST[selectedVariantIndex].wholesale_price))
            : (currentMode === 'wholesale' ? WS_START : OP_START);
        const totalEl = document.getElementById('sidebarTotalDisplay');
        if (totalEl) totalEl.textContent = '₹' + formatNum(price * val);
    }

    function switchImage(idx, src) {
        const mainImg = document.getElementById('mainProductImage');
        if (mainImg && src) mainImg.src = src;
        document.querySelectorAll('.thumb-btn').forEach(btn => {
            const isActive = parseInt(btn.dataset.idx) === idx;
            btn.classList.toggle('is-active', isActive);
        });
    }

    function openLightbox(src) {
        const modal = document.getElementById('lightboxModal');
        const img = document.getElementById('lightboxImg');
        if (img) img.src = src;
        if (modal) modal.style.display = 'flex';
    }

    function closeLightbox() {
        const modal = document.getElementById('lightboxModal');
        if (modal) modal.style.display = 'none';
    }

    window.rfqGetProductContextFromPage = function () {
        const selectedV = (selectedVariantIndex !== null && VARIANTS_LIST) ? VARIANTS_LIST[selectedVariantIndex] : null;
        return {
            id: <?= (int) $product['id'] ?>,
            name: <?= json_encode($product['name'] ?? '') ?>,
            sku: selectedV ? (selectedV.code || selectedV.sku) : <?= json_encode($product['sku'] ?? '') ?>,
            variant_id: selectedV ? selectedV.id : null,
            variant_name: selectedV ? selectedV.value : '',
            price: selectedV ? (currentMode === 'wholesale' ? selectedV.wholesale_price : (selectedV.one_piece_price || selectedV.wholesale_price)) : (currentMode === 'wholesale' ? WS_START : OP_START),
            moq: <?= $moq ?>
        };
    };

    function openRfqWithProducts() {
        const prodData = window.rfqGetProductContextFromPage();
        if (typeof openRfqModal === 'function') {
            openRfqModal(prodData);
        } else {
            window.open(`https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent('Hi, I want a quote for: ' + PRODUCT_NAME + (prodData.variant_name ? ' (' + prodData.variant_name + ')' : ''))}`, '_blank');
        }
    }
</script>

<!-- LIGHTBOX MODAL -->
<div id="lightboxModal" style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:99999; background:rgba(0,0,0,0.88); display:none; align-items:center; justify-content:center; padding:16px;" onclick="closeLightbox()">
    <button onclick="closeLightbox()" type="button" style="position:absolute; top:20px; right:20px; width:40px; height:40px; background:rgba(255,255,255,0.15); border:none; border-radius:50%; color:#fff; font-size:20px; cursor:pointer; display:flex; align-items:center; justify-content:center;">✕</button>
    <img id="lightboxImg" src="" alt="" style="max-width:90vw; max-height:85vh; object-fit:contain; border-radius:12px;">
</div>

<!-- Custom Mobile Bottom Bar -->
<div class="md:hidden fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 z-[999999] flex items-center gap-1.5 sm:gap-2 px-2.5 sm:px-3 py-2 shadow-[0_-4px_12px_rgba(0,0,0,0.05)]" style="padding-bottom: max(8px, env(safe-area-inset-bottom));">
    <a href="<?= !empty($product['factory_code']) ? url('factory/' . urlencode($product['factory_code'])) : '#' ?>" class="flex flex-col items-center justify-center w-[48px] sm:w-[52px] shrink-0 text-gray-500 hover:text-[#f05a29] transition-colors border-0" style="text-decoration:none;">
        <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        <span class="text-[9px] font-medium leading-none">Factory</span>
    </a>
    <button type="button" onclick="toggleDetailWishlist(this)" id="mobileBottomWishlistBtn" class="flex flex-col items-center justify-center w-[48px] sm:w-[52px] shrink-0 text-gray-500 hover:text-[#f05a29] transition-colors border-0 bg-transparent p-0 cursor-pointer">
        <svg id="bottomWishlistIcon" class="w-5 h-5 mb-0.5 transition-transform duration-150" fill="<?= $isWished ? '#f05a29' : 'none' ?>" stroke="<?= $isWished ? '#f05a29' : '#6B7280' ?>" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
        <span class="text-[9px] font-medium leading-none">Wishlist</span>
    </button>
    <div class="flex-1 flex items-stretch gap-2 h-[44px]">
        <div id="mobileCartBtnWrapper" class="flex-1 h-[44px] relative">
            <button type="button" id="mobileAtcBtnInitial" onclick="handleAddToCartClick('mobile')" class="absolute inset-0 w-full h-full rounded-full font-bold text-[13px] text-orange-600 border-2 border-orange-600 bg-white active:bg-orange-50 flex items-center justify-center cursor-pointer shadow-xs">
                Add cart
            </button>
            <div id="mobileAtcStepper" class="absolute inset-0 w-full h-full rounded-full border-2 border-orange-600 bg-white hidden items-center justify-between px-1 shadow-xs">
                <button type="button" id="mobileAtcMinusBtn" onclick="changeAtcQty(-1)" class="w-8 sm:w-10 h-full flex items-center justify-center text-orange-600 text-xl font-bold cursor-pointer bg-transparent border-0 select-none pb-0.5">−</button>
                <span id="mobileAtcQtyText" class="font-bold text-sm text-gray-800 flex-1 text-center select-none">1</span>
                <button type="button" id="mobileAtcPlusBtn" onclick="changeAtcQty(1)" class="w-8 sm:w-10 h-full flex items-center justify-center text-orange-600 text-xl font-bold cursor-pointer bg-transparent border-0 select-none pb-0.5">+</button>
            </div>
        </div>
        <button type="button" onclick="openRfqWithProducts()" class="flex-1 h-[44px] rounded-full font-bold text-[13px] text-white shadow-md active:scale-[0.98] transition-all border-0 cursor-pointer flex items-center justify-center" style="background: linear-gradient(135deg, #ff7a18 0%, #f05a29 100%); color: #ffffff;">
            Get Quote
        </button>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>