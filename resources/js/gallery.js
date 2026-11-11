import PhotoSwipe from 'photoswipe';
import 'photoswipe/style.css';
import '../css/gallery-lightbox.css';
import { handleScreenZones, lightboxOptions, lockPageScroll } from './photo-lightbox';

/*
 * Visionneuse de la galerie (composant Livewire Gallery), construite avec PhotoSwipe.
 *
 * Chaque vignette de la mosaïque est un lien vers la photo qui porte les données de PhotoSwipe (data-pswp-*).
 * La visionneuse s'ouvre sur la vignette touchée et parcourt les vignettes dans l'ordre de la page. Près de la dernière
 * photo chargée, elle demande la page suivante à la galerie et reprend les nouvelles vignettes dès qu'elles arrivent.
 * Le lien d'une photo (/photos/12) mène à la galerie avec ?photo=12 : la visionneuse s'ouvre alors sur elle.
 *
 * Les panneaux « Détails », « Commentaires » et « Partager » (composant Livewire PhotoDrawer) s'ouvrent à droite et
 * suivent la photo. Les gestes et les zones de l'écran sont ceux de resources/js/photo-lightbox.js.
 */

// À partir de cette largeur, la photo se range à gauche du panneau ouvert ; en dessous, le panneau couvre l'écran
const wideScreen = window.matchMedia('(min-width: 48rem)');

// Nombre de photos restantes à partir duquel on charge la page suivante
const PRELOAD_THRESHOLD = 5;

