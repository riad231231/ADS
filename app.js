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
  initMainNavLogin();
  updateFlightStats();
  loadAgendaPages();
  loadDynamicContacts();

});

/** 
 * Charge la liste complète des événements sur la page agenda.html
 */
async function loadAgendaPages() {
  const eventsList = document.getElementById('events-list');
  if (!eventsList) return;

  if (typeof SUPABASE_URL === 'undefined') {
      eventsList.innerHTML = "<span class='error'>Erreur: Configuration manquante.</span>";
      return;
  }

  try {
      const supabase = window.supabaseClient || window.supabase.createClient(SUPABASE_URL, SUPABASE_KEY, { auth: { persistSession: true, storage: window.sessionStorage } });
      const { data, error } = await supabase
          .from('events')
          .select('*')
          .gte('event_date', new Date().toISOString().split('T')[0])
          .order('event_date', { ascending: true });

      if (error) throw error;

      if (!data || data.length === 0) {
          eventsList.innerHTML = "<p style='text-align: center; color: var(--text-muted);'>Aucun événement programmé pour le moment.</p>";
          return;
      }

      const months = ["Jan", "Fév", "Mar", "Avr", "Mai", "Juin", "Juil", "Août", "Sept", "Oct", "Nov", "Déc"];
      
      eventsList.innerHTML = data.map(ev => {
          const date = new Date(ev.event_date);
          const day = String(date.getDate()).padStart(2, '0');
          const month = months[date.getMonth()];
          const year = date.getFullYear();

          return `
              <div class="event-card" style="display: flex; background: white; border: 1px solid var(--border); margin-bottom: 1.5rem; border-radius: var(--radius); overflow: hidden;">
                  <div class="event-date-box" style="background: #f1f5f9; min-width: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 1.5rem; border-right: 1px solid var(--border);">
                      <span style="font-size: 2.2rem; font-weight: 800; line-height: 1;">${day}</span>
                      <span style="font-size: 1.1rem; font-weight: 600; text-transform: uppercase; margin: 0.2rem 0;">${month}</span>
                      <span style="font-size: 0.9rem; letter-spacing: 0.1em; color: var(--text-muted);">${year}</span>
                  </div>
                  <div class="event-content" style="padding: 1.5rem 2rem; flex-grow: 1;">
                      <span style="font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase;">LADS</span>
                      <h2 style="font-size: 1.5rem; color: var(--primary); margin: 0.5rem 0;">${ev.title}</h2>
                      <div style="font-size: 0.95rem; line-height: 1.6;">
                          <p><strong>Lieu :</strong> ${ev.location || 'À la maison des associations ou site de vol'}</p>
                          <p>${ev.description || ''}</p>
                      </div>
                  </div>
              </div>
          `;
      }).join('');

  } catch (e) {
      console.error("Board Events Error:", e);
      eventsList.innerHTML = "<p style='text-align: center; color: #ef4444;'>Impossible de charger l'agenda pour le moment.</p>";
  }
}

/**
 * Calcule et affiche les statistiques de vol par année sur la homepage (depuis Supabase)
 */
async function updateFlightStats() {
  const container = document.getElementById('flight-stats-container');
  if (!container || typeof SUPABASE_URL === 'undefined') return;

  try {
    const supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_KEY);
    const { data: sorties, error } = await supabase
      .from('sorties')
      .select('sortie_date, nb_jours');
    
    if (error || !sorties) return;

    // Calcul des statistiques par année
    const stats = {};
    sorties.forEach(s => {
      if(!s.sortie_date) return;
      const year = new Date(s.sortie_date).getFullYear();
      const days = parseFloat(s.nb_jours) || 0;
      stats[year] = (stats[year] || 0) + days;
    });

    // Tri par année décroissante
    const sortedYears = Object.keys(stats).sort((a,b) => b - a);
    
    if (sortedYears.length > 0) {
      const displayStr = sortedYears.map(y => `${Math.round(stats[y])} jours en ${y}`).join(', ');
      container.innerText = displayStr;
    } else {
      container.innerText = "Données en cours de saisie";
    }

  } catch (e) {
    console.error("Flight Stats Error:", e);
  }
}

/**
 * Charge les prochains événements dans le widget Agenda (depuis Supabase)
 */
