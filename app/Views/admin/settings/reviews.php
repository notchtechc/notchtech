<?php
$pageTitle  = 'التقييمات';
$breadcrumb = [['label' => 'التقييمات']];
ob_start(); ?>
<div class="page-header">
  <div class="page-header-left"><h1>التقييمات</h1></div>
</div>
<div class="card">
  <div class="table-wrap">
    <table>
      <thead><tr><th>المنتج</th><th>العميل</th><th>التقييم</th><th>التعليق</th><th>الحالة</th><th></th></tr></thead>
      <tbody>
        <?php if (empty($reviews)): ?>
          <tr><td colspan="6"><div class="empty-state"><div class="empty-icon">⭐</div><div class="empty-title">لا توجد تقييمات</div></div></td></tr>
        <?php else: foreach ($reviews as $r): ?>
          <tr>
            <td style="font-weight:500"><?= e($r['product_name']??'—') ?></td>
            <td><?= e($r['name']) ?></td>
            <td><?= str_repeat('⭐', min(5, (int)$r['rating'])) ?></td>
            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($r['body']) ?></td>
            <td><span class="badge <?= $r['is_approved']?'badge-success':'badge-warning' ?>"><?= $r['is_approved']?'معتمد':'قيد المراجعة' ?></span></td>
            <td><div style="display:flex;gap:6px">
              <?php if (!$r['is_approved']): ?>
                <form method="POST" action="<?= adminUrl('reviews/'.$r['id'].'/approve') ?>"><?= csrf_field() ?><button class="btn btn-secondary btn-sm">✅ موافقة</button></form>
              <?php endif; ?>
              <button class="btn btn-danger btn-sm" onclick="confirmAction('<?= adminUrl('reviews/'.$r['id'].'/delete') ?>','حذف التقييم؟','')">حذف</button>
            </div></td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php $content = ob_get_clean(); require APP_PATH . '/Views/admin/layouts/app.php';
