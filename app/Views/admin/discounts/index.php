<?php
$pageTitle  = 'الخصومات';
$breadcrumb = [['label' => 'الخصومات']];
ob_start(); ?>

<div class="page-header">
  <div class="page-header-left"><h1>كودات الخصم</h1><p><?= number_format($paginator['total']) ?> كود</p></div>
</div>

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start">

  <!-- List -->
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>الكود</th><th>النوع</th><th>الاستخدامات</th><th>الصلاحية</th><th>الحالة</th><th></th></tr>
        </thead>
        <tbody>
          <?php if (empty($paginator['data'])): ?>
            <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">🎟️</div><div class="empty-title">لا توجد كودات</div></div></td></tr>
          <?php else: foreach ($paginator['data'] as $d): ?>
            <tr>
              <td><span style="font-family:monospace;font-weight:700;letter-spacing:1px;color:var(--accent-h)"><?= e($d['code']) ?></span></td>
              <td>
                <span class="badge <?= $d['type']==='percentage'?'badge-blue':'badge-purple' ?>">
                  <?= $d['type']==='percentage' ? $d['value'].'%' : money($d['value']) ?>
                </span>
              </td>
              <td><?= $d['used_count'] ?><?= $d['max_uses'] ? ' / '.$d['max_uses'] : '' ?></td>
              <td class="td-light">
                <?php if ($d['expires_at']): ?>
                  <?= formatDate($d['expires_at']) ?>
                  <?php if (strtotime($d['expires_at']) < time()): ?>
                    <span style="color:var(--red);font-size:11px">(منتهي)</span>
                  <?php endif; ?>
                <?php else: ?>—<?php endif; ?>
              </td>
              <td><span class="badge <?= $d['is_active']?'badge-success':'badge-neutral' ?>"><?= $d['is_active']?'نشط':'موقوف' ?></span></td>
              <td>
                <div style="display:flex;gap:6px">
                  <form method="POST" action="<?= adminUrl('discounts/'.$d['id'].'/toggle') ?>" style="display:inline"><?= csrf_field() ?>
                    <button class="btn btn-sm btn-secondary"><?= $d['is_active']?'إيقاف':'تفعيل' ?></button>
                  </form>
                  <button class="btn btn-sm btn-danger" onclick="confirmAction('<?= adminUrl('discounts/'.$d['id'].'/delete') ?>','حذف الكود؟','')">حذف</button>
                </div>
              </td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add new -->
  <div class="card">
    <div class="card-header"><span class="card-title">إضافة كود جديد</span></div>
    <div class="card-body">
      <form method="POST" action="<?= adminUrl('discounts/create') ?>">
        <?= csrf_field() ?>
        <div class="form-grid" style="gap:12px">
          <div class="form-group">
            <label class="form-label">الكود <span>*</span></label>
            <div style="display:flex;gap:6px">
              <input type="text" name="code" class="form-input" id="discountCode" placeholder="SALE20" style="text-transform:uppercase" required>
              <button type="button" class="btn btn-secondary btn-sm" onclick="generateCode()">عشوائي</button>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">النوع</label>
            <select name="type" class="form-select" id="discountType" onchange="updateTypeLabel()">
              <option value="percentage">نسبة مئوية %</option>
              <option value="fixed">مبلغ ثابت</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" id="valueLabel">الخصم (%)</label>
            <input type="number" name="value" class="form-input" step="0.01" min="0" required>
          </div>
          <div class="form-group">
            <label class="form-label">الحد الأدنى للطلب</label>
            <input type="number" name="min_order" class="form-input" step="0.01" min="0" placeholder="اختياري">
          </div>
          <div class="form-group">
            <label class="form-label">الحد الأقصى للاستخدام</label>
            <input type="number" name="max_uses" class="form-input" min="1" placeholder="لا نهاية">
          </div>
          <div class="form-group">
            <label class="form-label">تاريخ الانتهاء</label>
            <input type="datetime-local" name="expires_at" class="form-input">
          </div>
          <button type="submit" class="btn btn-primary" style="justify-content:center">+ إضافة الكود</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function generateCode() {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  let code = '';
  for (let i = 0; i < 8; i++) code += chars[Math.floor(Math.random() * chars.length)];
  document.getElementById('discountCode').value = code;
}
function updateTypeLabel() {
  const t = document.getElementById('discountType').value;
  document.getElementById('valueLabel').textContent = t === 'percentage' ? 'الخصم (%)' : 'مبلغ الخصم (<?= APP_CURRENCY_SYMBOL ?>)';
}
</script>

<?php $content = ob_get_clean(); require APP_PATH . '/Views/admin/layouts/app.php';
