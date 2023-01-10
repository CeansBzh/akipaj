<section class="flex flex-col justify-around space-y-4 xs:space-y-0 xs:flex-row">
    <header class="flex items-center mx-auto sm:mx-0">
        <h2 class="text-lg font-medium text-gray-900">Supprimer l'album</h2>
    </header>

    @can('delete', $album)
    <x-danger-button class="mx-auto sm:mx-0" x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-album-deletion')">Supprimer
    </x-danger-button>

    <x-modal name="confirm-album-deletion" :show="$errors->deleteAlbum->isNotEmpty()" focusable>
        <form method="post" action="{{ route('albums.destroy', $album) }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">Êtes-vous sûr(e) de vouloir supprimer cet album ?</h2>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Annuler
                </x-secondary-button>

                <x-danger-button class="ml-3">
                    Supprimer l'album
                </x-danger-button>
            </div>
        </form>
    </x-modal>
    @else
    <p class="text-sm text-gray-500">L'album contient des photos ne vous appartenant pas. Supression interdite.</p>
    @endcan
</section>
