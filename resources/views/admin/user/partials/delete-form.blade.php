<section class="flex flex-col justify-around space-y-4 xs:flex-row xs:space-y-0">
    <header class="mx-auto flex items-center sm:mx-0">
        <h2 class="text-lg font-medium text-gray-900">Supprimer le compte</h2>
    </header>

    <x-danger-button class="mx-auto sm:mx-0" x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">Supprimer
    </x-danger-button>

    <x-modal name="confirm-user-deletion" focusable>
        <form method="post" action="{{ route('admin.users.destroy', $user) }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">Êtes-vous sûr(e) de vouloir supprimer ce compte ?</h2>
            <p class="mt-1 text-sm text-gray-600">Après avoir validé la suppression de ce compte nous conserverons
                ses données pendant 30 jours, délai après lequel elles seront définitivement supprimées.</p>
            <p class="mt-2 text-sm text-gray-600">Rentrez votre mot de passe ci-dessous pour confirmer la suppression :
            </p>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Annuler') }}
                </x-secondary-button>

                <x-danger-button class="ml-3">
                    {{ __('Supprimer le compte') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
