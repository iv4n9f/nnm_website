<?php
require_once __DIR__.'/init.php';
require_once __DIR__.'/helpers.php';
$title = 'Restablecer contraseña • NNM Secure';
$description = 'Solicita o completa el restablecimiento de contraseña.';
include __DIR__.'/partials/head.php';
$token = $_GET['token'] ?? '';
?>
<main class="container py-5" style="max-width:420px;">
  <h1 class="h4 text-center mb-4">Recuperar contraseña</h1>
  <form id="requestForm" class="card shadow p-4" <?= $token ? 'style="display:none"' : '' ?>>
    <div class="mb-3">
      <label class="form-label">Email
        <input type="email" name="email" class="form-control" required>
      </label>
    </div>
    <button class="btn btn-primary w-100" type="submit">Enviar enlace</button>
  </form>
  <form id="resetForm" class="card shadow p-4" <?= $token ? '' : 'style="display:none"' ?>>
    <input type="hidden" name="token" value="<?= e($token) ?>">
    <div class="mb-3">
      <label class="form-label">Nueva contraseña
        <input type="password" name="password" class="form-control" required>
      </label>
    </div>
    <button class="btn btn-primary w-100" type="submit">Cambiar contraseña</button>
  </form>
  <p class="text-center mt-3"><a href="/login.php">Volver a acceder</a></p>
</main>
<script>
(() => {
  const rForm = document.getElementById('requestForm');
  rForm?.addEventListener('submit', async e => {
    e.preventDefault();
    const email = rForm.email.value.trim();
    const res = await fetch('/api/reset_password.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({email})});
    alert(res.ok ? 'Revisa tu correo para continuar' : 'Error');
  });
  const resetForm = document.getElementById('resetForm');
  resetForm?.addEventListener('submit', async e => {
    e.preventDefault();
    const token = resetForm.token.value;
    const password = resetForm.password.value;
    const res = await fetch('/api/reset_password.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({token,password})});
    if(res.ok){ alert('Contraseña actualizada'); window.location='/login.php'; } else { alert('Error'); }
  });
})();
</script>
<?php include __DIR__.'/partials/footer.php'; ?>
