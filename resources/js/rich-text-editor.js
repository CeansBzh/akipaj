import Trix from 'trix';
import 'trix/dist/trix.css';

// Trix defines its elements after a timeout, so the toolbars are still built with these labels
Object.assign(Trix.config.lang, {
    attachFiles: 'Joindre des fichiers',
    bold: 'Gras',
    bullets: 'Liste à puces',
    code: 'Code',
    heading1: 'Titre',
    indent: 'Augmenter le retrait',
    italic: 'Italique',
    link: 'Lien',
    numbers: 'Liste numérotée',
    outdent: 'Diminuer le retrait',
    quote: 'Citation',
    redo: 'Rétablir',
    remove: 'Supprimer',
    strike: 'Barré',
    undo: 'Annuler',
    unlink: 'Retirer le lien',
    url: 'URL',
    urlPlaceholder: 'Saisir une URL…',
});

// Files and pasted images have nowhere to be uploaded, and the sanitizer would remove them anyway
document.addEventListener('trix-file-accept', (event) => event.preventDefault());
document.addEventListener('trix-attachment-add', (event) => event.attachment.remove());
