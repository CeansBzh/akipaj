<x-member-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center">
            <h2 class="font-semibold text-gray-800 leading-tight text-2xl">
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

    <section class="overflow-hidden text-gray-700 ">
        <div class="container px-5 py-2 mx-auto lg:pt-12 lg:px-32">
            <div class="flex flex-wrap -m-1 md:-m-2">
                @forelse($albums->where('oldestPhoto', '!=', null) as $album)
                <div class="flex flex-wrap w-1/2 md:w-1/3 lg:w-1/4">
                    <div class="w-full p-1 md:p-2">
                        <figure
                            class="w-full h-full relative max-w-sm cursor-pointer transition-shadow ease-in-out duration-300 shadow-none hover:shadow-xl">
                            <a href="{{ route('albums.show', $album) }}">
                                <img class="object-cover rounded-lg w-full h-full" src="{{ $album->oldestPhoto->path }}"
                                    alt="Image de couverture de l'album {{ $album->title }}">
                                <div class="absolute rounded-lg inset-0 bg-gradient-to-t from-black/30 via-black/10">
                                </div>
                                <figcaption
                                    class="absolute bottom-1 px-1 text-center text-ellipsis break-words text-sm text-white sm:text-lg sm:px-4 sm:bottom-6">
                                    {{ $album->title }}
                                </figcaption>
                            </a>
                        </figure>
                    </div>
                </div>
                @empty
                <div class="flex flex-col items-center justify-center w-full h-64">
                    <p class="text-xl mb-5">Aucun album de publié.</p>
                    <x-primary-link href="{{ route('albums.create') }}">
                        Créer un album
                    </x-primary-link>
                </div>
                @endforelse
            </div>
        </div>
    </section>
    {{ $albums->links() }}

    <x-modal name="empty-albums" focusable>
        <header class="flex items-center justify-between p-6">
            <h2 class="text-lg font-medium text-gray-900">Albums vides</h2>
            <x-secondary-button x-on:click="$dispatch('close')">
                Fermer
            </x-secondary-button>
        </header>
        <ul class="p-6 list-disc list-inside">
            @forelse($albums->where('oldestPhoto', null) as $album)
            <li class="mb-2">
                <a href="{{ route('albums.show', $album) }}" class="font-medium text-sky-600 hover:underline">{{ $album->title }}</a>
            </li>
            @empty
                <p class="text-center">Aucun album vide</p>
            @endforelse
        </ul>
    </x-modal>
</x-member-layout>
