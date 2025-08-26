// Solo tema. Persistencia en localStorage. data-theme en <html>.
document.addEventListener("DOMContentLoaded", () => {
  const html = document.documentElement;
  const btn  = document.getElementById("themeToggle");

  function applyTheme(t){
    html.setAttribute("data-theme", t);
    localStorage.setItem("nnm_theme", t);
  }

  const initial = localStorage.getItem("nnm_theme")
    || (window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");
  applyTheme(initial);

  btn?.addEventListener("click", () => {
    applyTheme(html.getAttribute("data-theme") === "dark" ? "light" : "dark");
  });
});
