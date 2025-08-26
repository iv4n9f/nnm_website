<?php $isLogged = !empty($_SESSION['uid']); ?>
<nav class="bg-white dark:bg-gray-900 shadow-sm">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
    <a class="flex items-center gap-2 font-semibold text-indigo-600 dark:text-indigo-400" href="/">
      <img src="/static/rsc/nnm-logo.png" alt="NNM" class="w-7 h-7 logo-dark">
      <span data-i18n="brand">NNM Secure</span>
    </a>
    <button class="md:hidden text-2xl" id="navToggle" aria-label="Menú">
      <i class="bi bi-list"></i>
    </button>
    <div class="hidden flex flex-col md:flex md:flex-row md:items-center md:space-x-6" id="mainNav">
      <ul class="flex flex-col md:flex-row md:space-x-4">
        <li><a class="block py-2 md:py-0 hover:text-indigo-600" href="/#services" data-i18n="nav.services">Servicios</a></li>
        <li><a class="block py-2 md:py-0 hover:text-indigo-600" href="/#features" data-i18n="nav.features">Características</a></li>
        <li><a class="block py-2 md:py-0 hover:text-indigo-600" href="/#pricing" data-i18n="nav.pricing">Precios</a></li>
        <li><a class="block py-2 md:py-0 hover:text-indigo-600" href="/#faq" data-i18n="nav.faq">FAQ</a></li>
        <li><a class="block py-2 md:py-0 hover:text-indigo-600" href="/#contact" data-i18n="nav.contact">Contacto</a></li>
      </ul>
      <div class="flex items-center space-x-2 mt-2 md:mt-0">
        <div class="relative dropdown">
          <button class="dropdown-toggle flex items-center gap-1 px-3 py-2 rounded border border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white transition" id="btnLang"><i class="bi bi-translate"></i> <span id="lblLang">ES</span></button>
          <ul class="dropdown-menu absolute right-0 mt-1 hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-md" id="menuLang">
            <li><a class="block px-3 py-1 hover:bg-indigo-600 hover:text-white" data-lang="es" href="#">Español</a></li>
            <li><a class="block px-3 py-1 hover:bg-indigo-600 hover:text-white" data-lang="en" href="#">English</a></li>
          </ul>
        </div>
        <div class="relative dropdown">
          <button class="dropdown-toggle flex items-center gap-1 px-3 py-2 rounded border border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white transition" id="btnCurrency"><i class="bi bi-currency-exchange"></i> <span id="lblCurrency">EUR</span></button>
          <ul class="dropdown-menu absolute right-0 mt-1 hidden bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded shadow-md" id="menuCurrency">
            <li><a class="block px-3 py-1 hover:bg-indigo-600 hover:text-white" data-cur="EUR" href="#">EUR €</a></li>
            <li><a class="block px-3 py-1 hover:bg-indigo-600 hover:text-white" data-cur="USD" href="#">USD $</a></li>
          </ul>
        </div>
        <button class="px-3 py-2 rounded border border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white transition" id="themeToggle" aria-label="Tema"><i class="bi bi-moon" id="themeIcon"></i></button>
        <?php if ($isLogged): ?>
          <a class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 transition" href="/panel.php">Panel</a>
          <a class="px-3 py-2 rounded border border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white transition" href="/logout.php">Salir</a>
        <?php else: ?>
          <a class="px-3 py-2 rounded border border-indigo-600 text-indigo-600 hover:bg-indigo-600 hover:text-white transition" href="/login.php">Login</a>
          <a class="px-3 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 transition" href="/register.php">Registro</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
