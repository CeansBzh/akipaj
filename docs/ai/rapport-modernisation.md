# Rapport d'analyse et de modernisation — Akipaj

> Document destiné aux agents IA (et aux humains) qui reprennent le projet. Le guide opérationnel court est
> dans [CLAUDE.md](../../CLAUDE.md). Les chemins sont relatifs à la racine du dépôt. Mis à jour le 2026-10-19,
> à la fin de la montée de version (branche `modernisation`).

## 1. Synthèse

- **Le projet** : application Laravel + Livewire d'environ 5 000 lignes de PHP dans `app/`, développée
  d'octobre 2022 à juillet 2023 (dernier commit en février 2026). Domaine simple et bien découpé :
  ressources CRUD, policies, form requests, composants Livewire.
- **Point de départ** : Laravel 9 et PHP 8.1 (plus maintenus), Livewire 2, Vite 3, aucun test réel, pas
  d'analyse statique, 54 alertes Composer et 22 alertes npm.
- **État actuel** : **Laravel 13, Livewire 4, PHP 8.3+ (8.4 sous Sail), Vite 8, PHPUnit 12, PHPStan 2 +
  Larastan 3 (niveau 5, sans baseline), Laravel Boost 2**. 222 tests (≈ 98 % des lignes de `app/`), parcours
  navigateur vérifiés, 0 alerte Composer, 10 alertes npm restantes (§ 5.5). 23 bugs corrigés (§ 4).
- **Priorités restantes** : 1) préparer la production (PHP ≥ 8.3, build des assets ; § 5.1) ;
  2) remplacer le paiement par carte (§ 5.2) ; 3) le reste de la feuille de route (§ 6).

## 2. Historique des interventions

Commits de la branche `modernisation`, du plus ancien au plus récent :

| Commit | Contenu |
|---|---|
| Ajoute PHPStan et Larastan, retire les paquets laravel-lang | Outillage d'analyse ; `laravel-lang/*` retirés car signalés comme malware (§ 7) |
| Corrige les bugs et erreurs relevés par PHPStan | Bugs n° 1 à 20 (§ 4), types natifs |
| Ajoute une suite de tests complète et la CI | 222 tests, workflow GitHub Actions |
| Ajoute la documentation orientée IA | Première version de ce rapport et de `CLAUDE.md` |
| Monte vers Laravel 13, Livewire 4 et PHP 8.3+ | Dépendances, structure Laravel 11+, migration Livewire, tests PHPUnit 12 |
| Met à jour l'outillage front (Vite 8, Prettier 3) | `package.json` en ESM, dépendances inutilisées retirées |
| Passe l'environnement Docker sur Laravel Sail (PHP 8.4) | `compose.yaml`, Mailpit, phpMyAdmin conservé |
| Corrige le script « Ajouter à l'agenda » des événements | Bug n° 21 |
| Installe Laravel Boost 2 | Guidelines, skills, serveur MCP |
| Force DOMPurify 3 sous Toast UI Editor | Override npm (alerte XSS) |
| Met à jour la CI et la documentation | CI PHP 8.3/8.4 + build Vite, documentation à jour |
| Adopte Laravel Pint pour le PHP | Pint remplace Prettier pour le PHP (Prettier garde Blade, JS, CSS), vérifié en CI |
| Formate le code PHP avec Pint | Reformatage mécanique (preset `laravel`), sans changement de comportement |

### 2.1 Décisions de la montée de version

- **Cibles** : dernières versions majeures stables au 2026-10-19. `composer.json` déclare `php: ^8.3`
  (comme le squelette Laravel 13) et le lock est résolu pour PHP 8.3 (`config.platform.php`), afin de rester
  installable sur un hébergement en 8.3. Le développement se fait en 8.4 (Sail).
- **Structure Laravel 11+** adoptée : `bootstrap/app.php` et `bootstrap/providers.php` ; kernels, gestionnaire
  d'exceptions, middlewares par défaut et providers Auth/Event/Route/Broadcast supprimés. Les listeners et les
  policies sont découverts automatiquement.
