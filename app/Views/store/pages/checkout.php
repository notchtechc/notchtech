<?php
$pageTitle = 'إتمام الشراء — ' . SettingModel::get('store_name', APP_NAME);
$cartItems = Cart::get();
$summary   = Cart::summary();

if (empty($cartItems)) {
    header('Location: ' . url('cart')); exit;
}

$shippingZones = Database::fetchAll("SELECT * FROM `shipping_zones` WHERE is_active=1 ORDER BY price ASC");
$customer      = storeUser();
$govOptions    = ['القاهرة','الجيزة','الإسكندرية','القليوبية','المنوفية','الغربية','الدقهلية','الشرقية','البحيرة','كفر الشيخ','دمياط','بورسعيد','الإسماعيلية','السويس','شمال سيناء','جنوب سيناء','الفيوم','بني سويف','المنيا','أسيوط','سوهاج','قنا','الأقصر','أسوان','البحر الأحمر','مطروح','الوادي الجديد'];

ob_start(); ?>

<div class="container-sm" style="padding-top:24px;padding-bottom:80px">
  <div class="breadcrumb">
    <a href="<?= url() ?>">الرئيسية</a>
    <span class="breadcrumb-sep">/</span>
    <a href="<?= url('cart') ?>">السلة</a>
    <span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-current">إتمام الشراء</span>
  </div>

  <h1 style="font-size:24px;font-weight:800;margin-bottom:28px">إتمام الشراء</h1>

  <form method="POST" action="<?= url('checkout') ?>" id="checkoutForm">
    <?= csrf_field() ?>

    <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start">

      <!-- Left: Form -->
      <div style="display:flex;flex-direction:column;gap:18px">

        <!-- Contact info -->
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:22px">
          <div style="font-size:15px;font-weight:700;margin-bottom:18px">معلومات الاتصال</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div class="form-group">
              <label class="form-label">الاسم الكامل <span>*</span></label>
              <input type="text" name="customer_name" class="form-input" value="<?= e($customer['name'] ?? '') ?>" required placeholder="محمد أحمد">
            </div>
            <div class="form-group">
              <label class="form-label">رقم الهاتف <span>*</span></label>
              <input type="tel" name="customer_phone" class="form-input" value="<?= e($customer['phone'] ?? '') ?>" required placeholder="01xxxxxxxxx">
            </div>
            <div class="form-group" style="grid-column:span 2">
              <label class="form-label">البريد الإلكتروني <span>*</span></label>
              <input type="email" name="customer_email" class="form-input" value="<?= e($customer['email'] ?? '') ?>" required placeholder="email@example.com">
            </div>
          </div>
        </div>

        <!-- Shipping -->
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:22px">
          <div style="font-size:15px;font-weight:700;margin-bottom:18px">عنوان الشحن</div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
            <div class="form-group">
              <label class="form-label">المحافظة <span>*</span></label>
              <select name="shipping_gov" class="form-select" required onchange="updateShipping(this.value)">
                <option value="">اختر المحافظة</option>
                <?php foreach ($govOptions as $gov): ?>
                  <option value="<?= e($gov) ?>"><?= e($gov) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">المدينة / المنطقة <span>*</span></label>
              <input type="text" name="shipping_city" class="form-input" required placeholder="المدينة">
            </div>
            <div class="form-group" style="grid-column:span 2">
              <label class="form-label">العنوان التفصيلي <span>*</span></label>
              <textarea name="shipping_address" class="form-textarea" rows="3" required placeholder="الشارع، المبنى، الشقة..."></textarea>
            </div>
          </div>
        </div>

        <!-- Payment -->
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:22px">
          <div style="font-size:15px;font-weight:700;margin-bottom:18px">طريقة الدفع</div>
          <div style="display:flex;flex-direction:column;gap:10px">
            <?php if (SettingModel::get('cod_active', '1') === '1'): ?>
              <label style="display:flex;align-items:center;gap:12px;padding:14px;border:1px solid var(--border);border-radius:9px;cursor:pointer;transition:border-color .15s" id="pay-cod">
                <input type="radio" name="payment_method" value="cod" required onchange="updatePayLabel(this)" style="accent-color:var(--accent)">
                <div style="font-size:24px">💵</div>
                <div>
                  <div style="font-size:13px;font-weight:600"><?= e(SettingModel::get('cod_label', 'الدفع عند الاستلام')) ?></div>
                  <div style="font-size:11px;color:var(--text3)">ادفع نقداً عند استلام الطلب</div>
                </div>
              </label>
            <?php endif; ?>
            <?php if (SettingModel::get('fawateerk_active', '0') === '1'): ?>
              <label style="display:flex;align-items:center;gap:12px;padding:14px;border:1px solid var(--border);border-radius:9px;cursor:pointer;transition:border-color .15s" id="pay-fawateerk">
                <input type="radio" name="payment_method" value="fawateerk" onchange="updatePayLabel(this)" style="accent-color:var(--accent)">
                <div style="font-size:24px">💳</div>
                <div>
                  <div style="font-size:13px;font-weight:600">بطاقة ائتمانية / فيزا</div>
                  <div style="font-size:11px;color:var(--text3)">دفع إلكتروني آمن عبر فواتيرك</div>
                </div>
              </label>
            <?php endif; ?>
          </div>
        </div>

        <!-- Notes -->
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:22px">
          <label class="form-label">ملاحظات (اختياري)</label>
          <textarea name="notes" class="form-textarea" rows="2" placeholder="أي تعليمات خاصة للطلب..."></textarea>
        </div>
      </div>

      <!-- Right: Order summary -->
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:22px;position:sticky;top:80px">
        <div style="font-size:15px;font-weight:700;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border)">
          ملخص الطلب (<?= Cart::count() ?> منتج)
        </div>

        <!-- Items -->
        <?php foreach ($cartItems as $item): ?>
          <div style="display:flex;gap:10px;margin-bottom:12px;align-items:center">
            <?php if ($item['image']): ?>
              <img src="<?= uploadUrl($item['image']) ?>" style="width:44px;height:44px;object-fit:cover;border-radius:7px;border:1px solid var(--border);flex-shrink:0">
            <?php else: ?>
              <div style="width:44px;height:44px;border-radius:7px;background:var(--bg3);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0">📦</div>
            <?php endif; ?>
            <div style="flex:1;min-width:0">
              <div style="font-size:12px;font-weight:500;line-height:1.3"><?= e($item['name']) ?></div>
              <?php if ($item['variant']): ?><div style="font-size:10px;color:var(--text3)"><?= e($item['variant']) ?></div><?php endif; ?>
              <div style="font-size:11px;color:var(--text3)">× <?= $item['qty'] ?></div>
            </div>
            <div style="font-size:13px;font-weight:700;flex-shrink:0"><?= money($item['price'] * $item['qty']) ?></div>
          </div>
        <?php endforeach; ?>

        <div style="border-top:1px solid var(--border);padding-top:12px;margin-top:4px;display:flex;flex-direction:column;gap:8px;font-size:13px">
          <div style="display:flex;justify-content:space-between;color:var(--text2)">
            <span>المجموع الفرعي</span><span><?= money($summary['subtotal']) ?></span>
          </div>
          <?php if ($summary['discount_amount'] > 0): ?>
            <div style="display:flex;justify-content:space-between;color:var(--green)">
              <span>خصم</span><span>- <?= money($summary['discount_amount']) ?></span>
            </div>
          <?php endif; ?>
          <div style="display:flex;justify-content:space-between;color:var(--text2)">
            <span>الشحن</span>
            <span id="shippingDisplay">يُحدد عند اختيار المحافظة</span>
          </div>
          <input type="hidden" name="shipping_price" id="shippingPrice" value="0">
          <div style="display:flex;justify-content:space-between;font-size:17px;font-weight:800;padding-top:10px;border-top:1px solid var(--border)">
            <span>الإجمالي</span>
            <span id="totalDisplay"><?= money($summary['total']) ?></span>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-full" style="margin-top:18px;font-size:15px" id="submitBtn">
          تأكيد الطلب →
        </button>
        <p style="font-size:11px;color:var(--text3);text-align:center;margin-top:10px">🔒 دفع آمن ومشفر</p>
      </div>

    </div>
  </form>
