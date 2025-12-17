<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';
require_login();
$user = current_user();
$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /admin.php'); exit;
}

// CSRF
require_once __DIR__ . '/../src/utils.php';
if (!verify_csrf($_POST['csrf_token'] ?? '')) {
  die('Requête invalide (csrf)');
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$candidates_raw = trim($_POST['candidates'] ?? '');
$is_paid = intval($_POST['is_paid'] ?? 0);
$price = intval($_POST['price'] ?? 0) * 100; // cents
$provider = $_POST['provider'] ?? null;
$theme_color = trim($_POST['theme_color'] ?? '');
$logo_url = trim($_POST['logo_url'] ?? '');

// Validate inputs
if (!validate_price_cfa($_POST['price'] ?? '')) {
  die('Prix invalide');
}

// handle uploaded logo file if provided
$logo_file_path = null;
if (!empty($_FILES['logo_file']) && $_FILES['logo_file']['error'] !== UPLOAD_ERR_NO_FILE) {
  $logo_file_path = store_uploaded_logo($_FILES['logo_file']);
  if ($logo_file_path === null) {
    die('Échec téléversement du logo (type/size)');
  }
}

if (!$title || !$candidates_raw) {
  die('Titre et candidats requis');
}

$token = generate_token(24);
$final_logo = $logo_file_path ?: ($logo_url ?: null);
$stmt = $pdo->prepare('INSERT INTO elections (title, description, token, owner_id, is_paid, price_cents, provider, theme_color, logo_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
$stmt->execute([$title, $description, $token, $user['id'], $is_paid, $price, $provider, $theme_color ?: null, $final_logo]);
$election_id = $pdo->lastInsertId();

$candidates = array_filter(array_map('trim', explode(',', $candidates_raw)));
$insert = $pdo->prepare('INSERT INTO candidates (election_id, name, token) VALUES (?, ?, ?)');
foreach($candidates as $c) {
  $ctoken = generate_token(24);
  $insert->execute([$election_id, $c, $ctoken]);
}

// show links
$election_link = '/vote.php?e=' . $token;

// fetch candidate tokens
$stmt = $pdo->prepare('SELECT id,name,token FROM candidates WHERE election_id = ?');
$stmt->execute([$election_id]);
$crows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Élection créée</title><link rel="stylesheet" href="/assets/style.css"></head>
<body><div class="container">
  <h1>Élection créée</h1>
  <p>Lien élection: <a class="link" href="<?=$election_link?>"><?=$election_link?></a>
  <button onclick="copyToClipboard('<?=$election_link?>')">Copier</button></p>
  <h2>Liens candidats</h2>
  <?php foreach($crows as $c): ?>
    <div class="card" style="margin-bottom:8px">
      <strong><?=htmlspecialchars($c['name'])?></strong>
      <div><a class="link" href="/vote.php?c=<?=$c['token']?>">Lien candidat</a>
      <button onclick="copyToClipboard('<?= '/vote.php?c=' . $c['token'] ?>')">Copier</button></div>
    </div>
  <?php endforeach; ?>
  <p style="margin-top:12px"><a href="/">Accueil</a></p>
</div></body></html>
