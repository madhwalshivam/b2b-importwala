<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6 max-w-5xl">

    <!-- Top Header -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span
                    class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-red-50 text-red-600 rounded-lg tracking-wider border border-red-100">
                    Storefront Control
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Interactive Scooter Compare</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Homepage Compare Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Select up to 4 electric scooter models or accessories to display in the 4-card comparison engine on the
                storefront homepage.
            </p>
        </div>
    </div>

    <!-- Currently Selected Compare Products Table -->
    <div class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-4">
        <h3 class="text-sm font-semibold text-gray-900 border-b border-gray-100 pb-3">Currently Selected Products on
            Homepage Compare Section</h3>

        <?php if (empty($compareProducts)): ?>
            <div class="p-8 text-center text-xs text-gray-500 bg-gray-50 rounded-xl">
                No products assigned yet. Select a product below to add it to the homepage comparison.
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left border-collapse">
                    <thead>
                        <tr
                            class="bg-gray-50 border-b border-gray-900 text-gray-500 font-semibold uppercase tracking-wider">
                            <th class="p-3">Sort</th>
                            <th class="p-3">Product Image</th>
                            <th class="p-3">Product Name</th>
                            <th class="p-3">SKU</th>
                            <th class="p-3">Price</th>
                            <th class="p-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($compareProducts as $cp): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-3 font-semibold text-gray-500">#<?= $cp['sort_order'] ?></td>
                                <td class="p-3">
                                    <?php
                                    $cpImg = !empty($cp['main_image'])
                                        ? (str_starts_with($cp['main_image'], 'http') ? $cp['main_image'] : url(ltrim($cp['main_image'], '/')))
                                        : url('assets/images/placeholder.jpg');
                                    ?>
                                    <img src="<?= $cpImg ?>" alt="<?= htmlspecialchars($cp['name']) ?>"
                                        class="w-12 h-12 object-cover rounded-lg border border-gray-200 dark:border-slate-700"
                                        onerror="this.onerror=null; this.src='<?= url('assets/images/placeholder.jpg') ?>'">
                                </td>
                                <td class="p-3 font-semibold text-gray-900"><?= htmlspecialchars($cp['name']) ?></td>
                                <td class="p-3 font-mono text-gray-600"><?= htmlspecialchars($cp['sku']) ?></td>
                                <td class="p-3 font-semibold text-red-600">
                                    <?= format_price($cp['sale_price'] ?: $cp['price']) ?>
                                </td>
                                <td class="p-3 text-right">
                                    <form action="<?= url('admin/homepage-compare/remove') ?>" method="POST" class="inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="compare_id" value="<?= $cp['compare_id'] ?>">
                                        <button type="submit"
                                            class="px-3 py-1.5 bg-red-50 text-red-600 hover:bg-red-600 hover:text-white font-semibold rounded-lg transition">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Product Form -->
    <div class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-4">
        <h3 class="text-sm font-semibold text-gray-900 border-b border-gray-100 pb-3">Add Product to Homepage Compare
            Block</h3>

        <form action="<?= url('admin/homepage-compare/add') ?>" method="POST"
            class="flex flex-col sm:flex-row items-center gap-3">
            <?= csrf_field() ?>
            <select name="product_id" required
                class="flex-1 h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-gray-900">
                <option value="">-- Select Product from Catalog --</option>
                <?php foreach ($allProducts as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?> (SKU:
                        <?= htmlspecialchars($p['sku']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit"
                class="h-11 px-6 bg-gray-900 hover:bg-black text-white font-semibold text-xs rounded-xl transition shadow-xs flex items-center space-x-2 shrink-0">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Product</span>
            </button>
        </form>
    </div>

</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>