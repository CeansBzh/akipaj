<div class="w-full md:w-2/3 ml-auto shadow p-5 rounded-lg bg-white">
    <div class="flex items-center justify-between">
        <p class="font-medium">
            Filtres
        </p>

        <x-secondary-button class="w-min sm:w-auto" wire:click="$emit('resetFilters')">
            Réinitialiser les filtres
        </x-secondary-button>
    </div>

    <div>
        @if($users->count() > 1 || $albums->count() > 1)
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
            @if($users->count() > 1)
            <div wire:ignore>
                <x-input-label for="users_filter" value="Envoyées par" />
                <x-multi-select-input id="users_filter" name="users[]" search="true" wire:model="usersFilter">
                    @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </x-multi-select-input>
            </div>
            @endif

            @if($albums->count() > 1)
            <div class="col-span-2" wire:ignore>
                <x-input-label for="albums_filter" value="Dans les albums" />
                <x-multi-select-input id="albums_filter" name="albums[]" search="true" wire:model="albumsFilter">
                    @foreach ($albums->sort() as $year => $albumPerYear)
                    <optgroup label="{{ $year }}">
                        @foreach ($albumPerYear as $album)
                        <option value="{{ $album['id'] }}">{{ $album['title'] }}</option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </x-multi-select-input>
            </div>
            @endif
        </div>
        @else
        <p class="text-center text-gray-500 mt-4">
            Aucun filtre disponible
        </p>
        @endif
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        Livewire.on('resetFilters', () => {
            document.querySelectorAll('select + div.multiselect-dropdown').forEach((el) => {
                el.dispatchEvent(new Event('reset', { bubbles: true }));
            });
        })
    });
</script>
@endpush
