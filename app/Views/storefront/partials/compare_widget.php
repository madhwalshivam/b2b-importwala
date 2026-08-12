<?php
// Shared BikeDekho-style 4-Card Compare Widget (Brand -> Product Selection)
$brandModel = new \App\Models\Brand();
$allActiveBrands = $brandModel->getActiveBrands();

$cleanBrandsList = [];
foreach ($allActiveBrands as $b) {
    $logo = !empty($b['logo_path']) ? $b['logo_path'] : (!empty($b['logo']) ? $b['logo'] : null);
    $cleanBrandsList[] = [
        'id' => (int) $b['id'],
        'name' => (string) $b['name'],
        'slug' => (string) ($b['slug'] ?? ''),
        'logo' => $logo ? asset(ltrim($logo, '/')) : null
    ];
}

$productModel = new \App\Models\Product();
$rawProducts = $allProducts ?? $productModel->getAllActiveProducts();

$cleanProductsList = [];
foreach ($rawProducts as $p) {
    $cleanProductsList[] = [
        'id' => (int) $p['id'],
        'brand_id' => (int) ($p['brand_id'] ?? 0),
        'name' => (string) $p['name'],
        'price' => (float) $p['price'],
        'sale_price' => $p['sale_price'] ? (float) $p['sale_price'] : null,
        'main_image' => asset(ltrim($p['main_image'], '/')),
        'brand_name' => (string) ($p['brand_name'] ?? 'Mudsor')
    ];
}
$initialCompareIds = $_SESSION['compare'] ?? [];
?>

<!-- Shared Alpine Component Script -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('compareWidget', () => ({
            allBrands: <?= json_encode(array_values($cleanBrandsList)) ?>,
            allProducts: <?= json_encode(array_values($cleanProductsList)) ?>,
            slots: [null, null, null, null],
            selectedBrandId: ['', '', '', ''],
            brandPreviews: [null, null, null, null],

            init() {
                const initIds = <?= json_encode(array_values(array_map('intval', $initialCompareIds))) ?>;
                initIds.forEach((id, idx) => {
                    if (idx < 4) {
                        const found = this.allProducts.find(p => parseInt(p.id) === parseInt(id));
                        if (found) {
                            this.slots[idx] = found;
                            const foundBrand = this.allBrands.find(b => parseInt(b.id) === parseInt(found.brand_id));
                            if (foundBrand) {
                                this.selectedBrandId[idx] = foundBrand.id;
                                this.brandPreviews[idx] = foundBrand;
                            }
                        }
                    }
                });
            },

            onBrandChange(slotIdx) {
                const brandId = this.selectedBrandId[slotIdx];
                if (!brandId) {
                    this.brandPreviews[slotIdx] = null;
                    this.slots[slotIdx] = null;
                    return;
                }

                const foundBrand = this.allBrands.find(b => parseInt(b.id) === parseInt(brandId));
                this.brandPreviews[slotIdx] = foundBrand || null;

                // Clear product selection if selected product does not belong to the new brand
                if (this.slots[slotIdx] && foundBrand && parseInt(this.slots[slotIdx].brand_id) !== parseInt(foundBrand.id)) {
                    this.slots[slotIdx] = null;
                }
            },

            getFilteredProducts(slotIdx) {
                const brandId = this.selectedBrandId[slotIdx];
                if (!brandId) return [];
                const filtered = this.allProducts.filter(p => parseInt(p.brand_id) === parseInt(brandId));
                return filtered.length > 0 ? filtered : this.allProducts;
            },

            selectProduct(slotIdx, prodId) {
                if (!prodId) {
                    this.slots[slotIdx] = null;
                    return;
                }
                const found = this.allProducts.find(p => parseInt(p.id) === parseInt(prodId));
                if (found) {
                    this.slots[slotIdx] = found;
                }
            },

            clearSlot(slotIdx) {
                if (this.slots[slotIdx]) {
                    const pid = this.slots[slotIdx].id;
                    fetch('<?= url('compare/toggle') ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                        body: 'product_id=' + pid + '&_csrf_token=<?= csrf_token() ?>'
                    });
                }
                this.slots[slotIdx] = null;
                this.brandPreviews[slotIdx] = null;
                this.selectedBrandId[slotIdx] = '';
            },

            submitCompare() {
                const selectedIds = this.slots.filter(s => s !== null).map(s => s.id);
                if (selectedIds.length < 2) {
                    alert('Please select at least 2 products to compare!');
                    return;
                }
                location.href = '<?= url('compare') ?>?ids=' + selectedIds.join(',');
            }
        }));
    });
</script>

