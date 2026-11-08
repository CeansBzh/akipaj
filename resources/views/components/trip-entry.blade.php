@props(['trip'])

{{--
    Grande carte d'une sortie dans la liste des sorties, sur le modèle des cartes de l'accueil : la photo en plein cadre,
    le statut et la date posés dessus sur un fond de verre, puis le titre, les dates, le lieu, la durée, l'équipage
    et les photos sur un flou progressif. Toute la carte mène à la sortie ;
    une sortie à venir ou en cours a aussi son bouton « Ajouter à mon agenda ».
    Les albums doivent être chargés avec leur nombre de photos, et les matelots comptés (users_count).
--}}

@php
    $isOngoing = $trip->isOngoing();
    $isFinished = $trip->isFinished();
    $durationInDays = (int) $trip->start_date->diffInDays($trip->end_date) + 1;
    $photoCount = $trip->albums->sum('photos_count');
@endphp

<article id="trip-item-{{ $trip->id }}" data-timeline-anchor="trip-{{ $trip->id }}"
    {{ $attributes->class(['group relative isolate flex aspect-4/5 scroll-mt-24 flex-col justify-between gap-8 rounded-3xl p-4 text-white shadow-lg shadow-gray-900/15 sm:aspect-4/3 sm:p-6 lg:scroll-mt-8']) }}>
    @unless ($isFinished)
        <x-trip-to-agenda :trip="$trip" />
    @endunless

    {{-- La photo et ses voiles sont découpés à part : la carte garde ses proportions, mais s'allonge si son texte le demande --}}
    <div class="absolute inset-0 -z-10 overflow-hidden rounded-3xl bg-primary-100">
        <img src="{{ $trip->imagePath ?? Vite::asset('resources/images/index_2.jpg') }}" alt="" width="1200"
            height="900" loading="lazy" decoding="async"
            class="size-full object-cover motion-safe:transition-transform motion-safe:duration-500 motion-safe:group-hover:scale-[1.03]">
        {{-- Flou progressif et dégradé sombre sous le texte, pour qu'il reste lisible sur n'importe quelle photo --}}
        <div class="absolute inset-x-0 bottom-0 h-3/5 backdrop-blur-lg mask-[linear-gradient(to_top,#000_50%,transparent)]">
        </div>
        <div class="absolute inset-0 bg-linear-to-t from-gray-950/85 via-gray-950/45 via-40% to-transparent to-75%">
        </div>
    </div>

    <div class="flex items-start justify-between gap-3">
        <p @class([
            'w-fit rounded-full px-3 py-1 text-sm font-bold shadow-sm',
            'bg-primary-700 text-white' => $isOngoing,
            'bg-white/85 text-gray-900 backdrop-blur-md' => $isFinished,
            'bg-white text-primary-800' => !$isOngoing && !$isFinished,
        ])>
            <x-trip-status :trip="$trip" />
        </p>
        <p class="flex min-w-16 flex-col items-center rounded-2xl bg-white/85 px-3 py-2 leading-none text-primary-800 shadow-sm backdrop-blur-md">
            <span class="text-2xl font-bold">{{ $trip->start_date->isoFormat('D') }}</span>
            <span class="mt-1 text-sm">{{ $trip->start_date->isoFormat('MMMM') }}</span>
        </p>
    </div>

    <div class="flex flex-col items-start gap-3">
        <h3 class="text-2xl leading-tight font-bold text-balance break-words sm:text-3xl">
            <a href="{{ route('trips.show', $trip) }}"
                class="after:absolute after:inset-0 after:rounded-3xl group-hover:underline focus-visible:outline-none focus-visible:after:outline-4 focus-visible:after:-outline-offset-4 focus-visible:after:outline-primary-300">
                {{ $trip->title }}
            </a>
        </h3>

        <ul class="flex flex-col gap-1.5 text-white/90">
            <li class="flex items-center gap-2">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="size-4 shrink-0">
                    <rect x="3" y="4.5" width="18" height="16.5" rx="2" />
                    <path d="M3 9.5h18M8 2.5v4M16 2.5v4" />
                </svg>
                <span class="sr-only">Dates :</span>
                <span><x-date-range :start="$trip->start_date" :end="$trip->end_date" /></span>
            </li>
            @if ($trip->location)
                <li class="flex items-start gap-2">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="mt-1 size-4 shrink-0">
                        <path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" />
                        <circle cx="12" cy="9.5" r="2.5" />
                    </svg>
                    <span class="sr-only">Lieu :</span>
                    <span class="line-clamp-2 min-w-0 break-words">{{ $trip->location }}</span>
                </li>
            @endif
            <li class="flex flex-wrap gap-x-4 gap-y-1">
                <span>{{ $durationInDays }} {{ $durationInDays > 1 ? 'jours' : 'jour' }}</span>
                @if ($trip->users_count > 0)
                    <span>{{ $trip->users_count }} {{ $trip->users_count > 1 ? 'matelots' : 'matelot' }}</span>
                @endif
                @if ($photoCount > 0)
                    <span>{{ $photoCount }} {{ $photoCount > 1 ? 'photos' : 'photo' }}</span>
                @endif
            </li>
        </ul>

        <div class="mt-1 flex flex-wrap items-center gap-3">
            @unless ($isFinished)
                <button type="button"
                    class="add-to-agenda relative z-10 inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-5 py-2 font-semibold text-primary-800 shadow-md transition-colors hover:bg-primary-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white disabled:cursor-wait disabled:opacity-60">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                        <rect x="3" y="4.5" width="18" height="16.5" rx="2" />
                        <path d="M3 9.5h18M8 2.5v4M16 2.5v4M12 12.5v6M9 15.5h6" />
                    </svg>
                    Ajouter à mon agenda
                </button>
            @endunless
            {{-- Le lien est le titre, étendu à toute la carte : ce bouton le montre seulement --}}
            <span aria-hidden="true"
                class="inline-flex min-h-11 items-center gap-2 rounded-full bg-white/15 px-5 py-2 font-semibold text-white ring-1 ring-white/50 backdrop-blur-md transition-colors ring-inset group-hover:bg-white/25">
                Voir la sortie
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" class="size-5 shrink-0">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </span>
        </div>
    </div>
</article>
