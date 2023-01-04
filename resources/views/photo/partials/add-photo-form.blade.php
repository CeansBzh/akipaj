<section class="max-w-2xl mx-auto">
    <div class="mb-5 text-center">
        <form action="{{ route('photos.store', ['album' => $album]) }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div id="dropbox" class="flex justify-center items-center w-full">
                <label for="dropbox-file"
                    class="fixed bottom-[calc(5rem+25px)] bg-white p-3 rounded-lg border-2 border-sky-300 cursor-pointer hover:bg-sky-100 md:relative md:bottom-0 md:w-full md:h-56 md:flex md:justify-center md:items-center md:border-dashed md:p-8">
                    <div id="loading-spinner" role="status"
                        class="hidden absolute bottom-[calc(50%-1.25rem)] left-[calc(50%-1.25rem)]">
                        <svg class="inline mr-2 w-10 h-10 text-gray-300 motion-safe:animate-spin fill-sky-500"
                            viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                                fill="currentColor" />
                            <path
                                d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                                fill="currentFill" />
                        </svg>
                        <span class="sr-only">Chargement...</span>
                    </div>
                    <div id="drag-over-icon" role="status"
                        class="hidden absolute bottom-[calc(50%-1.75rem)] left-[calc(50%-1.75rem)]">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="inline mr-2 w-14 h-14 text-sky-500 motion-safe:animate-bounce">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        <span class="sr-only">Relâcher pour déposer les fichiers</span>
                    </div>
                    <div id="dropbox-body" class="flex flex-row justify-center items-center text-sky-600 md:flex-col">
                        <svg aria-hidden="true" class="mb-3 mr-3 w-10 h-10 text-sky-500" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                            </path>
                        </svg>
                        <div class="flex flex-col">
                            <p class="hidden mb-2 text-sm text-gray-500 md:block"><span class="font-semibold">Cliquez
                                    pour choisir
                                    des
                                    images</span><span id="draggableMsg" class="hidden"> ou faites-les glisser</span>
                            </p>
                            <p class="text-sm font-semibold md:hidden">Parcourir mes fichiers</p>
                            <p class="text-xs md:text-gray-500">PNG, JPG ou GIF</p>
                        </div>
                    </div>
                    <input id="dropbox-file" type="file" name="files[]" class="hidden" multiple />
                </label>
            </div>

            <p id="fileNb" class="text-center"></p>
            <div id="previewContainer" class="mt-4 pb-3">
                <div id="info" class="px-5 sm:py-10">
                    <div class="mx-auto max-w-xs text-sm text-gray-500">
                        @if ($errors->any())
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="h-14 w-14 mx-auto mb-5 text-red-500">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <div class="text-red-500">
                            <ul>
                                @foreach ($errors->all() as $error)
                                <li class="mb-2">{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @else
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="h-14 w-14 mx-auto mb-5 text-gray-400">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <p class="mb-2">Toutes les photos importées seront redimensionnées pour réduire leur
                            poids.</p>
                        @endif
                    </div>
                </div>
            </div>

            <template id="previewElement">
                <div class="mb-5 rounded-md">
                    <div class="flex items-center justify-between">
                        <canvas class="rounded" width="25" height="25">
                            Désolé, votre navigateur ne prend pas en charge &lt;canvas&gt;.
                        </canvas>
                        <div class="flex flex-row bg-white w-full justify-end py-2 px-4 rounded-r shadow-sm">
                            <span class="truncate px-3 text-base font-medium text-[#07074D]">
                                fichier.png
                            </span>
                            <button class="text-[#07074D] ml-4">
                                <svg width="10" height="10" viewBox="0 0 10 10" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M0.279337 0.279338C0.651787 -0.0931121 1.25565 -0.0931121 1.6281 0.279338L9.72066 8.3719C10.0931 8.74435 10.0931 9.34821 9.72066 9.72066C9.34821 10.0931 8.74435 10.0931 8.3719 9.72066L0.279337 1.6281C-0.0931125 1.25565 -0.0931125 0.651788 0.279337 0.279338Z"
                                        fill="currentColor" />
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M0.279337 9.72066C-0.0931125 9.34821 -0.0931125 8.74435 0.279337 8.3719L8.3719 0.279338C8.74435 -0.0931127 9.34821 -0.0931123 9.72066 0.279338C10.0931 0.651787 10.0931 1.25565 9.72066 1.6281L1.6281 9.72066C1.25565 10.0931 0.651787 10.0931 0.279337 9.72066Z"
                                        fill="currentColor" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <div class="fixed bottom-0 left-0 right-0">
                <div class="max-w-3xl mx-auto h-20 p-5 bg-white rounded-t-xl shadow-2xl">
                    <div class="flex justify-between items-center">
                        <p class="grow mr-2 text-left">Vos photos seront ajoutées à l'album "
                            <span class="font-semibold">{{ $album->title }}</span>"
                        </p>
                        <div>
                            <x-primary-button class="bg-sky-500 py-3 hover:bg-sky-700">Ajouter mes
                                photos</x-primary-button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>


@push('scripts')
<script type="text/javascript">
    const MAX_FILES = 50;
    const MAX_WIDTH = 1920;
    const MAX_HEIGHT = 1080;
    const PREVIEW_SIZE = 70;
    const MIME_TYPE = "image/jpeg";
    const QUALITY = 0.7;

    const dataTransfer = new DataTransfer();

    var isAdvancedUpload = function () {
        var div = document.createElement('div');
        return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
    }();
    if (isAdvancedUpload) {
        document.getElementById('draggableMsg').classList.remove('hidden');
    }

    let dropbox = document.getElementById("dropbox");
    dropbox.addEventListener("dragenter", dragenter, false);
    dropbox.addEventListener("dragover", dragover, false);
    dropbox.addEventListener("dragleave", dragleave, false);
    dropbox.addEventListener("drop", drop, false);

    let inputElement = document.getElementById("dropbox-file");
    inputElement.addEventListener("change", handleFiles, false);

    let previewContainer = document.getElementById("previewContainer");

    function dragenter(e) {
        e.stopPropagation();
        e.preventDefault();
        document.getElementById('dropbox-body').classList.add('invisible');
        document.getElementById('drag-over-icon').classList.remove('hidden');
    }

    function dragover(e) {
        e.stopPropagation();
        e.preventDefault();
    }

    function dragleave(e) {
        e.stopPropagation();
        e.preventDefault();
        document.getElementById('dropbox-body').classList.remove('invisible');
        document.getElementById('drag-over-icon').classList.add('hidden');
    }

    function drop(e) {
        e.stopPropagation();
        e.preventDefault();

        const dt = e.dataTransfer;
        const files = dt.files;

        handleFiles(files);
    }

    const loadImage = file =>
        new Promise((resolve, reject) => {
            const blobURL = URL.createObjectURL(file);
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
                canvas.toBlob(
                    async (blob) => {
                        dataTransfer.items.add(new File([await copyExif(file, blob)], file.name, { type: MIME_TYPE }));
                        inputElement.files = dataTransfer.files;
                        addPreview(img, file.name);
                        resolve(true);
                        return;
                    },
                    MIME_TYPE,
                    QUALITY
                );
            };
        });

    function handleFiles(files) {
        if (!(files instanceof FileList)) { files = this.files }
        const promises = [];
        const info = document.getElementById('info');
        if (files.length > MAX_FILES || dataTransfer.files.length + files.length > MAX_FILES) {
            let svg = info.querySelector('svg');
            svg.classList.remove('text-gray-400');
            svg.classList.add('text-red-500');
            svg.innerHTML = '<circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line>';
            info.replaceChild(svg, info.firstChild);
            let p = info.querySelectorAll('p');
            p[0].innerHTML = 'Vous ne pouvez pas ajouter plus de ' + MAX_FILES + ' fichiers.';
            p[1].remove();
            return;
        }
        document.getElementById('dropbox-body').classList.add('invisible');
        document.getElementById('loading-spinner').classList.remove('hidden');
        if (info) { info.remove(); }
        for (let i = 0; i < files.length; i++) {
            // Pour chaque fichier on vérifie qu'il s'agit bien d'une image
            const file = files[i];
            if (!file.type.startsWith('image/')) { continue }
            // On verifie si le fichier n'est pas déjà dans la liste
            if (dataTransfer.items.length > 0) {
                let alreadyInList = false;
                for (let i = 0; i < dataTransfer.items.length; i++) {
                    if (dataTransfer.items[i].getAsFile().name == file.name) {
                        alreadyInList = true;
                        break;
                    }
                }
                if (alreadyInList) { continue }
            }
            // Si le fichier est une image et n'est pas déjà dans la liste :
            promises.push(loadImage(file));
        }
        Promise.all(promises).then(() => {
            document.getElementById('dropbox-body').classList.remove('invisible');
            document.getElementById('loading-spinner').classList.add('hidden');
        });
    }

    function calculateSize(img, maxWidth, maxHeight) {
        let ratio = Math.min(1, maxWidth / img.naturalWidth, maxHeight / img.naturalHeight);
        return [img.naturalWidth * ratio, img.naturalHeight * ratio];
    }

    function removeFile(e) {
        const canvas = e.target.closest('div').previousElementSibling;
        const filename = canvas.getAttribute('data-filename');
        for (let i = 0; i < dataTransfer.items.length; i++) {
            if (dataTransfer.items[i].getAsFile().name == filename) {
                dataTransfer.items.remove(i);
                break;
            }
        }
        document.getElementById('fileNb').innerHTML = dataTransfer.files.length + ' fichiers sélectionnés';
        inputElement.files = dataTransfer.files;
        canvas.closest('div').remove();
    }

    function addPreview(img, fileName) {
        if ('content' in document.createElement('template')) {
            // Si le navigateur supporte les templates
            const template = document.getElementById('previewElement');
            const clone = template.content.cloneNode(true);
            const canvas = clone.querySelector('canvas');
            canvas.width = PREVIEW_SIZE;
            canvas.height = PREVIEW_SIZE;
            const ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0, PREVIEW_SIZE, PREVIEW_SIZE);
            clone.querySelector('span').innerHTML = fileName;
            canvas.setAttribute('data-filename', fileName);
            clone.querySelector('button').addEventListener('click', removeFile);
            previewContainer.appendChild(clone);
        } else {
            // Si le navigateur ne supporte pas les templates
            const previewElem = document.createElement("p");
            previewElem.innerHTML = fileName;
            previewContainer.appendChild(previewElem);
        }
        document.getElementById('fileNb').innerHTML = dataTransfer.files.length + ' fichiers sélectionnés';
    }

    async function copyExif(srcBlob, destBlob) {
        const exif = await getApp1Segment(srcBlob);
        return new Blob([destBlob.slice(0, 2), exif, destBlob.slice(2)], {
            type: "image/jpeg",
        });
    };

    const SOI = 0xffd8,
        SOS = 0xffda,
        APP1 = 0xffe1,
        EXIF = 0x45786966,
        LITTLE_ENDIAN = 0x4949,
        BIG_ENDIAN = 0x4d4d,
        TAG_ID_ORIENTATION = 0x0112,
        TAG_TYPE_SHORT = 3,
        getApp1Segment = (blob) =>
            new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.addEventListener("load", ({ target: { result: buffer } }) => {
                    const view = new DataView(buffer);
                    let offset = 0;
                    if (view.getUint16(offset) !== SOI) return reject("not a valid JPEG");
                    offset += 2;

                    while (true) {
                        const marker = view.getUint16(offset);
                        if (marker === SOS) break;

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
                                    return reject("TIFF header contains invalid endian");
                            }
                            if (view.getUint16(tiffOffset + 2, littleEndian) !== 0x2a)
                                return reject("TIFF header contains invalid version");

                            const ifd0Offset = view.getUint32(tiffOffset + 4, littleEndian);
                            let endOfTagsOffset =
                                tiffOffset +
                                ifd0Offset +
                                2 +
                                view.getUint16(tiffOffset + ifd0Offset, littleEndian) * 12;
                            for (
                                let i = tiffOffset + ifd0Offset + 2;
                                i < endOfTagsOffset;
                                i += 12
                            ) {
                                const tagId = view.getUint16(i);
                                if (tagId == TAG_ID_ORIENTATION) {
                                    if (view.getUint16(i + 2, littleEndian) !== TAG_TYPE_SHORT)
                                        return reject("Orientation data type is invalid");

                                    if (view.getUint32(i + 4, littleEndian) !== 1)
                                        return reject("Orientation data count is invalid");

                                    view.setUint16(i + 8, 1, littleEndian);
                                    break;
                                }
                            }
                            return resolve(buffer.slice(offset, offset + 2 + size));
                        }
                        offset += 2 + size;
                    }
                    return resolve(new Blob());
                });
                reader.readAsArrayBuffer(blob);
            });
</script>
@endpush
