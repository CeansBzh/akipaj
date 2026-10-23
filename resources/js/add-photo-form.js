// Script to drag and drop, resize, rewrite exif data, preview and upload images
// Assembled using different sources
// Speed for 100 images (average size 3.3MB): 15.6s

// PHP ignore les fichiers au-delà de max_file_uploads (20 par défaut) dans une même requête :
// les photos sont donc envoyées par lots
const FILES_PER_REQUEST = 20;
const MAX_WIDTH = 1920;
const MAX_HEIGHT = 1080;
const PREVIEW_SIZE = 70;
const MIME_TYPE = 'image/jpeg';
const QUALITY = 0.7;

const dataTransfer = new DataTransfer();
const loadedFileNames = [];

const inputElement = document.getElementById('dropbox-file');
const previewContainer = document.getElementById('previewContainer');

inputElement.addEventListener('change', () => handleFiles(inputElement.files));
inputElement.form.addEventListener('submit', uploadFiles);

const dragAndDropSupported = (() => {
    const div = document.createElement('div');
    return ('draggable' in div || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window;
})();
if (dragAndDropSupported) {
    document.getElementById('draggableMsg').classList.remove('hidden');

    const dropbox = document.getElementById('dropbox');
    dropbox.addEventListener('dragover', (e) => {
        // prevent default to allow drop
        e.preventDefault();
    });

    dropbox.addEventListener('dragenter', () => {
        // highlight potential drop target using svg icon
        document.getElementById('dropbox-body').classList.add('invisible');
        document.getElementById('drag-over-icon').classList.remove('hidden');
    });

    dropbox.addEventListener('dragleave', () => {
        // remove highlight for potential drop target
        document.getElementById('dropbox-body').classList.remove('invisible');
        document.getElementById('drag-over-icon').classList.add('hidden');
    });

    dropbox.addEventListener('drop', (e) => {
        // prevent default action
        e.preventDefault();
        // remove highlight for potential drop target
        document.getElementById('dropbox-body').classList.remove('invisible');
        document.getElementById('drag-over-icon').classList.add('hidden');
        handleFiles(e.dataTransfer.files);
    });
}

function handleFiles(files) {
    const startTime = performance.now();
    document.getElementById('dropbox-body').classList.add('invisible');
    document.getElementById('loading-spinner').classList.remove('hidden');
    document.getElementById('info')?.remove();
    // Pour chaque fichier on vérifie qu'il s'agit bien d'une image, et qu'il n'est pas déjà chargé
    const imageFiles = Array.from(files).filter(
        (file) => !loadedFileNames.includes(file.name) && file.type.startsWith('image/'),
    );
    Promise.all(imageFiles.map((file) => loadImage(file))).then((loaded) => {
        document.getElementById('dropbox-body').classList.remove('invisible');
        document.getElementById('loading-spinner').classList.add('hidden');
        console.log(`Temps d'exécution : ${(performance.now() - startTime) / 1000} secondes`);
        const unreadableFileNames = imageFiles.filter((file, i) => !loaded[i]).map((file) => file.name);
        if (unreadableFileNames.length > 0) {
            showErrors([`Ces fichiers n'ont pas pu être lus : ${unreadableFileNames.join(', ')}.`]);
        }
    });
}

/**
 * Redimensionne l'image en JPEG et l'ajoute à la sélection. La promesse est résolue à false si l'image est illisible.
 */
function loadImage(file) {
    return new Promise((resolve) => {
        const img = new Image();
        img.src = URL.createObjectURL(file);
        img.onerror = () => {
            URL.revokeObjectURL(img.src);
            resolve(false);
        };
        img.onload = () => {
            URL.revokeObjectURL(img.src);
            const [newWidth, newHeight] = calculateSize(img, MAX_WIDTH, MAX_HEIGHT);
            const canvas = document.createElement('canvas');
            canvas.width = newWidth;
            canvas.height = newHeight;
            const ctx = canvas.getContext('2d');
            // Fond blanc pour les images transparentes (PNG, GIF), dont la transparence deviendrait noire en JPEG
            ctx.fillStyle = 'white';
            ctx.fillRect(0, 0, newWidth, newHeight);
            ctx.drawImage(img, 0, 0, newWidth, newHeight);
            canvas.toBlob(
                async (blob) => {
                    if (!blob) {
                        resolve(false);
                        return;
                    }
                    dataTransfer.items.add(new File([await copyExif(file, blob)], file.name, { type: MIME_TYPE }));
                    inputElement.files = dataTransfer.files;
                    loadedFileNames.push(file.name);
                    addPreview(img, file.name);
                    resolve(true);
                },
                MIME_TYPE,
                QUALITY,
            );
        };
    });
}

function calculateSize(img, maxWidth, maxHeight) {
    const ratio = Math.min(1, maxWidth / img.naturalWidth, maxHeight / img.naturalHeight);
    return [img.naturalWidth * ratio, img.naturalHeight * ratio];
}

async function uploadFiles(e) {
    if (dataTransfer.files.length === 0) {
        // Envoi classique du formulaire pour afficher l'erreur de validation
        return;
    }
    e.preventDefault();
    const form = e.target;
    const submitButton = form.querySelector('button[type="submit"]');
    const fileNb = document.getElementById('fileNb');
    const totalCount = dataTransfer.files.length;
    let uploadedCount = 0;
    let response;
    submitButton.disabled = true;
    showErrors([]);
    while (dataTransfer.files.length > 0) {
        fileNb.textContent = `Envoi des photos : ${uploadedCount}/${totalCount}`;
        const batch = Array.from(dataTransfer.files).slice(0, FILES_PER_REQUEST);
        const formData = new FormData(form);
        formData.delete('files[]');
        batch.forEach((file) => formData.append('files[]', file));
        try {
            response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: formData,
            });
        } catch (error) {
            response = null;
        }
        if (!response?.ok) {
            await showUploadErrors(response, uploadedCount);
            submitButton.disabled = false;
            return;
        }
        // Les photos envoyées sont retirées de la sélection, pour ne pas les renvoyer en cas d'erreur sur un lot suivant
        batch.forEach((file) => removeFileByName(file.name));
        uploadedCount += batch.length;
    }
    fileNb.textContent = `Envoi des photos : ${uploadedCount}/${totalCount}`;
    window.location.href = (await response.json()).redirect;
}

