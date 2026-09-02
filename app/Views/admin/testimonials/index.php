<?php
include __DIR__ . '/../layouts/header.php';

$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="p-6 max-w-7xl mx-auto space-y-6 font-sans">

    <!-- Top Header Bar -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span
                    class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-emerald-50 text-emerald-600 rounded-lg tracking-wider border border-emerald-100">
                    Everful Style
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Customer Reviews & Testimonials</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Testimonials Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Manage customer buyer reviews displayed in the "Effortless sourcing the ImportWale way" section on the
                storefront homepage and dedicated <code
                    class="text-emerald-700 font-mono bg-emerald-50 px-1.5 py-0.5 rounded">/reviews</code> page.
            </p>
        </div>

        <button type="button" onclick="openAddModal()"
            class="px-5 py-2.5 bg-[#f05a29] hover:bg-[#d84b1d] text-white font-semibold text-xs rounded-xl transition shadow-xs flex items-center space-x-2 cursor-pointer">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add New Review</span>
        </button>
    </div>

    <!-- Flash Notifications -->
    <?php if ($flashSuccess): ?>
        <div
            class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-xl flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span><?= htmlspecialchars($flashSuccess) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($flashError): ?>
        <div
            class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold rounded-xl flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                <span><?= htmlspecialchars($flashError) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Testimonials List Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-900 flex items-center space-x-2">
                <i data-lucide="message-square-quote" class="w-5 h-5 text-[#f05a29]"></i>
                <span>All Customer Reviews (<?= count($testimonials) ?>)</span>
            </h3>
        </div>

        <?php if (empty($testimonials)): ?>
            <div class="p-12 text-center space-y-3">
                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto">
                    <i data-lucide="message-square-x" class="w-8 h-8"></i>
                </div>
                <p class="text-sm font-semibold text-slate-700">No Testimonials Added Yet</p>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">Add verified buyer reviews to showcase trust on your
                    homepage.</p>
                <button type="button" onclick="openAddModal()"
                    class="px-4 py-2 bg-[#f05a29] text-white text-xs font-semibold rounded-xl hover:bg-[#d84b1d] transition">
                    + Add First Review
                </button>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs font-medium text-slate-600 border-collapse">
                    <thead
                        class="bg-slate-50 text-slate-700 uppercase tracking-wider text-[10px] font-semibold border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-4">Reviewer & Avatar</th>
                            <th class="py-3.5 px-4">Location</th>
                            <th class="py-3.5 px-4">Rating</th>
                            <th class="py-3.5 px-4">Review Snippet</th>
                            <th class="py-3.5 px-4">Linked Product</th>
                            <th class="py-3.5 px-4 text-center">Featured</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-center">Order</th>
                            <th class="py-3.5 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($testimonials as $item): ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <!-- Reviewer & Avatar -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center space-x-3">
                                        <?php if (!empty($item['photo_path'])): ?>
                                            <img src="<?= url(ltrim($item['photo_path'], '/')) ?>"
                                                alt="<?= htmlspecialchars($item['reviewer_name']) ?>"
                                                class="w-10 h-10 rounded-full object-cover border border-slate-200 shadow-xs shrink-0">
                                        <?php else: ?>
                                            <div
                                                class="w-9 h-9 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center border border-slate-200 shrink-0">
                                                <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="font-semibold text-slate-900">
                                                <?= htmlspecialchars($item['reviewer_name']) ?></div>
                                            <div class="text-[10px] text-slate-400">ID: #<?= $item['id'] ?></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Location -->
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    <?= htmlspecialchars($item['location']) ?>
                                </td>

                                <!-- Rating Stars -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center space-x-0.5">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <span
                                                class="w-4 h-4 rounded text-[9px] font-semibold inline-flex items-center justify-center text-white <?= $s <= $item['rating'] ? 'bg-[#F59E0B]' : 'bg-slate-200 text-slate-400' ?>">★</span>
                                        <?php endfor; ?>
                                    </div>
                                </td>

                                <!-- Snippet -->
                                <td class="py-3.5 px-4 max-w-xs">
                                    <p class="text-xs text-slate-600 line-clamp-2 leading-relaxed">
                                        "<?= htmlspecialchars($item['review_text']) ?>"
                                    </p>
                                </td>

                                <!-- Linked Product -->
                                <td class="py-3.5 px-4">
                                    <?php if (!empty($item['product_name'])): ?>
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-100 max-w-[140px] truncate"
                                            title="<?= htmlspecialchars($item['product_name']) ?>">
                                            <i data-lucide="package" class="w-3 h-3 mr-1 text-blue-500 shrink-0"></i>
                                            <span class="truncate"><?= htmlspecialchars($item['product_name']) ?></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400 text-[11px] italic">General Review</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Featured Toggle -->
                                <td class="py-3.5 px-4 text-center">
                                    <a href="<?= url('admin/testimonials/toggle-featured/' . $item['id']) ?>"
                                        class="px-2.5 py-1 rounded-full text-[10px] font-semibold transition inline-flex items-center space-x-1 <?= $item['is_featured'] ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-500 border border-slate-200' ?>">
                                        <i data-lucide="star"
                                            class="w-3 h-3 <?= $item['is_featured'] ? 'fill-amber-500 text-amber-500' : '' ?>"></i>
                                        <span><?= $item['is_featured'] ? 'Homepage' : 'Hidden' ?></span>
                                    </a>
                                </td>

                                <!-- Status Toggle -->
                                <td class="py-3.5 px-4 text-center">
                                    <a href="<?= url('admin/testimonials/toggle-status/' . $item['id']) ?>"
                                        class="px-2.5 py-1 rounded-full text-[10px] font-semibold transition inline-flex items-center space-x-1 <?= $item['status'] === 'active' ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-rose-100 text-rose-800 border border-rose-200' ?>">
                                        <span
                                            class="w-1.5 h-1.5 rounded-full <?= $item['status'] === 'active' ? 'bg-emerald-600' : 'bg-rose-600' ?>"></span>
                                        <span class="capitalize"><?= $item['status'] ?></span>
                                    </a>
                                </td>

                                <!-- Order -->
                                <td class="py-3.5 px-4 text-center font-mono text-slate-700 font-semibold">
                                    <?= (int) $item['display_order'] ?>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button type="button"
                                            onclick='openEditModal(<?= json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'
                                            class="p-1.5 text-slate-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition"
                                            title="Edit Review">
                                            <i data-lucide="pencil" class="w-4 h-4"></i>
                                        </button>

                                        <a href="<?= url('admin/testimonials/delete/' . $item['id']) ?>"
                                            onclick="return confirm('Are you sure you want to delete this review?')"
                                            class="p-1.5 text-slate-600 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition"
                                            title="Delete Review">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<!-- Modal: Add Review -->
