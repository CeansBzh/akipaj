<x-member-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $photo->title }}</h2>
    </x-slot>

    <div class="p-2">
        <img class="mx-auto cursor-zoom-in lg:max-h-[80vh]" src="{{ $photo->path }}"
            alt="{{ isset($photo->legend) ? substr($photo->legend, 0, 125) : 'Photo sans légende' }}" x-data=""
            x-on:click.prevent="$dispatch('open-lightbox', 'photo-lightbox')">
        <div class="flex bg-white shadow-lg rounded-lg relative -mt-2 mx-auto max-w-md md:max-w-2xl ">
            <div class="flex items-start px-4 py-6 w-full">
                <img class="w-12 h-12 rounded-full object-cover mr-4 shadow"
                    src="{{ $photo->user->profile_picture_path ?? Vite::asset('resources/images/default-pfp.png') }}"
                    alt="Photo de profil">
                <div class="grow">
                    <div class="flex items-center justify-between">
                        <h2
                            class="text-lg font-semibold text-gray-900 -mt-1 whitespace-nowrap truncate overflow-hidden">
                            {{ $photo->user->name }}</h2>
                        <small class="text-sm text-gray-700">Envoyée
                            {{ $photo->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="text-gray-700 text-sm">
                        @if (isset($photo->legend))
                        <p class="mt-3">
                            {{ $photo->legend }}
                        </p>
                        @endif
                        @if (isset($photo->taken_at))
                        <p class="mt-4">Prise le : {{ $photo->taken_at->format('d/m/Y') }}</p>
                        @endif
                        <p class="mt-1">Album : <a href="{{ route('albums.show', $photo->album) }}"
                                class="text-blue-500 hover:text-blue-700">{{ $photo->album->title }}</a>
                        </p>
                    </div>
                    <div class="mt-4 flex items-center">
                        <div class="flex mr-4 text-gray-700 text-sm">
                            <svg fill="none" viewBox="0 0 24 24" class="w-4 h-4 mr-1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                            </svg>
                            <span>{{ $photo->comments->count() }}</span>
                        </div>
                        <div class="flex text-gray-700 text-sm">
                            <svg fill="none" viewBox="0 0 24 24" class="w-4 h-4 mr-1" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                            <span>Partager</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto flex-col space-y-4 sm:px-6 lg:px-8">

            <div class="p-4 sm:p-8 bg-white shadow mx-auto max-w-3xl sm:rounded-lg">
                <livewire:comments :comments="$photo->comments" :commentable="$photo" />
            </div>

        </div>
    </div>

    <x-lightbox name="photo-lightbox" :photo="$photo">
        <x-slot name="image">
            <img class="max-h-screen block w-full object-contain" src="{{ $photo->path }}"
                alt="{{ isset($photo->legend) ? substr($photo->legend, 0, 125) : 'Photo sans légende' }}">
        </x-slot>

        <p class="text-white text-center text-sm">Photo par - <span class="font-bold">{{ $photo->user->name }}</span>
        </p>
        <p class="text-white text-center text-sm">{{ $photo->title }}</p>
    </x-lightbox>

</x-member-layout>
