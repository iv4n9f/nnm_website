(() => {
  const servers = {
    uk: 'https://nnmsrvuk01/ping',
    de: 'https://nnmsrvde01/ping',
    e2e: 'https://bitwarden/ping'
  };
  Object.entries(servers).forEach(([id, url]) => {
    const dot = document.getElementById('srv-' + id);
    if (!dot) return;
    fetch(url, { method: 'HEAD', mode: 'no-cors' }).then(() => {
      dot.classList.replace('bg-secondary', 'bg-success');
    }).catch(() => {
      dot.classList.replace('bg-secondary', 'bg-danger');
    });
  });

  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.add('show');
        obs.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });
  document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
})();
