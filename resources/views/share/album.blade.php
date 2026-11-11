@php
    $cover = $photos->first();
    $sharedBy = $share->user?->name;
@endphp

{{--
    Album partagé : sa présentation, puis ses photos en mosaïque, de la plus ancienne à la plus récente.
    Une photo touchée s'ouvre dans la visionneuse, qui ne montre que les photos de l'album (resources/js/shared-photos.js).
--}}
<x-share-layout :title="$album->title" :image="$cover ? route('shares.file', [$share, $cover]) : null">
    <header class="px-4 pt-4 sm:px-6 sm:pt-6 lg:px-8">
        <x-application-logo class="bg-primary-700 h-12 rounded-xl px-2.5 py-2 shadow-sm" />
    </header>

    <main class="pb-16">
        <section aria-labelledby="shared-album-title"
            class="bg-primary-50 mx-4 mt-6 rounded-3xl p-5 sm:mx-6 sm:p-8 lg:mx-8">
            <p class="text-primary-800 font-semibold">
                {{ $sharedBy ? 'Album partagé par ' . $sharedBy : 'Album partagé' }}
            </p>
            <h1 id="shared-album-title" class="wrap-break-word mt-1 text-3xl font-bold text-gray-900 sm:text-4xl">
                {{ $album->title }}</h1>
            <p class="mt-2 text-gray-700">
                {{ ucfirst($album->date->isoFormat('MMMM YYYY')) }} ·
                {{ number_format($photoCount, 0, ',', "\u{202F}") }} {{ $photoCount > 1 ? 'photos' : 'photo' }}
            </p>
            @if ($album->description)
                <p class="wrap-break-word mt-4 max-w-prose leading-relaxed text-gray-800">{{ $album->description }}</p>
            @endif
            <p class="mt-4 max-w-prose leading-relaxed text-gray-800">
                Ouvrez une photo pour l'agrandir et la télécharger. Ce lien fonctionne jusqu'au
                {{ $share->expires_at->isoFormat('dddd D MMMM YYYY') }}.
            </p>
        </section>

        @if ($photoCount === 0)
            <p class="mx-4 mt-6 rounded-3xl bg-white px-6 py-12 text-center text-lg text-gray-800 sm:mx-6 lg:mx-8">
                Cet album n'a pas encore de photos.</p>
        @else
            <div class="@container mx-4 mt-6 sm:mx-6 lg:mx-8">
                <div class="photo-masonry" data-shared-grid>
                    @include('share.partials.photos', ['eagerCount' => 6])
                </div>
            </div>

            @if ($nextPageUrl)
                <div class="flex justify-center px-4 pt-10">
                    <button type="button" data-shared-more="{{ $nextPageUrl }}"
                        class="text-primary-800 hover:bg-primary-50 focus-visible:outline-primary-700 inline-flex min-h-11 items-center rounded-full bg-white px-5 py-2 font-semibold ring-1 ring-inset ring-gray-300 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-wait">
                        Afficher plus de photos
                    </button>
                </div>
            @endif
        @endif

        <p class="mx-auto mt-12 max-w-prose px-4 text-center leading-relaxed text-gray-700">
            Vous êtes membre du club ?
            <a href="{{ route('login') }}"
                class="text-primary-800 hover:text-primary-900 font-semibold underline underline-offset-4">Connectez-vous</a>
            pour retrouver l'album dans la galerie.
        </p>
    </main>

    @include('share.partials.lightbox-controls', ['canClose' => true])
</x-share-layout>
