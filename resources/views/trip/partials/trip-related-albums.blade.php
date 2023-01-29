@php
    $albums = $trip->albums->filter(function ($album, $key) {
        return $album->photos->count() >= 1;
    });
@endphp

<section class="overflow-hidden text-gray-700">
    <h2 class="text-center text-2xl font-semibold leading-tight text-gray-800">
        Albums liés
    </h2>
    <div class="container mx-auto px-2 py-2 lg:px-32 lg:pt-5">
        <div class="-m-1 flex flex-wrap justify-center md:-m-2">
            @foreach ($albums as $album)
                <div class="flex w-1/2 flex-wrap md:w-1/3 lg:w-1/4">
                    <div
                        class="max-h-56 w-full transform p-1 transition duration-300 ease-in-out hover:scale-105 md:p-2">
                        <figure
                            class="relative h-full w-full max-w-sm cursor-pointer shadow-none transition-shadow duration-300 ease-in-out hover:shadow-lg">
                            <a href="{{ route('albums.show', $album) }}">
                                <img class="h-full w-full rounded-lg object-cover"
                                    src="{{ $album->imagePath ?? $album->oldestPhoto->path }}"
                                    alt="Image de couverture de l'album {{ $album->title }}">
                                <div class="absolute inset-0 rounded-lg bg-gradient-to-t from-black/30 via-black/10">
                                </div>
                                <figcaption
                                    class="absolute bottom-1 text-ellipsis break-words px-1 text-center text-sm text-white sm:bottom-6 sm:px-4 sm:text-lg">
                                    {{ $album->title }}
                                </figcaption>
                            </a>
                        </figure>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
