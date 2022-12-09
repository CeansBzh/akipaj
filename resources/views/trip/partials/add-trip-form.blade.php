@php
$albums = \App\Models\Album::all()
->sortBy('date')
->groupBy([
function ($val) {
return $val->date->format('Y');
},
]);

$users = \App\Models\User::all()
@endphp

<section class="max-w-2xl mx-auto">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Ajouter une sortie</h2>
        <p class="mt-1 text-sm text-gray-600">La sortie sera visible à tous les membres.</p>
    </header>

    <form method="post" action="{{ route('trips.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
        @csrf

        <div>
            <x-input-label for="title_input" value="Nom" />
            <x-text-input id="title_input" name="title" type="text" class="mt-1 block w-full" :value="old('title')"
                placeholder="WK Boat Açores" maxlength="50" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('title')" />
        </div>

        <div class="flex space-x-5 w-full">
            <div>
                <x-input-label for="start_date_input" value="Date de début" />
                <x-text-input id="start_date_input" name="start_date" type="date" class="mt-1 block w-full"
                    :value="old('start_date', date('Y-m-d'))" :max="date('Y-m-d')" required />
                <x-input-error class="mt-2" :messages="$errors->get('start_date')" />
            </div>

            <div>
                <x-input-label for="end_date_input" value="Date de fin" />
                <x-text-input id="end_date_input" name="end_date" type="date" class="mt-1 block w-full"
                    :value="old('end_date', now()->addDays(10)->format('Y-m-d'))" :max="date('Y-m-d')" required />
                <x-input-error class="mt-2" :messages="$errors->get('end_date')" />
            </div>
        </div>

        <div>
            <x-input-label for="description_input" value="Description" />
            <x-textarea-input id="description_input" name="description" class="mt-1 block w-full"
                placeholder="Description de la sortie" maxlength="500" required>
                {{ old('description')}}
            </x-textarea-input>
            <x-input-error class="mt-2" :messages="$errors->get('description')" />
        </div>

        <hr>

        <div>
            <x-input-label for="albums_input" value="Lier un ou plusieurs albums (facultatif)" />
            <x-multi-select-input id="albums_input" name="albums[]" search="true">
                @foreach ($albums as $year => $albumPerYear)
                <optgroup label="{{ $year }}">
                    @foreach ($albumPerYear as $album)
                    <option value="{{ $album->id }}">{{ $album->title }}</option>
                    @endforeach
                </optgroup>
                @endforeach
            </x-multi-select-input>
            <x-input-error class="mt-2" :messages="$errors->get('albums')" />
            @foreach($errors->get('albums.*') as $message)
            <x-input-error class="mt-2" :messages="$message" />
            @endforeach
        </div>

        <div>
            <x-input-label for="users_input" value="Associer des utilisateurs (facultatif)" />
            <x-multi-select-input id="users_input" name="users[]" search="true">
                @foreach ($users as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </x-multi-select-input>
            <x-input-error class="mt-2" :messages="$errors->get('users')" />
            @foreach($errors->get('users.*') as $message)
            <x-input-error class="mt-2" :messages="$message" />
            @endforeach
        </div>

        <div>
            <x-input-label for="image_input" value="Image de couverture (facultatif)" />
            <input type="file" id="image_input" name="image" class="mt-1 block w-full" accept="image/png, image/jpeg">
            <x-input-error class="mt-2" :messages="$errors->get('image')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Ajouter</x-primary-button>
        </div>
    </form>
</section>