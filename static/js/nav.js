// nav.js — manejo de menú, tema, idioma y moneda sin Bootstrap
(() => {
  const root = document.documentElement;
  const themeKey = 'nnm_theme';

  function applyTheme(mode){
    root.setAttribute('data-theme', mode);
    const icon = document.getElementById('themeIcon');
    if(icon) icon.className = mode === 'dark' ? 'bi bi-sun' : 'bi bi-moon';
    localStorage.setItem(themeKey, mode);
  }

  applyTheme(localStorage.getItem(themeKey) || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));

  document.getElementById('themeToggle')?.addEventListener('click', () => {
    applyTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
  });

  // Menú responsive
  const navToggle = document.getElementById('navToggle');
  const mainNav = document.getElementById('mainNav');
  navToggle?.addEventListener('click', () => {
    mainNav?.classList.toggle('open');
  });

  // Dropdowns
  document.querySelectorAll('.dropdown-toggle').forEach(btn => {
    btn.addEventListener('click', e => {
      e.preventDefault();
      const menu = btn.nextElementSibling;
      menu?.classList.toggle('show');
    });
  });
  document.addEventListener('click', e => {
    if (!e.target.closest('.dropdown')) {
      document.querySelectorAll('.dropdown-menu.show').forEach(m => m.classList.remove('show'));
    }
  });

  // Idioma
  document.querySelectorAll('#menuLang a[data-lang]').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      const lang = a.dataset.lang || 'es';
      document.getElementById('lblLang').textContent = lang.toUpperCase();
      localStorage.setItem('nnm_lang', lang);
      window.dispatchEvent(new CustomEvent('nnm:set-lang',{detail:{lang}}));
    });
  });

  // Moneda
  document.querySelectorAll('#menuCurrency a[data-cur]').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      const currency = a.dataset.cur || 'EUR';
      document.getElementById('lblCurrency').textContent = currency;
      localStorage.setItem('nnm_currency', currency);
      window.dispatchEvent(new CustomEvent('nnm:set-currency',{detail:{currency}}));
    });
  });

  // Estados iniciales
  const savedLang = localStorage.getItem('nnm_lang');
  if(savedLang){
    document.getElementById('lblLang').textContent = savedLang.toUpperCase();
    window.dispatchEvent(new CustomEvent('nnm:set-lang',{detail:{lang:savedLang}}));
  }
  const savedCur = localStorage.getItem('nnm_currency');
  if(savedCur){
    document.getElementById('lblCurrency').textContent = savedCur;
    window.dispatchEvent(new CustomEvent('nnm:set-currency',{detail:{currency:savedCur}}));
  }
})();