document.addEventListener('click', (event) => {
    const tile = event.target.closest?.('[data-gallery-photo]');
    // Clic du milieu, Ctrl ou Cmd : le lien de la photo s'ouvre comme d'habitude, dans un nouvel onglet
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

openLinkedPhoto();

/**
 * Ouvre la visionneuse sur la photo d'un lien (?photo=12), que la galerie a chargée dans la mosaïque.
 */
function openLinkedPhoto() {
    const url = new URL(window.location.href);
    const photoId = url.searchParams.get('photo');
    if (!photoId) {
        return;
    }

    // Recharger la page ou y revenir ne rouvre pas la visionneuse
    url.searchParams.delete('photo');
    history.replaceState(history.state, '', url);

    const tile = document.querySelector(`[data-gallery-photo][data-photo-id="${CSS.escape(photoId)}"]`);
    const grid = tile?.closest('[data-gallery-grid]');
    if (grid) {
        // La photo s'ouvre depuis sa vignette, et s'y referme
        tile.scrollIntoView({ block: 'center' });
        openLightbox(grid, tile);
    }
}

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
    let drawer;

    const pswp = new PhotoSwipe(
        lightboxOptions({
            dataSource: tiles,
            index: Math.max(0, tiles.indexOf(openedTile)),
            // Le panneau ouvert sur grand écran laisse la photo à sa gauche
            paddingFn: () => ({ top: 0, bottom: 0, left: 0, right: drawer?.offset() ?? 0 }),
            // Le focus reste dans la visionneuse, ou dans son panneau qui est hors d'elle (voir keepFocus)
            trapFocus: false,
            // Le focus revient sur la vignette de la dernière photo vue, pas sur celle de départ (voir plus bas)
            returnFocus: false,
        }),
    );

    const currentTile = () => pswp.currSlide?.data.element ?? openedTile;
    drawer = createDrawer(pswp, currentTile);

    pswp.on('uiRegister', () => {
        pswp.ui.registerElement({
            name: 'gallery-actions',
            appendTo: 'root',
            onInit: (element) => setUpActions(pswp, element, currentTile, drawer),
        });
        pswp.ui.registerElement({
            name: 'gallery-uploader',
            appendTo: 'root',
            onInit: (element) => setUpUploader(pswp, element, currentTile),
        });
    });

    keepFocus(pswp, drawer);
    handleScreenZones(pswp, drawer.offset);
    handleHistory(pswp, drawer);
    loadMorePhotos(pswp, grid);
    lockPageScroll(pswp);

    // La photo se referme vers sa vignette : on l'amène à l'écran avant l'animation
    pswp.on('close', () => currentTile().scrollIntoView({ block: 'nearest' }));
    pswp.on('destroy', () => currentTile().focus({ preventScroll: true }));

    pswp.init();
}

/**
 * Le bouton « retour » du téléphone ferme le panneau ouvert, puis la visionneuse, au lieu de quitter la galerie.
 *
 * @param {PhotoSwipe} pswp
 * @param {ReturnType<typeof createDrawer>} drawer
 */
function handleHistory(pswp, drawer) {
    history.pushState({ ...history.state, galleryLightbox: true }, '');

    const closeOnBack = () => {
        if (!history.state?.galleryDrawer) {
            drawer.close();
        }
        if (!history.state?.galleryLightbox) {
            pswp.close();
        }
    };
    window.addEventListener('popstate', closeOnBack);

    pswp.on('close', () => {
        window.removeEventListener('popstate', closeOnBack);
        // Les entrées ajoutées par la visionneuse et son panneau
        const ownEntries = history.state?.galleryDrawer ? 2 : history.state?.galleryLightbox ? 1 : 0;
        drawer.close();
        if (ownEntries > 0) {
            history.go(-ownEntries);
        }
    });
}

/**
 * Le focus reste dans la visionneuse et dans son panneau, qui est hors de l'élément de PhotoSwipe :
 * ce piège remplace celui de PhotoSwipe, qui renverrait le focus du panneau vers la visionneuse.
 *
 * @param {PhotoSwipe} pswp
 * @param {ReturnType<typeof createDrawer>} drawer
 */
function keepFocus(pswp, drawer) {
    const onFocusIn = (event) => {
        if (pswp.element.contains(event.target) || drawer.element?.contains(event.target)) {
            return;
        }
        (drawer.isOpen() ? drawer.element : pswp.element).focus({ preventScroll: true });
    };

    pswp.on('bindEvents', () => {
        pswp.element.focus({ preventScroll: true });
        document.addEventListener('focusin', onFocusIn);
    });
    pswp.on('destroy', () => document.removeEventListener('focusin', onFocusIn));
}

/**
 * Panneaux « Détails », « Commentaires » et « Partager » (resources/views/livewire/photo-drawer.blade.php), à droite de la visionneuse.
 * Ils montrent la photo de la visionneuse et la suivent quand elle change. Sur grand écran, la photo et les commandes
 * se rangent à leur gauche ; sur téléphone, ils couvrent l'écran et la visionneuse dessous devient inerte.
 * Ouvrir un panneau ajoute une entrée à l'historique, pour que le bouton « retour » le referme.
 *
 * @param {PhotoSwipe} pswp
 * @param {() => HTMLAnchorElement} currentTile
 */
function createDrawer(pswp, currentTile) {
    const element = document.querySelector('[data-photo-drawer]');
    /** @type {'details' | 'comments' | 'share' | null} */
    let panel = null;
    /** @type {HTMLElement | null} */
    let opener = null;

    // Largeur prise à droite par le panneau, appliquée à la visionneuse
    let appliedOffset = 0;

    const send = (name, detail) => window.dispatchEvent(new CustomEvent(name, { detail }));

    const drawer = {
        element,
        panel: () => panel,
        isOpen: () => panel !== null,
        /** Largeur cachée par le panneau à droite de la visionneuse */
        offset: () => appliedOffset,

        /**
         * @param {'details' | 'comments' | 'share'} newPanel
         * @param {HTMLElement} newOpener bouton qui retrouve le focus à la fermeture
         */
        open(newPanel, newOpener) {
            if (!element) {
                return;
            }
            if (!panel) {
                history.pushState({ ...history.state, galleryDrawer: true }, '');
            }
            panel = newPanel;
            opener = newOpener;
            send('photo-drawer-open', { panel, photoId: currentTile().dataset.photoId });
            layout();
            pswp.dispatch('galleryDrawerToggle');
        },

        /** Ferme le panneau sans toucher à l'historique : retour arrière, ou fermeture de la visionneuse */
        close() {
            if (!panel) {
                return;
            }
            panel = null;
            send('photo-drawer-close');
            layout();
            pswp.dispatch('galleryDrawerToggle');
            if (!pswp.isDestroying) {
                opener?.focus({ preventScroll: true });
            }
        },

        /** Fermeture demandée par le matelot : revenir sur l'entrée du panneau dans l'historique le ferme */
        dismiss() {
            if (history.state?.galleryDrawer) {
                history.back();
            } else {
                drawer.close();
            }
        },
    };

    // La photo se range à gauche du panneau ouvert sur grand écran ; sur téléphone, la visionneuse dessous est inerte
    function layout() {
        if (pswp.isDestroying || !pswp.element) {
            return;
        }
        pswp.element.inert = drawer.isOpen() && !wideScreen.matches;

        const offset = drawer.isOpen() && wideScreen.matches ? element.offsetWidth : 0;
        if (offset !== appliedOffset) {
            appliedOffset = offset;
            pswp.element.style.setProperty('--gallery-drawer-offset', `${offset}px`);
            pswp.updateSize(true);
        }
    }

    pswp.on('change', () => {
        if (panel) {
            send('photo-drawer-photo', { photoId: currentTile().dataset.photoId });
        }
    });

    // Alpine affiche le panneau un peu après son ouverture : sa largeur se lit quand il paraît, et quand elle change
    const resizeObserver = new ResizeObserver(layout);
    if (element) {
        resizeObserver.observe(element);
    }
    window.addEventListener('photo-drawer-dismiss', drawer.dismiss);
    wideScreen.addEventListener('change', layout);
    pswp.on('destroy', () => {
        resizeObserver.disconnect();
        window.removeEventListener('photo-drawer-dismiss', drawer.dismiss);
        wideScreen.removeEventListener('change', layout);
    });

    return drawer;
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
 * Boutons des commentaires, « Plus d'actions » (détails, téléchargement, partage, modification) et « Fermer », en haut à droite.
 *
 * @param {PhotoSwipe} pswp
 * @param {HTMLElement} element
 * @param {() => HTMLAnchorElement} currentTile
 * @param {ReturnType<typeof createDrawer>} drawer
 */
function setUpActions(pswp, element, currentTile, drawer) {
    element.append(cloneTemplate('[data-gallery-lightbox-actions]'));

    const comments = element.querySelector('[data-lightbox-comments]');
    const commentsDot = element.querySelector('[data-lightbox-comments-dot]');
    const menuButton = element.querySelector('[data-lightbox-menu-button]');
    const menu = element.querySelector('[data-lightbox-menu]');
    const details = element.querySelector('[data-lightbox-details]');
    const download = element.querySelector('[data-lightbox-download]');
    const share = element.querySelector('[data-lightbox-share]');
    const edit = element.querySelector('[data-lightbox-edit]');

    const toggleMenu = (isOpen) => {
        menu.hidden = !isOpen;
        menuButton.setAttribute('aria-expanded', String(isOpen));
    };

    // Le point dit s'il y a des commentaires ; le libellé dit combien
    const updateComments = () => {
        const count = Number(currentTile().dataset.commentCount ?? 0);
        commentsDot.hidden = count === 0;
        comments.setAttribute(
            'aria-label',
            count > 0 ? `Commentaires (${count})` : 'Commentaires, aucun pour l’instant',
        );
    };

    menuButton.addEventListener('click', () => toggleMenu(menu.hidden));
    element.querySelector('[data-lightbox-close]').addEventListener('click', () => pswp.close());

    comments.addEventListener('click', () => {
        toggleMenu(false);
        if (drawer.panel() === 'comments') {
            drawer.dismiss();
        } else {
            drawer.open('comments', comments);
        }
    });
    details.addEventListener('click', () => {
        toggleMenu(false);
        drawer.open('details', menuButton);
    });
    pswp.on('galleryDrawerToggle', () => {
        comments.setAttribute('aria-expanded', String(drawer.panel() === 'comments'));
    });

    // Un commentaire publié ou supprimé dans le panneau met à jour le point de sa photo
    const stopListening = window.Livewire?.on('photo-comments-count', ({ photoId, count }) => {
        const tile = document.querySelector(`[data-gallery-photo][data-photo-id="${photoId}"]`);
        if (tile) {
            tile.dataset.commentCount = count;
        }
        updateComments();
    });
    pswp.on('destroy', () => stopListening?.());

    share.addEventListener('click', () => {
        toggleMenu(false);
        drawer.open('share', menuButton);
    });

    // Les touches tapées dans le panneau lui reviennent. Échap ferme d'abord le menu, puis le panneau, puis la visionneuse.
    pswp.on('keydown', (event) => {
        const { key, target } = event.originalEvent;
        const isInDrawer = Boolean(drawer.element?.contains(target));
        if (isInDrawer) {
            event.preventDefault();
        }
        if (key !== 'Escape') {
            return;
        }

        if (!menu.hidden && !isInDrawer) {
            event.preventDefault();
            toggleMenu(false);
            menuButton.focus();
        } else if (drawer.isOpen()) {
            event.preventDefault();
            drawer.dismiss();
        }
    });

    // Un clic ailleurs referme le menu, dans le panneau aussi
    const closeMenu = () => toggleMenu(false);
    pswp.on('pointerDown', closeMenu);
    drawer.element?.addEventListener('pointerdown', closeMenu);
    pswp.on('destroy', () => drawer.element?.removeEventListener('pointerdown', closeMenu));

    pswp.on('change', () => {
        const tile = currentTile();
        toggleMenu(false);
        updateComments();
        download.href = tile.dataset.pswpSrc;
        download.download = tile.dataset.downloadName;
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
