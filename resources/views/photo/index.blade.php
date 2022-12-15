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
        <livewire:photo.gallery />
    </section>
</x-member-layout>
