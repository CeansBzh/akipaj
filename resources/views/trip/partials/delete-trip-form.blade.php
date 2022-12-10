<section class="flex flex-col justify-around space-y-4 xs:space-y-0 xs:flex-row">
    <header class="flex items-center mx-auto sm:mx-0">
        <h2 class="text-lg font-medium text-gray-900">Supprimer la sortie</h2>
    </header>

    <x-danger-button class="mx-auto sm:mx-0" x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-trip-deletion')">Supprimer
    </x-danger-button>

    <x-modal name="confirm-trip-deletion" :show="$errors->deleteTrip->isNotEmpty()" focusable>
        <form method="post" action="{{ route('trips.destroy', $trip) }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">Êtes-vous sûr(e) de vouloir supprimer cette sortie ?</h2>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Annuler
                </x-secondary-button>

                <x-danger-button class="ml-3">
                    Supprimer la sortie
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>