<?php
include __DIR__ . '/../layouts/header.php';
?>

<div class="space-y-6">

    <div class="flex items-center justify-between bg-white p-6 rounded-[10px] border border-gray-900">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Employee Activity Audit Logs</h2>
            <p class="text-xs text-gray-500 mt-0.5">Track all employee login, creation, modification, and deletion
                actions</p>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-[10px] border border-gray-900 overflow-hidden">
        <table class="w-full text-xs text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-900 text-gray-500 font-semibold uppercase tracking-wider">
                    <th class="p-4">Employee</th>
                    <th class="p-4">Action & Module</th>
                    <th class="p-4">Details</th>
                    <th class="p-4">IP Address</th>
                    <th class="p-4">Date & Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-400">No activity logs recorded.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $l): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition">
                            <td class="p-4 font-semibold text-gray-900"><?= htmlspecialchars($l['admin_name']) ?></td>
                            <td class="p-4">
                                <span class="font-semibold text-red-600 block"><?= htmlspecialchars($l['action']) ?></span>
                                <span
                                    class="text-[10px] text-gray-400 uppercase tracking-wide"><?= htmlspecialchars($l['module']) ?></span>
                            </td>
                            <td class="p-4 text-gray-600 max-w-sm font-mono text-[11px]"><?= htmlspecialchars($l['details']) ?>
                            </td>
                            <td class="p-4 font-mono text-gray-500"><?= htmlspecialchars($l['ip_address']) ?></td>
                            <td class="p-4 text-gray-500"><?= date('d M Y, h:i:s A', strtotime($l['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="p-4 border-t border-gray-100">
            <?= $paginator->render() ?>
        </div>
    </div>

</div>

<?php
include __DIR__ . '/../layouts/footer.php';
?>