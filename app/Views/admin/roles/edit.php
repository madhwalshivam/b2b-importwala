<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between bg-white p-6 rounded-3xl shadow-sm border border-gray-900">
        <div>
            <span class="text-xs font-semibold text-red-600 uppercase">RBAC Configuration</span>
            <h2 class="text-xl font-black text-gray-900">Edit Role Permissions: <?= htmlspecialchars($role['name']) ?>
            </h2>
        </div>
        <a href="<?= url('admin/roles') ?>"
            class="px-4 py-2 bg-gray-100 text-gray-700 font-semibold text-xs rounded-xl hover:bg-gray-900 transition">Back
            to Roles</a>
    </div>

    <form action="<?= url('admin/roles/update/' . $role['id']) ?>" method="POST"
        class="bg-white p-8 rounded-3xl shadow-sm border border-gray-900 space-y-6">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Role Title *</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($role['name']) ?>"
                    class="w-full p-3 bg-gray-50 border border-gray-300 rounded-xl font-semibold">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Description</label>
                <input type="text" name="description" value="<?= htmlspecialchars($role['description']) ?>"
                    class="w-full p-3 bg-gray-50 border border-gray-300 rounded-xl">
            </div>
        </div>

        <div class="border-t pt-4">
            <h3 class="font-black text-gray-900 text-base mb-4">Module Permissions Checkboxes</h3>

            <div class="space-y-4">
                <?php foreach ($groupedPermissions as $module => $perms): ?>
                    <div class="bg-gray-50 p-4 rounded-2xl border border-gray-900 space-y-2">
                        <span
                            class="font-semibold text-red-600 uppercase text-xs tracking-wider block"><?= strtoupper(str_replace('_', ' ', $module)) ?>
                            MODULE</span>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                            <?php foreach ($perms as $p): ?>
                                <label class="flex items-center space-x-2 font-semibold text-gray-800 cursor-pointer">
                                    <input type="checkbox" name="permissions[]" value="<?= $p['id'] ?>" <?= in_array($p['id'], $rolePermissions) ? 'checked' : '' ?> class="rounded text-red-600">
                                    <span><?= htmlspecialchars($p['name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t">
            <button type="submit"
                class="px-8 py-3.5 bg-red-600 text-white font-semibold text-xs rounded-xl shadow-lg hover:bg-red-700 transition">Update
                Role Permissions</button>
        </div>

    </form>

</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>