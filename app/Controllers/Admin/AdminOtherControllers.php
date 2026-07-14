<?php
// ════════════════════════════════════════════════════
// AdminSettingsController
// ════════════════════════════════════════════════════
class AdminSettingsController extends Controller
{
    public function __construct() { AdminAuthMiddleware::handle(); }

    public function index(): void
    {
        $settings = SettingModel::all();
        $this->view('admin.settings.index', compact('settings'));
    }

    public function update(): void
    {
        if (!verifyCsrf()) { flashError('خطأ في التحقق'); $this->redirect($this->adminUrl('settings')); }

        $fields = ['store_name','store_email','store_phone','store_address','store_description',
                   'social_facebook','social_instagram','social_twitter','social_youtube','social_tiktok',
                   'fawateerk_api_key','fawateerk_active','cod_active','cod_label',
                   'meta_title','meta_description','google_analytics','facebook_pixel',
                   'maintenance_mode','min_order_amount','hero_title','hero_subtitle','hero_btn_text','hero_btn_url'];

        $data = [];
        foreach ($fields as $f) {
            $data[$f] = $_POST[$f] ?? '0';
        }

        // Logo upload
        if (!empty($_FILES['store_logo']['name'])) {
            $path = uploadFile($_FILES['store_logo'], 'settings');
            if ($path) $data['store_logo'] = $path;
        }

        // Hero image upload
        if (!empty($_FILES['hero_image']['name'])) {
            $path = uploadFile($_FILES['hero_image'], 'settings');
            if ($path) $data['hero_image'] = $path;
        }

        SettingModel::setMany($data);
        flashSuccess('تم حفظ الإعدادات بنجاح');
        $this->redirect($this->adminUrl('settings'));
    }

    public function shipping(): void
    {
        $zones = Database::fetchAll("SELECT * FROM `shipping_zones` ORDER BY `id` ASC");
        $this->view('admin.settings.shipping', compact('zones'));
    }

    public function updateShipping(): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect($this->adminUrl('settings/shipping')); }

        $name      = trim($this->post('name', ''));
        $price     = (float)$this->post('price', 0);
        $freeAbove = $this->post('free_above') ? (float)$this->post('free_above') : null;
        $govs      = array_filter(array_map('trim', explode(',', $this->post('governorates', ''))));

        Database::insert(
            "INSERT INTO `shipping_zones` (`name`,`price`,`free_above`,`governorates`) VALUES (?,?,?,?)",
            [$name, $price, $freeAbove, json_encode($govs, JSON_UNESCAPED_UNICODE)]
        );

        flashSuccess('تم إضافة منطقة شحن');
        $this->redirect($this->adminUrl('settings/shipping'));
    }

    public function deleteShipping(string $id): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect($this->adminUrl('settings/shipping')); }
        Database::execute("DELETE FROM `shipping_zones` WHERE `id` = ?", [(int)$id]);
        flashSuccess('تم حذف منطقة الشحن');
        $this->redirect($this->adminUrl('settings/shipping'));
    }
}

// ════════════════════════════════════════════════════
// AdminCollectionController
// ════════════════════════════════════════════════════
class AdminCollectionController extends Controller
{
    private CollectionModel $model;
    public function __construct() { AdminAuthMiddleware::handle(); $this->model = new CollectionModel(); }

    public function index(): void
    {
        $collections = $this->model->withProductCount();
        $this->view('admin.collections.index', compact('collections'));
    }

    public function create(): void
    {
        $this->view('admin.collections.form');
    }

