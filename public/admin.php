<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/db.php';
require_login();
$user = current_user();

$pdo = get_pdo();
$stmt = $pdo->prepare('SELECT * FROM elections WHERE owner_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$elections = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin - Massa Vote</title>
  <link rel="stylesheet" href="/assets/style.css">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  </head>
<body>
<div class="container">
  <h1>Mon espace - <?=htmlspecialchars($user['name'])?></h1>
  <p class="muted">Créer et gérer vos élections. Vous êtes connecté en tant que <?=htmlspecialchars($user['email'])?>. <a href="/logout.php">Se déconnecter</a></p>

  <h2>Créer une élection</h2>
  <form action="/create_election.php" method="post" enctype="multipart/form-data">
    <?php require_once __DIR__ . '/../src/utils.php'; ?>
    <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
    <label>Intitulé</label>
    <input type="text" name="title" required>
    <label>Description</label>
    <textarea name="description"></textarea>
    <label>Liste des candidats (séparés par des virgules)</label>
    <input type="text" name="candidates" placeholder="Ex: Alice, Bob, Carol" required>
    <label>Payant ?</label>
    <select name="is_paid">
      <option value="0">Non (gratuit)</option>
      <option value="1">Oui (payant)</option>
    </select>
    <label>Prix (si payant) en FCFA</label>
    <input type="text" name="price" placeholder="Ex: 100">
    <label>Fournisseur paiement (MTN/Orange)</label>
    <select name="provider">
      <option value="MTN">MTN</option>
      <option value="Orange">Orange</option>
    </select>
    <label>Couleur thème (ex: #2563eb)</label>
    <input type="text" name="theme_color" placeholder="#2563eb">
    <label>Logo (URL publique)</label>
    <input type="text" name="logo_url" placeholder="https://...">
    <label>Ou téléverser un logo (PNG/JPEG/SVG/WebP, max 2MB)</label>
    <input type="file" name="logo_file" accept="image/*">
    <div style="margin-top:12px">
      <button class="btn">Créer l'élection</button>
    </div>
  </form>

  <h2 style="margin-top:18px">Mes élections</h2>
  <?php if (!$elections): ?>
    <div class="card">Vous n'avez pas encore d'élection.</div>
  <?php else: ?>
    <?php foreach($elections as $e): ?>
      <div class="card" style="margin-bottom:12px">
        <h3><?=htmlspecialchars($e['title'])?></h3>
        <p class="muted"><?=htmlspecialchars($e['description'])?></p>
        <div style="margin-top:8px">
          <a class="link" href="/vote.php?e=<?=$e['token']?>">Lien élection</a>
          &nbsp;|&nbsp;
          <a class="link" href="/admin_edit.php?e=<?=$e['id']?>">Modifier</a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

  <p style="margin-top:12px"><a href="/">Retour à l'accueil</a></p>
</div>
</body>
</html>
