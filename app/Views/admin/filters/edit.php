<?php
include __DIR__ . '/../layouts/header.php';
$isEdit = !empty($attribute);
$actionUrl = $isEdit ? url('admin/filters/update/' . $attribute['id']) : url('admin/filters/store');
?>

<div class="max-w-4xl space-y-6">

    <!-- Top Header -->
    <div class="flex items-center justify-between bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 tracking-tight flex items-center space-x-2">
                <i data-lucide="sliders" class="w-6 h-6 text-orange-600"></i>
                <span><?= $isEdit ? 'Edit Filter Attribute' : 'Create New Filter Attribute' ?></span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Configure attribute settings, allowed filter options, and category assignments.</p>
        </div>
        <a href="<?= url('admin/filters') ?>" class="h-9 px-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition flex items-center space-x-1.5 cursor-pointer text-decoration-none">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Filters</span>
        </a>
    </div>

    <!-- FORM CARD -->
    <form action="<?= $actionUrl ?>" method="POST" class="bg-white rounded-2xl border border-slate-200 shadow-xs p-6 space-y-6">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Attribute Name -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Attribute Name *</label>
                <input type="text" name="name" value="<?= htmlspecialchars($attribute['name'] ?? '') ?>" placeholder="e.g. Material, Color, Stone Type" required class="w-full h-10 px-3.5 bg-white border border-slate-300 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-orange-600 transition">
            </div>

            <!-- Attribute Type -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Filter Selection Type *</label>
                <select name="type" class="w-full h-10 px-3.5 bg-white border border-slate-300 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-orange-600 transition">
                    <option value="multi_select" <?= ($attribute['type'] ?? '') === 'multi_select' ? 'selected' : '' ?>>Multi-Select Checkboxes (OR logic)</option>
                    <option value="single_select" <?= ($attribute['type'] ?? '') === 'single_select' ? 'selected' : '' ?>>Single-Select Radio / Dropdown</option>
                    <option value="range" <?= ($attribute['type'] ?? '') === 'range' ? 'selected' : '' ?>>Numeric Range (Min / Max Inputs)</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-100">
            <!-- Scope / Global -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Scope Availability</label>
                <select name="is_global" id="isGlobalSelect" onchange="toggleCategorySelect(this.value)" class="w-full h-10 px-3.5 bg-white border border-slate-300 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-orange-600 transition">
                    <option value="1" <?= ($attribute['is_global'] ?? 1) == 1 ? 'selected' : '' ?>>Global (Applies to All Categories)</option>
                    <option value="0" <?= ($attribute['is_global'] ?? 1) == 0 ? 'selected' : '' ?>>Category Specific (Select below)</option>
                </select>
            </div>

            <!-- Sort Order -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Sort Priority</label>
                <input type="number" name="sort_order" value="<?= htmlspecialchars((string)($attribute['sort_order'] ?? 0)) ?>" placeholder="0" class="w-full h-10 px-3.5 bg-white border border-slate-300 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-orange-600 transition">
            </div>

            <!-- Active Status -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Status</label>
                <select name="is_active" class="w-full h-10 px-3.5 bg-white border border-slate-300 rounded-xl text-xs font-medium text-slate-900 focus:outline-none focus:border-orange-600 transition">
                    <option value="1" <?= ($attribute['is_active'] ?? 1) == 1 ? 'selected' : '' ?>>Active (Visible on Storefront)</option>
                    <option value="0" <?= ($attribute['is_active'] ?? 1) == 0 ? 'selected' : '' ?>>Inactive (Hidden)</option>
                </select>
            </div>
        </div>

        <!-- Category Assignment Selection -->
        <div id="categorySelectBox" style="display: <?= ($attribute['is_global'] ?? 1) == 0 ? 'block' : 'none' ?>;" class="pt-4 border-t border-slate-100">
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Assign Categories *</label>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-48 overflow-y-auto p-3 bg-slate-50 border border-slate-200 rounded-xl">
                <?php foreach ($categories as $c): ?>
                    <?php $isChecked = in_array($c['id'], $assignedCategoryIds); ?>
                    <label class="flex items-center space-x-2 text-xs text-slate-700 font-medium cursor-pointer">
                        <input type="checkbox" name="category_ids[]" value="<?= $c['id'] ?>" <?= $isChecked ? 'checked' : '' ?> class="rounded border-slate-300 text-orange-600 focus:ring-orange-500">
                        <span><?= htmlspecialchars($c['name']) ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Filter Options Section -->
        <div class="pt-4 border-t border-slate-100">
            <label class="block text-xs font-semibold text-slate-700 uppercase tracking-wider mb-2">Filter Option Values</label>
            
            <?php if ($isEdit): ?>
                <!-- Existing Options Table -->
                <div class="space-y-2 mb-4">
                    <?php foreach ($options as $opt): ?>
                        <div class="flex items-center space-x-3 bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                            <input type="text" name="existing_options[<?= $opt['id'] ?>]" value="<?= htmlspecialchars($opt['value']) ?>" class="flex-1 h-8 px-3 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-900">
                            <span class="text-[11px] text-slate-400">Order:</span>
                            <input type="number" name="existing_option_sort[<?= $opt['id'] ?>]" value="<?= $opt['sort_order'] ?>" class="w-16 h-8 px-2 bg-white border border-slate-200 rounded-lg text-xs font-medium text-slate-900 text-center">
                            <button type="button" onclick="deleteOptionRow(<?= $opt['id'] ?>, this)" class="p-1.5 text-slate-400 hover:text-red-600 transition" title="Delete Option">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Add New Option Input -->
                <div class="flex items-center space-x-3 bg-orange-50/50 p-3 rounded-xl border border-orange-200">
                    <input type="text" name="new_option_value" placeholder="+ Add a new option value (e.g. Rose Gold)" class="flex-1 h-9 px-3 bg-white border border-slate-300 rounded-lg text-xs font-medium text-slate-900 focus:outline-none">
                    <span class="text-xs font-semibold text-orange-700">Type value & save form</span>
                </div>
            <?php else: ?>
                <!-- Multi-line Text Area for Initial Create -->
                <p class="text-xs text-slate-500 mb-2">Enter option values separated by newline (one value per line):</p>
                <textarea name="options_text" rows="5" placeholder="Gold&#10;Silver&#10;Rose Gold&#10;Brass Alloy" class="w-full p-3 bg-white border border-slate-300 rounded-xl text-xs font-mono text-slate-900 focus:outline-none focus:border-orange-600 transition"></textarea>
            <?php endif; ?>
        </div>

        <!-- Submit Buttons -->
        <div class="flex items-center justify-end space-x-3 pt-6 border-t border-slate-100">
            <a href="<?= url('admin/filters') ?>" class="h-10 px-5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition flex items-center justify-center text-decoration-none">
                Cancel
            </a>
            <button type="submit" class="h-10 px-6 bg-orange-600 hover:bg-orange-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center cursor-pointer">
                <?= $isEdit ? 'Save Changes' : 'Create Filter Attribute' ?>
            </button>
        </div>

    </form>

</div>

<script>
function toggleCategorySelect(isGlobalVal) {
    const box = document.getElementById('categorySelectBox');
    if (box) {
        box.style.display = (isGlobalVal === '0') ? 'block' : 'none';
    }
}

function deleteOptionRow(optionId, btnElem) {
    if (!confirm('Are you sure you want to delete this option value?')) return;

    const formData = new FormData();
    formData.append('option_id', optionId);

    fetch('<?= url('admin/filters/delete-option') ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            btnElem.closest('.flex').remove();
        } else {
            alert(res.error || 'Failed to delete option');
        }
    });
}
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>
