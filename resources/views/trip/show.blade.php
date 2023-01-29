<x-member-layout>
    @if ($trip->imagePath)
        <div class="absolute w-screen">
            <div class="absolute inset-0 from-transparent via-gray-100/10 to-gray-100 sm:bg-gradient-to-b">
            </div>
            <img src="{{ $trip->imagePath }}" alt="Image de couverture de l'événement {{ $trip->name }}"
                class="min-h-[150px] w-full object-cover">
        </div>
    @endif

    <div class="relative w-full py-12">
        <div class="mx-auto flex max-w-5xl flex-col space-y-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-5 w-fit rounded-lg bg-gray-100/80 p-3 sm:mt-12 sm:mb-16 sm:p-5">
                <h1 class="text-center text-2xl font-bold text-gray-900 sm:text-5xl lg:text-6xl">
                    {{ $trip->title }}
                </h1>
            </div>

            <div class="relative bg-white p-4 shadow sm:rounded-lg sm:p-8">
                @include('trip.partials.trip-details')

                <div class="absolute top-4 right-3">
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
                            @can('update', $trip)
                                <x-dropdown-link :href="route('trips.edit', $trip)">
                                    Modifier la sortie
                                </x-dropdown-link>
                            @endcan
                            <x-dropdown-link href="#">
                                Partager
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <div class="bg-white p-4 shadow sm:rounded-lg sm:p-8">
                @include('trip.partials.trip-related-albums')
            </div>

        </div>
    </div>

</x-member-layout>
