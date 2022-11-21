<x-modal name="photo-lightbox" :transparent="true" maxWidth="screen" focusable>
    <div class="absolute h-full top-0 right-0">
        <div class="absolute h-full right-0 w-screen -z-10" x-on:click="$dispatch('close')"></div>
        <button class="group" x-on:click="$dispatch('close')">
            <svg class="w-8 h-8 text-gray-300 hover:text-gray-200 group-focus:stroke-sky-500 group-focus:motion-safe:animate-pulse"
                fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                stroke="currentColor">
                <path d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    @if($photo)
    <figure class="flex flex-col h-[calc(100vh-1.5rem-45px)] mx-auto pb-3 justify-center">
        <div class="relative h-full">
            <div class="absolute h-full right-0 w-screen" x-on:click="$dispatch('close')"></div>
            <img alt="{{ $photo->legend ? substr($photo->legend, 0, 140) : 'Image sans description' }}"
                class="mx-auto object-contain mb-1 h-full absolute inset-0" src="{{ $photo->path }}">
        </div>
        <figcaption class="max-w-2xl mx-auto">
            <p class="text-white text-center mb-2">{{ $photo->title }}</p>
            <p class="text-white text-sm text-justify">{{ $photo->legend }}</p>
        </figcaption>
    </figure>
    <div class="flex bg-white items-center max-w-2xl mx-auto px-5 py-2 space-x-2 rounded-xl border-t border-gray-200">
        <livewire:comments :comments="$photo->comments" :commentable="$photo" />
    </div>
    @endif
</x-modal>