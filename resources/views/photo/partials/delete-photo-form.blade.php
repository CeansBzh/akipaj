<section class="flex flex-col justify-around space-y-4 xs:flex-row xs:space-y-0">
    <header class="mx-auto flex items-center sm:mx-0">
        <h2 class="text-lg font-medium text-gray-900">Supprimer la photo</h2>
    </header>

    <x-danger-button class="mx-auto sm:mx-0" x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-photo-deletion')">Supprimer
    </x-danger-button>

    <x-modal name="confirm-photo-deletion" :show="$errors->deletePhoto->isNotEmpty()" focusable>
        <form method="post" action="{{ route('photos.destroy', $photo) }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">Êtes-vous sûr(e) de vouloir supprimer cette photo ?</h2>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Annuler
                </x-secondary-button>

                <x-danger-button class="ml-3">
                    Supprimer la photo
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
