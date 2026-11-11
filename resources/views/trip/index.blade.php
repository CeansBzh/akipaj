{{--
    Les sorties du club, de la plus récente à la plus ancienne, rangées par année, sur une mer dessinée au trait
    qui reste en fond pendant la lecture. Sur grand écran, la frise à gauche montre toutes les années, suit la lecture
    et mène à une année ou à une sortie. Sur téléphone, les années se choisissent en haut de la liste et le titre
    de l'année reste affiché pendant qu'on lit ses sorties.
    Le repère « Aujourd'hui » sépare les sorties à venir ou en cours des sorties passées.
--}}

@php
    $today = today();
    $todayIsPlaced = false;
@endphp

<x-member-layout>
    <div class="bg-primary-50 relative pb-24">
        {{-- La mer en fond de page : elle reste à l'écran pendant que les cartes défilent par-dessus --}}
        <div aria-hidden="true" class="pointer-events-none sticky top-0 mb-[-100dvh] h-dvh overflow-hidden">
            <x-sea-swell class="text-primary-900/25 absolute inset-x-0 bottom-0 h-[72%]" />
        </div>

        <div class="relative mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8 lg:pt-12">
            @if ($trips->isEmpty())
                <p class="text-center text-lg text-gray-700">Aucune sortie pour l'instant.</p>
                @can('create', App\Models\Trip::class)
                    <p class="mt-6 text-center">@include('trip.partials.new-trip-link')</p>
                @endcan
            @else
                <div
                    class="lg:grid lg:grid-cols-[14rem_minmax(0,1fr)] lg:gap-x-8 xl:grid-cols-[14rem_minmax(0,44rem)_14rem] xl:justify-center 2xl:grid-cols-[16rem_minmax(0,52rem)_16rem] 2xl:gap-x-12">
                    <x-trip-timeline :trips="$trips" class="hidden lg:block" />

                    <div class="w-full">
                        <div class="flex flex-wrap items-center justify-between gap-x-6 gap-y-4">
                            <h1 class="text-4xl font-bold text-gray-900 sm:text-5xl">Les sorties</h1>
                            @can('create', App\Models\Trip::class)
                                @include('trip.partials.new-trip-link')
                            @endcan
                            {{--
                                Sur téléphone, les années se choisissent ici, sur une seule ligne qui défile à l'horizontale
                                jusqu'aux bords de l'écran ; sur grand écran, dans la frise
                            --}}
                            <nav aria-label="Années" class="w-full lg:hidden">
                                <ul
                                    class="-mx-4 flex scroll-px-4 gap-2 overflow-x-auto overscroll-x-contain px-4 pb-3 pt-1 sm:-mx-6 sm:scroll-px-6 sm:px-6">
                                    @foreach ($tripsByYear as $year => $yearTrips)
                                        <li class="shrink-0">
                                            <a href="#annee-{{ $year }}"
                                                class="hover:bg-primary-100 focus-visible:outline-primary-700 inline-flex min-h-11 items-center rounded-full bg-white px-4 py-2 font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-200 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2">
                                                {{ $year }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            </nav>
                        </div>

                        @foreach ($tripsByYear as $year => $yearTrips)
                            @if (!$todayIsPlaced && $today->year > $year)
                                @include('trip.partials.today-divider', ['class' => 'mt-14 lg:mt-20'])
                                @php($todayIsPlaced = true)
                            @endif

                            <section id="annee-{{ $year }}" data-timeline-anchor="year-{{ $year }}"
                                aria-labelledby="annee-{{ $year }}-titre"
                                class="mt-10 scroll-mt-4 lg:mt-20 lg:first-of-type:mt-12">
                                {{-- Sur téléphone, le titre de l'année reste en haut de l'écran pendant qu'on lit ses sorties --}}
                                <h2 id="annee-{{ $year }}-titre"
                                    class="top-below-nav-0 bg-primary-50/90 sticky z-20 -mx-4 flex items-baseline gap-3 px-4 py-3 backdrop-blur-md sm:-mx-6 sm:px-6 lg:static lg:mx-0 lg:p-0 lg:backdrop-blur-none">
                                    <span
                                        class="text-4xl font-bold text-gray-900 lg:text-7xl">{{ $year }}</span>
                                    <span class="font-sans text-gray-700">
                                        {{ $yearTrips->count() }} {{ $yearTrips->count() > 1 ? 'sorties' : 'sortie' }}
                                    </span>
                                </h2>

                                <div class="mt-6 flex flex-col gap-10 lg:mt-10 lg:gap-16">
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
