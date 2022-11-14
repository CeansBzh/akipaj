<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mon profil') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto flex-col space-y-4 sm:px-6 lg:px-8">
            @if(auth()->user()->hasRole('guest'))
            <div x-data="{ animation: false }" class="px-1">
                <div role="alert"
                    class="p-4 mb-6 text-sm text-yellow-800 bg-yellow-200 rounded-lg duration-500 relative transition ease-in-out scale-90 md:scale-100"
                    :class="animation ? '-translate-y-1 scale-100 ring-offset-2 ring ring-yellow-300' : ''"
                    x-init="$nextTick(() => {animation = true; setTimeout(() => { animation = false; }, 500);})">
                    Votre inscription n'a pas encore été validée par un administrateur. Vous ne pouvez pas encore
                    accéder à toutes les pages du site.
                </div>
            </div>
            @endif
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <x-change-password />
                </div>
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