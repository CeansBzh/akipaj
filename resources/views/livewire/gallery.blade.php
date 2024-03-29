<div x-data="gallery()" class="px-2">

    <div class="p-2">
        @include('livewire.partials.gallery-search-sort')
    </div>

    <div class="hidden" data-role="mass-selection-menu">
        @include('livewire.partials.gallery-mass-selection-menu')
    </div>

    <div class="grid auto-rows-[20px] grid-cols-1 gap-0.5 px-3 xs:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
        @foreach ($photos as $photo)
            @can('delete', $photo)
                <div x-on:click="imageClicked" id="photo-{{ $photo->id }}" data-role="gallery-item"
                    class="group relative h-full cursor-pointer select-none overflow-hidden">
                    <img alt="{{ $photo->legend ? substr($photo->legend, 0, 140) : 'Image sans description' }}"
                        class="content h-fit w-full object-cover transition duration-75 ease-linear"
                        src="{{ $photo->thumb_path }}" loading="lazy" width="{{ $photo->thumb_width }}"
                        height="{{ $photo->thumb_height }}" data-role="item-image">
                    <div class="absolute inset-x-0 top-0 h-16 bg-gradient-to-b from-black/50 opacity-0 group-hover:opacity-100"
                        data-role="item-top-gradient"></div>
                    <svg x-on:click="checkboxClicked" role="checkbox" aria-checked="false"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="absolute top-3 left-3 h-6 w-6 text-white opacity-0 hover:!opacity-100 group-hover:opacity-60">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
            @else
                <div data-role="gallery-item" class="relative h-full select-none overflow-hidden"
                    :class="selectedImages.length === 0 ? 'cursor-pointer' : 'cursor-default'"
                    x-on:click="openPhotoLightbox({{ $photo->id }})">
                    <img alt="{{ $photo->legend ? substr($photo->legend, 0, 140) : 'Image sans description' }}"
                        class="content h-fit w-full object-cover transition duration-75 ease-linear"
                        src="{{ $photo->thumb_path }}" loading="lazy" width="{{ $photo->thumb_width }}"
                        height="{{ $photo->thumb_height }}">
                </div>
            @endif
            @endforeach
        </div>
        <div class="mx-auto mt-4 max-w-5xl py-5 px-3">
            {{ $photos->onEachSide(2)->links('pagination::gallery-tailwind') }}
        </div>
    </div>
    @push('scripts')
        <script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.js"></script>
        <script>
            function gallery() {
                return {
                    selectedImages: [],
                    lightboxPhotoId: null,
                    checkboxes: document.querySelectorAll('svg[role=checkbox]'),
                    init() {
                        resizeAllGridItems();
                        window.addEventListener('resize', resizeAllGridItems);
                        // When a message is sent to the livewire component we reload the grid (for example when we filter the gallery)
                        Livewire.hook('message.processed', (message, component) => {
                            items = document.querySelectorAll('[data-role=gallery-item]');
                            for (x = 0; x < items.length; x++) {
                                imagesLoaded(items[x], resizeInstance);
                            }
                        })
                        // Listen for keyboard arrows navigation
                        document.addEventListener('keyup', (e) => {
                            if (e.code === "ArrowLeft") this.lightboxGoLeft()
                            else if (e.code === "ArrowRight") this.lightboxGoRight()
                        });
                    },
                    checkboxClicked: function(e) {
                        e.stopPropagation();
                        let item = e.target.closest('[data-role=gallery-item]');
                        this.toggleSelection(item);
                    },
                    imageClicked: function(e) {
                        e.preventDefault();
                        let item = e.target.closest('[data-role=gallery-item]');
                        if (this.selectedImages.length > 0) {
                            // If we have selected images, we don't open the lightbox but we toggle the selection of the image
                            this.toggleSelection(item);
                        } else {
                            // If we don't have selected images, we open the lightbox
                            let id = item.id.split('-')[1];
                            this.openPhotoLightbox(id);
                        }
                    },
                    toggleSelection: function(item) {
                        let id = item.id.split('-')[1];
                        let checkbox = item.querySelector('svg[role=checkbox]');

                        if (this.selectedImages.includes(id)) {
                            this.selectedImages = this.selectedImages.filter((item) => item !== id);
                            checkbox.setAttribute('aria-checked', 'false');
                            if (this.selectedImages.length === 0) {
                                // If the last image is unselected, we hide all the buttons and the mass selection menu
                                this.checkboxes.forEach(el => el.classList.remove('opacity-100'));
                                document.querySelector('[data-role=mass-selection-menu]').classList.add('hidden');
                            }
                        } else {
                            if (this.selectedImages.length === 0) {
                                // If the first image is selected, we show all the buttons and the mass selection menu
                                this.checkboxes.forEach(el => el.classList.add('opacity-100'));
                                document.querySelector('[data-role=mass-selection-menu]').classList.remove('hidden');
                            }
                            this.selectedImages.push(id);
                            checkbox.setAttribute('aria-checked', 'true');
                        }

                        this.toggleSelectedStyle(item);
                    },
                    stopSelection: function() {
                        this.selectedImages = [];
                        this.checkboxes.forEach(el => {
                            el.setAttribute('aria-checked', 'false');
                            el.classList.remove('opacity-100');
                        });
                        document.querySelector('[data-role=mass-selection-menu]').classList.add('hidden');
                        document.querySelectorAll('[data-role=gallery-item].selected').forEach(el => {
                            this.toggleSelectedStyle(el);
                        });
                    },
                    deleteSelected: function() {
                        Livewire.emit('deleteSelected', this.selectedImages);
                        this.stopSelection();
                    },
                    toggleSelectedStyle: function(item) {
                        let img = item.querySelector('img');
                        let gradient = item.querySelector('[data-role=item-top-gradient]');
                        let checkbox = item.querySelector('svg[role=checkbox]');

                        if (item.classList.contains('selected')) {
                            checkbox.classList.replace('text-sky-500', 'text-white');
                            checkbox.classList.remove('!opacity-100');
                            item.classList.remove('selected');
                            gradient.classList.remove('hidden');
                        } else {
                            checkbox.classList.replace('text-white', 'text-sky-500');
                            checkbox.classList.add('!opacity-100');
                            item.classList.add('selected');
                            gradient.classList.add('hidden');
                        }
                    },
                    openPhotoLightbox: function(photoId) {
                        if (this.selectedImages.length === 0) {
                            this.lightboxPhotoId = photoId;
                            Livewire.emit('openPhotoLightbox', photoId);
                        }
                    },
                    lightboxGoLeft: function() {
                        if (this.lightboxPhotoId === null) return;
                        let currentItemDisplayed = document.getElementById('photo-' + this.lightboxPhotoId);
                        let previous = currentItemDisplayed.previousElementSibling;
                        if (previous !== null) {
                            let id = previous.id.split('-')[1];
                            this.lightboxPhotoId = id;
                            Livewire.emit('loadPhoto', id);
                        }
                    },
                    lightboxGoRight: function() {
                        if (this.lightboxPhotoId === null) return;
                        let currentItemDisplayed = document.getElementById('photo-' + this.lightboxPhotoId);
                        let next = currentItemDisplayed.nextElementSibling;
                        if (next !== null) {
                            let id = next.id.split('-')[1];
                            this.lightboxPhotoId = id;
                            Livewire.emit('loadPhoto', id);
                        }
                    },
                }
            }

            // Functions to generate the masonry grid
            function resizeGridItem(item) {
                grid = document.getElementsByClassName('grid')[0];
                rowSpan = Math.ceil((item.querySelector('.content').getBoundingClientRect().height + 2) / (20 + 2) - 1);
                item.style.gridRowEnd = 'span ' + rowSpan;
            }

            function resizeAllGridItems() {
                items = document.querySelectorAll('[data-role=gallery-item]');
                for (x = 0; x < items.length; x++) {
                    resizeGridItem(items[x]);
                }
            }

            function resizeInstance(instance) {
                item = instance.elements[0];
                resizeGridItem(item);
            }
        </script>
    @endpush
