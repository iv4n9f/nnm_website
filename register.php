<?php
require_once __DIR__.'/init.php';
require_once __DIR__.'/mail.php';
require_once __DIR__.'/helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Email inválido';
  } elseif (strlen($pass) < 8) {
    $error = 'Contraseña muy corta';
  } else {
    $hash = password_hash($pass, PASSWORD_ARGON2ID);
    try {
      $st = db()->prepare('INSERT INTO users(email, password_hash) VALUES(?,?)');
      $st->execute([$email,$hash]);
      send_mail('welcome', $email, ['name'=>$email, 'subject'=>'Bienvenido a NNM Secure']);
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
<head>
  <meta charset="utf-8">
  <title>Registro • NNM Secure</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5" style="max-width:420px;">
  <h1 class="h3 mb-3 text-center">Crear cuenta</h1>
  <?php if($error): ?><div class="alert alert-danger"><?=e($error)?></div><?php endif; ?>
  <form method="post" class="card card-body shadow-sm">
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label">Email</label>
      <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Contraseña</label>
      <input type="password" name="password" class="form-control" required>
      <div class="form-text">Mínimo 8 caracteres.</div>
    </div>
    <button type="submit" class="btn btn-primary w-100">Registrarse</button>
  </form>
  <p class="text-center mt-3"><a href="/login.php">¿Ya tienes cuenta? Inicia sesión</a></p>
</main>
</body>
</html>
