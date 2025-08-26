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

<!-- Hero -->
<section class="hero">
  <div class="container">
    <div class="grid grid-2" style="align-items:center">
      <div>
        <h1 class="headline" data-i18n="hero.title">Privacidad simple. Rendimiento constante.</h1>
        <p class="subtle" data-i18n="hero.subtitle">VPN WireGuard, Bitwarden autoalojado y nube cifrada con servidores en Reino Unido y Alemania.</p>
        <div class="row" style="margin-top:10px">
          <md-filled-button onclick="document.querySelector('#services').scrollIntoView({behavior:'smooth'})" data-i18n="hero.cta_primary">Ver servicios</md-filled-button>
          <md-outlined-button onclick="document.querySelector('#pricing').scrollIntoView({behavior:'smooth'})" data-i18n="hero.cta_secondary">Ver precios</md-outlined-button>
          <span class="chip subtle">Desde <span class="price price-badge" data-price="PRICE_VPN" style="margin:0 .25rem"></span><span class="unit"></span> / mes</span>
        </div>
        <div class="row" style="margin-top:14px">
          <div class="row"><span class="material-symbols-rounded">shield_lock</span><strong>&nbsp;Cifrado serio</strong></div>
          <span class="subtle">Sin registros</span>
        </div>
        <div class="trustbar">
          <img src="static/rsc/logos/wireguard.svg" alt="WireGuard">
          <img src="static/rsc/logos/bitwarden.webp" alt="Bitwarden">
          <img src="static/rsc/logos/seafile.png" alt="Seafile">
          <img src="static/rsc/logos/ovh.webp" alt="OVH">
          <img src="static/rsc/logos/cloudflare.png" alt="Cloudflare">
        </div>
      </div>
      <div>
        <div class="card"><div class="content">
          <div class="row" style="justify-content:space-between">
            <strong>Estado</strong><span class="subtle">Infra UE</span>
          </div>
          <md-divider style="margin:12px 0"></md-divider>
          <div class="grid grid-3">
            <div><strong>UK</strong><div class="subtle small">nnmsrvuk01</div></div>
            <div><strong>DE</strong><div class="subtle small">nnmsrvde01</div></div>
            <div><strong>E2E</strong><div class="subtle small">Bitwarden</div></div>
          </div>
        </div></div>
      </div>
    </div>
  </div>
</section>