- **Configuration conservée** : les fichiers `config/*.php` de Laravel 9 sont gardés, sauf `app.php` (liste
  des providers retirée) et `sanctum.php` (supprimé). Les valeurs par défaut du squelette 13 casseraient le
  `.env` de production : cache et sessions en base, disque `local` sur `storage/app/private`, alors que tout le
  code stocke dans `storage/app/public`.
- **Livewire 2 → 4** : composants déplacés dans `App\Livewire`, `#[On]`, `dispatch()` avec paramètres nommés,
  `wire:model.live` là où la v2 mettait à jour immédiatement, actions par identifiant (`int $commentId`) au
  lieu d'une liaison de modèle. Livewire et Alpine sont importés par Vite depuis
  `vendor/livewire/livewire/dist/livewire.esm` (`@livewireScriptConfig` dans les layouts) : Alpine est ainsi
  disponible même sur les pages sans composant (pages invité).
- **Mail** : `symfony/sendinblue-mailer` (abandonné) remplacé par `symfony/brevo-mailer`. Les mailers `brevo`
  et `sendinblue` utilisent le même transport, et `BREVO_API_KEY` se rabat sur `SENDINBLUE_API_KEY` : le `.env`
  de production existant reste valable.
- **Retirés** : `laravel/breeze` (installateur, déjà publié), `spatie/laravel-ignition` (remplacé par la page
  d'erreur de Laravel), et côté npm : `alpinejs`, `@alpinejs/collapse`, `flowbite`, `axios`, `lodash`.
- **Formatage** : Laravel **Pint** (preset `laravel`) pour tout le PHP, conformément aux guidelines Boost ;
  Prettier ne formate plus que Blade, JS et CSS (`@prettier/plugin-php` retiré). Le passage de Prettier à
  Pint a fait l'objet d'un commit de reformatage isolé.
- **Tailwind 4** : migration par `@tailwindcss/upgrade` (classes renommées dans les vues), configuration
  déplacée dans `resources/css/app.css`, plugin `@tailwindcss/vite` à la place de PostCSS/autoprefixer.
  Bordures grises et curseur pointeur des boutons de la v3 conservés. Revue visuelle page par page à faire.
- **Boost** : guidelines dans `CLAUDE.md`, skills dans `.claude/skills` (le skill Laravel Cloud est désactivé
  dans `boost.json`), serveur MCP via Sail dans `.mcp.json`. `composer update` exécute `boost:update`.

### 2.2 Vérifications effectuées

- `composer test` (222 tests) et `composer analyse` (niveau 5) au vert sous PHP 8.4 (Sail).
- `artisan view:cache`, `config:cache`, `route:cache`, `event:list`, `schedule:list` sans erreur.
- MySQL 8.4 : `migrate:fresh --seed` ; les 19 pages principales répondent 200 en admin.
- Navigateur headless (Playwright) : Alpine et Livewire chargés sur les pages invité et membre, menu
  déroulant, recherche globale, filtres de la galerie, lightbox, commentaires, abonnement, ajout de bateau,
  catalogue admin, tri des albums, éditeur d'articles (y compris neutralisation d'un `onerror`). Aucune erreur JS.
- Serveur MCP Boost : handshake `initialize` et `tools/list` (10 outils) via Sail.

## 3. Architecture (ce qu'il faut savoir avant de modifier)

### 3.1 Routage et contrôle d'accès

`routes/web.php` est structuré en couches :
1. Public : `/` (route `home` : vue `index` pour un visiteur, `homepage.index` pour un connecté),
   `/mentions-legales`, `/profil/{username?}` (accès contrôlé dans le contrôleur), `/up` (health check).
2. `auth` : `/compte` (paramètres), `/virements` (tout utilisateur connecté, y compris GUEST).
3. `auth` + `level:1` (MEMBER) : `/matelots`, ressources articles, albums, events, trips, photos
   (+ `photos/create/{album}`).
4. `auth` + `level:99` (ADMIN), préfixe `/admin` : tableau de bord, `admin.users.*` (index, edit, update,
   destroy), `POST /articles/admin/storeImage` (le préfixe donne cette URL étrange, utilisée telle quelle par
   `resources/js/article-editor.js`), `GET /admin/reset`.

