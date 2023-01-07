<div class="px-2">

    <div class="my-2 sm:mr-5">
        <x-photos.gallery-search />
    </div>

    <div class="grid grid-cols-1 gap-0.5 auto-rows-[20px] sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        @foreach ($photos as $photo)
        <div class="item h-full overflow-hidden">
            <button onclick="Livewire.emit('openPhotoLightbox', '{{ $photo->id }}')" class="block cursor-pointer">
                <img alt="{{ $photo->legend ? substr($photo->legend, 0, 140) : 'Image sans description' }}"
                    class="content object-cover h-fit w-full" src="{{ $photo->path }}">
            </button>
        </div>
        @endforeach
    </div>
    <div class="max-w-lg py-2 mx-auto md:px-0">
        {{ $photos->links() }}
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.js"></script>
<script>
    function resizeGridItem(item) {
        grid = document.getElementsByClassName("grid")[0];
        rowSpan = Math.ceil((item.querySelector('.content').getBoundingClientRect().height + 2) / (20 + 2) - 1);
        item.style.gridRowEnd = "span " + rowSpan;
    }

    function resizeAllGridItems() {
        allItems = document.getElementsByClassName('item');
        for (x = 0; x < allItems.length; x++) {
            resizeGridItem(allItems[x]);
        }
    }

    function resizeInstance(instance) {
        item = instance.elements[0];
        resizeGridItem(item);
    }

    window.onload = resizeAllGridItems();
    window.addEventListener('resize', resizeAllGridItems);

    allItems = document.getElementsByClassName('item');
    Livewire.hook('message.processed', (message, component) => {
        for (x = 0; x < allItems.length; x++) {
            imagesLoaded(allItems[x], resizeInstance);
        }
    })
</script>
@endpush
