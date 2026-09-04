<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6">

    <!-- Top Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-5 sm:p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <h1 class="text-xl font-semibold text-slate-900 tracking-tight flex items-center space-x-2">
                <i data-lucide="sliders" class="w-6 h-6 text-orange-600"></i>
                <span>Filter & Attribute Management</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">Configure global and category-specific filters, option values, and storefront filter behavior.</p>
        </div>
        <a href="<?= url('admin/filters/create') ?>" class="h-10 px-4 bg-orange-600 hover:bg-orange-700 active:bg-orange-800 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center space-x-2 shrink-0 cursor-pointer text-decoration-none">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add New Filter Attribute</span>
        </a>
    </div>

    <!-- MAIN FILTERS TABLE CARD -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="text-xs text-slate-500 font-medium">
                <span>Active Filter Attributes: <strong class="text-slate-900"><?= count($attributes) ?></strong></span>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold text-[10px]">
                    <tr>
                        <th class="px-6 py-3.5">Sort</th>
                        <th class="px-6 py-3.5">Attribute Name</th>
                        <th class="px-6 py-3.5">Type</th>
                        <th class="px-6 py-3.5">Scope / Categories</th>
                        <th class="px-6 py-3.5">Option Count</th>
                        <th class="px-6 py-3.5">Status</th>
                        <th class="px-6 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    <?php if (!empty($attributes)): ?>
                        <?php foreach ($attributes as $attr): ?>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="px-6 py-4 text-slate-400 font-mono">#<?= $attr['sort_order'] ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-slate-900 text-sm"><?= htmlspecialchars($attr['name']) ?></div>
                                    <div class="text-[11px] text-slate-400 font-mono">slug: <?= htmlspecialchars($attr['slug']) ?></div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-700">
                                        <?= str_replace('_', ' ', ucfirst($attr['type'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if ($attr['is_global']): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Global (All Categories)
                                        </span>
                                    <?php else: ?>
                                        <div class="flex flex-wrap gap-1">
                                            <?php foreach ($attr['category_names'] as $cName): ?>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10.5px] bg-slate-100 text-slate-600">
                                                    <?= htmlspecialchars($cName) ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-semibold text-slate-900"><?= $attr['options_count'] ?> options</span>
                                </td>
                                <td class="px-6 py-4">
                                    <button type="button" onclick="toggleAttrStatus(<?= $attr['id'] ?>, <?= $attr['is_active'] ? 0 : 1 ?>)" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold transition cursor-pointer <?= $attr['is_active'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-400 border border-slate-200' ?>">
                                        <span class="w-1.5 h-1.5 rounded-full <?= $attr['is_active'] ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
                                        <?= $attr['is_active'] ? 'Active' : 'Inactive' ?>
                                    </button>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="<?= url('admin/filters/edit/' . $attr['id']) ?>" class="p-1.5 text-slate-500 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition" title="Edit Filter">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </a>
                                        <form action="<?= url('admin/filters/delete/' . $attr['id']) ?>" method="POST" onsubmit="return confirm('Are you sure you want to delete this filter attribute?');" class="inline">
                                            <button type="submit" class="p-1.5 text-slate-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Delete Filter">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                No filter attributes created yet. Click "Add New Filter Attribute" to begin.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function toggleAttrStatus(id, newStatus) {
    const formData = new FormData();
    formData.append('id', id);
    formData.append('is_active', newStatus);

    fetch('<?= url('admin/filters/toggle-status') ?>', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            window.location.reload();
        } else {
            alert(res.error || 'Failed to toggle status');
        }
    });
}
</script>

<?php
include __DIR__ . '/../layouts/footer.php';
?>
