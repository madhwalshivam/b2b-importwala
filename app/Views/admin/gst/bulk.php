<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-6xl mx-auto space-y-6 pb-12">
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 tracking-tight">Bulk GST Update</h1>
            <p class="text-xs text-slate-500 mt-1">Set one GST rate and/or HSN on many categories. Existing orders keep the rate that was saved on them.</p>
        </div>
        <a href="<?= url('admin/gst/export') ?>" class="h-10 px-4 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-semibold rounded-xl inline-flex items-center">Export CSV</a>
    </div>

    <?php if (!empty($canEdit)): ?>
        <form action="<?= url('admin/gst/bulk') ?>" method="POST" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-4">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <label class="flex items-start gap-2 bg-slate-50 border border-slate-200 rounded-xl p-3">
                    <input type="checkbox" name="apply_rate" value="1" class="mt-0.5" checked>
                    <span class="flex-1">
                        <span class="font-semibold text-slate-800 block mb-1">Set GST rate</span>
                        <select name="gst_rate" id="bulkRate" class="w-full h-9 px-3 bg-white border border-slate-200 rounded-lg">
                            <option value="0">0%</option>
                            <option value="3">3%</option>
                            <option value="5">5%</option>
                            <option value="12">12%</option>
                            <option value="18" selected>18%</option>
                            <option value="28">28%</option>
                            <option value="custom">Custom</option>
                        </select>
                        <input type="number" name="gst_rate_custom" id="bulkRateCustom" min="0" max="100" step="0.01" placeholder="Custom %" class="mt-2 w-full h-9 px-3 bg-white border border-slate-200 rounded-lg hidden">
                    </span>
                </label>
                <label class="flex items-start gap-2 bg-slate-50 border border-slate-200 rounded-xl p-3">
                    <input type="checkbox" name="apply_hsn" value="1" class="mt-0.5">
                    <span class="flex-1">
                        <span class="font-semibold text-slate-800 block mb-1">Set HSN code</span>
                        <input type="text" name="hsn_code" inputmode="numeric" maxlength="8" placeholder="4, 6 or 8 digits" class="w-full h-9 px-3 bg-white border border-slate-200 rounded-lg font-mono">
                    </span>
                </label>
            </div>

            <div class="overflow-x-auto border border-slate-200 rounded-xl">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-[10px] uppercase text-slate-500">
                        <tr>
                            <th class="py-2 px-3 w-10"><input type="checkbox" id="checkAllCats"></th>
                            <th class="py-2 px-3">Category</th>
                            <th class="py-2 px-3">Parent</th>
                            <th class="py-2 px-3">HSN</th>
                            <th class="py-2 px-3">GST %</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td class="py-2 px-3"><input type="checkbox" name="category_ids[]" value="<?= (int) $cat['id'] ?>" class="cat-check"></td>
                                <td class="py-2 px-3 font-semibold text-slate-800"><?= htmlspecialchars($cat['name']) ?></td>
                                <td class="py-2 px-3 text-slate-500"><?= htmlspecialchars($cat['parent_name'] ?? '—') ?></td>
                                <td class="py-2 px-3 font-mono"><?= htmlspecialchars($cat['hsn_code'] ?? '') ?></td>
                                <td class="py-2 px-3"><?= $cat['gst_rate'] === null ? 'Inherit' : rtrim(rtrim(number_format((float) $cat['gst_rate'], 2), '0'), '.') . '%' ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($subcategories): ?>
                <div class="overflow-x-auto border border-slate-200 rounded-xl">
                    <div class="px-3 py-2 text-[11px] font-semibold text-slate-600 bg-slate-50">Sub-categories inherit onto products before the parent category.</div>
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-[10px] uppercase text-slate-500">
                            <tr>
                                <th class="py-2 px-3 w-10"><input type="checkbox" id="checkAllSubs"></th>
                                <th class="py-2 px-3">Sub-category</th>
                                <th class="py-2 px-3">Category</th>
                                <th class="py-2 px-3">HSN</th>
                                <th class="py-2 px-3">GST %</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($subcategories as $sub): ?>
                                <tr>
                                    <td class="py-2 px-3"><input type="checkbox" name="subcategory_ids[]" value="<?= (int) $sub['id'] ?>" class="sub-check"></td>
                                    <td class="py-2 px-3 font-semibold"><?= htmlspecialchars($sub['name']) ?></td>
                                    <td class="py-2 px-3 text-slate-500"><?= htmlspecialchars($sub['category_name'] ?? '') ?></td>
                                    <td class="py-2 px-3 font-mono"><?= htmlspecialchars($sub['hsn_code'] ?? '') ?></td>
                                    <td class="py-2 px-3"><?= $sub['gst_rate'] === null || $sub['gst_rate'] === '' ? 'Inherit' : rtrim(rtrim(number_format((float) $sub['gst_rate'], 2), '0'), '.') . '%' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <div class="flex justify-end">
                <button type="submit" class="h-10 px-5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl">Apply to selected</button>
            </div>
        </form>

        <form action="<?= url('admin/gst/import') ?>" method="POST" enctype="multipart/form-data" class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col sm:flex-row sm:items-end gap-3 text-xs">
            <?= csrf_field() ?>
            <div class="flex-1">
                <label class="block font-semibold text-slate-700 mb-1.5">Import CSV</label>
                <input type="file" name="csv" accept=".csv,text/csv" required class="w-full text-xs">
                <p class="text-[10px] text-slate-400 mt-1">Columns: id, name, hsn_code, gst_rate. Rows update the category with that id.</p>
            </div>
            <button type="submit" class="h-10 px-4 bg-slate-900 text-white font-semibold rounded-xl">Import</button>
        </form>
    <?php endif; ?>
</div>
<script>
    const rate = document.getElementById('bulkRate');
    const custom = document.getElementById('bulkRateCustom');
    if (rate && custom) {
        const sync = () => custom.classList.toggle('hidden', rate.value !== 'custom');
        rate.addEventListener('change', sync);
        sync();
    }
    const wire = (masterId, childClass) => {
        const master = document.getElementById(masterId);
        if (!master) return;
        master.addEventListener('change', () => {
            document.querySelectorAll('.' + childClass).forEach((box) => { box.checked = master.checked; });
        });
    };
    wire('checkAllCats', 'cat-check');
    wire('checkAllSubs', 'sub-check');
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
