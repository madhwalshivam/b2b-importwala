<?php
$title = 'RFQ #' . $rfq['id'] . ' — ' . htmlspecialchars($rfq['product_name']) . ' | Admin Panel';
include __DIR__ . '/../layouts/header.php';

$statuses = ['New', 'Contacted', 'Quoted', 'Closed'];
$statusBadges = [
    'New'       => 'bg-rose-50 text-rose-700 border-rose-200',
    'Contacted' => 'bg-sky-50 text-sky-700 border-sky-200',
    'Quoted'    => 'bg-purple-50 text-purple-700 border-purple-200',
    'Closed'    => 'bg-slate-100 text-slate-700 border-slate-200',
];
$badgeClass = $statusBadges[$rfq['status']] ?? 'bg-slate-100 text-slate-700 border-slate-200';
$cleanPhone = preg_replace('/[^0-9]/', '', $rfq['phone']);
?>

<div class="space-y-5 font-sans pb-8">

    <!-- Breadcrumb & Top Actions -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <a href="<?= url('admin/rfq') ?>" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-[#f05a29] transition">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to RFQ Requests
        </a>

        <?php if (!empty($cleanPhone)): ?>
            <a href="https://wa.me/91<?= $cleanPhone ?>?text=Hi%20<?= urlencode($rfq['full_name']) ?>%2C%20regarding%20your%20ImportWale%20RFQ%20for%20<?= urlencode($rfq['product_name']) ?>"
                target="_blank" rel="noopener"
                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-xs transition">
                <i data-lucide="message-circle" class="w-4 h-4"></i> WhatsApp Buyer Directly
            </a>
        <?php endif; ?>
    </div>

    <!-- Header Card -->
    <div class="bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">RFQ #<?= $rfq['id'] ?></span>
                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full border text-xs font-semibold <?= $badgeClass ?>">
                    <span class="w-2 h-2 rounded-full bg-current"></span>
                    <?= htmlspecialchars($rfq['status']) ?>
                </span>
            </div>
            <h1 class="text-xl font-semibold text-slate-900 mt-1"><?= htmlspecialchars($rfq['product_name']) ?></h1>
            <p class="text-xs text-slate-500 font-medium mt-1">Submitted on <?= date('d M Y \a\t h:i A', strtotime($rfq['created_at'])) ?></p>
        </div>

        <!-- Status Change Control -->
        <div class="flex items-center gap-2 bg-slate-50 p-2 border border-slate-200 rounded-xl">
            <label class="text-[11px] font-semibold text-slate-600 px-2 uppercase tracking-wider">Status:</label>
            <select id="statusSelect" onchange="updateStatus(this.value)" class="px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-semibold text-slate-900 focus:outline-none focus:border-[#f05a29] cursor-pointer">
                <?php foreach ($statuses as $st): ?>
                    <option value="<?= $st ?>" <?= $rfq['status'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                <?php endforeach; ?>
            </select>
            <span id="statusSaveMsg" class="text-xs font-semibold text-emerald-600 px-2 hidden">✓ Saved</span>
        </div>
    </div>

    <!-- 2-Column Detail Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Product Specifications (Span 7) -->
        <div class="lg:col-span-7 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs space-y-4">
            <h3 class="text-xs font-semibold text-slate-900 border-b border-slate-100 pb-3 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="package" class="w-4 h-4 text-[#f05a29]"></i> Product Specifications &amp; Requirements
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-xs">
                <div class="p-3 bg-slate-50 border border-slate-200/60 rounded-xl">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Quantity</span>
                    <span class="text-xs font-semibold text-slate-900 mt-0.5 block"><?= number_format($rfq['quantity']) ?> <?= htmlspecialchars($rfq['unit']) ?></span>
                </div>

                <div class="p-3 bg-slate-50 border border-slate-200/60 rounded-xl">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Target Unit Price</span>
                    <span class="text-xs font-semibold text-[#f05a29] mt-0.5 block">₹<?= number_format($rfq['target_price'], 2) ?></span>
                </div>

                <div class="p-3 bg-slate-50 border border-slate-200/60 rounded-xl">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Overall Budget</span>
                    <span class="text-xs font-semibold text-slate-800 mt-0.5 block"><?= htmlspecialchars($rfq['overall_budget'] ?: 'Not specified') ?></span>
                </div>

                <div class="p-3 bg-slate-50 border border-slate-200/60 rounded-xl">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Sourcing Purpose</span>
                    <span class="text-xs font-semibold text-slate-800 mt-0.5 block"><?= htmlspecialchars($rfq['sourcing_purpose'] ?: 'Resale') ?></span>
                </div>
            </div>

            <?php if (!empty($rfq['specifications'])): ?>
                <div>
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Technical Specifications / Material</span>
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 leading-relaxed font-normal">
                        <?= nl2br(htmlspecialchars($rfq['specifications'])) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($rfq['product_reference_link'])): ?>
                <div>
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Reference Link</span>
                    <a href="<?= htmlspecialchars($rfq['product_reference_link']) ?>" target="_blank" rel="noopener"
                        class="text-xs font-semibold text-[#f05a29] hover:underline inline-flex items-center gap-1 break-all">
                        <?= htmlspecialchars($rfq['product_reference_link']) ?>
                        <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Attached Reference Photos -->
            <?php if (!empty($rfq['photos'])): ?>
                <div class="pt-2">
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block mb-2">Reference Images (<?= count($rfq['photos']) ?>)</span>
                    <div class="flex flex-wrap gap-3">
                        <?php foreach ($rfq['photos'] as $photo): ?>
                            <?php $pSrc = asset($photo['photo_path']); ?>
                            <a href="<?= htmlspecialchars($pSrc) ?>" target="_blank" class="w-20 h-20 rounded-xl border border-slate-200 overflow-hidden bg-slate-50 block group relative">
                                <img src="<?= htmlspecialchars($pSrc) ?>" alt="" class="w-full h-full object-cover group-hover:scale-105 transition duration-200">
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>

        <!-- Right Column: Buyer Details (Span 5) -->
        <div class="lg:col-span-5 bg-white p-5 rounded-2xl border border-gray-200/80 shadow-xs space-y-4">
            <h3 class="text-xs font-semibold text-slate-900 border-b border-slate-100 pb-3 uppercase tracking-wider flex items-center gap-2">
                <i data-lucide="user-check" class="w-4 h-4 text-emerald-600"></i> Buyer Details
            </h3>

            <div class="space-y-3 text-xs">
                <div>
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Buyer Full Name</span>
                    <span class="text-sm font-semibold text-slate-900 block mt-0.5"><?= htmlspecialchars($rfq['full_name']) ?></span>
                </div>

                <div>
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Mobile / WhatsApp</span>
                    <span class="text-xs font-semibold text-slate-900 block mt-0.5">+91 <?= htmlspecialchars($rfq['phone']) ?></span>
                </div>

                <div>
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Email Address</span>
                    <span class="text-xs font-medium text-slate-700 block mt-0.5"><?= htmlspecialchars($rfq['email']) ?></span>
                </div>

                <div>
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Business Type</span>
                    <span class="inline-block mt-1 px-2.5 py-0.5 bg-slate-100 text-slate-800 text-[11px] font-semibold rounded-md">
                        <?= htmlspecialchars($rfq['business_type'] ?: 'Not specified') ?>
                    </span>
                </div>

                <div>
                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">GST Registered</span>
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 text-[11px] font-semibold rounded-md mt-1 <?= $rfq['has_gst'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' ?>">
                        <?= $rfq['has_gst'] ? '✓ GST Registered' : 'Non-GST' ?>
                    </span>
                </div>

                <?php if (!empty($rfq['pincode'])): ?>
                    <div>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Pincode / Location</span>
                        <span class="text-xs font-semibold text-slate-800 block mt-0.5"><?= htmlspecialchars($rfq['pincode']) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($rfq['additional_comments'])): ?>
                    <div class="pt-2">
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block mb-1">Additional Buyer Comments</span>
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-700 leading-relaxed font-normal">
                            <?= nl2br(htmlspecialchars($rfq['additional_comments'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script>
async function updateStatus(val) {
    const msg = document.getElementById('statusSaveMsg');
    msg.classList.add('hidden');
    try {
        const res = await fetch('<?= url('admin/rfq/update-status/' . $rfq['id']) ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'status=' + encodeURIComponent(val)
        });
        const d = await res.json();
        if (d.success) {
            msg.classList.remove('hidden');
            setTimeout(() => msg.classList.add('hidden'), 3000);
        } else {
            alert(d.message || 'Error updating status');
        }
    } catch(e) {
        alert('Network error');
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
