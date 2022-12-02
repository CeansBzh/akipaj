<x-app-layout>
    <div class="py-12">
        <div class="max-w-3xl mx-auto">

            @if($event->imagePath)
            <img src="{{ $event->imagePath }}" alt="Image de couverture de l'événement {{ $event->name }}"
                class="max-h-80 w-full object-cover {{ $event->imagePath ? 'sm:rounded-t-lg' : '' }}">
            @endif
            <div class="p-4 sm:p-8 bg-white shadow {{ $event->imagePath ? 'sm:rounded-b-lg' : 'sm:rounded-lg' }}">
                <div class="flex flex-row justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-gray-900 mb-1">{{ $event->name }}</h1>
                        <div class="flex flex-col space-y-1">
                            <div class="flex flex-row text-gray-500 items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="h-5 mr-2">
                                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                    <line x1="16" y1="2" x2="16" y2="6"></line>
                                    <line x1="8" y1="2" x2="8" y2="6"></line>
                                    <line x1="3" y1="10" x2="21" y2="10"></line>
                                </svg>
                                <p class="text-sm">{{ $event->start_time->translatedFormat('d M Y') }} - {{
                                    $event->end_time->translatedFormat('d M Y') }}</p>
                            </div>
                            @if($event->location)
                            <div class="flex flex-row text-gray-500 items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="h-5 mr-2">
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
                        <p class="text-gray-600 mt-4">{{ $event->description }}</p>
                    </div>
                    <div class="text-center mt-5">
                        <x-primary-button type="button" class="mb-2 sm:mb-0 sm:mr-2">
                            + Ajouter à l'agenda
                        </x-primary-button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>