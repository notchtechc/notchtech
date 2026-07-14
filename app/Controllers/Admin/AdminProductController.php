<?php
class AdminProductController extends Controller
{
    private ProductModel    $product;
    private CollectionModel $collection;
    private BrandModel      $brand;

    public function __construct()
    {
        AdminAuthMiddleware::handle();
        $this->product    = new ProductModel();
        $this->collection = new CollectionModel();
        $this->brand      = new BrandModel();
    }

    public function index(): void
    {
        $page    = (int)$this->get('page', 1);
        $search  = trim($this->get('search', ''));
        $status  = $this->get('status', '');

        $paginator   = $this->product->adminList($page, $search, $status);
        $collections = $this->collection->getActive();
        $brands      = $this->brand->getActive();

        $this->view('admin.products.index', compact('paginator', 'search', 'status', 'collections', 'brands'));
    }

    public function create(): void
    {
        $collections = $this->collection->getActive();
        $brands      = $this->brand->getActive();
        $this->view('admin.products.form', compact('collections', 'brands'));
    }

    public function store(): void
    {
        if (!verifyCsrf()) { flashError('خطأ في التحقق'); $this->redirect($this->adminUrl('products')); }

        $data = $this->buildProductData();

        // Handle thumbnail
        if (!empty($_FILES['thumbnail']['name'])) {
            $path = uploadFile($_FILES['thumbnail'], 'products');
            if ($path) $data['thumbnail'] = $path;
        }

        // Generate slug if empty
        if (empty($data['slug'])) {
            $data['slug'] = slug($data['name']) . '-' . time();
        }

        $id = $this->product->create($data);

        // Upload extra images
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $i => $name) {
                if ($_FILES['images']['error'][$i] === 0) {
                    $file = [
                        'name'     => $name,
                        'type'     => $_FILES['images']['type'][$i],
                        'tmp_name' => $_FILES['images']['tmp_name'][$i],
                        'error'    => $_FILES['images']['error'][$i],
                        'size'     => $_FILES['images']['size'][$i],
                    ];
                    $path = uploadFile($file, 'products');
                    if ($path) $this->product->addImage($id, $path);
                }
            }
        }

        flashSuccess('تم إضافة المنتج بنجاح');
        $this->redirect($this->adminUrl('products'));
    }

    public function edit(string $id): void
    {
        $product = $this->product->find((int)$id);
        if (!$product) { flashError('المنتج غير موجود'); $this->redirect($this->adminUrl('products')); }

        $product['images']   = $this->product->getImages((int)$id);
        $product['variants'] = $this->product->getVariants((int)$id);
        $collections         = $this->collection->getActive();
        $brands              = $this->brand->getActive();

        $this->view('admin.products.form', compact('product', 'collections', 'brands'));
    }

    public function update(string $id): void
    {
        if (!verifyCsrf()) { flashError('خطأ في التحقق'); $this->redirect($this->adminUrl('products')); }

        $product = $this->product->find((int)$id);
        if (!$product) { flashError('المنتج غير موجود'); $this->redirect($this->adminUrl('products')); }

        $data = $this->buildProductData();

        if (!empty($_FILES['thumbnail']['name'])) {
            $path = uploadFile($_FILES['thumbnail'], 'products');
            if ($path) {
                if ($product['thumbnail']) deleteFile($product['thumbnail']);
                $data['thumbnail'] = $path;
            }
        }

        if (empty($data['slug'])) $data['slug'] = $product['slug'];

        $this->product->update((int)$id, $data);

        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $i => $name) {
                if ($_FILES['images']['error'][$i] === 0) {
                    $file = [
                        'name'     => $name,
                        'type'     => $_FILES['images']['type'][$i],
                        'tmp_name' => $_FILES['images']['tmp_name'][$i],
                        'error'    => $_FILES['images']['error'][$i],
                        'size'     => $_FILES['images']['size'][$i],
                    ];
                    $path = uploadFile($file, 'products');
                    if ($path) $this->product->addImage((int)$id, $path);
                }
            }
        }

        flashSuccess('تم تحديث المنتج');
        $this->redirect($this->adminUrl('products/' . $id . '/edit'));
    }

    public function delete(string $id): void
    {
        if (!verifyCsrf()) { flashError('خطأ في التحقق'); $this->redirect($this->adminUrl('products')); }

        $product = $this->product->find((int)$id);
        if ($product) {
            if ($product['thumbnail']) deleteFile($product['thumbnail']);
            foreach ($this->product->getImages((int)$id) as $img) deleteFile($img['image']);
            $this->product->delete((int)$id);
            flashSuccess('تم حذف المنتج');
        }
        $this->redirect($this->adminUrl('products'));
    }

    public function deleteImage(string $id): void
    {
        if (!verifyCsrf()) { jsonResponse(false, 'خطأ'); }

        $imageId = (int)$this->post('image_id', 0);
        $img     = $this->product->deleteImage($imageId);
        if ($img) deleteFile($img['image']);

        jsonResponse(true, 'تم الحذف');
    }

    private function buildProductData(): array
    {
        return [
            'name'            => trim($this->post('name', '')),
            'slug'            => trim($this->post('slug', '')),
            'description'     => $this->post('description', ''),
            'short_desc'      => trim($this->post('short_desc', '')),
            'sku'             => trim($this->post('sku', '')) ?: null,
            'collection_id'   => $this->post('collection_id') ?: null,
            'brand_id'        => $this->post('brand_id') ?: null,
            'price'           => (float)$this->post('price', 0),
            'compare_price'   => $this->post('compare_price') ? (float)$this->post('compare_price') : null,
            'cost_price'      => $this->post('cost_price') ? (float)$this->post('cost_price') : null,
            'stock'           => (int)$this->post('stock', 0),
            'track_stock'     => $this->post('track_stock') ? 1 : 0,
            'allow_backorder' => $this->post('allow_backorder') ? 1 : 0,
            'weight'          => $this->post('weight') ? (float)$this->post('weight') : null,
            'status'          => $this->post('status', 'draft'),
            'is_featured'     => $this->post('is_featured') ? 1 : 0,
            'meta_title'      => trim($this->post('meta_title', '')),
            'meta_desc'       => trim($this->post('meta_desc', '')),
            'sort_order'      => (int)$this->post('sort_order', 0),
        ];
    }
}
