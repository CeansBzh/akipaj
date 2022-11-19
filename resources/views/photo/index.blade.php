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
                @php
                $uniqueId = uniqid();
                @endphp
                <button x-data="" x-on:click.prevent="$dispatch('open-modal', 'photo-modal-{{ $uniqueId }}')" class="block cursor-pointer">
                    <img alt="{{ $photo->legend }}" class="mb-4 rounded" src="{{ $photo->path }}">
                </button>

                <x-modal name="photo-modal-{{ $uniqueId }}" :show="false">
                    <h3 class="text-xl text-center font-medium text-gray-900">{{ $photo->title }}</h3>

                    <div class="p-3 space-y-2">
                        <img alt="{{ $photo->legend }}" class="mx-auto object-contain max-h-[80vh]" src="{{ $photo->path }}">
                        <p class="text-sm font-light">{{ $photo->legend }}</p>
                    </div>
                    <div class="flex items-center px-5 py-2 space-x-2 rounded-b border-t border-gray-200">
                        <livewire:comments :comments="$photo->comments" :commentable="$photo" />
                    </div>
                </x-modal>
                @endforeach
            </div>
            {{ $photos->links() }}
        </div>
    </section>

</x-app-layout>