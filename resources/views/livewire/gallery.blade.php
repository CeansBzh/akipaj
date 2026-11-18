@php
    $selectedAlbum = $this->selectedAlbum;
    $activeFilterCount = count($usersFilter) + count($albumsFilter);
    $pill =
        'inline-flex min-h-11 items-center gap-2 rounded-full py-2 font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700';
    $primaryColors = 'bg-primary-700 text-white hover:bg-primary-800';
    $primaryPill = $pill . ' px-5 ' . $primaryColors;
    $secondaryPill = $pill . ' px-5 bg-white text-primary-800 ring-1 ring-gray-300 ring-inset hover:bg-primary-50';
@endphp

{{--
    Galerie : les albums en bandeau, les filtres, puis toutes les photos en mosaïque, aux marges de la page.
    La visionneuse (resources/js/gallery.js) s'ouvre sur la vignette touchée et parcourt les photos chargées.
--}}
<div class="pb-28 sm:pb-16" x-data="{
    filtersOpen: false,
    isDesktop: window.matchMedia('(min-width: 64rem)').matches,
    loading: false,
    openFilters() {
        this.filtersOpen = true;
    },
    closeFilters() {
        if (!this.filtersOpen) return;
        this.filtersOpen = false;
        this.$nextTick(() => this.$refs.filtersToggle.focus());
    },
    async loadMore() {
        if (this.loading || $wire.loadedPages >= $wire.lastPage) return;
        this.loading = true;
        try {
            await $wire.$island('photos', { mode: 'append' }).loadMore();
        } finally {
            this.loading = false;
        }
        // Le bas de la galerie peut rester en vue après une courte page : on continue sans attendre un nouveau défilement
        await this.$nextTick();
        const end = this.$refs.galleryEnd.getBoundingClientRect();
        if (end.height > 0 && end.top < window.innerHeight + 1200) {
            this.loadMore();
        }
    },
}" x-on:gallery-load-more.window="loadMore()"
    x-on:resize.window.debounce.100ms="isDesktop = window.matchMedia('(min-width: 64rem)').matches; if (isDesktop) filtersOpen = false"
    x-on:keydown.escape.window="closeFilters()">

    <h1 class="sr-only">Photos</h1>

    {{--
        Bandeau des albums, du plus récent au plus ancien : un album touché filtre la galerie.
        Îlot rendu une seule fois (un changement de filtre ne le renvoie pas) : l'album choisi y est marqué par Alpine,
        d'après $wire.albumsFilter, et dès le toucher, sans attendre la réponse du serveur.
    --}}
    @island(name: 'album-strip')
    @if ($this->albums->isNotEmpty())
        <section aria-labelledby="gallery-albums-title" class="mt-4 border-b sm:mt-8" x-data="{
            atStart: true,
            atEnd: false,
            pendingId: undefined,
            get selectedId() {
                const filter = this.$wire.albumsFilter ?? [];
                return filter.length === 1 ? Number(filter[0]) : null;
            },
            get shownId() {
                return this.pendingId === undefined ? this.selectedId : this.pendingId;
            },
            async choose(id) {
                this.pendingId = this.shownId === id ? null : id;
                try {
                    await this.$wire.selectAlbum(id);
                } finally {
                    this.pendingId = undefined;
                }
            },
            update() {
                const strip = this.$refs.strip;
                this.atStart = strip.scrollLeft < 8;
                this.atEnd = strip.scrollLeft + strip.clientWidth > strip.scrollWidth - 8;
            },
            scroll(direction) {
                const strip = this.$refs.strip;
                const behavior = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
                strip.scrollBy({ left: direction * strip.clientWidth * 0.8, behavior });
            },
        }"
            x-init="update()">
            <div class="flex items-center gap-3 px-4 sm:gap-4 sm:px-6 lg:px-8">
                <h2 id="gallery-albums-title" class="text-xl font-bold text-gray-900">Albums</h2>
                <div class="ml-auto hidden gap-2 sm:flex">
                    <button type="button" x-on:click="scroll(-1)" x-bind:disabled="atStart"
                        aria-label="Albums précédents" title="Albums précédents"
                        class="focus-visible:outline-primary-700 inline-flex size-11 items-center justify-center rounded-full text-gray-900 ring-1 ring-inset ring-gray-300 transition-colors hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-40 disabled:hover:bg-transparent">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
                            <path d="m15 6-6 6 6 6" />
                        </svg>
                    </button>
                    <button type="button" x-on:click="scroll(1)" x-bind:disabled="atEnd"
                        aria-label="Albums suivants" title="Albums suivants"
                        class="focus-visible:outline-primary-700 inline-flex size-11 items-center justify-center rounded-full text-gray-900 ring-1 ring-inset ring-gray-300 transition-colors hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-40 disabled:hover:bg-transparent">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
                            <path d="m9 6 6 6-6 6" />
                        </svg>
                    </button>
                </div>
            </div>

            <ul x-ref="strip" x-on:scroll.debounce.50ms="update()" x-on:resize.window.debounce.100ms="update()"
                class="mt-3 flex snap-x snap-mandatory scroll-px-4 gap-3 overflow-x-auto overscroll-x-contain px-4 pb-3 pt-1 sm:scroll-px-6 sm:px-6 lg:scroll-px-8 lg:px-8">
                @foreach ($this->albums as $album)
                    @php($cover = $album->coverUrl())
                    <li class="w-60 shrink-0 snap-start sm:w-64" wire:key="strip-album-{{ $album->id }}">
                        <button type="button" x-on:click="choose({{ $album->id }})"
                            x-bind:aria-pressed="shownId === {{ $album->id }} ? 'true' : 'false'"
                            x-bind:class="shownId === {{ $album->id }} ? 'bg-primary-50 ring-2 ring-primary-700' : 'bg-white ring-1 ring-gray-200 hover:bg-primary-50'"
                            class="focus-visible:outline-primary-700 flex h-full w-full items-center gap-3 rounded-2xl p-2 pr-3 text-left ring-inset transition-colors focus-visible:outline-2 focus-visible:outline-offset-2">
                            <span class="relative size-16 shrink-0 overflow-hidden rounded-xl bg-gray-200">
                                @if ($cover)
                                    <img src="{{ $cover }}" alt="" width="64" height="64"
                                        loading="lazy" decoding="async" class="size-full object-cover">
                                @endif
                                <span x-show="shownId === {{ $album->id }}" x-cloak
                                        class="bg-primary-900/50 absolute inset-0 flex items-center justify-center text-white">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                            class="size-7">
                                            <path d="m5 12.5 4.5 4.5L19 7.5" />
                                        </svg>
                                    </span>
                            </span>
                            <span class="min-w-0">
                                <span
                                    class="wrap-break-word line-clamp-2 font-bold leading-tight text-gray-900">{{ $album->title }}</span>
                                <span
                                    class="mt-1 block text-sm leading-tight text-gray-700">{{ ucfirst($album->date->isoFormat('MMMM YYYY')) }}</span>
                                <span class="block text-sm leading-tight text-gray-700">
                                    {{ $album->photos_count }} {{ $album->photos_count > 1 ? 'photos' : 'photo' }}
                                </span>
                            </span>
                        </button>
                    </li>
                @endforeach
                @can('create', \App\Models\Album::class)
                    <li class="shrink-0 snap-start">
                        <a href="{{ route('albums.create') }}"
                            class="text-primary-800 hover:border-primary-700 hover:bg-primary-50 focus-visible:outline-primary-700 flex h-full min-h-20 items-center gap-2 whitespace-nowrap rounded-2xl border-2 border-dashed border-gray-300 px-5 font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            Créer un album
                        </a>
                    </li>
                @endcan
            </ul>
        </section>
    @endif
    @endisland

    @if ($selectedAlbum)
        @php
            $canShareAlbum = auth()->user()->can('share', $selectedAlbum);
            $canUpdateAlbum = auth()->user()->can('update', $selectedAlbum);
            $canDeleteAlbum = auth()->user()->can('delete', $selectedAlbum);
        @endphp
        <section aria-labelledby="gallery-album-title"
            class="sm:bg-primary-50 mx-4 mt-6 sm:mx-6 sm:rounded-3xl sm:p-8 lg:mx-8">
            <div class="flex items-start gap-2">
                <h2 id="gallery-album-title"
                    class="flex min-w-0 flex-1 flex-wrap items-baseline gap-x-3 gap-y-1 pt-2 sm:block sm:pt-0">
                    <span
                        class="wrap-break-word text-xl font-bold text-gray-900 sm:text-2xl">{{ $selectedAlbum->title }}</span>
                    <span class="font-sans text-base font-normal text-gray-700 sm:mt-1 sm:block">
                        {{ ucfirst($selectedAlbum->date->isoFormat('MMMM YYYY')) }} ·
                        {{ $selectedAlbum->photos_count }} {{ $selectedAlbum->photos_count > 1 ? 'photos' : 'photo' }}
                    </span>
                </h2>

                @if ($canShareAlbum || $canUpdateAlbum || $canDeleteAlbum)
                    {{-- Sur grand écran, les actions sont des boutons visibles ; ailleurs, elles sont rangées sous « Plus d'actions » --}}
                    <div class="relative -mr-2 shrink-0 lg:mr-0" x-data="{ open: false }" x-on:click.outside="open = false"
                        x-on:keydown.escape="if (open) { $event.stopPropagation(); open = false; $refs.actionsButton.focus() }">
                        <button type="button" x-ref="actionsButton" x-on:click="open = ! open"
                            x-bind:aria-expanded="open" aria-controls="gallery-album-actions"
                            aria-label="Plus d'actions sur l'album" title="Plus d'actions sur l'album"
                            class="focus-visible:outline-primary-700 inline-flex size-11 items-center justify-center rounded-full text-gray-700 transition-colors hover:bg-gray-900/5 focus-visible:outline-2 focus-visible:outline-offset-2 lg:hidden">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                <circle cx="12" cy="5" r="1.75" />
                                <circle cx="12" cy="12" r="1.75" />
                                <circle cx="12" cy="19" r="1.75" />
                            </svg>
                        </button>
                        <div id="gallery-album-actions" x-show="open" x-cloak x-on:click="open = false"
                            class="absolute right-0 z-30 mt-1 w-64 rounded-2xl bg-white p-1 shadow-lg ring-1 ring-gray-200 lg:static lg:mt-0 lg:flex! lg:w-auto lg:gap-2 lg:bg-transparent lg:p-0 lg:shadow-none lg:ring-0">
                            @if ($canShareAlbum)
                                <button type="button" x-on:click="$dispatch('open-modal', 'share-album')"
                                    class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 py-2 text-left font-semibold transition-colors lg:w-auto lg:gap-2 lg:rounded-full lg:bg-white lg:px-5 lg:ring-1 lg:ring-inset lg:ring-gray-300 lg:hover:bg-primary-50 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-primary-700 hover:bg-primary-50 text-gray-900">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                        class="size-5 shrink-0 text-gray-600">
                                        <path d="M10 14a4.5 4.5 0 0 0 6.4 0l3-3a4.5 4.5 0 0 0-6.4-6.4l-1 1" />
                                        <path d="M14 10a4.5 4.5 0 0 0-6.4 0l-3 3a4.5 4.5 0 0 0 6.4 6.4l1-1" />
                                    </svg>
                                    Partager l'album
                                </button>
                            @endif
                            @if ($canUpdateAlbum)
                                <a href="{{ route('albums.edit', $selectedAlbum) }}"
                                    class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 py-2 text-left font-semibold transition-colors lg:w-auto lg:gap-2 lg:rounded-full lg:bg-white lg:px-5 lg:ring-1 lg:ring-inset lg:ring-gray-300 lg:hover:bg-primary-50 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-primary-700 hover:bg-primary-50 text-gray-900">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                        class="size-5 shrink-0 text-gray-600">
                                        <path d="M4 20h4L19 9a2.8 2.8 0 0 0-4-4L4 16v4Z" />
                                        <path d="m13.5 6.5 4 4" />
                                    </svg>
                                    Modifier l'album
                                </a>
                            @endif
                            @if ($canDeleteAlbum)
                                <button type="button" x-on:click="$dispatch('open-modal', 'confirm-album-deletion')"
                                    class="flex min-h-11 w-full items-center gap-3 rounded-xl px-3 py-2 text-left font-semibold transition-colors lg:w-auto lg:gap-2 lg:rounded-full lg:bg-white lg:px-5 lg:ring-1 lg:ring-inset lg:ring-gray-300 lg:hover:bg-red-50 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-primary-700 text-red-700 hover:bg-red-50">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                        class="size-5 shrink-0">
                                        <path d="M4 7h16M10 11v6M14 11v6" />
                                        <path d="M6 7l1 12a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2l1-12M9 7V4h6v3" />
                                    </svg>
                                    Supprimer l'album
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            @if ($selectedAlbum->description)
                {{-- Sur téléphone, une description longue est réduite à trois lignes, dépliées par « Lire la suite » --}}
                <div class="mt-2 sm:mt-4" x-data="{
                    expanded: false,
                    overflows: false,
                    measure() {
                        if (this.expanded) return;
                        const description = this.$refs.description;
                        this.overflows = description.scrollHeight > description.clientHeight + 1;
                    },
                }" x-init="measure()"
                    x-on:resize.window.debounce.100ms="measure()">
                    <p id="gallery-album-description" x-ref="description" x-bind:data-expanded="expanded"
                        class="wrap-break-word max-sm:not-data-expanded:line-clamp-3 max-w-prose leading-relaxed text-gray-800">
                        {{ $selectedAlbum->description }}</p>
                    <button type="button" x-show="overflows || expanded" x-cloak x-on:click="expanded = ! expanded"
                        x-bind:aria-expanded="expanded" aria-controls="gallery-album-description"
                        x-text="expanded ? 'Réduire' : 'Lire la suite'"
                        class="text-primary-800 hover:text-primary-900 focus-visible:outline-primary-700 min-h-11 font-semibold underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 sm:hidden">
                        Lire la suite
                    </button>
                </div>
            @endif
            @if ($selectedAlbum->trips->isNotEmpty())
                <p class="mt-2 text-gray-800 sm:mt-4">
                    {{ $selectedAlbum->trips->count() > 1 ? 'Sorties' : 'Sortie' }} :
                    @foreach ($selectedAlbum->trips as $trip)
                        <a href="{{ route('trips.show', $trip) }}"
                            class="text-primary-800 hover:text-primary-900 font-semibold underline underline-offset-4">{{ $trip->title }}</a>{{ $loop->last ? '' : ', ' }}
                    @endforeach
                </p>
            @endif

            @if ($canShareAlbum)
                @php
                    $albumShareLink =
                        ($albumShareLink['albumId'] ?? null) === $selectedAlbum->id ? $albumShareLink : null;
                @endphp
                <x-modal name="share-album" max-width="md" focusable>
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900">Partager l'album</h2>
                        <p class="mt-2 leading-relaxed text-gray-700">
                            Créer un lien vers <span class="font-semibold">{{ $selectedAlbum->title }}</span> pour une
                            personne sans compte. Lien valable {{ App\Models\Share::DEFAULT_EXPIRY_DAYS }} jours.
                        </p>
                        <div class="mt-6 flex justify-between">
                            @if ($albumShareLink)
                                <x-share-link :url="$albumShareLink['url']" wire:key="album-share-{{ $albumShareLink['url'] }}" />
                            @else
                                <button type="button" wire:click="shareAlbum" wire:loading.attr="disabled"
                                    wire:target="shareAlbum" class="{{ $primaryPill }} disabled:cursor-wait">
                                    Créer un lien de partage
                                </button>
                            @endif
                            <button type="button" x-on:click="$dispatch('close')"
                                class="{{ $secondaryPill }}">Fermer</button>
                        </div>
                    </div>
                </x-modal>
            @endif
            @if ($canDeleteAlbum)
                <x-modal name="confirm-album-deletion" max-width="md" focusable>
                    <form method="post" action="{{ route('albums.destroy', $selectedAlbum) }}" class="p-6">
                        @csrf
                        @method('delete')
                        <h2 class="text-lg font-bold text-gray-900">Supprimer l'album « {{ $selectedAlbum->title }} »
                            ?</h2>
                        <p class="mt-2 text-gray-700">Ses photos seront supprimées avec lui.</p>
                        <div class="mt-6 flex flex-wrap justify-end gap-3">
                            <button type="button" x-on:click="$dispatch('close')" class="{{ $secondaryPill }}">
                                Annuler
                            </button>
                            <button type="submit"
                                class="{{ $pill }} bg-red-700 px-5 text-white hover:bg-red-800">
                                Supprimer l'album
                            </button>
                        </div>
                    </form>
                </x-modal>
            @endif
        </section>
    @endif

    <div class="mt-3 px-4 sm:mt-6 sm:px-6 lg:px-8">
        <div class="flex flex-wrap items-center gap-2 sm:gap-3">
            <button type="button" x-ref="filtersToggle" x-on:click="openFilters()"
                x-bind:aria-expanded="filtersOpen" aria-controls="gallery-filters" title="Filtres"
                class="focus-visible:outline-primary-700 sm:text-primary-800 sm:hover:bg-primary-50 -ml-2 inline-flex min-h-11 min-w-11 shrink-0 items-center justify-center gap-2 rounded-full text-gray-600 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 sm:ml-0 sm:bg-white sm:px-5 sm:py-2 sm:font-semibold sm:ring-1 sm:ring-inset sm:ring-gray-300 lg:hidden">
                <span class="relative shrink-0">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-6 sm:size-5">
                        <path d="M2 5h20" />
                        <path d="M6 12h12" />
                        <path d="M9 19h6" />
                    </svg>
                    @if ($activeFilterCount > 0)
                        <span aria-hidden="true"
                            class="bg-primary-600 absolute top-0 -right-1 size-2 rounded-full"></span>
                    @endif
                </span>
                <span class="sr-only sm:not-sr-only">Filtres</span>
                @if ($activeFilterCount > 0)
                    <span class="sr-only">(filtres actifs)</span>
                @endif
            </button>

            {{-- L'ordre en un bouton : il montre l'ordre actuel et passe à l'autre --}}
            <button type="button" wire:click="toggleSort"
                title="{{ $this->isOldestFirst() ? 'Afficher les plus récentes d\'abord' : 'Afficher les plus anciennes d\'abord' }}"
                class="focus-visible:outline-primary-700 sm:text-primary-800 sm:hover:bg-primary-50 order-last -mr-2 ml-auto inline-flex min-h-11 min-w-11 shrink-0 items-center justify-center gap-2 rounded-full text-gray-600 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 sm:order-none sm:mr-0 sm:ml-0 sm:bg-white sm:px-5 sm:py-2 sm:font-semibold sm:ring-1 sm:ring-inset sm:ring-gray-300">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="size-6 shrink-0 sm:size-5">
                    @if ($this->isOldestFirst())
                        <path d="m3 8 4-4 4 4" />
                        <path d="M7 4v16" />
                        <path d="M11 12h4" />
                        <path d="M11 16h7" />
                        <path d="M11 20h10" />
                    @else
                        <path d="m3 16 4 4 4-4" />
                        <path d="M7 20V4" />
                        <path d="M11 4h10" />
                        <path d="M11 8h7" />
                        <path d="M11 12h4" />
                    @endif
                </svg>
                <span class="sr-only sm:not-sr-only">
                    {{ $this->isOldestFirst() ? 'Plus anciennes d\'abord' : 'Plus récentes d\'abord' }}
                </span>
            </button>

            {{-- Voile du panneau des filtres sur téléphone : le toucher ferme le panneau --}}
            <div x-show="filtersOpen && ! isDesktop" x-cloak x-on:click="closeFilters()" aria-hidden="true"
                class="fixed inset-0 z-50 bg-gray-900/60 lg:hidden"></div>

            {{--
                Les filtres : un seul jeu de champs. Sur téléphone, un panneau de toute la largeur et de 60 % de la hauteur,
                en bas de l'écran ; à partir de lg, ses champs se rangent dans la ligne, à côté de l'ordre.
            --}}
            <div id="gallery-filters" x-bind:data-open="filtersOpen" x-bind:role="isDesktop ? null : 'dialog'"
                x-bind:aria-modal="isDesktop ? null : 'true'" aria-labelledby="gallery-filters-title"
                x-trap.noscroll="filtersOpen && ! isDesktop"
                class="data-open:flex fixed inset-x-0 bottom-0 z-50 hidden h-[80dvh] flex-col rounded-t-sm bg-white shadow-2xl lg:static lg:z-auto lg:flex lg:h-auto lg:flex-row lg:items-center lg:gap-3 lg:rounded-none lg:bg-transparent lg:shadow-none">
                <div class="grid grid-cols-3 border-b items-center border-gray-200 py-2 pl-5 pr-2 lg:hidden">
                    <button type="button" wire:click="resetFilters"
                        class="text-sm text-gray-500 text-left">Réinitialiser</button>
                    <h2 id="gallery-filters-title" class="text-md font-bold text-gray-900 text-center">Filtres</h2>
                    <button type="button" x-on:click="closeFilters()" aria-label="Fermer les filtres"
                        title="Fermer les filtres"
                        class="focus-visible:outline-primary-700 ml-auto inline-flex size-11 items-center justify-center rounded-full text-gray-500 transition-colors hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-offset-2">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-6">
                            <path d="M6 6l12 12M18 6 6 18" />
                        </svg>
                    </button>
                </div>

                <div class="min-h-0 flex-1 space-y-6 overflow-y-auto px-5 py-5 lg:contents lg:space-y-0">
                    <div class="relative" x-data="uploaderSelect" x-on:click.outside="close()"
                        x-on:keydown.escape="if (open) { $event.stopPropagation(); close(true) }">
                        <p aria-hidden="true" class="mb-2 font-semibold text-gray-900 lg:hidden">Envoyées par</p>
                        <button type="button" x-ref="trigger" x-on:click="toggle()" x-bind:aria-expanded="open"
                            aria-controls="gallery-uploaders-panel"
                            class="focus-visible:outline-primary-700 hover:bg-primary-50 flex min-h-11 w-full items-center gap-2 rounded-full bg-white py-1 pl-2 pr-3 text-left text-gray-900 ring-1 ring-inset ring-gray-300 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 lg:w-auto lg:max-w-80">
                            @if ($filteredUsers->isEmpty())
                                <span class="flex size-9 shrink-0 items-center justify-center text-gray-600">
                                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                        class="size-5">
                                        <circle cx="9" cy="8" r="3.5" />
                                        <path d="M2.5 20a6.5 6.5 0 0 1 13 0" />
                                        <path d="M16 4.6a3.5 3.5 0 0 1 0 6.8M18.5 14.5a6.5 6.5 0 0 1 3 5.5" />
                                    </svg>
                                </span>
                            @else
                                <span class="flex shrink-0 -space-x-3">
                                    @foreach ($filteredUsers->take(3) as $filteredUser)
                                        <x-user-avatar :user="$filteredUser" class="size-9 ring-2 ring-white" />
                                    @endforeach
                                </span>
                            @endif
                            <span class="min-w-0 flex-1 truncate">
                                <span class="sr-only">Envoyées par :</span>
                                <span>
                                    @if ($filteredUsers->isEmpty())
                                        Tous les matelots
                                    @elseif ($filteredUsers->count() === 1)
                                        {{ $filteredUsers->first()->name }}
                                    @else
                                        {{ $filteredUsers->count() }} matelots
                                    @endif
                                </span>
                            </span>
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="size-5 shrink-0 text-gray-600 transition-transform motion-reduce:transition-none"
                                x-bind:class="open && 'rotate-180'">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <div id="gallery-uploaders-panel" x-show="open" x-cloak
                            class="mt-2 rounded-2xl bg-white p-2 ring-1 ring-gray-200 lg:absolute lg:left-0 lg:z-30 lg:w-80 lg:shadow-lg">
                            <label for="gallery-uploaders-search" class="sr-only">Rechercher un matelot</label>
                            <input id="gallery-uploaders-search" x-ref="search" type="search" x-model="query"
                                placeholder="Rechercher un matelot" autocomplete="off"
                                class="focus:border-primary-700 focus:ring-primary-700 min-h-11 w-full rounded-full border-gray-300 px-4 py-2 text-base text-gray-900 placeholder:text-gray-500">
                            <fieldset class="mt-2">
                                <legend class="sr-only">Envoyées par</legend>
                                {{--
                                    Liste rendue une seule fois. Les cases passent par Alpine et non par wire:model : dans un îlot,
                                    wire:model ne rendrait que l'îlot, sans le nombre de photos ni le bouton des filtres.
                                --}}
                                <ul class="max-h-72 overflow-y-auto">
                                    @island(name: 'uploader-options')
                                    @foreach ($this->uploaders as $uploader)
                                        <li wire:key="filter-user-{{ $uploader->id }}" data-uploader-name="{{ $uploader->name }}"
                                            x-show="matches(@js($uploader->name))">
                                            <label
                                                class="hover:bg-primary-50 has-checked:bg-primary-50 has-focus-visible:outline-primary-700 has-focus-visible:outline-2 flex min-h-12 cursor-pointer items-center gap-3 rounded-xl px-2 py-1.5 text-gray-900 transition-colors">
                                                <input type="checkbox" value="{{ $uploader->id }}"
                                                    x-bind:checked="isChosen({{ $uploader->id }})"
                                                    x-on:change="choose({{ $uploader->id }}, $event.target.checked)"
                                                    class="text-primary-700 focus:ring-primary-700 size-5 shrink-0 rounded border-gray-400">
                                                <x-user-avatar :user="$uploader" class="size-9" />
                                                <span class="wrap-break-word min-w-0">{{ $uploader->name }}</span>
                                            </label>
                                        </li>
                                    @endforeach
                                    @endisland
                                </ul>
                                <p x-show="matchCount === 0" x-cloak class="px-2 py-3 text-gray-700">
                                    Aucun matelot ne correspond.
                                </p>
                            </fieldset>
                        </div>
                    </div>

                    {{--
                        « Album » : un seul album, choisi en tapant une partie de son titre. Îlot rendu une seule fois :
                        l'album choisi vient de $wire.albumsFilter.
                    --}}
                    @island(name: 'album-combobox')
                    @php
                        $comboboxAlbums = $this->albums
                            ->map(
                                fn(\App\Models\Album $album) => [
                                    'id' => $album->id,
                                    'title' => $album->title,
                                    'details' =>
                                        ucfirst($album->date->isoFormat('MMMM YYYY')) .
                                        ' · ' .
                                        $album->photos_count .
                                        ($album->photos_count > 1 ? ' photos' : ' photo'),
                                ],
                            )
                            ->values();
                    @endphp
                    <div class="relative lg:w-72" x-data="albumCombobox(@js($comboboxAlbums))" x-on:click.outside="if (open) close()">
                        <label for="gallery-album-input" class="mb-2 block font-semibold text-gray-900 lg:sr-only">
                            Album
                        </label>
                        <div class="relative">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"
                                class="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-gray-600">
                                <rect x="3" y="5" width="18" height="15" rx="2.5" />
                                <circle cx="9" cy="10" r="1.75" />
                                <path d="m21 16-4.5-4.5L7 20" />
                            </svg>
                            <input id="gallery-album-input" type="text" role="combobox" autocomplete="off"
                                x-model="query" x-on:input="onInput()" x-on:focus="show()" x-on:click="show()"
                                x-on:keydown.arrow-down.prevent="move(1)" x-on:keydown.arrow-up.prevent="move(-1)"
                                x-on:keydown.enter.prevent="open ? choose(options[activeIndex]) : show()"
                                x-on:keydown.escape="if (open) { $event.stopPropagation(); close() }"
                                x-on:keydown.tab="if (open) close()" x-bind:aria-expanded="open"
                                aria-controls="gallery-album-listbox" aria-autocomplete="list"
                                x-bind:aria-activedescendant="open ? optionId(activeIndex) : null"
                                placeholder="Tous les albums"
                                class="focus:border-primary-700 focus:ring-primary-700 min-h-11 w-full rounded-full border-gray-300 bg-white py-2 pl-11 pr-10 text-base text-gray-900 placeholder:text-gray-900">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="pointer-events-none absolute right-3 top-1/2 size-5 -translate-y-1/2 text-gray-600">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </div>
                        <ul x-ref="listbox" id="gallery-album-listbox" role="listbox" aria-label="Albums"
                            x-show="open" x-cloak
                            class="mt-2 max-h-72 overflow-y-auto overscroll-contain rounded-2xl bg-white p-1 ring-1 ring-gray-200 lg:absolute lg:z-30 lg:w-96 lg:shadow-lg">
                            <template x-for="(option, index) in options" x-bind:key="option.id ?? 'all'">
                                <li role="option" x-bind:id="optionId(index)" x-bind:data-index="index"
                                    x-bind:aria-selected="option.id === selectedId ? 'true' : 'false'"
                                    x-on:mousedown.prevent x-on:click="choose(option)"
                                    x-on:mousemove="activeIndex = index"
                                    x-bind:class="index === activeIndex && 'bg-primary-50'"
                                    class="flex min-h-11 cursor-pointer items-center gap-3 rounded-xl px-3 py-2">
                                    <span class="min-w-0 flex-1">
                                        <span class="wrap-break-word block font-semibold text-gray-900"
                                            x-text="option.title"></span>
                                        <span x-show="option.details" class="block text-sm text-gray-700"
                                            x-text="option.details"></span>
                                    </span>
                                    <svg x-show="option.id === selectedId" aria-hidden="true" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2.5"
                                        stroke-linecap="round" stroke-linejoin="round"
                                        class="text-primary-700 size-5 shrink-0">
                                        <path d="m5 12.5 4.5 4.5L19 7.5" />
                                    </svg>
                                </li>
                            </template>
                            <li x-show="options.length === 1 && query" role="presentation"
                                class="px-3 py-2 text-gray-700">
                                Aucun album ne correspond.
                            </li>
                        </ul>
                    </div>
                    @endisland
                </div>

                <div class="border-t border-gray-200 px-5 py-3 lg:hidden">
                    <button type="button" x-on:click="closeFilters()"
                        class="{{ $primaryPill }} w-full justify-center">
                        @if ($photoCount === 0)
                            Aucune photo à afficher
                        @else
                            Afficher
                            {{ $photoCount > 1 ? 'les ' . number_format($photoCount, 0, ',', "\u{202F}") . ' photos' : 'la photo' }}
                        @endif
                    </button>
                </div>
            </div>

            <p class="min-w-0 text-gray-700">
                {{ number_format($photoCount, 0, ',', "\u{202F}") }} {{ $photoCount > 1 ? 'photos' : 'photo' }}
            </p>

            {{-- Sa liste de tous les albums ne change pas avec les filtres : îlot rendu une seule fois --}}
            <div class="sm:ml-auto">
                @island(name: 'add-photos')
                    <x-add-photos-button />
                @endisland
            </div>
        </div>
    </div>

    @if ($photoCount === 0)
        <div class="mx-4 mt-6 rounded-3xl bg-gray-50 px-6 py-12 text-center sm:mx-6 lg:mx-8">
            <p class="text-lg text-gray-800">
                @if ($selectedAlbum)
                    Cet album n'a pas encore de photos.
                @elseif ($activeFilterCount > 0)
                    Aucune photo ne correspond à ces filtres.
                @else
                    Aucune photo n'a encore été ajoutée.
                @endif
            </p>
        </div>
    @endif

    {{--
        Mosaïque : une seule grille pour toutes les photos et les titres des groupes, qui prennent toute la largeur.
        Chaque page chargée s'ajoute à la fin de l'îlot, donc de la grille. Pendant qu'un filtre ou l'ordre change,
        les photos affichées pâlissent : on voit tout de suite que la galerie se met à jour.
    --}}
    <div class="@container mx-4 mt-6 transition-opacity motion-reduce:transition-none sm:mx-6 lg:mx-8"
        wire:loading.delay.shortest.class="opacity-40" wire:loading.attr="aria-busy"
        wire:target="selectAlbum, toggleSort, resetFilters, usersFilter, albumsFilter">
        <div class="photo-masonry" data-gallery-grid>
            @island(name: 'photos', always: true)
                @foreach ($this->photoGroups as $group)
                    @if ($group['title'])
                        <h2 wire:key="group-{{ $group['key'] }}"
                            class="col-span-full flex flex-wrap items-baseline gap-x-3 gap-y-1 pb-3 pt-8 first:pt-2">
                            <span class="wrap-break-word text-xl font-bold text-gray-900">{{ $group['title'] }}</span>
                            <span class="font-sans text-base font-normal text-gray-700">{{ $group['details'] }}</span>
                        </h2>
                    @endif
                    @foreach ($group['photos'] as $photo)
                        @include('livewire.partials.gallery-photo', [
                            'photo' => $photo,
                            'isEager' => $loop->parent->first && $loop->index < 6 && $this->rendersFirstPage(),
                        ])
                    @endforeach
                @endforeach
            @endisland
        </div>
    </div>

    <div x-ref="galleryEnd" x-show="$wire.loadedPages < $wire.lastPage" x-cloak
        x-intersect.margin.1200px="loadMore()" class="flex justify-center px-4 pt-10">
        <button type="button" x-on:click="loadMore()" x-bind:disabled="loading"
            class="{{ $secondaryPill }} disabled:cursor-wait">
            <span x-show="! loading">Afficher plus de photos</span>
            <span x-show="loading" x-cloak>Chargement des photos…</span>
        </button>
    </div>

    @include('livewire.partials.gallery-lightbox')

    {{-- Matelots que la visionneuse montre sous chaque photo : îlot rendu une seule fois --}}
    @island(name: 'lightbox-uploaders')
        @foreach ($this->uploaders as $uploader)
            <template data-gallery-uploader="{{ $uploader->id }}" wire:key="lightbox-uploader-{{ $uploader->id }}">
                <x-user-avatar :user="$uploader" class="size-9" />
                <span class="font-semibold">{{ $uploader->name }}</span>
            </template>
        @endforeach
    @endisland
</div>