<div class="bg-white rounded-3xl p-3 sm:p-6 md:p-8 border border-gray-100 dark:border-slate-800 shadow-lg space-y-8 font-sans"
    x-data="compareWidget()">

    <!-- Mobile & Tablet Swiper Carousel (<1025px) -->
    <div class="block lg:hidden overflow-hidden relative pb-6">
        <div class="swiper swiper-compare w-full py-1">
            <div class="swiper-wrapper">
                <template x-for="(slot, idx) in slots" :key="idx">
                    <div class="swiper-slide h-auto">
                        <div
                            class="bg-white rounded-2xl border border-gray-100 dark:border-slate-800 p-5 flex flex-col items-center justify-between space-y-4 hover:border-red-400 transition-all shadow-xs relative w-full h-full min-h-[340px]">

                            <!-- Clear Slot Button -->
                            <button x-show="selectedBrandId[idx] !== '' || slot !== null" @click="clearSlot(idx)"
                                class="absolute top-3 right-3 text-gray-400 hover:text-red-600 p-1 bg-white/80 rounded-full z-10"
                                title="Clear slot">
                                <i data-lucide="x-circle" class="w-5 h-5"></i>
                            </button>

                            <!-- 1. EMPTY STATE: Dotted Circle with Red Scooty/Bike SVG Icon -->
                            <div x-show="selectedBrandId[idx] === '' && slot === null"
                                class="flex flex-col items-center justify-center py-4 space-y-3 flex-1">
                                <div
                                    class="w-20 h-20 rounded-full border-2 border-dashed border-red-300 flex items-center justify-center text-red-600 bg-red-50/60 hover:bg-red-100 hover:border-red-500 transition cursor-pointer shadow-xs">
                                    <svg class="w-10 h-10 text-red-600" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <circle cx="5.5" cy="17.5" r="3.5"></circle>
                                        <circle cx="18.5" cy="17.5" r="3.5"></circle>
                                        <path d="M15 6h4l-2 5h-7l-2-4H4"></path>
                                        <path d="M9 17.5h6"></path>
                                        <path d="M19 14v3.5"></path>
                                        <path d="M5.5 14v3.5"></path>
                                    </svg>
                                </div>
                                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Add Brand /
                                    Product</span>
                            </div>

                            <!-- 2. BRAND SELECTED PREVIEW -->
                            <div x-show="selectedBrandId[idx] !== '' && slot === null"
                                class="flex flex-col items-center text-center space-y-2 flex-1 w-full pt-2">
                                <div
                                    class="w-24 h-24 bg-gray-50 rounded-2xl border border-gray-900 overflow-hidden p-2 flex items-center justify-center">
                                    <template x-if="brandPreviews[idx] && brandPreviews[idx].logo">
                                        <img :src="brandPreviews[idx].logo"
                                            class="w-full h-full object-contain rounded-xl"
                                            :alt="brandPreviews[idx].name" loading="lazy">
                                    </template>
                                    <template x-if="!brandPreviews[idx] || !brandPreviews[idx].logo">
                                        <div class="flex flex-col items-center justify-center text-red-600">
                                            <svg class="w-10 h-10 text-red-600" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <circle cx="5.5" cy="17.5" r="3.5"></circle>
                                                <circle cx="18.5" cy="17.5" r="3.5"></circle>
                                                <path d="M15 6h4l-2 5h-7l-2-4H4"></path>
                                                <path d="M9 17.5h6"></path>
                                                <path d="M19 14v3.5"></path>
                                                <path d="M5.5 14v3.5"></path>
                                            </svg>
                                            <span class="text-[10px] font-semibold mt-1 font-mono text-red-600"
                                                x-text="brandPreviews[idx] ? brandPreviews[idx].name : ''"></span>
                                        </div>
                                    </template>
                                </div>
                                <span class="text-xs font-semibold text-gray-900 leading-snug"
                                    x-text="brandPreviews[idx] ? brandPreviews[idx].name : ''"></span>
                                <span
                                    class="text-[11px] font-semibold text-green-700 bg-green-50 px-2.5 py-0.5 rounded-full border border-green-200">Select
                                    Product Below</span>
                            </div>

                            <!-- 3. PRODUCT SELECTED PREVIEW -->
                            <div x-show="slot !== null"
                                class="flex flex-col items-center text-center space-y-2 flex-1 w-full pt-2">
                                <div
                                    class="w-24 h-24 bg-gray-50 rounded-2xl border border-gray-900 overflow-hidden p-1 flex items-center justify-center">
                                    <img :src="slot ? slot.main_image : ''"
                                        class="w-full h-full object-cover rounded-xl" loading="lazy">
                                </div>
                                <span class="text-[10px] font-semibold text-red-600 uppercase tracking-wider block"
                                    x-text="slot ? slot.brand_name : ''"></span>
                                <h4 class="text-xs font-semibold text-gray-900 line-clamp-2 leading-snug"
                                    x-text="slot ? slot.name : ''"></h4>
                                <p class="text-sm font-semibold text-red-600"
                                    x-text="slot ? '₹' + (slot.sale_price || slot.price) : ''"></p>
                            </div>

                            <!-- DROPDOWNS SECTION -->
                            <div class="w-full space-y-2.5 pt-2 border-t border-gray-100">
                                <!-- Dropdown 1: Select Brand -->
                                <div class="relative">
                                    <select x-model="selectedBrandId[idx]" @change="onBrandChange(idx)"
                                        class="w-full h-10 px-3 bg-gray-50 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 appearance-none pr-8 focus:outline-none focus:border-red-600 cursor-pointer">
                                        <option value="">Select Brand First</option>
                                        <?php foreach ($cleanBrandsList as $b): ?>
                                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <i data-lucide="chevron-down"
                                        class="w-3.5 h-3.5 text-gray-400 absolute right-2.5 top-3 pointer-events-none"></i>
                                </div>

                                <!-- Dropdown 2: Select Product -->
                                <div class="relative">
                                    <select :disabled="selectedBrandId[idx] === ''" :value="slot ? slot.id : ''"
                                        @change="selectProduct(idx, $el.value)"
                                        :class="selectedBrandId[idx] === '' ? 'bg-gray-100 text-gray-400 cursor-not-allowed border-gray-900' : 'bg-white text-gray-900 border-red-300 focus:border-red-600 cursor-pointer'"
                                        class="w-full h-10 px-3 border rounded-xl text-xs font-semibold appearance-none pr-8 focus:outline-none transition-all">
                                        <option value=""
                                            x-text="selectedBrandId[idx] === '' ? '⚠️ Select Brand First' : 'Select Product'">
                                        </option>
                                        <template x-for="p in getFilteredProducts(idx)" :key="p.id">
                                            <option :value="p.id"
                                                x-text="p.name + ' (₹' + (p.sale_price || p.price) + ')'">
                                            </option>
                                        </template>
                                    </select>
                                    <i data-lucide="chevron-down"
                                        class="w-3.5 h-3.5 text-gray-400 absolute right-2.5 top-3 pointer-events-none"></i>
                                </div>
                            </div>

                        </div>
                    </div>
                </template>
            </div>
            <!-- Pagination Dots outside cards -->
            <div class="swiper-compare-pagination swiper-pagination !-bottom-1"></div>
        </div>
    </div>

    <!-- Desktop Grid (1025px+) - Completely Unchanged -->
    <div class="hidden lg:grid lg:grid-cols-4 gap-6">
        <template x-for="(slot, idx) in slots" :key="idx">
            <div
                class="bg-white rounded-2xl border border-gray-100 dark:border-slate-800 p-6 flex flex-col items-center justify-between space-y-4 hover:border-red-400 transition-all shadow-xs relative min-h-[340px]">

                <!-- Clear Slot Button -->
                <button x-show="selectedBrandId[idx] !== '' || slot !== null" @click="clearSlot(idx)"
                    class="absolute top-3 right-3 text-gray-400 hover:text-red-600 p-1 bg-white/80 rounded-full z-10"
                    title="Clear slot">
                    <i data-lucide="x-circle" class="w-5 h-5"></i>
                </button>

                <!-- 1. EMPTY STATE: Dotted Circle with Red Scooty/Bike SVG Icon (When no brand selected) -->
                <div x-show="selectedBrandId[idx] === '' && slot === null"
                    class="flex flex-col items-center justify-center py-4 space-y-3 flex-1">
                    <div
                        class="w-20 h-20 rounded-full border-2 border-dashed border-red-300 flex items-center justify-center text-red-600 bg-red-50/60 hover:bg-red-100 hover:border-red-500 transition cursor-pointer shadow-xs">
                        <svg class="w-10 h-10 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="5.5" cy="17.5" r="3.5"></circle>
                            <circle cx="18.5" cy="17.5" r="3.5"></circle>
                            <path d="M15 6h4l-2 5h-7l-2-4H4"></path>
                            <path d="M9 17.5h6"></path>
                            <path d="M19 14v3.5"></path>
                            <path d="M5.5 14v3.5"></path>
                        </svg>
                    </div>
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Add Brand /
                        Product</span>
                </div>

                <!-- 2. BRAND SELECTED PREVIEW (When brand selected but no product chosen yet) -->
                <div x-show="selectedBrandId[idx] !== '' && slot === null"
                    class="flex flex-col items-center text-center space-y-2 flex-1 w-full pt-2">
                    <div
                        class="w-24 h-24 bg-gray-50 rounded-2xl border border-gray-900 overflow-hidden p-2 flex items-center justify-center">
                        <template x-if="brandPreviews[idx] && brandPreviews[idx].logo">
                            <img :src="brandPreviews[idx].logo" class="w-full h-full object-contain rounded-xl"
                                :alt="brandPreviews[idx].name" loading="lazy">
                        </template>
                        <template x-if="!brandPreviews[idx] || !brandPreviews[idx].logo">
                            <div class="flex flex-col items-center justify-center text-red-600">
                                <svg class="w-10 h-10 text-red-600" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <circle cx="5.5" cy="17.5" r="3.5"></circle>
                                    <circle cx="18.5" cy="17.5" r="3.5"></circle>
                                    <path d="M15 6h4l-2 5h-7l-2-4H4"></path>
                                    <path d="M9 17.5h6"></path>
                                    <path d="M19 14v3.5"></path>
                                    <path d="M5.5 14v3.5"></path>
                                </svg>
                                <span class="text-[10px] font-semibold mt-1 font-mono text-red-600"
                                    x-text="brandPreviews[idx] ? brandPreviews[idx].name : ''"></span>
                            </div>
                        </template>
                    </div>
                    <span class="text-xs font-semibold text-gray-900 leading-snug"
                        x-text="brandPreviews[idx] ? brandPreviews[idx].name : ''"></span>
                    <span
                        class="text-[11px] font-semibold text-green-700 bg-green-50 px-2.5 py-0.5 rounded-full border border-green-200">Select
                        Product Below</span>
                </div>

                <!-- 3. PRODUCT SELECTED PREVIEW (When product is selected) -->
                <div x-show="slot !== null" class="flex flex-col items-center text-center space-y-2 flex-1 w-full pt-2">
                    <div
                        class="w-24 h-24 bg-gray-50 rounded-2xl border border-gray-900 overflow-hidden p-1 flex items-center justify-center">
                        <img :src="slot ? slot.main_image : ''" class="w-full h-full object-cover rounded-xl"
                            loading="lazy">
                    </div>
                    <span class="text-[10px] font-semibold text-red-600 uppercase tracking-wider block"
                        x-text="slot ? slot.brand_name : ''"></span>
                    <h4 class="text-xs font-semibold text-gray-900 line-clamp-2 leading-snug"
                        x-text="slot ? slot.name : ''"></h4>
                    <p class="text-sm font-semibold text-red-600"
                        x-text="slot ? '₹' + (slot.sale_price || slot.price) : ''"></p>
                </div>

                <!-- DROPDOWNS SECTION -->
                <div class="w-full space-y-2.5 pt-2 border-t border-gray-100">

                    <!-- Dropdown 1: Select Brand -->
                    <div class="relative">
                        <select x-model="selectedBrandId[idx]" @change="onBrandChange(idx)"
                            class="w-full h-10 px-3 bg-gray-50 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 appearance-none pr-8 focus:outline-none focus:border-red-600 cursor-pointer">
                            <option value="">Select Brand First</option>
                            <?php foreach ($cleanBrandsList as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <i data-lucide="chevron-down"
                            class="w-3.5 h-3.5 text-gray-400 absolute right-2.5 top-3 pointer-events-none"></i>
                    </div>

                    <!-- Dropdown 2: Select Product (DISABLED until Brand is selected) -->
                    <div class="relative">
                        <select :disabled="selectedBrandId[idx] === ''" :value="slot ? slot.id : ''"
                            @change="selectProduct(idx, $el.value)"
                            :class="selectedBrandId[idx] === '' ? 'bg-gray-100 text-gray-400 cursor-not-allowed border-gray-900' : 'bg-white text-gray-900 border-red-300 focus:border-red-600 cursor-pointer'"
                            class="w-full h-10 px-3 border rounded-xl text-xs font-semibold appearance-none pr-8 focus:outline-none transition-all">
                            <option value=""
                                x-text="selectedBrandId[idx] === '' ? '⚠️ Select Brand First' : 'Select Product'">
                            </option>
                            <template x-for="p in getFilteredProducts(idx)" :key="p.id">
                                <option :value="p.id" x-text="p.name + ' (₹' + (p.sale_price || p.price) + ')'">
                                </option>
                            </template>
                        </select>
                        <i data-lucide="chevron-down"
                            class="w-3.5 h-3.5 text-gray-400 absolute right-2.5 top-3 pointer-events-none"></i>
                    </div>

                </div>

            </div>
        </template>
    </div>

    <!-- Centered Prominent Red "Compare Now" Button -->
    <div class="flex justify-center pt-2">
        <button @click="submitCompare()"
            class="h-12 px-12 bg-red-600 hover:bg-red-700 text-white font-semibold text-sm rounded-xl shadow-lg transition transform hover:scale-105 flex items-center space-x-2">
            <i data-lucide="git-compare" class="w-5 h-5"></i>
            <span>Compare Now</span>
        </button>
    </div>

</div>