</div>

<script>
const zones = <?= json_encode($shippingZones, JSON_UNESCAPED_UNICODE) ?>;
const subtotal = <?= $summary['subtotal'] - $summary['discount_amount'] ?>;

function updateShipping(gov) {
  let price = 0, label = 'غير متاح';
  for (const z of zones) {
    const govs = JSON.parse(z.governorates || '[]');
    if (govs.length === 0 || govs.includes(gov)) {
      price = parseFloat(z.price);
      label = price === 0 ? 'مجاني' : price.toLocaleString('ar-EG', {minimumFractionDigits:2}) + ' ج.م';
      if (z.free_above && subtotal >= parseFloat(z.free_above)) {
        price = 0; label = 'مجاني (فوق ' + parseFloat(z.free_above).toLocaleString('ar-EG') + ' ج.م)';
      }
      break;
    }
  }
  document.getElementById('shippingDisplay').textContent = label;
  document.getElementById('shippingPrice').value = price;
  const total = subtotal + price;
  document.getElementById('totalDisplay').textContent = total.toLocaleString('ar-EG', {minimumFractionDigits:2}) + ' ج.م';
}

function updatePayLabel(radio) {
  document.querySelectorAll('[id^="pay-"]').forEach(el => el.style.borderColor = 'var(--border)');
  const el = document.getElementById('pay-' + radio.value);
  if (el) el.style.borderColor = 'var(--accent)';
}

document.getElementById('checkoutForm').addEventListener('submit', function(e) {
  const btn = document.getElementById('submitBtn');
  btn.disabled = true;
  btn.textContent = '⏳ جاري المعالجة...';
});
</script>

<?php $content = ob_get_clean();
require APP_PATH . '/Views/store/layouts/app.php';
