# Akipaj — guide pour les agents IA

Site web privé de l'association de voile **Akipaj** : actualités, programme des événements, historique des
sorties en mer (avec bateaux et équipages), albums photos commentés, annuaire des membres et virements
(cotisations) par carte via Stripe. Interface et URLs **en français**, code et commentaires en anglais/français.

Rapport d'analyse complet, bugs connus et feuille de route de modernisation :
[docs/ai/rapport-modernisation.md](docs/ai/rapport-modernisation.md). **Lis-le avant toute montée de version.**

## Stack (état au 2026-10-19)

| Couche | Version | Remarque |
|---|---|---|
| PHP | `^8.0.2` déclaré, testé en **8.1** | Runtime Sail 8.1 |
| Laravel | **9.52** (fin de support) | Squelette Breeze 1 (Blade) |
| Livewire | **2.12** | Composants dans `app/Http/Livewire` |
| Front | Vite 3, Tailwind 3, Alpine 3, Flowbite, Toast UI Editor | `resources/js`, `resources/css` |
| Tests | PHPUnit 9.6, SQLite en mémoire | 222 tests, ~98 % de lignes couvertes |
| Analyse statique | PHPStan 1.12 + Larastan 2.11, **niveau 5**, sans baseline | `phpstan.neon` |
| Paiement | stripe-php 10 (API Charges + Tokens) | Voir problèmes connus |
| Mail | Sendinblue (`symfony/sendinblue-mailer`, abandonné → Brevo) | `AppServiceProvider` |

## Commandes

PHP **n'est pas installé** sur la machine de développement (WSL). Tout passe par Docker.

```bash
# Image locale minimale (une fois) : php:8.1-cli + gd(jpeg) + exif + zip + pdo_mysql + pcov + composer
# Voir la section « Environnement » du rapport pour le Dockerfile.
docker run --rm -u "$(id -u):$(id -g)" -e HOME=/tmp -v "$PWD":/app -w /app akipaj-php:8.1 composer test
docker run --rm -u "$(id -u):$(id -g)" -e HOME=/tmp -v "$PWD":/app -w /app akipaj-php:8.1 composer analyse
# Alternative : Laravel Sail (docker-compose.yml), une fois vendor/ installé : ./vendor/bin/sail composer test
```

- `composer test` : PHPUnit (`composer test -- --filter=PhotoTest` pour cibler)
- `composer test:coverage` : couverture texte (nécessite pcov ou xdebug)
- `composer analyse` : PHPStan + Larastan
- `npm run dev` / `npm run build` : assets Vite. `npm run prettier` formate le code (Prettier, y compris PHP et Blade).
- CI : `.github/workflows/tests.yml` (tests + PHPStan sur push `master` et PR). Déploiement :
  `ftp-deploy.yml` sur push de la branche `production`.

**Avant de rendre la main : `composer test` et `composer analyse` doivent passer.** Ne pas ajouter de baseline
PHPStan pour masquer une erreur : corriger le code, ou documenter l'exception dans `phpstan.neon` avec sa raison.

## Carte du code

```
routes/web.php        Routes principales. Groupes : public → auth → level:MEMBER → level:ADMINISTRATOR (/admin)
routes/auth.php       Breeze, URLs françaises (connexion, inscription, mot-de-passe-oublie…)
routes/bindings.php   {username} → User::findByUsername()
app/Enums             UserLevelEnum (GUEST=0, MEMBER=1, CONTRIBUTOR=10, ADMINISTRATOR=99), AlertLevelEnum
app/Http/Middleware/EnsureHasLevel.php   alias "level:<int>" ; GUEST insuffisant → redirection vers son profil, sinon 403
app/Http/Controllers  CRUD ressources (Album, Article, Event, Photo, Trip), Payment, Account/{Profile,Settings}, Admin/User
app/Http/Livewire     Comments, Gallery, Lightbox, SearchInput, ThreadSubscribeButton, Album/AlbumList,
                      Trip/Boats, Admin/RegistrationValidation, Admin/User/Catalog
app/Http/Requests     Form requests (Trip, Photo, Payment, ProfileUpdate, Admin/UserUpdate, Auth/Login)
app/Policies          Découvertes automatiquement par convention de nommage (AuthServiceProvider vide)
app/Traits/Commentable.php  Commentaires polymorphes + abonnements aux fils (Photo et User)
app/Models            User (SoftDeletes, CascadeSoftDeletes, Prunable), Photo (SoftDeletes), Comment (SoftDeletes),
                      Album, Article (clé de route = slug), Event, Trip, Boat, Payment (SoftDeletes), ThreadSubscription
resources/views       Blade ; certaines vues font des requêtes Eloquent directement (formulaires, homepage)
```

Vocabulaire : *sortie* = `Trip`, *programme* = `Event`, *actualités* = `Article`, *matelots* = liste des membres,
*virements* = `Payment`, *compte* = paramètres (`SettingsController`), *profil* = page publique d'un membre.

