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
- Formatage : Prettier (`.prettierrc.json`, guillemets simples, largeur 120). Pint n'est pas utilisé (retiré).

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

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.4. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `vendor/bin/sail composer show --direct` to list direct dependencies with versions, or `vendor/bin/sail composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `vendor/bin/sail npm run build` or ask the user to run `vendor/bin/sail npm run dev` or `vendor/bin/sail composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `vendor/bin/sail artisan route:list`). Use `vendor/bin/sail artisan list` to discover available commands and `vendor/bin/sail artisan [command] --help` to check parameters.
- Inspect routes with `vendor/bin/sail artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `vendor/bin/sail artisan config:show app.name`, `vendor/bin/sail artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `vendor/bin/sail artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `vendor/bin/sail artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== sail rules ===

# Laravel Sail

- This project runs inside Laravel Sail's Docker containers. You MUST execute all commands through Sail.
- Start services using `vendor/bin/sail up -d` and stop them with `vendor/bin/sail stop`.
- Open the application in the browser by running `vendor/bin/sail open`.
- Always prefix PHP, Artisan, Composer, and Node commands with `vendor/bin/sail`. Examples:
    - Run Artisan Commands: `vendor/bin/sail artisan migrate`
    - Install Composer packages: `vendor/bin/sail composer install`
    - Execute Node commands: `vendor/bin/sail npm run dev`
    - Execute PHP scripts: `vendor/bin/sail php [script]`
- View all available Sail commands by running `vendor/bin/sail` without arguments.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `vendor/bin/sail artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `vendor/bin/sail artisan list` and check their parameters with `vendor/bin/sail artisan [command] --help`.
- If you're creating a generic PHP class, use `vendor/bin/sail artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `vendor/bin/sail artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `vendor/bin/sail artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== livewire/core rules ===

# Livewire

- Livewire allows you to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `vendor/bin/sail artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `vendor/bin/sail artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/sail bin phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

</laravel-boost-guidelines>
