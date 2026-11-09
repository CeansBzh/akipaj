import PhotoSwipe from 'photoswipe';
import 'photoswipe/style.css';
import '../css/gallery-lightbox.css';

/*
 * Visionneuse de la galerie (composant Livewire Gallery), construite avec PhotoSwipe.
 *
 * Chaque vignette de la mosaïque est un lien vers la page de la photo qui porte les données de PhotoSwipe (data-pswp-*).
 * La visionneuse s'ouvre sur la vignette touchée et parcourt les vignettes dans l'ordre de la page. Près de la dernière
 * photo chargée, elle demande la page suivante à la galerie et reprend les nouvelles vignettes dès qu'elles arrivent.
 *
 * Toucher ou cliquer :
 * - le bord gauche ou droit de l'écran (un huitième de la largeur) montre la photo précédente ou suivante ;
 * - le haut ou le bas de l'écran (un cinquième de la hauteur), ou le fond autour de la photo, cache ou montre les commandes ;
 * - la photo la zoome là où l'on clique à la souris ; au doigt, on zoome en pinçant ou en touchant deux fois.
 * Glisser la photo (au doigt ou à la souris) fait venir la suivante ou la précédente, les flèches du clavier aussi.
 */

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

// Chevron au trait fin, dans la boîte de 60 × 60 des flèches de PhotoSwipe (retourné par sa feuille de style pour « suivante »)
const ARROW_SVG =
    '<svg aria-hidden="true" class="pswp__icn" viewBox="0 0 60 60" width="60" height="60">' +
    '<path d="M34 21 25 30l9 9" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>' +
    '</svg>';

// Nombre de photos restantes à partir duquel on charge la page suivante
const PRELOAD_THRESHOLD = 5;

document.addEventListener('click', (event) => {
    const tile = event.target.closest?.('[data-gallery-photo]');
    // Clic du milieu, Ctrl ou Cmd : le lien ouvre la page de la photo comme d'habitude
    if (!tile || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
    }

    const grid = tile.closest('[data-gallery-grid]');
    if (!grid) {
        return;
    }

    event.preventDefault();
    openLightbox(grid, tile);
});

/**
 * @param {HTMLElement} grid
 * @returns {HTMLAnchorElement[]}
 */
function galleryTiles(grid) {
    return Array.from(grid.querySelectorAll('[data-gallery-photo]'));
}

/**
 * @param {string} selector
 * @returns {DocumentFragment}
 */
function cloneTemplate(selector) {
    return document.querySelector(selector).content.cloneNode(true);
}

/**
 * @param {HTMLElement} grid
 * @param {HTMLAnchorElement} openedTile
 */
function openLightbox(grid, openedTile) {
    const tiles = galleryTiles(grid);

    const pswp = new PhotoSwipe({
        dataSource: tiles,
        index: Math.max(0, tiles.indexOf(openedTile)),
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
        // Le focus revient sur la vignette de la dernière photo vue, pas sur celle de départ (voir plus bas)
        returnFocus: false,
        zoom: false,
        close: false,
        counter: false,
        arrowPrevSVG: ARROW_SVG,
        arrowNextSVG: ARROW_SVG,
        arrowPrevTitle: 'Photo précédente',
        arrowNextTitle: 'Photo suivante',
        errorMsg: 'Cette photo n’a pas pu être chargée.',
        mainClass: 'gallery-lightbox',
    });

    const currentTile = () => pswp.currSlide?.data.element ?? openedTile;

    pswp.on('uiRegister', () => {
        pswp.ui.registerElement({
            name: 'gallery-actions',
            appendTo: 'root',
            onInit: (element) => setUpActions(pswp, element, currentTile),
        });
        pswp.ui.registerElement({
            name: 'gallery-uploader',
            appendTo: 'root',
            onInit: (element) => setUpUploader(pswp, element, currentTile),
        });
    });

    handleScreenZones(pswp);
    handleHistory(pswp);
    loadMorePhotos(pswp, grid);

    pswp.on('openingAnimationEnd', () => {
        document.documentElement.classList.add('gallery-lightbox-open');
        // Sans barre de défilement, l'écran s'élargit sans événement « resize »
        pswp.updateSize(true);
    });
    pswp.on('close', () => {
        document.documentElement.classList.remove('gallery-lightbox-open');
        // La photo se referme vers sa vignette : on l'amène à l'écran avant l'animation
        currentTile().scrollIntoView({ block: 'nearest' });
    });
    pswp.on('destroy', () => {
        document.documentElement.classList.remove('gallery-lightbox-open');
        currentTile().focus({ preventScroll: true });
    });

    pswp.init();
}

