<?php
$pageTitle = SettingModel::get('meta_title', APP_NAME);
$pageDesc  = SettingModel::get('meta_description', '');

$productModel    = new ProductModel();
$collectionModel = new CollectionModel();
$brandModel      = new BrandModel();

$featured    = $productModel->getFeatured(8);
$collections = $collectionModel->withProductCount();
$brands      = $brandModel->getActive();
$newArrivals = $productModel->getActive(8);

$totalProducts  = Database::fetch("SELECT COUNT(*) as c FROM products WHERE status='active'")['c'] ?? 0;
$totalOrders    = Database::fetch("SELECT COUNT(*) as c FROM orders WHERE status='delivered'")['c'] ?? 0;
$totalCustomers = Database::fetch("SELECT COUNT(*) as c FROM customers")['c'] ?? 0;

ob_start(); ?>

<section class="hero-modern">
  <div class="container">
    <div class="hero-layout">
      <div>
        <div class="hero-kicker">
          <span class="hero-dot"></span>
          <?= e(SettingModel::get('store_description', 'متجر الإلكترونيات الرائد')) ?>
        </div>
        <h1 class="hero-title">
          <?= e(SettingModel::get('hero_title', 'أحدث التقنيات')) ?> <span>بتجربة شراء فاخرة</span>
        </h1>
        <p class="hero-subtitle">
          <?= e(SettingModel::get('hero_subtitle', 'تسوق أفضل المنتجات الإلكترونية من أشهر الماركات العالمية مع ضمان رسمي، دفع آمن، وشحن سريع لباب البيت.')) ?>
        </p>
        <div class="hero-actions">
          <a href="<?= url(ltrim(SettingModel::get('hero_btn_url', 'products'), '/')) ?>" class="btn btn-primary btn-lg">
            <?= e(SettingModel::get('hero_btn_text', 'تسوق الآن')) ?>
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
          </a>
          <a href="<?= url('products') ?>?sort=popular" class="btn btn-outline btn-lg">الأكثر طلبًا</a>
        </div>

        <div class="hero-stats">
          <div class="hero-stat">
            <strong><?= number_format($totalProducts) ?>+</strong>
            <span>منتج متاح</span>
          </div>
          <div class="hero-stat">
            <strong><?= number_format($totalOrders) ?>+</strong>
            <span>طلب مكتمل</span>
          </div>
          <div class="hero-stat">
            <strong><?= number_format($totalCustomers) ?>+</strong>
            <span>عميل سعيد</span>
          </div>
        </div>
      </div>

      <div class="hero-showcase" aria-hidden="true">
        <div class="showcase-card showcase-main">
          <span class="showcase-chip">New generation gear</span>
          <div class="showcase-device">⚡</div>
          <div class="showcase-price">
            <div>
              <div style="font-size:12px;color:var(--text3);font-weight:700">Notch Selection</div>
              <b>Premium Tech</b>
            </div>
            <a href="<?= url('products') ?>" class="btn btn-primary btn-sm">اكتشف</a>
          </div>
        </div>
        <div class="showcase-card showcase-mini">
          <div class="ring">🔒</div>
          <div style="font-weight:800;color:#fff;margin-bottom:4px">شراء آمن</div>
          <div style="font-size:12px;color:var(--text3)">دفع مشفر وتتبع لحظي للطلب</div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>@keyframes pulse{0%,100%{opacity:1}50%{opacity:.45}}</style>

<?php if (!empty($brands)): ?>
<div class="brand-strip">
  <div class="container">
    <div class="brand-strip-inner">
      <?php foreach ($brands as $b): ?>
        <a href="<?= url('brands/' . $b['slug']) ?>" class="brand-item" aria-label="<?= e($b['name']) ?>">
          <?php if ($b['logo']): ?>
            <img src="<?= uploadUrl($b['logo']) ?>" alt="<?= e($b['name']) ?>" style="height:30px;width:auto;object-fit:contain">
          <?php else: ?>
            <span style="font-size:14px;font-weight:900;color:var(--text2);letter-spacing:.5px"><?= e($b['name']) ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if (!empty($collections)): ?>
<section class="section">
  <div class="container">
    <div class="section-header" style="display:flex;align-items:flex-end;justify-content:space-between">
      <div>
        <div class="section-label">🗂️ التصنيفات</div>
        <h2 class="section-title">اختار فئتك المفضلة</h2>
        <p class="section-sub">تنظيم أسهل لكل الأجهزة والإكسسوارات التي تحتاجها.</p>
      </div>
      <a href="<?= url('products') ?>" class="btn btn-outline btn-sm">عرض الكل</a>
    </div>
    <div class="category-grid">
      <?php foreach (array_slice($collections, 0, 8) as $c): ?>
        <a href="<?= url('collections/' . $c['slug']) ?>" class="category-card">
          <?php if ($c['image']): ?>
            <img src="<?= uploadUrl($c['image']) ?>" alt="<?= e($c['name']) ?>" class="category-icon" style="object-fit:cover;padding:0">
          <?php else: ?>
            <div class="category-icon">🗂️</div>
          <?php endif; ?>
          <div style="font-size:14px;font-weight:800;color:var(--text);margin-bottom:4px"><?= e($c['name']) ?></div>
          <div style="font-size:12px;color:var(--text3)"><?= $c['product_count'] ?> منتج</div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($featured)): ?>
<section class="section" style="padding-top:0">
  <div class="container">
    <div class="section-header" style="display:flex;align-items:flex-end;justify-content:space-between">
      <div>
        <div class="section-label">⭐ مميز</div>
        <h2 class="section-title">منتجات مختارة بعناية</h2>
        <p class="section-sub">أفضل الترشيحات من Notch Technology.</p>
      </div>
      <a href="<?= url('products') ?>" class="btn btn-outline btn-sm">عرض الكل</a>
    </div>
    <div class="products-grid">
      <?php foreach ($featured as $p): ?>
        <?php include APP_PATH . '/Views/store/partials/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php if (!empty($newArrivals)): ?>
<section class="section" style="padding-top:0">
  <div class="container">
    <div class="section-header" style="display:flex;align-items:flex-end;justify-content:space-between">
      <div>
        <div class="section-label">🆕 جديد</div>
        <h2 class="section-title">وصل حديثًا</h2>
        <p class="section-sub">أحدث المنتجات المتاحة الآن في المتجر.</p>
      </div>
      <a href="<?= url('products') ?>?sort=newest" class="btn btn-outline btn-sm">عرض الكل</a>
    </div>
    <div class="products-grid">
      <?php foreach ($newArrivals as $p): ?>
        <?php include APP_PATH . '/Views/store/partials/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section" style="background:rgba(14,14,16,.58);border-top:1px solid var(--border);border-bottom:1px solid var(--border)">
  <div class="container">
    <div class="trust-grid">
      <?php foreach ([
        ['🚚','شحن سريع','توصيل لجميع محافظات مصر'],
        ['🔒','دفع آمن','بوابة دفع مشفرة وآمنة 100%'],
        ['↩️','إرجاع مجاني','إرجاع سهل خلال 14 يوم'],
        ['💬','دعم 24/7','فريق دعم متاح دائماً'],
      ] as [$icon, $title, $sub]): ?>
        <div class="trust-card">
          <div class="trust-icon"><?= $icon ?></div>
          <div class="trust-title"><?= $title ?></div>
          <div class="trust-sub"><?= $sub ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
require APP_PATH . '/Views/store/layouts/app.php';
