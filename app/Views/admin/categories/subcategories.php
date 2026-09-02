<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="space-y-6" x-data="subcategoryManager()" x-init="init()">

    <!-- Top Header -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 tracking-tight flex items-center space-x-2">
                <i data-lucide="git-fork" class="w-6 h-6 text-red-600"></i>
                <span>Sub-category Manager</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Manage sub-categories linked to main parent categories for your
                wholesale catalog.</p>
        </div>
        <div class="flex items-center space-x-3 shrink-0">
            <a href="<?= url('admin/categories') ?>"
                class="h-10 px-4 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-xl shadow-xs transition flex items-center space-x-2">
                <i data-lucide="folders" class="w-4 h-4 text-slate-400"></i>
                <span>Main Categories</span>
            </a>
            <button type="button" onclick="openCreateSubcategoryModal()"
                class="h-10 px-4 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center space-x-2 cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Sub-category</span>
            </button>
        </div>
    </div>

    <!-- MAIN SUBCATEGORIES TABLE CARD -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <!-- Table Search & Filter Bar -->
        <div
            class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
                <!-- Live Search Box -->
                <div class="relative flex-1 max-w-sm">
                    <i data-lucide="search"
                        class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" x-model="searchQuery" placeholder="Search sub-categories by name or slug..."
                        class="w-full h-9 pl-9 pr-4 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-red-600 transition">
                </div>

                <!-- Filter by Parent Category -->
                <form action="<?= url('admin/subcategories') ?>" method="GET" class="flex items-center space-x-2">
                    <select name="category_id" onchange="this.form.submit()"
                        class="h-9 px-3 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-red-600 transition">
                        <option value="0">All Parent Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $selectedCategoryId == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <div class="flex items-center space-x-2 text-xs text-slate-500 font-medium">
                <span>Total Sub-categories: <strong class="text-slate-900" x-text="subcategoriesCount"></strong></span>
            </div>
        </div>

        <!-- Responsive Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead
                    class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold text-[10px]">
                    <tr>
                        <th class="py-3 px-4 text-center w-16">Image</th>
                        <th class="py-3 px-4">Sub-category Name</th>
                        <th class="py-3 px-4">Slug</th>
                        <th class="py-3 px-4">Parent Category</th>
                        <th class="py-3 px-4 text-center">Linked Products</th>
                        <th class="py-3 px-4 text-center">Sort Order</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right pr-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($subcategories)): ?>
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <i data-lucide="git-fork" class="w-8 h-8 text-slate-300"></i>
                                    <span class="font-medium text-slate-600">No sub-categories found</span>
                                    <p class="text-xs text-slate-400">Click "Add Sub-category" above to create your first
                                        sub-category.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($subcategories as $sub): ?>
                            <tr class="hover:bg-slate-50/80 transition group"
                                x-show="matchesSearch(<?= htmlspecialchars(json_encode($sub['name'])) ?>, <?= htmlspecialchars(json_encode($sub['slug'])) ?>)">

                                <!-- Image Column -->
                                <td class="py-3 px-4 text-center">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200/80 overflow-hidden flex items-center justify-center mx-auto text-slate-400 shadow-2xs group-hover:border-red-200 transition">
                                        <?php if (!empty($sub['image'])): ?>
                                            <img src="<?= asset(ltrim($sub['image'], '/')) ?>"
                                                alt="<?= htmlspecialchars($sub['name']) ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <i data-lucide="image" class="w-5 h-5 text-slate-300"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Subcategory Name -->
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-900">
                                        <?= htmlspecialchars($sub['name']) ?>
                                    </div>
                                    <?php if (!empty($sub['description'])): ?>
                                        <p class="text-[11px] text-slate-400 font-normal line-clamp-1 max-w-xs mt-0.5">
                                            <?= htmlspecialchars(trim(preg_replace('/\s+/', ' ', $sub['description']))) ?>
                                        </p>
                                    <?php endif; ?>
                                </td>

                                <!-- Slug -->
                                <td class="py-3 px-4">
                                    <span
                                        class="font-mono text-[11px] text-slate-500 bg-slate-100/70 px-2 py-0.5 rounded-md border border-slate-200/50">
                                        <?= htmlspecialchars($sub['slug']) ?>
                                    </span>
                                </td>

                                <!-- Parent Category Badge -->
                                <td class="py-3 px-4">
                                    <?php if (!empty($sub['category_name'])): ?>
                                        <span
                                            class="inline-flex items-center space-x-1.5 text-xs text-slate-700 font-medium bg-slate-50 border border-slate-200/70 px-2 py-1 rounded-lg">
                                            <i data-lucide="corner-down-right" class="w-3 h-3 text-slate-400"></i>
                                            <span><?= htmlspecialchars($sub['category_name']) ?></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-[11px] font-mono">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Linked Products -->
                                <td class="py-3 px-4 text-center">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200/60">
                                        <?= (int) ($sub['product_count'] ?? 0) ?> items
                                    </span>
                                </td>

                                <!-- Sort Order -->
                                <td class="py-3 px-4 text-center font-mono text-xs text-slate-600">
                                    <?= (int) ($sub['sort_order'] ?? 0) ?>
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3 px-4 text-center">
                                    <?php if ($sub['status'] === 'active'): ?>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-3 px-4 text-right pr-6">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        <button type="button" onclick="openEditSubcategoryModal(<?= $sub['id'] ?>)"
                                            class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-600 hover:text-red-600 transition flex items-center justify-center cursor-pointer border border-slate-200/80"
                                            title="Edit Sub-category">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>

                                        <a href="<?= url('admin/subcategories/delete/' . $sub['id']) ?>"
                                            onclick="return confirm('Are you sure you want to delete this sub-category?');"
                                            class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 transition flex items-center justify-center cursor-pointer border border-slate-200/80"
                                            title="Delete Sub-category">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </a>
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

