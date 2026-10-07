<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\ImageMirrorWorker;
use App\Core\Database;

class ImageMirrorController extends Controller
{
    public function batch()
    {
        // For CLI or crontab, we might not have a CSRF token
        $worker = new ImageMirrorWorker();
        $result = $worker->run(20, 40);

        if ($this->request->isAjax() || strpos($this->request->getPath(), '-cli') !== false) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'data' => $result]);
            exit;
        }
        
        $this->redirect('/admin/products');
    }

    public function statusPage()
    {
        $db = Database::getInstance();
        
        // Ensure table exists, otherwise show friendly error
        $tableExists = $db->query("SHOW TABLES LIKE 'image_mirror_queue'")->fetchColumn();
        if (!$tableExists) {
            return $this->render('admin/image_sync_status', [
                'tableExists' => false,
                'createSql' => "CREATE TABLE IF NOT EXISTS `image_mirror_queue` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `source_url` varchar(2048) NOT NULL,
                  `source_hash` varchar(40) NOT NULL,
                  `r2_key` varchar(255) DEFAULT NULL,
                  `status` enum('pending','processing','done','failed') NOT NULL DEFAULT 'pending',
                  `attempts` int(11) DEFAULT 0,
                  `last_error` text DEFAULT NULL,
                  `created_at` timestamp NULL DEFAULT current_timestamp(),
                  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `idx_source_hash` (`source_hash`),
                  KEY `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
            ]);
        }

        $stats = [
            'pending' => $db->query("SELECT COUNT(*) FROM image_mirror_queue WHERE status = 'pending' AND attempts < 3")->fetchColumn(),
            'processing' => $db->query("SELECT COUNT(*) FROM image_mirror_queue WHERE status = 'processing'")->fetchColumn(),
            'done' => $db->query("SELECT COUNT(*) FROM image_mirror_queue WHERE status = 'done'")->fetchColumn(),
            'failed' => $db->query("SELECT COUNT(*) FROM image_mirror_queue WHERE status = 'failed' OR attempts >= 3")->fetchColumn()
        ];

        $recentErrors = $db->query("SELECT id, source_url, attempts, last_error, updated_at FROM image_mirror_queue WHERE status = 'failed' ORDER BY updated_at DESC LIMIT 10")->fetchAll(\PDO::FETCH_ASSOC);

        return $this->render('admin/image_sync_status', [
            'tableExists' => true,
            'stats' => $stats,
            'recentErrors' => $recentErrors
        ]);
    }

    public function retryFailed()
    {
        $db = Database::getInstance();
        $reset = $db->prepare("UPDATE image_mirror_queue SET status = 'pending', attempts = 0, last_error = NULL, updated_at = '2000-01-01 00:00:00' WHERE status = 'failed'");
        $reset->execute();
        $count = $reset->rowCount();

        $result = ['processed' => 0, 'done' => 0, 'failed' => 0];
        if ($count > 0) {
            $worker = new ImageMirrorWorker();
            $result = $worker->run(max(1, min(20, $count)), 35);
        }

        $this->redirect(url('admin/image-sync-status') . '?' . http_build_query([
            'retry_processed' => (int) $result['processed'],
            'retry_done' => (int) $result['done'],
            'retry_failed' => (int) $result['failed'],
        ]));
    }

    public function runSelftest()
    {
        header('Content-Type: application/json');

        try {
            ob_start();
            $cliScript = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . 'cli_mirror_queue.php';
            
            // Execute the self-test CLI script and capture output
            $output = [];
            $returnVar = 0;
            exec('php "' . $cliScript . '" --selftest 2>&1', $output, $returnVar);
            
            $fullOutput = implode("\n", $output);
            ob_end_clean();

            echo json_encode([
                'success' => true,
                'pass' => $returnVar === 0,
                'output' => $fullOutput
            ]);
        } catch (\Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
        exit;
    }
}
