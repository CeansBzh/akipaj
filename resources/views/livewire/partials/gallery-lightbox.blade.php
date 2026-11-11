@php
    // Pilule de verre posée sur la photo : un fond sombre assez opaque pour que le texte blanc reste lisible
    $glass =
        'inline-flex min-h-11 items-center gap-2 rounded-full bg-gray-950/60 px-4 py-2 font-semibold text-white ring-1 ring-white/15 backdrop-blur-md';
    $focusRing = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white';
    // Bouton rond en verre, une icône seule : son nom est dans aria-label et title
    $glassIcon =
        'inline-flex size-11 shrink-0 items-center justify-center rounded-full bg-gray-950/60 text-white ring-1 ring-white/15 backdrop-blur-md transition-colors hover:bg-gray-950/80 ' .
        $focusRing;
    $menuItem =
        'flex min-h-12 w-full items-center gap-3 rounded-xl px-3 py-2 text-left font-semibold text-gray-900 transition-colors hover:bg-primary-50 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-primary-700';
@endphp

{{-- Commandes de la visionneuse, copiées dans PhotoSwipe par resources/js/gallery.js --}}
<template data-gallery-lightbox-actions>
    <div class="flex items-start gap-2">
        {{-- Commentaires : une icône, avec un point quand la photo en a (son libellé dit combien) --}}
        <button type="button" data-lightbox-comments aria-expanded="false" aria-controls="photo-drawer"
            aria-label="Commentaires" title="Commentaires"
            class="{{ $glassIcon }} relative aria-expanded:bg-white aria-expanded:text-gray-950 aria-expanded:hover:bg-gray-200">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                stroke-linecap="round" stroke-linejoin="round" class="size-6">
                <path
                    d="M20 11.5c0 4.1-3.6 7.5-8 7.5a8.6 8.6 0 0 1-3.3-.6L4 20l1.4-3.7A7.2 7.2 0 0 1 4 11.5C4 7.4 7.6 4 12 4s8 3.4 8 7.5Z" />
            </svg>
            <span data-lightbox-comments-dot hidden
                class="bg-primary-400 absolute right-1.5 top-1.5 size-3 rounded-full ring-2 ring-gray-950"></span>
        </button>
        <div class="relative">
            <button type="button" data-lightbox-menu-button aria-expanded="false" aria-controls="gallery-lightbox-menu"
                class="{{ $glass }} {{ $focusRing }} transition-colors hover:bg-gray-950/80">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor" class="size-5 shrink-0">
                    <circle cx="5" cy="12" r="1.75" />
                    <circle cx="12" cy="12" r="1.75" />
                    <circle cx="19" cy="12" r="1.75" />
                </svg>
                Plus d'actions
            </button>
            <div id="gallery-lightbox-menu" data-lightbox-menu hidden
                class="absolute right-0 top-full mt-2 w-max min-w-64 max-w-[calc(100vw-2rem)] rounded-2xl bg-white p-2 shadow-xl ring-1 ring-black/5">
                <button type="button" data-lightbox-details aria-controls="photo-drawer" class="{{ $menuItem }}">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" class="text-primary-700 size-5 shrink-0">
                        <circle cx="12" cy="12" r="8.5" />
                        <path d="M12 11v5.5M12 7.5v.5" />
                    </svg>
                    Détails
                </button>
                <a href="#" data-lightbox-download download class="{{ $menuItem }}">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" class="text-primary-700 size-5 shrink-0">
                        <path d="M12 4v11M7 10.5l5 5 5-5M5 20h14" />
                    </svg>
                    Télécharger
                </a>
                <button type="button" data-lightbox-share aria-controls="photo-drawer" class="{{ $menuItem }}">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" class="text-primary-700 size-5 shrink-0">
                        <path d="M10 14a4.5 4.5 0 0 0 6.4 0l3-3a4.5 4.5 0 0 0-6.4-6.4l-1 1" />
                        <path d="M14 10a4.5 4.5 0 0 0-6.4 0l-3 3a4.5 4.5 0 0 0 6.4 6.4l1-1" />
                    </svg>
                    Partager la photo
                </button>
                <a href="#" data-lightbox-edit class="{{ $menuItem }}">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" class="text-primary-700 size-5 shrink-0">
                        <path d="M4 20h4L19 9a2.8 2.8 0 0 0-4-4L4 16v4Z" />
                        <path d="m13.5 6.5 4 4" />
                    </svg>
                    Modifier la photo
                </a>
            </div>
        </div>
        <button type="button" data-lightbox-close aria-label="Fermer" title="Fermer" class="{{ $glassIcon }}">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" class="size-5">
                <path d="M6 6l12 12M18 6 6 18" />
            </svg>
        </button>
    </div>
</template>

{{-- Matelot qui a envoyé la photo, en bas à droite --}}
<template data-gallery-lightbox-uploader>
    <p class="{{ $glass }} py-1 pl-1 pr-4 font-normal">
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
