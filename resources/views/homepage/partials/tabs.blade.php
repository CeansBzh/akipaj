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
                <button class="text-primary-500 hover:text-primary-800 inline-block bg-white px-4 py-2 font-semibold"
                    :class="{ 'text-primary-800 border-primary-800 border-b-2': openTab === {{ $tab['id'] }} }">
                    {{ $tab['label'] }}
                </button>
            </li>
        @endforeach
    </ul>
    <div class="p-5" x-cloak>
        @foreach ($tabs as $tab)
            <div x-show="openTab === {{ $tab['id'] }}">
                @include($tab['view'], ['trips' => $upcomingTrips])
            </div>
        @endforeach
    </div>
</div>
