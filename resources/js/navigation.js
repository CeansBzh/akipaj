/*
 * Barre de navigation principale (layouts/navigation.blade.php), collée en haut de l'écran.
 * Elle se range quand on descend dans la page et revient dès qu'on remonte. Elle reste à l'écran tant qu'un de ses
 * menus est ouvert ou qu'elle a le focus clavier. Elle se range aussi quand elle cacherait l'endroit où l'on va :
 * l'élément qui reçoit le focus clavier, ou la cible d'un lien vers un endroit de la page.
 *
 * Sur la page, deux variables CSS décrivent la barre : --nav-height, sa hauteur, et --nav-offset, la place qu'elle
 * occupe en haut de l'écran (sa hauteur quand elle est là, 0 quand elle est rangée). Les éléments collés en haut
 * de l'écran (utilitaire top-below-nav-* de resources/css/app.css) se placent ainsi juste sous elle.
 */

// Défilement, en pixels, à partir duquel la barre se range ou revient : un tremblement du doigt ne la fait pas bouger
const SCROLL_THRESHOLD = 10;

// Temps sans défilement, en millisecondes, qui marque la fin du trajet vers la cible d'un lien
const JUMP_END_DELAY = 150;

export default () => ({
    // Menu des liens ouvert, sur téléphone
    open: false,
    isHidden: false,
    isScrolled: false,

    init() {
        const root = document.documentElement;
        // Sans le rebond au-delà du haut et du bas de la page (Safari), qui ferait croire à un changement de sens
        const scrollPosition = () => Math.min(Math.max(scrollY, 0), root.scrollHeight - innerHeight);
        let lastPosition = scrollPosition();
        let isJumping = false;
        let jumpTimer = null;

        new ResizeObserver(() => root.style.setProperty('--nav-height', `${this.$el.offsetHeight}px`)).observe(
            this.$el,
        );
        const publishOffset = () => root.style.setProperty('--nav-offset', this.isHidden ? '0px' : 'var(--nav-height)');
        publishOffset();
        this.$watch('isHidden', publishOffset);

        // Un menu ouvert ou le focus clavier dans la barre la gardent à l'écran
        const isInUse = () => this.$el.querySelector('[aria-expanded="true"], :focus-visible') !== null;

        // Pendant le trajet vers la cible d'un lien, le défilement ne fait pas revenir la barre
        const followJump = () => {
            isJumping = true;
            clearTimeout(jumpTimer);
            jumpTimer = setTimeout(() => {
                isJumping = false;
                lastPosition = scrollPosition();
            }, JUMP_END_DELAY);
        };

        addEventListener(
            'scroll',
            () => {
                const position = scrollPosition();
                this.isScrolled = position > 0;
                if (isJumping) {
                    followJump();

                    return;
                }
                if (position <= 0) {
                    this.isHidden = false;
                } else if (Math.abs(position - lastPosition) < SCROLL_THRESHOLD) {
                    return;
                } else {
                    this.isHidden = position > lastPosition && !isInUse();
                }
                lastPosition = position;
            },
            { passive: true },
        );

        // Un lien vers un endroit de la page : la barre se range pour ne pas cacher l'endroit atteint
        document.addEventListener('click', (event) => {
            const link = event.target.closest('a[href*="#"]');
            if (
                !link ||
                event.defaultPrevented ||
                event.button !== 0 ||
                event.ctrlKey ||
                event.metaKey ||
                event.shiftKey ||
                link.hash === '' ||
                link.origin !== location.origin ||
                link.pathname !== location.pathname
            ) {
                return;
            }
            this.isHidden = true;
            followJump();
        });

        // Le focus clavier arrive sur un élément que la barre recouvre (en remontant avec Maj+Tab) : elle se range
        document.addEventListener('focusin', ({ target }) => {
            if (this.$el.contains(target)) {
                this.isHidden = false;

                return;
            }
            if (this.isHidden || !target.matches(':focus-visible')) {
                return;
            }
            // Après le défilement qui amène l'élément à l'écran
            requestAnimationFrame(() => {
                if (target.getBoundingClientRect().top < this.$el.getBoundingClientRect().bottom) {
                    this.isHidden = true;
                }
            });
        });
    },
});
