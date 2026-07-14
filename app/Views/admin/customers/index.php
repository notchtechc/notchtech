<?php
$pageTitle  = 'العملاء';
$breadcrumb = [['label' => 'العملاء']];
ob_start(); ?>

<div class="page-header">
  <div class="page-header-left"><h1>العملاء</h1><p><?= number_format($paginator['total']) ?> عميل</p></div>
  <div class="page-header-actions">
    <a href="<?= adminUrl('analytics/export') ?>" class="btn btn-secondary">📥 تصدير CSV</a>
  </div>
</div>

<div class="card">
  <div style="padding:14px 16px;border-bottom:1px solid var(--border)">
    <form method="GET" class="filter-bar">
      <div class="search-wrap">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" name="search" class="search-input" placeholder="الاسم، البريد، الهاتف..." value="<?= e($search) ?>">
      </div>
      <button type="submit" class="btn btn-secondary">بحث</button>
    </form>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>العميل</th><th>الهاتف</th><th>الطلبات</th><th>إجمالي الإنفاق</th><th>الحالة</th><th>تاريخ التسجيل</th><th></th></tr>
      </thead>
      <tbody>
        <?php if (empty($paginator['data'])): ?>
          <tr><td colspan="7"><div class="empty-state"><div class="empty-icon">👥</div><div class="empty-title">لا يوجد عملاء</div></div></td></tr>
        <?php else: foreach ($paginator['data'] as $c): ?>
          <tr>
            <td>
              <div style="font-weight:500"><?= e($c['name']) ?></div>
              <div style="font-size:11px;color:var(--text-3)"><?= e($c['email']) ?></div>
            </td>
            <td class="td-light"><?= e($c['phone'] ?? '—') ?></td>
            <td style="font-weight:500"><?= number_format($c['total_orders']) ?></td>
            <td style="font-weight:600;color:var(--green)"><?= money($c['total_spent']) ?></td>
            <td><span class="badge <?= $c['is_active'] ? 'badge-success' : 'badge-danger' ?>"><?= $c['is_active'] ? 'نشط' : 'موقوف' ?></span></td>
            <td class="td-light"><?= formatDate($c['created_at']) ?></td>
            <td>
              <div style="display:flex;gap:6px">
                <a href="<?= adminUrl('customers/' . $c['id']) ?>" class="btn btn-secondary btn-sm">عرض</a>
                <form method="POST" action="<?= adminUrl('customers/' . $c['id'] . '/toggle') ?>" style="display:inline"><?= csrf_field() ?>
                  <button class="btn btn-sm <?= $c['is_active'] ? 'btn-danger' : 'btn-secondary' ?>"><?= $c['is_active'] ? 'إيقاف' : 'تفعيل' ?></button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($paginator['last_page'] > 1): ?>
    <div class="pagination">
      <?php for ($i=1;$i<=$paginator['last_page'];$i++): ?>
        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><div class="page-btn <?= $i===$paginator['current_page']?'active':'' ?>"><?= $i ?></div></a>
      <?php endfor; ?>
    </div>
  <?php endif; ?>
</div>

<?php $content = ob_get_clean(); require APP_PATH . '/Views/admin/layouts/app.php';