Redirection des invités vers `login` avec un message flash, et des utilisateurs connectés vers `home` :
configurées dans `bootstrap/app.php`. Les contrôleurs de ressources appellent `authorizeResource()` (le
contrôleur de base garde `Illuminate\Routing\Controller` et `AuthorizesRequests`).

### 3.2 Modèle de données

```
users ─┬─< photos >── albums >─< trips ─< boats
       ├─< payments            │
       ├─< comments (auteur)   └─>< users (trip_user : équipage)
       └─< thread_subscriptions
comments.commentable_*               → Photo | User (profil)      [trait Commentable]
thread_subscriptions.subscribeable_* → Photo | User
articles, events : autonomes
```

- `users.level` : entier casté en `UserLevelEnum`.
- Colonnes polymorphes en `unsignedInteger` alors que les ids sont des `bigint` : sans conséquence à cette
  échelle, à aligner avec `morphs()` lors d'une refonte.
- `articles.online` est un `char(1)` et non un booléen ; `imagePath` (camelCase) cohabite avec le snake_case.
- Les modèles utilisent encore la propriété `$casts` (fonctionne en Laravel 13 ; la méthode `casts()` est la
  forme moderne).

### 3.3 Fichiers

- Couvertures (albums, articles, événements, sorties) et photos de profil : `storage/app/public/<dossier>`,
  servies via le lien `public/storage`. `Storage::url('public/x')` donne `/storage/x`.
- Photos de la galerie : écrites directement dans `public_path('storage/photos')` avec GD (ré-encodage JPEG et
  miniature ≤ 450 px). Le fichier n'est **jamais supprimé** du disque.

### 3.4 Effets de bord

- `Registered` → `SendAccountCreatedNotification` (mail aux admins + bienvenue) ; le framework y ajoute
  `SendEmailVerificationNotification`, sans effet car `User` n'implémente pas `MustVerifyEmail`.
- `RegistrationValidated` → mail à l'utilisateur. Commentaire → mail synchrone à chaque abonné
  (`QUEUE_CONNECTION=sync`).
- Planificateur (`routes/console.php`) : `model:prune` quotidien (seul `User` est `Prunable`).

## 4. Bugs corrigés

Chacun est couvert par au moins un test. ⚠ = impact sécurité.

| # | Problème | Correction |
|---|---|---|
| 1 | ⚠ `PhotoController` sans autorisation : tout membre pouvait modifier ou supprimer les photos des autres. `update()` faisait `$photo->update($request->all())`, ce qui permettait d'écraser `path` et `thumb_path`. | `authorizeResource()` + champs validés uniquement |
| 2 | ⚠ `Livewire\Comments::updateComments()` appelait `$type::find()` avec une classe fournie par le client | Seules les classes utilisant `Commentable` sont acceptées (400 sinon) |
| 3 | ⚠ `RegistrationValidation` : `accept` et `reject` sans contrôle de rôle | `abort_unless(isAdmin)` |
| 4 | ⚠ `PaymentController` : une exception Stripe pendant le débit était traitée comme un succès → **paiement enregistré sans débit** | Erreur ou statut ≠ `succeeded` = échec |
| 5 | `new StripeClient(null)` → erreur sur `/virements` sans `STRIPE_SECRET_KEY` | Client injecté par le conteneur |
| 6 | `ProfileController::show` : erreur 500 pour un visiteur non connecté | 404 |
| 7 | Albums : sorties jamais associées à la création (`trip` au lieu de `trips`) | Corrigé |
| 8 | Albums : couvertures stockées et supprimées dans `public/trips` | `public/albums` |
| 9 | Articles : suppression de l'image dans `public/article` au lieu de `public/articles` | Corrigé |
| 10 | Photos PNG et GIF : plantage (`imagecreatefromjpeg`) | `imagecreatefromstring` |
| 11 | Sorties : les bateaux retirés n'étaient pas supprimés (`whereNotIn` avec `null`) | Suppression par ids enregistrés |
| 12 | `User::pruning()` appelait `roles()` (inexistant) et ignorait les photos déjà soft-deleted | `trips()->detach()` + `withTrashed()` |
| 13 | `User::isSubscribedTo()` ignorait le type polymorphe | Compare id et `getMorphClass()` |
| 14 | `VerifyEmailController` : TypeError sur `Verified` | Événement émis si `MustVerifyEmail` |
| 15 | `ArticlePolicy::view` / `AlbumPolicy::delete` utilisaient `auth()` au lieu de `$user` | `$user` |
| 16 | `require_once` de `routes/auth.php` (routes absentes après le premier test) | `require` |
| 17 | Routes `admin.users.create` et `admin.users.store` vers des méthodes vides | Retirées |
| 18 | Factories : téléchargement réseau, crash sans utilisateur, méthode inexistante, textes trop longs, `level` absent, événements « à venir » dans le passé | Corrigées |
| 19 | `UserSeeder` dépendant de l'ancien système de rôles | Supprimé |
| 20 | PHPDoc faux (`@return Response` partout) | Types natifs et génériques |
| 21 | « Ajouter à l'agenda » : valeurs insérées dans du JS avec `{{ }}` → erreur JS dès qu'une adresse contient un retour à la ligne (non sûr pour les guillemets) | `@js()` |
| 22 | `<livewire:admin.registration-validation>` non fermée (ouvre un slot en Livewire 4) | Balise auto-fermante |
| 23 | Vite 8 n'émettait plus les images de `resources/images` → `Vite::asset()` aurait levé une exception en production | `import.meta.glob(..., { eager: true, query: '?url' })` |

