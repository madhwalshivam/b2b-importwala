<?php
$title = "Manage Customer Inquiries | Admin Panel";
include __DIR__ . '/../layouts/header.php';

$statuses = ['New', 'In Progress', 'Contacted', 'Quotation Sent', 'Converted', 'Closed', 'Rejected'];
$businessTypes = ['Distributor', 'Wholesaler', 'Retailer', 'E-commerce Seller', 'Manufacturer', 'Other'];

$statusBadges = [
    'New' => 'bg-rose-50 text-rose-700 border-rose-200',
    'In Progress' => 'bg-amber-50 text-amber-700 border-amber-200',
    'Contacted' => 'bg-sky-50 text-sky-700 border-sky-200',
    'Quotation Sent' => 'bg-purple-50 text-purple-700 border-purple-200',
    'Converted' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'Closed' => 'bg-slate-100 text-slate-700 border-slate-200',
    'Rejected' => 'bg-gray-100 text-gray-500 border-gray-200',
];
?>

<div class="space-y-5 font-sans pb-8">

    <!-- Top Header Bar -->
    <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs">
        <div>
            <div class="flex items-center space-x-2">
                <span
                    class="px-2.5 py-0.5 text-[10px] font-semibold uppercase bg-orange-50 text-[#f05a29] rounded-md tracking-wider border border-orange-200">
                    CATALOG &amp; SALES
                </span>
                <span class="text-gray-300 text-xs">•</span>
                <span class="text-xs text-gray-500 font-medium">B2B Customer Inquiries</span>
            </div>
            <h1 class="text-xl font-semibold text-slate-900 mt-1 tracking-tight">Customer B2B Inquiries</h1>
            <p class="text-xs text-gray-500 font-medium mt-0.5">Manage multi-product wholesale requirements, quotations,
                and customer follow-ups.</p>
        </div>
        <?php if (!empty($newCount) && $newCount > 0): ?>
            <div class="shrink-0">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-rose-500 text-white text-xs font-semibold rounded-xl shadow-xs animate-pulse">
                    <span class="w-2 h-2 rounded-full bg-white"></span>
                    <?= $newCount ?> New Inquiries Pending
                </span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Quick Stats Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Total
                    Inquiries</span>
                <span class="text-xl font-semibold text-slate-900 mt-0.5 block"><?= number_format($total ?? 0) ?></span>
            </div>
            <div
                class="w-10 h-10 rounded-xl bg-orange-50 text-[#f05a29] flex items-center justify-center shrink-0 border border-orange-100">
                <i data-lucide="file-text" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Action
                    Required</span>
                <span
                    class="text-xl font-semibold text-rose-600 mt-0.5 block"><?= number_format($newCount ?? 0) ?></span>
            </div>
            <div
                class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0 border border-rose-100">
                <i data-lucide="bell" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Pipeline
                    Status</span>
                <span class="text-lg font-semibold text-slate-900 mt-0.5 block">Active</span>
            </div>
            <div
                class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100">
                <i data-lucide="send" class="w-5 h-5"></i>
            </div>
        </div>

        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider block">Sourcing
                    Channel</span>
                <span class="text-xs font-semibold text-emerald-600 mt-0.5 block">Verified Wholesale</span>
            </div>
            <div
                class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
        <form action="<?= url('admin/inquiries') ?>" method="GET"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">

            <div class="lg:col-span-4">
                <label class="block text-[11px] font-semibold text-slate-600 mb-1 uppercase tracking-wider">Search
                    Inquiries</label>
                <div class="relative">
                    <input type="text" name="search" value="<?= htmlspecialchars($search ?? '') ?>"
                        placeholder="Search ID, Customer, Phone, Email..."
                        class="w-full h-9 pl-9 pr-3 bg-slate-50 border border-gray-200 rounded-xl text-xs font-medium text-slate-900 placeholder-gray-400 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
                    <i data-lucide="search" class="w-4 h-4 text-gray-400 absolute left-3 top-2.5"></i>
                </div>
            </div>

            <div class="lg:col-span-2">
                <label
                    class="block text-[11px] font-semibold text-slate-600 mb-1 uppercase tracking-wider">Status</label>
                <select name="status"
                    class="w-full h-9 px-3 bg-slate-50 border border-gray-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#f05a29] focus:bg-white transition cursor-pointer">
                    <option value="">All Statuses</option>
                    <?php foreach ($statuses as $st): ?>
                        <option value="<?= $st ?>" <?= ($status ?? '') === $st ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 mb-1 uppercase tracking-wider">Business
                    Type</label>
                <select name="business_type"
                    class="w-full h-9 px-3 bg-slate-50 border border-gray-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:border-[#f05a29] focus:bg-white transition cursor-pointer">
                    <option value="">All Types</option>
                    <?php foreach ($businessTypes as $bt): ?>
                        <option value="<?= $bt ?>" <?= ($businessType ?? '') === $bt ? 'selected' : '' ?>><?= $bt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="lg:col-span-2">
                <label class="block text-[11px] font-semibold text-slate-600 mb-1 uppercase tracking-wider">Date
                    From</label>
                <input type="date" name="date_from" value="<?= htmlspecialchars($dateFrom ?? '') ?>"
                    class="w-full h-9 px-3 bg-slate-50 border border-gray-200 rounded-xl text-xs font-medium text-slate-800 focus:outline-none focus:border-[#f05a29] focus:bg-white transition">
            </div>

            <div class="lg:col-span-2 flex gap-2">
                <button type="submit"
                    class="h-9 w-full bg-[#f05a29] hover:bg-[#d8481b] text-white text-xs font-semibold rounded-xl transition shadow-xs flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i> Filter
                </button>
                <a href="<?= url('admin/inquiries') ?>"
                    class="h-9 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold rounded-xl transition flex items-center justify-center cursor-pointer"
                    title="Reset Filters">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                </a>
            </div>

        </form>
    </div>

    <!-- Main Inquiries Table Container -->
    <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">

        <div class="px-5 py-3.5 border-b border-gray-100 bg-white flex flex-wrap items-center justify-between gap-4">
            <span class="text-xs font-semibold text-slate-700 uppercase tracking-wider">
                Showing <?= count($inquiries ?? []) ?> of <?= number_format($total ?? 0) ?> Total Inquiries
            </span>
        </div>

        <div class="w-full overflow-x-auto">
            <table class="w-full min-w-[1020px] text-left border-collapse">
                <thead>
                    <tr
                        class="bg-slate-50 border-b border-gray-200 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">
                        <th class="py-3 px-5 whitespace-nowrap min-w-[150px]">Inquiry ID</th>
                        <th class="py-3 px-5 min-w-[200px]">Customer &amp; Business</th>
                        <th class="py-3 px-5 whitespace-nowrap min-w-[180px]">Contact Info</th>
                        <th class="py-3 px-5 text-center whitespace-nowrap min-w-[110px]">Products</th>
                        <th class="py-3 px-5 text-center whitespace-nowrap min-w-[110px]">Total Qty</th>
                        <th class="py-3 px-5 whitespace-nowrap min-w-[130px]">Status</th>
                        <th class="py-3 px-5 whitespace-nowrap min-w-[150px]">Date &amp; Time</th>
                        <th class="py-3 px-5 text-right whitespace-nowrap min-w-[120px]">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-xs text-slate-700">
                    <?php if (empty($inquiries)): ?>
                        <tr>
                            <td colspan="8" class="py-16 text-center text-slate-400">
                                <div class="w-12 h-12 mx-auto mb-3 text-slate-300 flex items-center justify-center">
                                    <i data-lucide="inbox" class="w-10 h-10"></i>
                                </div>
                                <p class="text-sm font-semibold text-slate-600">No customer inquiries found</p>
                                <p class="text-xs text-slate-400 mt-1">Try adjusting your filters or search keywords.</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($inquiries as $inq): ?>
                            <?php
                            $stClass = $statusBadges[$inq['status']] ?? 'bg-slate-100 text-slate-600 border-slate-200';
                            $rawPhone = trim($inq['phone'] ?? '');
                            $displayPhone = str_starts_with($rawPhone, '+') ? $rawPhone : '+91 ' . ltrim($rawPhone, '0');
                            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
                            if (strlen($cleanPhone) === 10) {
                                $cleanPhone = '91' . $cleanPhone;
                            }
                            ?>
                            <tr class="hover:bg-slate-50/60 transition group">

                                <!-- Inquiry ID -->
                                <td class="py-3.5 px-5 whitespace-nowrap font-mono font-semibold text-[#f05a29]">
                                    <a href="<?= url('admin/inquiries/' . $inq['id']) ?>" class="hover:underline">
                                        <?= htmlspecialchars($inq['inquiry_number']) ?>
                                    </a>
                                </td>

                                <!-- Customer & Business -->
                                <td class="py-3.5 px-5">
                                    <div class="font-semibold text-slate-900 text-xs">
                                        <?= htmlspecialchars($inq['customer_name']) ?></div>
                                    <?php if (!empty($inq['company_name']) || !empty($inq['business_type'])): ?>
                                        <div class="text-[11px] font-normal text-slate-500 mt-0.5 truncate max-w-[240px]">
                                            <?= htmlspecialchars($inq['company_name'] ?: 'Independent Buyer') ?>
                                            <?php if (!empty($inq['business_type'])): ?>
                                                &bull; <span
                                                    class="font-medium text-slate-700"><?= htmlspecialchars($inq['business_type']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Contact Info -->
                                <td class="py-3.5 px-5 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="font-medium text-slate-800 text-xs"><?= htmlspecialchars($displayPhone) ?></span>
                                        <?php if (!empty($cleanPhone)): ?>
                                            <a href="https://wa.me/<?= $cleanPhone ?>?text=Hi%20<?= urlencode($inq['customer_name']) ?>%2C%20regarding%20your%20ImportWale%20Inquiry%20<?= urlencode($inq['inquiry_number']) ?>"
                                                target="_blank" rel="noopener"
                                                class="text-emerald-600 hover:text-emerald-700 transition"
                                                title="WhatsApp Customer">
                                                <i data-lucide="message-circle" class="w-4 h-4 inline"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($inq['email'])): ?>
                                        <div class="text-[11px] text-slate-400 font-normal truncate max-w-[180px]">
                                            <?= htmlspecialchars($inq['email']) ?></div>
                                    <?php endif; ?>
                                </td>

                                <!-- Products Count -->
                                <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                    <span class="font-medium text-slate-800 text-xs">
                                        <?= $inq['total_products'] ?>
                                        <?= $inq['total_products'] == 1 ? 'Product' : 'Products' ?>
                                    </span>
                                </td>

                                <!-- Total Volume -->
                                <td class="py-3.5 px-5 text-center whitespace-nowrap">
                                    <span class="font-semibold text-slate-900 text-xs">
                                        <?= number_format($inq['total_quantity']) ?> Units
                                    </span>
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-5 whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 text-[11px] font-semibold rounded-full border <?= $stClass ?>">
                                        <?= htmlspecialchars($inq['status']) ?>
                                    </span>
                                </td>

                                <!-- Received Date -->
                                <td class="py-3.5 px-5 text-slate-500 text-[11px] whitespace-nowrap">
                                    <?= date('d M Y, h:i A', strtotime($inq['created_at'])) ?>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-5 text-right whitespace-nowrap">
                                    <a href="<?= url('admin/inquiries/' . $inq['id']) ?>"
                                        class="h-8 px-3.5 inline-flex items-center gap-1 bg-slate-900 hover:bg-[#f05a29] text-white font-semibold text-xs rounded-lg transition shadow-2xs">
                                        View Details &rsaquo;
                                    </a>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Bar -->
        <?php if (($totalPages ?? 1) > 1): ?>
            <div class="px-5 py-3.5 border-t border-gray-100 bg-white flex justify-center items-center gap-1.5">
                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <a href="<?= url('admin/inquiries?page=' . $p . '&search=' . urlencode($search) . '&status=' . urlencode($status) . '&business_type=' . urlencode($businessType)) ?>"
                        class="w-8 h-8 rounded-lg font-semibold text-xs flex items-center justify-center transition <?= $p === $currentPage ? 'bg-[#f05a29] text-white shadow-2xs' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-100' ?>">
                        <?= $p ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    </div>

</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>