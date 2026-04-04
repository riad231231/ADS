# 🗺️ Guide de Migration : Les Ailes de Sénart (ADS)

Félicitations pour cette migration ! Voici tout ce qu'il faut savoir pour que le projet continue de fonctionner parfaitement sur la machine du club.

---

## 🛠️ 1. Préparation de la machine
- **Installer Git** : Assure-toi que Git est installé sur l'ordinateur du club.
- **Antigravity** : Vérifie que l'agent est bien configuré et dispose de l'accès au dossier du projet.
- **Serveur de test** : Pour tester localement, tu peux simplement utiliser la commande `python3 -m http.server 8000` dans le dossier racine.

## 🔗 2. Dépôt Git & Déploiement Automatisé
Le projet est lié au dépôt GitHub : `https://github.com/riad231231/ADS`.

**Ce qu'il faut faire sur la nouvelle machine :**
1. **Se connecter à GitHub** : Fais un `git login` (ou clone le dépôt).
2. **Secrets de déploiement** : Si tu changes de serveur ou de mot de passe FTP à l'avenir, n'oublie pas de mettre à jour les **Secrets** dans GitHub (onglet `Settings` > `Secrets and variables` > `Actions`).
   - `FTP_SERVER`
   - `FTP_USERNAME`
   - `FTP_PASSWORD`
   - Ils sont déjà configurés pour ton hébergement actuel (`cluster111`).

## 🗄️ 3. Base de Données (Supabase)
> **Note** : Nous n'utilisons PAS Firebase, mais **Supabase**. C'est le cœur dynamique du site.

- **Dashboard Admin Supabase** : Connecte-toi sur [supabase.com](https://supabase.com) avec tes accès. Tu y trouveras l'onglet **Table Editor** pour voir les données en direct.
- **Configuration locale** : Tu n'as rien à faire. Les clés d'accès sont déjà inscrites dans :
  - `/js/supabase-config.js` (pour les widgets du site)
  - `/login.html` (pour la page de connexion)
  - `/admin/index.html` (pour le tableau de bord)

## 🏗️ 4. Structure sur OVH
- Le domaine `https://www.lesailesdesenart.com` pointe vers le dossier **`www/newsite`**.
- Toutes les mises à jour envoyées via GitHub iront directement dans ce dossier.
- **Vieux fichiers** : Ne supprime pas encore l'ancien dossier `www` racine (Joomla) au cas où, mais sache qu'il est désormais ignoré par ton domaine principal.

## 💡 5. Pour le futur agent Antigravity
Le fichier `/docs/ADS_EXPERT_SKILL.md` est la "bible" technique du projet. 
Si l'agent sur la machine du club semble perdu, demande-lui simplement : 
> *"Lis le fichier /docs/ADS_EXPERT_SKILL.md pour comprendre l'architecture du site avant de commencer."*

---
*Fin de la feuille de route. Bons vols (numériques et réels) au club !* 🦅🪂
