<?php
require_once __DIR__ . '/../src/db.php';
$pdo = get_pdo();

$e_token = $_GET['e'] ?? null;
$c_token = $_GET['c'] ?? null;

if ($c_token) {
    $stmt = $pdo->prepare('SELECT c.*, e.is_paid, e.price_cents, e.provider FROM candidates c JOIN elections e ON c.election_id = e.id WHERE c.token = ?');
    $stmt->execute([$c_token]);
    $cand = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$cand) die('Candidat introuvable');
  $election = $cand;
  $cdata = $cand;
} elseif ($e_token) {
    $stmt = $pdo->prepare('SELECT * FROM elections WHERE token = ?');
    $stmt->execute([$e_token]);
    $election = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$election) die('Élection introuvable');
    $stmt = $pdo->prepare('SELECT * FROM candidates WHERE election_id = ?');
    $stmt->execute([$election['id']]);
    $cands = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    header('Location: /'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Voting flow: check if paid
    $candidate_id = intval($_POST['candidate_id']);
    $stmt = $pdo->prepare('SELECT c.*, e.is_paid, e.price_cents, e.provider FROM candidates c JOIN elections e ON c.election_id = e.id WHERE c.id = ?');
    $stmt->execute([$candidate_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) die('Erreur');
    if ($row['is_paid']) {
        // create payment record and redirect to pay simulation
        $tx = 'tx_' . bin2hex(random_bytes(6));
        $insert = $pdo->prepare('INSERT INTO payments (election_id, candidate_id, amount_cents, provider, tx_ref, status) VALUES (?, ?, ?, ?, ?, ?)');
        $insert->execute([$row['election_id'], $candidate_id, $row['price_cents'], $row['provider'], $tx, 'pending']);
        header('Location: /pay.php?tx=' . $tx); exit;
    } else {
        // direct vote
        $upd = $pdo->prepare('UPDATE candidates SET votes = votes + 1 WHERE id = ?');
        $upd->execute([$candidate_id]);
        echo '<!doctype html><html><head><meta charset="utf-8"><link rel="stylesheet" href="/assets/style.css"></head><body><div class="container"><h1>Merci pour votre vote</h1><p><a href="/">Retour</a></p></div></body></html>';
        exit;
    }
}

?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Vote</title><link rel="stylesheet" href="/assets/style.css"></head>
<body style="<?php if (!empty($election['theme_color'])) echo 'background:' . htmlspecialchars($election['theme_color']) . ';' ?>">
<div class="container" style="<?php if (!empty($election['theme_color'])) echo 'border-color:' . htmlspecialchars($election['theme_color']) . ';' ?>">
  <?php if (!empty($election['logo_url'])): ?>
    <div style="text-align:center;margin-bottom:12px"><img src="<?=htmlspecialchars($election['logo_url'])?>" alt="logo" style="max-height:64px"></div>
  <?php endif; ?>
  <?php if (isset($cdata)): ?>
    <h1>Voter pour <?=htmlspecialchars($cdata['name'])?></h1>
    <p class="muted">Élection: <?=htmlspecialchars($election['title'] ?? $cdata['name'])?></p>
    <?php if ($cdata['is_paid']): ?>
      <p class="muted">Vote payant: <?=($cdata['price_cents']/100)?> via <?=$cdata['provider']?></p>
    <?php endif; ?>
    <form method="post">
      <input type="hidden" name="candidate_id" value="<?=$cdata['id']?>">
      <button class="btn">Voter</button>
    </form>
  <?php else: ?>
    <h1><?=htmlspecialchars($election['title'])?></h1>
    <p class="muted"><?=htmlspecialchars($election['description'])?></p>
    <?php if (!empty($election['theme_color'])): ?>
      <style> .btn{background: <?=htmlspecialchars($election['theme_color'])?>; } </style>
    <?php endif; ?>
    <h2>Candidats</h2>
    <?php foreach($cands as $c): ?>
      <div class="candidate">
        <div><?=htmlspecialchars($c['name'])?></div>
        <div>
          <form style="display:inline" method="post">
            <input type="hidden" name="candidate_id" value="<?=$c['id']?>">
            <button class="btn">Voter</button>
          </form>
          <a class="link" href="/vote.php?c=<?=$c['token']?>">Lien candidat</a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  <p style="margin-top:12px"><a href="/">Accueil</a></p>
</div></body></html>
