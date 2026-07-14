<?php
class AdminOrderController extends Controller
{
    private OrderModel $order;

    public function __construct()
    {
        AdminAuthMiddleware::handle();
        $this->order = new OrderModel();
    }

    public function index(): void
    {
        $page    = (int)$this->get('page', 1);
        $search  = trim($this->get('search', ''));
        $status  = $this->get('status', '');
        $payment = $this->get('payment', '');

        $paginator = $this->order->adminList($page, $search, $status, $payment);

        $this->view('admin.orders.index', compact('paginator', 'search', 'status', 'payment'));
    }

    public function show(string $id): void
    {
        $order = $this->order->getWithItems((int)$id);
        if (!$order) { flashError('الطلب غير موجود'); $this->redirect($this->adminUrl('orders')); }

        $this->view('admin.orders.show', compact('order'));
    }

    public function updateStatus(string $id): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect($this->adminUrl('orders')); }

        $status  = $this->post('status', '');
        $payment = $this->post('payment_status', '');

        $data = [];
        if ($status)  $data['status'] = $status;
        if ($payment) $data['payment_status'] = $payment;
        if ($status === 'shipped')   $data['shipped_at']   = date('Y-m-d H:i:s');
        if ($status === 'delivered') $data['delivered_at'] = date('Y-m-d H:i:s');

        if ($data) {
            $data['updated_at'] = date('Y-m-d H:i:s');
            $sets   = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($data)));
            $params = array_values($data);
            $params[] = (int)$id;
            Database::execute("UPDATE `orders` SET {$sets} WHERE `id` = ?", $params);
        }

        flashSuccess('تم تحديث حالة الطلب');
        $this->redirect($this->adminUrl('orders/' . $id));
    }

    public function updateNotes(string $id): void
    {
        if (!verifyCsrf()) { jsonResponse(false, 'خطأ'); }
        Database::execute(
            "UPDATE `orders` SET `admin_notes` = ? WHERE `id` = ?",
            [$this->post('admin_notes', ''), (int)$id]
        );
        jsonResponse(true, 'تم الحفظ');
    }
}
