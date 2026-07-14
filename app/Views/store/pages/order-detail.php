<?php
$pageTitle = 'طلب #' . $order['order_number'];
ob_start(); ?>
<div class="container-sm" style="padding:28px 24px 80px">
  <div class="breadcrumb" style="display:flex;align-items:center;gap:8px;font-size:13px;color:var(--text2);margin-bottom:24px">
    <a href="<?= url('account') ?>" style="color:var(--text2)">حسابي</a>
    <span>/</span><span style="color:var(--text)">طلب <?= e($order['order_number']) ?></span>
  </div>
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px">
    <div>
      <h1 style="font-size:22px;font-weight:800;font-family:monospace"><?= e($order['order_number']) ?></h1>
      <p style="color:var(--text2);font-size:13px;margin-top:3px"><?= formatDateTime($order['created_at']) ?></p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
      <span class="badge badge-<?= orderStatusColor($order['status']) ?>"><?= orderStatusLabel($order['status']) ?></span>
      <span class="badge badge-<?= $order['payment_status']==='paid'?'success':($order['payment_status']==='unpaid'?'warning':'danger') ?>"><?= paymentStatusLabel($order['payment_status']) ?></span>
    </div>
  </div>
  <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;margin-bottom:18px">
    <div style="padding:14px 18px;border-bottom:1px solid var(--border);font-weight:600;font-size:14px">المنتجات</div>
    <?php foreach ($order['items'] as $item): ?>
      <div style="display:flex;align-items:center;gap:14px;padding:14px 18px;border-bottom:1px solid var(--border)">
        <?php if ($item['image']): ?>
          <img src="<?= uploadUrl($item['image']) ?>" style="width:52px;height:52px;object-fit:cover;border-radius:8px;border:1px solid var(--border);flex-shrink:0">
        <?php else: ?>
          <div style="width:52px;height:52px;border-radius:8px;background:var(--bg3);display:flex;align-items:center;justify-content:center;font-size:24px;flex-shrink:0">📦</div>
        <?php endif; ?>
        <div style="flex:1"><div style="font-weight:500"><?= e($item['name']) ?></div><?php if($item['variant']): ?><div style="font-size:12px;color:var(--text2)"><?= e($item['variant']) ?></div><?php endif; ?><div style="font-size:12px;color:var(--text3)">× <?= $item['qty'] ?></div></div>
        <div style="font-weight:700"><?= money($item['total']) ?></div>
      </div>
    <?php endforeach; ?>
    <div style="padding:16px 18px">
      <div style="max-width:240px;margin-right:auto">
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:5px 0;color:var(--text2)"><span>المجموع الفرعي</span><span><?= money($order['subtotal']) ?></span></div>
        <?php if ($order['discount_amount']>0): ?><div style="display:flex;justify-content:space-between;font-size:13px;padding:5px 0;color:var(--green)"><span>خصم</span><span>- <?= money($order['discount_amount']) ?></span></div><?php endif; ?>
        <div style="display:flex;justify-content:space-between;font-size:13px;padding:5px 0;color:var(--text2)"><span>الشحن</span><span><?= money($order['shipping_price']) ?></span></div>
        <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;padding:10px 0 0;border-top:1px solid var(--border);margin-top:5px"><span>الإجمالي</span><span><?= money($order['total']) ?></span></div>
      </div>
    </div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px">
      <div style="font-weight:600;margin-bottom:12px;font-size:13px">عنوان الشحن</div>
      <div style="font-size:13px;color:var(--text2);line-height:1.9"><?= e($order['shipping_address']) ?><br><?= e($order['shipping_city']) ?> — <?= e($order['shipping_gov']) ?></div>
    </div>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px">
      <div style="font-weight:600;margin-bottom:12px;font-size:13px">معلومات الدفع</div>
      <div style="font-size:13px;color:var(--text2)"><?= $order['payment_method']==='cod'?'💵 الدفع عند الاستلام':'💳 فواتيرك' ?></div>
    </div>
  </div>
  <style>.badge{display:inline-flex;align-items:center;gap:3px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:600}.badge::before{content:'';width:5px;height:5px;border-radius:50%;background:currentColor}.badge-success{background:rgba(34,197,94,.1);color:#22c55e}.badge-warning{background:rgba(245,158,11,.1);color:#f59e0b}.badge-danger{background:rgba(239,68,68,.1);color:#ef4444}.badge-neutral{background:rgba(255,255,255,.06);color:#9898a8}.badge-primary,.badge-info{background:rgba(109,90,205,.1);color:#8b75e8}</style>
</div>
<?php $content = ob_get_clean();
require APP_PATH . '/Views/store/layouts/app.php';
