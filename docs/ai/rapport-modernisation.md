# Rapport d'analyse et feuille de route de modernisation — Akipaj

> Document destiné aux agents IA (et aux humains) qui reprennent le projet. Rédigé le 2026-10-19 après une
> lecture complète de `app/`, `routes/`, `database/`, `config/` et des vues principales, puis la mise en place
> d'une suite de tests et de PHPStan. Le guide opérationnel court est dans [CLAUDE.md](../../CLAUDE.md).
> Les chemins sont relatifs à la racine du dépôt.

## 1. Synthèse

- **Fonctionnel** : application Laravel 9 + Livewire 2 d'environ 5 000 lignes de PHP dans `app/` (≈ 7 200 avec
  routes, migrations, seeders et tests d'origine), développée d'octobre 2022 à juillet 2023, avec un dernier commit en février 2026.
  Le domaine est simple et bien découpé (ressources CRUD, policies, form requests, composants Livewire).
- **Avant cette intervention** : aucun test réel (les tests Breeze ciblaient des URLs inexistantes), pas d'analyse
  statique, et des dépendances obsolètes ou vulnérables : 54 alertes Composer sur 17 paquets, 22 alertes npm
  dont 1 critique. Laravel 9 (fin de support des correctifs de sécurité en février 2024) et PHP 8.1 (fin de
  support en décembre 2025) ne sont plus maintenus.
- **Après** : 222 tests (≈ 98 % des lignes de `app/` couvertes), PHPStan niveau 5 sans baseline sur `app`,
  `config`, `database`, `routes` et `tests`, CI GitHub Actions, et 20 bugs corrigés (§ 4), dont 4 de sécurité.
- **Priorités** : 1) remplacer le flux de paiement par carte (§ 5.1) ; 2) monter Laravel et Livewire de version
  (§ 6), avec les tests comme filet de sécurité ; 3) fiabiliser le déploiement (§ 5.4).

## 2. Ce qui a été mis en place

| Élément | Fichiers |
|---|---|
| Configuration de test : SQLite en mémoire, `APP_KEY` et clés Stripe factices | `phpunit.xml` |
| Base de test : `withoutVite()`, `public_path()` temporaire, helpers de niveau | `tests/TestCase.php` |
| 222 tests (Feature + Unit) | `tests/` |
| PHPStan 1.12 + Larastan 2.11 + phpstan-mockery, niveau 5 | `phpstan.neon`, `composer.json` |
| Scripts `composer test`, `composer test:coverage`, `composer analyse` | `composer.json` |
| CI : PHPUnit + PHPStan sur push `master` et PR | `.github/workflows/tests.yml` |
| `phpstan.neon` exclu du déploiement FTP | `.github/workflows/ftp-deploy.yml` |

Changements de dépendances (lock), volontairement minimaux :
- **Retirés** : `laravel-lang/attributes`, `laravel-lang/lang`, `laravel-lang/publisher` (dev). Composer refuse
  de les installer : Packagist les signale comme **malware** (rapports Aikido, couvrant la quasi-totalité des
  versions publiées). Ils ne servaient qu'à publier les traductions, déjà versionnées dans `lang/`. Si une
  machine a installé ces paquets récemment, vérifier son intégrité.
- **Mis à jour** : `laravel/framework` 9.52.10 → 9.52.22 (correctifs, requis par Larastan 2.9+).
- **Ajoutés** (dev) : `larastan/larastan ^2.9`, `phpstan/phpstan ^1.12`, `phpstan/phpstan-mockery ^1.1`.

Organisation des tests :

```
tests/Unit/                      Enums, conversion GPS EXIF (StorePhotoRequest::toGps)
tests/Feature/Auth/              Connexion (dont rate limiting), inscription (niveau GUEST + mails), mots de passe, vérification email
tests/Feature/AccessControlTest  Matrice routes × niveaux (visiteur, GUEST, MEMBER, CONTRIBUTOR, ADMIN)
tests/Feature/Account/           Profil public, paramètres du compte, suppression de compte
tests/Feature/Admin/             Gestion des utilisateurs
tests/Feature/{Article,Album,Event,Trip,Photo,Payment}Test   CRUD, validation, images, autorisations
tests/Feature/Livewire/          Tous les composants Livewire
tests/Feature/Models/            Relations, cascades de suppression, pruning
tests/Feature/PoliciesTest       Matrice des policies
tests/Feature/NotificationsTest  Contenu des mails
tests/Feature/InfrastructureTest Planificateur, macro whereLike, middleware, mailer Sendinblue, seeder local
```

