<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="space-y-6">

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
                <span class="text-xs text-slate-500 font-medium">Home Page Featured Categories</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Featured Categories Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Manage the horizontal pill category tabs and subcategory cards displayed directly below the hero banner
                on the home page (EverfulWholesale UI).
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="openAddTabModal()"
                class="px-4 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-semibold hover:bg-slate-800 transition flex items-center gap-2 shadow-xs cursor-pointer">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Add Category Tab</span>
            </button>
            <button onclick="openAddSubmodal()"
                class="px-4 py-2.5 bg-red-600 text-white rounded-xl text-xs font-semibold hover:bg-red-700 transition flex items-center gap-2 shadow-xs cursor-pointer">
                <i data-lucide="grid-plus" class="w-4 h-4"></i>
                <span>Add Subcategory Card</span>
            </button>
        </div>
    </div>

    <!-- Category Tabs List & Subcategory Management -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Category Tabs -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center space-x-2">
                    <i data-lucide="layers" class="w-4 h-4 text-red-600"></i>
                    <h2 class="text-sm font-semibold text-slate-900">Category Tabs (Pills)</h2>
                </div>
                <span class="text-xs font-medium text-slate-400"><?= count($categories) ?> tabs</span>
            </div>

            <div class="p-3 space-y-2 max-h-[600px] overflow-y-auto" id="tabsContainer">
                <?php if (empty($categories)): ?>
                    <p class="text-xs text-slate-400 text-center py-6">No category tabs found. Click "Add Category Tab" to
                        create one.</p>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <div
                            class="p-3 rounded-xl border border-slate-200 hover:border-slate-300 transition bg-slate-50/50 flex items-center justify-between group">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <span
                                        class="font-semibold text-xs text-slate-900"><?= htmlspecialchars($cat['name']) ?></span>
                                    <?php if ($cat['is_active']): ?>
                                        <span
                                            class="px-2 py-0.5 text-[10px] font-semibold bg-emerald-50 text-emerald-600 rounded-full border border-emerald-100">Active</span>
                                    <?php else: ?>
                                        <span
                                            class="px-2 py-0.5 text-[10px] font-semibold bg-slate-100 text-slate-500 rounded-full">Hidden</span>
                                    <?php endif; ?>
                                </div>
                                <span class="text-[11px] text-slate-400 font-mono">slug: <?= htmlspecialchars($cat['slug']) ?> •
                                    sort: <?= $cat['sort_order'] ?></span>
                            </div>
                            <div class="flex items-center space-x-1 opacity-90 group-hover:opacity-100">
                                <button onclick='openEditTabModal(<?= json_encode($cat) ?>)'
                                    class="p-1.5 text-slate-500 hover:text-slate-900 hover:bg-slate-200 rounded-lg transition"
                                    title="Edit Tab">
                                    <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                </button>
                                <form action="<?= url('admin/featured-categories/delete-category/' . $cat['id']) ?>"
                                    method="POST" onsubmit="return confirm('Delete this category tab and all its cards?');">
                                    <?= csrf_field() ?>
                                    <button type="submit"
                                        class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                        title="Delete Tab">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Column: Subcategory Cards Per Tab -->
        <div class="lg:col-span-2 space-y-6">
            <?php foreach ($categories as $cat): ?>
                <?php $subList = $subcategoriesGrouped[$cat['id']] ?? []; ?>
                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-900"></span>
                            <h3 class="text-sm font-semibold text-slate-900"><?= htmlspecialchars($cat['name']) ?> Tab Cards
                            </h3>
                            <span class="text-xs text-slate-400">(<?= count($subList) ?> cards)</span>
                        </div>
                        <button onclick="openAddSubmodal(<?= $cat['id'] ?>)"
                            class="text-xs font-semibold text-red-600 hover:text-red-700 flex items-center space-x-1">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Add Card</span>
                        </button>
                    </div>

                    <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Auto First Card: View All -->
                        <div
                            class="p-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 flex items-center space-x-3 opacity-80">
                            <div class="w-14 h-14 rounded-lg bg-slate-200 flex items-center justify-center shrink-0">
                                <i data-lucide="grid" class="w-6 h-6 text-slate-500"></i>
                            </div>
                            <div>
                                <span
                                    class="px-1.5 py-0.5 text-[9px] font-semibold uppercase bg-slate-200 text-slate-600 rounded">Auto
                                    Card 1</span>
                                <h4 class="text-xs font-semibold text-slate-900 mt-1">View All
                                    <?= htmlspecialchars($cat['name']) ?></h4>
                                <p class="text-[10px] text-slate-500">Automatically generated as card #1</p>
                            </div>
                        </div>

                        <!-- Subcategory Cards -->
                        <?php foreach ($subList as $sub): ?>
                            <div
                                class="p-4 rounded-xl border border-slate-200 bg-white flex items-center justify-between shadow-2xs hover:shadow-xs transition">
                                <div class="flex items-center space-x-3 overflow-hidden">
                                    <?php if (!empty($sub['image'])): ?>
                                        <img src="<?= asset(ltrim($sub['image'], '/')) ?>" alt=""
                                            class="w-14 h-14 object-cover rounded-lg border border-slate-100 shrink-0"
                                            onerror="this.src='https://via.placeholder.com/60?text=IMG';">
                                    <?php else: ?>
                                        <div
                                            class="w-14 h-14 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 shrink-0">
                                            <i data-lucide="image" class="w-5 h-5"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="truncate">
                                        <div class="flex items-center space-x-2">
                                            <h4 class="text-xs font-semibold text-slate-900 truncate">
                                                <?= htmlspecialchars($sub['name']) ?></h4>
                                            <?php if (!$sub['is_active']): ?>
                                                <span
                                                    class="px-1.5 py-0.2 text-[9px] font-semibold bg-slate-100 text-slate-500 rounded">Off</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-[11px] text-slate-400 truncate mt-0.5">
                                            <?= htmlspecialchars($sub['link_url']) ?></p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-1 shrink-0 ml-2">
                                    <button onclick='openEditSubmodal(<?= json_encode($sub) ?>)'
                                        class="p-1.5 text-slate-400 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition"
                                        title="Edit">
                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <form action="<?= url('admin/featured-categories/delete-subcategory/' . $sub['id']) ?>"
                                        method="POST" onsubmit="return confirm('Delete this subcategory card?');">
                                        <?= csrf_field() ?>
                                        <button type="submit"
                                            class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                                            title="Delete">
                                            <i data-lucide="trash" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

