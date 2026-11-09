@php
    $isOngoing = $trip->isOngoing();
    $isFinished = $trip->isFinished();
@endphp

<x-member-layout>
    <div id="trip-item-{{ $trip->id }}" class="bg-white pb-20">
        @unless ($isFinished)
            <x-trip-to-agenda :trip="$trip" />
        @endunless

        {{-- Bannière : la photo de la sortie en grand, sans autre texte posé dessus que le bouton de retour --}}
        <div class="relative lg:mx-auto lg:max-w-6xl lg:px-8 lg:pt-6">
            <img src="{{ $trip->imagePath ?? Vite::asset('resources/images/index_2.jpg') }}" alt=""
                width="1600" height="700" fetchpriority="high" decoding="async"
                class="h-64 w-full bg-primary-100 object-cover sm:h-80 lg:h-112 lg:rounded-3xl">
            <a href="{{ route('trips.index') }}"
                class="absolute top-4 left-4 inline-flex min-h-11 items-center gap-2 rounded-full bg-white/85 py-2 pr-5 pl-3 font-semibold text-gray-900 shadow-sm backdrop-blur-md transition-colors hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white lg:top-10 lg:left-12">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                    <path d="m15 18-6-6 6-6" />
                </svg>
                Toutes les sorties
            </a>
        </div>

        {{-- Sur téléphone, le contenu remonte sur le bas de la photo comme une feuille aux coins arrondis --}}
        <div class="relative -mt-6 rounded-t-3xl bg-white lg:mt-0 lg:rounded-none">
            <div class="mx-auto max-w-6xl px-4 pt-7 sm:px-6 lg:px-8 lg:pt-10">
                <div class="grid gap-y-10 lg:grid-cols-[minmax(0,1fr)_21rem] lg:gap-x-16">
                    <header class="flex flex-col items-start gap-4">
                        <p @class([
                            'rounded-full px-3 py-1 text-sm font-bold',
                            'bg-primary-700 text-white' => $isOngoing,
                            'bg-gray-100 text-gray-700' => $isFinished,
                            'bg-primary-50 text-primary-800 ring-1 ring-primary-200 ring-inset' => !$isOngoing && !$isFinished,
                        ])>
                            <x-trip-status :trip="$trip" />
                        </p>
                        <h1 class="text-3xl leading-tight font-bold text-balance break-words text-gray-900 sm:text-4xl lg:text-5xl">
                            {{ $trip->title }}
                        </h1>
                    </header>

                    <aside class="lg:sticky lg:top-6 lg:col-start-2 lg:row-span-3 lg:row-start-1 lg:self-start">
                        @include('trip.partials.trip-facts')
                    </aside>

                    <section aria-labelledby="trip-description-title">
                        <h2 id="trip-description-title" class="text-2xl font-bold text-gray-900">À propos</h2>
                        <div class="rich-text mt-4 max-w-prose text-lg leading-relaxed text-gray-800">
                            {!! $trip->description !!}
                        </div>
                    </section>

                    @if ($trip->users->isNotEmpty())
                        <section aria-labelledby="trip-crew-title">
                            <h2 id="trip-crew-title" class="text-2xl font-bold text-gray-900">Les matelots</h2>
                            <ul class="mt-4 flex flex-wrap gap-3">
                                @foreach ($trip->users as $user)
                                    <li class="flex min-w-0 items-center gap-3 rounded-full bg-gray-100 py-1 pr-5 pl-1">
                                        <x-user-avatar :user="$user" class="size-10" />
                                        <span class="truncate text-gray-900">{{ $user->name }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </div>

                @if ($trip->coursePoints->isNotEmpty())
                    <x-course-map :trip="$trip" class="mt-16" />
                @endif

                @if ($trip->boats->isNotEmpty())
                    <section aria-labelledby="trip-boats-title" class="mt-16">
                        <h2 id="trip-boats-title" class="text-2xl font-bold text-gray-900">
                            {{ $trip->boats->count() === 1 ? 'Le bateau' : 'Les bateaux' }}
                        </h2>
                        <div class="mt-6 grid gap-12 md:grid-cols-[repeat(auto-fit,minmax(20rem,1fr))] md:gap-10">
                            @foreach ($trip->boats as $boat)
                                <x-boat-card :boat="$boat" :index="$loop->index" />
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($trip->albums->isNotEmpty())
                    <section aria-labelledby="trip-albums-title" class="mt-16">
                        <h2 id="trip-albums-title" class="text-2xl font-bold text-gray-900">Les photos</h2>
                        <ul class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($trip->albums as $album)
                                @php($cover = $album->imagePath ?? $album->oldestPhoto?->thumb_path)
                                <li class="min-w-0">
                                    {{-- Toute la carte mène à l'album ; le bouton « Voir l'album » montre qu'elle se touche --}}
                                    <a href="{{ route('albums.show', $album) }}"
                                        class="group flex h-full flex-col overflow-hidden rounded-3xl bg-gray-100 transition-colors hover:bg-gray-200/70 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary-700">
                                        @if ($cover)
                                            <img src="{{ $cover }}" alt="" width="480" height="320" loading="lazy"
                                                decoding="async" class="aspect-3/2 w-full bg-gray-200 object-cover">
                                        @else
                                            <span class="flex aspect-3/2 w-full items-center justify-center bg-gray-200 text-gray-500">
                                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="size-12">
                                                    <rect x="3" y="4" width="18" height="16" rx="2.5" />
                                                    <circle cx="9" cy="9.5" r="1.75" />
                                                    <path d="m21 15.5-4.5-4.5L7 20" />
                                                </svg>
                                            </span>
                                        @endif
                                        <span class="flex grow flex-col items-start gap-1 p-5">
                                            <span class="text-lg font-bold break-words text-gray-900">{{ $album->title }}</span>
                                            <span class="flex items-center gap-2 text-gray-700">
                                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                                                    <path d="M4 8.5A2.5 2.5 0 0 1 6.5 6h1.8l1.4-2h4.6l1.4 2h1.8A2.5 2.5 0 0 1 20 8.5v9a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5Z" />
                                                    <circle cx="12" cy="13" r="3.5" />
                                                </svg>
                                                @if ($album->photos_count === 0)
                                                    Aucune photo pour l'instant
                                                @else
                                                    {{ $album->photos_count }} {{ $album->photos_count > 1 ? 'photos' : 'photo' }}
                                                @endif
                                            </span>
                                            <span class="mt-auto pt-4">
                                                <span class="inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-5 py-2 font-semibold text-primary-800 ring-1 ring-gray-300 ring-inset">
                                                    Voir l'album
                                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                                                        <path d="M5 12h14M13 6l6 6-6 6" />
                                                    </svg>
                                                </span>
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>
        </div>
    </div>
</x-member-layout>
