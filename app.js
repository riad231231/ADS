// App Logic for Les Ailes de Sénart

document.addEventListener('DOMContentLoaded', () => {

  // 1. Bouton Haut de Page (Footer)
  const backToTop = document.querySelector('.footer-link');
  if (backToTop) {
    backToTop.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }



  // 3. Navigation Dropdown (Mobile support & Accessibility)
  // Sur mobile, on pourrait ajouter un menu burger ici si nécessaire
  // Mais pour rester sur le "plus simple possible", on garde le hover sur desktop.

});
