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

            <!-- Self-Test Button -->
            <div class="bg-white border border-slate-200 p-6 rounded-xl shadow-sm mb-6">
                <h3 class="font-bold text-slate-800 mb-2">Cloudflare R2 Self-Test</h3>
                <p class="text-slate-500 text-sm mb-4">Run the R2 self-test to verify credentials, upload capabilities, and public URL configuration.</p>
                
                <button type="button" id="btnSelftest" onclick="runSelftest()" class="px-5 py-2.5 bg-slate-800 text-white font-semibold rounded-xl text-sm hover:bg-slate-700 transition flex items-center space-x-2 cursor-pointer">
                    <i data-lucide="shield-check" class="w-4 h-4"></i>
                    <span>Run Self-Test</span>
                </button>

                <div id="selftestResult" class="hidden mt-4 p-4 rounded-xl font-mono text-xs whitespace-pre-wrap"></div>
            </div>

            <?php if (!empty($recentErrors)): ?>
                <!-- Recent Errors -->
                <div class="bg-white border border-slate-200 p-0 rounded-xl shadow-sm mb-6 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                        <h3 class="font-bold text-slate-800">Recent Failed Syncs</h3>
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
                                        <td class="px-6 py-4 max-w-xs truncate" title="<?= htmlspecialchars($err['source_url']) ?>">
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

<script>
function runSelftest() {
    const btn = document.getElementById('btnSelftest');
    const resultBox = document.getElementById('selftestResult');
    
    btn.disabled = true;
    btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i><span>Running...</span>';
    lucide.createIcons();
    
    resultBox.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-800', 'border-emerald-200', 'bg-red-50', 'text-red-800', 'border-red-200');
    resultBox.classList.add('bg-slate-100', 'text-slate-800', 'border', 'border-slate-200');
    resultBox.textContent = "Running test on server...\n\n";
    
    fetch('<?= url('admin/image-sync-status/selftest') ?>', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded' },
        body: '_csrf_token=<?= csrf_token() ?>'
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            resultBox.textContent += "Error executing test: " + data.error;
            resultBox.classList.replace('bg-slate-100', 'bg-red-50');
            resultBox.classList.replace('text-slate-800', 'text-red-800');
            resultBox.classList.replace('border-slate-200', 'border-red-200');
            return;
        }
        
        resultBox.textContent += data.output;
        
        if (data.pass) {
            resultBox.classList.replace('bg-slate-100', 'bg-emerald-50');
            resultBox.classList.replace('text-slate-800', 'text-emerald-800');
            resultBox.classList.replace('border-slate-200', 'border-emerald-200');
        } else {
            resultBox.classList.replace('bg-slate-100', 'bg-red-50');
            resultBox.classList.replace('text-slate-800', 'text-red-800');
            resultBox.classList.replace('border-slate-200', 'border-red-200');
        }
    })
    .catch(err => {
        resultBox.textContent += "Network Error: " + err;
        resultBox.classList.replace('bg-slate-100', 'bg-red-50');
        resultBox.classList.replace('text-slate-800', 'text-red-800');
        resultBox.classList.replace('border-slate-200', 'border-red-200');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i data-lucide="shield-check" class="w-4 h-4"></i><span>Run Self-Test Again</span>';
        lucide.createIcons();
    });
}
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
