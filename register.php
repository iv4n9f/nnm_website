<?php
require_once __DIR__.'/init.php';
require_once __DIR__.'/mail.php';
require_once __DIR__.'/helpers.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  csrf_verify();
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';
  $accept = isset($_POST['accept_terms']);
  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Email inválido';
  } elseif (strlen($pass) < 8) {
    $error = 'Contraseña muy corta';
  } elseif (!$accept) {
    $error = 'Debes aceptar las políticas.';
  } else {
    $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT;
    $hash = password_hash($pass, $algo);
    try {
      $st = db()->prepare('INSERT INTO users(email, password_hash) VALUES(?,?)');
      $st->execute([$email,$hash]);
      $uid = (int)db()->lastInsertId();
      send_mail('welcome', $email, ['name'=>$email, 'subject'=>'Bienvenido a NNM Secure']);
      $_SESSION['uid'] = $uid;
      audit($uid, 'consent_privacy', ['ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
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
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="accept_terms" id="acceptTerms" required>
      <label class="form-check-label" for="acceptTerms">
        Acepto la <a href="/static/privacy.html" target="_blank">Privacidad</a>, <a href="/static/terms.html" target="_blank">Términos</a> y <a href="/static/cookies.html" target="_blank">Cookies</a>.
      </label>
    </div>
    <button type="submit" class="btn btn-primary w-100">Registrarse</button>
  </form>
  <p class="text-center mt-3"><a href="/login.php">¿Ya tienes cuenta? Inicia sesión</a></p>
  <p class="text-center small mt-3">
    <a href="/static/privacy.html" target="_blank">Privacidad</a> ·
    <a href="/static/terms.html" target="_blank">Términos</a> ·
    <a href="/static/cookies.html" target="_blank">Cookies</a>
  </p>
</main>
</body>
</html>
