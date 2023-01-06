@php
$trips = \App\Models\Trip::all()
->sortByDesc('start_date')
->groupBy([
function ($val) {
return $val->start_date->format('Y');
},
]);
@endphp

<section class="max-w-2xl mx-auto">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Modifier l'album</h2>
        <p class="mt-1 text-sm text-gray-600">L'album sera mis à jour immédiatement.</p>
    </header>

    <form method="post" action="{{ route('albums.update', $album) }}" class="mt-6 space-y-6">
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


        @if(!$trips->isEmpty())
        <div>
            <x-input-label for="trips_input" value="Lier l'album à une ou plusieurs sorties (facultatif)" />
            <x-multi-select-input id="trips_input" name="trips[]" search="true">
                @foreach ($trips as $year => $tripPerYear)
                <optgroup label="{{ $year }}">
                    @foreach ($tripPerYear as $trip)
                    <option value="{{ $trip->id }}" {{ $album->trips->contains($trip->id) ? 'selected' : '' }}>{{
                        $trip->title }}</option>
                    @endforeach
                </optgroup>
                @endforeach
            </x-multi-select-input>
            <x-input-error class="mt-2" :messages="$errors->get('trips')" />
            @foreach($errors->get('trips.*') as $message)
            <x-input-error class="mt-2" :messages="$message" />
            @endforeach
        </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>Mettre à jour</x-primary-button>
        </div>
    </form>
</section>

@if(!$trips->isEmpty())
@push('scripts')
<script type="text/javascript">
    let select = document.getElementById('trip_input');
    let clearBtn = document.getElementById('clear_button');
    clearBtn.addEventListener('click', function () {
        select.selectedIndex = 0;
        select.dispatchEvent(new Event('change'));
    });
</script>
@endpush
@endif
