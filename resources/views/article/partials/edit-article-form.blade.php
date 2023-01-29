<section class="mx-auto max-w-2xl" x-data="editArticle()" x-init="init()">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Modifier : {{ $article->title }}
            {{ $article->online ? '' : '(brouillon)' }}</h2>
        <p class="mt-1 text-sm text-gray-600">L'article sera immédiatement modifié. S'il n'est pas encore publié il
            restera en brouillon.</p>
    </header>

    <form id="add-article-form" method="post" action="{{ route('articles.update', $article) }}"
        enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="title_input" value="Titre" />
            <x-text-input id="title_input" name="title" type="text" class="mt-1 block w-full" :value="$article->title"
                placeholder="Nouveau super article" maxlength="50" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->updateArticle->get('title')" />
        </div>

        <div>
            <div class="flex flex-col space-y-2">
                <x-input-label for="editor" value="Contenu" />
                <div id="editor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    {!! $article->body_html !!}
                </div>
                <x-input-error class="mt-2" :messages="$errors->updateArticle->get('body')" />
            </div>
            <input id="body" type="hidden" name="body">
        </div>

        <div>
            <x-input-label for="summary_input" value="Résumé" />
            <x-textarea-input id="summary_input" name="summary" class="mt-1 block w-full"
                placeholder="Dans cet article nous parlerons de..." maxlength="350" rows="4">
                {{ $article->summary }}
            </x-textarea-input>
            <x-input-error class="mt-2" :messages="$errors->updateArticle->get('summary')" />
        </div>

        <div>
            <x-input-label for="image_input" value="Image de couverture (facultatif)" />
            <input id="image_input" name="image" type="file" class="mt-1 block w-full"
                accept="image/png, image/jpeg" @change="resizeImage">
            <x-input-error class="mt-2" :messages="$errors->updateArticle->get('image')" />
        </div>

        <div>
            <input x-model="imageRemoved" name="remove_image" type="checkbox" class="hidden" aria-hidden="true">
            <x-input-error class="mt-2" :messages="$errors->updateArticle->get('remove_image')" />
        </div>

        <div class="relative" x-show="!imageRemoved">
            <img id="image_display" src="{{ $article->imagePath }}"
                alt="Image de couverture de l'événement {{ $article->name }}"
                class="h-64 w-full rounded-xl object-cover">
            <button type="button" class="group" x-on:click.prevent="removeImage">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="absolute top-3 right-3 h-6 text-white drop-shadow-[0_3px_3px_rgba(0,0,0,0.8)] hover:text-gray-100 group-focus:stroke-sky-500 group-focus:motion-safe:animate-pulse">
                    <path d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Enregistrer l'article</x-primary-button>

            <div class="flex items-center space-x-2">
                <x-input-label for="online_input" value="Publier l'article" />
                <input id="online_input" type="checkbox" name="online" class="rounded"
                    {{ $article->online ? 'checked' : '' }}>
                <x-input-error class="mt-2" :messages="$errors->updateArticle->get('online')" />
            </div>
        </div>
    </form>
</section>

@push('scripts')
    <script type="text/javascript">
        document.getElementById('add-article-form').addEventListener('submit', e => {
            e.preventDefault();
            document.getElementById('body').value = editor.getMarkdown();
            e.target.submit();
        });

        const MAX_WIDTH = 2560;
        const MAX_HEIGHT = 1600;
        const MIME_TYPE = "image/jpeg";
        const QUALITY = 0.8;

        function editArticle() {
            return {
                imageRemoved: false,
                init() {
                    this.imageRemoved = document.getElementById('image_display').getAttribute('src') == '';
                },
                fileToDataUrl(event, callback) {
                    if (!event.target.files.length) return

                    let file = event.target.files[0],
                        reader = new FileReader()

                    reader.readAsDataURL(file)
                    reader.onload = e => callback(e.target.result)
                },
                removeImage: function() {
                    this.imageRemoved = confirm('Supprimer l\'image de l\'article ?');
                    if (this.imageRemoved) {
                        document.getElementById('image_input').value = '';
                    }
                },
                resizeImage(event) {
                    const file = event.target.files[0];
                    const blobURL = URL.createObjectURL(file);
                    const img = new Image();
                    const dataTransfer = new DataTransfer();
                    img.src = blobURL;
                    img.onerror = function() {
                        URL.revokeObjectURL(this.src);
                        // Handle the failure properly
                        console.log("Cannot load image");
                    };
                    img.onload = () => {
                        URL.revokeObjectURL(this.src);
                        const [newWidth, newHeight] = this.calculateSize(img, MAX_WIDTH, MAX_HEIGHT);
                        const canvas = document.createElement("canvas");
                        canvas.width = newWidth;
                        canvas.height = newHeight;
                        const ctx = canvas.getContext("2d");
                        ctx.drawImage(img, 0, 0, newWidth, newHeight);
                        canvas.toBlob(
                            (blob) => {
                                dataTransfer.items.add(new File([blob], file.name, {
                                    type: MIME_TYPE
                                }));
                                event.target.files = dataTransfer.files
                                this.fileToDataUrl(event, src => document.getElementById('image_display').src =
                                src);
                                this.imageRemoved = false;
                                return;
                            },
                            MIME_TYPE,
                            QUALITY
                        );
                    };
                },
                calculateSize(img, maxWidth, maxHeight) {
                    let ratio = Math.min(1, maxWidth / img.naturalWidth, maxHeight / img.naturalHeight);
                    return [img.naturalWidth * ratio, img.naturalHeight * ratio];
                }
            }
        }
    </script>
@endpush
