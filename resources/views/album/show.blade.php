<x-member-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between">
            <h2 class="mb-2 text-xl leading-tight text-gray-800 md:mb-0">
                <span class="font-semibold">{{ $album->title }}</span> - <span
                    class="text-lg">{{ $album->date->translatedFormat('F Y') }}</span>
            </h2>
            <div class="flex w-full justify-between xs:w-auto sm:space-x-3">
                <x-primary-link href="{{ route('photos.create', $album) }}">
                    Ajouter mes photos
                </x-primary-link>
                <div class="flex items-center">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="group">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="h-6 text-gray-900">
                                    <circle cx="12" cy="12" r="1"></circle>
                                    <circle cx="12" cy="5" r="1"></circle>
                                    <circle cx="12" cy="19" r="1"></circle>
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @can('update', $album)
                                <x-dropdown-link :href="route('albums.edit', $album)">
                                    Modifier l'album
                                </x-dropdown-link>
                            @endcan
                            @can('delete', $album)
                                <form method="post" action="{{ route('albums.destroy', $album) }}" class="w-full">
                                    @csrf
                                    @method('delete')
                                    <button class="w-full text-left">
                                        <x-dropdown-link class="text-red-600">
                                            Supprimer l'album
                                        </x-dropdown-link>
                                    </button>
                                </form>
                            @endcan
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </div>
        <hr class="mt-2 mb-4">
        <div
            class="{{ strlen($album->description) > 150 ? 'text-sm' : '' }} flex flex-wrap space-x-1 sm:space-x-5 md:flex-nowrap">
            <p class="min-w-fit font-bold">Description :</p>
            <p class="flex-grow">{{ $album->description }}</p>
        </div>
        <div class="flex flex-wrap space-x-1 sm:space-x-5 md:flex-nowrap">
            <p class="min-w-fit font-bold">Nombre de photos :</p>
            <p class="flex-grow">{{ $album->photos->count() }}</p>
        </div>
    </x-slot>

    <section class="text-gray-700">
        @if (count($photoIds) > 0)
            <livewire:lightbox />
            <div class="mx-auto max-w-screen-2xl">
                <livewire:gallery :photoIds="$photoIds" />
            </div>
        @else
            <div class="flex h-64 flex-col items-center justify-center">
                <p class="mb-5 text-xl">Aucune photo de publiée.</p>
                <x-primary-link href="{{ route('photos.create', $album) }}">
                    Ajouter mes photos
                </x-primary-link>
            </div>
        @endif
    </section>
</x-member-layout>
