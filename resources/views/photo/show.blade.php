<x-member-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">{{ $photo->title }}</h2>
    </x-slot>

    <div class="p-2">
        <img class="mx-auto cursor-zoom-in lg:max-h-[80vh]" src="{{ $photo->path }}"
            alt="{{ isset($photo->legend) ? substr($photo->legend, 0, 125) : 'Photo sans légende' }}"
            x-data="" x-on:click.prevent="$dispatch('open-lightbox', 'photo-lightbox')">
        <div class="relative mx-auto -mt-2 flex max-w-md rounded-lg bg-white shadow-lg md:max-w-2xl">
            <div class="flex w-full items-start px-4 py-6">
                <img class="mr-4 h-12 w-12 rounded-full object-cover shadow"
                    src="{{ $photo->user->profile_picture_path ?? Vite::asset('resources/images/default-pfp.png') }}"
                    alt="Photo de profil">
                <div class="grow">
                    <div class="flex items-center justify-between">
                        <h2
                            class="-mt-1 overflow-hidden truncate whitespace-nowrap text-lg font-semibold text-gray-900">
                            {{ $photo->user->name }}</h2>
                        <small class="text-sm text-gray-700">Envoyée
                            {{ $photo->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="text-sm text-gray-700">
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
                        <div class="mr-4 flex text-sm text-gray-700">
                            <svg fill="none" viewBox="0 0 24 24" class="mr-1 h-4 w-4" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" />
                            </svg>
                            <span>{{ $photo->comments->count() }}</span>
                        </div>
                        <div class="flex text-sm text-gray-700">
                            <svg fill="none" viewBox="0 0 24 24" class="mr-1 h-4 w-4" stroke="currentColor">
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
        <div class="mx-auto max-w-7xl flex-col space-y-4 sm:px-6 lg:px-8">

            <div class="mx-auto max-w-3xl bg-white p-4 shadow sm:rounded-lg sm:p-8">
                <livewire:comments :comments="$photo->comments" :commentable="$photo" />
            </div>

        </div>
    </div>

    <x-lightbox name="photo-lightbox" :photo="$photo">
        <x-slot name="image">
            <img class="block max-h-screen w-full object-contain" src="{{ $photo->path }}"
                alt="{{ isset($photo->legend) ? substr($photo->legend, 0, 125) : 'Photo sans légende' }}">
        </x-slot>

        <p class="text-center text-sm text-white">Photo par - <span class="font-bold">{{ $photo->user->name }}</span>
        </p>
        <p class="text-center text-sm text-white">{{ $photo->title }}</p>
    </x-lightbox>

</x-member-layout>
