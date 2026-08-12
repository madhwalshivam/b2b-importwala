<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6 font-sans">

    <!-- Top Header -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span
                    class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-red-50 text-red-600 rounded-lg tracking-wider border border-red-100">
                    Inventory & Catalog
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">All Products</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Products & Accessories Catalog</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Manage catalog items, prices, inventory stock levels, category assignments, and EV scooter model fitment
                specifications.
            </p>
        </div>
        <?php if (\App\Core\Auth::hasPermission('products.add')): ?>
            <div class="flex items-center space-x-2">
                <a href="<?= url('admin/products/create') ?>"
                    class="h-10 px-5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-xs flex items-center space-x-2 cursor-pointer">
                    <i data-lucide="plus" class="w-4 h-4 text-white"></i>
                    <span>Add Product</span>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Filter & Search Bar -->
    <div
        class="bg-white p-4 rounded-2xl border border-gray-900 shadow-xs flex flex-wrap items-center justify-between gap-4">
        <form action="<?= url('admin/products') ?>" method="GET" class="flex flex-wrap items-center gap-3 flex-1">
            <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>"
                placeholder="Search by name or SKU..."
                class="flex-1 min-w-[200px] h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-medium text-gray-900 focus:outline-none focus:border-gray-900">

            <select name="status" @change="$el.form.submit()"
                class="h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none">
                <option value="">All Statuses</option>
                <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="merged" <?= ($status ?? '') === 'merged' ? 'selected' : '' ?>>Merged</option>
            </select>

            <button type="submit"
                class="h-11 px-6 bg-gray-900 text-white text-xs font-semibold rounded-xl hover:bg-black transition">Filter</button>
        </form>

        <!-- Bulk Action Bar -->
        <div id="bulk-action-bar"
            class="hidden flex items-center space-x-3 bg-red-50 border border-red-200 px-4 py-2 rounded-xl">
            <span class="text-xs font-semibold text-red-700" id="selected-count-label">0 Selected</span>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-gray-900 shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse min-w-[760px]">
                <thead>
                    <tr
                        class="bg-gray-100/80 dark:bg-gray-900 border-b border-gray-900 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="py-4 px-4 w-10 text-center">
                            <input type="checkbox" id="select-all-chk" onchange="toggleSelectAll(this)"
                                class="rounded text-red-600 focus:ring-red-500 w-4 h-4 cursor-pointer">
                        </th>
                        <th class="py-4 px-4">Product Accessory</th>
                        <th class="py-4 px-4">SKU</th>
                        <th class="py-4 px-4">Price</th>
                        <th class="py-4 px-4">Inventory Stock</th>
                        <th class="py-4 px-4">Status</th>
                        <th class="py-4 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="p-6 text-center text-gray-400 font-medium">No products found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <?php
                            $imgSrc = !empty($p['main_image'])
                                ? (str_starts_with($p['main_image'], 'http') ? $p['main_image'] : url(ltrim($p['main_image'], '/')))
                                : url('assets/images/placeholder.jpg');
                            $catName = !empty($p['category_name']) ? htmlspecialchars_decode($p['category_name']) : ('Cat ID: ' . $p['category_id']);
                            ?>
                            <tr class="hover:bg-gray-50 transition <?= $p['status'] === 'merged' ? 'bg-purple-50/40' : '' ?>">
                                <td class="p-4 text-center">
                                    <input type="checkbox" value="<?= $p['id'] ?>" onchange="updateBulkBar()"
                                        class="product-chk rounded text-red-600 focus:ring-red-500 w-4 h-4 cursor-pointer">
                                </td>
                                <td class="p-4 flex items-center space-x-3 min-w-[280px]">
                                    <div
                                        class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 overflow-hidden shrink-0 flex items-center justify-center p-1 shadow-2xs">
                                        <img src="<?= $imgSrc ?>" alt="" class="w-full h-full object-contain rounded-lg"
                                            onerror="this.onerror=null; this.src='<?= url('assets/images/placeholder.jpg') ?>'">
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 line-clamp-1 text-xs">
                                            <?= htmlspecialchars(htmlspecialchars_decode($p['name'])) ?>
                                        </h4>
                                        <span class="text-[10px] text-gray-500 font-medium block">
                                            Category: <?= htmlspecialchars($catName) ?></span>
                                    </div>
                                </td>
                                <td class="p-4 font-mono font-semibold text-gray-700"><?= htmlspecialchars($p['sku']) ?></td>
                                <td class="p-4 font-semibold text-gray-900"><?= format_price($p['sale_price'] ?: $p['price']) ?>
                                </td>
                                <td
                                    class="p-4 font-semibold <?= $p['stock'] <= ($p['low_stock_threshold'] ?? 10) ? 'text-red-600' : 'text-gray-800' ?>">
                                    <?= $p['stock'] ?> units
                                </td>
                                <td class="p-4">
                                    <?php if ($p['status'] === 'active'): ?>
                                        <span
                                            class="px-2.5 py-1 text-[10px] font-semibold rounded-lg uppercase bg-gray-900 text-white">Active</span>
                                    <?php elseif ($p['status'] === 'merged'): ?>
                                        <span
                                            class="px-2.5 py-1 text-[10px] font-semibold rounded-lg uppercase bg-purple-100 text-purple-700 border border-purple-200">Merged
                                            301</span>
                                    <?php else: ?>
                                        <span
                                            class="px-2.5 py-1 text-[10px] font-semibold rounded-lg uppercase bg-gray-100 text-gray-600">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="p-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <?php if (\App\Core\Auth::hasPermission('products.edit')): ?>
                                            <a href="<?= url('admin/products/edit/' . $p['id']) ?>"
                                                class="px-3 py-1.5 bg-gray-900 text-white rounded-lg font-semibold text-[11px] hover:bg-black transition shadow-xs">Edit</a>
                                        <?php endif; ?>
                                        <?php if (\App\Core\Auth::hasPermission('products.delete')): ?>
                                            <form action="<?= url('admin/products/delete/' . $p['id']) ?>" method="POST"
                                                class="inline" data-confirm="Are you sure you want to delete this product?">
                                                <?= csrf_field() ?>
                                                <button type="submit"
                                                    class="px-3 py-1.5 bg-red-50 text-red-600 rounded-lg font-semibold text-[11px] hover:bg-red-600 hover:text-white transition shadow-xs inline-flex items-center space-x-1"
                                                    title="Delete product">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Delete</span>
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

        <?php if (isset($paginator)): ?>
            <div class="p-4 border-t border-gray-900">
                <?= $paginator->render() ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    function getSelectedProductIds() {
        return [...document.querySelectorAll('.product-chk:checked')].map(cb => parseInt(cb.value));
    }

    function toggleSelectAll(master) {
        document.querySelectorAll('.product-chk').forEach(cb => cb.checked = master.checked);
        updateBulkBar();
    }

    function updateBulkBar() {
        const selected = getSelectedProductIds();
        const bar = document.getElementById('bulk-action-bar');
        const label = document.getElementById('selected-count-label');
        if (selected.length > 0) {
            bar.classList.remove('hidden');
            label.textContent = selected.length + ' Selected';
        } else {
            bar.classList.add('hidden');
        }
    }
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>