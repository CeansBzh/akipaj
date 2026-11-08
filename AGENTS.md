<laravel-boost-guidelines>
=== .ai/design rules ===

# Design

## Le site et son public

- Akipaj est le site d'un club de voile. Ses visiteurs sont les membres du club, dont beaucoup de personnes âgées peu à l'aise avec le numérique. Ils le consultent surtout sur téléphone, parfois avec une connexion lente.
- Objectif principal : annoncer les prochaines sorties et permettre de consulter les photos (albums réservés aux membres).
- Objectif secondaire : garder l'historique des sorties passées du club.
- Les événements ont été fusionnés dans les sorties (`trips`) : un événement est désormais une sortie à venir. Concevoir une seule carte et une seule page de sortie qui couvrent les deux cas.
- Les fonctionnalités sociales (pages de profil `profile.*`, commentaires sous les photos) vont être supprimées : ne pas les redessiner et ne pas en ajouter de nouvelles.
- Hors périmètre pour l'instant : l'administration (`resources/views/admin`, `resources/views/components/admin`, `layouts/admin.blade.php`). Ne pas la modifier.

## Direction visuelle

- Toujours moderne : le public âgé n'est pas une raison pour un style daté. Suivre les codes actuels (grands arrondis, photos en plein cadre, verre et flou progressif pour poser du texte sur une photo, icônes au trait fin, boutons ronds ou en pilule). L'accessibilité passe par la lisibilité, pas par un style ancien.
- Élégant et un peu ludique, mais simple : la clarté passe avant l'effet. Une seule touche marquante par page, le reste reste calme.
- C'est un site de voile : les touches marines sont bienvenues (vagues, voiles, cordages, pavillons, bouées, cartes marines…). Elles sont dessinées en SVG léger directement dans la page, purement décoratives (`aria-hidden="true"`), une ou deux par page, et ne gênent jamais la lecture.
- Logo : SVG dans `layouts/navigation.blade.php`, version plus grande dans `components/application-logo.blade.php`, et favicon (`public/favicon*`, `public/apple-touch-icon.png`…). Il est bleu `#0ea5e9` (le `sky-500` de Tailwind 3) et blanc.
- Thème clair uniquement : pas de variantes `dark:`.
- Couleur principale : les classes `primary-*`, déclarées dans le bloc `@theme` de `resources/css/app.css` et aujourd'hui égales à la palette `sky`. Ne pas écrire de `sky-*` dans du nouveau code, et remplacer les `sky-*` par `primary-*` dans les fichiers modifiés. Neutres en `gray`. Rouge, vert, jaune et bleu sont réservés aux messages d'état (composant `x-alert`).
- Les photos du club sont le meilleur décor : les mettre en avant plutôt que des illustrations ou des dégradés décoratifs.
- Verre (fond translucide et `backdrop-blur`) : bienvenu sur une photo, pour y poser un badge, un bouton ou du texte lisible (voir `x-trip-card`). Pas sur un fond uni, où il n'apporte rien.

## Cartes de sortie

Par ordre d'importance :

