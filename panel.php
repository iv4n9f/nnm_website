<?php
declare(strict_types=1);
require_once __DIR__.'/init.php';
require_once __DIR__.'/helpers.php';
$cfg = $CONFIG;
$u = require_login();
$subs = get_subs($u);
$title = 'Panel • NNM Secure';
$description = 'Gestión centralizada de servicios y suscripciones';
include __DIR__.'/partials/head.php';
?>
<main class="container py-5">
  <h1 class="display-6 mb-4">Panel</h1>
  <section class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="h5 m-0">Suscripciones</h2>
      <button class="btn btn-outline-primary btn-sm" id="refreshBilling">Revalidar</button>
    </div>
    <div class="row g-3" id="billingCards">
      <?php
      foreach (['vpn','password','storage','bundle'] as $p) {
        $s = $subs[$p] ?? ['status'=>'—','until'=>0,'active'=>false];
        $until = $s['until'] ? date('Y-m-d', (int)$s['until']) : '—';
        $label = strtoupper($p);
        echo "<div class='col-sm-6 col-lg-3'><div class='card h-100 shadow-sm sub-card' data-product='{$p}'><div class='card-body d-flex flex-column gap-2'>".
             "<strong>{$label}</strong>".
             "<span class='badge bg-secondary chip'>".e((string)$s['status'])."</span>".
             "<small class='text-muted'>Hasta <span class='js-until'>{$until}</span></small>".
             "<button class='btn btn-outline-primary btn-sm mt-auto js-manage-sub' data-product='{$p}'>".(($s['active'] ?? false)?'Gestionar':'Suscribirse')."</button>".
             "</div></div></div>";
      }
      ?>
    </div>
  </section>

  <section class="mb-5">
    <h2 class="h5 mb-3">Servicios</h2>
    <div class="row g-4">
      <div class="col-md-4">
        <div class="card h-100 shadow-sm"><div class="card-body d-flex flex-column gap-3">
          <h5 class="m-0">VPN</h5>
          <select id="vpnServer" class="form-select">
            <option value="uk">Servidor UK</option>
            <option value="de">Servidor DE</option>
          </select>
          <button class="btn btn-primary mt-auto" id="genVpn">Generar configuración</button>
        </div></div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm"><div class="card-body d-flex flex-column gap-3">
          <h5 class="m-0">Gestor de contraseñas</h5>
          <button class="btn btn-primary mt-auto" id="pwReset">Enviar correo de activación</button>
        </div></div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm"><div class="card-body d-flex flex-column gap-3">
          <h5 class="m-0">Almacenamiento cifrado</h5>
          <button class="btn btn-primary" id="sfInvite">Enviar invitación</button>
          <button class="btn btn-outline-primary mt-auto" id="sfUpgrade">Ampliar almacenamiento</button>
        </div></div>
      </div>
    </div>
  </section>

    <div class="card shadow-sm mt-4"><div class="card-body text-muted small">
    Cambios quedan registrados en <code>ops_audit</code>. Acciones restringidas a suscripciones activas.
  </div></div>
</main>
<script src="/static/js/panel.js"></script>
<?php include __DIR__.'/partials/footer.php'; ?>
