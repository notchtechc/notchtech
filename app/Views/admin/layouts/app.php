<?php
// Safe helpers
function _st(string $key, string $default = ''): string {
    try { return SettingModel::get($key, $default); }
    catch (\Throwable $e) { return $default; }
}
function _adm(): ?array { 
    try { 
        if (function_exists('adminUser')) return adminUser();
        return $_SESSION['admin'] ?? null;
    } catch (\Throwable $e) { return null; }
}
$_storeName   = _st('store_name', defined('APP_NAME') ? APP_NAME : 'Notch');
$_adminUser   = _adm() ?? ['name'=>'Admin','role'=>''];
$_adm         = defined('ADMIN_PREFIX') ? ADMIN_PREFIX : 'admin';
$_currentUri  = $_SERVER['REQUEST_URI'] ?? '';
$_pendingOrders = 0;
try { $_pendingOrders = (int)(Database::fetch("SELECT COUNT(*) as c FROM orders WHERE status='pending'")['c'] ?? 0); }
catch (\Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'لوحة التحكم' ?> — <?= htmlspecialchars($_storeName) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#0f0f11;--s:#17171a;--s2:#1e1e23;--bd:#2a2a32;--bd2:#34343e;--t:#e8e8f0;--t2:#9898a8;--t3:#55555f;--ac:#6d5acd;--ac2:#7c6de0;--acb:rgba(109,90,205,.1);--acb2:rgba(109,90,205,.18);--g:#22c55e;--r:#ef4444;--y:#f59e0b;--bl:#3b82f6;--sw:232px;--hh:54px;--rd:9px}
*,::before,::after{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter','Segoe UI',system-ui,sans-serif;background:var(--bg);color:var(--t);font-size:13.5px;line-height:1.5;min-height:100vh}
a{color:inherit;text-decoration:none}
::-webkit-scrollbar{width:4px}::-webkit-scrollbar-thumb{background:var(--bd2);border-radius:2px}
.shell{display:flex;min-height:100vh}
.sb{width:var(--sw);background:var(--s);border-left:1px solid var(--bd);position:fixed;top:0;right:0;bottom:0;display:flex;flex-direction:column;z-index:100;transition:transform .22s}
.sb-hd{height:var(--hh);padding:0 14px;border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:10px}
.lm{width:30px;height:30px;background:linear-gradient(135deg,var(--ac),#8b75e8);border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0}
.bn{font-size:12.5px;font-weight:700}.bn small{display:block;font-size:10px;font-weight:400;color:var(--t3)}
.sb-nav{flex:1;overflow-y:auto;padding:6px}
.nl{font-size:10px;font-weight:600;color:var(--t3);text-transform:uppercase;letter-spacing:.8px;padding:12px 10px 5px}
.ni{display:flex;align-items:center;gap:9px;padding:7px 11px;border-radius:7px;color:var(--t2);font-size:13px;font-weight:500;transition:background .1s,color .1s;margin-bottom:1px;cursor:pointer;position:relative}
.ni:hover{background:var(--s2);color:var(--t)}.ni.on{background:var(--acb2);color:var(--ac2)}
.ni.on::before{content:'';position:absolute;right:0;top:4px;bottom:4px;width:3px;background:var(--ac);border-radius:2px 0 0 2px}
.nb{margin-right:auto;background:var(--r);color:#fff;font-size:10px;font-weight:700;padding:1px 5px;border-radius:20px}
.sb-ft{padding:8px;border-top:1px solid var(--bd)}
.ai{display:flex;align-items:center;gap:9px;padding:7px 11px;border-radius:7px;transition:background .1s}
.ai:hover{background:var(--s2)}
.av{width:28px;height:28px;background:var(--acb2);border:1px solid var(--ac);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;color:var(--ac2);flex-shrink:0}
.an{font-size:12px;font-weight:500}.ar{font-size:10px;color:var(--t3)}
.lo{margin-right:auto;color:var(--t3);font-size:13px;background:none;border:none;padding:3px;cursor:pointer;transition:color .1s}
.lo:hover{color:var(--r)}
.main{margin-right:var(--sw);flex:1;display:flex;flex-direction:column;min-height:100vh}
.tb{height:var(--hh);background:var(--s);border-bottom:1px solid var(--bd);display:flex;align-items:center;gap:12px;padding:0 20px;position:sticky;top:0;z-index:50}
.hbg{display:none;background:none;border:none;color:var(--t2);font-size:20px;padding:4px;cursor:pointer}
.bc{display:flex;align-items:center;gap:6px;font-size:12px;color:var(--t3)}
.bc a:hover{color:var(--t)}.bc-sep{color:var(--bd2)}.bc-cur{color:var(--t);font-weight:600}
.ta{margin-right:auto;display:flex;align-items:center;gap:8px}
.vs{display:flex;align-items:center;gap:5px;padding:5px 11px;background:var(--s2);border:1px solid var(--bd);border-radius:7px;color:var(--t2);font-size:12px;transition:all .1s}
.vs:hover{border-color:var(--ac);color:var(--ac2)}
.pg{padding:20px;flex:1}
.ph{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:18px;gap:12px}
.ph h1{font-size:20px;font-weight:700;letter-spacing:-.3px}
.ph p{font-size:12px;color:var(--t3);margin-top:2px}
.pha{display:flex;gap:7px;align-items:center;flex-shrink:0}
.card{background:var(--s);border:1px solid var(--bd);border-radius:var(--rd);overflow:hidden}
.ch{padding:12px 16px;border-bottom:1px solid var(--bd);display:flex;align-items:center;justify-content:space-between;gap:10px}
.ct{font-size:13px;font-weight:600}
.cb{padding:16px}
.sg{display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px}
.sc{background:var(--s);border:1px solid var(--bd);border-radius:var(--rd);padding:16px;position:relative;overflow:hidden}
.si{position:absolute;top:12px;left:12px;width:32px;height:32px;border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:16px}
.sl{font-size:11px;color:var(--t3);margin-bottom:6px}
.sv{font-size:22px;font-weight:800;letter-spacing:-.8px}
.ss{font-size:11px;color:var(--t3);margin-top:4px}
.btn{display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:7px;font-size:12.5px;font-weight:500;border:1px solid transparent;transition:all .1s;cursor:pointer;white-space:nowrap;font-family:inherit}
.btn-p{background:var(--ac);color:#fff;border-color:var(--ac)}.btn-p:hover{background:var(--ac2)}
.btn-s{background:var(--s2);color:var(--t);border-color:var(--bd)}.btn-s:hover{border-color:var(--bd2)}
.btn-d{background:rgba(239,68,68,.08);color:var(--r);border-color:rgba(239,68,68,.25)}.btn-d:hover{background:var(--r);color:#fff}
.btn-sm{padding:4px 9px;font-size:11.5px}
.tw{overflow-x:auto}
table{width:100%;border-collapse:collapse}
thead th{padding:8px 13px;font-size:10.5px;font-weight:600;color:var(--t3);text-transform:uppercase;letter-spacing:.5px;text-align:right;border-bottom:1px solid var(--bd);white-space:nowrap}
tbody tr{border-bottom:1px solid var(--bd);transition:background .08s}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:var(--s2)}
tbody td{padding:11px 13px;font-size:13px;vertical-align:middle}
.tdg{color:var(--t2)}.tdm{font-family:monospace;font-size:12px}
.pth{display:flex;align-items:center;gap:9px}
.pt{width:36px;height:36px;border-radius:7px;object-fit:cover;border:1px solid var(--bd)}
.pph{width:36px;height:36px;border-radius:7px;background:var(--s2);border:1px solid var(--bd);display:flex;align-items:center;justify-content:center;font-size:15px}
.pn{font-weight:500;font-size:13px}.ps{font-size:11px;color:var(--t3);margin-top:1px}
.bdg{display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:20px;font-size:11px;font-weight:600;white-space:nowrap}
.bdg::before{content:'';width:4px;height:4px;border-radius:50%;background:currentColor}
.bdg-ok{background:rgba(34,197,94,.1);color:var(--g)}.bdg-er{background:rgba(239,68,68,.1);color:var(--r)}.bdg-wa{background:rgba(245,158,11,.1);color:var(--y)}.bdg-in{background:rgba(59,130,246,.1);color:var(--bl)}.bdg-pu{background:var(--acb);color:var(--ac2)}.bdg-no{background:var(--s2);color:var(--t2)}
.fg{display:flex;flex-direction:column;gap:5px;margin-bottom:14px}
.fl2{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.fl3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
.fla label{font-size:12px;font-weight:500;color:var(--t2)}
.fla label span{color:var(--r)}
.inp,.sel,.txta{background:var(--bg);border:1px solid var(--bd);border-radius:7px;color:var(--t);padding:8px 11px;font-size:13px;outline:none;width:100%;font-family:inherit;transition:border-color .1s}
.inp:focus,.sel:focus,.txta:focus{border-color:var(--ac);box-shadow:0 0 0 3px rgba(109,90,205,.1)}
.inp::placeholder{color:var(--t3)}.txta{resize:vertical;min-height:85px}.sel{appearance:none}
.tg-w{display:flex;align-items:center;gap:8px}.tg{position:relative;width:36px;height:20px;flex-shrink:0}
.tg input{opacity:0;width:0;height:0;position:absolute}
.tgs{position:absolute;inset:0;background:var(--bd2);border-radius:20px;cursor:pointer;transition:background .15s}
.tgs::before{content:'';position:absolute;width:14px;height:14px;right:3px;top:3px;background:#fff;border-radius:50%;transition:transform .15s}
.tg input:checked+.tgs{background:var(--ac)}.tg input:checked+.tgs::before{transform:translateX(-16px)}
.fb{display:flex;gap:7px;margin-bottom:13px;flex-wrap:wrap}
.sw{position:relative;flex:1;min-width:160px}
.sw svg{position:absolute;right:9px;top:50%;transform:translateY(-50%);color:var(--t3);pointer-events:none}
.si2{width:100%;background:var(--s);border:1px solid var(--bd);border-radius:7px;color:var(--t);padding:7px 30px 7px 11px;font-size:13px;outline:none;transition:border-color .1s}
.si2:focus{border-color:var(--ac)}.si2::placeholder{color:var(--t3)}
.fs{background:var(--s);border:1px solid var(--bd);border-radius:7px;color:var(--t2);padding:7px 11px;font-size:12.5px;outline:none;cursor:pointer}
.pag{display:flex;justify-content:center;gap:4px;padding:13px}
.pb{width:28px;height:28px;display:flex;align-items:center;justify-content:center;border-radius:6px;font-size:12px;font-weight:500;color:var(--t2);background:var(--s2);border:1px solid var(--bd);transition:all .1s;cursor:pointer;text-decoration:none}
.pb:hover{border-color:var(--ac);color:var(--ac2)}.pb.on{background:var(--ac);border-color:var(--ac);color:#fff}
.empty{text-align:center;padding:44px 20px}.empty-i{font-size:40px;margin-bottom:12px}
.empty-t{font-size:15px;font-weight:600;margin-bottom:5px}.empty-s{font-size:12px;color:var(--t3);margin-bottom:16px}
.fl{display:flex;align-items:center;gap:9px;padding:10px 14px;border-radius:7px;font-size:13px;font-weight:500;margin-bottom:14px;animation:fd .2s}
@keyframes fd{from{opacity:0;transform:translateY(-4px)}to{opacity:1}}
.fl-ok{background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.2);color:var(--g)}
.fl-er{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--r)}
.fl-x{margin-right:auto;background:none;border:none;color:inherit;opacity:.6;font-size:15px;cursor:pointer}
.mo{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;align-items:center;justify-content:center;backdrop-filter:blur(4px)}
.mo.on{display:flex}
.md{background:var(--s);border:1px solid var(--bd);border-radius:13px;padding:24px;width:100%;max-width:360px;animation:mi .15s}
@keyframes mi{from{opacity:0;transform:scale(.95)}to{opacity:1;transform:none}}
.md-i{font-size:32px;margin-bottom:8px}.md h3{font-size:15px;font-weight:700;margin-bottom:4px}
.md p{font-size:12px;color:var(--t3);margin-bottom:20px}.md-a{display:flex;gap:7px;justify-content:flex-end}
@media(max-width:900px){.sb{transform:translateX(100%)}.sb.on{transform:translateX(0);box-shadow:0 0 30px rgba(0,0,0,.6)}.main{margin-right:0}.hbg{display:block}.sg{grid-template-columns:repeat(2,1fr)}}
@media(max-width:580px){.sg{grid-template-columns:1fr}.pg{padding:14px}.fl2,.fl3{grid-template-columns:1fr}}
</style>
<?php if(!empty($extraHead)) echo $extraHead; ?>
</head>
<body>
<div class="mo" id="mo"><div class="md">
  <div class="md-i">🗑️</div><h3 id="mo-t">هل أنت متأكد؟</h3><p id="mo-d">لا يمكن التراجع.</p>
  <div class="md-a">
    <button class="btn btn-s" onclick="cmo()">إلغاء</button>
    <form id="mo-f" method="POST" style="display:inline">
      <input type="hidden" name="_csrf" value="<?php try { echo Session::csrf(); } catch(\Throwable $e) {} ?>">
      <button type="submit" class="btn btn-d">تأكيد الحذف</button>
    </form>
  </div>
</div></div>

<div class="shell">
<aside class="sb" id="sb">
  <div class="sb-hd"><div class="lm">⚡</div><div class="bn"><?= htmlspecialchars($_storeName) ?><small>لوحة التحكم</small></div></div>
  <nav class="sb-nav">
    <?php
    $nav = [
      ['s'=>'الرئيسية'],
      ['u'=>"/{$_adm}",'i'=>'🏠','l'=>'الرئيسية','m'=>"/{$_adm}(/dashboard)?$"],
      ['u'=>"/{$_adm}/analytics",'i'=>'📊','l'=>'التحليلات','m'=>"/{$_adm}/analytics"],
      ['s'=>'المتجر'],
      ['u'=>"/{$_adm}/products",'i'=>'📦','l'=>'المنتجات','m'=>"/{$_adm}/products"],
      ['u'=>"/{$_adm}/collections",'i'=>'🗂️','l'=>'التصنيفات','m'=>"/{$_adm}/collections"],
      ['u'=>"/{$_adm}/brands",'i'=>'🏷️','l'=>'الماركات','m'=>"/{$_adm}/brands"],
      ['s'=>'المبيعات'],
      ['u'=>"/{$_adm}/orders",'i'=>'🛒','l'=>'الطلبات','m'=>"/{$_adm}/orders",'b'=>$_pendingOrders],
      ['u'=>"/{$_adm}/customers",'i'=>'👥','l'=>'العملاء','m'=>"/{$_adm}/customers"],
      ['u'=>"/{$_adm}/discounts",'i'=>'🎟️','l'=>'الخصومات','m'=>"/{$_adm}/discounts"],
      ['u'=>"/{$_adm}/reviews",'i'=>'⭐','l'=>'التقييمات','m'=>"/{$_adm}/reviews"],
      ['s'=>'النظام'],
      ['u'=>"/{$_adm}/settings",'i'=>'⚙️','l'=>'الإعدادات','m'=>"/{$_adm}/settings"],
    ];
    foreach ($nav as $n):
      if (isset($n['s'])): ?><div class="nl"><?= $n['s'] ?></div><?php continue; endif;
      $on = isset($n['m']) && preg_match('#'.$n['m'].'#', $_currentUri);
    ?><a href="<?= htmlspecialchars($n['u']) ?>" class="ni <?= $on?'on':'' ?>">
        <span><?= $n['i'] ?></span><?= htmlspecialchars($n['l']) ?>
        <?php if (!empty($n['b']) && $n['b'] > 0): ?><span class="nb"><?= $n['b'] ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="sb-ft"><div class="ai">
    <div class="av"><?= mb_substr($_adminUser['name'] ?? 'A', 0, 1) ?></div>
    <div><div class="an"><?= htmlspecialchars($_adminUser['name'] ?? 'Admin') ?></div><div class="ar"><?= htmlspecialchars($_adminUser['role'] ?? '') ?></div></div>
    <a href="/<?= $_adm ?>/logout" class="lo" title="خروج">⎋</a>
  </div></div>
</aside>

<div class="main">
  <header class="tb">
    <button class="hbg" onclick="document.getElementById('sb').classList.toggle('on')">☰</button>
    <div class="bc">
      <a href="/<?= $_adm ?>">الرئيسية</a>
      <?php if (!empty($breadcrumb)): foreach ($breadcrumb as $i => $cr): ?>
        <span class="bc-sep">/</span>
        <?php if ($i === count($breadcrumb)-1): ?><span class="bc-cur"><?= htmlspecialchars($cr['label']) ?></span>
        <?php else: ?><a href="<?= htmlspecialchars($cr['url'] ?? '#') ?>"><?= htmlspecialchars($cr['label']) ?></a>
        <?php endif; ?>
      <?php endforeach; endif; ?>
    </div>
    <div class="ta"><a href="/" target="_blank" class="vs"><svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>المتجر</a></div>
  </header>
  <div class="pg">
    <?php
    try {
      if ($s = Session::flash('success')): ?><div class="fl fl-ok">✅ <?= htmlspecialchars($s) ?><button class="fl-x" onclick="this.parentElement.remove()">×</button></div><?php endif;
      if ($e = Session::flash('error')):   ?><div class="fl fl-er">⚠️ <?= htmlspecialchars($e) ?><button class="fl-x" onclick="this.parentElement.remove()">×</button></div><?php endif;
    } catch (\Throwable $e) {}
    if (!empty($content)) echo $content;
    ?>
  </div>
</div></div>

<script>
function cnf(url,t,d){document.getElementById('mo-t').textContent=t||'هل أنت متأكد؟';document.getElementById('mo-d').textContent=d||'لا يمكن التراجع.';document.getElementById('mo-f').action=url;document.getElementById('mo').classList.add('on')}
function cmo(){document.getElementById('mo').classList.remove('on')}
document.getElementById('mo').addEventListener('click',e=>{if(e.target===document.getElementById('mo'))cmo()})
document.addEventListener('click',e=>{const sb=document.getElementById('sb');if(window.innerWidth<=900&&sb.classList.contains('on')&&!sb.contains(e.target)&&!e.target.closest('.hbg'))sb.classList.remove('on')})
setTimeout(()=>document.querySelectorAll('.fl').forEach(el=>el.remove()),5000)
function mkslug(v){return v.trim().toLowerCase().replace(/\s+/g,'-').replace(/[^a-z0-9\-]/g,'').replace(/--+/g,'-').replace(/^-+|-+$/g,'')}
<?php if (!empty($extraScript)) echo $extraScript; ?>
</script>
</body>
</html>
