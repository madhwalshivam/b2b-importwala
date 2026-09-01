<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="space-y-6 font-sans">

    <!-- Top Header Card -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-red-50 text-red-600 rounded-lg tracking-wider border border-red-100">
                    Storefront Control
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Header Navigation Menu</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Manage Top Navigation Menu</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Control top header menu links (Home, Categories, New Arrivals, Support, etc.), dropdown submenus, URLs, position order, and hide/show status dynamically.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="openAddModal()" 
                class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-2 cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Add New Nav Link</span>
            </button>
        </div>
    </div>

    <!-- Navigation Links Tree Card Container -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6">
        <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100">
            <div class="flex items-center space-x-2">
                <i data-lucide="compass" class="w-4 h-4 text-red-600"></i>
                <h2 class="text-sm font-semibold text-slate-900">
                    Active Menu Links Hierarchy (<?= count($navTree) ?> Top-Level Links)
                </h2>
            </div>
            <span class="text-xs text-slate-400 font-medium">Reorder using Up/Down arrows or 1-click status toggle</span>
        </div>

        <?php if (empty($navTree)): ?>
            <div class="py-12 text-center text-slate-400">
                <i data-lucide="navigation-off" class="w-10 h-10 mx-auto mb-2 text-slate-300"></i>
                <p class="text-xs font-medium">No navigation links found. Click "Add New Nav Link" to create one.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3" id="navTreeContainer">
                <?php foreach ($navTree as $index => $parent): ?>
                    <!-- Parent Nav Item Card -->
                    <div class="bg-white hover:bg-slate-50/60 border border-slate-200 rounded-xl p-4 transition shadow-2xs">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            
                            <!-- Left Details -->
                            <div class="flex items-center space-x-3 flex-1 min-w-0">
                                <span class="flex-shrink-0 w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 text-slate-700 text-xs font-bold flex items-center justify-center">
                                    #<?= $parent['sort_order'] ?>
                                </span>
                                
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center space-x-2 flex-wrap gap-y-1">
                                        <span class="text-sm font-bold text-slate-900 tracking-wide truncate">
                                            <?= htmlspecialchars($parent['label']) ?>
                                        </span>

                                        <!-- Type Badge -->
                                        <span class="px-2 py-0.5 text-[10px] font-semibold uppercase rounded-md tracking-wider border <?= $parent['type'] === 'dropdown' ? 'bg-purple-50 text-purple-700 border-purple-200' : ($parent['type'] === 'category' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-700 border-slate-200') ?>">
                                            <?= htmlspecialchars($parent['type']) ?>
                                        </span>

                                        <!-- Open New Tab Badge -->
                                        <?php if (!empty($parent['open_in_new_tab'])): ?>
                                            <span class="px-1.5 py-0.5 text-[9px] font-medium bg-amber-50 text-amber-700 border border-amber-200 rounded" title="Opens in new tab">
                                                ↗ New Tab
                                            </span>
                                        <?php endif; ?>

                                        <!-- Active Status Badge -->
                                        <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full border <?= !empty($parent['is_active']) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' ?>">
                                            <?= !empty($parent['is_active']) ? '● Active' : '○ Hidden' ?>
                                        </span>
                                    </div>

                                    <p class="text-xs text-slate-500 font-mono mt-0.5 truncate">
                                        <?= htmlspecialchars($parent['url']) ?>
                                    </p>
                                </div>
                            </div>

                            <!-- Right Action Buttons -->
                            <div class="flex items-center space-x-1.5 shrink-0 self-end sm:self-center">
                                <!-- Add Submenu Link Button -->
                                <button type="button" onclick="openAddSubmenuModal(<?= $parent['id'] ?>, '<?= htmlspecialchars(addslashes($parent['label'])) ?>')"
                                    class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-purple-700 text-xs font-semibold rounded-lg border border-slate-200 transition flex items-center gap-1 cursor-pointer"
                                    title="Add Child Submenu Link under this item">
                                    <i data-lucide="folder-plus" class="w-3.5 h-3.5"></i>
                                    <span class="hidden md:inline">+ Submenu</span>
                                </button>

                                <!-- Position Up/Down Buttons -->
                                <a href="<?= url("admin/navigation/move/{$parent['id']}/up") ?>" 
                                    class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition" title="Move Up">
                                    <i data-lucide="arrow-up" class="w-4 h-4"></i>
                                </a>
                                <a href="<?= url("admin/navigation/move/{$parent['id']}/down") ?>" 
                                    class="p-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition" title="Move Down">
                                    <i data-lucide="arrow-down" class="w-4 h-4"></i>
                                </a>

                                <!-- 1-Click Toggle Active -->
                                <a href="<?= url("admin/navigation/toggle-status/{$parent['id']}") ?>" 
                                    class="px-2 py-1.5 text-xs font-semibold rounded-lg border transition <?= !empty($parent['is_active']) ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' ?>"
                                    title="<?= !empty($parent['is_active']) ? 'Hide from website' : 'Show on website' ?>">
                                    <?= !empty($parent['is_active']) ? 'Hide' : 'Show' ?>
                                </a>

                                <!-- Edit Link Button -->
                                <button type="button" onclick='openEditModal(<?= json_encode($parent, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                                    class="p-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 rounded-lg border border-blue-200 transition cursor-pointer" title="Edit Link">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </button>

                                <!-- Delete Link Button -->
                                <form action="<?= url("admin/navigation/delete/{$parent['id']}") ?>" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete \'<?= htmlspecialchars(addslashes($parent['label'])) ?>\'? Any dropdown sub-items will also be deleted.')">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="p-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg border border-rose-200 transition cursor-pointer" title="Delete Link">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Nested Sub-Items List (Child Links) -->
                        <?php if (!empty($parent['children'])): ?>
                            <div class="mt-3 ml-4 sm:ml-8 pl-4 border-l-2 border-slate-200 space-y-2 pt-2">
                                <div class="text-[11px] font-semibold uppercase text-slate-400 tracking-wider mb-2 flex items-center gap-1.5">
                                    <i data-lucide="corner-down-right" class="w-3.5 h-3.5 text-purple-600"></i>
                                    Submenu Items under "<?= htmlspecialchars($parent['label']) ?>"
                                </div>
                                
                                <?php foreach ($parent['children'] as $child): ?>
                                    <div class="bg-slate-50 hover:bg-slate-100/70 border border-slate-200/80 rounded-lg p-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 transition">
                                        
                                        <div class="flex items-center space-x-3 min-w-0">
                                            <span class="text-slate-400 text-xs font-mono font-bold">
                                                ↳ #<?= $child['sort_order'] ?>
                                            </span>
                                            <div class="min-w-0">
                                                <div class="flex items-center space-x-2 flex-wrap">
                                                    <span class="text-xs font-bold text-slate-800">
                                                        <?= htmlspecialchars($child['label']) ?>
                                                    </span>
                                                    <span class="px-1.5 py-0.2 text-[9px] font-mono rounded border <?= $child['type'] === 'category' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-slate-100 text-slate-600 border-slate-200' ?>">
                                                        <?= htmlspecialchars($child['type']) ?>
                                                    </span>
                                                    <span class="px-1.5 py-0.2 text-[9px] font-semibold rounded <?= !empty($child['is_active']) ? 'text-emerald-700' : 'text-rose-600' ?>">
                                                        <?= !empty($child['is_active']) ? '● Active' : '○ Hidden' ?>
                                                    </span>
                                                </div>
                                                <p class="text-[11px] text-slate-500 font-mono truncate">
                                                    <?= htmlspecialchars($child['url']) ?>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-1 shrink-0 self-end sm:self-center">
                                            <a href="<?= url("admin/navigation/move/{$child['id']}/up") ?>" class="p-1 text-slate-500 hover:text-slate-800" title="Move Up"><i data-lucide="chevron-up" class="w-3.5 h-3.5"></i></a>
                                            <a href="<?= url("admin/navigation/move/{$child['id']}/down") ?>" class="p-1 text-slate-500 hover:text-slate-800" title="Move Down"><i data-lucide="chevron-down" class="w-3.5 h-3.5"></i></a>
                                            <a href="<?= url("admin/navigation/toggle-status/{$child['id']}") ?>" class="px-2 py-0.5 text-[11px] font-semibold rounded border <?= !empty($child['is_active']) ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' ?>">
                                                <?= !empty($child['is_active']) ? 'Hide' : 'Show' ?>
                                            </a>
                                            <button type="button" onclick='openEditModal(<?= json_encode($child, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)' class="p-1 text-blue-600 hover:text-blue-800" title="Edit">
                                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                            </button>
                                            <form action="<?= url("admin/navigation/delete/{$child['id']}") ?>" method="POST" class="inline" onsubmit="return confirm('Delete submenu item?')">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="p-1 text-rose-600 hover:text-rose-800" title="Delete">
                                                    <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                                </button>
                                            </form>
                                        </div>

                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- ========================================================================= -->
