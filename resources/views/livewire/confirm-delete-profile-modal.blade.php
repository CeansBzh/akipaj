<div>
    <x-modal wire:model="show">
        <x-slot name="title">
            <h3 class="text-lg font-medium text-gray-900 mr-2">Êtes-vous sûr de vouloir supprimer votre compte ?</h3>
        </x-slot>

        <div class="p-3 space-y-2 max-w-fit">
            <p>Après avoir validé la suppression votre compte et ses données seront sauvegardés pendant 30 jours.</p>
            <form wire:submit.prevent="delete">
                <label class="font-semibold" for="password">Saisir votre mot de passe pour valider la suppression de votre compte :</label>
                <input type="password" id="password" name="password" class="w-full" placeholder="Mot de passe"
                    wire:model="password" />
                @error('password') <div class="text-red-600">{{ $message }}</div> @enderror
                <button type="submit"
                    class="w-full mt-2 py-2 px-4 text-sm text-center text-white bg-red-700 rounded focus:ring-4 focus:ring-blue-200 hover:bg-red-600">
                    Envoyer
                </button>
            </form>
        </div>
    </x-modal>
</div>