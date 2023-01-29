@php
    $trips = App\Models\Trip::where('end_date', '<=', now())
        ->orderByDesc('end_date')
        ->limit(3)
        ->get();
@endphp

<section class="mx-auto max-w-3xl">
    <div class="flex justify-evenly space-x-5">
        @if ($trips->count() === 0)
            <div class="flex flex-col justify-center p-12 text-center">
                <p class="text-gray-500">Aucune sortie n'a encore été créée.</p>
                @can('create', App\Models\Trip::class)
                    <a href="{{ route('trips.create') }}" class="text-sky-400 hover:underline">Créer une sortie</a>
                @endcan
            </div>
        @else
            @if ($trips->count() >= 1)
                <div
                    class="relative h-48 w-full transform overflow-hidden rounded-3xl shadow-lg transition duration-300 ease-in-out hover:scale-105 sm:w-1/2 lg:w-1/3">
                    <img src="{{ $trips[0]->imagePath ?? Vite::asset('resources/images/index_2.jpg') }}"
                        alt="Image de couverture de la sortie {{ $trips[0]->title }}"
                        class="h-full w-full select-none object-cover">
                    <div class="absolute top-0 h-full w-full items-end justify-center">
                        <a href="{{ route('trips.show', $trips[0]) }}"
                            class="absolute inset-0 rounded-3xl p-4 text-xl font-bold text-white ring-inset ring-sky-400 focus:ring">
                            {{ $trips[0]->title }}
                        </a>
                    </div>
                </div>
            @endif
            @if ($trips->count() >= 2)
                <div
                    class="relative hidden h-48 w-1/2 transform overflow-hidden rounded-3xl shadow-lg transition duration-300 ease-in-out hover:scale-105 sm:block lg:w-1/3">
                    <img src="{{ $trips[1]->imagePath ?? Vite::asset('resources/images/index_2.jpg') }}"
                        alt="Image de couverture de la sortie {{ $trips[1]->title }}"
                        class="h-full w-full select-none object-cover">
                    <div class="absolute top-0 h-full w-full items-end justify-center">
                        <a href="{{ route('trips.show', $trips[1]) }}"
                            class="absolute inset-0 rounded-3xl p-4 text-xl font-bold text-white ring-inset ring-sky-400 focus:ring">
                            {{ $trips[1]->title }}
                        </a>
                    </div>
                </div>
            @endif
            @if ($trips->count() >= 3)
                <div
                    class="relative hidden h-48 w-1/3 transform overflow-hidden rounded-3xl shadow-lg transition duration-300 ease-in-out hover:scale-105 lg:block">
                    <img src="{{ $trips[2]->imagePath ?? Vite::asset('resources/images/index_2.jpg') }}"
                        alt="Image de couverture de la sortie {{ $trips[2]->title }}"
                        class="h-full w-full select-none object-cover">
                    <div class="absolute top-0 h-full w-full items-end justify-center">
                        <a href="{{ route('trips.show', $trips[2]) }}"
                            class="absolute inset-0 rounded-3xl p-4 text-xl font-bold text-white ring-inset ring-sky-400 focus:ring">
                            {{ $trips[2]->title }}
                        </a>
                    </div>
                </div>
            @endif
        @endif
    </div>
</section>
