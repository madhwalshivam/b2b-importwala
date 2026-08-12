<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6 font-sans" x-data="{ 
    showAddModal: false, 
    showEditModal: false, 
    editData: { id: 0, name: '', description: '', permissions: [] },
    openEdit(r) {
        this.editData = {
            id: r.id,
            name: r.name || '',
            description: r.description || '',
            permissions: (r.permission_ids || []).map(String)
        };
        this.showEditModal = true;
    }
}">

    <!-- Top Header -->
    <div
        class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span
                    class="px-2.5 py-1 text-[11px] font-semibold uppercase bg-red-50 text-red-600 rounded-lg tracking-wider border border-red-100">
                    Access Control
                </span>
                <span class="text-slate-400 text-xs">•</span>
                <span class="text-xs text-slate-500 font-medium">Role Matrix</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Custom Roles & Permissions Matrix</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Configure module-level access permissions per employee role using interactive permission checkboxes.
            </p>
        </div>

        <?php if (\App\Core\Auth::hasPermission('roles.add')): ?>
            <button @click="showAddModal = true"
                class="h-10 px-5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition flex items-center space-x-2 cursor-pointer shadow-xs">
                <i data-lucide="shield-plus" class="w-4 h-4 text-white"></i>
                <span>Add Custom Role</span>
            </button>
        <?php endif; ?>
    </div>



    <!-- Roles Table -->
    <div class="bg-white rounded-2xl border border-gray-900 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr
                        class="bg-gray-50/80 border-b border-gray-900 text-gray-500 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="p-4 pl-6">Role Title</th>
                        <th class="p-4">Assigned Employees</th>
                        <th class="p-4">Configured Permissions</th>
                        <th class="p-4 pr-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($roles as $r): ?>
                        <tr class="hover:bg-gray-50/60 transition-colors">
                            <td class="p-4 pl-6">
                                <div class="flex items-center space-x-3">
                                    <div
                                        class="w-8 h-8 rounded-xl bg-red-50 text-red-600 border border-red-100 flex items-center justify-center shrink-0 font-bold">
                                        <i data-lucide="shield" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <h4 class="font-semibold text-gray-900 text-xs"><?= htmlspecialchars($r['name']) ?>
                                        </h4>
                                        <p class="text-[10px] text-gray-500">
                                            <?= htmlspecialchars($r['description'] ?: 'Custom staff access profile') ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                <?php $activeCnt = (int) ($r['active_employee_count'] ?? 0); ?>
                                <span
                                    class="px-2.5 py-1 text-[11px] font-semibold rounded-lg <?= $activeCnt > 0 ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-gray-100 text-gray-600 border border-gray-900' ?>">
                                    <?= $activeCnt ?> Active <?= $activeCnt === 1 ? 'Employee' : 'Employees' ?>
                                </span>
                            </td>
                            <td class="p-4">
                                <?php $permCnt = (int) ($r['permissions_count'] ?? 0); ?>
                                <span
                                    class="px-2.5 py-1 text-[11px] font-semibold bg-slate-100 text-slate-800 rounded-lg border border-slate-200">
                                    <?= $permCnt ?>     <?= $permCnt === 1 ? 'Permission' : 'Permissions' ?> Granted
                                </span>
                            </td>
                            <td class="p-4 pr-6 text-right whitespace-nowrap">
                                <?php if (\App\Core\Auth::hasPermission('roles.edit')): ?>
                                    <div class="inline-flex items-center space-x-2">
                                        <button type="button" @click="openEdit(<?= htmlspecialchars(json_encode($r)) ?>)"
                                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-red-50 hover:bg-red-600 text-red-600 hover:text-white border border-red-200 rounded-xl font-semibold text-xs transition cursor-pointer">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            <span>Edit Matrix</span>
                                        </button>
                                        <a href="<?= url('admin/roles/edit/' . $r['id']) ?>"
                                            class="px-2.5 py-1.5 text-gray-500 hover:text-gray-900 hover:bg-gray-100 rounded-xl font-medium text-xs transition">
                                            Full Page
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ADD ROLE MODAL -->
    <div x-show="showAddModal"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 overflow-y-auto"
        x-cloak>
        <div
            class="bg-white max-w-2xl w-full p-6 rounded-2xl border border-gray-900 shadow-2xl space-y-4 my-auto max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 shrink-0">
                <h3 class="text-base font-semibold text-gray-900">Create Custom Employee Role</h3>
                <button type="button" @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="<?= url('admin/roles/store') ?>" method="POST" class="space-y-4 text-xs overflow-y-auto pr-1">
                <?= csrf_field() ?>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Role Title <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Store Manager / Catalog Lead"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Role Description</label>
                    <input type="text" name="description" placeholder="Brief summary of duties and authority"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <!-- Checkbox Permission Grid -->
                <div>
                    <label class="block font-semibold text-gray-900 text-xs uppercase tracking-wider mb-2">Assign
                        Permissions:</label>

                    <div class="space-y-3 max-h-60 overflow-y-auto p-3 bg-gray-50 rounded-xl border border-gray-900">
                        <?php foreach ($groupedPermissions as $module => $perms): ?>
                            <div class="bg-white p-3 rounded-xl border border-gray-900">
                                <span
                                    class="font-bold text-red-600 uppercase text-[11px] block mb-2 tracking-wider"><?= uppercase_words($module) ?>
                                    MODULE</span>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    <?php foreach ($perms as $p): ?>
                                        <label
                                            class="flex items-center space-x-2 text-xs font-semibold text-gray-800 cursor-pointer hover:text-red-600">
                                            <input type="checkbox" name="permissions[]" value="<?= $p['id'] ?>"
                                                class="rounded text-red-600 focus:ring-red-500">
                                            <span><?= htmlspecialchars($p['name']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100 shrink-0">
                    <button type="button" @click="showAddModal = false"
                        class="h-10 px-4 bg-gray-100 text-gray-700 font-semibold rounded-xl text-xs">Cancel</button>
                    <button type="submit"
                        class="h-10 px-6 bg-red-600 text-white font-semibold rounded-xl text-xs hover:bg-red-700 transition shadow-xs cursor-pointer">Save
                        Role</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT ROLE MODAL -->
    <div x-show="showEditModal"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4 overflow-y-auto"
        x-cloak>
        <div
            class="bg-white max-w-2xl w-full p-6 rounded-2xl border border-gray-900 shadow-2xl space-y-4 my-auto max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3 shrink-0">
                <h3 class="text-base font-semibold text-gray-900">Edit Role & Permissions Matrix</h3>
                <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'<?= url('admin/roles/update/') ?>' + editData.id" method="POST"
                class="space-y-4 text-xs overflow-y-auto pr-1">
                <?= csrf_field() ?>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Role Title <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="editData.name" required
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Role Description</label>
                    <input type="text" name="description" x-model="editData.description"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <!-- Checkbox Permission Grid -->
                <div>
                    <label class="block font-semibold text-gray-900 text-xs uppercase tracking-wider mb-2">Modify
                        Permissions Matrix:</label>

                    <div class="space-y-3 max-h-60 overflow-y-auto p-3 bg-gray-50 rounded-xl border border-gray-900">
                        <?php foreach ($groupedPermissions as $module => $perms): ?>
                            <div class="bg-white p-3 rounded-xl border border-gray-900">
                                <span
                                    class="font-bold text-red-600 uppercase text-[11px] block mb-2 tracking-wider"><?= uppercase_words($module) ?>
                                    MODULE</span>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                    <?php foreach ($perms as $p): ?>
                                        <label
                                            class="flex items-center space-x-2 text-xs font-semibold text-gray-800 cursor-pointer hover:text-red-600">
                                            <input type="checkbox" name="permissions[]" value="<?= $p['id'] ?>"
                                                :checked="editData.permissions.includes('<?= $p['id'] ?>')"
                                                class="rounded text-red-600 focus:ring-red-500">
                                            <span><?= htmlspecialchars($p['name']) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100 shrink-0">
                    <button type="button" @click="showEditModal = false"
                        class="h-10 px-4 bg-gray-100 text-gray-700 font-semibold rounded-xl text-xs">Cancel</button>
                    <button type="submit"
                        class="h-10 px-6 bg-red-600 text-white font-semibold rounded-xl text-xs hover:bg-red-700 transition shadow-xs cursor-pointer">Update
                        Permissions</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
function uppercase_words($str)
{
    return strtoupper(str_replace('_', ' ', $str));
}
include __DIR__ . '/../layouts/footer.php';
?>