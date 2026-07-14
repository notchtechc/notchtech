<?php
$pageTitle  = 'العميل: ' . e($customer['name']);
$breadcrumb = [['label'=>'العملاء','url'=>adminUrl('customers')],['label'=>$customer['name']]];
ob_start(); ?>
<div class="page-header">
  <div class="page-header-left"><h1><?= e($customer['name']) ?></h1><p>عضو منذ <?= formatDate($customer['created_at']) ?></p></div>
  <div class="page-header-actions"><a href="<?= adminUrl('customers') ?>" class="btn btn-secondary">← رجوع</a></div>
</div>
<div style="display:grid;grid-template-columns:1fr 280px;gap:16px">
  <div class="card">
    <div class="card-header"><span class="card-title">سجل الطلبات</span></div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>رقم الطلب</th><th>المبلغ</th><th>الحالة</th><th>التاريخ</th></tr></thead>
        <tbody>
          <?php if (empty($orders)): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--text-3);padding:30px">لا توجد طلبات</td></tr>
          <?php else: foreach ($orders as $o): ?>
            <tr>
              <td><a href="<?= adminUrl('orders/'.$o['id']) ?>" style="color:var(--accent-h)"><?= e($o['order_number']) ?></a></td>
              <td style="font-weight:600"><?= money($o['total']) ?></td>
              <td><span class="badge badge-<?= orderStatusColor($o['status']) ?>"><?= orderStatusLabel($o['status']) ?></span></td>
              <td class="td-light"><?= formatDate($o['created_at']) ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">معلومات العميل</span></div>
    <div class="card-body" style="font-size:13px;display:flex;flex-direction:column;gap:10px">
      <div>📧 <?= e($customer['email']) ?></div>
      <div>📱 <?= e($customer['phone']??'—') ?></div>
      <div>🛒 <?= $customer['total_orders'] ?> طلبات</div>
      <div>💰 <?= money($customer['total_spent']) ?> إجمالي</div>
      <div><span class="badge <?= $customer['is_active']?'badge-success':'badge-danger' ?>"><?= $customer['is_active']?'نشط':'موقوف' ?></span></div>
    </div>
  </div>
</div>
<?php $content = ob_get_clean(); require APP_PATH . '/Views/admin/layouts/app.php';
