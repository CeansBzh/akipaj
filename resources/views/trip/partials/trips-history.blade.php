<h3 class="font-bold text-3xl mb-4">Historique</h3>

<div class="overflow-x-auto whitespace-nowrap w-screen -ml-4 sm:w-full" x-data="app()">
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
                            <a :href="'{{ route('trips.index') }}/'+trip.id" class="px-5 py-2 block rounded-full bg-sky-700 mb-1 w-fit relative text-white cursor-pointer transform transition duration-300 ease-in-out hover:scale-105 hover:-translate-y-2 focus:bg-sky-600">
                                <p x-text="trip.title" class="font-bold"></p>
                            </a>
                        </template>
                    </td>
                </template>
            </tr>
        </tbody>
    </table>
</div>

@push('scripts')
<script>
    const YEAR_SPAN = range(@js(intval($trips[0]->start_date->format('Y'))), 2022);
    const MONTH_NAMES = ['Jan', 'Fév', 'Mars', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sept', 'Oct', 'Nov', 'Déc'];
    const MONTH_YEAR = YEAR_SPAN.flatMap(d => range(0, 11).map(v => [v, d]));

    function app() {
        return {
            trips: @json($trips),
        }
    }

    function range(start, end) {
        return Array(end - start + 1).fill().map((_, idx) => start + idx)
    }
</script>
@endpush