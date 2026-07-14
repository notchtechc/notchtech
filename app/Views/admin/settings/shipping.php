<?php
$pageTitle  = 'مناطق الشحن';
$breadcrumb = [['label'=>'الإعدادات','url'=>adminUrl('settings')],['label'=>'الشحن']];
ob_start(); ?>
<div class="page-header">
  <div class="page-header-left"><h1>مناطق الشحن</h1></div>
  <div class="page-header-actions"><a href="<?= adminUrl('settings') ?>" class="btn btn-secondary">← الإعدادات</a></div>
</div>
<div style="display:grid;grid-template-columns:1fr 360px;gap:18px">
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>المنطقة</th><th>سعر الشحن</th><th>شحن مجاني فوق</th><th>الحالة</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($zones as $z): ?>
            <tr>
              <td><div style="font-weight:500"><?= e($z['name']) ?></div><div style="font-size:11px;color:var(--text-3)"><?php $gs=json_decode($z['governorates']??'[]',true); echo implode('، ', array_slice($gs,0,3)); if(count($gs)>3) echo '...'; ?></div></td>
              <td style="font-weight:600"><?= money($z['price']) ?></td>
              <td><?= $z['free_above'] ? money($z['free_above']) : '—' ?></td>
              <td><span class="badge <?= $z['is_active']?'badge-success':'badge-neutral' ?>"><?= $z['is_active']?'نشط':'موقوف' ?></span></td>
              <td><button class="btn btn-danger btn-sm" onclick="confirmAction('<?= adminUrl('settings/shipping/'.$z['id'].'/delete') ?>','حذف المنطقة؟','')">حذف</button></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">إضافة منطقة</span></div>
    <div class="card-body">
      <form method="POST" action="<?= adminUrl('settings/shipping') ?>">
        <?= csrf_field() ?>
        <div class="form-grid" style="gap:12px">
          <div class="form-group"><label class="form-label">اسم المنطقة</label><input type="text" name="name" class="form-input" required></div>
          <div class="form-group"><label class="form-label">سعر الشحن (<?= APP_CURRENCY_SYMBOL ?>)</label><input type="number" name="price" class="form-input" step="0.01" min="0" required></div>
          <div class="form-group"><label class="form-label">شحن مجاني فوق</label><input type="number" name="free_above" class="form-input" step="0.01" min="0" placeholder="اختياري"></div>
          <div class="form-group"><label class="form-label">المحافظات (مفصولة بفاصلة)</label><input type="text" name="governorates" class="form-input" placeholder="القاهرة، الجيزة، ..."></div>
          <button type="submit" class="btn btn-primary" style="justify-content:center">+ إضافة</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php $content = ob_get_clean(); require APP_PATH . '/Views/admin/layouts/app.php';