<!-- Create Sub-category Modal -->
<div id="createSubmodal"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-semibold text-base text-slate-900 flex items-center space-x-2">
                <i data-lucide="plus-circle" class="w-5 h-5 text-red-600"></i>
                <span>Add New Sub-category</span>
            </h3>
            <button type="button" onclick="closeCreateSubcategoryModal()"
                class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="<?= url('admin/subcategories/store') ?>" method="POST" enctype="multipart/form-data"
            class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Parent Category
                    *</label>
                <select name="category_id" required
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:border-red-600">
                    <option value="">-- Select Parent Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Sub-category
                    Name *</label>
                <input type="text" name="name" required placeholder="e.g. Stainless Steel Necklaces"
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-red-600">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Sort
                        Order</label>
                    <input type="number" name="sort_order" value="0"
                        class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-red-600">
                </div>
                <div>
                    <label
                        class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Status</label>
                    <select name="status"
                        class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:border-red-600">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Image
                    Upload</label>
                <input type="file" name="image_file" accept="image/*"
                    class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-600 hover:file:bg-red-100">
            </div>

            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateSubcategoryModal()"
                    class="h-9 px-4 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer">Cancel</button>
                <button type="submit"
                    class="h-9 px-5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-semibold shadow-xs cursor-pointer">Save
                    Sub-category</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Sub-category Modal -->
<div id="editSubmodal"
    class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-semibold text-base text-slate-900 flex items-center space-x-2">
                <i data-lucide="pencil" class="w-5 h-5 text-red-600"></i>
                <span>Edit Sub-category</span>
            </h3>
            <button type="button" onclick="closeEditSubcategoryModal()"
                class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="editSubForm" action="" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?= csrf_field() ?>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Parent Category
                    *</label>
                <select id="edit_category_id" name="category_id" required
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:border-red-600">
                    <option value="">-- Select Parent Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Sub-category
                    Name *</label>
                <input type="text" id="edit_name" name="name" required
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-red-600">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Sort
                        Order</label>
                    <input type="number" id="edit_sort_order" name="sort_order"
                        class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-red-600">
                </div>
                <div>
                    <label
                        class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Status</label>
                    <select id="edit_status" name="status"
                        class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:outline-none focus:border-red-600">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-1">Update
                    Image</label>
                <input type="file" name="image_file" accept="image/*"
                    class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-600 hover:file:bg-red-100">
            </div>

            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditSubcategoryModal()"
                    class="h-9 px-4 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 cursor-pointer">Cancel</button>
                <button type="submit"
                    class="h-9 px-5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-semibold shadow-xs cursor-pointer">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
    function subcategoryManager() {
        return {
            searchQuery: '',
            subcategoriesCount: <?= count($subcategories) ?>,
            init() {
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            },
            matchesSearch(name, slug) {
                if (!this.searchQuery) return true;
                const query = this.searchQuery.toLowerCase().trim();
                return (name || '').toLowerCase().includes(query) || (slug || '').toLowerCase().includes(query);
            }
        };
    }

    function openCreateSubcategoryModal() {
        document.getElementById('createSubmodal').classList.remove('hidden');
    }
    function closeCreateSubcategoryModal() {
        document.getElementById('createSubmodal').classList.add('hidden');
    }
    function openEditSubcategoryModal(id) {
        fetch('<?= url("admin/subcategories/get/") ?>' + id)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const sub = data.subcategory;
                    document.getElementById('editSubForm').action = '<?= url("admin/subcategories/update/") ?>' + id;
                    document.getElementById('edit_category_id').value = sub.parent_id || sub.category_id;
                    document.getElementById('edit_name').value = sub.name;
                    document.getElementById('edit_sort_order').value = sub.sort_order;
                    document.getElementById('edit_status').value = sub.status;
                    document.getElementById('editSubmodal').classList.remove('hidden');
                } else {
                    alert(data.message || 'Error fetching subcategory.');
                }
            });
    }
    function closeEditSubcategoryModal() {
        document.getElementById('editSubmodal').classList.add('hidden');
    }
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>