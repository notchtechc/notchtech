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

ob_start(); ?>

<!-- ══════════════════════════════════
     HERO SECTION
══════════════════════════════════ -->
<section style="position:relative;overflow:hidden;min-height:580px;display:flex;align-items:center">
  <!-- Background -->
  <div style="position:absolute;inset:0;background:linear-gradient(135deg,#0a0a12 0%,#0e0a1a 50%,#080812 100%)"></div>
  <!-- Grid pattern -->
  <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(109,90,205,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(109,90,205,.04) 1px,transparent 1px);background-size:40px 40px;"></div>
  <!-- Glow -->
  <div style="position:absolute;top:-100px;right:-100px;width:600px;height:600px;background:radial-gradient(circle,rgba(109,90,205,.2) 0%,transparent 65%);pointer-events:none"></div>
  <div style="position:absolute;bottom:-100px;left:-100px;width:400px;height:400px;background:radial-gradient(circle,rgba(109,90,205,.1) 0%,transparent 65%);pointer-events:none"></div>

  <div class="container" style="position:relative;z-index:1;padding-top:60px;padding-bottom:60px">
    <div style="max-width:640px">
      <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(109,90,205,.12);border:1px solid rgba(109,90,205,.25);border-radius:20px;padding:5px 14px;font-size:12px;color:#8b75e8;font-weight:600;margin-bottom:20px">
        <span style="width:6px;height:6px;border-radius:50%;background:#6d5acd;animation:pulse 2s infinite"></span>
        <?= e(SettingModel::get('store_description', 'متجر الإلكترونيات الرائد')) ?>
      </div>
      <h1 style="font-size:clamp(36px,5vw,60px);font-weight:900;color:#fff;letter-spacing:-2px;line-height:1.1;margin-bottom:16px">
        <?= e(SettingModel::get('hero_title', 'أحدث التقنيات بين يديك')) ?>
      </h1>
      <p style="font-size:17px;color:var(--text2);line-height:1.7;margin-bottom:32px;max-width:500px">
        <?= e(SettingModel::get('hero_subtitle', 'تسوق أفضل المنتجات الإلكترونية من أشهر الماركات العالمية')) ?>
      </p>
      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <a href="<?= url(ltrim(SettingModel::get('hero_btn_url', 'products'), '/')) ?>" class="btn btn-primary btn-lg">
          <?= e(SettingModel::get('hero_btn_text', 'تسوق الآن')) ?>
          <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="m9 18 6-6-6-6"/></svg>
        </a>
        <a href="<?= url('collections') ?>" class="btn btn-outline btn-lg">استكشف التصنيفات</a>
      </div>

      <!-- Stats -->
      <div style="display:flex;gap:32px;margin-top:48px;padding-top:32px;border-top:1px solid rgba(255,255,255,.06)">
        <?php
        $totalProducts  = Database::fetch("SELECT COUNT(*) as c FROM products WHERE status='active'")['c'] ?? 0;
        $totalOrders    = Database::fetch("SELECT COUNT(*) as c FROM orders WHERE status='delivered'")['c'] ?? 0;
        $totalCustomers = Database::fetch("SELECT COUNT(*) as c FROM customers")['c'] ?? 0;
        ?>
        <div>
          <div style="font-size:24px;font-weight:800;color:#fff"><?= number_format($totalProducts) ?>+</div>
          <div style="font-size:12px;color:var(--text3)">منتج متاح</div>
        </div>
        <div>
          <div style="font-size:24px;font-weight:800;color:#fff"><?= number_format($totalOrders) ?>+</div>
          <div style="font-size:12px;color:var(--text3)">طلب مكتمل</div>
        </div>
        <div>
          <div style="font-size:24px;font-weight:800;color:#fff"><?= number_format($totalCustomers) ?>+</div>
          <div style="font-size:12px;color:var(--text3)">عميل سعيد</div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}</style>

<!-- ══════════════════════════════════
     BRANDS
