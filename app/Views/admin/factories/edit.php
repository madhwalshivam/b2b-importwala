<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="p-6 max-w-4xl mx-auto space-y-6">

    <!-- Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 text-[10px] font-semibold uppercase bg-orange-50 text-[#f05a29] rounded-md tracking-wider border border-orange-200">
                    Catalog &amp; Sales
                </span>
                <span class="text-gray-300 text-xs">•</span>
                <span class="text-xs text-gray-500 font-medium">Edit Profile [<?= htmlspecialchars($factory['factory_code']) ?>]</span>
            </div>
            <h1 class="text-xl font-semibold text-slate-900 mt-1 tracking-tight">Edit Factory: <?= htmlspecialchars($factory['name']) ?></h1>
        </div>

        <div class="flex items-center gap-2">
            <a href="<?= url('factory/' . $factory['factory_code']) ?>" target="_blank" class="h-9 px-3.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold text-xs rounded-xl transition border border-emerald-200 flex items-center space-x-1.5 cursor-pointer">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Open on Main Website</span>
            </a>
            <a href="<?= url('admin/factories/show/' . $factory['id']) ?>" class="h-9 px-3.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-semibold text-xs rounded-xl transition border border-indigo-200 flex items-center space-x-1.5 cursor-pointer">
                <i data-lucide="eye" class="w-4 h-4"></i>
                <span>View Factory Catalog</span>
            </a>
            <a href="<?= url('admin/factories') ?>" class="h-9 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition border border-slate-300 flex items-center space-x-1.5 cursor-pointer shrink-0">
                <i data-lucide="arrow-left" class="w-4 h-4 text-slate-600"></i>
                <span>Back</span>
            </a>
        </div>
    </div>

    <!-- Edit Form Card -->
    <form method="POST" action="<?= url('admin/factories/update/' . $factory['id']) ?>" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Factory Code (Readonly) -->
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Factory Code</label>
                <input type="text" value="<?= htmlspecialchars($factory['factory_code']) ?>" readonly class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-200 rounded-xl text-xs font-mono font-bold text-indigo-700 cursor-not-allowed">
                <p class="text-[11px] text-slate-400 mt-1">Unique Factory Code cannot be modified.</p>
            </div>

            <!-- Factory Name -->
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Factory / Manufacturer Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="<?= htmlspecialchars($factory['name']) ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition">
            </div>

            <!-- Contact Person -->
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Contact Person</label>
                <input type="text" name="contact_person" value="<?= htmlspecialchars($factory['contact_person'] ?? '') ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition">
            </div>

            <!-- Phone Number -->
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Phone Number</label>
                <input type="text" name="phone" value="<?= htmlspecialchars($factory['phone'] ?? '') ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition">
            </div>

            <!-- WhatsApp Number -->
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">WhatsApp Number</label>
                <input type="text" name="whatsapp" value="<?= htmlspecialchars($factory['whatsapp'] ?? '') ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Email Address</label>
                <input type="email" name="email" value="<?= htmlspecialchars($factory['email'] ?? '') ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition">
            </div>

            <!-- Source Platform (Manual Text Input with Suggestions) -->
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Source Platform</label>
                <input type="text" name="source_platform" value="<?= htmlspecialchars($factory['source_platform'] ?? '') ?>" list="platform_options" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition" placeholder="e.g. 1688, Alibaba, Taobao, Direct Sourcing">
                <datalist id="platform_options">
                    <option value="1688">
                    <option value="Alibaba">
                    <option value="Taobao">
                    <option value="IndiaMART">
                    <option value="Global Sources">
                    <option value="Direct Sourcing">
                </datalist>
            </div>

            <!-- Status -->
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Status</label>
                <select name="status" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition">
                    <option value="active" <?= $factory['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $factory['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="archived" <?= $factory['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>

            <!-- Original Store/Profile URL -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Original Store / Profile URL</label>
                <input type="url" name="store_url" value="<?= htmlspecialchars($factory['store_url'] ?? '') ?>" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition">
            </div>

            <!-- Notes -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Internal Remarks / Sourcing Notes</label>
                <textarea name="notes" rows="3" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition"><?= htmlspecialchars($factory['notes'] ?? '') ?></textarea>
            </div>

        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="<?= url('admin/factories') ?>" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Update Factory Details</span>
            </button>
        </div>
    </form>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
