<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="max-w-5xl mx-auto space-y-6 pb-12">
    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-xs">
        <h1 class="text-xl font-semibold text-slate-900 tracking-tight">Missing HSN Codes</h1>
        <p class="text-xs text-slate-500 mt-1">Orders are not blocked. These categories and products will be invoiced without an HSN until one is set.</p>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 text-xs font-semibold text-slate-700">Categories without an HSN (<?= count($categories) ?>)</div>
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px]">
                <tr><th class="py-2 px-4">ID</th><th class="py-2 px-4">Name</th><th class="py-2 px-4"></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!$categories): ?>
                    <tr><td colspan="3" class="p-4 text-slate-400">Every category has an HSN code.</td></tr>
                <?php endif; ?>
                <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td class="py-2 px-4 font-mono"><?= (int) $cat['id'] ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($cat['name']) ?></td>
                        <td class="py-2 px-4 text-right"><a class="text-red-600 font-semibold" href="<?= url('admin/categories') ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 text-xs font-semibold text-slate-700">Products with no resolved HSN (<?= count($products) ?>)</div>
        <table class="w-full text-left text-xs">
            <thead class="bg-slate-50 text-slate-500 uppercase text-[10px]">
                <tr><th class="py-2 px-4">SKU</th><th class="py-2 px-4">Name</th><th class="py-2 px-4"></th></tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (!$products): ?>
                    <tr><td colspan="3" class="p-4 text-slate-400">Every product resolves to an HSN from itself or its category.</td></tr>
                <?php endif; ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td class="py-2 px-4 font-mono"><?= htmlspecialchars($product['sku']) ?></td>
                        <td class="py-2 px-4"><?= htmlspecialchars($product['name']) ?></td>
                        <td class="py-2 px-4 text-right"><a class="text-red-600 font-semibold" href="<?= url('admin/products/edit/' . (int) $product['id']) ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
