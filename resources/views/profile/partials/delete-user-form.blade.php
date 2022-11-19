<section class="flex flex-col justify-around space-y-4 xs:space-y-0 xs:flex-row">
    <header class="flex items-center mx-auto sm:mx-0">
        <h2 class="text-lg font-medium text-gray-900">Supprimer le compte</h2>
    </header>

    <x-danger-button class="mx-auto sm:mx-0" x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">Supprimer
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900">Êtes-vous sûr(e) de vouloir supprimer votre compte ?</h2>
            <p class="mt-1 text-sm text-gray-600">Après avoir validé la suppression de votre compte nous conserverons
                ses données pendant 30 jours, délai après lequel elle seront définitivement supprimées.</p>
            <p class="mt-2 text-sm text-gray-600">Rentrez votre mot de passe ci-dessous pour confirmer la suppression :</p>

            <div class="mt-3">
                <x-input-label for="password" value="Mot de passe" class="sr-only" />
                <x-text-input id="password" name="password" type="password" class="mt-1 block w-3/4"
                    placeholder="Mot de passe" />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

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