<?php
$pageTitle  = 'المنتجات';
$breadcrumb = [['label' => 'المنتجات']];
ob_start(); ?>

<div class="page-header">
  <div class="page-header-left">
    <h1>المنتجات</h1>
    <p><?= number_format($paginator['total']) ?> منتج</p>
  </div>
  <div class="page-header-actions">
    <a href="<?= adminUrl('products/create') ?>" class="btn btn-primary">
      + إضافة منتج
    </a>
  </div>
</div>

<div class="card">
  <!-- Filter bar -->
  <div style="padding:16px;border-bottom:1px solid var(--border)">
    <form method="GET" class="filter-bar">
      <div class="search-wrap">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" name="search" class="search-input" placeholder="بحث بالاسم أو SKU..." value="<?= e($search) ?>">
      </div>
      <select name="status" class="filter-select" onchange="this.form.submit()">
        <option value="">كل الحالات</option>
        <option value="active"   <?= $status==='active'   ?'selected':'' ?>>نشط</option>
        <option value="draft"    <?= $status==='draft'    ?'selected':'' ?>>مسودة</option>
        <option value="archived" <?= $status==='archived' ?'selected':'' ?>>مؤرشف</option>
      </select>
      <button type="submit" class="btn btn-secondary">بحث</button>
      <?php if ($search || $status): ?>
        <a href="<?= adminUrl('products') ?>" class="btn btn-secondary">مسح</a>
      <?php endif; ?>
    </form>
  </div>

  <!-- Table -->
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>المنتج</th>
          <th>الحالة</th>
          <th>السعر</th>
          <th>المخزون</th>
          <th>التصنيف</th>
          <th>الماركة</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($paginator['data'])): ?>
          <tr>
            <td colspan="7">
              <div class="empty-state">
                <div class="empty-icon">📦</div>
                <div class="empty-title">لا توجد منتجات</div>
                <div class="empty-desc">ابدأ بإضافة أول منتج في متجرك</div>
                <a href="<?= adminUrl('products/create') ?>" class="btn btn-primary">+ إضافة منتج</a>
              </div>
            </td>
          </tr>
        <?php else: foreach ($paginator['data'] as $p): ?>
          <tr>
            <td>
              <div class="product-cell">
                <?php if ($p['thumbnail']): ?>
                  <img src="<?= uploadUrl($p['thumbnail']) ?>" class="product-thumb" alt="">
                <?php else: ?>
                  <div class="product-thumb-placeholder">📦</div>
                <?php endif; ?>
                <div class="product-cell-info">
                  <div class="product-cell-name"><?= e($p['name']) ?></div>
                  <?php if ($p['sku']): ?>
                    <div class="product-cell-sku">SKU: <?= e($p['sku']) ?></div>
                  <?php endif; ?>
                </div>
              </div>
            </td>
            <td>
              <span class="badge <?= match($p['status']) { 'active'=>'badge-success', 'draft'=>'badge-warning', 'archived'=>'badge-neutral', default=>'badge-neutral' } ?>">
                <?= match($p['status']) { 'active'=>'نشط', 'draft'=>'مسودة', 'archived'=>'مؤرشف', default=>$p['status'] } ?>
              </span>
            </td>
            <td style="font-weight:600">
              <?= money($p['price']) ?>
              <?php if ($p['compare_price']): ?>
                <div style="font-size:11px;color:var(--text-3);text-decoration:line-through"><?= money($p['compare_price']) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <?php if (!$p['track_stock']): ?>
                <span style="color:var(--text-3)">—</span>
              <?php elseif ($p['stock'] == 0): ?>
                <span class="badge badge-danger">نفذ</span>
              <?php elseif ($p['stock'] <= 5): ?>
                <span class="badge badge-warning"><?= $p['stock'] ?></span>
              <?php else: ?>
                <span style="font-weight:500"><?= number_format($p['stock']) ?></span>
              <?php endif; ?>
            </td>
            <td class="td-light"><?= e($p['collection_name'] ?? '—') ?></td>
            <td class="td-light"><?= e($p['brand_name'] ?? '—') ?></td>
            <td>
              <div style="display:flex;gap:6px;justify-content:flex-end">
                <a href="<?= url('products/' . $p['slug']) ?>" target="_blank" class="btn btn-secondary btn-sm btn-icon" title="عرض">👁️</a>
                <a href="<?= adminUrl('products/' . $p['id'] . '/edit') ?>" class="btn btn-secondary btn-sm">تعديل</a>
                <button class="btn btn-danger btn-sm"
                  onclick="confirmAction('<?= adminUrl('products/' . $p['id'] . '/delete') ?>', 'حذف <?= e($p['name']) ?>؟', 'سيتم حذف المنتج وجميع صوره نهائياً.')">
                  حذف
                </button>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Pagination -->
  <?php if ($paginator['last_page'] > 1): ?>
    <div class="pagination">
      <?php for ($i = 1; $i <= $paginator['last_page']; $i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= urlencode($status) ?>">
          <div class="page-btn <?= $i === $paginator['current_page'] ? 'active' : '' ?>"><?= $i ?></div>
        </a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<?php
$content = ob_get_clean();
require APP_PATH . '/Views/admin/layouts/app.php';
