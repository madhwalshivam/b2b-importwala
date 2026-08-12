<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6 max-w-4xl">

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
                <span class="text-xs text-slate-500 font-medium">Top Announcement Bar</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Announcement Bar Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Edit promotional offer banner message text, action button URL, and toggle announcement visibility on
                live storefront.
            </p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-6">

        <form action="<?= url('admin/announcement/update') ?>" method="POST" class="space-y-5">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= $announcement['id'] ?? '' ?>">

            <div class="flex items-center space-x-3 bg-gray-50 p-4 rounded-xl border border-gray-900">
                <input type="checkbox" id="is_active" name="is_active" value="1" <?= !empty($announcement['is_active']) ? 'checked' : '' ?>
                    class="w-4 h-4 text-red-600 rounded focus:ring-red-500 border-gray-300 cursor-pointer">
                <label for="is_active" class="text-xs font-semibold text-gray-900 cursor-pointer">
                    Enable Top Announcement Bar on Live Storefront
                </label>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">Announcement Message</label>
                <textarea name="message" rows="3" required
                    class="w-full p-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-gray-900"><?= htmlspecialchars($announcement['message'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">CTA Button Text</label>
                    <input type="text" name="cta_text" value="<?= htmlspecialchars($announcement['cta_text'] ?? '') ?>"
                        placeholder="e.g. Shop Deals"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-gray-900">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 uppercase mb-2">CTA Button Link</label>
                    <input type="text" name="cta_link" value="<?= htmlspecialchars($announcement['cta_link'] ?? '') ?>"
                        placeholder="e.g. /shop"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-gray-900">
                </div>
            </div>

            <button type="submit"
                class="px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-xs flex items-center space-x-2">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Save Announcement Bar Settings</span>
            </button>
        </form>

    </div>

</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>