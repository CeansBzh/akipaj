<x-member-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $album->title }}</h2>
            <x-primary-link href="{{ route('photos.create', $album) }}">
                Ajouter mes photos
            </x-primary-link>
        </div>
    </x-slot>

    <section class="overflow-hidden text-gray-700">
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
