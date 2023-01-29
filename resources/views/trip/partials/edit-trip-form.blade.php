@php
    $albums = \App\Models\Album::all()
        ->sortBy('date')
        ->groupBy([
            function ($val) {
                return $val->date->format('Y');
            },
        ]);

    $users = \App\Models\User::all();
@endphp

<section class="mx-auto max-w-2xl" x-data="editTrip()" x-init="init()">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Modifier une sortie</h2>
        <p class="mt-1 text-sm text-gray-600">La sortie sera mise à jour immédiatement.</p>
    </header>

    <form method="post" action="{{ route('trips.update', $trip) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div class="flex w-full space-x-5">
            <div class="flex-grow">
                <x-input-label for="title_input" value="Nom" />
                <x-text-input id="title_input" name="title" type="text" class="mt-1 block w-full" :value="$trip->title"
                    placeholder="WK Boat Açores" maxlength="50" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->updateTrip->get('title')" />
            </div>

            <div class="w-28">
                <x-input-label for="uuid_input" value="N° de la sortie" />
                <x-text-input id="uuid_input" name="uuid" type="text" class="mt-1 block w-full" :value="$trip->uuid"
                    maxlength="50" />
                <x-input-error class="mt-2" :messages="$errors->updateTrip->get('uuid')" />
            </div>
        </div>

        <div class="flex w-full space-x-5">
            <div>
                <x-input-label for="start_date_input" value="Date de début" />
                <x-text-input id="start_date_input" name="start_date" type="date" class="mt-1 block w-full"
                    :value="$trip->start_date->format('Y-m-d')" required />
                <x-input-error class="mt-2" :messages="$errors->updateTrip->get('start_date')" />
            </div>

            <div>
                <x-input-label for="end_date_input" value="Date de fin" />
                <x-text-input id="end_date_input" name="end_date" type="date" class="mt-1 block w-full"
                    :value="$trip->end_date->format('Y-m-d')" required />
                <x-input-error class="mt-2" :messages="$errors->updateTrip->get('end_date')" />
            </div>
        </div>

        <div>
            <x-input-label for="description_input" value="Description" />
            <x-textarea-input id="description_input" name="description" class="mt-1 block w-full"
                placeholder="Description de l'événement" maxlength="140" rows="6" required>
                {{ $trip->description }}
            </x-textarea-input>
            <x-input-error class="mt-2" :messages="$errors->updateTrip->get('description')" />
        </div>

        <hr>

        <div>
            <x-input-label for="albums_input" value="Lier un ou plusieurs albums (facultatif)" />
            <x-multi-select-input id="albums_input" name="albums[]" search="true">
                @foreach ($albums as $year => $albumPerYear)
                    <optgroup label="{{ $year }}">
                        @foreach ($albumPerYear as $album)
                            <option value="{{ $album->id }}"
                                {{ $trip->albums->contains($album->id) ? 'selected' : '' }}>{{ $album->title }}</option>
                        @endforeach
                    </optgroup>
                @endforeach
            </x-multi-select-input>
            <x-input-error class="mt-2" :messages="$errors->get('albums')" />
            @foreach ($errors->get('albums.*') as $message)
                <x-input-error class="mt-2" :messages="$message" />
            @endforeach
        </div>

        <div>
            <x-input-label for="users_input" value="Associer des utilisateurs (facultatif)" />
            <x-multi-select-input id="users_input" name="users[]" search="true">
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ $trip->users->contains($user->id) ? 'selected' : '' }}>
                        {{ $user->name }}</option>
                @endforeach
            </x-multi-select-input>
            <x-input-error class="mt-2" :messages="$errors->get('users')" />
            @foreach ($errors->get('users.*') as $message)
                <x-input-error class="mt-2" :messages="$message" />
            @endforeach
        </div>

        <div>
            <x-input-label for="image_input" value="Image de couverture (facultatif)" />
            <input id="image_input" name="image" type="file" class="mt-1 block w-full"
                accept="image/png, image/jpeg" @change="resizeImage">
            <x-input-error class="mt-2" :messages="$errors->updateTrip->get('image')" />
        </div>

        <div>
            <input x-model="imageRemoved" name="remove_image" type="checkbox" class="hidden" aria-hidden="true">
            <x-input-error class="mt-2" :messages="$errors->updateTrip->get('remove_image')" />
        </div>

        <div class="relative" x-show="!imageRemoved">
            <img id="image_display" src="{{ $trip->imagePath }}"
                alt="Image de couverture de l'événement {{ $trip->name }}"
                class="h-64 w-full rounded-xl object-cover">
            <button type="button" class="group" x-on:click.prevent="removeImage">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="absolute top-3 right-3 h-6 text-white drop-shadow-[0_3px_3px_rgba(0,0,0,0.8)] hover:text-gray-100 group-focus:stroke-sky-500 group-focus:motion-safe:animate-pulse">
                    <path d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <hr>

        <livewire:trip.boats :boats="$trip->boats->toArray()" />

        <div class="flex items-center gap-4">
            <x-primary-button>Mettre à jour</x-primary-button>
        </div>
    </form>
</section>

@push('scripts')
    <script>
        const MAX_WIDTH = 2560;
        const MAX_HEIGHT = 1600;
        const MIME_TYPE = "image/jpeg";
        const QUALITY = 0.8;

        function editTrip() {
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
