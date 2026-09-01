<?php
$title = 'RFQ Requests | Admin Panel';
include __DIR__ . '/../layouts/header.php';

$statuses = ['New', 'Contacted', 'Quoted', 'Closed'];
$statusBadges = [
    'New'       => 'bg-rose-50 text-rose-700 border-rose-200',
    'Contacted' => 'bg-sky-50 text-sky-700 border-sky-200',
    'Quoted'    => 'bg-purple-50 text-purple-700 border-purple-200',
    'Closed'    => 'bg-slate-100 text-slate-700 border-slate-200',
];
?>

<div class="space-y-5 font-sans pb-8">

    <!-- Top Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 text-[10px] font-semibold uppercase bg-orange-50 text-[#f05a29] rounded-md tracking-wider border border-orange-200">
                    CATALOG &amp; SALES
                </span>
                <span class="text-gray-300 text-xs">•</span>
                <span class="text-xs text-gray-500 font-medium">RFQ Custom Sourcing</span>
            </div>
            <h1 class="text-xl font-semibold text-slate-900 mt-1 tracking-tight">RFQ Requests</h1>
            <p class="text-xs text-gray-500 font-medium mt-0.5">Manage buyer custom sourcing requirements, follow up via WhatsApp, and track status.</p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <?php if (!empty($newCount) && $newCount > 0): ?>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#f05a29] text-white text-xs font-semibold rounded-xl shadow-xs animate-pulse">
                    <span class="w-2 h-2 rounded-full bg-white"></span>
                    <?= $newCount ?> New Requests
                </span>
            <?php endif; ?>
            <a href="<?= url('admin/rfq/export-csv?' . http_build_query(['search' => $search, 'status' => $status, 'date_from' => $dateFrom, 'date_to' => $dateTo])) ?>"
                class="h-9 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1.5">
                <i data-lucide="download" class="w-4 h-4"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Quick Stats Metric Cards Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total RFQs</span>
                <span class="text-xl font-bold text-slate-900 mt-0.5 block"><?= number_format($total ?? 0) ?></span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-orange-50 text-[#f05a29] flex items-center justify-center shrink-0 border border-orange-100">
                <i data-lucide="file-question" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">New Requests</span>
                <span class="text-xl font-bold text-rose-600 mt-0.5 block"><?= number_format($newCount ?? 0) ?></span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100">
                <i data-lucide="sparkles" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Sourcing Mode</span>
                <span class="text-lg font-semibold text-slate-900 mt-0.5 block">Custom RFQ</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100">
                <i data-lucide="check-check" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">WhatsApp Direct</span>
                <span class="text-xs font-bold text-emerald-600 mt-0.5 block">1-Click Contact</span>
            </div>
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                <i data-lucide="message-square" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
        <form action="<?= url('admin/rfq') ?>" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
            
            <div class="lg:col-span-4">
                <label class="block text-[11px] font-semibold text-slate-600 mb-1 uppercase tracking-wider">Search RFQ</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>" placeholder="Search name, phone, email, product..."
                        class="w-full h-9 pl-9 pr-3 bg-slate-50 border border-gray-200 rounded-xl text-xs font-medium text-slate-900 placeholder-gray-400 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
                </div>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 mb-1 uppercase tracking-wider">Status</label>
                <select name="status" class="w-full h-9 px-3 bg-slate-50 border border-gray-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#f05a29] focus:bg-white transition cursor-pointer">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?= $st ?>" <?= ($status ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 mb-1 uppercase tracking-wider">From Date</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom ?? '') ?>"
                    class="w-full h-9 px-3 bg-slate-50 border border-gray-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
            </div>

            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 mb-1 uppercase tracking-wider">To Date</label>
                <input type="date" name="date_to" value="<?= htmlspecialchars($dateTo ?? '') ?>"
                    class="w-full h-9 px-3 bg-slate-50 border border-gray-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
            </div>

            <div class="lg:col-span-2 flex gap-2">
                <button type="submit" class="h-9 w-full bg-[#f05a29] hover:bg-[#d8481b] text-white text-xs font-bold rounded-xl transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i> Filter
                </button>
                <a href="<?= url('admin/rfq') ?>" class="h-9 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-xl transition flex items-center justify-center cursor-pointer" title="Reset Filters">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>

        </form>
    </div>

    <!-- Table Container -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
        
        <div class="px-5 py-3.5 border-b border-gray-100 bg-white flex items-center justify-between">
            <span class="text-xs font-semibold text-slate-700 uppercase tracking-wider">
                Total RFQ Requests: <?= number_format($total ?? 0) ?>
            </span>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[1020px] text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 border-b border-gray-200 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-5 whitespace-nowrap min-w-[150px]">Date &amp; Time</th>
                        <th class="py-3 px-5 min-w-[220px]">Product &amp; Requirement</th>
                        <th class="py-3 px-5 text-center whitespace-nowrap min-w-[140px]">Quantity &amp; Budget</th>
                        <th class="py-3 px-5 whitespace-nowrap min-w-[160px]">Buyer Name</th>
                        <th class="py-3 px-5 whitespace-nowrap min-w-[180px]">Contact Info</th>
                        <th class="py-3 px-5 text-center whitespace-nowrap min-w-[120px]">Status</th>
                        <th class="py-3 px-5 text-right whitespace-nowrap min-w-[140px]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs text-slate-700">
                    <?php if (empty($rfqs)): ?>
                        <tr>
                            <td colspan="7" class="py-16 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto mb-3 text-slate-300 flex items-center justify-center">
                                    <i data-lucide="file-question" class="w-10 h-10"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-600">No RFQ requests found</p>
                                <p class="text-xs text-slate-400 mt-1">Try adjusting your filters or date range.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rfqs as $rfq): ?>
                            <?php
                                $scClass = $statusBadges[$rfq['status']] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                                $rawPhone = trim($rfq['phone'] ?? '');
                                $displayPhone = str_starts_with($rawPhone, '+') ? $rawPhone : '+91 ' . ltrim($rawPhone, '0');
                                $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                                if (strlen($cleanPhone) === 10) {
                                    $cleanPhone = '91' . $cleanPhone;
                                }
                            ?>
                            <tr class="hover:bg-slate-50/60 transition group">
                                
                                <td class="py-3.5 px-5 whitespace-nowrap text-slate-500 text-[11px]">
                                    <?= date('d M Y, h:i A', strtotime($rfq['created_at'])) ?>
                                </td>

                                <td class="py-3.5 px-5 max-w-[240px]">
                                    <div class="font-semibold text-slate-900 text-xs truncate" title="<?= htmlspecialchars($rfq['product_name']) ?>">
                                        <?= htmlspecialchars($rfq['product_name']) ?>
                                    </div>
                                    <?php if (!empty($rfq['sourcing_purpose'])): ?>
                                        <div class="text-[11px] text-slate-500 font-medium mt-0.5 truncate">
                                            <?= htmlspecialchars($rfq['sourcing_purpose']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                    <div class="font-semibold text-slate-900 text-xs">
                                        <?= number_format($rfq['quantity']) ?> <?= htmlspecialchars($rfq['unit']) ?>
                                    </div>
                                    <?php if (!empty($rfq['overall_budget'])): ?>
                                        <div class="text-[11px] font-normal text-slate-500"><?= htmlspecialchars($rfq['overall_budget']) ?></div>
                                    <?php endif; ?>
                                </td>

                                <td class="py-3.5 px-5 whitespace-nowrap">
                                    <div class="font-semibold text-slate-900 text-xs"><?= htmlspecialchars($rfq['full_name']) ?></div>
                                    <?php if (!empty($rfq['business_type'])): ?>
                                        <div class="text-[11px] text-slate-500 font-normal"><?= htmlspecialchars($rfq['business_type']) ?></div>
                                    <?php endif; ?>
                                </td>

                                <td class="py-3.5 px-5 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-medium text-slate-800 text-xs"><?= htmlspecialchars($displayPhone) ?></span>
                                        <?php if (!empty($cleanPhone)): ?>
                                            <a href="https://wa.me/<?= $cleanPhone ?>?text=Hi%20<?= urlencode($rfq['full_name']) ?>%2C%20regarding%20your%20ImportWale%20RFQ%20for%20<?= urlencode($rfq['product_name']) ?>"
                                                target="_blank" rel="noopener"
                                                class="text-emerald-600 hover:text-emerald-700 transition" title="WhatsApp Buyer">
                                                <i data-lucide="message-circle" class="w-4 h-4 inline"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($rfq['email'])): ?>
                                        <div class="text-[11px] text-slate-400 font-normal truncate max-w-[180px]"><?= htmlspecialchars($rfq['email']) ?></div>
                                    <?php endif; ?>
                                </td>

                                <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-[11px] font-semibold rounded-full border <?= $scClass ?>">
                                        <?= htmlspecialchars($rfq['status']) ?>
                                    </span>
                                </td>

                                <td class="py-3.5 px-5 text-right whitespace-nowrap space-x-1">
                                    <!-- View Action -->
                                    <a href="<?= url('admin/rfq/' . $rfq['id']) ?>"
                                        class="h-8 px-3 inline-flex items-center gap-1 bg-slate-900 hover:bg-[#f05a29] text-white font-semibold text-xs rounded-lg transition shadow-2xs">
                                        View Details &rsaquo;
                                    </a>

                                    <!-- Delete Action -->
                                    <button onclick="deleteRfq(<?= $rfq['id'] ?>)" title="Delete RFQ"
                                        class="h-8 px-2.5 inline-flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 transition cursor-pointer">
                                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <?php if (!empty($totalPages) && $totalPages > 1): ?>
            <div class="px-5 py-3.5 border-t border-gray-100 bg-white flex justify-center items-center gap-1.5">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="?<?= http_build_query(['search' => $search, 'status' => $status, 'date_from' => $dateFrom, 'date_to' => $dateTo, 'page' => $p]) ?>"
                        class="w-8 h-8 rounded-lg font-bold text-xs flex items-center justify-center transition <?= $p == $currentPage ? 'bg-[#f05a29] text-white shadow-2xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </div>

</div>

<script>
function deleteRfq(id) {
    if (!confirm('Are you sure you want to delete this RFQ request?')) return;
    fetch('<?= url('admin/rfq/delete/') ?>' + id, { method: 'POST' })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            location.reload();
        } else {
            alert(d.message || 'Error deleting RFQ.');
        }
    });
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
