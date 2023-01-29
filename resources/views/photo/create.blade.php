<x-member-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">Ajouter des photos à l'album</h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl flex-col space-y-4 sm:px-6 lg:px-8">

            <div class="p-4 sm:p-8">
                @include('photo.partials.add-photo-form')
            </div>

        </div>
    </div>

</x-member-layout>
