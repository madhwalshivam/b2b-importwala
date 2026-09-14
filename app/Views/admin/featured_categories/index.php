<?php include __DIR__ . '/../layouts/header.php'; ?>

<?php
$session = new \App\Core\Session();
$flashSuccess = $session->getFlash('success');
$flashError = $session->getFlash('error');
?>

<div class="space-y-6">

    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 tracking-tight flex items-center space-x-2">
                <i data-lucide="layers" class="w-6 h-6 text-red-600"></i>
                <span>Homepage Category Grids Manager</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Manage the two homepage sections displayed directly below Top Deals:
                <strong>Section 1</strong> (Main Category Orange/Blue Split Tiles) &amp; 
                <strong>Section 2</strong> (Subcategory Minimal Icon Grid).
            </p>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button onclick="openAddCategoryModal()"
                class="h-10 px-4 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Add Main Category Tile</span>
            </button>
            <button onclick="openAddSubcategoryModal()"
                class="h-10 px-4 bg-slate-900 hover:bg-slate-800 active:bg-slate-950 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i data-lucide="grid-plus" class="w-4 h-4"></i>
                <span>Add Subcategory Icon</span>
            </button>
        </div>
    </div>

    <!-- Flash Notifications -->
    <?php if (!empty($flashSuccess)): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium rounded-xl flex items-center gap-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
            <span><?= htmlspecialchars($flashSuccess) ?></span>
        </div>
    <?php endif; ?>
    <?php if (!empty($flashError)): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs font-medium rounded-xl flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 shrink-0"></i>
            <span><?= htmlspecialchars($flashError) ?></span>
        </div>
    <?php endif; ?>

    <!-- SECTION 1 TABLE CARD -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-red-600"></span>
                <h2 class="text-sm font-semibold text-slate-900">Section 1: Main Category Tiles (Orange / Blue Split)</h2>
                <span class="text-xs text-slate-400 font-normal">(<?= count($categories) ?> items)</span>
            </div>
            <button onclick="openAddCategoryModal()" class="h-9 px-3.5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center space-x-1.5 shrink-0 cursor-pointer">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Add Main Category Tile</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold text-[10px]">
                    <tr>
                        <th class="py-3 px-4 text-center w-16">Image</th>
                        <th class="py-3 px-4">Tile Label Name</th>
                        <th class="py-3 px-4">Target Link URL</th>
                        <th class="py-3 px-4 text-center">Sort Order</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right pr-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <i data-lucide="layout-grid" class="w-8 h-8 text-slate-300"></i>
                                    <span class="font-medium text-slate-600">No main category tiles added</span>
                                    <p class="text-xs text-slate-400">Click "Add Main Category Tile" to create your first tile.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <tr class="hover:bg-slate-50/80 transition group">
                                <td class="py-3 px-4 text-center">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200/80 overflow-hidden flex items-center justify-center mx-auto text-slate-400 shadow-2xs group-hover:border-red-200 transition">
                                        <?php if (!empty($cat['image'])): ?>
                                            <img src="<?= asset(ltrim($cat['image'], '/')) ?>" alt="" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/50?text=TILE';">
                                        <?php else: ?>
                                            <i data-lucide="image" class="w-5 h-5 text-slate-300"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-900"><?= htmlspecialchars($cat['name']) ?></div>
                                    <span class="font-mono text-[10px] text-slate-400">slug: <?= htmlspecialchars($cat['slug']) ?></span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-mono text-[11px] text-slate-600 bg-slate-100/80 px-2 py-0.5 rounded-md border border-slate-200/60">
                                        <?= htmlspecialchars($cat['link_url'] ?? '/category/' . $cat['slug']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-xs text-slate-600">
                                    <?= (int) ($cat['sort_order'] ?? 0) ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($cat['is_active']): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-right pr-6">
                                    <div class="flex items-center justify-end space-x-1">
                                        <button onclick='openEditCategoryModal(<?= json_encode($cat) ?>)'
                                            class="p-1.5 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition"
                                            title="Edit Tile">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <form action="<?= url('admin/featured-categories/delete-category/' . $cat['id']) ?>"
                                            method="POST" onsubmit="return confirm('Delete this category tile?');" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                                title="Delete Tile">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>


    <!-- SECTION 2 TABLE CARD -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-2">
                <span class="w-3 h-3 rounded-full bg-slate-800"></span>
                <h2 class="text-sm font-semibold text-slate-900">Section 2: Subcategory Icon Grid</h2>
                <span class="text-xs text-slate-400 font-normal">(<?= count($subcategories) ?> items)</span>
            </div>
            <button onclick="openAddSubcategoryModal()" class="h-9 px-3.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center space-x-1.5 shrink-0 cursor-pointer">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                <span>Add Subcategory Icon</span>
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold text-[10px]">
                    <tr>
                        <th class="py-3 px-4 text-center w-16">Image</th>
                        <th class="py-3 px-4">Subcategory Name</th>
                        <th class="py-3 px-4">Target Link URL</th>
                        <th class="py-3 px-4 text-center">Sort Order</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right pr-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($subcategories)): ?>
                        <tr>
                            <td colspan="6" class="p-8 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <i data-lucide="grid" class="w-8 h-8 text-slate-300"></i>
                                    <span class="font-medium text-slate-600">No subcategory icons added</span>
                                    <p class="text-xs text-slate-400">Click "Add Subcategory Icon" to create your first icon.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subcategories as $sub): ?>
                            <tr class="hover:bg-slate-50/80 transition group">
                                <td class="py-3 px-4 text-center">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200/80 overflow-hidden flex items-center justify-center mx-auto text-slate-400 shadow-2xs group-hover:border-red-200 transition">
                                        <?php if (!empty($sub['image'])): ?>
                                            <img src="<?= asset(ltrim($sub['image'], '/')) ?>" alt="" class="w-full h-full object-cover" onerror="this.src='https://via.placeholder.com/50?text=ICON';">
                                        <?php else: ?>
                                            <i data-lucide="image" class="w-5 h-5 text-slate-300"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-900"><?= htmlspecialchars($sub['name']) ?></div>
                                    <span class="font-mono text-[10px] text-slate-400">slug: <?= htmlspecialchars($sub['slug']) ?></span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="font-mono text-[11px] text-slate-600 bg-slate-100/80 px-2 py-0.5 rounded-md border border-slate-200/60">
                                        <?= htmlspecialchars($sub['link_url']) ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-mono text-xs text-slate-600">
                                    <?= (int) ($sub['sort_order'] ?? 0) ?>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <?php if ($sub['is_active']): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-right pr-6">
                                    <div class="flex items-center justify-end space-x-1">
                                        <button onclick='openEditSubcategoryModal(<?= json_encode($sub) ?>)'
                                            class="p-1.5 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition"
                                            title="Edit Icon">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <form action="<?= url('admin/featured-categories/delete-subcategory/' . $sub['id']) ?>"
                                            method="POST" onsubmit="return confirm('Delete this subcategory icon?');" class="inline">
                                            <?= csrf_field() ?>
                                            <button type="submit"
                                                class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                                title="Delete Icon">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal 1: Main Category Tile Form (Section 1) -->
