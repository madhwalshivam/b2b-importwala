<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Page;

class PageController extends Controller {
    public function show(string $slug): string {
        $db = \App\Core\Database::getInstance();
        $stmt = $db->prepare("SELECT * FROM cms_pages WHERE slug = ?");
        $stmt->execute([$slug]);
        $page = $stmt->fetch();

        if (!$page) {
            $this->response->setStatusCode(404);
            return $this->render('errors/404');
        }

        return $this->render('storefront/page', [
            'page' => $page
        ]);
    }
}
