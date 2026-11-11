import Trix from 'trix';
import 'trix/dist/trix.css';

const titleAttributes = ['heading2', 'heading3', 'heading4'];

delete Trix.config.blockAttributes.heading1;
titleAttributes.forEach((attributeName, index) => {
    Trix.config.blockAttributes[attributeName] = {
        tagName: `h${index + 2}`,
        terminal: true,
        breakOnReturn: true,
        group: false,
    };
});

// Trix définit ses éléments après un délai : ses barres d'outils sont encore créées avec ces libellés et boutons
Object.assign(Trix.config.lang, {
    attachFiles: 'Joindre des fichiers',
    bold: 'Gras',
    bullets: 'Liste à puces',
    code: 'Code',
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

const getDefaultToolbarHTML = Trix.config.toolbar.getDefaultHTML;
Trix.config.toolbar.getDefaultHTML = () => {
    const toolbar = document.createElement('template');
    toolbar.innerHTML = getDefaultToolbarHTML();
    toolbar.content.querySelector('[data-trix-attribute="heading1"]').outerHTML = titleAttributes
        .map(
            (attributeName, index) => `<button type="button" class="trix-button" data-trix-action="x-${attributeName}"
            title="Titre ${index + 1}" aria-label="Titre ${index + 1}" tabindex="-1">T${index + 1}</button>`,
        )
        .join('');

    return toolbar.innerHTML;
};

// Les boutons de titre sont des actions plutôt que des attributs : Trix désactive les autres attributs de bloc dans
// un titre, qui ne pourrait donc pas changer de niveau sans être retiré d'abord
document.addEventListener('trix-action-invoke', ({ target, actionName }) => {
    const attributeName = actionName.replace(/^x-/, '');
    if (!titleAttributes.includes(attributeName)) {
        return;
    }

    const { editor } = target;
    const currentTitle = titleAttributes.find((title) => editor.attributeIsActive(title));
    editor.recordUndoEntry('Formatting');
    if (currentTitle) {
        editor.deactivateAttribute(currentTitle);
    }
    if (currentTitle !== attributeName) {
        editor.activateAttribute(attributeName);
    }
});

// Trix ne met à jour que l'état des boutons d'attribut, et active les boutons d'action qu'il ne connaît pas à chaque
// changement des actions. Son premier changement d'attributs a lieu avant que l'éditeur soit défini, d'où la mise à
// jour à l'initialisation.
function refreshTitleButtons({ target }) {
    const { editor, toolbarElement } = target;
    if (!editor) {
        return;
    }

    const currentTitle = titleAttributes.find((title) => editor.attributeIsActive(title));
    titleAttributes.forEach((attributeName) => {
        const button = toolbarElement.querySelector(`[data-trix-action="x-${attributeName}"]`);
        button.classList.toggle('trix-active', attributeName === currentTitle);
        button.disabled = !currentTitle && !editor.canActivateAttribute(attributeName);
    });
}

document.addEventListener('trix-initialize', refreshTitleButtons);
document.addEventListener('trix-attributes-change', refreshTitleButtons);
document.addEventListener('trix-actions-change', refreshTitleButtons);

// Les fichiers et images collées n'ont nulle part où être envoyés, et le nettoyage HTML les retirerait de toute façon
document.addEventListener('trix-file-accept', (event) => event.preventDefault());
document.addEventListener('trix-attachment-add', (event) => event.attachment.remove());