    public function store(): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect($this->adminUrl('collections')); }
        $data = $this->buildData();
        if (!empty($_FILES['image']['name'])) {
            $path = uploadFile($_FILES['image'], 'categories');
            if ($path) $data['image'] = $path;
        }
        if (empty($data['slug'])) $data['slug'] = slug($data['name']) . '-' . time();
        $this->model->create($data);
        flashSuccess('تم إضافة التصنيف');
        $this->redirect($this->adminUrl('collections'));
    }

    public function edit(string $id): void
    {
        $collection = $this->model->find((int)$id);
        if (!$collection) { flashError('التصنيف غير موجود'); $this->redirect($this->adminUrl('collections')); }
        $this->view('admin.collections.form', compact('collection'));
    }

    public function update(string $id): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect($this->adminUrl('collections')); }
        $data = $this->buildData();
        $col  = $this->model->find((int)$id);
        if (!empty($_FILES['image']['name'])) {
            $path = uploadFile($_FILES['image'], 'categories');
            if ($path) { if ($col['image']) deleteFile($col['image']); $data['image'] = $path; }
        }
        if (empty($data['slug'])) $data['slug'] = $col['slug'];
        $this->model->update((int)$id, $data);
        flashSuccess('تم تحديث التصنيف');
        $this->redirect($this->adminUrl('collections'));
    }

    public function delete(string $id): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect($this->adminUrl('collections')); }
        $col = $this->model->find((int)$id);
        if ($col) { if ($col['image']) deleteFile($col['image']); $this->model->delete((int)$id); flashSuccess('تم الحذف'); }
        $this->redirect($this->adminUrl('collections'));
    }

    private function buildData(): array
    {
        return [
            'name'        => trim($this->post('name', '')),
            'slug'        => trim($this->post('slug', '')),
            'description' => $this->post('description', ''),
            'is_active'   => $this->post('is_active') ? 1 : 0,
            'sort_order'  => (int)$this->post('sort_order', 0),
            'meta_title'  => trim($this->post('meta_title', '')),
            'meta_desc'   => trim($this->post('meta_desc', '')),
        ];
    }
}

// ════════════════════════════════════════════════════
// AdminBrandController
// ════════════════════════════════════════════════════
class AdminBrandController extends Controller
{
    private BrandModel $model;
    public function __construct() { AdminAuthMiddleware::handle(); $this->model = new BrandModel(); }

    public function index(): void
    {
        $brands = $this->model->all('sort_order', 'ASC');
        $this->view('admin.collections.brands', compact('brands'));
    }

    public function store(): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect($this->adminUrl('brands')); }
        $data = ['name' => trim($this->post('name','')), 'slug' => slug(trim($this->post('name',''))).'_'.time(), 'is_active' => 1, 'sort_order' => (int)$this->post('sort_order',0)];
        if (!empty($_FILES['logo']['name'])) { $path = uploadFile($_FILES['logo'], 'brands'); if ($path) $data['logo'] = $path; }
        $this->model->create($data);
        flashSuccess('تم إضافة الماركة');
        $this->redirect($this->adminUrl('brands'));
    }

    public function update(string $id): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect($this->adminUrl('brands')); }
        $data = ['name' => trim($this->post('name','')), 'is_active' => $this->post('is_active') ? 1 : 0, 'sort_order' => (int)$this->post('sort_order',0)];
        if (!empty($_FILES['logo']['name'])) { $path = uploadFile($_FILES['logo'], 'brands'); if ($path) $data['logo'] = $path; }
        $this->model->update((int)$id, $data);
        flashSuccess('تم التحديث');
        $this->redirect($this->adminUrl('brands'));
    }

    public function delete(string $id): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect($this->adminUrl('brands')); }
        $this->model->delete((int)$id);
        flashSuccess('تم الحذف');
        $this->redirect($this->adminUrl('brands'));
    }
}

// ════════════════════════════════════════════════════
// AdminCustomerController
// ════════════════════════════════════════════════════
class AdminCustomerController extends Controller
{
    private CustomerModel $model;
    public function __construct() { AdminAuthMiddleware::handle(); $this->model = new CustomerModel(); }

    public function index(): void
    {
        $page      = (int)$this->get('page', 1);
        $search    = trim($this->get('search', ''));
        $paginator = $this->model->adminList($page, $search);
        $this->view('admin.customers.index', compact('paginator', 'search'));
    }

    public function show(string $id): void
    {
        $customer = $this->model->find((int)$id);
        if (!$customer) { flashError('العميل غير موجود'); $this->redirect($this->adminUrl('customers')); }
        $orders = Database::fetchAll("SELECT * FROM `orders` WHERE customer_id = ? ORDER BY created_at DESC", [(int)$id]);
        $this->view('admin.customers.show', compact('customer', 'orders'));
    }

    public function toggle(string $id): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect($this->adminUrl('customers')); }
        $c = $this->model->find((int)$id);
        if ($c) { $this->model->update((int)$id, ['is_active' => $c['is_active'] ? 0 : 1]); flashSuccess('تم تغيير حالة العميل'); }
        $this->redirect($this->adminUrl('customers'));
    }
}

// ════════════════════════════════════════════════════
// AdminDiscountController
// ════════════════════════════════════════════════════
class AdminDiscountController extends Controller
{
    private DiscountModel $model;
    public function __construct() { AdminAuthMiddleware::handle(); $this->model = new DiscountModel(); }