<div id="addModal"
    class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden my-8">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
            <h3 class="text-sm font-semibold flex items-center space-x-2">
                <i data-lucide="message-square-plus" class="w-4 h-4 text-[#00B67A]"></i>
                <span>Add Customer Testimonial</span>
            </h3>
            <button type="button" onclick="closeAddModal()" class="text-slate-400 hover:text-white transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="<?= url('admin/testimonials/store') ?>" method="POST" enctype="multipart/form-data"
            class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Reviewer Name -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Reviewer Name <span
                            class="text-rose-500">*</span></label>
                    <input type="text" name="reviewer_name" required placeholder="e.g. Wampler, Dan, Rahul M."
                        class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-[#f05a29] focus:outline-none">
                </div>

                <!-- Country/City Location -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Country / City <span
                            class="text-rose-500">*</span></label>
                    <input type="text" name="location" required placeholder="e.g. United States, Delhi India, UK"
                        class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-[#f05a29] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Star Rating -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Star Rating (1 - 5)</label>
                    <select name="rating"
                        class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-[#f05a29] focus:outline-none">
                        <option value="5" selected>★★★★★ 5 Stars (Excellent)</option>
                        <option value="4">★★★★☆ 4 Stars (Very Good)</option>
                        <option value="3">★★★☆☆ 3 Stars (Good)</option>
                        <option value="2">★★☆☆☆ 2 Stars (Average)</option>
                        <option value="1">★☆☆☆☆ 1 Star (Poor)</option>
                    </select>
                </div>

                <!-- Display Order -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Display Order</label>
                    <input type="number" name="display_order" value="0" min="0"
                        class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-[#f05a29] focus:outline-none">
                </div>
            </div>

            <!-- Review Text -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Review Text / Comment <span
                        class="text-rose-500">*</span></label>
                <textarea name="review_text" rows="4" required
                    placeholder="Enter buyer feedback or testimonial snippet..."
                    class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-[#f05a29] focus:outline-none"></textarea>
            </div>

            <!-- Reviewer Photo (Optional) -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Reviewer Photo (Optional)</label>
                <input type="file" name="photo" accept="image/*"
                    class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
                <p class="text-[11px] text-slate-400 mt-1">If no photo is uploaded, a colored circular avatar with the
                    initial letter will be auto-generated (like Everful).</p>
            </div>

            <!-- Optional Linked Product -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Link to Product (Optional)</label>
                <select name="product_id"
                    class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-[#f05a29] focus:outline-none">
                    <option value="">-- None (General Homepage Review) --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (SKU:
                            <?= htmlspecialchars($p['sku']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Toggles Row -->
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200">
                <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_featured" value="1" checked
                        class="w-4 h-4 rounded text-[#f05a29] focus:ring-[#f05a29]">
                    <span>Feature on Homepage (Everful Section)</span>
                </label>

                <div class="flex items-center space-x-2">
                    <span class="text-xs font-semibold text-slate-700">Status:</span>
                    <select name="status" class="px-2.5 py-1 border border-slate-300 rounded-lg text-xs font-medium">
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Modal Footer Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" onclick="closeAddModal()"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-[#f05a29] hover:bg-[#d84b1d] text-white font-semibold text-xs rounded-xl transition shadow-xs">
                    Save Review
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edit Review -->
<div id="editModal"
    class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white w-full max-w-xl rounded-2xl shadow-2xl border border-slate-200 overflow-hidden my-8">
        <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
            <h3 class="text-sm font-semibold flex items-center space-x-2">
                <i data-lucide="pencil" class="w-4 h-4 text-[#00B67A]"></i>
                <span>Edit Customer Testimonial</span>
            </h3>
            <button type="button" onclick="closeEditModal()" class="text-slate-400 hover:text-white transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form id="editForm" action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-4">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Reviewer Name -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Reviewer Name <span
                            class="text-rose-500">*</span></label>
                    <input type="text" id="edit_reviewer_name" name="reviewer_name" required
                        class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-[#f05a29] focus:outline-none">
                </div>

                <!-- Country/City Location -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Country / City <span
                            class="text-rose-500">*</span></label>
                    <input type="text" id="edit_location" name="location" required
                        class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-[#f05a29] focus:outline-none">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Star Rating -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Star Rating (1 - 5)</label>
                    <select id="edit_rating" name="rating"
                        class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-[#f05a29] focus:outline-none">
                        <option value="5">★★★★★ 5 Stars (Excellent)</option>
                        <option value="4">★★★★☆ 4 Stars (Very Good)</option>
                        <option value="3">★★★☆☆ 3 Stars (Good)</option>
                        <option value="2">★★☆☆☆ 2 Stars (Average)</option>
                        <option value="1">★☆☆☆☆ 1 Star (Poor)</option>
                    </select>
                </div>

                <!-- Display Order -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Display Order</label>
                    <input type="number" id="edit_display_order" name="display_order" min="0"
                        class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-[#f05a29] focus:outline-none">
                </div>
            </div>

            <!-- Review Text -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Review Text / Comment <span
                        class="text-rose-500">*</span></label>
                <textarea id="edit_review_text" name="review_text" rows="4" required
                    class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-[#f05a29] focus:outline-none"></textarea>
            </div>

            <!-- Reviewer Photo -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Reviewer Photo</label>
                <div id="currentPhotoContainer" class="mb-2 hidden flex items-center space-x-3">
                    <img id="edit_photo_preview" src=""
                        class="w-10 h-10 rounded-full object-cover border border-slate-200">
                    <label class="flex items-center space-x-1.5 text-xs text-rose-600 cursor-pointer">
                        <input type="checkbox" name="remove_photo" value="1"
                            class="rounded text-rose-600 focus:ring-rose-500">
                        <span>Remove current photo</span>
                    </label>
                </div>
                <input type="file" name="photo" accept="image/*"
                    class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
            </div>

            <!-- Optional Linked Product -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Link to Product (Optional)</label>
                <select id="edit_product_id" name="product_id"
                    class="w-full px-3.5 py-2 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-[#f05a29] focus:outline-none">
                    <option value="">-- None (General Homepage Review) --</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (SKU:
                            <?= htmlspecialchars($p['sku']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Toggles Row -->
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-200">
                <label class="flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                    <input type="checkbox" id="edit_is_featured" name="is_featured" value="1"
                        class="w-4 h-4 rounded text-[#f05a29] focus:ring-[#f05a29]">
                    <span>Feature on Homepage</span>
                </label>

                <div class="flex items-center space-x-2">
                    <span class="text-xs font-semibold text-slate-700">Status:</span>
                    <select id="edit_status" name="status"
                        class="px-2.5 py-1 border border-slate-300 rounded-lg text-xs font-medium">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
            </div>

            <!-- Modal Footer Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" onclick="closeEditModal()"
                    class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 bg-[#f05a29] hover:bg-[#d84b1d] text-white font-semibold text-xs rounded-xl transition shadow-xs">
                    Update Review
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openAddModal() {
        document.getElementById('addModal').classList.remove('hidden');
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.add('hidden');
    }

    function openEditModal(item) {
        const form = document.getElementById('editForm');
        form.action = '<?= url("admin/testimonials/update/") ?>' + item.id;

        document.getElementById('edit_reviewer_name').value = item.reviewer_name || '';
        document.getElementById('edit_location').value = item.location || '';
        document.getElementById('edit_rating').value = item.rating || 5;
        document.getElementById('edit_display_order').value = item.display_order || 0;
        document.getElementById('edit_review_text').value = item.review_text || '';
        document.getElementById('edit_product_id').value = item.product_id || '';
        document.getElementById('edit_status').value = item.status || 'active';
        document.getElementById('edit_is_featured').checked = (item.is_featured == 1);

        const photoContainer = document.getElementById('currentPhotoContainer');
        const photoPreview = document.getElementById('edit_photo_preview');
        if (item.photo_path) {
            photoPreview.src = '<?= url("") ?>' + item.photo_path.replace(/^\//, '');
            photoContainer.classList.remove('hidden');
        } else {
            photoContainer.classList.add('hidden');
        }

        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>