<div class="fixed top-0 left-0 right-0 bg-gray-100 z-50 shadow-lg">
    <div class="flex justify-between p-6">
        <div class="flex space-x-8 items-center text-gray-700">
            <svg x-on:click="stopSelection" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="h-6 cursor-pointer">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
            <p class="text-lg"><span x-text="selectedImages.length"></span> photos sélectionnées</p>
            <p class="text-sm">(vous ne pouvez sélectionner que les photos vous appartenant)</p>
        </div>
        <div class="items-center pr-2">
            <svg x-on:click.prevent="$dispatch('open-modal', 'confirm-photos-deletion')" role="button"
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 cursor-pointer text-red-600">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                <line x1="10" y1="11" x2="10" y2="17"></line>
                <line x1="14" y1="11" x2="14" y2="17"></line>
            </svg>
        </div>
    </div>
</div>

<x-modal name="confirm-photos-deletion" focusable>
    <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">Êtes-vous sûr(e) de vouloir supprimer ces photos ?</h2>
        <p class="mt-1 text-sm text-gray-600">Les photos seront conservées pendant 30 jours pour permettre leur
            restauration, mais ne
            seront plus visibles par les autres utilisateurs.</p>

        <div class="mt-6 flex justify-end">
            <x-secondary-button x-on:click="$dispatch('close')">Annuler</x-secondary-button>
            <x-danger-button type="button" class="ml-3" x-on:click="deleteSelected; $dispatch('close')">
                Placer dans la corbeille
            </x-danger-button>
        </div>
    </div>
</x-modal>
