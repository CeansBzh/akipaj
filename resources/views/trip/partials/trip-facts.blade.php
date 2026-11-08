@php
    $isSingleDay = $trip->start_date->isSameDay($trip->end_date);
    $durationInDays = (int) $trip->start_date->diffInDays($trip->end_date) + 1;
@endphp

{{-- Fiche de la sortie : ses dates, sa durée et son lieu, puis les actions --}}
<div class="rounded-3xl bg-primary-50 p-6 ring-1 ring-primary-100 ring-inset">
    <h2 class="sr-only">Informations pratiques</h2>

    <dl class="flex flex-col gap-4">
        @if ($isSingleDay)
            <div>
                <dt class="text-sm text-gray-600">Date</dt>
                <dd class="text-lg font-bold text-gray-900">{{ $trip->start_date->isoFormat('dddd Do MMMM YYYY') }}</dd>
            </div>
        @else
            <div>
                <dt class="text-sm text-gray-600">Départ</dt>
                <dd class="text-lg font-bold text-gray-900">{{ $trip->start_date->isoFormat('dddd Do MMMM YYYY') }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-600">Retour</dt>
                <dd class="text-lg font-bold text-gray-900">{{ $trip->end_date->isoFormat('dddd Do MMMM YYYY') }}</dd>
            </div>
            <div>
                <dt class="text-sm text-gray-600">Durée</dt>
                <dd class="text-lg font-bold text-gray-900">{{ $durationInDays }} jours</dd>
            </div>
        @endif
        @if ($trip->location)
            <div>
                <dt class="text-sm text-gray-600">Lieu</dt>
                <dd class="text-lg font-bold whitespace-pre-line break-words text-gray-900">{{ $trip->location }}</dd>
            </div>
        @endif
    </dl>

    <div class="mt-6 flex flex-col gap-3">
        @unless ($trip->isFinished())
            <button type="button"
                class="add-to-agenda inline-flex min-h-12 items-center justify-center gap-2 rounded-full bg-primary-700 px-6 py-2 font-semibold text-white transition-colors hover:bg-primary-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700 disabled:cursor-wait disabled:opacity-60">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                    <rect x="3" y="4.5" width="18" height="16.5" rx="2" />
                    <path d="M3 9.5h18M8 2.5v4M16 2.5v4M12 12.5v6M9 15.5h6" />
                </svg>
                Ajouter à mon agenda
            </button>
        @endunless

        <div class="flex flex-wrap gap-3">
            {{-- Partage par le menu du téléphone, ou copie du lien là où ce menu n'existe pas --}}
            <button type="button"
                x-data="{
                    copied: false,
                    async share() {
                        const url = @js(route('trips.show', $trip));
                        if (navigator.share) {
                            await navigator.share({ title: @js($trip->title), url }).catch(() => {});
                            return;
                        }
                        await navigator.clipboard.writeText(url);
                        this.copied = true;
                        setTimeout(() => this.copied = false, 3000);
                    },
                }"
                x-on:click="share()"
                class="inline-flex min-h-12 grow items-center justify-center gap-2 rounded-full bg-white px-5 py-2 font-semibold text-gray-900 ring-1 ring-gray-300 transition-colors ring-inset hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                    <path d="M12 3v12M7.5 7.5 12 3l4.5 4.5" />
                    <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-7" />
                </svg>
                <span aria-live="polite" x-text="copied ? 'Lien copié' : 'Partager'">Partager</span>
            </button>

            @can('update', $trip)
                <a href="{{ route('trips.edit', $trip) }}"
                    class="inline-flex min-h-12 grow items-center justify-center gap-2 rounded-full bg-white px-5 py-2 font-semibold text-gray-900 ring-1 ring-gray-300 transition-colors ring-inset hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                        <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L8 18l-4 1 1-4Z" />
                    </svg>
                    Modifier la sortie
                </a>
            @endcan
        </div>
    </div>
</div>