</div>

<!-- Modal 1: Category Tab Form (Add/Edit) -->
<div id="tabModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 id="tabModalTitle" class="text-sm font-semibold text-slate-900">Add Category Tab</h3>
            <button onclick="closeTabModal()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form id="tabForm" action="<?= url('admin/featured-categories/store-category') ?>" method="POST"
            class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tab Name *</label>
                <input type="text" id="tab_name" name="name" required placeholder="e.g. Home & Kitchen"
                    class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sort Order</label>
                    <input type="number" id="tab_sort_order" name="sort_order" value="0"
                        class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
                </div>
                <div class="flex items-center pt-5">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="tab_is_active" name="is_active" value="1" checked
                            class="sr-only peer">
                        <div
                            class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-slate-900">
                        </div>
                        <span class="ml-2 text-xs font-semibold text-slate-700">Active</span>
                    </label>
                </div>
            </div>
            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeTabModal()"
                    class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl">Cancel</button>
                <button type="submit"
                    class="px-4 py-2 text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 rounded-xl">Save
                    Tab</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Subcategory Card Form (Add/Edit) -->
<div id="subModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center hidden">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 id="subModalTitle" class="text-sm font-semibold text-slate-900">Add Subcategory Card</h3>
            <button onclick="closeSubmodal()" class="text-slate-400 hover:text-slate-600">✕</button>
        </div>
        <form id="subForm" action="<?= url('admin/featured-categories/store-subcategory') ?>" method="POST"
            enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Select Category Tab *</label>
                <select id="sub_category_id" name="featured_category_id" required
                    class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Subcategory Card Title *</label>
                <input type="text" id="sub_name" name="name" required placeholder="e.g. Drinkware & Insulated Tumblers"
                    oninput="updateLivePreview()"
                    class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Target Link URL *</label>
                <div class="space-y-2">
                    <select onchange="if(this.value) document.getElementById('sub_link_url').value = this.value;"
                        class="w-full h-9 px-3 border border-slate-200 rounded-xl text-xs bg-slate-50 text-slate-600 focus:outline-none">
                        <option value="">-- Pick Existing Category Page --</option>
                        <?php foreach ($regCategories as $rc): ?>
                            <option value="/catalog?category_id=<?= $rc['id'] ?>"><?= htmlspecialchars($rc['name']) ?>
                                (/catalog?category_id=<?= $rc['id'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <input type="text" id="sub_link_url" name="link_url"
                        placeholder="/catalog?q=drinkware or custom URL"
                        class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
                </div>
            </div>

            <!-- Thumbnail Upload & URL -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Thumbnail Image (Square
                    Recommended)</label>
                <div class="flex items-center space-x-3">
                    <input type="file" name="image_file" accept="image/*" onchange="previewFileImage(this)"
                        class="text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                </div>
                <div class="mt-2">
                    <input type="text" id="sub_image_url" name="image_url" placeholder="Or paste image URL"
                        oninput="updateLivePreview()"
                        class="w-full h-9 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none">
                </div>
            </div>

            <!-- Live Card Preview -->
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Live Card
                    Preview</label>
                <div class="p-3 rounded-xl border border-slate-200 bg-slate-50 flex items-center space-x-3">
                    <img id="previewImg" src="https://via.placeholder.com/60?text=IMG"
                        class="w-12 h-12 object-cover rounded-lg border border-slate-200">
                    <div>
                        <h4 id="previewTitle" class="text-xs font-semibold text-slate-900">Subcategory Title</h4>
                        <span class="text-[10px] text-slate-400">Card Preview</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sort Order</label>
                    <input type="number" id="sub_sort_order" name="sort_order" value="0"
                        class="w-full h-10 px-3 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-red-600">
                </div>
                <div class="flex items-center pt-5">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="sub_is_active" name="is_active" value="1" checked
                            class="sr-only peer">
                        <div
                            class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-slate-900">
                        </div>
                        <span class="ml-2 text-xs font-semibold text-slate-700">Active</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end space-x-2 pt-2">
                <button type="button" onclick="closeSubmodal()"
                    class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl">Cancel</button>
                <button type="submit"
                    class="px-4 py-2 text-xs font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl">Save
                    Card</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddTabModal() {
        document.getElementById('tabModalTitle').innerText = 'Add Category Tab';
        document.getElementById('tabForm').action = "<?= url('admin/featured-categories/store-category') ?>";
        document.getElementById('tab_name').value = '';
        document.getElementById('tab_sort_order').value = '0';
        document.getElementById('tab_is_active').checked = true;
        document.getElementById('tabModal').classList.remove('hidden');
    }

    function openEditTabModal(cat) {
        document.getElementById('tabModalTitle').innerText = 'Edit Category Tab';
        document.getElementById('tabForm').action = "<?= url('admin/featured-categories/update-category/') ?>" + cat.id;
        document.getElementById('tab_name').value = cat.name;
        document.getElementById('tab_sort_order').value = cat.sort_order;
        document.getElementById('tab_is_active').checked = parseInt(cat.is_active) === 1;
        document.getElementById('tabModal').classList.remove('hidden');
    }

    function closeTabModal() {
        document.getElementById('tabModal').classList.add('hidden');
    }

    function openAddSubmodal(catId = null) {
        document.getElementById('subModalTitle').innerText = 'Add Subcategory Card';
        document.getElementById('subForm').action = "<?= url('admin/featured-categories/store-subcategory') ?>";
        document.getElementById('sub_name').value = '';
        document.getElementById('sub_link_url').value = '';
        document.getElementById('sub_image_url').value = '';
        document.getElementById('sub_sort_order').value = '0';
        document.getElementById('sub_is_active').checked = true;
        if (catId) {
            document.getElementById('sub_category_id').value = catId;
        }
        updateLivePreview();
        document.getElementById('subModal').classList.remove('hidden');
    }

    function openEditSubmodal(sub) {
        document.getElementById('subModalTitle').innerText = 'Edit Subcategory Card';
        document.getElementById('subForm').action = "<?= url('admin/featured-categories/update-subcategory/') ?>" + sub.id;
        document.getElementById('sub_category_id').value = sub.featured_category_id;
        document.getElementById('sub_name').value = sub.name;
        document.getElementById('sub_link_url').value = sub.link_url;
        document.getElementById('sub_image_url').value = sub.image;
        document.getElementById('sub_sort_order').value = sub.sort_order;
        document.getElementById('sub_is_active').checked = parseInt(sub.is_active) === 1;
        updateLivePreview();
        document.getElementById('subModal').classList.remove('hidden');
    }

    function closeSubmodal() {
        document.getElementById('subModal').classList.add('hidden');
    }

    function updateLivePreview() {
        const title = document.getElementById('sub_name').value || 'Subcategory Title';
        const url = document.getElementById('sub_image_url').value;
        document.getElementById('previewTitle').innerText = title;
        if (url) {
            document.getElementById('previewImg').src = url;
        }
    }

    function previewFileImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('previewImg').src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>