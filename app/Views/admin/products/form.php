<?php
$isEdit     = isset($product);
$pageTitle  = $isEdit ? 'تعديل المنتج' : 'إضافة منتج جديد';
$breadcrumb = [['label' => 'المنتجات', 'url' => adminUrl('products')], ['label' => $pageTitle]];
$v          = $product ?? [];

$extraScript = "
document.getElementById('nameInput')?.addEventListener('input', function() {
  const slugEl = document.getElementById('slugInput');
  if (!slugEl.dataset.manual) slugEl.value = makeSlug(this.value);
});
document.getElementById('slugInput')?.addEventListener('input', function() {
  this.dataset.manual = '1';
});
";

ob_start(); ?>

<div class="page-header">
  <div class="page-header-left">
    <h1><?= $pageTitle ?></h1>
    <?php if ($isEdit): ?>
      <p>آخر تحديث: <?= formatDateTime($product['updated_at']) ?></p>
    <?php endif; ?>
  </div>
  <div class="page-header-actions">
    <a href="<?= adminUrl('products') ?>" class="btn btn-secondary">← رجوع</a>
    <?php if ($isEdit): ?>
      <a href="<?= url('products/' . $product['slug']) ?>" target="_blank" class="btn btn-secondary">عرض في المتجر</a>
    <?php endif; ?>
  </div>
</div>

