<?php
include __DIR__ . '/../layouts/header.php';
$displaySections = $displaySections ?? [];
$allProducts = $allProducts ?? [];
?>

<div class="p-6 max-w-7xl mx-auto space-y-6 font-sans">

    <!-- Flash Alert Notifications -->
    <?php if (isset($_SESSION['flash_success'])): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-800 text-xs font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center space-x-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-800"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-amber-50 text-amber-700 rounded-lg tracking-wider border border-amber-200">
                    Jumia-Style Rows
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Multiple Sections Support</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Top Deals Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Create and manage unlimited "Top Deals" style sections directly below the hero banner. Add unlimited products per section, drag-and-drop to reorder, and configure custom "See All" target links.
            </p>
        </div>

        <div class="flex items-center space-x-3">
            <button type="button" onclick="openAddSectionModal()"
                class="px-4 py-2 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white text-xs font-semibold rounded-xl transition flex items-center space-x-2 shadow-sm cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>+ Add New Deals Section</span>
            </button>
            <a href="<?= url('') ?>" target="_blank"
                class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-xl transition flex items-center space-x-2 shadow-xs">
                <i data-lucide="external-link" class="w-4 h-4 text-slate-500"></i>
                <span>Preview Storefront</span>
            </a>
        </div>
    </div>

    <!-- EMPTY STATE IF NO SECTIONS -->
    <?php if (empty($displaySections)): ?>
        <div class="bg-white p-12 rounded-2xl border border-slate-200 text-center space-y-4">
            <div class="w-12 h-12 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto">
                <i data-lucide="zap" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-base font-semibold text-slate-900">No Deals Sections Found</h3>
                <p class="text-xs text-slate-500 mt-1">Click the button below to create your first Jumia-style Top Deals row section.</p>
            </div>
            <button type="button" onclick="openAddSectionModal()"
                class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition inline-flex items-center space-x-2 cursor-pointer shadow-xs">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>+ Add New Deals Section</span>
            </button>
        </div>
    <?php else: ?>

        <!-- LOOP OVER EACH DEALS ROW SECTION -->
        <div class="space-y-6">
            <?php foreach ($displaySections as $index => $sec): ?>
                <?php
                    $secId = (int)$sec['id'];
                    $isEnabled = ($sec['status'] === 'active' || $sec['status'] === 'enabled');
                    $selectedProducts = $sec['products'] ?? [];
                    $secSlug = $sec['slug'] ?: 'top-deals';
                ?>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden" id="section-card-<?= $secId ?>">
                    
                    <!-- Section Card Top Header Bar -->
                    <div class="px-6 py-4 bg-gradient-to-r from-slate-900 via-slate-800 to-black text-white flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-500 flex items-center justify-center shrink-0 shadow-xs">
                                <i data-lucide="zap" class="w-5 h-5 text-slate-950 fill-slate-950"></i>
                            </div>
                            <div>
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-base font-semibold text-white" id="card-title-header-<?= $secId ?>">
                                        <?= htmlspecialchars($sec['title']) ?>
                                    </h3>
                                    <span class="px-2 py-0.5 text-[10px] font-mono bg-slate-800 text-amber-300 rounded border border-slate-700">
                                        /section/<?= htmlspecialchars($secSlug) ?>
                                    </span>
                                </div>
                                <p class="text-xs text-slate-300">Section #<?= $secId ?> • Display Order: <?= (int)$sec['sort_order'] ?></p>
                            </div>
                        </div>

                        <!-- Header Controls (Status & Delete) -->
                        <div class="flex items-center space-x-4">
                            <div class="flex items-center space-x-2">
                                <span class="text-xs font-semibold text-slate-300">Status:</span>
                                <label class="relative inline-flex items-center cursor-pointer select-none">
                                    <input type="checkbox" form="form-sec-<?= $secId ?>" name="enabled" value="1" <?= $isEnabled ? 'checked' : '' ?> class="sr-only peer"
                                        onchange="document.getElementById('status-input-<?= $secId ?>').value = this.checked ? 'active' : 'inactive'; document.getElementById('status-badge-<?= $secId ?>').textContent = this.checked ? 'ENABLED (ON)' : 'DISABLED (OFF)'; document.getElementById('status-badge-<?= $secId ?>').className = this.checked ? 'px-2.5 py-1 text-[11px] font-semibold rounded-lg uppercase tracking-wider bg-amber-400 text-slate-950 shadow-xs' : 'px-2.5 py-1 text-[11px] font-semibold rounded-lg uppercase tracking-wider bg-slate-700 text-slate-300';">
                                    <div class="w-11 h-6 bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500">
                                    </div>
                                </label>
                                <span id="status-badge-<?= $secId ?>"
                                    class="px-2.5 py-1 text-[11px] font-semibold rounded-lg uppercase tracking-wider <?= $isEnabled ? 'bg-amber-400 text-slate-950 shadow-xs' : 'bg-slate-700 text-slate-300' ?>">
                                    <?= $isEnabled ? 'ENABLED (ON)' : 'DISABLED (OFF)' ?>
                                </span>
                            </div>

                            <!-- Delete Form Button -->
                            <form action="<?= url('admin/top-deals/delete/' . $secId) ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete section &quot;<?= htmlspecialchars($sec['title']) ?>&quot;?');">
                                <?= csrf_field() ?>
                                <button type="submit" class="p-1.5 rounded-lg bg-slate-800 hover:bg-red-600 text-slate-400 hover:text-white transition cursor-pointer" title="Delete Deals Section">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Update Form for Section -->
                    <form action="<?= url('admin/top-deals/update/' . $secId) ?>" method="POST" id="form-sec-<?= $secId ?>" class="p-6 space-y-6">
                        <?= csrf_field() ?>
                        <input type="hidden" name="section_id" value="<?= $secId ?>">
                        <input type="hidden" name="status" id="status-input-<?= $secId ?>" value="<?= $isEnabled ? 'active' : 'inactive' ?>">

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                            <!-- Section Title -->
                            <div class="md:col-span-4 space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                    Section Title <span class="text-red-600">*</span>
                                </label>
                                <input type="text" name="title" value="<?= htmlspecialchars($sec['title']) ?>" required
                                    placeholder="e.g. Top Deals"
                                    oninput="document.getElementById('card-title-header-<?= $secId ?>').textContent = this.value || 'Untitled Section';"
                                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-bold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                            </div>

                            <!-- Subtitle -->
                            <div class="md:col-span-4 space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                    Subtitle / Subtext <span class="text-slate-400 font-normal lowercase">(optional)</span>
                                </label>
                                <input type="text" name="subtitle" value="<?= htmlspecialchars($sec['subtitle'] ?? '') ?>"
                                    placeholder="e.g. Limited time offers"
                                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-red-600 transition">
                            </div>

                            <!-- Slug -->
                            <div class="md:col-span-2 space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">URL Slug</label>
                                <input type="text" name="slug" value="<?= htmlspecialchars($secSlug) ?>" required
                                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                            </div>

                            <!-- Display Order -->
                            <div class="md:col-span-2 space-y-1.5">
                                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Display Order</label>
                                <input type="number" name="sort_order" value="<?= (int)($sec['sort_order'] ?? ($index + 1)) ?>" min="0"
                                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                            </div>
                        </div>

                        <!-- Custom See All Link -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                                Custom "See All" Link <span class="text-slate-400 font-normal lowercase">(optional - overrides /section/<?= htmlspecialchars($secSlug) ?>)</span>
                            </label>
                            <input type="text" name="custom_url" value="<?= htmlspecialchars($sec['custom_url'] ?? '') ?>"
                                placeholder="e.g. /shop?sort=discount or /catalog"
                                class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                        </div>

                        <!-- Hidden Product IDs Field for Form Submission -->
                        <input type="hidden" name="product_ids" id="product-ids-<?= $secId ?>"
                            value="<?= implode(',', array_column($selectedProducts, 'id')) ?>">

                        <!-- Product Selector Box for this Section -->
                        <div class="space-y-3 pt-2 border-t border-slate-100 relative">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold text-slate-800 uppercase tracking-wider">
                                    Selected Section Products (<span id="count-<?= $secId ?>"><?= count($selectedProducts) ?></span>)
                                </label>
                                <span class="text-[11px] text-slate-500 font-medium">Drag items to reorder. Renders horizontally as Jumia-style cards.</span>
                            </div>

                            <!-- Product Search Input with Live Dropdown -->
                            <div class="relative z-30">
                                <div class="relative">
                                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3 text-slate-400"></i>
                                    <input type="text" placeholder="Search product by name or SKU to add..."
                                        oninput="debounceSearchSection(<?= $secId ?>, this.value)"
                                        onfocus="searchProductsForSection(<?= $secId ?>, this.value)"
                                        class="w-full h-10 pl-10 pr-4 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition shadow-2xs">
                                </div>
                                <!-- Floating Search Results Dropdown -->
                                <div id="search-results-<?= $secId ?>"
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
                                            ondragstart="handleDragStartSec(event, <?= $secId ?>)"
                                            ondragend="handleDragEndSec(event)"
                                            ondragover="handleDragOverSec(event)"
                                            ondragleave="handleDragLeaveSec(event)"
                                            ondrop="handleDropSec(event, <?= $secId ?>)">
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

                        <!-- Footer Action Bar for Section -->
                        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-500 font-medium">Changes appear immediately on storefront under hero banner.</span>
                            <button type="submit"
                                class="h-10 px-6 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold text-xs rounded-xl transition shadow-xs flex items-center space-x-2 cursor-pointer">
                                <i data-lucide="save" class="w-4 h-4"></i>
                                <span>Save <?= htmlspecialchars($sec['title']) ?></span>
                            </button>
                        </div>

                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<!-- CREATE NEW DEALS SECTION MODAL -->
