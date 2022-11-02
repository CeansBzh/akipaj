<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $album->title }}
            </h2>
            <p>Créé le {{ $album->created_at->format('d M Y') }}</p>
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
            <div
                class="pt-2 columns-2 {{ $photos->count() >= 3 ? 'md:columns-3' : '' }} {{ $photos->count() >= 4 ? 'lg:columns-4' : ''}}">
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