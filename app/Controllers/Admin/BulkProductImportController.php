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

    // ------------------------------------------------------------------ template

    /**
     * Download the official XLSX template.
     */
    public function downloadTemplate(): void
    {
        $format   = $_GET['format'] ?? 'xlsx';
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

    // ------------------------------------------------------------------ Phase 1: parse & validate

    /**
     * POST /admin/products/import/parse
     *
     * Parses the whole sheet, runs ALL validations, returns JSON preview.
     * Nothing is written to DB or R2.
     */
    public function parse(): void
    {
        header('Content-Type: application/json');

        if (empty($_FILES['file']['tmp_name']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Please upload a valid .xlsx or .csv file.']);
            return;
        }

        $tmpFile    = $_FILES['file']['tmp_name'];
        $zipTmpFile = (!empty($_FILES['zip_file']['tmp_name']) && $_FILES['zip_file']['error'] === UPLOAD_ERR_OK)
            ? $_FILES['zip_file']['tmp_name']
            : null;

        $autoCreateCat = !isset($_POST['auto_create_category']) || $_POST['auto_create_category'] === '1';

        try {
            $result = $this->importService->parseAndValidate($tmpFile, $zipTmpFile, $autoCreateCat);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'File parsing failed: ' . $e->getMessage()]);
            return;
        }

        if (!$result['success']) {
            echo json_encode($result);
            return;
        }

        // Store validated preview in session regardless of error status
        // (admin needs the session to download the error report)
        $_SESSION['bulk_import_preview'] = $result;

        // If there are errors, the commit button must be blocked.
        // We still return success=>true with has_errors flag so the UI can
        // show the detailed preview table with error rows highlighted.
        echo json_encode($result);
    }

    // ------------------------------------------------------------------ Phase 2: commit

    /**
     * POST /admin/products/import/commit
     *
     * Called ONLY when has_errors === false. Runs everything in ONE transaction.
     * Returns a single JSON response (not chunked) because atomicity requires it.
     */
    public function commit(): void
    {
        header('Content-Type: application/json');

        $previewData = $_SESSION['bulk_import_preview'] ?? null;

        if (!$previewData || empty($previewData['products'])) {
            echo json_encode(['success' => false, 'error' => 'No active preview session. Please re-upload your file.']);
            return;
        }

        // Guard: refuse if Phase 1 reported errors
        if (!empty($previewData['has_errors'])) {
            echo json_encode(['success' => false, 'error' => 'Cannot commit: the import has validation errors. Fix the errors and re-upload.']);
            return;
        }

        // Verify essential tables
        $db             = \App\Core\Database::getInstance();
        $requiredTables = [
            'products', 'product_categories', 'product_brands', 'product_images',
            'product_colors', 'product_color_sizes', 'product_variants', 'product_variations',
            'product_specifications', 'categories', 'subcategories', 'brands', 'factories',
        ];
        $missingTables = [];
        foreach ($requiredTables as $t) {
            if (!$db->query("SHOW TABLES LIKE '$t'")->fetchColumn()) {
                $missingTables[] = $t;
            }
        }
        if (!empty($missingTables)) {
            echo json_encode(['success' => false, 'error' => 'Database is missing tables: ' . implode(', ', $missingTables)]);
            return;
        }

        // Run atomic commit
        try {
            $result = $this->importService->commitImport(
                $previewData['products'],
                $previewData['extracted_zip_dir'] ?? null
            );
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'Import commit failed: ' . $e->getMessage()]);
            return;
        }

        if (!($result['success'] ?? false)) {
            echo json_encode($result); // already contains error message
            return;
        }

        // Clear session preview on success
        unset($_SESSION['bulk_import_preview']);
        $_SESSION['bulk_import_last_result'] = $result;

        // Flush page/product cache
        try { \App\Infrastructure\Cache\CacheManager::getInstance()->flush(); } catch (\Throwable $e) {}

        echo json_encode(array_merge($result, ['finished' => true]));
    }

    // ------------------------------------------------------------------ error report download

    /**
     * GET /admin/products/import/errors-xlsx
     *
     * Generates and streams a downloadable XLSX error report from the last
     * parse session (including both errors and warnings).
     */
    public function errorsXlsx(): void
    {
        $previewData = $_SESSION['bulk_import_preview'] ?? null;
        $products    = $previewData['products'] ?? [];
        $globalErrors = $previewData['global_errors'] ?? [];

        if (empty($products) && empty($globalErrors)) {
            header('Content-Type: text/plain');
            echo 'No errors or warnings in the current import session. Please re-upload your file first.';
            return;
        }

        $filePath = $this->importService->generateErrorReport($products, $globalErrors);
        $filename = 'importwala_import_errors_' . date('Ymd_His') . '.xlsx';

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
     * GET /admin/products/import/errors-csv  (legacy fallback)
     */
    public function errorsCsv(): void
    {
        $previewData  = $_SESSION['bulk_import_preview'] ?? null;
        $products     = $previewData['products'] ?? [];
        $globalErrors = $previewData['global_errors'] ?? [];

        $filename = 'importwala_import_errors_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Sheet Row', 'Product SKU', 'Product Name', 'Column', 'Error Reason', 'Severity']);

        foreach ($globalErrors as $err) {
            fputcsv($output, ['-', '-', 'GLOBAL', $err['column'] ?? '', $err['reason'] ?? (string)$err, 'ERROR']);
        }
        foreach ($products as $prod) {
            foreach (($prod['errors'] ?? []) as $e) {
                fputcsv($output, [
                    is_array($e) ? ($e['row'] ?? '') : '',
                    is_array($e) ? ($e['sku'] ?? $prod['product_sku'] ?? '') : $prod['product_sku'] ?? '',
                    is_array($e) ? ($e['name'] ?? $prod['name'] ?? '') : $prod['name'] ?? '',
                    is_array($e) ? ($e['column'] ?? '') : '',
                    is_array($e) ? ($e['reason'] ?? '') : (string)$e,
                    'ERROR',
                ]);
            }
            foreach (($prod['warnings'] ?? []) as $w) {
                fputcsv($output, [
                    is_array($w) ? ($w['row'] ?? '') : '',
                    is_array($w) ? ($w['sku'] ?? $prod['product_sku'] ?? '') : $prod['product_sku'] ?? '',
                    is_array($w) ? ($w['name'] ?? $prod['name'] ?? '') : $prod['name'] ?? '',
                    is_array($w) ? ($w['column'] ?? '') : '',
                    is_array($w) ? ($w['reason'] ?? '') : (string)$w,
                    'WARNING',
                ]);
            }
        }
        fclose($output);
        exit;
    }

    // ------------------------------------------------------------------ image sync

    /**
     * POST /admin/products/import/sync-images
     *
     * Triggers background image mirroring (unchanged from v1).
     */
    public function syncImages(): void
    {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $cliScript = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'cli_sync_images.php';
        if (file_exists($cliScript)) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                pclose(popen('start /B php "' . $cliScript . '" > NUL', 'r'));
            } else {
                exec('php "' . $cliScript . '" > /dev/null 2>&1 &');
            }
        }

        echo json_encode(['success' => true]);
        exit;
    }
}
