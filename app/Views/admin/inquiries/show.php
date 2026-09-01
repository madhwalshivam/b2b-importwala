<?php
$title = "Inquiry #" . htmlspecialchars($inquiry['inquiry_number']) . " | Admin Panel";
include __DIR__ . '/../layouts/header.php';

$statuses = ['New', 'In Progress', 'Contacted', 'Quotation Sent', 'Converted', 'Closed', 'Rejected'];
$statusBadges = [
    'New'            => 'bg-rose-50 text-rose-700 border-rose-200',
    'In Progress'     => 'bg-amber-50 text-amber-700 border-amber-200',
    'Contacted'       => 'bg-sky-50 text-sky-700 border-sky-200',
    'Quotation Sent'  => 'bg-purple-50 text-purple-700 border-purple-200',
    'Converted'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'Closed'          => 'bg-slate-100 text-slate-700 border-slate-200',
    'Rejected'        => 'bg-gray-100 text-gray-500 border-gray-200',
];
$badgeClass = $statusBadges[$inquiry['status']] ?? 'bg-slate-100 text-slate-700 border-slate-200';
$cleanPhone = preg_replace('/[^0-9]/', '', $inquiry['phone']);
?>

<div class="space-y-5 font-sans pb-8">

    <!-- Breadcrumb & Top Actions -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="<?= url('admin/inquiries') ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-[#f05a29] transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Inquiries List
        </a>

        <?php if (!empty($cleanPhone)): ?>
            <a href="https://wa.me/<?= $cleanPhone ?>?text=Hi%20<?= urlencode($inquiry['customer_name']) ?>%2C%20regarding%20your%20ImportWale%20Inquiry%20<?= urlencode($inquiry['inquiry_number']) ?>"
                target="_blank" rel="noopener"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                <i data-lucide="message-circle" class="w-4 h-4"></i> WhatsApp Customer Directly
            </a>
        <?php endif; ?>
    </div>

    <!-- Header Summary Card -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-xl font-semibold text-slate-900">
                    Inquiry: <span class="text-[#f05a29] font-mono font-bold"><?= htmlspecialchars($inquiry['inquiry_number']) ?></span>
                </h1>
                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full border text-xs font-semibold <?= $badgeClass ?>">
                    <span class="w-2 h-2 rounded-full bg-current"></span>
                    <?= htmlspecialchars($inquiry['status']) ?>
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-1">
                Received on <?= date('d F Y \a\t h:i A', strtotime($inquiry['created_at'])) ?>
            </p>
        </div>

        <!-- Status Change Form -->
        <form action="<?= url('admin/inquiries/update-status/' . $inquiry['id']) ?>" method="POST" class="flex items-center gap-2 bg-slate-50 p-2 border border-slate-200 rounded-xl">
            <label class="text-[11px] font-semibold text-slate-600 px-2 uppercase tracking-wider">Update Status:</label>
            <select name="status" onchange="this.form.submit()" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-900 focus:outline-none focus:border-[#f05a29] cursor-pointer">
                <?php foreach ($statuses as $st): ?>
                    <option value="<?= $st ?>" <?= $inquiry['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Customer Information & Internal Staff Notes (Span 4) -->
        <div class="lg:col-span-4 space-y-5">

            <!-- Customer Details Card -->
            <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs space-y-4">
                <h3 class="text-xs font-semibold text-slate-900 border-b border-slate-100 pb-3 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-[#f05a29]"></i> Customer Information
                </h3>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Full Name</span>
                        <span class="text-sm font-semibold text-slate-900 block mt-0.5"><?= htmlspecialchars($inquiry['customer_name']) ?></span>
                    </div>

                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Phone / WhatsApp</span>
                        <span class="text-xs font-semibold text-slate-900 block mt-0.5"><?= htmlspecialchars($inquiry['phone']) ?></span>
                    </div>

                    <?php if (!empty($inquiry['email'])): ?>
                        <div>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Email Address</span>
                            <span class="text-xs font-medium text-slate-700 block mt-0.5"><?= htmlspecialchars($inquiry['email']) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($inquiry['company_name'])): ?>
                        <div>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Company / Firm</span>
                            <span class="text-xs font-semibold text-slate-900 block mt-0.5"><?= htmlspecialchars($inquiry['company_name']) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($inquiry['business_type'])): ?>
                        <div>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Business Type</span>
                            <span class="inline-block mt-1 px-2.5 py-0.5 bg-slate-100 text-slate-800 text-[11px] font-semibold rounded-md">
                                <?= htmlspecialchars($inquiry['business_type']) ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($inquiry['city']) || !empty($inquiry['state'])): ?>
                        <div>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Location</span>
                            <span class="text-xs font-medium text-slate-700 block mt-0.5"><?= htmlspecialchars(trim($inquiry['city'] . ', ' . $inquiry['state'], ', ')) ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($inquiry['gst_number'])): ?>
                        <div>
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">GSTIN</span>
                            <span class="text-xs font-mono font-semibold text-slate-800 bg-slate-100 px-2 py-1 rounded inline-block mt-1">
                                <?= htmlspecialchars($inquiry['gst_number']) ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($inquiry['customer_message'])): ?>
                        <div class="pt-2">
                            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Customer Message</span>
                            <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 leading-relaxed font-normal">
                                <?= nl2br(htmlspecialchars($inquiry['customer_message'])) ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Internal Staff Notes Card -->
            <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs space-y-3">
                <h3 class="text-xs font-semibold text-slate-900 border-b border-slate-100 pb-3 uppercase tracking-wider flex items-center gap-2">
                    <i data-lucide="notebook-pen" class="w-4 h-4 text-purple-600"></i> Internal Admin Notes
                </h3>
                <form action="<?= url('admin/inquiries/update-notes/' . $inquiry['id']) ?>" method="POST" class="space-y-3">
                    <textarea name="admin_notes" rows="4" placeholder="Add private staff notes, quotation details, or discussion logs..."
                        class="w-full p-3 bg-slate-50 border border-gray-200 rounded-xl text-xs font-normal text-slate-900 placeholder-slate-400 focus:outline-none focus:border-[#f05a29] focus:bg-white transition resize-none"><?= htmlspecialchars($inquiry['admin_notes'] ?? '') ?></textarea>
                    <button type="submit" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center justify-center gap-2 cursor-pointer">
                        <i data-lucide="save" class="w-3.5 h-3.5"></i> Save Staff Notes
                    </button>
                </form>
            </div>

        </div>

        <!-- Right Column: Requested Products List (Span 8) -->
        <div class="lg:col-span-8">
            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
                <div class="px-5 py-3.5 border-b border-gray-100 bg-white flex items-center justify-between">
                    <h3 class="text-xs font-semibold text-slate-900 uppercase tracking-wider flex items-center gap-2">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-[#f05a29]"></i> Requested Wholesale Products
                    </h3>
                    <div class="text-xs font-semibold text-[#f05a29]">
                        <?= $inquiry['total_products'] ?> Products &bull; <?= number_format($inquiry['total_quantity']) ?> Total Units
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-gray-200 text-[10px] font-semibold text-slate-500 uppercase tracking-wider">
                                <th class="py-3 px-5 w-16">Item</th>
                                <th class="py-3 px-5">Product Details</th>
                                <th class="py-3 px-5 text-center">Requested Quantity</th>
                                <th class="py-3 px-5 text-right">Catalog Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-slate-700">
                            <?php foreach ($inquiry['items'] as $item): ?>
                                <?php
                                    $imgSrc = !empty($item['product_image_snapshot']) ? asset($item['product_image_snapshot']) : asset('assets/images/placeholder.jpg');
                                ?>
                                <tr class="hover:bg-slate-50/50 transition">
                                    <td class="py-3.5 px-5">
                                        <div class="w-12 h-12 rounded-xl overflow-hidden border border-gray-200 bg-slate-50 shrink-0">
                                            <img src="<?= htmlspecialchars($imgSrc) ?>" alt="" class="w-full h-full object-cover">
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-5">
                                        <div class="font-semibold text-slate-900 text-xs">
                                            <?php if (!empty($item['current_product_slug'])): ?>
                                                <a href="<?= url('product/' . $item['current_product_slug']) ?>" target="_blank" class="hover:text-[#f05a29] transition inline-flex items-center gap-1">
                                                    <?= htmlspecialchars($item['product_name_snapshot']) ?>
                                                    <i data-lucide="external-link" class="w-3 h-3 text-slate-400"></i>
                                                </a>
                                            <?php else: ?>
                                                <?= htmlspecialchars($item['product_name_snapshot']) ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-5 text-center font-semibold text-slate-900">
                                        <?= number_format($item['requested_quantity']) ?> Units
                                    </td>
                                    <td class="py-3.5 px-5 text-right font-semibold text-slate-900">
                                        ₹<?= number_format($item['unit_price_snapshot'] ?? 0, 2) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
