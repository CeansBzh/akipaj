@use('App\Models\Album')

@php
    $uploadAlbums = Album::orderByDesc('date')->orderByDesc('id')->get(['id', 'title', 'date']);
    $pill =
        'inline-flex min-h-11 items-center gap-2 rounded-full py-2 font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700';
@endphp

{{--
    « Ajouter mes photos » : sur la galerie filtrée sur un seul album (?albums[]=3), mène à l'ajout dans cet album ;
    ailleurs, on choisit d'abord l'album, vides compris. L'adresse est lue au clic, car la galerie la change sans recharger la page.
--}}
<div x-data="{
    addPhotos() {
        const albums = new URLSearchParams(location.search).getAll('albums[]');
        if (albums.length === 1 && /^\d+$/.test(albums[0])) {
            location.href = @js(route('photos.create', '__ALBUM__')).replace('__ALBUM__', albums[0]);
            return;
        }
        $dispatch('open-modal', 'choose-upload-album');
    },
}">
    {{-- Sur téléphone, un bouton rond fixé en bas à gauche de l'écran ; à partir de sm, une pilule dans la page --}}
    <button type="button" x-on:click="addPhotos()" title="Ajouter mes photos"
        class="{{ $pill }} bg-primary-700 hover:bg-primary-800 fixed bottom-5 left-4 z-30 size-14 shrink-0 justify-center text-white shadow-lg ring-4 ring-white sm:static sm:size-auto sm:px-5 sm:shadow-none sm:ring-0">
        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" class="size-6 shrink-0 sm:size-5">
            <path d="M12 15V4m0 0L8 8m4-4 4 4M4 15v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3" />
        </svg>
        <span class="sr-only sm:not-sr-only">Ajouter mes photos</span>
    </button>

    <x-modal name="choose-upload-album" max-width="md" focusable>
        {{-- La fenêtre tient dans la hauteur de l'écran (marges de x-modal déduites) : seule la liste défile --}}
        <div class="flex max-h-[calc(100dvh-4.5rem)] flex-col p-6">
            <h2 class="text-xl font-bold text-gray-900">Dans quel album ?</h2>
            <p class="mt-2 text-gray-700">Choisissez l'album où ajouter vos photos.</p>
            <ul class="mt-4 min-h-0 divide-y divide-gray-200 overflow-y-auto">
                @foreach ($uploadAlbums as $album)
                    <li>
                        <a href="{{ route('photos.create', $album) }}"
                            class="hover:bg-primary-50 focus-visible:outline-primary-700 flex min-h-14 flex-col justify-center rounded-xl px-3 py-2 transition-colors focus-visible:outline-2">
                            <span class="wrap-break-word font-semibold text-gray-900">{{ $album->title }}</span>
                            <span
                                class="text-sm text-gray-700">{{ ucfirst($album->date->isoFormat('MMMM YYYY')) }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')"
                    class="{{ $pill }} text-primary-800 hover:bg-primary-50 bg-white px-5 ring-1 ring-gray-300 ring-inset">Annuler</button>
                @can('create', Album::class)
                    <a href="{{ route('albums.create') }}"
                        class="{{ $pill }} bg-primary-700 hover:bg-primary-800 px-5 text-white">Créer un album</a>
                @endcan
            </div>
        </div>
    </x-modal>
</div>
