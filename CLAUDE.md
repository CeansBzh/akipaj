# Akipaj — guide pour les agents IA

Site web privé de l'association de voile **Akipaj** : actualités, programme des événements, historique des
sorties en mer (avec bateaux et équipages), albums photos commentés, annuaire des membres et virements
(cotisations) par carte via Stripe. Interface et URLs **en français**, code et commentaires en anglais/français.

Ce fichier contient deux parties : ce guide propre au projet (prioritaire en cas de contradiction), puis les
guidelines génériques de **Laravel Boost** (bloc final, régénéré par `boost:update` à chaque `composer update` :
ne pas l'éditer à la main, et ne jamais écrire le nom de sa balise ailleurs dans ce fichier). Analyse, historique et problèmes connus :
[docs/ai/rapport-modernisation.md](docs/ai/rapport-modernisation.md).

## Stack (état au 2026-10-19)

| Couche | Version | Remarque |
|---|---|---|
| PHP | `^8.3` (lock résolu pour 8.3), **8.4** sous Sail | `config.platform.php` = 8.3.0 dans `composer.json` |
| Laravel | **13** | Structure Laravel 11+ (`bootstrap/app.php`, pas de kernels) |
| Livewire | **4** | Composants classiques dans `app/Livewire`, vues dans `resources/views/**/livewire` |
| Front | Vite 8, Tailwind **3.4**, Alpine (fourni par Livewire), Toast UI Editor | `resources/js`, `resources/css` |
| Tests | PHPUnit 12, SQLite en mémoire | 222 tests |
| Analyse statique | PHPStan 2 + Larastan 3, **niveau 5**, sans baseline | `phpstan.neon` |
| IA | Laravel Boost 2 (MCP + skills `.claude/skills`) | `.mcp.json`, `boost.json` |
| Paiement | stripe-php 10 (API Charges + Tokens) | À remplacer, voir problèmes connus |
| Mail | Brevo (`symfony/brevo-mailer`) | Mailers `brevo` et `sendinblue` (ancien nom) |

## Commandes (toujours via Sail)

```bash
vendor/bin/sail up -d                 # PHP 8.4, MySQL 8.4, Redis, Mailpit (:8025), phpMyAdmin (:8081)
vendor/bin/sail composer test         # PHPUnit (ajouter -- --filter=PhotoTest pour cibler)
vendor/bin/sail composer analyse      # PHPStan + Larastan
vendor/bin/sail composer lint         # Pint (style du code PHP, preset laravel)
vendor/bin/sail npm run build         # assets Vite (requis pour naviguer, pas pour les tests)
vendor/bin/sail artisan migrate:fresh --seed   # données de démo (APP_ENV=local)
```

- Premier démarrage sans `vendor/` : `docker run --rm -u "$(id -u):$(id -g)" -v "$(pwd):/var/www/html" -w /var/www/html laravelsail/php84-composer:latest composer install`,
  puis `cp .env.example .env`, `sail up -d`, `sail artisan key:generate`, `sail artisan storage:link`,
  `mkdir -p storage/app/public/photos/thumbs`, `sail npm ci && sail npm run build`.
- Comptes de démo (mot de passe `password`) : `michel@test.com` (admin), `henry@test.com` (membre),
  `lucien@test.com` (GUEST).
- `composer test:coverage` : couverture (pcov est inclus dans l'image Sail).
- Formatage : **Pint** pour tout le PHP (`pint.json`, preset `laravel` ; vérifié en CI avec `composer lint:test`).
  Prettier (`npm run prettier`) ne sert plus qu'au Blade, au JS et au CSS ; il n'a plus de plugin PHP.
- CI : `.github/workflows/tests.yml` (PHP 8.3 et 8.4 : Pint, build Vite, tests, PHPStan). Déploiement : `ftp-deploy.yml`
  sur push de la branche `production`.

**Avant de rendre la main : `composer lint`, `composer test` et `composer analyse` doivent passer.** Ne pas ajouter de baseline
PHPStan pour masquer une erreur : corriger le code, ou documenter l'exception (`@phpstan-ignore <identifiant>` + raison).

## Carte du code

```
bootstrap/app.php     Routing (web, api, console, /up), alias de middleware "level", redirections invité/connecté
bootstrap/providers.php  AppServiceProvider uniquement (policies et listeners auto-découverts)
routes/web.php        Groupes : public → auth → level:MEMBER → level:ADMINISTRATOR (/admin). "/" = route "home"
routes/auth.php       Authentification (ex-Breeze), URLs françaises (connexion, inscription, mot-de-passe-oublie…)
routes/console.php    Planification : model:prune quotidien
app/Providers/AppServiceProvider.php  Binding StripeClient, Route::bind('username'), transport Brevo, Faker markdown
app/Enums             UserLevelEnum (GUEST=0, MEMBER=1, CONTRIBUTOR=10, ADMINISTRATOR=99), AlertLevelEnum
app/Http/Middleware/EnsureHasLevel.php  "level:<int>" ; GUEST insuffisant → redirection vers son profil, sinon 403
app/Http/Controllers  CRUD (Album, Article, Event, Photo, Trip), Payment, Account/{Profile,Settings}, Admin/User, Auth/*
app/Http/Requests     Form requests (Trip, Photo, Payment, ProfileUpdate, Admin/UserUpdate, Auth/Login)
app/Livewire          Comments, Gallery, Lightbox, SearchInput, ThreadSubscribeButton, Album/AlbumList,
                      Trip/Boats, Admin/RegistrationValidation, Admin/User/Catalog
app/Policies          Découvertes par convention de nommage
app/Traits/Commentable.php  Commentaires polymorphes + abonnements aux fils (Photo et User)
app/Models            User (SoftDeletes, CascadeSoftDeletes, Prunable), Photo (SoftDeletes), Comment (SoftDeletes),
                      Album, Article (clé de route = slug), Event, Trip, Boat, Payment (SoftDeletes), ThreadSubscription
resources/js/app.js   Importe Livewire + Alpine depuis vendor/livewire/livewire/dist/livewire.esm, puis Livewire.start()
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
  supprime définitivement après un mois.

## Conventions propres au projet

- Messages flash : `session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Message en français.')`,
  affichés par `layouts/app.blade.php` via `<x-alert>`.
- Formulaires d'édition : `validateWithBag('updateXxx', …)` ; booléens d'interface (`online`, `remove_image`)
  normalisés avec `filter_var(…, FILTER_VALIDATE_BOOLEAN)` avant validation.
- Images de couverture : `storeAs('public/<dossier>', time() . '.' . ext)` sur le disque `local` (racine
  `storage/app`, **pas** `app/private` comme dans le squelette Laravel 13), URL `Storage::url()` stockée dans
  `imagePath`. Dossiers : `albums`, `articles`, `events`, `trips`, `profile_pictures`.
  Pour supprimer : `Storage::delete('public/<dossier>/' . basename($model->imagePath))`.
- Photos : **exception**, écrites directement dans `public_path('storage/photos')` (et `/thumbs`) avec GD.
- Recherche texte : `whereAny([...colonnes], 'like', "%{$terme}%")` (l'ancienne macro `whereLike` a été supprimée).
- Livewire : `wire:model` est **différé** ; utiliser `wire:model.live` (ou `.live.debounce.300ms`) pour une mise
  à jour immédiate. Événements : `#[On('nom')]` côté PHP, `$this->dispatch('nom', param: …)` et, côté JS,
  `Livewire.dispatch('nom', { param: … })` (paramètres **nommés**). Les actions reçoivent des identifiants
  (`int $commentId`) et chargent le modèle avec `findOrFail`. Fermer les balises `<livewire:… />` (sinon slot).
- Données injectées dans du JavaScript inline : `@js($valeur)`, jamais `"{{ $valeur }}"`.
- Images statiques référencées avec `Vite::asset()` : doivent être sous `resources/images` ou
  `resources/views/**/images` (glob eager dans `app.js`).
- `StripeClient` est résolu par le conteneur : l'injecter, ne jamais faire `new`.
- Relations Eloquent : type de retour natif **et** PHPDoc générique (`@return HasMany<Photo, $this>`), sinon
  Larastan ne les comprend pas.
- La configuration (`config/*.php`) est celle de Laravel 9, conservée pour rester compatible avec le `.env` de
  production (`CACHE_DRIVER`, `MAIL_ENCRYPTION`…). Ne pas la remplacer par celle du squelette sans migrer le `.env`.

## Écrire des tests

- Étendre `Tests\TestCase` + `RefreshDatabase`. Helpers : `createUser(UserLevelEnum, attrs)`,
  `actingAsLevel()`, `actingAsMember()`, `actingAsAdmin()`.
- `TestCase::setUp()` appelle `withoutVite()` et `usePublicPath()` vers un dossier temporaire
  (`$this->publicPath`) : les photos ne polluent pas `public/`.
- Fichiers : `Storage::fake('local')` + `UploadedFile::fake()->image()` (GD requis).
- Stripe : voir `tests/Feature/PaymentTest.php` (sous-classe de `StripeClient` avec services mockés). Aucun appel réseau.
- Livewire : `Livewire::test(Component::class, [...])->set()->call()->dispatch('evt', param: …)` ;
  `viewData('x')` pour lire les données de vue ; `assertDispatched()`.
- Data providers : attribut `#[DataProvider('nom')]` (les annotations `@dataProvider` ne marchent plus).
- Pièges des factories : `PhotoFactory`, `CommentFactory` et `PaymentFactory` réutilisent **au hasard** un
  album ou un utilisateur existant. Passe toujours `->for($album)` / `['user_id' => …]` quand le test en dépend.
  `PhotoFactory` écrit de vrais fichiers JPEG (générés localement). `UserFactory` crée un `GUEST` par défaut.

## Pièges connus (résumé, détails dans le rapport)

- **Déploiement** : la production doit tourner en PHP ≥ 8.3, avec `composer install --no-dev` et
  `npm run build` exécutés pour ce déploiement. Le workflow FTP n'envoie que les fichiers suivis par git
  (`vendor/` et `public/build` ne le sont pas).
- Paiement : les numéros de carte transitent par le serveur (API Tokens/Charges dépréciée, non conforme
  PCI-DSS SAQ A). À remplacer par Stripe Checkout ou Payment Element, pas à étendre.
- `GET /admin/reset` vide les caches puis exécute `config:cache` : ne jamais l'appeler dans un test.
- Noms de fichiers `time().ext` : deux envois dans la même seconde s'écrasent.
- Les paquets `laravel-lang/*` ont été retirés (signalés comme malware sur Packagist). Les traductions sont
  versionnées dans `lang/` : ne pas les réinstaller.
- Composer bloque les versions ayant des alertes de sécurité et les paquets malveillants : ne jamais utiliser
  `--no-blocking` (désactive aussi le filtre malware).

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

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/sail bin pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/sail bin pint --test --format agent`, simply run `vendor/bin/sail bin pint --format agent` to fix any formatting issues.

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
