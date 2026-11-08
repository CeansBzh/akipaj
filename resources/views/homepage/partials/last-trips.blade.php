@php
    $trips = App\Models\Trip::finished()
        ->orderByDesc('end_date')
        ->limit(3)
        ->get();
    $defaultImage = Vite::asset('resources/images/index_2.jpg');
@endphp

<section class="mx-auto max-w-md md:max-w-none">
    @if ($trips->isEmpty())
        <div class="flex flex-col items-center gap-2 p-12 text-center">
            <p class="text-gray-600">Aucune sortie terminée pour l'instant.</p>
            @can('create', App\Models\Trip::class)
                <a href="{{ route('trips.create') }}" class="font-semibold text-primary-700 underline">Créer une sortie</a>
            @endcan
        </div>
    @else
        <ul class="flex flex-col gap-4 md:flex-row">
            {{-- La dernière sortie, en grand : le texte est posé sur la photo --}}
            @php($latestTrip = $trips->first())
            <li class="md:flex-[1.3]">
                <article
                    class="group relative isolate flex h-full min-h-96 flex-col justify-end gap-2 overflow-hidden p-6 text-white shadow-md">
                    <img src="{{ $latestTrip->imagePath ?? $defaultImage }}" alt=""
                        class="absolute inset-0 -z-10 size-full object-cover motion-safe:transition-transform motion-safe:duration-500 motion-safe:group-hover:scale-105"
                        loading="lazy" decoding="async">
                    <div class="absolute inset-0 -z-10 bg-linear-to-t from-gray-950/85 via-gray-950/40 to-transparent">
                    </div>

                    <p class="font-semibold">
                        <x-date-range :start="$latestTrip->start_date" :end="$latestTrip->end_date" />
                    </p>
                    <h3 class="text-2xl font-bold text-balance">
                        <a href="{{ route('trips.show', $latestTrip) }}"
                            class="after:absolute after:inset-0 group-hover:underline focus-visible:outline-none focus-visible:after:outline-4 focus-visible:after:-outline-offset-4 focus-visible:after:outline-primary-400">
                            {{ $latestTrip->title }}
                        </a>
                    </h3>
                    @if ($latestTrip->location)
                        <p class="text-white/90">{{ $latestTrip->location }}</p>
                    @endif
                    <p class="line-clamp-3 max-w-prose text-white/90">{{ $latestTrip->description_text }}</p>
                </article>
            </li>

            {{-- Les sorties précédentes : la photo au-dessus du texte --}}
            @foreach ($trips->slice(1) as $trip)
                <li class="md:flex-1">
                    <article class="group relative flex h-full flex-col overflow-hidden bg-white shadow-md">
                        <div class="aspect-3/2 overflow-hidden">
                            <img src="{{ $trip->imagePath ?? $defaultImage }}" alt=""
                                class="size-full object-cover motion-safe:transition-transform motion-safe:duration-500 motion-safe:group-hover:scale-105"
                                loading="lazy" decoding="async">
                        </div>
                        <div class="flex grow flex-col gap-2 p-5">
                            <p class="font-semibold text-primary-700">
                                <x-date-range :start="$trip->start_date" :end="$trip->end_date" />
                            </p>
                            <h3 class="text-xl font-bold text-balance text-gray-900">
                                <a href="{{ route('trips.show', $trip) }}"
                                    class="after:absolute after:inset-0 group-hover:underline focus-visible:outline-none focus-visible:after:outline-4 focus-visible:after:-outline-offset-4 focus-visible:after:outline-primary-400">
                                    {{ $trip->title }}
                                </a>
                            </h3>
                            @if ($trip->location)
                                <p class="text-gray-600">{{ $trip->location }}</p>
                            @endif
                            <p class="line-clamp-3 text-gray-700">{{ $trip->description_text }}</p>
                        </div>
                    </article>
                </li>
            @endforeach
        </ul>
    @endif
</section>