async function loadSidebarEvents() {
  const container = document.getElementById('next-events');
  if (!container) return;

  if (typeof SUPABASE_URL === 'undefined') {
    container.innerHTML = "<p style='color: #ef4444; font-size: 0.8rem;'>Erreur : Configuration manquante.</p>";
    return;
  }

  try {
    const supabase = (window.supabaseClient) ? window.supabaseClient : window.supabase.createClient(SUPABASE_URL, SUPABASE_KEY);
    const { data: events, error } = await supabase
      .from('events')
      .select('*')
      .gte('event_date', new Date().toISOString().split('T')[0])
      .order('event_date', { ascending: true })
      .limit(3);
    
    if (error) throw error;

    if (!events || events.length === 0) {
      container.innerHTML = "<p style='font-style: italic; opacity: 0.7;'>Aucun événement prévu.</p>";
      return;
    }

    container.innerHTML = events.map(e => `
      <div style="margin-bottom: 12px; border-bottom: 1px dashed var(--border); padding-bottom: 8px;">
        <div style="font-weight: 800; color: var(--secondary); font-size: 0.75rem; text-transform: uppercase;">
          ${new Date(e.event_date).toLocaleDateString('fr-FR', {day: '2-digit', month: 'short'})}
        </div>
        <a href="agenda.html" style="text-decoration: none; color: var(--text-main); font-weight: 600; display: block; margin-top: 2px;">
          ${e.title}
        </a>
        ${e.location ? `<div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 2px;">${e.location}</div>` : ''}
      </div>
    `).join('');
  } catch (e) {
    console.error("Sidebar Events Error:", e);
    container.innerHTML = "<p style='color: #ef4444; font-size: 0.8rem;'>Erreur de chargement.</p>";
  }
}

/**
 * Charge les dernières news dans le widget News (depuis Supabase)
 */
async function loadSidebarNews() {
  const container = document.getElementById('news-list');
  if (!container) return;

  if (typeof SUPABASE_URL === 'undefined') {
    container.innerHTML = "<li style='color: #ef4444; font-size: 0.8rem;'>Configuration manquante</li>";
    return;
  }

  try {
    const supabase = (window.supabaseClient) ? window.supabaseClient : window.supabase.createClient(SUPABASE_URL, SUPABASE_KEY);
    const { data: news, error } = await supabase
      .from('news')
      .select('*')
      .order('created_at', { ascending: false })
      .limit(5);
    
    if (error) throw error;

    if (!news || news.length === 0) {
        container.innerHTML = "<li style='font-size: 0.85rem; color: var(--text-muted);'>Aucune news récente.</li>";
        return;
    }

    container.innerHTML = news.map(n => {
      const fullMsg = n.message || '';
      const limit = 70;
      const isLong = fullMsg.length > limit;

      return `
        <li style="margin-bottom: 12px; border-bottom: 1px dashed var(--border); padding-bottom: 8px;">
          <a href="news.html#news-${n.id}" style="text-decoration: none; color: var(--text-main); font-weight: 700; display: block; margin-bottom: 4px; font-size: 0.95rem;">
            ${n.title}
          </a>
          <p style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.4; margin-bottom: 0;">
            ${isLong ? fullMsg.substring(0, limit) + '...' : fullMsg}
          </p>
        </li>
      `;
    }).join('');
  } catch (e) {
    console.error("Sidebar News Error:", e);
    container.innerHTML = "<li style='color: #ef4444; font-size: 0.8rem;'>Erreur de connexion</li>";
  }
}

/**
 * Gère l'état du bouton de connexion dans le menu principal
 */
async function initMainNavLogin() {
  const loginLink = document.getElementById('nav-connexion');
  if (!loginLink || typeof SUPABASE_URL === 'undefined') return;

  try {
      const supabase = window.supabase.createClient(SUPABASE_URL, SUPABASE_KEY);
      const { data: { user } } = await supabase.auth.getUser();

      if (user) {
          // On vérifie si l'utilisateur est approuvé
          const { data: profile } = await supabase
              .from('profiles')
              .select('is_approved')
              .eq('id', user.id)
              .single();

          if (profile && profile.is_approved) {
              // Si déjà connecté ET approuvé
              loginLink.innerText = "Mon Espace";
              loginLink.href = "admin/index.html";
          } else {
              // Connecté mais pas encore approuvé par le bureau
              loginLink.innerText = "En attente...";
              loginLink.href = "login.html?status=pending";
          }
      }
  } catch (e) {
      console.error("Login Nav Error:", e);
  }
}

/**
 * Charge les contacts dynamiquement sur la page contacts.html
 */
async function loadDynamicContacts() {
  const container = document.getElementById('dynamic-contacts-container');
  if (!container) return;

  if (typeof SUPABASE_URL === 'undefined') return;

  try {
      const supabase = (window.supabaseClient) ? window.supabaseClient : window.supabase.createClient(SUPABASE_URL, SUPABASE_KEY);
      const { data: contacts, error } = await supabase
          .from('contacts_list')
          .select('*')
          .order('display_order', { ascending: true });

      if (error) throw error;

      if (!contacts || contacts.length === 0) {
          // On laisse le contenu par défaut ou on met un message
          return;
      }

      container.innerHTML = contacts.map(c => {
          const emailList = c.emails.split('\n').filter(e => e.trim() !== '');
          const emailsHtml = emailList.map(email => `
              <a href="mailto:${email.trim()}" class="email-link">${email.trim()}</a>
          `).join('');

          return `
              <div class="contact-point">
                  <h3>${c.title}</h3>
                  ${c.description ? `<p>${c.description}</p>` : ''}
                  ${emailsHtml}
              </div>
          `;
      }).join('');
  } catch (e) {
      console.error("Load Contacts Error:", e);
  }
}