<main>
  <!-- Servicios -->
  <section id="services" class="section">
    <div class="container">
      <h2 class="headline" style="font-size:var(--headline-md)" data-i18n="sections.services.title">Servicios</h2>
      <div class="grid grid-3" style="margin-top:12px">
        <!-- VPN -->
        <div class="card"><div class="content" style="display:flex;flex-direction:column;gap:12px">
          <img class="service-logo" src="static/rsc/nnm-vpn-logo.png" alt="VPN">
          <div class="row" style="justify-content:space-between">
            <strong data-i18n="sections.services.card_vpn.title">VPN</strong>
            <span class="chip"><span class="price" data-price="PRICE_VPN"></span><span class="unit"></span> /m</span>
          </div>
          <ul class="subtle">
            <li data-i18n="sections.services.card_vpn.p1">WireGuard por defecto.</li>
            <li data-i18n="sections.services.card_vpn.p2">Nodos UK/DE baja latencia.</li>
            <li data-i18n="sections.services.card_vpn.p3">Apps iOS/Android/Win/Linux.</li>
          </ul>
          <md-filled-button onclick="location.href='<?php echo $isLogged ? '/panel.php' : '/register.php'; ?>'">Empezar</md-filled-button>
        </div></div>
        <!-- Gestor -->
        <div class="card"><div class="content" style="display:flex;flex-direction:column;gap:12px">
          <img class="service-logo" src="static/rsc/nnm-psw-logo.png" alt="Gestor">
          <div class="row" style="justify-content:space-between">
            <strong data-i18n="sections.services.card_password_manager.title">Gestor de contraseñas</strong>
            <span class="chip"><span class="price" data-price="PRICE_PASSWORD"></span><span class="unit"></span> /m</span>
          </div>
          <ul class="subtle">
            <li data-i18n="sections.services.card_password_manager.p1">Cifrado E2E.</li>
            <li data-i18n="sections.services.card_password_manager.p2">Colecciones y compartición.</li>
            <li data-i18n="sections.services.card_password_manager.p3">Extensiones navegador.</li>
          </ul>
          <md-filled-button onclick="location.href='<?php echo $isLogged ? '/panel.php' : '/register.php'; ?>'">Empezar</md-filled-button>
        </div></div>
        <!-- Storage -->
        <div class="card"><div class="content" style="display:flex;flex-direction:column;gap:12px">
          <img class="service-logo" src="static/rsc/nnm-stg-logo.png" alt="Storage">
          <div class="row" style="justify-content:space-between">
            <strong data-i18n="sections.services.card_encrypted_storage.title">Almacenamiento cifrado</strong>
            <span class="chip"><span class="price" data-price="PRICE_STORAGE"></span><span class="unit"></span> /m</span>
          </div>
          <ul class="subtle">
            <li data-i18n="sections.services.card_encrypted_storage.p1">Versionado y enlaces protegidos.</li>
            <li data-i18n="sections.services.card_encrypted_storage.p2">Clientes desktop y móvil.</li>
            <li data-i18n="sections.services.card_encrypted_storage.p3">Servidores en la UE.</li>
          </ul>
          <md-filled-button onclick="location.href='<?php echo $isLogged ? '/panel.php' : '/register.php'; ?>'">Empezar</md-filled-button>
        </div></div>
      </div>
    </div>
  </section>
  <!-- Características -->
  <section id="features" class="section" style="background:var(--md-surface-variant)">
    <div class="container">
      <h2 class="headline" style="font-size:var(--headline-md)" data-i18n="sections.features.title">Características</h2>
      <div class="grid grid-3" style="margin-top:12px">
        <div class="card"><div class="content">
          <strong data-i18n="sections.features.f1.title">Cifrado serio</strong>
          <p class="subtle" data-i18n="sections.features.f1.body">TLS moderno, WireGuard y buenas prácticas por defecto.</p>
        </div></div>
        <div class="card"><div class="content">
          <strong data-i18n="sections.features.f2.title">Baja latencia</strong>
          <p class="subtle" data-i18n="sections.features.f2.body">Nodos en Reino Unido y Alemania con rutas optimizadas.</p>
        </div></div>
        <div class="card"><div class="content">
          <strong data-i18n="sections.features.f3.title">Sin humo</strong>
          <p class="subtle" data-i18n="sections.features.f3.body">Sin registros innecesarios ni permanencias.</p>
  <!-- Precios -->
  <section id="pricing" class="section" style="background:var(--md-surface-variant)">
    <div class="container">
      <h2 class="headline" style="font-size:var(--headline-md)" data-i18n="sections.pricing.title">Precios</h2>
      <div class="grid grid-4" style="margin-top:12px">
        <?php
        $plans = [
          ['VPN','PRICE_VPN',['WireGuard','Sin registros','Apps multiplataforma']],
          ['Gestor','PRICE_PASSWORD',['Bitwarden self-hosted','Compartición segura','2FA y llaves']],
          ['Nube','PRICE_STORAGE',['Cifrado y versionado','Clientes desktop y móvil','Enlaces protegidos']],
          ['Paquete','PRICE_PACKAGE',['VPN + Gestor + Nube','Ahorro frente a planes sueltos','Soporte prioritario']],
        ];
        foreach ($plans as [$name,$key,$bullets]): ?>
        <div class="card"><div class="content" style="display:flex;flex-direction:column;gap:10px">
          <strong><?= e($name) ?></strong>
          <div style="font-size:28px;font-weight:600"><span class="price" data-price="<?= e($key) ?>"></span><span class="unit"></span> /m</div>
          <ul class="subtle">
            <?php foreach ($bullets as $b): ?><li><?= e($b) ?></li><?php endforeach; ?>
          </ul>
          <md-filled-button onclick="location.href='<?php echo $isLogged ? '/panel.php' : '/register.php'; ?>'">Empezar</md-filled-button>
        </div></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- Precios -->
  <section id="pricing" class="section" style="background:var(--md-surface-variant)">
    <div class="container">
      <h2 class="headline" style="font-size:var(--headline-md)" data-i18n="sections.pricing.title">Precios</h2>
      <div class="grid grid-4" style="margin-top:12px">
        <?php
        $plans = [
          ['VPN','PRICE_VPN',['WireGuard','Sin registros','Apps multiplataforma']],
          ['Gestor','PRICE_PASSWORD',['Bitwarden self-hosted','Compartición segura','2FA y llaves']],
          ['Nube','PRICE_STORAGE',['Cifrado y versionado','Clientes desktop y móvil','Enlaces protegidos']],
          ['Paquete','PRICE_PACKAGE',['VPN + Gestor + Nube','Ahorro frente a planes sueltos','Soporte prioritario']],
        ];
        foreach ($plans as [$name,$key,$bullets]): ?>
        <div class="card"><div class="content" style="display:flex;flex-direction:column;gap:10px">
          <strong><?= e($name) ?></strong>
          <div style="font-size:28px;font-weight:600"><span class="price" data-price="<?= e($key) ?>"></span><span class="unit"></span> /m</div>
          <ul class="subtle">
            <?php foreach ($bullets as $b): ?><li><?= e($b) ?></li><?php endforeach; ?>
          </ul>
          <md-filled-button onclick="location.href='<?php echo $isLogged ? '/panel.php' : '/register.php'; ?>'">Empezar</md-filled-button>
        </div></div>
        <?php endforeach; ?>
      </div>
      <p class="subtle" id="fxNote" style="margin-top:8px"></p>
    </div>
  </section>

  <!-- FAQ -->
  <section id="faq" class="section">
    <div class="container">
      <h2 class="headline" style="font-size:24px" data-i18n="sections.faq.title">Preguntas frecuentes</h2>
      <div class="card" style="margin-top:12px"><div class="content">
        <details open><summary>¿Guardáis registros?</summary><p class="subtle">No. Métricas agregadas mínimas para mantenimiento.</p></details>
        <md-divider style="margin:12px 0"></md-divider>
        <details><summary>¿Cómo cancelo?</summary><p class="subtle">Desde tu panel. Sin permanencia.</p></details>
      </div></div>
    </div>
  </section>

  <!-- Contacto -->
  <section id="contact" class="section">
    <div class="container">
      <h2 class="headline" style="font-size:24px" data-i18n="sections.contact.title">Contacto</h2>
      <p>Escríbenos a <a class="link" href="mailto:<?php echo e($config['MAIL'] ?? 'info@northnexusmex.cloud'); ?>"><?php echo e($config['MAIL'] ?? 'info@northnexusmex.cloud'); ?></a>.</p>
    </div>
  </section>