## 3. Architecture (ce qu'il faut savoir avant de modifier)

### 3.1 Routage et contrôle d'accès

`routes/web.php` est structuré en couches :
1. Public : `/` (vue `index` pour un visiteur, `homepage.index` pour un connecté), `/mentions-legales`,
   `/profil/{username?}` (contrôle d'accès dans le contrôleur), `/compte` (le middleware `auth` est posé dans
   le constructeur de `SettingsController`), `/matelots` (middleware posé dans le constructeur).
2. `auth` : `/virements` (tout utilisateur connecté, y compris GUEST).
3. `auth` + `level:1` (MEMBER) : `Route::resources` articles, albums, events, trips, photos (+ `photos/create/{album}`).
4. `auth` + `level:99` (ADMIN), préfixe `/admin` : tableau de bord, `admin.users.*` (index, edit, update, destroy),
   `POST /articles/admin/storeImage` (le préfixe donne cette URL étrange, utilisée telle quelle par
   `resources/js/article-editor.js`), `GET /admin/reset`.

Les contrôleurs de ressources appellent `authorizeResource()` (sauf `Admin\UserController`, protégé par la route).
Les policies sont découvertes par convention (`AuthServiceProvider::$policies` est vide).

### 3.2 Modèle de données

```
users ─┬─< photos >── albums >─< trips ─< boats
       ├─< payments            │
       ├─< comments (auteur)   └─>< users (trip_user : équipage)
       └─< thread_subscriptions
comments.commentable_*            → Photo | User (profil)      [trait Commentable]
thread_subscriptions.subscribeable_* → Photo | User
articles, events : autonomes
```

- `users.level` : entier casté en `UserLevelEnum` (ajouté par la migration `2023_02_24`, qui a remplacé un
  ancien système de rôles dont il restait des traces, nettoyées ici).
- Colonnes polymorphes en `unsignedInteger` alors que les ids sont des `bigint` : sans conséquence à cette
  échelle, à aligner avec `morphs()` lors d'une refonte.
- `articles.online` est un `char(1)`, et non un booléen.
- Les noms de colonnes `imagePath` (camelCase) cohabitent avec le snake_case ailleurs.

### 3.3 Fichiers

- Couvertures (albums, articles, événements, sorties) et photos de profil : `storage/app/public/<dossier>`,
  servies via le lien symbolique `public/storage`. `Storage::url('public/x')` donne `/storage/x` : Laravel retire
  `public/` pour le disque local.
- Photos de la galerie : écrites directement dans `public_path('storage/photos')` avec GD (ré-encodage JPEG
  qualité par défaut et miniature ≤ 450 px). Le fichier n'est **jamais supprimé** du disque, ni à la suppression
  de la photo, ni au pruning.

### 3.4 Effets de bord

- Événement `Registered` → listeners `SendEmailVerificationNotification` (sans effet : `User` n'implémente pas
  `MustVerifyEmail`) et `SendAccountCreatedNotification` (mail aux admins + mail de bienvenue).
- Événement `RegistrationValidated` → mail à l'utilisateur.
- Commentaire posté → mail synchrone à chaque abonné (`QUEUE_CONNECTION=sync`, aucune notification en file).
- Planificateur : `model:prune` quotidien (seul `User` est `Prunable`).

## 4. Bugs corrigés pendant l'intervention

Chacun est couvert par au moins un test. ⚠ = impact sécurité.

