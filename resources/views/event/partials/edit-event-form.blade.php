<section class="max-w-2xl mx-auto" x-data="editEvent()" x-init="init()">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Modifier un événement</h2>
        <p class="mt-1 text-sm text-gray-600">L'événement sera mis à jour immédiatement.</p>
    </header>

    <form method="post" action="{{ route('events.update', $event) }}" enctype="multipart/form-data"
        class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name_input" value="Nom" />
            <x-text-input id="name_input" name="name" type="text" class="mt-1 block w-full" :value="$event->name"
                placeholder="WK Boat Açores" maxlength="50" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->updateEvent->get('name')" />
        </div>

        <div class="flex space-x-5 w-full">
            <div>
                <x-input-label for="start_time_input" value="Date de début" />
                <x-text-input id="start_time_input" name="start_time" type="date" class="mt-1 block w-full"
                    :value="$event->start_time->format('Y-m-d')" :min="date('Y-m-d')" required />
                <x-input-error class="mt-2" :messages="$errors->updateEvent->get('start_time')" />
            </div>

            <div>
                <x-input-label for="end_time_input" value="Date de fin" />
                <x-text-input id="end_time_input" name="end_time" type="date" class="mt-1 block w-full"
                    :value="$event->end_time->format('Y-m-d')" :min="date('Y-m-d')" required />
                <x-input-error class="mt-2" :messages="$errors->updateEvent->get('end_time')" />
            </div>
        </div>

        <div>
            <x-input-label for="description_input" value="Description" />
            <x-textarea-input id="description_input" name="description" class="mt-1 block w-full"
                placeholder="Description de l'événement" maxlength="140" required>
                {{ $event->description }}
            </x-textarea-input>
            <x-input-error class="mt-2" :messages="$errors->updateEvent->get('description')" />
        </div>

        <div>
            <x-input-label for="location_input" value="Adresse (facultatif)" />
            <x-text-input id="location_input" name="location" type="text" class="mt-1 block w-full"
                :value="$event->location" placeholder="Port de plaisance de Horta, Portugal" maxlength="255" />
            <x-input-error class="mt-2" :messages="$errors->updateEvent->get('location')" />
        </div>

        <div>
            <x-input-label for="image_input" value="Image de couverture (facultatif)" />
            <input id="image_input" name="image" type="file" class="mt-1 block w-full" accept="image/png, image/jpeg"
                @change="resizeImage">
            <x-input-error class="mt-2" :messages="$errors->updateEvent->get('image')" />
        </div>

        <div>
            <input x-model="imageRemoved" name="remove_image" type="checkbox" class="hidden" aria-hidden="true">
            <x-input-error class="mt-2" :messages="$errors->updateEvent->get('remove_image')" />
        </div>

        <div class="relative" x-show="!imageRemoved">
            <img id="image_display" src="{{ $event->imagePath }}"
                alt="Image de couverture de l'événement {{ $event->name }}" class="h-64 w-full object-cover rounded-xl">
            <button class="group" x-on:click.prevent="removeImage">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="h-6 drop-shadow-[0_3px_3px_rgba(0,0,0,0.8)] hover:text-gray-100 group-focus:stroke-sky-500 group-focus:motion-safe:animate-pulse text-white absolute top-3 right-3">
                    <path d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Modifier</x-primary-button>
        </div>
    </form>
</section>


@push('scripts')
<script>
    const MAX_WIDTH = 2560;
    const MAX_HEIGHT = 1600;
    const MIME_TYPE = "image/jpeg";
    const QUALITY = 0.8;

    function editEvent() {
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
            removeImage: function () {
                this.imageRemoved = confirm('Supprimer l\'image de la sortie ?');
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
                img.onerror = function () {
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
                            dataTransfer.items.add(new File([blob], file.name, { type: MIME_TYPE }));
                            event.target.files = dataTransfer.files
                            this.fileToDataUrl(event, src => document.getElementById('image_display').src = src);
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
