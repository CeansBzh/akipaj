<x-member-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Historique des sorties</h2>
            @can('create', App\Models\Trip::class)
                <x-primary-link href="{{ route('trips.create') }}">
                    Nouvelle sortie
                </x-primary-link>
            @endcan
        </div>
    </x-slot>

    <div x-data="trips()">
        <div class="mb-8 bg-sky-500/90">
            <div class="relative mx-auto grid max-w-5xl grid-cols-1 grid-rows-1">
                <div class="carousel col-start-1 row-start-1" x-ref="carousel">
                    @foreach ($tripsByYear as $year => $trips)
                        <div class="mr-8 flex h-32 w-1/4 flex-col items-center justify-center rounded sm:w-1/6">
                            <p class="cursor-pointer font-merriweather text-4xl font-bold text-white"
                                x-on:click="flkty.selectCell({{ $loop->index }})">{{ $year }}</p>
                            <p class="mt-2 text-center text-lg text-gray-200">{{ $trips->count() }}
                                sortie{{ $trips->count() > 1 ? 's' : '' }}</p>
                        </div>
                    @endforeach
                </div>
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <span class="h-28 w-28 rounded-lg border-2 border-white"></span>
                </div>
            </div>
        </div>
        <div class="mx-auto max-w-4xl p-4">
            @foreach ($tripsByYear->keys() as $index => $year)
                <div x-show="active === {{ $index }}">
                    <p class="mb-3 font-bold">Sorties en {{ $year }}</p>
                    <ul class="flex max-w-full flex-col space-y-3">
                        @forelse ($tripsByYear[$year] as $trip)
                            @php
                                $photoCount = $trip->albums->sum(function ($album) {
                                    return $album->photos->count();
                                });
                            @endphp
                            <li class="trip_item relative cursor-pointer shadow-lg">
                                @if ($trip->imagePath)
                                    <img src="{{ $trip->imagePath }}"
                                        alt="Image de couverture de la sortie {{ $trip->title }}"
                                        class="h-[22rem] w-full rounded-xl object-cover">
                                @endif
                                <div
                                    class="{{ $trip->imagePath ? 'absolute bottom-0 bg-clip-padding backdrop-filter backdrop-blur bg-opacity-75 rounded-md' : 'rounded-xl' }} flex w-full flex-row justify-between space-x-4 bg-white py-3 px-3 sm:px-8">
                                    <div>
                                        <a href="{{ route('trips.show', $trip) }}"
                                            class="trip_item_clickable trip_item_link text-lg font-bold">
                                            {{ $trip->title }}
                                        </a>
                                        <p class="text-sm text-gray-600">
                                            {{ $trip->start_date->translatedFormat('d M') }}
                                        </p>
                                    </div>
                                    <div class="flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:space-x-4">
                                        <div class="flex items-center space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" class="h-6 text-gray-600">
                                                <path
                                                    d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z">
                                                </path>
                                                <circle cx="12" cy="13" r="4"></circle>
                                            </svg>
                                            <p class="text-sm text-gray-800">{{ $photoCount }} photos</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" class="h-6 text-gray-600">
                                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="9" cy="7" r="4"></circle>
                                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                            </svg>
                                            <p class="text-sm text-gray-800">{{ $trip->users->count() }} équipiers</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                                stroke-linejoin="round" class="h-6 text-gray-600">
                                                <rect x="3" y="4" width="18" height="18"
                                                    rx="2" ry="2"></rect>
                                                <line x1="16" y1="2" x2="16" y2="6">
                                                </line>
                                                <line x1="8" y1="2" x2="8" y2="6">
                                                </line>
                                                <line x1="3" y1="10" x2="21" y2="10">
                                                </line>
                                            </svg>
                                            <p class="text-sm text-gray-800">
                                                {{ date_diff($trip->start_date, $trip->end_date)->days + 1 }} jours</p>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="flex flex-col items-center justify-center">
                                <p class="text-center text-gray-500">Aucun sortie en {{ $year }}</p>
                            </li>
                        @endforelse
                    </ul>
                </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
        <script>
            function trips() {
                return {
                    active: 0,
                    init() {
                        this.active = this.$refs.carousel.children.length - 1;
                        this.flkty = new Flickity(this.$refs.carousel, {
                            wrapAround: false,
                            initialIndex: this.active,
                        });
                        this.flkty.on('change', i => this.active = i);
                    }
                }
            }

            // Manage clickable divs
            const eventItems = document.querySelectorAll('li.trip_item');
            const clickableElements = document.querySelectorAll('li.trip_item > trip_item_clickable');
            clickableElements.forEach((ele) =>
                ele.addEventListener('click', (e) => e.stopPropagation())
            );

            function handleClick(event) {
                const noTextSelected = !window.getSelection().toString();
                if (noTextSelected && event.target.nodeName !== 'BUTTON') {
                    const link = event.target.closest('li.trip_item').querySelector('a.trip_item_link');
                    if (link) {
                        link.click();
                    }
                }
            }
            document.querySelectorAll('.trip_item').forEach(item => {
                item.addEventListener('click', handleClick);
            })
        </script>
    @endpush
</x-member-layout>
