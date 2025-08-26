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
<main class="container py-5" style="max-width:420px;">
  <h1 class="h4 text-center" data-i18n="login.title">Iniciar sesión</h1>
  <?php if($error): ?>
    <div class="alert alert-danger mt-3" role="alert"><?=e($error)?></div>
  <?php endif; ?>
    <form method="post" class="card shadow p-4 mt-3">
    <?= csrf_field() ?>
    <div class="mb-3">
      <label class="form-label">Email
        <input type="email" name="email" class="form-control" required>
      </label>
    </div>
    <div class="mb-3">
      <label class="form-label">Contraseña
        <input type="password" name="password" class="form-control" required>
      </label>
    </div>
    <button class="btn btn-primary w-100" type="submit">Entrar</button>
    <p class="text-end mt-2"><a class="link-secondary" href="/reset.php">¿Olvidaste tu contraseña?</a></p>
  </form>
  <p class="text-muted text-center mt-3"><a class="link-secondary" href="/register.php">Crear cuenta</a></p>
  <p class="text-muted text-center mt-3">
    <a class="link-secondary" href="/static/privacy.html" target="_blank">Privacidad</a> ·
    <a class="link-secondary" href="/static/terms.html" target="_blank">Términos</a> ·
    <a class="link-secondary" href="/static/cookies.html" target="_blank">Cookies</a>
  </p>
</main>
<?php include __DIR__.'/partials/footer.php'; ?>
