import os
import re

new_nav_html = '''        <nav id="main-nav">
          <ul class="nav-list">
            <li class="nav-item"><a href="index.html" class="nav-link">Accueil</a></li>
            <li class="nav-item" style="position: relative;">
              <a href="#" class="nav-link">Le Club</a>
              <ul class="dropdown-content">
                <li><a href="activites.html" class="dropdown-link">Nos Activités</a></li>
                <li><a href="reunions-club.html" class="dropdown-link">Les Réunions Club</a></li>
                <li><a href="bureau.html" class="dropdown-link">Le Bureau</a></li>
                <li><a href="adhesion.html" class="dropdown-link">Adhésion</a></li>
                <li><a href="media.html" class="dropdown-link">Média & Documents</a></li>
              </ul>
            </li>
            <li class="nav-item" style="position: relative;">
              <a href="#" class="nav-link">Vol Libre</a>
              <ul class="dropdown-content">
                <li><a href="referentiel-cross.html" class="dropdown-link">Référentiel Cross</a></li>
                <li><a href="sites.html" class="dropdown-link">Sites de Vol</a></li>
                <li><a href="ressources.html" class="dropdown-link">Météo & Préparation</a></li>
                <li><a href="treuil.html" class="dropdown-link">Le Treuil</a></li>
                <li><a href="sorties.html" class="dropdown-link">Nos Sorties</a></li>
                <li><a href="incidents.html" class="dropdown-link">Incidents / Accidents</a></li>
                <li><a href="secours.html" class="dropdown-link">Secours & Sécurité</a></li>
                <li><a href="gestion-risques.html" class="dropdown-link">Gestion des Risques</a></li>
              </ul>
            </li>
            <li class="nav-item" style="position: relative;">
              <a href="#" class="nav-link">Actualités</a>
              <ul class="dropdown-content">
                <li><a href="news.html" class="dropdown-link">Journal du Club</a></li>
                <li><a href="agenda.html" class="dropdown-link">Agenda du Club</a></li>
                <li><a href="liens.html" class="dropdown-link">Liens Utiles</a></li>
              </ul>
            </li>
            <li class="nav-item"><a href="contacts.html" class="nav-link">Contacts</a></li>
            <li class="nav-item"><a href="login.html" class="nav-link" id="nav-connexion">Connexion</a></li>
          </ul>
        </nav>'''

def process_file(filepath):
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()

        # Remplacement regex du bloc <nav> complet
        # On cherche <nav id="main-nav"> jusqu'au </nav> correspondant
        pattern = re.compile(r'<nav id="main-nav">.*?</nav>', re.IGNORECASE | re.DOTALL)
        
        if pattern.search(content):
            new_content = pattern.sub(new_nav_html, content)
            if new_content != content:
                with open(filepath, 'w', encoding='utf-8') as f:
                    f.write(new_content)
                print(f"✅ Mis à jour : {filepath}")
            else:
                print(f"ℹ️ Aucun changement pour : {filepath}")
        else:
            print(f"⚠️ Bloc nav introuvable dans : {filepath}")
    except Exception as e:
        print(f"❌ Erreur sur {filepath}: {e}")

html_files = [f for f in os.listdir('.') if f.endswith('.html')]

print("Début de la refonte globale de la navigation...")
for file in html_files:
    # Ne pas toucher aux fichiers dans d'autres dossiers s'ils n'ont pas la même structure de lien,
    # mais là tout est à la racine sauf l'admin.
    process_file(file)
print("Terminé.")
