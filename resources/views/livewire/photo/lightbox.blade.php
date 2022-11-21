<x-modal name="photo-lightbox" :transparent="true" maxWidth="screen" focusable>
    @if($photo)
    <div class="flex justify-between px-5 w-full text-white fixed top-0 right-0 z-20">
        <button class="group" x-on:click="$dispatch('close')">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="h-6 drop-shadow-[0_3px_3px_rgba(0,0,0,0.8)] hover:text-gray-100 group-focus:stroke-sky-500 group-focus:motion-safe:animate-pulse">
                <path d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        @can('update', $photo)
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="group">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="h-6 drop-shadow-[0_3px_3px_rgba(0,0,0,0.8)] hover:text-gray-100 group-focus:stroke-sky-500 group-focus:motion-safe:animate-pulse">
                        <circle cx="12" cy="12" r="1"></circle>
                        <circle cx="12" cy="5" r="1"></circle>
                        <circle cx="12" cy="19" r="1"></circle>
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link :href="route('photos.edit', $photo)">
                    Gérer la photo
                </x-dropdown-link>
            </x-slot>
        </x-dropdown>
        @endcan
    </div>
    <figure class="flex flex-col h-[calc(100vh-1.5rem-45px)] mx-auto pb-3 justify-center">
        <div class="relative h-full">
            <div class="absolute h-full right-0 w-screen" x-on:click="$dispatch('close')"></div>
            <img alt="{{ $photo->legend ? substr($photo->legend, 0, 140) : 'Image sans description' }}"
                class="mx-auto object-contain mb-1 h-full absolute inset-0" src="{{ $photo->path }}">
        </div>
        <figcaption class="max-w-2xl mx-auto">
            <p class="text-white text-center mb-2">{{ $photo->title }}</p>
            @if(isset($photo->legend))<p class="text-white text-sm text-justify">{{ $photo->legend }}</p>@endif
        </figcaption>
    </figure>
    <div class="flex bg-white items-center max-w-2xl mx-auto px-5 py-2 space-x-2 rounded-xl border-t border-gray-200">
        <livewire:comments :comments="$photo->comments" :commentable="$photo" />
    </div>
    @endif
</x-modal>