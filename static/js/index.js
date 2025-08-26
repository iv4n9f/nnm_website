(() => {
  const servers = {
    uk: 'https://nnmsrvuk01/ping',
    de: 'https://nnmsrvde01/ping',
    e2e: 'https://bitwarden/ping'
  };
  Object.entries(servers).forEach(([id,url]) => {
    const dot = document.getElementById('srv-'+id);
    if (!dot) return;
    fetch(url, {method:'HEAD', mode:'no-cors'}).then(() => {
      dot.classList.remove('bg-secondary');
      dot.classList.add('bg-success');
    }).catch(() => {
      dot.classList.remove('bg-secondary');
      dot.classList.add('bg-danger');
    });
  });
})();
