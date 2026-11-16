@use('App\Models\Album')

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
    loading: false,
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
}" x-on:gallery-load-more.window="loadMore()">

    <h1 class="sr-only">Photos</h1>

    {{-- Bandeau des albums, du plus récent au plus ancien : un album touché filtre la galerie --}}
    @if ($this->albums->isNotEmpty())
        <section aria-labelledby="gallery-albums-title" class="mt-4 border-b sm:mt-8" x-data="{
            atStart: true,
            atEnd: false,
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
            <div class="flex items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <h2 id="gallery-albums-title" class="text-xl font-bold text-gray-900">Albums</h2>
                <div class="hidden gap-2 sm:flex">
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
                    @php
                        $isSelected = count($albumsFilter) === 1 && (int) reset($albumsFilter) === $album->id;
                        $cover = $album->imagePath ?? $album->oldestPhoto?->thumb_path;
                    @endphp
                    <li class="w-60 shrink-0 snap-start sm:w-64" wire:key="strip-album-{{ $album->id }}">
                        <button type="button" wire:click="selectAlbum({{ $album->id }})"
                            aria-pressed="{{ $isSelected ? 'true' : 'false' }}" @class([
                                'flex h-full w-full items-center gap-3 rounded-2xl p-2 pr-3 text-left ring-inset transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700',
                                'bg-white ring-1 ring-gray-200 hover:bg-primary-50' => !$isSelected,
                                'bg-primary-50 ring-2 ring-primary-700' => $isSelected,
                            ])>
                            <span class="relative size-16 shrink-0 overflow-hidden rounded-xl bg-gray-200">
                                @if ($cover)
                                    <img src="{{ $cover }}" alt="" width="64" height="64"
                                        loading="lazy" decoding="async" class="size-full object-cover">
                                @endif
                                @if ($isSelected)
                                    <span
                                        class="bg-primary-900/50 absolute inset-0 flex items-center justify-center text-white">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                            class="size-7">
                                            <path d="m5 12.5 4.5 4.5L19 7.5" />
                                        </svg>
                                    </span>
                                @endif
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
                @can('create', Album::class)
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

    {{-- Ordre et filtres --}}
    <div class="mt-3 px-4 sm:mt-6 sm:px-6 lg:px-8">
        {{--
            Sur téléphone : filtres et nombre de photos à gauche, ordre en icône à droite.
            À partir de sm : ordre, filtres et nombre de photos, puis « Ajouter mes photos » à droite.
        --}}
        <div class="flex items-center gap-2 sm:gap-3">
            <button type="button" x-on:click="filtersOpen = ! filtersOpen" x-bind:aria-expanded="filtersOpen"
                aria-controls="gallery-filters" title="Filtres"
                class="focus-visible:outline-primary-700 -ml-2 inline-flex min-h-11 min-w-11 shrink-0 items-center justify-center gap-2 rounded-full text-gray-500 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 sm:ml-0 sm:bg-white sm:px-5 sm:py-2 sm:font-semibold sm:text-primary-800 sm:ring-1 sm:ring-inset sm:ring-gray-300 sm:hover:bg-primary-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 5h20" />
                    <path d="M6 12h12" />
                    <path d="M9 19h6" />
                </svg>
                <span class="sr-only sm:not-sr-only">Filtres</span>
                @if ($activeFilterCount > 0)
                    <span class="bg-primary-700 rounded-full px-2 text-sm font-bold text-white">
                        {{ $activeFilterCount }}<span class="sr-only"> actifs</span>
                    </span>
                @endif
            </button>

            <p class="min-w-0 text-gray-700">
                {{ number_format($photoCount, 0, ',', "\u{202F}") }} {{ $photoCount > 1 ? 'photos' : 'photo' }}
            </p>

            {{-- L'ordre en un bouton : il montre l'ordre actuel et passe à l'autre --}}
            <button type="button" wire:click="toggleSort"
                title="{{ $this->isOldestFirst() ? 'Afficher les plus récentes d\'abord' : 'Afficher les plus anciennes d\'abord' }}"
                class="focus-visible:outline-primary-700 order-last -mr-2 ml-auto inline-flex min-h-11 min-w-11 shrink-0 items-center justify-center gap-2 rounded-full text-gray-500 transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 sm:order-first sm:mr-0 sm:ml-0 sm:bg-white sm:px-5 sm:py-2 sm:font-semibold sm:text-primary-800 sm:ring-1 sm:ring-inset sm:ring-gray-300 sm:hover:bg-primary-50">
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

            <div class="sm:ml-auto">
                <x-add-photos-button />
            </div>
        </div>
    </div>

    {{-- Filtres dépliés : des cases à cocher en pilules, qui filtrent dès qu'on les touche --}}
    <div id="gallery-filters" x-show="filtersOpen" x-collapse x-cloak class="px-4 sm:px-6 lg:px-8">
        <div class="mt-4 grid gap-8 rounded-3xl bg-gray-50 p-5 sm:p-6 md:grid-cols-2">
            <fieldset class="min-w-0">
                <legend class="text-lg font-bold text-gray-900">Envoyées par</legend>
                <div class="mt-3 flex max-h-80 flex-wrap gap-2 overflow-y-auto p-1">
                    @foreach ($this->uploaders as $uploader)
                        <label class="relative" wire:key="filter-user-{{ $uploader->id }}">
                            <input type="checkbox" wire:model.live="usersFilter" value="{{ $uploader->id }}"
                                class="peer sr-only">
                            <span
                                class="peer-checked:bg-primary-700 peer-checked:ring-primary-700 peer-focus-visible:outline-primary-700 hover:bg-primary-50 peer-checked:hover:bg-primary-800 inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-3xl bg-white py-1 pl-1 pr-4 text-gray-900 ring-1 ring-inset ring-gray-300 transition-colors peer-checked:text-white peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-checked:[&>svg]:block">
                                <x-user-avatar :user="$uploader" class="size-9" />
                                {{ $uploader->name }}
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                    class="hidden size-5 shrink-0">
                                    <path d="m5 12.5 4.5 4.5L19 7.5" />
                                </svg>
                            </span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <fieldset class="min-w-0">
                <legend class="text-lg font-bold text-gray-900">Albums</legend>
                <div class="mt-3 max-h-80 overflow-y-auto p-1">
                    @foreach ($this->albums->groupBy(fn(Album $album) => $album->date->year) as $year => $albumsOfYear)
                        <p class="mt-3 text-sm font-bold text-gray-700 first:mt-0">{{ $year }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($albumsOfYear as $album)
                                <label class="relative" wire:key="filter-album-{{ $album->id }}">
                                    <input type="checkbox" wire:model.live="albumsFilter"
                                        value="{{ $album->id }}" class="peer sr-only">
                                    <span
                                        class="peer-checked:bg-primary-700 peer-checked:ring-primary-700 peer-focus-visible:outline-primary-700 hover:bg-primary-50 peer-checked:hover:bg-primary-800 inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-3xl bg-white px-4 py-2 text-gray-900 ring-1 ring-inset ring-gray-300 transition-colors peer-checked:text-white peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-checked:[&>svg]:block">
                                        {{ $album->title }}
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                            stroke-linejoin="round" class="hidden size-5 shrink-0">
                                            <path d="m5 12.5 4.5 4.5L19 7.5" />
                                        </svg>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </fieldset>
        </div>
    </div>

    {{-- Filtres choisis, chacun retirable, même quand les filtres sont repliés (par exemple en venant d'un profil) --}}
    @if ($activeFilterCount > 0)
        <div class="mt-4 flex flex-wrap items-center gap-2 px-4 sm:px-6 lg:px-8">
            @foreach ($filteredUsers as $filteredUser)
                <button type="button" wire:click="removeUserFilter({{ $filteredUser->id }})"
                    wire:key="chip-user-{{ $filteredUser->id }}"
                    class="bg-primary-50 ring-primary-200 hover:bg-primary-100 focus-visible:outline-primary-700 inline-flex min-h-11 items-center gap-2 rounded-full py-1 pl-1 pr-3 text-gray-900 ring-1 ring-inset transition-colors focus-visible:outline-2 focus-visible:outline-offset-2">
                    <x-user-avatar :user="$filteredUser" class="size-9" />
                    <span>Envoyées par <strong>{{ $filteredUser->name }}</strong></span>
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="size-5 shrink-0 text-gray-700">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                    <span class="sr-only">(retirer ce filtre)</span>
                </button>
            @endforeach
            @foreach ($filteredAlbums as $filteredAlbum)
                <button type="button" wire:click="removeAlbumFilter({{ $filteredAlbum->id }})"
                    wire:key="chip-album-{{ $filteredAlbum->id }}"
                    class="bg-primary-50 ring-primary-200 hover:bg-primary-100 focus-visible:outline-primary-700 inline-flex min-h-11 items-center gap-2 rounded-full py-2 pl-4 pr-3 text-gray-900 ring-1 ring-inset transition-colors focus-visible:outline-2 focus-visible:outline-offset-2">
                    <span>Album <strong>{{ $filteredAlbum->title }}</strong></span>
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="size-5 shrink-0 text-gray-700">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                    <span class="sr-only">(retirer ce filtre)</span>
                </button>
            @endforeach
            <button type="button" wire:click="resetFilters"
                class="text-primary-800 hover:text-primary-900 focus-visible:outline-primary-700 inline-flex min-h-11 items-center rounded-full px-3 font-semibold underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2">
                Voir toutes les photos
            </button>
        </div>
    @endif

    {{-- L'album choisi seul est présenté au-dessus de ses photos : il remplace l'ancienne page de l'album --}}
    @if ($selectedAlbum)
        <section aria-labelledby="gallery-album-title"
            class="bg-primary-50 mx-4 mt-6 rounded-3xl p-5 sm:mx-6 sm:p-8 lg:mx-8">
            <h2 id="gallery-album-title" class="wrap-break-word text-2xl font-bold text-gray-900">
                {{ $selectedAlbum->title }}</h2>
            <p class="mt-1 text-gray-700">
                {{ ucfirst($selectedAlbum->date->isoFormat('MMMM YYYY')) }} ·
                {{ $selectedAlbum->photos_count }} {{ $selectedAlbum->photos_count > 1 ? 'photos' : 'photo' }}
            </p>
            @if ($selectedAlbum->description)
                <p class="wrap-break-word mt-4 max-w-prose leading-relaxed text-gray-800">
                    {{ $selectedAlbum->description }}</p>
            @endif
            @if ($selectedAlbum->trips->isNotEmpty())
                <p class="mt-4 text-gray-800">
                    {{ $selectedAlbum->trips->count() > 1 ? 'Sorties' : 'Sortie' }} :
                    @foreach ($selectedAlbum->trips as $trip)
                        <a href="{{ route('trips.show', $trip) }}"
                            class="text-primary-800 hover:text-primary-900 font-semibold underline underline-offset-4">{{ $trip->title }}</a>{{ $loop->last ? '' : ', ' }}
                    @endforeach
                </p>
            @endif
            <div class="mt-5 flex flex-wrap gap-3">
                @can('share', $selectedAlbum)
                    <button type="button" x-on:click="$dispatch('open-modal', 'share-album')"
                        class="{{ $secondaryPill }}">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                            <path d="M10 14a4.5 4.5 0 0 0 6.4 0l3-3a4.5 4.5 0 0 0-6.4-6.4l-1 1" />
                            <path d="M14 10a4.5 4.5 0 0 0-6.4 0l-3 3a4.5 4.5 0 0 0 6.4 6.4l1-1" />
                        </svg>
                        Partager l'album
                    </button>
                    @php
                        $albumShareLink =
                            ($albumShareLink['albumId'] ?? null) === $selectedAlbum->id ? $albumShareLink : null;
                    @endphp
                    <x-modal name="share-album" max-width="md" focusable>
                        <div class="p-6">
                            <h2 class="text-xl font-bold text-gray-900">Partager l'album</h2>
                            <p class="mt-2 leading-relaxed text-gray-700">
                                Le lien montre les photos de « {{ $selectedAlbum->title }} » à une personne sans compte,
                                sans le reste de la galerie. Toute personne qui l'a pourra les voir et les télécharger
                                pendant {{ App\Models\Share::DEFAULT_EXPIRY_DAYS }} jours.
                            </p>
                            <button type="button" wire:click="shareAlbum" wire:loading.attr="disabled"
                                wire:target="shareAlbum"
                                class="{{ $albumShareLink ? $secondaryPill : $primaryPill }} mt-4 disabled:cursor-wait">
                                {{ $albumShareLink ? 'Créer un autre lien' : 'Créer un lien de partage' }}
                            </button>
                            @if ($albumShareLink)
                                <x-share-link :url="$albumShareLink['url']" class="mt-5"
                                    wire:key="album-share-{{ $albumShareLink['url'] }}">
                                    <p class="mt-2 text-gray-700">Valable jusqu'au {{ $albumShareLink['expiresAt'] }}.</p>
                                </x-share-link>
                            @endif
                            <div class="mt-6 flex justify-end">
                                <button type="button" x-on:click="$dispatch('close')"
                                    class="{{ $secondaryPill }}">Fermer</button>
                            </div>
                        </div>
                    </x-modal>
                @endcan
                @can('update', $selectedAlbum)
                    <a href="{{ route('albums.edit', $selectedAlbum) }}" class="{{ $secondaryPill }}">Modifier
                        l'album</a>
                @endcan
                @can('delete', $selectedAlbum)
                    <button type="button" x-on:click="$dispatch('open-modal', 'confirm-album-deletion')"
                        class="{{ $pill }} bg-white px-5 text-red-700 ring-1 ring-inset ring-gray-300 hover:bg-red-50">
                        Supprimer l'album
                    </button>
                    <x-modal name="confirm-album-deletion" max-width="md" focusable>
                        <form method="post" action="{{ route('albums.destroy', $selectedAlbum) }}" class="p-6">
                            @csrf
                            @method('delete')
                            <h2 class="text-lg font-bold text-gray-900">Supprimer l'album « {{ $selectedAlbum->title }} »
                                ?</h2>
                            <p class="mt-2 text-gray-700">Ses photos seront supprimées avec lui.</p>
                            <div class="mt-6 flex flex-wrap justify-end gap-3">
                                <button type="button" x-on:click="$dispatch('close')"
                                    class="{{ $secondaryPill }}">Annuler</button>
                                <button type="submit"
                                    class="{{ $pill }} bg-red-700 px-5 text-white hover:bg-red-800">Supprimer
                                    l'album</button>
                            </div>
                        </form>
                    </x-modal>
                @endcan
            </div>
        </section>
    @endif

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
        Chaque page chargée s'ajoute à la fin de l'îlot, donc de la grille.
    --}}
    <div class="@container mx-4 mt-6 sm:mx-6 lg:mx-8">
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
</div>
