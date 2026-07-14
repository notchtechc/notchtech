<?php
class PaymentController extends Controller
{
    public function callback(): void
    {
        $data = array_merge($_GET, $_POST);
        $result = Fawateerk::verifyCallback($data);
        if ($result['success'] && $result['order_number']) {
            Database::execute("UPDATE `orders` SET `payment_status`='paid', `status`='processing', `payment_ref`=? WHERE `order_number`=?",
                [$result['ref'], $result['order_number']]);
            $this->redirect(url('checkout/success/' . $result['order_number']));
        }
        $this->redirect(url('payment/failed'));
    }
    public function success(): void { $this->redirect(url()); }
    public function failed(): void
    {
        flashError('فشل الدفع. يرجى المحاولة مرة أخرى أو اختيار الدفع عند الاستلام.');
        $this->redirect(url('checkout'));
    }
}
