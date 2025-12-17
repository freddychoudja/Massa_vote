<?php
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

function current_user()
{
    if (empty($_SESSION['user_id'])) return null;
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT id,name,email FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function require_login()
{
    if (!current_user()) {
        header('Location: /login.php'); exit;
    }
}

function login_user($email, $password)
{
    $pdo = get_pdo();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u) return false;
    if (password_verify($password, $u['password_hash'])) {
        $_SESSION['user_id'] = $u['id'];
        return true;
    }
    return false;
}

function register_user($name, $email, $password)
{
    $pdo = get_pdo();
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name,email,password_hash) VALUES (?, ?, ?)');
    $stmt->execute([$name, $email, $hash]);
    return $pdo->lastInsertId();
}
