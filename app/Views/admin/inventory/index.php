<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6">

    <!-- Top Header -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span
                    class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-red-50 text-red-600 rounded-lg tracking-wider border border-red-100">
                    Stock Control
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Warehouse Inventory</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Inventory & Stock Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Monitor real-time product stock quantities, set minimum threshold alerts, and perform quick bulk stock
                updates.
            </p>
        </div>

        <div class="flex items-center space-x-2 text-xs font-semibold">
            <a href="<?= url('admin/inventory') ?>"
                class="h-10 px-4 leading-[40px] rounded-xl transition <?= $filter === 'all' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700 hover:bg-slate-200' ?>">All
                Products</a>
            <a href="<?= url('admin/inventory?filter=low') ?>"
                class="h-10 px-4 leading-[40px] rounded-xl transition <?= $filter === 'low' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-600 border border-red-100 hover:bg-red-100' ?>">Low
                Stock Alert</a>
        </div>
    </div>

    <!-- Inventory Form -->
    <form action="<?= url('admin/inventory/update') ?>" method="POST"
        class="bg-white rounded-[10px] border border-gray-900 overflow-hidden space-y-4">
        <?= csrf_field() ?>

        <table class="w-full text-xs text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-900 text-gray-500 font-semibold uppercase tracking-wider">
                    <th class="p-4">Product Name</th>
                    <th class="p-4">SKU</th>
                    <th class="p-4">Low Stock Threshold</th>
                    <th class="p-4">Current Stock Units</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <?php
                    $imgSrc = !empty($p['main_image'])
                        ? (str_starts_with($p['main_image'], 'http') ? $p['main_image'] : url(ltrim($p['main_image'], '/')))
                        : url('assets/images/placeholder.jpg');
                    ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="p-4 font-semibold text-gray-900 flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 overflow-hidden shrink-0 flex items-center justify-center p-1 shadow-2xs">
                                <img src="<?= $imgSrc ?>" alt="" class="w-full h-full object-contain rounded-lg"
                                    onerror="this.onerror=null; this.src='<?= url('assets/images/placeholder.jpg') ?>'">
                            </div>
                            <span><?= htmlspecialchars($p['name']) ?></span>
                        </td>
                        <td class="p-4 font-mono text-gray-600"><?= htmlspecialchars($p['sku']) ?></td>
                        <td class="p-4 font-semibold text-gray-500"><?= $p['low_stock_threshold'] ?> units</td>
                        <td class="p-4">
                            <input type="number" name="stock[<?= $p['id'] ?>]" value="<?= $p['stock'] ?>" min="0"
                                class="w-24 h-10 px-3 bg-gray-50 border border-gray-900 rounded-[10px] font-semibold text-xs focus:outline-none focus:border-gray-900">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (\App\Core\Auth::hasPermission('inventory.edit')): ?>
            <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-end">
                <button type="submit"
                    class="h-12 px-6 bg-red-600 text-white font-semibold text-xs rounded-[10px] hover:bg-red-700 transition">Save
                    Bulk Stock Updates</button>
            </div>
        <?php endif; ?>
    </form>

</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>