| # | Problème | Correction |
|---|---|---|
| 1 | ⚠ `PhotoController` sans autorisation : tout membre pouvait modifier ou supprimer les photos des autres en HTTP. `update()` faisait `$photo->update($request->all())`, ce qui permettait d'écraser `path` et `thumb_path`. | `authorizeResource()` + mise à jour limitée aux champs validés |
| 2 | ⚠ `Livewire\Comments::updateComments()` appelait `$type::find()` avec un nom de classe fourni par le client | Accepte uniquement les classes utilisant `Commentable`, sinon 400 |
| 3 | ⚠ `Livewire\Admin\RegistrationValidation` : `accept`/`reject` sans contrôle de rôle | `abort_unless(isAdmin)` |
| 4 | ⚠ `PaymentController` : une exception Stripe pendant le débit renvoyait `['error' => …]`, considéré comme un succès → **paiement enregistré sans débit** | Tout tableau d'erreur ou un statut ≠ `succeeded` est traité comme un échec |
| 5 | `PaymentController` construisait `new StripeClient(null)` → erreur sur toutes les pages `/virements` sans `STRIPE_SECRET_KEY` | Client injecté, binding dans `AppServiceProvider` |
| 6 | `ProfileController::show` : erreur 500 pour un visiteur non connecté (`null->isGuest()`) | 404, comme prévu par le code |
| 7 | `AlbumController::store` lisait `trip` alors que le formulaire envoie `trips[]` → sorties jamais associées à la création | `trips` |
| 8 | `AlbumController::update` stockait et supprimait les couvertures dans `public/trips` → impossible de retirer une couverture créée à la création (stockée dans `public/albums`) | `public/albums` partout |
| 9 | `ArticleController::update` : même problème (`public/article` au lieu de `public/articles`) | `public/articles` |
| 10 | `PhotoController::store` : `imagecreatefromjpeg` plantait sur les PNG et GIF, pourtant autorisés par la validation | `imagecreatefromstring` |
| 11 | `TripController::update` : `whereNotIn('id', [.., null])` ne supprimait jamais les bateaux retirés si un bateau était ajouté dans la même requête (et sinon, les nouveaux bateaux auraient été supprimés) | Suppression basée sur les ids réellement enregistrés |
| 12 | `User::pruning()` appelait `roles()`, inexistant → `model:prune` planté chaque nuit ; et les photos déjà soft-deleted n'étaient pas supprimées définitivement | `trips()->detach()` + `withTrashed()` |
| 13 | `User::isSubscribedTo()` ne comparait que l'id, pas le type polymorphe (photo n°3 et profil n°3 confondus) | Compare id et `getMorphClass()` |
| 14 | `VerifyEmailController` : `new Verified($user)` → TypeError (`User` n'implémente pas `MustVerifyEmail`) | Événement émis seulement si le contrat est implémenté |
| 15 | `ArticlePolicy::view` et `AlbumPolicy::delete` utilisaient `auth()->user()` au lieu de l'utilisateur évalué (faux avec `Gate::forUser`) | Utilisent `$user` |
| 16 | `routes/web.php` : `require_once` de `auth.php` → routes d'authentification absentes après le premier test du processus | `require` |
| 17 | Routes `admin.users.create` et `admin.users.store` exposées vers des méthodes vides (réponse 200 vide) | Routes et méthodes retirées |
| 18 | Factories : `PhotoFactory` téléchargeait une image sur cataas.com à chaque photo ; `PaymentFactory` plantait sans utilisateur ; `TripFactory::withAlbums(n, true)` appelait une méthode inexistante ; textes plus longs que la validation ; `UserFactory` sans `level` (null en mémoire) ; événements « à venir » parfois dans le passé | Corrigées |
| 19 | `database/seeders/UserSeeder.php` dépendait de l'ancien système de rôles (`RoleSeeder`, `attachRole`) | Supprimé |
| 20 | PHPDoc incorrects (`@return Response` partout, classes inexistantes) masquant les vrais types | Types de retour natifs sur contrôleurs et relations |

## 5. Problèmes connus non corrigés (décisions à prendre)

### 5.1 Paiement par carte — **critique**

`PaymentController` + `PaymentRequest` reçoivent le **numéro de carte, la date et le CVC en clair** sur le
serveur, puis créent un token et un `Charge` Stripe.
- Ce flux exige la conformité PCI-DSS SAQ D. Stripe désactive par défaut l'envoi de données de carte brutes via
  l'API, et l'API Charges est remplacée par PaymentIntents (SCA/3-D Secure non géré ici).
- `card_cvc` et `card_number` sont convertis en `int` : un CVC commençant par 0 (`012`) devient `12` et échoue à
  la validation.
- Une date d'expiration mal formée (`13/xx`) fait échouer `date_create_from_format` → erreur 500.
- Un message vide enregistre une description vide, alors que Stripe reçoit le libellé par défaut.

**Recommandation** : remplacer le formulaire par **Stripe Checkout** (redirection) ou le **Payment Element**
(Stripe.js), avec un webhook `checkout.session.completed` / `payment_intent.succeeded` qui crée le `Payment`.
Les tests `tests/Feature/PaymentTest.php` seront à réécrire autour du webhook.

### 5.2 Sécurité et robustesse

- `GET /admin/reset` exécute `route:clear`, `cache:clear`, `config:clear`, puis `config:cache` : action à effet
  de bord sur une route GET, sans protection CSRF. À passer en POST, ou mieux, dans le script de déploiement.
- Noms de fichiers `time() . '.' . extension` : deux envois dans la même seconde, même par deux utilisateurs
  différents, **s'écrasent**. Utiliser `store()` (nom aléatoire) ou `hashName()`.
- Les fichiers photo ne sont jamais supprimés du disque (§ 3.3) et le traitement d'image est synchrone (jusqu'à
  50 fichiers de 10 Mo par requête). L'orientation EXIF est ignorée, et un PNG ré-encodé en JPEG garde son
  extension `.png`.
