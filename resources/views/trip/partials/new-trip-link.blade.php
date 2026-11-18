{{-- Lien vers le formulaire d'une nouvelle sortie, pour qui peut en créer --}}
<a href="{{ route('trips.create') }}"
    class="text-gray-500 md:bg-primary-700 md:hover:bg-primary-800 focus-visible:outline-primary-700 inline-flex min-h-11 items-center gap-2 rounded-full md:px-5 py-2 font-semibold md:text-white transition-colors focus-visible:outline-2 focus-visible:outline-offset-2">
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
        stroke-linejoin="round" class="size-7 md:size-5 shrink-0">
        <path d="M12 5v14M5 12h14" />
    </svg>
    <span class="sr-only md:not-sr-only">Nouvelle sortie</span>
</a>
