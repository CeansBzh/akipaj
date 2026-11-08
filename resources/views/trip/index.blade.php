{{--
    Les sorties du club, de la plus récente à la plus ancienne, rangées par année.
    Sur grand écran, la frise à gauche suit la lecture et mène à une année ou à une sortie ;
    sur téléphone, les années se choisissent en haut de la liste et leur titre reste affiché pendant la lecture.
    Le repère « Aujourd'hui » sépare les sorties à venir ou en cours des sorties passées.
--}}

@php
    $today = today();
    $todayIsPlaced = false;
@endphp

<x-member-layout>
    <div class="bg-white pb-24">
        <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
            {{-- La houle prend la place sous le texte, sans jamais passer derrière --}}
            <header class="flex flex-col overflow-hidden rounded-3xl bg-primary-50">
                <div class="flex flex-col items-start gap-4 p-6 pb-2 sm:p-10 sm:pb-2 lg:p-14 lg:pb-2">
                    <h1 class="text-4xl font-bold text-gray-900 sm:text-5xl lg:text-6xl">Les sorties</h1>
                    @if ($trips->isNotEmpty())
                        <p class="max-w-prose text-lg text-gray-700">
                            {{ $trips->count() }} {{ $trips->count() > 1 ? 'sorties' : 'sortie' }}
                            depuis {{ $trips->last()->start_date->year }}, des plus récentes aux plus anciennes.
                        </p>
                    @endif
                    @can('create', App\Models\Trip::class)
                        <a href="{{ route('trips.create') }}"
                            class="inline-flex min-h-11 items-center gap-2 rounded-full bg-primary-700 px-5 py-2 font-semibold text-white transition-colors hover:bg-primary-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            Nouvelle sortie
                        </a>
                    @endcan
                </div>
                <x-sea-swell class="h-40 w-full fill-primary-50 text-primary-900/35 sm:h-52 lg:h-72" />
            </header>

            @if ($trips->isEmpty())
                <p class="mt-12 text-center text-lg text-gray-700">Aucune sortie pour l'instant.</p>
            @else
                <div class="mt-12 lg:mt-16 lg:grid lg:grid-cols-[15rem_minmax(0,1fr)] lg:gap-x-12 xl:grid-cols-[16rem_minmax(0,42rem)_16rem] xl:justify-between">
                    <x-trip-timeline :trips="$trips" class="hidden lg:block" />

                    <div class="mx-auto w-full max-w-2xl">
                        <nav aria-label="Années" class="lg:hidden">
                            <ul class="flex flex-wrap gap-2">
                                @foreach ($tripsByYear->keys() as $year)
                                    <li>
                                        <a href="#annee-{{ $year }}"
                                            class="inline-flex min-h-11 items-center rounded-full bg-white px-4 py-2 font-semibold text-gray-900 ring-1 ring-gray-300 ring-inset hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                                            {{ $year }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </nav>

                        @foreach ($tripsByYear as $year => $yearTrips)
                            @if (!$todayIsPlaced && $today->year > $year)
                                @include('trip.partials.today-divider', ['class' => 'mt-14 lg:mt-20'])
                                @php($todayIsPlaced = true)
                            @endif

                            <section id="annee-{{ $year }}" data-timeline-anchor="year-{{ $year }}"
                                aria-labelledby="annee-{{ $year }}-titre" class="mt-10 scroll-mt-4 first-of-type:mt-8 lg:mt-20 lg:first-of-type:mt-0">
                                {{-- Sur téléphone, le titre de l'année reste en haut de l'écran pendant qu'on lit ses sorties --}}
                                <h2 id="annee-{{ $year }}-titre"
                                    class="sticky top-0 z-20 -mx-4 flex items-baseline gap-3 bg-white/90 px-4 py-3 backdrop-blur-md sm:-mx-6 sm:px-6 lg:static lg:mx-0 lg:bg-transparent lg:p-0 lg:backdrop-blur-none">
                                    <span class="text-4xl font-bold text-gray-900 lg:text-7xl">{{ $year }}</span>
                                    <span class="font-sans text-gray-600">
                                        {{ $yearTrips->count() }} {{ $yearTrips->count() > 1 ? 'sorties' : 'sortie' }}
                                    </span>
                                </h2>

                                <div class="mt-6 flex flex-col gap-14 lg:mt-10 lg:gap-20">
                                    @foreach ($yearTrips as $trip)
                                        @if (!$todayIsPlaced && $trip->start_date->lte($today))
                                            @include('trip.partials.today-divider')
                                            @php($todayIsPlaced = true)
                                        @endif
                                        <x-trip-entry :trip="$trip" />
                                    @endforeach
                                </div>
                            </section>
                        @endforeach

                        @unless ($todayIsPlaced)
                            @include('trip.partials.today-divider', ['class' => 'mt-14 lg:mt-20'])
                        @endunless
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-member-layout>