</main>

<footer>
  <div class="container row" style="justify-content:space-between">
    <span class="subtle">&copy; <?php echo date('Y'); ?> <span data-i18n="brand">NNM Secure</span></span>
    <div class="row"><a class="link" href="#top">Subir</a><a class="link" href="/static/terms.html">Términos</a><a class="link" href="/static/privacy.html">Privacidad</a></div>
  </div>
</footer>

<script>
  window.NNM_CONFIG = {
    BASE_CURRENCY: '<?php echo e($config['BASE_CURRENCY'] ?? 'EUR'); ?>',
    UNIT: '<?php echo e($config['UNIT'] ?? '€'); ?>',
    PRICES: {
      PRICE_VPN: <?php echo (int)round(100 * (float)($config['PRICE_VPN'] ?? 1)) / 100; ?>,
      PRICE_PASSWORD: <?php echo (int)round(100 * (float)($config['PRICE_PASSWORD'] ?? 1)) / 100; ?>,
      PRICE_STORAGE: <?php echo (int)round(100 * (float)($config['PRICE_STORAGE'] ?? 1)) / 100; ?>,
      PRICE_PACKAGE: <?php echo (int)round(100 * (float)($config['PRICE_PACKAGE'] ?? 3)) / 100; ?>
    }
  };
</script>
<?php include __DIR__.'/partials/footer.php'; ?>
