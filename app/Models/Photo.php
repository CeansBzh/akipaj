<?php

namespace App\Models;

use App\Traits\Commentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Photo extends Model
{
    use Commentable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'path',
        'thumb_path',
        'legend',
        'taken_at',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
    ];

    protected $cascadeDeletes = [];

    /**
     * Get the album that owns the photo.
     *
     * @return BelongsTo<Album, $this>
     */
    public function album(): BelongsTo
    {
        return $this->belongsTo(Album::class);
    }

    /**
     * Get the user that owns the photo.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Poids du fichier de la photo, en octets, ou null s'il est introuvable.
     */
    public function fileSize(): ?int
    {
        $disk = Storage::disk('photos');
        $fileName = basename($this->path);

        return $disk->fileExists($fileName) ? $disk->size($fileName) : null;
    }
}
