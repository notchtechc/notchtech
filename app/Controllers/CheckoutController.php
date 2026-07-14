<?php
class CheckoutController extends Controller
{
    public function index(): void
    {
        if (Cart::isEmpty()) { $this->redirect(url('cart')); }
        $this->view('store.pages.checkout');
    }

    public function process(): void
    {
        if (!verifyCsrf()) { flashError('خطأ في التحقق'); $this->redirect(url('checkout')); }
        if (Cart::isEmpty()) { $this->redirect(url('cart')); }

        $orderModel = new OrderModel();
        $discount   = Cart::getDiscount();
        $shipping   = (float)$this->post('shipping_price', 0);
        $summary    = Cart::summary($shipping);

        $orderData = [
            'order_number'     => $orderModel->generateOrderNumber(),
            'customer_id'      => isStoreLoggedIn() ? storeUser()['id'] : null,
            'customer_name'    => trim($this->post('customer_name', '')),
            'customer_email'   => trim($this->post('customer_email', '')),
            'customer_phone'   => trim($this->post('customer_phone', '')),
            'shipping_address' => trim($this->post('shipping_address', '')),
            'shipping_city'    => trim($this->post('shipping_city', '')),
            'shipping_gov'     => trim($this->post('shipping_gov', '')),
            'shipping_price'   => $shipping,
            'subtotal'         => $summary['subtotal'],
            'discount_code'    => $discount['code'],
            'discount_amount'  => $discount['amount'],
            'total'            => $summary['total'],
            'payment_method'   => $this->post('payment_method', 'cod'),
            'payment_status'   => 'unpaid',
            'status'           => 'pending',
            'notes'            => trim($this->post('notes', '')),
        ];

        $items = [];
        foreach (Cart::get() as $item) {
            $items[] = [
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'],
                'name'       => $item['name'],
                'variant'    => $item['variant'],
                'sku'        => $item['sku'],
                'price'      => $item['price'],
                'qty'        => $item['qty'],
                'image'      => $item['image'],
            ];
        }

        try {
            $orderId = $orderModel->createWithItems($orderData, $items);

            // Increment discount usage
            if ($discount['id']) {
                (new DiscountModel())->incrementUsed($discount['id']);
            }

            // Update customer stats
            if (isStoreLoggedIn()) {
                (new CustomerModel())->updateStats(storeUser()['id']);
            }

            $orderNumber = $orderData['order_number'];

            // Fawateerk redirect
            if ($orderData['payment_method'] === 'fawateerk' && Fawateerk::isActive()) {
                $result = Fawateerk::createInvoice(array_merge($orderData, ['id' => $orderId]));
                if ($result['success']) {
                    Database::execute("UPDATE `orders` SET `payment_ref`=? WHERE `id`=?", [$result['ref'], $orderId]);
                    Cart::clear();
                    header('Location: ' . $result['url']);
                    exit;
                }
            }

            Cart::clear();
            $this->redirect(url('checkout/success/' . $orderNumber));
        } catch (\Throwable $e) {
            flashError('حدث خطأ أثناء معالجة الطلب. حاول مرة أخرى.');
            $this->redirect(url('checkout'));
        }
    }

    public function success(string $orderNumber): void
    {
        $order = (new OrderModel())->getByNumber($orderNumber);
        if (!$order) { $this->redirect(url()); }
        $this->view('store.pages.success', compact('order'));
    }
}
