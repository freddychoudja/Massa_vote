<?php
require_once __DIR__ . '/../src/db.php';
$pdo = get_pdo();

$tx = $_GET['tx'] ?? null;
if (!$tx) die('Transaction introuvable');

$stmt = $pdo->prepare('SELECT p.*, c.name as candidate_name, e.title as election_title FROM payments p LEFT JOIN candidates c ON p.candidate_id = c.id LEFT JOIN elections e ON p.election_id = e.id WHERE p.tx_ref = ?');
$stmt->execute([$tx]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$payment) die('Paiement introuvable');

// Simulation: page to "confirmer" le paiement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // mark payment as success and increment vote
    $upd = $pdo->prepare('UPDATE payments SET status = ? WHERE tx_ref = ?');
    $upd->execute(['success', $tx]);
    if ($payment['candidate_id']) {
        $u2 = $pdo->prepare('UPDATE candidates SET votes = votes + 1 WHERE id = ?');
        $u2->execute([$payment['candidate_id']]);
    }
    echo '<!doctype html><html><head><meta charset="utf-8"><link rel="stylesheet" href="/assets/style.css"></head><body><div class="container"><h1>Paiement réussi</h1><p>Merci, votre vote a été pris en compte.</p><p><a href="/">Accueil</a></p></div></body></html>';
    exit;
}

?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Paiement</title><link rel="stylesheet" href="/assets/style.css"></head>
<body><div class="container">
  <h1>Paiement (simulation)</h1>
  <p>Élection: <?=htmlspecialchars($payment['election_title'])?></p>
  <p>Candidat: <?=htmlspecialchars($payment['candidate_name'])?></p>
  <p>Montant: <?=($payment['amount_cents']/100)?> FCFA</p>
  <p>Fournisseur: <?=htmlspecialchars($payment['provider'])?></p>
  <form method="post">
    <button class="btn">Simuler paiement réussi</button>
  </form>
  <p class="muted">Note: pour intégration réelle, rediriger vers l'API de MTN/Orange et gérer webhooks.</p>
  <p style="margin-top:12px"><a href="/">Accueil</a></p>
</div></body></html>
