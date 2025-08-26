<?php
declare(strict_types=1);
require_once __DIR__.'/init.php';
$config = include __DIR__.'/variables.php';
if (!function_exists('e')) { function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); } }
$isLogged = !empty($_SESSION['uid']);
$title = 'NNM Secure • VPN, Bitwarden y Nube cifrada';
$description = 'VPN WireGuard, Bitwarden autoalojado y nube cifrada con servidores en Reino Unido y Alemania.';
include __DIR__.'/partials/head.php';
?>

<section class="py-5 text-center bg-brand text-light">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-md-6 text-md-start">
        <h1 class="display-5 fw-bold" data-i18n="hero.title">Privacidad simple. Rendimiento constante.</h1>
        <p class="lead" data-i18n="hero.subtitle">VPN WireGuard, Bitwarden autoalojado y nube cifrada con servidores en Reino Unido y Alemania.</p>
        <div class="d-flex gap-2 justify-content-center justify-content-md-start align-items-center">
          <button class="btn btn-primary" onclick="document.querySelector('#services').scrollIntoView({behavior:'smooth'})" data-i18n="hero.cta_primary">Ver servicios</button>
          <button class="btn btn-outline-light" onclick="document.querySelector('#pricing').scrollIntoView({behavior:'smooth'})" data-i18n="hero.cta_secondary">Ver precios</button>
          <span class="badge bg-light text-dark">Desde <span class="price price-badge" data-price="PRICE_VPN"></span><span class="unit"></span> / mes</span>
        </div>
        <div class="d-flex gap-2 mt-3">
          <i class="bi bi-shield-lock"></i><strong>&nbsp;Cifrado serio</strong>
        </div>
        <div class="trustbar d-flex flex-wrap gap-4 mt-3 align-items-center">
          <img src="static/rsc/logos/wireguard.svg" alt="WireGuard">
          <img src="static/rsc/logos/bitwarden.webp" alt="Bitwarden">
          <img src="static/rsc/logos/seafile.png" alt="Seafile">
          <img src="static/rsc/logos/ovh.webp" alt="OVH">
          <img src="static/rsc/logos/cloudflare.png" alt="Cloudflare">
        </div>
      </div>
      <div class="col-md-6 mt-4 mt-md-0">
        <div class="card shadow">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <strong>Estado</strong><span class="text-muted">Infra UE</span>
            </div>
            <hr>
            <div class="row text-center">
              <div class="col">
                <strong>UK</strong>
                <div class="d-flex flex-column align-items-center">
                  <span class="status-dot bg-secondary" id="srv-uk"></span>
                  <div class="text-muted small">nnmsrvuk01</div>
                </div>
              </div>
              <div class="col">
                <strong>DE</strong>
                <div class="d-flex flex-column align-items-center">
                  <span class="status-dot bg-secondary" id="srv-de"></span>
                  <div class="text-muted small">nnmsrvde01</div>
                </div>
              </div>
              <div class="col">
                <strong>E2E</strong>
                <div class="d-flex flex-column align-items-center">
                  <span class="status-dot bg-secondary" id="srv-e2e"></span>
                  <div class="text-muted small">Bitwarden</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<main>
  <section id="services" class="py-5">
    <div class="container">
      <h2 class="h3 mb-4" data-i18n="sections.services.title">Servicios</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 shadow-sm lift"><div class="card-body d-flex flex-column gap-2">
            <img class="service-logo logo-dark" src="static/rsc/nnm-vpn-logo.png" alt="VPN">
            <div class="d-flex justify-content-between">
              <strong data-i18n="sections.services.card_vpn.title">VPN</strong>
              <span class="badge bg-secondary"><span class="price" data-price="PRICE_VPN"></span><span class="unit"></span> /m</span>
            </div>
            <ul class="text-muted">
              <li data-i18n="sections.services.card_vpn.p1">WireGuard por defecto.</li>
              <li data-i18n="sections.services.card_vpn.p2">Nodos UK/DE baja latencia.</li>
              <li data-i18n="sections.services.card_vpn.p3">Apps iOS/Android/Win/Linux.</li>
            </ul>
            <button class="btn btn-primary mt-auto" onclick="location.href='<?php echo $isLogged ? '/panel.php' : '/register.php'; ?>'">Empezar</button>
          </div></div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm lift"><div class="card-body d-flex flex-column gap-2">
            <img class="service-logo logo-dark" src="static/rsc/nnm-psw-logo.png" alt="Gestor">
            <div class="d-flex justify-content-between">
              <strong data-i18n="sections.services.card_password_manager.title">Gestor de contraseñas</strong>
              <span class="badge bg-secondary"><span class="price" data-price="PRICE_PASSWORD"></span><span class="unit"></span> /m</span>
            </div>
            <ul class="text-muted">
              <li data-i18n="sections.services.card_password_manager.p1">Cifrado E2E.</li>
              <li data-i18n="sections.services.card_password_manager.p2">Colecciones y compartición.</li>
              <li data-i18n="sections.services.card_password_manager.p3">Extensiones navegador.</li>
            </ul>
            <button class="btn btn-primary mt-auto" onclick="location.href='<?php echo $isLogged ? '/panel.php' : '/register.php'; ?>'">Empezar</button>
          </div></div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm lift"><div class="card-body d-flex flex-column gap-2">
            <img class="service-logo logo-dark" src="static/rsc/nnm-stg-logo.png" alt="Storage">
            <div class="d-flex justify-content-between">
              <strong data-i18n="sections.services.card_encrypted_storage.title">Almacenamiento cifrado</strong>
              <span class="badge bg-secondary"><span class="price" data-price="PRICE_STORAGE"></span><span class="unit"></span> /m</span>
            </div>
            <ul class="text-muted">
              <li data-i18n="sections.services.card_encrypted_storage.p1">Versionado y enlaces protegidos.</li>
              <li data-i18n="sections.services.card_encrypted_storage.p2">Clientes desktop y móvil.</li>
              <li data-i18n="sections.services.card_encrypted_storage.p3">Servidores en la UE.</li>
            </ul>
            <button class="btn btn-primary mt-auto" onclick="location.href='<?php echo $isLogged ? '/panel.php' : '/register.php'; ?>'">Empezar</button>
          </div></div>
        </div>
      </div>
    </div>
  </section>

  <section id="features" class="py-5 bg-body-secondary">
    <div class="container">
      <h2 class="h3 mb-4" data-i18n="sections.features.title">Características</h2>
      <div class="row g-4">
        <div class="col-md-4"><div class="card h-100 shadow-sm lift"><div class="card-body text-center">
          <i class="bi bi-shield-lock fs-1 text-primary mb-3"></i>
          <strong data-i18n="sections.features.f1.title">Cifrado serio</strong>
          <p class="text-muted" data-i18n="sections.features.f1.body">TLS moderno, WireGuard y buenas prácticas por defecto.</p>
        </div></div></div>
        <div class="col-md-4"><div class="card h-100 shadow-sm lift"><div class="card-body text-center">
          <i class="bi bi-speedometer2 fs-1 text-primary mb-3"></i>
          <strong data-i18n="sections.features.f2.title">Baja latencia</strong>
          <p class="text-muted" data-i18n="sections.features.f2.body">Nodos en Reino Unido y Alemania con rutas optimizadas.</p>
        </div></div></div>
        <div class="col-md-4"><div class="card h-100 shadow-sm lift"><div class="card-body text-center">
          <i class="bi bi-wind fs-1 text-primary mb-3"></i>
          <strong data-i18n="sections.features.f3.title">Sin humo</strong>
          <p class="text-muted" data-i18n="sections.features.f3.body">Sin registros innecesarios ni permanencias.</p>
        </div></div></div>
      </div>
    </div>
  </section>

  <section id="pricing" class="py-5 bg-body-secondary">
    <div class="container">
      <h2 class="h3 mb-4" data-i18n="sections.pricing.title">Precios</h2>
      <div class="row g-4">
        <?php
        $plans = [
          ['VPN','PRICE_VPN',['WireGuard','Sin registros','Apps multiplataforma']],
          ['Gestor','PRICE_PASSWORD',['Bitwarden self-hosted','Compartición segura','2FA y llaves']],
          ['Nube','PRICE_STORAGE',['Cifrado y versionado','Clientes desktop y móvil','Enlaces protegidos']],
          ['Paquete','PRICE_BUNDLE',['VPN + Gestor + Nube','Ahorro frente a planes sueltos','Soporte prioritario']],
        ];
        foreach ($plans as [$name,$key,$bullets]): ?>
        <div class="col-md-3">
          <div class="card h-100 shadow-sm lift"><div class="card-body d-flex flex-column gap-2">
            <strong><?= e($name) ?></strong>
            <div class="fs-3 fw-semibold"><span class="price" data-price="<?= e($key) ?>"></span><span class="unit"></span> /m</div>
            <ul class="text-muted">
              <?php foreach ($bullets as $b): ?><li><?= e($b) ?></li><?php endforeach; ?>
            </ul>
            <button class="btn btn-primary mt-auto" onclick="location.href='<?php echo $isLogged ? '/panel.php' : '/register.php'; ?>'">Empezar</button>
          </div></div>
        </div>
        <?php endforeach; ?>
      </div>
      <p class="text-muted mt-2" id="fxNote"></p>
    </div>
  </section>

  <section id="faq" class="py-5">
    <div class="container">
      <h2 class="h4 mb-4" data-i18n="sections.faq.title">Preguntas frecuentes</h2>
      <div class="faq">
        <details open>
          <summary>¿Guardáis registros?</summary>
          <p class="text-muted">No. Métricas agregadas mínimas para mantenimiento.</p>
        </details>
        <details>
          <summary>¿Cómo cancelo?</summary>
          <p class="text-muted">Desde tu panel. Sin permanencia.</p>
        </details>
      </div>
    </div>
  </section>

  <section id="contact" class="py-5">
    <div class="container">
      <h2 class="h4 mb-3" data-i18n="sections.contact.title">Contacto</h2>
      <p>Escríbenos a <a href="mailto:<?php echo e($config['MAIL'] ?? 'info@northnexusmex.cloud'); ?>"><?php echo e($config['MAIL'] ?? 'info@northnexusmex.cloud'); ?></a>.</p>
    </div>
  </section>
</main>

<footer class="border-top py-3">
  <div class="container d-flex justify-content-between">
    <span class="text-muted">&copy; <?php echo date('Y'); ?> <span data-i18n="brand">NNM Secure</span></span>
    <div class="d-flex gap-3">
      <a class="text-decoration-none" href="#top">Subir</a>
      <a class="text-decoration-none" href="/static/terms.html">Términos</a>
      <a class="text-decoration-none" href="/static/privacy.html">Privacidad</a>
    </div>
  </div>
</footer>

<script>
  window.NNM_CONFIG = {
    BASE_CURRENCY: '<?php echo e($config['BASE_CURRENCY'] ?? 'EUR'); ?>',
    UNIT: '<?php echo e($config['UNIT'] ?? '€'); ?>',
    PRICES: {}
  };
</script>
<script src="/static/js/index.js"></script>
<?php include __DIR__.'/partials/footer.php'; ?>
