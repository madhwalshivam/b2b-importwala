<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;
use App\Models\Setting;
use PDO;

class ReportController extends Controller {

    public function salesTax(): string {
        $this->ensureAdminOrAccess();

        $data = $this->prepareReportData();

        return $this->render('admin/reports/sales_tax', $data);
    }

    public function salesTaxPdf(): string {
        $this->ensureAdminOrAccess();

        $data = $this->prepareReportData();

        return $this->render('admin/reports/sales_tax_pdf', $data);
    }

    public function salesTaxCsv(): void {
        $this->ensureAdminOrAccess();

        $data = $this->prepareReportData();
        $orders = $data['orders'];
        $stats = $data['stats'];
        $filterLabel = $data['filter_label'];

        $filename = 'Mudsor_Sales_Tax_Report_' . str_replace(' ', '_', $filterLabel) . '.csv';

        // Clear any previous output buffers so CSV stream downloads cleanly
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // UTF-8 BOM for Excel compatibility
        fputs($output, "\xEF\xBB\xBF");

        // Header info rows
        fputcsv($output, ['Mudsor - Monthly Sales & Tax Report']);
        fputcsv($output, ['Period / Filter', $filterLabel]);
        fputcsv($output, ['Generated On', date('d M Y, h:i A')]);
        fputcsv($output, []);

        // Summary row
        fputcsv($output, ['SUMMARY TOTALS']);
        fputcsv($output, ['Total Orders', $stats['total_orders']]);
        fputcsv($output, ['Gross Sales (INR)', number_format($stats['gross_sales'], 2, '.', '')]);
        fputcsv($output, ['GST Tax Collected (INR)', number_format($stats['total_tax'], 2, '.', '')]);
        fputcsv($output, ['Net Sales (INR)', number_format($stats['net_sales'], 2, '.', '')]);
        fputcsv($output, ['Cancelled Orders Count', $stats['cancelled_count']]);
        fputcsv($output, ['Cancelled Amount (INR)', number_format($stats['cancelled_amount'], 2, '.', '')]);
        fputcsv($output, []);

        // Table headers
        fputcsv($output, [
            'Order #',
            'Order Date',
            'Customer Name',
            'Customer Phone',
            'Customer Email',
            'HSN Code',
            'GST %',
            'Subtotal Excl. Tax (INR)',
            'Tax Amount (INR)',
            'Shipping (INR)',
            'Discount (INR)',
            'Gross Total (INR)',
            'Payment Method',
            'Payment Status',
            'Order Status'
        ]);

        // Table rows
        foreach ($orders as $ord) {
            fputcsv($output, [
                $ord['order_number'],
                date('d M Y h:i A', strtotime($ord['created_at'])),
                $ord['customer_name'] ?? 'N/A',
                $ord['customer_phone'] ?? 'N/A',
                $ord['customer_email'] ?? 'N/A',
                $ord['hsn_codes'] ?? '8714.99.90',
                $ord['gst_rates'] ?? '18%',
                number_format((float)($ord['computed_subtotal'] ?? 0), 2, '.', ''),
                number_format((float)($ord['computed_tax'] ?? 0), 2, '.', ''),
                number_format((float)($ord['shipping_charge'] ?? 0), 2, '.', ''),
                number_format((float)($ord['discount_amount'] ?? 0), 2, '.', ''),
                number_format((float)($ord['total_amount'] ?? 0), 2, '.', ''),
                strtoupper($ord['payment_provider'] ?? $ord['payment_method'] ?? 'COD'),
                strtoupper($ord['payment_status'] ?? 'pending'),
                strtoupper($ord['order_status'] ?? 'pending')
            ]);
        }

        // Summary row at bottom
        fputcsv($output, []);
        fputcsv($output, [
            'TOTALS',
            '',
            '',
            '',
            '',
            '',
            '',
            number_format($stats['total_subtotal'], 2, '.', ''),
            number_format($stats['total_tax'], 2, '.', ''),
            number_format($stats['total_shipping'], 2, '.', ''),
            number_format($stats['total_discount'], 2, '.', ''),
            number_format($stats['gross_sales'], 2, '.', ''),
            '',
            '',
            ''
        ]);

        fclose($output);
        exit;
    }

    private function ensureAdminOrAccess(): void {
        if (!Auth::check()) {
            $this->redirect(url('admin/login'));
            exit;
        }

        // Allow Super Admin or users with orders/reports view permission
        $user = Auth::user();
        if (!$user) {
            $this->redirect(url('admin/login'));
            exit;
        }

        if (!Auth::hasPermission('orders.view') && !Auth::canAccessModule('orders') && ($user['role_slug'] ?? '') !== 'super-admin') {
            $this->setFlash('danger', 'Access denied to Sales & Tax Reports.');
            $this->redirect(url('admin/dashboard'));
            exit;
        }
    }

