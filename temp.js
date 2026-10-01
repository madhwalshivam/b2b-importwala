
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
                

    function openDrawer(id) {
        const d = document.getElementById(id);
        if(d) { d.classList.remove('hidden'); setTimeout(() => d.classList.add('open'), 10); }
    }
    function closeDrawer(id) {
        const d = document.getElementById(id);
        if(d) { d.classList.remove('open'); setTimeout(() => d.classList.add('hidden'), 300); }
    }
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

    const GALLERY_IMAGES = <?= json_encode(array_values($gallery)) ?>;
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

    const VARIANTS_LIST = <?= json_encode($variantsJsonData) ?>;
    let selectedVariantIndex = null; // null = no variant selected yet

    function selectAmazonVariant(idx) {
        if (!VARIANTS_LIST || !VARIANTS_LIST[idx]) return;

        // Deselect: clicking the already-selected variant clears size selection, keeps color active
        if (selectedVariantIndex === idx) {
            if (<?= $isDoubleMode ? 'true' : 'false' ?> && currentColorSelection) {
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
        const prodTiers = <?= json_encode($prodTiers) ?>;
        renderVariantTiers(prodTiers);

        // Restore URL to base product URL
        window.history.replaceState(null, '', <?= json_encode($canonicalUrl) ?>);

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
        let price = <?= (float)$price ?>;
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

        const currentProductId = <?= (int) $product['id'] ?>;

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
        const pId = <?= (int) $product['id'] ?>;
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
        const pId = <?= (int) $product['id'] ?>;
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