- `ArticleController::update` ne vérifie pas l'unicité du titre : changer le titre vers celui d'un autre article
  (ou vers un titre produisant le même slug, ex. « Été » / « Ete ») provoque une violation de la contrainte
  unique sur `slug` → 500. `store` a le même problème pour deux titres différents donnant le même slug.
- `TripController::update` : un `boats[*][id]` appartenant à une **autre** sortie passe la validation
  (`exists:boats,id`) puis provoque une erreur de clé dupliquée. Réservé aux admins.
- `ThreadSubscribeButton` peut créer des abonnements en double (pas de contrainte unique).
- Un administrateur peut supprimer son propre compte, ou le dernier compte administrateur.
- `EventController::update` impose `start_time >= now` : impossible de corriger un événement en cours ou passé.
- `User::$cascadeDeletes = ['photos', 'comments']` : `comments` désigne les commentaires **reçus sur le profil**,
  pas ceux écrits par l'utilisateur. Ses commentaires sur les photos des autres restent visibles jusqu'au
  pruning. À clarifier fonctionnellement.
- Vérification d'email : fonctionnalité Breeze en sommeil (`User` n'implémente pas `MustVerifyEmail`). Décider
  de l'activer (ajouter le contrat et le middleware `verified`) ou de supprimer les routes, vues et contrôleurs
  correspondants.
- Le niveau `CONTRIBUTOR` existe mais n'ouvre aucun droit (l'écriture d'articles reste réservée aux admins).
- `TrustHosts` est désactivé, Sanctum est installé pour la seule route `/api/user`, et `BroadcastServiceProvider`
  est désactivé : du code mort à retirer ou à assumer.

### 5.3 Qualité du code

