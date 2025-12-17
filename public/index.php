<?php
require_once __DIR__ . '/../src/db.php';
$pdo = get_pdo();
$stmt = $pdo->query('SELECT id, title, description, token, is_paid, price_cents, provider FROM elections ORDER BY created_at DESC');
$elections = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Massa Vote</title>
  <link rel="stylesheet" href="/assets/style.css">
  <script src="/assets/app.js"></script>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  </head>
<body>
<div class="container">
  <h1>Massa Vote</h1>
  <p class="muted">Liste des élections publiques. Créer une élection: <a href="/admin.php">Admin</a></p>

  <?php if(!$elections): ?>
    <div class="card">Aucune élection. <a href="/admin.php">Créer</a></div>
  <?php else: ?>
    <?php foreach($elections as $e): ?>
      <div class="card" style="margin-bottom:12px">
        <h2><?=htmlspecialchars($e['title'])?></h2>
        <p class="muted"><?=htmlspecialchars($e['description'])?></p>
        <div style="margin-top:8px">
          <a class="link" href="/vote.php?e=<?=$e['token']?>">Lien élection</a>
          &nbsp;|&nbsp;
          <?php if($e['is_paid']): ?>
            <span class="muted">Payant (<?=($e['price_cents']/100)?>) via <?=$e['provider']?></span>
          <?php else: ?>
            <span class="muted">Gratuit</span>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>

</div>
</body>
</html>
