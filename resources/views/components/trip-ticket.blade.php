@props(['trip', 'eager' => false])

{{-- Grande carte de sortie à venir en forme de billet : la photo, puis le talon avec les dates, le lieu et l'ajout à l'agenda --}}

@php
    $isOngoing = $trip->isOngoing();
    $isSingleDay = $trip->start_date->isSameDay($trip->end_date);
@endphp

<article id="trip-item-{{ $trip->id }}"
    {{ $attributes->class(['ticket grid overflow-hidden rounded-2xl bg-white md:grid-cols-[var(--ticket-photo-width)_1fr]']) }}>
    <x-trip-to-agenda :trip="$trip" />

    <a href="{{ route('trips.show', $trip) }}"
        class="h-(--ticket-photo-height) focus-visible:outline-primary-400 group relative isolate flex flex-col justify-between gap-3 overflow-hidden p-4 focus-visible:outline-4 focus-visible:-outline-offset-4 md:h-auto md:min-h-72">
        <img src="{{ $trip->imagePath ?? Vite::asset('resources/images/index_2.jpg') }}" alt=""
            class="absolute inset-0 -z-10 size-full object-cover motion-safe:transition-transform motion-safe:duration-500 motion-safe:group-hover:scale-105"
            loading="{{ $eager ? 'eager' : 'lazy' }}" decoding="async">
        <div class="bg-linear-to-t absolute inset-0 -z-10 from-gray-950/80 via-gray-950/20 to-transparent"></div>

        <p @class([
            'w-fit rounded-full px-3 py-1 text-sm font-bold shadow-sm',
            'bg-primary-700 text-white' => $isOngoing,
            'bg-white text-primary-800' => !$isOngoing,
        ])>
            <x-trip-status :trip="$trip" />
        </p>

        <h3 class="text-balance text-xl font-bold text-white group-hover:underline">
            {{ $trip->title }}
        </h3>
    </a>

    <div
        class="relative isolate flex flex-col gap-5 border-t-2 border-dashed border-gray-300 p-5 md:border-l-2 md:border-t-0">
        {{-- Voile du logo en filigrane, comme la marque de la compagnie sur un billet --}}
        <svg aria-hidden="true" viewBox="0 0 48 58" fill="currentColor"
            class="text-primary-50 pointer-events-none absolute -right-6 -top-2 -z-10 h-44">
            <path fill-opacity="0.5"
                d="M36.3885 6.14278L6.06381 55.2067C8.03098 55.3719 10.6753 55.4709 13.6326 55.4058C23.1945 43.0471 30.2202 28.9267 34.2935 13.8812L36.3885 6.14278Z" />
            <path
                d="M38.5289 0.354818C44.427 7.43984 46.3765 41.5354 37.0222 48.995C31.5835 53.332 24.0237 55.319 16.158 55.4921C28.364 39.3951 36.0946 20.3191 38.5289 0.354818Z" />
        </svg>

        <dl class="grid grid-cols-[auto_1fr] items-baseline gap-x-4 gap-y-3">
            @if ($isSingleDay)
                <dt class="text-sm text-gray-600">Date</dt>
                <dd class="text-lg font-bold text-gray-900">{{ $trip->start_date->isoFormat('dddd Do MMMM YYYY') }}</dd>
            @else
                <dt class="text-sm text-gray-600">Départ</dt>
                <dd class="text-lg font-bold text-gray-900">{{ $trip->start_date->isoFormat('dddd Do MMMM YYYY') }}</dd>
                <dt class="text-sm text-gray-600">Retour</dt>
                <dd class="text-lg font-bold text-gray-900">{{ $trip->end_date->isoFormat('dddd Do MMMM YYYY') }}</dd>
            @endif
            @if ($trip->location)
                <dt class="text-sm text-gray-600">Lieu</dt>
                <dd class="whitespace-pre-line text-gray-900">{{ $trip->location }}</dd>
            @endif
        </dl>

        <button type="button"
            class="add-to-agenda bg-primary-700 hover:bg-primary-800 focus-visible:outline-primary-700 mt-auto inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-lg px-4 py-2 font-semibold text-white transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-wait disabled:opacity-60">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                <rect x="3" y="4.5" width="18" height="16.5" rx="2" />
                <path d="M3 9.5h18M8 2.5v4M16 2.5v4M12 12.5v6M9 15.5h6" />
            </svg>
            Ajouter à mon agenda
        </button>
    </div>
</article>
