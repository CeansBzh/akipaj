<section class="mx-auto max-w-3xl">
    <ul class="flex flex-col space-y-6 max-w-full">

        @forelse ($events as $event)
        <a href="{{ route('events.show', $event) }}">
            <li class="shadow-lg relative">
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
                        <x-primary-button type="button" class="max-w-min md:hidden">
                            Agenda
                        </x-primary-button>
                    </div>
                    <div class="basis-1/2 overflow-hidden grow max-h-28">
                        <h3 class="text-lg leading-6 font-bold text-gray-900 truncate whitespace-nowrap">
                            {{ $event->name }}
                        </h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-600">
                            {{ $event->description }}
                        </p>
                    </div>
                    <div class="self-center hidden max-w-min md:block lg:max-w-fit">
                        <x-primary-button type="button">
                            + Ajouter à l'agenda
                        </x-primary-button>
                    </div>
                </div>
            </li>
        </a>
        @empty
        <li class="flex flex-col items-center justify-center">
            <p class="text-gray-500 text-center">Aucun événement à venir</p>
        </li>
        @endforelse

    </ul>
</section>