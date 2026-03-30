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

  // 2. Navigation Highlighting (Optional but nice)
  const currentPath = window.location.pathname.split('/').pop() || 'index.html';
  const navLinks = document.querySelectorAll('.nav-link, .dropdown-link');
  navLinks.forEach(link => {
    if (link.getAttribute('href') === currentPath) {
      link.classList.add('active');
    }
  });

  // 3. Dynamic Sidebar Components
  loadSidebarEvents();
  loadSidebarNews();
  initSidebarLogin();

});

/**
 * Charge les prochains événements dans le widget Agenda
 */
async function loadSidebarEvents() {
  const container = document.getElementById('next-events');
  if (!container) return;

  try {
    // Priorité 1: localStorage (données fraîches de l'admin)
    let events = JSON.parse(localStorage.getItem('ads_events') || '[]');
    
    // Priorité 2: Fichier JSON (données par défaut)
    if (events.length === 0) {
      const res = await fetch('data/events.json?t=' + Date.now());
      if (res.ok) events = await res.json();
    }
    
    if (!events || events.length === 0) {
      container.innerHTML = "<p style='font-style: italic; opacity: 0.7;'>Aucun événement prévu.</p>";
      return;
    }

    // Affichage des 3 prochains
    container.innerHTML = events.slice(0, 3).map(e => `
      <div style="margin-bottom: 12px; border-bottom: 1px dashed var(--border); padding-bottom: 8px;">
        <div style="font-weight: 800; color: var(--secondary); font-size: 0.75rem; text-transform: uppercase;">
          ${new Date(e.date).toLocaleDateString('fr-FR', {day: '2-digit', month: 'short'})}
        </div>
        <a href="agenda.html" style="text-decoration: none; color: var(--text-main); font-weight: 600; display: block; margin-top: 2px;">
          ${e.title}
        </a>
      </div>
    `).join('');
  } catch (e) {
    container.innerHTML = "<p style='color: #ef4444; font-size: 0.8rem;'>Erreur de chargement.</p>";
  }
}

/**
 * Charge les dernières news dans le widget News
 */
async function loadSidebarNews() {
  const container = document.getElementById('news-list');
  if (!container) return;

  try {
    // Priorité 1: localStorage
    let news = JSON.parse(localStorage.getItem('ads_news') || '[]');
    
    // News par défaut si vide
    if (news.length === 0) {
      news = [
        { title: "Concours photos 2022: l'œil dans le viseur", url: "#", message: "Le concours annuel est lancé !" },
        { title: "L'offre Formation de la ligue", url: "#", message: "Découvrez les nouvelles aides." },
        { title: "Examen théorique 07 Avril", url: "#", message: "Inscrivez-vous rapidement." }
      ];
    }

    container.innerHTML = news.slice(0, 5).map(n => {
      const fullMsg = n.message || '';
      const limit = 70;
      const isLong = fullMsg.length > limit;

      if (!isLong) {
        return `
          <li style="margin-bottom: 12px; border-bottom: 1px dashed var(--border); padding-bottom: 8px;">
            <a href="${n.url || '#'}" target="_blank" style="text-decoration: none; color: var(--text-main); font-weight: 700; display: block; margin-bottom: 4px; font-size: 0.95rem;">
              ${n.title}
            </a>
            <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.4; margin-bottom: 0;">${fullMsg}</p>
          </li>
        `;
      }

      const shortMsg = fullMsg.substring(0, limit);

      return `
        <li style="margin-bottom: 12px; border-bottom: 1px dashed var(--border); padding-bottom: 8px;">
          <a href="${n.url || '#'}" target="_blank" style="text-decoration: none; color: var(--text-main); font-weight: 700; display: block; margin-bottom: 4px; font-size: 0.95rem;">
            ${n.title}
          </a>
          <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.4; margin-bottom: 0;">
            <span>${shortMsg}...</span>
            <a href="javascript:void(0)" onclick="const p = this.parentElement; p.querySelector('span').innerText = \`${fullMsg}\`; this.remove();" style="display:inline; color: var(--primary); font-weight: 700; border-bottom: none; font-size: 0.8rem; text-decoration: none; margin-left: 5px;">Lire la suite</a>
          </p>
        </li>
      `;
    }).join('');
  } catch (e) {
    container.innerHTML = "<li style='font-size: 0.8rem; color: #ef4444;'>Erreur de chargement.</li>";
  }
}

/**
 * Initialise le formulaire de connexion de la sidebar
 */
function initSidebarLogin() {
  const loginForm = document.getElementById('sidebarLoginForm');
  if (!loginForm) return;

  loginForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const u = document.getElementById('side-username').value;
    const p = document.getElementById('side-password').value;
    
    if (u && p) {
      // NOTE: Authentification côté client pour démonstration
      localStorage.setItem('ads_logged_in', 'true');
      window.location.href = 'admin/index.html';
    } else {
      const status = document.getElementById('login-status');
      if (status) {
        status.style.display = 'block';
        status.innerText = "Identifiants requis.";
      }
    }
  });
}
