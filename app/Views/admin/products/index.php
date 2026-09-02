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
                    class="px-2.5 py-0.5 text-[10px] font-semibold uppercase bg-orange-50 text-[#f05a29] rounded-md tracking-wider border border-orange-200">
                    Catalog &amp; Products
                </span>
                <span class="text-gray-300 text-xs">•</span>
                <span class="text-xs text-gray-500 font-medium">All Wholesale Products</span>
            </div>
            <h1 class="text-xl font-semibold text-slate-900 mt-1 tracking-tight">Products Management</h1>
        </div>
        <div class="flex items-center space-x-2">
            <a href="<?= url('admin/products/import/template') ?>"
                class="h-9 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition border border-slate-300 flex items-center space-x-1.5 cursor-pointer shrink-0">
                <i data-lucide="download" class="w-4 h-4 text-slate-600"></i>
                <span>Download Template</span>
            </a>
            <button onclick="openBulkImportModal()" type="button"
                class="h-9 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl transition shadow-sm flex items-center space-x-1.5 cursor-pointer shrink-0 border-0">
                <i data-lucide="upload-cloud" class="w-4 h-4 text-white"></i>
                <span>Bulk Import</span>
            </button>
            <?php if (\App\Core\Auth::hasPermission('products.add')): ?>
                <a href="<?= url('admin/products/create') ?>"
                    class="h-9 px-4 bg-[#f05a29] hover:bg-[#d8481b] text-white font-semibold text-xs rounded-xl transition shadow-sm shadow-[#f05a29]/30 flex items-center space-x-1.5 cursor-pointer shrink-0">
                    <i data-lucide="plus" class="w-4 h-4 text-white"></i>
                    <span>Add Product</span>
                </a>
            <?php endif; ?>
        </div>
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
                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider text-[10px] select-none">
                        <th class="py-3 px-3 w-8 text-center">
                            <input type="checkbox" id="select-all-chk" onchange="toggleSelectAll(this)"
                                class="rounded text-[#f05a29] focus:ring-0 w-3.5 h-3.5 cursor-pointer">
                        </th>
                        <th class="py-3 px-3">Product</th>
                        <th class="py-3 px-3">SKU</th>
                        <th class="py-3 px-3">Price</th>
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
                                <td class="py-2.5 px-3 font-mono font-medium text-slate-600 text-[11px]">
                                    <?= htmlspecialchars($p['sku']) ?></td>
                                <td class="py-2.5 px-3 font-semibold text-slate-900 text-[11px]">
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

                                <td class="py-2.5 px-3 text-center font-medium text-[11px] text-slate-700">
                                    <?= number_format($soldCount) ?>
                                </td>

                                <td class="py-2.5 px-3 text-center font-medium text-[11px] text-orange-600">
                                    <?= $moqCount ?> pcs
                                </td>

                                <td class="py-2.5 px-3">
                                    <?php if ($p['status'] === 'active'): ?>
                                        <span
                                            class="px-2 py-0.5 text-[9.5px] font-semibold rounded-full uppercase bg-emerald-50 text-emerald-700 border border-emerald-200">Active</span>
                                    <?php elseif ($p['status'] === 'merged'): ?>
                                        <span
                                            class="px-2 py-0.5 text-[9.5px] font-semibold rounded-full uppercase bg-purple-50 text-purple-700 border border-purple-200">Merged</span>
                                    <?php else: ?>
                                        <span
                                            class="px-2 py-0.5 text-[9.5px] font-semibold rounded-full uppercase bg-slate-100 text-slate-600 border border-slate-200">Inactive</span>
                                    <?php endif; ?>
                                </td>

                                <td class="py-2.5 px-3 text-right">
                                    <div class="flex items-center justify-end space-x-1.5">
                                        <?php if (\App\Core\Auth::hasPermission('products.edit')): ?>
                                            <a href="<?= url('admin/products/edit/' . $p['id']) ?>"
                                                class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 rounded-lg font-medium text-[10px] transition shadow-2xs">Edit</a>
                                        <?php endif; ?>
                                        <?php if (\App\Core\Auth::hasPermission('products.delete')): ?>
                                            <form action="<?= url('admin/products/delete/' . $p['id']) ?>" method="POST"
                                                class="inline" data-confirm="Are you sure you want to delete this product?">
                                                <?= csrf_field() ?>
                                                <button type="submit"
                                                    class="px-2.5 py-1 bg-red-50 text-red-600 border border-red-200 rounded-lg font-medium text-[10px] hover:bg-red-600 hover:text-white transition shadow-2xs inline-flex items-center space-x-1 cursor-pointer"
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


