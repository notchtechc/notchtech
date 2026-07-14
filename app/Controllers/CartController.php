<?php
class CartController extends Controller
{
    public function index(): void { $this->view('store.pages.cart'); }

    public function add(): void
    {
        if (!verifyCsrf()) { jsonResponse(false, 'خطأ'); }
        $ok = Cart::add((int)$this->post('product_id'), (int)$this->post('qty', 1), $this->post('variant_id') ? (int)$this->post('variant_id') : null);
        jsonResponse($ok, $ok ? 'تمت الإضافة' : 'حدث خطأ', ['count' => Cart::count()]);
    }

    public function update(): void
    {
        if (!verifyCsrf()) { jsonResponse(false, 'خطأ'); }
        Cart::update($this->post('key'), (int)$this->post('qty'));
        jsonResponse(true, 'تم التحديث', ['count' => Cart::count()]);
    }

    public function remove(): void
    {
        if (!verifyCsrf()) { jsonResponse(false, 'خطأ'); }
        Cart::remove($this->post('key'));
        jsonResponse(true, 'تم الحذف', ['count' => Cart::count()]);
    }

    public function clear(): void
    {
        if (!verifyCsrf()) { flashError('خطأ'); $this->redirect(url('cart')); }
        Cart::clear();
        $this->redirect(url('cart'));
    }

    public function discount(): void
    {
        if (!verifyCsrf()) { jsonResponse(false, 'خطأ'); }
        $result = Cart::applyDiscount($this->post('code', ''));
        jsonResponse($result['success'], $result['message'], ['amount' => $result['amount'] ?? 0]);
    }

    public function count(): void
    {
        jsonResponse(true, '', ['count' => Cart::count()]);
    }
}
