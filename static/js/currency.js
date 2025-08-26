// currency.js — convierte precios según divisa elegida
(() => {
  const cfg   = window.NNM_CONFIG || { BASE_CURRENCY: 'EUR', PRICES: {} };
  const base  = (cfg.BASE_CURRENCY || 'EUR').toUpperCase();
  const note    = document.getElementById('fxNote');
  const priceEls= [...document.querySelectorAll('.price')];
  const unitEls = [...document.querySelectorAll('.unit')];
  const baseMap = {
    PRICE_VPN: Number(cfg.PRICES?.PRICE_VPN || 0),
    PRICE_PASSWORD: Number(cfg.PRICES?.PRICE_PASSWORD || 0),
    PRICE_STORAGE: Number(cfg.PRICES?.PRICE_STORAGE || 0),
    PRICE_PACKAGE: Number(cfg.PRICES?.PRICE_PACKAGE || 0),
  };
  const CACHE_KEY = 'nnm_fx_cache';
  const TTL_MS = 12 * 60 * 60 * 1000;
  function loadCache(pair){
    try {
      const j = JSON.parse(localStorage.getItem(CACHE_KEY) || '{}');
      if (j.pair === pair && Date.now() - (j.ts||0) < TTL_MS) return Number(j.rate);
    } catch(_) {}
    return null;
  }
  function saveCache(pair, rate){
    localStorage.setItem(CACHE_KEY, JSON.stringify({ pair, rate, ts: Date.now() }));
  }
  async function fx(from, to){
    const url = `https://api.exchangerate.host/latest?base=${encodeURIComponent(from)}&symbols=${encodeURIComponent(to)}`;
    const r = await fetch(url, { cache: 'no-store' });
    if (!r.ok) throw 0;
    const j = await r.json();
    const v = j?.rates?.[to];
    if (!v) throw 0;
    return Number(v);
  }
  async function getRate(from, to){
    if(from===to) return 1;
    const pair = `${from}-${to}`;
    const cached = loadCache(pair);
    if(cached) return cached;
    const v = await fx(from, to);
    saveCache(pair, v);
    return v;
  }
  function paintUnit(target){
    const symbol = target === 'USD' ? ' $' : ' €';
    unitEls.forEach(u => { u.textContent = symbol; });
  }
  function paintPrices(rate, target){
    priceEls.forEach(el => {
      const key = el.getAttribute('data-price');
      const baseVal = Number(baseMap[key] || 0);
      el.textContent = (baseVal * rate).toFixed(2);
    });
    paintUnit(target);
    window.dispatchEvent(new CustomEvent('nnm:currency-changed',{detail:{target,rate}}));
  }
  async function render(target){
    const t = (target || base).toUpperCase();
    try {
      const rate = await getRate(base, t);
      paintPrices(rate, t);
      if (note) note.textContent = rate === 1 ? '' : `Tipo de cambio ${base}/${t}: ${rate.toFixed(4)}`;
      localStorage.setItem('nnm_currency', t);
    } catch {
      paintPrices(1, base);
      if (note) note.textContent = 'No se pudo obtener el tipo de cambio. Mostrando precios base.';
    }
  }
  window.addEventListener('nnm:set-currency', e => render(e.detail?.currency));
  // Inicial
  const initCur = localStorage.getItem('nnm_currency') || base;
  render(initCur);
})();
