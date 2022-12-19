import Editor from '@toast-ui/editor'
import '@toast-ui/editor/dist/toastui-editor.css';

if (document.querySelector('#editor')) {
    window.editor = new Editor({
        el: document.querySelector('#editor'),
        height: '90vh',
        initialEditType: 'wysiwyg',
        previewStyle: 'tab',
        autofocus: false,
        toolbarItems: [
            ['heading', 'bold', 'italic', 'strike'],
            ['hr', 'quote'],
            ['ul', 'ol', 'task', 'indent', 'outdent'],
            ['table', 'image', 'link'],
        ],
        hooks: {
            addImageBlobHook: (blob, callback) => onAddImageBlob(blob, callback),
        }
    });
}

async function uploadImage(blob) {
    let formData = new FormData();
    formData.append('image', blob);
    const response = await fetch('/articles/admin/storeImage', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
        body: formData
    });
    if (response.ok) {
        return response.json();
    }
    throw new Error('Server or network error');
}

function onAddImageBlob(blob, callback) {
    resizeImage(blob).then((blob) => {
        uploadImage(blob)
            .then(response => {
                if (!response.success) {
                    throw new Error('Validation error');
                }
                callback(response.data.url);
            }).catch(error => {
                console.log(error);
            });
    });
}

const MAX_WIDTH = 800;
const MAX_HEIGHT = 800;
const MIME_TYPE = "image/jpeg";
const QUALITY = 0.7;

async function resizeImage(blob) {
    return new Promise(function (resolve, reject) {
        const blobURL = URL.createObjectURL(blob);
        const img = new Image();
        img.src = blobURL;
        img.onerror = function () {
            URL.revokeObjectURL(img.src);
            // Handle the failure properly
            console.log("Cannot load image");
        };
        img.onload = () => {
            URL.revokeObjectURL(img.src);
            const [newWidth, newHeight] = calculateSize(img, MAX_WIDTH, MAX_HEIGHT);
            const canvas = document.createElement("canvas");
            canvas.width = newWidth;
            canvas.height = newHeight;
            const ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0, newWidth, newHeight);
            getCanvasBlob(canvas).then((blob) => {
                resolve(blob);
            });
        };
    });
}

function getCanvasBlob(canvas) {
    return new Promise(function (resolve, reject) {
        canvas.toBlob(
            (blob) => {
                resolve(blob)
            },
            MIME_TYPE,
            QUALITY)
    })
}

function calculateSize(img, maxWidth, maxHeight) {
    let ratio = Math.min(1, maxWidth / img.naturalWidth, maxHeight / img.naturalHeight);
    return [img.naturalWidth * ratio, img.naturalHeight * ratio];
}
