@php
    $upcomingTrips = App\Models\Trip::upcoming()->orderBy('start_date')->get();

    // Sans sortie à venir, les dernières sorties passent devant le programme vide
    $tabs = [
        ['id' => 1, 'label' => 'Programme', 'view' => 'homepage.partials.upcoming-trips'],
        ['id' => 2, 'label' => 'Dernières sorties', 'view' => 'homepage.partials.last-trips'],
    ];
    if ($upcomingTrips->isEmpty()) {
        $tabs = array_reverse($tabs);
    }
@endphp

<div x-data="{ openTab: {{ $tabs[0]['id'] }} }">
    <ul class="flex flex-wrap rounded-md bg-white text-center shadow-lg sm:m-5">
        @foreach ($tabs as $tab)
            <li class="flex-1" @click="openTab = {{ $tab['id'] }}">
                <button @class([
                    'hover:text-primary-900 inline-block bg-white px-4 py-2 font-semibold',
                    'text-primary-800 border-primary-800 border-b-2' => $loop->first,
                    'text-primary-700' => !$loop->first,
                ])
                    :class="{
                        'text-primary-800 border-primary-800 border-b-2': openTab === {{ $tab['id'] }},
                        'text-primary-700': openTab !== {{ $tab['id'] }},
                    }">
                    {{ $tab['label'] }}
                </button>
            </li>
        @endforeach
    </ul>
    <div class="p-5">
        {{-- L'onglet ouvert par défaut est visible avant le démarrage d'Alpine : la page ne saute pas à son arrivée --}}
        @foreach ($tabs as $tab)
            <div x-show="openTab === {{ $tab['id'] }}" @if (!$loop->first) x-cloak @endif>
                @include($tab['view'], ['trips' => $upcomingTrips])
            </div>
        @endforeach
    </div>
</div>