async function showUploadErrors(response, uploadedCount) {
    let messages = ["L'envoi des photos a échoué, veuillez réessayer."];
    if (response?.status === 422) {
        messages = Object.values((await response.json()).errors).flat();
    }
    if (uploadedCount > 0) {
        messages.push(`${uploadedCount} photos ont déjà été ajoutées à l'album.`);
    }
    showErrors(messages);
}

function showErrors(messages) {
    document.getElementById('uploadErrors').replaceChildren(
        ...messages.map((message) => {
            const li = document.createElement('li');
            li.textContent = message;
            return li;
        }),
    );
}

function removeFile(e) {
    const canvas = e.target.closest('div').previousElementSibling;
    removeFileByName(canvas.getAttribute('data-filename'));
}

function removeFileByName(filename) {
    for (let i = 0; i < dataTransfer.items.length; i++) {
        if (dataTransfer.items[i].getAsFile().name == filename) {
            dataTransfer.items.remove(i);
            break;
        }
    }
    document.getElementById('fileNb').textContent = `${dataTransfer.files.length} fichiers sélectionnés`;
    inputElement.files = dataTransfer.files;
    previewContainer
        .querySelector(`canvas[data-filename="${CSS.escape(filename)}"]`)
        ?.closest('div')
        .remove();
}

function addPreview(img, fileName) {
    const clone = document.getElementById('previewElement').content.cloneNode(true);
    const canvas = clone.querySelector('canvas');
    canvas.width = PREVIEW_SIZE;
    canvas.height = PREVIEW_SIZE;
    canvas.getContext('2d').drawImage(img, 0, 0, PREVIEW_SIZE, PREVIEW_SIZE);
    canvas.setAttribute('data-filename', fileName);
    clone.querySelector('span').textContent = fileName;
    clone.querySelector('button').addEventListener('click', removeFile);
    previewContainer.appendChild(clone);
    document.getElementById('fileNb').textContent = `${dataTransfer.files.length} fichiers sélectionnés`;
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
