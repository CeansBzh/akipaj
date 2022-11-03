<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mon profil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto flex-col space-y-4 sm:px-6 lg:px-8">
            <div class="p-4 mb-6 text-sm text-yellow-700 bg-yellow-100 rounded-lg dark:bg-yellow-200 dark:text-yellow-800"
                role="alert">Votre inscription n'a pas encore été validée par un
                administrateur. Vous ne pouvez pas encore accéder à toutes les pages du site.
            </div>
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <livewire:confirm-delete-profile-modal />
                    <a onclick="Livewire.emit('show')" class="block cursor-pointer text-red-600">
                        Supprimer mon compte
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>