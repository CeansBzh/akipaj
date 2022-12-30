<?php

namespace App\Models;

use App\Models\User;
use App\Models\Album;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'location',
        'start_date',
        'end_date',
        'imagePath',
        'positions',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    /**
     * Get the users for the trip.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the albums for the trip.
     */
    public function albums()
    {
        return $this->belongsToMany(Album::class);
    }

    /**
     * Get the boats for the trip.
     */
    public function boats()
    {
        return $this->hasMany(Boat::class);
    }
}
