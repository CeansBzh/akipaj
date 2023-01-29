<x-member-layout>
    <div class="py-12">
        <div id="event-item-{{ $event->id }}" class="mx-auto max-w-3xl">
            <x-event-to-agenda :event="$event" />
            @if ($event->imagePath)
                <img src="{{ $event->imagePath }}" alt="Image de couverture de l'événement {{ $event->name }}"
                    class="{{ $event->imagePath ? 'sm:rounded-t-lg' : '' }} max-h-80 w-full object-cover">
            @endif
            <div class="{{ $event->imagePath ? 'sm:rounded-b-lg' : 'sm:rounded-lg' }} bg-white p-4 shadow sm:p-8">
                <div class="flex flex-row justify-between">
                    <div>
                        <h1 class="mb-1 text-xl font-bold text-gray-900">{{ $event->name }}</h1>
                        <div class="flex flex-col space-y-1">
                            <div class="flex flex-row items-center text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="mr-2 h-5">
                                    <rect x="3" y="4" width="18" height="18" rx="2"
                                        ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <p class="text-sm">{{ $event->start_time->translatedFormat('d M Y') }} -
                                    {{ $event->end_time->translatedFormat('d M Y') }}</p>
                            </div>
                            @if ($event->location)
                                <div class="flex flex-row items-center text-gray-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" class="mr-2 h-5">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    <p class="text-sm">{{ $event->location }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="group">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="h-6 text-gray-900">
                                    <circle cx="12" cy="12" r="1"></circle>
                                    <circle cx="12" cy="5" r="1"></circle>
                                    <circle cx="12" cy="19" r="1"></circle>
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            @can('update', $event)
                                <x-dropdown-link :href="route('events.edit', $event)">
                                    Modifier l'événement
                                </x-dropdown-link>
                            @endcan
                            <x-dropdown-link href="#">
                                Partager
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                </div>
                <div>
                    <div>
                        <p class="mt-4 text-gray-600">{{ $event->description }}</p>
                    </div>
                    <div class="mt-5 text-center">
                        <x-primary-button type="button" class="add-to-agenda mb-2 sm:mb-0 sm:mr-2">
                            + Ajouter à l'agenda
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-member-layout>
