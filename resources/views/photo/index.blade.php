<x-member-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Toutes les photos</h2>
            <x-primary-link href="{{ route('albums.create') }}">
                Créer un album
            </x-primary-link>
        </div>
    </x-slot>

    <div>
        @if (\App\Models\Photo::count() > 0)
            <livewire:lightbox />
            <div class="max-w-screen-2xl mx-auto">
                <livewire:gallery />
            </div>
        @else
            <div class="flex flex-col items-center justify-center h-64">
                <p class="text-xl mb-5">Aucune photo de publiée.</p>
                <x-primary-link href="{{ route('albums.create') }}">
                    Créer un album
                </x-primary-link>
            </div>
        @endif
    </div>
</x-member-layout>
