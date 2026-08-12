<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6" x-data="{ showModal: false }" x-effect="document.body.classList.toggle('modal-open', showModal)">

    <div class="flex items-center justify-between bg-white p-6 rounded-[10px] border border-gray-900">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Scooter Models Directory</h2>
            <p class="text-xs text-gray-500 mt-0.5">Manage models (S1 Pro, S1 X, 450X, Rizta, iQube, Chetak)</p>
        </div>

        <?php if (\App\Core\Auth::hasPermission('scooter_models.add')): ?>
            <button @click="showModal = true"
                class="h-12 px-5 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-[10px] transition flex items-center space-x-2">
                <i data-lucide="plus" class="w-4 h-4 text-white"></i>
                <span>Add Scooter Model</span>
            </button>
        <?php endif; ?>
    </div>

    <!-- Models Table -->
    <div class="bg-white rounded-[10px] border border-gray-900 overflow-hidden">
        <table class="w-full text-xs text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-900 text-gray-500 font-semibold uppercase tracking-wider">
                    <th class="p-4">Scooter Brand</th>
                    <th class="p-4">Model Name</th>
                    <th class="p-4">Year / Generation</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($models as $m): ?>
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                        <td class="p-4 font-semibold text-red-600"><?= htmlspecialchars($m['brand_name']) ?></td>
                        <td class="p-4 font-semibold text-gray-900"><?= htmlspecialchars($m['name']) ?></td>
                        <td class="p-4 text-gray-600"><?= htmlspecialchars($m['year_generation'] ?: 'All Years') ?></td>
                        <td class="p-4">
                            <span
                                class="px-2.5 py-1 text-[10px] font-semibold rounded-[10px] uppercase <?= $m['status'] === 'active' ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600' ?>">
                                <?= $m['status'] ?>
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <?php if (\App\Core\Auth::hasPermission('scooter_models.delete')): ?>
                                <form action="<?= url('admin/scooter-models/delete/' . $m['id']) ?>" method="POST"
                                    class="inline">
                                    <?= csrf_field() ?>
                                    <button type="submit"
                                        class="px-3 py-1.5 bg-red-50 text-red-600 rounded-[10px] font-semibold text-xs hover:bg-red-600 hover:text-white transition inline-flex items-center space-x-1"
                                        title="Delete model">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        <span>Delete</span>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Add Scooter Model Modal -->
    <div x-show="showModal"
        class="fixed inset-0 bg-slate-950/70 backdrop-blur-xs z-[99999] flex items-center justify-center p-3 sm:p-6 overflow-y-auto"
        x-cloak>
        <div
            class="bg-white max-w-md w-full p-6 rounded-2xl border border-gray-900 shadow-2xl space-y-4 my-auto z-[100000] max-h-[90vh] sm:max-h-[85vh] overflow-y-auto flex flex-col">
            <h3 class="text-base font-semibold text-gray-900">Add New Scooter Model</h3>

            <form action="<?= url('admin/scooter-models/store') ?>" method="POST" class="space-y-4 text-xs">
                <?= csrf_field() ?>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Select Brand *</label>
                    <select name="brand_id" required
                        class="w-full h-12 px-3 bg-gray-50 border border-gray-900 rounded-[10px] font-semibold">
                        <?php foreach ($brands as $b): ?>
                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Scooter Model Name *</label>
                    <input type="text" name="name" required placeholder="e.g. S1 Pro (Gen 2)"
                        class="w-full h-12 px-4 bg-gray-50 border border-gray-900 rounded-[10px]">
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Year / Generation (Optional)</label>
                    <input type="text" name="year_generation" placeholder="2023-2026"
                        class="w-full h-12 px-4 bg-gray-50 border border-gray-900 rounded-[10px]">
                </div>

                <div class="flex justify-end space-x-2 pt-4 border-t border-gray-100">
                    <button type="button" @click="showModal = false"
                        class="h-10 px-4 bg-gray-100 text-gray-700 font-semibold rounded-[10px]">Cancel</button>
                    <button type="submit"
                        class="h-10 px-6 bg-red-600 text-white font-semibold rounded-[10px] hover:bg-red-700 transition">Save
                        Model</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>