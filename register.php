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
$title = 'Registro • NNM Secure';
$description = 'Crea tu cuenta para acceder a los servicios cifrados de NNM Secure';
include __DIR__.'/partials/head.php';
?>
<main class="container py-5" style="max-width:420px;">
  <h1 class="h4 text-center" data-i18n="register.title">Crear cuenta</h1>
  <?php if($error): ?>
    <div class="alert alert-danger mt-3" role="alert"><?=e($error)?></div>
  <?php endif; ?>
  <form method="post" class="card p-4 mt-3">
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label">Email
        <input type="email" name="email" class="form-control" required>
      </label>
    </div>
    <div class="mb-3">
      <label class="form-label">Contraseña
        <input type="password" name="password" class="form-control" required>
        <div class="form-text">Mínimo 8 caracteres.</div>
      </label>
    </div>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="accept_terms" id="accept_terms" required>
      <label class="form-check-label" for="accept_terms">
        Acepto la <a class="link-secondary" href="/static/privacy.html" target="_blank">Privacidad</a>,
        <a class="link-secondary" href="/static/terms.html" target="_blank">Términos</a> y
        <a class="link-secondary" href="/static/cookies.html" target="_blank">Cookies</a>.
      </label>
    </div>
    <button class="btn btn-primary w-100" type="submit">Registrarse</button>
  </form>
  <p class="text-muted text-center mt-3"><a class="link-secondary" href="/login.php">¿Ya tienes cuenta? Inicia sesión</a></p>
  <p class="text-muted text-center mt-3">
    <a class="link-secondary" href="/static/privacy.html" target="_blank">Privacidad</a> ·
    <a class="link-secondary" href="/static/terms.html" target="_blank">Términos</a> ·
    <a class="link-secondary" href="/static/cookies.html" target="_blank">Cookies</a>
  </p>
</main>
<?php include __DIR__.'/partials/footer.php'; ?>
