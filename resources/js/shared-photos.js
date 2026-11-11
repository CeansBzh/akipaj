import PhotoSwipe from 'photoswipe';
import 'photoswipe/style.css';
import '../css/gallery-lightbox.css';
import { handleScreenZones, lightboxOptions, lockPageScroll } from './photo-lightbox';

/*
 * Visionneuse des liens de partage, ouverts sans compte (resources/views/share).
 *
 * - Photo partagée seule : la visionneuse s'ouvre aussitôt sur elle et ne se ferme pas, ni par un bouton, ni par Échap,
 *   ni en glissant la photo. La page ne contient que cette photo : il n'y a rien d'autre à voir.
 * - Album partagé : chaque vignette ouvre la visionneuse, qui parcourt les photos de l'album et se referme sur la
 *   mosaïque. Les photos se chargent par pages, à la fin de la mosaïque, comme dans la galerie.
 *
 * Les gestes et les zones de l'écran sont ceux de la galerie (resources/js/photo-lightbox.js).
 */

// Nombre de photos restantes à partir duquel on charge la page suivante
const PRELOAD_THRESHOLD = 5;

const grid = document.querySelector('[data-shared-grid]');
const loadMore = grid ? setUpLoading(grid) : null;

if (grid) {
    grid.addEventListener('click', (event) => {
        const tile = event.target.closest('[data-shared-photo]');
        // Clic du milieu, Ctrl ou Cmd : la photo s'ouvre comme d'habitude, dans un nouvel onglet
        if (!tile || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }
        event.preventDefault();
        openAlbumLightbox(tile);
    });
} else {
    const photo = document.querySelector('[data-shared-photo]');
    if (photo) {
        openSinglePhoto(photo);
    }
}

/**
 * @returns {HTMLElement[]}
 */
function albumTiles() {
    return Array.from(grid.querySelectorAll('[data-shared-photo]'));
}

/**
 * La photo seule, dans une visionneuse qu'on ne peut pas quitter.
 *
 * @param {HTMLElement} photo
 */
function openSinglePhoto(photo) {
    const pswp = new PhotoSwipe(
        lightboxOptions({
            dataSource: [photo],
            showHideAnimationType: 'none',
            arrowPrev: false,
            arrowNext: false,
            escKey: false,
            closeOnVerticalDrag: false,
            pinchToClose: false,
            clickToCloseNonZoomable: false,
        }),
    );

    setUpControls(pswp, () => photo);
    handleScreenZones(pswp);
    lockPageScroll(pswp);
    pswp.init();
}

/**
 * Les photos de l'album, depuis la vignette touchée ; la visionneuse se referme sur la mosaïque.
 *
 * @param {HTMLElement} openedTile
 */
function openAlbumLightbox(openedTile) {
    const tiles = albumTiles();
    const pswp = new PhotoSwipe(
        lightboxOptions({
            dataSource: tiles,
            index: Math.max(0, tiles.indexOf(openedTile)),
            // Le focus revient sur la vignette de la dernière photo vue (voir plus bas)
            returnFocus: false,
        }),
    );
    const currentTile = () => pswp.currSlide?.data.element ?? openedTile;

    setUpControls(pswp, currentTile);
    handleScreenZones(pswp);
    lockPageScroll(pswp);
    handleHistory(pswp);
    followLoadedPhotos(pswp);

    // La photo se referme vers sa vignette : on l'amène à l'écran avant l'animation
    pswp.on('close', () => currentTile().scrollIntoView({ block: 'nearest' }));
    pswp.on('destroy', () => currentTile().focus({ preventScroll: true }));

    pswp.init();
}

/**
 * « Télécharger » et, pour un album, « Fermer » en haut à droite ; le nom du club en haut à gauche.
 *
 * @param {PhotoSwipe} pswp
 * @param {() => HTMLElement} currentPhoto
 */
