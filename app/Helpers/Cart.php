<?php
class Cart
{
    private const SESSION_KEY = 'cart';

    public static function get(): array
    {
        return Session::get(self::SESSION_KEY, []);
    }

    public static function add(int $productId, int $qty = 1, ?int $variantId = null): bool
    {
        $product = (new ProductModel())->find($productId);
        if (!$product || $product['status'] !== 'active') return false;

        $cart = self::get();
        $key  = $productId . '_' . ($variantId ?? 0);

        $price = $product['price'];
        $name  = $product['name'];
        $sku   = $product['sku'] ?? '';
        $image = $product['thumbnail'] ?? '';
        $variant = null;

        if ($variantId) {
            $v = Database::fetch("SELECT * FROM `product_variants` WHERE `id` = ? AND `product_id` = ?", [$variantId, $productId]);
            if ($v) {
                $price   = $v['price'];
                $variant = $v['title'];
                $image   = $v['image'] ?: $image;
                $sku     = $v['sku'] ?: $sku;
            }
        }

        if (isset($cart[$key])) {
            $cart[$key]['qty'] += $qty;
        } else {
            $cart[$key] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'name'       => $name,
                'variant'    => $variant,
                'price'      => (float)$price,
                'qty'        => $qty,
                'sku'        => $sku,
                'image'      => $image,
            ];
        }

        Session::set(self::SESSION_KEY, $cart);
        return true;
    }

    public static function update(string $key, int $qty): void
    {
        $cart = self::get();
        if ($qty <= 0) {
            unset($cart[$key]);
        } elseif (isset($cart[$key])) {
            $cart[$key]['qty'] = $qty;
        }
        Session::set(self::SESSION_KEY, $cart);
    }

    public static function remove(string $key): void
    {
        $cart = self::get();
        unset($cart[$key]);
        Session::set(self::SESSION_KEY, $cart);
    }

    public static function clear(): void
    {
        Session::remove(self::SESSION_KEY);
        Session::remove('discount_code');
        Session::remove('discount_amount');
    }

    public static function count(): int
    {
        return array_sum(array_column(self::get(), 'qty'));
    }

    public static function subtotal(): float
    {
        $total = 0;
        foreach (self::get() as $item) {
            $total += $item['price'] * $item['qty'];
        }
        return $total;
    }

    public static function isEmpty(): bool
    {
        return empty(self::get());
    }

    public static function applyDiscount(string $code): array
    {
        $discount = Database::fetch(
            "SELECT * FROM `discounts`
             WHERE code = ? AND is_active = 1
             AND (starts_at IS NULL OR starts_at <= NOW())
             AND (expires_at IS NULL OR expires_at >= NOW())
             AND (max_uses IS NULL OR used_count < max_uses)",
            [strtoupper(trim($code))]
        );

        if (!$discount) {
            return ['success' => false, 'message' => 'كود الخصم غير صحيح أو منتهي'];
        }

        $subtotal = self::subtotal();

        if ($discount['min_order'] && $subtotal < $discount['min_order']) {
            return [
                'success' => false,
                'message' => 'الحد الأدنى للطلب هو ' . money($discount['min_order'])
            ];
        }

        $amount = $discount['type'] === 'percentage'
            ? ($subtotal * $discount['value'] / 100)
            : (float)$discount['value'];

        $amount = min($amount, $subtotal);

        Session::set('discount_code', $discount['code']);
        Session::set('discount_amount', $amount);
        Session::set('discount_id', $discount['id']);

        return [
            'success' => true,
            'message' => 'تم تطبيق كود الخصم!',
            'amount'  => $amount,
        ];
    }

    public static function getDiscount(): array
    {
        return [
            'code'   => Session::get('discount_code', ''),
            'amount' => (float)Session::get('discount_amount', 0),
            'id'     => Session::get('discount_id'),
        ];
    }

    public static function summary(float $shippingPrice = 0): array
    {
        $subtotal = self::subtotal();
        $discount = self::getDiscount();

        return [
            'subtotal'        => $subtotal,
            'discount_code'   => $discount['code'],
            'discount_amount' => $discount['amount'],
            'shipping'        => $shippingPrice,
            'total'           => max(0, $subtotal - $discount['amount'] + $shippingPrice),
        ];
    }
}
