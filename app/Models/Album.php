<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

class Album extends Model
{
    use HasFactory;

    /**
     * Plus grand côté de la vignette de la couverture, en pixels : assez pour les cartes de la page d'une sortie.
     */
    public const COVER_THUMB_SIZE = 600;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
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
     *
     * @return HasMany<Photo, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * Get the trips for the album.
     *
     * @return BelongsToMany<Trip, $this>
     */
    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class);
    }

    /**
     * Get the albums's oldest uploaded photo (not necessarily the oldest photo realtime-wise).
     *
     * @return HasOne<Photo, $this>
     */
    public function oldestPhoto(): HasOne
    {
        return $this->hasOne(Photo::class)->oldestOfMany();
    }

    /**
     * Liens de partage de l'album.
     *
     * @return MorphMany<Share, $this>
     */
    public function shares(): MorphMany
    {
        return $this->morphMany(Share::class, 'shareable');
    }

    /**
     * Image à montrer en couverture : la vignette de la couverture choisie, sinon cette couverture
     * (vignette pas encore créée par la commande photos:thumbnails), sinon la vignette de la première photo envoyée.
     */
    public function coverUrl(): ?string
    {
        return $this->cover_thumb_path ?? $this->imagePath ?? $this->oldestPhoto?->thumb_path;
    }

    /**
     * Recrée la vignette de la couverture à partir de l'image choisie, ou la supprime quand l'album n'a plus de couverture.
     * Ne fait que préparer l'album : il reste à l'enregistrer.
     */
    public function refreshCoverThumbnail(): void
    {
        if ($this->cover_thumb_path) {
            Storage::delete(self::storagePath($this->cover_thumb_path));
            $this->cover_thumb_path = null;
        }

        $source = $this->imagePath ? self::storagePath($this->imagePath) : null;

        if ($source === null || ! Storage::exists($source)) {
            return;
        }

        $image = @imagecreatefromstring(Storage::get($source));

        if ($image === false) {
            return;
        }

        // Le dossier fait partie du nom : une couverture d'album et une image de sortie peuvent porter le même nom
        $name = basename(dirname($source)).'-'.pathinfo($source, PATHINFO_FILENAME);
        $thumbPath = 'public/albums/thumbs/'.$name.'.'.Photo::thumbnailExtension();
        Storage::makeDirectory('public/albums/thumbs');
        Photo::writeThumbnail($image, Storage::path($thumbPath), self::COVER_THUMB_SIZE);
        $this->cover_thumb_path = Storage::url($thumbPath);
    }

    /**
     * Chemin, sur le disque par défaut, d'une image publique enregistrée par son adresse (« /storage/albums/1.jpg »).
     */
    protected static function storagePath(string $url): string
    {
        return 'public/'.ltrim(str((string) parse_url($url, PHP_URL_PATH))->after('/storage/'), '/');
    }
}
