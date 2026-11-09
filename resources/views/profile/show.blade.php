{{--
    Profil d'un matelot. En tête, la photo de sa dernière sortie (ou la mer au trait), sa photo de profil, son nom,
    sa ville et quelques chiffres. Puis, par ordre d'importance : sa prochaine sortie, son carnet de bord (les sorties
    faites, rangées par année), ses dernières photos avec le lien vers la galerie filtrée sur lui, et les commentaires.
    Sur grand écran, la prochaine sortie et les photos passent dans une colonne à droite du carnet de bord.
--}}

<x-member-layout>
    <div class="bg-white pb-20">
        {{-- Bannière : la photo de sa dernière sortie, ou la mer dessinée au trait s'il n'en a pas encore fait --}}
        <div class="relative lg:mx-auto lg:max-w-6xl lg:px-8 lg:pt-6">
            @if ($coverImage)
                <img src="{{ $coverImage }}" alt="" width="1600" height="500" fetchpriority="high" decoding="async"
                    class="h-44 w-full bg-primary-100 object-cover sm:h-60 lg:h-72 lg:rounded-3xl">
            @else
                <div class="relative h-44 overflow-hidden bg-primary-100 sm:h-60 lg:h-72 lg:rounded-3xl">
                    <x-sea-swell class="absolute inset-x-0 bottom-0 h-4/5 text-primary-900/20" />
                </div>
            @endif
            @unless (auth()->user()->isGuest())
                <a href="{{ route('profile.index') }}"
                    class="absolute top-4 left-4 inline-flex min-h-11 items-center gap-2 rounded-full bg-white/85 py-2 pr-5 pl-3 font-semibold text-gray-900 shadow-sm backdrop-blur-md transition-colors hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white lg:top-10 lg:left-12">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                        <path d="m15 18-6-6 6-6" />
                    </svg>
                    Tous les matelots
                </a>
            @endunless
        </div>

        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <header class="relative flex flex-col items-start gap-4 sm:flex-row sm:gap-6 lg:px-8">
                <x-user-avatar :user="$user" class="-mt-14 size-28 shadow-lg ring-4 ring-white sm:-mt-20 sm:size-40" />
                <div class="min-w-0 flex-1 sm:pt-5">
                    <h1 class="text-3xl leading-tight font-bold break-words text-gray-900 sm:text-4xl">{{ $user->name }}</h1>
                    <div class="mt-2 flex flex-wrap items-center gap-x-5 gap-y-2">
                        @if ($user->city)
                            <p class="inline-flex items-center gap-1.5 text-lg text-gray-700">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                                    <path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" />
                                    <circle cx="12" cy="9.5" r="2.5" />
                                </svg>
                                <span class="sr-only">Ville :</span>
                                {{ $user->city }}
                            </p>
                        @endif
                        @if ($isOwnProfile)
                            <a href="{{ route('settings.edit') }}"
                                class="inline-flex min-h-11 items-center gap-2 rounded-full px-4 py-2 font-semibold text-primary-800 ring-1 ring-gray-300 ring-inset transition-colors hover:bg-primary-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4 shrink-0">
                                    <path d="M4 20h4L19 9a2.8 2.8 0 0 0-4-4L4 16v4Z" />
                                    <path d="m13.5 6.5 4 4" />
                                </svg>
                                Modifier mon profil
                            </a>
                        @endif
                    </div>
                </div>
            </header>

            <dl class="mt-8 grid max-w-lg grid-cols-3 gap-2 sm:gap-3 lg:ml-8">
                @foreach ([
                    [$tripCount, $tripCount > 1 ? 'Sorties' : 'Sortie'],
                    [$daysAboard, $daysAboard > 1 ? 'Jours à bord' : 'Jour à bord'],
                    [$user->photos_count, $user->photos_count > 1 ? 'Photos' : 'Photo'],
                ] as [$figure, $label])
                    <div class="flex flex-col-reverse items-center rounded-2xl bg-primary-50 px-2 py-3 text-center">
                        <dt class="text-sm leading-tight text-gray-700">{{ $label }}</dt>
                        <dd class="text-2xl font-bold text-primary-800">{{ $figure }}</dd>
                    </div>
                @endforeach
            </dl>

            @if ($isOwnProfile && $user->isGuest())
                <p class="mt-8 max-w-prose rounded-2xl bg-yellow-50 px-5 py-4 text-yellow-900 ring-1 ring-yellow-200 ring-inset">
                    Votre inscription n'a pas encore été validée par un administrateur.
                    En attendant, certaines pages du site ne vous sont pas encore ouvertes.
                </p>
            @endif

            <div class="mt-14 grid gap-y-14 lg:grid-cols-[minmax(0,1fr)_22rem] lg:grid-rows-[auto_1fr_auto] lg:gap-x-16">
                @if ($nextTrip)
                    <section aria-labelledby="next-trip-title" class="lg:col-start-2 lg:row-start-1">
                        <h2 id="next-trip-title" class="text-2xl font-bold text-gray-900">Prochaine sortie</h2>
                        {{-- La carte prend toute la hauteur de son parent : elle ne doit pas prendre celle de la section --}}
                        <div class="mt-4">
                            <x-trip-card :trip="$nextTrip" />
                        </div>
                    </section>
                @endif

                <section aria-labelledby="logbook-title" class="lg:col-start-1 lg:row-span-2 lg:row-start-1">
                    <h2 id="logbook-title" class="text-2xl font-bold text-gray-900">Carnet de bord</h2>
                    @if ($tripsByYear->isEmpty())
                        <p class="mt-4 max-w-prose rounded-3xl bg-gray-50 px-5 py-6 text-gray-700">
                            {{ $isOwnProfile ? 'Vous n\'avez' : $user->name . ' n\'a' }} pas encore fait de sortie avec le club.
                        </p>
                    @else
                        <p class="mt-1 text-gray-700">
                            {{ $tripCount }} {{ $tripCount > 1 ? 'sorties' : 'sortie' }}, de la plus récente à la plus ancienne.
                        </p>

                        <div class="mt-6 flex flex-col gap-10">
                            @foreach ($tripsByYear as $year => $yearTrips)
                                <section aria-labelledby="logbook-{{ $year }}">
                                    <h3 id="logbook-{{ $year }}" class="flex items-baseline gap-3 border-b border-gray-200 pb-2">
                                        <span class="text-2xl font-bold text-gray-900">{{ $year }}</span>
                                        <span class="font-sans text-gray-700">
                                            {{ $yearTrips->count() }} {{ $yearTrips->count() > 1 ? 'sorties' : 'sortie' }}
                                        </span>
                                    </h3>
                                    <ol class="mt-2 flex flex-col gap-1">
                                        @foreach ($yearTrips as $trip)
                                            <li>
                                                <a href="{{ route('trips.show', $trip) }}"
                                                    class="group -mx-3 flex items-center gap-4 rounded-2xl px-3 py-3 transition-colors hover:bg-primary-50 focus-visible:outline-2 focus-visible:outline-primary-700">
                                                    <span aria-hidden="true"
                                                        class="flex w-20 shrink-0 flex-col items-center rounded-2xl bg-primary-50 py-2 leading-none text-primary-800 transition-colors group-hover:bg-white">
                                                        <span class="text-2xl font-bold">{{ $trip->start_date->isoFormat('D') }}</span>
                                                        <span class="mt-1 text-sm">{{ $trip->start_date->isoFormat('MMMM') }}</span>
                                                    </span>
                                                    <span class="min-w-0 flex-1">
                                                        <span class="block text-lg leading-snug font-bold break-words text-gray-900 group-hover:underline">
                                                            {{ $trip->title }}
                                                        </span>
                                                        <span class="mt-0.5 block text-gray-700">
                                                            <x-date-range :start="$trip->start_date" :end="$trip->end_date" />
                                                        </span>
                                                        @if ($trip->location)
                                                            <span class="mt-0.5 flex items-start gap-1.5 text-gray-700">
                                                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                                    stroke-linejoin="round" class="mt-1 size-4 shrink-0">
                                                                    <path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" />
                                                                    <circle cx="12" cy="9.5" r="2.5" />
                                                                </svg>
                                                                <span class="sr-only">Lieu :</span>
                                                                <span class="min-w-0 break-words">{{ $trip->location }}</span>
                                                            </span>
                                                        @endif
                                                    </span>
                                                    @if ($trip->isOngoing())
                                                        <span class="shrink-0 rounded-full bg-primary-700 px-3 py-1 text-sm font-bold text-white">
                                                            En cours
                                                        </span>
                                                    @endif
                                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                        class="size-5 shrink-0 text-gray-500 transition-transform motion-safe:group-hover:translate-x-0.5">
                                                        <path d="m9 18 6-6-6-6" />
                                                    </svg>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ol>
                                </section>
                            @endforeach
                        </div>
                    @endif
                </section>

                <section aria-labelledby="photos-title" @class([
                    'lg:col-start-2 lg:self-start',
                    'lg:row-start-2' => $nextTrip,
                    'lg:row-start-1' => !$nextTrip,
                ])>
                    <h2 id="photos-title" class="text-2xl font-bold text-gray-900">{{ $isOwnProfile ? 'Vos photos' : 'Ses photos' }}</h2>
                    @if ($latestPhotos->isEmpty())
                        <p class="mt-4 max-w-prose rounded-3xl bg-gray-50 px-5 py-6 text-gray-700">
                            {{ $isOwnProfile ? 'Vous n\'avez' : $user->name . ' n\'a' }} pas encore partagé de photo.
                        </p>
                    @else
                        <ul class="mt-4 grid grid-cols-3 gap-1.5 overflow-hidden rounded-3xl sm:grid-cols-6 lg:grid-cols-3">
                            @foreach ($latestPhotos as $photo)
                                <li>
                                    <a href="{{ route('photos.show', $photo) }}"
                                        class="block focus-visible:outline-4 focus-visible:-outline-offset-4 focus-visible:outline-primary-700">
                                        <img src="{{ $photo->thumb_path }}"
                                            alt="{{ $photo->legend ? Str::limit($photo->legend, 120) : 'Photo de l\'album « ' . $photo->album?->title . ' »' }}"
                                            width="{{ $photo->thumb_width }}" height="{{ $photo->thumb_height }}"
                                            loading="lazy" decoding="async"
                                            class="aspect-square w-full bg-gray-100 object-cover transition-opacity hover:opacity-85">
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('photos.index', ['auteurs' => [$user->id]]) }}"
                            class="mt-4 inline-flex min-h-11 items-center gap-2 rounded-full bg-primary-700 px-5 py-2 font-semibold text-white transition-colors hover:bg-primary-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                            @if ($user->photos_count > 1)
                                Voir {{ $isOwnProfile ? 'mes' : 'ses' }} {{ $user->photos_count }} photos
                            @else
                                Voir {{ $isOwnProfile ? 'ma' : 'sa' }} photo dans la galerie
                            @endif
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                                <path d="M5 12h14M13 6l6 6-6 6" />
                            </svg>
                        </a>
                    @endif
                </section>

                <div class="lg:col-start-1 lg:row-start-3">
                    <livewire:comments :comments="$user->comments" :commentable="$user" />
                </div>
            </div>
        </div>
    </div>
</x-member-layout>
