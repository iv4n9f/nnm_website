// nav.js – menús, tema, i18n y currency
(() => {
  const $ = s => document.querySelector(s);
  const $$ = s => Array.from(document.querySelectorAll(s));

  // Tema
  const THEME_KEY = 'nnm_theme';
  function applyTheme(mode){
    const root = document.documentElement;
    if(mode==='dark'){ root.classList.add('theme-dark'); root.setAttribute('data-theme','dark'); }
    else { root.classList.remove('theme-dark'); root.setAttribute('data-theme','light'); }
    $('#themeIcon').textContent = mode==='dark' ? 'light_mode' : 'dark_mode';
    localStorage.setItem(THEME_KEY, mode);
  }
  applyTheme(localStorage.getItem(THEME_KEY) || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'));
  $('#themeToggle')?.addEventListener('click', () => {
    applyTheme(document.documentElement.classList.contains('theme-dark') ? 'light' : 'dark');
  });

  // Dropdown genérico
  function bindDropdown(rootSel, onSelect){
    const root = $(rootSel); if(!root) return;
    const btn = root.querySelector('.dropdown-btn');
    const menu = root.querySelector('.dropdown-menu');
    const closeAll = () => $$('.dropdown.open').forEach(d=>d.classList.remove('open'));
    btn.addEventListener('click', (e)=>{ e.stopPropagation(); closeAll(); root.classList.toggle('open'); });
    menu.addEventListener('click', (e)=>{
      const item = e.target.closest('.dropdown-item'); if(!item) return;
      onSelect(item);
      root.classList.remove('open');
    });
    document.addEventListener('click', closeAll);
  }

  // Idioma
  const LANG_KEY = 'nnm_lang';
  const curLang = localStorage.getItem(LANG_KEY) || 'es';
  $('#lblLang').textContent = curLang.toUpperCase();
  bindDropdown('#ddLang', (item)=>{
    const lang = item.dataset.lang;
    if(!lang) return;
    $('#lblLang').textContent = lang.toUpperCase();
    localStorage.setItem(LANG_KEY, lang);
    window.dispatchEvent(new CustomEvent('nnm:set-lang', {detail:{lang}}));
  });

  // Currency
  const CUR_KEY = 'nnm_currency';
  const cur = localStorage.getItem(CUR_KEY) || (window.NNM_CONFIG?.BASE_CURRENCY || 'EUR');
  $('#lblCurrency').textContent = cur;
  bindDropdown('#ddCurrency', (item)=>{
    const currency = item.dataset.cur;
    if(!currency) return;
    $('#lblCurrency').textContent = currency;
    localStorage.setItem(CUR_KEY, currency);
    window.dispatchEvent(new CustomEvent('nnm:set-currency', {detail:{currency}}));
  });

  // Inicializar conversión al cargar
  window.dispatchEvent(new CustomEvent('nnm:set-currency', {detail:{currency: cur}}));
  window.dispatchEvent(new CustomEvent('nnm:set-lang', {detail:{lang: curLang}}));
})();
