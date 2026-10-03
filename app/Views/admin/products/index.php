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
            <?php if (\App\Core\Auth::hasPermission('products.delete')): ?>
                <button onclick="openBulkDeleteModal('all')" type="button"
                    class="h-9 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition shadow-sm flex items-center space-x-1.5 cursor-pointer shrink-0 border-0">
                    <i data-lucide="trash-2" class="w-4 h-4 text-white"></i>
                    <span>Delete All Products</span>
                </button>
            <?php endif; ?>
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
            class="hidden flex items-center space-x-3 bg-red-50 border border-red-200 px-3 py-1.5 rounded-xl transition">
            <span class="text-xs font-semibold text-red-700" id="selected-count-label">0 Selected</span>
            <button type="button" onclick="openBulkDeleteModal('selected')" class="px-2.5 py-1 text-[10px] font-bold text-white bg-red-600 rounded-lg hover:bg-red-700 transition shadow-sm border-0 cursor-pointer">Delete Selected</button>
            <button type="button" onclick="clearSelection()" class="text-[10px] text-red-500 font-semibold hover:text-red-700 underline border-0 bg-transparent cursor-pointer">Clear selection</button>
        </div>
    </div>

    <!-- Compact Modern Table Container -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden mt-3">
        <div id="select-all-banner" class="hidden bg-indigo-50 border-b border-indigo-100 p-2 text-center text-xs text-indigo-800 font-medium">
            <span id="banner-text-default">All <span id="page-selected-count">0</span> products on this page are selected. 
            <button type="button" onclick="selectAllMatching()" class="text-indigo-600 font-bold underline bg-transparent border-0 cursor-pointer ml-1">Select all matching products</button></span>
            <span id="banner-text-all" class="hidden font-bold">All matching products are selected.</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse min-w-[840px]">
                <thead>
                    <tr
                        class="bg-slate-50 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider text-[10px] select-none">
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
                            $catName = !empty($p['category_name']) ? $p['category_name'] : ('Cat ID: ' . $p['category_id']);
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
                                            <?= e($p['name']) ?>
                                        </h4>
                                        <span class="text-[9.5px] text-gray-500 font-medium block">
                                            Cat: <?= e($catName) ?></span>
                                    </div>
                                </td>
                                <td class="py-2.5 px-3 font-mono font-semibold text-indigo-700 text-[11px] whitespace-nowrap">
                                    <span
                                        class="px-1.5 py-0.5 bg-indigo-50 border border-indigo-200/60 rounded text-indigo-700 font-mono font-bold"><?= htmlspecialchars($p['sku']) ?></span>
                                </td>
                                <td class="py-2.5 px-3 font-semibold text-slate-900 text-[11px]">
                                    <?= format_price($p['sale_price'] ?: $p['price']) ?>
                                </td>

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
                formData.append('_csrf_token', window.CSRF_TOKEN || '<?= csrf_token() ?>');

                fetch('<?= url("admin/products/toggle-flag") ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': window.CSRF_TOKEN || '<?= csrf_token() ?>'
                    }
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
    let bulkDeleteMode = 'selected';
    let allMatchingIds = [];

    function getSelectedProductIds() {
        return [...document.querySelectorAll('.product-chk:checked')].map(cb => parseInt(cb.value));
    }

    function toggleSelectAll(master) {
        document.querySelectorAll('.product-chk').forEach(cb => cb.checked = master.checked);
        bulkDeleteMode = 'selected';
        
        if (master.checked) {
            document.getElementById('select-all-banner').classList.remove('hidden');
            document.getElementById('banner-text-default').classList.remove('hidden');
            document.getElementById('banner-text-all').classList.add('hidden');
            document.getElementById('page-selected-count').textContent = getSelectedProductIds().length;
        } else {
            document.getElementById('select-all-banner').classList.add('hidden');
        }
        updateBulkBar();
    }

    function selectAllMatching() {
        bulkDeleteMode = 'all_matching';
        document.getElementById('banner-text-default').classList.add('hidden');
        document.getElementById('banner-text-all').classList.remove('hidden');
        
        // Fetch the count to show it in the action bar
        const formData = new FormData();
        formData.append('action', 'get_ids');
        formData.append('mode', 'all_matching');
        formData.append('search', '<?= htmlspecialchars($search ?? '') ?>');
        formData.append('status', '<?= htmlspecialchars($status ?? '') ?>');
        formData.append('_csrf_token', window.CSRF_TOKEN || '<?= csrf_token() ?>');

        fetch('<?= url("admin/products/bulk-delete") ?>', {
            method: 'POST', body: formData
        }).then(r => r.json()).then(data => {
            if (data.success) {
                allMatchingIds = data.ids;
                const label = document.getElementById('selected-count-label');
                label.textContent = data.total + ' Selected (All Matching)';
            }
        });
    }

    function clearSelection() {
        document.getElementById('select-all-chk').checked = false;
        document.querySelectorAll('.product-chk').forEach(cb => cb.checked = false);
        document.getElementById('select-all-banner').classList.add('hidden');
        bulkDeleteMode = 'selected';
        updateBulkBar();
    }

    function updateBulkBar() {
        if (bulkDeleteMode === 'all_matching') return; // Handled separately
        const selected = getSelectedProductIds();
        const bar = document.getElementById('bulk-action-bar');
        const label = document.getElementById('selected-count-label');
        if (selected.length > 0) {
            bar.classList.remove('hidden');
            label.textContent = selected.length + ' Selected';
        } else {
            bar.classList.add('hidden');
            document.getElementById('select-all-banner').classList.add('hidden');
        }
    }
