<?php

namespace App\Models;

use Database\Factories\CoursePointFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Point du parcours d'une sortie : un lieu nommé, à sa place dans le parcours et rattaché à un jour de la sortie (1 pour le premier).
 */
class CoursePoint extends Model
{
    /** @use HasFactory<CoursePointFactory> */
    use HasFactory;

    /**
     * Rayon moyen de la Terre, en milles nautiques.
     */
    private const EARTH_RADIUS_IN_NAUTICAL_MILES = 3440.065;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'position',
        'day',
        'name',
        'latitude',
        'longitude',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'day' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    /**
     * Donne la sortie dont ce point fait partie.
     *
     * @return BelongsTo<Trip, $this>
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Donne la distance jusqu'à un autre point, à vol d'oiseau, en milles nautiques (formule de haversine).
     */
    public function distanceInNauticalMilesTo(self $other): float
    {
        $latitudeDelta = deg2rad($other->latitude - $this->latitude);
        $longitudeDelta = deg2rad($other->longitude - $this->longitude);

        $haversine = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($this->latitude)) * cos(deg2rad($other->latitude)) * sin($longitudeDelta / 2) ** 2;

        return 2 * self::EARTH_RADIUS_IN_NAUTICAL_MILES * asin(min(1, sqrt($haversine)));
    }
}
