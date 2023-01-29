<x-member-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Programme</h2>
            @can('create', App\Models\Event::class)
                <x-primary-link href="{{ route('events.create') }}">
                    Nouvel évènement
                </x-primary-link>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl flex-col space-y-4 sm:px-6 lg:px-8">

            <div class="bg-sky-100 p-4 shadow sm:rounded-lg sm:p-8">
                @include('event.partials.event-list')
            </div>

        </div>
    </div>

</x-member-layout>
