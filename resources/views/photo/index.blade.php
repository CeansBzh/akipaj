<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Toutes les photos') }}
            </h2>
            <a class="m-1 px-4 py-2 text-blue-100 no-underline bg-blue-500 rounded hover:bg-blue-600 hover:underline hover:text-blue-200"
                href="{{ route('photos.create') }}">Ajouter mes photos</a>
        </div>
    </x-slot>

    <livewire:photo-modal />

    @if(Session::has('success'))
    <div class="text-green-600">
        {{ Session::get('success') }}
    </div>
    @endif

    <section class="overflow-hidden text-gray-700">
        <div class="px-5 py-2 mx-auto md:px-12 lg:pt-5 lg:px-24">
            {{ $photos->links() }}
            <div class="pt-2 columns-2 md:columns-3 lg:columns-4">
                @foreach ($photos as $photo)
                <a onclick="Livewire.emit('create', '{{ $photo->id }}')" class="block cursor-pointer">
                    <img alt="{{ $photo->legend }}" class="mb-4 rounded" src="{{ $photo->path }}">
                </a>
                @endforeach
            </div>
            {{ $photos->links() }}
        </div>
    </section>

</x-app-layout>