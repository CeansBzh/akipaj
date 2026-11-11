@php
    // Pilule de verre posée sur la photo : un fond sombre assez opaque pour que le texte blanc reste lisible
    $glass =
        'inline-flex min-h-11 items-center gap-2 rounded-full bg-gray-950/60 px-4 py-2 font-semibold text-white ring-1 ring-white/15 backdrop-blur-md';
    $focusRing = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white';
@endphp

{{-- Commandes de la visionneuse des photos partagées, copiées dans PhotoSwipe par resources/js/shared-photos.js --}}
<template data-shared-lightbox-actions>
    <div class="flex items-start gap-2">
        <a href="#" data-lightbox-download download
            class="{{ $glass }} {{ $focusRing }} transition-colors hover:bg-gray-950/80">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                <path d="M12 4v11M7 10.5l5 5 5-5M5 20h14" />
            </svg>
            Télécharger
        </a>
        @if ($canClose)
            <button type="button" data-lightbox-close aria-label="Fermer" title="Fermer"
                class="{{ $focusRing }} inline-flex size-11 shrink-0 items-center justify-center rounded-full bg-gray-950/60 text-white ring-1 ring-white/15 backdrop-blur-md transition-colors hover:bg-gray-950/80">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="size-5">
                    <path d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>
        @endif
    </div>
</template>

{{-- Le club qui partage, en haut à gauche --}}
<template data-shared-lightbox-brand>
    <p class="{{ $glass }} py-1.5 pl-2 pr-4">
        <span class="bg-primary-700 inline-flex rounded-full px-2 py-1">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                stroke-linecap="round" stroke-linejoin="round" class="size-5">
                <path d="M12 3v14M12 4c3 3 5 7 5.5 11H12M12 7c-2 2-4 5-5 8h5" />
                <path d="M3 19c1.5 1.3 3 1.3 4.5 0s3-1.3 4.5 0 3 1.3 4.5 0 3-1.3 4.5 0" />
            </svg>
        </span>
        Akipaj
    </p>
</template>
