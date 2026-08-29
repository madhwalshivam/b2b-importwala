<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\RfqRequest;
use App\Models\RfqPhoto;

class RfqController extends BaseController
{
    private RfqRequest $rfqModel;
    private RfqPhoto   $photoModel;

    private const UPLOAD_BASE = __DIR__ . '/../../../public/';

    public function __construct()
    {
        parent::__construct();
        $this->rfqModel   = new RfqRequest();
        $this->photoModel = new RfqPhoto();
    }

    /**
     * GET /admin/rfq  — List all RFQs with filters & pagination.
     */
    public function index(): void
    {
        $search   = trim($_GET['search'] ?? '');
        $status   = trim($_GET['status'] ?? '');
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo   = trim($_GET['date_to'] ?? '');
        $page     = max(1, (int)($_GET['page'] ?? 1));
        $limit    = 25;
        $offset   = ($page - 1) * $limit;

        $results    = $this->rfqModel->getAllFiltered($search, $status, $dateFrom, $dateTo, $limit, $offset);
        $totalPages = (int)ceil(($results['total'] ?? 0) / $limit);

        $this->renderView('admin/rfq/index', [
            'rfqs'        => $results['items'],
            'total'       => $results['total'],
            'search'      => $search,
            'status'      => $status,
            'dateFrom'    => $dateFrom,
            'dateTo'      => $dateTo,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'newCount'    => $this->rfqModel->getNewCount(),
        ]);
    }

    /**
     * GET /admin/rfq/{id}  — View a single RFQ.
     */
    public function show($id): void
    {
        $rfqId = (int)$id;
        $rfq   = $this->rfqModel->getWithPhotos($rfqId);

        if (!$rfq) {
            header('Location: ' . url('admin/rfq'));
            exit;
        }

        $this->renderView('admin/rfq/show', ['rfq' => $rfq]);
    }

    /**
     * POST /admin/rfq/update-status/{id}  — AJAX status update.
     */
    public function updateStatus($id): void
    {
        $rfqId  = (int)$id;
        $input  = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $status = trim($input['status'] ?? '');

        $success = $this->rfqModel->updateStatus($rfqId, $status);

        if ($this->isAjax()) {
            $this->jsonResponse(['success' => $success, 'status' => $status]);
            return;
        }

        header('Location: ' . url('admin/rfq/' . $rfqId));
        exit;
    }

    /**
     * GET /admin/rfq/export-csv  — Download filtered results as CSV.
     */
    public function exportCsv(): void
    {
        $search   = trim($_GET['search'] ?? '');
        $status   = trim($_GET['status'] ?? '');
        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo   = trim($_GET['date_to'] ?? '');

        $rows = $this->rfqModel->getForExport($search, $status, $dateFrom, $dateTo);

        $filename = 'rfq_requests_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Excel
        fputs($out, "\xEF\xBB\xBF");

        // Header row
        fputcsv($out, [
            'ID', 'Date', 'Product Name', 'Quantity', 'Unit',
            'Target Price (₹)', 'Overall Budget', 'Sourcing Purpose',
            'Full Name', 'Phone', 'Email', 'Pincode',
            'Business Type', 'Has GST', 'Status',
            'Reference Link', 'Specifications', 'Additional Comments',
        ]);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['id'],
                $row['created_at'],
                $row['product_name'],
                $row['quantity'],
                $row['unit'],
                $row['target_price'],
                $row['overall_budget'],
                $row['sourcing_purpose'],
                $row['full_name'],
                '+91' . $row['phone'],
                $row['email'],
                $row['pincode'],
                $row['business_type'],
                $row['has_gst'] ? 'Yes' : 'No',
                $row['status'],
                $row['product_reference_link'] ?? '',
                $row['specifications'] ?? '',
                $row['additional_comments'] ?? '',
            ]);
        }

        fclose($out);
        exit;
    }

    /**
     * POST /admin/rfq/delete/{id}  — Delete an RFQ & its photos.
     */
    public function delete($id): void
    {
        $rfqId = (int)$id;

        // Delete photo files from disk
        $photoPaths = $this->photoModel->deleteByRfq($rfqId);
        foreach ($photoPaths as $path) {
            $fullPath = self::UPLOAD_BASE . ltrim($path, '/');
            if (file_exists($fullPath)) {
                @unlink($fullPath);
            }
        }

        $this->rfqModel->deleteRfq($rfqId);

        if ($this->isAjax()) {
            $this->jsonResponse(['success' => true]);
            return;
        }

        header('Location: ' . url('admin/rfq'));
        exit;
    }

    // ---------------------------------------------------------------
    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
