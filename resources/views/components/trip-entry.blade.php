@props(['trip'])

{{--
    Carte d'une sortie dans la liste des sorties : la photo avec le statut posé dessus sur un fond de verre, les dates,
    le titre, le lieu, puis la durée, l'équipage et les photos. Toute la carte mène à la sortie ;
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
    {{ $attributes->class(['group relative scroll-mt-24 lg:scroll-mt-8']) }}>
    @unless ($isFinished)
        <x-trip-to-agenda :trip="$trip" />
    @endunless

    <div class="relative overflow-hidden rounded-3xl bg-primary-100">
        <img src="{{ $trip->imagePath ?? Vite::asset('resources/images/index_2.jpg') }}" alt="" width="1200"
            height="800" loading="lazy" decoding="async"
            class="aspect-3/2 w-full object-cover motion-safe:transition-transform motion-safe:duration-500 motion-safe:group-hover:scale-[1.03]">
        <p @class([
            'absolute top-4 left-4 rounded-full px-3 py-1 text-sm font-bold shadow-sm',
            'bg-primary-700 text-white' => $isOngoing,
            'bg-white/85 text-gray-800 backdrop-blur-md' => $isFinished,
            'bg-white/85 text-primary-800 backdrop-blur-md' => !$isOngoing && !$isFinished,
        ])>
            <x-trip-status :trip="$trip" />
        </p>
    </div>

    <div class="mt-5 flex flex-col items-start gap-2">
        <p class="font-semibold text-primary-800">
            <x-date-range :start="$trip->start_date" :end="$trip->end_date" />
        </p>
        <h3 class="text-2xl leading-tight font-bold text-balance break-words text-gray-900">
            <a href="{{ route('trips.show', $trip) }}"
                class="after:absolute after:inset-0 after:rounded-3xl group-hover:underline focus-visible:outline-none focus-visible:after:outline-2 focus-visible:after:outline-offset-4 focus-visible:after:outline-primary-700">
                {{ $trip->title }}
            </a>
        </h3>
        @if ($trip->location)
            <p class="flex items-start gap-2 text-gray-700">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                    stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 size-5 shrink-0">
                    <path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" />
                    <circle cx="12" cy="9.5" r="2.5" />
                </svg>
                <span class="sr-only">Lieu :</span>
                <span class="min-w-0 break-words">{{ $trip->location }}</span>
            </p>
        @endif

        <ul class="flex flex-wrap gap-x-5 gap-y-1 text-gray-700">
            <li>{{ $durationInDays }} {{ $durationInDays > 1 ? 'jours' : 'jour' }}</li>
            @if ($trip->users_count > 0)
                <li>{{ $trip->users_count }} {{ $trip->users_count > 1 ? 'matelots' : 'matelot' }}</li>
            @endif
            @if ($photoCount > 0)
                <li>{{ $photoCount }} {{ $photoCount > 1 ? 'photos' : 'photo' }}</li>
            @endif
        </ul>

        <div class="mt-3 flex flex-wrap items-center gap-3">
            @unless ($isFinished)
                <button type="button"
                    class="add-to-agenda relative z-10 inline-flex min-h-11 items-center gap-2 rounded-full bg-primary-700 px-5 py-2 font-semibold text-white transition-colors hover:bg-primary-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700 disabled:cursor-wait disabled:opacity-60">
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
                class="inline-flex min-h-11 items-center gap-2 rounded-full bg-white px-5 py-2 font-semibold text-gray-900 ring-1 ring-gray-300 transition-colors ring-inset group-hover:bg-gray-50">
                Voir la sortie
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" class="size-5 shrink-0">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </span>
        </div>
    </div>
</article>
