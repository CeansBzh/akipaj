@php
    $trips = App\Models\Trip::upcoming()->orderBy('start_date')->get();
@endphp

<section class="mx-auto flex max-w-4xl flex-col gap-8">
    @if ($trips->isEmpty())
        <p class="p-12 text-center text-gray-600">Aucune sortie à venir.</p>
    @else
        {{-- La prochaine sortie en grand, les suivantes en petit --}}
        <div class="drop-shadow-md">
            <x-trip-ticket :trip="$trips->first()" :eager="true" />
        </div>

        @if ($trips->count() > 1)
            <ul class="grid gap-6 sm:grid-cols-2">
                @foreach ($trips->slice(1) as $trip)
                    <li class="min-w-0">
                        <x-trip-card :trip="$trip" />
                    </li>
                @endforeach
            </ul>
        @endif
    @endif
</section>
