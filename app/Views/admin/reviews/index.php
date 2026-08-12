<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6 font-sans" x-data="{ addModal: false }"
    x-effect="document.body.classList.toggle('modal-open', addModal)">

    <!-- Page Header & Title Strip -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 flex items-center gap-2">
                <i data-lucide="star" class="w-6 h-6 text-amber-500 fill-amber-500"></i>
                <span>Customer Reviews &amp; Ratings Management</span>
            </h1>
            <p class="text-xs text-gray-500 mt-1">Add, review, approve, or delete customer product reviews. Ratings
                automatically recalculate in real-time.</p>
        </div>

        <button type="button" @click="addModal = true"
            class="px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition shadow-xs flex items-center justify-center space-x-2 cursor-pointer shrink-0">
            <i data-lucide="plus-circle" class="w-4 h-4"></i>
            <span>Add New Product Review</span>
        </button>
    </div>

    <!-- Flash Messages Notification -->
    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div
            class="mb-6 p-4 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-xl text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span><?= htmlspecialchars($_SESSION['flash_success']) ?></span>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_error'])): ?>
        <div
            class="mb-6 p-4 bg-red-50 text-red-800 border border-red-200 rounded-xl text-xs font-semibold flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <i data-lucide="alert-triangle" class="w-4 h-4 text-red-600"></i>
                <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        </div>
    <?php endif; ?>

    <!-- Reviews Datatable Card -->
    <div class="bg-white rounded-2xl border border-gray-900 shadow-xs overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-700">
                <thead class="bg-gray-50 text-gray-900 uppercase font-semibold text-[11px] border-b border-gray-900">
                    <tr>
                        <th class="px-5 py-3.5">Product</th>
                        <th class="px-5 py-3.5">Customer</th>
                        <th class="px-5 py-3.5">Rating</th>
                        <th class="px-5 py-3.5">Review Title &amp; Comment</th>
                        <th class="px-5 py-3.5">Status &amp; Verified</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (!empty($reviews)): ?>
                        <?php foreach ($reviews as $rev): ?>
                            <tr class="hover:bg-gray-50/80 transition">
                                <td class="px-5 py-4 font-semibold text-gray-900 max-w-[200px]">
                                    <div class="flex items-center space-x-2.5">
                                        <?php if (!empty($rev['main_image'])): ?>
                                            <img src="<?= asset($rev['main_image']) ?>"
                                                class="w-9 h-9 rounded-lg object-cover border border-gray-900 shrink-0">
                                        <?php endif; ?>
                                        <div class="truncate">
                                            <p class="truncate font-semibold text-gray-900">
                                                <?= htmlspecialchars($rev['product_name'] ?? 'Product #' . $rev['product_id']) ?>
                                            </p>
                                            <a href="<?= url('product/' . ($rev['product_slug'] ?? '')) ?>" target="_blank"
                                                class="text-[10px] text-red-600 hover:underline">View Product &rarr;</a>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-5 py-4 font-medium text-gray-800">
                                    <p class="font-semibold text-gray-900"><?= htmlspecialchars($rev['customer_name']) ?></p>
                                    <span
                                        class="text-[10px] text-gray-400"><?= date('d M Y, h:i A', strtotime($rev['created_at'])) ?></span>
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex text-amber-400">
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <i data-lucide="star"
                                                class="w-3.5 h-3.5 <?= $s <= (int) $rev['rating'] ? 'text-amber-400 fill-amber-400' : 'text-gray-900' ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span
                                        class="text-[10px] font-semibold text-gray-600 mt-0.5 block"><?= (int) $rev['rating'] ?>
                                        / 5 Stars</span>
                                </td>

                                <td class="px-5 py-4 max-w-sm">
                                    <?php if (!empty($rev['title'])): ?>
                                        <p class="font-semibold text-gray-900 text-xs mb-0.5"><?= htmlspecialchars($rev['title']) ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="text-gray-600 text-xs line-clamp-2"><?= htmlspecialchars($rev['comment']) ?></p>
                                </td>

                                <td class="px-5 py-4">
                                    <form action="<?= url('admin/reviews/update-status/' . $rev['id']) ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <select name="status" onchange="this.form.submit()"
                                            class="text-[11px] w-full font-semibold rounded-lg px-2.5 py-1 border cursor-pointer focus:outline-none <?= $rev['status'] === 'approved' ? 'bg-emerald-50 text-emerald-800 border-emerald-300' : ($rev['status'] === 'rejected' ? 'bg-red-50 text-red-800 border-red-300' : 'bg-amber-50 text-amber-800 border-amber-300') ?>">
                                            <option value="approved" <?= $rev['status'] === 'approved' ? 'selected' : '' ?>>
                                                Approved</option>
                                            <option value="pending" <?= $rev['status'] === 'pending' ? 'selected' : '' ?>>Pending
                                            </option>
                                            <option value="rejected" <?= $rev['status'] === 'rejected' ? 'selected' : '' ?>>
                                                Rejected</option>
                                        </select>
                                    </form>
                                    <form action="<?= url('admin/reviews/update-verified/' . $rev['id']) ?>" method="POST"
                                        class="flex items-center space-x-1.5 mt-3">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="is_verified" value="0">
                                        <input type="checkbox" name="is_verified" value="1" id="verified_<?= $rev['id'] ?>"
                                            onchange="this.form.submit()" <?= !empty($rev['is_verified']) ? 'checked' : '' ?>
                                            class="w-3.5 h-3.5 text-red-600 border-gray-300 rounded focus:ring-red-600">
                                        <label for="verified_<?= $rev['id'] ?>"
                                            class="text-[11px] font-semibold text-gray-700 cursor-pointer">Verified
                                            Buyer</label>
                                    </form>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <a href="<?= url('admin/reviews/delete/' . $rev['id']) ?>"
                                        data-confirm="Are you sure you want to delete this review? Product rating will recalculate automatically."
                                        class="p-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg inline-flex items-center transition"
                                        title="Delete Review">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-12 text-gray-500 font-semibold">
                                No customer reviews found. Click "Add New Product Review" above to create one.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: ADD MANUAL REVIEW -->
    <div x-show="addModal"
        class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-[99999] flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
        x-cloak>
        <div class="relative w-full max-w-lg bg-white rounded-2xl border border-gray-900 shadow-2xl p-6 space-y-4 my-auto z-[100000] max-h-[90vh] sm:max-h-[85vh] overflow-y-auto flex flex-col"
            @click.outside="addModal = false">

            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-semibold text-gray-900 flex items-center space-x-2">
                    <i data-lucide="plus-circle" class="w-5 h-5 text-red-600"></i>
                    <span>Add Customer Review (Admin)</span>
                </h3>
                <button type="button" @click="addModal = false" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="<?= url('admin/reviews/store') ?>" method="POST" class="space-y-4">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Select Product *</label>
                    <select name="product_id" required
                        class="w-full h-10 px-3 bg-gray-50 border border-gray-300 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                        <option value="">-- Choose Product --</option>
                        <?php foreach ($products as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (SKU:
                                <?= htmlspecialchars($p['sku'] ?? 'N/A') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Customer Name *</label>
                        <input type="text" name="customer_name" required placeholder="e.g. Vikram Singh"
                            class="w-full h-10 px-3 bg-gray-50 border border-gray-300 rounded-xl text-xs text-gray-900 focus:outline-none focus:border-red-600">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Star Rating *</label>
                        <select name="rating" required
                            class="w-full h-10 px-3 bg-gray-50 border border-gray-300 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                            <option value="5">★★★★★ (5 Stars)</option>
                            <option value="4">★★★★☆ (4 Stars)</option>
                            <option value="3">★★★☆☆ (3 Stars)</option>
                            <option value="2">★★☆☆☆ (2 Stars)</option>
                            <option value="1">★☆☆☆☆ (1 Star)</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Review Headline / Title</label>
                    <input type="text" name="title" placeholder="e.g. Highly durable, easy to install"
                        class="w-full h-10 px-3 bg-gray-50 border border-gray-300 rounded-xl text-xs text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Review Comment *</label>
                    <textarea name="comment" required rows="3" placeholder="Enter customer feedback..."
                        class="w-full p-3 bg-gray-50 border border-gray-300 rounded-xl text-xs text-gray-900 focus:outline-none focus:border-red-600"></textarea>
                </div>

                <div class="flex items-center space-x-2 pt-2">
                    <input type="checkbox" name="is_verified" value="1" id="add_is_verified" checked
                        class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 cursor-pointer">
                    <label for="add_is_verified"
                        class="text-xs font-semibold text-gray-700 select-none cursor-pointer">Show Verified Customer
                        Badge</label>
                </div>

                <div class="pt-2 flex justify-end space-x-3">
                    <button type="button" @click="addModal = false"
                        class="px-4 py-2.5 bg-gray-100 text-gray-700 text-xs font-semibold rounded-xl hover:bg-gray-900 transition">Cancel</button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">Save
                        &amp; Recalculate Rating</button>
                </div>
            </form>

        </div>
    </div>
</div>

</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>