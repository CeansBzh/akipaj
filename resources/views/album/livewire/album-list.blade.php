<section x-data="albumList()">
    @if ($albums->count() > 0)
        <div class="w-full text-right mb-5">
            <x-select-input wire:model="sortTerm" class="text-gray-500">
                <option value="date_desc" selected>Date (décroissant)</option>
                <option value="date_asc">Date (croissant)</option>
            </x-select-input>
        </div>
    @endif

    <div class="grid grid-cols-2 items-stretch mb-5 gap-5 sm:grid-cols-3 md:grid-cols-4">
        @forelse($albums as $album)
            <figure
                class="h-[12rem] w-full relative max-w-sm cursor-pointer overflow-hidden rounded-xl transition-shadow ease-in-out duration-300 shadow-none hover:shadow-xl">
                <a href="{{ route('albums.show', $album) }}">
                    <img class="object-cover w-full h-full"
                        src="{{ $album->imagePath ?? $album->oldestPhoto->path }}"
                        alt="Image de couverture de l'album {{ $album->title }}">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/30 via-black/10">
                    </div>
                    <figcaption
                        class="absolute bottom-1 px-1 text-ellipsis break-words mt-auto text-white sm:px-4 sm:bottom-6">
                        <p class="text-sm xs:text-base">{{ $album->title }}</p>
                        <p class="text-sm">{{ $album->photos->count() }} photos</p>
                    </figcaption>
                </a>
            </figure>
        @empty
            <div class="flex flex-col items-center justify-center w-full h-64 col-span-4">
                <p class="text-xl mb-5">Aucun album de publié.</p>
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
