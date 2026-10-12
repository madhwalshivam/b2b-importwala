<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-3xl mx-auto space-y-6 pb-12">
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-xs">
        <h1 class="text-xl font-semibold text-slate-900 tracking-tight">GST Settings</h1>
        <p class="text-xs text-slate-500 mt-1">Seller details used to decide CGST + SGST versus IGST. Cart and checkout calculate tax on the server from these settings.</p>
        <div class="flex gap-3 mt-3 text-xs font-semibold">
            <a href="<?= url('admin/gst/bulk') ?>" class="text-red-600">Bulk update &amp; CSV</a>
            <a href="<?= url('admin/gst/warnings') ?>" class="text-red-600">Missing HSN warnings</a>
        </div>
    </div>

    <form action="<?= url('admin/gst/settings') ?>" method="POST" class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200 shadow-xs space-y-5">
        <?= csrf_field() ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 text-xs">
            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">Seller State</label>
                <select name="seller_state" <?= empty($canEdit) ? 'disabled' : '' ?> class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-900">
                    <?php foreach ($states as $state): ?>
                        <option value="<?= htmlspecialchars($state) ?>" <?= ($settings['seller_state'] ?? '') === $state ? 'selected' : '' ?>><?= htmlspecialchars($state) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="text-[10px] text-slate-400 mt-1">If this is empty at checkout, the order is charged IGST and a warning is logged.</p>
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">Seller GSTIN</label>
                <input type="text" name="seller_gstin" maxlength="15" value="<?= htmlspecialchars($settings['seller_gstin'] ?? '') ?>" <?= empty($canEdit) ? 'disabled' : '' ?>
                    placeholder="15-character GSTIN"
                    class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-mono uppercase font-semibold text-slate-900">
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">Default GST Rate (%)</label>
                <select name="default_gst_rate" <?= empty($canEdit) ? 'disabled' : '' ?> class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-900">
                    <?php foreach ([0, 3, 5, 12, 18, 28] as $rate): ?>
                        <option value="<?= $rate ?>" <?= (float) ($settings['default_gst_rate'] ?? 18) == $rate ? 'selected' : '' ?>><?= $rate ?>%</option>
                    <?php endforeach; ?>
                </select>
                <p class="text-[10px] text-slate-400 mt-1">Used only when the product, its category and its parent category have no rate.</p>
            </div>
            <div>
                <label class="block font-semibold text-slate-700 mb-1.5">Price Mode</label>
                <select name="price_mode" <?= empty($canEdit) ? 'disabled' : '' ?> class="w-full h-10 px-3.5 bg-slate-50 border border-slate-200 rounded-xl font-semibold text-slate-900">
                    <option value="exclusive" <?= ($settings['price_mode'] ?? '') === 'exclusive' ? 'selected' : '' ?>>Exclusive — GST is added on top</option>
                    <option value="inclusive" <?= ($settings['price_mode'] ?? '') === 'inclusive' ? 'selected' : '' ?>>Inclusive — GST is included in the price</option>
                </select>
            </div>
        </div>
        <?php if (!empty($canEdit)): ?>
            <div class="flex justify-end">
                <button type="submit" class="h-10 px-5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl">Save GST Settings</button>
            </div>
        <?php endif; ?>
    </form>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
