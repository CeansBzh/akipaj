{{--
    Ajout de photos à un album : on choisit ses photos, ou on les fait glisser n'importe où sur la page,
    on les vérifie dans une grille de vignettes, puis on les envoie (resources/js/add-photo-form.js).
--}}

<x-member-layout>
    <div class="mx-auto max-w-4xl px-4 pb-6 pt-4 sm:px-6 sm:pt-6 lg:px-8">
        <a href="{{ route('photos.index', ['albums' => [$album->id]]) }}"
            class="text-primary-800 focus-visible:outline-primary-700 -ml-2 inline-flex min-h-11 items-center gap-1.5 rounded-full px-2 font-semibold underline-offset-4 hover:underline focus-visible:outline-2">
            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                <path d="m15 6-6 6 6 6" />
            </svg>
            Retour à l'album
        </a>

        <header class="mt-2">
            <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">Ajouter des photos</h1>
            <p class="mt-2 wrap-break-word text-lg text-gray-700">
                Dans l'album <strong class="font-bold text-gray-900">«&nbsp;{{ $album->title }}&nbsp;»</strong>
            </p>
            <p class="text-gray-600">{{ ucfirst($album->date->isoFormat('MMMM YYYY')) }}</p>
        </header>

        @include('photo.partials.add-photo-form')
    </div>
</x-member-layout>