## 5. Problèmes connus et décisions à prendre

### 5.1 Mise en production — **à faire avant de fusionner dans `production`**

- L'hébergement doit fournir **PHP ≥ 8.3** avec gd et exif.
- `vendor/` et `public/build/` ne sont pas versionnés, et le workflow FTP n'envoie que les fichiers suivis par
  git. Ils doivent être produits pour ce déploiement (`composer install --no-dev -o`, `npm ci && npm run build`).
  Ce point n'a pas pu être vérifié côté serveur.
- Les assets Livewire 2 publiés dans `public/vendor/livewire` ont été supprimés : Livewire est maintenant inclus
  dans le bundle Vite.
- Après déploiement : `php artisan migrate --force` (aucune nouvelle migration à ce jour), `php artisan optimize`,
  vérifier que `bootstrap/cache/*.php` ne contient pas d'anciens caches (providers Laravel 9).
- `.env` : aucune variable obligatoire ne change. Facultatif : `MAIL_MAILER=brevo` et `BREVO_API_KEY`.
- Le workflow utilise `SamKirkland/FTP-Deploy-Action@master` (branche flottante : risque de chaîne
  d'approvisionnement) et `actions/checkout@v2.1.0` : épingler une version.

**Recommandation** : un workflow qui exécute `composer install --no-dev -o` et `npm ci && npm run build`,
déploie l'artefact complet, puis lance `migrate --force` et `optimize`. Cela remplacerait aussi
`GET /admin/reset`.

### 5.2 Paiement par carte — **critique**

`PaymentController` et `PaymentRequest` reçoivent **numéro de carte, date et CVC en clair** puis créent un
token et un `Charge` Stripe : flux non conforme PCI-DSS SAQ A, API Charges dépréciée, pas de 3-D Secure.
Le CVC est converti en `int` (« 012 » devient 12 et est rejeté), une date mal formée donne une erreur 500, et un
message vide enregistre une description vide. `stripe/stripe-php` est resté en v10 pour ne pas toucher à ce
flux. **Recommandation** : Stripe Checkout ou Payment Element, avec un webhook qui crée le `Payment`.

### 5.3 Sécurité et robustesse

- `GET /admin/reset` : effets de bord (`config:cache`) sur une route GET.
- Noms de fichiers `time() . '.' . extension` : deux envois dans la même seconde s'écrasent.
- Les fichiers photo ne sont jamais supprimés du disque. Traitement d'image synchrone (jusqu'à 50 × 10 Mo par
  requête), orientation EXIF ignorée, PNG ré-encodé en JPEG sous une extension `.png`.
