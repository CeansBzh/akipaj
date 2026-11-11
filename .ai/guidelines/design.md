# Design

## Le site et son public

- Akipaj est le site d'un club de voile. Ses visiteurs sont les membres du club, dont beaucoup de personnes âgées peu à l'aise avec le numérique. Ils le consultent surtout sur téléphone, parfois avec une connexion lente.
- Objectif principal : annoncer les prochaines sorties et permettre de consulter les photos (albums réservés aux membres).
- Objectif secondaire : garder l'historique des sorties passées du club.
- Les événements ont été fusionnés dans les sorties (`trips`) : un événement est désormais une sortie à venir. Concevoir une seule carte et une seule page de sortie qui couvrent les deux cas.
- Les commentaires des photos se lisent et s'écrivent dans un panneau de la visionneuse : ne pas ajouter d'autres fonctionnalités sociales. La liste des matelots et les pages de profil (`profile.*`) restent et sont redessinées.
- Hors périmètre pour l'instant : l'administration (`resources/views/admin`, `resources/views/components/admin`, `layouts/admin.blade.php`). Ne pas la modifier.

## Direction visuelle

- Toujours moderne : le public âgé n'est pas une raison pour un style daté. Suivre les codes actuels (grands arrondis, photos en plein cadre, verre et flou progressif pour poser du texte sur une photo, icônes au trait fin, boutons ronds ou en pilule). L'accessibilité passe par la lisibilité, pas par un style ancien.
- Élégant et un peu ludique, mais simple : la clarté passe avant l'effet. Une seule touche marquante par page, le reste reste calme.
- C'est un site de voile : les touches marines sont bienvenues (vagues, voiles, cordages, pavillons, bouées, cartes marines…). Elles sont dessinées en SVG léger directement dans la page, purement décoratives (`aria-hidden="true"`), une ou deux par page, et ne gênent jamais la lecture.
- Logo : SVG dans `layouts/navigation.blade.php`, version plus grande dans `components/application-logo.blade.php`, et favicon (`public/favicon*`, `public/apple-touch-icon.png`…). Il est bleu `#0ea5e9` (le `sky-500` de Tailwind 3) et blanc.
- Thème clair uniquement : pas de variantes `dark:`. Seule la visionneuse des photos a un fond noir, et ses panneaux sont sombres comme elle.
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

La page des sorties les présente de la plus récente à la plus ancienne, rangées par année, en grandes cartes photo sur le modèle des petites cartes de l'accueil (composant `x-trip-entry`), sur une mer dessinée au trait qui reste en fond de page (`x-sea-swell`). Sur grand écran, une frise (`x-trip-timeline`) accompagne les cartes à gauche, sur toute la hauteur de l'écran : elle montre toutes les années, déplie seulement celle en cours de lecture sur ses sorties espacées selon le temps écoulé, suit la lecture et mène à une année ou à une sortie. Sur téléphone, les années se choisissent en haut de la liste.

## Matelots

La liste des matelots sert à retrouver quelqu'un. Une recherche par nom, en haut, filtre les cartes pendant la frappe sans tenir compte des accents, des majuscules ni des tirets, et son champ reste à l'écran pendant le défilement. Chaque carte montre la photo de profil, le nom et le nombre de sorties faites (commencées). Le matelot connecté vient en premier, marqué « Vous », puis les autres du plus grand au plus petit nombre de sorties. Peu de membres ont une photo de profil : le composant `x-user-avatar` affiche alors les initiales des deux premiers mots du nom, sur une teinte de bleu tirée du nom. L'utiliser partout où un matelot apparaît, administration comprise (il n'y a plus d'autre image par défaut).

La page de profil met d'abord en avant les sorties du matelot : en tête, la photo de sa dernière sortie (ou la mer au trait), sa photo de profil, son nom, sa ville et trois chiffres (sorties, jours à bord, photos), puis sa prochaine sortie (`x-trip-card`) et son carnet de bord, les sorties faites rangées par année, de la plus récente à la plus ancienne. Viennent ensuite ses six dernières photos, avec un lien vers la galerie filtrée sur lui (`photos.index` avec `auteurs[]`), puis les commentaires. Sur son propre profil, un petit lien mène aux réglages du compte.

## Galerie photo