    private function prepareReportData(): array {
        $db = Database::getInstance();

        // Query params
        $selectedMonth = isset($_GET['month']) && is_numeric($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
        $selectedYear = isset($_GET['year']) && is_numeric($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
        $fromDate = isset($_GET['from_date']) ? trim($_GET['from_date']) : '';
        $toDate = isset($_GET['to_date']) ? trim($_GET['to_date']) : '';

        // Validate date filters
        if (!empty($fromDate) && !empty($toDate)) {
            $startDate = $fromDate . ' 00:00:00';
            $endDate = $toDate . ' 23:59:59';
            $filterLabel = date('d M Y', strtotime($fromDate)) . ' to ' . date('d M Y', strtotime($toDate));
        } else {
            $startDate = sprintf('%04d-%02d-01 00:00:00', $selectedYear, $selectedMonth);
            $lastDay = date('t', strtotime($startDate));
            $endDate = sprintf('%04d-%02d-%02d 23:59:59', $selectedYear, $selectedMonth, $lastDay);
            $filterLabel = date('F Y', strtotime($startDate));
        }

        // Fetch Orders with HSN Code & GST Rate aggregation (Newest first)
        $sql = "
            SELECT 
                o.*, 
                GROUP_CONCAT(DISTINCT COALESCE(NULLIF(oi.hsn_code, ''), '8714.99.90') SEPARATOR ', ') AS hsn_codes,
                GROUP_CONCAT(DISTINCT CONCAT(ROUND(COALESCE(oi.tax_percent, 18)), '%') SEPARATOR ', ') AS gst_rates,
                SUM(COALESCE(oi.tax_amount, 0)) AS items_tax_sum
            FROM orders o
            LEFT JOIN order_items oi ON o.id = oi.order_id
            WHERE o.created_at >= ? AND o.created_at <= ?
            GROUP BY o.id
            ORDER BY o.created_at DESC, o.id DESC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        $rawOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Aggregate statistics
        $totalOrders = count($rawOrders);
        $grossSales = 0.0;
        $totalTax = 0.0;
        $totalSubtotal = 0.0;
        $totalItemsGross = 0.0;
        $totalShipping = 0.0;
        $totalDiscount = 0.0;
        $cancelledCount = 0;
        $cancelledAmount = 0.0;

        $orders = [];

        foreach ($rawOrders as $ord) {
            if (empty($ord['hsn_codes'])) {
                $ord['hsn_codes'] = '8714.99.90';
            }
            if (empty($ord['gst_rates'])) {
                $ord['gst_rates'] = '18%';
            }

            $totalAmt = (float)($ord['total_amount'] ?? 0);
            $shipCharge = (float)($ord['shipping_charge'] ?? 0);
            $discAmt = (float)($ord['discount_amount'] ?? 0);
            $itemsTaxSum = (float)($ord['items_tax_sum'] ?? 0);

            // Items Gross Subtotal (Inclusive of GST)
            $subTotal = max(0, $totalAmt - $shipCharge + $discAmt);

            // Get exact GST Tax amount for order
            $taxTotal = isset($ord['tax_total']) && (float)$ord['tax_total'] > 0 
                ? (float)$ord['tax_total'] 
                : ($itemsTaxSum > 0 ? $itemsTaxSum : (float)($ord['tax_amount'] ?? 0));

            if ($taxTotal <= 0 && $subTotal > 0) {
                $effectiveGstRate = (float)preg_replace('/[^0-9.]/', '', $ord['gst_rates']);
                if ($effectiveGstRate <= 0) { $effectiveGstRate = 18; }
                $taxTotal = round($subTotal - ($subTotal / (1 + ($effectiveGstRate / 100))), 2);
            }

            $ord['computed_tax'] = $taxTotal;
            $ord['computed_subtotal'] = $subTotal;

            $status = strtolower($ord['order_status'] ?? '');

            if ($status === 'cancelled') {
                $cancelledCount++;
                $cancelledAmount += $totalAmt;
            } else {
                $grossSales += $totalAmt;
                $totalTax += $taxTotal;
                $totalSubtotal += $subTotal;
                $totalShipping += $shipCharge;
                $totalDiscount += $discAmt;
            }

            $orders[] = $ord;
        }

        $netSales = max(0, $grossSales - $totalTax);

        $stats = [
            'total_orders'     => $totalOrders,
            'gross_sales'      => $grossSales,
            'total_tax'        => $totalTax,
            'total_subtotal'   => $totalSubtotal,
            'total_shipping'   => $totalShipping,
            'total_discount'   => $totalDiscount,
            'net_sales'        => $netSales,
            'cancelled_count'  => $cancelledCount,
            'cancelled_amount' => $cancelledAmount,
            'period_label'     => $filterLabel
        ];

        // Fetch company details from settings
        $settingModel = new Setting();
        $settings = $settingModel->getAllAsKeyValue();

        $company = [
            'brand'      => $settings['site_name'] ?? 'Mudsor',
            'legal_name' => $settings['company_legal_name'] ?? 'Rughwani Enterprises',
            'gstin'      => $settings['gstin'] ?? '07FLOPR6641L1Z8',
            'phone'      => $settings['contact_phone'] ?? '+91 9217714452',
            'email'      => $settings['contact_email'] ?? 'mudsorinfo@gmail.com',
            'owner'      => $settings['owner_name'] ?? 'Jass Rughwani',
        ];

        // Month & Year lists for filters
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];

        $currentYr = (int)date('Y');
        $years = range($currentYr - 3, $currentYr + 1);

        return [
            'orders'        => $orders,
            'stats'         => $stats,
            'company'       => $company,
            'selectedMonth' => $selectedMonth,
            'selectedYear'  => $selectedYear,
            'fromDate'      => $fromDate,
            'toDate'        => $toDate,
            'filter_label'  => $filterLabel,
            'months'        => $months,
            'years'         => $years,
        ];
    }
}
