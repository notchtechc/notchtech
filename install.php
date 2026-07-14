<?php
session_start();
$step   = (int)($_GET['step'] ?? 1);
$errors = [];

// ─── Step 2: Connect DB + install schema ──────────────────────────────────────
if ($step === 2 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim($_POST['db_host'] ?? 'localhost');
    $name = trim($_POST['db_name'] ?? '');
    $user = trim($_POST['db_user'] ?? '');
    $pass = $_POST['db_pass'] ?? '';
    if (!$name || !$user) {
        $errors[] = 'يرجى إدخال اسم قاعدة البيانات والمستخدم';
    } else {
        try {
            // Connect without DB name first
            $pdo = new PDO("mysql:host={$host};charset=utf8mb4", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$name}`");
            // Run schema
            $schemaFile = __DIR__ . '/database/schema.sql';
            if (!file_exists($schemaFile)) throw new Exception('database/schema.sql غير موجود');
            $sql = file_get_contents($schemaFile);
            // Remove comments
            $sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
            // Split and execute
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
                if (strlen($stmt) > 3) {
                    try { $pdo->exec($stmt); }
                    catch (PDOException $e) {
                        if (stripos($e->getMessage(), 'already exists') === false &&
                            stripos($e->getMessage(), 'Duplicate') === false) {
                            throw $e;
                        }
                    }
                }
            }
            $_SESSION['install_db'] = compact('host','name','user','pass');
            header('Location: install.php?step=3'); exit;
        } catch (Exception $e) {
            $errors[] = 'خطأ في قاعدة البيانات: ' . $e->getMessage();
        }
    }
}

// ─── Step 3: Store settings + admin user + write config ──────────────────────
if ($step === 3 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db       = $_SESSION['install_db'] ?? null;
    $appUrl   = rtrim(trim($_POST['app_url']    ?? ''), '/');
    $sName    = trim($_POST['store_name']        ?? 'Notch Technology');
    $aName    = trim($_POST['admin_name']        ?? 'Super Admin');
    $aEmail   = trim($_POST['admin_email']       ?? '');
    $aPass    = $_POST['admin_pass']             ?? '';

    if (!$db)           $errors[] = 'انتهت الجلسة — ارجع للخطوة 2';
    if (!$appUrl)       $errors[] = 'رابط الموقع مطلوب';
    if (!$aEmail)       $errors[] = 'بريد المدير مطلوب';
    if (strlen($aPass) < 8) $errors[] = 'كلمة المرور 8 أحرف على الأقل';

    if (empty($errors)) {
        try {
            $pdo = new PDO("mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4", $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            // Settings
            $st = $pdo->prepare("INSERT INTO settings(`key`,`value`) VALUES(?,?) ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)");
            foreach ([
                'store_name'  => $sName,
                'store_email' => trim($_POST['store_email'] ?? ''),
                'store_phone' => trim($_POST['store_phone'] ?? ''),
                'cod_active'  => '1',
                'cod_label'   => 'الدفع عند الاستلام',
            ] as $k => $v) { $st->execute([$k, $v]); }
            // Admin user
            $hash = password_hash($aPass, PASSWORD_BCRYPT, ['cost' => 12]);
            $pdo->prepare("INSERT INTO admin_users(name,email,password,role,is_active,created_at,updated_at) VALUES(?,?,?,'superadmin',1,NOW(),NOW()) ON DUPLICATE KEY UPDATE name=VALUES(name),password=VALUES(password)")->execute([$aName, $aEmail, $hash]);
            // Write config.php
            $cfgContent = "<?php\n"
                . "define('DB_HOST',    '" . addslashes($db['host']) . "');\n"
                . "define('DB_NAME',    '" . addslashes($db['name']) . "');\n"
                . "define('DB_USER',    '" . addslashes($db['user']) . "');\n"
                . "define('DB_PASS',    '" . addslashes($db['pass']) . "');\n"
                . "define('DB_CHARSET', 'utf8mb4');\n\n"
                . "define('APP_NAME',   '" . addslashes($sName)   . "');\n"
                . "define('APP_URL',    '" . addslashes($appUrl)  . "');\n"
                . "define('APP_DEBUG',  false);\n\n"
                . "define('ADMIN_PREFIX',     'admin');\n"
                . "define('SESSION_NAME',     'nt_" . substr(md5($appUrl), 0, 8) . "');\n"
                . "define('SESSION_LIFETIME', 86400);\n\n"
                . "define('UPLOAD_PATH',       ROOT_PATH . '/uploads');\n"
                . "define('UPLOAD_URL',         APP_URL . '/uploads');\n"
                . "define('MAX_FILE_SIZE',      5 * 1024 * 1024);\n"
                . "define('ALLOWED_IMAGE_TYPES', ['image/jpeg','image/png','image/webp','image/gif']);\n\n"
                . "define('APP_CURRENCY',        'EGP');\n"
                . "define('APP_CURRENCY_SYMBOL', 'ج.م');\n"
                . "define('ITEMS_PER_PAGE',       20);\n\n"
                . "define('FAWATEERK_API_KEY', '');\n"
                . "define('FAWATEERK_API_URL', 'https://app.fawaterk.com/api/v2');\n"
                . "define('FAWATEERK_CALLBACK_URL', APP_URL . '/payment/callback');\n\n"
                . "date_default_timezone_set('Africa/Cairo');\n"
                . "error_reporting(0);\n"
                . "ini_set('display_errors', 0);\n"
                . "ini_set('log_errors', 1);\n"
                . "ini_set('error_log', STORAGE_PATH . '/logs/php.log');\n";

            if (!file_put_contents(__DIR__ . '/config/config.php', $cfgContent)) {
                throw new Exception('لا يمكن الكتابة على config/config.php — تأكد من الصلاحيات');
            }
            file_put_contents(__DIR__ . '/storage/.installed', date('Y-m-d H:i:s'));
            $_SESSION['install_done'] = ['url' => $appUrl, 'email' => $aEmail];
            header('Location: install.php?step=4'); exit;
        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }
    }
}

// ─── Requirements check ───────────────────────────────────────────────────────
function checkReqs(): array {
    foreach (['uploads','storage','storage/logs','config'] as $d) {
        if (!is_dir(__DIR__."/$d")) @mkdir(__DIR__."/$d", 0755, true);
    }
    return [
        ['PHP ≥ 8.0',             version_compare(PHP_VERSION,'8.0','>='), PHP_VERSION],
        ['PDO MySQL',             extension_loaded('pdo_mysql'),  extension_loaded('pdo_mysql') ? '✓' : '❌ مفقود'],
        ['GD',                    extension_loaded('gd'),         extension_loaded('gd') ? '✓' : '❌ مفقود'],
        ['cURL',                  extension_loaded('curl'),       extension_loaded('curl') ? '✓' : '❌ مفقود'],
        ['database/schema.sql',   file_exists(__DIR__.'/database/schema.sql'), file_exists(__DIR__.'/database/schema.sql') ? '✓ موجود' : '❌ مفقود!'],
        ['uploads/ قابل للكتابة', is_writable(__DIR__.'/uploads'),  is_writable(__DIR__.'/uploads')  ? '✓' : '❌ غير قابل'],
        ['storage/ قابل للكتابة', is_writable(__DIR__.'/storage'),  is_writable(__DIR__.'/storage')  ? '✓' : '❌ غير قابل'],
        ['config/ قابل للكتابة',  is_writable(__DIR__.'/config'),   is_writable(__DIR__.'/config')   ? '✓' : '❌ غير قابل'],
    ];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>تثبيت Notch Technology</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',Tahoma,sans-serif;background:#09090b;color:#e0e0e0;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.box{width:100%;max-width:500px;background:#111;border:1px solid #222;border-radius:14px;padding:32px}
.logo{text-align:center;margin-bottom:26px}
.logo h1{font-size:21px;font-weight:800;color:#fff}
.logo h1 span{color:#6d5acd}
.logo p{color:#555;font-size:12px;margin-top:3px}
.steps{display:flex;margin-bottom:26px;border-bottom:1px solid #1e1e1e;padding-bottom:16px}
.stp{flex:1;text-align:center;font-size:10px;color:#444}
.stp.on{color:#6d5acd}.stp.dn{color:#22c55e}
.sn{width:22px;height:22px;border-radius:50%;background:#1a1a1a;border:2px solid #2a2a2a;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;font-size:9px;font-weight:700}
.stp.on .sn{background:#6d5acd;border-color:#6d5acd;color:#fff}
.stp.dn .sn{background:#22c55e;border-color:#22c55e;color:#fff}
h2{font-size:16px;font-weight:700;color:#fff;margin-bottom:4px}
.sub{color:#555;font-size:12px;margin-bottom:16px}
.err{background:#1c0808;border:1px solid #7f1d1d;color:#fca5a5;padding:10px 13px;border-radius:8px;font-size:12px;margin-bottom:14px;line-height:1.7}
.ok{background:#081308;border:1px solid #103a10;color:#4ade80;padding:10px 13px;border-radius:8px;font-size:12px;margin-bottom:14px}
.fg{margin-bottom:12px}
label{display:block;font-size:11px;color:#888;margin-bottom:4px;font-weight:500}
input{width:100%;background:#0a0a0a;border:1px solid #252525;border-radius:7px;color:#e0e0e0;padding:9px 12px;font-size:13px;font-family:inherit;outline:none;transition:border-color .12s}
input:focus{border-color:#6d5acd}
input::placeholder{color:#333}
.r2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
.btn{width:100%;padding:11px;background:#6d5acd;color:#fff;border:none;border-radius:7px;font-size:13.5px;font-weight:600;cursor:pointer;font-family:inherit;margin-top:5px;transition:background .12s}
.btn:hover{background:#5a48b8}
.req{display:flex;align-items:center;gap:8px;padding:6px 0;border-bottom:1px solid #1a1a1a;font-size:12px}
.g{color:#22c55e}.r{color:#ef4444}
.info{background:#0d0d1e;border:1px solid #252545;border-radius:7px;padding:10px 12px;font-size:11px;color:#6666aa;margin-bottom:12px;line-height:1.8}
.hr{border-top:1px solid #1e1e1e;margin:14px 0;padding-top:14px;font-size:11px;color:#444;font-weight:600}
.done-box{background:#0d0d0d;border:1px solid #1e1e1e;border-radius:8px;padding:14px;margin:14px 0;font-size:12px;line-height:2.3}
.done-box strong{color:#6d5acd}
.go{display:block;text-align:center;background:#22c55e;color:#fff;padding:11px;border-radius:7px;font-size:13.5px;font-weight:600;text-decoration:none;margin-top:8px}
.warn{background:#130d08;border:1px solid #3a2010;color:#f59e0b;padding:10px 12px;border-radius:7px;font-size:11px;margin-top:10px;line-height:1.7}
</style>
</head>
<body>
<div class="box">
  <div class="logo">
    <h1>Notch <span>Technology</span></h1>
    <p>معالج التثبيت</p>
  </div>

  <div class="steps">
    <?php foreach (['المتطلبات','قاعدة البيانات','الإعدادات','اكتمل'] as $i => $lbl):
      $n=$i+1; $cl=($n===$step?'on':($n<$step?'dn':'')); $num=($n<$step?'✓':$n); ?>
      <div class="stp <?=$cl?>"><div class="sn"><?=$num?></div><?=$lbl?></div>
    <?php endforeach; ?>
  </div>

  <?php if ($errors): ?>
    <div class="err"><?= implode('<br>', array_map('htmlspecialchars', $errors)) ?></div>
  <?php endif; ?>

  <?php if ($step === 1):
    $reqs = checkReqs(); $allOk = !in_array(false, array_column($reqs, 1)); ?>
    <h2>فحص المتطلبات</h2>
    <p class="sub">التحقق من بيئة السيرفر</p>
    <?php foreach ($reqs as $r): ?>
      <div class="req">
        <span class="<?=$r[1]?'g':'r'?>"><?=$r[1]?'✅':'❌'?></span>
        <span><?= htmlspecialchars($r[0]) ?></span>
        <span style="margin-right:auto;color:#444;font-size:11px"><?= htmlspecialchars((string)$r[2]) ?></span>
      </div>
    <?php endforeach; ?>
    <?php if (!$allOk): ?>
      <div class="err" style="margin-top:12px">⚠️ بعض المتطلبات ناقصة — تأكد من رفع كل الملفات</div>
    <?php endif; ?>
    <a href="install.php?step=2" style="display:block;margin-top:14px"><button class="btn">التالي ←</button></a>

  <?php elseif ($step === 2): ?>
    <h2>قاعدة البيانات</h2>
    <p class="sub">بيانات MySQL من hPanel</p>
    <div class="info">💡 hPanel → Databases → MySQL Databases<br>أنشئ قاعدة بيانات ومستخدم جديد وانسخ البيانات هنا</div>
    <form method="POST" action="install.php?step=2">
      <div class="fg"><label>Host</label><input type="text" name="db_host" value="localhost"></div>
      <div class="fg"><label>اسم قاعدة البيانات</label><input type="text" name="db_name" placeholder="u363175221_notchtech" required></div>
      <div class="r2">
        <div class="fg"><label>اسم المستخدم</label><input type="text" name="db_user" placeholder="u363175221_user" required></div>
        <div class="fg"><label>كلمة المرور</label><input type="password" name="db_pass"></div>
      </div>
      <button type="submit" class="btn">اتصال وتثبيت الجداول</button>
    </form>

  <?php elseif ($step === 3): ?>
    <h2>إعدادات المتجر</h2>
    <p class="sub">بيانات المتجر وحساب المدير</p>
    <form method="POST" action="install.php?step=3">
      <div class="fg">
        <label>رابط الموقع (بدون / في النهاية)</label>
        <input type="text" name="app_url" value="https://<?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? '') ?>" required>
      </div>
      <div class="r2">
        <div class="fg"><label>اسم المتجر</label><input type="text" name="store_name" value="Notch Technology" required></div>
        <div class="fg"><label>بريد المتجر</label><input type="email" name="store_email"></div>
      </div>
      <div class="hr">حساب المدير</div>
      <div class="r2">
        <div class="fg"><label>الاسم</label><input type="text" name="admin_name" value="Super Admin" required></div>
        <div class="fg"><label>البريد الإلكتروني</label><input type="email" name="admin_email" required></div>
      </div>
      <div class="fg"><label>كلمة المرور (8 أحرف+)</label><input type="password" name="admin_pass" required minlength="8"></div>
      <button type="submit" class="btn">إكمال التثبيت ✓</button>
    </form>

  <?php elseif ($step === 4):
    $done = $_SESSION['install_done'] ?? []; ?>
    <div style="text-align:center;font-size:52px;margin:6px 0 14px">🎉</div>
    <h2 style="text-align:center;margin-bottom:4px">تم التثبيت بنجاح!</h2>
    <p class="sub" style="text-align:center">Notch Technology جاهز للاستخدام</p>
    <div class="done-box">
      <div>🌐 <strong>المتجر:</strong> <?= htmlspecialchars($done['url'] ?? '') ?></div>
      <div>⚙️ <strong>لوحة التحكم:</strong> <?= htmlspecialchars(($done['url'] ?? '').'/admin') ?></div>
      <div>📧 <strong>البريد:</strong> <?= htmlspecialchars($done['email'] ?? '') ?></div>
      <div>🔑 <strong>كلمة المرور:</strong> التي أدخلتها</div>
    </div>
    <a class="go" href="<?= htmlspecialchars(($done['url'] ?? '').'/admin') ?>">الذهاب إلى لوحة التحكم →</a>
    <div class="warn">⚠️ <strong>مهم:</strong> احذف ملف install.php من السيرفر فوراً بعد الانتهاء!</div>
  <?php endif; ?>
</div>
</body>
</html>
