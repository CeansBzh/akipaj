<x-member-layout>
    <livewire:gallery />
    <livewire:photo-drawer />

    @push('scripts')
        @vite('resources/js/gallery.js')
    @endpush
</x-member-layout>
