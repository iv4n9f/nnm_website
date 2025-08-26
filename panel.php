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
  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <strong>Estado de suscripciones</strong>
          <div class="text-muted small">Verificación en tiempo real vía webhook y cache local.</div>
        </div>
        <button class="btn btn-outline-primary btn-sm" id="refreshBilling">Revalidar</button>
      </div>
      <div class="table-responsive mt-3">
          <table class="table">
          <thead><tr><th>Servicio</th><th>Estado</th><th>Hasta</th><th>Acción</th></tr></thead>
          <tbody id="billingRows">
            <?php
            foreach (['vpn','password','storage','bundle'] as $p) {
              $s = $subs[$p] ?? ['status'=>'—','until'=>0,'active'=>false];
              $until = $s['until'] ? date('Y-m-d', (int)$s['until']) : '—';
              echo "<tr data-product='{$p}'>".
                   "<td>".strtoupper($p)."</td>".
                   "<td><span class='badge text-bg-secondary chip'>".e((string)($s['status']??'sin datos'))."</span></td>".
                   "<td>{$until}</td>".
                   "<td><button class='btn btn-outline-primary btn-sm js-manage-sub' data-product='{$p}'>".(($s['active'] ?? false)?'Gestionar':'Suscribirse')."</button></td>".
                   "</tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <?php
    $cards = [
      ['VPN','vpn',$cfg['WG_MODULE']],
      ['Gestor de contraseñas','password',$cfg['VAULTWARDEN_MODULE']],
      ['Almacenamiento cifrado','storage',$cfg['SEAFILE_MODULE']],
    ];
    foreach ($cards as [$titleCard,$key,$module]): ?>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm"><div class="card-body d-flex flex-column gap-2">
        <h5 class="d-flex justify-content-between align-items-center m-0">
          <span><?= e($titleCard) ?></span>
          <span class="d-flex gap-1 align-items-center"><span class="status-dot bg-secondary" id="dot-<?= e($key) ?>"></span><small id="txt-<?= e($key) ?>">Desconocido</small></span>
        </h5>
        <div class="text-muted small">Módulo: <code><?= e($module) ?></code></div>
        <div class="d-flex flex-wrap gap-2 mt-auto">
          <button class="btn btn-outline-primary btn-sm js-action" data-product="<?= e($key) ?>" data-action="status">Estado</button>
          <button class="btn btn-outline-primary btn-sm js-action" data-product="<?= e($key) ?>" data-action="start">Iniciar</button>
          <button class="btn btn-outline-primary btn-sm js-action" data-product="<?= e($key) ?>" data-action="restart">Reiniciar</button>
          <button class="btn btn-outline-primary btn-sm js-action" data-product="<?= e($key) ?>" data-action="stop">Detener</button>
          <?php if ($key === 'vpn'): ?>
            <button class="btn btn-primary btn-sm js-action" data-product="vpn" data-action="provision" data-extra='{"client":"nuevo"}'>Alta cliente VPN</button>
          <?php else: ?>
            <button class="btn btn-primary btn-sm js-action" data-product="<?= e($key) ?>" data-action="admin-url">Abrir admin</button>
          <?php endif; ?>
        </div>
      </div></div>
    </div>
    <?php endforeach; ?>
  </div>

    <div class="card shadow-sm mt-4"><div class="card-body text-muted small">
    Cambios quedan registrados en <code>ops_audit</code>. Acciones restringidas a suscripciones activas.
  </div></div>
</main>
<script src="/static/js/panel.js"></script>
<?php include __DIR__.'/partials/footer.php'; ?>
