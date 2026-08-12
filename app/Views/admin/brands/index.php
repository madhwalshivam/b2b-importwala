<?php
include __DIR__ . '/../layouts/header.php';
?>

<!-- Include SortableJS for drag-and-drop reordering -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<div class="space-y-6" x-data="brandManager()" x-effect="document.body.classList.toggle('modal-open', showModal)">

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
                <span class="text-xs text-slate-500 font-medium">Featured Brands</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Brands Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Manage brand logos, external website links, active status, and drag-and-drop homepage display order.
            </p>
        </div>

        <?php if (\App\Core\Auth::hasPermission('brands.add')): ?>
            <button @click="openAddModal()"
                class="h-10 px-5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition flex items-center space-x-2 shrink-0 shadow-xs cursor-pointer">
                <i data-lucide="plus" class="w-4 h-4 text-white"></i>
                <span>Add Featured Brand</span>
            </button>
        <?php endif; ?>
    </div>

    <!-- Bulk Actions & Controls Bar -->
    <div
        class="bg-white p-4 rounded-[10px] border border-gray-900 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
        <form action="<?= url('admin/brands/bulk-action') ?>" method="POST" id="bulkForm"
            class="flex items-center space-x-3 w-full sm:w-auto" @submit.prevent="submitBulkAction($event)">
            <?= csrf_field() ?>
            <span class="font-semibold text-gray-700">Bulk Actions:</span>
            <select name="action" x-model="bulkAction"
                class="h-10 px-3 bg-gray-50 border border-gray-900 rounded-[8px] text-xs font-medium focus:ring-2 focus:ring-red-500 focus:border-transparent">
                <option value="">-- Choose Action --</option>
                <option value="activate">Activate Selected</option>
                <option value="deactivate">Deactivate Selected</option>
                <option value="delete">Delete Selected</option>
            </select>
            <button type="submit" :disabled="selectedIds.length === 0 || !bulkAction"
                class="h-10 px-4 bg-gray-900 hover:bg-black text-white font-semibold rounded-[8px] disabled:opacity-40 disabled:cursor-not-allowed transition">
                Apply (<span x-text="selectedIds.length">0</span>)
            </button>
            <template x-for="id in selectedIds" :key="id">
                <input type="hidden" name="ids[]" :value="id">
            </template>
        </form>

        <div class="text-xs text-gray-500 font-medium flex items-center space-x-2">
            <i data-lucide="move" class="w-4 h-4 text-gray-400"></i>
            <span>Drag rows by the <strong class="text-gray-700">:::</strong> handle to reorder</span>
        </div>
    </div>

    <!-- Brands Table -->
    <div class="bg-white rounded-[10px] border border-gray-900 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr
                        class="bg-gray-50 border-b border-gray-900 text-gray-500 font-semibold uppercase tracking-wider">
                        <th class="p-4 w-10 text-center">
                            <input type="checkbox" @change="toggleSelectAll($event)"
                                class="rounded text-red-600 focus:ring-red-500 cursor-pointer">
                        </th>
                        <th class="p-4 w-10 text-center">Order</th>
                        <th class="p-4 w-20">Logo</th>
                        <th class="p-4">Brand Name</th>
                        <th class="p-4">Website Link</th>
                        <th class="p-4 w-28 text-center">Status</th>
                        <th class="p-4 text-right w-36">Actions</th>
                    </tr>
                </thead>
                <tbody id="brandSortableBody">
                    <?php if (empty($brands)): ?>
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-400 font-medium">
                                No brands found. Click "Add Featured Brand" to create one.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($brands as $b): ?>
                            <?php
                            $logoUrl = !empty($b['logo_path']) ? $b['logo_path'] : (!empty($b['logo']) ? $b['logo'] : null);
                            $isActive = (int) ($b['is_active'] ?? ($b['status'] === 'active' ? 1 : 0));
                            ?>
                            <tr data-id="<?= $b['id'] ?>"
                                class="border-b border-gray-100 hover:bg-gray-50/80 transition brand-row">
                                <!-- Checkbox -->
                                <td class="p-4 text-center">
                                    <input type="checkbox" :value="<?= $b['id'] ?>" x-model="selectedIds"
                                        class="rounded text-red-600 focus:ring-red-500 cursor-pointer">
                                </td>

                                <!-- Drag Handle -->
                                <td class="p-4 text-center cursor-grab drag-handle text-gray-400 hover:text-gray-900 transition"
                                    title="Drag to reorder">
                                    <span class="text-base font-semibold select-none">⋮⋮</span>
                                </td>

                                <!-- Logo Thumbnail -->
                                <td class="p-4">
                                    <div
                                        class="w-16 h-10 bg-gray-50 border border-gray-900 rounded-[8px] p-1 flex items-center justify-center overflow-hidden">
                                        <?php if (!empty($logoUrl)): ?>
                                            <img src="<?= asset(ltrim($logoUrl, '/')) ?>" alt="<?= htmlspecialchars($b['name']) ?>"
                                                class="max-h-8 max-w-full object-contain"
                                                onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                            <span
                                                class="text-[10px] font-semibold text-gray-400 hidden font-mono"><?= htmlspecialchars(substr($b['name'], 0, 3)) ?></span>
                                        <?php else: ?>
                                            <span
                                                class="text-[10px] font-semibold text-gray-400 font-mono uppercase"><?= htmlspecialchars(substr($b['name'], 0, 3)) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Brand Name -->
                                <td class="p-4 font-semibold text-gray-900">
                                    <div class="flex items-center space-x-2">
                                        <span><?= htmlspecialchars($b['name']) ?></span>
                                        <?php if (!empty($b['is_featured'])): ?>
                                            <span
                                                class="px-2 py-0.5 text-[9px] font-semibold bg-amber-100 text-amber-800 border border-amber-200 rounded-full uppercase">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($b['description'])): ?>
                                        <p class="text-[11px] font-normal text-gray-400 truncate max-w-xs mt-0.5">
                                            <?= htmlspecialchars($b['description']) ?>
                                        </p>
                                    <?php endif; ?>
                                </td>

                                <!-- Website Link -->
                                <td class="p-4">
                                    <?php if (!empty($b['website_link'])): ?>
                                        <a href="<?= htmlspecialchars($b['website_link']) ?>" target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-blue-600 hover:underline flex items-center space-x-1 max-w-xs truncate font-mono text-[11px]">
                                            <span class="truncate"><?= htmlspecialchars($b['website_link']) ?></span>
                                            <i data-lucide="external-link" class="w-3 h-3 shrink-0"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-[11px] italic">No link</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Status Toggle Switch -->
                                <td class="p-4 text-center">
                                    <button type="button" @click="toggleBrandStatus(<?= $b['id'] ?>)"
                                        class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none"
                                        :class="<?= $isActive ?> ? 'bg-red-600' : 'bg-gray-900'"
                                        title="Click to toggle Active / Inactive">
                                        <span
                                            class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform shadow-xs"
                                            :class="<?= $isActive ?> ? 'translate-x-6' : 'translate-x-1'"></span>
                                    </button>
                                </td>

                                <!-- Actions -->
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <?php if (\App\Core\Auth::hasPermission('brands.edit')): ?>
                                            <button type="button"
                                                @click="openEditModal(<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>)"
                                                class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-100 hover:text-blue-700 font-medium rounded-[8px] transition-colors flex items-center space-x-1"
                                                title="Edit Brand">
                                                <i data-lucide="edit" class="w-3.5 h-3.5"></i>
                                                <span class="text-[11px]">Edit</span>
                                            </button>
                                        <?php endif; ?>

                                        <?php if (\App\Core\Auth::hasPermission('brands.delete')): ?>
                                            <form action="<?= url('admin/brands/delete/' . $b['id']) ?>" method="POST"
                                                class="inline m-0" data-confirm="Are you sure you want to delete this brand?">
                                                <?= csrf_field() ?>
                                                <button type="submit"
                                                    class="p-2 bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 font-medium rounded-[8px] transition-colors flex items-center space-x-1"
                                                    title="Delete Brand">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span class="text-[11px]">Delete</span>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add / Edit Brand Modal -->
    <div x-show="showModal"
        class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-[99999] flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
        x-cloak>
        <div class="bg-white max-w-lg w-full p-6 rounded-2xl border border-gray-900 shadow-2xl space-y-5 my-auto z-[100000] max-h-[90vh] sm:max-h-[85vh] overflow-y-auto flex flex-col"
            @click.away="showModal = false">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-semibold text-gray-900"
                    x-text="isEdit ? 'Edit Brand' : 'Add New Featured Brand'">
                </h3>
                <button type="button" @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="isEdit ? '<?= url('admin/brands/update/') ?>' + form.id : '<?= url('admin/brands/store') ?>'"
                method="POST" enctype="multipart/form-data" class="space-y-4 text-xs">
                <?= csrf_field() ?>

                <!-- Brand Name -->
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Brand Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="form.name" required
                        placeholder="e.g. Ola Electric, Ather Energy"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-[10px] font-medium focus:bg-white focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                </div>

                <!-- Website Link -->
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Website Link <span
                            class="text-gray-400 font-normal">(Optional URL)</span></label>
                    <input type="url" name="website_link" x-model="form.website_link"
                        placeholder="https://olaelectric.com"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-[10px] font-mono text-xs focus:bg-white focus:ring-2 focus:ring-red-500 focus:border-transparent transition">
                    <p class="text-[10px] text-gray-400 mt-1">If specified, clicking the brand logo on the homepage will
                        open this link in a new tab.</p>
                </div>

                <!-- Logo Upload & Live Preview -->
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Brand Logo Image <span
                            class="text-gray-400 font-normal">(JPG, PNG, SVG, WEBP — Max 2MB)</span></label>
                    <input type="file" name="logo" accept="image/jpeg,image/png,image/svg+xml,image/webp,image/gif"
                        @change="previewImage($event)"
                        class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-[8px] file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-900 cursor-pointer">

                    <!-- Live Image Preview Box -->
                    <div x-show="logoPreview"
                        class="mt-3 p-3 bg-gray-50 border border-gray-900 rounded-[10px] flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <span class="text-[10px] font-semibold text-gray-500 uppercase">Preview:</span>
                            <div
                                class="h-12 w-24 bg-white border border-gray-900 rounded p-1 flex items-center justify-center">
                                <img :src="logoPreview" class="max-h-10 max-w-full object-contain">
                            </div>
                        </div>
                        <button type="button" @click="logoPreview = ''; form.logo_url = ''"
                            class="text-xs text-red-600 font-semibold hover:underline">Remove</button>
                    </div>

                    <!-- Optional External Logo URL -->
                    <div class="mt-2">
                        <input type="text" name="logo_url" x-model="form.logo_url"
                            placeholder="Or paste image URL (https://...)"
                            class="w-full h-9 px-3 bg-gray-50 border border-gray-900 rounded-[8px] font-mono text-[11px]">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Description <span
                            class="text-gray-400 font-normal">(Optional)</span></label>
                    <textarea name="description" x-model="form.description" rows="2"
                        placeholder="Brief manufacturer description..."
                        class="w-full p-3 bg-gray-50 border border-gray-900 rounded-[10px] font-medium focus:bg-white focus:ring-2 focus:ring-red-500 focus:border-transparent transition"></textarea>
                </div>

                <!-- Toggles: Active Status & Homepage Featured -->
                <div class="grid grid-cols-2 gap-4 p-3 bg-gray-50 rounded-[10px] border border-gray-900">
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" :checked="form.is_active == 1"
                            class="rounded text-red-600 focus:ring-red-500">
                        <span class="font-semibold text-gray-800">Active</span>
                    </label>
                    <label class="flex items-center space-x-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" :checked="form.is_featured == 1"
                            class="rounded text-red-600 focus:ring-red-500">
                        <span class="font-semibold text-gray-800">Featured on Homepage</span>
                    </label>
                </div>

                <!-- Modal Actions -->
                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100">
                    <button type="button" @click="showModal = false"
                        class="h-10 px-4 bg-gray-100 text-gray-700 font-semibold rounded-[8px] hover:bg-gray-900 transition">Cancel</button>
                    <button type="submit"
                        class="h-10 px-6 bg-red-600 text-white font-semibold rounded-[8px] hover:bg-red-700 transition shadow-xs"
                        x-text="isEdit ? 'Save Changes' : 'Add Brand'"></button>
                </div>
            </form>
        </div>
    </div>

