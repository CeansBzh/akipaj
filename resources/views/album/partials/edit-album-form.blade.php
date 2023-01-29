@php
    $trips = \App\Models\Trip::all()
        ->sortByDesc('start_date')
        ->groupBy([
            function ($val) {
                return $val->start_date->format('Y');
            },
        ]);
    
    $month = $album->date->format('m');
@endphp

<section class="mx-auto max-w-2xl" x-data="editAlbum()" x-init="init()">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Modifier l'album</h2>
        <p class="mt-1 text-sm text-gray-600">L'album sera mis à jour immédiatement.</p>
    </header>

    <form method="post" action="{{ route('albums.update', $album) }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="title_input" value="Titre" />
            <x-text-input id="title_input" name="title" type="text" class="mt-1 block w-full"
                placeholder="Titre de l'album" :value="$album->title" maxlength="63" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->updateAlbum->get('title')" />
        </div>

        <div>
            <x-input-label for="description_input" value="Description" />
            <x-textarea-input id="description_input" name="description" class="mt-1 block w-full"
                placeholder="Description de l'album" maxlength="255" required>
                {{ $album->description }}
            </x-textarea-input>
            <x-input-error class="mt-2" :messages="$errors->updateAlbum->get('description')" />
        </div>

        <div class="flex space-x-2">
            <div>
                <x-input-label for="month_input" value="Mois" />
                <x-select-input id="month_input" name="month" class="mt-1 block w-full" required>
                    <option value="1" {{ $month == 1 ? 'selected' : '' }}>Janvier</option>
                    <option value="2" {{ $month == 2 ? 'selected' : '' }}>Février</option>
                    <option value="3" {{ $month == 3 ? 'selected' : '' }}>Mars</option>
                    <option value="4" {{ $month == 4 ? 'selected' : '' }}>Avril</option>
                    <option value="5" {{ $month == 5 ? 'selected' : '' }}>Mai</option>
                    <option value="6" {{ $month == 6 ? 'selected' : '' }}>Juin</option>
                    <option value="7" {{ $month == 7 ? 'selected' : '' }}>Juillet</option>
                    <option value="8" {{ $month == 8 ? 'selected' : '' }}>Août</option>
                    <option value="9" {{ $month == 9 ? 'selected' : '' }}>Septembre</option>
                    <option value="10" {{ $month == 10 ? 'selected' : '' }}>Octobre</option>
                    <option value="11" {{ $month == 11 ? 'selected' : '' }}>Novembre</option>
                    <option value="12" {{ $month == 12 ? 'selected' : '' }}>Décembre</option>
                </x-select-input>
                <x-input-error class="mt-2" :messages="$errors->updateAlbum->get('month')" />
            </div>
            <div>
                <x-input-label for="year_input" value="Année" />
                <x-text-input id="year_input" name="year" type="number" class="mt-1 block w-full" :value="$album->date->format('Y')"
                    min="1900" max="{{ date('Y') + 1 }}" required />
                <x-input-error class="mt-2" :messages="$errors->updateAlbum->get('year')" />
            </div>
        </div>

        <div>
            <x-input-label for="image_input" value="Image de couverture (facultatif)" />
            <input id="image_input" name="image" type="file" class="mt-1 block w-full"
                accept="image/png, image/jpeg" @change="resizeImage">
            <x-input-error class="mt-2" :messages="$errors->updateAlbum->get('image')" />
        </div>

        <div>
            <input x-model="imageRemoved" name="remove_image" type="checkbox" class="hidden" aria-hidden="true">
            <x-input-error class="mt-2" :messages="$errors->updateAlbum->get('remove_image')" />
        </div>

        <div class="relative w-fit" x-show="!imageRemoved">
            <img id="image_display" src="{{ $album->imagePath }}"
                alt="Image de couverture de l'événement {{ $album->title }}"
                class="h-[250px] w-[250px] rounded-xl object-cover">
            <button type="button" class="group" x-on:click.prevent="removeImage">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="absolute top-3 right-3 h-6 text-white drop-shadow-[0_3px_3px_rgba(0,0,0,0.8)] hover:text-gray-100 group-focus:stroke-sky-500 group-focus:motion-safe:animate-pulse">
                    <path d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        @if (!$trips->isEmpty())
            <div>
                <x-input-label for="trips_input" value="Lier l'album à une ou plusieurs sorties (facultatif)" />
                <x-multi-select-input id="trips_input" name="trips[]" search="true">
                    @foreach ($trips as $year => $tripPerYear)
                        <optgroup label="{{ $year }}">
                            @foreach ($tripPerYear as $trip)
                                <option value="{{ $trip->id }}"
                                    {{ $album->trips->contains($trip->id) ? 'selected' : '' }}>{{ $trip->title }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </x-multi-select-input>
                <x-input-error class="mt-2" :messages="$errors->get('trips')" />
                @foreach ($errors->get('trips.*') as $message)
                    <x-input-error class="mt-2" :messages="$message" />
                @endforeach
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>Mettre à jour</x-primary-button>
        </div>
    </form>
</section>
@push('scripts')
    <script>
        const MAX_WIDTH = 250;
        const MAX_HEIGHT = 250;
        const MIME_TYPE = "image/jpeg";
        const QUALITY = 0.7;

        function editAlbum() {
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
                    this.imageRemoved = confirm('Supprimer la couverture de l\'album ?');
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
                    let ratio = Math.max(maxWidth / img.naturalWidth, maxHeight / img.naturalHeight);
                    return [img.naturalWidth * ratio, img.naturalHeight * ratio];
                }
            }
        }
    </script>
@endpush
