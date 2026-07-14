<?php

if (!function_exists('normalizeEnvValue')) {
    function normalizeEnvValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);
        if ((str_starts_with($trimmed, '"') && str_ends_with($trimmed, '"')) ||
            (str_starts_with($trimmed, "'") && str_ends_with($trimmed, "'"))) {
            $trimmed = substr($trimmed, 1, -1);
        }

        return match (strtolower($trimmed)) {
            'true', '(true)', 'yes', 'on' => true,
            'false', '(false)', 'no', 'off' => false,
            'null', '(null)' => null,
            'empty', '(empty)' => '',
            default => $trimmed,
        };
    }
}

if (!function_exists('loadEnv')) {
    function loadEnv(string $file): void
    {
        if (!is_readable($file)) {
            return;
        }

        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = array_map('trim', explode('=', $line, 2));
            if ($key === '' || getenv($key) !== false) {
                continue;
            }

            $normalized = normalizeEnvValue($value);
            putenv($key . '=' . (is_bool($normalized) ? ($normalized ? 'true' : 'false') : (string) $normalized));
            $_ENV[$key] = $normalized;
            $_SERVER[$key] = $normalized;
        }
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($value === false || $value === null) {
            return $default;
        }

        return normalizeEnvValue($value);
    }
}

loadEnv(ROOT_PATH . '/.env');

// ─── Database ─────────────────────────────────────────────────────────────────
define('DB_HOST',    env('DB_HOST', 'localhost'));
define('DB_NAME',    env('DB_NAME', 'notchtech'));
define('DB_USER',    env('DB_USER', 'root'));
define('DB_PASS',    env('DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');

// ─── App ──────────────────────────────────────────────────────────────────────
define('APP_NAME',            'Notch Technology');
define('APP_URL',             rtrim((string) env('APP_URL', 'https://yourdomain.com'), '/')); // ← يتغير في install
define('APP_ENV',             env('APP_ENV', 'production'));
define('APP_DEBUG',           (bool) env('APP_DEBUG', false));
define('APP_TIMEZONE',        env('APP_TIMEZONE', 'Africa/Cairo'));
define('APP_CURRENCY',        env('APP_CURRENCY', 'EGP'));
define('APP_CURRENCY_SYMBOL', env('APP_CURRENCY_SYMBOL', 'ج.م'));
define('APP_LANG',            'ar');

// ─── Admin ────────────────────────────────────────────────────────────────────
define('ADMIN_PREFIX',    trim((string) env('ADMIN_PREFIX', 'admin'), '/'));
define('SESSION_NAME',    env('SESSION_NAME', 'notchtech_session'));
define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 86400));

// ─── Upload ───────────────────────────────────────────────────────────────────
// On Hostinger: uploads sit directly in public_html/uploads/
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL',  APP_URL . '/uploads');
define('MAX_FILE_SIZE', 5 * 1024 * 1024);
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// ─── Fawateerk ────────────────────────────────────────────────────────────────
define('FAWATEERK_API_KEY',      env('FAWATEERK_API_KEY', ''));
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
