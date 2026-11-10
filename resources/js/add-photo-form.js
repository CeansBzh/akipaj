/*
 * Formulaire d'ajout de photos à un album (photo/partials/add-photo-form.blade.php).
 *
 * Les photos se choisissent avec les boutons « Choisir des photos », ou se déposent n'importe où sur la page.
 * Chacune est réduite en JPEG dans le navigateur, avec ses données EXIF (date, position), puis montrée en vignette,
 * retirable jusqu'à l'envoi. L'envoi se fait par lots ; un voilier avance sur la barre de progression.
 */

// PHP ignore les fichiers au-delà de max_file_uploads (20 par défaut) dans une même requête :
// les photos sont donc envoyées par lots
const FILES_PER_REQUEST = 20;
const MAX_WIDTH = 1920;
const MAX_HEIGHT = 1080;
const MIME_TYPE = 'image/jpeg';
const QUALITY = 0.7;
// Photos préparées en même temps : en ouvrir des dizaines d'un coup peut épuiser la mémoire d'un téléphone
const PREPARE_CONCURRENCY = 4;

const form = document.querySelector('[data-upload-form]');
const fileInput = form.querySelector('[data-file-input]');
const dropCard = form.querySelector('[data-drop-card]');
const selection = form.querySelector('[data-selection]');
const tileList = form.querySelector('[data-tiles]');
const actionBar = form.querySelector('[data-action-bar]');
const readyRow = form.querySelector('[data-ready]');
const submitButton = form.querySelector('[data-submit]');
const progress = form.querySelector('[data-progress]');
const dropOverlay = document.querySelector('[data-drop-overlay]');
const tileTemplate = document.getElementById('photo-tile-template');

/**
 * Photos choisies, dans l'ordre de la grille.
 *
 * @type {{name: string, file: File|null, tile: HTMLLIElement, state: 'preparing'|'ready'|'sent'}[]}
 */
let photos = [];
let isUploading = false;

form.querySelectorAll('[data-pick-files]').forEach((button) => {
    button.addEventListener('click', () => fileInput.click());
});
fileInput.addEventListener('change', () => {
    addFiles(fileInput.files);
    // Vide le champ, pour que choisir de nouveau les mêmes fichiers soit bien pris en compte
    fileInput.value = '';
});
document.querySelector('[data-confirm-clear]').addEventListener('click', () => {
    [...photos].forEach(removePhoto);
});
form.addEventListener('submit', upload);
window.addEventListener('beforeunload', (event) => {
    if (isUploading) {
        event.preventDefault();
    }
});

/*
 * Glisser-déposer sur toute la page. Le compteur suit les entrées et sorties successives au-dessus des éléments
 * de la page : le voile ne disparaît que lorsque les fichiers quittent vraiment la fenêtre.
 */
let dragDepth = 0;

function isFileDrag(event) {
    return event.dataTransfer?.types.includes('Files');
}

window.addEventListener('dragenter', (event) => {
    if (!isFileDrag(event)) {
        return;
    }
    event.preventDefault();
    dragDepth++;
    if (!isUploading) {
        dropOverlay.classList.remove('hidden');
    }
});

window.addEventListener('dragover', (event) => {
    if (!isFileDrag(event)) {
        return;
    }
    // Sans cela, le navigateur ouvrirait la photo déposée à la place de la page
    event.preventDefault();
    event.dataTransfer.dropEffect = isUploading ? 'none' : 'copy';
});

window.addEventListener('dragleave', (event) => {
    if (!isFileDrag(event)) {
        return;
    }
    dragDepth = Math.max(0, dragDepth - 1);
    if (dragDepth === 0) {
        dropOverlay.classList.add('hidden');
    }
});

window.addEventListener('drop', (event) => {
    if (!isFileDrag(event)) {
        return;
    }
    event.preventDefault();
    dragDepth = 0;
    dropOverlay.classList.add('hidden');
    addFiles(event.dataTransfer.files);
});

/**
 * Ajoute des fichiers à la grille : une vignette d'attente apparaît tout de suite pour chacun,
 * puis la photo s'y affiche dès qu'elle est prête.
 *
 * @param {FileList} fileList
 */
