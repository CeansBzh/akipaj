<?php

namespace App\Models;

use App\Casts\RichText;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'positions',
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
     * Get the boats for the trip.
     *
     * @return HasMany<Boat, $this>
     */
    public function boats(): HasMany
    {
        return $this->hasMany(Boat::class);
    }
}
