<div id="dropbox" {{ $attributes->merge(['class' => 'flex justify-center items-center w-full']) }}>
    <label for="dropbox-file"
        class="flex flex-col justify-center items-center w-full h-64 bg-gray-50 rounded-lg border-2 border-gray-300 border-dashed cursor-pointer hover:bg-gray-100">
        <div class="flex flex-col justify-center items-center pt-5 pb-6">
            <svg aria-hidden="true" class="mb-3 w-10 h-10 text-gray-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
            </svg>
            <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Cliquer pour choisir des
                    images</span><span id="draggableMsg" class="hidden"> ou faites-les glisser</span></p>
            <p class="text-xs text-gray-500">PNG, JPG ou GIF</p>
        </div>
        <input id="dropbox-file" type="file" name="files[]" class="hidden" multiple />
    </label>
</div>

<div>
    <button type="submit" class="hidden bg-blue-500 text-white px-4 py-3 rounded font-medium w-full">Envoyer</button>
</div>

<p id="fileNb" class="text-center"></p>
<div id="preview"></div>

@push('scripts')
<script type="text/javascript">
    const formData = new FormData();
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
    dropbox.addEventListener("drop", drop, false);

    let inputElement = document.getElementById("dropbox-file");
    inputElement.addEventListener("change", handleFiles, false);

    let preview = document.getElementById("preview");

    function dragenter(e) {
        e.stopPropagation();
        e.preventDefault();
    }

    function dragover(e) {
        e.stopPropagation();
        e.preventDefault();
    }

    function drop(e) {
        e.stopPropagation();
        e.preventDefault();

        const dt = e.dataTransfer;
        const files = dt.files;

        handleFiles(files);
    }

    function handleFiles(files) {
        if (!(files instanceof FileList)) { files = this.files }
        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            if (!file.type.startsWith('image/')) { continue }

            let formDataIncludes = false;
            for (var value of formData.values()) {
                if (value.name === file.name) {
                    formDataIncludes = true;
                    break;
                }
            }
            if (!formDataIncludes) {
                const img = document.createElement("img");
                img.classList.add("max-w-xs", "mb-1", "mx-auto");
                img.file = file;
                preview.appendChild(img);

                const reader = new FileReader();
                reader.onload = (e) => { img.src = e.target.result; };
                reader.readAsDataURL(file);
                formData.append('files', file);
                dataTransfer.items.add(file);
            }
        }
        document.getElementById('fileNb').innerHTML = formData.getAll('files').length + ' fichiers sélectionnés';
        document.querySelector('button[type="submit"]').classList.remove('hidden');
        inputElement.files = dataTransfer.files;
    }
</script>
@endpush