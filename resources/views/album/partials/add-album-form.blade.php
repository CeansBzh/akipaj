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
        <h2 class="text-lg font-medium text-gray-900">Ajouter un album</h2>
        <p class="mt-1 text-sm text-gray-600">L'album sera visible pour tous les membres, et chacun pourra y ajouter ses
            photos librement.</p>
    </header>

    <form method="post" action="{{ route('albums.store') }}" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-input-label for="title_input" value="Titre" />
            <x-text-input id="title_input" name="title" type="text" class="mt-1 block w-full"
                placeholder="Titre de l'album" :value="old('title')" maxlength="63" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('title')" />
        </div>

        <div>
            <x-input-label for="description_input" value="Description" />
            <x-textarea-input id="description_input" name="description" class="mt-1 block w-full"
                placeholder="Description de l'album" maxlength="255" required>
                {{ old('description')}}
            </x-textarea-input>
            <x-input-error class="mt-2" :messages="$errors->get('description')" />
        </div>


        @if(!$trips->isEmpty())
        <div class="relative">
            <x-input-label for="trip_input" value="Lier l'album à une sortie existante (facultatif)" />
            <x-select-input id="trip_input" name="trip" class="mt-1 block w-full" :value="old('trip')">
                <option hidden disabled selected>Liste des sorties</option>
                @foreach ($trips as $year => $tripPerYear)
                <optgroup label="{{ $year }}">
                    @foreach ($tripPerYear as $trip)
                    <option value="{{ $trip->id }}">{{ $trip->title }}</option>
                    @endforeach
                </optgroup>
                @endforeach
            </x-select-input>
            <button type="button" id="clear_button" class="w-5 h-6 absolute top-8 right-9 text-slate-500">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="m-0">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
            <x-input-error class="mt-2" :messages="$errors->get('trip')" />
        </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>Ajouter l'album</x-primary-button>
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
