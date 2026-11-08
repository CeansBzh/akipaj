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
                                        <img src="{{ $user->profile_picture_path ?? Vite::asset('resources/images/default-pfp.png') }}"
                                            alt="" width="40" height="40" loading="lazy"
                                            class="size-10 shrink-0 rounded-full object-cover">
                                        <span class="truncate text-gray-900">{{ $user->name }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif
                </div>

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
                        <ul class="mt-6 grid grid-cols-2 gap-x-4 gap-y-6 md:grid-cols-3 lg:grid-cols-4">
                            @foreach ($trip->albums as $album)
                                <li class="min-w-0">
                                    <a href="{{ route('albums.show', $album) }}"
                                        class="group flex flex-col gap-3 rounded-2xl focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-primary-700">
                                        <img src="{{ $album->imagePath ?? $album->oldestPhoto?->thumb_path }}" alt=""
                                            width="400" height="300" loading="lazy" decoding="async"
                                            class="aspect-4/3 w-full rounded-2xl bg-gray-100 object-cover">
                                        <span>
                                            <span class="block font-bold break-words text-gray-900 group-hover:underline">
                                                {{ $album->title }}
                                            </span>
                                            <span class="text-gray-600">
                                                {{ $album->photos_count }} {{ $album->photos_count > 1 ? 'photos' : 'photo' }}
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
