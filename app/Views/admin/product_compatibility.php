<?php
include __DIR__ . '/layouts/header.php';
?>

<div class="p-6 space-y-6">

    <!-- Top Header Bar with Clean Back Arrow -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center space-x-3">
            <a href="<?= url('admin/products') ?>"
                class="w-9 h-9 bg-white border border-gray-900 rounded-xl flex items-center justify-center text-gray-600 hover:text-gray-900 hover:border-gray-400 transition"
                title="Back to Products">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <h1 class="text-xl font-semibold text-gray-900 leading-tight">Spare Parts Specs & Compatibility Editor
                </h1>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Manage OEM benchmarks, vehicle compatibility,
                    quality scores, installation difficulty & badges</p>
            </div>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div
            class="p-4 bg-green-50 border border-green-200 rounded-2xl text-xs font-semibold text-green-800 flex items-center space-x-2">
            <i data-lucide="check-circle" class="w-4 h-4 text-green-600"></i>
            <span><?= htmlspecialchars($success) ?></span>
        </div>
    <?php endif; ?>

    <!-- Product Selector Bar -->
    <div
        class="bg-white p-4 rounded-2xl border border-gray-900 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-4">
        <label class="text-xs font-semibold text-gray-800 uppercase tracking-wider shrink-0">Select Spare Part
            Product:</label>
        <select onchange="location.href='<?= url('admin/product-compatibility?product_id=') ?>' + this.value"
            class="w-full sm:w-96 h-11 px-3 bg-gray-50 border border-gray-900 rounded-xl text-xs font-semibold text-gray-900 focus:outline-none focus:border-red-600">
            <?php foreach ($products as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $productId === (int) $p['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['name']) ?> (SKU: <?= htmlspecialchars($p['sku']) ?>)</option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($product): ?>
        <form action="<?= url('admin/product-compatibility/update/' . $product['id']) ?>" method="POST" class="space-y-6">
            <?= csrf_field() ?>

            <!-- 1. OEM VS MUDSOR COMPARISON BENCHMARKS -->
            <div class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-4">
                <h3
                    class="text-sm font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-2 flex items-center space-x-2">
                    <i data-lucide="git-compare" class="w-4 h-4 text-red-600"></i>
                    <span>OEM vs MUDSOR Comparison Data</span>
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs">
                    <!-- MUDSOR SPECS -->
                    <div class="space-y-4 bg-red-50/50 p-4 rounded-xl border border-red-100">
                        <h4 class="font-semibold text-red-700 uppercase tracking-wider">MUDSOR Accessory Specs</h4>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Material</label>
                            <input type="text" name="material" value="<?= htmlspecialchars($product['material']) ?>"
                                class="w-full h-10 px-3 bg-white border border-gray-900 rounded-xl font-semibold">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Paint / Coating Finish</label>
                            <input type="text" name="finish" value="<?= htmlspecialchars($product['finish']) ?>"
                                class="w-full h-10 px-3 bg-white border border-gray-900 rounded-xl font-semibold">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Weight (Grams)</label>
                            <input type="number" name="weight_grams" value="<?= $product['weight_grams'] ?>"
                                class="w-full h-10 px-3 bg-white border border-gray-900 rounded-xl font-semibold">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Warranty (Months)</label>
                            <input type="number" name="warranty_months" value="<?= $product['warranty_months'] ?>"
                                class="w-full h-10 px-3 bg-white border border-gray-900 rounded-xl font-semibold">
                        </div>
                    </div>

                    <!-- OEM BENCHMARKS -->
                    <div class="space-y-4 bg-gray-50 p-4 rounded-xl border border-gray-900">
                        <h4 class="font-semibold text-gray-700 uppercase tracking-wider">Original OEM Equivalent Specs</h4>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">OEM Equivalent Price (₹)</label>
                            <input type="number" step="0.01" name="oem_price" value="<?= $product['oem_price'] ?>"
                                class="w-full h-10 px-3 bg-white border border-gray-900 rounded-xl font-semibold">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">OEM Material</label>
                            <input type="text" name="oem_material" value="<?= htmlspecialchars($product['oem_material']) ?>"
                                class="w-full h-10 px-3 bg-white border border-gray-900 rounded-xl font-semibold">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">OEM Finish</label>
                            <input type="text" name="oem_finish" value="<?= htmlspecialchars($product['oem_finish']) ?>"
                                class="w-full h-10 px-3 bg-white border border-gray-900 rounded-xl font-semibold">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">OEM Warranty (Months)</label>
                            <input type="number" name="oem_warranty_months" value="<?= $product['oem_warranty_months'] ?>"
                                class="w-full h-10 px-3 bg-white border border-gray-900 rounded-xl font-semibold">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2. INSTALLATION DIFFICULTY & QUALITY SCORES -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- INSTALLATION DIFFICULTY -->
                <div class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-4">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-2">
                        Installation Difficulty & Time</h3>
                    <div class="space-y-4 text-xs font-semibold">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Difficulty Level</label>
                            <select name="installation_difficulty"
                                class="w-full h-10 px-3 bg-gray-50 border border-gray-900 rounded-xl">
                                <option value="easy" <?= $product['installation_difficulty'] === 'easy' ? 'selected' : '' ?>>🟢
                                    Easy DIY</option>
                                <option value="moderate" <?= $product['installation_difficulty'] === 'moderate' ? 'selected' : '' ?>>🟡 Moderate</option>
                                <option value="professional" <?= $product['installation_difficulty'] === 'professional' ? 'selected' : '' ?>>🔴 Professional Required</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Estimated Time (Minutes)</label>
                            <input type="number" name="installation_time_minutes"
                                value="<?= $product['installation_time_minutes'] ?>"
                                class="w-full h-10 px-3 bg-gray-50 border border-gray-900 rounded-xl">
                        </div>
                    </div>
                </div>

                <!-- QUALITY SCORES -->
                <div class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-4">
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-2">
                        Quality Progress Scores (%)</h3>
                    <div class="grid grid-cols-2 gap-4 text-xs font-semibold">
                        <div>
                            <label class="block text-gray-700 mb-1">Material Quality %</label>
                            <input type="number" min="0" max="100" name="material_quality_pct"
                                value="<?= $product['material_quality_pct'] ?>"
                                class="w-full h-10 px-3 bg-gray-50 border border-gray-900 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Paint Finish %</label>
                            <input type="number" min="0" max="100" name="paint_finish_pct"
                                value="<?= $product['paint_finish_pct'] ?>"
                                class="w-full h-10 px-3 bg-gray-50 border border-gray-900 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Fitment Precision %</label>
                            <input type="number" min="0" max="100" name="fitment_pct" value="<?= $product['fitment_pct'] ?>"
                                class="w-full h-10 px-3 bg-gray-50 border border-gray-900 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-gray-700 mb-1">Durability %</label>
                            <input type="number" min="0" max="100" name="durability_pct"
                                value="<?= $product['durability_pct'] ?>"
                                class="w-full h-10 px-3 bg-gray-50 border border-gray-900 rounded-xl">
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. VEHICLE COMPATIBILITY CHECKLIST -->
            <div class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-2">
                    Compatible Electric Scooter Vehicles</h3>

                <?php
                $allScooters = ['Ola S1 Pro (Gen 1 & 2)', 'Ola S1 X / Air', 'Ather 450X / 450S', 'TVS iQube S / ST', 'Hero Vida V1 Pro', 'Bajaj Chetak Premium', 'Simple One EV', 'Bounce Infinity E1'];
                $compMap = [];
                foreach ($compatibilities as $c) {
                    $compMap[$c['vehicle_name']] = (bool) $c['is_compatible'];
                }
                ?>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs font-semibold">
                    <?php foreach ($allScooters as $vName): ?>
                        <label
                            class="flex items-center space-x-2 p-3 bg-gray-50 rounded-xl border border-gray-900 cursor-pointer hover:bg-gray-100">
                            <input type="checkbox" name="vehicles[]" value="<?= htmlspecialchars($vName) ?>"
                                <?= !empty($compMap[$vName]) ? 'checked' : '' ?>
                                class="rounded text-red-600 focus:ring-red-500 w-4 h-4">
                            <span class="text-gray-800"><?= htmlspecialchars($vName) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 4. WHAT'S INCLUDED CHECKLIST -->
            <div class="bg-white p-6 rounded-2xl border border-gray-900 shadow-xs space-y-4">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-2">
                    What's Included Box Items</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <?php
                    $incList = array_pad(array_column($includedItems, 'item_name'), 4, '');
                    foreach ($incList as $idx => $itemText):
                        ?>
                        <input type="text" name="included_items[]" value="<?= htmlspecialchars($itemText) ?>"
                            placeholder="Included Item #<?= $idx + 1 ?> (e.g. Heavy Duty Mounting Brackets)"
                            class="w-full h-10 px-3 bg-gray-50 border border-gray-900 rounded-xl font-semibold">
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- SAVE BUTTON -->
            <div class="flex justify-end">
                <button type="submit"
                    class="h-12 px-8 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl shadow-md transition">Save
                    All Spare Part Specs</button>
            </div>

        </form>
    <?php endif; ?>

</div>

<?php
include __DIR__ . '/layouts/footer.php';
?>