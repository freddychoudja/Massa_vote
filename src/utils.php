<?php
// Helpers: CSRF, validation, sanitization
if (session_status() === PHP_SESSION_NONE) session_start();

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token)
{
    if (empty($token)) return false;
    if (empty($_SESSION['csrf_token'])) return false;
    return hash_equals($_SESSION['csrf_token'], $token);
}

function h($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validate_price_cfa($p)
{
    if ($p === '' || $p === null) return true;
    if (!is_numeric($p)) return false;
    $n = intval($p);
    return $n >= 0 && $n <= 1000000;
}

function allowed_image_file($file)
{
    $allowed = ['image/png','image/jpeg','image/svg+xml','image/webp'];
    if (empty($file) || empty($file['tmp_name'])) return false;
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    return in_array($mime, $allowed, true);
}

function store_uploaded_logo($file)
{
    if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > 2 * 1024 * 1024) return null; // 2MB
    if (!allowed_image_file($file)) return null;

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $safe = bin2hex(random_bytes(8)) . '.' . preg_replace('/[^a-zA-Z0-9._-]/', '', $ext);
    $uploadDir = __DIR__ . '/../public/uploads';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    $target = $uploadDir . '/' . $safe;
    if (!move_uploaded_file($file['tmp_name'], $target)) return null;
    // return web path
    return '/uploads/' . $safe;
}
