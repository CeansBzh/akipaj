<div class="px-2">

    <div class="my-2 mr-5">
        <x-photos.gallery-search />
    </div>

    <div
        class="columns-2 gap-1 [column-fill:_balance] box-border before:box-inherit after:box-inherit {{ $photos->count() >= 3 ? 'md:columns-3' : '' }} {{ $photos->count() >= 4 ? 'lg:columns-4' : ''}}">
        @foreach ($photos as $photo)
        <button onclick="Livewire.emit('openPhotoLightbox', '{{ $photo->id }}')" class="block cursor-pointer">
            <img alt="{{ $photo->legend ? substr($photo->legend, 0, 140) : 'Image sans description' }}"
                class="h-full w-full mb-1" src="{{ $photo->path }}">
        </button>
        @endforeach
    </div>
    <div class="max-w-lg py-2 mx-auto md:px-0">
        {{ $photos->links() }}
    </div>
</div>
