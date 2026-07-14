<?php
$pageTitle = e($product['name']) . ' — ' . SettingModel::get('store_name', APP_NAME);
$pageDesc  = e($product['short_desc'] ?? '');

$productModel = new ProductModel();
$images       = $product['images'] ?? [];
$variants     = $product['variants'] ?? [];
$rating       = $product['rating'] ?? ['avg' => 0, 'count' => 0];

// Related products
$related = $product['collection_id']
    ? $productModel->getByCollection($product['collection_id'], 4)
    : $productModel->getFeatured(4);
$related = array_filter($related, fn($r) => $r['id'] !== $product['id']);

// Reviews
$reviews = Database::fetchAll(
    "SELECT * FROM `reviews` WHERE product_id = ? AND is_approved = 1 ORDER BY created_at DESC LIMIT 10",
    [$product['id']]
);

$discount = 0;
if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']) {
    $discount = round((1 - $product['price'] / $product['compare_price']) * 100);
}

$allImages = [];
if ($product['thumbnail']) $allImages[] = ['image' => $product['thumbnail'], 'alt' => $product['name']];
foreach ($images as $img) $allImages[] = $img;

$extraHead = '<style>
.img-gallery{display:grid;grid-template-columns:80px 1fr;gap:12px}
.thumbs{display:flex;flex-direction:column;gap:8px}
.thumb{width:80px;height:80px;object-fit:cover;border-radius:8px;border:2px solid transparent;cursor:pointer;transition:border-color .15s;background:var(--bg3)}
.thumb:hover,.thumb.active{border-color:var(--accent)}
.main-img{width:100%;aspect-ratio:1;object-fit:cover;border-radius:var(--radius);background:var(--bg3)}
.variant-btn{
  padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;
  background:var(--surface);border:1px solid var(--border);color:var(--text2);
  cursor:pointer;transition:all .15s;
}
.variant-btn:hover,.variant-btn.selected{background:var(--accent-bg);border-color:var(--accent);color:var(--text)}
.qty-ctrl{display:flex;align-items:center;gap:0;border:1px solid var(--border);border-radius:8px;overflow:hidden}
.qty-btn{width:38px;height:38px;background:var(--surface);border:none;color:var(--text);font-size:18px;cursor:pointer;transition:background .12s}
.qty-btn:hover{background:var(--surface2)}
.qty-val{width:50px;text-align:center;background:var(--bg);border:none;color:var(--text);font-size:14px;font-weight:600;border-left:1px solid var(--border);border-right:1px solid var(--border)}
</style>';

$extraScript = "
const images = " . json_encode(array_values($allImages)) . ";
const uploadUrl = '" . APP_URL . "/uploads/';

function switchImg(idx) {
  const main = document.getElementById('mainImg');
  const img  = images[idx];
  if (img) {
    main.src = img.image.startsWith('http') ? img.image : uploadUrl + img.image;
    document.querySelectorAll('.thumb').forEach((t,i) => t.classList.toggle('active', i === idx));
  }
}

// Variant selection
let selectedVariant = null;
function selectVariant(id, price, btn) {
  selectedVariant = id;
  document.querySelectorAll('.variant-btn').forEach(b => b.classList.remove('selected'));
  btn.classList.add('selected');
  document.getElementById('currentPrice').textContent = parseFloat(price).toLocaleString('ar-EG', {minimumFractionDigits:2}) + ' ج.م';
}

// Qty
function changeQty(delta) {
  const el = document.getElementById('qtyInput');
  el.value = Math.max(1, parseInt(el.value) + delta);
}

