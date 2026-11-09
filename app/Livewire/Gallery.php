<?php

namespace App\Livewire;

use App\Enums\GalleryGroupingEnum;
use App\Enums\GallerySortEnum;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Galerie des photos du club : filtrable par matelot et par album, regroupée par jour, par album ou pas du tout.
 *
 * Les photos se chargent par pages de 100 dans l'îlot « photos ». Une page de plus s'ajoute à la suite des autres
 * sans les renvoyer ; un changement de filtre, de regroupement ou d'ordre repart de la première page.
 *
 * @property-read Collection<int, Album> $albums
 * @property-read Collection<int, Album> $uploadAlbums
 * @property-read Collection<int, User> $uploaders
 * @property-read ?Album $selectedAlbum
 * @property-read list<array{key: string, title: ?string, details: ?string, photos: list<Photo>}> $photoGroups
 */
class Gallery extends Component
{
    /**
     * Nombre de photos chargées à la fois.
     */
    public const PER_PAGE = 100;

    /**
     * Matelots dont on montre les photos, lus dans l'adresse (?auteurs[]=12) :
     * le lien « Voir ses photos » d'un profil ouvre ainsi la galerie déjà filtrée sur ce matelot.
     *
     * @var array<int, int|string>
     */
    #[Url(as: 'auteurs')]
    public array $usersFilter = [];

    /**
     * Albums dont on montre les photos (?albums[]=3).
     *
     * @var array<int, int|string>
     */
    #[Url(as: 'albums')]
    public array $albumsFilter = [];

    /**
     * Regroupement des photos, une valeur de GalleryGroupingEnum.
     */
    #[Url(as: 'vue', except: 'jour')]
    public string $grouping = 'jour';

    /**
     * Ordre des photos, une valeur de GallerySortEnum.
     */
    #[Url(as: 'tri', except: 'recentes')]
    public string $sort = 'recentes';

    /**
     * Nombre de pages de photos affichées.
     */
    #[Locked]
    public int $loadedPages = 1;

    /**
     * Dernière page pour les filtres choisis : la page sait ainsi s'il reste des photos à charger.
     */
    #[Locked]
    public int $lastPage = 1;

    /**
     * Vrai quand la requête ajoute une page à la suite des autres : l'îlot ne rend alors que cette page.
     */
    protected bool $isAppending = false;

    /**
     * Page ajoutée par loadMore(), ou null s'il n'y en avait plus.
     */
    protected ?int $appendedPage = null;

    public function updated(): void
    {
        // Seuls les filtres, le regroupement et l'ordre se modifient depuis la page
        $this->loadedPages = 1;
    }

    /**
     * Filtre la galerie sur un album du bandeau, ou retire ce filtre s'il était déjà le seul.
     */
    public function selectAlbum(int $albumId): void
    {
        $this->albumsFilter = $this->albumsFilter == [$albumId] ? [] : [$albumId];
        $this->loadedPages = 1;
    }

    public function removeUserFilter(int $userId): void
    {
        $this->usersFilter = array_values(array_filter($this->usersFilter, fn ($id) => (int) $id !== $userId));
        $this->loadedPages = 1;
    }

    public function removeAlbumFilter(int $albumId): void
    {
        $this->albumsFilter = array_values(array_filter($this->albumsFilter, fn ($id) => (int) $id !== $albumId));
        $this->loadedPages = 1;
    }

    #[On('resetFilters')]
    public function resetFilters(): void
    {
        $this->usersFilter = [];
        $this->albumsFilter = [];
        $this->loadedPages = 1;
    }

    /**
     * Charge la page de photos suivante, appelée avec le mode « append » de l'îlot « photos ».
     */
    public function loadMore(): void
    {
        $this->isAppending = true;

        if ($this->loadedPages < $this->lastPage) {
            $this->appendedPage = ++$this->loadedPages;
        }
    }

