import sys

def main():
    # 1. Read storefront/product.php
    with open(r'c:\xampp\htdocs\importwala\app\Views\storefront\product.php', 'r', encoding='utf-8') as f:
        storefront_content = f.read()
    
    # Extract the bottom sheet HTML + JS from storefront/product.php
    # It starts at: <!-- ====================================================
    #                  MOBILE VARIANT BOTTOM SHEET
    start_marker = "<!-- ====================================================\n         MOBILE VARIANT BOTTOM SHEET"
    end_marker = "    <?php\n    include __DIR__ . '/layouts/footer.php';"
    
    start_idx = storefront_content.find(start_marker)
    if start_idx == -1:
        print("Could not find bottom sheet start marker in storefront/product.php")
        return
        
    end_idx = storefront_content.find(end_marker, start_idx)
    if end_idx == -1:
        # Fallback end marker
        end_idx = storefront_content.find("<?php\n    include __DIR__ . '/layouts/footer.php';", start_idx)
        if end_idx == -1:
            print("Could not find bottom sheet end marker in storefront/product.php")
            return
            
    bottom_sheet_code = storefront_content[start_idx:end_idx]
    
    # 2. Read views/web/product_detail.php
    target_path = r'c:\xampp\htdocs\importwala\views\web\product_detail.php'
    with open(target_path, 'r', encoding='utf-8') as f:
        web_content = f.read()
        
    # Replace Target 1 (hide desktop variant selector on mobile)
    t1 = """                <!-- Compact Variant Selector -->\n                <?php if (!empty($variants)): ?>\n                    <div class="bg-white p-3 sm:p-4 md:p-5 md:rounded-2xl shadow-sm mb-1" id="variantSelectorBox">"""
    r1 = """                <!-- Compact Variant Selector -->\n                <?php if (!empty($variants)): ?>\n                    <!-- DESKTOP VARIANT SELECTOR (Hidden on Mobile) -->\n                    <div class="hidden md:block bg-white p-3 sm:p-4 md:p-5 md:rounded-2xl shadow-sm mb-1" id="variantSelectorBox">"""
    web_content = web_content.replace(t1, r1)
    
    # Replace Target 2 (inject mobile variant selector below desktop one)
    t2 = """                            </div>\n                        <?php endif; ?>\n                    </div>\n                <?php endif; ?>"""
    r2 = """                            </div>\n                        <?php endif; ?>\n                    </div>\n\n                    <!-- MOBILE VARIANT SELECTOR (< md) -->\n                    <div class="md:hidden bg-white p-3 md:rounded-2xl shadow-sm mb-1">\n                        <div class="flex items-center justify-between mb-2">\n                            <span class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Color\n                                <?php if (!empty($variationMatrix['colors'])): ?>\n                                <span class="font-normal text-gray-500 normal-case ml-1">\n                                    (<?= count($variationMatrix['colors']) ?> options)\n                                </span>\n                                <?php endif; ?>\n                            </span>\n                        </div>\n                        <?php if (!empty($variationMatrix['colors'])): ?>\n                        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-none no-scrollbar">\n                            <?php foreach ($variationMatrix['colors'] as $color): \n                                $imgSrc = !empty($color['swatch_hex_or_image']) && (strpos($color['swatch_hex_or_image'], 'http') === 0 || strpos($color['swatch_hex_or_image'], '/') === 0) ? $color['swatch_hex_or_image'] : '';\n                                $bgStyle = !empty($color['swatch_hex_or_image']) && strpos($color['swatch_hex_or_image'], '#') === 0 ? "background-color: " . $color['swatch_hex_or_image'] : "";\n                            ?>\n                                <button type="button"\n                                    onclick="openVariantSheet(<?= $color['id'] ?>)"\n                                    title="<?= htmlspecialchars($color['color_name']) ?>"\n                                    class="shrink-0 w-11 h-11 rounded-lg border-2 border-gray-200 overflow-hidden bg-gray-50 transition cursor-pointer focus:outline-none">\n                                    <?php if ($imgSrc): ?>\n                                        <img src="<?= htmlspecialchars($imgSrc) ?>" class="w-full h-full object-cover" alt="<?= htmlspecialchars($color['color_name']) ?>">\n                                    <?php elseif ($bgStyle): ?>\n                                        <span class="block w-full h-full" style="<?= htmlspecialchars($bgStyle) ?>"></span>\n                                    <?php else: ?>\n                                        <span class="block w-full h-full flex items-center justify-center text-[9px] text-gray-500 font-medium leading-tight text-center p-0.5"><?= htmlspecialchars(substr($color['color_name'], 0, 4)) ?></span>\n                                    <?php endif; ?>\n                                </button>\n                            <?php endforeach; ?>\n                        </div>\n                        <?php endif; ?>\n                        <button type="button"\n                            onclick="openVariantSheet()"\n                            class="mt-2 w-full flex items-center justify-between border border-gray-200 rounded-xl px-3 py-2.5 text-sm text-gray-700 bg-gray-50 hover:bg-gray-100 transition cursor-pointer">\n                            <span>Select size &amp; quantity</span>\n                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>\n                        </button>\n                    </div>\n                <?php endif; ?>"""
    web_content = web_content.replace(t2, r2)
    
    # Replace Target 3 (inject bottom sheet at the end of the file)
    t3 = """<?php\n$content = ob_get_clean();\ninclude __DIR__ . '/layout.php';\n?>"""
    r3 = bottom_sheet_code + "\n" + t3
    
    # Also fix the "Add to Cart" button inside the new controller's mobile bar if we need to.
    # We can just leave it as is or change it to call openVariantSheet().
    # In views/web/product_detail.php line 1100, wait, it doesn't have it, it's just `views/web/product_detail.php`.
    # Let's check if views/web/product_detail.php has the mobile bar. 
    # Yes, lines 1550-1575 have "Custom Mobile Bottom Bar".
    
    t4 = """        <div id="mobileCartBtnWrapper" class="flex-1 h-[44px] relative">\n            <button type="button" id="mobileAtcBtnInitial" onclick="handleAddToCartClick('mobile')" class="absolute inset-0 w-full h-full rounded-full font-bold text-[13px] text-orange-600 border-2 border-orange-600 bg-white active:bg-orange-50 flex items-center justify-center cursor-pointer shadow-xs">\n                Add cart\n            </button>"""
    r4 = """        <div id="mobileCartBtnWrapper" class="flex-1 h-[44px] relative">\n            <button type="button" id="mobileAtcBtnInitial" onclick="openVariantSheet()" class="absolute inset-0 w-full h-full rounded-full font-bold text-[13px] text-orange-600 border-2 border-orange-600 bg-white active:bg-orange-50 flex items-center justify-center cursor-pointer shadow-xs">\n                Add cart\n            </button>"""
    web_content = web_content.replace(t4, r4)

    web_content = web_content.replace(t3, r3)
    
    with open(target_path, 'w', encoding='utf-8') as f:
        f.write(web_content)
        
    print("Successfully injected bottom sheet into web/product_detail.php")

if __name__ == "__main__":
    main()