/**
 * Bord gauche ou droit : photo précédente ou suivante. Haut ou bas de l'écran : commandes cachées ou montrées.
 * Une photo zoomée garde le comportement de PhotoSwipe, pour la déplacer et la dézoomer sans changer de photo.
 *
 * @param {PhotoSwipe} pswp
 */
function handleScreenZones(pswp) {
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
            const { innerWidth: width, innerHeight: height } = window;
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
 * Le bouton « retour » du téléphone ferme la visionneuse au lieu de quitter la galerie.
 *
 * @param {PhotoSwipe} pswp
 */
function handleHistory(pswp) {
    history.pushState({ ...history.state, galleryLightbox: true }, '');

    const closeOnBack = () => pswp.close();
    window.addEventListener('popstate', closeOnBack);

    pswp.on('close', () => {
        window.removeEventListener('popstate', closeOnBack);
        if (history.state?.galleryLightbox) {
            history.back();
        }
    });
}

/**
 * Près de la dernière photo chargée, demande la page suivante à la galerie (événement écouté par son Alpine),
 * puis ajoute à la visionneuse les vignettes arrivées dans la mosaïque.
 *
 * @param {PhotoSwipe} pswp
 * @param {HTMLElement} grid
 */
function loadMorePhotos(pswp, grid) {
    pswp.on('change', () => {
        if (pswp.currIndex >= pswp.getNumItems() - PRELOAD_THRESHOLD) {
            window.dispatchEvent(new CustomEvent('gallery-load-more'));
        }
    });

    const observer = new MutationObserver(() => {
        const tiles = galleryTiles(grid);
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
 * Boutons « Plus d'actions » et « Fermer », en haut à droite.
 *
 * @param {PhotoSwipe} pswp
 * @param {HTMLElement} element
 * @param {() => HTMLAnchorElement} currentTile
 */
function setUpActions(pswp, element, currentTile) {
    element.append(cloneTemplate('[data-gallery-lightbox-actions]'));

    const menuButton = element.querySelector('[data-lightbox-menu-button]');
    const menu = element.querySelector('[data-lightbox-menu]');
    const download = element.querySelector('[data-lightbox-download]');
    const copy = element.querySelector('[data-lightbox-copy]');
    const copyLabel = element.querySelector('[data-lightbox-copy-label]');
    const page = element.querySelector('[data-lightbox-page]');
    const edit = element.querySelector('[data-lightbox-edit]');
    const status = element.querySelector('[data-lightbox-status]');
    let copyTimer;

    const toggleMenu = (isOpen) => {
        menu.hidden = !isOpen;
        menuButton.setAttribute('aria-expanded', String(isOpen));
    };

    menuButton.addEventListener('click', () => toggleMenu(menu.hidden));
    element.querySelector('[data-lightbox-close]').addEventListener('click', () => pswp.close());

    copy.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(currentTile().href);
            copyLabel.textContent = 'Lien copié';
        } catch {
            copyLabel.textContent = 'Le lien n’a pas pu être copié';
        }
        status.textContent = copyLabel.textContent;
        clearTimeout(copyTimer);
        copyTimer = setTimeout(() => {
            copyLabel.textContent = 'Copier le lien';
            status.textContent = '';
        }, 2500);
    });

    // Échap ferme d'abord le menu, puis la visionneuse ; un clic ailleurs le referme aussi
    pswp.on('keydown', (event) => {
        if (event.originalEvent.key === 'Escape' && !menu.hidden) {
            event.preventDefault();
            toggleMenu(false);
            menuButton.focus();
        }
    });
    pswp.on('pointerDown', () => toggleMenu(false));

    pswp.on('change', () => {
        const tile = currentTile();
        toggleMenu(false);
        download.href = tile.dataset.pswpSrc;
        download.download = tile.dataset.downloadName;
        page.href = tile.href;
        edit.hidden = !tile.dataset.editUrl;
        edit.href = tile.dataset.editUrl ?? '#';
    });
}

/**
 * Photo de profil et nom du matelot qui a envoyé la photo, en bas à droite.
 *
 * @param {PhotoSwipe} pswp
 * @param {HTMLElement} element
 * @param {() => HTMLAnchorElement} currentTile
 */
function setUpUploader(pswp, element, currentTile) {
    element.append(cloneTemplate('[data-gallery-lightbox-uploader]'));
    const uploader = element.querySelector('[data-lightbox-uploader]');

    pswp.on('change', () => {
        const template = document.querySelector(`[data-gallery-uploader="${currentTile().dataset.uploader}"]`);
        element.hidden = !template;
        uploader.replaceChildren(template ? template.content.cloneNode(true) : '');
    });
}
