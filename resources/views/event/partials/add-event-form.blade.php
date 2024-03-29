<section class="mx-auto max-w-2xl" x-data="addEvent()">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Ajouter un événement</h2>
        <p class="mt-1 text-sm text-gray-600">L'événement sera visible à tous les membres.</p>
    </header>

    <form method="post" action="{{ route('events.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-input-label for="name_input" value="Nom" />
            <x-text-input id="name_input" name="name" type="text" class="mt-1 block w-full" :value="old('name')"
                placeholder="WK Boat Açores" maxlength="50" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="flex w-full space-x-5">
            <div>
                <x-input-label for="start_time_input" value="Date de début" />
                <x-text-input id="start_time_input" name="start_time" type="date" class="mt-1 block w-full"
                    :value="old('start_time', date('Y-m-d'))" :min="date('Y-m-d')" required />
                <x-input-error class="mt-2" :messages="$errors->get('start_time')" />
            </div>

            <div>
                <x-input-label for="end_time_input" value="Date de fin" />
                <x-text-input id="end_time_input" name="end_time" type="date" class="mt-1 block w-full"
                    :value="old(
                        'end_time',
                        now()
                            ->addDays(10)
                            ->format('Y-m-d'),
                    )" :min="date('Y-m-d')" required />
                <x-input-error class="mt-2" :messages="$errors->get('end_time')" />
            </div>
        </div>

        <div>
            <x-input-label for="description_input" value="Description" />
            <x-textarea-input id="description_input" name="description" class="mt-1 block w-full"
                placeholder="Description de l'événement" maxlength="140" required>
                {{ old('description') }}
            </x-textarea-input>
            <x-input-error class="mt-2" :messages="$errors->get('description')" />
        </div>

        <div>
            <x-input-label for="location_input" value="Adresse (facultatif)" />
            <x-text-input id="location_input" name="location" type="text" class="mt-1 block w-full" :value="old('location')"
                placeholder="Port de plaisance de Horta, Portugal" maxlength="255" />
            <x-input-error class="mt-2" :messages="$errors->get('location')" />
        </div>

        <div>
            <x-input-label for="image_input" value="Image de couverture (facultatif)" />
            <input type="file" id="image_input" name="image" class="mt-1 block w-full"
                accept="image/png, image/jpeg" @change="resizeImage">
            <x-input-error class="mt-2" :messages="$errors->get('image')" />
        </div>

        <div class="relative" x-show="showDisplay">
            <img id="image_display" src="" alt="Image de couverture de la sortie"
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
            <x-primary-button>Ajouter</x-primary-button>
        </div>
    </form>
</section>

@push('scripts')
    <script>
        const MAX_WIDTH = 2560;
        const MAX_HEIGHT = 1600;
        const MIME_TYPE = "image/jpeg";
        const QUALITY = 0.8;

        function addEvent() {
            return {
                showDisplay: false,
                fileToDataUrl(event, callback) {
                    if (!event.target.files.length) return

                    let file = event.target.files[0],
                        reader = new FileReader()

                    reader.readAsDataURL(file)
                    reader.onload = e => callback(e.target.result)
                },
                removeImage: function() {
                    this.showDisplay = !(confirm('Supprimer l\'image de la sortie ?'));
                    if (!this.showDisplay) {
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
                                this.showDisplay = true;
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
