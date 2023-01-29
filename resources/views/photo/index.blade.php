<x-member-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Toutes les photos</h2>
            <x-primary-link href="{{ route('albums.create') }}">
                Créer un album
            </x-primary-link>
        </div>
    </x-slot>

    <div>
        @if (\App\Models\Photo::count() > 0)
            <livewire:lightbox />
            <div class="mx-auto max-w-screen-2xl">
                <livewire:gallery />
            </div>
        @else
            <div class="flex h-64 flex-col items-center justify-center">
                <p class="mb-5 text-xl">Aucune photo de publiée.</p>
                <x-primary-link href="{{ route('albums.create') }}">
                    Créer un album
                </x-primary-link>
            </div>
        @endif
    </div>
</x-member-layout>
