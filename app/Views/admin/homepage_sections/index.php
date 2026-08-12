<?php
include __DIR__ . '/../layouts/header.php';

$sectionDefinitions = [
    'featured_products' => [
        'name' => 'Featured Products',
        'default_title' => 'Featured Products',
        'default_subtitle' => 'Top rated accessories with verified fitment guarantee'
    ],
    'best_sellers' => [
        'name' => 'Best Sellers',
        'default_title' => 'Best Sellers',
        'default_subtitle' => 'Most popular accessories trusted by thousands of riders'
    ],
    'new_arrivals' => [
        'name' => 'New Arrivals',
        'default_title' => 'New Arrivals',
        'default_subtitle' => 'Fresh accessories just added to our collection'
    ]
];
?>

<div class="p-6 max-w-7xl mx-auto space-y-6 font-sans">

    <!-- Top Header -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span
                    class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-red-50 text-red-600 rounded-lg tracking-wider border border-red-100">
                    Storefront Control
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Database-Driven Sections</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Homepage Sections Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Control which product sections appear on the homepage, customize section titles, maximum item limits,
                and manually select & drag-and-drop order products for each section.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <a href="<?= url('') ?>" target="_blank"
                class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center space-x-2 shadow-xs">
                <i data-lucide="external-link" class="w-4 h-4 text-slate-500"></i>
                <span>Preview Storefront</span>
            </a>
        </div>
    </div>



    <!-- Featured Section Left Promo Card Control Box -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div
            class="px-6 py-4 bg-gradient-to-r from-slate-900 via-slate-800 to-black text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-red-600 flex items-center justify-center shrink-0">
                    <i data-lucide="layout-template" class="w-4 h-4 text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Featured Products: Left Promo Banner Card</h3>
                    <p class="text-[11px] text-slate-300">Customize the left special offer promo box (Title, Subtitle,
                        Badge, Button Text, Target URL & Background Image)</p>
                </div>
            </div>
            <span
                class="px-2.5 py-1 text-[10px] font-semibold uppercase bg-red-600/90 text-white rounded-lg tracking-wider">Dynamic
                Promo</span>
        </div>

        <form action="<?= url('admin/homepage-sections/update-promo') ?>" method="POST" enctype="multipart/form-data"
            class="p-6 space-y-4">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Badge Text -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Badge Text</label>
                    <input type="text" name="featured_promo_badge"
                        value="<?= htmlspecialchars($promoSettings['badge'] ?? 'SPECIAL OFFER') ?>"
                        placeholder="e.g. SPECIAL OFFER"
                        class="w-full h-9 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
                </div>

                <!-- Card Heading / Title -->
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Promo Title / Heading</label>
                    <input type="text" name="featured_promo_title"
                        value="<?= htmlspecialchars($promoSettings['title'] ?? 'Mudsor Heavy-Duty EV Protection') ?>"
                        placeholder="e.g. Mudsor Heavy-Duty EV Protection"
                        class="w-full h-9 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600 font-semibold">
                </div>
            </div>

            <!-- Description -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Promo Description / Subtitle</label>
                <textarea name="featured_promo_description" rows="2"
                    placeholder="Brief subtitle text describing the special offer..."
                    class="w-full p-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600"><?= htmlspecialchars($promoSettings['description'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Button Text -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Button Text</label>
                    <input type="text" name="featured_promo_btn_text"
                        value="<?= htmlspecialchars($promoSettings['btn_text'] ?? 'Shop Now') ?>"
                        placeholder="e.g. Shop Now"
                        class="w-full h-9 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
                </div>

                <!-- Target Link URL -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Target Link URL <span
                            class="text-slate-400 font-normal">(e.g. shop, category/crash-guards,
                            https://...)</span></label>
                    <input type="text" name="featured_promo_link"
                        value="<?= htmlspecialchars($promoSettings['link'] ?? 'shop') ?>" placeholder="e.g. shop"
                        class="w-full h-9 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
                </div>
            </div>

            <!-- Background Image Upload or URL -->
            <?php $isUrlMethod = !empty($promoSettings['image']) && preg_match('/^(https?:)?\/\//i', $promoSettings['image']); ?>
            <div x-data="{ method: '<?= $isUrlMethod ? 'url' : 'upload' ?>', preview: '<?= !empty($promoSettings['image']) ? asset($promoSettings['image']) : '' ?>' }"
                class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-semibold text-slate-800 flex items-center space-x-1.5">
                        <i data-lucide="image" class="w-4 h-4 text-red-600"></i>
                        <span>Card Banner Image <span class="text-red-600 font-semibold">(Recommended: 430 × 430 px —
                                Square 1:1)</span></span>
                    </label>
                    <div class="flex space-x-1">
                        <button type="button" @click="method='upload'"
                            :class="method==='upload' ? 'bg-red-600 text-white' : 'bg-slate-200 text-slate-700'"
                            class="px-2.5 py-1 rounded-md text-[10px] font-semibold transition cursor-pointer">Upload</button>
                        <button type="button" @click="method='url'"
                            :class="method==='url' ? 'bg-red-600 text-white' : 'bg-slate-200 text-slate-700'"
                            class="px-2.5 py-1 rounded-md text-[10px] font-semibold transition cursor-pointer">URL</button>
                    </div>
                </div>
                <p class="text-[11px] text-slate-500">Note: Uploading an image will render the card as a clean, full
                    clickable banner linked directly to your <strong>Target Link URL</strong> without any text overlays.
                </p>


                <div x-show="method==='upload'">
                    <input type="file" name="image_file" accept="image/*"
                        @change="if($event.target.files[0]) preview = URL.createObjectURL($event.target.files[0])"
                        class="block w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">
                </div>

                <div x-show="method==='url'" x-cloak>
                    <input type="text" name="image_url" value="<?= htmlspecialchars($promoSettings['image'] ?? '') ?>"
                        placeholder="https://..." @input="preview = $event.target.value"
                        class="w-full h-9 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600 bg-white">
                </div>

                <template x-if="preview">
                    <div class="mt-2 flex items-center space-x-3">
                        <img :src="preview" class="h-16 w-28 object-cover rounded-lg border border-slate-300 shadow-xs">
                        <p class="text-[11px] text-slate-500 font-medium">Card background preview active</p>
                    </div>
                </template>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="h-10 px-6 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold text-xs rounded-xl transition shadow-xs flex items-center space-x-2 cursor-pointer">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Save</span>
                </button>
            </div>
        </form>
    </div>
    <div class="space-y-6">
        <?php foreach ($sectionDefinitions as $secKey => $def): ?>
            <?php
            $sec = $sections[$secKey] ?? [
                'id' => 0,
                'section_key' => $secKey,
                'title' => $def['default_title'],
                'subtitle' => $def['default_subtitle'],
                'status' => 'inactive',
                'max_products' => 8,
                'products' => []
            ];
            $isEnabled = ($sec['status'] === 'active');
            $selectedProducts = $sec['products'] ?? [];
            ?>

            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs overflow-hidden transition hover:border-slate-300 dark:hover:border-slate-600"
                id="section-card-<?= $secKey ?>">

                <!-- Card Header -->
                <div
                    class="px-6 py-4 bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center space-x-3">
                        <div class="w-2.5 h-7 rounded-full bg-red-600 shrink-0"></div>
                        <div>
                            <div class="flex items-center space-x-2">
                                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100"><?= htmlspecialchars($def['name']) ?>
                                </h3>
                                <span
                                    class="px-2 py-0.5 text-[10px] font-semibold uppercase bg-slate-200/70 dark:bg-slate-700/80 text-slate-600 dark:text-slate-300 rounded-md"><?= $secKey ?></span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">Configured title: <span
                                    class="text-slate-800 dark:text-slate-200 font-semibold"><?= htmlspecialchars($sec['title'] ?: $def['default_title']) ?></span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-4">
                        <!-- Enable / Disable Toggle -->
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Status:</span>
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" form="form-<?= $secKey ?>" name="enabled" value="1" <?= $isEnabled ? 'checked' : '' ?> class="sr-only peer"
                                    onchange="updateSectionBadge('<?= $secKey ?>', this.checked)">
                                <div
                                    class="w-11 h-6 bg-slate-300 dark:bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600">
                                </div>
                            </label>
                            <span id="badge-status-<?= $secKey ?>"
                                class="px-2.5 py-1 text-[11px] font-semibold rounded-lg uppercase tracking-wider <?= $isEnabled ? 'bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-900/60' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' ?>">
                                <?= $isEnabled ? 'ENABLED (ON)' : 'DISABLED (OFF)' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card Body & Config Form -->
                <form action="<?= url('admin/homepage-sections/update/' . $secKey) ?>" method="POST"
                    id="form-<?= $secKey ?>" class="p-6 space-y-6">
                    <?= csrf_field() ?>

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <!-- Title -->
                        <div class="md:col-span-5 space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Section
                                Title</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($sec['title']) ?>"
                                placeholder="<?= htmlspecialchars($def['default_title']) ?>" required
                                class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                        </div>

                        <!-- Subtitle -->
                        <div class="md:col-span-5 space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Subtitle
                                (Optional)</label>
                            <input type="text" name="subtitle" value="<?= htmlspecialchars($sec['subtitle']) ?>"
                                placeholder="<?= htmlspecialchars($def['default_subtitle']) ?>"
                                class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-red-600 transition">
                        </div>

                        <!-- Max Products -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Max
                                Items</label>
                            <select name="max_products"
                                class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                                <?php foreach ([4, 6, 8, 12, 16, 20] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= (int) $sec['max_products'] === $opt ? 'selected' : '' ?>>
                                        <?= $opt ?> Products
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Hidden Product IDs Field for Form Submission -->
                    <input type="hidden" name="product_ids" id="input-ids-<?= $secKey ?>"
                        value="<?= implode(',', array_column($selectedProducts, 'id')) ?>">

                    <!-- Product Selector Box -->
                    <div class="space-y-3 pt-2 border-t border-slate-100">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-slate-800 uppercase tracking-wider">
                                Selected Products (<span id="count-<?= $secKey ?>"><?= count($selectedProducts) ?></span>)
                            </label>
                            <span class="text-[11px] text-slate-500 font-medium">Drag items to reorder. Only active selected
                                products appear on homepage.</span>
                        </div>

                        <!-- Product Search Input with Live Dropdown -->
                        <div class="relative">
                            <div class="relative">
                                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3 text-slate-400"></i>
                                <input type="text" placeholder="Search product by name or SKU to add..."
                                    oninput="debounceSearch('<?= $secKey ?>', this.value)"
                                    onfocus="searchProductsSection('<?= $secKey ?>', this.value)"
                                    class="w-full h-10 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                            </div>
                            <!-- Search Dropdown Results -->
                            <div id="results-<?= $secKey ?>"
                                class="hidden absolute z-30 top-11 left-0 right-0 bg-white border border-slate-200 rounded-xl shadow-xl max-h-60 overflow-y-auto divide-y divide-slate-100 font-sans">
                            </div>
                        </div>

                        <!-- Selected Products Drag & Drop List -->
                        <div id="selected-list-<?= $secKey ?>"
                            class="min-h-[70px] bg-slate-50 p-3 rounded-xl border border-dashed border-slate-300 flex flex-wrap gap-2.5 items-center">
                            <?php if (empty($selectedProducts)): ?>
                                <p class="text-xs text-slate-400 italic py-2 px-3 empty-placeholder">No products added yet. Use
                                    search above to select products for this section.</p>
                            <?php else: ?>
                                <?php foreach ($selectedProducts as $p): ?>
                                    <div class="product-badge bg-white border border-slate-200 rounded-xl p-2 flex items-center space-x-2.5 shadow-2xs hover:border-slate-400 cursor-move transition select-none group"
                                        draggable="true" data-product-id="<?= $p['id'] ?>"
                                        ondragstart="handleDragStart(event, '<?= $secKey ?>')" ondragover="handleDragOver(event)"
                                        ondrop="handleDrop(event, '<?= $secKey ?>')">
                                        <i data-lucide="grip-vertical"
                                            class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-700 shrink-0"></i>
                                        <img src="<?= asset($p['main_image']) ?>"
                                            class="w-8 h-8 rounded-lg object-cover bg-slate-100 shrink-0 border border-slate-100">
                                        <div class="text-left max-w-[180px]">
                                            <p class="text-[11px] font-semibold text-slate-900 truncate leading-tight">
                                                <?= htmlspecialchars($p['name']) ?>
                                            </p>
                                            <p class="text-[10px] text-slate-500 font-mono">
                                                <?= format_price($p['sale_price'] ?: $p['price']) ?>
                                            </p>
                                        </div>
                                        <button type="button" onclick="removeProductFromSection('<?= $secKey ?>', <?= $p['id'] ?>)"
                                            class="w-6 h-6 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition flex items-center justify-center shrink-0 ml-1">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Footer Action Bar -->
                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs text-slate-500 font-medium">Changes take effect immediately on storefront upon
                            saving.</span>
                        <button type="submit"
                            class="h-10 px-6 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold text-xs rounded-xl transition shadow-xs flex items-center space-x-2 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Save <?= htmlspecialchars($def['name']) ?> Settings</span>
                        </button>
                    </div>

                </form>
            </div>
        <?php endforeach; ?>
    </div>

</div>


<!-- Interactive JS Drag-and-Drop & Live Product Search -->
<script>
    let searchDebounceTimers = {};
    let draggedElement = null;
    let dragSourceSection = null;

    function updateSectionBadge(secKey, isChecked) {
        const badge = document.getElementById('badge-status-' + secKey);
        if (badge) {
            if (isChecked) {
                badge.innerText = 'ENABLED (ON)';
                badge.className = 'px-2.5 py-1 text-[11px] font-semibold rounded-lg uppercase tracking-wider bg-emerald-100 text-emerald-800 border border-emerald-200';
            } else {
                badge.innerText = 'DISABLED (OFF)';
                badge.className = 'px-2.5 py-1 text-[11px] font-semibold rounded-lg uppercase tracking-wider bg-slate-200 text-slate-600';
            }
        }
    }

    function debounceSearch(secKey, query) {
        clearTimeout(searchDebounceTimers[secKey]);
        searchDebounceTimers[secKey] = setTimeout(() => {
            searchProductsSection(secKey, query);
        }, 250);
    }

    function searchProductsSection(secKey, query) {
        const resultsBox = document.getElementById('results-' + secKey);
        if (!resultsBox) return;

        fetch('<?= url('admin/homepage-sections/search-products') ?>?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.items || data.items.length === 0) {
                    resultsBox.innerHTML = '<div class="p-3 text-xs text-slate-400 italic font-medium">No matching active products found</div>';
                    resultsBox.classList.remove('hidden');
                    return;
                }

                const currentIds = getSectionProductIds(secKey);

                let html = '';
                data.items.forEach(p => {
                    const isSelected = currentIds.includes(p.id);
                    html += `
                    <div class="p-2.5 flex items-center justify-between hover:bg-slate-50 transition cursor-pointer" onclick="addProductToSection('${secKey}', ${p.id}, '${escapeJs(p.name)}', '${p.price}', '${p.main_image}')">
                        <div class="flex items-center space-x-3">
                            <img src="${p.main_image}" class="w-8 h-8 rounded-lg object-cover bg-slate-100 border border-slate-200">
                            <div>
                                <p class="text-xs font-semibold text-slate-900 leading-tight">${escapeHtml(p.name)}</p>
                                <p class="text-[10px] text-slate-400 font-mono">SKU: ${escapeHtml(p.sku || 'N/A')} • ${p.price}</p>
                            </div>
                        </div>
                        <div>
                            ${isSelected
                            ? '<span class="text-[10px] font-semibold px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md">✓ Added</span>'
                            : '<span class="text-[10px] font-semibold px-2 py-0.5 bg-slate-900 text-white rounded-md hover:bg-black transition">+ Add</span>'}
                        </div>
                    </div>
                `;
                });
                resultsBox.innerHTML = html;
                resultsBox.classList.remove('hidden');
            });
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.relative')) {
            document.querySelectorAll('[id^="results-"]').forEach(el => el.classList.add('hidden'));
        }
    });

    function getSectionProductIds(secKey) {
        const list = document.getElementById('selected-list-' + secKey);
        if (!list) return [];
        const badges = list.querySelectorAll('.product-badge');
        const ids = [];
        badges.forEach(b => {
            const id = parseInt(b.getAttribute('data-product-id'));
            if (id) ids.push(id);
        });
        return ids;
    }

    function syncProductIdsInput(secKey) {
        const ids = getSectionProductIds(secKey);
        const input = document.getElementById('input-ids-' + secKey);
        const count = document.getElementById('count-' + secKey);
        if (input) input.value = ids.join(',');
        if (count) count.innerText = ids.length;

        const list = document.getElementById('selected-list-' + secKey);
        const emptyMsg = list.querySelector('.empty-placeholder');
        if (ids.length === 0) {
            if (!emptyMsg) {
                list.innerHTML = '<p class="text-xs text-slate-400 italic py-2 px-3 empty-placeholder">No products added yet. Use search above to select products for this section.</p>';
            }
        } else {
            if (emptyMsg) emptyMsg.remove();
        }
    }

    function addProductToSection(secKey, id, name, price, mainImage) {
        const currentIds = getSectionProductIds(secKey);
        if (currentIds.includes(id)) {
            return; // Already added
        }

        const list = document.getElementById('selected-list-' + secKey);
        const emptyMsg = list.querySelector('.empty-placeholder');
        if (emptyMsg) emptyMsg.remove();

        const badge = document.createElement('div');
        badge.className = 'product-badge bg-white border border-slate-200 rounded-xl p-2 flex items-center space-x-2.5 shadow-2xs hover:border-slate-400 cursor-move transition select-none group';
        badge.setAttribute('draggable', 'true');
        badge.setAttribute('data-product-id', id);
        badge.setAttribute('ondragstart', `handleDragStart(event, '${secKey}')`);
        badge.setAttribute('ondragover', 'handleDragOver(event)');
        badge.setAttribute('ondrop', `handleDrop(event, '${secKey}')`);

        badge.innerHTML = `
        <i data-lucide="grip-vertical" class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-700 shrink-0"></i>
        <img src="${mainImage}" class="w-8 h-8 rounded-lg object-cover bg-slate-100 shrink-0 border border-slate-100">
        <div class="text-left max-w-[180px]">
            <p class="text-[11px] font-semibold text-slate-900 truncate leading-tight">${escapeHtml(name)}</p>
            <p class="text-[10px] text-slate-400 font-mono">${price}</p>
        </div>
        <button type="button" onclick="removeProductFromSection('${secKey}', ${id})" class="w-6 h-6 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition flex items-center justify-center shrink-0 ml-1">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
    `;

        list.appendChild(badge);
        if (window.lucide) lucide.createIcons();

        syncProductIdsInput(secKey);

        // Refresh search results badge
        const resultsBox = document.getElementById('results-' + secKey);
        if (resultsBox && !resultsBox.classList.contains('hidden')) {
            const input = resultsBox.previousElementSibling.querySelector('input');
            if (input) searchProductsSection(secKey, input.value);
        }
    }

    function removeProductFromSection(secKey, id) {
        const list = document.getElementById('selected-list-' + secKey);
        const badge = list.querySelector(`.product-badge[data-product-id="${id}"]`);
        if (badge) {
            badge.remove();
            syncProductIdsInput(secKey);
        }
    }

    // Drag and Drop Logic
    function handleDragStart(e, secKey) {
        draggedElement = e.currentTarget;
        dragSourceSection = secKey;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', e.currentTarget.getAttribute('data-product-id'));
        e.currentTarget.classList.add('opacity-40');
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }

    function handleDrop(e, secKey) {
        e.preventDefault();
        if (draggedElement) {
            draggedElement.classList.remove('opacity-40');
        }

        if (dragSourceSection !== secKey) return;

        const targetBadge = e.target.closest('.product-badge');
        if (targetBadge && targetBadge !== draggedElement) {
            const list = document.getElementById('selected-list-' + secKey);
            const children = Array.from(list.children);
            const draggedIdx = children.indexOf(draggedElement);
            const targetIdx = children.indexOf(targetBadge);

            if (draggedIdx < targetIdx) {
                list.insertBefore(draggedElement, targetBadge.nextSibling);
            } else {
                list.insertBefore(draggedElement, targetBadge);
            }

            syncProductIdsInput(secKey);
        }

        draggedElement = null;
        dragSourceSection = null;
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }

    function escapeJs(str) {
        if (!str) return '';
        return str.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '\\"');
    }
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>