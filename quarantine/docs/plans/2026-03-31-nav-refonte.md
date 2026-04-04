# Plan d'Implémentation : Refonte globale de la Navigation & Optimisation Responsive

**Objectif :** Déployer une nouvelle structure de navigation regroupée (Option B : "Le Club", "Vol Libre", "Actualités") sur l'ensemble du site et garantir une expérience mobile fluide et sans débordement (menus déroulants, page admin).
**Architecture :** Remplacement unifié par script Python du bloc `<nav>` de toutes les pages statiques. Optimisation CSS de `style.css` pour gérer les menus déroulants sur mobile et corriger les débordements (notamment sur l'iPhone / petits écrans). Test des modifications avec vérification des logs et absence de scrolls horizontaux.
**Stack Technique :** HTML5, CSS3 pur (Custom properties), Python (pour le remplacement récursif statique).

---

### Tâche 1 : Optimisation CSS pour menus et petits écrans
**Fichiers impactés :**
- Modifier : `style.css:lignes 149-192` et les `@media (max-width: 600px)` (lignes 466+)
- Modifier : `admin/index.html` (ajout de `max-width` sur les conteneurs pour le responsive de l'admin)

**Étape 1 : Améliorer les dropdowns sur mobile**
On doit cibler `.dropdown-content` pour éviter les dépassements hors écran sur mobile avec `transform: translateX(-50%)` et le remplacer par des règles fiables ou ajouter un `z-index` supérieur et des marges intérieures sécurisées. On mettra aussi à jour le comportement au survol ou un ajustement visuel mobile.
- Retrait des marges fixes ou styles bloquants pour mobile.

**Étape 2 : Optimisation Mobile Complète**
- Définir `table { display: block; overflow-x: auto; }` et régler les largeurs dans l'admin et d'autres tableaux / blocs (`max-width: 100%`) si présents.

### Tâche 2 : Refonte globale et remplacement de la barre de navigation
**Fichiers impactés :**
- Créer : `apply_new_nav.py`
- Exécuter : `python3 apply_new_nav.py`

**Étape 1 : Création du script Python unifié**
Écriture du code du script Python exact :
1. Recherche du bloc complet depuis `<nav id="main-nav">` jusqu'à `</nav>` inclu.
2. Remplacement de ce bloc complet par la nouvelle structure HTML regroupée.
3. Le tout en respectant l'encodage `utf-8`.

**Étape 2 : Exécution du script et vérification**
Commande : `python3 apply_new_nav.py && grep -L "nav-connexion" *.html`
Résultat attendu : Application sans accros, le `grep -L` ne doit sortir aucune page de l'arborescence (seuls des messages de succès sortent du script).

### Tâche 3 : Assurance Qualité de l'apparence Mobile
**Fichiers impactés :**
- Tous les navigateurs rendus
- Test final manuel sur l'interface (admin compris).

**Étape 1 : Vérifier la page Web sur Serveur Local**
Naviguer à travers l'instance locale via l'outil ou le regard pour s'assurer que les liens pointent bien et que "Le Club" et ses sous-menus s'affichent correctement.

**Étape 2 : Commit**
Commande Git exacte requise : `git add . && git commit -m "feat: refonte navigation globale et opti responsive mobile"`
