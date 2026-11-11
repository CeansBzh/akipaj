<?php

namespace App\Models;

use App\Traits\Commentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
     * Liens de partage de la photo.
     *
     * @return MorphMany<Share, $this>
     */
    public function shares(): MorphMany
    {
        return $this->morphMany(Share::class, 'shareable');
    }

    /**
     * Chemin du fichier de la photo dans le disque « photos ».
     */
    public function fileName(): string
    {
        return basename($this->path);
    }

    /**
     * Chemin du fichier de la vignette dans le disque « photos ».
     */
    public function thumbFileName(): string
    {
        return 'thumbs/'.basename($this->thumb_path);
    }

    /**
     * Hauteur de la vignette dans la mosaïque (.photo-masonry de resources/css/app.css), en lignes de sa grille :
     * une colonne est large de 32 lignes.
     */
    public function masonryRows(): int
    {
        $thumbWidth = $this->thumb_width ?: 4;
        $thumbHeight = $this->thumb_height ?: 3;

        return (int) min(96, max(10, round((32 * $thumbHeight) / $thumbWidth)));
    }

    /**
     * Taille de la photo d'origine, dont la visionneuse a besoin pour la zoomer ; à défaut, celle de la vignette agrandie.
     *
     * @return array{int, int}
     */
    public function viewerSize(): array
    {
        return [
            $this->width ?: ($this->thumb_width ?: 4) * 4,
            $this->height ?: ($this->thumb_height ?: 3) * 4,
        ];
    }

    /**
     * Nom du fichier proposé au téléchargement, tiré du nom d'origine de la photo.
     */
    public function downloadName(): string
    {
        return (pathinfo($this->title, PATHINFO_FILENAME) ?: 'photo').'.jpg';
    }

    /**
     * Texte alternatif de la photo : sa légende, sinon son matelot et sa date de prise de vue.
     */
    public function altText(bool $withUploader = true): string
    {
        if ($this->legend) {
            return Str::limit($this->legend, 140);
        }

        $uploaderName = $withUploader ? $this->user?->name : null;

        return trim('Photo'.($uploaderName ? ' de '.$uploaderName : '').($this->taken_at ? ', '.$this->taken_at->isoFormat('D MMMM YYYY') : ''));
    }

    /**
     * Poids du fichier de la photo, en octets, ou null s'il est introuvable.
     */
    public function fileSize(): ?int
    {
        $disk = Storage::disk('photos');

        return $disk->fileExists($this->fileName()) ? $disk->size($this->fileName()) : null;
    }

    /**
     * Envoie un fichier du disque des photos, qui n'est pas public, ou une erreur 404 s'il est introuvable.
     *
     * @param  array<string, string>  $headers
     */
    public static function fileResponse(string $path, array $headers = []): StreamedResponse
    {
        $disk = Storage::disk('photos');

        abort_unless($disk->fileExists($path), 404);

        // Les noms des fichiers sont uniques : le navigateur peut les garder, mais pas les caches partagés
        return $disk->response($path, headers: [
            'Cache-Control' => 'private, max-age=31536000, immutable',
            ...$headers,
        ]);
    }
}
