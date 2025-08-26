// i18n.js — carga diccionarios y aplica textos
(() => {
  const STORAGE_KEY = 'nnm_lang';
  const DEFAULT_LANG = 'es';
  const I18N_PATH = lang => `static/i18n/${lang}.json`;
  let currentDict = null;

  function tokenize(str) {
    if (!str || typeof str !== 'string') return str;
    const priceVpnEl = document.querySelector(".price[data-price='PRICE_VPN']");
    const unitEl = document.querySelector('.unit');
    const unit = (unitEl && unitEl.textContent.trim()) || ' €';
    return str.replace(/\{PRICE_VPN\}/g, priceVpnEl ? `${priceVpnEl.textContent}${unit}` : '{PRICE_VPN}');
  }

  async function loadDict(lang) {
    const res = await fetch(I18N_PATH(lang), { cache: 'no-store' });
    if (!res.ok) throw new Error('i18n load failed: '+lang);
    return res.json();
  }

  function applyText(dict) {
    if (!dict) return;
    document.querySelectorAll('[data-i18n]').forEach(el => {
      const k = el.getAttribute('data-i18n');
      if (k in dict) el.textContent = tokenize(dict[k]);
    });
    document.querySelectorAll('[data-i18n-attr]').forEach(el => {
      const pairs = el.getAttribute('data-i18n-attr').split(',').map(s=>s.trim()).filter(Boolean);
      pairs.forEach(pair => {
        const [attr,k] = pair.split(':').map(s=>s.trim());
        if(attr && k && k in dict) el.setAttribute(attr, tokenize(dict[k]));
      });
    });
  }

  async function setLang(lang){
    const dict = await loadDict(lang);
    currentDict = dict;
    localStorage.setItem(STORAGE_KEY, lang);
    applyText(dict);
    window.__NNM_I18N__ = { lang, get: k => dict[k], dict };
  }

  window.addEventListener('nnm:set-lang', e => setLang(e.detail?.lang || DEFAULT_LANG));
  window.addEventListener('nnm:currency-changed', () => applyText(currentDict));

  const browserEs = (navigator.language || '').toLowerCase().startsWith('es');
  const initial = localStorage.getItem(STORAGE_KEY) || (browserEs ? 'es' : 'en');
  setLang(initial);
})();