1. Une grande photo, qui donne envie d'en savoir plus.
2. Le statut : en cours, à venir ou terminée.
3. Les dates.
4. Le lieu.
5. Un bouton « Ajouter à mon agenda » bien visible, pour les sorties à venir ou en cours (aujourd'hui le composant `x-trip-to-agenda`).

Le titre et la description passent après.

Sur l'accueil, seule la sortie à venir la plus proche a la forme d'un billet (composant `x-trip-ticket`, encoches dessinées par la classe `.ticket` de `resources/css/app.css`). Les suivants sont de petites cartes sans style de billet, sur deux colonnes à partir de `sm` (composant `x-trip-card`) : photo en plein cadre, date et statut sur fond de verre, nom, dates et lieu sur un flou progressif, bouton d'agenda rond en icône seule. Les cartes des dernières sorties n'ont pas de coins arrondis. Les dates d'une sortie s'écrivent avec le composant `x-date-range` (« Du 12 au 14 juin 2026 »).

La page des sorties les présente de la plus récente à la plus ancienne, rangées par année, en grandes cartes (composant `x-trip-entry`), sous une houle dessinée au trait (`x-sea-swell`). Sur grand écran, une frise (`x-trip-timeline`) les accompagne à gauche : elle suit la lecture, mène à une année ou à une sortie, et espace les sorties selon le temps écoulé. Sur téléphone, les années se choisissent en haut de la liste.

## Albums photo

- Un album contient de 10 à plus de 1 000 photos. Ne jamais les charger toutes d'un coup : la galerie (`App\Livewire\Gallery`) les charge par pages de 100.
- Réserver la place de chaque vignette (`aspect-ratio` ou `width`/`height`) pour que la page ne saute pas pendant le chargement, et afficher le nombre de photos de l'album.

## Typographie

- Texte : une police sans empattements (Nunito aujourd'hui).
- Titres : Merriweather, appliquée à tous les `h1` à `h6` par une règle de base de `resources/css/app.css` (variable `--font-merriweather`). Ne pas forcer une autre police sur un titre.
- La police Guttie ne sert qu'au logo, déjà dessiné en SVG : ne pas l'utiliser ailleurs.
- Deux familles au plus, et seulement les graisses réellement utilisées.
- Texte courant en `text-base` (16 px) au minimum. `text-sm` pour les informations secondaires seulement, jamais `text-xs` pour une information utile.
- Lignes de 80 caractères au plus (`max-w-prose`) et interligne généreux.

## Accessibilité

- Contraste WCAG AA au minimum : texte bleu sur fond clair en `primary-700` ou plus foncé, texte blanc sur un fond `primary-700` ou plus foncé (`primary-500` et `primary-600` ne suffisent pas pour du texte). Texte gris en `gray-600` ou plus foncé, `gray-500` seulement sur fond blanc. Sur une photo, garder un fond assez opaque derrière le texte.
- Cibles tactiles d'au moins 44 × 44 px, bien espacées.
- Toute action porte un libellé texte : une icône seule ne suffit pas. Seule exception, voulue : le bouton d'agenda des petites cartes de sortie à venir, avec un `aria-label` et un `title`. Les liens dans un texte sont soulignés.
- Rien ne dépend du survol (inexistant sur téléphone) ni d'un geste caché (balayage, appui long) : chaque action a un bouton visible. Pas de défilement automatique, carrousels compris.
- Dates écrites en toutes lettres au format français (« samedi 14 juin 2026 ») plutôt qu'en chiffres (« 14/06 »).
- Focus clavier visible, `prefers-reduced-motion` respecté, animations rares et courtes, seulement en réponse à une action.
- La mise en page supporte un zoom du texte à 200 % sans contenu coupé ni défilement horizontal.

## Mobile d'abord, connexion lente, navigateurs récents

- Écrire les classes pour le téléphone, puis adapter avec `sm:`, `md:` et `lg:`. Vérifier à 360 px de large avant les grands écrans.
- Seuls les navigateurs récents sont pris en charge : utiliser librement le CSS et le JavaScript modernes (requêtes de conteneur, `:has()`, `aspect-ratio`, `dvh`, `@starting-style`…), sans préfixes ni solutions de repli pour les anciens navigateurs.
- Grilles et listes de photos : toujours la vignette (`thumb_path`), l'image d'origine seulement sur la page d'une photo.
- Images avec `width` et `height`, `loading="lazy"` sauf la première image visible. Images statiques en avif ou webp, à la taille d'affichage (`srcset`).
- Pas de vidéo, de grande image décorative ni d'animation lourde : le décor se fait en CSS ou en SVG léger.
- Une bibliothèque lourde qui ne sert qu'à une action se charge à la demande avec `import()`, comme add-to-calendar-button, téléchargée au premier clic sur « Ajouter à mon agenda » (`window.addToAgenda` dans `resources/js/bootstrap.js`). Une bibliothèque propre à quelques pages a sa propre entrée Vite, ajoutée par son composant (voir `x-rich-text-input`).
- Pas de nouvelle bibliothèque JS ou CSS ni de nouvelle police sans accord.

## Mise en œuvre

- Réutiliser et faire évoluer les composants de `resources/views/components` (`x-primary-button`, `x-secondary-button`, `x-text-input`, `x-modal`, `x-alert`…) plutôt que d'en créer des variantes parallèles.
- Couleurs et polices du thème dans le bloc `@theme` de `resources/css/app.css`.
- Textes en français, en vouvoyant, en phrases courtes. Un bouton dit ce qu'il fait (« Ajouter à mon agenda », pas « Valider »).
- Après un changement visuel, faire une capture d'écran de la page à 360 px et à 1280 px de large pour vérifier le rendu.

=== .ai/langue rules ===

# Langue

- Écrire en français tout texte lu par un humain : textes de l'interface, messages de validation, d'erreur et de notification, e-mails, commentaires de code et PHPDoc (y compris ceux générés par les commandes `make:`), messages de commit et descriptions de pull request.
- Garder en anglais les identifiants (classes, méthodes, variables, noms de tests, clés de configuration), comme le reste du code.

=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

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
