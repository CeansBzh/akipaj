<x-app-layout>
    <h1>Liste des albums</h1>

    <div class="block md:flex space-x-4 justify-evenly">
        @foreach($albums as $album)
        <div class="w-52 bg-white rounded-lg border border-gray-200 shadow-md dark:bg-gray-800 dark:border-gray-700">
            <a href="{{ route('albums.show', $album) }}">
                <img class="rounded-t-lg h-64 w-full object-cover" src="{{ $album->oldestPhoto->path }}" alt="" />
            </a>
            <div class="p-5 text-center">
                <a href="{{ route('albums.show', $album) }}">
                    <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $album->title }}
                    </h5>
                </a>
            </div>
        </div>
        @endforeach
    </div>

    {{ $albums->links() }}
</x-app-layout>