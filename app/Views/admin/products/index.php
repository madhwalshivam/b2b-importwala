<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-5 font-sans pb-8">

    <!-- Top Header Bar -->
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span
                    class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase bg-orange-50 text-[#f05a29] rounded-md tracking-wider border border-orange-200">
                    Catalog &amp; Products
                </span>
                <span class="text-gray-300 text-xs">•</span>
                <span class="text-xs text-gray-500 font-medium">All Wholesale Products</span>
            </div>
            <h1 class="text-xl font-extrabold text-slate-900 mt-1 tracking-tight">Products Management</h1>
        </div>
        <?php if (\App\Core\Auth::hasPermission('products.add')): ?>
            <a href="<?= url('admin/products/create') ?>"
                class="h-9 px-4 bg-[#f05a29] hover:bg-[#d8481b] text-white font-semibold text-xs rounded-xl transition shadow-sm shadow-[#f05a29]/30 flex items-center space-x-1.5 cursor-pointer shrink-0">
                <i data-lucide="plus" class="w-4 h-4 text-white"></i>
                <span>Add Product</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- Filter & Search Bar -->
    <div
        class="bg-white p-3.5 rounded-2xl border border-gray-200/80 shadow-xs flex flex-wrap items-center justify-between gap-3">
        <form action="<?= url('admin/products') ?>" method="GET" class="flex flex-wrap items-center gap-2.5 flex-1">
            <div class="relative flex-1 min-w-[220px]">
                <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>"
                    placeholder="Search product name or SKU..."
                    class="w-full h-9 pl-9 pr-3 bg-slate-50 border border-gray-200 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
            </div>

            <select name="status" onchange="this.form.submit()"
                class="h-9 px-3 bg-slate-50 border border-gray-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#f05a29] transition cursor-pointer">
                <option value="">All Statuses</option>
                <option value="active" <?= ($status ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= ($status ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                <option value="merged" <?= ($status ?? '') === 'merged' ? 'selected' : '' ?>>Merged</option>
            </select>

            <button type="submit"
                class="h-9 px-4 bg-slate-900 text-white text-xs font-semibold rounded-xl hover:bg-black transition cursor-pointer">Filter</button>
        </form>

        <!-- Bulk Action Bar -->
        <div id="bulk-action-bar"
            class="hidden flex items-center space-x-3 bg-red-50 border border-red-200 px-3 py-1.5 rounded-xl">
            <span class="text-xs font-semibold text-red-700" id="selected-count-label">0 Selected</span>
        </div>
    </div>

    <!-- Compact Modern Table Container -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse min-w-[840px]">
                <thead>
                    <tr class="bg-slate-900 text-white font-semibold uppercase tracking-wider text-[10px] select-none">
                        <th class="py-3 px-3 w-8 text-center">
                            <input type="checkbox" id="select-all-chk" onchange="toggleSelectAll(this)"
                                class="rounded text-[#f05a29] focus:ring-0 w-3.5 h-3.5 cursor-pointer">
                        </th>
                        <th class="py-3 px-3">Product</th>
                        <th class="py-3 px-3">SKU</th>
                        <th class="py-3 px-3 font-extrabold text-slate-900 text-[11px]">Price</th>
                        <th class="py-3 px-3 text-center">New</th>
                        <th class="py-3 px-3 text-center">Free Delivery</th>
                        <th class="py-3 px-3 text-center">Sold</th>
                        <th class="py-3 px-3 text-center">MOQ</th>
                        <th class="py-3 px-3">Status</th>
                        <th class="py-3 px-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/80">
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="11" class="py-8 text-center text-gray-400 font-medium text-xs">No products found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <?php
                            $pImgs = get_product_images($p);
                            $imgSrc = !empty($pImgs[0]) ? $pImgs[0] : asset('assets/images/placeholder.jpg');
                            $catName = !empty($p['category_name']) ? htmlspecialchars_decode($p['category_name']) : ('Cat ID: ' . $p['category_id']);
                            $isBestSeller = !empty($p['is_best_seller']);
                            $isNew = !empty($p['is_new']) || !empty($p['is_new_arrival']);
                            $isFreeShipping = !isset($p['is_free_shipping']) || !empty($p['is_free_shipping']);
                            $soldCount = (int) ($p['total_sold'] ?? $p['sales_count'] ?? 0);
                            $moqCount = (int) ($p['moq'] ?? 1);
                            ?>
                            <tr
                                class="hover:bg-slate-50/70 transition <?= $p['status'] === 'merged' ? 'bg-purple-50/30' : '' ?>">
                                <td class="py-2.5 px-3 text-center">
                                    <input type="checkbox" value="<?= $p['id'] ?>" onchange="updateBulkBar()"
                                        class="product-chk rounded text-[#f05a29] focus:ring-0 w-3.5 h-3.5 cursor-pointer">
                                </td>
                                <td class="py-2.5 px-3 flex items-center space-x-2.5 min-w-[240px]">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-slate-100 border border-slate-200 overflow-hidden shrink-0 flex items-center justify-center p-0.5">
                                        <img src="<?= $imgSrc ?>" alt="" class="w-full h-full object-contain rounded"
                                            onerror="this.onerror=null; this.src='<?= url('assets/images/placeholder.jpg') ?>'">
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-slate-900 line-clamp-1 text-[11px] leading-snug">
                                            <?= htmlspecialchars(htmlspecialchars_decode($p['name'])) ?>
                                        </h4>
                                        <span class="text-[9.5px] text-gray-500 font-medium block">
                                            Cat: <?= htmlspecialchars($catName) ?></span>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 font-mono font-semibold text-slate-600 text-[11px]">
                                    <?= htmlspecialchars($p['sku']) ?></td>
                                <td class="py-2.5 px-3 font-extrabold text-slate-900 text-[11px]">
                                    <?= format_price($p['sale_price'] ?: $p['price']) ?></td>

                                <!-- Professional iOS Toggle: New Product -->
                                <td class="py-2.5 px-3 text-center">
                                    <button type="button" onclick="quickToggleSwitch(<?= $p['id'] ?>, 'is_new', this)"
                                        class="inline-flex items-center w-8 h-4.5 rounded-full transition-colors duration-200 ease-in-out p-0.5 cursor-pointer border shadow-2xs <?= $isNew ? 'bg-emerald-500 border-emerald-600' : 'bg-slate-200 border-slate-300' ?>"
                                        title="Toggle New Product Badge">
                                        <span
                                            class="w-3.5 h-3.5 rounded-full bg-white transition-transform duration-200 ease-in-out shadow-xs transform <?= $isNew ? 'translate-x-3.5' : 'translate-x-0' ?>"></span>
                                    </button>
                                </td>

                                <!-- Professional iOS Toggle: Free Delivery -->
                                <td class="py-2.5 px-3 text-center">
                                    <button type="button" onclick="quickToggleSwitch(<?= $p['id'] ?>, 'is_free_shipping', this)"
                                        class="inline-flex items-center w-8 h-4.5 rounded-full transition-colors duration-200 ease-in-out p-0.5 cursor-pointer border shadow-2xs <?= $isFreeShipping ? 'bg-emerald-500 border-emerald-600' : 'bg-slate-200 border-slate-300' ?>"
                                        title="Toggle Free Delivery Badge">
                                        <span
                                            class="w-3.5 h-3.5 rounded-full bg-white transition-transform duration-200 ease-in-out shadow-xs transform <?= $isFreeShipping ? 'translate-x-3.5' : 'translate-x-0' ?>"></span>
                                    </button>
                                </td>

                                <td class="py-2.5 px-3 text-center font-semibold text-[11px] text-slate-800">
                                    <?= number_format($soldCount) ?>
                                </td>

                                <td class="py-2.5 px-3 text-center font-semibold text-[11px] text-orange-600">
                                    <?= $moqCount ?> pcs
                                </td>

                                <td class="py-2.5 px-3">
                                    <?php if ($p['status'] === 'active'): ?>
                                        <span
                                            class="px-2 py-0.5 text-[9px] font-semibold rounded-md uppercase bg-emerald-100 text-emerald-700 border border-emerald-200">Active</span>
                                    <?php elseif ($p['status'] === 'merged'): ?>
                                        <span
                                            class="px-2 py-0.5 text-[9px] font-semibold rounded-md uppercase bg-purple-100 text-purple-700 border border-purple-200">Merged</span>
                                    <?php else: ?>
                                        <span
                                            class="px-2 py-0.5 text-[9px] font-semibold rounded-md uppercase bg-gray-100 text-gray-600 border border-gray-200">Inactive</span>
                                    <?php endif; ?>
                                </td>

                                <td class="py-2.5 px-3 text-right">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        <?php if (\App\Core\Auth::hasPermission('products.edit')): ?>
                                            <a href="<?= url('admin/products/edit/' . $p['id']) ?>"
                                                class="px-2.5 py-1 bg-slate-900 text-white rounded-md font-semibold text-[10px] hover:bg-black transition shadow-2xs">Edit</a>
                                        <?php endif; ?>
                                        <?php if (\App\Core\Auth::hasPermission('products.delete')): ?>
                                            <form action="<?= url('admin/products/delete/' . $p['id']) ?>" method="POST"
                                                class="inline" data-confirm="Are you sure you want to delete this product?">
                                                <?= csrf_field() ?>
                                                <button type="submit"
                                                    class="px-2.5 py-1 bg-red-50 text-red-600 rounded-md font-semibold text-[10px] hover:bg-red-600 hover:text-white transition shadow-2xs inline-flex items-center space-x-1"
                                                    title="Delete product">
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

        <script>
            function quickToggleSwitch(id, field, btn) {
                btn.disabled = true;
                btn.style.opacity = '0.6';
                const formData = new FormData();
                formData.append('id', id);
                formData.append('field', field);

                fetch('<?= url("admin/products/toggle-flag") ?>', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(res => res.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        if (data.success) {
                            const dot = btn.querySelector('span');
                            if (data.newValue === 1) {
                                dot.classList.remove('translate-x-0');
                                dot.classList.add('translate-x-3.5');
                                btn.className = 'inline-flex items-center w-8 h-4.5 rounded-full transition-colors duration-200 ease-in-out p-0.5 cursor-pointer border shadow-2xs bg-emerald-500 border-emerald-600';
                            } else {
                                dot.classList.remove('translate-x-3.5');
                                dot.classList.add('translate-x-0');
                                btn.className = 'inline-flex items-center w-8 h-4.5 rounded-full transition-colors duration-200 ease-in-out p-0.5 cursor-pointer border shadow-2xs bg-slate-200 border-slate-300';
                            }
                        } else {
                            alert(data.message || 'Toggle failed');
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        console.error(err);
                    });
            }
        </script>

        <?php if (isset($paginator)): ?>
            <div class="p-3 border-t border-gray-100">
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