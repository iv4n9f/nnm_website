<?php $isLogged = !empty($_SESSION['uid']); ?>
<header class="app-bar">
  <a class="brand" href="/"><img class="brand-logo" src="/static/rsc/nnm-logo.png" alt="NNM"><span data-i18n="brand">NNM Secure</span></a>
  <nav class="nav-links">
    <a href="/#services" data-i18n="nav.services">Servicios</a>
    <a href="/#features" data-i18n="nav.features">Características</a>
    <a href="/#pricing" data-i18n="nav.pricing">Precios</a>
    <a href="/#faq" data-i18n="nav.faq">FAQ</a>
    <a href="/#contact" data-i18n="nav.contact">Contacto</a>
  </nav>
  <md-outlined-button id="btnLang"><span class="material-symbols-rounded" style="margin-right:.35rem">translate</span><span id="lblLang">ES</span></md-outlined-button>
  <md-menu id="menuLang" anchor="btnLang">
    <md-menu-item data-lang="es"><div slot="headline">Español</div></md-menu-item>
    <md-menu-item data-lang="en"><div slot="headline">English</div></md-menu-item>
  </md-menu>
  <md-outlined-button id="btnCurrency"><span class="material-symbols-rounded" style="margin-right:.35rem">payments</span><span id="lblCurrency">EUR</span></md-outlined-button>
  <md-menu id="menuCurrency" anchor="btnCurrency">
    <md-menu-item data-cur="EUR"><div slot="headline">EUR €</div></md-menu-item>
    <md-menu-item data-cur="USD"><div slot="headline">USD $</div></md-menu-item>
  </md-menu>
  <md-icon-button id="themeToggle" aria-label="Tema"><span class="material-symbols-rounded" id="themeIcon">dark_mode</span></md-icon-button>
  <?php if ($isLogged): ?>
    <md-filled-button onclick="location.href='/panel.php'">Panel</md-filled-button>
    <md-outlined-button onclick="location.href='/logout.php'">Salir</md-outlined-button>
  <?php else: ?>
    <md-outlined-button onclick="location.href='/login.php'">Login</md-outlined-button>
    <md-filled-button onclick="location.href='/register.php'">Registro</md-filled-button>
  <?php endif; ?>
</header>
