<?php
namespace App\Models;

use App\Core\Model;

class Order extends Model {
    protected string $table = 'orders';

    public function generateOrderNumber(): string {
        return 'MUD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }

    public function getOrderItems(int $orderId): array {
        $stmt = $this->db->prepare("
            SELECT oi.*, p.main_image 
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getRecentOrders(int $limit = 10, string $dateRange = 'all'): array {
        $whereSql = $this->buildDateCondition($dateRange);
        $stmt = $this->db->prepare("SELECT * FROM orders {$whereSql} ORDER BY id DESC LIMIT {$limit}");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function getDashboardStats(string $dateRange = 'all'): array {
        $whereSql = $this->buildDateCondition($dateRange);
        $whereAnd = empty($whereSql) ? "WHERE order_status != 'cancelled'" : "{$whereSql} AND order_status != 'cancelled'";
        $wherePending = empty($whereSql) ? "WHERE order_status = 'pending'" : "{$whereSql} AND order_status = 'pending'";

        $totalSales = (float)$this->db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders {$whereAnd}")->fetchColumn();
        $totalOrders = (int)$this->db->query("SELECT COUNT(*) FROM orders {$whereSql}")->fetchColumn();
        $pendingOrders = (int)$this->db->query("SELECT COUNT(*) FROM orders {$wherePending}")->fetchColumn();
        
        $totalCustomers = (int)$this->db->query("SELECT COUNT(*) FROM customers")->fetchColumn();
        if ($totalCustomers === 0) {
            $totalCustomers = (int)$this->db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        }
        if ($totalCustomers === 0) {
            $totalCustomers = (int)$this->db->query("SELECT COUNT(DISTINCT customer_email) FROM orders")->fetchColumn();
        }

        $totalProducts = (int)$this->db->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $lowStockProducts = (int)$this->db->query("SELECT COUNT(*) FROM products WHERE stock <= low_stock_threshold")->fetchColumn();

        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'total_customers' => $totalCustomers,
            'total_products' => $totalProducts,
            'low_stock_products' => $lowStockProducts
        ];
    }

    public function getRevenueChartData(string $dateRange = 'all'): array {
        $labels = [];
        $values = [];

        if (in_array($dateRange, ['7days', '30days', 'today'], true)) {
            // Daily Grouping
            $whereSql = $this->buildDateCondition($dateRange);
            $sql = "
                SELECT DATE(created_at) as d_date, 
                       DATE_FORMAT(created_at, '%b %d') as label, 
                       COALESCE(SUM(total_amount), 0) as total 
                FROM orders 
                " . (empty($whereSql) ? "WHERE order_status != 'cancelled'" : "{$whereSql} AND order_status != 'cancelled'") . "
                GROUP BY DATE(created_at) 
                ORDER BY d_date ASC
            ";
            $rows = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($rows)) {
                // Generate empty fallback dates for clean chart rendering
                for ($i = 6; $i >= 0; $i--) {
                    $labels[] = date('b d', strtotime("-{$i} days"));
                    $values[] = 0;
                }
            } else {
                foreach ($rows as $r) {
                    $labels[] = $r['label'];
                    $values[] = (float)$r['total'];
                }
            }
        } else {
            // Monthly Grouping (Default for year / all)
            $whereSql = $this->buildDateCondition($dateRange);
            $sql = "
                SELECT DATE_FORMAT(created_at, '%Y-%m') as m_date, 
                       DATE_FORMAT(created_at, '%b') as label, 
                       COALESCE(SUM(total_amount), 0) as total 
                FROM orders 
                " . (empty($whereSql) ? "WHERE order_status != 'cancelled'" : "{$whereSql} AND order_status != 'cancelled'") . "
                GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
                ORDER BY m_date ASC
            ";
            $rows = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

            if (empty($rows)) {
                $labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $values = array_fill(0, 12, 0);
            } else {
                foreach ($rows as $r) {
                    $labels[] = $r['label'];
                    $values[] = (float)$r['total'];
                }
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function getCategoryShareChartData(): array {
        $sql = "
            SELECT c.name as category_name, 
                   COALESCE(SUM(oi.quantity), 0) as total_qty,
                   COALESCE(SUM(oi.total_amount), 0) as total_sales
            FROM categories c
            LEFT JOIN products p ON c.id = p.category_id
            LEFT JOIN order_items oi ON p.id = oi.product_id
            LEFT JOIN orders o ON oi.order_id = o.id AND o.order_status != 'cancelled'
            GROUP BY c.id
            ORDER BY total_sales DESC, total_qty DESC
        ";
        $rows = $this->db->query($sql)->fetchAll(\PDO::FETCH_ASSOC);

        $labels = [];
        $values = [];

        foreach ($rows as $r) {
            $labels[] = $r['category_name'];
            $values[] = (float)$r['total_sales'] > 0 ? (float)$r['total_sales'] : (int)$r['total_qty'];
        }

        if (empty($labels) || array_sum($values) == 0) {
            // Fallback product counts per category if no orders yet
            $catCountSql = "
                SELECT c.name, COUNT(p.id) as p_count 
                FROM categories c 
                LEFT JOIN products p ON c.id = p.category_id 
                GROUP BY c.id 
                ORDER BY p_count DESC
            ";
            $catRows = $this->db->query($catCountSql)->fetchAll(\PDO::FETCH_ASSOC);
            $labels = [];
            $values = [];
            foreach ($catRows as $cr) {
                $labels[] = $cr['name'];
                $values[] = (int)$cr['p_count'];
            }
        }

        return ['labels' => $labels, 'values' => $values];
    }

    protected function buildDateCondition(string $dateRange): string {
        switch ($dateRange) {
            case 'today':
                return "WHERE created_at >= CURDATE()";
            case '7days':
                return "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case '30days':
                return "WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            case 'this_month':
                return "WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')";
            case 'this_year':
                return "WHERE created_at >= DATE_FORMAT(NOW(), '%Y-01-01')";
            case 'all':
            default:
                return "";
        }
    }
}
