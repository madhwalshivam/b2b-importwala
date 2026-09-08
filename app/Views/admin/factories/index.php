<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="p-6 space-y-6">

    <!-- Page Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 text-[10px] font-semibold uppercase bg-orange-50 text-[#f05a29] rounded-md tracking-wider border border-orange-200">
                    Catalog &amp; Sales
                </span>
                <span class="text-gray-300 text-xs">•</span>
                <span class="text-xs text-gray-500 font-medium">Global Manufacturers Directory</span>
            </div>
            <h1 class="text-xl font-semibold text-slate-900 mt-1 tracking-tight">Factory / Manufacturer Management</h1>
        </div>

        <div class="flex items-center gap-3">
            <a href="<?= url('admin/factories/create') ?>" class="h-9 px-4 bg-[#f05a29] hover:bg-[#d8481b] text-white font-semibold text-xs rounded-xl transition shadow-sm shadow-[#f05a29]/30 flex items-center space-x-1.5 cursor-pointer shrink-0">
                <i data-lucide="plus" class="w-4 h-4 text-white"></i>
                <span>Add Factory Manually</span>
            </a>
        </div>
    </div>

    <!-- Filters & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="<?= url('admin/factories') ?>" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <div class="relative min-w-[280px]">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search Code, Name, Contact, Email..." class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-indigo-600 focus:bg-white transition">
            </div>

            <select name="status" class="py-2 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:border-indigo-600 focus:bg-white transition">
                <option value="">All Statuses</option>
                <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="archived" <?= $status === 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-900 hover:bg-black text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5">
                <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                <span>Filter</span>
            </button>

            <?php if (!empty($search) || !empty($status)): ?>
                <a href="<?= url('admin/factories') ?>" class="px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-xs rounded-xl transition">
                    Clear
                </a>
            <?php endif; ?>
        </form>

        <div class="text-xs text-slate-500 font-medium">
            Total Factories: <strong class="text-slate-900 font-bold"><?= $total ?></strong>
        </div>
    </div>

    <!-- Factories Table Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse min-w-[850px]">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4">Factory Code</th>
                        <th class="py-3.5 px-4">Manufacturer / Factory Name</th>
                        <th class="py-3.5 px-4">Contact Info</th>
                        <th class="py-3.5 px-4">Source Platform</th>
                        <th class="py-3.5 px-4 text-center">Products</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (empty($factories)): ?>
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400">
                                <i data-lucide="factory" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                <p class="font-bold text-slate-600">No factories found.</p>
                                <p class="text-xs text-slate-400">Click "Add Factory Manually" above or upload a bulk sheet with Manufacturer IDs.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($factories as $f): ?>
                            <tr class="hover:bg-slate-50/80 transition group">
                                <td class="py-3.5 px-4">
                                    <span class="font-mono font-bold text-indigo-700 bg-indigo-50 border border-indigo-200 px-2.5 py-1 rounded-lg text-xs">
                                        <?= htmlspecialchars($f['factory_code']) ?>
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    <a href="<?= url('admin/factories/show/' . $f['id']) ?>" class="font-bold text-slate-900 hover:text-indigo-600 transition block">
                                        <?= htmlspecialchars($f['name']) ?>
                                    </a>
                                    <?php if (!empty($f['store_url'])): ?>
                                        <a href="<?= htmlspecialchars($f['store_url']) ?>" target="_blank" class="text-[10px] text-slate-400 hover:text-indigo-600 truncate max-w-[200px] inline-flex items-center gap-1 mt-0.5">
                                            <i data-lucide="external-link" class="w-3 h-3"></i>
                                            <span>Original Store</span>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="space-y-0.5">
                                        <?php if (!empty($f['contact_person'])): ?>
                                            <div class="text-slate-900 font-semibold"><i data-lucide="user" class="w-3 h-3 inline text-slate-400 mr-1"></i><?= htmlspecialchars($f['contact_person']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($f['phone']) || !empty($f['whatsapp'])): ?>
                                            <div class="text-slate-500 text-[11px]"><i data-lucide="phone" class="w-3 h-3 inline text-slate-400 mr-1"></i><?= htmlspecialchars($f['phone'] ?: $f['whatsapp']) ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($f['email'])): ?>
                                            <div class="text-slate-400 text-[10px]"><i data-lucide="mail" class="w-3 h-3 inline text-slate-400 mr-1"></i><?= htmlspecialchars($f['email']) ?></div>
                                        <?php endif; ?>
                                        <?php if (empty($f['contact_person']) && empty($f['phone']) && empty($f['email'])): ?>
                                            <span class="text-slate-400 italic">No contact details</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <?php if (!empty($f['source_platform'])): ?>
                                        <span class="px-2.5 py-0.5 bg-slate-100 border border-slate-200 text-slate-700 font-semibold rounded-md text-[10px] uppercase">
                                            <?= htmlspecialchars($f['source_platform']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-slate-400">Direct</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <a href="<?= url('admin/factories/show/' . $f['id']) ?>" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 font-bold rounded-lg hover:bg-emerald-100 transition">
                                        <i data-lucide="package" class="w-3.5 h-3.5"></i>
                                        <span><?= (int)$f['product_count'] ?></span>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <?php if ($f['status'] === 'active'): ?>
                                        <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 uppercase">Active</span>
                                    <?php elseif ($f['status'] === 'inactive'): ?>
                                        <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full bg-amber-100 text-amber-800 border border-amber-300 uppercase">Inactive</span>
                                    <?php else: ?>
                                        <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full bg-slate-200 text-slate-700 border border-slate-300 uppercase">Archived</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="<?= url('factory/' . $f['factory_code']) ?>" target="_blank" class="p-1.5 bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-emerald-600 rounded-lg transition" title="Open Storefront Page on Main Website">
                                            <i data-lucide="external-link" class="w-4 h-4"></i>
                                        </a>
                                        <a href="<?= url('admin/factories/show/' . $f['id']) ?>" class="p-1.5 bg-slate-100 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 rounded-lg transition" title="View Catalog">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="<?= url('admin/factories/edit/' . $f['id']) ?>" class="p-1.5 bg-slate-100 hover:bg-amber-50 text-slate-600 hover:text-amber-600 rounded-lg transition" title="Edit Factory">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <form method="POST" action="<?= url('admin/factories/delete/' . $f['id']) ?>" onsubmit="return confirm('Are you sure you want to delete factory <?= htmlspecialchars($f['factory_code']) ?>? Linked products will become Unassigned.');" class="inline">
                                            <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
                                            <button type="submit" class="p-1.5 bg-slate-100 hover:bg-red-50 text-slate-600 hover:text-red-600 rounded-lg transition border-0 cursor-pointer" title="Delete Factory">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($paginator->hasPages()): ?>
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                <?= $paginator->render() ?>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
