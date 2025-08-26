// nav.js — gestiona tema, idioma y moneda con menus Material
(() => {
  const root = document.documentElement;
  const themeKey = 'nnm_theme';
  function applyTheme(mode){
    if(mode==='dark'){ root.classList.add('theme-dark'); root.setAttribute('data-theme','dark'); }
    else { root.classList.remove('theme-dark'); root.setAttribute('data-theme','light'); }
    document.getElementById('themeIcon').textContent = mode==='dark' ? 'light_mode' : 'dark_mode';
    localStorage.setItem(themeKey, mode);
  }
  applyTheme(localStorage.getItem(themeKey) || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark':'light'));
  document.getElementById('themeToggle')?.addEventListener('click', () => {
    applyTheme(root.classList.contains('theme-dark') ? 'light' : 'dark');
  });

  const menuLang = document.getElementById('menuLang');
  const btnLang  = document.getElementById('btnLang');
  btnLang?.addEventListener('click', () => menuLang.open = !menuLang.open);
  menuLang?.addEventListener('click', e => {
    const it = e.target.closest('md-menu-item'); if(!it) return;
    const lang = it.getAttribute('data-lang');
    document.getElementById('lblLang').textContent = (lang||'es').toUpperCase();
    menuLang.open = false;
    localStorage.setItem('nnm_lang', lang);
    window.dispatchEvent(new CustomEvent('nnm:set-lang',{detail:{lang}}));
  });

  const menuCur = document.getElementById('menuCurrency');
  const btnCur  = document.getElementById('btnCurrency');
  btnCur?.addEventListener('click', () => menuCur.open = !menuCur.open);
  menuCur?.addEventListener('click', e => {
    const it = e.target.closest('md-menu-item'); if(!it) return;
    const currency = it.getAttribute('data-cur');
    document.getElementById('lblCurrency').textContent = currency;
    menuCur.open = false;
    localStorage.setItem('nnm_currency', currency);
    window.dispatchEvent(new CustomEvent('nnm:set-currency',{detail:{currency}}));
  });

  // Inicializar valores almacenados
  const savedLang = localStorage.getItem('nnm_lang');
  if(savedLang) {
    document.getElementById('lblLang').textContent = savedLang.toUpperCase();
    window.dispatchEvent(new CustomEvent('nnm:set-lang',{detail:{lang:savedLang}}));
  }
  const savedCur = localStorage.getItem('nnm_currency');
  if(savedCur) {
    document.getElementById('lblCurrency').textContent = savedCur;
    window.dispatchEvent(new CustomEvent('nnm:set-currency',{detail:{currency:savedCur}}));
  }
  const navToggle = document.getElementById('navToggle');
  const mainNav = document.getElementById('mainNav');
  navToggle?.addEventListener('click', () => mainNav.classList.toggle('open'));
  mainNav?.addEventListener('click', e => { if(e.target.tagName === 'A') mainNav.classList.remove('open'); });
})();
