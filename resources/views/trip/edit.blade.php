<x-member-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Modifier la sortie</h2>
    </x-slot>


    <div class="py-12">
        <div class="max-w-7xl mx-auto flex-col space-y-4 sm:px-6 lg:px-8">

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include('trip.partials.edit-trip-form')
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include('trip.partials.delete-trip-form')
            </div>

        </div>
    </div>

</x-member-layout>
