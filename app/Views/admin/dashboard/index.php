<?php
$pageTitle  = 'الرئيسية';
$breadcrumb = [['label' => 'الرئيسية']];

// Safe data fetching
try {
    $maxRev = max(array_merge([1], array_column($salesChart ?? [], 'revenue')));
    $pts = []; $cnt = count($salesChart ?? []);
    foreach (($salesChart ?? []) as $i => $row) {
        $x = $cnt > 1 ? ($i / ($cnt - 1)) * 560 + 20 : 300;
        $y = 110 - (($row['revenue'] / $maxRev) * 100);
        $pts[] = "{$x},{$y}";
    }
    $ptStr = implode(' ', $pts);
} catch(\Throwable $e) { $pts = []; $ptStr = ''; }

ob_start(); ?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--accent-bg)">📦</div>
    <div class="stat-label">إجمالي الطلبات</div>
    <div class="stat-value"><?= number_format($stats['total_orders'] ?? 0) ?></div>
    <div class="stat-sub">اليوم: <?= $stats['today_orders'] ?? 0 ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--green-bg)">💰</div>
    <div class="stat-label">إجمالي الإيرادات</div>
    <div class="stat-value" style="font-size:18px"><?= money($stats['total_revenue'] ?? 0) ?></div>
    <div class="stat-sub">هذا الشهر: <?= money($stats['month_revenue'] ?? 0) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--blue-bg)">👥</div>
    <div class="stat-label">العملاء</div>
    <div class="stat-value"><?= number_format($stats['total_customers'] ?? 0) ?></div>
    <div class="stat-sub">منتجات: <?= number_format($stats['total_products'] ?? 0) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--yellow-bg)">⏳</div>
    <div class="stat-label">طلبات معلقة</div>
    <div class="stat-value"><?= $stats['pending_orders'] ?? 0 ?></div>
    <div class="stat-sub">تحتاج مراجعة</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:18px">
  <div class="card">
    <div class="card-header">
      <span class="card-title">الإيرادات — آخر 30 يوم</span>
      <a href="/<?= defined('ADMIN_PREFIX')?ADMIN_PREFIX:'admin' ?>/analytics" class="btn btn-secondary btn-sm">تفاصيل</a>
    </div>
    <div class="card-body">
      <div style="font-size:26px;font-weight:800;letter-spacing:-1px;margin-bottom:14px"><?= money($stats['month_revenue'] ?? 0) ?></div>
      <?php if ($pts): ?>
        <svg viewBox="0 0 600 120" style="width:100%;height:110px;overflow:visible">
          <defs><linearGradient id="cg" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0%" stop-color="#6d5acd" stop-opacity=".3"/>
            <stop offset="100%" stop-color="#6d5acd" stop-opacity="0"/>
          </linearGradient></defs>
          <?php
          $area = "20,110 " . $ptStr . " " . end($pts) . " " . ($cnt > 1 ? '580' : '20') . ",110";
          ?>
          <polygon points="<?= $area ?>" fill="url(#cg)"/>
          <polyline points="<?= $ptStr ?>" fill="none" stroke="#6d5acd" stroke-width="2.5" stroke-linejoin="round"/>
          <?php foreach ($pts as $i => $pt): [$px,$py] = explode(',', $pt); ?>
            <circle cx="<?= $px ?>" cy="<?= $py ?>" r="3" fill="#6d5acd">
              <title><?= e($salesChart[$i]['date'] ?? '') ?>: <?= money($salesChart[$i]['revenue'] ?? 0) ?></title>
            </circle>
          <?php endforeach; ?>
        </svg>
      <?php else: ?>
        <div style="text-align:center;color:var(--text-3);padding:30px 0">لا توجد بيانات بعد</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><span class="card-title">أكثر المنتجات مبيعاً</span></div>
    <?php if (empty($topProducts)): ?>
      <div style="padding:30px;text-align:center;color:var(--text-3);font-size:13px">لا توجد مبيعات بعد</div>
    <?php else: foreach ($topProducts as $i => $p): ?>
      <div style="padding:11px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px">
        <div style="width:22px;height:22px;border-radius:50%;background:var(--accent-bg);color:var(--accent-h);font-size:10px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0"><?= $i+1 ?></div>
        <?php if ($p['thumbnail']): ?>
          <img src="<?= uploadUrl($p['thumbnail']) ?>" class="product-thumb" alt="">
        <?php else: ?>
          <div class="product-thumb-ph">📦</div>
        <?php endif; ?>
        <div style="flex:1;min-width:0">
          <div style="font-size:12px;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($p['name']) ?></div>
          <div style="font-size:10px;color:var(--text-3)"><?= $p['sold'] ?> مبيعة</div>
        </div>
        <div style="font-size:11px;font-weight:600;color:var(--green);flex-shrink:0"><?= money($p['revenue']) ?></div>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<div style="display:grid;grid-template-columns:3fr 2fr;gap:14px">
  <div class="card">
    <div class="card-header">
      <span class="card-title">آخر الطلبات</span>
      <a href="/<?= defined('ADMIN_PREFIX')?ADMIN_PREFIX:'admin' ?>/orders" class="btn btn-secondary btn-sm">عرض الكل</a>
    </div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>رقم الطلب</th><th>العميل</th><th>المبلغ</th><th>الحالة</th><th>التاريخ</th></tr></thead>
        <tbody>
          <?php if (empty($recentOrders)): ?>
            <tr><td colspan="5" style="text-align:center;color:var(--text-3);padding:30px">لا توجد طلبات بعد</td></tr>
          <?php else: foreach ($recentOrders as $o): ?>
            <tr>
              <td><a href="/<?= defined('ADMIN_PREFIX')?ADMIN_PREFIX:'admin' ?>/orders/<?= $o['id'] ?>" style="color:var(--accent-h);font-weight:600;font-family:monospace;font-size:12px"><?= e($o['order_number']) ?></a></td>
              <td style="font-weight:500"><?= e($o['customer_name']) ?></td>
              <td style="font-weight:700"><?= money($o['total']) ?></td>
              <td><span class="badge badge-<?= orderStatusColor($o['status']) ?>"><?= orderStatusLabel($o['status']) ?></span></td>
              <td class="td-light"><?= formatDate($o['created_at']) ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><span class="card-title">⚠️ مخزون منخفض</span></div>
    <?php if (empty($stats['low_stock'])): ?>
      <div style="padding:30px;text-align:center;color:var(--text-3);font-size:13px">✅ كل المنتجات بمخزون كافٍ</div>
    <?php else: foreach ($stats['low_stock'] as $p): ?>
      <div style="padding:11px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:8px">
        <div style="font-size:12px;font-weight:500;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= e($p['name']) ?></div>
        <span class="badge badge-<?= $p['stock']==0?'danger':'warning' ?>"><?= $p['stock']==0?'نفذ':$p['stock'].' متبقي' ?></span>
      </div>
    <?php endforeach; endif; ?>
  </div>
</div>

<?php
$content = ob_get_clean();
require APP_PATH . '/Views/admin/layouts/app.php';
