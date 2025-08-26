(function(){
  const q = s=>document.querySelector(s);
  const qa = s=>Array.from(document.querySelectorAll(s));

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

  qa('.js-action').forEach(btn=>{
    btn.addEventListener('click', async ()=>{
      const product = btn.dataset.product;
      const action = btn.dataset.action;
      const extra = btn.dataset.extra ? JSON.parse(btn.dataset.extra) : {};
      btn.disabled = true;
      try{
        let data;
        if(action==='status'){
          data = await call(`/api/status.php?product=${product}`);
        }else{
          data = await call('/api/action.php', {
            method:'POST',
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({product, action, extra})
          });
        }
        const ok = !!data.ok;
        let text = 'Hecho';
        if (data.output) {
          const m = /STATUS:\\s*(\\w+)/i.exec(data.output);
          if (m) text = m[1];
        }
        setStatus(product, ok, text);
        if (action==='admin-url' && data.output) {
          const url = data.output.trim().split('\\n').pop();
          if(/^https?:\\/\\//.test(url)) window.open(url,'_blank');
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

  q('#refreshBilling')?.addEventListener('click', async ()=>{
    const rows = await call('/api/billing_refresh.php');
    Object.entries(rows).forEach(([prod, s])=>{
      const tr = document.querySelector(`tr[data-product=\"${prod}\"]`);
      if(!tr) return;
      tr.querySelector('.badge').className = 'badge bg-' + (s.active ? 'success' : 'secondary');
      tr.querySelector('.badge').textContent = s.status;
      tr.children[2].textContent = s.until ? new Date(s.until*1000).toISOString().slice(0,10) : '—';
      const btn = tr.querySelector('.js-manage-sub');
      btn.className = 'btn btn-sm ' + (s.active ? 'btn-outline-secondary' : 'btn-primary');
      btn.textContent = s.active ? 'Gestionar' : 'Suscribirse';
    });
  });
})();
