<?php
declare(strict_types=1);
require_once __DIR__.'/init.php';
require_once __DIR__.'/helpers.php';

$cfg = $CONFIG;
$u = require_login();
$subs = get_subs($u);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Panel • NNM Secure</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/static/css/styles.css" rel="stylesheet">
  <style>
    .card-hover:hover{transform:translateY(-2px);transition:.2s}
    .status-dot{width:.6rem;height:.6rem;border-radius:50%}
  </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="/">NNM Secure</a>
    <div class="ms-auto d-flex gap-2">
      <a class="btn btn-sm btn-outline-light" href="/logout.php">Salir</a>
    </div>
  </div>
</nav>

<main class="container my-4">
  <div class="row g-3">
    <!-- Estado de pago -->
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body d-flex flex-wrap align-items-center justify-content-between">
          <div>
            <h5 class="mb-1">Estado de suscripciones</h5>
            <div class="text-muted small">Verificación en tiempo real vía webhook y cache local.</div>
          </div>
          <button id="refreshBilling" class="btn btn-outline-primary btn-sm">Revalidar</button>
        </div>
        <div class="table-responsive">
          <table class="table table-sm mb-0 align-middle">
            <thead><tr><th>Servicio</th><th>Estado</th><th>Hasta</th><th>Acción</th></tr></thead>
            <tbody id="billingRows">
              <?php
              foreach (['vpn','password','storage','bundle'] as $p) {
                $s = $subs[$p] ?? ['status'=>'—','until'=>0,'active'=>false];
                $active = $s['active'] ?? false;
                $badge = $active ? 'success' : 'secondary';
                $until = $s['until'] ? date('Y-m-d', (int)$s['until']) : '—';
                echo "<tr data-product='{$p}'>
                  <td>".strtoupper($p)."</td>
                  <td><span class='badge bg-{$badge}'>".e((string)($s['status']??'sin datos'))."</span></td>
                  <td>{$until}</td>
                  <td>
                    <button class='btn btn-sm ".($active?'btn-outline-secondary':'btn-primary')." js-manage-sub'>".($active?'Gestionar':'Suscribirse')."</button>
                  </td>
                </tr>";
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Servicios -->
    <?php
    $cards = [
      ['VPN','vpn',$cfg['WG_MODULE']],
      ['Gestor de contraseñas','password',$cfg['VAULTWARDEN_MODULE']],
      ['Almacenamiento cifrado','storage',$cfg['SEAFILE_MODULE']],
    ];
    foreach ($cards as [$title,$key,$module]): ?>
    <div class="col-md-4">
      <div class="card h-100 shadow-sm card-hover">
        <div class="card-body d-flex flex-column">
          <h5 class="mb-2 d-flex align-items-center justify-content-between">
            <span><?= e($title) ?></span>
            <span class="d-inline-flex align-items-center gap-2">
              <span class="status-dot bg-secondary" id="dot-<?= e($key) ?>"></span>
              <small id="txt-<?= e($key) ?>">Desconocido</small>
            </span>
          </h5>
          <div class="small text-muted mb-3">Módulo: <code><?= e($module) ?></code></div>

          <div class="d-grid gap-2 mt-auto">
            <button class="btn btn-outline-primary js-action" data-product="<?= e($key) ?>" data-action="status">Estado</button>
            <button class="btn btn-outline-success js-action" data-product="<?= e($key) ?>" data-action="start">Iniciar</button>
            <button class="btn btn-outline-warning js-action" data-product="<?= e($key) ?>" data-action="restart">Reiniciar</button>
            <button class="btn btn-outline-danger js-action" data-product="<?= e($key) ?>" data-action="stop">Detener</button>
            <?php if ($key === 'vpn'): ?>
              <button class="btn btn-primary js-action" data-product="vpn" data-action="provision" data-extra='{"client":"nuevo"}'>Alta cliente VPN</button>
            <?php elseif ($key === 'password'): ?>
              <button class="btn btn-primary js-action" data-product="password" data-action="admin-url">Abrir admin</button>
            <?php else: ?>
              <button class="btn btn-primary js-action" data-product="storage" data-action="admin-url">Abrir admin</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="alert alert-light border mt-4 small">
    Cambios quedan registrados en <code>ops_audit</code>. Acciones restringidas a suscripciones activas.
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script src="/static/js/panel.js"></script>
</body>
</html>
