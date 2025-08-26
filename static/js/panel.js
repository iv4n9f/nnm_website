(() => {
  const q = s => document.querySelector(s);
  const qa = s => Array.from(document.querySelectorAll(s));

  function setStatus(product, ok, text){
    const dot = q('#dot-'+product);
    const t = q('#txt-'+product);
    if(!dot||!t) return;
    dot.className = 'status-dot ' + (ok ? 'bg-success' : 'bg-secondary');
    t.textContent = text || (ok?'Activo':'Detenido');
  }

  async function call(url, opts){
    const r = await fetch(url, opts);
    if(!r.ok) throw new Error(await r.text());
    return r.json();
  }

  qa('.js-action').forEach(btn => {
    btn.addEventListener('click', async () => {
      const product = btn.dataset.product;
      const action = btn.dataset.action;
      const extra = btn.dataset.extra ? JSON.parse(btn.dataset.extra) : {};
      btn.disabled = true;
      try {
        let data;
        if(action==='status'){
          data = await call(`/api/status.php?product=${product}`);
        } else {
          data = await call('/api/action.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({product, action, extra})
          });
        }
        const ok = !!data.ok;
        let text = 'Hecho';
        if (data.output) {
          const m = /STATUS:\s*(\w+)/i.exec(data.output);
          if (m) text = m[1];
        }
        setStatus(product, ok, text);
        if (action==='admin-url' && data.output) {
          const url = data.output.trim().split('\n').pop();
          if(/^https?:\/\//.test(url)) window.open(url,'_blank');
        }
      } catch(e){
        setStatus(product, false, 'Error');
        console.error(e);
        alert('Error: '+e.message);
      } finally {
        btn.disabled = false;
      }
    });
  });

  q('#refreshBilling')?.addEventListener('click', async () => {
    const rows = await call('/api/billing_refresh.php');
    Object.entries(rows).forEach(([prod, s]) => {
      const card = document.querySelector(`.sub-card[data-product="${prod}"]`);
      if(!card) return;
      card.querySelector('.chip').textContent = s.status;
      const untilEl = card.querySelector('.js-until');
      if (untilEl) untilEl.textContent = s.until ? new Date(s.until*1000).toISOString().slice(0,10) : '—';
      const btn = card.querySelector('.js-manage-sub');
      if (btn) btn.textContent = s.active ? 'Gestionar' : 'Suscribirse';
    });
  });

  qa('.js-manage-sub').forEach(btn => {
    btn.addEventListener('click', async () => {
      const card = btn.closest('.sub-card');
      const product = card?.dataset.product;
      try {
        if (btn.textContent.includes('Gestionar')) {
          const {url} = await call('/api/stripe_portal.php', {method:'POST'});
          window.location = url;
        } else {
          const {url} = await call('/api/stripe_checkout.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({product})
          });
          window.location = url;
        }
      } catch(e){
        alert('Error: '+e.message);
      }
    });
  });
})();
