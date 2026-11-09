@use('App\Enums\GalleryGroupingEnum')
@use('App\Enums\GallerySortEnum')
@use('App\Models\Album')

@php
    $selectedAlbum = $this->selectedAlbum;
    $activeFilterCount = count($usersFilter) + count($albumsFilter);
    $pill = 'inline-flex min-h-11 items-center gap-2 rounded-full py-2 font-semibold transition-colors focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700';
    $primaryColors = 'bg-primary-700 text-white hover:bg-primary-800';
    $primaryPill = $pill.' px-5 '.$primaryColors;
    $secondaryPill = $pill.' px-5 bg-white text-primary-800 ring-1 ring-gray-300 ring-inset hover:bg-primary-50';
@endphp

{{--
    Galerie : les albums en bandeau, les filtres, puis toutes les photos en mosaïque sur toute la largeur.
    La visionneuse (resources/js/gallery.js) s'ouvre sur la vignette touchée et parcourt les photos chargées.
--}}
<div class="pb-16" x-data="{
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

    <header class="flex flex-wrap items-center justify-between gap-x-3 gap-y-3 px-4 pt-6 sm:px-6 sm:pt-8 lg:px-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 sm:text-4xl">Photos</h1>
            <p class="mt-1 text-gray-700">
                {{ number_format($photoCount, 0, ',', "\u{202F}") }} {{ $photoCount > 1 ? 'photos' : 'photo' }}
            </p>
        </div>
        @if ($selectedAlbum)
            <a href="{{ route('photos.create', $selectedAlbum) }}" class="{{ $pill }} {{ $primaryColors }} px-4 sm:px-5">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                Ajouter mes photos
            </a>
        @else
            <button type="button" x-on:click="$dispatch('open-modal', 'choose-upload-album')" class="{{ $pill }} {{ $primaryColors }} px-4 sm:px-5">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                    <path d="M12 5v14M5 12h14" />
                </svg>
                Ajouter mes photos
            </button>
        @endif
    </header>

    {{-- Bandeau des albums, du plus récent au plus ancien : un album touché filtre la galerie --}}
    @if ($this->albums->isNotEmpty())
        <section aria-labelledby="gallery-albums-title" class="mt-6 sm:mt-8" x-data="{
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
        }" x-init="update()">
            <div class="flex items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                <h2 id="gallery-albums-title" class="text-xl font-bold text-gray-900">Albums</h2>
                <div class="hidden gap-2 sm:flex">
                    <button type="button" x-on:click="scroll(-1)" x-bind:disabled="atStart" aria-label="Albums précédents"
                        title="Albums précédents"
                        class="inline-flex size-11 items-center justify-center rounded-full text-gray-900 ring-1 ring-gray-300 ring-inset transition-colors hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700 disabled:opacity-40 disabled:hover:bg-transparent">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="size-5">
                            <path d="m15 6-6 6 6 6" />
                        </svg>
                    </button>
                    <button type="button" x-on:click="scroll(1)" x-bind:disabled="atEnd" aria-label="Albums suivants"
                        title="Albums suivants"
                        class="inline-flex size-11 items-center justify-center rounded-full text-gray-900 ring-1 ring-gray-300 ring-inset transition-colors hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700 disabled:opacity-40 disabled:hover:bg-transparent">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="size-5">
                            <path d="m9 6 6 6-6 6" />
                        </svg>
                    </button>
                </div>
            </div>

            <ul x-ref="strip" x-on:scroll.debounce.50ms="update()" x-on:resize.window.debounce.100ms="update()"
                class="mt-3 flex snap-x snap-mandatory scroll-px-4 gap-3 overflow-x-auto overscroll-x-contain px-4 pt-1 pb-3 sm:mt-4 sm:scroll-px-6 sm:gap-4 sm:px-6 lg:scroll-px-8 lg:px-8">
                @foreach ($this->albums as $album)
                    @php
                        $isSelected = count($albumsFilter) === 1 && (int) reset($albumsFilter) === $album->id;
                        $cover = $album->imagePath ?? $album->oldestPhoto?->thumb_path;
                    @endphp
                    <li class="w-32 shrink-0 snap-start sm:w-44" wire:key="strip-album-{{ $album->id }}">
                        <button type="button" wire:click="selectAlbum({{ $album->id }})"
                            aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                            class="group flex w-full flex-col text-left focus-visible:outline-none">
                            <span @class([
                                'relative block aspect-square overflow-hidden rounded-2xl bg-gray-200 ring-offset-2 group-focus-visible:ring-4 group-focus-visible:ring-primary-700',
                                'ring-4 ring-primary-700' => $isSelected,
                            ])>
                                @if ($cover)
                                    <img src="{{ $cover }}" alt="" width="176" height="176" loading="lazy"
                                        decoding="async"
                                        class="size-full object-cover motion-safe:transition-transform motion-safe:duration-300 motion-safe:group-hover:scale-105">
                                @endif
                                @if ($isSelected)
                                    <span
                                        class="absolute top-2 right-2 inline-flex size-8 items-center justify-center rounded-full bg-primary-700 text-white shadow-md">
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="size-5">
                                            <path d="m5 12.5 4.5 4.5L19 7.5" />
                                        </svg>
                                    </span>
                                @endif
                            </span>
                            <span class="mt-2 line-clamp-2 leading-snug font-bold break-words text-gray-900">{{ $album->title }}</span>
                            <span class="mt-0.5 text-sm text-gray-700">
                                {{ ucfirst($album->date->isoFormat('MMMM YYYY')) }} ·
                                {{ $album->photos_count }} {{ $album->photos_count > 1 ? 'photos' : 'photo' }}
                            </span>
                        </button>
                    </li>
                @endforeach
                @can('create', Album::class)
                    <li class="w-32 shrink-0 snap-start sm:w-44">
                        <a href="{{ route('albums.create') }}"
                            class="flex aspect-square w-full flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-gray-300 p-4 text-center font-semibold text-primary-800 transition-colors hover:border-primary-700 hover:bg-primary-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                            <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"
                                stroke-linecap="round" stroke-linejoin="round" class="size-8">
                                <path d="M12 5v14M5 12h14" />
                            </svg>
                            Créer un album
                        </a>
                    </li>
                @endcan
            </ul>
        </section>
    @endif

    {{-- Regroupement, ordre et filtres --}}
    <div class="mt-4 flex flex-wrap items-center gap-3 px-4 sm:mt-6 sm:px-6 lg:px-8">
        <fieldset>
            <legend class="sr-only">Regrouper les photos</legend>
            <div class="inline-flex rounded-full bg-gray-100 p-1">
                @foreach (GalleryGroupingEnum::cases() as $groupingOption)
                    <label class="relative">
                        <input type="radio" wire:model.live="grouping" value="{{ $groupingOption->value }}"
                            class="peer sr-only">
                        <span
                            class="inline-flex min-h-11 cursor-pointer items-center rounded-full px-4 font-semibold text-gray-700 transition-colors peer-checked:bg-white peer-checked:text-gray-900 peer-checked:shadow-sm peer-focus-visible:outline-2 peer-focus-visible:outline-offset-1 peer-focus-visible:outline-primary-700 hover:text-gray-900">
                            {{ $groupingOption->label() }}
                        </span>
                    </label>
                @endforeach
            </div>
        </fieldset>

        <div class="flex flex-wrap items-center gap-x-2 gap-y-3 sm:gap-x-3">
            <label class="flex items-center gap-2">
                <span class="font-semibold text-gray-900">Trier</span>
                <select wire:model.live="sort"
                    class="min-h-11 rounded-full border-0 bg-white py-2 pr-9 pl-4 text-gray-900 ring-1 ring-gray-300 ring-inset focus:ring-2 focus:ring-primary-700">
                    @foreach (GallerySortEnum::cases() as $sortOption)
                        <option value="{{ $sortOption->value }}">{{ $sortOption->label() }}</option>
                    @endforeach
                </select>
            </label>

            <button type="button" x-on:click="filtersOpen = ! filtersOpen" x-bind:aria-expanded="filtersOpen"
                aria-controls="gallery-filters" class="{{ $secondaryPill }}">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0">
                    <path d="M4 6h16M7 12h10M10 18h4" />
                </svg>
                Filtres
                @if ($activeFilterCount > 0)
                    <span class="rounded-full bg-primary-700 px-2 text-sm font-bold text-white">{{ $activeFilterCount }}</span>
                @endif
            </button>
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
                                class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-3xl bg-white py-1 pr-4 pl-1 text-gray-900 ring-1 ring-gray-300 ring-inset transition-colors peer-checked:bg-primary-700 peer-checked:text-white peer-checked:ring-primary-700 peer-checked:[&>svg]:block peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-primary-700 hover:bg-primary-50 peer-checked:hover:bg-primary-800">
                                <x-user-avatar :user="$uploader" class="size-9" />
                                {{ $uploader->name }}
                                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round" class="hidden size-5 shrink-0">
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
                    @foreach ($this->albums->groupBy(fn (Album $album) => $album->date->year) as $year => $albumsOfYear)
                        <p class="mt-3 text-sm font-bold text-gray-700 first:mt-0">{{ $year }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach ($albumsOfYear as $album)
                                <label class="relative" wire:key="filter-album-{{ $album->id }}">
                                    <input type="checkbox" wire:model.live="albumsFilter" value="{{ $album->id }}"
                                        class="peer sr-only">
                                    <span
                                        class="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-3xl bg-white px-4 py-2 text-gray-900 ring-1 ring-gray-300 ring-inset transition-colors peer-checked:bg-primary-700 peer-checked:text-white peer-checked:ring-primary-700 peer-checked:[&>svg]:block peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-primary-700 hover:bg-primary-50 peer-checked:hover:bg-primary-800">
                                        {{ $album->title }}
                                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                                    stroke-linecap="round" stroke-linejoin="round" class="hidden size-5 shrink-0">
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
                    class="inline-flex min-h-11 items-center gap-2 rounded-full bg-primary-50 py-1 pr-3 pl-1 text-gray-900 ring-1 ring-primary-200 ring-inset transition-colors hover:bg-primary-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                    <x-user-avatar :user="$filteredUser" class="size-9" />
                    <span>Envoyées par <strong>{{ $filteredUser->name }}</strong></span>
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0 text-gray-700">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                    <span class="sr-only">(retirer ce filtre)</span>
                </button>
            @endforeach
            @foreach ($filteredAlbums as $filteredAlbum)
                <button type="button" wire:click="removeAlbumFilter({{ $filteredAlbum->id }})"
                    wire:key="chip-album-{{ $filteredAlbum->id }}"
                    class="inline-flex min-h-11 items-center gap-2 rounded-full bg-primary-50 py-2 pr-3 pl-4 text-gray-900 ring-1 ring-primary-200 ring-inset transition-colors hover:bg-primary-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                    <span>Album <strong>{{ $filteredAlbum->title }}</strong></span>
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round" class="size-5 shrink-0 text-gray-700">
                        <path d="M6 6l12 12M18 6 6 18" />
                    </svg>
                    <span class="sr-only">(retirer ce filtre)</span>
                </button>
            @endforeach
            <button type="button" wire:click="resetFilters"
                class="inline-flex min-h-11 items-center rounded-full px-3 font-semibold text-primary-800 underline underline-offset-4 hover:text-primary-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-700">
                Voir toutes les photos
            </button>
        </div>
    @endif

    {{-- L'album choisi seul est présenté au-dessus de ses photos : il remplace l'ancienne page de l'album --}}
    @if ($selectedAlbum)
        <section aria-labelledby="gallery-album-title"
            class="mx-4 mt-6 rounded-3xl bg-primary-50 p-5 sm:mx-6 sm:p-8 lg:mx-8">
            <h2 id="gallery-album-title" class="text-2xl font-bold break-words text-gray-900">{{ $selectedAlbum->title }}</h2>
            <p class="mt-1 text-gray-700">
                {{ ucfirst($selectedAlbum->date->isoFormat('MMMM YYYY')) }} ·
                {{ $selectedAlbum->photos_count }} {{ $selectedAlbum->photos_count > 1 ? 'photos' : 'photo' }}
            </p>
            @if ($selectedAlbum->description)
                <p class="mt-4 max-w-prose leading-relaxed break-words text-gray-800">{{ $selectedAlbum->description }}</p>
            @endif
            @if ($selectedAlbum->trips->isNotEmpty())
                <p class="mt-4 text-gray-800">
                    {{ $selectedAlbum->trips->count() > 1 ? 'Sorties' : 'Sortie' }} :
                    @foreach ($selectedAlbum->trips as $trip)
                        <a href="{{ route('trips.show', $trip) }}"
                            class="font-semibold text-primary-800 underline underline-offset-4 hover:text-primary-900">{{ $trip->title }}</a>{{ $loop->last ? '' : ', ' }}
                    @endforeach
                </p>
            @endif
            <div class="mt-5 flex flex-wrap gap-3">
                @can('update', $selectedAlbum)
                    <a href="{{ route('albums.edit', $selectedAlbum) }}" class="{{ $secondaryPill }}">Modifier l'album</a>
                @endcan
                @can('delete', $selectedAlbum)
                    <button type="button" x-on:click="$dispatch('open-modal', 'confirm-album-deletion')"
                        class="{{ $pill }} px-5 bg-white text-red-700 ring-1 ring-gray-300 ring-inset hover:bg-red-50">
                        Supprimer l'album
                    </button>
                    <x-modal name="confirm-album-deletion" max-width="md" focusable>
                        <form method="post" action="{{ route('albums.destroy', $selectedAlbum) }}" class="p-6">
                            @csrf
                            @method('delete')
                            <h2 class="text-lg font-bold text-gray-900">Supprimer l'album « {{ $selectedAlbum->title }} » ?</h2>
                            <p class="mt-2 text-gray-700">Ses photos seront supprimées avec lui.</p>
                            <div class="mt-6 flex flex-wrap justify-end gap-3">
                                <button type="button" x-on:click="$dispatch('close')" class="{{ $secondaryPill }}">Annuler</button>
                                <button type="submit" class="{{ $pill }} px-5 bg-red-700 text-white hover:bg-red-800">Supprimer l'album</button>
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
    <div class="mt-6 @container">
        <div class="photo-masonry" data-gallery-grid>
            @island(name: 'photos', always: true)
                @foreach ($this->photoGroups as $group)
                    @if ($group['title'])
                        <h2 wire:key="group-{{ $group['key'] }}"
                            class="col-span-full flex flex-wrap items-baseline gap-x-3 gap-y-1 px-4 pt-8 pb-3 first:pt-2 sm:px-6 lg:px-8">
                            <span class="text-xl font-bold break-words text-gray-900">{{ $group['title'] }}</span>
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

    <div x-ref="galleryEnd" x-show="$wire.loadedPages < $wire.lastPage" x-cloak x-intersect.margin.1200px="loadMore()"
        class="flex justify-center px-4 pt-10">
        <button type="button" x-on:click="loadMore()" x-bind:disabled="loading" class="{{ $secondaryPill }} disabled:cursor-wait">
            <span x-show="! loading">Afficher plus de photos</span>
            <span x-show="loading" x-cloak>Chargement des photos…</span>
        </button>
    </div>

    {{-- « Ajouter mes photos » sans album choisi : on choisit d'abord l'album, vides compris --}}
    @unless ($selectedAlbum)
        <x-modal name="choose-upload-album" max-width="md" focusable>
            <div class="p-6">
                <h2 class="text-xl font-bold text-gray-900">Dans quel album ?</h2>
                <p class="mt-2 text-gray-700">Choisissez l'album où ajouter vos photos.</p>
                <ul class="mt-4 max-h-[60dvh] divide-y divide-gray-200 overflow-y-auto">
                    @foreach ($this->uploadAlbums as $album)
                        <li wire:key="upload-album-{{ $album->id }}">
                            <a href="{{ route('photos.create', $album) }}"
                                class="flex min-h-14 flex-col justify-center rounded-xl px-3 py-2 transition-colors hover:bg-primary-50 focus-visible:outline-2 focus-visible:outline-primary-700">
                                <span class="font-semibold break-words text-gray-900">{{ $album->title }}</span>
                                <span class="text-sm text-gray-700">{{ ucfirst($album->date->isoFormat('MMMM YYYY')) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6 flex flex-wrap justify-end gap-3">
                    <button type="button" x-on:click="$dispatch('close')" class="{{ $secondaryPill }}">Annuler</button>
                    @can('create', Album::class)
                        <a href="{{ route('albums.create') }}" class="{{ $primaryPill }}">Créer un album</a>
                    @endcan
                </div>
            </div>
        </x-modal>
    @endunless

    @include('livewire.partials.gallery-lightbox')
</div>