// Add to cart
document.getElementById('addToCartBtn')?.addEventListener('click', function() {
  const qty = parseInt(document.getElementById('qtyInput').value) || 1;
  this.disabled = true;
  this.textContent = '⏳ جاري الإضافة...';
  const form = new FormData();
  form.append('product_id', " . $product['id'] . ");
  form.append('qty', qty);
  if (selectedVariant) form.append('variant_id', selectedVariant);
  form.append('_csrf', '" . csrf_token() . "');
  fetch('" . url('cart/add') . "', {method:'POST',body:form})
    .then(r=>r.json())
    .then(d=>{
      this.disabled=false;
      if(d.success){
        this.textContent='✅ تمت الإضافة!';
        updateCartBadge(d.count);
        setTimeout(()=>this.textContent='🛒 أضف للسلة',2000);
      } else {
        this.textContent='⚠️ ' + (d.message||'خطأ');
        setTimeout(()=>this.textContent='🛒 أضف للسلة',2000);
      }
    });
});
";

ob_start(); ?>

<div class="container" style="padding-top:24px;padding-bottom:80px">
  <!-- Breadcrumb -->
  <div class="breadcrumb">
    <a href="<?= url() ?>">الرئيسية</a>
    <span class="breadcrumb-sep">/</span>
    <a href="<?= url('products') ?>">المنتجات</a>
    <?php if ($product['collection_name'] ?? null): ?>
      <span class="breadcrumb-sep">/</span>
      <a href="<?= url('collections/' . ($product['collection_slug'] ?? '')) ?>"><?= e($product['collection_name']) ?></a>
    <?php endif; ?>
    <span class="breadcrumb-sep">/</span>
    <span class="breadcrumb-current"><?= e($product['name']) ?></span>
  </div>

  <!-- Product main -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:48px;margin-bottom:60px">

    <!-- Images -->
    <div class="img-gallery">
      <?php if (count($allImages) > 1): ?>
        <div class="thumbs">
          <?php foreach ($allImages as $i => $img): ?>
            <img src="<?= uploadUrl($img['image']) ?>"
                 class="thumb <?= $i===0?'active':'' ?>"
                 onclick="switchImg(<?= $i ?>)"
                 alt="<?= e($img['alt'] ?? $product['name']) ?>">
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <div>
        <?php $firstImg = $allImages[0]['image'] ?? null; ?>
        <?php if ($firstImg): ?>
          <img src="<?= uploadUrl($firstImg) ?>" class="main-img" id="mainImg" alt="<?= e($product['name']) ?>">
        <?php else: ?>
          <div class="main-img" style="display:flex;align-items:center;justify-content:center;font-size:80px">📦</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Details -->
    <div>
      <!-- Brand -->
      <?php if (!empty($product['brand_name'])): ?>
        <a href="<?= url('brands/' . slug($product['brand_name'])) ?>"
           style="font-size:11px;font-weight:700;color:var(--accent2);text-transform:uppercase;letter-spacing:1px">
          <?= e($product['brand_name']) ?>
        </a>
      <?php endif; ?>

      <h1 style="font-size:26px;font-weight:800;color:var(--text);letter-spacing:-.5px;margin:8px 0 12px;line-height:1.3">
        <?= e($product['name']) ?>
      </h1>

      <!-- Rating -->
      <?php if ($rating['count'] > 0): ?>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px">
          <div style="color:var(--gold);font-size:16px"><?= str_repeat('★', min(5, round($rating['avg']))) ?><?= str_repeat('☆', 5 - min(5, round($rating['avg']))) ?></div>
          <span style="font-size:13px;color:var(--text3)"><?= $rating['avg'] ?> (<?= $rating['count'] ?> تقييم)</span>
        </div>
      <?php endif; ?>

      <!-- Price -->
      <div style="display:flex;align-items:baseline;gap:10px;margin-bottom:20px">
        <div style="font-size:32px;font-weight:900;color:var(--text)" id="currentPrice"><?= money($product['price']) ?></div>
        <?php if ($discount > 0): ?>
          <div style="font-size:16px;color:var(--text3);text-decoration:line-through"><?= money($product['compare_price']) ?></div>
          <div class="badge badge-sale">-<?= $discount ?>%</div>
        <?php endif; ?>
      </div>

      <!-- Short desc -->
      <?php if ($product['short_desc']): ?>
        <p style="font-size:14px;color:var(--text2);line-height:1.7;margin-bottom:20px">
          <?= nl2br(e($product['short_desc'])) ?>
        </p>
      <?php endif; ?>

      <!-- Variants -->
      <?php if (!empty($variants)): ?>
        <div style="margin-bottom:20px">
          <div style="font-size:12px;font-weight:600;color:var(--text2);margin-bottom:10px">الاختيارات:</div>
          <div style="display:flex;gap:8px;flex-wrap:wrap">
            <?php foreach ($variants as $v): ?>
              <button class="variant-btn <?= $v === reset($variants) ? 'selected' : '' ?>"
                onclick="selectVariant(<?= $v['id'] ?>,'<?= $v['price'] ?>',this)">
                <?= e($v['title']) ?>
                <?php if ($v['price'] != $product['price']): ?>
                  <span style="font-size:11px;color:var(--text3)"> — <?= money($v['price']) ?></span>
                <?php endif; ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <!-- Qty + Add to cart -->
      <div style="display:flex;gap:12px;align-items:center;margin-bottom:16px;flex-wrap:wrap">
        <div class="qty-ctrl">
          <button class="qty-btn" onclick="changeQty(-1)">−</button>
          <input type="number" id="qtyInput" class="qty-val" value="1" min="1" max="99">
          <button class="qty-btn" onclick="changeQty(1)">+</button>
        </div>

        <?php $inStock = ($product['stock'] > 0 || !$product['track_stock']); ?>
        <button id="addToCartBtn" class="btn btn-primary btn-lg" style="flex:1" <?= !$inStock?'disabled':'' ?>>
          <?= $inStock ? '🛒 أضف للسلة' : '❌ نفذ من المخزون' ?>
        </button>

        <button class="btn btn-outline" style="padding:11px 14px" onclick="toggleWishlist(<?= $product['id'] ?>,this)" title="أضف للمفضلة">🤍</button>
      </div>

      <!-- Stock indicator -->
      <?php if ($product['track_stock']): ?>
        <div style="font-size:12px;margin-bottom:16px">
          <?php if ($product['stock'] > 10): ?>
            <span style="color:var(--green)">✅ متوفر في المخزون</span>
          <?php elseif ($product['stock'] > 0): ?>
            <span style="color:var(--yellow)">⚠️ آخر <?= $product['stock'] ?> قطع</span>
          <?php else: ?>
            <span style="color:var(--red)">❌ نفذ من المخزون</span>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- Trust badges -->
      <div style="border:1px solid var(--border);border-radius:var(--radius);padding:16px;background:var(--surface)">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:12px;color:var(--text2)">
          <div>🚚 شحن لجميع المحافظات</div>
          <div>↩️ إرجاع خلال 14 يوم</div>
          <div>🔒 دفع آمن 100%</div>
          <div>💵 دفع عند الاستلام</div>
        </div>
      </div>
    </div>
  </div>

  <!-- Description + Reviews tabs -->
  <div style="border-bottom:1px solid var(--border);display:flex;gap:4px;margin-bottom:32px" id="productTabs">
    <button class="btn btn-ghost tab-btn active" onclick="switchTab('desc',this)" style="border-bottom:2px solid var(--accent);border-radius:0">الوصف</button>
    <button class="btn btn-ghost tab-btn" onclick="switchTab('reviews',this)" style="border-radius:0">التقييمات (<?= count($reviews) ?>)</button>
  </div>

  <!-- Description -->
  <div id="tab-desc" style="line-height:1.9;color:var(--text2);font-size:14px;max-width:800px;margin-bottom:40px">
    <?= !empty($product['description']) ? $product['description'] : '<p style="color:var(--text3)">لا يوجد وصف متاح</p>' ?>
  </div>

  <!-- Reviews -->
  <div id="tab-reviews" style="display:none;max-width:700px;margin-bottom:40px">
    <?php if (empty($reviews)): ?>
      <div style="text-align:center;padding:40px;color:var(--text3)">
        <div style="font-size:36px;margin-bottom:12px">⭐</div>
        <div>لا توجد تقييمات بعد. كن أول من يقيّم هذا المنتج!</div>
      </div>
    <?php else: ?>
      <?php foreach ($reviews as $r): ?>
        <div style="border-bottom:1px solid var(--border);padding:16px 0">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--accent-bg);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:var(--accent2)">
              <?= mb_substr($r['name'], 0, 1) ?>
            </div>
            <div>
              <div style="font-size:13px;font-weight:600"><?= e($r['name']) ?></div>
              <div style="font-size:11px;color:var(--text3)"><?= formatDate($r['created_at']) ?></div>
            </div>
            <div style="margin-right:auto;color:var(--gold)"><?= str_repeat('★', $r['rating']) ?></div>
          </div>
          <?php if ($r['title']): ?>
            <div style="font-size:13px;font-weight:600;margin-bottom:4px"><?= e($r['title']) ?></div>
          <?php endif; ?>
          <p style="font-size:13px;color:var(--text2)"><?= e($r['body']) ?></p>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- Review form -->
    <?php if (isStoreLoggedIn()): ?>
      <div style="margin-top:24px;background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px">
        <div style="font-size:14px;font-weight:600;margin-bottom:16px">اكتب تقييمك</div>
        <form method="POST" action="<?= url('products/' . $product['slug']) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="review">
          <div class="form-group">
            <label class="form-label">التقييم</label>
            <div style="display:flex;gap:8px">
              <?php for ($i = 5; $i >= 1; $i--): ?>
                <label style="cursor:pointer;font-size:24px;color:var(--text3)" title="<?= $i ?> نجوم">
                  <input type="radio" name="rating" value="<?= $i ?>" style="display:none" required>
                  ★
                </label>
              <?php endfor; ?>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">العنوان</label>
            <input type="text" name="title" class="form-input" placeholder="ملخص رأيك">
          </div>
          <div class="form-group">
            <label class="form-label">التعليق <span>*</span></label>
            <textarea name="body" class="form-textarea" rows="4" placeholder="شاركنا تجربتك مع المنتج..." required></textarea>
          </div>
          <button type="submit" class="btn btn-primary">إرسال التقييم</button>
        </form>
      </div>
    <?php else: ?>
      <div style="text-align:center;padding:20px;color:var(--text3);font-size:13px">
        <a href="<?= url('login') ?>" style="color:var(--accent2)">سجل دخولك</a> لكتابة تقييم
      </div>
    <?php endif; ?>
  </div>

  <!-- Related products -->
  <?php if (!empty($related)): ?>
    <div>
      <h3 style="font-size:20px;font-weight:700;margin-bottom:20px">منتجات مشابهة</h3>
      <div class="products-grid">
        <?php foreach (array_slice(array_values($related), 0, 4) as $p): ?>
          <?php include APP_PATH . '/Views/store/partials/product-card.php'; ?>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<script>
function switchTab(id, btn) {
  document.querySelectorAll('[id^="tab-"]').forEach(t => t.style.display = 'none');
  document.getElementById('tab-' + id).style.display = 'block';
  document.querySelectorAll('.tab-btn').forEach(b => { b.classList.remove('active'); b.style.borderBottom = 'none'; });
  btn.classList.add('active');
  btn.style.borderBottom = '2px solid var(--accent)';
}

// Star rating hover
document.querySelectorAll('[name="rating"]').forEach((r, i, arr) => {
  r.parentElement.addEventListener('mouseover', () => {
    arr.forEach((s, j) => s.parentElement.style.color = j >= i ? 'var(--gold)' : 'var(--text3)');
  });
  r.addEventListener('change', () => {
    arr.forEach((s, j) => s.parentElement.style.color = j >= i ? 'var(--gold)' : 'var(--text3)');
  });
});
</script>

<?php
$content = ob_get_clean();
require APP_PATH . '/Views/store/layouts/app.php';
