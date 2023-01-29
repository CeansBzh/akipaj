<x-member-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between">
            <h2 class="text-2xl font-semibold leading-tight text-gray-800">
                Albums
            </h2>
            <div class="flex space-x-2">
                <x-primary-link href="{{ route('albums.create') }}">
                    Créer un album
                </x-primary-link>
                <x-secondary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'empty-albums')">
                    Voir les albums vides
                </x-secondary-button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl flex-col space-y-4 sm:px-6 lg:px-8">

            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                <livewire:album.album-list />
            </div>

        </div>
    </div>

    <x-modal name="empty-albums" focusable>
        <header class="flex items-center justify-between p-6">
            <h2 class="text-lg font-medium text-gray-900">Albums vides</h2>
            <x-secondary-button x-on:click="$dispatch('close')">
                Fermer
            </x-secondary-button>
        </header>
        <ul class="list-inside list-disc p-6">
            @forelse($emptyAlbums as $emptyAlbum)
                <li class="mb-2">
                    <a href="{{ route('albums.show', $emptyAlbum) }}"
                        class="font-medium text-sky-600 hover:underline">{{ $emptyAlbum->title }}</a>
                </li>
            @empty
                <p class="text-center">Aucun album vide</p>
            @endforelse
        </ul>
    </x-modal>
</x-member-layout>
