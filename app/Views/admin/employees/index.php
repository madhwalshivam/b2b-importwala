<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6 font-sans" x-data="{ 
    showAddModal: false, 
    showEditModal: false, 
    editData: { id: 0, name: '', email: '', phone: '', username: '', role_id: 1, status: 'active' },
    openEdit(emp) {
        this.editData = {
            id: emp.id,
            name: emp.name || '',
            email: emp.email || '',
            phone: emp.phone || '',
            username: emp.username || '',
            role_id: emp.role_id || 1,
            status: emp.status || 'active'
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
                <span class="text-xs text-slate-500 font-medium">Employee Management</span>
            </div>
            <h1 class="text-2xl font-semibold text-slate-900 mt-1 tracking-tight">Employees & Staff Manager</h1>
            <p class="text-xs text-slate-500 mt-0.5 font-medium max-w-2xl">
                Manage staff credentials, active accounts, and assign custom role-based access permissions (RBAC).
            </p>
        </div>

        <?php if (\App\Core\Auth::hasPermission('employees.add')): ?>
            <button @click="showAddModal = true"
                class="h-10 px-5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl transition flex items-center space-x-2 cursor-pointer shadow-xs">
                <i data-lucide="user-plus" class="w-4 h-4 text-white"></i>
                <span>Add Employee Account</span>
            </button>
        <?php endif; ?>
    </div>



    <!-- Employees Table -->
    <div class="bg-white rounded-2xl border border-gray-900 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse">
                <thead>
                    <tr
                        class="bg-gray-50/80 border-b border-gray-900 text-gray-500 font-semibold uppercase tracking-wider text-[11px]">
                        <th class="p-4 pl-6">Employee</th>
                        <th class="p-4">Username / Email</th>
                        <th class="p-4">Assigned Role</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 pr-6 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="5" class="p-8 text-center text-gray-400 font-medium">No employee accounts found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $emp): ?>
                            <?php
                            // Compute initials for clean SVG/HTML avatar badge
                            $nameParts = explode(' ', trim($emp['name']));
                            $initials = strtoupper(substr($nameParts[0] ?? 'E', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
                            ?>
                            <tr class="hover:bg-gray-50/60 transition-colors">
                                <td class="p-4 pl-6">
                                    <div class="flex items-center space-x-3">
                                        <!-- Clean initials avatar circle -->
                                        <div
                                            class="w-9 h-9 rounded-xl bg-slate-900 text-white font-bold text-xs flex items-center justify-center shrink-0 border border-slate-800 shadow-xs">
                                            <?= $initials ?>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900 text-xs">
                                                <?= htmlspecialchars($emp['name']) ?></h4>
                                            <span class="text-[10px] text-gray-500 font-mono">Phone:
                                                <?= htmlspecialchars($emp['phone'] ?: 'N/A') ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4">
                                    <span
                                        class="font-semibold text-gray-800 block"><?= htmlspecialchars($emp['username']) ?></span>
                                    <span
                                        class="text-gray-500 text-[10px] font-mono"><?= htmlspecialchars($emp['email']) ?></span>
                                </td>
                                <td class="p-4">
                                    <span
                                        class="px-2.5 py-1 text-[10px] font-semibold bg-gray-100 text-gray-800 rounded-lg border border-gray-900">
                                        <?= htmlspecialchars($emp['role_name'] ?? 'Staff') ?>
                                    </span>
                                </td>
                                <td class="p-4">
                                    <span
                                        class="px-2.5 py-1 text-[10px] font-semibold rounded-full uppercase tracking-wider <?= strtolower($emp['status']) === 'active' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
                                        <?= htmlspecialchars($emp['status']) ?>
                                    </span>
                                </td>
                                <td class="p-4 pr-6 text-right whitespace-nowrap">
                                    <div class="inline-flex items-center justify-end space-x-2">
                                        <!-- EDIT BUTTON FOR ALL EMPLOYEES -->
                                        <button type="button" @click="openEdit(<?= htmlspecialchars(json_encode($emp)) ?>)"
                                            class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 rounded-xl font-semibold text-xs transition cursor-pointer">
                                            <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                            <span>Edit</span>
                                        </button>

                                        <!-- DELETE BUTTON (Excluding Super Admin ID 1) -->
                                        <?php if ((int) $emp['id'] !== 1 && \App\Core\Auth::hasPermission('employees.delete')): ?>
                                            <form action="<?= url('admin/employees/delete/' . $emp['id']) ?>" method="POST"
                                                data-confirm="Are you sure you want to delete employee <?= htmlspecialchars($emp['name']) ?>?"
                                                class="inline">
                                                <?= csrf_field() ?>
                                                <button type="submit"
                                                    class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 rounded-xl font-semibold text-xs transition cursor-pointer">
                                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                        <?php elseif ((int) $emp['id'] === 1): ?>
                                            <span
                                                class="px-2.5 py-1 text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 rounded-lg">Protected</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ADD EMPLOYEE MODAL -->
    <div x-show="showAddModal"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white max-w-lg w-full p-6 rounded-2xl border border-gray-900 shadow-2xl space-y-4 my-auto">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-semibold text-gray-900">Create Employee Account</h3>
                <button type="button" @click="showAddModal = false" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form action="<?= url('admin/employees/store') ?>" method="POST" class="grid grid-cols-2 gap-4 text-xs">
                <?= csrf_field() ?>

                <div class="col-span-2">
                    <label class="block font-semibold text-gray-700 mb-1">Employee Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="e.g. Rahul Sharma"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Email Address <span
                            class="text-red-500">*</span></label>
                    <input type="email" name="email" required placeholder="rahul@mudsor.com"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" name="phone" placeholder="9876543210"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Login Username <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="username" required placeholder="rahul_sales"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Password <span
                            class="text-red-500">*</span></label>
                    <input type="password" name="password" required placeholder="••••••••"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Assigned Role <span
                            class="text-red-500">*</span></label>
                    <select name="role_id" required
                        class="w-full h-11 px-3 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600 cursor-pointer">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Account Status</label>
                    <select name="status"
                        class="w-full h-11 px-3 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600 cursor-pointer">
                        <option value="active" selected>Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="col-span-2 flex justify-end space-x-2 pt-4 border-t border-gray-100">
                    <button type="button" @click="showAddModal = false"
                        class="h-10 px-4 bg-gray-100 text-gray-700 font-semibold rounded-xl text-xs">Cancel</button>
                    <button type="submit"
                        class="h-10 px-6 bg-red-600 text-white font-semibold rounded-xl text-xs hover:bg-red-700 transition cursor-pointer shadow-xs">Create
                        Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT EMPLOYEE MODAL -->
    <div x-show="showEditModal"
        class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4" x-cloak>
        <div class="bg-white max-w-lg w-full p-6 rounded-2xl border border-gray-900 shadow-2xl space-y-4 my-auto">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h3 class="text-base font-semibold text-gray-900">Edit Employee Account</h3>
                <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-600">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form :action="'<?= url('admin/employees/update/') ?>' + editData.id" method="POST"
                class="grid grid-cols-2 gap-4 text-xs">
                <?= csrf_field() ?>

                <div class="col-span-2">
                    <label class="block font-semibold text-gray-700 mb-1">Employee Name <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="editData.name" required
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Email Address <span
                            class="text-red-500">*</span></label>
                    <input type="email" name="email" x-model="editData.email" required
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Phone Number</label>
                    <input type="tel" name="phone" x-model="editData.phone"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Login Username <span
                            class="text-red-500">*</span></label>
                    <input type="text" name="username" x-model="editData.username" required
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">New Password (Optional)</label>
                    <input type="password" name="password" placeholder="Leave blank to keep unchanged"
                        class="w-full h-11 px-4 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Assigned Role <span
                            class="text-red-500">*</span></label>
                    <select name="role_id" x-model="editData.role_id" required
                        class="w-full h-11 px-3 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600 cursor-pointer">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Account Status</label>
                    <select name="status" x-model="editData.status"
                        class="w-full h-11 px-3 bg-gray-50 border border-gray-900 rounded-xl font-semibold text-gray-900 focus:outline-none focus:border-red-600 cursor-pointer">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>

                <div class="col-span-2 flex justify-end space-x-2 pt-4 border-t border-gray-100">
                    <button type="button" @click="showEditModal = false"
                        class="h-10 px-4 bg-gray-100 text-gray-700 font-semibold rounded-xl text-xs">Cancel</button>
                    <button type="submit"
                        class="h-10 px-6 bg-red-600 text-white font-semibold rounded-xl text-xs hover:bg-red-700 transition cursor-pointer shadow-xs">Save
                        Changes</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>