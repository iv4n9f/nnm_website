<?php
require_once __DIR__.'/init.php';
require_once __DIR__.'/helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Email inválido';
  } else {
    $hash = password_hash($pass, PASSWORD_ARGON2ID);
    try {
      $st = db()->prepare('INSERT INTO users(email, pass_hash) VALUES(?,?)');
      $st->execute([$email,$hash]);
      $_SESSION['uid'] = (int)db()->lastInsertId();
      header('Location: /panel.php');
      exit;
    } catch (PDOException $e) {
      $error = 'Usuario ya existe';
    }
  }
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Registro</title></head>
<body>
<h1>Registro</h1>
<?php if($error): ?><div style="color:red"><?=e($error)?></div><?php endif; ?>
<form method="post">
  <?= csrf_field() ?>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Contraseña" required>
  <button type="submit">Registrarse</button>
</form>
</body>
</html>
