<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales & Tax Report - <?= htmlspecialchars($filter_label) ?> | ImportWale</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            red: '#A8111C',
                            dark: '#6E0D14'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            body {
                background-color: white !important;
                color: black !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .print-shadow-none {
                box-shadow: none !important;
            }

            .print\:hidden {
                display: none !important;
            }
        }
    </style>
</head>

<body class="bg-slate-100 py-8 px-4 text-slate-800 font-sans antialiased">

    <!-- Floating Action Controls Bar (Hidden in Print / PDF Output) -->
    <div
        class="max-w-5xl mx-auto mb-6 flex items-center justify-between bg-slate-900 text-white p-4 rounded-2xl shadow-lg print:hidden">
        <div class="flex items-center space-x-3">
            <span class="w-3 h-3 bg-emerald-500 rounded-full animate-ping"></span>
            <div>
                <h3 class="text-xs font-semibold">ImportWale Official Tax Report Document</h3>
                <p class="text-[10px] text-slate-400">Period: <?= htmlspecialchars($filter_label) ?></p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <button onclick="downloadPdfFile()"
                class="h-9 px-4 bg-red-600 hover:bg-red-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <span>Save PDF File</span>
            </button>
            <button onclick="window.print()"
                class="h-9 px-4 bg-slate-800 hover:bg-slate-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center space-x-1.5 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                    </path>
                </svg>
                <span>Print Document</span>
            </button>
        </div>
    </div>

    <!-- PDF Printable Document Wrapper -->
    <div id="pdf-report-content"
        class="max-w-5xl mx-auto bg-white p-8 md:p-12 rounded-2xl shadow-xl print-shadow-none relative overflow-hidden">

        <!-- Top Brand Accent Strip -->
        <div class="absolute top-0 left-0 right-0 h-2 bg-brand-red"></div>

        <!-- Document Header: Company Info + Report Title -->
        <div class="flex flex-col md:flex-row justify-between items-start border-b border-slate-200 pb-6 mb-6 mt-2">
            <div>
                <h1 class="text-3xl font-black text-brand-red tracking-tight mb-1">IMPORTWALE</h1>
                <p class="font-semibold text-slate-900 text-sm uppercase tracking-wide">
                    <?= htmlspecialchars($company['legal_name'] ?? 'Rughwani Enterprises') ?>
                </p>
                <div class="text-xs text-slate-500 mt-2 space-y-0.5">
                    <p><span class="font-semibold text-slate-700">GSTIN:</span>
                        <?= htmlspecialchars($company['gstin']) ?></p>
                    <p><span class="font-semibold text-slate-700">Phone:</span>
                        <?= htmlspecialchars($company['phone']) ?> | <span
                            class="font-semibold text-slate-700">Email:</span>
                        <?= htmlspecialchars($company['email']) ?></p>
                </div>
            </div>

            <div class="mt-4 md:mt-0 text-left md:text-right">
                <div
                    class="inline-block bg-red-50 border border-red-200 text-brand-red font-semibold px-3.5 py-1 rounded-full text-xs uppercase tracking-wider mb-2">
                    Official Tax Report
                </div>
                <h2 class="text-xl font-semibold text-slate-900">Monthly Sales & Tax Report</h2>
                <p class="text-xs text-slate-500 mt-1">Period: <strong
                        class="text-slate-900"><?= htmlspecialchars($filter_label) ?></strong></p>
                <p class="text-[11px] text-slate-400 mt-0.5">Generated On: <?= date('d M Y, h:i A') ?></p>
            </div>
        </div>

        <!-- Financial Summary Metric Box -->
        <div
            class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-slate-50 p-5 rounded-xl border border-slate-200 mb-8 text-xs">
            <div>
                <span class="text-[10px] font-semibold uppercase text-slate-500">Total Orders</span>
                <p class="text-lg font-extrabold text-slate-900 mt-0.5"><?= number_format($stats['total_orders']) ?></p>
            </div>
            <div>
                <span class="text-[10px] font-semibold uppercase text-slate-500">Gross Sales</span>
                <p class="text-lg font-extrabold text-slate-900 mt-0.5"><?= format_price($stats['gross_sales']) ?></p>
            </div>
            <div>
                <span class="text-[10px] font-semibold uppercase text-slate-500">GST Tax Collected</span>
                <p class="text-lg font-extrabold text-brand-red mt-0.5"><?= format_price($stats['total_tax']) ?></p>
            </div>
            <div>
                <span class="text-[10px] font-semibold uppercase text-slate-500">Net Sales</span>
                <p class="text-lg font-extrabold text-slate-900 mt-0.5"><?= format_price($stats['net_sales']) ?></p>
            </div>
        </div>

        <!-- Itemized Order Table -->
        <div class="mb-8">
            <h3 class="text-xs font-semibold text-slate-900 uppercase tracking-wider mb-3">Itemized Sales & Tax
                Breakdown</h3>

            <table class="w-full text-left text-xs border border-slate-200 border-collapse">
                <thead
                    class="bg-slate-100 text-slate-700 font-semibold uppercase text-[9px] tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-2.5 px-2 border-r border-slate-200">Order #</th>
                        <th class="py-2.5 px-2 border-r border-slate-200">Customer Name</th>
                        <th class="py-2.5 px-2 border-r border-slate-200 text-center">HSN Code</th>
                        <th class="py-2.5 px-2 border-r border-slate-200 text-center">GST %</th>
                        <th class="py-2.5 px-2 border-r border-slate-200 text-center">Status</th>
                        <th class="py-2.5 px-2 border-r border-slate-200 text-right">Subtotal</th>
                        <th class="py-2.5 px-2 border-r border-slate-200 text-right">GST Tax (Included)</th>
                        <th class="py-2.5 px-2 border-r border-slate-200 text-right">Shipping</th>
                        <th class="py-2.5 px-2 text-right">Gross Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 font-medium text-[10px]">
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="9" class="p-6 text-center text-slate-400">
                                No orders found for the selected period (<?= htmlspecialchars($filter_label) ?>).
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $ord): ?>
                            <?php $isCancelled = strtolower($ord['order_status'] ?? '') === 'cancelled'; ?>
                            <tr class="<?= $isCancelled ? 'bg-red-50/50' : '' ?>">
                                <td class="py-2 px-2 border-r border-slate-200 font-mono font-semibold text-slate-900">
                                    <?= htmlspecialchars($ord['order_number']) ?>
                                </td>
                                <td class="py-2 px-2 border-r border-slate-200 text-slate-900">
                                    <?= htmlspecialchars($ord['customer_name'] ?? 'N/A') ?>
                                </td>
                                <td
                                    class="py-2 px-2 border-r border-slate-200 text-center font-mono font-semibold text-slate-800 text-[9px]">
                                    <?= htmlspecialchars($ord['hsn_codes'] ?? '8714.99.90') ?>
                                </td>
                                <td
                                    class="py-2 px-2 border-r border-slate-200 text-center font-mono font-semibold text-brand-red text-[9px]">
                                    <?= htmlspecialchars($ord['gst_rates'] ?? '18%') ?>
                                </td>
                                <td class="py-2 px-2 border-r border-slate-200 text-center uppercase font-semibold text-[9px]">
                                    <?= htmlspecialchars($ord['order_status'] ?? 'pending') ?>
                                </td>
                                <td
                                    class="py-2 px-2 border-r border-slate-200 text-right font-mono font-semibold text-slate-900">
                                    <?= format_price($ord['computed_subtotal']) ?>
                                </td>
                                <td
                                    class="py-2 px-2 border-r border-slate-200 text-right font-mono text-brand-red font-semibold">
                                    <?= format_price($ord['computed_tax']) ?>
                                </td>
                                <td class="py-2 px-2 border-r border-slate-200 text-right font-mono font-semibold">
                                    <?= ((float) ($ord['shipping_charge'] ?? 0) <= 0) ? 'FREE' : format_price($ord['shipping_charge']) ?>
                                </td>
                                <td class="py-2 px-2 text-right font-mono font-semibold text-slate-900">
                                    <?= format_price($ord['total_amount']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
                <?php if (!empty($orders)): ?>
                    <tfoot class="bg-slate-100 font-semibold text-[11px] border-t-2 border-slate-300">
                        <tr>
                            <td colspan="5"
                                class="py-2.5 px-2 text-right uppercase tracking-wider border-r border-slate-200">
                                Totals:
                            </td>
                            <td class="py-2.5 px-2 text-right font-mono border-r border-slate-200 text-slate-900">
                                <?= format_price($stats['total_subtotal']) ?>
                            </td>
                            <td class="py-2.5 px-2 text-right font-mono border-r border-slate-200 text-brand-red">
                                <?= format_price($stats['total_tax']) ?>
                            </td>
                            <td class="py-2.5 px-2 text-right font-mono border-r border-slate-200 text-slate-900">
                                <?= format_price($stats['total_shipping']) ?>
                            </td>
                            <td class="py-2.5 px-2 text-right font-mono text-brand-red">
                                <?= format_price($stats['gross_sales']) ?>
                            </td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <!-- Footer Sign & Notes -->
        <div class="pt-6 border-t border-slate-200 flex justify-between items-end text-xs">
            <div class="text-slate-500 space-y-1">
                <p class="font-semibold text-slate-800">ImportWale Financial Intelligence Unit</p>
                <p class="text-[10px] text-slate-400 max-w-sm">This is a system-generated monthly sales and tax report.
                    All figures are based on logged customer order records.</p>
            </div>
            <div class="text-center">
                <div class="h-12 w-32 mx-auto border-b border-slate-300 mb-1 relative flex items-center justify-center">
                    <span class="text-[10px] text-slate-300 italic">Digitally Verified</span>
                </div>
                <p class="text-[11px] font-semibold text-slate-900">Authorized Signature</p>
                <p class="text-[9px] text-slate-500 uppercase"><?= htmlspecialchars($company['legal_name']) ?></p>
            </div>
        </div>

    </div>

    <!-- JavaScript 1-Click PDF Generator Script -->
    <script>
        function downloadPdfFile() {
            const element = document.getElementById('pdf-report-content');
            const opt = {
                margin: [8, 8, 8, 8],
                filename: 'ImportWale_Sales_Tax_Report_<?= str_replace(' ', '_', $filter_label) ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save();
        }

        window.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('download')) {
                setTimeout(() => {
                    downloadPdfFile();
                }, 600);
            }
        });
    </script>
</body>

</html>