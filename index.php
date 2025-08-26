<?php
declare(strict_types=1);
require_once __DIR__.'/init.php';
$config = include __DIR__.'/variables.php';
if (!function_exists('e')) { function e(string $v): string { return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); } }
$isLogged = !empty($_SESSION['uid']);
?>
<!doctype html>
<html lang="es" data-theme="light">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NNM Secure • VPN, Bitwarden y Nube cifrada</title>
  <meta name="color-scheme" content="light dark">
  <link rel="icon" href="static/rsc/nnm-logo.ico">
  <link rel="stylesheet" href="static/css/styles.css">
  <link rel="stylesheet" href="static/css/material.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
  <script type="importmap">{"imports":{"@material/web/":"https://unpkg.com/@material/web@1.4.0/"}}</script>
  <script type="module">
    import "@material/web/button/filled-button.js";
    import "@material/web/button/outlined-button.js";
    import "@material/web/menu/menu.js";
    import "@material/web/menu/menu-item.js";
    import "@material/web/iconbutton/icon-button.js";
    import "@material/web/card/elevated-card.js";
    import "@material/web/divider/divider.js";
  </script>
</head>
<body id="top">
  <!-- App bar -->
  <header class="app-bar">
    <a class="brand" href="/"><img class="brand-logo" src="static/rsc/nnm-logo.png" alt="NNM"><span data-i18n="brand">NNM Secure</span></a>

    <nav class="nav-links">
      <a href="#services" data-i18n="nav.services">Servicios</a>
      <a href="#features" data-i18n="nav.features">Características</a>
      <a href="#pricing" data-i18n="nav.pricing">Precios</a>
      <a href="#faq" data-i18n="nav.faq">FAQ</a>
      <a href="#contact" data-i18n="nav.contact">Contacto</a>
    </nav>

    <!-- Idioma -->
    <md-outlined-button id="btnLang"><span class="material-symbols-rounded" style="margin-right:.35rem">translate</span><span id="lblLang">ES</span></md-outlined-button>
    <md-menu id="menuLang" anchor="btnLang">
      <md-menu-item data-lang="es"><div slot="headline">Español</div></md-menu-item>
      <md-menu-item data-lang="en"><div slot="headline">English</div></md-menu-item>
    </md-menu>

    <!-- Moneda -->
    <md-outlined-button id="btnCurrency"><span class="material-symbols-rounded" style="margin-right:.35rem">payments</span><span id="lblCurrency">EUR</span></md-outlined-button>
    <md-menu id="menuCurrency" anchor="btnCurrency">
      <md-menu-item data-cur="EUR"><div slot="headline">EUR €</div></md-menu-item>
      <md-menu-item data-cur="USD"><div slot="headline">USD $</div></md-menu-item>
    </md-menu>

    <!-- Tema -->
    <md-icon-button id="themeToggle" aria-label="Tema"><span class="material-symbols-rounded" id="themeIcon">dark_mode</span></md-icon-button>

    <!-- Auth -->
    <?php if ($isLogged): ?>
      <md-filled-button onclick="location.href='/panel.php'">Panel</md-filled-button>
      <md-outlined-button onclick="location.href='/logout.php'">Salir</md-outlined-button>
    <?php else: ?>
      <md-outlined-button onclick="location.href='/login.php'">Login</md-outlined-button>
      <md-filled-button onclick="location.href='/register.php'">Registro</md-filled-button>
    <?php endif; ?>
  </header>

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
      <div class="row"><a class="link" href="#top">Subir</a><a class="link" href="#">Términos</a><a class="link" href="#">Privacidad</a></div>
    </div>
  </footer>

  <!-- Config global -->
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

  <!-- Theme + menús -->
  <script>
    const themeKey='nnm_theme';
    const applyTheme=(m)=>{ const r=document.documentElement; m==='dark'?r.classList.add('theme-dark'):r.classList.remove('theme-dark'); r.setAttribute('data-theme',m); document.getElementById('themeIcon').textContent=m==='dark'?'light_mode':'dark_mode'; localStorage.setItem(themeKey,m); };
    applyTheme(localStorage.getItem(themeKey)|| (matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'));
    document.getElementById('themeToggle')?.addEventListener('click',()=>applyTheme(document.documentElement.classList.contains('theme-dark')?'light':'dark'));

    // Menús
    const menuLang=document.getElementById('menuLang'), btnLang=document.getElementById('btnLang');
    btnLang?.addEventListener('click',()=>menuLang.open=!menuLang.open);
    menuLang?.addEventListener('click',e=>{const it=e.target.closest('md-menu-item'); if(!it) return; const lang=it.getAttribute('data-lang'); document.getElementById('lblLang').textContent=(lang||'es').toUpperCase(); window.dispatchEvent(new CustomEvent('nnm:set-lang',{detail:{lang}}));});

    const menuCur=document.getElementById('menuCurrency'), btnCur=document.getElementById('btnCurrency');
    btnCur?.addEventListener('click',()=>menuCur.open=!menuCur.open);
    menuCur?.addEventListener('click',e=>{const it=e.target.closest('md-menu-item'); if(!it) return; const c=it.getAttribute('data-cur'); document.getElementById('lblCurrency').textContent=c; window.dispatchEvent(new CustomEvent('nnm:set-currency',{detail:{currency:c}}));});
  </script>

  <!-- i18n + currency -->
  <script src="static/js/i18n.js"></script>
  <script src="static/js/currency.js"></script>
</body>
</html>
