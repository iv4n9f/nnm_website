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
<main class="container" style="padding:40px 0;">
  <h1 class="headline" style="font-size:24px;margin-bottom:16px">Panel</h1>
  <!-- Estado de suscripciones -->
  <div class="card" style="margin-bottom:24px">
    <div class="content">
      <div class="row" style="justify-content:space-between;align-items:center">
        <div>
          <strong>Estado de suscripciones</strong>
          <div class="subtle small">Verificación en tiempo real vía webhook y cache local.</div>
        </div>
        <md-outlined-button id="refreshBilling">Revalidar</md-outlined-button>
      </div>
      <div style="overflow-x:auto;margin-top:12px">
        <table class="table-simple">
          <thead><tr><th>Servicio</th><th>Estado</th><th>Hasta</th><th>Acción</th></tr></thead>
          <tbody id="billingRows">
            <?php
            foreach (['vpn','password','storage','bundle'] as $p) {
              $s = $subs[$p] ?? ['status'=>'—','until'=>0,'active'=>false];
              $active = $s['active'] ?? false;
              $badge = $active ? 'success' : 'secondary';
              $until = $s['until'] ? date('Y-m-d', (int)$s['until']) : '—';
              echo "<tr data-product='{$p}'>".
                   "<td>".strtoupper($p)."</td>".
                   "<td><span class='chip'>".e((string)($s['status']??'sin datos'))."</span></td>".
                   "<td>{$until}</td>".
                   "<td><md-outlined-button class='js-manage-sub' data-product='{$p}'>".($active?'Gestionar':'Suscribirse')."</md-outlined-button></td>".
                   "</tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Servicios -->
  <div class="grid grid-3">
    <?php
    $cards = [
      ['VPN','vpn',$cfg['WG_MODULE']],
      ['Gestor de contraseñas','password',$cfg['VAULTWARDEN_MODULE']],
      ['Almacenamiento cifrado','storage',$cfg['SEAFILE_MODULE']],
    ];
    foreach ($cards as [$titleCard,$key,$module]): ?>
    <div class="card"><div class="content" style="display:flex;flex-direction:column;gap:8px">
      <h5 style="margin:0;display:flex;justify-content:space-between;align-items:center">
        <span><?= e($titleCard) ?></span>
        <span class="row" style="gap:4px"><span class="status-dot bg-secondary" id="dot-<?= e($key) ?>"></span><small id="txt-<?= e($key) ?>">Desconocido</small></span>
      </h5>
      <div class="subtle small">Módulo: <code><?= e($module) ?></code></div>
      <div class="row" style="flex-wrap:wrap;gap:8px;margin-top:auto">
        <md-outlined-button class="js-action" data-product="<?= e($key) ?>" data-action="status">Estado</md-outlined-button>
        <md-outlined-button class="js-action" data-product="<?= e($key) ?>" data-action="start">Iniciar</md-outlined-button>
        <md-outlined-button class="js-action" data-product="<?= e($key) ?>" data-action="restart">Reiniciar</md-outlined-button>
        <md-outlined-button class="js-action" data-product="<?= e($key) ?>" data-action="stop">Detener</md-outlined-button>
        <?php if ($key === 'vpn'): ?>
          <md-filled-button class="js-action" data-product="vpn" data-action="provision" data-extra='{"client":"nuevo"}'>Alta cliente VPN</md-filled-button>
        <?php else: ?>
          <md-filled-button class="js-action" data-product="<?= e($key) ?>" data-action="admin-url">Abrir admin</md-filled-button>
        <?php endif; ?>
      </div>
    </div></div>
    <?php endforeach; ?>
  </div>

  <div class="card" style="margin-top:24px"><div class="content subtle small">
    Cambios quedan registrados en <code>ops_audit</code>. Acciones restringidas a suscripciones activas.
  </div></div>
</main>
<script src="/static/js/panel.js"></script>
<?php include __DIR__.'/partials/footer.php'; ?>
