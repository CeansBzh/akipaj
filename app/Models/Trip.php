<?php

namespace App\Models;

use App\Casts\RichText;
use Carbon\CarbonInterface;
use GdImage;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class Trip extends Model
{
    use HasFactory;

    /**
     * Largeurs, en pixels, des copies réduites de l'image de couverture. Une image plus étroite s'arrête à sa
     * propre largeur : elle n'est jamais agrandie.
     */
    public const COVER_WIDTHS = [480, 960, 1600];

    protected $fillable = [
        'title',
        'description',
        'location',
        'start_date',
        'end_date',
        'uuid',
        'imagePath',
    ];

    protected $casts = [
        'description' => RichText::class,
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'image_sizes' => 'array',
    ];

    /**
     * Description sans sa mise en forme, pour les cartes de sortie et les rappels d'agenda.
     *
     * @return Attribute<string, never>
     */
    protected function descriptionText(): Attribute
    {
        return Attribute::get(fn (): string => RichText::toPlainText($this->description));
    }

    /**
     * Garde les sorties à venir ou en cours : une sortie le reste jusqu'à la fin de son dernier jour.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function upcoming(Builder $query): void
    {
        $query->whereDate('end_date', '>=', today());
    }

    /**
     * Garde les sorties terminées, celles dont le dernier jour est passé.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function finished(Builder $query): void
    {
        $query->whereDate('end_date', '<', today());
    }

    /**
     * Garde les sorties commencées, terminées ou en cours : celles que leurs matelots ont déjà faites.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function started(Builder $query): void
    {
        $query->whereDate('start_date', '<=', today());
    }

    /**
     * Garde les sorties qui n'ont pas encore commencé.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function notStarted(Builder $query): void
    {
        $query->whereDate('start_date', '>', today());
    }

    /**
     * Indique si la sortie a lieu aujourd'hui. Ses dates n'ont pas d'heure : elle est en cours de son premier à son dernier jour inclus.
     */
    public function isOngoing(): bool
    {
        return today()->between($this->start_date->copy()->startOfDay(), $this->end_date->copy()->startOfDay());
    }

    /**
     * Indique si la sortie est terminée, c'est-à-dire si son dernier jour est passé.
     */
    public function isFinished(): bool
    {
        return $this->end_date->copy()->startOfDay()->lt(today());
    }

    /**
     * Adresses des copies réduites de l'image de couverture, rangées par largeur en pixels, de la plus étroite
     * à la plus large. Vide pour une sortie sans image, ou tant que la commande photos:thumbnails n'a pas créé
     * les copies d'une image envoyée avant elles.
     *
     * @return array<int, string>
     */
    public function coverSizes(): array
    {
        return array_map(fn (string $path): string => Storage::url($path), $this->image_sizes ?? []);
    }

    /**
     * Adresse de la plus petite copie de l'image de couverture au moins aussi large que $width (la plus large sinon),
     * ou de l'image d'origine si elle n'a pas encore de copies. Null pour une sortie sans image.
     */
    public function coverUrl(int $width): ?string
    {
        if ($this->imagePath === null) {
            return null;
        }

        $coverSizes = $this->coverSizes();

        return Arr::first($coverSizes, fn (string $url, int $sizeWidth): bool => $sizeWidth >= $width)
            ?? Arr::last($coverSizes)
            ?? $this->imagePath;
    }

    /**
     * Recrée les copies réduites de l'image de couverture, ou les supprime seulement quand la sortie n'a plus d'image.
     * Ne fait que préparer la sortie : il reste à l'enregistrer.
     */
    public function refreshCoverSizes(): void
    {
        $this->deleteCoverSizes();

        $source = $this->imagePath ? 'public/trips/'.basename($this->imagePath) : null;

        if ($source === null || ! Storage::exists($source)) {
            return;
        }

        $image = @imagecreatefromstring(Storage::get($source));

        if ($image === false) {
            return;
        }

        $image = self::orientAsShot($image, Storage::path($source));
        $name = pathinfo($source, PATHINFO_FILENAME);
        $extension = Photo::thumbnailExtension();
        $coverSizes = [];
        Storage::makeDirectory('public/trips/sizes');

        foreach (self::COVER_WIDTHS as $width) {
            $width = min($width, imagesx($image));
            $path = "public/trips/sizes/{$name}-{$width}.{$extension}";
            Photo::writeImage(imagescale($image, $width), Storage::path($path));
            $coverSizes[$width] = $path;

            if ($width === imagesx($image)) {
                break;
            }
        }

        $this->image_sizes = $coverSizes;
    }

    /**
     * Supprime les copies réduites de l'image de couverture. Ne fait que préparer la sortie : il reste à l'enregistrer.
     */
    public function deleteCoverSizes(): void
    {
        Storage::delete(array_values($this->image_sizes ?? []));
        $this->image_sizes = null;
    }

    /**
     * Redresse une photo prise appareil tourné. Le JPEG garde les pixels dans le sens du capteur et indique dans
     * ses données EXIF comment le navigateur doit les tourner : une copie qui perdrait cette indication serait de travers.
     */
    protected static function orientAsShot(GdImage $image, string $path): GdImage
    {
        $exif = function_exists('exif_read_data') ? @exif_read_data($path) : false;

        return match (is_array($exif) ? ($exif['Orientation'] ?? 1) : 1) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };
    }

    /**
     * Get the users for the trip.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the albums for the trip.
     *
     * @return BelongsToMany<Album, $this>
     */
    public function albums(): BelongsToMany
    {
        return $this->belongsToMany(Album::class);
    }

    /**
     * Donne les points du parcours, dans l'ordre.
     *
     * @return HasMany<CoursePoint, $this>
     */
    public function coursePoints(): HasMany
    {
        return $this->hasMany(CoursePoint::class)->orderBy('position');
    }

    /**
     * Découpe le parcours en journées, dans l'ordre. Une journée part du dernier point de la précédente
     * puis passe par ses propres points ; sa distance va d'un point au suivant, à vol d'oiseau.
     *
     * @return Collection<int, array{day: int, date: CarbonInterface, points: Collection<int, CoursePoint>, distance: float}>
     */
    public function courseDays(): Collection
    {
        $courseDays = new Collection;
        $previousDayLastPoint = null;

        foreach ($this->coursePoints->groupBy('day')->sortKeys() as $day => $points) {
            $route = $points->toBase()->values();
            if ($previousDayLastPoint !== null) {
                $route->prepend($previousDayLastPoint);
            }
            $previousDayLastPoint = $points->last();

            $courseDays->push([
                'day' => (int) $day,
                'date' => $this->start_date->copy()->addDays((int) $day - 1),
                'points' => $route,
                'distance' => $route->sliding(2)->sum(
                    fn (Collection $leg): float => $leg->first()->distanceInNauticalMilesTo($leg->last())
                ),
            ]);
        }

        return $courseDays;
    }

    /**
     * Get the boats for the trip.
     *
     * @return HasMany<Boat, $this>
     */
    public function boats(): HasMany
    {
        return $this->hasMany(Boat::class);
    }
}
