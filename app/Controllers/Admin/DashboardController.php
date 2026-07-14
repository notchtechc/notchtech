<?php
class DashboardController extends Controller
{
    public function index(): void
    {
        AdminAuthMiddleware::handle();

        $orderModel   = new OrderModel();
        $productModel = new ProductModel();
        $customerModel = new CustomerModel();

        $stats = $orderModel->stats();
        $stats['total_products']  = $productModel->count();
        $stats['total_customers'] = $customerModel->count();
        $stats['low_stock']       = Database::fetchAll(
            "SELECT * FROM `products` WHERE stock <= 5 AND track_stock = 1 AND status = 'active' ORDER BY stock ASC LIMIT 5"
        );

        $recentOrders = $orderModel->recentOrders(10);
        $salesChart   = $orderModel->salesChart(30);
        $topProducts  = Database::fetchAll(
            "SELECT p.name, p.thumbnail, SUM(oi.qty) as sold, SUM(oi.total) as revenue
             FROM `order_items` oi
             JOIN `products` p ON p.id = oi.product_id
             GROUP BY oi.product_id ORDER BY sold DESC LIMIT 5"
        );

        $this->view('admin.dashboard.index', compact('stats', 'recentOrders', 'salesChart', 'topProducts'));
    }
}