- La galerie (`photos.index`, `App\Livewire\Gallery`) est la seule page des photos : les anciennes pages des albums mènent à elle, filtrée sur l'album. En haut, le bandeau des albums qui ont des photos, du plus récent au plus ancien, en petites cartes (la couverture à côté du titre, de la date et du nombre de photos), défile à l'horizontale et filtre la galerie sur l'album touché ; un trait marqué d'une vague le sépare des photos. Viennent ensuite le regroupement (par jour, par défaut, par album ou aucun), l'ordre, les filtres « Envoyées par » et « Albums », puis la mosaïque, aux mêmes marges que le reste de la page. Un album choisi seul est présenté au-dessus de ses photos (description, sorties, modification).
- Un album contient de 10 à plus de 1 000 photos. Ne jamais les charger toutes d'un coup : la galerie les charge par pages de 100, chacune ajoutée à la fin de l'îlot Livewire « photos » sans renvoyer les précédentes.
- La mosaïque est une seule grille CSS (`.photo-masonry` dans `resources/css/app.css`) : chaque vignette s'étend sur un nombre de lignes calculé côté serveur à partir de `thumb_width` et `thumb_height`. La page ne saute pas pendant le chargement, et l'ordre de lecture reste celui de la visionneuse. Afficher le nombre de photos.
- La visionneuse est faite avec PhotoSwipe (`resources/js/gallery.js`, sa propre entrée Vite). On glisse la photo au doigt ou à la souris et la voisine arrive pendant le geste ; les flèches du clavier et les bords gauche et droit de l'écran changent aussi de photo ; le haut et le bas de l'écran cachent les commandes ; un clic zoome là où l'on clique (au doigt : pincer ou toucher deux fois). En haut à droite, les commentaires (une icône, marquée d'un point quand la photo en a), « Plus d'actions » (détails, télécharger, copier le lien, modifier quand c'est permis) et « Fermer » (une icône) ; en bas à droite, le matelot qui a envoyé la photo. Jamais le titre de la photo, qui est le nom du fichier. Le bouton retour du téléphone la ferme.
- « Détails » et les commentaires s'ouvrent dans un panneau sombre à droite de la visionneuse (`App\Livewire\PhotoDrawer`), qui suit la photo montrée ; sur grand écran, la photo se range à sa gauche, sur téléphone il couvre l'écran. Les détails : le matelot et la date d'envoi, la légende (modifiable par son matelot ou un administrateur), la prise de vue, le lieu sur une petite carte, l'album et la taille. Les commentaires : la liste, l'abonnement par e-mail et le champ pour écrire, toujours en bas. Le bouton retour du téléphone ferme d'abord le panneau.
- Une photo n'a pas de page à elle : son adresse (`photos.show`) ouvre la galerie sur son album, visionneuse ouverte sur elle (`?photo=`).
- La page d'ajout de photos (`photos.create`) : on choisit ses photos, ou on les fait glisser n'importe où sur la page, qu'un voile marqué d'un voilier (`x-sailboat`) recouvre pendant le geste. Avant le choix, la flotte du club au trait (`x-boat-drawing`) orne la carte de départ. Les photos sont réduites dans le navigateur, montrées en petites vignettes carrées pour les revoir d'un coup d'œil, chacune avec un bouton « Retirer », puis envoyées par lots de 20 ; pendant l'envoi, le voilier avance sur la barre de progression jusqu'à la bouée.
- La sélection de plusieurs photos, pour les supprimer, reviendra plus tard : garder des vignettes qui peuvent recevoir une case à cocher (`Gallery::deleteSelected` existe toujours).

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
- Toute action porte un libellé texte : une icône seule ne suffit pas. Exceptions voulues, toutes avec un `aria-label` et un `title` : le bouton d'agenda des petites cartes de sortie à venir, les flèches de la visionneuse et du bandeau des albums de la galerie, et les boutons des commentaires et de fermeture de la visionneuse et de ses panneaux. Les liens dans un texte sont soulignés.
- Rien ne dépend du survol (inexistant sur téléphone) ni d'un geste caché (balayage, appui long) : chaque action a un bouton visible. Seule exception, voulue : sur téléphone, la visionneuse des photos change de photo en glissant ou en touchant les bords de l'écran, sans flèches. Pas de défilement automatique, carrousels compris.
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
