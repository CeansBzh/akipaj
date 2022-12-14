<x-member-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Toutes les photos</h2>
            <a class="m-1 px-4 py-2 text-blue-100 no-underline bg-blue-500 rounded hover:bg-blue-600 hover:underline hover:text-blue-200"
                href="{{ route('photos.create') }}">Ajouter mes photos</a>
        </div>
    </x-slot>

    <section class="overflow-hidden text-gray-700">
        <livewire:photo.lightbox />

        <div class="px-5 py-2 mx-auto md:px-12 lg:pt-5 lg:px-24">
            {{ $photos->links() }}
            <div
                class="pt-2 columns-2 {{ $photos->count() >= 3 ? 'md:columns-3' : '' }} {{ $photos->count() >= 4 ? 'lg:columns-4' : ''}}">
                @foreach ($photos as $photo)
                <button onclick="Livewire.emit('openPhotoLightbox', '{{ $photo->id }}')" class="block cursor-pointer">
                    <img alt="{{ $photo->legend ? substr($photo->legend, 0, 140) : 'Image sans description' }}"
                        class="mb-4 rounded" src="{{ $photo->path }}">
                </button>
                @endforeach
            </div>
            {{ $photos->links() }}
        </div>
    </section>
</x-member-layout>
