@php
    // Get only the albums that have at least one photo
    $albums = $trip->albums->filter(function ($album, $key) {
        return $album->photos->count() >= 1;
    });

    $colCount = $albums->count() > 1 ? 'grid-cols-2' : 'grid-cols-1';

    switch ($albums->count()) {
        case 1:
            $colCount = 'grid-cols-1';
            break;
        case 2:
            $colCount = 'grid-cols-2';
            break;
        case 3:
            $colCount = 'grid-cols-3';
            break;
        default:
            $colCount = 'grid-cols-4';
    }
@endphp

<section class="overflow-hidden text-gray-700">
    <h2 class="text-center text-2xl font-semibold leading-tight text-gray-800">
        {{ $albums->count() > 1 ? 'Albums liés' : 'Album lié' }}
    </h2>
    <div class="container mx-auto px-2 py-2 lg:px-24 lg:pt-5">
        <div class="-m-1 grid grid-cols-2 gap-4 md:-m-2 md:{{ $colCount }}">
            @foreach ($albums as $album)
                <div class="h-56 transform p-1 transition duration-300 ease-in-out hover:scale-105 md:p-2">
                    <figure
                        class="relative h-full w-full max-w-sm cursor-pointer shadow-none transition-shadow duration-300 ease-in-out hover:shadow-lg">
                        <a href="{{ route('albums.show', $album) }}">
                            <img class="h-full w-full rounded-lg object-cover"
                                src="{{ $album->imagePath ?? $album->oldestPhoto->path }}"
                                alt="Image de couverture de l'album {{ $album->title }}">
                            <div class="absolute inset-0 rounded-lg bg-gradient-to-t from-black/30 via-black/10">
                            </div>
                            <figcaption
                                class="absolute bottom-1 text-ellipsis break-words px-1 text-center text-sm text-white sm:bottom-6 sm:px-4 sm:text-base">
                                {{ $album->title }}
                            </figcaption>
                        </a>
                    </figure>
                </div>
            @endforeach
        </div>
    </div>
</section>
