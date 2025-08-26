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
  if ($u && password_verify($pass, $u['password_hash'] ?? '')) {
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
<head>
  <meta charset="utf-8">
  <title>Acceder • NNM Secure</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5" style="max-width:420px;">
  <h1 class="h3 mb-3 text-center">Iniciar sesión</h1>
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
    </div>
    <button type="submit" class="btn btn-primary w-100">Entrar</button>
  </form>
  <p class="text-center mt-3"><a href="/register.php">Crear cuenta</a></p>
</main>
</body>
</html>
