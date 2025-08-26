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
<main class="container" style="max-width:420px;padding:40px 0;">
  <h1 class="headline" style="font-size:24px;text-align:center" data-i18n="register.title">Crear cuenta</h1>
  <?php if($error): ?>
    <div class="card" style="margin-top:12px"><div class="content"><p class="subtle"><?=e($error)?></p></div></div>
  <?php endif; ?>
  <form method="post" class="card" style="margin-top:16px">
    <div class="content" style="display:flex;flex-direction:column;gap:12px">
      <?= csrf_field() ?>
      <label>Email
        <input type="email" name="email" required>
      </label>
      <label>Contraseña
        <input type="password" name="password" required>
        <span class="subtle" style="font-size:12px">Mínimo 8 caracteres.</span>
      </label>
      <label style="flex-direction:row;align-items:center;gap:8px">
        <input type="checkbox" name="accept_terms" required>
        <span>Acepto la <a class="link" href="/static/privacy.html" target="_blank">Privacidad</a>, <a class="link" href="/static/terms.html" target="_blank">Términos</a> y <a class="link" href="/static/cookies.html" target="_blank">Cookies</a>.</span>
      </label>
      <md-filled-button type="submit">Registrarse</md-filled-button>
    </div>
  </form>
  <p class="subtle" style="text-align:center;margin-top:12px"><a class="link" href="/login.php">¿Ya tienes cuenta? Inicia sesión</a></p>
  <p class="subtle" style="text-align:center;margin-top:12px">
    <a class="link" href="/static/privacy.html" target="_blank">Privacidad</a> ·
    <a class="link" href="/static/terms.html" target="_blank">Términos</a> ·
    <a class="link" href="/static/cookies.html" target="_blank">Cookies</a>
    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" name="accept_terms" id="acceptTerms" required>
      <label class="form-check-label" for="acceptTerms">
        Acepto la <a href="/static/privacy.html" target="_blank">Privacidad</a>, <a href="/static/terms.html" target="_blank">Términos</a> y <a href="/static/cookies.html" target="_blank">Cookies</a>.
      </label>
    </div>
  </form>
  <p class="subtle" style="text-align:center;margin-top:12px"><a class="link" href="/login.php">¿Ya tienes cuenta? Inicia sesión</a></p>
  <p class="subtle" style="text-align:center;margin-top:12px">
    <a class="link" href="/static/privacy.html" target="_blank">Privacidad</a> ·
    <a class="link" href="/static/terms.html" target="_blank">Términos</a> ·
    <a class="link" href="/static/cookies.html" target="_blank">Cookies</a>
  <p class="text-center mt-3"><a href="/login.php">¿Ya tienes cuenta? Inicia sesión</a></p>
  <p class="text-center small mt-3">
    <a href="/static/privacy.html" target="_blank">Privacidad</a> ·
    <a href="/static/terms.html" target="_blank">Términos</a> ·
    <a href="/static/cookies.html" target="_blank">Cookies</a>
  </p>
</main>
<?php include __DIR__.'/partials/footer.php'; ?>
