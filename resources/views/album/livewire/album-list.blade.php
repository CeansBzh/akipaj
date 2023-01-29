<section x-data="albumList()">
    @if ($albums->count() > 0)
        <div class="mb-5 w-full text-right">
            <x-select-input wire:model="sortTerm" class="text-gray-500">
                <option value="date_desc" selected>Date (décroissant)</option>
                <option value="date_asc">Date (croissant)</option>
            </x-select-input>
        </div>
    @endif

    <div class="mb-5 grid grid-cols-2 items-stretch gap-5 sm:grid-cols-3 md:grid-cols-4">
        @forelse($albums as $album)
            <figure
                class="relative h-[12rem] w-full max-w-sm cursor-pointer overflow-hidden rounded-xl shadow-none transition-shadow duration-300 ease-in-out hover:shadow-xl">
                <a href="{{ route('albums.show', $album) }}">
                    <img class="h-full w-full object-cover" src="{{ $album->imagePath ?? $album->oldestPhoto->path }}"
                        alt="Image de couverture de l'album {{ $album->title }}">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-black/10">
                    </div>
                    <figcaption
                        class="absolute bottom-1 mt-auto text-ellipsis break-words px-1 text-white sm:bottom-6 sm:px-4">
                        <p class="text-sm xs:text-base">{{ $album->title }}</p>
                        <p class="text-sm">{{ $album->photos->count() }} photos</p>
                    </figcaption>
                </a>
            </figure>
        @empty
            <div class="col-span-4 flex h-64 w-full flex-col items-center justify-center">
                <p class="mb-5 text-xl">Aucun album de publié.</p>
                <x-primary-link href="{{ route('albums.create') }}">
                    Créer un album
                </x-primary-link>
            </div>
        @endforelse
    </div>
    @if ($albums->count() > 0)
        {{ $albums->links() }}
    @endif
</section>
@push('scripts')
    <script>
        function albumList() {
            return {
                init() {

                }
            }
        }
    </script>
@endpush
