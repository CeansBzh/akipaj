<?php

namespace App\Models;

use App\Models\Album;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Photo extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'album_id', // TODO retirer ça et faire une relation automatique
        'title',
        'path',
        'legend',
        'taken',
    ];

    /**
     * Get the album that owns the photo.
     */
    public function album()
    {
        return $this->belongsTo(Album::class);
    }
}
