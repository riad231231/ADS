---
name: ads-expert
description: Expert technique sur le projet "Les Ailes de Sénart" (ADS). Gère le déploiement OVH via GitHub et la base de données Supabase.
---

# 🦅 Skill : Expert Les Ailes de Sénart (ADS)

Ce skill contient l'historique technique, les choix d'architecture et les procédures de maintenance pour le site du club.

## 🏗️ Architecture Technique
- **Frontend** : HTML5, CSS3, JavaScript (Vanilla - Sans framework pour la légèreté).
- **Backend (BaaS)** : Supabase (Authentification et Base de données SQL).
- **Hébergement** : OVH Cloud (Offre mutualisée).
- **Déploiement** : Automatisé via GitHub Actions (GitHub -> FTP OVH).

## 🗄️ Base de Données (Supabase)
- **URL du Projet** : `https://hksbiafznhdjngzimypi.supabase.co`
- **Table `profiles`** : Gère les membres. Le champ `is_approved` (boolean) est CRITIQUE. Si `false`, l'accès au tableau de bord admin est bloqué.
- **Table `news`** : Dernières actualités du club.
- **Table `events`** : Agenda des réunions et sorties.
- **Table `sorties`** : Historique des journées de vol du club.

## 🚀 Procédure de Déploiement (AUTO)
- Ne plus utiliser FileZilla sauf urgence.
- Un `git push` sur la branche `main` déclenche le workflow `.github/workflows/deploy.yml`.
- **Chemin distant** : `www/newsite` (Le domaine pointe vers ce sous-dossier dans le Manager OVH).

## ⚖️ Sécurité et RLS
- Les politiques de sécurité (Row Level Security) sont actives sur Supabase.
- Un utilisateur inscrit doit être validé MANUELLEMENT dans l'onglet "Gestion des membres" du tableau de bord admin pour devenir actif.

## ⚠️ Pièges à éviter (Lessons Learned)
- **.htaccess** : OVH Mutualisé gère mal les redirections complexes dans les sous-dossiers (Erreur 500). Actuellement, aucune règle `.htaccess` n'est active pour éviter les plantages.
- **Cache** : Le navigateur garde souvent en mémoire l'ancien `index.php`. Toujours tester en navigation privée ou vider le cache après un déploiement.
