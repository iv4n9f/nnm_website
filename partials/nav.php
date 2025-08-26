<?php $isLogged = !empty($_SESSION['uid']); ?>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center gap-2" href="/">
      <img src="/static/rsc/nnm-logo.png" alt="NNM" width="28" height="28" class="rounded-circle">
      <span data-i18n="brand">NNM Secure</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/#services" data-i18n="nav.services">Servicios</a></li>
        <li class="nav-item"><a class="nav-link" href="/#features" data-i18n="nav.features">Características</a></li>
        <li class="nav-item"><a class="nav-link" href="/#pricing" data-i18n="nav.pricing">Precios</a></li>
        <li class="nav-item"><a class="nav-link" href="/#faq" data-i18n="nav.faq">FAQ</a></li>
        <li class="nav-item"><a class="nav-link" href="/#contact" data-i18n="nav.contact">Contacto</a></li>
      </ul>
      <div class="d-flex align-items-center gap-2">
        <div class="dropdown">
          <button class="btn btn-outline-secondary dropdown-toggle" id="btnLang" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-translate"></i> <span id="lblLang">ES</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="btnLang" id="menuLang">
            <li><a class="dropdown-item" data-lang="es" href="#">Español</a></li>
            <li><a class="dropdown-item" data-lang="en" href="#">English</a></li>
          </ul>
        </div>
        <div class="dropdown">
          <button class="btn btn-outline-secondary dropdown-toggle" id="btnCurrency" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-currency-exchange"></i> <span id="lblCurrency">EUR</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="btnCurrency" id="menuCurrency">
            <li><a class="dropdown-item" data-cur="EUR" href="#">EUR €</a></li>
            <li><a class="dropdown-item" data-cur="USD" href="#">USD $</a></li>
          </ul>
        </div>
        <button class="btn btn-outline-secondary" id="themeToggle" aria-label="Tema"><i class="bi bi-moon" id="themeIcon"></i></button>
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
