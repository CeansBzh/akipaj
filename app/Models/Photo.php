<?php

namespace App\Models;

use App\Models\Album;
use App\Traits\Commentable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Photo extends Model
{
    use HasFactory, Commentable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'path',
        'legend',
        'taken_at',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
    ];

    /**
     * Get the album that owns the photo.
     */
    public function album()
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * Get the user that owns the photo.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
