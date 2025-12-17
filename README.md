# Massa Vote - Plateforme de vote en ligne (démonstration)

Petit projet de démonstration d'une plateforme de vote en ligne en PHP + SQLite + HTML/CSS/JS.

Fonctionnalités:
- Création d'élections par une organisation (admin)
- Génération de liens partagés pour une élection et pour chaque candidat
- Votes gratuits ou payants (simulation)
- Modes de paiement simulés: `MTN` et `Orange`

Attention: Ceci est une implémentation de démonstration pour usage local. L'intégration réelle des API MTN/Orange nécessite des clés, webhooks et sécurisation.

Installation rapide (Windows, PowerShell):

1. Ouvrir PowerShell et se placer dans le dossier `Massa_vote`.
2. Lancer le serveur PHP intégré:

```powershell
cd c:/Users/DELL/Desktop/Massa_vote
php -S localhost:8000 -t public
```

3. Dans un navigateur, ouvrir `http://localhost:8000`.

Premiers pas:
- Ouvrir `http://localhost:8000/admin.php` pour créer une élection et ajouter des candidats (séparés par des virgules).
- Après création, vous aurez un lien d'élection et un lien par candidat pour partager.

Structure:
- `public/` : fichiers accessibles (index, admin, vote, pay)
- `src/` : configuration et accès base de données
- `data/` : base de données SQLite (générée automatiquement)

Sécurité et production:
- Cette démo n'est pas sécurisée pour un déploiement public. Pour production, ajouter validation, authentification, HTTPS, intégration sécurisée des paiements et protection contre fraude.
Sécurité ajoutée pour la démo:
 - Protection CSRF: chaque formulaire dispose d'un token CSRF et d'une vérification côté serveur.
 - Validation: certaines valeurs (email, prix) sont validées côté serveur.
 - Uploads: les logos peuvent être téléversés; seuls les types image (PNG/JPEG/SVG/WebP) et fichiers < 2MB sont autorisés. Les fichiers sont stockés dans `public/uploads`.

Notes sécurité uploads:
 - Ne pas faire confiance aux URLs externes fournies par les utilisateurs — préférez téléversement contrôlé.
 - Pour production: scanner les fichiers, utiliser stockage cloud, et empêcher l'exécution de code dans le dossier `uploads`.
