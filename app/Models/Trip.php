<?php

namespace App\Models;

use App\Casts\RichText;
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
     * Description sans sa mise en forme, pour les cartes de sortie.
     *
     * @return Attribute<string, never>
     */
    protected function descriptionText(): Attribute
    {
        return Attribute::get(fn (): string => RichText::toPlainText($this->description));
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