- Articles : pas d'unicité vérifiée sur le titre ou le slug en mise à jour (collision → 500).
- Sorties : un `boats[*][id]` d'une autre sortie passe la validation et provoque une erreur SQL (admins seulement).
- Abonnements en double possibles (`ThreadSubscribeButton`) ; un admin peut supprimer son propre compte ou le
  dernier admin ; impossible de modifier un événement en cours ou passé (`start_time >= now`).
- `User::$cascadeDeletes` : `comments` désigne les commentaires **reçus sur le profil**, pas ceux écrits par
  l'utilisateur. À clarifier fonctionnellement.
- Vérification d'email en sommeil : l'activer (`MustVerifyEmail` + middleware `verified`) ou supprimer le code.
- Niveau `CONTRIBUTOR` sans droit propre. Sanctum installé pour la seule route `/api/user`.

### 5.4 Qualité du code

- Des vues exécutent des requêtes (`Album::all()`, `User::all()` dans les formulaires, partials de l'accueil).
- Logique d'upload d'image dupliquée dans 6 contrôleurs ; validation inline dans Album, Article et Event.
- PHPStan niveau 6 : **109 erreurs** (propriétés Livewire non typées, paramètres et retours sans type).
- `resources/views/components/hero-picture.blade.php` n'est utilisé nulle part et référence des images
  inexistantes (code mort antérieur).
- Bundle `app.js` d'environ 1 Mo (Toast UI Editor, Livewire, Flickity) chargé sur toutes les pages : charger
  l'éditeur à la demande (`import()`) sur les pages d'articles.

### 5.5 Dépendances front

`npm audit` : 9 alertes, toutes dues au Tailwind 3 embarqué par `@shufo/prettier-plugin-blade` (blade-formatter),
un outil de formatage non livré au navigateur. DOMPurify est forcé en v3 (`overrides`) sous
`@toast-ui/editor`, qui n'est plus maintenu depuis 2022 : envisager un autre éditeur Markdown.

## 6. Feuille de route

| Étape | État |
|---|---|
| Tests, PHPStan/Larastan, CI | ✅ |
| `minimum-stability: stable`, audit Composer à zéro | ✅ |
| PHP 8.3+ / Sail 8.4 | ✅ (côté code ; hébergement à vérifier, § 5.1) |
| Laravel 9 → 13, structure Laravel 11+ | ✅ |
| Livewire 2 → 4 | ✅ |
| Sendinblue → Brevo | ✅ |
| Vite 8, Prettier 3 | ✅ |
| Laravel Boost 2 | ✅ |
| Déploiement automatisé avec build (§ 5.1) | À faire, **bloquant pour la mise en production** |
| Paiement Stripe Checkout (§ 5.2) | À faire |
| Tailwind 3 → 4 | ✅ (revue visuelle à faire) |
| PHPStan niveau 6 puis 8 | À faire |
| Factorisation des uploads, Form Requests partout, `casts()` | À faire |
| Notifications en file d'attente | À faire |

Optionnel : lancer le skill Boost `infer-conventions` pour enregistrer les conventions du projet dans
`.ai/rules`.

## 7. Environnement de développement

Tout passe par **Laravel Sail** (`compose.yaml` : PHP 8.4 avec gd, exif, pcov et xdebug, MySQL 8.4, Redis,
Mailpit sur :8025, phpMyAdmin sur :8081). Les commandes sont détaillées dans `CLAUDE.md`. Sans `vendor/`,
l'installation initiale passe par l'image `laravelsail/php84-composer`, comme le recommande la documentation
de Sail.

Composer bloque l'installation des versions ayant des alertes de sécurité **et** des paquets signalés comme
malware. Ne jamais utiliser `--no-blocking`, qui désactive aussi le filtre malware. Les paquets `laravel-lang/*`
ont été retirés pour cette raison (rapports Aikido couvrant la quasi-totalité de leurs versions) ; les
traductions restent versionnées dans `lang/`.