function setUpControls(pswp, currentPhoto) {
    pswp.on('uiRegister', () => {
        pswp.ui.registerElement({
            name: 'gallery-actions',
            appendTo: 'root',
            onInit: (element) => {
                element.append(cloneTemplate('[data-shared-lightbox-actions]'));
                const download = element.querySelector('[data-lightbox-download]');
                element.querySelector('[data-lightbox-close]')?.addEventListener('click', () => pswp.close());

                pswp.on('change', () => {
                    download.href = currentPhoto().dataset.pswpSrc;
                    download.download = currentPhoto().dataset.downloadName;
                });
            },
        });
        pswp.ui.registerElement({
            name: 'gallery-brand',
            appendTo: 'root',
            onInit: (element) => element.append(cloneTemplate('[data-shared-lightbox-brand]')),
        });
    });
}

/**
 * @param {string} selector
 * @returns {DocumentFragment}
 */
function cloneTemplate(selector) {
    return document.querySelector(selector).content.cloneNode(true);
}

/**
 * Le bouton « retour » du téléphone referme la visionneuse et revient à la mosaïque, au lieu de quitter la page.
 *
 * @param {PhotoSwipe} pswp
 */
function handleHistory(pswp) {
    history.pushState({ ...history.state, sharedLightbox: true }, '');

    const closeOnBack = () => {
        if (!history.state?.sharedLightbox) {
            pswp.close();
        }
    };
    window.addEventListener('popstate', closeOnBack);

    pswp.on('close', () => {
        window.removeEventListener('popstate', closeOnBack);
        if (history.state?.sharedLightbox) {
            history.back();
        }
    });
}

/**
 * Près de la dernière photo chargée, charge la page suivante, puis ajoute à la visionneuse les vignettes arrivées.
 *
 * @param {PhotoSwipe} pswp
 */
function followLoadedPhotos(pswp) {
    pswp.on('change', () => {
        if (pswp.currIndex >= pswp.getNumItems() - PRELOAD_THRESHOLD) {
            loadMore?.();
        }
    });

    const observer = new MutationObserver(() => {
        const tiles = albumTiles();
        const previousCount = pswp.options.dataSource.length;
        if (tiles.length <= previousCount) {
            return;
        }

        pswp.options.dataSource = tiles;
        // La photo suivante n'avait pas encore de place si l'on regardait la dernière
        if (pswp.currIndex + 1 >= previousCount) {
            pswp.refreshSlideContent(pswp.currIndex + 1);
        }
    });
    observer.observe(grid, { childList: true });
    pswp.on('destroy', () => observer.disconnect());
}

/**
 * Pages suivantes de l'album, ajoutées à la fin de la mosaïque : par le bouton « Afficher plus de photos », en
 * approchant du bas de la page, ou depuis la visionneuse. L'adresse de la page d'après arrive dans l'en-tête X-Next-Page.
 *
 * @param {HTMLElement} grid
 * @returns {() => Promise<void>}
 */
function setUpLoading(grid) {
    const button = document.querySelector('[data-shared-more]');
    if (!button) {
        return async () => {};
    }

    let nextUrl = button.dataset.sharedMore;
    let loading = false;

    const load = async () => {
        if (loading || !nextUrl) {
            return;
        }
        loading = true;
        button.disabled = true;
        button.textContent = 'Chargement des photos…';
        try {
            const response = await fetch(nextUrl, { headers: { Accept: 'text/html' } });
            if (!response.ok) {
                throw new Error(String(response.status));
            }
            grid.insertAdjacentHTML('beforeend', await response.text());
            nextUrl = response.headers.get('X-Next-Page') || null;
        } catch {
            button.textContent = 'Réessayer de charger les photos';
            button.disabled = false;
            loading = false;
            return;
        }
        loading = false;
        button.disabled = false;
        button.textContent = 'Afficher plus de photos';
        button.parentElement.hidden = !nextUrl;
    };

    button.addEventListener('click', load);
    new IntersectionObserver((entries) => entries.some((entry) => entry.isIntersecting) && load(), {
        rootMargin: '1200px 0px',
    }).observe(button);

    return load;
}
