<?php
include __DIR__ . '/../layouts/header.php';

// Prepare list of raw sections ordered by sort_order
$displaySections = !empty($rawSections) ? $rawSections : array_values($sections);
?>

<div class="p-6 max-w-7xl mx-auto space-y-6 font-sans" x-data="{ addModalOpen: false, editModalOpen: false, activeEditSection: {} }">

    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-red-50 text-red-600 rounded-lg tracking-wider border border-red-100">
                    Storefront Control
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Dynamic Homepage Sections</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Homepage Sections Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Create unlimited dynamic homepage sections, reorder sections, manage product assignments with drag-and-drop, and link each section to a dedicated "View All" page.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <button type="button" @click="addModalOpen = true"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white text-xs font-semibold rounded-xl transition flex items-center space-x-2 shadow-xs cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>+ Add New Section</span>
            </button>
            <a href="<?= url('') ?>" target="_blank"
                class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center space-x-2 shadow-xs">
                <i data-lucide="external-link" class="w-4 h-4 text-slate-500"></i>
                <span>Preview Storefront</span>
            </a>
        </div>
    </div>


    <!-- ADD NEW SECTION MODAL -->
    <div x-show="addModalOpen" x-cloak
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div @click.away="addModalOpen = false"
            class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden space-y-0">
            
            <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                <div class="flex items-center space-x-2.5">
                    <i data-lucide="plus-circle" class="w-5 h-5 text-red-500"></i>
                    <h3 class="text-sm font-semibold text-white">Add New Homepage Section</h3>
                </div>
                <button type="button" @click="addModalOpen = false" class="text-slate-400 hover:text-white transition">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="<?= url('admin/homepage-sections/store') ?>" method="POST" class="p-6 space-y-4" x-data="{ title: '', slug: '' }">
                <?= csrf_field() ?>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Section Title <span class="text-red-600">*</span></label>
                    <input type="text" name="title" x-model="title" @input="slug = slugify(title)" required
                        placeholder="e.g. Summer Deals, Flash Sale, Trending Accessories"
                        class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-red-600">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                        URL Slug <span class="text-slate-400 font-normal">(Dedicated page: /section/{slug})</span>
                    </label>
                    <input type="text" name="slug" x-model="slug" required
                        placeholder="e.g. summer-deals"
                        class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs font-mono focus:outline-none focus:border-red-600">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Subtitle / Description</label>
                    <input type="text" name="subtitle"
                        placeholder="e.g. Exclusive discounts on heavy-duty accessories"
                        class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase">Homepage Limit</label>
                        <select name="homepage_display_count" class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs font-semibold">
                            <option value="0">Show All (No Limit)</option>
                            <option value="3">3 Items</option>
                            <option value="4">4 Items</option>
                            <option value="5" selected>5 Items</option>
                            <option value="6">6 Items</option>
                            <option value="8">8 Items</option>
                            <option value="10">10 Items</option>
                            <option value="12">12 Items</option>
                            <option value="20">20 Items</option>
                            <option value="50">50 Items</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-semibold text-slate-700 uppercase">Display Order</label>
                        <input type="number" name="sort_order" value="10" min="0"
                            class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs font-semibold focus:outline-none focus:border-red-600">
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-2 pt-4 border-t border-slate-100">
                    <button type="button" @click="addModalOpen = false"
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition shadow-xs flex items-center space-x-1.5">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Create Section</span>
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- Featured Section Left Promo Card Control Box -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-6 py-4 bg-gradient-to-r from-slate-900 via-slate-800 to-black text-white flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-lg bg-red-600 flex items-center justify-center shrink-0">
                    <i data-lucide="layout-template" class="w-4 h-4 text-white"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Featured Products: Left Promo Banner Card</h3>
                    <p class="text-[11px] text-slate-300">Customize the left special offer promo box (Title, Subtitle, Badge, Button Text, Target URL & Background Image)</p>
                </div>
            </div>
            <span class="px-2.5 py-1 text-[10px] font-semibold uppercase bg-red-600/90 text-white rounded-lg tracking-wider">Dynamic Promo</span>
        </div>

        <form action="<?= url('admin/homepage-sections/update-promo') ?>" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <?= csrf_field() ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Badge Text</label>
                    <input type="text" name="featured_promo_badge"
                        value="<?= htmlspecialchars($promoSettings['badge'] ?? 'SPECIAL OFFER') ?>"
                        placeholder="e.g. SPECIAL OFFER"
                        class="w-full h-9 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Promo Title / Heading</label>
                    <input type="text" name="featured_promo_title"
                        value="<?= htmlspecialchars($promoSettings['title'] ?? 'Mudsor Heavy-Duty EV Protection') ?>"
                        placeholder="e.g. Mudsor Heavy-Duty EV Protection"
                        class="w-full h-9 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600 font-semibold">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Promo Description / Subtitle</label>
                <textarea name="featured_promo_description" rows="2"
                    placeholder="Brief subtitle text describing the special offer..."
                    class="w-full p-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600"><?= htmlspecialchars($promoSettings['description'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Button Text</label>
                    <input type="text" name="featured_promo_btn_text"
                        value="<?= htmlspecialchars($promoSettings['btn_text'] ?? 'Shop Now') ?>"
                        placeholder="e.g. Shop Now"
                        class="w-full h-9 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Target Link URL</label>
                    <input type="text" name="featured_promo_link"
                        value="<?= htmlspecialchars($promoSettings['link'] ?? 'shop') ?>" placeholder="e.g. shop"
                        class="w-full h-9 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
                </div>
            </div>

            <?php $isUrlMethod = !empty($promoSettings['image']) && preg_match('/^(https?:)?\/\//i', $promoSettings['image']); ?>
            <div x-data="{ method: '<?= $isUrlMethod ? 'url' : 'upload' ?>', preview: '<?= !empty($promoSettings['image']) ? asset($promoSettings['image']) : '' ?>' }"
                class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-3">
                <div class="flex items-center justify-between">
                    <label class="text-xs font-semibold text-slate-800 flex items-center space-x-1.5">
                        <i data-lucide="image" class="w-4 h-4 text-red-600"></i>
                        <span>Card Banner Image <span class="text-red-600 font-semibold">(Recommended: 430 × 430 px)</span></span>
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
                    <span>Save Promo Banner Settings</span>
                </button>
            </div>
        </form>
    </div>


    <!-- DYNAMIC HOMEPAGE SECTIONS LIST -->
    <div class="space-y-6">
        <?php foreach ($displaySections as $sec): ?>
            <?php
            $secId = (int)$sec['id'];
            $secKey = $sec['section_key'] ?: ('sec_' . $secId);
            $secSlug = $sec['slug'] ?: slugify($sec['title'] ?? $secKey);
            $isEnabled = ($sec['status'] === 'active' || $sec['status'] === 'enabled');
            $selectedProducts = $sec['products'] ?? [];
            ?>

            <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-xs relative transition hover:border-slate-300 dark:hover:border-slate-600"
                id="section-card-<?= $secId ?>">

                <!-- Card Header -->
                <div class="px-6 py-4 bg-slate-50/80 dark:bg-slate-900/60 border-b border-slate-200 dark:border-slate-700 rounded-t-2xl flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center space-x-3 min-w-0 flex-1">
                        <div class="w-2.5 h-7 rounded-full bg-red-600 shrink-0"></div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center space-x-2 flex-wrap">
                                <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100 break-words">
                                    <?= htmlspecialchars($sec['title']) ?>
                                </h3>
                                <span class="px-2 py-0.5 text-[10px] font-mono font-semibold bg-slate-200/70 dark:bg-slate-700/80 text-slate-600 dark:text-slate-300 rounded-md shrink-0">
                                    /section/<?= htmlspecialchars($secSlug) ?>
                                </span>
                                <span class="px-2 py-0.5 text-[10px] font-semibold bg-red-50 text-red-600 border border-red-100 rounded-md shrink-0">
                                    Order: <?= (int)($sec['sort_order'] ?? 0) ?>
                                </span>
                            </div>
                            <?php if (!empty($sec['subtitle'])): ?>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium truncate">
                                    <?= htmlspecialchars($sec['subtitle']) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3 shrink-0">
                        <!-- View Dedicated Page Link -->
                        <a href="<?= url('section/' . $secSlug) ?>" target="_blank" title="View dedicated View All page"
                            class="p-2 text-slate-500 hover:text-red-600 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg transition">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                        </a>

                        <!-- Delete Section Button -->
                        <form action="<?= url('admin/homepage-sections/delete/' . $secId) ?>" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this homepage section? (Products will NOT be deleted)');"
                            class="inline-block">
                            <?= csrf_field() ?>
                            <button type="submit" title="Delete Section"
                                class="p-2 text-slate-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/60 rounded-lg transition cursor-pointer">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>

                        <!-- Enable / Disable Toggle -->
                        <div class="flex items-center space-x-2 pl-2 border-l border-slate-200 dark:border-slate-700">
                            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Status:</span>
                            <label class="relative inline-flex items-center cursor-pointer select-none">
                                <input type="checkbox" form="form-<?= $secId ?>" name="enabled" value="1" <?= $isEnabled ? 'checked' : '' ?> class="sr-only peer"
                                    onchange="updateSectionBadge(<?= $secId ?>, this.checked)">
                                <div class="w-11 h-6 bg-slate-300 dark:bg-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600">
                                </div>
                            </label>
                            <span id="badge-status-<?= $secId ?>"
                                class="px-2.5 py-1 text-[11px] font-semibold rounded-lg uppercase tracking-wider <?= $isEnabled ? 'bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-900/60' : 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' ?>">
                                <?= $isEnabled ? 'ENABLED (ON)' : 'DISABLED (OFF)' ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Card Body & Config Form -->
                <form action="<?= url('admin/homepage-sections/update/' . $secId) ?>" method="POST"
                    id="form-<?= $secId ?>" class="p-6 space-y-6">
                    <?= csrf_field() ?>
                    <input type="hidden" name="status" id="status-input-<?= $secId ?>" value="<?= $isEnabled ? 'active' : 'inactive' ?>">

                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        <!-- Title -->
                        <div class="md:col-span-4 space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Section Title</label>
                            <input type="text" name="title" value="<?= htmlspecialchars($sec['title']) ?>" required
                                class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                        </div>

                        <!-- Subtitle -->
                        <div class="md:col-span-4 space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Subtitle</label>
                            <input type="text" name="subtitle" value="<?= htmlspecialchars($sec['subtitle'] ?? '') ?>"
                                placeholder="Subtitle text..."
                                class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-red-600 transition">
                        </div>

                        <!-- Slug -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Slug</label>
                            <input type="text" name="slug" value="<?= htmlspecialchars($secSlug) ?>" required
                                class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                        </div>

                        <!-- Homepage Preview Count -->
                        <div class="md:col-span-2 space-y-1.5">
                            <label class="block text-[11px] font-semibold text-slate-700 uppercase">Preview</label>
                            <select name="homepage_display_count"
                                class="w-full h-10 px-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                                <option value="0" <?= (int)($sec['homepage_display_count'] ?? 0) === 0 ? 'selected' : '' ?>>Show All</option>
                                <?php foreach ([3, 4, 5, 6, 8, 10, 12, 20, 50] as $opt): ?>
                                    <option value="<?= $opt ?>" <?= (int)($sec['homepage_display_count'] ?? 5) === $opt ? 'selected' : '' ?>>
                                        <?= $opt ?> Home
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Hidden Product IDs Field for Form Submission -->
                    <input type="hidden" name="product_ids" id="input-ids-<?= $secId ?>"
                        value="<?= implode(',', array_column($selectedProducts, 'id')) ?>">

                    <!-- Product Selector Box -->
                    <div class="space-y-3 pt-2 border-t border-slate-100 relative">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-semibold text-slate-800 uppercase tracking-wider">
                                Selected Products (<span id="count-<?= $secId ?>"><?= count($selectedProducts) ?></span>)
                            </label>
                            <span class="text-[11px] text-slate-500 font-medium">Drag items to reorder. First <?= (int)($sec['homepage_display_count'] ?? 5) ?> items appear on homepage.</span>
                        </div>

                        <!-- Product Search Input with Live Dropdown -->
                        <div class="relative z-30">
                            <div class="relative">
                                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3 text-slate-400"></i>
                                <input type="text" placeholder="Search product by name or SKU to add..."
                                    oninput="debounceSearch(<?= $secId ?>, this.value)"
                                    onfocus="searchProductsSection(<?= $secId ?>, this.value)"
                                    class="w-full h-10 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition shadow-2xs">
                            </div>
                            <!-- Floating Overlay Search Dropdown Results -->
                            <div id="results-<?= $secId ?>"
                                class="hidden absolute z-50 top-11 left-0 right-0 bg-white border border-slate-300 rounded-2xl shadow-2xl max-h-[380px] overflow-y-auto divide-y divide-slate-100 font-sans max-w-full">
                            </div>
                        </div>

                        <!-- Selected Products Drag & Drop List -->
                        <div id="selected-list-<?= $secId ?>"
                            class="min-h-[70px] bg-slate-50 p-3 rounded-xl border border-dashed border-slate-300 flex flex-wrap gap-2.5 items-center">
                            <?php if (empty($selectedProducts)): ?>
                                <p class="text-xs text-slate-400 italic py-2 px-3 empty-placeholder">No products added yet. Use search above to select products for this section.</p>
                            <?php else: ?>
                                <?php foreach ($selectedProducts as $p): ?>
                                    <div class="product-badge bg-white border border-slate-200 rounded-xl p-2 flex items-center space-x-2.5 shadow-2xs hover:border-slate-400 cursor-move transition select-none group max-w-full"
                                        draggable="true" data-product-id="<?= $p['id'] ?>"
                                        ondragstart="handleDragStart(event, <?= $secId ?>)" ondragover="handleDragOver(event)"
                                        ondrop="handleDrop(event, <?= $secId ?>)">
                                        <i data-lucide="grip-vertical" class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-700 shrink-0"></i>
                                        <img src="<?= asset($p['main_image']) ?>" class="w-8 h-8 rounded-lg object-cover bg-slate-100 shrink-0 border border-slate-100">
                                        <div class="text-left max-w-[180px] min-w-0">
                                            <p class="text-[11px] font-semibold text-slate-900 truncate leading-tight" title="<?= htmlspecialchars($p['name']) ?>">
                                                <?= htmlspecialchars($p['name']) ?>
                                            </p>
                                            <p class="text-[10px] text-slate-500 font-mono truncate">
                                                <?= format_price($p['sale_price'] ?: $p['price']) ?>
                                            </p>
                                        </div>
                                        <button type="button" onclick="removeProductFromSection(<?= $secId ?>, <?= $p['id'] ?>)"
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
                        <span class="text-xs text-slate-500 font-medium">Changes take effect immediately on storefront upon saving.</span>
                        <button type="submit"
                            class="h-10 px-6 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold text-xs rounded-xl transition shadow-xs flex items-center space-x-2 cursor-pointer">
                            <i data-lucide="save" class="w-4 h-4"></i>
                            <span>Save Section Settings</span>
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

    function slugify(text) {
        if (!text) return '';
        return text.toString().toLowerCase().trim()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
            .replace(/\-\-+/g, '-');
    }

    function updateSectionBadge(secId, isChecked) {
        const badge = document.getElementById('badge-status-' + secId);
        const statusInput = document.getElementById('status-input-' + secId);
        if (statusInput) {
            statusInput.value = isChecked ? 'active' : 'inactive';
        }
        if (badge) {
            if (isChecked) {
                badge.innerText = 'ENABLED (ON)';
                badge.className = 'px-2.5 py-1 text-[11px] font-semibold rounded-lg uppercase tracking-wider bg-red-50 dark:bg-red-950/60 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-900/60';
            } else {
                badge.innerText = 'DISABLED (OFF)';
                badge.className = 'px-2.5 py-1 text-[11px] font-semibold rounded-lg uppercase tracking-wider bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300';
            }
        }
    }

    function debounceSearch(secId, query) {
        clearTimeout(searchDebounceTimers[secId]);
        searchDebounceTimers[secId] = setTimeout(() => {
            searchProductsSection(secId, query);
        }, 250);
    }

    function searchProductsSection(secId, query) {
        const resultsBox = document.getElementById('results-' + secId);
        if (!resultsBox) return;

        fetch('<?= url('admin/homepage-sections/search-products') ?>?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.items || data.items.length === 0) {
                    resultsBox.innerHTML = '<div class="p-3 text-xs text-slate-400 italic font-medium">No matching active products found</div>';
                    resultsBox.classList.remove('hidden');
                    return;
                }

                const currentIds = getSectionProductIds(secId);

                let html = '';
                data.items.forEach(p => {
                    const isSelected = currentIds.includes(p.id);
                    html += `
                    <div class="p-2.5 flex items-center justify-between gap-3 hover:bg-slate-50 transition cursor-pointer min-w-0" onclick="addProductToSection(${secId}, ${p.id}, '${escapeJs(p.name)}', '${p.price}', '${p.main_image}')">
                        <div class="flex items-center space-x-3 min-w-0 flex-1">
                            <img src="${p.main_image}" class="w-8 h-8 rounded-lg object-cover bg-slate-100 border border-slate-200 shrink-0">
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-semibold text-slate-900 leading-tight line-clamp-2 break-words" title="${escapeHtml(p.name)}">${escapeHtml(p.name)}</p>
                                <p class="text-[10px] text-slate-400 font-mono truncate">SKU: ${escapeHtml(p.sku || 'N/A')} • ${p.price}</p>
                            </div>
                        </div>
                        <div class="shrink-0 ml-2">
                            ${isSelected
                            ? '<span class="text-[10px] font-semibold px-2 py-0.5 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-md whitespace-nowrap">✓ Added</span>'
                            : '<span class="text-[10px] font-semibold px-2 py-0.5 bg-slate-900 text-white rounded-md hover:bg-black transition whitespace-nowrap">+ Add</span>'}
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

    function getSectionProductIds(secId) {
        const list = document.getElementById('selected-list-' + secId);
        if (!list) return [];
        const badges = list.querySelectorAll('.product-badge');
        const ids = [];
        badges.forEach(b => {
            const id = parseInt(b.getAttribute('data-product-id'));
            if (id) ids.push(id);
        });
        return ids;
    }

    function syncProductIdsInput(secId) {
        const ids = getSectionProductIds(secId);
        const input = document.getElementById('input-ids-' + secId);
        const count = document.getElementById('count-' + secId);
        if (input) input.value = ids.join(',');
        if (count) count.innerText = ids.length;

        const list = document.getElementById('selected-list-' + secId);
        const emptyMsg = list.querySelector('.empty-placeholder');
        if (ids.length === 0) {
            if (!emptyMsg) {
                list.innerHTML = '<p class="text-xs text-slate-400 italic py-2 px-3 empty-placeholder">No products added yet. Use search above to select products for this section.</p>';
            }
        } else {
            if (emptyMsg) emptyMsg.remove();
        }
    }

    function addProductToSection(secId, id, name, price, mainImage) {
        const currentIds = getSectionProductIds(secId);
        if (currentIds.includes(id)) {
            return;
        }

        const list = document.getElementById('selected-list-' + secId);
        const emptyMsg = list.querySelector('.empty-placeholder');
        if (emptyMsg) emptyMsg.remove();

        const badge = document.createElement('div');
        badge.className = 'product-badge bg-white border border-slate-200 rounded-xl p-2 flex items-center space-x-2.5 shadow-2xs hover:border-slate-400 cursor-move transition select-none group max-w-full';
        badge.setAttribute('draggable', 'true');
        badge.setAttribute('data-product-id', id);
        badge.setAttribute('ondragstart', `handleDragStart(event, ${secId})`);
        badge.setAttribute('ondragover', 'handleDragOver(event)');
        badge.setAttribute('ondrop', `handleDrop(event, ${secId})`);

        badge.innerHTML = `
        <i data-lucide="grip-vertical" class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-700 shrink-0"></i>
        <img src="${mainImage}" class="w-8 h-8 rounded-lg object-cover bg-slate-100 shrink-0 border border-slate-100">
        <div class="text-left max-w-[180px] min-w-0">
            <p class="text-[11px] font-semibold text-slate-900 truncate leading-tight" title="${escapeHtml(name)}">${escapeHtml(name)}</p>
            <p class="text-[10px] text-slate-400 font-mono truncate">${price}</p>
        </div>
        <button type="button" onclick="removeProductFromSection(${secId}, ${id})" class="w-6 h-6 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition flex items-center justify-center shrink-0 ml-1">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
    `;

        list.appendChild(badge);
        if (window.lucide) lucide.createIcons();

        syncProductIdsInput(secId);

        const resultsBox = document.getElementById('results-' + secId);
        if (resultsBox && !resultsBox.classList.contains('hidden')) {
            const input = resultsBox.previousElementSibling.querySelector('input');
            if (input) searchProductsSection(secId, input.value);
        }
    }

    function removeProductFromSection(secId, id) {
        const list = document.getElementById('selected-list-' + secId);
        const badge = list.querySelector(`.product-badge[data-product-id="${id}"]`);
        if (badge) {
            badge.remove();
            syncProductIdsInput(secId);
        }
    }

    function handleDragStart(e, secId) {
        draggedElement = e.currentTarget;
        dragSourceSection = secId;
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', e.currentTarget.getAttribute('data-product-id'));
        e.currentTarget.classList.add('opacity-40');
    }

    function handleDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }

    function handleDrop(e, secId) {
        e.preventDefault();
        if (draggedElement) {
            draggedElement.classList.remove('opacity-40');
        }

        if (dragSourceSection !== secId) return;

        const targetBadge = e.target.closest('.product-badge');
        if (targetBadge && targetBadge !== draggedElement) {
            const list = document.getElementById('selected-list-' + secId);
            const children = Array.from(list.children);
            const draggedIdx = children.indexOf(draggedElement);
            const targetIdx = children.indexOf(targetBadge);

            if (draggedIdx < targetIdx) {
                list.insertBefore(draggedElement, targetBadge.nextSibling);
            } else {
                list.insertBefore(draggedElement, targetBadge);
            }

            syncProductIdsInput(secId);
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