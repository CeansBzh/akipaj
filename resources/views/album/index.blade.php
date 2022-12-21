<x-member-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-gray-800 leading-tight text-2xl">
                Albums
            </h2>
            <x-primary-link href="{{ route('photos.create') }}">
                Ajouter mes photos
            </x-primary-link>
        </div>
    </x-slot>

    <section class="overflow-hidden text-gray-700 ">
        <div class="container px-5 py-2 mx-auto lg:pt-12 lg:px-32">
            <div class="flex flex-wrap -m-1 md:-m-2">
                @forelse($albums as $album)
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
                    <x-primary-link href="{{ route('photos.create') }}">
                        Ajouter mes photos
                    </x-primary-link>
                </div>
                @endforelse
            </div>
        </div>
    </section>
    {{ $albums->links() }}
</x-member-layout>
