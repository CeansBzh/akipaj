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

        <div class="flex space-x-2">
            <div>
                <x-input-label for="month_input" value="Mois" />
                <x-select-input id="month_input" name="month" class="mt-1 block w-full" required>
                    <option value="1" {{ old('month')==1 ? 'selected' : '' }}>Janvier</option>
                    <option value="2" {{ old('month')==2 ? 'selected' : '' }}>Février</option>
                    <option value="3" {{ old('month')==3 ? 'selected' : '' }}>Mars</option>
                    <option value="4" {{ old('month')==4 ? 'selected' : '' }}>Avril</option>
                    <option value="5" {{ old('month')==5 ? 'selected' : '' }}>Mai</option>
                    <option value="6" {{ old('month')==6 ? 'selected' : '' }}>Juin</option>
                    <option value="7" {{ old('month')==7 ? 'selected' : '' }}>Juillet</option>
                    <option value="8" {{ old('month')==8 ? 'selected' : '' }}>Août</option>
                    <option value="9" {{ old('month')==9 ? 'selected' : '' }}>Septembre</option>
                    <option value="10" {{ old('month')==10 ? 'selected' : '' }}>Octobre</option>
                    <option value="11" {{ old('month')==11 ? 'selected' : '' }}>Novembre</option>
                    <option value="12" {{ old('month')==12 ? 'selected' : '' }}>Décembre</option>
                </x-select-input>
                <x-input-error class="mt-2" :messages="$errors->get('month')" />
            </div>
            <div>
                <x-input-label for="year_input" value="Année" />
                <x-text-input id="year_input" name="year" type="number" class="mt-1 block w-full" :value="old('year')"
                    min="1900" max="{{ date('Y') + 1 }}" required />
                <x-input-error class="mt-2" :messages="$errors->get('year')" />
            </div>
        </div>

        @if(!$trips->isEmpty())
        <div>
            <x-input-label for="trips_input" value="Lier l'album à une ou plusieurs sorties (facultatif)" />
            <x-multi-select-input id="trips_input" name="trips[]" search="true">
                @foreach ($trips as $year => $tripPerYear)
                <optgroup label="{{ $year }}">
                    @foreach ($tripPerYear as $trip)
                    <option value="{{ $trip->id }}">{{ $trip->title }}</option>
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
            <x-primary-button>Ajouter l'album</x-primary-button>
        </div>
    </form>
</section>
