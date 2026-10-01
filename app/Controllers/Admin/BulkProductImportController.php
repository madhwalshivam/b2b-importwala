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

        try {
            $result = $this->importService->parseAndValidate($tmpFile, $zipTmpFile, $autoCreateCat);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'File parsing failed: ' . $e->getMessage()]);
            return;
        }

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

        $chunkIndex = (int)($_POST['chunk'] ?? $_GET['chunk'] ?? 0);
        $chunkSize = 10;
        
        if ($chunkIndex === 0) {
            // Verify essential tables exist
            $requiredTables = [
                'products', 'product_categories', 'product_brands', 'product_images', 
                'product_colors', 'product_color_sizes', 'product_variants', 'product_variations',
                'product_specifications', 'categories', 'subcategories', 'brands', 'factories'
            ];
            
            $db = \App\Core\Database::getInstance();
            $missingTables = [];
            foreach ($requiredTables as $t) {
                $check = $db->query("SHOW TABLES LIKE '$t'")->fetchColumn();
                if (!$check) {
                    $missingTables[] = $t;
                }
            }
            
            if (!empty($missingTables)) {
                echo json_encode(['success' => false, 'error' => 'Database is missing tables: ' . implode(', ', $missingTables)]);
                return;
            }

            $_SESSION['bulk_import_errors'] = [];
            $_SESSION['bulk_import_summary'] = [
                'total_rows' => $previewData['summary']['total_rows'] ?? 0,
                'total_products' => $previewData['summary']['total_products'] ?? 0,
                'total_variants' => $previewData['summary']['total_variants'] ?? 0,
                'valid_products' => 0,
                'error_products' => 0,
                'staged_factories' => $previewData['summary']['staged_factories'] ?? 0,
            ];
            $_SESSION['image_mirror_stats'] = ['mirrored' => 0, 'failed' => 0];
        }

        $totalProducts = count($previewData['products']);
        $chunkProducts = array_slice($previewData['products'], $chunkIndex * $chunkSize, $chunkSize);

        if (empty($chunkProducts)) {
            $result = [
                'success' => true,
                'summary' => $_SESSION['bulk_import_summary'],
                'errors' => $_SESSION['bulk_import_errors'],
                'image_stats' => $_SESSION['image_mirror_stats'] ?? [],
                'finished' => true
            ];
            
            $_SESSION['bulk_import_last_errors'] = $_SESSION['bulk_import_errors'];
            unset($_SESSION['bulk_import_preview']);
            try { \App\Infrastructure\Cache\CacheManager::getInstance()->flush(); } catch (\Throwable $e) {}
            
            echo json_encode($result);
            return;
        }

        try {
            $result = $this->importService->commitImport(
                $chunkProducts,
                $previewData['extracted_zip_dir'] ?? null
            );
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => 'Import commit failed: ' . $e->getMessage()]);
            return;
        }

        if (isset($result['success']) && !$result['success']) {
            echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Unknown database error during commit']);
            return;
        }

        if (!empty($result['errors'])) {
            $_SESSION['bulk_import_errors'] = array_merge($_SESSION['bulk_import_errors'], $result['errors']);
        }
        $_SESSION['bulk_import_summary']['created_products'] = ($_SESSION['bulk_import_summary']['created_products'] ?? 0) + ($result['created_products'] ?? 0);
        $_SESSION['bulk_import_summary']['updated_products'] = ($_SESSION['bulk_import_summary']['updated_products'] ?? 0) + ($result['updated_products'] ?? 0);
        $_SESSION['bulk_import_summary']['created_variants'] = ($_SESSION['bulk_import_summary']['created_variants'] ?? 0) + ($result['created_variants'] ?? 0);
        $_SESSION['bulk_import_summary']['updated_variants'] = ($_SESSION['bulk_import_summary']['updated_variants'] ?? 0) + ($result['updated_variants'] ?? 0);
        $_SESSION['bulk_import_summary']['error_products']   = ($_SESSION['bulk_import_summary']['error_products'] ?? 0) + ($result['skipped_products'] ?? 0);
        
        if (isset($result['image_stats'])) {
            $_SESSION['image_mirror_stats']['mirrored'] += $result['image_stats']['mirrored'] ?? 0;
            $_SESSION['image_mirror_stats']['failed'] += $result['image_stats']['failed'] ?? 0;
        }

        echo json_encode([
            'success' => true,
            'finished' => false,
            'processed' => ($chunkIndex * $chunkSize) + count($chunkProducts),
            'total' => $totalProducts,
            'percentage' => round((($chunkIndex * $chunkSize) + count($chunkProducts)) / $totalProducts * 100)
        ]);
    }

    /**
     * Trigger background image synchronization
     */
    public function syncImages(): void
    {
        header('Content-Type: application/json');
        
        // Close session immediately so the HTTP request finishes and doesn't block the UI
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        // Spawn background CLI process to sync images
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
