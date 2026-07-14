<?php
function projectRoot(): string { return ROOT_PATH; }

function url(string $path = ''): string { return APP_URL . ($path ? '/' . ltrim($path, '/') : ''); }
function adminUrl(string $path = ''): string { return APP_URL . '/' . ADMIN_PREFIX . ($path ? '/' . ltrim($path, '/') : ''); }
function asset(string $path): string { return APP_URL . '/assets/' . ltrim($path, '/'); }
function uploadUrl(string $path): string { return APP_URL . '/uploads/' . ltrim($path, '/'); }

function e(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8'); }
function csrf_field(): string { return '<input type="hidden" name="_csrf" value="' . Session::csrf() . '">'; }
function csrf_token(): string { return Session::csrf(); }
function verifyCsrf(): bool { $t = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''; return Session::verifyCsrf($t); }

function flash(string $key, mixed $value = null): mixed { return Session::flash($key, $value); }
function flashSuccess(string $msg): void { Session::flash('success', $msg); }
function flashError(string $msg): void { Session::flash('error', $msg); }

function adminUser(): ?array { return Session::get('admin_user'); }
function isAdminLoggedIn(): bool { return Session::has('admin_user'); }
function storeUser(): ?array { return Session::get('store_user'); }
function isStoreLoggedIn(): bool { return Session::has('store_user'); }

function money(float $amount): string { return number_format($amount, 2) . ' ' . APP_CURRENCY_SYMBOL; }
function formatDate(string $date, string $format = 'd/m/Y'): string { return date($format, strtotime($date)); }
function formatDateTime(string $date): string { return date('d/m/Y H:i', strtotime($date)); }
function timeAgo(string $date): string {
    $diff = time() - strtotime($date);
    if ($diff < 60) return 'الآن';
    if ($diff < 3600) return floor($diff/60) . ' دقيقة';
    if ($diff < 86400) return floor($diff/3600) . ' ساعة';
    if ($diff < 2592000) return floor($diff/86400) . ' يوم';
    return formatDate($date);
}
function slug(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/\s+/', '-', $text);
    $text = preg_replace('/[^\p{L}\p{N}\-]/u', '', $text);
    $text = trim($text, '-');
    return $text ?: uniqid();
}
function truncate(string $text, int $length = 100): string {
    if (mb_strlen($text) <= $length) return $text;
    return mb_substr($text, 0, $length) . '...';
}
function uploadFile(array $file, string $folder = 'products'): string|false {
    if ($file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > MAX_FILE_SIZE) return false;
    if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) return false;
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $filename = uniqid() . '_' . time() . '.' . $ext;
    $dir = UPLOAD_PATH . '/' . $folder;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    if (move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) return $folder . '/' . $filename;
    return false;
}
function deleteFile(string $path): bool {
    $full = UPLOAD_PATH . '/' . $path;
    if (file_exists($full)) return unlink($full);
    return false;
}
function jsonResponse(bool $success, string $message = '', mixed $data = null, int $status = 200): never {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success'=>$success,'message'=>$message,'data'=>$data], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    exit;
}
function orderStatusLabel(string $s): string { return match($s){'pending'=>'في الانتظار','processing'=>'قيد المعالجة','shipped'=>'تم الشحن','delivered'=>'تم التسليم','cancelled'=>'ملغي','refunded'=>'مسترجع',default=>$s}; }
function orderStatusColor(string $s): string { return match($s){'pending'=>'warning','processing'=>'info','shipped'=>'primary','delivered'=>'success','cancelled'=>'danger','refunded'=>'secondary',default=>'secondary'}; }
function paymentStatusLabel(string $s): string { return match($s){'unpaid'=>'غير مدفوع','paid'=>'مدفوع','refunded'=>'مسترجع','failed'=>'فشل الدفع',default=>$s}; }
