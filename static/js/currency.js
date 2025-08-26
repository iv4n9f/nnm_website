// Solo divisa. EUR⇄USD con exchangerate.host → open.er-api.com. Caché 12 h.
// Emite 'nnm:currency-changed' tras pintar para que i18n reprocese tokens.
document.addEventListener("DOMContentLoaded", () => {
  const cfg   = window.NNM_CONFIG || { BASE_CURRENCY: "EUR", PRICES: {} };
  const base  = (cfg.BASE_CURRENCY || "EUR").toUpperCase();

  const ddBtn   = document.getElementById("currencyDropdown");
  const note    = document.getElementById("fxNote");
  const priceEls= [...document.querySelectorAll(".price")];
  const unitEls = [...document.querySelectorAll(".unit")];

  const baseMap = {
    PRICE_VPN: Number(cfg.PRICES?.PRICE_VPN || 0),
    PRICE_PASSWORD: Number(cfg.PRICES?.PRICE_PASSWORD || 0),
    PRICE_STORAGE: Number(cfg.PRICES?.PRICE_STORAGE || 0),
    PRICE_PACKAGE: Number(cfg.PRICES?.PRICE_PACKAGE || 0),
  };

  const CACHE_KEY = "nnm_fx_cache";
  const TTL_MS = 12 * 60 * 60 * 1000;

  function setCurrent(cur){
    if (ddBtn) ddBtn.textContent = cur;
    document.querySelectorAll(".js-currency").forEach(el => {
      el.classList.toggle("active", el.dataset.cur?.toUpperCase() === cur);
    });
  }

  function loadCache(pair){
    try {
      const j = JSON.parse(localStorage.getItem(CACHE_KEY) || "{}");
      if (j.pair === pair && Date.now() - (j.ts||0) < TTL_MS) return Number(j.rate);
    } catch(_) {}
    return null;
  }
  function saveCache(pair, rate){
    localStorage.setItem(CACHE_KEY, JSON.stringify({ pair, rate, ts: Date.now() }));
  }

  async function fx_primary(from, to){
    const url = `https://api.exchangerate.host/latest?base=${encodeURIComponent(from)}&symbols=${encodeURIComponent(to)}`;
    const r = await fetch(url, { cache: "no-store" });
    if (!r.ok) throw 0;
    const j = await r.json();
    const v = j?.rates?.[to];
    if (!v) throw 0;
    return Number(v);
  }
  async function fx_fallback(from, to){
    const url = `https://open.er-api.com/v6/latest/${encodeURIComponent(from)}`;
    const r = await fetch(url, { cache: "no-store" });
    if (!r.ok) throw 0;
    const j = await r.json();
    const v = j?.rates?.[to];
    if (!v) throw 0;
    return Number(v);
  }

  async function getRate(from, to){
    if (from === to) return 1;
    const pair = `${from}-${to}`;
    const cached = loadCache(pair);
    if (cached) return cached;
    try { const v1 = await fx_primary(from, to);   saveCache(pair, v1); return v1; } catch(_){}
    try { const v2 = await fx_fallback(from, to);  saveCache(pair, v2); return v2; } catch(_){}
    throw new Error("No FX source available");
  }

  function paintUnit(target){
    const symbol = target === "USD" ? " $" : " €";
    unitEls.forEach(u => { u.textContent = symbol; });
  }
  function paintPrices(rate, target){
    priceEls.forEach(el => {
      const key = el.getAttribute("data-price");
      const baseVal = Number(baseMap[key] || 0);
      el.textContent = (baseVal * rate).toFixed(2);
    });
    paintUnit(target);
    window.dispatchEvent(new CustomEvent("nnm:currency-changed", { detail: { target, rate } }));
  }

  async function render(target){
    const t = (target || localStorage.getItem("nnm_currency") || base).toUpperCase();
    try {
      const rate = await getRate(base, t);
      paintPrices(rate, t);
      setCurrent(t);
      if (note) note.textContent = rate === 1 ? "" : `Tipo de cambio ${base}/${t}: ${rate.toFixed(4)}`;
      localStorage.setItem("nnm_currency", t);
    } catch(e){
      paintPrices(1, base);
      setCurrent(base);
      if (note) note.textContent = "No se pudo obtener el tipo de cambio. Mostrando precios base.";
    }
  }

  // Click en items del dropdown
  document.addEventListener("click", (e) => {
    const btn = e.target.closest(".js-currency");
    if (!btn) return;
    const cur = (btn.dataset.cur || base).toUpperCase();
    render(cur);
  });

  // Init
  const saved = (localStorage.getItem("nnm_currency") || base).toUpperCase();
  setCurrent(saved);
  render(saved);
});
