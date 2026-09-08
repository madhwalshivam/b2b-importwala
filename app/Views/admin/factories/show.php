<?php require __DIR__ . '/../layouts/header.php'; ?>

<div class="p-6 space-y-6" x-data="{ showAssignModal: false, filterText: '' }">

    <!-- Factory Header Card -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs p-5 space-y-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2.5 py-0.5 text-[10px] font-semibold uppercase bg-orange-50 text-[#f05a29] rounded-md tracking-wider border border-orange-200">
                        Catalog &amp; Sales
                    </span>
                    <span class="text-gray-300 text-xs">•</span>
                    <span class="text-xs text-gray-500 font-medium">Manufacturer Profile</span>
                    <span class="text-gray-300 text-xs">•</span>
                    <span class="font-mono font-bold text-slate-700 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded text-[11px]"><?= htmlspecialchars($factory['factory_code']) ?></span>
                    <?php if ($factory['status'] === 'active'): ?>
                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 uppercase">Active</span>
                    <?php elseif ($factory['status'] === 'inactive'): ?>
                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-amber-100 text-amber-800 border border-amber-300 uppercase">Inactive</span>
                    <?php else: ?>
                        <span class="px-2.5 py-0.5 text-[10px] font-semibold rounded-full bg-slate-200 text-slate-700 border border-slate-300 uppercase">Archived</span>
                    <?php endif; ?>
                </div>

                <h1 class="text-xl font-semibold text-slate-900 mt-1 tracking-tight">
                    <?= htmlspecialchars($factory['name']) ?>
                </h1>

                <?php if (!empty($factory['store_url'])): ?>
                    <a href="<?= htmlspecialchars($factory['store_url']) ?>" target="_blank" class="text-xs text-[#f05a29] hover:underline inline-flex items-center gap-1 mt-1 font-semibold">
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        <span>View Original Manufacturer Store Profile</span>
                    </a>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <a href="<?= url('factory/' . $factory['factory_code']) ?>" target="_blank" class="h-9 px-3.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold text-xs rounded-xl transition border border-emerald-200 flex items-center space-x-1.5 cursor-pointer">
                    <i data-lucide="external-link" class="w-4 h-4"></i>
                    <span>Open on Main Website</span>
                </a>
                <button type="button" @click="showAssignModal = true" class="h-9 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl transition shadow-sm flex items-center space-x-1.5 cursor-pointer border-0">
                    <i data-lucide="plus-circle" class="w-4 h-4"></i>
                    <span>Link Products to Factory</span>
                </button>
                <a href="<?= url('admin/factories/edit/' . $factory['id']) ?>" class="h-9 px-4 bg-[#f05a29] hover:bg-[#d8481b] text-white font-semibold text-xs rounded-xl transition shadow-sm shadow-[#f05a29]/30 flex items-center space-x-1.5 cursor-pointer">
                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                    <span>Edit Profile</span>
                </a>
                <a href="<?= url('admin/factories') ?>" class="h-9 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition border border-slate-300 flex items-center space-x-1.5 cursor-pointer shrink-0">
                    <i data-lucide="arrow-left" class="w-4 h-4 text-slate-600"></i>
                    <span>Back</span>
                </a>
            </div>
        </div>

        <!-- Contact & Profile Metadata Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs font-medium text-slate-700">
            <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl">
                <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Contact Person</div>
                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                    <i data-lucide="user" class="w-3.5 h-3.5 text-[#f05a29]"></i>
                    <span><?= htmlspecialchars($factory['contact_person'] ?: 'N/A') ?></span>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl">
                <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Phone / WhatsApp</div>
                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                    <i data-lucide="phone" class="w-3.5 h-3.5 text-[#f05a29]"></i>
                    <span><?= htmlspecialchars($factory['phone'] ?: ($factory['whatsapp'] ?: 'N/A')) ?></span>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl">
                <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Email Address</div>
                <div class="font-bold text-slate-900 flex items-center gap-1.5 truncate">
                    <i data-lucide="mail" class="w-3.5 h-3.5 text-[#f05a29]"></i>
                    <span class="truncate"><?= htmlspecialchars($factory['email'] ?: 'N/A') ?></span>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50 border border-slate-100 rounded-xl">
                <div class="text-[10px] uppercase font-bold text-slate-400 mb-1">Source Platform</div>
                <div class="font-bold text-slate-900 flex items-center gap-1.5">
                    <i data-lucide="globe" class="w-3.5 h-3.5 text-[#f05a29]"></i>
                    <span><?= htmlspecialchars($factory['source_platform'] ?: 'Direct Sourcing') ?></span>
                </div>
            </div>
        </div>

        <?php if (!empty($factory['notes'])): ?>
            <div class="p-3.5 bg-amber-50/60 border border-amber-200/80 rounded-xl text-xs text-amber-900">
                <strong class="font-bold block mb-0.5 uppercase text-[10px] text-amber-700">Internal Remarks / Sourcing Notes:</strong>
                <span><?= nl2br(htmlspecialchars($factory['notes'])) ?></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- ISOLATED PRODUCT CATALOG FOR THIS FACTORY -->
    <div class="space-y-4">

        <!-- Title & Filter Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-base font-semibold text-slate-900 flex items-center gap-2">
                    <i data-lucide="package-search" class="w-4 h-4 text-[#f05a29]"></i>
                    <span>Factory Products Catalog</span>
                    <span class="px-2.5 py-0.5 text-xs font-semibold bg-orange-50 text-[#f05a29] border border-orange-200 rounded-full">
                        <?= $totalProduct ?> Items
                    </span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Showing isolated products linked exclusively to <?= htmlspecialchars($factory['name']) ?>.</p>
            </div>

            <div class="flex items-center gap-2">
                <button type="button" @click="showAssignModal = true" class="h-9 px-3.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center space-x-1 cursor-pointer border-0">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                    <span>Link Products</span>
                </button>

                <form method="GET" action="<?= url('admin/factories/show/' . $factory['id']) ?>" class="flex items-center gap-2">
                    <div class="relative">
                        <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search SKU or Name..." class="h-9 pl-8 pr-3 bg-white border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-[#f05a29] transition">
                    </div>

                    <select name="status" class="h-9 py-1.5 px-3 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:outline-none focus:border-[#f05a29] transition">
                        <option value="">All Status</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>

                    <button type="submit" class="h-9 px-3.5 bg-slate-900 hover:bg-black text-white font-semibold text-xs rounded-xl transition cursor-pointer">
                        Filter
                    </button>
                </form>
            </div>
        </div>

        <!-- Products Table Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse min-w-[750px]">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Product</th>
                            <th class="py-3 px-4">SKU</th>
                            <th class="py-3 px-4">Category</th>
                            <th class="py-3 px-4 text-right">Price</th>
                            <th class="py-3 px-4 text-center">Stock</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <i data-lucide="box" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                    <p class="font-bold text-slate-600">No products linked to this factory.</p>
                                    <p class="text-xs text-slate-400 mb-3">Assign products here or from Product Edit page.</p>
                                    <button type="button" @click="showAssignModal = true" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl transition inline-flex items-center gap-1.5">
                                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                        <span>Link Existing Products Now</span>
                                    </button>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($products as $p): ?>
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-3">
                                            <img src="<?= asset($p['main_image'] ?: 'assets/images/placeholder.jpg') ?>" alt="" class="w-10 h-10 object-cover rounded-lg border border-slate-200 shrink-0">
                                            <div>
                                                <a href="<?= url('admin/products/edit/' . $p['id']) ?>" class="font-bold text-slate-900 hover:text-indigo-600 transition block">
                                                    <?= htmlspecialchars($p['name']) ?>
                                                </a>
                                                <?php if (!empty($p['variety'])): ?>
                                                    <span class="text-[10px] text-slate-400 font-semibold">Variety: <?= htmlspecialchars($p['variety']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-800">
                                        <?= htmlspecialchars($p['sku']) ?>
                                    </td>
                                    <td class="py-3 px-4 text-slate-600">
                                        <?= htmlspecialchars($p['category_name'] ?: 'N/A') ?>
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-slate-900">
                                        ₹<?= number_format((float)$p['price'], 2) ?>
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold">
                                        <span class="<?= (int)$p['stock'] > 10 ? 'text-emerald-600' : ((int)$p['stock'] > 0 ? 'text-amber-600' : 'text-red-600') ?>">
                                            <?= (int)$p['stock'] ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <?php if ($p['status'] === 'active'): ?>
                                            <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 uppercase">Active</span>
                                        <?php else: ?>
                                            <span class="px-2.5 py-0.5 text-[10px] font-extrabold rounded-full bg-slate-200 text-slate-700 border border-slate-300 uppercase">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <a href="<?= url('admin/products/edit/' . $p['id']) ?>" class="px-2.5 py-1.5 bg-slate-100 hover:bg-indigo-50 text-slate-700 hover:text-indigo-600 rounded-lg text-xs font-bold transition inline-flex items-center gap-1">
                                                <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                                <span>Edit</span>
                                            </a>

                                            <form method="POST" action="<?= url('admin/factories/remove-product/' . $factory['id']) ?>" onsubmit="return confirm('Unlink this product from <?= htmlspecialchars(addslashes($factory['name'])) ?>?');" class="inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                                <button type="submit" class="px-2.5 py-1.5 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 rounded-lg text-xs font-bold transition inline-flex items-center gap-1 border-0 cursor-pointer" title="Unlink product from factory">
                                                    <i data-lucide="link-2-off" class="w-3.5 h-3.5"></i>
                                                    <span>Unlink</span>
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

    <!-- LINK PRODUCTS MODAL (LIGHT & COMPACT ADMIN DESIGN) -->
    <div x-show="showAssignModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/40 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-2xl w-full p-5 space-y-4 relative" @click.away="showAssignModal = false">
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        <i data-lucide="package-plus" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-slate-900">Link Products to <?= htmlspecialchars($factory['name']) ?></h3>
                        <p class="text-[11px] text-slate-500">Select items from catalog to assign to factory [<?= htmlspecialchars($factory['factory_code']) ?>].</p>
                    </div>
                </div>
                <button type="button" @click="showAssignModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Form -->
            <form method="POST" action="<?= url('admin/factories/assign-products/' . $factory['id']) ?>" class="space-y-4">
                <?= csrf_field() ?>

                <!-- Search Input inside Modal -->
                <div class="relative">
                    <i data-lucide="search" class="w-3.5 h-3.5 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" x-model="filterText" placeholder="Quick search product name or SKU..." class="w-full pl-8 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-900 focus:bg-white focus:outline-none focus:border-indigo-600 transition">
                </div>

                <!-- Product Selection List -->
                <div class="max-h-72 overflow-y-auto border border-slate-200 rounded-xl divide-y divide-slate-100 p-1">
                    <?php if (empty($assignableProducts)): ?>
                        <div class="py-8 text-center text-slate-400 text-xs font-medium">
                            No unassigned products available.
                        </div>
                    <?php else: ?>
                        <?php foreach ($assignableProducts as $ap): ?>
                            <label class="flex items-center justify-between p-2.5 hover:bg-slate-50 rounded-lg cursor-pointer transition select-none"
                                   x-show="!filterText || '<?= htmlspecialchars(addslashes(strtolower($ap['name'] . ' ' . $ap['sku']))) ?>'.includes(filterText.toLowerCase())">
                                <div class="flex items-center gap-3">
                                    <input type="checkbox" name="product_ids[]" value="<?= $ap['id'] ?>" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                                    <img src="<?= asset($ap['main_image'] ?: 'assets/images/placeholder.jpg') ?>" class="w-8 h-8 rounded border border-slate-200 object-cover">
                                    <div>
                                        <div class="font-bold text-xs text-slate-900"><?= htmlspecialchars($ap['name']) ?></div>
                                        <div class="text-[10px] text-slate-500 font-mono">SKU: <?= htmlspecialchars($ap['sku']) ?></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-xs text-slate-900">₹<?= number_format((float)$ap['price'], 2) ?></span>
                                    <?php if (!empty($ap['factory_id'])): ?>
                                        <span class="block text-[9px] text-amber-600 font-semibold">Reassign Factory</span>
                                    <?php endif; ?>
                                </div>
                            </label>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" @click="showAssignModal = false" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5 cursor-pointer">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Link Selected Products</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
