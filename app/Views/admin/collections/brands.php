<?php
$pageTitle  = 'الماركات';
$breadcrumb = [['label' => 'الماركات']];
ob_start(); ?>
<div class="page-header">
  <div class="page-header-left"><h1>الماركات</h1></div>
</div>
<div style="display:grid;grid-template-columns:1fr 320px;gap:18px">
  <div class="card">
    <div class="table-wrap">
      <table>
        <thead><tr><th>الماركة</th><th>الحالة</th><th>الترتيب</th><th></th></tr></thead>
        <tbody>
          <?php if (empty($brands)): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--text-3);padding:30px">لا توجد ماركات</td></tr>
          <?php else: foreach ($brands as $b): ?>
            <tr>
              <td><div style="display:flex;align-items:center;gap:10px">
                <?php if ($b['logo']): ?><img src="<?= uploadUrl($b['logo']) ?>" style="height:30px;object-fit:contain"><?php endif; ?>
                <span style="font-weight:500"><?= e($b['name']) ?></span>
              </div></td>
              <td><span class="badge <?= $b['is_active']?'badge-success':'badge-neutral' ?>"><?= $b['is_active']?'نشط':'مخفي' ?></span></td>
              <td><?= $b['sort_order'] ?></td>
              <td><button class="btn btn-danger btn-sm" onclick="confirmAction('<?= adminUrl('brands/'.$b['id'].'/delete') ?>','حذف الماركة؟','')">حذف</button></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card">
    <div class="card-header"><span class="card-title">إضافة ماركة</span></div>
    <div class="card-body">
      <form method="POST" action="<?= adminUrl('brands/create') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-grid" style="gap:12px">
          <div class="form-group"><label class="form-label">الاسم</label><input type="text" name="name" class="form-input" required></div>
          <div class="form-group"><label class="form-label">الشعار</label><input type="file" name="logo" class="form-input" accept="image/*" style="padding:6px"></div>
          <div class="form-group"><label class="form-label">الترتيب</label><input type="number" name="sort_order" class="form-input" value="0"></div>
          <button type="submit" class="btn btn-primary" style="justify-content:center">+ إضافة</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php $content = ob_get_clean(); require APP_PATH . '/Views/admin/layouts/app.php';
