<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Requête invalide';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if (login_user($email, $password)) {
            header('Location: /admin.php'); exit;
        } else {
            $error = 'Identifiants invalides';
        }
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Connexion</title><link rel="stylesheet" href="/assets/style.css"></head>
<body><div class="container">
  <h1>Connexion</h1>
  <?php if (!empty($error)): ?><div class="card muted"><?=htmlspecialchars($error)?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
    <label>Email</label>
    <input type="text" name="email" required>
    <label>Mot de passe</label>
    <input type="password" name="password" required>
    <div style="margin-top:12px"><button class="btn">Se connecter</button></div>
  </form>
  <p style="margin-top:12px">Pas encore de compte? <a href="/register.php">S'inscrire</a></p>
</div></body></html>
