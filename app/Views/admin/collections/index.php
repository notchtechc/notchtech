<?php
$pageTitle  = 'التصنيفات';
$breadcrumb = [['label' => 'التصنيفات']];
ob_start(); ?>

<div class="page-header">
  <div class="page-header-left"><h1>التصنيفات</h1></div>
  <div class="page-header-actions">
    <a href="<?= adminUrl('collections/create') ?>" class="btn btn-primary">+ إضافة تصنيف</a>
  </div>
</div>

<div class="card">
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>التصنيف</th><th>عدد المنتجات</th><th>الحالة</th><th>الترتيب</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (empty($collections)): ?>
          <tr><td colspan="5"><div class="empty-state"><div class="empty-icon">🗂️</div><div class="empty-title">لا توجد تصنيفات</div><a href="<?= adminUrl('collections/create') ?>" class="btn btn-primary">+ إضافة تصنيف</a></div></td></tr>
        <?php else: foreach ($collections as $c): ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:12px">
                <?php if ($c['image']): ?>
                  <img src="<?= uploadUrl($c['image']) ?>" style="width:40px;height:40px;object-fit:cover;border-radius:7px;border:1px solid var(--border)">
                <?php else: ?>
                  <div style="width:40px;height:40px;background:var(--surface2);border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:20px">🗂️</div>
                <?php endif; ?>
                <div>
                  <div style="font-weight:500"><?= e($c['name']) ?></div>
                  <div style="font-size:11px;color:var(--text-3)"><?= e($c['slug']) ?></div>
                </div>
              </div>
            </td>
            <td><span class="badge badge-purple"><?= number_format($c['product_count']) ?> منتج</span></td>
            <td><span class="badge <?= $c['is_active']?'badge-success':'badge-neutral' ?>"><?= $c['is_active']?'نشط':'مخفي' ?></span></td>
            <td class="td-light"><?= $c['sort_order'] ?></td>
            <td>
              <div style="display:flex;gap:6px;justify-content:flex-end">
                <a href="<?= adminUrl('collections/'.$c['id'].'/edit') ?>" class="btn btn-secondary btn-sm">تعديل</a>
                <button class="btn btn-danger btn-sm" onclick="confirmAction('<?= adminUrl('collections/'.$c['id'].'/delete') ?>','حذف التصنيف؟','سيتم إلغاء ربط المنتجات بهذا التصنيف.')">حذف</button>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php $content = ob_get_clean(); require APP_PATH . '/Views/admin/layouts/app.php';
