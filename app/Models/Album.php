<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Trip;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Album extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'date',
        'imagePath',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'datetime:Y-m-d',
    ];

    /**
     * Get the photos for the album.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * Get the trips for the album.
     */
    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class);
    }

    /**
     * Get the albums's oldest uploaded photo (not necessarily the oldest photo realtime-wise).
     */
    public function oldestPhoto(): HasOne
    {
        return $this->hasOne(Photo::class)->oldestOfMany();
    }
}
