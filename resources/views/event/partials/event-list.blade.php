<section class="mx-auto max-w-3xl">
    <ul class="flex max-w-full flex-col space-y-6">
        @forelse ($events as $event)
            <li id="event-item-{{ $event->id }}" class="event-item relative cursor-pointer shadow-lg">
                <x-event-to-agenda :event="$event" />
                @if ($event->imagePath)
                    <img src="{{ $event->imagePath }}" alt="Image de couverture de l'événement {{ $event->name }}"
                        class="h-64 w-full rounded-xl object-cover">
                @endif
                <div
                    class="{{ $event->imagePath ? 'absolute bottom-0 bg-clip-padding backdrop-filter backdrop-blur bg-opacity-60 rounded-md' : 'rounded-xl' }} flex w-full flex-row space-x-4 bg-white p-3">
                    <div
                        class="flex min-w-fit flex-col items-center justify-around rounded-md bg-sky-100 p-2 text-lg font-bold md:justify-center">
                        {{ $event->start_time != $event->end_time ? $event->start_time->format('d') . ' - ' : '' }}{{ $event->end_time->translatedFormat('d M') }}
                        <x-primary-button type="button" class="event-item-clickable add-to-agenda max-w-min md:hidden">
                            Agenda
                        </x-primary-button>
                    </div>
                    <div class="max-h-28 grow basis-1/2 overflow-hidden">
                        <a href="{{ route('events.show', $event) }}" class="event-item-clickable event_item_link">
                            <h3 class="truncate whitespace-nowrap text-lg font-bold leading-6 text-gray-900">
                                {{ $event->name }}
                            </h3>
                        </a>
                        <p class="mt-1 max-w-2xl text-sm text-gray-600">
                            {{ $event->description }}
                        </p>
                    </div>
                    <div class="hidden max-w-min self-center md:block lg:max-w-fit">
                        <x-primary-button type="button" class="event-item-clickable add-to-agenda">
                            + Ajouter à l'agenda
                        </x-primary-button>
                    </div>
                </div>
            </li>
        @empty
            <li class="flex flex-col items-center justify-center">
                <p class="text-center text-gray-500">Aucun événement à venir</p>
            </li>
        @endforelse
    </ul>
</section>
@push('scripts')
    <script type="text/javascript">
        const eventItems = document.querySelectorAll("li.event-item");
        const clickableElements = document.querySelectorAll("li.event-item > event-item-clickable");
        clickableElements.forEach((ele) =>
            ele.addEventListener("click", (e) => e.stopPropagation())
        );

        function handleClick(event) {
            const noTextSelected = !window.getSelection().toString();
            if (noTextSelected && event.target.nodeName !== 'BUTTON') {
                const link = event.target.closest("li.event-item").querySelector("a.event_item_link");
                if (link) {
                    link.click();
                }
            }
        }
        document.querySelectorAll('.event-item').forEach(item => {
            item.addEventListener('click', handleClick);
        });
    </script>
@endpush
