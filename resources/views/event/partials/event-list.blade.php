<section class="mx-auto max-w-3xl">
    <ul class="flex flex-col space-y-6 max-w-full">
        @forelse ($events as $event)
        <li class="event_item shadow-lg relative cursor-pointer">
            @if($event->imagePath)
            <img src="{{ $event->imagePath }}" alt="Image de couverture de l'événement {{ $event->name }}"
                class="h-64 w-full object-cover rounded-xl">
            @endif
            <div
                class="bg-white flex flex-row space-x-4 p-3 w-full {{ $event->imagePath ? 'absolute bottom-0 bg-clip-padding backdrop-filter backdrop-blur bg-opacity-60 rounded-md' : 'rounded-xl' }}">
                <div
                    class="flex flex-col justify-around items-center bg-sky-100 rounded-md p-2 text-lg font-bold min-w-fit md:justify-center">
                    {{ $event->start_time != $event->end_time ? $event->start_time->format('d') . ' - ' : '' }}{{
                    $event->end_time->translatedFormat('d M') }}
                    <x-primary-button type="button" class="event_item_clickable max-w-min md:hidden"
                        onclick="event.stopPropagation()">
                        Agenda
                    </x-primary-button>
                </div>
                <div class="basis-1/2 overflow-hidden grow max-h-28">
                    <a href="{{ route('events.show', $event) }}" class="event_item_clickable event_item_link">
                        <h3 class="text-lg leading-6 font-bold text-gray-900 truncate whitespace-nowrap">
                            {{ $event->name }}
                        </h3>
                    </a>
                    <p class="mt-1 max-w-2xl text-sm text-gray-600">
                        {{ $event->description }}
                    </p>
                </div>
                <div class="self-center hidden max-w-min md:block lg:max-w-fit">
                    <x-primary-button type="button" class="event_item_clickable">
                        + Ajouter à l'agenda
                    </x-primary-button>
                </div>
            </div>
        </li>
        @empty
        <li class="flex flex-col items-center justify-center">
            <p class="text-gray-500 text-center">Aucun événement à venir</p>
        </li>
        @endforelse
    </ul>
</section>

@push('scripts')
<script type="text/javascript">
    const eventItems = document.querySelectorAll("li.event_item");
    const clickableElements = document.querySelectorAll("li.event_item > event_item_clickable");

    clickableElements.forEach((ele) =>
        ele.addEventListener("click", (e) => e.stopPropagation())
    );

    function handleClick(event) {
        const noTextSelected = !window.getSelection().toString();
        if (noTextSelected && event.target.nodeName !== 'BUTTON') {
            const link = event.target.closest("li.event_item").querySelector("a.event_item_link");
            if (link) {
                link.click();
            }
        }
    }

    document.querySelectorAll('.event_item').forEach(item => {
        item.addEventListener('click', handleClick);
    })
</script>
@endpush