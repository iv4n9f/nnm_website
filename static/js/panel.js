(() => {
  const q = s => document.querySelector(s);
  const qa = s => Array.from(document.querySelectorAll(s));

  async function call(url, opts){
    const r = await fetch(url, opts);
    if(!r.ok) throw new Error(await r.text());
    return r.json();
  }

  q('#genVpn')?.addEventListener('click', () => {
    const server = q('#vpnServer').value;
    window.location = '/api/vpn_config.php?server=' + encodeURIComponent(server);
  });

  q('#pwReset')?.addEventListener('click', async () => {
    try {
      await fetch('/api/reset_password.php', {method:'POST'});
      alert('Correo enviado');
    } catch(e){ alert('Error'); }
  });

  q('#sfInvite')?.addEventListener('click', async () => {
    try {
      await fetch('/api/seafile_invite.php', {method:'POST'});
      alert('Correo enviado');
    } catch(e){ alert('Error'); }
  });

  q('#sfUpgrade')?.addEventListener('click', async () => {
    const size = prompt('Nuevo tamaño en GB');
    if(!size) return;
    try {
      await fetch('/api/seafile_upgrade.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({size})
      });
      alert('Solicitud enviada');
    } catch(e){ alert('Error'); }
  });

  q('#refreshBilling')?.addEventListener('click', async () => {
    try {
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
    } catch(e){ alert('Error'); }
  });

  qa('.js-manage-sub').forEach(btn => {
    btn.addEventListener('click', async () => {
      const product = btn.closest('.sub-card')?.dataset.product;
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
        alert('Error');
      }
    });
  });
})();
