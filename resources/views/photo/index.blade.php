<x-member-layout>
    <livewire:gallery />

    @push('scripts')
        @vite('resources/js/gallery.js')
    @endpush
</x-member-layout>