</div>
</div>

<script>
    function brandManager() {
        return {
            showModal: false,
            isEdit: false,
            form: {
                id: null,
                name: '',
                website_link: '',
                description: '',
                is_active: 1,
                is_featured: 1,
                logo_url: '',
                logo_preview: ''
            },
            logoPreview: '',
            selectedIds: [],
            bulkAction: '',

            init() {
                this.$nextTick(() => {
                    this.initSortable();
                });
            },

            initSortable() {
                const el = document.getElementById('brandSortableBody');
                if (!el) return;

                Sortable.create(el, {
                    handle: '.drag-handle',
                    animation: 150,
                    onEnd: () => {
                        const rows = el.querySelectorAll('tr.brand-row');
                        const order = Array.from(rows).map(row => parseInt(row.dataset.id));

                        // AJAX update sort order
                        const formData = new FormData();
                        formData.append('_csrf_token', '<?= csrf_token() ?>');
                        order.forEach(id => formData.append('order[]', id));

                        fetch('<?= url('admin/brands/reorder') ?>', {
                            method: 'POST',
                            body: formData
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    if (window.showToast) {
                                        window.showToast('Brand order updated successfully.', 'success');
                                    }
                                } else {
                                    alert(data.message || 'Failed to update order.');
                                }
                            })
                            .catch(err => {
                                console.error('Reorder error:', err);
                            });
                    }
                });
            },

            openAddModal() {
                this.isEdit = false;
                this.form = {
                    id: null,
                    name: '',
                    website_link: '',
                    description: '',
                    is_active: 1,
                    is_featured: 1,
                    logo_url: '',
                    logo_preview: ''
                };
                this.logoPreview = '';
                this.showModal = true;
            },

            openEditModal(brand) {
                this.isEdit = true;
                const logo = brand.logo_path || brand.logo || '';
                let previewUrl = '';
                if (logo) {
                    if (logo.startsWith('http://') || logo.startsWith('https://') || logo.startsWith('//')) {
                        previewUrl = logo;
                    } else {
                        previewUrl = '<?= url('/') ?>/' + logo.replace(/^\//, '');
                    }
                }
                this.form = {
                    id: brand.id,
                    name: brand.name || '',
                    website_link: brand.website_link || '',
                    description: brand.description || '',
                    is_active: (brand.is_active != null ? parseInt(brand.is_active) : (brand.status === 'active' ? 1 : 0)),
                    is_featured: parseInt(brand.is_featured || 0),
                    logo_url: (logo.startsWith('http') || logo.startsWith('//')) ? logo : '',
                    logo_preview: previewUrl
                };
                this.logoPreview = previewUrl;
                this.showModal = true;
            },

            previewImage(event) {
                const file = event.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.logoPreview = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            },

            toggleBrandStatus(id) {
                const formData = new FormData();
                formData.append('_csrf_token', '<?= csrf_token() ?>');

                fetch('<?= url('admin/brands/toggle-status/') ?>' + id, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (window.showToast) {
                                window.showToast('Brand status updated successfully.', 'success');
                            }
                            setTimeout(() => window.location.reload(), 300);
                        } else {
                            alert(data.message || 'Failed to update status.');
                        }
                    })
                    .catch(err => {
                        console.error('Toggle status error:', err);
                    });
            },

            toggleSelectAll(event) {
                if (event.target.checked) {
                    const rows = document.querySelectorAll('tr.brand-row');
                    this.selectedIds = Array.from(rows).map(r => parseInt(r.dataset.id));
                } else {
                    this.selectedIds = [];
                }
            },

            submitBulkAction(event) {
                if (this.selectedIds.length === 0 || !this.bulkAction) return;
                document.getElementById('bulkForm').submit();
            }
        }
    }
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>