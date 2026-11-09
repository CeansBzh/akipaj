@php
    // Pilule de verre posée sur la photo : un fond sombre assez opaque pour que le texte blanc reste lisible
    $glass = 'inline-flex min-h-11 items-center gap-2 rounded-full bg-gray-950/60 px-4 py-2 font-semibold text-white ring-1 ring-white/15 backdrop-blur-md';
    $menuItem = 'flex min-h-12 w-full items-center gap-3 rounded-xl px-3 py-2 text-left font-semibold text-gray-900 transition-colors hover:bg-primary-50 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-primary-700';
@endphp

{{-- Commandes de la visionneuse, copiées dans PhotoSwipe par resources/js/gallery.js --}}
<template data-gallery-lightbox-actions>
    <div class="flex items-start gap-2">
        <div class="relative">
            <button type="button" data-lightbox-menu-button aria-expanded="false" aria-controls="gallery-lightbox-menu"
                class="{{ $glass }} transition-colors hover:bg-gray-950/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor" class="size-5 shrink-0">
                    <circle cx="5" cy="12" r="1.75" />
                    <circle cx="12" cy="12" r="1.75" />
                    <circle cx="19" cy="12" r="1.75" />
                </svg>
                Plus d'actions
            </button>
            <div id="gallery-lightbox-menu" data-lightbox-menu hidden
                class="absolute top-full right-0 mt-2 w-max max-w-[calc(100vw-2rem)] min-w-64 rounded-2xl bg-white p-2 shadow-xl ring-1 ring-black/5">
                <a href="#" data-lightbox-download download class="{{ $menuItem }}">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0 text-primary-700">
                        <path d="M12 4v11M7 10.5l5 5 5-5M5 20h14" />
                    </svg>
                    Télécharger
                </a>
                <button type="button" data-lightbox-copy class="{{ $menuItem }}">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0 text-primary-700">
                        <path d="M10 14a4.5 4.5 0 0 0 6.4 0l3-3a4.5 4.5 0 0 0-6.4-6.4l-1 1" />
                        <path d="M14 10a4.5 4.5 0 0 0-6.4 0l-3 3a4.5 4.5 0 0 0 6.4 6.4l1-1" />
                    </svg>
                    <span data-lightbox-copy-label>Copier le lien</span>
                </button>
                <a href="#" data-lightbox-page class="{{ $menuItem }}">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0 text-primary-700">
                        <rect x="3.5" y="4.5" width="17" height="15" rx="2.5" />
                        <path d="M7.5 9.5h9M7.5 13h9M7.5 16.5h5" />
                    </svg>
                    Voir la page de la photo
                </a>
                <a href="#" data-lightbox-edit class="{{ $menuItem }}">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0 text-primary-700">
                        <path d="M4 20h4L19 9a2.8 2.8 0 0 0-4-4L4 16v4Z" />
                        <path d="m13.5 6.5 4 4" />
                    </svg>
                    Modifier la photo
                </a>
            </div>
        </div>
        <button type="button" data-lightbox-close
            class="{{ $glass }} transition-colors hover:bg-gray-950/80 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                <path d="M6 6l12 12M18 6 6 18" />
            </svg>
            Fermer
        </button>
    </div>
    <p data-lightbox-status class="sr-only" aria-live="polite"></p>
</template>

{{-- Matelot qui a envoyé la photo, en bas à droite --}}
<template data-gallery-lightbox-uploader>
    <p class="{{ $glass }} py-1 pr-4 pl-1 font-normal">
        <span class="sr-only">Envoyée par</span>
        <span data-lightbox-uploader class="contents"></span>
    </p>
</template>

@foreach ($this->uploaders as $uploader)
    <template data-gallery-uploader="{{ $uploader->id }}" wire:key="lightbox-uploader-{{ $uploader->id }}">
        <x-user-avatar :user="$uploader" class="size-9" />
        <span class="font-semibold">{{ $uploader->name }}</span>
    </template>
@endforeach
