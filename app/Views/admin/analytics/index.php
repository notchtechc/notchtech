<?php
$pageTitle  = 'التحليلات';
$breadcrumb = [['label' => 'التحليلات']];
ob_start(); ?>

<div class="page-header">
  <div class="page-header-left"><h1>التحليلات والتقارير</h1></div>
  <div class="page-header-actions">
    <a href="<?= adminUrl('analytics/export') ?>" class="btn btn-secondary">📥 تصدير CSV</a>
  </div>
</div>

<!-- Stats row -->
<div class="stats-grid" style="margin-bottom:20px">
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--green-bg)">💰</div>
    <div class="stat-label">إجمالي الإيرادات</div>
    <div class="stat-value"><?= money($stats['total_revenue']) ?></div>
    <div class="stat-sub">هذا الشهر: <?= money($stats['month_revenue']) ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--accent-bg)">🛒</div>
    <div class="stat-label">إجمالي الطلبات</div>
    <div class="stat-value"><?= number_format($stats['total_orders']) ?></div>
    <div class="stat-sub">هذا الشهر: <?= $stats['month_orders'] ?></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--yellow-bg)">⏳</div>
    <div class="stat-label">طلبات معلقة</div>
    <div class="stat-value"><?= $stats['pending_orders'] ?></div>
    <div class="stat-sub">تحتاج مراجعة</div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:var(--blue-bg)">📊</div>
    <div class="stat-label">متوسط قيمة الطلب</div>
    <div class="stat-value"><?= $stats['total_orders'] > 0 ? money($stats['total_revenue'] / $stats['total_orders']) : money(0) ?></div>
    <div class="stat-sub">AOV</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:16px;margin-bottom:20px">
  <!-- Sales Chart -->
  <div class="card">
    <div class="card-header"><span class="card-title">المبيعات — آخر 30 يوم</span></div>
    <div class="card-body">
      <?php
      $maxRev = max(array_merge([1], array_column($salesChart, 'revenue')));
      $pts = []; $cnt = count($salesChart);
      foreach ($salesChart as $i => $row):
        $x = $cnt > 1 ? ($i / ($cnt - 1)) * 560 + 20 : 300;
        $y = 110 - (($row['revenue'] / $maxRev) * 100);
        $pts[] = "{$x},{$y}";
      endforeach;
      $ptStr  = implode(' ', $pts);
      ?>
      <svg viewBox="0 0 600 130" style="width:100%;height:130px">
        <defs><linearGradient id="cg" x1="0" y1="0" x2="0" y2="1">
          <stop offset="0%" stop-color="#6d5acd" stop-opacity=".3"/>
          <stop offset="100%" stop-color="#6d5acd" stop-opacity="0"/>
        </linearGradient></defs>
        <?php if ($pts): $area = "20,110 " . $ptStr . " " . end($pts) . " " . ($cnt > 1 ? '580' : '20') . ",110"; ?>
        <polygon points="<?= $area ?>" fill="url(#cg)"/>
        <polyline points="<?= $ptStr ?>" fill="none" stroke="#6d5acd" stroke-width="2.5" stroke-linejoin="round"/>
        <?php foreach ($pts as $i => $pt): [$px,$py] = explode(',', $pt); ?>
          <circle cx="<?= $px ?>" cy="<?= $py ?>" r="3" fill="#6d5acd">
            <title><?= e($salesChart[$i]['date']??'') ?>: <?= money($salesChart[$i]['revenue']??0) ?></title>
          </circle>
        <?php endforeach; endif; ?>
        <!-- X axis -->
        <line x1="20" y1="115" x2="580" y2="115" stroke="var(--border)" stroke-width="1"/>
      </svg>
    </div>
  </div>

  <!-- Payment mix -->
  <div class="card">
    <div class="card-header"><span class="card-title">طرق الدفع</span></div>
    <div class="card-body">
      <?php
      $total = array_sum(array_column($paymentMix, 'cnt'));
      foreach ($paymentMix as $pm):
        $pct = $total > 0 ? round($pm['cnt'] / $total * 100) : 0;
      ?>
        <div style="margin-bottom:14px">
          <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:5px">
            <span><?= $pm['payment_method']==='cod'?'💵 كاش':'💳 فواتيرك' ?></span>
            <span style="font-weight:600"><?= $pct ?>%</span>
          </div>
          <div style="height:6px;background:var(--surface2);border-radius:3px;overflow:hidden">
            <div style="height:100%;width:<?= $pct ?>%;background:var(--accent);border-radius:3px"></div>
          </div>
        </div>
      <?php endforeach; if (empty($paymentMix)): ?>
        <div style="text-align:center;color:var(--text-3);padding:30px">لا توجد بيانات</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
  <!-- Top Products -->
  <div class="card">
    <div class="card-header"><span class="card-title">أكثر المنتجات مبيعاً</span></div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>#</th><th>المنتج</th><th>الكمية</th><th>الإيراد</th></tr></thead>
        <tbody>
          <?php if (empty($topProducts)): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--text-3);padding:30px">لا توجد مبيعات</td></tr>
          <?php else: foreach ($topProducts as $i => $p): ?>
            <tr>
              <td style="color:var(--text-3);font-weight:700"><?= $i + 1 ?></td>
              <td style="font-weight:500"><?= e($p['name']) ?></td>
              <td><?= number_format($p['sold']) ?></td>
              <td style="font-weight:600;color:var(--green)"><?= money($p['revenue']) ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Top Governorates -->
  <div class="card">
    <div class="card-header"><span class="card-title">المبيعات بالمحافظة</span></div>
    <div class="table-wrap">
      <table>
        <thead><tr><th>المحافظة</th><th>الطلبات</th><th>الإيراد</th></tr></thead>
        <tbody>
          <?php if (empty($topGovs)): ?>
            <tr><td colspan="3" style="text-align:center;color:var(--text-3);padding:30px">لا توجد بيانات</td></tr>
          <?php else: foreach ($topGovs as $g): ?>
            <tr>
              <td style="font-weight:500"><?= e($g['shipping_gov']) ?></td>
              <td><?= number_format($g['orders']) ?></td>
              <td style="font-weight:600;color:var(--green)"><?= money($g['revenue']) ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php $content = ob_get_clean(); require APP_PATH . '/Views/admin/layouts/app.php';
