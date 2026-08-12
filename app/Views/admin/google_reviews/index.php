<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="p-6 max-w-7xl mx-auto space-y-6 font-sans">

    <!-- Top Header -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span
                    class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-red-50 text-red-600 rounded-lg tracking-wider border border-red-100">
                    Google Reviews
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Customer Social Proof</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Google Customer Reviews Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Manage authentic customer Google Reviews shown in the "Google Backed Trust in Every Order" section on
                the storefront homepage.
            </p>
        </div>

        <button type="button" onclick="document.getElementById('add-review-modal').classList.remove('hidden')"
            class="px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-xs flex items-center space-x-2 cursor-pointer">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add Google Review</span>
        </button>
    </div>

    <!-- Reviews Grid -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-base font-semibold text-slate-900 flex items-center space-x-2">
                <i data-lucide="star" class="w-5 h-5 text-amber-500 fill-amber-500"></i>
                <span>Active Google Reviews (<?= count($reviews) ?>)</span>
            </h3>
        </div>

        <?php if (empty($reviews)): ?>
            <div class="p-12 text-center space-y-3">
                <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto">
                    <i data-lucide="message-square-x" class="w-8 h-8"></i>
                </div>
                <p class="text-sm font-semibold text-slate-700">No Google Reviews Added Yet</p>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">Add verified customer reviews to showcase social proof on
                    your homepage.</p>
                <button type="button" onclick="document.getElementById('add-review-modal').classList.remove('hidden')"
                    class="px-4 py-2 bg-amber-600 text-white text-xs font-semibold rounded-xl hover:bg-amber-700 transition">
                    + Add First Google Review
                </button>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                <?php foreach ($reviews as $r): ?>
                    <div
                        class="bg-slate-50 rounded-2xl border border-slate-200 p-5 flex flex-col justify-between space-y-4 shadow-xs relative group hover:border-slate-300 transition">
                        <div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <?php if ($r['photo_path']): ?>
                                        <img src="<?= asset($r['photo_path']) ?>"
                                            class="w-10 h-10 rounded-full object-cover border border-slate-200">
                                    <?php else: ?>
                                        <div
                                            class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 text-white font-semibold text-sm flex items-center justify-center shadow-xs">
                                            <?= htmlspecialchars(substr($r['customer_name'], 0, 1)) ?>
                                        </div>
                                    <?php endif; ?>
                                    <div>
                                        <h4 class="text-xs font-semibold text-slate-900">
                                            <?= htmlspecialchars($r['customer_name']) ?>
                                        </h4>
                                        <p class="text-[10px] text-slate-400 font-medium">
                                            <?= htmlspecialchars($r['review_date'] ?: date('Y-m-d')) ?>
                                        </p>
                                    </div>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24">
                                    <path fill="#4285F4"
                                        d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" />
                                    <path fill="#34A853"
                                        d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" />
                                    <path fill="#FBBC05"
                                        d="M5.84 14.1c-.22-.66-.35-1.36-.35-2.1s.13-1.44.35-2.1V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.62z" />
                                    <path fill="#EA4335"
                                        d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z" />
                                </svg>
                            </div>

                            <div class="flex text-amber-400 text-xs items-center space-x-1 mt-3">
                                <?php for ($i = 0; $i < (int) $r['rating']; $i++): ?>
                                    <i data-lucide="star" class="w-3.5 h-3.5 fill-amber-400 text-amber-400"></i>
                                <?php endfor; ?>
                                <?php if ($r['is_verified']): ?>
                                    <span
                                        class="ml-1.5 px-1.5 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-semibold rounded flex items-center space-x-0.5">
                                        <i data-lucide="check-circle-2" class="w-3 h-3 text-blue-600"></i>
                                        <span>Verified</span>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <p class="text-xs text-slate-700 font-medium mt-2 leading-relaxed">
                                "<?= htmlspecialchars($r['review_text']) ?>"
                            </p>
                        </div>

                        <div class="pt-3 border-t border-slate-200/60 flex items-center justify-between">
                            <span class="text-[11px] font-mono text-slate-400">Order: #<?= (int) $r['display_order'] ?></span>
                            <a href="<?= url('admin/google-reviews/delete/' . $r['id']) ?>"
                                data-confirm="Are you sure you want to delete this review?"
                                class="p-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition"
                                title="Delete Review">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add Review Modal -->
<div id="add-review-modal"
    class="hidden fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-[99999] flex items-center justify-center p-3 sm:p-6 overflow-y-auto">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5 border border-slate-200 my-auto z-[100000] max-h-[90vh] sm:max-h-[85vh] overflow-y-auto flex flex-col">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-semibold text-slate-900 flex items-center space-x-2">
                <i data-lucide="star" class="w-5 h-5 text-amber-500 fill-amber-500"></i>
                <span>Add Google Customer Review</span>
            </h3>
            <button type="button" onclick="document.getElementById('add-review-modal').classList.add('hidden'); document.body.classList.remove('modal-open');"
                class="text-slate-400 hover:text-slate-700 transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="<?= url('admin/google-reviews/store') ?>" method="POST" enctype="multipart/form-data"
            class="space-y-4">
            <?= csrf_field() ?>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase">Customer Name</label>
                <input type="text" name="customer_name" placeholder="e.g. Harkeet Singh" required
                    class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-slate-900">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700 uppercase">Star Rating</label>
                    <select name="rating"
                        class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900 focus:bg-white focus:outline-none focus:border-slate-900">
                        <option value="5" selected>5 Stars (★★★★★)</option>
                        <option value="4">4 Stars (★★★★☆)</option>
                        <option value="3">3 Stars (★★★☆☆)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-semibold text-slate-700 uppercase">Review Date</label>
                    <input type="date" name="review_date" value="<?= date('Y-m-d') ?>"
                        class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-900">
                </div>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase">Review Text</label>
                <textarea name="review_text" rows="3" placeholder="Customer review message..." required
                    class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:bg-white focus:outline-none focus:border-slate-900"></textarea>
            </div>

            <div class="space-y-1">
                <label class="block text-xs font-semibold text-slate-700 uppercase">Customer Photo / Avatar
                    (Optional)</label>
                <input type="file" name="photo" accept="image/*"
                    class="w-full p-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700">
            </div>

            <div class="flex items-center space-x-6 pt-1">
                <label class="inline-flex items-center space-x-2 text-xs font-semibold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="is_verified" value="1" checked
                        class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4">
                    <span>Show Verified Customer Badge</span>
                </label>

                <div class="flex items-center space-x-2">
                    <label class="text-xs font-semibold text-slate-700">Sort Order:</label>
                    <input type="number" name="display_order" value="1" min="0"
                        class="w-20 h-9 px-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-900">
                </div>
            </div>

            <div class="pt-3 flex justify-end space-x-3 border-t border-slate-100">
                <button type="button" onclick="document.getElementById('add-review-modal').classList.add('hidden')"
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl transition shadow-xs">
                    Save Review
                </button>
            </div>
        </form>
    </div>
</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>