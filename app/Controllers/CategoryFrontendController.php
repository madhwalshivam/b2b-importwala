<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Category;

class CategoryFrontendController extends Controller {
    protected Category $categoryModel;

    public function __construct() {
        parent::__construct();
        $this->categoryModel = new Category();
    }

    /**
     * All Categories Directory Page (/categories)
     */
    public function index(): string {
        $categories = $this->categoryModel->getActiveCategories();
        $db = Database::getInstance();

        // Attach product count for each category
        foreach ($categories as &$cat) {
            $stmt = $db->prepare("
                SELECT COUNT(DISTINCT p.id) as total 
                FROM products p
                LEFT JOIN product_categories pc ON pc.product_id = p.id
                WHERE (p.category_id = ? OR pc.category_id = ?) AND p.stock > 0
            ");
            $stmt->execute([$cat['id'], $cat['id']]);
            $countRow = $stmt->fetch();
            $cat['product_count'] = (int)($countRow['total'] ?? 0);
            
            // Attach subcategories
            $cat['subcategories'] = $this->categoryModel->getSubcategories((int)$cat['id']);
        }
        unset($cat);

        return $this->render('storefront/categories', [
            'categories' => $categories,
            'seoOptions' => [
                'title' => 'Explore All Categories | ImportWale',
                'description' => 'Browse our complete catalog of electric scooter accessories including Mobile Holders, Body Covers, Crash Guards, Seat Covers, Chargers, and Storage Solutions.'
            ]
        ]);
    }

    /**
     * Clean Category URL Handler (/category/{slug})
     */
    public function show(string $slug): string {
        $category = $this->categoryModel->findBy('slug', $slug);
        if (!$category) {
            // Check subcategories table as fallback
            $db = Database::getInstance();
            $stmt = $db->prepare("SELECT * FROM subcategories WHERE slug = ? LIMIT 1");
            $stmt->execute([$slug]);
            $sub = $stmt->fetch();
            if ($sub) {
                $_GET['category_id'] = $sub['category_id'];
            } else {
                $this->redirect(url('categories'));
                return '';
            }
        } else {
            $_GET['category'] = $slug;
        }

        return (new ShopController())->index();
    }
}