<div id="catModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 id="catModalTitle" class="text-sm font-semibold text-slate-900">Add Main Category Tile</h3>
            <button onclick="closeCategoryModal()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form id="catForm" action="<?= url('admin/featured-categories/store-category') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Pick Existing Category or Subcategory</label>
                <select id="cat_source_select" onchange="onSelectSourceCategory(this)"
                    class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs bg-slate-50 text-slate-800 font-medium focus:outline-none focus:border-red-600 mb-3">
                    <option value="">-- Select Category or Subcategory --</option>
                    <optgroup label="Main Categories">
                        <?php foreach ($regCategories as $rc): ?>
                            <option value="<?= htmlspecialchars(json_encode(['name' => $rc['name'], 'link' => '/category/' . $rc['slug'], 'image' => $rc['image'] ?? ''])) ?>">
                                📁 [Category] <?= htmlspecialchars($rc['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                    <optgroup label="Subcategories">
                        <?php foreach ($regSubcategories as $rs): ?>
                            <option value="<?= htmlspecialchars(json_encode(['name' => $rs['name'], 'link' => '/category/' . ($rs['parent_slug'] ?? 'jewellery') . '/' . $rs['slug'], 'image' => $rs['image'] ?? ''])) ?>">
                                🏷️ [Subcategory] <?= htmlspecialchars($rs['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </optgroup>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tile Label Badge Text *</label>
                <input type="text" id="cat_name" name="name" required placeholder="e.g. Jewellery"
                    class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Target Link URL</label>
                <input type="text" id="cat_link_url" name="link_url" placeholder="/category/jewellery"
                    class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Category Image (Transparent PNG Recommended)</label>
                <input type="file" name="image_file" accept="image/*"
                    class="text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                <input type="text" id="cat_image_url" name="image_url" placeholder="Or image URL path"
                    class="w-full h-9 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none mt-2">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sort Order</label>
                    <input type="number" id="cat_sort_order" name="sort_order" value="0"
                        class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
                </div>
                <div class="flex items-center pt-5">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="cat_is_active" name="is_active" value="1" checked class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-red-600"></div>
                        <span class="ml-2 text-xs font-semibold text-slate-700">Active</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeCategoryModal()"
                    class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl">Cancel</button>
                <button type="submit"
                    class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl">Save Tile</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Subcategory Icon Form (Section 2) -->
<div id="subModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 id="subModalTitle" class="text-sm font-semibold text-slate-900">Add Subcategory Icon</h3>
            <button onclick="closeSubcategoryModal()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form id="subForm" action="<?= url('admin/featured-categories/store-subcategory') ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Subcategory Label Name *</label>
                <input type="text" id="sub_name" name="name" required placeholder="e.g. Ring"
                    class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Target Link URL</label>
                <select onchange="if(this.value) document.getElementById('sub_link_url').value = this.value;"
                    class="w-full h-9 px-3 border border-slate-200 rounded-xl text-xs bg-slate-50 text-slate-600 focus:outline-none mb-1.5">
                    <option value="">-- Select Subcategory Page Link --</option>
                    <?php foreach ($regCategories as $rc): ?>
                        <option value="/category/<?= htmlspecialchars($rc['slug']) ?>"><?= htmlspecialchars($rc['name']) ?> (/category/<?= htmlspecialchars($rc['slug']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <input type="text" id="sub_link_url" name="link_url" placeholder="/category/jewellery/ring"
                    class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Square Icon Image</label>
                <input type="file" name="image_file" accept="image/*"
                    class="text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                <input type="text" id="sub_image_url" name="image_url" placeholder="Or image URL path"
                    class="w-full h-9 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none mt-2">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sort Order</label>
                    <input type="number" id="sub_sort_order" name="sort_order" value="0"
                        class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
                </div>
                <div class="flex items-center pt-5">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="sub_is_active" name="is_active" value="1" checked class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-slate-900"></div>
                        <span class="ml-2 text-xs font-semibold text-slate-700">Active</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeSubcategoryModal()"
                    class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl">Cancel</button>
                <button type="submit"
                    class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl">Save Icon</button>
            </div>
        </form>
    </div>
</div>

<script>
    function onSelectSourceCategory(select) {
        if (!select.value) return;
        try {
            const data = JSON.parse(select.value);
            if (data.name) {
                document.getElementById('cat_name').value = data.name;
            }
            if (data.link) {
                document.getElementById('cat_link_url').value = data.link;
            }
            if (data.image) {
                document.getElementById('cat_image_url').value = data.image;
            }
        } catch (e) {
            console.error('Error parsing category selection data:', e);
        }
    }

    function openAddCategoryModal() {
        document.getElementById('catModalTitle').innerText = 'Add Main Category Tile';
        document.getElementById('catForm').action = "<?= url('admin/featured-categories/store-category') ?>";
        document.getElementById('cat_source_select').value = '';
        document.getElementById('cat_name').value = '';
        document.getElementById('cat_link_url').value = '';
        document.getElementById('cat_image_url').value = '';
        document.getElementById('cat_sort_order').value = '0';
        document.getElementById('cat_is_active').checked = true;
        document.getElementById('catModal').classList.remove('hidden');
    }

    function openEditCategoryModal(cat) {
        document.getElementById('catModalTitle').innerText = 'Edit Main Category Tile';
        document.getElementById('catForm').action = "<?= url('admin/featured-categories/update-category/') ?>" + cat.id;
        document.getElementById('cat_name').value = cat.name;
        document.getElementById('cat_link_url').value = cat.link_url || '';
        document.getElementById('cat_image_url').value = cat.image || '';
        document.getElementById('cat_sort_order').value = cat.sort_order;
        document.getElementById('cat_is_active').checked = parseInt(cat.is_active) === 1;
        document.getElementById('catModal').classList.remove('hidden');
    }

    function closeCategoryModal() {
        document.getElementById('catModal').classList.add('hidden');
    }

    function openAddSubcategoryModal() {
        document.getElementById('subModalTitle').innerText = 'Add Subcategory Icon';
        document.getElementById('subForm').action = "<?= url('admin/featured-categories/store-subcategory') ?>";
        document.getElementById('sub_name').value = '';
        document.getElementById('sub_link_url').value = '';
        document.getElementById('sub_image_url').value = '';
        document.getElementById('sub_sort_order').value = '0';
        document.getElementById('sub_is_active').checked = true;
        document.getElementById('subModal').classList.remove('hidden');
    }

    function openEditSubcategoryModal(sub) {
        document.getElementById('subModalTitle').innerText = 'Edit Subcategory Icon';
        document.getElementById('subForm').action = "<?= url('admin/featured-categories/update-subcategory/') ?>" + sub.id;
        document.getElementById('sub_name').value = sub.name;
        document.getElementById('sub_link_url').value = sub.link_url || '';
        document.getElementById('sub_image_url').value = sub.image || '';
        document.getElementById('sub_sort_order').value = sub.sort_order;
        document.getElementById('sub_is_active').checked = parseInt(sub.is_active) === 1;
        document.getElementById('subModal').classList.remove('hidden');
    }

    function closeSubcategoryModal() {
        document.getElementById('subModal').classList.add('hidden');
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>