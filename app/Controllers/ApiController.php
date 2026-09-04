<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class ApiController extends Controller
{
    public function searchAutocomplete(): void
    {
        $q = trim($this->request->input('q', ''));
        if (strlen($q) < 2) {
            $this->json(['products' => [], 'brands' => [], 'models' => [], 'categories' => []]);
        }

        $db = Database::getInstance();

        $words = array_filter(explode(' ', preg_replace('/\s+/', ' ', $q)));
        $whereWords = [];
        $paramsP = [];
        foreach ($words as $idx => $w) {
            $whereWords[] = "(name LIKE ? OR tags LIKE ? OR sku LIKE ?)";
            $st = '%' . $w . '%';
            $paramsP[] = $st;
            $paramsP[] = $st;
            $paramsP[] = $st;
        }
        $whereSql = !empty($whereWords) ? implode(" AND ", $whereWords) : "1=1";

        $sqlP = "SELECT id, name, slug, price, sale_price, main_image 
                 FROM products 
                 WHERE status = 'active' AND ({$whereSql}) 
                 ORDER BY (CASE 
                    WHEN name LIKE ? THEN 1 
                    WHEN name LIKE ? THEN 2 
                    ELSE 3 
                 END) ASC, id DESC LIMIT 20";

        $paramsP[] = $q . '%';
        $paramsP[] = '%' . $q . '%';

        $stmtP = $db->prepare($sqlP);
        $stmtP->execute($paramsP);
        $products = $stmtP->fetchAll();

        // Brands
        $stmtB = $db->prepare("SELECT id, name, slug, logo FROM brands WHERE name LIKE ? AND status = 'active' LIMIT 3");
        $stmtB->execute([$term]);
        $brands = $stmtB->fetchAll();

        // Scooter Models
        $stmtM = $db->prepare("SELECT sm.id, sm.name, sm.slug, b.name as brand_name FROM scooter_models sm JOIN brands b ON sm.brand_id = b.id WHERE sm.name LIKE ? AND sm.status = 'active' LIMIT 5");
        $stmtM->execute([$term]);
        $models = $stmtM->fetchAll();

        // Categories
        $stmtC = $db->prepare("SELECT id, name, slug FROM categories WHERE name LIKE ? AND status = 'active' LIMIT 3");
        $stmtC->execute([$term]);
        $categories = $stmtC->fetchAll();

        $this->json([
            'products' => $products,
            'brands' => $brands,
            'models' => $models,
            'categories' => $categories
        ]);
    }

    public function getModelsByBrand(): void
    {
        $brandId = (int) $this->request->input('brand_id');
        $db = Database::getInstance();
        $stmt = $db->prepare("SELECT id, name, slug FROM scooter_models WHERE brand_id = ? AND status = 'active' ORDER BY sort_order ASC, name ASC");
        $stmt->execute([$brandId]);
        $models = $stmt->fetchAll();

        $this->json(['models' => $models]);
    }
}
