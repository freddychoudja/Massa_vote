<?php
$config = require __DIR__ . '/config.php';

function get_pdo()
{
    static $pdo = null;
    global $config;
    if ($pdo) return $pdo;
    $dbPath = $config['db_path'];
    $dir = dirname($dbPath);
    if (!is_dir($dir)) mkdir($dir, 0777, true);
    $needInit = !file_exists($dbPath);
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    if ($needInit) init_db($pdo);
    migrate_db($pdo);
    return $pdo;
}

function init_db(PDO $pdo)
{
    $sql = file_get_contents(__DIR__ . '/../db/init.sql');
    $pdo->exec($sql);
}

function table_exists(PDO $pdo, $table)
{
    $stmt = $pdo->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name = ?");
    $stmt->execute([$table]);
    return (bool)$stmt->fetchColumn();
}

function column_exists(PDO $pdo, $table, $column)
{
    $stmt = $pdo->prepare("PRAGMA table_info($table)");
    $stmt->execute();
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($cols as $c) { if ($c['name'] === $column) return true; }
    return false;
}

function migrate_db(PDO $pdo)
{
    // create users table if missing
    if (!table_exists($pdo, 'users')) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );");
    }

    // add missing columns to elections
    if (table_exists($pdo, 'elections')) {
        if (!column_exists($pdo, 'elections', 'owner_id')) {
            $pdo->exec('ALTER TABLE elections ADD COLUMN owner_id INTEGER DEFAULT NULL');
        }
        if (!column_exists($pdo, 'elections', 'theme_color')) {
            $pdo->exec("ALTER TABLE elections ADD COLUMN theme_color TEXT DEFAULT NULL");
        }
        if (!column_exists($pdo, 'elections', 'logo_url')) {
            $pdo->exec("ALTER TABLE elections ADD COLUMN logo_url TEXT DEFAULT NULL");
        }
    }
}

function generate_token($len = 24)
{
    return bin2hex(random_bytes((int)($len/2)));
}
