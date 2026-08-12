<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Auth;
use App\Models\Order;

class DashboardController extends Controller {
    public function index(): string {
        if (!Auth::hasPermission('dashboard.view')) {
            $this->redirect(url('admin/login'));
        }

        $dateRange = (string)$this->request->input('date_range', 'all');

        $orderModel = new Order();
        $stats = $orderModel->getDashboardStats($dateRange);
        $recentOrders = $orderModel->getRecentOrders(10, $dateRange);
        $revenueChart = $orderModel->getRevenueChartData($dateRange);
        $categoryChart = $orderModel->getCategoryShareChartData();

        return $this->render('admin/dashboard/index', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'revenueChart' => $revenueChart,
            'categoryChart' => $categoryChart,
            'dateRange' => $dateRange,
            'user' => Auth::user()
        ]);
    }
}
