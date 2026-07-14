<?php
// ─── Database ─────────────────────────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_NAME',    'notchtech');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ─── App ──────────────────────────────────────────────────────────────────────
define('APP_NAME',            'Notch Technology');
define('APP_URL',             'https://yourdomain.com'); // ← يتغير في install
define('APP_ENV',             'production');
define('APP_DEBUG',           false);
define('APP_TIMEZONE',        'Africa/Cairo');
define('APP_CURRENCY',        'EGP');
define('APP_CURRENCY_SYMBOL', 'ج.م');
define('APP_LANG',            'ar');

// ─── Admin ────────────────────────────────────────────────────────────────────
define('ADMIN_PREFIX',    'admin');
define('SESSION_NAME',    'notchtech_session');
define('SESSION_LIFETIME', 86400);

// ─── Upload ───────────────────────────────────────────────────────────────────
// On Hostinger: uploads sit directly in public_html/uploads/
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL',  APP_URL . '/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// ─── Fawateerk ────────────────────────────────────────────────────────────────
define('FAWATEERK_API_KEY',      '');
define('FAWATEERK_API_URL',      'https://app.fawaterk.com/api/v2');
define('FAWATEERK_CALLBACK_URL', APP_URL . '/payment/callback');
define('FAWATEERK_REDIRECT_URL', APP_URL . '/payment/success');

// ─── Pagination ───────────────────────────────────────────────────────────────
define('ITEMS_PER_PAGE', 20);

// ─── Timezone ─────────────────────────────────────────────────────────────────
date_default_timezone_set(APP_TIMEZONE);

// ─── Error handling ───────────────────────────────────────────────────────────
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', STORAGE_PATH . '/logs/error.log');
}