<div id="add-section-modal" class="hidden fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl border border-slate-200 shadow-2xl max-w-lg w-full overflow-hidden font-sans">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i data-lucide="plus-circle" class="w-5 h-5 text-amber-400"></i>
                <h3 class="text-sm font-semibold text-white">Create New Deals Section</h3>
            </div>
            <button type="button" onclick="closeAddSectionModal()" class="text-slate-400 hover:text-white transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="<?= url('admin/top-deals/store') ?>" method="POST" class="p-6 space-y-4">
            <?= csrf_field() ?>
            
            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                    Section Title <span class="text-red-600">*</span>
                </label>
                <input type="text" name="title" id="new-sec-title" required placeholder="e.g. Flash Deals, Super Savings, Weekly Deals"
                    oninput="autoGenerateSlug(this.value)"
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                    URL Slug <span class="text-red-600">*</span>
                </label>
                <input type="text" name="slug" id="new-sec-slug" required placeholder="e.g. flash-deals"
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                    Subtitle / Subtext <span class="text-slate-400 font-normal lowercase">(optional)</span>
                </label>
                <input type="text" name="subtitle" placeholder="e.g. Limited time offer — grab them fast"
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:outline-none focus:border-red-600 transition">
            </div>

            <div class="space-y-1.5">
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">
                    Custom "See All" Link <span class="text-slate-400 font-normal lowercase">(optional)</span>
                </label>
                <input type="text" name="custom_url" placeholder="e.g. /shop?sort=discount"
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Display Order</label>
                    <input type="number" name="sort_order" value="<?= count($displaySections) + 1 ?>" min="0"
                        class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                </div>

                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider">Initial Status</label>
                    <select name="status" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-red-600 transition">
                        <option value="active">Active (Enabled)</option>
                        <option value="inactive">Inactive (Disabled)</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 border-t border-slate-100 flex items-center justify-end space-x-3">
                <button type="button" onclick="closeAddSectionModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Cancel
                </button>
                <button type="submit" class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition shadow-xs flex items-center space-x-1.5 cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Create Section</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const allProductsTD = <?= json_encode($allProducts ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const sectionSearchTimers = {};

function openAddSectionModal() {
    document.getElementById('add-section-modal').classList.remove('hidden');
    document.getElementById('new-sec-title').focus();
}

function closeAddSectionModal() {
    document.getElementById('add-section-modal').classList.add('hidden');
}

function autoGenerateSlug(title) {
    const slugInput = document.getElementById('new-sec-slug');
    if (slugInput && !slugInput.dataset.manuallyEdited) {
        slugInput.value = (title || '').toLowerCase().trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const slugInput = document.getElementById('new-sec-slug');
    if (slugInput) {
        slugInput.addEventListener('input', () => {
            slugInput.dataset.manuallyEdited = 'true';
        });
    }
});

function debounceSearchSection(secId, query) {
    clearTimeout(sectionSearchTimers[secId]);
    sectionSearchTimers[secId] = setTimeout(() => searchProductsForSection(secId, query), 200);
}

function searchProductsForSection(secId, query) {
    const resultsContainer = document.getElementById(`search-results-${secId}`);
    if (!resultsContainer) return;
    query = (query || '').trim().toLowerCase();

    if (!query) {
        resultsContainer.classList.add('hidden');
        resultsContainer.innerHTML = '';
        return;
    }

    const currentIds = getCurrentProductIdsForSection(secId);
    const filtered = allProductsTD.filter(p => {
        const nameMatch = (p.name || '').toLowerCase().includes(query);
        const skuMatch = (p.sku || '').toLowerCase().includes(query);
        return (nameMatch || skuMatch) && !currentIds.includes(parseInt(p.id));
    }).slice(0, 15);

    if (filtered.length === 0) {
        resultsContainer.innerHTML = '<div class="p-4 text-xs text-slate-400 italic text-center">No matching unselected products found</div>';
        resultsContainer.classList.remove('hidden');
        return;
    }

    let html = '';
    filtered.forEach(p => {
        const priceDisplay = (p.sale_price && parseFloat(p.sale_price) > 0) ? p.sale_price : p.price;
        const mainImg = p.main_image || 'assets/images/placeholder.jpg';
        const imgUrl = (mainImg.startsWith('http://') || mainImg.startsWith('https://')) ? mainImg : '<?= asset('') ?>' + mainImg;

        html += `
            <div class="p-2.5 hover:bg-slate-50 flex items-center justify-between gap-3 cursor-pointer transition"
                 onclick="addProductToSection(${secId}, ${p.id}, '${escapeHtmlTD(p.name)}', '${imgUrl}', '${priceDisplay}')">
                <div class="flex items-center space-x-3 min-w-0">
                    <img src="${imgUrl}" class="w-9 h-9 rounded-lg object-cover bg-slate-100 shrink-0 border border-slate-200">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-slate-900 truncate">${escapeHtmlTD(p.name)}</p>
                        <p class="text-[10px] text-slate-500 font-mono">SKU: ${escapeHtmlTD(p.sku || 'N/A')} • Price: ₹${priceDisplay}</p>
                    </div>
                </div>
                <button type="button" class="px-2.5 py-1 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white rounded-lg text-[11px] font-semibold transition shrink-0">
                    + Add
                </button>
            </div>
        `;
    });

    resultsContainer.innerHTML = html;
    resultsContainer.classList.remove('hidden');
}

function getCurrentProductIdsForSection(secId) {
    const list = document.getElementById(`selected-list-${secId}`);
    if (!list) return [];
    const badges = list.querySelectorAll('.product-badge');
    return Array.from(badges).map(b => parseInt(b.getAttribute('data-product-id'))).filter(id => !isNaN(id));
}

function addProductToSection(secId, id, name, imgUrl, price) {
    const list = document.getElementById(`selected-list-${secId}`);
    if (!list) return;

    const placeholder = list.querySelector('.empty-placeholder');
    if (placeholder) placeholder.remove();

    const badge = document.createElement('div');
    badge.className = 'product-badge bg-white border border-slate-200 rounded-xl p-2 flex items-center space-x-2.5 shadow-2xs hover:border-slate-400 cursor-move transition select-none group max-w-full';
    badge.setAttribute('draggable', 'true');
    badge.setAttribute('data-product-id', id);
    badge.setAttribute('ondragstart', `handleDragStartSec(event, ${secId})`);
    badge.setAttribute('ondragend', `handleDragEndSec(event)`);
    badge.setAttribute('ondragover', `handleDragOverSec(event)`);
    badge.setAttribute('ondragleave', `handleDragLeaveSec(event)`);
    badge.setAttribute('ondrop', `handleDropSec(event, ${secId})`);

    badge.innerHTML = `
        <i data-lucide="grip-vertical" class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-700 shrink-0"></i>
        <img src="${imgUrl}" class="w-8 h-8 rounded-lg object-cover bg-slate-100 shrink-0 border border-slate-100">
        <div class="text-left max-w-[180px] min-w-0">
            <p class="text-[11px] font-semibold text-slate-900 truncate leading-tight" title="${escapeHtmlTD(name)}">${escapeHtmlTD(name)}</p>
            <p class="text-[10px] text-slate-500 font-mono truncate">₹${price}</p>
        </div>
        <button type="button" onclick="removeProductFromSection(${secId}, ${id})"
            class="w-6 h-6 rounded-lg hover:bg-red-50 text-slate-400 hover:text-red-600 transition flex items-center justify-center shrink-0 ml-1">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
    `;

    list.appendChild(badge);
    if (window.lucide) lucide.createIcons();

    updateSectionProductIdsInput(secId);
    const resultsContainer = document.getElementById(`search-results-${secId}`);
    if (resultsContainer) resultsContainer.classList.add('hidden');
}

function removeProductFromSection(secId, id) {
    const list = document.getElementById(`selected-list-${secId}`);
    if (!list) return;

    const badge = list.querySelector(`[data-product-id="${id}"]`);
    if (badge) badge.remove();

    if (list.querySelectorAll('.product-badge').length === 0) {
        list.innerHTML = '<p class="text-xs text-slate-400 italic py-2 px-3 empty-placeholder">No products added yet. Use search above to select products for this section.</p>';
    }

    updateSectionProductIdsInput(secId);
}

function updateSectionProductIdsInput(secId) {
    const ids = getCurrentProductIdsForSection(secId);
    const hiddenInput = document.getElementById(`product-ids-${secId}`);
    const countBadge = document.getElementById(`count-${secId}`);
    if (hiddenInput) hiddenInput.value = ids.join(',');
    if (countBadge) countBadge.textContent = ids.length;
}

let draggedBadgeSec = null;

function handleDragStartSec(e, secId) {
    const badge = e.target.closest('.product-badge');
    if (!badge) return;
    draggedBadgeSec = badge;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', badge.getAttribute('data-product-id'));
    badge.classList.add('opacity-40', 'scale-95');
}

function handleDragEndSec(e) {
    const badge = e.target.closest('.product-badge');
    if (badge) {
        badge.classList.remove('opacity-40', 'scale-95');
    }
    document.querySelectorAll('.product-badge').forEach(b => b.classList.remove('border-red-500', 'bg-red-50/50'));
    draggedBadgeSec = null;
}

function handleDragOverSec(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    const targetBadge = e.target.closest('.product-badge');
    if (targetBadge && targetBadge !== draggedBadgeSec) {
        targetBadge.classList.add('border-red-500', 'bg-red-50/50');
    }
}

function handleDragLeaveSec(e) {
    const targetBadge = e.target.closest('.product-badge');
    if (targetBadge) {
        targetBadge.classList.remove('border-red-500', 'bg-red-50/50');
    }
}

function handleDropSec(e, secId) {
    e.preventDefault();
    const targetBadge = e.target.closest('.product-badge');
    if (targetBadge) {
        targetBadge.classList.remove('border-red-500', 'bg-red-50/50');
    }
    if (draggedBadgeSec && targetBadge && draggedBadgeSec !== targetBadge) {
        const list = document.getElementById(`selected-list-${secId}`);
        if (!list) return;
        const badges = Array.from(list.querySelectorAll('.product-badge'));
        const draggedIndex = badges.indexOf(draggedBadgeSec);
        const targetIndex = badges.indexOf(targetBadge);

        if (draggedIndex < targetIndex) {
            targetBadge.after(draggedBadgeSec);
        } else {
            targetBadge.before(draggedBadgeSec);
        }
        updateSectionProductIdsInput(secId);
    }
}

function escapeHtmlTD(str) {
    return (str || '').replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
}

document.addEventListener('click', function(e) {
    document.querySelectorAll('[id^="search-results-"]').forEach(container => {
        if (container && !container.contains(e.target) && !e.target.closest('input[placeholder*="Search product"]')) {
            container.classList.add('hidden');
        }
    });
});
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