<!-- ============================================================ -->
<!-- BULK IMPORT MODAL & STAGED PREVIEW -->
<!-- ============================================================ -->
<div id="bulkImportModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="upload-cloud" class="w-5 h-5 text-emerald-600"></i>
                    <span>Bulk Product Listing Importer</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Upload .xlsx / .csv catalog spreadsheet (+ optional companion ZIP for images)</p>
            </div>
            <button onclick="closeBulkImportModal()" type="button" class="w-8 h-8 rounded-full hover:bg-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center transition border-0 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-6 overflow-y-auto flex-1 space-y-6">

            <!-- STEP 1: FILE UPLOAD SECTION -->
            <div id="importStepUpload" class="space-y-5">
                <form id="bulkUploadForm" onsubmit="handleParseSpreadsheet(event)" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Spreadsheet File Input -->
                        <div class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-xl p-5 bg-slate-50/50 text-center transition group">
                            <i data-lucide="file-spreadsheet" class="w-8 h-8 mx-auto text-emerald-600 mb-2 group-hover:scale-110 transition"></i>
                            <label class="block text-xs font-bold text-slate-800 mb-1 cursor-pointer">Select Spreadsheet (.xlsx / .csv)</label>
                            <input type="file" id="importSpreadsheetFile" accept=".xlsx, .csv" required class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                            <span class="text-[11px] text-slate-400 block mt-1">Fixed 55-column v1 schema</span>
                        </div>

                        <!-- Companion ZIP Input -->
                        <div class="border-2 border-dashed border-slate-300 hover:border-blue-500 rounded-xl p-5 bg-slate-50/50 text-center transition group">
                            <i data-lucide="archive" class="w-8 h-8 mx-auto text-blue-600 mb-2 group-hover:scale-110 transition"></i>
                            <label class="block text-xs font-bold text-slate-800 mb-1 cursor-pointer">Optional Image ZIP Archive (.zip)</label>
                            <input type="file" id="importZipFile" accept=".zip" class="block w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <span class="text-[11px] text-slate-400 block mt-1">Pack product &amp; variant image files</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between bg-slate-100 p-3 rounded-xl">
                        <label class="flex items-center space-x-2 text-xs font-medium text-slate-700 cursor-pointer select-none">
                            <input type="checkbox" id="chkAutoCreateCategory" checked class="rounded text-emerald-600 focus:ring-0 w-4 h-4 cursor-pointer">
                            <span>Auto-create Category, Subcategory &amp; Brand if missing in database</span>
                        </label>
                        <a href="<?= url('admin/products/import/template') ?>" class="text-xs font-bold text-[#f05a29] hover:underline flex items-center space-x-1">
                            <i data-lucide="download" class="w-3.5 h-3.5"></i>
                            <span>Download Template</span>
                        </a>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" id="btnParseSpreadsheet" class="px-6 h-10 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center space-x-2 cursor-pointer border-0">
                            <i data-lucide="scan" class="w-4 h-4"></i>
                            <span>Parse &amp; Validate Catalog</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- LOADER -->
            <div id="importStepLoader" class="hidden py-12 text-center space-y-3">
                <div class="w-10 h-10 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin mx-auto"></div>
                <h4 class="text-sm font-bold text-slate-800">Parsing catalog spreadsheet &amp; verifying schema...</h4>
                <p class="text-xs text-slate-500">Checking product SKUs, variant codes, prices, and categories...</p>
            </div>

            <!-- STEP 2: PREVIEW TABLE -->
            <div id="importStepPreview" class="hidden space-y-4">
                <!-- Summary Metrics Bar -->
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center">
                    <div class="bg-slate-50 border border-slate-200 p-2.5 rounded-xl">
                        <div class="text-[10px] uppercase font-bold text-slate-400">Total Rows</div>
                        <div class="text-base font-black text-slate-800" id="previewTotalRows">0</div>
                    </div>
                    <div class="bg-slate-50 border border-slate-200 p-2.5 rounded-xl">
                        <div class="text-[10px] uppercase font-bold text-slate-400">Products</div>
                        <div class="text-base font-black text-slate-800" id="previewTotalProducts">0</div>
                    </div>
                    <div class="bg-emerald-50 border border-emerald-200 p-2.5 rounded-xl">
                        <div class="text-[10px] uppercase font-bold text-emerald-600">Valid Products</div>
                        <div class="text-base font-black text-emerald-700" id="previewValidProducts">0</div>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 p-2.5 rounded-xl">
                        <div class="text-[10px] uppercase font-bold text-amber-600">Warnings</div>
                        <div class="text-base font-black text-amber-700" id="previewWarningProducts">0</div>
                    </div>
                    <div class="bg-red-50 border border-red-200 p-2.5 rounded-xl">
                        <div class="text-[10px] uppercase font-bold text-red-600">Errors</div>
                        <div class="text-base font-black text-red-700" id="previewErrorProducts">0</div>
                    </div>
                </div>

                <!-- Products Group Table -->
                <div class="border border-slate-200 rounded-xl overflow-hidden max-h-[350px] overflow-y-auto">
                    <table class="w-full text-xs text-left border-collapse min-w-[700px]">
                        <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[10px] font-semibold uppercase sticky top-0 z-10">
                            <tr>
                                <th class="py-2.5 px-3">Product SKU</th>
                                <th class="py-2.5 px-3">Product Name</th>
                                <th class="py-2.5 px-3">Category</th>
                                <th class="py-2.5 px-3 text-center">Variants</th>
                                <th class="py-2.5 px-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody id="previewTableBody" class="divide-y divide-slate-100 bg-white">
                            <!-- Populated dynamically -->
                        </tbody>
                    </table>
                </div>

                <!-- Preview Actions Footer -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <button onclick="resetImportModal()" type="button" class="px-4 h-9 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition cursor-pointer border-0">
                        Back / Re-upload
                    </button>
                    <button id="btnCommitImport" onclick="executeCommitImport()" type="button" class="px-6 h-10 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center space-x-2 cursor-pointer border-0">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span>Confirm &amp; Import Products</span>
                    </button>
                </div>
            </div>

            <!-- STEP 3: RESULT SUMMARY -->
            <div id="importStepResult" class="hidden text-center py-6 space-y-4">
                <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
                    <i data-lucide="check-check" class="w-8 h-8"></i>
                </div>
                <h3 class="text-lg font-extrabold text-slate-900">Import Operation Completed!</h3>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-lg mx-auto text-xs">
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                        <div class="text-slate-500 font-medium">Created Products</div>
                        <div class="text-lg font-black text-emerald-700" id="resCreatedProducts">0</div>
                    </div>
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl">
                        <div class="text-slate-500 font-medium">Updated Products</div>
                        <div class="text-lg font-black text-blue-700" id="resUpdatedProducts">0</div>
                    </div>
                    <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl">
                        <div class="text-slate-500 font-medium">Created Variants</div>
                        <div class="text-lg font-black text-purple-700" id="resCreatedVariants">0</div>
                    </div>
                    <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-xl">
                        <div class="text-slate-500 font-medium">Updated Variants</div>
                        <div class="text-lg font-black text-indigo-700" id="resUpdatedVariants">0</div>
                    </div>
                </div>

                <div id="resErrorNotice" class="hidden max-w-lg mx-auto bg-amber-50 border border-amber-200 p-3 rounded-xl text-left text-xs text-amber-800 flex items-center justify-between">
                    <div>
                        <strong class="font-bold block">Some rows were skipped due to validation errors.</strong>
                        <span class="text-[11px] text-amber-700">You can download the error CSV report below for correction.</span>
                    </div>
                    <a href="<?= url('admin/products/import/errors-csv') ?>" target="_blank" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-lg text-xs transition shrink-0 ml-3">
                        Download Error CSV
                    </a>
                </div>

                <div class="pt-4">
                    <button onclick="window.location.reload()" type="button" class="px-6 h-10 bg-slate-900 hover:bg-black text-white font-bold text-xs rounded-xl shadow-md transition cursor-pointer border-0">
                        Done &amp; Refresh Products
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function openBulkImportModal() {
        document.getElementById('bulkImportModal').classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeBulkImportModal() {
        document.getElementById('bulkImportModal').classList.add('hidden');
        resetImportModal();
    }

    function resetImportModal() {
        document.getElementById('importStepUpload').classList.remove('hidden');
        document.getElementById('importStepLoader').classList.add('hidden');
        document.getElementById('importStepPreview').classList.add('hidden');
        document.getElementById('importStepResult').classList.add('hidden');
        document.getElementById('bulkUploadForm').reset();
    }

    async function handleParseSpreadsheet(e) {
        e.preventDefault();
        const fileInput = document.getElementById('importSpreadsheetFile');
        const zipInput = document.getElementById('importZipFile');
        const chkAuto = document.getElementById('chkAutoCreateCategory');

        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Please select a spreadsheet file.');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        if (zipInput.files && zipInput.files.length > 0) {
            formData.append('zip_file', zipInput.files[0]);
        }
        formData.append('auto_create_category', chkAuto.checked ? '1' : '0');

        document.getElementById('importStepUpload').classList.add('hidden');
        document.getElementById('importStepLoader').classList.remove('hidden');

        try {
            const resp = await fetch('<?= url('admin/products/import/parse') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await resp.json();
            document.getElementById('importStepLoader').classList.add('hidden');

            if (!data.success) {
                alert(data.error || 'Validation error while parsing file.');
                document.getElementById('importStepUpload').classList.remove('hidden');
                return;
            }

            renderPreview(data);
        } catch (err) {
            console.error(err);
            document.getElementById('importStepLoader').classList.add('hidden');
            document.getElementById('importStepUpload').classList.remove('hidden');
            alert('Server error while parsing file. Please check file format.');
        }
    }

    function renderPreview(data) {
        document.getElementById('importStepPreview').classList.remove('hidden');

        const s = data.summary || {};
        document.getElementById('previewTotalRows').textContent = s.total_rows || 0;
        document.getElementById('previewTotalProducts').textContent = s.total_products || 0;
        document.getElementById('previewValidProducts').textContent = s.valid_products || 0;
        document.getElementById('previewWarningProducts').textContent = s.warning_products || 0;
        document.getElementById('previewErrorProducts').textContent = s.error_products || 0;

        const tbody = document.getElementById('previewTableBody');
        tbody.innerHTML = '';

        if (!data.products || data.products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="py-6 text-center text-slate-400">No product groups found in file.</td></tr>';
            return;
        }

        data.products.forEach(p => {
            let badgeClass = 'bg-emerald-100 text-emerald-800 border-emerald-300';
            let badgeLabel = 'VALID';
            if (p.status === 'error') {
                badgeClass = 'bg-red-100 text-red-800 border-red-300';
                badgeLabel = 'ERROR';
            } else if (p.status === 'warning') {
                badgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
                badgeLabel = 'WARNING';
            }

            let tr = document.createElement('tr');
            tr.className = 'hover:bg-slate-50 border-b border-slate-100';
            tr.innerHTML = `
                <td class="py-2.5 px-3 font-mono font-bold text-slate-900">${p.product_sku || 'N/A'}</td>
                <td class="py-2.5 px-3 font-semibold text-slate-800">${p.name || 'Unnamed Product'}</td>
                <td class="py-2.5 px-3 text-slate-600">${p.category || 'N/A'}</td>
                <td class="py-2.5 px-3 text-center font-bold text-slate-700">${(p.variants || []).length}</td>
                <td class="py-2.5 px-3 text-center">
                    <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full border ${badgeClass}">${badgeLabel}</span>
                </td>
            `;
            tbody.appendChild(tr);

            // If errors or warnings present, show details sub-row
            const logs = [...(p.errors || []), ...(p.warnings || [])];
            if (logs.length > 0) {
                let errTr = document.createElement('tr');
                errTr.className = 'bg-slate-50/80';
                errTr.innerHTML = `
                    <td colspan="5" class="py-2 px-4 text-[11px] text-slate-600">
                        <ul class="list-disc pl-4 space-y-0.5">
                            ${logs.map(l => `<li class="${p.errors && p.errors.includes(l) ? 'text-red-600 font-semibold' : 'text-amber-700'}">${l}</li>`).join('')}
                        </ul>
                    </td>
                `;
                tbody.appendChild(errTr);
            }
        });

        // Enable / Disable commit button
        const btnCommit = document.getElementById('btnCommitImport');
        if ((s.valid_products || 0) + (s.warning_products || 0) === 0) {
            btnCommit.disabled = true;
            btnCommit.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            btnCommit.disabled = false;
            btnCommit.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    async function executeCommitImport() {
        const btn = document.getElementById('btnCommitImport');
        btn.disabled = true;
        btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div><span>Importing...</span>';

        try {
            const resp = await fetch('<?= url('admin/products/import/commit') ?>', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await resp.json();

            if (!data.success) {
                alert(data.error || 'Commit failed.');
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="check-circle-2" class="w-4 h-4"></i><span>Confirm & Import Products</span>';
                if (typeof lucide !== 'undefined') lucide.createIcons();
                return;
            }

            document.getElementById('importStepPreview').classList.add('hidden');
            document.getElementById('importStepResult').classList.remove('hidden');

            document.getElementById('resCreatedProducts').textContent = data.created_products || 0;
            document.getElementById('resUpdatedProducts').textContent = data.updated_products || 0;
            document.getElementById('resCreatedVariants').textContent = data.created_variants || 0;
            document.getElementById('resUpdatedVariants').textContent = data.updated_variants || 0;

            if (data.skipped_products && data.skipped_products > 0) {
                document.getElementById('resErrorNotice').classList.remove('hidden');
            }
        } catch (err) {
            console.error(err);
            alert('Server error during import commit.');
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="check-circle-2" class="w-4 h-4"></i><span>Confirm & Import Products</span>';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>