    /**
     * Supprime les photos choisies que le matelot a le droit de supprimer.
     * Gardée pour la sélection de plusieurs photos, qui reviendra dans la nouvelle galerie.
     *
     * @param  array<int, int|string>  $selectedIds
     */
    #[On('deleteSelected')]
    public function deleteSelected(array $selectedIds): void
    {
        foreach (Photo::whereIn('id', $selectedIds)->get() as $photo) {
            if (auth()->user()->can('delete', $photo)) {
                $photo->delete();
            }
        }

        $this->loadedPages = 1;
    }

    /**
     * Vrai quand l'îlot rend la galerie depuis la première page : ses premières vignettes se chargent sans attendre.
     */
    public function rendersFirstPage(): bool
    {
        return ! $this->isAppending;
    }

    public function groupingOption(): GalleryGroupingEnum
    {
        return GalleryGroupingEnum::tryFrom($this->grouping) ?? GalleryGroupingEnum::DAY;
    }

    public function sortOption(): GallerySortEnum
    {
        return GallerySortEnum::tryFrom($this->sort) ?? GallerySortEnum::NEWEST;
    }

    /**
     * Albums du bandeau, du plus récent au plus ancien : seulement ceux qui ont des photos.
     *
     * @return Collection<int, Album>
     */
    #[Computed]
    public function albums(): Collection
    {
        return Album::has('photos')
            ->withCount('photos')
            ->with('oldestPhoto')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Tous les albums, vides compris, proposés par « Ajouter mes photos » quand la galerie n'est pas filtrée sur un album.
     *
     * @return Collection<int, Album>
     */
    #[Computed]
    public function uploadAlbums(): Collection
    {
        return Album::orderByDesc('date')->orderByDesc('id')->get(['id', 'title', 'date']);
    }

    /**
     * Matelots qui ont envoyé des photos, proposés par le filtre « Envoyées par ».
     *
     * @return Collection<int, User>
     */
    #[Computed]
    public function uploaders(): Collection
    {
        return User::has('photos')->orderBy('name')->get();
    }

    /**
     * Album présenté au-dessus des photos quand la galerie est filtrée sur lui seul.
     */
    #[Computed]
    public function selectedAlbum(): ?Album
    {
        if (count($this->albumsFilter) !== 1) {
            return null;
        }

        return Album::with('trips')->withCount('photos')->find(reset($this->albumsFilter));
    }

    /**
     * Photos rendues par l'îlot, en groupes qui portent leur titre : un jour, un album, ou un seul groupe sans titre.
     * Un groupe commencé sur la page précédente continue sans répéter son titre.
     *
     * Propriété calculée, et non méthode publique : elle ne peut pas être appelée depuis le navigateur,
     * qui recevrait alors les photos et leurs matelots tels quels.
     *
     * @return list<array{key: string, title: ?string, details: ?string, photos: list<Photo>}>
     */
    #[Computed]
    public function photoGroups(): array
    {
        if ($this->isAppending && $this->appendedPage === null) {
            return [];
        }

        $firstPage = $this->appendedPage ?? 1;
        $offset = ($firstPage - 1) * self::PER_PAGE;
        $limit = ($this->loadedPages - $firstPage + 1) * self::PER_PAGE;

        // La photo d'avant la page dit si son premier groupe continue celui de la page précédente
        $photos = $this->orderedPhotos()
            ->with('user')
            ->when($this->groupingOption() === GalleryGroupingEnum::ALBUM, fn (Builder $query) => $query->with('album'))
            ->offset(max(0, $offset - 1))
            ->limit($offset > 0 ? $limit + 1 : $limit)
            ->get();
        $previousKey = $offset > 0 && $photos->isNotEmpty() ? $this->groupKey($photos->shift()) : null;

        $groupCounts = $this->groupCounts();
        $groups = [];

        foreach ($photos as $photo) {
            $key = $this->groupKey($photo);

            if ($groups === [] || $groups[array_key_last($groups)]['key'] !== $key) {
                $groups[] = [
                    'key' => $key,
                    'title' => $key === $previousKey ? null : $this->groupTitle($photo),
                    'details' => $key === $previousKey ? null : $this->groupDetails($photo, $groupCounts[$key] ?? 0),
                    'photos' => [],
                ];
            }

            $groups[array_key_last($groups)]['photos'][] = $photo;
        }

        return $groups;
    }

    /**
     * Photos qui répondent aux filtres choisis.
     *
     * @return Builder<Photo>
     */
    protected function filteredPhotos(): Builder
    {
        return Photo::query()
            ->when($this->usersFilter, fn (Builder $query) => $query->whereIn('photos.user_id', $this->usersFilter))
            ->when($this->albumsFilter, fn (Builder $query) => $query->whereIn('photos.album_id', $this->albumsFilter));
    }

    /**
     * Photos filtrées, dans l'ordre de la galerie. Celles sans date de prise de vue viennent toujours en dernier.
     *
     * @return Builder<Photo>
     */
    protected function orderedPhotos(): Builder
    {
        $direction = $this->sortOption()->direction();

        return $this->filteredPhotos()
            ->select('photos.*')
            ->when($this->groupingOption() === GalleryGroupingEnum::ALBUM, fn (Builder $query) => $query
                ->join('albums', 'albums.id', '=', 'photos.album_id')
                ->orderBy('albums.date', $direction)
                ->orderBy('albums.id', $direction))
            ->orderByRaw('photos.taken_at is null')
            ->orderBy('photos.taken_at', $direction)
            ->orderBy('photos.id', $direction);
    }

    protected function groupKey(Photo $photo): string
    {
        return match ($this->groupingOption()) {
            GalleryGroupingEnum::DAY => $photo->taken_at?->toDateString() ?? 'inconnue',
            GalleryGroupingEnum::ALBUM => 'album-'.$photo->album_id,
            GalleryGroupingEnum::NONE => 'toutes',
        };
    }

    protected function groupTitle(Photo $photo): ?string
    {
        return match ($this->groupingOption()) {
            GalleryGroupingEnum::DAY => $photo->taken_at ? ucfirst($photo->taken_at->isoFormat('dddd Do MMMM YYYY')) : 'Date inconnue',
            GalleryGroupingEnum::ALBUM => $photo->album->title,
            GalleryGroupingEnum::NONE => null,
        };
    }

    protected function groupDetails(Photo $photo, int $count): ?string
    {
        $photoCount = $count.' '.($count > 1 ? 'photos' : 'photo');

        return match ($this->groupingOption()) {
            GalleryGroupingEnum::DAY => $photoCount,
            GalleryGroupingEnum::ALBUM => ucfirst($photo->album->date->isoFormat('MMMM YYYY')).' · '.$photoCount,
            GalleryGroupingEnum::NONE => null,
        };
    }

    /**
     * Nombre de photos filtrées de chaque groupe, indexé par la clé du groupe.
     *
     * @return array<string, int>
     */
    protected function groupCounts(): array
    {
        return match ($this->groupingOption()) {
            GalleryGroupingEnum::DAY => $this->filteredPhotos()
                ->selectRaw("coalesce(date(photos.taken_at), 'inconnue') as day, count(*) as total")
                ->groupBy('day')
                ->pluck('total', 'day')
                ->map(fn ($total): int => (int) $total)
                ->all(),
            GalleryGroupingEnum::ALBUM => $this->filteredPhotos()
                ->selectRaw('photos.album_id, count(*) as total')
                ->groupBy('photos.album_id')
                ->pluck('total', 'photos.album_id')
                ->mapWithKeys(fn ($total, $albumId): array => ['album-'.$albumId => (int) $total])
                ->all(),
            GalleryGroupingEnum::NONE => [],
        };
    }

    public function render(): View
    {
        $photoCount = $this->filteredPhotos()->count();
        $this->lastPage = max(1, (int) ceil($photoCount / self::PER_PAGE));

        return view('livewire.gallery', [
            'photoCount' => $photoCount,
            'filteredUsers' => $this->uploaders->whereIn('id', $this->usersFilter)->values(),
            'filteredAlbums' => $this->albumsFilter
                ? Album::whereIn('id', $this->albumsFilter)->orderByDesc('date')->get(['id', 'title'])
                : collect(),
        ]);
    }
}
