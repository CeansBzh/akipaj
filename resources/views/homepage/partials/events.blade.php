@php
    // Les événements en cours restent affichés jusqu'à la fin de leur dernier jour
    $events = App\Models\Event::where('end_time', '>=', today())
        ->orderBy('start_time')
        ->get();
@endphp

<section class="mx-auto flex max-w-4xl flex-col gap-8">
    @if ($events->isEmpty())
        <p class="p-12 text-center text-gray-600">Aucun événement à venir.</p>
    @else
        {{-- Le prochain événement en grand, les suivants en petit --}}
        <div class="drop-shadow-md">
            <x-event-ticket :event="$events->first()" :eager="true" />
        </div>

        @if ($events->count() > 1)
            <ul class="grid gap-6 sm:grid-cols-2">
                @foreach ($events->slice(1) as $event)
                    <li class="min-w-0">
                        <x-event-card :event="$event" />
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</section>
