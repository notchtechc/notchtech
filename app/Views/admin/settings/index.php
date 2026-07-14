<?php
// ──────────────────────────────────────────────
// Settings view: admin/settings/index.php
// Usage: require from AdminSettingsController
// ──────────────────────────────────────────────
$pageTitle  = 'الإعدادات';
$breadcrumb = [['label' => 'الإعدادات']];
ob_start(); ?>

<div class="page-header">
  <div class="page-header-left"><h1>إعدادات المتجر</h1></div>
</div>

<!-- Settings nav tabs -->
<div style="display:flex;gap:4px;margin-bottom:20px;border-bottom:1px solid var(--border);padding-bottom:0">
  <?php
  $tab = $_GET['tab'] ?? 'general';
  $tabs = ['general'=>'عام','payment'=>'الدفع','social'=>'السوشيال ميديا','seo'=>'SEO','hero'=>'الصفحة الرئيسية'];
  foreach ($tabs as $k=>$v): ?>
    <a href="?tab=<?= $k ?>">
      <button class="btn <?= $tab===$k?'btn-primary':'btn-secondary' ?> btn-sm" style="border-radius:8px 8px 0 0;border-bottom:none"><?= $v ?></button>
    </a>
  <?php endforeach; ?>
</div>

<form method="POST" action="<?= adminUrl('settings') ?>" enctype="multipart/form-data">
  <?= csrf_field() ?>

  <?php if ($tab === 'general'): ?>
  <div class="card">
    <div class="card-header"><span class="card-title">معلومات المتجر</span></div>
    <div class="card-body">
      <div class="form-grid form-grid-2">
        <?php $s = $settings; ?>
        <div class="form-group"><label class="form-label">اسم المتجر</label><input type="text" name="store_name" class="form-input" value="<?= e($s['store_name']??'') ?>"></div>
        <div class="form-group"><label class="form-label">البريد الإلكتروني</label><input type="email" name="store_email" class="form-input" value="<?= e($s['store_email']??'') ?>"></div>
        <div class="form-group"><label class="form-label">رقم الهاتف</label><input type="text" name="store_phone" class="form-input" value="<?= e($s['store_phone']??'') ?>"></div>
        <div class="form-group"><label class="form-label">العنوان</label><input type="text" name="store_address" class="form-input" value="<?= e($s['store_address']??'') ?>"></div>
        <div class="form-group" style="grid-column:span 2">
          <label class="form-label">وصف المتجر</label>
          <textarea name="store_description" class="form-textarea" rows="3"><?= e($s['store_description']??'') ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">شعار المتجر (Logo)</label>
          <?php if (!empty($s['store_logo'])): ?>
            <img src="<?= uploadUrl($s['store_logo']) ?>" style="height:50px;margin-bottom:8px;display:block">
          <?php endif; ?>
          <input type="file" name="store_logo" class="form-input" accept="image/*" style="padding:6px">
        </div>
        <div class="form-group">
          <label class="form-label">وضع الصيانة</label>
          <div class="toggle-wrap" style="margin-top:8px">
            <label class="toggle"><input type="checkbox" name="maintenance_mode" value="1" <?= !empty($s['maintenance_mode'])&&$s['maintenance_mode']=='1'?'checked':'' ?>>
            <span class="toggle-slider"></span></label>
            <span style="font-size:13px">تفعيل وضع الصيانة</span>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">الحد الأدنى للطلب (<?= APP_CURRENCY_SYMBOL ?>)</label>
          <input type="number" name="min_order_amount" class="form-input" value="<?= e($s['min_order_amount']??'0') ?>" min="0" step="0.01">
        </div>
      </div>
    </div>
    <div class="card-footer" style="display:flex;justify-content:flex-end">
      <button type="submit" class="btn btn-primary">💾 حفظ الإعدادات</button>
    </div>
  </div>

  <?php elseif ($tab === 'payment'): ?>
  <div class="card">
    <div class="card-header"><span class="card-title">إعدادات الدفع</span></div>
    <div class="card-body">
      <div class="form-grid" style="gap:18px">
        <div style="padding:16px;background:var(--surface2);border-radius:var(--radius);border:1px solid var(--border)">
          <div style="font-weight:600;margin-bottom:12px">💵 الدفع عند الاستلام (COD)</div>
          <div class="form-group" style="margin-bottom:10px">
            <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="cod_active" value="1" <?= ($s['cod_active']??'1')==='1'?'checked':'' ?>><span class="toggle-slider"></span></label><span style="font-size:13px">تفعيل</span></div>
          </div>
          <div class="form-group"><label class="form-label">اسم طريقة الدفع</label><input type="text" name="cod_label" class="form-input" value="<?= e($s['cod_label']??'الدفع عند الاستلام') ?>"></div>
        </div>
        <div style="padding:16px;background:var(--surface2);border-radius:var(--radius);border:1px solid var(--border)">
          <div style="font-weight:600;margin-bottom:12px">💳 فواتيرك (Fawateerk)</div>
          <div class="form-group" style="margin-bottom:10px">
            <div class="toggle-wrap"><label class="toggle"><input type="checkbox" name="fawateerk_active" value="1" <?= ($s['fawateerk_active']??'0')==='1'?'checked':'' ?>><span class="toggle-slider"></span></label><span style="font-size:13px">تفعيل</span></div>
          </div>
          <div class="form-group"><label class="form-label">API Key</label><input type="text" name="fawateerk_api_key" class="form-input" value="<?= e($s['fawateerk_api_key']??'') ?>" placeholder="Bearer token..."></div>
        </div>
      </div>
    </div>
    <div class="card-footer" style="display:flex;justify-content:flex-end">
      <button type="submit" class="btn btn-primary">💾 حفظ</button>
    </div>
  </div>

  <?php elseif ($tab === 'social'): ?>
  <div class="card">
    <div class="card-header"><span class="card-title">روابط السوشيال ميديا</span></div>
    <div class="card-body">
      <div class="form-grid form-grid-2">
        <?php foreach (['facebook'=>'Facebook','instagram'=>'Instagram','twitter'=>'X (Twitter)','youtube'=>'YouTube','tiktok'=>'TikTok'] as $k=>$l): ?>
          <div class="form-group"><label class="form-label"><?= $l ?></label><input type="url" name="social_<?= $k ?>" class="form-input" value="<?= e($s["social_{$k}"]??'') ?>" placeholder="https://"></div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="card-footer" style="display:flex;justify-content:flex-end">
      <button type="submit" class="btn btn-primary">💾 حفظ</button>
    </div>
  </div>

  <?php elseif ($tab === 'seo'): ?>
  <div class="card">
    <div class="card-header"><span class="card-title">إعدادات SEO والتتبع</span></div>
    <div class="card-body">
      <div class="form-grid" style="gap:16px">
        <div class="form-group"><label class="form-label">عنوان الموقع (Meta Title)</label><input type="text" name="meta_title" class="form-input" value="<?= e($s['meta_title']??'') ?>"></div>
        <div class="form-group"><label class="form-label">وصف الموقع (Meta Description)</label><textarea name="meta_description" class="form-textarea" rows="3"><?= e($s['meta_description']??'') ?></textarea></div>
        <div class="form-group"><label class="form-label">Google Analytics ID</label><input type="text" name="google_analytics" class="form-input" value="<?= e($s['google_analytics']??'') ?>" placeholder="G-XXXXXXXXXX"></div>
        <div class="form-group"><label class="form-label">Facebook Pixel ID</label><input type="text" name="facebook_pixel" class="form-input" value="<?= e($s['facebook_pixel']??'') ?>" placeholder="000000000000000"></div>
      </div>
    </div>
    <div class="card-footer" style="display:flex;justify-content:flex-end">
      <button type="submit" class="btn btn-primary">💾 حفظ</button>
    </div>
  </div>

  <?php elseif ($tab === 'hero'): ?>
  <div class="card">
    <div class="card-header"><span class="card-title">إعدادات الصفحة الرئيسية (Hero)</span></div>
    <div class="card-body">
      <div class="form-grid form-grid-2">
        <div class="form-group"><label class="form-label">العنوان الرئيسي</label><input type="text" name="hero_title" class="form-input" value="<?= e($s['hero_title']??'') ?>"></div>
        <div class="form-group"><label class="form-label">العنوان الفرعي</label><input type="text" name="hero_subtitle" class="form-input" value="<?= e($s['hero_subtitle']??'') ?>"></div>
        <div class="form-group"><label class="form-label">نص الزر</label><input type="text" name="hero_btn_text" class="form-input" value="<?= e($s['hero_btn_text']??'') ?>"></div>
        <div class="form-group"><label class="form-label">رابط الزر</label><input type="text" name="hero_btn_url" class="form-input" value="<?= e($s['hero_btn_url']??'') ?>"></div>
        <div class="form-group" style="grid-column:span 2">
          <label class="form-label">صورة الـ Hero</label>
          <?php if (!empty($s['hero_image'])): ?>
            <img src="<?= uploadUrl($s['hero_image']) ?>" style="height:80px;border-radius:8px;margin-bottom:8px;display:block">
          <?php endif; ?>
          <input type="file" name="hero_image" class="form-input" accept="image/*" style="padding:6px">
        </div>
      </div>
    </div>
    <div class="card-footer" style="display:flex;justify-content:flex-end">
      <button type="submit" class="btn btn-primary">💾 حفظ</button>
    </div>
  </div>
  <?php endif; ?>
</form>

<?php $content = ob_get_clean();
require APP_PATH . '/Views/admin/layouts/app.php';