<form method="POST" enctype="multipart/form-data"
  action="<?= $isEdit ? adminUrl('products/' . $product['id'] . '/edit') : adminUrl('products/create') ?>">
  <?= csrf_field() ?>

  <div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start">

    <!-- Right: Main info -->
    <div style="display:flex;flex-direction:column;gap:18px">

      <!-- Basic Info -->
      <div class="card">
        <div class="card-header"><span class="card-title">معلومات المنتج</span></div>
        <div class="card-body">
          <div class="form-grid" style="gap:16px">
            <div class="form-group">
              <label class="form-label">اسم المنتج <span>*</span></label>
              <input type="text" id="nameInput" name="name" class="form-input" value="<?= e($v['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label class="form-label">Slug (رابط المنتج)</label>
              <input type="text" id="slugInput" name="slug" class="form-input" value="<?= e($v['slug'] ?? '') ?>" placeholder="auto-generated">
              <span class="form-hint">يُستخدم في الرابط: <?= url('products/') ?><strong id="slugPreview"><?= e($v['slug'] ?? '') ?></strong></span>
            </div>
            <div class="form-group">
              <label class="form-label">وصف مختصر</label>
              <textarea name="short_desc" class="form-textarea" rows="2" style="min-height:70px"><?= e($v['short_desc'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
              <label class="form-label">الوصف الكامل</label>
              <textarea name="description" class="form-textarea" rows="8"><?= e($v['description'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>

      <!-- Images -->
      <div class="card">
        <div class="card-header"><span class="card-title">الصور</span></div>
        <div class="card-body">
          <!-- Existing images -->
          <?php if (!empty($product['images'])): ?>
          <div id="existingImages" style="display:flex;flex-wrap:wrap;gap:10px;margin-bottom:16px">
            <?php foreach ($product['images'] as $img): ?>
            <div style="position:relative;width:90px;height:90px" id="img-<?= $img['id'] ?>">
              <img src="<?= uploadUrl($img['image']) ?>" style="width:90px;height:90px;object-fit:cover;border-radius:8px;border:1px solid var(--border)">
              <button type="button"
                onclick="deleteImage(<?= $img['id'] ?>, <?= $product['id'] ?>)"
                style="position:absolute;top:-6px;left:-6px;width:20px;height:20px;background:var(--red);border:none;border-radius:50%;color:#fff;font-size:12px;cursor:pointer;display:flex;align-items:center;justify-content:center">×</button>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <!-- Thumbnail -->
          <div class="form-group" style="margin-bottom:12px">
            <label class="form-label">الصورة الرئيسية</label>
            <?php if (!empty($product['thumbnail'])): ?>
              <img src="<?= uploadUrl($product['thumbnail']) ?>" style="width:80px;height:80px;object-fit:cover;border-radius:8px;margin-bottom:8px;display:block">
            <?php endif; ?>
            <input type="file" name="thumbnail" class="form-input" accept="image/*" style="padding:6px">
          </div>

          <!-- Extra images -->
          <div class="form-group">
            <label class="form-label">صور إضافية</label>
            <input type="file" name="images[]" class="form-input" accept="image/*" multiple style="padding:6px">
            <span class="form-hint">يمكنك رفع عدة صور مرة واحدة</span>
          </div>
        </div>
      </div>

      <!-- SEO -->
      <div class="card">
        <div class="card-header"><span class="card-title">SEO</span></div>
        <div class="card-body">
          <div class="form-grid" style="gap:14px">
            <div class="form-group">
              <label class="form-label">عنوان الصفحة (Meta Title)</label>
              <input type="text" name="meta_title" class="form-input" value="<?= e($v['meta_title'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">وصف الصفحة (Meta Description)</label>
              <textarea name="meta_desc" class="form-textarea" rows="2" style="min-height:70px"><?= e($v['meta_desc'] ?? '') ?></textarea>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Left: Sidebar panels -->
    <div style="display:flex;flex-direction:column;gap:16px">

      <!-- Status & Visibility -->
      <div class="card">
        <div class="card-header"><span class="card-title">النشر</span></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:14px">
          <div class="form-group">
            <label class="form-label">الحالة</label>
            <select name="status" class="form-select">
              <option value="active"   <?= ($v['status']??'draft')==='active'  ?'selected':'' ?>>✅ نشط</option>
              <option value="draft"    <?= ($v['status']??'draft')==='draft'   ?'selected':'' ?>>📝 مسودة</option>
              <option value="archived" <?= ($v['status']??'')==='archived'     ?'selected':'' ?>>📦 مؤرشف</option>
            </select>
          </div>
          <div class="toggle-wrap">
            <label class="toggle">
              <input type="checkbox" name="is_featured" value="1" <?= !empty($v['is_featured']) ? 'checked' : '' ?>>
              <span class="toggle-slider"></span>
            </label>
            <span style="font-size:13px">منتج مميز (Featured)</span>
          </div>
          <div class="form-group">
            <label class="form-label">ترتيب العرض</label>
            <input type="number" name="sort_order" class="form-input" value="<?= e($v['sort_order'] ?? 0) ?>" min="0">
          </div>
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
            <?= $isEdit ? '💾 حفظ التعديلات' : '+ إضافة المنتج' ?>
          </button>
        </div>
      </div>

      <!-- Pricing -->
      <div class="card">
        <div class="card-header"><span class="card-title">الأسعار</span></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
          <div class="form-group">
            <label class="form-label">السعر (<?= APP_CURRENCY_SYMBOL ?>) <span>*</span></label>
            <input type="number" name="price" class="form-input" value="<?= e($v['price'] ?? '') ?>" step="0.01" min="0" required>
          </div>
          <div class="form-group">
            <label class="form-label">السعر قبل الخصم</label>
            <input type="number" name="compare_price" class="form-input" value="<?= e($v['compare_price'] ?? '') ?>" step="0.01" min="0">
            <span class="form-hint">أعلى من السعر الحالي لإظهار خصم</span>
          </div>
          <div class="form-group">
            <label class="form-label">سعر التكلفة</label>
            <input type="number" name="cost_price" class="form-input" value="<?= e($v['cost_price'] ?? '') ?>" step="0.01" min="0">
            <span class="form-hint">لحساب الربح (لا يُعرض للعميل)</span>
          </div>
        </div>
      </div>

      <!-- Inventory -->
      <div class="card">
        <div class="card-header"><span class="card-title">المخزون</span></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
          <div class="toggle-wrap">
            <label class="toggle">
              <input type="checkbox" name="track_stock" value="1" <?= ($v['track_stock']??1) ? 'checked' : '' ?> id="trackStock">
              <span class="toggle-slider"></span>
            </label>
            <span style="font-size:13px">تتبع المخزون</span>
          </div>
          <div class="form-group">
            <label class="form-label">الكمية</label>
            <input type="number" name="stock" class="form-input" value="<?= e($v['stock'] ?? 0) ?>" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">SKU</label>
            <input type="text" name="sku" class="form-input" value="<?= e($v['sku'] ?? '') ?>" placeholder="اختياري">
          </div>
          <div class="toggle-wrap">
            <label class="toggle">
              <input type="checkbox" name="allow_backorder" value="1" <?= !empty($v['allow_backorder']) ? 'checked' : '' ?>>
              <span class="toggle-slider"></span>
            </label>
            <span style="font-size:13px">السماح بالطلب عند نفاد المخزون</span>
          </div>
          <div class="form-group">
            <label class="form-label">الوزن (كجم)</label>
            <input type="number" name="weight" class="form-input" value="<?= e($v['weight'] ?? '') ?>" step="0.001" min="0">
          </div>
        </div>
      </div>

      <!-- Classification -->
      <div class="card">
        <div class="card-header"><span class="card-title">التصنيف</span></div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:12px">
          <div class="form-group">
            <label class="form-label">التصنيف</label>
            <select name="collection_id" class="form-select">
              <option value="">بدون تصنيف</option>
              <?php foreach ($collections as $c): ?>
                <option value="<?= $c['id'] ?>" <?= ($v['collection_id']??'')==$c['id'] ?'selected':'' ?>><?= e($c['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">الماركة</label>
            <select name="brand_id" class="form-select">
              <option value="">بدون ماركة</option>
              <?php foreach ($brands as $b): ?>
                <option value="<?= $b['id'] ?>" <?= ($v['brand_id']??'')==$b['id'] ?'selected':'' ?>><?= e($b['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
      </div>

    </div><!-- /sidebar -->
  </div>
</form>

<script>
// Update slug preview
document.getElementById('slugInput')?.addEventListener('input', function() {
  document.getElementById('slugPreview').textContent = this.value;
});

// Delete image via AJAX
function deleteImage(imageId, productId) {
  if (!confirm('حذف هذه الصورة؟')) return;
  const form = new FormData();
  form.append('image_id', imageId);
  form.append('_csrf', '<?= csrf_token() ?>');
  fetch('<?= adminUrl('products') ?>/' + productId + '/image-delete', { method:'POST', body: form })
    .then(r => r.json())
    .then(d => { if (d.success) document.getElementById('img-' + imageId)?.remove(); });
}
</script>

<?php
$content = ob_get_clean();
require APP_PATH . '/Views/admin/layouts/app.php';
