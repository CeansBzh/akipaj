@php
    $pill = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-full font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700';
    $primaryPill = $pill.' px-5 bg-primary-700 text-white hover:bg-primary-800 disabled:cursor-not-allowed disabled:bg-gray-400';
    $secondaryPill = $pill.' px-5 bg-white text-primary-800 ring-1 ring-gray-300 ring-inset hover:bg-primary-50';
    $plusIcon = '<svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0"><path d="M12 5v14M5 12h14" /></svg>';
@endphp

{{--
    Le script remplit la grille des vignettes à mesure que les photos sont prêtes, et les envoie par lots.
    Sans photo choisie, seule la carte « Choisir des photos » est affichée ; ensuite, la grille et la barre d'envoi.
--}}
<form action="{{ route('photos.store', ['album' => $album]) }}" method="POST" enctype="multipart/form-data"
    class="mt-6 sm:mt-8" data-upload-form>
    @csrf
    <input type="file" name="files[]" multiple accept="image/png, image/jpeg, image/gif" class="hidden"
        data-file-input>

    <div role="alert" @class(['mb-6 rounded-2xl bg-red-50 p-4 text-red-800 ring-1 ring-red-200 ring-inset', 'hidden' => ! $errors->any()])
        data-errors>
        <ul class="space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>

    {{-- Carte de départ, sur papier quadrillé : la flotte du club au trait, puis le bouton pour choisir les photos --}}
    <div class="blueprint rounded-3xl px-5 pt-8 pb-7 text-center ring-1 ring-primary-100 ring-inset sm:px-10 sm:pt-10 sm:pb-9"
        data-drop-card>
        <div aria-hidden="true" class="flex h-36 items-end justify-center gap-3 text-primary-900 sm:h-56 sm:gap-10">
            <x-boat-drawing.catamaran class="h-[78%] w-auto" />
            <x-boat-drawing.sloop class="h-full w-auto" />
            <x-boat-drawing.ketch mirrored class="h-[88%] w-auto" />
        </div>

        <button type="button" class="{{ $primaryPill }} mt-7 min-h-14 px-7 text-lg shadow-md" data-pick-files>
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" class="size-6 shrink-0">
                <rect x="3" y="5" width="18" height="15" rx="3" />
                <circle cx="9" cy="10.5" r="1.75" />
                <path d="m21 16-4.5-4.5L8 20" />
            </svg>
            Choisir des photos
        </button>
        <p class="mt-3 hidden text-gray-800 pointer-fine:block">ou faites-les glisser n'importe où sur cette page</p>
        <p class="mx-auto mt-3 max-w-prose text-sm text-gray-700">
            JPG, PNG ou GIF. Vos photos seront compressées, Akipaj ne sauvegarde pas les photos en qualité originale.
        </p>
    </div>

    {{-- Photos choisies, en vignettes, chacune retirable avant l'envoi --}}
    <section aria-labelledby="upload-selection-title" class="hidden" data-selection>
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 id="upload-selection-title" class="text-xl font-bold text-gray-900" data-selection-title></h2>
                <p class="mt-0.5 text-gray-700" aria-live="polite" data-preparing></p>
            </div>
            <div class="flex flex-wrap items-center gap-2 in-data-uploading:hidden">
                <button type="button" class="{{ $secondaryPill }}" data-pick-files>
                    {!! $plusIcon !!}
                    Ajouter d'autres photos
                </button>
                <button type="button"
                    class="inline-flex min-h-11 items-center rounded-full px-3 font-semibold text-primary-800 underline underline-offset-4 hover:text-primary-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700"
                    x-on:click="$dispatch('open-modal', 'confirm-clear-photos')">
                    Tout retirer
                </button>
            </div>
        </div>

        <ul class="mt-4 grid grid-cols-[repeat(auto-fill,minmax(6rem,1fr))] gap-2 sm:grid-cols-[repeat(auto-fill,minmax(7.5rem,1fr))]"
            data-tiles></ul>
    </section>

    {{-- Barre d'envoi, en bas de l'écran pendant qu'on parcourt les vignettes --}}
    <div class="sticky bottom-0 z-30 hidden pt-6 pb-3 sm:pb-6" data-action-bar>
        <div class="rounded-3xl bg-white p-2 shadow-xl ring-1 ring-gray-200 sm:p-4 sm:pl-6">
            <div class="flex items-center gap-3" data-ready>
                <x-sailboat class="hidden h-10 w-12 shrink-0 text-primary-700 sm:block" />
                <div class="hidden min-w-0 grow sm:block">
                    <p class="text-sm text-gray-700">Cap sur l'album</p>
                    <p class="truncate font-bold text-gray-900">«&nbsp;{{ $album->title }}&nbsp;»</p>
                </div>
                <button type="submit" class="{{ $primaryPill }} min-h-12 w-full shrink-0 sm:w-auto" data-submit>
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                        <path d="M12 16V4m-5 5 5-5 5 5M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3" />
                    </svg>
                    <span data-submit-label>Ajouter mes photos</span>
                </button>
            </div>

            {{-- Pendant l'envoi : un voilier avance sur la barre jusqu'à la bouée d'arrivée --}}
            <div class="hidden px-3 pt-1 sm:p-0" data-progress>
                <div class="flex items-baseline justify-between gap-3">
                    <p class="font-bold text-gray-900">Envoi des photos…</p>
                    <p class="text-sm text-gray-700" data-progress-label></p>
                </div>
                <div role="progressbar" aria-label="Envoi des photos" aria-valuemin="0" class="relative mt-1 h-11"
                    data-progress-bar>
                    <div class="absolute right-7 bottom-2 left-5 h-1.5 rounded-full bg-primary-100"></div>
                    <div class="absolute bottom-2 left-5 h-1.5 w-0 rounded-full bg-primary-700 motion-safe:transition-[width] motion-safe:duration-500"
                        data-progress-fill></div>
                    <x-sailboat :waves="false" data-progress-boat
                        class="absolute bottom-3 left-5 h-8 w-10 -translate-x-1/2 text-primary-800 motion-safe:transition-[left] motion-safe:duration-500" />
                    <svg aria-hidden="true" viewBox="0 0 24 32" fill="none" stroke="currentColor" stroke-width="1.75"
                        stroke-linecap="round" stroke-linejoin="round" class="absolute right-0 bottom-0.5 h-8 w-6 text-primary-700">
                        <path d="M12 3v17" />
                        <path d="M12 3h8l-2.5 3 2.5 3h-8" fill="currentColor" />
                        <path d="M6 23q6-7 12 0Z" fill="currentColor" fill-opacity=".15" />
                        <path d="M3 27q3-2 6 0t6 0 6 0" />
                    </svg>
                </div>
            </div>
        </div>
    </div>
</form>

<x-modal name="confirm-clear-photos" max-width="md" focusable>
    <div class="p-6">
        <h2 class="text-xl font-bold text-gray-900">Retirer toutes les photos ?</h2>
        <p class="mt-2 text-gray-700">Elles ne seront pas ajoutées à l'album. Vous pourrez en choisir d'autres.</p>
        <div class="mt-6 flex flex-wrap justify-end gap-3">
            <button type="button" x-on:click="$dispatch('close')" class="{{ $secondaryPill }}">Garder mes photos</button>
            <button type="button" x-on:click="$dispatch('close')" class="{{ $primaryPill }}" data-confirm-clear>
                Tout retirer
            </button>
        </div>
    </div>
</x-modal>

{{-- Pendant qu'on fait glisser des fichiers au-dessus de la page --}}
<div class="fixed inset-0 z-50 hidden bg-primary-900/95 p-3 sm:p-6" data-drop-overlay>
    <div class="flex size-full flex-col items-center justify-center rounded-[2rem] border-2 border-dashed border-white/70 px-6 text-center text-white">
        <x-sailboat class="h-36 w-auto motion-safe:animate-sail-in sm:h-52" />
        <p class="mt-6 text-3xl font-bold">Déposez vos photos</p>
        <p class="mt-2 max-w-prose text-lg break-words">Elles rejoindront l'album «&nbsp;{{ $album->title }}&nbsp;».</p>
    </div>
</div>

{{-- Vignette d'une photo choisie : data-state vaut preparing, ready ou sent --}}
<template id="photo-tile-template">
    <li class="group/tile relative aspect-square overflow-hidden rounded-xl bg-primary-100" data-state="preparing">
        <canvas width="240" height="240" role="img" class="size-full"></canvas>
        <span class="absolute inset-0 hidden items-center justify-center bg-primary-100 group-data-[state=preparing]/tile:flex">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" class="size-7 text-primary-700 motion-safe:animate-spin">
                <path d="M12 3a9 9 0 1 0 9 9" />
            </svg>
            <span class="sr-only">En préparation</span>
        </span>
        <span class="absolute inset-0 hidden flex-col items-center justify-center gap-1 bg-primary-900/60 font-semibold text-white group-data-[state=sent]/tile:flex">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round" class="size-7">
                <path d="m5 12.5 4.5 4.5L19 7.5" />
            </svg>
            Envoyée
        </span>
        <button type="button"
            class="absolute top-1 right-1 inline-flex min-h-9 items-center gap-1 rounded-full bg-gray-900/60 py-1 pr-3 pl-2 text-sm font-semibold text-white backdrop-blur-md transition-colors before:absolute before:-inset-1 hover:bg-gray-900/80 focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-white in-data-uploading:hidden group-data-[state=sent]/tile:hidden"
            data-remove>
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                stroke-linecap="round" stroke-linejoin="round" class="size-4 shrink-0">
                <path d="M6 6l12 12M18 6 6 18" />
            </svg>
            Retirer<span class="sr-only" data-remove-name></span>
        </button>
    </li>
</template>

@push('scripts')
    @vite('resources/js/add-photo-form.js')
@endpush
