<x-member-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ajouter des photos à l'album</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto flex-col space-y-4 sm:px-6 lg:px-8">

            <div class="p-4 sm:p-8">
                @include('photo.partials.add-photo-form')
            </div>

        </div>
    </div>

</x-member-layout>
