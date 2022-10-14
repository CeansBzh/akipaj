<x-app-layout>
    <h1 class="text-center">{{ $album->title }}</h1>

    @foreach($album->photos as $photo)
    <div class="w-52 bg-white rounded-lg border border-gray-200 shadow-md dark:bg-gray-800 dark:border-gray-700">
        <a href="{{ route('photos.show', $photo) }}">
            <img class="rounded-t-lg h-64 w-full object-cover" src="{{ $photo->path }}" alt="" />
        </a>
        <div class="p-5 text-center">
            <a href="{{ route('photos.show', $photo) }}">
                <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $photo->title }}
                </h5>
            </a>
        </div>
    </div>
    @endforeach
</x-app-layout>