- Des vues exécutent des requêtes (`Album::all()`, `User::all()` dans les formulaires de sortie et d'album ;
  partials de la page d'accueil ; `admin/index`). À déplacer dans les contrôleurs ou des view composers.
- Logique d'upload dupliquée dans 6 contrôleurs (stocker, remplacer ou retirer une image) : à factoriser
  (trait, action ou service) en même temps que la correction des noms de fichiers.
- Validation inline dans `AlbumController`, `ArticleController` et `EventController`, contre des Form Requests
  ailleurs : à uniformiser.
- PHPStan niveau 6 : 127 erreurs restantes (propriétés Livewire non typées, génériques des relations, paramètres
  sans type). À traiter pendant la migration Livewire 3, qui réécrit ces composants.
- `composer.json` : `minimum-stability: dev`. Pendant l'installation de Larastan, une résolution a sélectionné
  `laravel/framework 9.x-dev` : passer à `stable`. Le nom (`laravel/laravel`) et la description sont ceux du
  squelette, tout comme `README.md`.
- `FakerServiceProvider` (enregistré en production) référence un paquet `require-dev` : sans effet tant que
  Faker n'est pas résolu, mais fragile.

### 5.4 Déploiement

- `.github/workflows/ftp-deploy.yml` synchronise par FTP les fichiers **suivis par git** de la branche
  `production`. `vendor/` et `public/build/` ne sont pas versionnés : ils sont donc gérés hors du workflow, à
  la main ou sur le serveur. Ce point n'a pas pu être vérifié.
- `npm run deploy` enchaîne `build`, `git stash`, un merge dans `production` et un push : le build local n'est
  pas déployé, puisque `public/build` est ignoré par git.
- Ni migrations, ni `storage:link`, ni vidage de cache automatisés (d'où `/admin/reset`).
- Le workflow utilise `actions/checkout@v2.1.0` et `SamKirkland/FTP-Deploy-Action@master` (branche flottante :
  risque de chaîne d'approvisionnement). Épingler une version ou un SHA.

**Recommandation** : un workflow qui exécute `composer install --no-dev -o` et `npm ci && npm run build`, puis
déploie l'artefact complet (FTP ou rsync/SSH si l'hébergeur le permet) et lance `php artisan migrate --force`
et `optimize`.

### 5.5 Dépendances front

`npm audit` : 22 vulnérabilités (1 critique `form-data` via axios, 14 hautes dont `vite`, `axios`, `lodash`,
`postcss`, `dompurify` via Toast UI). Vite 3 → version actuelle avec `laravel-vite-plugin` correspondant ;
axios 0.27 → 1.x ; Tailwind 3 (la v4 change la configuration : migration à part) ; `@shufo/prettier-plugin-blade`
est figé en 0.2.1.

## 6. Feuille de route de modernisation proposée

Chaque étape : une branche, `composer test` et `composer analyse` verts, puis un déploiement de recette.

1. **Hygiène (rapide)** : `minimum-stability: stable` ; `composer update` dans les contraintes actuelles
   (corrige une partie des 53 alertes : guzzle, symfony, commonmark, livewire 2.12.x) ; `npm audit fix` sans
   changement de version majeure ; épingler les actions GitHub ; vrai `README.md`.
2. **Paiement** (§ 5.1), indépendamment des montées de version.
3. **PHP 8.2 ou 8.3** sur l'hébergement et en CI (Laravel 10 accepte 8.1–8.3).
4. **Laravel 9 → 10** : PHPUnit 10 (attributs `#[DataProvider]` à la place des annotations, `phpunit.xml` à
   migrer), Sanctum 3, Collision 7, Ignition 2. Types de retour natifs ajoutés par Laravel 10 dans les classes du
   framework (middlewares, providers) : PHPStan les signalera.
5. **Livewire 2 → 3** : changement majeur (`wire:model` différé par défaut, `$emit` → `$dispatch`,
   `$listeners` → `#[On]`, `dispatchBrowserEvent` → `dispatch`, namespace `App\Livewire`, Alpine embarqué par
   Livewire, donc à retirer de `resources/js/app.js`). Les tests `tests/Feature/Livewire` servent de
   spécification. Utiliser l'outil `livewire:upgrade`.
6. **Laravel 10 → 11 → version courante** : nouvelle structure de `bootstrap/app.php` (optionnelle),
   **renommer la macro `whereLike`** (conflit avec la méthode native depuis Laravel 11 : la méthode native est
   appelée et reçoit un tableau de colonnes → erreur), `casts()` en méthode, Larastan 3 / PHPStan 2.
   Remplacer `symfony/sendinblue-mailer` (abandonné) par `symfony/brevo-mailer` (transport `brevo+api`).
7. **Monter le niveau PHPStan** (6, puis 8) une fois Livewire 3 en place.
8. **Améliorations fonctionnelles** : upload d'images factorisé et sûr (§ 5.2), notifications en file d'attente,
   vérification d'email ou suppression du code mort, droits du niveau CONTRIBUTOR.

## 7. Environnement de développement

PHP n'est pas installé sur la machine du mainteneur (WSL Ubuntu). Image utilisée pour cette intervention :

```dockerfile
FROM php:8.1-cli
RUN apt-get update && apt-get install -y --no-install-recommends git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-jpeg --with-freetype \
 && docker-php-ext-install -j$(nproc) gd exif zip pdo_mysql bcmath \
 && pecl install pcov && docker-php-ext-enable pcov \
 && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN echo "memory_limit=-1" > /usr/local/etc/php/conf.d/mem.ini
WORKDIR /app
```

```bash
docker build -t akipaj-php:8.1 .     # depuis le dossier contenant ce Dockerfile
docker run --rm -u "$(id -u):$(id -g)" -e HOME=/tmp -v "$PWD":/app akipaj-php:8.1 composer install
docker run --rm -u "$(id -u):$(id -g)" -e HOME=/tmp -v "$PWD":/app akipaj-php:8.1 composer test
```

Composer 2.10 bloque par défaut l'installation des versions ayant des alertes de sécurité **et** des paquets
signalés comme malware. Toutes les versions de Laravel 9 ont des alertes : pour une mise à jour ciblée dans
Laravel 9, désactiver **uniquement** le blocage des alertes (`policy.advisories.block: false` et
`audit.block-insecure: false` dans une config Composer globale temporaire), **jamais** `--no-blocking`, qui
désactive aussi le filtre malware.

Données de démo : `php artisan migrate:fresh --seed` en environnement `local` exécute `LocalEnvironmentSeeder`.
Comptes créés, mot de passe `password` : `michel@test.com` (admin), `henry@test.com` (membre),
`lucien@test.com` (GUEST).