══════════════════════════════════ -->
<?php if (!empty($brands)): ?>
<div style="background:var(--bg2);border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:24px 0">
  <div class="container">
    <div style="display:flex;align-items:center;justify-content:center;gap:40px;flex-wrap:wrap">
      <?php foreach ($brands as $b): ?>
        <a href="<?= url('brands/' . $b['slug']) ?>" style="opacity:.5;transition:opacity .2s;filter:grayscale(1)" onmouseover="this.style.opacity=1;this.style.filter='none'" onmouseout="this.style.opacity='.5';this.style.filter='grayscale(1)'">
          <?php if ($b['logo']): ?>
            <img src="<?= uploadUrl($b['logo']) ?>" alt="<?= e($b['name']) ?>" style="height:28px;width:auto;object-fit:contain">
          <?php else: ?>
            <span style="font-size:14px;font-weight:800;color:var(--text2);letter-spacing:.5px"><?= e($b['name']) ?></span>
          <?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ══════════════════════════════════
     COLLECTIONS
══════════════════════════════════ -->
<?php if (!empty($collections)): ?>
<section class="section">
  <div class="container">
    <div class="section-header" style="display:flex;align-items:flex-end;justify-content:space-between">
      <div>
        <div class="section-label">🗂️ التصنيفات</div>
        <h2 class="section-title">تسوق حسب الفئة</h2>
      </div>
      <a href="<?= url('products') ?>" class="btn btn-outline btn-sm">عرض الكل</a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px">
      <?php foreach (array_slice($collections, 0, 6) as $c): ?>
        <a href="<?= url('collections/' . $c['slug']) ?>"
           style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px;text-align:center;transition:all .2s;display:block"
           onmouseover="this.style.borderColor='var(--accent)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.transform='none'">
          <?php if ($c['image']): ?>
            <img src="<?= uploadUrl($c['image']) ?>" alt="<?= e($c['name']) ?>" style="width:56px;height:56px;object-fit:cover;border-radius:10px;margin:0 auto 10px">
          <?php else: ?>
            <div style="width:56px;height:56px;background:var(--accent-bg);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:24px;margin:0 auto 10px">🗂️</div>
          <?php endif; ?>
          <div style="font-size:13px;font-weight:600;color:var(--text);margin-bottom:3px"><?= e($c['name']) ?></div>
          <div style="font-size:11px;color:var(--text3)"><?= $c['product_count'] ?> منتج</div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════
     FEATURED PRODUCTS
══════════════════════════════════ -->
<?php if (!empty($featured)): ?>
<section class="section" style="padding-top:0">
  <div class="container">
    <div class="section-header" style="display:flex;align-items:flex-end;justify-content:space-between">
      <div>
        <div class="section-label">⭐ مميز</div>
        <h2 class="section-title">المنتجات المميزة</h2>
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

<!-- ══════════════════════════════════
     NEW ARRIVALS
══════════════════════════════════ -->
<?php if (!empty($newArrivals)): ?>
<section class="section" style="padding-top:0">
  <div class="container">
    <div class="section-header" style="display:flex;align-items:flex-end;justify-content:space-between">
      <div>
        <div class="section-label">🆕 جديد</div>
        <h2 class="section-title">وصل حديثاً</h2>
      </div>
      <a href="<?= url('products') ?>" class="btn btn-outline btn-sm">عرض الكل</a>
    </div>
    <div class="products-grid">
      <?php foreach ($newArrivals as $p): ?>
        <?php include APP_PATH . '/Views/store/partials/product-card.php'; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════
     TRUST BADGES
══════════════════════════════════ -->
<section style="background:var(--bg2);border-top:1px solid var(--border);padding:48px 0">
  <div class="container">
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:24px">
      <?php foreach ([
        ['🚚','شحن سريع','توصيل لجميع محافظات مصر'],
        ['🔒','دفع آمن','بوابة دفع مشفرة وآمنة 100%'],
        ['↩️','إرجاع مجاني','إرجاع سهل خلال 14 يوم'],
        ['💬','دعم 24/7','فريق دعم متاح دائماً'],
      ] as [$icon, $title, $sub]): ?>
        <div style="text-align:center;padding:20px">
          <div style="font-size:32px;margin-bottom:10px"><?= $icon ?></div>
          <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:4px"><?= $title ?></div>
          <div style="font-size:12px;color:var(--text3)"><?= $sub ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php
$content = ob_get_clean();
require APP_PATH . '/Views/store/layouts/app.php';
