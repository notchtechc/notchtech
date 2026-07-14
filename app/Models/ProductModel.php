<?php
class ProductModel extends Model
{
    protected string $table = 'products';
    protected array $fillable = [
        'name','slug','description','short_desc','sku','collection_id','brand_id',
        'price','compare_price','cost_price','stock','track_stock','allow_backorder',
        'weight','status','is_featured','has_variants','thumbnail',
        'meta_title','meta_desc','sort_order'
    ];

    public function getActive(int $limit = 20, int $offset = 0): array
    {
        return Database::fetchAll(
            "SELECT p.*, c.name as collection_name, b.name as brand_name
             FROM `products` p
             LEFT JOIN `collections` c ON p.collection_id = c.id
             LEFT JOIN `brands` b ON p.brand_id = b.id
             WHERE p.status = 'active'
             ORDER BY p.sort_order DESC, p.created_at DESC
             LIMIT ? OFFSET ?",
            [$limit, $offset]
        );
    }

    public function getBySlug(string $slug): ?array
    {
        $product = Database::fetch(
            "SELECT p.*, c.name as collection_name, b.name as brand_name
             FROM `products` p
             LEFT JOIN `collections` c ON p.collection_id = c.id
             LEFT JOIN `brands` b ON p.brand_id = b.id
             WHERE p.slug = ? AND p.status = 'active'",
            [$slug]
        );

        if (!$product) return null;

        $product['images'] = $this->getImages($product['id']);
        $product['variants'] = $this->getVariants($product['id']);
        $product['rating'] = $this->getRating($product['id']);

        return $product;
    }

    public function getImages(int $productId): array
    {
        return Database::fetchAll(
            "SELECT * FROM `product_images` WHERE `product_id` = ? ORDER BY `sort_order` ASC",
            [$productId]
        );
    }

    public function getVariants(int $productId): array
    {
        return Database::fetchAll(
            "SELECT * FROM `product_variants` WHERE `product_id` = ? ORDER BY `sort_order` ASC",
            [$productId]
        );
    }

    public function getRating(int $productId): array
    {
        $row = Database::fetch(
            "SELECT AVG(rating) as avg, COUNT(*) as count
             FROM `reviews`
             WHERE product_id = ? AND is_approved = 1",
            [$productId]
        );
        return [
            'avg'   => round((float)($row['avg'] ?? 0), 1),
            'count' => (int)($row['count'] ?? 0),
        ];
    }

    public function getFeatured(int $limit = 8): array
    {
        return Database::fetchAll(
            "SELECT p.*, b.name as brand_name
             FROM `products` p
             LEFT JOIN `brands` b ON p.brand_id = b.id
             WHERE p.status = 'active' AND p.is_featured = 1
             ORDER BY p.sort_order DESC LIMIT ?",
            [$limit]
        );
    }

    public function getByCollection(int $collectionId, int $limit = 20, int $offset = 0): array
    {
        return Database::fetchAll(
            "SELECT p.*, b.name as brand_name
             FROM `products` p
             LEFT JOIN `brands` b ON p.brand_id = b.id
             WHERE p.collection_id = ? AND p.status = 'active'
             ORDER BY p.sort_order DESC LIMIT ? OFFSET ?",
            [$collectionId, $limit, $offset]
        );
    }

    public function getByBrand(int $brandId, int $limit = 20, int $offset = 0): array
    {
        return Database::fetchAll(
            "SELECT p.*, c.name as collection_name
             FROM `products` p
             LEFT JOIN `collections` c ON p.collection_id = c.id
             WHERE p.brand_id = ? AND p.status = 'active'
             ORDER BY p.sort_order DESC LIMIT ? OFFSET ?",
            [$brandId, $limit, $offset]
        );
    }

    public function search(string $q, int $limit = 20): array
    {
        return Database::fetchAll(
            "SELECT p.*, b.name as brand_name
             FROM `products` p
             LEFT JOIN `brands` b ON p.brand_id = b.id
             WHERE p.status = 'active'
             AND (p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ?)
             ORDER BY p.views DESC LIMIT ?",
            ["%{$q}%", "%{$q}%", "%{$q}%", $limit]
        );
    }

    public function decrementStock(int $productId, int $qty, ?int $variantId = null): void
    {
        if ($variantId) {
            Database::execute(
                "UPDATE `product_variants` SET `stock` = `stock` - ? WHERE `id` = ?",
                [$qty, $variantId]
            );
        }
        Database::execute(
            "UPDATE `products` SET `stock` = `stock` - ? WHERE `id` = ?",
            [$qty, $productId]
        );
    }

    public function incrementViews(int $productId): void
    {
        Database::execute(
            "UPDATE `products` SET `views` = `views` + 1 WHERE `id` = ?",
            [$productId]
        );
    }

    public function addImage(int $productId, string $image, string $alt = ''): int
    {
        return Database::insert(
            "INSERT INTO `product_images` (`product_id`, `image`, `alt`) VALUES (?, ?, ?)",
            [$productId, $image, $alt]
        );
    }

    public function deleteImage(int $imageId): ?array
    {
        $img = Database::fetch("SELECT * FROM `product_images` WHERE `id` = ?", [$imageId]);
        if ($img) {
            Database::execute("DELETE FROM `product_images` WHERE `id` = ?", [$imageId]);
        }
        return $img;
    }

    public function adminList(int $page = 1, string $search = '', string $status = ''): array
    {
        $where  = [];
        $params = [];

        if ($search) {
            $where[]  = "(p.name LIKE ? OR p.sku LIKE ?)";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($status) {
            $where[]  = "p.status = ?";
            $params[] = $status;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset   = ($page - 1) * ITEMS_PER_PAGE;

        $total = Database::fetch(
            "SELECT COUNT(*) as cnt FROM `products` p {$whereStr}",
            $params
        )['cnt'];

        $data = Database::fetchAll(
            "SELECT p.*, c.name as collection_name, b.name as brand_name
             FROM `products` p
             LEFT JOIN `collections` c ON p.collection_id = c.id
             LEFT JOIN `brands` b ON p.brand_id = b.id
             {$whereStr}
             ORDER BY p.created_at DESC
             LIMIT " . ITEMS_PER_PAGE . " OFFSET {$offset}",
            $params
        );

        return [
            'data'         => $data,
            'total'        => (int)$total,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / ITEMS_PER_PAGE),
        ];
    }
}
