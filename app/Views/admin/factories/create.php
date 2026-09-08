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
                <span class="text-xs text-gray-500 font-medium">Verified Manufacturers</span>
            </div>
            <h1 class="text-xl font-semibold text-slate-900 mt-1 tracking-tight">Add New Factory / Manufacturer</h1>
        </div>

        <a href="<?= url('admin/factories') ?>" class="h-9 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition border border-slate-300 flex items-center space-x-1.5 cursor-pointer shrink-0">
            <i data-lucide="arrow-left" class="w-4 h-4 text-slate-600"></i>
            <span>Back to Factories</span>
        </a>
    </div>

    <!-- Create Form Card -->
    <form method="POST" action="<?= url('admin/factories/store') ?>" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-6">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Factory Code -->
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Factory Code <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" name="factory_code" value="<?= htmlspecialchars($nextCode) ?>" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-bold text-indigo-700 focus:outline-none focus:border-indigo-600 focus:bg-white transition" placeholder="e.g. FCT-001">
                </div>
                <p class="text-[11px] text-slate-400 mt-1">Auto-generated sequential code. Never reused if deleted.</p>
            </div>

            <!-- Factory Name -->
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Factory / Manufacturer Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition" placeholder="e.g. Yiwu Jewelry Corp / Guangzhou Leather Co.">
            </div>

            <!-- Contact Person -->
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Contact Person</label>
                <input type="text" name="contact_person" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition" placeholder="e.g. Mr. Chen / John Smith">
            </div>

            <!-- Phone Number -->
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Phone Number</label>
                <input type="text" name="phone" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition" placeholder="+86 138 0000 0000">
            </div>

            <!-- WhatsApp Number -->
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">WhatsApp Number</label>
                <input type="text" name="whatsapp" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition" placeholder="+86 138 0000 0000">
            </div>

            <!-- Email -->
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Email Address</label>
                <input type="email" name="email" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition" placeholder="sales@manufacturer.com">
            </div>

            <!-- Source Platform (Manual Text Input with Suggestions) -->
            <div>
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Source Platform</label>
                <input type="text" name="source_platform" list="platform_options" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition" placeholder="e.g. 1688, Alibaba, Taobao, Direct Sourcing">
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
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="archived">Archived</option>
                </select>
            </div>

            <!-- Original Store/Profile URL -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Original Store / Profile URL</label>
                <input type="url" name="store_url" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition" placeholder="https://shop12345.1688.com or https://company.en.alibaba.com">
            </div>

            <!-- Notes -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-slate-800 uppercase mb-1.5">Internal Remarks / Sourcing Notes</label>
                <textarea name="notes" rows="3" class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition" placeholder="Internal remarks about MOQ negotiations, payment terms, or factory contacts..."></textarea>
            </div>

        </div>

        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
            <a href="<?= url('admin/factories') ?>" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition">
                Cancel
            </a>
            <button type="submit" class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center gap-2">
                <i data-lucide="check" class="w-4 h-4"></i>
                <span>Save Factory Profile</span>
            </button>
        </div>
    </form>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
