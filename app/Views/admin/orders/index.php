<?php
$pageTitle  = 'الطلبات';
$breadcrumb = [['label' => 'الطلبات']];
ob_start(); ?>

<div class="page-header">
  <div class="page-header-left">
    <h1>الطلبات</h1>
    <p><?= number_format($paginator['total']) ?> طلب</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= adminUrl('analytics') ?>" class="btn btn-secondary">📊 التقارير</a>
  </div>
</div>

<!-- Quick status filters -->
<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
  <?php
  $statuses = [''=>'الكل','pending'=>'معلق','processing'=>'قيد المعالجة','shipped'=>'تم الشحن','delivered'=>'تم التسليم','cancelled'=>'ملغي'];
  foreach ($statuses as $val => $label):
    $isActive = $status === $val;
  ?>
    <a href="?status=<?= urlencode($val) ?>&search=<?= urlencode($search) ?>">
      <button class="btn <?= $isActive ? 'btn-primary' : 'btn-secondary' ?> btn-sm"><?= $label ?></button>
    </a>
  <?php endforeach; ?>
</div>

<div class="card">
  <div style="padding:14px 16px;border-bottom:1px solid var(--border)">
    <form method="GET" class="filter-bar">
      <input type="hidden" name="status" value="<?= e($status) ?>">
      <div class="search-wrap">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" name="search" class="search-input" placeholder="رقم الطلب، اسم العميل، البريد..." value="<?= e($search) ?>">
      </div>
      <select name="payment" class="filter-select" onchange="this.form.submit()">
        <option value="">كل المدفوعات</option>
        <option value="unpaid"   <?= $payment==='unpaid'  ?'selected':'' ?>>غير مدفوع</option>
        <option value="paid"     <?= $payment==='paid'    ?'selected':'' ?>>مدفوع</option>
        <option value="failed"   <?= $payment==='failed'  ?'selected':'' ?>>فشل الدفع</option>
      </select>
      <button type="submit" class="btn btn-secondary">بحث</button>
    </form>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>رقم الطلب</th>
          <th>العميل</th>
          <th>المبلغ</th>
          <th>الدفع</th>
          <th>حالة الطلب</th>
          <th>طريقة الدفع</th>
          <th>التاريخ</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($paginator['data'])): ?>
          <tr><td colspan="8"><div class="empty-state"><div class="empty-icon">🛒</div><div class="empty-title">لا توجد طلبات</div></div></td></tr>
        <?php else: foreach ($paginator['data'] as $o): ?>
          <tr>
            <td>
              <a href="<?= adminUrl('orders/' . $o['id']) ?>" style="color:var(--accent-h);font-weight:600;font-family:monospace">
                <?= e($o['order_number']) ?>
              </a>
            </td>
            <td>
              <div style="font-weight:500"><?= e($o['customer_name']) ?></div>
              <div style="font-size:11px;color:var(--text-3)"><?= e($o['customer_phone']) ?></div>
            </td>
            <td style="font-weight:700"><?= money($o['total']) ?></td>
            <td>
              <span class="badge <?= match($o['payment_status']) { 'paid'=>'badge-success', 'unpaid'=>'badge-warning', 'failed'=>'badge-danger', default=>'badge-neutral' } ?>">
                <?= paymentStatusLabel($o['payment_status']) ?>
              </span>
            </td>
            <td>
              <span class="badge badge-<?= orderStatusColor($o['status']) ?>">
                <?= orderStatusLabel($o['status']) ?>
              </span>
            </td>
            <td class="td-light">
              <?= $o['payment_method'] === 'cod' ? '💵 استلام' : '💳 فواتيرك' ?>
            </td>
            <td class="td-light"><?= formatDate($o['created_at']) ?></td>
            <td>
              <a href="<?= adminUrl('orders/' . $o['id']) ?>" class="btn btn-secondary btn-sm">عرض</a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($paginator['last_page'] > 1): ?>
    <div class="pagination">
      <?php for ($i = 1; $i <= $paginator['last_page']; $i++): ?>
        <a href="?page=<?= $i ?>&status=<?= urlencode($status) ?>&search=<?= urlencode($search) ?>&payment=<?= urlencode($payment) ?>">
          <div class="page-btn <?= $i === $paginator['current_page'] ? 'active' : '' ?>"><?= $i ?></div>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<?php $content = ob_get_clean();
require APP_PATH . '/Views/admin/layouts/app.php'; ?>