    public function index(): void
    {
        $page      = (int)$this->get('page', 1);
        $paginator = $this->model->adminList($page);
        $this->view('admin.discounts.index', compact('paginator'));
    }

    public function store(): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect($this->adminUrl('discounts')); }
        $data = [
            'code'       => strtoupper(trim($this->post('code', ''))),
            'type'       => $this->post('type', 'percentage'),
            'value'      => (float)$this->post('value', 0),
            'min_order'  => $this->post('min_order') ? (float)$this->post('min_order') : null,
            'max_uses'   => $this->post('max_uses') ? (int)$this->post('max_uses') : null,
            'starts_at'  => $this->post('starts_at') ?: null,
            'expires_at' => $this->post('expires_at') ?: null,
            'is_active'  => 1,
        ];
        try { $this->model->create($data); flashSuccess('تم إضافة كود الخصم'); }
        catch (\Exception $e) { flashError('الكود مستخدم مسبقاً'); }
        $this->redirect($this->adminUrl('discounts'));
    }

    public function toggle(string $id): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect($this->adminUrl('discounts')); }
        $d = $this->model->find((int)$id);
        if ($d) { $this->model->update((int)$id, ['is_active' => $d['is_active'] ? 0 : 1]); }
        flashSuccess('تم تغيير الحالة');
        $this->redirect($this->adminUrl('discounts'));
    }

    public function delete(string $id): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect($this->adminUrl('discounts')); }
        $this->model->delete((int)$id);
        flashSuccess('تم الحذف');
        $this->redirect($this->adminUrl('discounts'));
    }
}

// ════════════════════════════════════════════════════
// AdminAnalyticsController
// ════════════════════════════════════════════════════
class AdminAnalyticsController extends Controller
{
    public function __construct() { AdminAuthMiddleware::handle(); }

    public function index(): void
    {
        $orderModel = new OrderModel();
        $stats      = $orderModel->stats();
        $salesChart = $orderModel->salesChart(30);

        $topProducts = Database::fetchAll(
            "SELECT p.name, p.thumbnail, SUM(oi.qty) as sold, SUM(oi.total) as revenue
             FROM `order_items` oi JOIN `products` p ON p.id = oi.product_id
             GROUP BY oi.product_id ORDER BY revenue DESC LIMIT 10"
        );
        $topGovs = Database::fetchAll(
            "SELECT shipping_gov, COUNT(*) as orders, SUM(total) as revenue
             FROM `orders` WHERE payment_status='paid'
             GROUP BY shipping_gov ORDER BY orders DESC LIMIT 10"
        );
        $paymentMix = Database::fetchAll(
            "SELECT payment_method, COUNT(*) as cnt FROM `orders` GROUP BY payment_method"
        );

        $this->view('admin.analytics.index', compact('stats', 'salesChart', 'topProducts', 'topGovs', 'paymentMix'));
    }

    public function export(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=orders-' . date('Y-m-d') . '.csv');
        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
        fputcsv($out, ['رقم الطلب','العميل','الهاتف','المدينة','المحافظة','الإجمالي','الدفع','الحالة','التاريخ']);
        $orders = Database::fetchAll("SELECT * FROM `orders` ORDER BY created_at DESC");
        foreach ($orders as $o) {
            fputcsv($out, [$o['order_number'],$o['customer_name'],$o['customer_phone'],$o['shipping_city'],$o['shipping_gov'],$o['total'],$o['payment_method'],$o['status'],$o['created_at']]);
        }
        fclose($out);
    }
}

// ════════════════════════════════════════════════════
// AdminReviewController
// ════════════════════════════════════════════════════
class AdminReviewController extends Controller
{
    public function __construct() { AdminAuthMiddleware::handle(); }

    public function index(): void
    {
        $reviews = Database::fetchAll("SELECT r.*, p.name as product_name FROM `reviews` r LEFT JOIN `products` p ON p.id = r.product_id ORDER BY r.created_at DESC");
        $this->view('admin.settings.reviews', compact('reviews'));
    }

    public function approve(string $id): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect($this->adminUrl('reviews')); }
        Database::execute("UPDATE `reviews` SET `is_approved` = 1 WHERE `id` = ?", [(int)$id]);
        flashSuccess('تم الموافقة على التقييم');
        $this->redirect($this->adminUrl('reviews'));
    }

    public function delete(string $id): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect($this->adminUrl('reviews')); }
        Database::execute("DELETE FROM `reviews` WHERE `id` = ?", [(int)$id]);
        flashSuccess('تم الحذف');
        $this->redirect($this->adminUrl('reviews'));
    }
}
