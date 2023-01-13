<h3 class="font-bold text-3xl mb-4">Historique</h3>
{{-- // TODO Refaire la frise + belle, afficher par année au lieu de par mois, scroll à la souris --}}
<div id="slider" class="overflow-x-auto whitespace-nowrap w-screen -ml-4 sm:w-full" x-data="tripsHistorySlider()">
    <table class="w-full table-auto border-separate border-spacing-4">
        <thead>
            <tr>
                <template x-for="month_year in MONTH_YEAR">
                    <th x-text="MONTH_NAMES[month_year[0]]+' '+month_year[1]"></th>
                </template>
            </tr>
        </thead>
        <tbody>
            <tr>
                <template x-for="month_year in MONTH_YEAR">
                    <td
                        class="mr-2 h-52 bg-gradient-to-b from-gray-100 to-gray-100/50 bg-[length:2px_100%] bg-no-repeat bg-center">
                        <template
                            x-for="trip in trips.filter(e => (new Date(e.start_date).getMonth() === month_year[0] && new Date(e.start_date).getFullYear() === month_year[1]))">
                            <a :href="'{{ route('trips.index') }}/' + trip.id"
                                class="px-5 py-2 block rounded-full bg-sky-700 mb-1 w-fit relative text-white cursor-pointer transform transition duration-300 ease-in-out hover:bg-sky-600 focus:bg-sky-600">
                                <p x-text="trip.title" class="font-bold"></p>
                            </a>
                        </template>
                    </td>
                </template>
            </tr>
        </tbody>
    </table>
</div>

@push('styles')
    <style>
        #slider {
            position: relative;
            overflow-x: scroll;
            overflow-y: hidden;
            white-space: nowrap;
            transition: all 0.2s;
            transform: scale(0.98);
            will-change: transform;
            user-select: none;
            cursor: pointer;
            scroll-snap-type: x mandatory;
        }

        #slider.active {
            cursor: grabbing;
            cursor: -webkit-grabbing;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let currentYear = new Date().getFullYear();
        @if ($trips->count() > 0)
            const YEAR_SPAN = range(@js(intval($trips[0]->start_date->format('Y'))), currentYear);
        @else
            const YEAR_SPAN = range(currentYear, currentYear);
        @endif
        const MONTH_NAMES = ['Jan', 'Fév', 'Mars', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'];
        const MONTH_YEAR = YEAR_SPAN.flatMap(d => range(0, 11).map(v => [v, d]));
        const slider = document.querySelector('#slider');

        function tripsHistorySlider() {
            return {
                trips: @json($trips),
                isDown: false,
                startX: 0,
                scrollLeft: 0,
                init: function() {
                    // this.trips = this.trips.map(e => {
                    //     e.start_date = new Date(e.start_date);
                    //     return e;
                    // });

                    // Listeners to scroll the slider horizontally
                    // IE9, Chrome, Safari, Opera
                    slider.addEventListener('mousewheel', this.scrollHorizontally, false);
                    // Firefox
                    slider.addEventListener('DOMMouseScroll', this.scrollHorizontally, false);
                    // Listeners to grab and drag the slider horizontally
                    slider.addEventListener('mousedown', (e) => {
                        this.isDown = true;
                        slider.classList.add('active');
                        this.startX = e.pageX - slider.offsetLeft;
                        this.scrollLeft = slider.scrollLeft;
                    });
                    slider.addEventListener('mouseleave', () => {
                        this.isDown = false;
                        slider.classList.remove('active');
                    });
                    slider.addEventListener('mouseup', () => {
                        this.isDown = false;
                        slider.classList.remove('active');
                    });
                    slider.addEventListener('mousemove', (e) => {
                        if (!this.isDown) return;
                        e.preventDefault();
                        const x = e.pageX - slider.offsetLeft;
                        const walk = (x - this.startX) * 1.5; //scroll-fast
                        slider.scrollLeft = this.scrollLeft - walk;
                    });
                },
                scrollHorizontally: function(e) {
                    e = window.event || e;
                    var delta = Math.max(-1, Math.min(1, (e.wheelDelta || -e.detail)));
                    slider.scrollLeft -= (delta * 60); // Multiplied by 60
                    e.preventDefault();
                }
            }
        }

        function range(start, end) {
            return Array(end - start + 1).fill().map((_, idx) => start + idx)
        }
    </script>
@endpush
