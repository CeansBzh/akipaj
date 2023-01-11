@php
$albums = $trip->albums->filter(function ($album, $key) {
    return $album->photos->count() >= 1;
});
@endphp

<section class="overflow-hidden text-gray-700">
    <h2 class="font-semibold text-gray-800 leading-tight text-center text-2xl">
        Albums liés
    </h2>
    <div class="container px-2 py-2 mx-auto lg:pt-5 lg:px-32">
        <div class="flex flex-wrap justify-center -m-1 md:-m-2">
            @foreach($albums as $album)
            <div class="flex flex-wrap w-1/2 md:w-1/3 lg:w-1/4">
                <div class="w-full max-h-56 p-1 md:p-2 transform transition duration-300 ease-in-out hover:scale-105">
                    <figure
                        class="w-full h-full relative max-w-sm cursor-pointer transition-shadow ease-in-out duration-300 shadow-none hover:shadow-lg">
                        <a href="{{ route('albums.show', $album) }}">
                            <img class="object-cover rounded-lg w-full h-full" src="{{ $album->imagePath ?? $album->oldestPhoto->path }}"
                                alt="Image de couverture de l'album {{ $album->title }}">
                            <div class="absolute rounded-lg inset-0 bg-gradient-to-t from-black/30 via-black/10">
                            </div>
                            <figcaption
                                class="absolute bottom-1 px-1 text-center text-ellipsis break-words text-sm text-white sm:text-lg sm:px-4 sm:bottom-6">
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
