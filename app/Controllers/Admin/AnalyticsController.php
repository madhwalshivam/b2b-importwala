<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Core\Database;

class AnalyticsController extends Controller {

    public function index(): string {
        if (!Auth::hasPermission('dashboard.view')) {
            $this->redirect(url('admin/dashboard'));
        }

        $db = Database::getInstance();

        // 1. Live Users Online (active in last 5 minutes)
        try {
            $liveStmt = $db->query("SELECT COUNT(*) FROM live_sessions WHERE last_active >= NOW() - INTERVAL 5 MINUTE");
            $liveOnlineCount = (int)($liveStmt ? $liveStmt->fetchColumn() : 0);

            $liveUsersListStmt = $db->query("
                SELECT ls.*, u.name as user_name, u.email as user_email
                FROM live_sessions ls
                LEFT JOIN users u ON ls.user_id = u.id
                WHERE ls.last_active >= NOW() - INTERVAL 5 MINUTE
                ORDER BY ls.last_active DESC
                LIMIT 15
            ");
            $liveUsersList = $liveUsersListStmt ? $liveUsersListStmt->fetchAll() : [];
        } catch (\Throwable $e) {
            $liveOnlineCount = 0;
            $liveUsersList = [];
        }

        // 2. Date Range Filter for Orders & Revenue
        $preset = $this->request->input('range', 'this_month');
        $startDate = $this->request->input('start_date');
        $endDate = $this->request->input('end_date');

        $whereConds = ["1=1"];
        $params = [];

        switch ($preset) {
            case 'today':
                $whereConds[] = "DATE(created_at) = CURDATE()";
                break;
            case 'this_week':
                $whereConds[] = "YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
                break;
            case 'this_month':
                $whereConds[] = "MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())";
                break;
            case 'custom':
                if (!empty($startDate)) {
                    $whereConds[] = "DATE(created_at) >= ?";
                    $params[] = $startDate;
                }
                if (!empty($endDate)) {
                    $whereConds[] = "DATE(created_at) <= ?";
                    $params[] = $endDate;
                }
                break;
        }

        $whereSql = implode(' AND ', $whereConds);

        // Total orders & total revenue
        try {
            $metricsStmt = $db->prepare("SELECT COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as total_revenue FROM orders WHERE {$whereSql}");
            $metricsStmt->execute($params);
            $metrics = $metricsStmt->fetch() ?: ['total_orders' => 0, 'total_revenue' => 0];
        } catch (\Throwable $e) {
            $metrics = ['total_orders' => 0, 'total_revenue' => 0];
        }

        // Orders over time for Chart.js
        try {
            $chartStmt = $db->prepare("
                SELECT DATE(created_at) as order_date, COUNT(*) as count, COALESCE(SUM(total_amount), 0) as revenue
                FROM orders
                WHERE {$whereSql}
                GROUP BY DATE(created_at)
                ORDER BY order_date ASC
                LIMIT 30
            ");
            $chartStmt->execute($params);
            $chartData = $chartStmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            $chartData = [];
        }

        // 3. Per-user purchase history table
        $userSearch = trim($this->request->input('user_search', ''));
        $userWhere = "1=1";
        $userParams = [];
        if (!empty($userSearch)) {
            $userWhere = "(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $userParams = ["%{$userSearch}%", "%{$userSearch}%", "%{$userSearch}%"];
        }

        try {
            $purchasesStmt = $db->prepare("
                SELECT 
                    u.id, 
                    TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) as name, 
                    u.email, 
                    u.phone, 
                    COUNT(o.id) as order_count, 
                    COALESCE(SUM(o.total_amount), 0) as total_spent, 
                    MAX(o.created_at) as last_order_date
                FROM users u
                LEFT JOIN orders o ON o.customer_email = u.email OR o.customer_phone = u.phone
                WHERE {$userWhere}
                GROUP BY u.id, u.first_name, u.last_name, u.email, u.phone
                ORDER BY total_spent DESC
                LIMIT 25
            ");
            $purchasesStmt->execute($userParams);
            $userPurchases = $purchasesStmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            $userPurchases = [];
        }

        // 4. Cart Abandonment Tracking
        try {
            $abandonedStmt = $db->query("
                SELECT ci.*, p.name as product_name, p.price, p.main_image, 
                       TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) as user_name, 
                       u.email as user_email, u.phone as user_phone
                FROM cart_items ci
                JOIN products p ON ci.product_id = p.id
                LEFT JOIN users u ON ci.user_id = u.id
                ORDER BY ci.updated_at DESC
                LIMIT 20
            ");
            $abandonedCarts = $abandonedStmt ? $abandonedStmt->fetchAll() : [];
        } catch (\Throwable $e) {
            $abandonedCarts = [];
        }

        // 5. Wishlist Activity Report
        try {
            $wishlistTrendsStmt = $db->query("
                SELECT p.id, p.name, p.main_image, p.price, COUNT(w.id) as wishlist_count
                FROM wishlist w
                JOIN products p ON w.product_id = p.id
                GROUP BY p.id, p.name, p.main_image, p.price
                ORDER BY wishlist_count DESC
                LIMIT 10
            ");
            $wishlistTrends = $wishlistTrendsStmt ? $wishlistTrendsStmt->fetchAll() : [];
        } catch (\Throwable $e) {
            $wishlistTrends = [];
        }

        try {
            $userWishlistsStmt = $db->query("
                SELECT TRIM(CONCAT(COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) as user_name, 
                       u.email as user_email, u.phone as user_phone, p.name as product_name, w.created_at
                FROM wishlist w
                JOIN users u ON w.user_id = u.id
                JOIN products p ON w.product_id = p.id
                ORDER BY w.created_at DESC
                LIMIT 15
            ");
            $userWishlists = $userWishlistsStmt ? $userWishlistsStmt->fetchAll() : [];
        } catch (\Throwable $e) {
            $userWishlists = [];
        }

        return $this->render('admin/analytics', [
            'liveOnlineCount' => $liveOnlineCount,
            'liveUsersList' => $liveUsersList,
            'preset' => $preset,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'metrics' => $metrics,
            'chartData' => $chartData,
            'userPurchases' => $userPurchases,
            'userSearch' => $userSearch,
            'abandonedCarts' => $abandonedCarts,
            'wishlistTrends' => $wishlistTrends,
            'userWishlists' => $userWishlists
        ]);
    }

    // Detail modal/drill-down endpoint for specific user's orders
    public function userOrders(int $userId): void {
        header('Content-Type: application/json');
        $db = Database::getInstance();
        $uStmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $uStmt->execute([$userId]);
        $user = $uStmt->fetch();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            exit;
        }

        $oStmt = $db->prepare("SELECT * FROM orders WHERE customer_email = ? OR customer_phone = ? ORDER BY id DESC");
        $oStmt->execute([$user['email'], $user['phone']]);
        $orders = $oStmt->fetchAll();

        echo json_encode([
            'success' => true,
            'user' => $user,
            'orders' => $orders
        ]);
        exit;
    }
}
