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

  // 2. Simulation de Connexion (Accessibilité : feedback immédiat)
  const loginForm = document.querySelector('form');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const username = document.getElementById('username')?.value;
      if (username) {
        alert(`Tentative de connexion pour : ${username}. (Simulation)`);
      } else {
        alert('Veuillez remplir les champs de connexion.');
      }
    });
  }

  // 3. Navigation Dropdown (Mobile support & Accessibility)
  // Sur mobile, on pourrait ajouter un menu burger ici si nécessaire
  // Mais pour rester sur le "plus simple possible", on garde le hover sur desktop.

});
