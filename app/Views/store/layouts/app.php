<?php
// Safe preamble — wrap all global calls
if (!function_exists("_s")) {
    function _s(string $key, string $default = ""): string {
        try { return SettingModel::get($key, $default); }
        catch(\Throwable $e) { return $default; }
    }
}
if (!isset($collections)) {
    try { $collections = (new CollectionModel())->getActive(); }
    catch(\Throwable $e) { $collections = []; }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? _s('meta_title', APP_NAME)) ?></title>
<meta name="description" content="<?= e($pageDesc ?? _s('meta_description', '')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<?php
$fbPixel = _s('facebook_pixel');
$gaId    = _s('google_analytics');
if ($fbPixel): ?>
<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','<?= e($fbPixel) ?>');fbq('track','PageView');</script>
<?php endif; if ($gaId): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','<?= e($gaId) ?>');</script>
<?php endif; ?>
<style>
/* ══════════════════════════════════════════════════════
   Notch Technology Storefront
   Dark tech aesthetic — notchtech.co inspired
   ══════════════════════════════════════════════════════ */
:root{
  --bg:         #080809;
  --bg2:        #0e0e10;
  --bg3:        #141416;
  --surface:    #18181b;
  --surface2:   #1e1e23;
  --border:     #26262e;
  --border2:    #32323c;
  --text:       #f0f0f5;
  --text2:      #9898a8;
  --text3:      #4a4a58;
  --accent:     #6d5acd;
  --accent2:    #8b75e8;
  --accent-bg:  rgba(109,90,205,.12);
  --green:      #22c55e;
  --red:        #ef4444;
  --yellow:     #f59e0b;
  --gold:       #f59e0b;
  --radius:     12px;
  --radius-sm:  8px;
  --nav-h:      64px;
  --transition: .2s cubic-bezier(.4,0,.2,1);
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{font-family:'Inter',system-ui,sans-serif;background:var(--bg);color:var(--text);line-height:1.6;overflow-x:hidden}
a{text-decoration:none;color:inherit}
img{max-width:100%;display:block}
button{font-family:inherit;cursor:pointer}
::-webkit-scrollbar{width:5px}
::-webkit-scrollbar-track{background:transparent}
::-webkit-scrollbar-thumb{background:var(--border2);border-radius:3px}

/* ── NAV ── */
.nav{
  position:sticky;top:0;z-index:100;
  height:var(--nav-h);
  background:rgba(8,8,9,.85);
  backdrop-filter:blur(20px);
  -webkit-backdrop-filter:blur(20px);
  border-bottom:1px solid var(--border);
  display:flex;align-items:center;
}
.nav-inner{
  max-width:1280px;margin:0 auto;padding:0 24px;
  width:100%;display:flex;align-items:center;gap:32px;
}
.nav-logo{
  font-size:18px;font-weight:800;color:var(--text);letter-spacing:-.5px;
  display:flex;align-items:center;gap:8px;flex-shrink:0;
}
.nav-logo-mark{
  width:30px;height:30px;
  background:linear-gradient(135deg,var(--accent),var(--accent2));
  border-radius:7px;
  display:flex;align-items:center;justify-content:center;
  font-size:14px;
}
.nav-logo span{color:var(--accent2)}
.nav-links{display:flex;align-items:center;gap:4px;flex:1}
.nav-link{
  padding:6px 12px;border-radius:7px;
  font-size:13.5px;font-weight:500;color:var(--text2);
  transition:background var(--transition),color var(--transition);
}
.nav-link:hover,.nav-link.active{background:var(--surface2);color:var(--text)}
.nav-actions{display:flex;align-items:center;gap:8px;margin-right:auto}
.nav-search{
  display:flex;align-items:center;gap:8px;
  background:var(--surface);border:1px solid var(--border);
  border-radius:8px;padding:7px 12px;
  width:200px;transition:width var(--transition),border-color var(--transition);
  cursor:text;
}
.nav-search:focus-within{width:260px;border-color:var(--accent)}
.nav-search input{background:none;border:none;outline:none;font-size:13px;color:var(--text);width:100%;font-family:inherit}
.nav-search input::placeholder{color:var(--text3)}
.nav-btn{
  width:38px;height:38px;border-radius:8px;
  background:var(--surface);border:1px solid var(--border);
  display:flex;align-items:center;justify-content:center;
  color:var(--text2);font-size:18px;position:relative;
  transition:all var(--transition);
}
.nav-btn:hover{border-color:var(--accent);color:var(--text)}
.nav-badge{
  position:absolute;top:-5px;right:-5px;
  width:18px;height:18px;background:var(--accent);
  border-radius:50%;font-size:10px;font-weight:700;color:#fff;
  display:flex;align-items:center;justify-content:center;
}
.nav-hamburger{display:none;background:none;border:none;color:var(--text);font-size:22px}

/* Mobile menu */
.mobile-menu{
  display:none;position:fixed;inset:0;z-index:200;
  background:rgba(0,0,0,.8);backdrop-filter:blur(8px);
}
.mobile-menu.open{display:flex}
.mobile-menu-inner{
  background:var(--bg3);border-right:1px solid var(--border);
  width:280px;height:100%;padding:24px 16px;
  display:flex;flex-direction:column;gap:4px;
  animation:slideIn .2s ease;
}
@keyframes slideIn{from{transform:translateX(100%)}to{transform:none}}
.mobile-close{
  align-self:flex-end;background:none;border:none;
  color:var(--text2);font-size:22px;margin-bottom:16px;
}
.mobile-link{
  padding:12px 16px;border-radius:8px;
  font-size:14px;font-weight:500;color:var(--text2);
  transition:background var(--transition);
}
.mobile-link:hover{background:var(--surface2);color:var(--text)}

/* ── CONTAINER ── */
.container{max-width:1280px;margin:0 auto;padding:0 24px}
.container-sm{max-width:900px;margin:0 auto;padding:0 24px}

/* ── SECTION ── */
.section{padding:80px 0}
.section-sm{padding:48px 0}
.section-header{margin-bottom:40px}
.section-label{
  display:inline-flex;align-items:center;gap:6px;
  font-size:11px;font-weight:700;color:var(--accent2);
  text-transform:uppercase;letter-spacing:1.5px;
  margin-bottom:10px;
}
.section-title{font-size:32px;font-weight:800;color:var(--text);letter-spacing:-.5px;line-height:1.2}
.section-sub{font-size:15px;color:var(--text2);margin-top:8px}

/* ── PRODUCT CARD ── */
.products-grid{
  display:grid;
  grid-template-columns:repeat(4,1fr);
  gap:18px;
}
.product-card{
  background:var(--surface);
  border:1px solid var(--border);
  border-radius:var(--radius);
  overflow:hidden;
  transition:border-color var(--transition),transform var(--transition);
  position:relative;
  display:flex;flex-direction:column;
}
.product-card:hover{border-color:var(--accent);transform:translateY(-2px)}
.product-card-img{
  aspect-ratio:1;
  background:var(--bg3);
  overflow:hidden;position:relative;
}
.product-card-img img{
  width:100%;height:100%;object-fit:cover;
  transition:transform .4s ease;
}
.product-card:hover .product-card-img img{transform:scale(1.05)}
.product-card-img-placeholder{
  width:100%;height:100%;
  display:flex;align-items:center;justify-content:center;
  font-size:48px;color:var(--text3);
}
.product-card-badge{
  position:absolute;top:10px;right:10px;
  background:var(--accent);color:#fff;
  font-size:10px;font-weight:700;padding:3px 8px;border-radius:20px;
}
.product-card-wishlist{
  position:absolute;top:10px;left:10px;
  width:32px;height:32px;border-radius:50%;
  background:rgba(0,0,0,.6);backdrop-filter:blur(4px);
  border:1px solid var(--border);
  display:flex;align-items:center;justify-content:center;
  font-size:14px;opacity:0;
  transition:opacity var(--transition);
  cursor:pointer;
}
.product-card:hover .product-card-wishlist{opacity:1}
.product-card-body{padding:14px;flex:1;display:flex;flex-direction:column;gap:6px}
.product-card-brand{font-size:10px;font-weight:600;color:var(--accent2);text-transform:uppercase;letter-spacing:.8px}
.product-card-name{font-size:14px;font-weight:600;color:var(--text);line-height:1.4}
.product-card-rating{display:flex;align-items:center;gap:4px;font-size:11px;color:var(--gold)}
.product-card-rating span{color:var(--text3)}
.product-card-footer{
  display:flex;align-items:center;justify-content:space-between;
  margin-top:auto;padding-top:10px;border-top:1px solid var(--border);
}
.product-card-price{font-size:16px;font-weight:800;color:var(--text)}
.product-card-old-price{font-size:11px;color:var(--text3);text-decoration:line-through}
.product-card-add{
  width:34px;height:34px;border-radius:8px;
  background:var(--accent);color:#fff;border:none;
  display:flex;align-items:center;justify-content:center;font-size:18px;
  transition:background var(--transition),transform var(--transition);
}
.product-card-add:hover{background:var(--accent2);transform:scale(1.1)}

/* ── BUTTONS ── */
.btn{
  display:inline-flex;align-items:center;justify-content:center;gap:8px;
  padding:11px 22px;border-radius:9px;font-size:14px;font-weight:600;
  border:1px solid transparent;transition:all var(--transition);cursor:pointer;
  font-family:inherit;white-space:nowrap;
}
.btn-primary{background:var(--accent);color:#fff;border-color:var(--accent)}
.btn-primary:hover{background:var(--accent2);border-color:var(--accent2)}
.btn-outline{background:transparent;color:var(--text);border-color:var(--border)}
.btn-outline:hover{border-color:var(--accent);color:var(--accent2)}
.btn-ghost{background:transparent;color:var(--text2);border-color:transparent}
.btn-ghost:hover{background:var(--surface);color:var(--text)}
.btn-danger{background:var(--red);color:#fff;border-color:var(--red)}
.btn-lg{padding:14px 28px;font-size:15px}
.btn-sm{padding:7px 14px;font-size:12px}
.btn-full{width:100%}

/* ── BADGE ── */
.badge{
  display:inline-flex;align-items:center;gap:3px;
  padding:3px 8px;border-radius:20px;font-size:11px;font-weight:600;
}
.badge-new{background:var(--accent-bg);color:var(--accent2)}
.badge-sale{background:rgba(239,68,68,.12);color:var(--red)}
.badge-out{background:rgba(74,74,88,.2);color:var(--text3)}

/* ── BREADCRUMB ── */
.breadcrumb{
  display:flex;align-items:center;gap:8px;
  font-size:13px;color:var(--text3);padding:20px 0;
}
.breadcrumb a:hover{color:var(--text)}
.breadcrumb-sep{color:var(--border2)}
.breadcrumb-current{color:var(--text2)}

/* ── FILTER SIDEBAR ── */
.filter-sidebar{
  width:230px;flex-shrink:0;
}
.filter-group{
  border-bottom:1px solid var(--border);padding-bottom:20px;margin-bottom:20px;
}
.filter-group:last-child{border-bottom:none}
.filter-title{font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.8px;margin-bottom:12px}
.filter-item{
  display:flex;align-items:center;gap:8px;
  padding:5px 0;font-size:13px;color:var(--text2);cursor:pointer;
  transition:color var(--transition);
}
.filter-item:hover,.filter-item.active{color:var(--text)}
.filter-item input{width:14px;height:14px;accent-color:var(--accent)}

/* ── FORMS ── */
.form-group{margin-bottom:16px}
.form-label{display:block;font-size:12px;font-weight:500;color:var(--text2);margin-bottom:6px}
.form-label span{color:var(--red)}
.form-input,.form-select,.form-textarea{
  width:100%;background:var(--surface);border:1px solid var(--border);
  border-radius:var(--radius-sm);color:var(--text);
  padding:10px 14px;font-size:14px;font-family:inherit;outline:none;
  transition:border-color var(--transition),box-shadow var(--transition);
}
.form-input:focus,.form-select:focus,.form-textarea:focus{
  border-color:var(--accent);box-shadow:0 0 0 3px rgba(109,90,205,.1);
}
.form-input::placeholder{color:var(--text3)}
.form-textarea{resize:vertical;min-height:100px}
.form-select{appearance:none}
.form-error{font-size:12px;color:var(--red);margin-top:4px}

/* ── FLASH ── */
.flash-container{max-width:1280px;margin:0 auto;padding:12px 24px 0}
.flash{
  display:flex;align-items:center;gap:10px;
  padding:12px 16px;border-radius:9px;font-size:13.5px;font-weight:500;
  animation:fadeSlide .2s ease;
}
@keyframes fadeSlide{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}
.flash-success{background:rgba(34,197,94,.1);border:1px solid rgba(34,197,94,.2);color:var(--green)}
.flash-error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:var(--red)}

/* ── FOOTER ── */
.footer{background:var(--bg2);border-top:1px solid var(--border);padding:60px 0 24px}
.footer-grid{display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:40px;margin-bottom:48px}
.footer-brand .nav-logo{margin-bottom:12px}
.footer-brand p{font-size:13px;color:var(--text3);line-height:1.8;max-width:240px}
.footer-col-title{font-size:12px;font-weight:700;color:var(--text2);text-transform:uppercase;letter-spacing:.8px;margin-bottom:14px}
.footer-link{
  display:block;font-size:13px;color:var(--text3);padding:4px 0;
  transition:color var(--transition);
}
.footer-link:hover{color:var(--text)}
.footer-social{display:flex;gap:8px;margin-top:16px}
.footer-social a{
  width:34px;height:34px;border-radius:8px;
  background:var(--surface);border:1px solid var(--border);
  display:flex;align-items:center;justify-content:center;
  font-size:15px;color:var(--text3);
  transition:all var(--transition);
}
.footer-social a:hover{border-color:var(--accent);color:var(--accent2)}
.footer-bottom{
  padding-top:24px;border-top:1px solid var(--border);
  display:flex;align-items:center;justify-content:space-between;
  font-size:12px;color:var(--text3);flex-wrap:wrap;gap:8px;
}
.footer-bottom a:hover{color:var(--text)}


/* ── VISUAL REFRESH ── */
body{
  background:
    radial-gradient(circle at 80% 10%, rgba(109,90,205,.16), transparent 28rem),
    radial-gradient(circle at 15% 35%, rgba(139,117,232,.10), transparent 24rem),
    linear-gradient(180deg,#07070a 0%,#0d0d12 45%,#080809 100%);
}
body::before{
  content:"";position:fixed;inset:0;z-index:-1;pointer-events:none;opacity:.32;
  background-image:linear-gradient(rgba(255,255,255,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.035) 1px,transparent 1px);
  background-size:44px 44px;mask-image:linear-gradient(to bottom,#000,transparent 80%);
}
.nav{box-shadow:0 18px 60px rgba(0,0,0,.28)}
.nav-logo-mark{box-shadow:0 0 26px rgba(109,90,205,.45)}
.nav-link{position:relative}
.nav-link::after{content:"";position:absolute;right:12px;left:12px;bottom:2px;height:2px;border-radius:99px;background:linear-gradient(90deg,var(--accent),var(--accent2));transform:scaleX(0);transition:transform var(--transition)}
.nav-link:hover::after,.nav-link.active::after{transform:scaleX(1)}
.nav-btn,.nav-search,.product-card,.form-input,.form-select,.form-textarea{box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}
.announcement{
  background:linear-gradient(90deg,rgba(109,90,205,.18),rgba(139,117,232,.09),rgba(109,90,205,.18));
  border-bottom:1px solid rgba(139,117,232,.18);color:#dcd7ff;font-size:12px;font-weight:700;text-align:center;padding:8px 16px;
}
.hero-modern{position:relative;overflow:hidden;min-height:660px;display:flex;align-items:center;border-bottom:1px solid var(--border)}
.hero-modern::before{content:"";position:absolute;inset:0;background:linear-gradient(135deg,rgba(109,90,205,.22),transparent 34%),linear-gradient(225deg,rgba(139,117,232,.14),transparent 42%)}
.hero-modern::after{content:"";position:absolute;inset:auto -15% -45% -15%;height:70%;background:radial-gradient(ellipse at center,rgba(109,90,205,.22),transparent 62%);filter:blur(10px)}
.hero-layout{position:relative;z-index:1;display:grid;grid-template-columns:minmax(0,1.05fr) minmax(320px,.95fr);gap:56px;align-items:center;padding:80px 0}
.hero-kicker{display:inline-flex;align-items:center;gap:9px;background:rgba(109,90,205,.14);border:1px solid rgba(139,117,232,.28);border-radius:999px;padding:7px 15px;font-size:12px;color:#c8c0ff;font-weight:800;margin-bottom:22px;box-shadow:0 12px 40px rgba(109,90,205,.12)}
.hero-dot{width:7px;height:7px;border-radius:50%;background:#8b75e8;box-shadow:0 0 0 6px rgba(139,117,232,.12);animation:pulse 2s infinite}
.hero-title{font-size:clamp(42px,6vw,78px);font-weight:950;color:#fff;letter-spacing:-3px;line-height:1.02;margin-bottom:20px;text-wrap:balance}
.hero-title span{background:linear-gradient(135deg,#fff,#a99cff 55%,#6d5acd);-webkit-background-clip:text;background-clip:text;color:transparent}
.hero-subtitle{font-size:18px;color:#b9b9c8;line-height:1.9;margin-bottom:34px;max-width:620px}
.hero-actions{display:flex;gap:12px;flex-wrap:wrap}
.hero-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-top:42px;max-width:620px}
.hero-stat{background:rgba(255,255,255,.045);border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:17px 18px;backdrop-filter:blur(14px)}
.hero-stat strong{display:block;font-size:25px;color:#fff;line-height:1;font-weight:900}.hero-stat span{font-size:12px;color:var(--text2)}
.hero-showcase{position:relative;min-height:430px}.showcase-card{position:absolute;background:linear-gradient(180deg,rgba(30,30,35,.92),rgba(14,14,18,.92));border:1px solid rgba(255,255,255,.10);box-shadow:0 30px 100px rgba(0,0,0,.45);backdrop-filter:blur(18px);border-radius:28px;overflow:hidden}.showcase-main{inset:20px 20px 40px 45px;padding:24px}.showcase-main::before{content:"";position:absolute;inset:0;background:radial-gradient(circle at 25% 10%,rgba(139,117,232,.28),transparent 42%)}.showcase-chip{position:relative;display:inline-flex;border:1px solid rgba(139,117,232,.25);background:rgba(109,90,205,.12);color:#d5ceff;border-radius:999px;padding:5px 10px;font-size:11px;font-weight:800}.showcase-device{position:relative;margin:34px auto 18px;width:min(260px,78%);aspect-ratio:1;border-radius:36px;background:linear-gradient(135deg,#262235,#0b0b0d);display:flex;align-items:center;justify-content:center;font-size:86px;box-shadow:inset 0 0 0 1px rgba(255,255,255,.08),0 22px 60px rgba(109,90,205,.24)}.showcase-price{position:relative;display:flex;justify-content:space-between;align-items:end;gap:16px}.showcase-price b{font-size:28px}.showcase-mini{right:0;bottom:0;width:190px;padding:18px;transform:translateY(18px)}.showcase-mini .ring{width:52px;height:52px;border-radius:16px;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;margin-bottom:12px;box-shadow:0 14px 35px rgba(109,90,205,.35)}
.brand-strip{background:rgba(14,14,16,.72);border-top:1px solid var(--border);border-bottom:1px solid var(--border);padding:24px 0;backdrop-filter:blur(16px)}
.brand-strip-inner{display:flex;align-items:center;justify-content:center;gap:42px;flex-wrap:wrap}.brand-item{opacity:.58;filter:grayscale(1);transition:opacity var(--transition),filter var(--transition),transform var(--transition)}.brand-item:hover{opacity:1;filter:none;transform:translateY(-2px)}
.category-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(175px,1fr));gap:16px}.category-card{background:linear-gradient(180deg,rgba(30,30,35,.86),rgba(20,20,24,.86));border:1px solid var(--border);border-radius:18px;padding:22px;text-align:center;transition:all var(--transition);position:relative;overflow:hidden}.category-card::before{content:"";position:absolute;inset:0;background:radial-gradient(circle at top,rgba(109,90,205,.18),transparent 60%);opacity:0;transition:opacity var(--transition)}.category-card:hover{border-color:rgba(139,117,232,.6);transform:translateY(-4px);box-shadow:0 24px 70px rgba(0,0,0,.24)}.category-card:hover::before{opacity:1}.category-card>*{position:relative}.category-icon{width:64px;height:64px;background:var(--accent-bg);border:1px solid rgba(139,117,232,.18);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:26px;margin:0 auto 13px}
.trust-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px}.trust-card{background:rgba(255,255,255,.035);border:1px solid var(--border);border-radius:18px;padding:24px;text-align:center}.trust-icon{font-size:34px;margin-bottom:12px}.trust-title{font-size:15px;font-weight:800;color:var(--text);margin-bottom:5px}.trust-sub{font-size:12px;color:var(--text3)}
.product-card{border-radius:18px;background:linear-gradient(180deg,rgba(28,28,33,.98),rgba(18,18,22,.98));box-shadow:0 18px 50px rgba(0,0,0,.18)}
.product-card:hover{box-shadow:0 28px 90px rgba(0,0,0,.32),0 0 0 1px rgba(139,117,232,.26)}
.product-card-add{box-shadow:0 12px 30px rgba(109,90,205,.32)}
@media(max-width:960px){.hero-layout{grid-template-columns:1fr;gap:28px;padding:58px 0}.hero-showcase{min-height:360px;order:-1}.showcase-main{inset:0 8% 36px 8%}.showcase-mini{right:8%;}.trust-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:640px){.announcement{font-size:11px}.hero-modern{min-height:auto}.hero-layout{padding:42px 0}.hero-title{font-size:38px;letter-spacing:-1.4px}.hero-subtitle{font-size:15px}.hero-stats{grid-template-columns:1fr}.hero-showcase{display:none}.brand-strip-inner{gap:24px}.trust-grid{grid-template-columns:1fr}.section-header{align-items:flex-start!important;flex-direction:column}.btn-lg{width:100%}}

/* ── RESPONSIVE ── */
@media(max-width:1024px){
  .products-grid{grid-template-columns:repeat(3,1fr)}
  .footer-grid{grid-template-columns:1fr 1fr}
}
@media(max-width:768px){
  .nav-links,.nav-search{display:none}
  .nav-hamburger{display:block}
  .products-grid{grid-template-columns:repeat(2,1fr);gap:12px}
  .section{padding:48px 0}
  .section-title{font-size:24px}
  .footer-grid{grid-template-columns:1fr}
  .footer-brand p{max-width:100%}
}
@media(max-width:480px){
  .container{padding:0 16px}
  .products-grid{grid-template-columns:repeat(2,1fr);gap:10px}
}
</style>
<?php if(!empty($extraHead)) echo $extraHead; ?>
</head>
<body>

<!-- Mobile menu -->
<div class="mobile-menu" id="mobileMenu" onclick="if(event.target===this)closeMobileMenu()">
  <div class="mobile-menu-inner">
    <button class="mobile-close" onclick="closeMobileMenu()">×</button>
    <div class="nav-logo" style="margin-bottom:20px">
      <div class="nav-logo-mark">⚡</div>
      <span>Notch <span>Tech</span></span>
    </div>
    <?php
    try { $collections = (new CollectionModel())->getActive(); } catch(\Throwable $e) { $collections = []; }
    ?>
    <a href="<?= url() ?>" class="mobile-link">الرئيسية</a>
    <a href="<?= url('products') ?>" class="mobile-link">المنتجات</a>
    <?php foreach (array_slice($collections, 0, 5) as $c): ?>
      <a href="<?= url('collections/' . $c['slug']) ?>" class="mobile-link"><?= e($c['name']) ?></a>
    <?php endforeach; ?>
    <a href="<?= url('about') ?>" class="mobile-link">من نحن</a>
    <a href="<?= url('contact') ?>" class="mobile-link">تواصل معنا</a>
    <div style="margin-top:auto;padding-top:20px;border-top:1px solid var(--border)">
      <?php if (isStoreLoggedIn()): ?>
        <a href="<?= url('account') ?>" class="mobile-link">حسابي</a>
        <a href="<?= url('logout') ?>" class="mobile-link">تسجيل الخروج</a>
      <?php else: ?>
        <a href="<?= url('login') ?>" class="mobile-link">تسجيل الدخول</a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Announcement -->
<div class="announcement">توصيل سريع داخل مصر · ضمان رسمي · دفع آمن مع Fawateerk</div>

<!-- Navbar -->
<nav class="nav">
  <div class="nav-inner">
    <a href="<?= url() ?>" class="nav-logo">
      <?php $logo = _s('store_logo'); ?>
      <?php if ($logo): ?>
        <img src="<?= uploadUrl($logo) ?>" alt="logo" style="height:28px;width:auto">
      <?php else: ?>
        <div class="nav-logo-mark">⚡</div>
        <?= e(_s('store_name', 'Notch')) ?> <span>Tech</span>
      <?php endif; ?>
    </a>

    <div class="nav-links">
      <?php
      $currentPath = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
      ?>
      <a href="<?= url('products') ?>" class="nav-link <?= str_starts_with($currentPath,'products')?'active':'' ?>">المنتجات</a>
      <?php foreach (array_slice($collections ?? [], 0, 4) as $c): ?>
        <a href="<?= url('collections/'.$c['slug']) ?>" class="nav-link <?= str_starts_with($currentPath,'collections/'.$c['slug'])?'active':'' ?>">
          <?= e($c['name']) ?>
        </a>
      <?php endforeach; ?>
      <a href="<?= url('about') ?>" class="nav-link">من نحن</a>
    </div>

    <div class="nav-actions">
      <form action="<?= url('search') ?>" method="GET" class="nav-search">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color:var(--text3);flex-shrink:0"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input type="text" name="q" placeholder="بحث..." value="<?= e($_GET['q'] ?? '') ?>">
      </form>

      <?php if (isStoreLoggedIn()): ?>
        <a href="<?= url('account') ?>" class="nav-btn" title="حسابي">👤</a>
      <?php else: ?>
        <a href="<?= url('login') ?>" class="nav-btn" title="تسجيل الدخول">👤</a>
      <?php endif; ?>

      <a href="<?= url('cart') ?>" class="nav-btn" title="السلة" id="cartBtn">
        🛒
        <?php $cartCount = Cart::count(); ?>
        <?php if ($cartCount > 0): ?>
          <span class="nav-badge" id="cartBadge"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>

      <button class="nav-hamburger" onclick="openMobileMenu()" aria-label="القائمة">☰</button>
    </div>
  </div>
</nav>

<!-- Flash messages -->
<?php if ($s = flash('success')): ?>
  <div class="flash-container"><div class="flash flash-success">✅ <?= e($s) ?></div></div>
<?php endif; ?>
<?php if ($e = flash('error')): ?>
  <div class="flash-container"><div class="flash flash-error">⚠️ <?= e($e) ?></div></div>
<?php endif; ?>

<!-- Page Content -->
<?php if (!empty($content)) echo $content; ?>

<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div class="footer-brand">
        <div class="nav-logo" style="margin-bottom:12px">
          <div class="nav-logo-mark">⚡</div>
          <?= e(_s('store_name', 'Notch Technology')) ?>
        </div>
        <p><?= e(_s('store_description', 'متجر الإلكترونيات الرائد في مصر')) ?></p>
        <div class="footer-social">
          <?php foreach (['facebook'=>'f','instagram'=>'📷','twitter'=>'𝕏','youtube'=>'▶','tiktok'=>'♪'] as $k=>$icon): ?>
            <?php try { $link = SettingModel::get("social_{$k}"); } catch(\Throwable $e) { $link = ""; } if ($link): ?>
              <a href="<?= e($link) ?>" target="_blank"><?= $icon ?></a>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      </div>
      <div>
        <div class="footer-col-title">تسوق</div>
        <a href="<?= url('products') ?>" class="footer-link">كل المنتجات</a>
        <?php foreach (array_slice($collections ?? [], 0, 5) as $c): ?>
          <a href="<?= url('collections/'.$c['slug']) ?>" class="footer-link"><?= e($c['name']) ?></a>
        <?php endforeach; ?>
      </div>
      <div>
        <div class="footer-col-title">الشركة</div>
        <a href="<?= url('about') ?>" class="footer-link">من نحن</a>
        <a href="<?= url('contact') ?>" class="footer-link">تواصل معنا</a>
        <a href="#" class="footer-link">سياسة الخصوصية</a>
        <a href="#" class="footer-link">سياسة الإرجاع</a>
      </div>
      <div>
        <div class="footer-col-title">الدعم</div>
        <a href="<?= url('account/orders') ?>" class="footer-link">تتبع طلبك</a>
        <a href="<?= url('contact') ?>" class="footer-link">المساعدة</a>
        <?php if ($phone = _s('store_phone')): ?>
          <a href="tel:<?= e($phone) ?>" class="footer-link">📞 <?= e($phone) ?></a>
        <?php endif; ?>
        <?php if ($email = _s('store_email')): ?>
          <a href="mailto:<?= e($email) ?>" class="footer-link">✉️ <?= e($email) ?></a>
        <?php endif; ?>
      </div>
    </div>
    <div class="footer-bottom">
      <span>© <?= date('Y') ?> <?= e(_s('store_name', 'Notch Technology')) ?> — جميع الحقوق محفوظة</span>
      <div style="display:flex;gap:16px">
        <span>💳 Fawateerk</span>
        <span>💵 Cash on Delivery</span>
        <span>🔒 دفع آمن</span>
      </div>
    </div>
  </div>
</footer>

<script>
// Mobile menu
function openMobileMenu()  { document.getElementById('mobileMenu').classList.add('open'); document.body.style.overflow='hidden'; }
function closeMobileMenu() { document.getElementById('mobileMenu').classList.remove('open'); document.body.style.overflow=''; }

// Cart count update
function updateCartBadge(count) {
  const badge = document.getElementById('cartBadge');
  const btn   = document.getElementById('cartBtn');
  if (count > 0) {
    if (!badge) {
      const b = document.createElement('span');
      b.className = 'nav-badge'; b.id = 'cartBadge'; b.textContent = count;
      btn.appendChild(b);
    } else { badge.textContent = count; }
  } else if (badge) { badge.remove(); }
}

// Add to cart
function addToCart(productId, variantId = null, qty = 1) {
  const form = new FormData();
  form.append('product_id', productId);
  form.append('qty', qty);
  if (variantId) form.append('variant_id', variantId);
  form.append('_csrf', '<?= csrf_token() ?>');

  fetch('<?= url('cart/add') ?>', { method: 'POST', body: form })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        updateCartBadge(d.count);
        showToast('✅ تمت الإضافة للسلة');
      } else {
        showToast('⚠️ ' + (d.message || 'حدث خطأ'), 'error');
      }
    });
}

// Toast notification
function showToast(msg, type = 'success') {
  const t = document.createElement('div');
  t.style.cssText = `position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;
    background:${type==='error'?'rgba(239,68,68,.95)':'rgba(34,197,94,.95)'};
    color:#fff;padding:12px 20px;border-radius:10px;font-size:13.5px;font-weight:500;
    box-shadow:0 8px 24px rgba(0,0,0,.4);animation:fadeSlide .2s ease;white-space:nowrap;`;
  t.textContent = msg;
  document.body.appendChild(t);
  setTimeout(() => t.remove(), 2800);
}

// Wishlist toggle
function toggleWishlist(productId, btn) {
  const form = new FormData();
  form.append('product_id', productId);
  form.append('_csrf', '<?= csrf_token() ?>');
  fetch('<?= url('wishlist/toggle') ?>', { method: 'POST', body: form })
    .then(r => r.json())
    .then(d => {
      if (d.success) {
        btn.textContent = d.inWishlist ? '❤️' : '🤍';
        showToast(d.inWishlist ? '❤️ تمت الإضافة للمفضلة' : '🤍 تم الحذف من المفضلة');
      }
    });
}

// Auto-dismiss flash
setTimeout(() => {
  document.querySelectorAll('.flash').forEach(el => el.style.opacity = '0');
  setTimeout(() => document.querySelectorAll('.flash-container').forEach(el => el.remove()), 300);
}, 4000);

<?php if (!empty($extraScript)) echo $extraScript; ?>
</script>
</body>
</html>
