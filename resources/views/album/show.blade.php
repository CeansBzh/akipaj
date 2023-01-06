<x-member-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center">
            <h2 class="text-xl text-gray-800 leading-tight mb-2 md:mb-0">
                <span class="font-semibold ">{{ $album->title }}</span> - <span class="text-lg">{{
                    $album->date->translatedFormat('F Y') }}</span>
            </h2>
            <div class="flex">
                <x-primary-link href="{{ route('photos.create', $album) }}">
                    Ajouter mes photos
                </x-primary-link>
                @if($album->photos->count() === 0)
                <form method="post" action="{{ route('albums.destroy', $album) }}">
                    @csrf
                    @method('delete')

                    <x-secondary-button type="submit" class="ml-3 border-red-600 text-red-600">
                        Supprimer l'album
                    </x-secondary-button>
                </form>
                @endif
            </div>
        </div>
        <hr class="mt-2 mb-4">
        <div
            class="flex flex-wrap {{ strlen($album->description) > 150 ? 'text-sm' : '' }} sm:space-x-5 md:flex-nowrap">
            <p class="font-bold min-w-fit">Description :</p>
            <p class="flex-grow">{{ $album->description }}</p>
        </div>
    </x-slot>

    <section class="text-gray-700">
        @if($photos->first())
        <livewire:photo.lightbox />
        <livewire:photo.gallery :photoIds="$photos->pluck('id')->toArray()" />
        @else
        <div class="flex flex-col items-center justify-center h-64">
            <p class="text-xl mb-5">Aucune photo de publiée.</p>
            <x-primary-link href="{{ route('photos.create', $album) }}">
                Ajouter mes photos
            </x-primary-link>
        </div>
        @endif
    </section>
</x-member-layout>
