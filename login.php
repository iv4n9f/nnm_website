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
$title = 'Acceder • NNM Secure';
$description = 'Accede a tu cuenta segura en NNM Secure';
include __DIR__.'/partials/head.php';
?>
<main class="container" style="max-width:420px;padding:40px 0;">
  <h1 class="headline" style="font-size:24px;text-align:center" data-i18n="login.title">Iniciar sesión</h1>
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
      </label>
      <md-filled-button type="submit">Entrar</md-filled-button>
    </div>
  </form>
  <p class="subtle" style="text-align:center;margin-top:12px"><a class="link" href="/register.php">Crear cuenta</a></p>
  <p class="subtle" style="text-align:center;margin-top:12px">
    <a class="link" href="/static/privacy.html" target="_blank">Privacidad</a> ·
    <a class="link" href="/static/terms.html" target="_blank">Términos</a> ·
    <a class="link" href="/static/cookies.html" target="_blank">Cookies</a>
  </p>
</main>
<?php include __DIR__.'/partials/footer.php'; ?>
