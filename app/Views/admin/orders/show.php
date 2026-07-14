<?php
$pageTitle  = 'طلب #' . $order['order_number'];
$breadcrumb = [['label' => 'الطلبات', 'url' => adminUrl('orders')], ['label' => $order['order_number']]];
ob_start(); ?>

<div class="page-header">
  <div class="page-header-left">
    <h1 style="font-family:monospace"><?= e($order['order_number']) ?></h1>
    <p><?= formatDateTime($order['created_at']) ?></p>
  </div>
  <div class="page-header-actions">
    <a href="<?= adminUrl('orders') ?>" class="btn btn-secondary">← الطلبات</a>
    <button class="btn btn-secondary" onclick="window.print()">🖨️ طباعة</button>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">

  <!-- Main: items + timeline -->
  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- Order Items -->
    <div class="card">
      <div class="card-header"><span class="card-title">المنتجات</span></div>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>المنتج</th><th>السعر</th><th>الكمية</th><th>الإجمالي</th></tr>
          </thead>
          <tbody>
            <?php foreach ($order['items'] as $item): ?>
              <tr>
                <td>
                  <div style="display:flex;align-items:center;gap:10px">
                    <?php if ($item['image']): ?>
                      <img src="<?= uploadUrl($item['image']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:7px;border:1px solid var(--border)">
                    <?php else: ?>
                      <div style="width:40px;height:40px;background:var(--surface2);border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:18px">📦</div>
                    <?php endif; ?>
                    <div>
                      <div style="font-weight:500"><?= e($item['name']) ?></div>
                      <?php if ($item['variant']): ?>
                        <div style="font-size:12px;color:var(--text-3)"><?= e($item['variant']) ?></div>
                      <?php endif; ?>
                      <?php if ($item['sku']): ?>
                        <div style="font-size:11px;color:var(--text-3)">SKU: <?= e($item['sku']) ?></div>
                      <?php endif; ?>
                    </div>
                  </div>
                </td>
                <td><?= money($item['price']) ?></td>
                <td><?= $item['qty'] ?></td>
                <td style="font-weight:600"><?= money($item['total']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <!-- Totals -->
      <div style="padding:16px 20px;border-top:1px solid var(--border)">
        <div style="max-width:240px;margin-right:auto">
          <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;color:var(--text-2)">
            <span>المجموع الفرعي</span><span><?= money($order['subtotal']) ?></span>
          </div>
          <?php if ($order['discount_amount'] > 0): ?>
            <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;color:var(--green)">
              <span>خصم (<?= e($order['discount_code']) ?>)</span>
              <span>- <?= money($order['discount_amount']) ?></span>
            </div>
          <?php endif; ?>
          <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:13px;color:var(--text-2)">
            <span>الشحن</span><span><?= money($order['shipping_price']) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between;padding:10px 0 0;font-size:16px;font-weight:700;border-top:1px solid var(--border);margin-top:4px">
            <span>الإجمالي</span><span><?= money($order['total']) ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Admin Notes -->
    <div class="card">
      <div class="card-header"><span class="card-title">ملاحظات داخلية</span></div>
      <div class="card-body">
        <textarea id="adminNotes" class="form-textarea" rows="3" style="margin-bottom:10px"><?= e($order['admin_notes'] ?? '') ?></textarea>
        <button class="btn btn-secondary btn-sm" onclick="saveNotes(<?= $order['id'] ?>)">💾 حفظ الملاحظات</button>
      </div>
    </div>

    <?php if ($order['notes']): ?>
    <div class="card">
      <div class="card-header"><span class="card-title">ملاحظة العميل</span></div>
      <div class="card-body" style="color:var(--text-2);font-size:13px"><?= e($order['notes']) ?></div>
    </div>
    <?php endif; ?>

  </div>

  <!-- Sidebar: status + customer -->
  <div style="display:flex;flex-direction:column;gap:16px">

    <!-- Update Status -->
    <div class="card">
      <div class="card-header"><span class="card-title">حالة الطلب</span></div>
      <div class="card-body">
        <form method="POST" action="<?= adminUrl('orders/' . $order['id'] . '/status') ?>">
          <?= csrf_field() ?>
          <div class="form-group" style="margin-bottom:12px">
            <label class="form-label">حالة الطلب</label>
            <select name="status" class="form-select">
              <?php foreach (['pending'=>'معلق','processing'=>'قيد المعالجة','shipped'=>'تم الشحن','delivered'=>'تم التسليم','cancelled'=>'ملغي','refunded'=>'مسترجع'] as $val=>$lbl): ?>
                <option value="<?= $val ?>" <?= $order['status']===$val?'selected':'' ?>><?= $lbl ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:14px">
            <label class="form-label">حالة الدفع</label>
            <select name="payment_status" class="form-select">
              <?php foreach (['unpaid'=>'غير مدفوع','paid'=>'مدفوع','refunded'=>'مسترجع','failed'=>'فشل'] as $val=>$lbl): ?>
                <option value="<?= $val ?>" <?= $order['payment_status']===$val?'selected':'' ?>><?= $lbl ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">تحديث الحالة</button>
        </form>

        <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border)">
          <div style="display:flex;justify-content:space-between;margin-bottom:8px">
            <span style="font-size:12px;color:var(--text-3)">حالة الطلب</span>
            <span class="badge badge-<?= orderStatusColor($order['status']) ?>"><?= orderStatusLabel($order['status']) ?></span>
          </div>
          <div style="display:flex;justify-content:space-between">
            <span style="font-size:12px;color:var(--text-3)">الدفع</span>
            <span class="badge badge-<?= match($order['payment_status']){'paid'=>'success','unpaid'=>'warning','failed'=>'danger',default=>'neutral'} ?>"><?= paymentStatusLabel($order['payment_status']) ?></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Customer Info -->
    <div class="card">
      <div class="card-header"><span class="card-title">العميل</span></div>
      <div class="card-body" style="font-size:13px;display:flex;flex-direction:column;gap:8px">
        <div style="font-weight:600;font-size:14px"><?= e($order['customer_name']) ?></div>
        <div style="color:var(--text-2)">📧 <?= e($order['customer_email']) ?></div>
        <div style="color:var(--text-2)">📱 <?= e($order['customer_phone']) ?></div>
      </div>
    </div>

    <!-- Shipping -->
    <div class="card">
      <div class="card-header"><span class="card-title">عنوان الشحن</span></div>
      <div class="card-body" style="font-size:13px;color:var(--text-2);line-height:1.8">
        <?= e($order['shipping_address']) ?><br>
        <?= e($order['shipping_city']) ?> — <?= e($order['shipping_gov']) ?><br>
        <span style="color:var(--text-3)">الشحن: <?= money($order['shipping_price']) ?></span>
      </div>
    </div>

    <!-- Payment -->
    <div class="card">
      <div class="card-header"><span class="card-title">الدفع</span></div>
      <div class="card-body" style="font-size:13px;display:flex;flex-direction:column;gap:6px">
        <div style="color:var(--text-2)">الطريقة: <strong><?= $order['payment_method']==='cod'?'💵 الدفع عند الاستلام':'💳 فواتيرك' ?></strong></div>
        <?php if ($order['payment_ref']): ?>
          <div style="color:var(--text-2)">مرجع: <span class="td-mono"><?= e($order['payment_ref']) ?></span></div>
        <?php endif; ?>
        <?php if ($order['shipped_at']): ?>
          <div style="color:var(--text-3)">شُحن: <?= formatDateTime($order['shipped_at']) ?></div>
        <?php endif; ?>
        <?php if ($order['delivered_at']): ?>
          <div style="color:var(--text-3)">استُلم: <?= formatDateTime($order['delivered_at']) ?></div>
        <?php endif; ?>
      </div>
    </div>

  </div>
</div>

<script>
function saveNotes(orderId) {
  const notes = document.getElementById('adminNotes').value;
  const form  = new FormData();
  form.append('admin_notes', notes);
  form.append('_csrf', '<?= csrf_token() ?>');
  fetch('<?= adminUrl('orders') ?>/' + orderId + '/notes', {method:'POST',body:form})
    .then(r=>r.json()).then(d=>{
      if (d.success) { alert('✅ تم حفظ الملاحظات'); }
    });
}
</script>

<?php $content = ob_get_clean();
require APP_PATH . '/Views/admin/layouts/app.php'; ?>
