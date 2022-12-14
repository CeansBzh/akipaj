<x-member-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Historique des sorties</h2>
            @can('create', App\Models\Trip::class)
            <x-primary-link href="{{ route('trips.create') }}">
                Nouvelle sortie
            </x-primary-link>
            @endcan
        </div>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto flex-col space-y-4 sm:px-6 lg:px-8">

            <div class="p-4 sm:p-8">
                @include('trip.partials.latest-trips')
            </div>

            <div class="p-4 bg-white shadow sm:rounded-lg sm:p-8">
                @include('trip.partials.trips-history')
            </div>

        </div>
    </div>

</x-member-layout>