async function addFiles(fileList) {
    if (isUploading) {
        return;
    }

    const files = Array.from(fileList);
    const notImageNames = files.filter((file) => !file.type.startsWith('image/')).map((file) => file.name);
    // Une photo déjà choisie (même nom) n'est pas ajoutée une seconde fois
    const newFiles = files.filter(
        (file) => file.type.startsWith('image/') && !photos.some((photo) => photo.name === file.name),
    );
    const messages =
        notImageNames.length > 0 ? [`Ces fichiers ne sont pas des photos : ${notImageNames.join(', ')}.`] : [];
    showErrors(messages);

    const added = newFiles.map((file) => {
        const photo = { name: file.name, file: null, tile: createTile(file.name), state: 'preparing' };
        photo.tile.querySelector('[data-remove]').addEventListener('click', () => removePhoto(photo));
        photos.push(photo);
        tileList.append(photo.tile);
        return { photo, file };
    });
    render();

    const unreadableNames = [];
    await eachWithConcurrency(added, PREPARE_CONCURRENCY, async ({ photo, file }) => {
        const preparedFile = await prepareImage(file, photo.tile.querySelector('canvas'));
        // La photo a pu être retirée pendant sa préparation
        if (!photos.includes(photo)) {
            return;
        }
        if (preparedFile) {
            photo.file = preparedFile;
            setState(photo, 'ready');
        } else {
            unreadableNames.push(file.name);
            removePhoto(photo);
        }
        render();
    });

    if (unreadableNames.length > 0) {
        showErrors([...messages, `Ces photos n'ont pas pu être lues : ${unreadableNames.join(', ')}.`]);
    }
}

/**
 * @param {string} name
 * @returns {HTMLLIElement}
 */
function createTile(name) {
    const tile = tileTemplate.content.firstElementChild.cloneNode(true);
    tile.querySelector('canvas').setAttribute('aria-label', name);
    tile.querySelector('[data-remove-name]').textContent = ` ${name}`;
    return tile;
}

function removePhoto(photo) {
    photos = photos.filter((other) => other !== photo);
    photo.tile.remove();
    render();
}

function setState(photo, state) {
    photo.state = state;
    photo.tile.dataset.state = state;
}

/**
 * Met la page à jour d'après les photos choisies : carte de départ ou grille, compteurs, bouton d'envoi.
 */
function render() {
    const preparingCount = photos.filter((photo) => photo.state === 'preparing').length;
    const readyCount = photos.filter((photo) => photo.state === 'ready').length;
    const hasPhotos = photos.length > 0;

    dropCard.classList.toggle('hidden', hasPhotos);
    selection.classList.toggle('hidden', !hasPhotos);
    actionBar.classList.toggle('hidden', !hasPhotos);

    form.querySelector('[data-selection-title]').textContent = countLabel(
        photos.length,
        'photo choisie',
        'photos choisies',
    );
    form.querySelector('[data-preparing]').textContent =
        preparingCount > 0 ? `Préparation en cours, encore ${countLabel(preparingCount, 'photo', 'photos')}…` : '';
    form.querySelector('[data-submit-label]').textContent = submitLabel(preparingCount, readyCount);
    submitButton.disabled = isUploading || preparingCount > 0 || readyCount === 0;
}

/**
 * Le bouton d'envoi dit combien de photos il ajoute.
 */
function submitLabel(preparingCount, readyCount) {
    if (preparingCount > 0) {
        return 'Préparation des photos…';
    }
    if (readyCount > 1) {
        return `Ajouter mes ${readyCount} photos`;
    }
    return readyCount === 1 ? 'Ajouter ma photo' : 'Ajouter mes photos';
}

/**
 * @param {number} count
 * @param {string} singular
 * @param {string} plural
 */
function countLabel(count, singular, plural) {
    return `${count} ${count > 1 ? plural : singular}`;
}

/**
 * Lance worker sur chaque élément, au plus limit à la fois.
 */
async function eachWithConcurrency(items, limit, worker) {
    let next = 0;
    const run = async () => {
        while (next < items.length) {
            await worker(items[next++]);
        }
    };
    await Promise.all(Array.from({ length: Math.min(limit, items.length) }, run));
}

