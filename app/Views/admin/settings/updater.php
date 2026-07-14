<?php
$pageTitle  = 'التحديثات';
$breadcrumb = [['label' => 'التحديثات']];
ob_start(); ?>

<div class="page-header">
  <div class="page-header-left">
    <h1>مدير التحديثات</h1>
    <p>الإصدار الحالي: <strong><?= VERSION ?></strong></p>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start">

  <div style="display:flex;flex-direction:column;gap:16px">
    <!-- Available packages -->
    <div class="card">
      <div class="card-header"><span class="card-title">حزم التحديث المتاحة</span></div>
      <?php if (empty($packages)): ?>
        <div style="padding:40px;text-align:center;color:var(--text-3)">لا توجد حزم مرفوعة. ارفع ملف ZIP من الجانب الأيمن.</div>
      <?php else: foreach ($packages as $pkg): ?>
        <div style="padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px">
          <div style="font-size:24px">📦</div>
          <div style="flex:1">
            <div style="font-weight:600;font-family:monospace"><?= e($pkg['name']) ?></div>
            <div style="font-size:12px;color:var(--text-3)"><?= $pkg['size'] ?> — <?= $pkg['modified'] ?></div>
          </div>
          <div style="display:flex;gap:6px">
            <form method="POST" action="<?= adminUrl('updater/apply/' . urlencode($pkg['name'])) ?>"><?= csrf_field() ?>
              <button class="btn btn-primary btn-sm" onclick="return confirm('تطبيق التحديث <?= e($pkg['name']) ?>؟')">⬆️ تطبيق</button>
            </form>
            <?php if ($pkg['hasBackup']): ?>
              <form method="POST" action="<?= adminUrl('updater/rollback/' . urlencode($pkg['name'])) ?>"><?= csrf_field() ?>
                <button class="btn btn-secondary btn-sm">↩️ استعادة</button>
              </form>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; endif; ?>
    </div>

    <!-- History -->
    <div class="card">
      <div class="card-header"><span class="card-title">سجل التحديثات</span></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>الإصدار</th><th>الوصف</th><th>التاريخ</th></tr></thead>
          <tbody>
            <?php foreach ($history as $h): ?>
              <tr>
                <td><span style="font-family:monospace;font-weight:600;color:var(--accent-h)"><?= e($h['version']) ?></span></td>
                <td class="td-light"><?= e($h['description']) ?></td>
                <td class="td-light"><?= formatDateTime($h['applied_at']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Upload -->
  <div class="card">
    <div class="card-header"><span class="card-title">رفع حزمة تحديث</span></div>
    <div class="card-body">
      <form method="POST" action="<?= adminUrl('updater/upload') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div style="border:2px dashed var(--border2);border-radius:10px;padding:32px;text-align:center;margin-bottom:14px" id="dropZone">
          <div style="font-size:36px;margin-bottom:10px">📁</div>
          <div style="font-weight:500;margin-bottom:6px">اسحب وأسقط ملف ZIP هنا</div>
          <div style="font-size:12px;color:var(--text-3);margin-bottom:16px">أو انقر للاختيار</div>
          <input type="file" name="package" accept=".zip" id="packageFile" style="display:none" onchange="updateFileName(this)">
          <button type="button" class="btn btn-secondary" onclick="document.getElementById('packageFile').click()">اختيار ملف</button>
          <div id="fileName" style="font-size:12px;color:var(--accent-h);margin-top:10px"></div>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">⬆️ رفع الحزمة</button>
      </form>
      <div style="margin-top:16px;padding:14px;background:var(--yellow-bg);border:1px solid rgba(245,158,11,.2);border-radius:8px;font-size:12px;color:var(--yellow);line-height:1.7">
        ⚠️ <strong>تنبيه:</strong> سيتم تطبيق التحديث مباشرة على الملفات. تأكد من وجود نسخة احتياطية قبل التحديث. الإعدادات (config.php) تُحفظ تلقائياً.
      </div>
    </div>
  </div>
</div>

<script>
function updateFileName(input) {
  document.getElementById('fileName').textContent = input.files[0]?.name || '';
}
// Drag and drop
const dz = document.getElementById('dropZone');
dz.addEventListener('dragover', e => { e.preventDefault(); dz.style.borderColor = 'var(--accent)'; });
dz.addEventListener('dragleave', () => { dz.style.borderColor = 'var(--border2)'; });
dz.addEventListener('drop', e => {
  e.preventDefault();
  dz.style.borderColor = 'var(--border2)';
  const file = e.dataTransfer.files[0];
  if (file) {
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('packageFile').files = dt.files;
    document.getElementById('fileName').textContent = file.name;
  }
});
</script>

<?php $content = ob_get_clean(); require APP_PATH . '/Views/admin/layouts/app.php';
