<?php
class OrderModel extends Model
{
    protected string $table = 'orders';
    protected array $fillable = [
        'order_number','customer_id','customer_name','customer_email','customer_phone',
        'shipping_address','shipping_city','shipping_gov','shipping_price',
        'subtotal','discount_code','discount_amount','total',
        'payment_method','payment_status','payment_ref',
        'status','notes','admin_notes'
    ];

    public function generateOrderNumber(): string
    {
        do {
            $num = 'NT-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
        } while ($this->findBy('order_number', $num));
        return $num;
    }

    public function createWithItems(array $order, array $items): int
    {
        Database::beginTransaction();
        try {
            $orderId = $this->create($order);

            foreach ($items as $item) {
                $item['order_id'] = $orderId;
                $item['total']    = $item['price'] * $item['qty'];
                Database::insert(
                    "INSERT INTO `order_items` (`order_id`,`product_id`,`variant_id`,`name`,`variant`,`sku`,`price`,`qty`,`total`,`image`)
                     VALUES (?,?,?,?,?,?,?,?,?,?)",
                    [
                        $orderId,
                        $item['product_id'] ?? null,
                        $item['variant_id'] ?? null,
                        $item['name'],
                        $item['variant'] ?? null,
                        $item['sku'] ?? null,
                        $item['price'],
                        $item['qty'],
                        $item['total'],
                        $item['image'] ?? null,
                    ]
                );

                // Decrement stock
                if (!empty($item['product_id'])) {
                    (new ProductModel())->decrementStock(
                        $item['product_id'],
                        $item['qty'],
                        $item['variant_id'] ?? null
                    );
                }
            }

            Database::commit();
            return $orderId;
        } catch (\Throwable $e) {
            Database::rollback();
            throw $e;
        }
    }

    public function getWithItems(int $id): ?array
    {
        $order = $this->find($id);
        if (!$order) return null;
        $order['items'] = Database::fetchAll(
            "SELECT * FROM `order_items` WHERE `order_id` = ?",
            [$id]
        );
        return $order;
    }

    public function getByNumber(string $number): ?array
    {
        $order = $this->findBy('order_number', $number);
        if (!$order) return null;
        $order['items'] = Database::fetchAll(
            "SELECT * FROM `order_items` WHERE `order_id` = ?",
            [$order['id']]
        );
        return $order;
    }

    public function adminList(int $page = 1, string $search = '', string $status = '', string $payment = ''): array
    {
        $where  = [];
        $params = [];

        if ($search) {
            $where[]  = "(o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_email LIKE ?)";
            $params   = array_merge($params, ["%{$search}%", "%{$search}%", "%{$search}%"]);
        }
        if ($status) {
            $where[]  = "o.status = ?";
            $params[] = $status;
        }
        if ($payment) {
            $where[]  = "o.payment_status = ?";
            $params[] = $payment;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset   = ($page - 1) * ITEMS_PER_PAGE;

        $total = Database::fetch("SELECT COUNT(*) as cnt FROM `orders` o {$whereStr}", $params)['cnt'];
        $data  = Database::fetchAll(
            "SELECT o.* FROM `orders` o {$whereStr} ORDER BY o.created_at DESC LIMIT " . ITEMS_PER_PAGE . " OFFSET {$offset}",
            $params
        );

        return [
            'data'         => $data,
            'total'        => (int)$total,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / ITEMS_PER_PAGE),
        ];
    }

    public function stats(): array
    {
        $today = date('Y-m-d');
        $month = date('Y-m');

        return [
            'total_orders'    => (int)Database::fetch("SELECT COUNT(*) as c FROM `orders`")['c'],
            'today_orders'    => (int)Database::fetch("SELECT COUNT(*) as c FROM `orders` WHERE DATE(created_at) = ?", [$today])['c'],
            'month_orders'    => (int)Database::fetch("SELECT COUNT(*) as c FROM `orders` WHERE DATE_FORMAT(created_at,'%Y-%m') = ?", [$month])['c'],
            'total_revenue'   => (float)(Database::fetch("SELECT COALESCE(SUM(total),0) as s FROM `orders` WHERE payment_status = 'paid'")['s']),
            'today_revenue'   => (float)(Database::fetch("SELECT COALESCE(SUM(total),0) as s FROM `orders` WHERE payment_status = 'paid' AND DATE(created_at) = ?", [$today])['s']),
            'month_revenue'   => (float)(Database::fetch("SELECT COALESCE(SUM(total),0) as s FROM `orders` WHERE payment_status = 'paid' AND DATE_FORMAT(created_at,'%Y-%m') = ?", [$month])['s']),
            'pending_orders'  => (int)Database::fetch("SELECT COUNT(*) as c FROM `orders` WHERE status = 'pending'")['c'],
        ];
    }

    public function recentOrders(int $limit = 10): array
    {
        return Database::fetchAll(
            "SELECT * FROM `orders` ORDER BY created_at DESC LIMIT ?",
            [$limit]
        );
    }

    public function salesChart(int $days = 30): array
    {
        return Database::fetchAll(
            "SELECT DATE(created_at) as date, COUNT(*) as orders, COALESCE(SUM(total),0) as revenue
             FROM `orders`
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) AND payment_status = 'paid'
             GROUP BY DATE(created_at)
             ORDER BY date ASC",
            [$days]
        );
    }
}
