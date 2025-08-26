<?php
require_once __DIR__.'/init.php';
require_once __DIR__.'/helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  $st = db()->prepare('SELECT * FROM users WHERE email=?');
  $st->execute([$email]);
  $u = $st->fetch();
  if ($u && password_verify($pass, $u['pass_hash'])) {
    $_SESSION['uid'] = $u['id'];
    header('Location: /panel.php');
    exit;
  } else {
    $error = 'Credenciales inválidas';
  }
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Login</title></head>
<body>
<h1>Acceder</h1>
<?php if($error): ?><div style="color:red"><?=e($error)?></div><?php endif; ?>
<form method="post">
  <?= csrf_field() ?>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Contraseña" required>
  <button type="submit">Entrar</button>
</form>
</body>
</html>