</script>


<!-- ============================================================ -->
<!-- BULK IMPORT MODAL & STAGED PREVIEW (v2 — validate-then-commit) -->
<!-- ============================================================ -->
<div id="bulkImportModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div
        class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-5xl max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">

        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="upload-cloud" class="w-5 h-5 text-emerald-600"></i>
                    <span>Bulk Product Listing Importer</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">Upload .xlsx / .csv catalog spreadsheet (Two-Phase All-or-Nothing Import)</p>
            </div>
            <button onclick="closeBulkImportModal()" type="button"
                class="w-8 h-8 rounded-full hover:bg-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center transition border-0 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable) -->
        <div class="p-6 overflow-y-auto flex-1 space-y-6">

            <!-- STEP 1: FILE UPLOAD SECTION -->
            <div id="importStepUpload" class="space-y-5">
                <form id="bulkUploadForm" onsubmit="handleParseSpreadsheet(event)" class="space-y-4">
                    <!-- Single Spreadsheet File Input -->
                    <div
                        class="border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-2xl p-8 bg-slate-50/50 text-center transition group">
                        <i data-lucide="file-spreadsheet"
                            class="w-10 h-10 mx-auto text-emerald-600 mb-3 group-hover:scale-110 transition"></i>
                        <label class="block text-sm font-bold text-slate-800 mb-1 cursor-pointer">Select Spreadsheet
                            (.xlsx / .csv)</label>
                        <p class="text-xs text-slate-400 mb-4">Fixed 55-column v1 catalog spreadsheet schema</p>
                        <input type="file" id="importSpreadsheetFile" accept=".xlsx, .csv" required
                            class="block w-full max-w-md mx-auto text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                    </div>

                    <div class="flex items-center justify-between bg-slate-100 p-3.5 rounded-xl">
                        <label
                            class="flex items-center space-x-2 text-xs font-medium text-slate-700 cursor-pointer select-none">
                            <input type="checkbox" id="chkAutoCreateCategory" checked
                                class="rounded text-emerald-600 focus:ring-0 w-4 h-4 cursor-pointer">
                            <span>Auto-create Category, Subcategory &amp; Brand if missing in database</span>
                        </label>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit" id="btnParseSpreadsheet"
                            class="px-6 h-10 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center space-x-2 cursor-pointer border-0">
                            <i data-lucide="scan" class="w-4 h-4"></i>
                            <span>Validate &amp; Preview Import</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- LOADER -->
            <div id="importStepLoader" class="hidden py-12 text-center space-y-3">
                <div
                    class="w-10 h-10 border-4 border-emerald-200 border-t-emerald-600 rounded-full animate-spin mx-auto">
                </div>
                <h4 class="text-sm font-bold text-slate-800">Parsing spreadsheet &amp; performing dry-run validations...</h4>
                <p class="text-xs text-slate-500">Checking rows, duplicate titles, SKUs, category requirements, and price constraints...</p>
            </div>

            <!-- STEP 2: PREVIEW & VALIDATION RESULTS TABLE -->
            <div id="importStepPreview" class="hidden space-y-4">

                <!-- Error Alert Banner (Shown when validation fails) -->
                <div id="previewErrorBanner" class="hidden bg-red-50 border border-red-200 rounded-2xl p-4 text-xs space-y-3">
                    <div class="flex items-start justify-between">
                        <div class="flex items-start space-x-3">
                            <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <h4 class="font-extrabold text-red-900 text-sm">Validation Failed — Import Blocked</h4>
                                <p class="text-red-700 mt-0.5">
                                    <span id="previewErrorBannerCount">0</span> errors detected across the spreadsheet.
                                    <strong class="font-bold underline">Nothing has been added or changed in the database.</strong>
                                    Please correct the errors in your spreadsheet and re-upload.
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 shrink-0">
                            <a href="<?= url('admin/products/import/errors-xlsx') ?>" target="_blank"
                                class="px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg text-xs transition flex items-center space-x-1 shadow-sm">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>Download Error XLSX</span>
                            </a>
                            <a href="<?= url('admin/products/import/errors-csv') ?>" target="_blank"
                                class="px-3 py-1.5 bg-slate-700 hover:bg-slate-800 text-white font-bold rounded-lg text-xs transition flex items-center space-x-1 shadow-sm">
                                <i data-lucide="download" class="w-3.5 h-3.5"></i>
                                <span>Download CSV</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Success Ready Banner (Shown when 0 errors) -->
                <div id="previewSuccessBanner" class="hidden bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-xs">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <i data-lucide="check-circle" class="w-5 h-5"></i>
                        </div>
                        <div>
                            <h4 class="font-extrabold text-emerald-900 text-sm">All Validation Checks Passed!</h4>
                            <p class="text-emerald-700 mt-0.5">
                                Zero errors found. Ready to perform atomic transaction commit.
                            </p>
                        </div>
                    </div>
                </div>

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
                        <div class="text-[10px] uppercase font-bold text-emerald-600">New Products</div>
                        <div class="text-base font-black text-emerald-700" id="previewNewProducts">0</div>
                    </div>
                    <div class="bg-amber-50 border border-amber-200 p-2.5 rounded-xl">
                        <div class="text-[10px] uppercase font-bold text-amber-600">Merge Products</div>
                        <div class="text-base font-black text-amber-700" id="previewMergeProducts">0</div>
                    </div>
                    <div class="bg-red-50 border border-red-200 p-2.5 rounded-xl">
                        <div class="text-[10px] uppercase font-bold text-red-600">Total Errors</div>
                        <div class="text-base font-black text-red-700" id="previewErrorProducts">0</div>
                    </div>
                </div>

                <!-- Detailed Error List Section (If errors exist) -->
                <div id="previewErrorListContainer" class="hidden space-y-2">
                    <h5 class="text-xs font-bold text-red-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="alert-circle" class="w-4 h-4 text-red-600"></i>
                        <span>Validation Errors Details (<span id="previewErrorCountHeader">0</span>)</span>
                    </h5>
                    <div class="border border-red-200 rounded-xl overflow-hidden max-h-[220px] overflow-y-auto bg-red-50/20">
                        <table class="w-full text-xs text-left border-collapse min-w-[700px]">
                            <thead class="bg-red-100/80 border-b border-red-200 text-red-900 text-[10px] font-bold uppercase sticky top-0 z-10">
                                <tr>
                                    <th class="py-2 px-3 w-16 text-center">Row #</th>
                                    <th class="py-2 px-3 w-32">Product SKU</th>
                                    <th class="py-2 px-3">Product Name</th>
                                    <th class="py-2 px-3 w-36">Column</th>
                                    <th class="py-2 px-3">Error Reason</th>
                                </tr>
                            </thead>
                            <tbody id="previewErrorTableBody" class="divide-y divide-red-100 bg-white text-slate-800">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Products Staged Preview Table -->
                <div class="space-y-2">
                    <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                        <i data-lucide="package" class="w-4 h-4 text-slate-500"></i>
                        <span>Parsed Products &amp; Variants Staged Preview</span>
                    </h5>
                    <div class="border border-slate-200 rounded-xl overflow-hidden max-h-[280px] overflow-y-auto">
                        <table class="w-full text-xs text-left border-collapse min-w-[700px]">
                            <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 text-[10px] font-semibold uppercase sticky top-0 z-10">
                                <tr>
                                    <th class="py-2.5 px-3">Product SKU</th>
                                    <th class="py-2.5 px-3">Product Name</th>
                                    <th class="py-2.5 px-3">Category</th>
                                    <th class="py-2.5 px-3 text-center">Variants</th>
                                    <th class="py-2.5 px-3 text-center">Import Action</th>
                                    <th class="py-2.5 px-3 text-center">Validation</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody" class="divide-y divide-slate-100 bg-white">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Preview Actions Footer -->
                <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                    <button onclick="resetImportModal()" type="button"
                        class="px-4 h-9 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition cursor-pointer border-0">
                        Back / Re-upload File
                    </button>
                    <button id="btnCommitImport" onclick="executeCommitImport()" type="button"
                        class="px-6 h-10 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition flex items-center space-x-2 cursor-pointer border-0">
                        <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        <span>Confirm &amp; Execute Import</span>
                    </button>
                </div>
            </div>

            <!-- STEP 3: RESULT SUMMARY -->
            <div id="importStepResult" class="hidden text-center py-6 space-y-4">
                <div
                    class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
                    <i data-lucide="check-check" class="w-8 h-8"></i>
                </div>
                <h3 class="text-lg font-extrabold text-slate-900">Import Operation Completed Successfully!</h3>
                <p class="text-xs text-slate-500">All products and variants have been committed to the database in a single transaction.</p>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 max-w-lg mx-auto text-xs">
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl">
                        <div class="text-slate-500 font-medium">Created Products</div>
                        <div class="text-lg font-black text-emerald-700" id="resCreatedProducts">0</div>
                    </div>
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-xl">
                        <div class="text-slate-500 font-medium">Updated/Merged Products</div>
                        <div class="text-lg font-black text-blue-700" id="resUpdatedProducts">0</div>
                    </div>
                    <div class="p-3 bg-purple-50 border border-purple-200 rounded-xl">
                        <div class="text-slate-500 font-medium">Created Variants</div>
                        <div class="text-lg font-black text-purple-700" id="resCreatedVariants">0</div>
                    </div>
                    <div class="p-3 bg-indigo-50 border border-indigo-200 rounded-xl">
                        <div class="text-slate-500 font-medium">Updated/Merged Variants</div>
                        <div class="text-lg font-black text-indigo-700" id="resUpdatedVariants">0</div>
                    </div>
                </div>

                <div id="imageStatsContainer" class="hidden grid-cols-1 gap-3 max-w-md mx-auto text-xs mt-3">
                    <div class="p-3 bg-cyan-50 border border-cyan-200 rounded-xl text-center">
                        <div class="text-slate-500 font-medium">Image Sync Status</div>
                        <div class="text-xs font-semibold text-cyan-900 mt-1">
                            Images enqueued &amp; syncing in background to Cloudflare R2.<br>
                            <a href="<?= url('admin/image-sync-status') ?>" target="_blank" class="inline-block mt-1.5 font-bold text-cyan-700 hover:text-cyan-900 underline">
                                View Live Queue Progress (&rarr;)
                            </a>
                        </div>
                    </div>
                </div>

                <div class="pt-4">
                    <button onclick="window.location.reload()" type="button"
                        class="px-6 h-10 bg-slate-900 hover:bg-black text-white font-bold text-xs rounded-xl shadow-md transition cursor-pointer border-0">
                        Done &amp; Refresh Products List
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
        const chkAuto = document.getElementById('chkAutoCreateCategory');

        if (!fileInput.files || fileInput.files.length === 0) {
            alert('Please select a spreadsheet file.');
            return;
        }

        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('auto_create_category', chkAuto.checked ? '1' : '0');
        formData.append('_csrf_token', window.CSRF_TOKEN || '<?= csrf_token() ?>');

        document.getElementById('importStepUpload').classList.add('hidden');
        document.getElementById('importStepLoader').classList.remove('hidden');

        try {
            const resp = await fetch('<?= url('admin/products/import/parse') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.CSRF_TOKEN || '<?= csrf_token() ?>'
                }
            });
            const data = await resp.json();
            document.getElementById('importStepLoader').classList.add('hidden');

            if (!data.success) {
                alert(data.error || data.message || 'Validation error while parsing file.');
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
        const errors = data.errors || [];
        const products = data.products || [];
        const canCommit = data.can_commit === true || (data.can_commit !== false && !data.has_errors && errors.length === 0);

        // Summary counters
        document.getElementById('previewTotalRows').textContent = s.total_rows || 0;
        document.getElementById('previewTotalProducts').textContent = s.total_products || 0;
        document.getElementById('previewNewProducts').textContent = s.new_products !== undefined ? s.new_products : (s.total_products - (s.merge_products || 0));
        document.getElementById('previewMergeProducts').textContent = s.merge_products || 0;
        document.getElementById('previewErrorProducts').textContent = errors.length;

        // Banners
        const elErrBanner = document.getElementById('previewErrorBanner');
        const elSucBanner = document.getElementById('previewSuccessBanner');
        const elErrListCont = document.getElementById('previewErrorListContainer');

        if (!canCommit || errors.length > 0) {
            elErrBanner.classList.remove('hidden');
            elSucBanner.classList.add('hidden');
            document.getElementById('previewErrorBannerCount').textContent = errors.length;

            // Render Error Details Table
            elErrListCont.classList.remove('hidden');
            document.getElementById('previewErrorCountHeader').textContent = errors.length;
            const errTbody = document.getElementById('previewErrorTableBody');
            errTbody.innerHTML = '';

            errors.forEach(err => {
                const tr = document.createElement('tr');
                tr.className = 'hover:bg-red-50/50';
                tr.innerHTML = `
                    <td class="py-2 px-3 text-center font-mono font-bold text-red-700">${err.row || '-'}</td>
                    <td class="py-2 px-3 font-mono text-slate-800">${err.product_sku || '-'}</td>
                    <td class="py-2 px-3 text-slate-700">${err.product_name || '-'}</td>
                    <td class="py-2 px-3 font-semibold text-slate-600">${err.column || '-'}</td>
                    <td class="py-2 px-3 text-red-600 font-semibold">${err.reason || '-'}</td>
                `;
                errTbody.appendChild(tr);
            });
        } else {
            elErrBanner.classList.add('hidden');
            elSucBanner.classList.remove('hidden');
            elErrListCont.classList.add('hidden');
        }

        // Staged Products Table
        const tbody = document.getElementById('previewTableBody');
        tbody.innerHTML = '';

        if (products.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="py-6 text-center text-slate-400">No product groups found in spreadsheet.</td></tr>';
        } else {
            products.forEach(p => {
                let actionBadge = '<span class="px-2 py-0.5 text-[10px] font-bold rounded-md border bg-emerald-100 text-emerald-800 border-emerald-300">NEW</span>';
                if (p.action === 'merge' || p.merge_action === 'merge') {
                    actionBadge = '<span class="px-2 py-0.5 text-[10px] font-bold rounded-md border bg-amber-100 text-amber-800 border-amber-300">MERGE EXISTING</span>';
                }

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
                    <td class="py-2.5 px-3 text-center">${actionBadge}</td>
                    <td class="py-2.5 px-3 text-center">
                        <span class="px-2 py-0.5 text-[10px] font-extrabold rounded-full border ${badgeClass}">${badgeLabel}</span>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        // Enable / Disable confirm button
        const btnCommit = document.getElementById('btnCommitImport');
        if (!canCommit) {
            btnCommit.disabled = true;
            btnCommit.classList.add('opacity-50', 'cursor-not-allowed');
            btnCommit.title = 'Validation failed. Fix errors in spreadsheet to enable import.';
        } else {
            btnCommit.disabled = false;
            btnCommit.classList.remove('opacity-50', 'cursor-not-allowed');
            btnCommit.title = 'Click to execute atomic import transaction';
        }

        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    async function executeCommitImport() {
        const btn = document.getElementById('btnCommitImport');
        btn.disabled = true;
        btn.innerHTML = '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div><span>Committing transaction...</span>';

        const formData = new FormData();
        formData.append('_csrf_token', window.CSRF_TOKEN || '<?= csrf_token() ?>');

        try {
            const resp = await fetch('<?= url('admin/products/import/commit') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': window.CSRF_TOKEN || '<?= csrf_token() ?>'
                }
            });
            const data = await resp.json();

            if (!data.success) {
                alert('Import Failed & Database Rolled Back:\n\n' + (data.error || data.message || 'Commit transaction error.'));
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="check-circle-2" class="w-4 h-4"></i><span>Confirm &amp; Execute Import</span>';
                if (typeof lucide !== 'undefined') lucide.createIcons();
                return;
            }

            // Success screen
            document.getElementById('importStepPreview').classList.add('hidden');
            document.getElementById('importStepResult').classList.remove('hidden');

            const summary = data.summary || data;
            document.getElementById('resCreatedProducts').textContent = summary.created_products || 0;
            document.getElementById('resUpdatedProducts').textContent = summary.updated_products || summary.merged_products || 0;
            document.getElementById('resCreatedVariants').textContent = summary.created_variants || 0;
            document.getElementById('resUpdatedVariants').textContent = summary.updated_variants || 0;

            if (data.image_stats) {
                document.getElementById('imageStatsContainer').classList.remove('hidden');
                document.getElementById('imageStatsContainer').classList.add('grid');
            }

            // Trigger background worker process immediately
            fetch('<?= url("admin/products/import/sync-images") ?>', { method: 'POST' }).catch(() => {});

            if (typeof lucide !== 'undefined') lucide.createIcons();
        } catch (err) {
            console.error(err);
            alert('Server error during import transaction commit.');
            btn.disabled = false;
            btn.innerHTML = '<i data-lucide="check-circle-2" class="w-4 h-4"></i><span>Confirm &amp; Execute Import</span>';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }
</script>
<!-- ============================================================ -->
<!-- BULK DELETE MODAL -->
<!-- ============================================================ -->
<div id="bulkDeleteModal"
    class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl border border-slate-200 w-full max-w-md overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        
        <div class="px-5 py-4 border-b border-red-100 bg-red-50/30 flex items-center space-x-3">
            <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center shrink-0">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900">Permanent Deletion</h3>
                <p class="text-[11px] text-red-600/80 font-medium">This action CANNOT be undone.</p>
            </div>
        </div>

        <div class="p-5 space-y-4">
            <p class="text-xs text-slate-700 leading-relaxed" id="bulkDeleteWarningText">
                You are about to permanently delete <span id="bd-count" class="font-bold text-red-600 text-sm">0</span> products. All associated variants, images, and pricing tiers will be permanently erased.
            </p>

            <div id="bd-confirm-container" class="hidden space-y-1.5">
                <label class="block text-[11px] font-semibold text-slate-700">To confirm, type <span id="bd-expected-text" class="font-mono bg-slate-100 border border-slate-200 px-1 py-0.5 rounded text-red-600 select-all"></span> below:</label>
                <input type="text" id="bd-confirm-input" class="w-full h-8 px-2.5 text-xs font-mono font-bold uppercase border border-slate-300 rounded-lg focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none transition" autocomplete="off" oninput="checkBdConfirm()">
            </div>
            
            <div class="space-y-2">
                <label class="flex items-start space-x-2 cursor-pointer group">
                    <input type="checkbox" id="bd-csv-chk" checked class="mt-0.5 rounded-sm text-emerald-600 focus:ring-0 w-3.5 h-3.5 border-slate-300">
                    <div class="flex-1">
                        <span class="block text-xs font-semibold text-slate-800 group-hover:text-emerald-700 transition">Download CSV Backup</span>
                    </div>
                </label>
                <label class="flex items-start space-x-2 cursor-pointer group">
                    <input type="checkbox" id="bd-r2-chk" class="mt-0.5 rounded-sm text-red-600 focus:ring-0 w-3.5 h-3.5 border-slate-300">
                    <div class="flex-1">
                        <span class="block text-xs font-semibold text-slate-800 group-hover:text-red-700 transition">Delete R2 Images</span>
                    </div>
                </label>
            </div>
            
            <!-- Progress Area -->
            <div id="bd-progress-container" class="hidden space-y-1.5 pt-2">
                <div class="flex justify-between text-[11px] font-bold text-slate-700">
                    <span>Deleting...</span>
                    <span id="bd-progress-text" class="tabular-nums">0 / 0</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div id="bd-progress-bar" class="bg-red-600 h-1.5 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
                </div>
            </div>
        </div>
        
        <div class="px-5 py-3 bg-slate-50 border-t border-slate-100 flex justify-end space-x-2">
            <button type="button" id="bd-btn-cancel" onclick="closeBulkDeleteModal()" class="px-3.5 h-8 bg-white border border-slate-300 hover:bg-slate-100 text-slate-700 font-semibold text-xs rounded-lg transition cursor-pointer">Cancel</button>
            <button type="button" id="bd-btn-confirm" onclick="executeBulkDelete()" class="px-3.5 h-8 bg-red-600 hover:bg-red-700 text-white font-bold text-xs rounded-lg shadow-sm transition cursor-pointer border-0 flex items-center justify-center space-x-1.5 disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                <span>Delete</span>
            </button>
        </div>
    </div>
</div>

<script>
    let bdTargetIds = [];
    let bdMode = 'selected';
    let bdExpectedText = '';
    
    function openBulkDeleteModal(mode) {
        bdMode = mode; // 'selected' or 'all' or 'all_matching'
        if (mode === 'selected') {
            bdMode = bulkDeleteMode; // Could be 'all_matching' if banner was used
        }
        
        bdTargetIds = [];
        
        const modal = document.getElementById('bulkDeleteModal');
        const countSpan = document.getElementById('bd-count');
        const confirmContainer = document.getElementById('bd-confirm-container');
        const confirmInput = document.getElementById('bd-confirm-input');
        const confirmBtn = document.getElementById('bd-btn-confirm');
        const expectedTextSpan = document.getElementById('bd-expected-text');
        
        document.getElementById('bd-progress-container').classList.add('hidden');
        confirmInput.value = '';
        
        modal.classList.remove('hidden');
        if (typeof lucide !== 'undefined') lucide.createIcons();
        
        if (bdMode === 'selected') {
            bdTargetIds = getSelectedProductIds();
            setupBulkDeleteUI(bdTargetIds.length, bdTargetIds);
        } else {
            // all or all_matching
            countSpan.textContent = '...';
            confirmBtn.disabled = true;
            
            const formData = new FormData();
            formData.append('action', 'get_ids');
            formData.append('mode', bdMode);
            if (bdMode === 'all_matching') {
                formData.append('search', '<?= htmlspecialchars($search ?? '') ?>');
                formData.append('status', '<?= htmlspecialchars($status ?? '') ?>');
            }
            formData.append('_csrf_token', window.CSRF_TOKEN || '<?= csrf_token() ?>');

            fetch('<?= url("admin/products/bulk-delete") ?>', {
                method: 'POST', body: formData
            }).then(r => r.json()).then(data => {
                if (data.success) {
                    bdTargetIds = data.ids;
                    setupBulkDeleteUI(data.total, bdTargetIds, bdMode === 'all');
                }
            });
        }
    }
    
    function setupBulkDeleteUI(total, ids, isAll = false) {
        document.getElementById('bd-count').textContent = total;
        const confirmContainer = document.getElementById('bd-confirm-container');
        const confirmInput = document.getElementById('bd-confirm-input');
        const expectedTextSpan = document.getElementById('bd-expected-text');
        
        if (isAll) {
            bdExpectedText = 'DELETE ALL';
            confirmContainer.classList.remove('hidden');
            expectedTextSpan.textContent = bdExpectedText;
            document.getElementById('bd-btn-confirm').disabled = true;
            setTimeout(() => confirmInput.focus(), 100);
        } else if (total > 20) {
            bdExpectedText = `DELETE ${total}`;
            confirmContainer.classList.remove('hidden');
            expectedTextSpan.textContent = bdExpectedText;
            document.getElementById('bd-btn-confirm').disabled = true;
            setTimeout(() => confirmInput.focus(), 100);
        } else {
            confirmContainer.classList.add('hidden');
            bdExpectedText = '';
            document.getElementById('bd-btn-confirm').disabled = false;
            setTimeout(() => document.getElementById('bd-btn-cancel').focus(), 100);
        }
    }
    
    function checkBdConfirm() {
        const val = document.getElementById('bd-confirm-input').value.trim();
        document.getElementById('bd-btn-confirm').disabled = (val !== bdExpectedText);
    }
    
    function closeBulkDeleteModal() {
        document.getElementById('bulkDeleteModal').classList.add('hidden');
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('bulkDeleteModal');
            if (modal && !modal.classList.contains('hidden')) {
                closeBulkDeleteModal();
            }
        }
    });
    
    async function executeBulkDelete() {
        if (bdTargetIds.length === 0) return;
        
        const btnConfirm = document.getElementById('bd-btn-confirm');
        const btnCancel = document.getElementById('bd-btn-cancel');
        const chkR2 = document.getElementById('bd-r2-chk').checked;
        const chkCsv = document.getElementById('bd-csv-chk').checked;
        
        if (chkCsv) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url("admin/products/bulk-delete-export") ?>';
            form.target = '_blank';
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_csrf_token';
            csrf.value = window.CSRF_TOKEN || '<?= csrf_token() ?>';
            form.appendChild(csrf);
            
            const idsInput = document.createElement('input');
            idsInput.type = 'hidden';
            idsInput.name = 'ids';
            idsInput.value = bdTargetIds.join(',');
            form.appendChild(idsInput);
            
            document.body.appendChild(form);
            form.submit();
            document.body.removeChild(form);
            
            await new Promise(r => setTimeout(r, 1000));
        }
        
        btnConfirm.disabled = true;
        btnCancel.disabled = true;
        document.getElementById('bd-confirm-input').disabled = true;
        
        document.getElementById('bd-progress-container').classList.remove('hidden');
        const progressText = document.getElementById('bd-progress-text');
        const progressBar = document.getElementById('bd-progress-bar');
        
        let deletedTotal = 0;
        let skippedTotal = 0;
        let reasons = [];
        
        const batchSize = 200;
        const total = bdTargetIds.length;
        
        for (let i = 0; i < total; i += batchSize) {
            const batchIds = bdTargetIds.slice(i, i + batchSize);
            
            const formData = new FormData();
            formData.append('action', 'delete_batch');
            formData.append('delete_r2', chkR2 ? '1' : '0');
            batchIds.forEach(id => formData.append('ids[]', id));
            formData.append('_csrf_token', window.CSRF_TOKEN || '<?= csrf_token() ?>');
            
            try {
                const res = await fetch('<?= url("admin/products/bulk-delete") ?>', {
                    method: 'POST', body: formData
                });
                const data = await res.json();
                
                if (data.success) {
                    deletedTotal += data.deleted;
                    skippedTotal += data.skipped;
                    if (data.reasons && data.reasons.length) {
                        reasons = [...new Set([...reasons, ...data.reasons])];
                    }
                }
            } catch (err) {
                console.error(err);
            }
            
            const processed = Math.min(i + batchSize, total);
            progressText.textContent = `${processed} / ${total}`;
            progressBar.style.width = `${(processed / total) * 100}%`;
        }
        
        let msg = `Deleted ${deletedTotal} products.`;
        if (skippedTotal > 0) {
            msg += `\nSkipped ${skippedTotal} products because they are referenced in past orders or RFQs.\n\nReasons:\n` + reasons.join('\n');
            alert(msg);
        }
        
        window.location.reload();
    }
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>