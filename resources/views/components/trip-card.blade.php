@props(['trip'])

{{--
    Petite carte de sortie à venir : la photo en plein cadre, la date et le statut posés dessus sur un fond de verre,
    puis le nom, le lieu et les dates sur un flou progressif, à côté d'un bouton rond d'ajout à l'agenda.
--}}

@php($isOngoing = $trip->isOngoing())

<article id="trip-item-{{ $trip->id }}"
    {{ $attributes->class(['group relative isolate flex h-full min-h-80 flex-col justify-between gap-6 overflow-hidden rounded-3xl p-4 text-white shadow-lg shadow-gray-900/15']) }}>
    <x-trip-to-agenda :trip="$trip" />

    <img src="{{ $trip->imagePath ?? Vite::asset('resources/images/index_2.jpg') }}" alt=""
        class="absolute inset-0 -z-10 size-full object-cover motion-safe:transition-transform motion-safe:duration-500 motion-safe:group-hover:scale-105"
        loading="lazy" decoding="async">
    {{-- Flou progressif et dégradé sombre sous le texte, pour qu'il reste lisible sur n'importe quelle photo --}}
    <div
        class="mask-[linear-gradient(to_top,#000_45%,transparent)] absolute inset-x-0 bottom-0 -z-10 h-3/4 backdrop-blur-lg">
    </div>
    <div class="bg-linear-to-t absolute inset-0 -z-10 from-gray-950/85 via-gray-950/40 via-45% to-transparent">
    </div>

    <div class="flex items-start justify-between gap-3">
        <p @class([
            'w-fit rounded-full px-3 py-1 text-sm font-bold shadow-sm',
            'bg-primary-700 text-white' => $isOngoing,
            'bg-white text-primary-800' => !$isOngoing,
        ])>
            <x-trip-status :trip="$trip" />
        </p>
        <p class="text-primary-800 flex min-w-16 flex-col items-center rounded-2xl bg-white px-3 py-2 leading-none">
            <span class="text-2xl font-bold">{{ $trip->start_date->isoFormat('D') }}</span>
            <span class="mt-1 text-sm">{{ $trip->start_date->isoFormat('MMMM') }}</span>
        </p>
    </div>

    <div class="flex flex-col gap-3">
        <div class="flex items-end justify-between gap-4">
            <h3 class="line-clamp-2 text-balance text-xl font-bold leading-snug">
                <a href="{{ route('trips.show', $trip) }}"
                    class="focus-visible:after:outline-primary-300 after:absolute after:inset-0 after:rounded-3xl hover:underline focus-visible:outline-none focus-visible:after:outline-4 focus-visible:after:-outline-offset-4">
                    {{ $trip->title }}
                </a>
            </h3>
            <button type="button" aria-label="Ajouter « {{ $trip->title }} » à mon agenda"
                title="Ajouter à mon agenda"
                class="add-to-agenda text-primary-700 hover:bg-primary-50 relative z-10 inline-flex size-11 shrink-0 items-center justify-center rounded-full bg-white shadow-md transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white disabled:cursor-wait disabled:opacity-60">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="size-5">
                    <rect x="3" y="4.5" width="18" height="16.5" rx="2" />
                    <path d="M3 9.5h18M8 2.5v4M16 2.5v4M12 12.5v6M9 15.5h6" />
                </svg>
            </button>
        </div>

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
                <li class="flex items-center gap-2">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="size-4 shrink-0">
                        <path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z" />
                        <circle cx="12" cy="9.5" r="2.5" />
                    </svg>
                    <span class="sr-only">Lieu :</span>
                    <span class="min-w-0 truncate">{{ $trip->location }}</span>
                </li>
            @endif
        </ul>
    </div>
</article>
