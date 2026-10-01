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

// Safe Description Formatting
function formatProductDescription($text) {
    if (empty($text)) return '';
    // Strip tags to prevent XSS
    $text = strip_tags($text);
    
    // Normalize spaces around markers to split inline text
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
    $vTiers = !empty($varTiersMap[$vId]) ? $varTiersMap[$vId] : $prodTiers;
    // Split color / size for double-mode
    $colorKey = '';
    $sizeKey  = $v['attribute_value'] ?? '';
    if ($isDoubleMode) {
        $parts    = explode(' - ', $v['attribute_value'] ?? '');
        $colorKey = trim($parts[0] ?? '');
        $sizeKey  = isset($parts[1]) ? trim(implode(' - ', array_slice($parts, 1))) : trim($parts[0] ?? '');
    }
    return [
        'id'              => $vId,
        'code'            => $v['variant_code'] ?? '',
        'label'           => $v['attribute_label'] ?? 'Color',
        'value'           => $v['attribute_value'] ?? '',
        'color'           => $colorKey,
        'size'            => $sizeKey,
        'stock'           => (int) $v['stock_quantity'],
        'wholesale_price' => (float) $v['wholesale_price'],
        'one_piece_price' => (float) $v['one_piece_price'],
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
    
    /* New Structural CSS */
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

    /* Grid layout */
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

/* Footer padding for Need Help button */
.footer-bottom {
    padding-right: 160px !important;
}


/* Thumbnail Strip: Exactly 5 items per row */
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

/* Hide scrollbar for horizontal scroll areas */
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

    /* Spec List Grid */
    .spec-list {
        display: grid;
        grid-template-columns: 38% 1fr;
        column-gap: 12px;
        margin: 0;
        padding: 0;
    }
    .spec-list dt, .spec-list dd {
        margin: 0;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
        font-size: 12px;
        line-height: 1.45;
        min-width: 0;
        overflow-wrap: anywhere;
    }
    .spec-list dt { color: #888; font-weight: 400; }
    .spec-list dd { color: #222; font-weight: 500; text-align: left; }
    .spec-list dt:nth-last-of-type(1), .spec-list dd:nth-last-of-type(1) { border-bottom: none; }
    @media (prefers-color-scheme: dark) {
        .spec-list dt, .spec-list dd { border-bottom-color: #333; }
        .spec-list dt { color: #aaa; }
        .spec-list dd { color: #eee; }
    }
    
    /* View More Button */
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
        padding: 14px 0; /* tap area 40px */
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

    /* Custom Pricing Mode Toggle */
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

        <!-- ======================================================= -->
        <!-- LEFT — IMAGE GALLERY -->
        <!-- ======================================================= -->
        <div class="product-gallery w-full bg-white md:rounded-2xl relative overflow-hidden">
            <!-- Mobile Custom Header (Overlaps Image) -->
            <div class="mobile-custom-header md:hidden absolute top-0 left-0 w-full z-50 flex items-center justify-between p-3" style="background: linear-gradient(to bottom, rgba(0,0,0,0.2) 0%, transparent 100%);">
                <button type="button" onclick="window.history.back()" class="w-8 h-8 rounded-full bg-black/30 flex items-center justify-center text-white cursor-pointer backdrop-blur-sm border-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <div class="flex gap-2">
                    <button type="button" onclick="openCartDrawer()" class="w-8 h-8 rounded-full bg-black/30 flex items-center justify-center text-white cursor-pointer backdrop-blur-sm border-0 relative">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        <div class="absolute -top-1 -right-1 bg-[#f05a29] text-[9px] w-4 h-4 rounded-full flex items-center justify-center font-bold" id="mobileCustomCartCount" style="display: <?= ($initialCartCount ?? 0) > 0 ? 'flex' : 'none' ?>"><?= (int)($initialCartCount ?? 0) ?></div>
                    </button>
                </div>
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

        <!-- ======================================================= -->
        <!-- MIDDLE — INFO BOX -->
        <!-- ======================================================= -->
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
                
                // Show toggle only if there's a valid single piece price
                $hasSinglePrice = (isset($onePieceStartPrice) && $onePieceStartPrice > 0);
                ?>
                <!-- Price & Toggle Row -->
                <div class="flex items-center justify-between mb-2 gap-[8px] flex-wrap">
                    <!-- Price Box -->
                    <div id="bigOrangePriceContainer" class="flex flex-col shrink-0">
                        <div class="flex items-baseline gap-1 font-bold tracking-tight" style="color: #f05a29; font-size: 18px; line-height: 1.1;">
                            <span class="text-xs pb-0.5">₹</span>
                            <span id="priceDisplay" data-wsprice="<?= htmlspecialchars($wholesaleDisplayPrice) ?>" data-wsisrange="<?= $isRange ? '1' : '0' ?>" data-opprice="<?= rtrim(rtrim(number_format($onePieceStartPrice ?? 0, 2), '0'), '.') ?>"><?= htmlspecialchars($wholesaleDisplayPrice) ?></span>
                            <span class="text-[9px] sm:text-[10px] text-gray-500 font-medium pb-0.5 whitespace-nowrap" id="priceSuffix">≥<?= $moq ?>pcs</span>
                        </div>
                        <div class="text-[9px] sm:text-[10px] text-gray-400 font-medium mt-0.5" id="singlePriceRow" style="display:none;">No MOQ for single piece</div>
                    </div>

                    <?php if ($hasSinglePrice): ?>
                    <!-- Pricing Mode Toggle -->
                    <div class="price-mode" role="tablist">
                        <button type="button" class="price-mode__btn is-active" id="btnWholesale" onclick="setPricingMode('wholesale')">Wholesale</button>
                        <button type="button" class="price-mode__btn" id="btnOnePiece" onclick="setPricingMode('onepiece')">Single</button>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Horizontal Tiers Strip -->
                <div id="wholesaleTierContainer">
                    <div class="flex overflow-x-auto gap-2 no-scrollbar pb-1" id="tierCardsRow">
                        <?php
                        $initialTiers = !empty($variantsJsonData[0]['tiers']) ? $variantsJsonData[0]['tiers'] : ($productTiers ?? []);
                        if (empty($initialTiers)) {
                            $initialTiers = [['min_qty' => $moq, 'max_qty' => null, 'unit_price' => $wholesaleStartPrice]];
                        }
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

            <!-- Info Rows (Shipping, Factory) styled similar to Reference screenshot -->
            <div class="bg-white px-3 sm:px-4 py-2 md:rounded-2xl shadow-sm flex flex-col mb-1 text-[11px] sm:text-xs">
                <!-- Shipping -->
                <a href="#" class="py-2 border-b border-gray-50 flex items-start gap-2 md:grid" style="grid-template-columns: 90px 1fr 20px;">
                    <div class="mt-0.5 text-gray-400 flex-shrink-0 w-16 md:w-auto">Shipping</div>
                    <div class="flex-1 md:col-span-1 text-gray-700 min-w-0">
                        <div class="font-semibold text-orange-600 mb-0.5 md:whitespace-nowrap md:overflow-hidden md:text-ellipsis" style="color: #f05a29;">Standard <span class="text-gray-800 font-normal">Consolidation to India</span></div>
                        <div class="text-gray-500 mb-0.5">ETA: <?= $delivStart ?> - <?= $delivEnd ?> days</div>
                        <div class="text-gray-500">Fees applied at checkout</div>
                    </div>
                    <div class="text-gray-400 mt-0.5 md:text-right">›</div>
                </a>
                
                <!-- Factory -->
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
        </div> <!-- End product-info -->

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
            <div class="product-specs bg-white p-3 sm:p-4 md:p-5 md:rounded-2xl shadow-sm mb-2">
                <h2 class="text-[14px] font-[600] text-gray-800 mb-[8px]">Specifications</h2>
                <div class="relative">
                    <div id="specsContent" class="overflow-hidden transition-all duration-300 relative" style="max-height: 180px;">
                        <dl class="spec-list">
                            <?php foreach ($validSpecs as $s): ?>
                                <dt><?= htmlspecialchars($s['key']) ?></dt>
                                <dd><?= htmlspecialchars($s['value']) ?></dd>
                            <?php endforeach; ?>
                        </dl>
                        <div id="specsFade" class="absolute bottom-0 left-0 right-0 h-[28px] bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
                    </div>
                </div>
                <button type="button" id="specsToggleBtn" onclick="toggleSpecs()" class="view-more-btn hidden">
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
                    content.style.maxHeight = '180px';
                    if (fade) fade.style.display = 'block';
                    text.innerHTML = 'View More';
                    btn.classList.remove('expanded');
                }
            }
            document.addEventListener('DOMContentLoaded', function() {
                var content = document.getElementById('specsContent');
                var btn = document.getElementById('specsToggleBtn');
                var fade = document.getElementById('specsFade');
                if (content && content.scrollHeight > 185) {
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
                <div class="product-description bg-white p-3 sm:p-4 md:p-5 md:rounded-2xl shadow-sm mb-2">
                    <h2 class="text-[14px] font-[600] text-gray-800 mb-[8px]">Product Details</h2>
                    <div class="relative">
                        <div id="descContent" class="overflow-hidden transition-all duration-300 relative" style="max-height: 9em;">
                            <div class="text-[13px] text-gray-700 leading-relaxed font-sans max-w-none pb-1">
                                <?= $descHtml ?>
                            </div>
                            <div id="descFade" class="absolute bottom-0 left-0 right-0 h-[28px] bg-gradient-to-t from-white to-transparent pointer-events-none"></div>
                        </div>
                    </div>
                    <button type="button" id="descToggleBtn" onclick="toggleDesc()" class="view-more-btn hidden">
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

        <!-- ======================================================= -->
        <!-- RIGHT — ORDER SUMMARY SIDEBAR (Desktop only) -->
        <!-- ======================================================= -->
        <div class="product-side hidden lg:flex lg:flex-col w-full lg:w-[300px] xl:w-[320px] shrink-0 lg:sticky lg:top-24 gap-4">

            <!-- Order Summary Box -->
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
                    <div>
                        <div class="font-semibold text-gray-800 flex items-center gap-1">
                            <svg class="w-3 h-3 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Delivery arranged by ImportWale*
                        </div>
                        <div class="pl-4">Expect your order to be delivered before scheduled dates.</div>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 flex items-center gap-1">
                            <svg class="w-3 h-3 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            OEM/ODM Customization Available*
                        </div>
                        <div class="pl-4">Custom logo, branding &amp; packaging available for bulk orders.</div>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 flex items-center gap-1">
                            <svg class="w-3 h-3 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Sample Available*
                        </div>
                        <div class="pl-4">Sample available as per one piece price to check quality before bulk buying.</div>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 flex items-center gap-1">
                            <svg class="w-3 h-3 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Easy Return*
                        </div>
                        <div class="pl-4">Make free local returns for defects on qualifying request.</div>
                    </div>
                    <div>
                        <div class="font-semibold text-gray-800 flex items-center gap-1">
                            <svg class="w-3 h-3 text-green-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            Full Money-back protection*
                        </div>
                        <div class="pl-4">Claim a refund if your order doesn't ship or is missing.</div>
                    </div>
                </div>

                <p class="text-[10px] text-gray-500 pt-2 mt-2 border-t border-gray-200">
                    Only orders placed and paid through <span class="font-semibold text-gray-700">ImportWale</span> can enjoy free protection by Trade Assurance.
                </p>

                <div class="grid grid-cols-3 gap-1 pt-2 mt-2 border-t border-gray-200 text-[9px] text-center">
                    <div>
                        <div class="font-bold text-gray-800">Trade Protection</div>
                        <div class="text-gray-500">100% Escrow &amp; Order Protection</div>
                    </div>
                    <div>
                        <div class="font-bold text-gray-800">Verified Factories</div>
                        <div class="text-gray-500">Direct Global Manufacturer Sourced</div>
                    </div>
                    <div>
                        <div class="font-bold text-gray-800">Express Freight</div>
                        <div class="text-gray-500">Air &amp; Sea Customs Cleared Shipping</div>
                    </div>
                </div>
            </div>

        </div><!-- /RIGHT SIDEBAR -->

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

<!-- ============================================================ -->
<!-- STICKY BOTTOM ACTION BAR -->
<!-- ============================================================ -->
<div class="hidden fixed bottom-0 left-0 right-0 z-40 bg-white border-t border-gray-200 shadow-lg pb-[env(safe-area-inset-bottom)]">
    <div class="w-full max-w-7xl mx-auto flex items-stretch h-[52px]">
        <!-- Icons -->
        <div class="flex items-center gap-1 sm:gap-2 px-1 sm:px-2 flex-shrink-0">
            <button class="flex flex-col items-center justify-center w-12 text-gray-500 hover:text-gray-800 transition-colors" onclick="window.location.href='#'">
                <svg class="w-[18px] h-[18px] mb-[2px]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                <span class="text-[9px] font-medium">Factory</span>
            </button>
            <button class="flex flex-col items-center justify-center w-12 text-gray-500 hover:text-gray-800 transition-colors" onclick="window.open(`https://wa.me/<?= $waNumber ?>`, '_blank')">
                <svg class="w-[18px] h-[18px] mb-[2px]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                <span class="text-[9px] font-medium leading-tight">Request<br>Quote</span>
            </button>
            <button class="flex flex-col items-center justify-center w-12 text-gray-500 hover:text-gray-800 transition-colors" onclick="toggleDetailWishlist()">
                <svg id="detailWishlistIcon" class="w-[18px] h-[18px] mb-[2px]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                <span class="text-[9px] font-medium">Save</span>
            </button>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex-1 flex gap-2 items-center pr-2 py-1.5">
            <button class="flex-1 h-full rounded border font-semibold text-[11px] sm:text-xs flex items-center justify-center transition-opacity hover:opacity-80" style="border-color: #f05a29; color: #f05a29;" onclick="addToCartFromDetail()">
                Add cart
            </button>
            <button class="flex-1 h-full rounded text-white font-semibold text-[11px] sm:text-xs shadow-sm flex items-center justify-center transition-opacity hover:opacity-90" style="background-color: #f05a29;" onclick="if(typeof openRfqWithProducts === 'function'){openRfqWithProducts();}else{openRfqModal(null,true);}">
                Get Quote
            </button>
        </div>
    </div>
</div>

<!-- ============================================================ -->
<!-- LIGHTBOX MODAL -->
<!-- ============================================================ -->
<div id="lightboxModal"
    style="position:fixed; top:0; left:0; right:0; bottom:0; z-index:99999; background:rgba(0,0,0,0.88); display:none; align-items:center; justify-content:center; padding:16px;"
    onclick="closeLightbox()">
    <button onclick="closeLightbox()" type="button" aria-label="Close"
        style="position:absolute; top:20px; right:20px; width:40px; height:40px; background:rgba(255,255,255,0.15); border:none; border-radius:50%; color:#fff; font-size:20px; cursor:pointer; display:flex; align-items:center; justify-content:center; z-index:10;">✕</button>
    <img id="lightboxImg" src="" alt=""
        style="max-width:90vw; max-height:85vh; object-fit:contain; border-radius:12px; box-shadow:0 25px 50px -12px rgba(0,0,0,0.5);"
        onclick="event.stopPropagation()">
</div>

<!-- ============================================================ -->
<!-- JAVASCRIPT -->
<!-- ============================================================ -->
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
    const WS_START = <?= (float)($wholesaleStartPrice ?? 0) ?>;
    const OP_START = <?= (float)($onePieceStartPrice ?? 0) ?>;

    let currentMode = 'wholesale';
    let selectedVariantEl = null;

    function setPricingMode(mode) {
        currentMode = mode;
        const btnOP = document.getElementById('btnOnePiece');
        const btnWS = document.getElementById('btnWholesale');
        const wholesaleTierContainer = document.getElementById('wholesaleTierContainer');
        const bigOrangePriceContainer = document.getElementById('bigOrangePriceContainer');
        const singlePriceRow = document.getElementById('singlePriceRow');
        const priceSuffix = document.getElementById('priceSuffix');
        const priceEl = document.getElementById('priceDisplay');

        if (mode === 'wholesale') {
            if (btnWS) btnWS.classList.add('is-active');
            if (btnOP) btnOP.classList.remove('is-active');

            if (singlePriceRow) singlePriceRow.style.display = 'none';
            if (priceSuffix) priceSuffix.textContent = '≥<?= $moq ?>pcs';
            if (wholesaleTierContainer) wholesaleTierContainer.classList.remove('hidden');

            let wsStr = priceEl ? priceEl.dataset.wsprice : '';
            let isRange = priceEl ? (priceEl.dataset.wsisrange === '1') : false;
            if (typeof VARIANTS_LIST !== 'undefined' && VARIANTS_LIST[selectedVariantIndex]) {
                const v = VARIANTS_LIST[selectedVariantIndex];
                if (v.tiers && v.tiers.length > 0) {
                    const prices = v.tiers.map(t => parseFloat(t.unit_price));
                    const minP = Math.min(...prices);
                    const maxP = Math.max(...prices);
                    isRange = (minP !== maxP);
                    wsStr = isRange ? formatNum(minP) + ' - ' + formatNum(maxP) : formatNum(minP);
                } else {
                    isRange = false;
                    wsStr = formatNum(v.wholesale_price || WS_START);
                }
            }
            if (priceEl && wsStr) priceEl.textContent = wsStr;
        } else {
            if (btnOP) btnOP.classList.add('is-active');
            if (btnWS) btnWS.classList.remove('is-active');

            if (singlePriceRow) singlePriceRow.style.display = 'block';
            if (priceSuffix) priceSuffix.textContent = '/ piece';
            if (wholesaleTierContainer) wholesaleTierContainer.classList.add('hidden');

            let p = OP_START;
            if (typeof VARIANTS_LIST !== 'undefined' && VARIANTS_LIST[selectedVariantIndex]) {
                p = VARIANTS_LIST[selectedVariantIndex].one_piece_price || OP_START;
            } else if (selectedVariantEl) {
                p = parseFloat(selectedVariantEl.dataset.onepiece) || OP_START;
            } else if (priceEl && priceEl.dataset.opprice) {
                p = parseFloat(priceEl.dataset.opprice);
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
                card.querySelector('.tier-suffix').classList.add('text-[#f05a29]', 'opacity-80');
                card.querySelector('.tier-suffix').classList.remove('text-gray-400');
                card.querySelector('.tier-label').classList.add('text-gray-800');
                card.querySelector('.tier-label').classList.remove('text-gray-500');
                activePrice = card.dataset.price;
            } else {
                card.classList.remove('border-[#f05a29]', 'bg-orange-50', 'shadow-sm');
                card.classList.add('border-gray-200', 'bg-white');
                card.querySelector('.tier-price').classList.remove('text-[#f05a29]');
                card.querySelector('.tier-price').classList.add('text-gray-900');
                card.querySelector('.tier-suffix').classList.remove('text-[#f05a29]', 'opacity-80');
                card.querySelector('.tier-suffix').classList.add('text-gray-400');
                card.querySelector('.tier-label').classList.remove('text-gray-800');
                card.querySelector('.tier-label').classList.add('text-gray-500');
            }
        });
        
        if (activePrice) {
            const priceEl = document.getElementById('priceDisplay');
            if (priceEl) priceEl.textContent = formatNum(activePrice);
        } else {
            // Restore default wholesale price
            const priceEl = document.getElementById('priceDisplay');
            if (priceEl) {
                let wsStr = priceEl.dataset.wsprice || '';
                if (typeof VARIANTS_LIST !== 'undefined' && VARIANTS_LIST[selectedVariantIndex]) {
                    const v = VARIANTS_LIST[selectedVariantIndex];
                    if (v.tiers && v.tiers.length > 0) {
                        const prices = v.tiers.map(t => parseFloat(t.unit_price));
                        const minP = Math.min(...prices);
                        const maxP = Math.max(...prices);
                        wsStr = (minP !== maxP) ? formatNum(minP) + ' - ' + formatNum(maxP) : formatNum(minP);
                    } else {
                        wsStr = formatNum(v.wholesale_price || WS_START);
                    }
                }
                if (wsStr) priceEl.textContent = wsStr;
            }
        }
    }

    function formatNumNoDec(n) {
        return parseFloat(n).toLocaleString('en-IN', { maximumFractionDigits: 2 });
    }

    function togglePricingMode() {
        setPricingMode(currentMode === 'wholesale' ? 'onepiece' : 'wholesale');
    }

    function formatNum(n) {
        return parseFloat(n).toLocaleString('en-IN', { maximumFractionDigits: 2 });
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
        const priceEl = document.getElementById('priceDisplay');
        let displayStr = '';
        let isRange = false;
        if (currentMode === 'wholesale') {
            const vWholesale = parseFloat(el.dataset.wholesale) || 0;
            // For non-Amazon variants, we don't have tiers in JS, so just show the variant's wholesale price, or fallback to the initial range.
            if (vWholesale > 0) {
                displayStr = formatNum(vWholesale);
            } else {
                displayStr = priceEl ? priceEl.dataset.wsprice : '';
                isRange = priceEl ? (priceEl.dataset.wsisrange === '1') : false;
            }
        } else {
            const vOnePiece = parseFloat(el.dataset.onepiece) || 0;
            displayStr = formatNum(vOnePiece > 0 ? vOnePiece : OP_START);
        }
        
        if (priceEl && displayStr) priceEl.textContent = displayStr;
    }

    const GALLERY_IMAGES = <?= json_encode(array_values($gallery ?? [])) ?>;
    let currentImgIdx = 0;

    function switchImage(idx, src) {
        currentImgIdx = idx;
        const mainImg = document.getElementById('mainProductImage');
        if (mainImg && src) {
            const loader = new Image();
            loader.onload = function() {
                mainImg.src = src;
                mainImg.style.opacity = '1';
            };
            loader.onerror = function() {
                mainImg.src = src;
                mainImg.style.opacity = '1';
            };
            mainImg.style.opacity = '0.75';
            loader.src = src;
        }
        document.querySelectorAll('.thumb-btn').forEach(btn => {
            const isActive = parseInt(btn.dataset.idx) === idx;
            if (isActive) {
                btn.classList.add('is-active');
                btn.style.borderColor = '#f05a29';
            } else {
                btn.classList.remove('is-active');
                btn.style.borderColor = 'transparent';
            }
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
        const imgs = (typeof GALLERY_IMAGES !== 'undefined' && GALLERY_IMAGES.length) ? GALLERY_IMAGES : [src || ''];
        if (typeof openGlobalGalleryModal === 'function') {
            openGlobalGalleryModal(imgs, currentImgIdx, PRODUCT_NAME);
            return;
        }
        const modal = document.getElementById('lightboxModal');
        const img = document.getElementById('lightboxImg');
        if (img) img.src = src;
        if (modal) {
            modal.style.position = 'fixed';
            modal.style.top = '0';
            modal.style.left = '0';
            modal.style.right = '0';
            modal.style.bottom = '0';
            modal.style.zIndex = '99999';
            modal.style.background = 'rgba(0,0,0,0.88)';
            modal.style.display = 'flex';
            modal.style.alignItems = 'center';
            modal.style.justifyContent = 'center';
        }
    }

    function closeLightbox() {
        if (typeof closeGlobalGalleryModal === 'function') {
            closeGlobalGalleryModal();
        }
        const modal = document.getElementById('lightboxModal');
        if (modal) modal.style.display = 'none';
    }

    window.onGlobalGalleryIndexChange = function (idx, src) {
        if (typeof switchImage === 'function' && src) {
            switchImage(idx, src);
        }
    };

    window.rfqGetProductContextFromPage = function () {
        const activeMode = typeof currentMode !== 'undefined' ? currentMode : 'wholesale';
        const detailQty = parseInt(document.getElementById('detailQtyInput')?.value) || 1;
        const mainImg = document.getElementById('mainProductImage')?.src || <?= json_encode($mainImage) ?>;

        const vars = (typeof VARIANTS_LIST !== 'undefined' && VARIANTS_LIST) ? VARIANTS_LIST.map((v, i) => {
            const isSelected = (typeof selectedVariantIndex !== 'undefined' && selectedVariantIndex === i);
            return {
                id: v.id,
                code: v.code,
                label: v.label,
                value: v.value,
                stock: v.stock,
                wholesale_price: v.wholesale_price,
                one_piece_price: v.one_piece_price,
                image: v.image,
                checked: isSelected,
                qty: isSelected ? detailQty : 0
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
            selectedVariantIndex: (typeof selectedVariantIndex !== 'undefined') ? selectedVariantIndex : null,
            detailQty: detailQty,
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

    const VARIANTS_LIST = <?= json_encode($variantsJsonData ?? []) ?>;
    let selectedVariantIndex = null; // null = no variant selected yet

    function selectAmazonVariant(idx) {
        if (!VARIANTS_LIST || !VARIANTS_LIST[idx]) return;

        // Deselect: clicking the already-selected variant clears size selection, keeps color active
        if (selectedVariantIndex === idx) {
            if (<?= !empty($isDoubleMode) ? 'true' : 'false' ?> && currentColorSelection) {
                // Save color before restore (restore clears it)
                const savedColor = VARIANTS_LIST[idx].color || '';
                selectedVariantIndex = null;
                restoreMainProductState(); // resets price/image/color/size-chips
                // Re-activate color: apply card style + re-show this color's chips
                currentColorSelection = savedColor;
                _applyColorCardStyle(currentColorSelection);
                const sizeContainer = document.getElementById('sizeChipsContainer');
                const sizePlaceholder = document.getElementById('sizePlaceholder');
                if (sizeContainer) { sizeContainer.style.display = 'flex'; sizeContainer.style.flexWrap = 'wrap'; sizeContainer.style.gap = '8px'; }
                if (sizePlaceholder) sizePlaceholder.style.display = 'none';
                document.querySelectorAll('.size-chip').forEach(chip => {
                    chip.style.display = (chip.dataset.color === currentColorSelection) ? 'inline-flex' : 'none';
                });
            } else {
                restoreMainProductState();
            }
            return;
        }

        selectedVariantIndex = idx;
        const v = VARIANTS_LIST[idx];

        // 1. Update Size Chip styles (double-mode) OR variant-row styles (single-mode)
        if (<?= $isDoubleMode ? 'true' : 'false' ?>) {
            document.querySelectorAll('.size-chip').forEach(chip => {
                const chipIdx = parseInt(chip.dataset.variantIdx);
                const chipV = VARIANTS_LIST[chipIdx];
                const isOos = chipV && chipV.stock <= 0;
                if (chipIdx === idx) {
                    chip.className = `size-chip relative inline-flex flex-col items-center justify-center px-3 min-h-[32px] sm:min-h-[34px] py-1 rounded-lg border-2 border-[#f05a29] bg-orange-50/40 transition-all duration-150 select-none text-center cursor-pointer`;
                    const lbl = chip.querySelector('span:not(.hidden)');
                    if (lbl) { lbl.className = 'text-[11.5px] sm:text-xs font-bold text-[#f05a29] leading-tight'; }
                    const priceLbl = chip.querySelectorAll('span:not(.hidden)')[1];
                    if (priceLbl) priceLbl.className = 'text-[10px] text-[#f05a29] font-semibold mt-0.5';
                } else {
                    chip.className = `size-chip relative inline-flex flex-col items-center justify-center px-3 min-h-[32px] sm:min-h-[34px] py-1 rounded-lg border border-gray-200 bg-white transition-all duration-150 select-none text-center ${isOos ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-gray-400'}`;
                    const spans = chip.querySelectorAll('span:not(.hidden)');
                    if (spans[0]) spans[0].className = `text-[11.5px] sm:text-xs font-semibold text-gray-800 leading-tight${isOos ? ' line-through text-gray-400' : ''}`;
                    if (spans[1]) spans[1].className = 'text-[10px] text-[#f05a29] font-medium mt-0.5';
                }
            });
        } else {
            document.querySelectorAll('.variant-row').forEach((row, i) => {
                const isOos = VARIANTS_LIST[i] && VARIANTS_LIST[i].stock <= 0;
                const check = row.querySelector('.variant-check');
                if (i === idx) {
                    row.className = `variant-row relative flex flex-col p-1 sm:p-1.5 rounded-xl border-2 border-[#f05a29] bg-orange-50/20 transition-all duration-150 cursor-pointer select-none text-left group ${isOos ? 'opacity-50 grayscale cursor-not-allowed' : ''}`;
                    if (check) { check.classList.remove('hidden'); check.style.display = 'flex'; }
                } else {
                    row.className = `variant-row relative flex flex-col p-1 sm:p-1.5 rounded-xl border border-gray-200 bg-white transition-all duration-150 cursor-pointer select-none text-left group ${isOos ? 'opacity-50 grayscale cursor-not-allowed' : 'hover:border-gray-400'}`;
                    if (check) { check.classList.add('hidden'); check.style.display = ''; }
                }
            });
        }

        // Update Amazon Swatch buttons if present
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
            const fixedImg = document.getElementById('desktopFixedBarImg');
            if (fixedImg) fixedImg.src = v.image;
        }

        // 5. Update Tier Pricing Cards for selected variant
        if (v && v.tiers) {
            renderVariantTiers(v.tiers);
        }

        // 5. Dynamic Price Update for current active mode
        const priceEl = document.getElementById('priceDisplay');
        const fixedPrice = document.getElementById('desktopFixedBarPrice');
        let displayStr = '';
        let isRange = false;
        if (currentMode === 'wholesale') {
            if (v.tiers && v.tiers.length > 0) {
                const prices = v.tiers.map(t => parseFloat(t.unit_price));
                const minP = Math.min(...prices);
                const maxP = Math.max(...prices);
                isRange = (minP !== maxP);
                displayStr = isRange ? formatNum(minP) + ' - ' + formatNum(maxP) : formatNum(minP);
            } else {
                displayStr = formatNum(v.wholesale_price || WS_START);
            }
        } else {
            displayStr = formatNum(v.one_piece_price || OP_START);
        }
        
        if (priceEl && displayStr) priceEl.textContent = displayStr;
        if (fixedPrice && displayStr) {
            fixedPrice.innerHTML = `₹${displayStr} <span class="text-[10px] text-gray-400 font-normal">/ piece</span>`;
        }

        // 6. Update URL to clean SEO route without page reload
        const baseUrl = <?= json_encode($canonicalUrl ?? '') ?>;
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

        // 8. Sync Main Add to Cart Stepper
        const span = document.getElementById('vQtyVal_' + idx);
        currentAtcQty = span ? (parseInt(span.textContent) || 0) : 0;
        if (typeof updateAtcStepperUI === 'function') {
            updateAtcStepperUI();
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
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    function closeVariantQuickView() {
        const modal = document.getElementById('variantQuickViewModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
    }

    // Helper: apply color-card active style for a given color name
    function _applyColorCardStyle(colorName) {
        document.querySelectorAll('.color-card').forEach(card => {
            const check = card.querySelector('.color-card-check');
            if (card.dataset.color === colorName) {
                card.classList.add('border-2', 'border-[#f05a29]', 'bg-orange-50/20');
                card.classList.remove('border-gray-200');
                if (check) { check.classList.remove('hidden'); check.style.display = 'flex'; }
            } else {
                card.classList.remove('border-2', 'border-[#f05a29]', 'bg-orange-50/20');
                card.classList.add('border-gray-200');
                if (check) { check.classList.add('hidden'); check.style.display = ''; }
            }
        });
    }

    // Helper: reset all size-chip styles to unselected
    function _resetSizeChipStyles() {
        document.querySelectorAll('.size-chip').forEach(chip => {
            const chipIdx = parseInt(chip.dataset.variantIdx);
            const chipV = VARIANTS_LIST && VARIANTS_LIST[chipIdx];
            const isOos = chipV && chipV.stock <= 0;
            chip.className = `size-chip relative inline-flex flex-col items-center justify-center px-3 min-h-[32px] sm:min-h-[34px] py-1 rounded-lg border border-gray-200 bg-white transition-all duration-150 select-none text-center ${isOos ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-gray-400'}`;
            const spans = chip.querySelectorAll('span:not(.hidden)');
            if (spans[0]) spans[0].className = `text-[11.5px] sm:text-xs font-semibold text-gray-800 leading-tight${isOos ? ' line-through text-gray-400' : ''}`;
            if (spans[1]) spans[1].className = 'text-[10px] text-[#f05a29] font-medium mt-0.5';
        });
    }

    // Restore main product state when no variant is selected (deselected or page load)
    function restoreMainProductState() {
        selectedVariantIndex = null;

        // Reset color card styles (double-mode)
        currentColorSelection = '';
        document.querySelectorAll('.color-card').forEach(card => {
            card.classList.remove('border-2', 'border-[#f05a29]', 'bg-orange-50/20');
            card.classList.add('border-gray-200');
            const check = card.querySelector('.color-card-check');
            if (check) { check.classList.add('hidden'); check.style.display = ''; }
        });

        // Hide size chips section, show placeholder
        const sizeContainer = document.getElementById('sizeChipsContainer');
        const sizePlaceholder = document.getElementById('sizePlaceholder');
        if (sizeContainer) sizeContainer.style.display = 'none';
        if (sizePlaceholder) sizePlaceholder.style.display = '';

        // Reset size chip styles
        _resetSizeChipStyles();

        // Also reset .variant-row for single-mode products
        document.querySelectorAll('.variant-row').forEach((row, i) => {
            const isOos = VARIANTS_LIST[i] && VARIANTS_LIST[i].stock <= 0;
            row.className = `variant-row relative flex flex-col p-1 sm:p-1.5 rounded-xl border border-gray-200 bg-white transition-all duration-150 cursor-pointer select-none text-left group ${isOos ? 'opacity-50 grayscale cursor-not-allowed' : 'hover:border-gray-400'}`;
            const check = row.querySelector('.variant-check');
            if (check) { check.classList.add('hidden'); check.style.display = ''; }
        });

        // Restore main product image
        const mainImg = document.getElementById('mainProductImage');
        if (mainImg) mainImg.src = <?= json_encode($mainImage) ?>;
        const fixedImg = document.getElementById('desktopFixedBarImg');
        if (fixedImg) fixedImg.src = <?= json_encode($mainImage) ?>;

        // Restore main product price display
        const priceEl = document.getElementById('priceDisplay');
        if (priceEl) priceEl.textContent = formatNum(currentMode === 'wholesale' ? WS_START : OP_START);
        const fixedPrice = document.getElementById('desktopFixedBarPrice');
        if (fixedPrice) fixedPrice.innerHTML = `₹${formatNum(currentMode === 'wholesale' ? WS_START : OP_START)} <span class="text-[10px] text-gray-400 font-normal">/ piece</span>`;

        // Restore main product title badge and SKU
        const titleEl = document.getElementById('selectedVariantTitle');
        if (titleEl) titleEl.textContent = <?= json_encode($product['name'] ?? '') ?>;
        const codeBadge = document.getElementById('selectedVariantCodeBadge');
        if (codeBadge) codeBadge.textContent = <?= json_encode($product['sku'] ?? '') ?>;

        // Restore main stock badge
        const mainStockBadge = document.getElementById('activeStockBadge');
        const stockQty = <?= (int)($product['stock_quantity'] ?? 0) ?>;
        if (mainStockBadge) {
            mainStockBadge.textContent = stockQty > 0 ? 'In Stock' : 'Out of Stock';
            mainStockBadge.className = stockQty > 0 ? 'font-semibold text-emerald-600' : 'font-semibold text-red-500';
        }
        const stockBadge = document.getElementById('variantStockStatusText');
        if (stockBadge) {
            stockBadge.textContent = stockQty > 0 ? 'In stock' : 'Out of Stock';
            stockBadge.className = stockQty > 0 ? 'text-[11px] font-semibold text-emerald-600' : 'text-[11px] font-semibold text-red-500';
        }

        // Restore product-level tiers
        const prodTiers = <?= json_encode($prodTiers ?? []) ?>;
        renderVariantTiers(prodTiers);

        // Restore URL to base product URL
        window.history.replaceState(null, '', <?= json_encode($canonicalUrl ?? '') ?>);

        // Clear all variant active styling
        document.querySelectorAll('.variant-row').forEach(r => {
            const isChip = r.classList.contains('px-3') && r.classList.contains('py-2');
            const isOos = (() => {
                const idx = parseInt(r.dataset.variantIdx);
                return VARIANTS_LIST && VARIANTS_LIST[idx] && VARIANTS_LIST[idx].stock <= 0;
            })();
            if (isChip) {
                r.className = `variant-row px-3 py-2 rounded-lg cursor-pointer transition-all duration-200 text-xs font-semibold select-none border border-gray-200 bg-white text-gray-700 hover:border-gray-300 hover:shadow-2xs ${isOos ? 'opacity-50' : ''}`;
            } else {
                r.className = `variant-row p-2.5 sm:p-3 rounded-xl cursor-pointer transition-all duration-200 border border-gray-200 bg-white hover:border-gray-300 hover:shadow-2xs ${isOos ? 'opacity-50 grayscale' : ''}`;
                const imgBox = r.querySelector('.variant-img-box');
                if (imgBox) imgBox.className = 'variant-img-box w-11 h-11 sm:w-12 sm:h-12 rounded-lg border border-gray-200 overflow-hidden shrink-0 bg-white shadow-2xs transition-all';
            }
        });
        document.querySelectorAll('.amazon-swatch-btn').forEach(btn => {
            btn.className = `amazon-swatch-btn relative flex items-center gap-2 px-2.5 py-1.5 rounded-xl border border-gray-200 hover:border-gray-400 bg-white transition-all cursor-pointer focus:outline-none select-none`;
        });

        // Sync ATC stepper with main product cart state
        if (typeof window.syncProductDetailCartState === 'function') {
            window.syncProductDetailCartState();
        } else {
            currentAtcQty = 0;
            if (typeof updateAtcStepperUI === 'function') updateAtcStepperUI();
        }
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
                // Also select the color if double mode
                const chip = document.querySelector(`.size-chip[data-variant-idx="${foundIdx}"]`);
                const row  = document.querySelector(`.variant-row[data-variant-idx="${foundIdx}"]`);
                const colorEl = chip || row;
                if (colorEl && colorEl.dataset.color) {
                    selectColorCard(colorEl.dataset.color);
                }
            }
            // Note: intentionally NOT auto-selecting first color/variant when no URL code present
        }

        // Double-mode: on page load, no color or size is pre-selected — all options visible
        // (No auto-selection of first color or first size — user must choose explicitly)

        checkDetailWishlistStatus();

        // Mobile touch swipe listener for main cover image
        const card = document.getElementById('mainImgCardWrapper');
        if (card) {
            let touchStartX = 0;
            let touchEndX = 0;
            card.addEventListener('touchstart', (e) => {
                if (e.changedTouches && e.changedTouches[0]) {
                    touchStartX = e.changedTouches[0].clientX;
                }
            }, { passive: true });
            card.addEventListener('touchend', (e) => {
                if (e.changedTouches && e.changedTouches[0]) {
                    touchEndX = e.changedTouches[0].clientX;
                    const diff = touchStartX - touchEndX;
                    if (diff > 35) {
                        if (typeof nextImage === 'function') nextImage();
                    } else if (diff < -35) {
                        if (typeof prevImage === 'function') prevImage();
                    }
                }
            }, { passive: true });
        }
    });

    let currentColorSelection = '';

    // Show a short inline partial-selection hint (e.g. "Please select a Size")
    function showPartialSelectionHint(missingGroupLabel) {
        let hintEl = document.getElementById('partialSelectionHint');
        if (!hintEl) {
            hintEl = document.createElement('div');
            hintEl.id = 'partialSelectionHint';
            hintEl.style.cssText = 'display:none; margin-top:8px; padding:8px 12px; background:#fff7ed; border:1.5px solid #f05a29; border-radius:10px; color:#c0390d; font-size:12px; font-weight:600; text-align:center;';
            const variantSelectorBox = document.getElementById('variantSelectorBox');
            if (variantSelectorBox) variantSelectorBox.insertAdjacentElement('afterend', hintEl);
            else document.body.appendChild(hintEl);
        }
        hintEl.textContent = `Please select a ${missingGroupLabel}`;
        hintEl.style.display = 'block';
        const sizeSection = document.getElementById('sizeChipsSection');
        if (sizeSection) {
            sizeSection.style.transition = 'box-shadow 0.2s';
            sizeSection.style.boxShadow = '0 0 0 2px #f05a29';
            setTimeout(() => { sizeSection.style.boxShadow = ''; }, 1800);
            sizeSection.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        setTimeout(() => { if (hintEl) hintEl.style.display = 'none'; }, 3000);
    }

    // Two-step color card selector (replaces old selectVariantColor)
    function selectColorCard(colorName) {
        // Toggle: clicking already-selected color deselects everything
        if (currentColorSelection === colorName) {
            restoreMainProductState(); // resets color, size, image, price
            return;
        }

        currentColorSelection = colorName;
        selectedVariantIndex = null;

        // 1. Update color card styles
        _applyColorCardStyle(colorName);

        // 2. Switch main gallery image to this color's representative image
        const selectedCard = document.querySelector(`.color-card[data-color="${CSS.escape(colorName)}"]`);
        const colorImage = selectedCard ? selectedCard.dataset.colorImage : null;
        if (colorImage) {
            const mainImg = document.getElementById('mainProductImage');
            if (mainImg) { mainImg.style.opacity = '0.7'; mainImg.src = colorImage; mainImg.style.opacity = '1'; }
            const fixedImg = document.getElementById('desktopFixedBarImg');
            if (fixedImg) fixedImg.src = colorImage;
        }

        // 3. Show size chips for this color, hide others
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

        // 4. Clear any previously selected size
        _resetSizeChipStyles();
    }

    // Legacy alias kept for deep-link handler compatibility
    function selectVariantColor(colorName) { selectColorCard(colorName); }

    // ========================================================
    // CART & WISHLIST DETAIL FUNCTIONS
    // ========================================================
    let cartSyncDebounceTimers = {};

    function updateVariantQty(vi, delta) {
        const span = document.getElementById('vQtyVal_' + vi);
        if (!span) return;
        let val = parseInt(span.textContent) || 0;
        
        let minQty = (currentMode === 'wholesale') ? <?= $moq ?> : 1;
        if (delta > 0 && val === 0) {
            val = minQty;
        } else if (delta < 0 && val <= minQty) {
            val = 0;
        } else {
            val += delta;
        }
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
        const currentProductId = <?= (int)($product['id'] ?? 0) ?>;
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
        const currentProductId = <?= (int)($product['id'] ?? 0) ?>;
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
        // Legacy IDs (in-page summary if any)
        const qtyEl = document.getElementById('summaryQtyText');
        const totalEl = document.getElementById('summaryTotalText');
        if (qtyEl) qtyEl.textContent = (count || 0) + ' units';
        if (totalEl) totalEl.textContent = '₹' + (subtotalStr || '0.00');
        // Desktop sidebar IDs
        const sidebarQty = document.getElementById('sidebarQtyDisplay');
        const sidebarTotal = document.getElementById('sidebarTotalDisplay');
        if (sidebarQty) sidebarQty.textContent = (count || 0) + ' units';
        
        // Also update detailQtyInput if it exists
        const detailQty = document.getElementById('detailQtyInput');
        if (detailQty && count > 0) detailQty.value = count;
        
        if (sidebarTotal) sidebarTotal.textContent = '₹' + (subtotalStr || '0.00');
    }

    function updateDetailQtyTotal(val) {
        let price = <?= (float)($price ?? 0) ?>;
        if (currentMode === 'wholesale') {
            const cards = document.querySelectorAll('.tier-card');
            cards.forEach(card => {
                const min = parseInt(card.dataset.min);
                const max = parseInt(card.dataset.max);
                if (val >= min && val <= max) {
                    price = parseFloat(card.dataset.price);
                }
            });
        }
        const totalEl = document.getElementById('sidebarTotalDisplay');
        if (totalEl) totalEl.textContent = '₹' + formatNum(price * val);
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
    
    document.addEventListener('DOMContentLoaded', () => {
        const inp = document.getElementById('detailQtyInput');
        if (inp) updateDetailQtyTotal(parseInt(inp.value));
    });

    let currentAtcQty = 0;

    function handleAddToCartClick(source) {
        // Partial selection guard (double-mode: color chosen but no size yet)
        if (<?= $isDoubleMode ? 'true' : 'false' ?> && currentColorSelection && selectedVariantIndex === null) {
            showPartialSelectionHint('Size');
            return;
        }
        // No variant required — null means main product (variant_id = null)
        let minQty = (currentMode === 'wholesale') ? <?= $moq ?> : 1;
        currentAtcQty = minQty;
        updateAtcStepperUI();
        addToCartFromDetail(currentAtcQty);
    }

    function changeAtcQty(delta) {
        let minQty = (currentMode === 'wholesale') ? <?= $moq ?> : 1;
        if (delta > 0 && currentAtcQty === 0) {
            currentAtcQty = minQty;
        } else if (delta < 0 && currentAtcQty <= minQty) {
            currentAtcQty = 0;
        } else {
            currentAtcQty += delta;
        }
        updateAtcStepperUI();

        if (currentAtcQty === 0) {
            addToCartFromDetail(0);
        } else {
            addToCartFromDetail(currentAtcQty);
        }
    }

    function updateAtcStepperUI() {
        if (typeof updateTierHighlightAndPrice === 'function') {
            updateTierHighlightAndPrice(currentAtcQty);
        }
        
        const mobileInit = document.getElementById('mobileAtcBtnInitial');
        const mobileStep = document.getElementById('mobileAtcStepper');
        const mobileQty = document.getElementById('mobileAtcQtyText');

        const inlineInit = document.getElementById('inlineAtcBtnInitial');
        const inlineStep = document.getElementById('inlineAtcStepper');
        const inlineQty = document.getElementById('inlineAtcQtyText');

        const fixedInit = document.getElementById('desktopFixedAtcBtnInitial');
        const fixedStep = document.getElementById('desktopFixedAtcStepper');
        const fixedQty = document.getElementById('desktopFixedAtcQtyText');

        const qtyInput = document.getElementById('detailQtyInput');
        if (qtyInput) {
            qtyInput.value = currentAtcQty > 0 ? currentAtcQty : 1;
        }

        if (currentAtcQty > 0) {
            if (mobileInit) {
                mobileInit.classList.add('hidden');
                mobileInit.classList.remove('flex');
            }
            if (mobileStep) {
                mobileStep.classList.remove('hidden');
                mobileStep.classList.add('flex');
            }
            if (mobileQty) mobileQty.textContent = currentAtcQty;

            if (inlineInit) {
                inlineInit.classList.add('hidden');
                inlineInit.classList.remove('flex');
            }
            if (inlineStep) {
                inlineStep.classList.remove('hidden');
                inlineStep.classList.add('flex');
            }
            if (inlineQty) inlineQty.textContent = currentAtcQty;

            if (fixedInit) {
                fixedInit.classList.add('hidden');
                fixedInit.classList.remove('flex');
            }
            if (fixedStep) {
                fixedStep.classList.remove('hidden');
                fixedStep.classList.add('flex');
            }
            if (fixedQty) fixedQty.textContent = currentAtcQty;
        } else {
            if (mobileInit) {
                mobileInit.classList.remove('hidden');
                mobileInit.classList.add('flex');
            }
            if (mobileStep) {
                mobileStep.classList.add('hidden');
                mobileStep.classList.remove('flex');
            }

            if (inlineInit) {
                inlineInit.classList.remove('hidden');
                inlineInit.classList.add('flex');
            }
            if (inlineStep) {
                inlineStep.classList.add('hidden');
                inlineStep.classList.remove('flex');
            }

            if (fixedInit) {
                fixedInit.classList.remove('hidden');
                fixedInit.classList.add('flex');
            }
            if (fixedStep) {
                fixedStep.classList.add('hidden');
                fixedStep.classList.remove('flex');
            }
        }
    }

    window.lastCartItems = [];

    window.syncProductDetailCartState = function(items, cartCount) {
        if (items && Array.isArray(items)) {
            window.lastCartItems = items;
        } else if ((!items || !Array.isArray(items)) && window.lastCartItems && Array.isArray(window.lastCartItems)) {
            items = window.lastCartItems;
        }

        const currentProductId = <?= (int)($product['id'] ?? 0) ?>;

        if (!items || !Array.isArray(items)) {
            currentAtcQty = 0;
            updateAtcStepperUI();
            return;
        }

        const prodItems = items.filter(i => parseInt(i.product_id) === currentProductId);
        if (prodItems.length > 0) {
            if (typeof selectedVariantIndex !== 'undefined' && selectedVariantIndex !== null && typeof VARIANTS_LIST !== 'undefined' && VARIANTS_LIST && VARIANTS_LIST[selectedVariantIndex]) {
                // Specific variant is currently selected: track qty for this variant
                const selectedV = VARIANTS_LIST[selectedVariantIndex];
                const match = prodItems.find(i => parseInt(i.variant_id) === parseInt(selectedV.id));
                currentAtcQty = match ? (parseInt(match.quantity) || 0) : 0;
            } else {
                // Main product (no specific variant card selected)
                const mainMatch = prodItems.find(i => !i.variant_id || parseInt(i.variant_id) === 0 || i.variant_id === null || i.variant_id === 'null');
                if (mainMatch) {
                    currentAtcQty = parseInt(mainMatch.quantity) || 0;
                } else {
                    // Fallback: sum all items if no variant_id match (e.g. simple product without variants)
                    let totalQty = 0;
                    prodItems.forEach(i => { totalQty += (parseInt(i.quantity) || 0); });
                    currentAtcQty = totalQty;
                }
            }
        } else {
            currentAtcQty = 0;
        }
        updateAtcStepperUI();

        if (typeof VARIANTS_LIST !== 'undefined' && VARIANTS_LIST && VARIANTS_LIST.length > 0) {
            VARIANTS_LIST.forEach((v, vi) => {
                const span = document.getElementById('vQtyVal_' + vi);
                if (span) {
                    const match = items.find(i => parseInt(i.product_id) === currentProductId && parseInt(i.variant_id) === parseInt(v.id));
                    span.textContent = match ? match.quantity : 0;
                }
            });
        }
        
        // Update Mobile Cart Badge
        const mobileBadge = document.getElementById('mobileCustomCartCount');
        if (mobileBadge) {
            if (cartCount > 0) {
                mobileBadge.style.display = 'flex';
                mobileBadge.textContent = cartCount > 99 ? '99+' : cartCount;
            } else {
                mobileBadge.style.display = 'none';
                mobileBadge.textContent = '0';
            }
        }
    };

    let detailAddToCartInFlight = false;
    async function addToCartFromDetail(overrideQty = null) {
        const pId = <?= (int)($product['id'] ?? 0) ?>;
        if (detailAddToCartInFlight) return;
        detailAddToCartInFlight = true;

        let qty = 1;
        if (overrideQty !== null) {
            qty = overrideQty;
        } else {
            const qtyInp = document.getElementById('detailQtyInput');
            qty = parseInt(qtyInp ? qtyInp.value : (currentAtcQty > 0 ? currentAtcQty : 1));
        }

        const payload = new URLSearchParams();
        payload.append('product_id', pId);
        if (typeof VARIANTS_LIST !== 'undefined' && VARIANTS_LIST && VARIANTS_LIST.length > 0) {
            const selectedV = VARIANTS_LIST[selectedVariantIndex];
            if (selectedV && selectedV.id) {
                payload.append('variant_id', selectedV.id);
            }
        }
        payload.append('quantity', qty);
        payload.append('set_exact_qty', '1');
        payload.append('pricing_mode', currentMode);

        try {
            const res = await fetch('<?= url('cart/add') ?>', { method: 'POST', body: payload });
            const data = await res.json();
            if (data.success) {
                if (qty > 0) {
                    if (!window.USER_CART_PRODUCT_IDS.includes(pId)) {
                        window.USER_CART_PRODUCT_IDS.push(pId);
                    }
                } else {
                    const idx = window.USER_CART_PRODUCT_IDS.indexOf(pId);
                    if (idx > -1) window.USER_CART_PRODUCT_IDS.splice(idx, 1);
                }
                if (typeof window.applyUserProductStates === 'function') {
                    window.applyUserProductStates();
                }
                const cCount = data.cart_count || data.count;
                if (typeof window.syncProductDetailCartState === 'function') {
                    window.syncProductDetailCartState(data.items, cCount);
                }
                if (typeof showCartToast === 'function') {
                    showCartToast(qty > 0 ? 'Cart updated!' : 'Item removed from cart');
                }
                if (typeof updateHeaderCartBadge === 'function') {
                    updateHeaderCartBadge(cCount);
                }
                if (typeof updateHeaderCartCount === 'function') {
                    updateHeaderCartCount(cCount);
                }
                if (typeof updateOrderSummarySidebar === 'function') {
                    updateOrderSummarySidebar(cCount, data.subtotal);
                }
                if (typeof renderCartDrawerUI === 'function') {
                    renderCartDrawerUI(data.items, data.subtotal, cCount);
                }
            } else {
                alert(data.message || 'Could not update cart');
            }
        } catch (e) {
            alert('Error updating cart');
        } finally {
            detailAddToCartInFlight = false;
        }
    }

    async function buyNowFromDetail() {
        // No variant required — null selectedVariantIndex means main product (variant_id = null)
        if (typeof showComingSoonModal === 'function') {
            showComingSoonModal();
        } else {
            alert('This option is coming soon. Please request a quote and we will get back to you.');
        }
    }

    async function checkDetailWishlistStatus() {
        try {
            const res = await fetch('<?= url('wishlist/status?product_id=' . $product['id']) ?>');
            const data = await res.json();
            if (data.success && data.saved) {
                setDetailWishlistActive(true);
            }
            if (data.count !== undefined && typeof updateHeaderWishlistCount === 'function') {
                updateHeaderWishlistCount(data.count);
            }
        } catch (e) { }
    }

    async function toggleDetailWishlist() {
        const pId = <?= (int)($product['id'] ?? 0) ?>;
        const payload = new URLSearchParams();
        payload.append('product_id', pId);
        try {
            const res = await fetch('<?= url('wishlist/toggle') ?>', { method: 'POST', body: payload });
            const data = await res.json();
            if (data.success) {
                const isSaved = (data.saved === true || data.status === 'added');
                setDetailWishlistActive(isSaved);
                if (isSaved) {
                    if (!window.USER_WISHLIST_PRODUCT_IDS.includes(pId)) window.USER_WISHLIST_PRODUCT_IDS.push(pId);
                } else {
                    window.USER_WISHLIST_PRODUCT_IDS = window.USER_WISHLIST_PRODUCT_IDS.filter(id => id !== pId);
                }
                if (typeof window.applyUserProductStates === 'function') {
                    window.applyUserProductStates();
                }
                if (data.count !== undefined && typeof updateHeaderWishlistCount === 'function') {
                    updateHeaderWishlistCount(data.count);
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

<!-- Custom Mobile Bottom Bar -->
<div class="md:hidden fixed bottom-0 left-0 w-full bg-white border-t border-gray-200 z-[999999] flex items-center justify-between px-2 py-2 gap-2 shadow-[0_-4px_12px_rgba(0,0,0,0.05)]" style="padding-bottom: max(8px, env(safe-area-inset-bottom));">
    
    <!-- Left Icons (Factory, Save) -->
    <div class="flex items-center gap-1 shrink-0">
        <!-- Factory Link -->
        <?php if (!empty($product['factory_code'])): ?>
            <a href="<?= url('factory/' . urlencode($product['factory_code'])) ?>" class="flex flex-col items-center justify-center min-w-[45px] text-gray-500 hover:text-[#f05a29] transition-colors border-0" style="text-decoration:none;">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span class="text-[9px] font-medium leading-none">Factory</span>
            </a>
        <?php else: ?>
            <div class="flex flex-col items-center justify-center min-w-[45px] text-gray-300 cursor-not-allowed border-0" style="text-decoration:none;">
                <svg class="w-5 h-5 mb-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                <span class="text-[9px] font-medium leading-none">Factory</span>
            </div>
        <?php endif; ?>

        <!-- Save (Wishlist) -->
        <button type="button" onclick="toggleDetailWishlist()" class="flex flex-col items-center justify-center min-w-[45px] text-gray-500 hover:text-red-500 transition-colors bg-transparent border-0 cursor-pointer" id="mobileBottomWishlistBtn">
            <svg class="w-5 h-5 mb-0.5 <?= in_array($product['id'], $initialWishlistProductIds ?? []) ? 'text-red-500 fill-current' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="mobileBottomWishlistIcon"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            <span class="text-[9px] font-medium leading-none">Save</span>
        </button>
    </div>

    <!-- Right Action Buttons -->
    <div class="flex-1 flex items-stretch gap-2 h-[44px]">
        <!-- Add to Cart Wrapper -->
        <div id="mobileCartBtnWrapper" class="flex-1 h-[44px] relative">
            <button type="button" id="mobileAtcBtnInitial" onclick="handleAddToCartClick('mobile')" class="absolute inset-0 w-full h-full rounded-full font-bold text-[13px] text-[#f05a29] border border-[#f05a29] bg-white active:bg-orange-50 flex items-center justify-center cursor-pointer">
                Add cart
            </button>
            <div id="mobileAtcStepper" class="absolute inset-0 w-full h-full rounded-full border border-[#f05a29] bg-white hidden items-center justify-between px-1">
                <button type="button" onclick="changeAtcQty(-1)" class="w-10 h-full flex items-center justify-center text-[#f05a29] text-2xl font-bold cursor-pointer bg-transparent border-0 pb-1">−</button>
                <span id="mobileAtcQtyText" class="font-bold text-[15px] text-gray-800 flex-1 text-center">1</span>
                <button type="button" onclick="changeAtcQty(1)" class="w-10 h-full flex items-center justify-center text-[#f05a29] text-2xl font-bold cursor-pointer bg-transparent border-0 pb-1">+</button>
            </div>
        </div>

        <!-- Get Quote -->
        <button type="button" onclick="if(typeof openRfqWithProducts === 'function'){openRfqWithProducts();}else{openRfqModal(null,true);}" class="flex-1 h-[44px] rounded-full font-bold text-[13px] text-white shadow-md active:scale-[0.98] transition-all border-0 cursor-pointer flex items-center justify-center" style="background: linear-gradient(to right, #ff7a18, #f05a29); color: #ffffff;">
            Get Quote
        </button>
    </div>
</div>

<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
?>