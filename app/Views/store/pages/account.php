<?php
$pageTitle = 'حسابي — ' . SettingModel::get('store_name', APP_NAME);
$user = storeUser();
$tab  = $_GET['tab'] ?? 'orders';
ob_start(); ?>
<div class="container" style="padding-top:28px;padding-bottom:80px">
  <div style="display:grid;grid-template-columns:220px 1fr;gap:24px;align-items:start">
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px;position:sticky;top:80px">
      <div style="text-align:center;margin-bottom:18px;padding-bottom:16px;border-bottom:1px solid var(--border)">
        <div style="width:56px;height:56px;border-radius:50%;background:var(--accent-bg);border:2px solid var(--accent);display:flex;align-items:center;justify-content:center;font-size:22px;font-weight:700;color:var(--accent2);margin:0 auto 10px"><?= mb_substr($user['name'],0,1) ?></div>
        <div style="font-weight:600;font-size:14px"><?= e($user['name']) ?></div>
        <div style="font-size:11px;color:var(--text2)"><?= e($user['email']) ?></div>
      </div>
      <?php foreach ([['orders','🛒','طلباتي'],['profile','👤','الملف الشخصي'],['wishlist','❤️','المفضلة']] as [$t,$ic,$lb]): ?>
        <a href="?tab=<?= $t ?>" style="display:flex;align-items:center;gap:8px;padding:9px 12px;border-radius:7px;font-size:13px;color:<?= $tab===$t?'var(--accent-h)':'var(--text2)' ?>;background:<?= $tab===$t?'var(--accent-bg2)':'transparent' ?>;margin-bottom:2px;transition:all .15s">
          <?= $ic ?> <?= $lb ?>
        </a>
      <?php endforeach; ?>
      <div style="border-top:1px solid var(--border);margin-top:10px;padding-top:10px">
        <a href="<?= url('logout') ?>" style="display:flex;align-items:center;gap:8px;padding:9px 12px;border-radius:7px;font-size:13px;color:var(--red)">⎋ تسجيل الخروج</a>
      </div>
    </div>

    <div>
      <?php if ($tab === 'orders'): ?>
        <h2 style="font-size:18px;font-weight:700;margin-bottom:18px">طلباتي</h2>
        <?php if (empty($orders)): ?>
          <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:50px;text-align:center">
            <div style="font-size:44px;margin-bottom:14px">🛒</div>
            <div style="font-weight:600;margin-bottom:6px">لا توجد طلبات بعد</div>
            <a href="<?= url('products') ?>" class="btn btn-primary" style="margin-top:12px">تسوق الآن</a>
          </div>
        <?php else: ?>
          <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden">
            <div class="table-wrap">
              <table>
                <thead><tr><th>رقم الطلب</th><th>التاريخ</th><th>الإجمالي</th><th>الحالة</th><th></th></tr></thead>
                <tbody>
                  <?php foreach ($orders as $o): ?>
                    <tr>
                      <td style="font-family:monospace;font-weight:600;color:var(--accent2)"><?= e($o['order_number']) ?></td>
                      <td class="td-light"><?= formatDate($o['created_at']) ?></td>
                      <td style="font-weight:700"><?= money($o['total']) ?></td>
                      <td><span class="badge badge-<?= orderStatusColor($o['status']) ?>"><?= orderStatusLabel($o['status']) ?></span></td>
                      <td><a href="<?= url('account/orders/'.$o['id']) ?>" class="btn btn-outline btn-sm">تفاصيل</a></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>

      <?php elseif ($tab === 'profile'): ?>
        <h2 style="font-size:18px;font-weight:700;margin-bottom:18px">الملف الشخصي</h2>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:24px">
          <form method="POST" action="<?= url('account/profile') ?>">
            <?= csrf_field() ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
              <div class="form-group"><label class="form-label">الاسم</label><input type="text" name="name" class="form-input" value="<?= e($user['name']) ?>" required></div>
              <div class="form-group"><label class="form-label">الهاتف</label><input type="tel" name="phone" class="form-input" value="<?= e($user['phone']??'') ?>"></div>
            </div>
            <div class="form-group" style="margin-bottom:20px"><label class="form-label">البريد الإلكتروني</label><input type="email" class="form-input" value="<?= e($user['email']) ?>" disabled style="opacity:.5"></div>
            <button type="submit" class="btn btn-primary">💾 حفظ التغييرات</button>
          </form>
        </div>

      <?php elseif ($tab === 'wishlist'): ?>
        <h2 style="font-size:18px;font-weight:700;margin-bottom:18px">المفضلة</h2>
        <?php if (empty($wishlist)): ?>
          <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:50px;text-align:center;color:var(--text2)">
            <div style="font-size:44px;margin-bottom:14px">🤍</div>
            <div style="font-weight:600;margin-bottom:6px">قائمة المفضلة فارغة</div>
            <a href="<?= url('products') ?>" class="btn btn-primary" style="margin-top:12px">استكشف المنتجات</a>
          </div>
        <?php else: ?>
          <div class="products-grid">
            <?php foreach ($wishlist as $p): ?>
              <?php include APP_PATH . '/Views/store/partials/product-card.php'; ?>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
<style>
.badge{display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:600}
.badge::before{content:'';width:5px;height:5px;border-radius:50%;background:currentColor}
.badge-success{background:rgba(34,197,94,.1);color:#22c55e}
.badge-warning{background:rgba(245,158,11,.1);color:#f59e0b}
.badge-danger{background:rgba(239,68,68,.1);color:#ef4444}
.badge-info{background:rgba(59,130,246,.1);color:#3b82f6}
.badge-neutral{background:rgba(255,255,255,.06);color:#9898a8}
.badge-primary{background:rgba(109,90,205,.1);color:#8b75e8}
</style>
<?php $content = ob_get_clean();
require APP_PATH . '/Views/store/layouts/app.php';