<!-- ADD NEW NAV LINK MODAL -->
<!-- ========================================================================= -->
<div id="addNavModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-xl shadow-2xl overflow-hidden animate-scale-in">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4 text-red-600"></i>
                Add New Navigation Link
            </h3>
            <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-700 transition">✕</button>
        </div>

        <form action="<?= url('admin/navigation/store') ?>" method="POST" class="p-6 space-y-4">
            <?= csrf_field() ?>

            <!-- Quick Preset Selector -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Quick Preset Loader (Optional)
                </label>
                <select id="addPresetSelect" onchange="applyPreset('add', this.value)"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:outline-none focus:border-red-500">
                    <option value="">-- Choose Preset to Auto-Fill --</option>
                    <option value="home" data-label="Home" data-url="/" data-type="internal">Home (/)</option>
                    <option value="catalog" data-label="All Categories" data-url="/catalog" data-type="dropdown">Categories Catalog (/catalog)</option>
                    <option value="new_arrivals" data-label="New Arrivals" data-url="/catalog?sort=newest" data-type="internal">New Arrivals (/catalog?sort=newest)</option>
                    <option value="best_sellers" data-label="Best Sellers" data-url="/catalog?sort=popular" data-type="internal">Best Sellers (/catalog?sort=popular)</option>
                    <option value="free_shipping" data-label="Free Air Shipping" data-url="/catalog?free_shipping=1" data-type="internal">Free Air Shipping (/catalog?free_shipping=1)</option>
                    <option value="price_drops" data-label="Price Drops" data-url="/catalog?price_drops=1" data-type="internal">Price Drops (/catalog?price_drops=1)</option>
                    <option value="blog" data-label="Blog" data-url="/blog" data-type="internal">Blog (/blog)</option>
                    <option value="support" data-label="Support" data-url="/support" data-type="dropdown">Support (/support)</option>
                    <?php if (!empty($categories)): ?>
                        <optgroup label="Product Categories">
                            <?php foreach ($categories as $cat): ?>
                                <option value="cat_<?= $cat['id'] ?>" data-label="<?= htmlspecialchars($cat['name']) ?>" data-url="/catalog?category=<?= urlencode($cat['slug']) ?>" data-type="category">
                                    Category: <?= htmlspecialchars($cat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Label Text -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Link Text / Label <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="label" id="addLabel" required placeholder="e.g. Clearance Sale"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-red-500">
            </div>

            <!-- URL Path -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Link URL / Path <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="url" id="addUrl" required placeholder="e.g. /catalog?clearance=1 or https://example.com"
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-red-500">
            </div>

            <!-- Parent Link (Submenu) & Link Type -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Parent Link (For Dropdown Submenu)
                    </label>
                    <select name="parent_id" id="addParentId"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-red-500">
                        <option value="">-- None (Top Level Menu Item) --</option>
                        <?php foreach ($flatLinks as $fl): ?>
                            <?php if (empty($fl['parent_id'])): ?>
                                <option value="<?= $fl['id'] ?>">📁 <?= htmlspecialchars($fl['label']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Link Type
                    </label>
                    <select name="type" id="addType"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-red-500">
                        <option value="internal">Internal Page</option>
                        <option value="dropdown">Dropdown Parent</option>
                        <option value="category">Category Link</option>
                        <option value="custom">Custom External URL</option>
                    </select>
                </div>
            </div>

            <!-- Position Control & Options -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Sort Order
                    </label>
                    <input type="number" name="sort_order" id="addSortOrder" placeholder="Auto" min="1"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-red-500">
                </div>

                <div class="flex items-center pt-5">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600 relative"></div>
                        <span class="ml-2.5 text-xs font-semibold text-slate-700">Active</span>
                    </label>
                </div>

                <div class="flex items-center pt-5">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="open_in_new_tab" value="1" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-600 relative"></div>
                        <span class="ml-2.5 text-xs font-semibold text-slate-700">New Tab</span>
                    </label>
                </div>
            </div>

            <!-- Submit Controls -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeAddModal()"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    Save Navigation Link
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ========================================================================= -->
<!-- EDIT NAV LINK MODAL -->
<!-- ========================================================================= -->
<div id="editNavModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 flex items-center justify-center hidden p-4">
    <div class="bg-white border border-slate-200 rounded-2xl w-full max-w-xl shadow-2xl overflow-hidden animate-scale-in">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i data-lucide="edit-3" class="w-4 h-4 text-blue-600"></i>
                Edit Navigation Link
            </h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-700 transition">✕</button>
        </div>

        <form id="editForm" action="" method="POST" class="p-6 space-y-4">
            <?= csrf_field() ?>

            <!-- Label Text -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Link Text / Label <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="label" id="editLabel" required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-blue-500">
            </div>

            <!-- URL Path -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                    Link URL / Path <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="url" id="editUrl" required
                    class="w-full bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5 text-xs text-slate-900 font-mono focus:outline-none focus:border-blue-500">
            </div>

            <!-- Parent Link (Submenu) & Link Type -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Parent Link (For Dropdown Submenu)
                    </label>
                    <select name="parent_id" id="editParentId"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-blue-500">
                        <option value="">-- None (Top Level Menu Item) --</option>
                        <?php foreach ($flatLinks as $fl): ?>
                            <?php if (empty($fl['parent_id'])): ?>
                                <option value="<?= $fl['id'] ?>">📁 <?= htmlspecialchars($fl['label']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Link Type
                    </label>
                    <select name="type" id="editType"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-blue-500">
                        <option value="internal">Internal Page</option>
                        <option value="dropdown">Dropdown Parent</option>
                        <option value="category">Category Link</option>
                        <option value="custom">Custom External URL</option>
                    </select>
                </div>
            </div>

            <!-- Position Control & Options -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1.5">
                        Sort Order
                    </label>
                    <input type="number" name="sort_order" id="editSortOrder" required min="1"
                        class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 text-xs text-slate-900 focus:outline-none focus:border-blue-500">
                </div>

                <div class="flex items-center pt-5">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" id="editIsActive" value="1" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-emerald-600 relative"></div>
                        <span class="ml-2.5 text-xs font-semibold text-slate-700">Active</span>
                    </label>
                </div>

                <div class="flex items-center pt-5">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="open_in_new_tab" id="editOpenInNewTab" value="1" class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-600 relative"></div>
                        <span class="ml-2.5 text-xs font-semibold text-slate-700">New Tab</span>
                    </label>
                </div>
            </div>

            <!-- Submit Controls -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closeEditModal()"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-xs transition">
                    Update Navigation Link
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addNavModal').classList.remove('hidden');
}
function closeAddModal() {
    document.getElementById('addNavModal').classList.add('hidden');
}

function openAddSubmenuModal(parentId, parentLabel) {
    document.getElementById('addParentId').value = parentId;
    document.getElementById('addType').value = 'category';
    openAddModal();
}

function openEditModal(linkData) {
    document.getElementById('editForm').action = '<?= url('admin/navigation/update/') ?>' + linkData.id;
    document.getElementById('editLabel').value = linkData.label || '';
    document.getElementById('editUrl').value = linkData.url || '';
    document.getElementById('editParentId').value = linkData.parent_id || '';
    document.getElementById('editType').value = linkData.type || 'internal';
    document.getElementById('editSortOrder').value = linkData.sort_order || 1;
    document.getElementById('editIsActive').checked = parseInt(linkData.is_active) === 1;
    document.getElementById('editOpenInNewTab').checked = parseInt(linkData.open_in_new_tab) === 1;
    
    document.getElementById('editNavModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editNavModal').classList.add('hidden');
}

function applyPreset(modalType, value) {
    if (!value) return;
    const select = document.getElementById(modalType + 'PresetSelect');
    const option = select.options[select.selectedIndex];
    if (option) {
        const label = option.getAttribute('data-label');
        const url = option.getAttribute('data-url');
        const type = option.getAttribute('data-type');
        if (label) document.getElementById(modalType + 'Label').value = label;
        if (url) document.getElementById(modalType + 'Url').value = url;
        if (type) document.getElementById(modalType + 'Type').value = type;
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
