<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'year',
        'builder',
        'renter',
        'navigation_area',
        'city',
        'crew',
    ];

    /**
     * Get the trip that owns the boat.
     */
    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
