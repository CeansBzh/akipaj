/*
 * Réglages et gestes communs aux visionneuses PhotoSwipe : celle de la galerie (resources/js/gallery.js) et celle des
 * liens de partage (resources/js/shared-photos.js).
 *
 * Toucher ou cliquer :
 * - le bord gauche ou droit de l'écran (un huitième de la largeur) montre la photo précédente ou suivante ;
 * - le haut ou le bas de l'écran (un cinquième de la hauteur), ou le fond autour de la photo, cache ou montre les commandes ;
 * - la photo la zoome là où l'on clique à la souris ; au doigt, on zoome en pinçant ou en touchant deux fois.
 * Glisser la photo (au doigt ou à la souris) fait venir la suivante ou la précédente, les flèches du clavier aussi.
 */

export const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

// Chevron au trait fin, dans la boîte de 60 × 60 des flèches de PhotoSwipe (retourné par sa feuille de style pour « suivante »)
const ARROW_SVG =
    '<svg aria-hidden="true" class="pswp__icn" viewBox="0 0 60 60" width="60" height="60">' +
    '<path d="M34 21 25 30l9 9" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>' +
    '</svg>';

/**
 * Options de PhotoSwipe communes aux visionneuses, complétées ou remplacées par celles de chacune.
 *
 * @param {import('photoswipe').PhotoSwipeOptions} options
 * @returns {import('photoswipe').PhotoSwipeOptions}
 */
export function lightboxOptions(options) {
    return {
        loop: false,
        bgOpacity: 1,
        showHideAnimationType: reducedMotion.matches ? 'none' : 'zoom',
        imageClickAction: 'zoom',
        bgClickAction: 'toggle-controls',
        tapAction: 'toggle-controls',
        doubleTapAction: 'zoom',
        // Zoom vers la taille réelle de la photo, et au moins ×2,5 pour une photo déjà affichée en entier
        secondaryZoomLevel: (zoomLevel) => Math.max(zoomLevel.fit * 2.5, Math.min(1, zoomLevel.fit * 3)),
        preload: [1, 2],
        zoom: false,
        close: false,
        counter: false,
        arrowPrevSVG: ARROW_SVG,
        arrowNextSVG: ARROW_SVG,
        arrowPrevTitle: 'Photo précédente',
        arrowNextTitle: 'Photo suivante',
        errorMsg: 'Cette photo n’a pas pu être chargée.',
        mainClass: 'gallery-lightbox',
        ...options,
    };
}

/**
 * Bord gauche ou droit : photo précédente ou suivante. Haut ou bas de l'écran : commandes cachées ou montrées.
 * Une photo zoomée garde le comportement de PhotoSwipe, pour la déplacer et la dézoomer sans changer de photo.
 *
 * @param {import('photoswipe').default} pswp
 * @param {() => number} rightOffset largeur cachée à droite de la visionneuse (le panneau de la galerie ouvert)
 */
export function handleScreenZones(pswp, rightOffset = () => 0) {
    const isZoomedIn = () => {
        const slide = pswp.currSlide;
        return slide && slide.currZoomLevel > slide.zoomLevels.initial + 0.01;
    };

    for (const action of ['imageClickAction', 'bgClickAction', 'tapAction']) {
        pswp.on(action, (event) => {
            if (isZoomedIn()) {
                return;
            }

            const { clientX, clientY } = event.originalEvent;
            const width = window.innerWidth - rightOffset();
            const height = window.innerHeight;
            const edge = Math.max(44, width / 8);

            if (clientY < height / 5 || clientY > height - height / 5) {
                event.preventDefault();
                pswp.element.classList.toggle('pswp--ui-visible');
            } else if (clientX < edge) {
                event.preventDefault();
                pswp.prev();
            } else if (clientX > width - edge) {
                event.preventDefault();
                pswp.next();
            }
        });
    }
}

/**
 * Pendant que la visionneuse est ouverte, la page ne défile pas derrière elle.
 *
 * @param {import('photoswipe').default} pswp
 */
export function lockPageScroll(pswp) {
    pswp.on('openingAnimationEnd', () => {
        document.documentElement.classList.add('gallery-lightbox-open');
        // Sans barre de défilement, l'écran s'élargit sans événement « resize »
        pswp.updateSize(true);
    });
    pswp.on('close', () => document.documentElement.classList.remove('gallery-lightbox-open'));
    pswp.on('destroy', () => document.documentElement.classList.remove('gallery-lightbox-open'));
}