## Règles métier

- Inscription → niveau `GUEST` ; un admin valide via `Admin/RegistrationValidation` (→ `MEMBER` + mail).
- `GUEST` : ne voit que son profil, ses paramètres et les virements. `MEMBER`+ : tout le contenu.
  `CONTRIBUTOR` n'a **aucun droit supplémentaire implémenté** (prévu pour écrire des articles).
- Articles, événements, sorties : écriture **admin seulement**. Brouillons d'articles visibles des admins seulement.
- Albums : créés et modifiés par tout membre ; suppression interdite s'il contient des photos d'autres membres.
- Photos et commentaires : modifiables et supprimables par leur auteur ; un admin peut tout supprimer, mais ne
  peut pas **réécrire** le commentaire d'un autre (`CommentPolicy::before`).
- Poster une photo abonne son auteur au fil de commentaires ; chaque commentaire envoie un mail aux abonnés,
  sauf à son auteur.
- Supprimer un compte = soft delete en cascade (photos, commentaires reçus sur le profil) ; `model:prune`
  (planifié chaque jour) supprime définitivement après un mois.

## Conventions à respecter

- Messages flash : `session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Message en français.')`,
  affichés par `layouts/app.blade.php` via `<x-alert>`.
- Formulaires d'édition : `validateWithBag('updateXxx', …)` ; booléens d'interface (`online`, `remove_image`)
  normalisés avec `filter_var(…, FILTER_VALIDATE_BOOLEAN)` avant validation.
- Images de couverture : `storeAs('public/<dossier>', time() . '.' . ext)` sur le disque `local`, URL
  `Storage::url()` stockée dans `imagePath`. Dossiers : `albums`, `articles`, `events`, `trips`, `profile_pictures`.
  Pour supprimer : `Storage::delete('public/<dossier>/' . basename($model->imagePath))`.
- Photos : **exception**, écrites directement dans `public_path('storage/photos')` (et `/thumbs`) avec GD.
- Contrôleurs : types de retour natifs (`View`, `RedirectResponse`, `JsonResponse`). Relations Eloquent :
  type de retour natif obligatoire (sinon Larastan ne les voit pas).
- Recherche texte : macro `whereLike(array|string $columns, string $term)` (AppServiceProvider). **Elle entre en
  conflit avec `whereLike()` natif de Laravel ≥ 11** : à renommer lors de la montée de version.
- `StripeClient` est résolu par le conteneur (binding dans `AppServiceProvider`) : l'injecter, ne jamais faire `new`.
- Formatage : Prettier (`.prettierrc.json`, guillemets simples, largeur 120). Pint est installé mais pas utilisé.

## Écrire des tests

- Étendre `Tests\TestCase` + `RefreshDatabase`. Helpers : `createUser(UserLevelEnum, attrs)`,
  `actingAsLevel()`, `actingAsMember()`, `actingAsAdmin()`.
- `TestCase::setUp()` appelle `withoutVite()` et redirige `public_path()` vers un dossier temporaire
  (`$this->publicPath`) : les photos ne polluent pas `public/`.
- Fichiers : `Storage::fake('local')` + `UploadedFile::fake()->image()` (GD requis).
- Stripe : voir `tests/Feature/PaymentTest.php` (sous-classe de `StripeClient` avec services mockés). Aucun appel réseau.
- Livewire 2 : `Livewire::test(Component::class, [...])->set()->call()->emit()` ; `viewData('x')` pour lire les données de vue.
- Pièges des factories : `PhotoFactory`, `CommentFactory` et `PaymentFactory` réutilisent **au hasard** un
  album ou un utilisateur existant. Passe toujours `->for($album)` / `['user_id' => …]` quand le test en dépend.
  `PhotoFactory` écrit de vrais fichiers JPEG (générés localement). `UserFactory` crée un `GUEST` par défaut.
- Les tests doivent être déterministes : fixer les dates et longueurs dont dépend une assertion.

## Pièges connus (résumé, détails dans le rapport)

- Paiement : les numéros de carte transitent par le serveur (API Tokens/Charges dépréciée, non conforme
  PCI-DSS SAQ A). À remplacer par Stripe Checkout ou Payment Element, pas à étendre.
- `GET /admin/reset` vide les caches puis exécute `config:cache` : ne jamais l'appeler dans un test.
- Noms de fichiers `time().ext` : deux envois dans la même seconde s'écrasent.
- Le déploiement FTP n'envoie que les fichiers suivis par git : `vendor/` et `public/build` ne sont pas versionnés.
- `minimum-stability: dev` dans `composer.json` : une mise à jour peut installer des branches `-dev`
  (déjà arrivé avec `laravel/framework 9.x-dev`). Vérifier le lock après chaque `composer update`.
- Les paquets `laravel-lang/*` ont été retirés (signalés comme malware sur Packagist). Les traductions sont
  versionnées dans `lang/` : ne pas les réinstaller.
