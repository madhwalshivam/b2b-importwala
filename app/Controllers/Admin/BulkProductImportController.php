<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\BulkImportService;
use App\Core\Response;

class BulkProductImportController extends BaseController
{
    private BulkImportService $importService;

    public function __construct()
    {
        $this->importService = new BulkImportService();
    }

    /**
     * Download the official 55-column annotated XLSX template.
     */
    public function downloadTemplate(): void
    {
        $format = $_GET['format'] ?? 'xlsx';
        $filePath = $this->importService->generateTemplate($format);
        $filename = 'importwala_bulk_products_template.' . $format;

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filePath));
        header('Pragma: no-cache');
        header('Expires: 0');

        readfile($filePath);
        @unlink($filePath);
        exit;
    }

    /**
     * Upload spreadsheet & companion ZIP, parse, validate, and return JSON preview.
     */
    public function parse(): void
    {
        header('Content-Type: application/json');

        if (empty($_FILES['file']['tmp_name']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Please upload a valid .xlsx or .csv file.']);
            return;
        }

        $tmpFile = $_FILES['file']['tmp_name'];
        $zipTmpFile = !empty($_FILES['zip_file']['tmp_name']) && $_FILES['zip_file']['error'] === UPLOAD_ERR_OK
            ? $_FILES['zip_file']['tmp_name']
            : null;

        $autoCreateCat = !isset($_POST['auto_create_category']) || $_POST['auto_create_category'] === '1';

        $result = $this->importService->parseAndValidate($tmpFile, $zipTmpFile, $autoCreateCat);

        if ($result['success']) {
            $_SESSION['bulk_import_preview'] = $result;
        }

        echo json_encode($result);
    }

    /**
     * Commit validated products & variants to database.
     */
    public function commit(): void
    {
        header('Content-Type: application/json');

        $previewData = $_SESSION['bulk_import_preview'] ?? null;
        if (!$previewData || empty($previewData['products'])) {
            echo json_encode(['success' => false, 'error' => 'No active preview session found. Please re-upload your file.']);
            return;
        }

        $result = $this->importService->commitImport(
            $previewData['products'],
            $previewData['extracted_zip_dir'] ?? null
        );

        if ($result['success']) {
            $_SESSION['bulk_import_last_errors'] = $result['errors'] ?? [];
            unset($_SESSION['bulk_import_preview']);
        }

        echo json_encode($result);
    }

    /**
     * Download CSV of failed rows from last import attempt.
     */
    public function errorsCsv(): void
    {
        $errors = $_SESSION['bulk_import_last_errors'] ?? [];
        if (empty($errors)) {
            echo "No errors logged in the previous import session.";
            return;
        }

        $filename = 'importwala_import_errors_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Product SKU', 'Product Name', 'Error Reason']);

        foreach ($errors as $e) {
            fputcsv($output, [$e['sku'] ?? '', $e['name'] ?? '', $e['reason'] ?? '']);
        }

        fclose($output);
        exit;
    }
}