/**
 * Dessine la vignette, puis réduit la photo en JPEG en gardant ses EXIF. Résolue à null si la photo est illisible.
 *
 * @param {File} file
 * @param {HTMLCanvasElement} previewCanvas
 * @returns {Promise<File|null>}
 */
function prepareImage(file, previewCanvas) {
    return new Promise((resolve) => {
        const img = new Image();
        img.onerror = () => {
            URL.revokeObjectURL(img.src);
            resolve(null);
        };
        img.onload = () => {
            URL.revokeObjectURL(img.src);
            drawPreview(img, previewCanvas);

            const [width, height] = calculateSize(img, MAX_WIDTH, MAX_HEIGHT);
            const canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            const ctx = canvas.getContext('2d');
            // Fond blanc pour les images transparentes (PNG, GIF), dont la transparence deviendrait noire en JPEG
            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, width, height);
            ctx.drawImage(img, 0, 0, width, height);
            canvas.toBlob(
                async (blob) => {
                    if (!blob) {
                        resolve(null);
                        return;
                    }
                    resolve(new File([await copyExif(file, blob)], file.name, { type: MIME_TYPE }));
                },
                MIME_TYPE,
                QUALITY,
            );
        };
        img.src = URL.createObjectURL(file);
    });
}

/**
 * Vignette carrée : le centre de la photo, recadré pour remplir le carré.
 */
function drawPreview(img, canvas) {
    const side = Math.min(img.naturalWidth, img.naturalHeight);
    const ctx = canvas.getContext('2d');
    ctx.fillStyle = 'white';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.drawImage(
        img,
        (img.naturalWidth - side) / 2,
        (img.naturalHeight - side) / 2,
        side,
        side,
        0,
        0,
        canvas.width,
        canvas.height,
    );
}

function calculateSize(img, maxWidth, maxHeight) {
    const ratio = Math.min(1, maxWidth / img.naturalWidth, maxHeight / img.naturalHeight);
    return [Math.round(img.naturalWidth * ratio), Math.round(img.naturalHeight * ratio)];
}

/**
 * Envoie les photos prêtes par lots. Celles d'un lot réussi sont marquées « Envoyée » : après une erreur,
 * un nouvel envoi ne reprend que les autres.
 */
async function upload(event) {
    event.preventDefault();
    const photosToSend = photos.filter((photo) => photo.state === 'ready');
    if (isUploading || photosToSend.length === 0) {
        return;
    }

    isUploading = true;
    form.dataset.uploading = '';
    showErrors([]);
    readyRow.classList.add('hidden');
    progress.classList.remove('hidden');
    setProgress(0, photosToSend.length);
    render();

    let response = null;
    for (let start = 0; start < photosToSend.length; start += FILES_PER_REQUEST) {
        const batch = photosToSend.slice(start, start + FILES_PER_REQUEST);
        const formData = new FormData(form);
        formData.delete('files[]');
        batch.forEach((photo) => formData.append('files[]', photo.file));
        try {
            response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: formData,
            });
        } catch {
            response = null;
        }
        if (!response?.ok) {
            await showUploadErrors(response, start);
            stopUploading();
            return;
        }
        batch.forEach((photo) => {
            setState(photo, 'sent');
            photo.file = null;
        });
        setProgress(start + batch.length, photosToSend.length);
    }

    const { redirect } = await response.json();
    isUploading = false;
    window.location.href = redirect;
}

function stopUploading() {
    isUploading = false;
    delete form.dataset.uploading;
    progress.classList.add('hidden');
    readyRow.classList.remove('hidden');
    render();
}

/**
 * Avance le voilier et la barre de progression.
 */
function setProgress(sentCount, totalCount) {
    // La piste va de 1,25 rem du bord gauche jusqu'à la bouée, large de 1,75 rem
    const distance = `(100% - 3rem) * ${totalCount > 0 ? sentCount / totalCount : 0}`;
    const bar = progress.querySelector('[data-progress-bar]');
    progress.querySelector('[data-progress-fill]').style.width = `calc(${distance})`;
    progress.querySelector('[data-progress-boat]').style.left = `calc(1.25rem + ${distance})`;
    bar.setAttribute('aria-valuemax', totalCount);
    bar.setAttribute('aria-valuenow', sentCount);
    progress.querySelector('[data-progress-label]').textContent = `${sentCount} sur ${totalCount}`;
}

