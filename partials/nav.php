<?php $isLogged = !empty($_SESSION['uid']); ?>
<nav class="navbar shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand" href="/">
      <img src="/static/rsc/nnm-logo.png" alt="NNM" width="28" height="28" class="logo-dark">
      <span data-i18n="brand">NNM Secure</span>
    </a>
    <button class="navbar-toggler" id="navToggle" aria-label="Menú">
      <i class="bi bi-list"></i>
    </button>
    <div class="navbar-menu" id="mainNav">
      <ul class="navbar-nav">
        <li><a class="nav-link" href="/#services" data-i18n="nav.services">Servicios</a></li>
        <li><a class="nav-link" href="/#features" data-i18n="nav.features">Características</a></li>
        <li><a class="nav-link" href="/#pricing" data-i18n="nav.pricing">Precios</a></li>
        <li><a class="nav-link" href="/#faq" data-i18n="nav.faq">FAQ</a></li>
        <li><a class="nav-link" href="/#contact" data-i18n="nav.contact">Contacto</a></li>
      </ul>
      <div class="nav-actions">
        <div class="dropdown">
          <button class="btn btn-outline-primary dropdown-toggle" id="btnLang"><i class="bi bi-translate"></i> <span id="lblLang">ES</span></button>
          <ul class="dropdown-menu" id="menuLang">
            <li><a class="dropdown-item" data-lang="es" href="#">Español</a></li>
            <li><a class="dropdown-item" data-lang="en" href="#">English</a></li>
          </ul>
        </div>
        <div class="dropdown">
          <button class="btn btn-outline-primary dropdown-toggle" id="btnCurrency"><i class="bi bi-currency-exchange"></i> <span id="lblCurrency">EUR</span></button>
          <ul class="dropdown-menu" id="menuCurrency">
            <li><a class="dropdown-item" data-cur="EUR" href="#">EUR €</a></li>
            <li><a class="dropdown-item" data-cur="USD" href="#">USD $</a></li>
          </ul>
        </div>
        <button class="btn btn-outline-primary" id="themeToggle" aria-label="Tema"><i class="bi bi-moon" id="themeIcon"></i></button>
        <?php if ($isLogged): ?>
          <a class="btn btn-primary" href="/panel.php">Panel</a>
          <a class="btn btn-outline-primary" href="/logout.php">Salir</a>
        <?php else: ?>
          <a class="btn btn-outline-primary" href="/login.php">Login</a>
          <a class="btn btn-primary" href="/register.php">Registro</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
