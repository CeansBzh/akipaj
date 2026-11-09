<?php

namespace App\Models;

use App\Casts\RichText;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Trip extends Model
{
    use HasFactory;

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
