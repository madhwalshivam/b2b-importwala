<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Services\ImageMirrorWorker;

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
}
