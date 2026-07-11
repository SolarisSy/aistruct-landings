
document.querySelector(".card__cta")?.addEventListener("click", () => {
  const next = "../login/index.html" + window.location.search;
  window.location.href = next;
});
