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

<div class="w-full md:w-2/3 ml-auto shadow p-5 rounded-lg bg-white">
    <div class="flex items-center justify-between">
        <p class="font-medium">
            Filtres
        </p>

        <x-secondary-button class="w-min sm:w-auto" wire:click="resetFilters">
            Réinitialiser les filtres
        </x-secondary-button>
    </div>

    <div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
            <div wire:ignore>
                <x-input-label for="users_input" value="Envoyées par" />
                <x-multi-select-input id="users_input" name="users[]" search="true" wire:model="usersFilter">
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </x-multi-select-input>
            </div>

            <div class="col-span-2" wire:ignore>
                <x-input-label for="albums_input" value="Dans les albums" />
                <x-multi-select-input id="albums_input" name="albums[]" search="true" wire:model="albumsFilter">
                    @foreach ($albums as $year => $albumPerYear)
                    <optgroup label="{{ $year }}">
                        @foreach ($albumPerYear as $album)
                        <option value="{{ $album->id }}">{{ $album->title }}</option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </x-multi-select-input>
            </div>
        </div>
    </div>
</div>
