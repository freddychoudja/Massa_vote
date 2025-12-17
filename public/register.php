<?php
require_once __DIR__ . '/../src/auth.php';
require_once __DIR__ . '/../src/utils.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!verify_csrf($_POST['csrf_token'] ?? '')) { $error = 'Requête invalide'; }
    elseif (!$name || !$email || !$password) { $error = 'Tous les champs sont requis'; }
    elseif (!validate_email($email)) { $error = 'Email invalide'; }
    else {
        try {
            $id = register_user($name, $email, $password);
            $_SESSION['user_id'] = $id;
            header('Location: /admin.php'); exit;
        } catch (Exception $e) {
            $error = 'Impossible de créer le compte (email déjà utilisé?)';
        }
    }
}
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Inscription</title><link rel="stylesheet" href="/assets/style.css"></head>
<body><div class="container">
  <h1>Inscription</h1>
  <?php if (!empty($error)): ?><div class="card muted"><?=htmlspecialchars($error)?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?=h(csrf_token())?>">
    <label>Nom</label>
    <input type="text" name="name" required>
    <label>Email</label>
    <input type="text" name="email" required>
    <label>Mot de passe</label>
    <input type="password" name="password" required>
    <div style="margin-top:12px"><button class="btn">S'inscrire</button></div>
  </form>
  <p style="margin-top:12px">Vous avez déjà un compte? <a href="/login.php">Se connecter</a></p>
</div></body></html>
