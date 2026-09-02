<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GST Tax Invoice - <?= htmlspecialchars($order['order_number']) ?> | Mudsor</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

            .print-border-none {
                border: none !important;
            }

            .print\:hidden {
                display: none !important;
            }
        }
    </style>
</head>

<body class="bg-slate-100 py-8 px-4 text-slate-800 font-sans antialiased">

    <?php
    $totalAmt = (float) ($order['total_amount'] ?? 0);
    $shippingCharge = (float) ($order['shipping_charge'] ?? 0);
    $discountAmt = (float) ($order['discount_amount'] ?? 0);
    $taxTotal = isset($order['tax_total']) ? (float) $order['tax_total'] : (isset($order['tax_amount']) ? (float) $order['tax_amount'] : 0);

    $grossItemsSubtotal = max(0, $totalAmt - $shippingCharge + $discountAmt);

    if ($taxTotal <= 0 && $grossItemsSubtotal > 0) {
        $taxTotal = round($grossItemsSubtotal - ($grossItemsSubtotal / 1.18), 2);
    }

    $baseSubtotal = max(0, $grossItemsSubtotal - $taxTotal);
    ?>

    <!-- Invoice Container Wrapper -->
    <div
        class="max-w-4xl mx-auto bg-white p-8 md:p-12 rounded-2xl shadow-2xl print-shadow-none print:p-0 relative overflow-hidden border border-slate-200">

        <!-- Top Brand Accent Bar -->
        <div class="absolute top-0 left-0 right-0 h-2.5 bg-gradient-to-r from-brand-red via-red-600 to-brand-dark">
        </div>

        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start border-b border-slate-200 pb-8 mb-8 mt-2">
            <!-- Left: Company Logo & Statutory Info -->
            <div class="mb-6 md:mb-0">
                <div class="flex items-center space-x-3 mb-2">
                    <h1 class="text-3xl font-black text-brand-red tracking-wider">MUDSOR</h1>
                    <span
                        class="bg-slate-100 text-slate-700 text-[10px] font-semibold px-2 py-0.5 rounded uppercase border border-slate-300">
                        Official Store
                    </span>
                </div>
                <p class="font-extrabold text-slate-900 text-sm uppercase tracking-wide">
                    <?= htmlspecialchars($company['legal_name'] ?? 'RUGH WANI ENTERPRISES') ?>
                </p>
                <div class="text-xs text-slate-600 mt-2 space-y-1 leading-relaxed">
                    <p class="max-w-xs text-slate-500 font-medium"><?= htmlspecialchars($company['address']) ?></p>
                    <p><span class="font-semibold text-slate-800">GSTIN:</span> <strong
                            class="font-mono text-slate-900 bg-slate-100 px-1.5 py-0.5 rounded border border-slate-200"><?= htmlspecialchars($company['gstin']) ?></strong>
                    </p>
                    <p><span class="font-semibold text-slate-800">Phone:</span>
                        <?= htmlspecialchars($company['phone']) ?> | <span
                            class="font-semibold text-slate-800">Email:</span>
                        <?= htmlspecialchars($company['email']) ?></p>
                    <p><span class="font-semibold text-slate-800">State Code:</span> 07 - DELHI (Place of Supply)</p>
                </div>
            </div>

            <!-- Right: Tax Invoice Status & Metadata -->
            <div class="text-left md:text-right">
                <div
                    class="inline-flex items-center space-x-2 bg-red-50 border border-red-200 text-brand-red font-extrabold px-4 py-1.5 rounded-full text-xs uppercase tracking-wider mb-3">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>GST TAX INVOICE</span>
                </div>
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                    #<?= htmlspecialchars($order['order_number']) ?></h2>
                <div class="text-xs text-slate-600 mt-2 space-y-1">
                    <p>Date of Invoice: <strong
                            class="text-slate-900"><?= date('d M Y', strtotime($order['created_at'])) ?></strong></p>
                    <p>Payment Mode: <strong
                            class="uppercase text-slate-900"><?= htmlspecialchars($order['payment_provider'] ?? $order['payment_method'] ?? 'COD') ?></strong>
                    </p>
                    <div class="pt-1">
                        <?php
                        $pStatus = strtolower($order['payment_status'] ?? 'pending');
                        if ($pStatus === 'paid'): ?>
                            <span
                                class="inline-block bg-emerald-100 text-emerald-800 border border-emerald-300 font-extrabold px-3 py-0.5 rounded-md text-[11px] uppercase">
                                ✓ PAYMENT PAID
                            </span>
                        <?php else: ?>
                            <span
                                class="inline-block bg-amber-100 text-amber-800 border border-amber-300 font-extrabold px-3 py-0.5 rounded-md text-[11px] uppercase">
                                ⏳ PAYMENT <?= strtoupper($pStatus) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Billing & Shipping Information Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Billed To Box -->
            <div class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Billed To
                        (Customer)</span>
                    <span
                        class="text-[10px] bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded font-mono font-semibold">RECIPIENT</span>
                </div>
                <p class="font-extrabold text-slate-900 text-base mb-1"><?= htmlspecialchars($order['customer_name']) ?>
                </p>
                <div class="text-xs text-slate-600 space-y-1 font-medium">
                    <p><span class="text-slate-400">Phone:</span> <?= htmlspecialchars($order['customer_phone']) ?></p>
                    <p><span class="text-slate-400">Email:</span> <?= htmlspecialchars($order['customer_email']) ?></p>
                    <?php if (!empty($order['gstin'])): ?>
                        <p class="pt-1">
                            <span
                                class="font-semibold text-slate-900 bg-white px-2 py-0.5 border border-slate-300 rounded text-[11px] font-mono">
                                GSTIN: <?= htmlspecialchars($order['gstin']) ?>
                            </span>
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Shipped To Box -->
            <div class="bg-slate-50 p-5 rounded-xl border border-slate-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest">Shipped To
                        (Destination)</span>
                    <span
                        class="text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-mono font-semibold">EXPRESS
                        DELIVERY</span>
                </div>
                <p class="text-xs text-slate-700 leading-relaxed font-semibold">
                    <?= htmlspecialchars($shippingAddress['address_line1'] ?? '') ?><br>
                    <?php if (!empty($shippingAddress['address_line2'])): ?>
                        <?= htmlspecialchars($shippingAddress['address_line2'] ?? '') ?><br>
                    <?php endif; ?>
                    <?= htmlspecialchars($shippingAddress['city'] ?? '') ?>,
                    <?= htmlspecialchars($shippingAddress['state'] ?? '') ?> -
                    <strong
                        class="font-mono text-slate-900"><?= htmlspecialchars($shippingAddress['pincode'] ?? '') ?></strong>
                </p>
            </div>
        </div>

        <!-- Itemized Order Table with Detailed GST Breakdown -->
        <div class="overflow-x-auto mb-8 border border-slate-200 rounded-xl shadow-xs">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white text-[10px] uppercase tracking-wider font-semibold">
                        <th class="py-3 px-2.5 border-r border-slate-800 text-center w-8">#</th>
                        <th class="py-3 px-3 border-r border-slate-800">Item Description</th>
                        <th class="py-3 px-2.5 border-r border-slate-800 text-center">HSN Code</th>
                        <th class="py-3 px-2.5 border-r border-slate-800 text-right">Unit Price (Incl. GST)</th>
                        <th class="py-3 px-2.5 border-r border-slate-800 text-center w-12">GST %</th>
                        <th class="py-3 px-2.5 border-r border-slate-800 text-right">GST Tax (Included)</th>
                        <th class="py-3 px-2.5 border-r border-slate-800 text-center w-10">Qty</th>
                        <th class="py-3 px-3 text-right">Total Amount</th>
                    </tr>
                </thead>
                <tbody class="text-xs divide-y divide-slate-200 font-medium">
                    <?php foreach ($items as $idx => $item):
                        $grossPrice = (float) ($item['price'] ?? 0);
                        $gstPercent = (float) ($item['tax_percent'] ?? 18);
                        $qty = (int) ($item['quantity'] ?? 1);

                        $unitBasePrice = $grossPrice / (1 + ($gstPercent / 100));
                        $unitGstAmt = $grossPrice - $unitBasePrice;

                        $lineTaxAmt = isset($item['tax_amount']) && (float) $item['tax_amount'] > 0
                            ? (float) $item['tax_amount']
                            : ($unitGstAmt * $qty);

                        $lineGrossTotal = (float) ($item['total_amount'] ?? ($grossPrice * $qty));
                        ?>
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-2.5 text-center text-slate-400 font-mono font-semibold"><?= $idx + 1 ?></td>
                            <td class="py-3 px-3 font-semibold text-slate-900">
                                <?= htmlspecialchars($item['product_name']) ?>
                                <div class="text-[10px] text-slate-400 font-mono font-normal">SKU:
                                    <?= htmlspecialchars($item['sku']) ?></div>
                            </td>
                            <td class="py-3 px-2.5 text-center">
                                <span
                                    class="bg-slate-100 border border-slate-300 text-slate-800 font-mono font-semibold px-1.5 py-0.5 rounded text-[10px]">
                                    <?= htmlspecialchars($item['hsn_code'] ?? '8714.99.90') ?>
                                </span>
                            </td>
                            <td class="py-3 px-2.5 text-right font-mono text-slate-900 font-semibold">
                                <?= format_price($grossPrice) ?>
                            </td>
                            <td class="py-3 px-2.5 text-center font-mono text-slate-600 font-semibold">
                                <?= number_format($gstPercent, 0) ?>%
                            </td>
                            <td class="py-3 px-2.5 text-right font-mono text-brand-red font-semibold">
                                <?= format_price($lineTaxAmt) ?>
                            </td>
                            <td class="py-3 px-2.5 text-center font-extrabold text-slate-900">
                                <?= $qty ?>
                            </td>
                            <td class="py-3 px-3 text-right font-mono font-extrabold text-slate-900">
                                <?= format_price($lineGrossTotal) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Financial Summary & Statutory Tax Declaration Section -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 pt-2 border-t border-slate-200">

            <!-- Left 7 cols: Statutory Tax Declaration & Terms -->
            <div class="md:col-span-7 space-y-3">
                <div class="bg-red-50/80 border border-red-200 p-4 rounded-xl space-y-1.5">
                    <h4
                        class="text-xs font-extrabold text-brand-red uppercase tracking-wider flex items-center space-x-1.5">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Statutory Tax Declaration</span>
                    </h4>
                    <p class="text-xs text-slate-700 font-semibold leading-relaxed">
                        * Certified that the particulars given above are true and correct. All product prices are
                        <strong>INCLUSIVE OF GST</strong> as per Central &amp; State GST Tax Acts.
                    </p>
                </div>

                <div class="text-[11px] text-slate-500 space-y-1 leading-relaxed">
                    <p>• Goods once sold are covered under Mudsor Warranty &amp; Return Policies.</p>
                    <p>• This is a computer-generated official tax invoice issued by
                        <strong><?= htmlspecialchars($company['legal_name']) ?></strong>.</p>
                </div>
            </div>

            <!-- Right 5 cols: Order Totals Box -->
            <div class="md:col-span-5 bg-slate-50 rounded-xl p-5 border border-slate-200 shadow-xs">
                <div class="space-y-2.5 text-xs">
                    <div class="flex justify-between text-slate-600 font-medium">
                        <span>Subtotal</span>
                        <span
                            class="font-mono font-semibold text-slate-900"><?= format_price($grossItemsSubtotal) ?></span>
                    </div>
                    <div class="flex justify-between text-slate-600 font-medium">
                        <span>GST Tax (Included)</span>
                        <span
                            class="font-mono font-semibold text-brand-red font-semibold"><?= format_price($taxTotal) ?></span>
                    </div>
                    <div class="flex justify-between text-slate-600 font-medium">
                        <span>Shipping Charge</span>
                        <span
                            class="font-mono font-semibold text-slate-900"><?= $shippingCharge == 0 ? 'FREE' : format_price($shippingCharge) ?></span>
                    </div>
                    <?php if ($discountAmt > 0): ?>
                        <div class="flex justify-between text-emerald-700 font-semibold">
                            <span>Discount</span>
                            <span class="font-mono">-<?= format_price($discountAmt) ?></span>
                        </div>
                    <?php endif; ?>

                    <div class="pt-3 mt-3 border-t-2 border-slate-900 flex justify-between items-center">
                        <span class="text-sm font-extrabold text-slate-900 uppercase">Grand Total</span>
                        <span class="text-xl font-black text-brand-red font-mono"><?= format_price($totalAmt) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Signature Block -->
        <div
            class="mt-10 pt-6 border-t border-slate-200 flex flex-col sm:flex-row justify-between items-center sm:items-end gap-6">
            <div class="text-[11px] text-slate-400 text-center sm:text-left">
                <p class="font-semibold text-slate-700">Thank you for shopping with Mudsor!</p>
                <p>For any invoice or tax inquiries, email: <a href="mailto:<?= htmlspecialchars($company['email']) ?>"
                        class="underline text-slate-600"><?= htmlspecialchars($company['email']) ?></a></p>
            </div>

            <div class="text-center shrink-0">
                <div
                    class="h-14 w-40 mx-auto border border-dashed border-slate-300 rounded-lg mb-2 relative flex items-center justify-center bg-slate-50/50">
                    <span
                        class="text-[10px] text-brand-red font-semibold uppercase tracking-wider opacity-80 border border-brand-red/30 px-2 py-0.5 rounded bg-red-50">
                        ✓ DIGITALLY SIGNED
                    </span>
                </div>
                <p class="text-xs font-extrabold text-slate-900 uppercase">For
                    <?= htmlspecialchars($company['legal_name']) ?></p>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Authorized Signatory</p>
            </div>
        </div>

    </div>

    <!-- Floating Print Button (Hidden in Print) -->
    <div class="max-w-4xl mx-auto mt-6 text-center print:hidden">
        <button onclick="window.print()"
            class="inline-flex items-center justify-center px-8 py-3 bg-slate-900 hover:bg-black text-white font-semibold text-xs rounded-full shadow-lg transition-transform transform hover:-translate-y-0.5 focus:outline-none focus:ring-4 focus:ring-slate-900 cursor-pointer space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                </path>
            </svg>
            <span>Print Tax Invoice</span>
        </button>
    </div>

</body>

</html>