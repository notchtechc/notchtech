<?php
class CollectionModel extends Model
{
    protected string $table = 'collections';
    protected array $fillable = ['name','slug','description','image','parent_id','sort_order','is_active','meta_title','meta_desc'];

    public function getActive(): array
    {
        return Database::fetchAll(
            "SELECT * FROM `collections` WHERE `is_active` = 1 ORDER BY `sort_order` ASC, `name` ASC"
        );
    }

    public function getBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    public function withProductCount(): array
    {
        return Database::fetchAll(
            "SELECT c.*, COUNT(p.id) as product_count
             FROM `collections` c
             LEFT JOIN `products` p ON p.collection_id = c.id AND p.status = 'active'
             WHERE c.is_active = 1
             GROUP BY c.id
             ORDER BY c.sort_order ASC"
        );
    }
}

class BrandModel extends Model
{
    protected string $table = 'brands';
    protected array $fillable = ['name','slug','logo','is_active','sort_order'];

    public function getActive(): array
    {
        return Database::fetchAll(
            "SELECT * FROM `brands` WHERE `is_active` = 1 ORDER BY `sort_order` ASC, `name` ASC"
        );
    }

    public function getBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }
}

class CustomerModel extends Model
{
    protected string $table = 'customers';
    protected array $fillable = ['name','email','password','phone','is_active','email_verified'];

    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    public function authenticate(string $email, string $password): ?array
    {
        $customer = $this->findByEmail($email);
        if ($customer && password_verify($password, $customer['password'])) {
            return $customer;
        }
        return null;
    }

    public function updateStats(int $id): void
    {
        Database::execute(
            "UPDATE `customers` SET
               `total_orders` = (SELECT COUNT(*) FROM `orders` WHERE customer_id = ?),
               `total_spent` = (SELECT COALESCE(SUM(total),0) FROM `orders` WHERE customer_id = ? AND payment_status = 'paid'),
               `last_order_at` = (SELECT MAX(created_at) FROM `orders` WHERE customer_id = ?)
             WHERE id = ?",
            [$id, $id, $id, $id]
        );
    }

    public function adminList(int $page = 1, string $search = ''): array
    {
        $where  = '';
        $params = [];
        if ($search) {
            $where  = "WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?";
            $params = ["%{$search}%", "%{$search}%", "%{$search}%"];
        }
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        $total  = Database::fetch("SELECT COUNT(*) as c FROM `customers` {$where}", $params)['c'];
        $data   = Database::fetchAll(
            "SELECT * FROM `customers` {$where} ORDER BY created_at DESC LIMIT " . ITEMS_PER_PAGE . " OFFSET {$offset}",
            $params
        );
        return ['data' => $data, 'total' => (int)$total, 'current_page' => $page, 'last_page' => (int)ceil($total / ITEMS_PER_PAGE)];
    }
}

class DiscountModel extends Model
{
    protected string $table = 'discounts';
    protected array $fillable = ['code','type','value','min_order','max_uses','starts_at','expires_at','is_active'];

    public function adminList(int $page = 1): array
    {
        $offset = ($page - 1) * ITEMS_PER_PAGE;
        $total  = Database::fetch("SELECT COUNT(*) as c FROM `discounts`")['c'];
        $data   = Database::fetchAll("SELECT * FROM `discounts` ORDER BY created_at DESC LIMIT " . ITEMS_PER_PAGE . " OFFSET {$offset}");
        return ['data' => $data, 'total' => (int)$total, 'current_page' => $page, 'last_page' => (int)ceil($total / ITEMS_PER_PAGE)];
    }

    public function incrementUsed(int $id): void
    {
        Database::execute("UPDATE `discounts` SET `used_count` = `used_count` + 1 WHERE `id` = ?", [$id]);
    }
}
