<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6" x-data="categoryManager()" x-init="init()"
    x-effect="document.body.classList.toggle('modal-open', modalOpen || viewModalOpen || deleteModalOpen)">

    <!-- Toast Notification Container -->
    <div x-show="toast.show" x-transition:enter="transition ease-out duration-300 transform"
        x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200 transform"
        x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
        class="fixed top-5 right-5 z-[100001] flex items-center space-x-3 px-4 py-3 rounded-xl shadow-lg border text-xs font-semibold max-w-sm"
        :class="toast.type === 'success' ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-red-50 text-red-800 border-red-200'"
        x-cloak>
        <i :data-lucide="toast.type === 'success' ? 'check-circle-2' : 'alert-triangle'" class="w-4 h-4 shrink-0"></i>
        <span x-text="toast.message"></span>
        <button @click="toast.show = false" class="ml-auto text-slate-400 hover:text-slate-600">
            <i data-lucide="x" class="w-3.5 h-3.5"></i>
        </button>
    </div>

    <!-- Top Header -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 tracking-tight flex items-center space-x-2">
                <i data-lucide="folders" class="w-6 h-6 text-red-600"></i>
                <span>Category Management</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Manage scooter accessory categories, parent-child hierarchies, and
                image assets.</p>
        </div>
        <button @click="openAddModal()"
            class="h-10 px-4 bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center space-x-2 shrink-0 cursor-pointer">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add New Category</span>
        </button>
    </div>

    <!-- MAIN CATEGORIES TABLE CARD -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <!-- Table Search & Filter Bar -->
        <div
            class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="relative flex-1 max-w-sm">
                <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input type="text" x-model="searchQuery" placeholder="Search categories by name or slug..."
                    class="w-full h-9 pl-9 pr-4 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-red-600 transition">
            </div>
            <div class="flex items-center space-x-2 text-xs text-slate-500 font-medium">
                <span>Total Categories: <strong class="text-slate-900" x-text="categoriesCount"></strong></span>
            </div>
        </div>

        <!-- Responsive Table Container -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead
                    class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold text-[10px]">
                    <tr>
                        <th class="py-3 px-4 text-center w-16">Image</th>
                        <th class="py-3 px-4">Category Name</th>
                        <th class="py-3 px-4">Slug</th>
                        <th class="py-3 px-4">Parent Category</th>
                        <th class="py-3 px-4 text-center">Linked Products</th>
                        <th class="py-3 px-4 text-center">Sort Order</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-right pr-6">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="8" class="p-8 text-center text-slate-500">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <i data-lucide="folder-open" class="w-8 h-8 text-slate-300"></i>
                                    <span class="font-medium text-slate-600">No categories found</span>
                                    <p class="text-xs text-slate-400">Click "Add Category" above to create your first
                                        category.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($categories as $cat): ?>
                            <?php
                            $catImg = !empty($cat['custom_icon']) ? $cat['custom_icon'] : (!empty($cat['image']) ? $cat['image'] : '');
                            ?>
                            <tr class="hover:bg-slate-50/80 transition group"
                                x-show="matchesSearch(<?= htmlspecialchars(json_encode($cat['name'])) ?>, <?= htmlspecialchars(json_encode($cat['slug'])) ?>)">

                                <!-- Image Column -->
                                <td class="py-3 px-4 text-center">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-slate-100 border border-slate-200/80 overflow-hidden flex items-center justify-center mx-auto text-slate-400 shadow-2xs group-hover:border-red-200 transition">
                                        <?php if (!empty($catImg)): ?>
                                            <img src="<?= asset($catImg) ?>" alt="<?= htmlspecialchars($cat['name']) ?>"
                                                class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <i data-lucide="image" class="w-5 h-5 text-slate-300"></i>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Category Name -->
                                <td class="py-3 px-4">
                                    <div class="font-semibold text-slate-900 flex items-center space-x-2">
                                        <span><?= htmlspecialchars($cat['name']) ?></span>
                                        <?php if (!empty($cat['is_featured'])): ?>
                                            <span
                                                class="px-1.5 py-0.5 text-[9px] font-semibold uppercase bg-amber-50 text-amber-600 border border-amber-200/60 rounded">Featured</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($cat['description'])): ?>
                                        <p class="text-[11px] text-slate-400 font-normal line-clamp-1 max-w-xs mt-0.5">
                                            <?= htmlspecialchars(trim(preg_replace('/\s+/', ' ', $cat['description']))) ?>
                                        </p>
                                    <?php endif; ?>
                                </td>

                                <!-- Slug -->
                                <td class="py-3 px-4">
                                    <span
                                        class="font-mono text-[11px] text-slate-500 bg-slate-100/70 px-2 py-0.5 rounded-md border border-slate-200/50"><?= htmlspecialchars($cat['slug']) ?></span>
                                </td>

                                <!-- Parent Category -->
                                <td class="py-3 px-4">
                                    <?php if (!empty($cat['parent_name'])): ?>
                                        <span
                                            class="inline-flex items-center space-x-1.5 text-xs text-slate-700 font-medium bg-slate-50 border border-slate-200/70 px-2 py-1 rounded-lg">
                                            <i data-lucide="corner-down-right" class="w-3 h-3 text-slate-400"></i>
                                            <span><?= htmlspecialchars($cat['parent_name']) ?></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-[11px] font-mono">—</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Linked Products Count -->
                                <td class="py-3 px-4 text-center">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-800 border border-slate-200/60">
                                        <?= (int) ($cat['product_count'] ?? 0) ?> items
                                    </span>
                                </td>

                                <!-- Sort Order -->
                                <td class="py-3 px-4 text-center font-mono text-xs text-slate-600">
                                    <?= (int) ($cat['sort_order'] ?? 0) ?>
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3 px-4 text-center">
                                    <?php if ($cat['status'] === 'active'): ?>
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
                                        <button @click="viewCategory(<?= $cat['id'] ?>)"
                                            class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 hover:text-slate-900 transition flex items-center justify-center cursor-pointer border border-slate-200/80"
                                            title="View Details">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </button>

                                        <button @click="openEditModal(<?= $cat['id'] ?>)"
                                            class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-600 hover:text-red-600 transition flex items-center justify-center cursor-pointer border border-slate-200/80"
                                            title="Edit Category">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>

                                        <?php if ((int) ($cat['product_count'] ?? 0) > 0): ?>
                                            <button
                                                @click="openDeleteModal(<?= $cat['id'] ?>, <?= htmlspecialchars(json_encode($cat['name'])) ?>, <?= (int) ($cat['product_count'] ?? 0) ?>)"
                                                class="w-8 h-8 rounded-lg bg-amber-50 hover:bg-amber-600 text-amber-600 hover:text-white transition flex items-center justify-center cursor-pointer border border-amber-200/60"
                                                title="Delete Category (Reassign Products)">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        <?php else: ?>
                                            <button
                                                @click="openDeleteModal(<?= $cat['id'] ?>, <?= htmlspecialchars(json_encode($cat['name'])) ?>, <?= (int) ($cat['product_count'] ?? 0) ?>)"
                                                class="w-8 h-8 rounded-lg bg-red-50 hover:bg-red-600 text-red-600 hover:text-white transition flex items-center justify-center cursor-pointer border border-red-200/60"
                                                title="Delete Category">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
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

    <!-- ADD / EDIT CATEGORY MODAL -->
    <div x-show="modalOpen"
        class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-[99999] flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>

        <div class="bg-white max-w-xl w-full rounded-2xl border border-slate-200 shadow-2xl overflow-hidden max-h-[90vh] sm:max-h-[85vh] flex flex-col my-auto z-[100000]"
            @click.outside="modalOpen = false">

            <!-- Modal Header -->
            <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between shrink-0">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-lg bg-red-100 text-red-600 flex items-center justify-center">
                        <i :data-lucide="isEditing ? 'pencil' : 'plus'" class="w-4 h-4"></i>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900"
                        x-text="isEditing ? 'Edit Category' : 'Add New Category'"></h3>
                </div>
                <button type="button" @click="modalOpen = false"
                    class="text-slate-400 hover:text-slate-600 transition p-1">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Form Body -->
            <form novalidate @submit.prevent="submitForm()" class="p-6 space-y-5 overflow-y-auto text-xs flex-1">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Category Name -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Category Name <span
                                class="text-red-600">*</span></label>
                        <input type="text" x-model="formData.name" @input="onNameInput()" required
                            placeholder="e.g. Footrests & Crash Guards"
                            class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none focus:border-red-600 focus:bg-white transition text-xs">
                    </div>

                    <!-- Slug -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Category Slug</label>
                        <input type="text" x-model="formData.slug" placeholder="footrests-crash-guards"
                            class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-mono text-slate-600 focus:outline-none focus:border-red-600 focus:bg-white transition text-xs">
                    </div>
                </div>

                <!-- Parent Category Selection -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Parent Category</label>
                    <select x-model="formData.parent_id"
                        class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-medium focus:outline-none focus:border-red-600 focus:bg-white transition text-xs">
                        <option value="">None (Root Category)</option>
                        <?php foreach ($categories as $pCat): ?>
                            <option value="<?= $pCat['id'] ?>" :disabled="formData.id == <?= $pCat['id'] ?>">
                                <?= htmlspecialchars($pCat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">Select a parent category if this is a sub-category.</p>
                </div>

                <!-- CATEGORY IMAGE SELECTION (File Upload or Image URL) -->
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2.5">
                        <div class="flex items-center space-x-2">
                            <i data-lucide="image" class="w-4 h-4 text-slate-600"></i>
                            <label class="font-semibold text-slate-800 uppercase text-[11px] tracking-wider">Category
                                Image</label>
                        </div>

                        <!-- Toggle Switch: Upload File vs Image URL -->
                        <div class="flex bg-slate-200/80 p-1 rounded-xl text-[11px] font-semibold space-x-1">
                            <button type="button" @click="imgMode = 'upload'"
                                :class="imgMode === 'upload' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-3 py-1 rounded-lg transition flex items-center space-x-1.5">
                                <i data-lucide="upload-cloud" class="w-3.5 h-3.5"></i>
                                <span>Upload File</span>
                            </button>
                            <button type="button" @click="imgMode = 'url'"
                                :class="imgMode === 'url' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'"
                                class="px-3 py-1 rounded-lg transition flex items-center space-x-1.5">
                                <i data-lucide="link" class="w-3.5 h-3.5"></i>
                                <span>Image URL</span>
                            </button>
                        </div>
                    </div>

                    <!-- Recommended Image Dimension Notice -->
                    <div
                        class="flex items-center space-x-2 text-[11px] text-slate-600 bg-blue-50/80 border border-blue-100 p-2.5 rounded-xl">
                        <i data-lucide="info" class="w-4 h-4 text-blue-600 shrink-0"></i>
                        <span><strong>Recommended Image Size:</strong> <strong>512 × 512 px</strong></span>
                    </div>

                    <!-- MODE 1: FILE UPLOAD -->
                    <div x-show="imgMode === 'upload'" class="space-y-2">
                        <label class="block font-semibold text-slate-700">Choose File from Device</label>
                        <input type="file" @change="handleImageFileChange($event)"
                            accept=".svg,.png,.webp,.jpg,.jpeg,image/*"
                            class="w-full p-2 bg-white border border-slate-200 rounded-xl text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-red-50 file:text-red-700 hover:file:bg-red-100 cursor-pointer">
                    </div>

                    <!-- MODE 2: IMAGE URL -->
                    <div x-show="imgMode === 'url'" class="space-y-2">
                        <label class="block font-semibold text-slate-700">Paste Image URL Link</label>
                        <input type="text" x-model="formData.image_url"
                            placeholder="https://example.com/images/crash-guards.jpg"
                            class="w-full h-10 px-3.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:border-red-600">
                    </div>

                    <!-- LIVE IMAGE PREVIEW BOX -->
                    <div x-show="imagePreviewUrl || formData.image_url"
                        class="flex items-center space-x-3 bg-white p-3 rounded-xl border border-slate-200">
                        <span class="text-[11px] font-semibold text-slate-500 uppercase">Live Image Preview:</span>
                        <div
                            class="w-14 h-14 rounded-xl bg-slate-100 border border-slate-200 flex items-center justify-center p-1 overflow-hidden shrink-0">
                            <img :src="getImageSrc()" alt="Category Preview"
                                class="w-full h-full object-cover rounded-lg">
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-xs font-semibold text-slate-800 block truncate"
                                x-text="imagePreviewUrl ? 'File Selected' : formData.image_url"></span>
                            <span class="text-[10px] text-slate-400">Ready to save as category image</span>
                        </div>
                    </div>
                </div>

                <!-- Category Description -->
                <div>
                    <label class="block font-semibold text-slate-700 mb-1">Description</label>
                    <textarea x-model="formData.description" rows="2"
                        placeholder="Brief description of products in this category..."
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:border-red-600 focus:bg-white transition text-xs"></textarea>
                </div>

                <!-- Display & Status Options -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 p-3.5 rounded-xl border border-slate-200">
                    <div class="flex items-center space-x-2 pt-2">
                        <input type="checkbox" id="cat_is_featured" x-model="formData.is_featured"
                            class="rounded text-red-600 focus:ring-red-500 w-4 h-4 cursor-pointer">
                        <label for="cat_is_featured" class="font-semibold text-slate-700 cursor-pointer">Featured on
                            Homepage</label>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Sort Order</label>
                        <input type="number" x-model="formData.sort_order" min="0"
                            class="w-full h-9 px-3 bg-white border border-slate-200 rounded-lg text-xs">
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-700 mb-1">Status</label>
                        <select x-model="formData.status"
                            class="w-full h-9 px-3 bg-white border border-slate-200 rounded-lg font-semibold text-xs">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <!-- SEO Options (Accordion) -->
                <div class="border border-slate-200 rounded-xl overflow-hidden">
                    <button type="button" @click="showSeo = !showSeo"
                        class="w-full px-4 py-2.5 bg-slate-100 flex items-center justify-between font-semibold text-slate-800 text-xs">
                        <div class="flex items-center space-x-2">
                            <i data-lucide="globe" class="w-4 h-4 text-slate-600"></i>
                            <span>SEO Metadata (Optional)</span>
                        </div>
                        <i :data-lucide="showSeo ? 'chevron-up' : 'chevron-down'" class="w-4 h-4 text-slate-500"></i>
                    </button>

                    <div x-show="showSeo" class="p-4 space-y-3 bg-white border-t border-slate-200" x-cloak>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Meta Title</label>
                            <input type="text" x-model="formData.meta_title" placeholder="Meta title for search engines"
                                class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-700 mb-1">Meta Description</label>
                            <textarea x-model="formData.meta_description" rows="2"
                                placeholder="Meta description for search engines"
                                class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Submit & Cancel Buttons -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-slate-200 shrink-0">
                    <button type="button" @click="modalOpen = false"
                        class="h-10 px-5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition cursor-pointer">Cancel</button>
                    <button type="submit" :disabled="isSaving"
                        class="h-10 px-6 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white font-semibold rounded-xl transition flex items-center space-x-2 cursor-pointer shadow-xs">
                        <template x-if="isSaving">
                            <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                        </template>
                        <span
                            x-text="isSaving ? 'Saving...' : (isEditing ? 'Update Category' : 'Save Category')"></span>
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- VIEW CATEGORY DETAILS MODAL -->
    <div x-show="viewModalOpen"
        class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-[99999] flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>

        <div class="bg-white max-w-lg w-full rounded-2xl border border-slate-200 shadow-2xl p-6 space-y-5 my-auto z-[100000] max-h-[90vh] sm:max-h-[85vh] overflow-y-auto"
            @click.outside="viewModalOpen = false">

            <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-12 h-12 rounded-xl bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center p-0.5">
                        <template x-if="viewData.custom_icon || viewData.image">
                            <img :src="getAssetUrl(viewData.custom_icon || viewData.image)"
                                class="w-full h-full object-cover rounded-lg">
                        </template>
                        <template x-if="!viewData.custom_icon && !viewData.image">
                            <i data-lucide="image" class="w-6 h-6 text-slate-300"></i>
                        </template>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900" x-text="viewData.name"></h3>
                        <span class="text-xs text-slate-400 font-mono" x-text="viewData.slug"></span>
                    </div>
                </div>
                <button type="button" @click="viewModalOpen = false" class="text-slate-400 hover:text-slate-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <div class="space-y-3 text-xs">
                <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200">
                    <div>
                        <span class="text-[10px] font-semibold uppercase text-slate-400 block">Parent Category</span>
                        <span class="font-semibold text-slate-800"
                            x-text="viewData.parent_name || 'None (Root Category)'"></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-semibold uppercase text-slate-400 block">Linked Products</span>
                        <span class="font-semibold text-slate-800"
                            x-text="(viewData.product_count || 0) + ' Products'"></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-semibold uppercase text-slate-400 block">Status</span>
                        <span class="font-semibold uppercase text-slate-800" x-text="viewData.status"></span>
                    </div>
                    <div>
                        <span class="text-[10px] font-semibold uppercase text-slate-400 block">Featured</span>
                        <span class="font-semibold text-slate-800" x-text="viewData.is_featured ? 'Yes' : 'No'"></span>
                    </div>
                </div>

                <template x-if="viewData.description">
                    <div>
                        <span class="text-[10px] font-semibold uppercase text-slate-400 block mb-1">Description</span>
                        <p class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-700"
                            x-text="viewData.description"></p>
                    </div>
                </template>

                <template x-if="viewData.meta_title || viewData.meta_description">
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-200 space-y-1">
                        <span class="text-[10px] font-semibold uppercase text-slate-400 block">SEO Information</span>
                        <p class="font-semibold text-slate-800" x-text="viewData.meta_title"></p>
                        <p class="text-slate-600" x-text="viewData.meta_description"></p>
                    </div>
                </template>
            </div>

            <div class="flex justify-end pt-3 border-t border-slate-200">
                <button type="button" @click="viewModalOpen = false"
                    class="h-9 px-4 bg-slate-900 text-white font-semibold text-xs rounded-xl">Close</button>
            </div>

        </div>
    </div>

    <!-- DELETE CATEGORY CONFIRMATION MODAL -->
    <div x-show="deleteModalOpen"
        class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-[99999] flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
        x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" x-cloak>

        <div class="bg-white max-w-md w-full rounded-2xl border border-slate-200 shadow-2xl p-6 space-y-4 my-auto z-[100000] max-h-[90vh] sm:max-h-[85vh] overflow-y-auto"
            @click.outside="deleteModalOpen = false">

            <div class="flex items-center space-x-3 text-red-600">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                    <i data-lucide="trash-2" class="w-5 h-5 text-red-600"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-slate-900">Delete Category</h3>
                    <p class="text-xs text-slate-500 font-medium" x-text="deleteData.name"></p>
                </div>
            </div>

            <!-- Product Count Warning -->
            <template x-if="deleteData.product_count > 0">
                <div class="bg-amber-50 border border-amber-200 p-3.5 rounded-xl space-y-2 text-xs">
                    <div class="flex items-center space-x-2 text-amber-800 font-semibold">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-amber-600 shrink-0"></i>
                        <span>Warning: Category has linked products</span>
                    </div>
                    <p class="text-amber-700">
                        This category contains <strong x-text="deleteData.product_count"></strong> linked product(s).
                        Please choose a replacement category to migrate products before deleting:
                    </p>
                    <div>
                        <label class="block font-semibold text-amber-900 mb-1">Target Category for Migration</label>
                        <select x-model="reassignCatId"
                            class="w-full h-9 px-3 bg-white border border-amber-300 rounded-lg text-xs font-semibold text-slate-800">
                            <option value="">Do Not Migrate (Unlink Category)</option>
                            <?php foreach ($categories as $tCat): ?>
                                <option value="<?= $tCat['id'] ?>" x-show="deleteData.id != <?= $tCat['id'] ?>">
                                    <?= htmlspecialchars($tCat['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </template>

            <template x-if="deleteData.product_count === 0">
                <p class="text-xs text-slate-600">
                    Are you sure you want to delete this category? This action cannot be undone.
                </p>
            </template>

            <div class="flex justify-end space-x-2 pt-3 border-t border-slate-200">
                <button type="button" @click="deleteModalOpen = false"
                    class="h-9 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl cursor-pointer">Cancel</button>
                <button type="button" @click="confirmDelete()" :disabled="isSaving"
                    class="h-9 px-5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition flex items-center space-x-2 cursor-pointer shadow-xs">
                    <template x-if="isSaving">
                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                    </template>
                    <span x-text="isSaving ? 'Deleting...' : 'Confirm Delete'"></span>
                </button>
            </div>

        </div>
    </div>

</div>

<!-- Alpine.js Manager Script -->
<script>
    function categoryManager() {
        return {
            searchQuery: '',
            modalOpen: false,
            viewModalOpen: false,
            deleteModalOpen: false,
            isEditing: false,
            isSaving: false,
            showSeo: false,
            imgMode: 'upload', // 'upload' or 'url'
            imagePreviewUrl: '',
            imageFile: null,
            reassignCatId: '',

            toast: { show: false, type: 'success', message: '' },

            formData: {
                id: null,
                name: '',
                slug: '',
                image_url: '',
                parent_id: '',
                description: '',
                status: 'active',
                is_featured: false,
                sort_order: 0,
                meta_title: '',
                meta_description: ''
            },

            viewData: {},
            deleteData: { id: null, name: '', product_count: 0 },

            init() {
                this.$watch('modalOpen', val => {
                    if (val) setTimeout(() => lucide.createIcons(), 50);
                });
                this.$watch('viewModalOpen', val => {
                    if (val) setTimeout(() => lucide.createIcons(), 50);
                });
                this.$watch('deleteModalOpen', val => {
                    if (val) setTimeout(() => lucide.createIcons(), 50);
                });
                setTimeout(() => lucide.createIcons(), 100);
            },

            showToast(type, message) {
                this.toast = { show: true, type, message };
                setTimeout(() => { this.toast.show = false; }, 3500);
            },

            matchesSearch(name, slug) {
                if (!this.searchQuery) return true;
                const q = this.searchQuery.toLowerCase();
                return (name && name.toLowerCase().includes(q)) || (slug && slug.toLowerCase().includes(q));
            },

            onNameInput() {
                if (!this.isEditing) {
                    this.formData.slug = this.slugify(this.formData.name);
                }
            },

            slugify(text) {
                return text.toString().toLowerCase().trim()
                    .replace(/\s+/g, '-')
                    .replace(/[^\w\-]+/g, '')
                    .replace(/\-\-+/g, '-');
            },

            handleImageFileChange(event) {
                const file = event.target.files[0];
                if (!file) return;
                this.imageFile = file;

                const reader = new FileReader();
                reader.onload = (e) => {
                    this.imagePreviewUrl = e.target.result;
                };
                reader.readAsDataURL(file);
            },

            getImageSrc() {
                if (this.imagePreviewUrl) return this.imagePreviewUrl;
                if (this.formData.image_url) return this.getAssetUrl(this.formData.image_url);
                return '';
            },

            getAssetUrl(path) {
                if (!path) return '';
                if (path.startsWith('http://') || path.startsWith('https://')) return path;
                const base = '<?= url('') ?>'.replace(/\/+$/, '');
                return base + '/' + path.replace(/^\/+/, '');
            },

            openCreateModal() {
                this.openAddModal();
            },

            openAddModal() {
                this.isEditing = false;
                this.imgMode = 'upload';
                this.imagePreviewUrl = '';
                this.imageFile = null;
                this.showSeo = false;
                this.formData = {
                    id: null,
                    name: '',
                    slug: '',
                    image_url: '',
                    parent_id: '',
                    description: '',
                    status: 'active',
                    is_featured: false,
                    sort_order: 0,
                    meta_title: '',
                    meta_description: ''
                };
                this.modalOpen = true;
            },

            openEditModal(id) {
                this.isEditing = true;
                this.imagePreviewUrl = '';
                this.imageFile = null;
                this.showSeo = false;

                fetch('<?= url('admin/categories/get/') ?>' + id, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.category) {
                            const c = data.category;
                            const catImg = c.custom_icon || c.image || c.icon || '';
                            this.imgMode = (catImg && (catImg.startsWith('http://') || catImg.startsWith('https://'))) ? 'url' : 'upload';
                            this.formData = {
                                id: c.id,
                                name: c.name || '',
                                slug: c.slug || '',
                                image_url: catImg,
                                parent_id: c.parent_id || '',
                                description: c.description || '',
                                status: c.status || 'active',
                                is_featured: c.is_featured == 1,
                                sort_order: c.sort_order || 0,
                                meta_title: c.meta_title || '',
                                meta_description: c.meta_description || ''
                            };
                            this.modalOpen = true;
                        } else {
                            this.showToast('error', data.message || 'Error fetching category details.');
                        }
                    })
                    .catch(() => this.showToast('error', 'Network error fetching category details.'));
            },

            viewCategory(id) {
                fetch('<?= url('admin/categories/get/') ?>' + id, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.category) {
                            this.viewData = data.category;
                            this.viewModalOpen = true;
                        } else {
                            this.showToast('error', data.message || 'Error viewing category.');
                        }
                    })
                    .catch(() => this.showToast('error', 'Network error viewing category.'));
            },

            submitForm() {
                const catName = (this.formData && this.formData.name) ? String(this.formData.name).trim() : '';
                if (!catName) {
                    this.showToast('error', 'Category name is required.');
                    return;
                }

                this.isSaving = true;

                const formPayload = new FormData();
                formPayload.append('_csrf_token', '<?= csrf_token() ?>');
                formPayload.append('_token', '<?= csrf_token() ?>');
                formPayload.append('name', catName);
                formPayload.append('slug', (this.formData && this.formData.slug) ? String(this.formData.slug).trim() : '');
                formPayload.append('image_url', (this.formData && this.formData.image_url) ? String(this.formData.image_url).trim() : '');
                formPayload.append('parent_id', (this.formData && this.formData.parent_id) ? String(this.formData.parent_id) : '');
                formPayload.append('description', (this.formData && this.formData.description) ? String(this.formData.description).trim() : '');
                formPayload.append('status', (this.formData && this.formData.status) ? String(this.formData.status) : 'active');
                formPayload.append('sort_order', (this.formData && this.formData.sort_order) ? parseInt(this.formData.sort_order) : 0);
                formPayload.append('meta_title', (this.formData && this.formData.meta_title) ? String(this.formData.meta_title).trim() : '');
                formPayload.append('meta_description', (this.formData && this.formData.meta_description) ? String(this.formData.meta_description).trim() : '');
                if (this.formData && this.formData.is_featured) {
                    formPayload.append('is_featured', '1');
                }

                if (this.imageFile) {
                    formPayload.append('image_file', this.imageFile);
                    formPayload.append('image', this.imageFile);
                }

                const targetUrl = (this.isEditing && this.formData && this.formData.id)
                    ? '<?= url('admin/categories/update/') ?>' + this.formData.id
                    : '<?= url('admin/categories/store') ?>';

                console.log('[CategorySubmit] Target URL:', targetUrl);

                fetch(targetUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '<?= csrf_token() ?>'
                    },
                    body: formPayload
                })
                    .then(async res => {
                        const text = await res.text();
                        console.log('[CategorySubmit] Response text:', text);
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error(text || 'Server error');
                        }
                    })
                    .then(data => {
                        this.isSaving = false;
                        if (data.success) {
                            this.modalOpen = false;
                            this.showToast('success', data.message);
                            setTimeout(() => window.location.reload(), 600);
                        } else {
                            this.showToast('error', data.message || 'Error saving category.');
                        }
                    })
                    .catch(err => {
                        this.isSaving = false;
                        console.error('[CategorySubmit] Error:', err);
                        this.showToast('error', err.message || 'Network error saving category.');
                    });
            },

            openDeleteModal(id, name, productCount) {
                this.deleteData = { id, name, product_count: productCount };
                this.reassignCatId = '';
                this.deleteModalOpen = true;
            },

            confirmDelete() {
                this.isSaving = true;

                const isReassign = this.deleteData.product_count > 0;
                const targetUrl = isReassign
                    ? '<?= url('admin/categories/reassign-delete/') ?>' + this.deleteData.id
                    : '<?= url('admin/categories/delete/') ?>' + this.deleteData.id;

                const formPayload = new FormData();
                formPayload.append('_csrf_token', '<?= csrf_token() ?>');
                formPayload.append('_token', '<?= csrf_token() ?>');
                if (isReassign) {
                    formPayload.append('target_category_id', this.reassignCatId);
                }

                fetch(targetUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '<?= csrf_token() ?>'
                    },
                    body: formPayload
                })
                    .then(async res => {
                        const text = await res.text();
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            throw new Error(text || 'Server error');
                        }
                    })
                    .then(data => {
                        this.isSaving = false;
                        if (data.success) {
                            this.deleteModalOpen = false;
                            this.showToast('success', data.message);
                            setTimeout(() => window.location.reload(), 600);
                        } else {
                            this.showToast('error', data.message || 'Error deleting category.');
                        }
                    })
                    .catch(err => {
                        this.isSaving = false;
                        this.showToast('error', err.message || 'Network error deleting category.');
                    });
            }
        };
    }
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>