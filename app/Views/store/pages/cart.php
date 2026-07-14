<?php
$pageTitle = 'السلة — ' . SettingModel::get('store_name', APP_NAME);
$cartItems = Cart::get();
$summary   = Cart::summary();
ob_start(); ?>

<div class="container" style="padding-top:24px;padding-bottom:80px">
  <div class="breadcrumb">
    <a href="<?= url() ?>">الرئيسية</a>
    <span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-current">السلة</span>
  </div>

  <h1 style="font-size:24px;font-weight:800;margin-bottom:28px">سلة التسوق</h1>

  <?php if (empty($cartItems)): ?>
    <div style="text-align:center;padding:80px 20px">
      <div style="font-size:64px;margin-bottom:20px">🛒</div>
      <div style="font-size:20px;font-weight:700;margin-bottom:8px">السلة فارغة</div>
      <p style="color:var(--text3);margin-bottom:24px">لم تضف أي منتجات بعد</p>
      <a href="<?= url('products') ?>" class="btn btn-primary btn-lg">تسوق الآن</a>
    </div>
  <?php else: ?>
    <div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start">

      <!-- Cart items -->
      <div style="display:flex;flex-direction:column;gap:0;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden">
        <?php foreach ($cartItems as $key => $item): ?>
          <div style="display:flex;gap:16px;padding:18px 20px;border-bottom:1px solid var(--border);align-items:center" id="cart-<?= e($key) ?>">
            <a href="<?= url('products/' . slug($item['name'])) ?>">
              <?php if ($item['image']): ?>
                <img src="<?= uploadUrl($item['image']) ?>" style="width:72px;height:72px;object-fit:cover;border-radius:9px;border:1px solid var(--border);flex-shrink:0">
              <?php else: ?>
                <div style="width:72px;height:72px;border-radius:9px;background:var(--bg3);display:flex;align-items:center;justify-content:center;font-size:32px;flex-shrink:0">📦</div>
              <?php endif; ?>
            </a>
            <div style="flex:1;min-width:0">
              <div style="font-size:14px;font-weight:600;margin-bottom:3px"><?= e($item['name']) ?></div>
              <?php if ($item['variant']): ?>
                <div style="font-size:12px;color:var(--text3)">الاختيار: <?= e($item['variant']) ?></div>
              <?php endif; ?>
              <?php if ($item['sku']): ?>
                <div style="font-size:11px;color:var(--text3)">SKU: <?= e($item['sku']) ?></div>
              <?php endif; ?>
            </div>
            <!-- Qty control -->
            <div style="display:flex;align-items:center;gap:0;border:1px solid var(--border);border-radius:7px;overflow:hidden">
              <button onclick="updateItem('<?= e($key) ?>',<?= $item['qty'] - 1 ?>)" style="width:32px;height:32px;background:var(--surface2);border:none;color:var(--text);font-size:16px;cursor:pointer">−</button>
              <span style="width:36px;text-align:center;font-size:13px;font-weight:600;background:var(--bg)"><?= $item['qty'] ?></span>
              <button onclick="updateItem('<?= e($key) ?>',<?= $item['qty'] + 1 ?>)" style="width:32px;height:32px;background:var(--surface2);border:none;color:var(--text);font-size:16px;cursor:pointer">+</button>
            </div>
            <div style="font-size:15px;font-weight:800;min-width:90px;text-align:center"><?= money($item['price'] * $item['qty']) ?></div>
            <button onclick="removeItem('<?= e($key) ?>')" style="background:none;border:none;color:var(--text3);font-size:18px;cursor:pointer;padding:4px" title="حذف">🗑️</button>
          </div>
        <?php endforeach; ?>

        <!-- Clear cart -->
        <div style="padding:14px 20px;display:flex;justify-content:flex-end">
          <form method="POST" action="<?= url('cart/clear') ?>">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-ghost btn-sm" style="color:var(--text3)" onclick="return confirm('مسح السلة كلها؟')">× مسح السلة</button>
          </form>
        </div>
      </div>

      <!-- Summary -->
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:22px;position:sticky;top:80px">
        <div style="font-size:16px;font-weight:700;margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid var(--border)">ملخص الطلب</div>

        <!-- Discount code -->
        <form id="discountForm" style="display:flex;gap:8px;margin-bottom:18px">
          <?= csrf_field() ?>
          <input type="text" name="code" id="discountCode" class="form-input" placeholder="كود الخصم" style="flex:1"
            value="<?= e(Cart::getDiscount()['code']) ?>">
          <button type="button" onclick="applyDiscount()" class="btn btn-outline btn-sm">تطبيق</button>
        </form>
        <div id="discountMsg" style="font-size:12px;margin-bottom:14px"></div>

        <div style="display:flex;flex-direction:column;gap:10px;font-size:13px">
          <div style="display:flex;justify-content:space-between;color:var(--text2)">
            <span>المجموع الفرعي</span>
            <span id="subtotalDisplay"><?= money($summary['subtotal']) ?></span>
          </div>
          <?php if ($summary['discount_amount'] > 0): ?>
            <div style="display:flex;justify-content:space-between;color:var(--green)">
              <span>خصم (<?= e($summary['discount_code']) ?>)</span>
              <span>- <?= money($summary['discount_amount']) ?></span>
            </div>
          <?php endif; ?>
          <div style="display:flex;justify-content:space-between;color:var(--text2)">
            <span>الشحن</span>
            <span>يُحدد عند الدفع</span>
          </div>
          <div style="display:flex;justify-content:space-between;font-size:18px;font-weight:800;padding-top:12px;border-top:1px solid var(--border)">
            <span>الإجمالي</span>
            <span><?= money($summary['total']) ?></span>
          </div>
        </div>

        <a href="<?= url('checkout') ?>" class="btn btn-primary btn-full" style="margin-top:18px;font-size:15px">
          إتمام الشراء →
        </a>
        <a href="<?= url('products') ?>" class="btn btn-ghost btn-full btn-sm" style="margin-top:8px">← متابعة التسوق</a>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
function updateItem(key, qty) {
  const form = new FormData();
  form.append('key', key); form.append('qty', qty);
  form.append('_csrf', '<?= csrf_token() ?>');
  fetch('<?= url('cart/update') ?>', {method:'POST',body:form})
    .then(r=>r.json()).then(d=>{ if(d.success) location.reload(); });
}
function removeItem(key) {
  const form = new FormData();
  form.append('key', key);
  form.append('_csrf', '<?= csrf_token() ?>');
  fetch('<?= url('cart/remove') ?>', {method:'POST',body:form})
    .then(r=>r.json()).then(d=>{ if(d.success){ updateCartBadge(d.count); document.getElementById('cart-'+key)?.remove(); location.reload(); } });
}
function applyDiscount() {
  const code = document.getElementById('discountCode').value.trim();
  if (!code) return;
  const form = new FormData();
  form.append('code', code); form.append('_csrf', '<?= csrf_token() ?>');
  fetch('<?= url('cart/discount') ?>', {method:'POST',body:form})
    .then(r=>r.json()).then(d=>{
      const el = document.getElementById('discountMsg');
      el.style.color = d.success ? 'var(--green)' : 'var(--red)';
      el.textContent = (d.success ? '✅ ' : '❌ ') + d.message;
      if (d.success) setTimeout(()=>location.reload(), 800);
    });
}
</script>

<?php $content = ob_get_clean();
require APP_PATH . '/Views/store/layouts/app.php';
