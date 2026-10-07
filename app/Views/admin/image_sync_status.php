<?php
$title = 'Image Sync Status';
require_once __DIR__ . '/layouts/header.php';
require_once __DIR__ . '/layouts/sidebar.php';
?>

<div class="flex-1 flex flex-col min-w-0 bg-slate-50 relative">
    <div class="h-16 px-6 bg-white border-b border-slate-200 flex items-center justify-between shrink-0 sticky top-0 z-20">
        <h1 class="text-lg font-bold text-slate-800">Image Sync Status</h1>
    </div>

    <div class="p-6 overflow-y-auto flex-1">
        <?php if (isset($_GET['retry_processed'])): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-xl mb-6 shadow-sm text-sm font-medium">
                Retried <?= (int) $_GET['retry_processed'] ?> failed image<?= (int) $_GET['retry_processed'] === 1 ? '' : 's' ?>:
                <?= (int) ($_GET['retry_done'] ?? 0) ?> saved,
                <?= (int) ($_GET['retry_failed'] ?? 0) ?> still failed.
            </div>
        <?php endif; ?>
        <?php if (!$tableExists): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl mb-6 shadow-sm">
                <h3 class="font-bold text-red-800 mb-2">Error: Missing Queue Table</h3>
                <p class="mb-4">The `image_mirror_queue` table does not exist in the database. Please create it manually.</p>
                <textarea class="w-full text-xs font-mono p-3 border border-red-300 rounded bg-white text-slate-800 h-48" readonly><?= htmlspecialchars($createSql) ?></textarea>
            </div>
        <?php else: ?>
            
            <!-- Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white border border-slate-200 p-5 rounded-xl shadow-sm">
                    <div class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Pending</div>
                    <div class="text-2xl font-black text-amber-600"><?= number_format($stats['pending'] ?? 0) ?></div>
                </div>
                <div class="bg-white border border-slate-200 p-5 rounded-xl shadow-sm">
                    <div class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Processing</div>
                    <div class="text-2xl font-black text-blue-600"><?= number_format($stats['processing'] ?? 0) ?></div>
                </div>
                <div class="bg-white border border-slate-200 p-5 rounded-xl shadow-sm">
                    <div class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Done</div>
                    <div class="text-2xl font-black text-emerald-600"><?= number_format($stats['done'] ?? 0) ?></div>
                </div>
                <div class="bg-white border border-slate-200 p-5 rounded-xl shadow-sm">
                    <div class="text-slate-500 text-xs font-bold uppercase tracking-wider mb-1">Failed</div>
                    <div class="text-2xl font-black text-red-600"><?= number_format($stats['failed'] ?? 0) ?></div>
                </div>
            </div>


            <?php if (!empty($recentErrors)): ?>
                <!-- Recent Errors -->
                <div class="bg-white border border-slate-200 p-0 rounded-xl shadow-sm mb-6 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between gap-3">
                        <h3 class="font-bold text-slate-800">Recent Failed Syncs</h3>
                        <form method="POST" action="<?= url('admin/image-sync-status/retry') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="inline-flex items-center px-3 py-1.5 rounded-lg bg-[#f05a29] text-white text-xs font-bold hover:bg-[#d94e22]">
                                Retry failed
                            </button>
                        </form>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600">
                            <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                                <tr>
                                    <th class="px-6 py-3 font-semibold">Source URL</th>
                                    <th class="px-6 py-3 font-semibold text-center">Attempts</th>
                                    <th class="px-6 py-3 font-semibold">Last Error</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($recentErrors as $err): ?>
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-4 break-all max-w-xl" title="<?= htmlspecialchars($err['source_url']) ?>">
                                            <a href="<?= htmlspecialchars($err['source_url']) ?>" target="_blank" class="text-blue-600 hover:underline"><?= htmlspecialchars($err['source_url']) ?></a>
                                        </td>
                                        <td class="px-6 py-4 text-center font-mono"><?= $err['attempts'] ?></td>
                                        <td class="px-6 py-4 text-xs text-red-600 break-words max-w-md"><?= htmlspecialchars($err['last_error'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>


<?php require_once __DIR__ . '/layouts/footer.php'; ?>