async function showUploadErrors(response, uploadedCount) {
    let messages = ["L'envoi des photos a échoué, veuillez réessayer."];
    if (response?.status === 422) {
        messages = Object.values((await response.json()).errors).flat();
    }
    if (uploadedCount > 0) {
        messages.push(
            `${countLabel(uploadedCount, 'photo a', 'photos ont')} déjà été ${uploadedCount > 1 ? 'ajoutées' : 'ajoutée'} à l'album ; un nouvel envoi ne reprendra que les autres.`,
        );
    }
    showErrors(messages);
}

function showErrors(messages) {
    const errors = form.querySelector('[data-errors]');
    errors.querySelector('ul').replaceChildren(
        ...messages.map((message) => {
            const li = document.createElement('li');
            li.textContent = message;
            return li;
        }),
    );
    errors.classList.toggle('hidden', messages.length === 0);
    if (messages.length > 0) {
        errors.scrollIntoView({ block: 'nearest' });
    }
}

const SOI = 0xffd8;
const SOS = 0xffda;
const APP1 = 0xffe1;
const EXIF = 0x45786966;
const LITTLE_ENDIAN = 0x4949;
const BIG_ENDIAN = 0x4d4d;
const TAG_ID_ORIENTATION = 0x0112;
const TAG_TYPE_SHORT = 3;

/**
 * Copie les données EXIF (date, position GPS...) de la photo d'origine dans la photo redimensionnée.
 */
async function copyExif(srcBlob, destBlob) {
    // Les images qui ne sont pas des JPEG (PNG, GIF...) ou dont les EXIF sont illisibles sont envoyées sans EXIF
    const exif = await getApp1Segment(srcBlob).catch(() => new Blob());
    return new Blob([destBlob.slice(0, 2), exif, destBlob.slice(2)], { type: MIME_TYPE });
}

/**
 * Extrait le segment EXIF d'un JPEG, avec une orientation remise à 1 car le canvas a déjà tourné l'image.
 */
async function getApp1Segment(blob) {
    const buffer = await blob.arrayBuffer();
    const view = new DataView(buffer);
    let offset = 0;
    if (view.getUint16(offset) !== SOI) {
        throw new Error('not a valid JPEG');
    }
    offset += 2;

    while (true) {
        const marker = view.getUint16(offset);
        if (marker === SOS) {
            break;
        }

        const size = view.getUint16(offset + 2);
        if (marker === APP1 && view.getUint32(offset + 4) === EXIF) {
            const tiffOffset = offset + 10;
            let littleEndian;
            switch (view.getUint16(tiffOffset)) {
                case LITTLE_ENDIAN:
                    littleEndian = true;
                    break;
                case BIG_ENDIAN:
                    littleEndian = false;
                    break;
                default:
                    throw new Error('TIFF header contains invalid endian');
            }
            if (view.getUint16(tiffOffset + 2, littleEndian) !== 0x2a) {
                throw new Error('TIFF header contains invalid version');
            }

            const ifd0Offset = view.getUint32(tiffOffset + 4, littleEndian);
            const endOfTagsOffset =
                tiffOffset + ifd0Offset + 2 + view.getUint16(tiffOffset + ifd0Offset, littleEndian) * 12;
            for (let i = tiffOffset + ifd0Offset + 2; i < endOfTagsOffset; i += 12) {
                if (view.getUint16(i, littleEndian) === TAG_ID_ORIENTATION) {
                    if (view.getUint16(i + 2, littleEndian) !== TAG_TYPE_SHORT) {
                        throw new Error('Orientation data type is invalid');
                    }
                    if (view.getUint32(i + 4, littleEndian) !== 1) {
                        throw new Error('Orientation data count is invalid');
                    }

                    view.setUint16(i + 8, 1, littleEndian);
                    break;
                }
            }
            return buffer.slice(offset, offset + 2 + size);
        }
        offset += 2 + size;
    }
    return new Blob();
}
