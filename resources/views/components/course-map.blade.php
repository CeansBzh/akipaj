@props(['trip'])

{{--
    Parcours d'une sortie : la carte, puis le parcours écrit jour par jour, lisible même sans la carte.
    Chaque journée part du dernier point de la précédente. Les distances sont mesurées en ligne droite d'un point au suivant.
--}}

@php
    $courseDays = $trip->courseDays();
    $isSeveralDays = $courseDays->count() > 1;
    $pointNumbers = $trip->coursePoints->values()->mapWithKeys(fn($point, $index) => [$point->id => $index + 1]);
    $totalDistance = $courseDays->sum('distance');

    $formatMiles = function (float $miles): string {
        $rounded = round($miles, $miles < 10 ? 1 : 0);

        return number_format($rounded, $rounded < 10 && fmod($rounded, 1) !== 0.0 ? 1 : 0, ',', ' ') .
            ' ' .
            ($rounded < 2 ? 'mille' : 'milles');
    };
    $dayLabel = fn(array $courseDay): string => 'Jour ' .
        $courseDay['day'] .
        ' · ' .
        $courseDay['date']->isoFormat('dddd D MMMM');

    $mapDays = $courseDays
        ->map(
            fn(array $courseDay): array => [
                'day' => $courseDay['day'],
                'label' => $dayLabel($courseDay),
                'points' => $courseDay['points']
                    ->map(
                        fn($point): array => [
                            'number' => $pointNumbers[$point->id],
                            'name' => $point->name,
                            'latitude' => $point->latitude,
                            'longitude' => $point->longitude,
                        ],
                    )
                    ->all(),
            ],
        )
        ->all();
@endphp

<section aria-labelledby="trip-course-title" data-course-component x-ignore x-data="courseMap(@js($mapDays))" {{ $attributes }}>

    <div class="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start lg:gap-10">
        <div class="course-map lg:top-below-nav-6 isolate lg:sticky">
            <h2 id="trip-course-title" class="text-2xl font-bold text-gray-900">Le parcours</h2>
            @if ($totalDistance > 0)
                <p class="my-2 max-w-prose text-gray-700">
                    Environ {{ $formatMiles($totalDistance) }}
                    nautiques{{ $isSeveralDays ? ' en ' . $courseDays->count() . ' jours' : '' }},
                    mesurés en ligne droite d'un point à l'autre.
                </p>
            @endif
            <div x-ref="map" role="region" aria-label="Carte du parcours"
                class="course-map bg-primary-50 lg:top-below-nav-6 lg:h-128 isolate h-80 rounded-3xl sm:h-96 lg:sticky">
            </div>
        </div>

        <div class="flex flex-col items-start gap-4">
            <button type="button" hidden x-bind:hidden="selectedDay === null" x-on:click="showAll()"
                class="focus-visible:outline-primary-700 inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-5 py-2 font-semibold text-gray-900 ring-1 ring-inset ring-gray-300 transition-colors hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2">
                Voir tout le parcours
            </button>

            <ol class="flex w-full flex-col gap-4">
                @foreach ($courseDays as $courseDay)
                    <li class="rounded-3xl p-5 ring-1 ring-inset ring-gray-200 transition-colors"
                        x-bind:class="selectedDay === {{ $courseDay['day'] }} && 'bg-primary-50 ring-primary-200'">
                        @if ($isSeveralDays)
                            <h3 class="font-bold text-gray-900">
                                Jour {{ $courseDay['day'] }}
                                <span class="font-normal text-gray-700">·
                                    {{ $courseDay['date']->isoFormat('dddd D MMMM') }}</span>
                            </h3>
                        @endif

                        <ol @class(['flex flex-col gap-2', 'mt-3' => $isSeveralDays])>
                            @foreach ($courseDay['points'] as $point)
                                @php($isDeparture = $loop->first && !$loop->parent->first)
                                <li class="flex min-w-0 items-center gap-3">
                                    <span aria-hidden="true"
                                        @class([
                                            'flex size-8 shrink-0 items-center justify-center rounded-full text-sm font-bold',
                                            'bg-primary-700 text-white' => !$isDeparture,
                                            'bg-white text-primary-800 ring-1 ring-primary-300 ring-inset' => $isDeparture,
                                        ])>{{ $pointNumbers[$point->id] }}</span>
                                    <span class="wrap-break-word min-w-0 text-gray-900">
                                        @if ($isDeparture)
                                            <span class="text-gray-700">Départ de</span>
                                        @endif
                                        {{ $point->name }}
                                    </span>
                                </li>
                            @endforeach
                        </ol>

                        @if ($courseDay['distance'] > 0)
                            <p class="mt-3 text-gray-700">Environ {{ $formatMiles($courseDay['distance']) }}</p>
                        @endif

                        @if ($isSeveralDays)
                            <button type="button" hidden x-bind:hidden="false"
                                x-on:click="showDay({{ $courseDay['day'] }})"
                                class="text-primary-800 focus-visible:outline-primary-700 mt-4 inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-5 py-2 font-semibold ring-1 ring-inset ring-gray-300 transition-colors hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="size-5 shrink-0">
                                    <path d="M9 4 3 6.5v13.5l6-2.5 6 2.5 6-2.5V4l-6 2.5Z" />
                                    <path d="M9 4v13.5M15 6.5V20" />
                                </svg>
                                Voir ce jour sur la carte
                            </button>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>

@once
    @push('scripts')
        @vite('resources/js/course-map.js')
    @endpush
@endonce
