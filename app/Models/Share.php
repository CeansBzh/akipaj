<?php

namespace App\Models;

use Database\Factories\ShareFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Lien de partage d'une photo ou d'un album : qui a le lien voit la photo, ou les photos de l'album, sans compte,
 * jusqu'à l'expiration du lien. Chaque partage crée un nouveau lien.
 *
 * @property int $id
 * @property string $token
 * @property string $shareable_type
 * @property int $shareable_id
 * @property ?int $user_id
 * @property int $expires_in_days
 * @property Carbon $expires_at
 * @property-read Photo|Album|null $shareable
 */
class Share extends Model
{
    /** @use HasFactory<ShareFactory> */
    use HasFactory, Prunable;

    /**
     * Durée de validité d'un lien, en jours.
     */
    public const DEFAULT_EXPIRY_DAYS = 7;

    protected $fillable = [
        'expires_in_days',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'expires_in_days' => 'integer',
        ];
    }

    /**
     * Crée un lien de partage de la photo ou de l'album, au nom du matelot qui partage.
     */
    public static function createFor(Photo|Album $shareable, User $user, int $expiresInDays = self::DEFAULT_EXPIRY_DAYS): self
    {
        $share = new self(['expires_in_days' => $expiresInDays]);
        $share->shareable()->associate($shareable);
        $share->user()->associate($user);
        $share->save();

        return $share;
    }

    protected static function booted(): void
    {
        static::creating(function (Share $share): void {
            $share->token ??= Str::random(40);
            $share->expires_at ??= now()->addDays($share->expires_in_days);
        });
    }

    /**
     * Les liens ne sont retrouvés que par leur jeton, jamais par leur numéro.
     */
    public function getRouteKeyName(): string
    {
        return 'token';
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function shareable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Matelot qui a créé le lien.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Vrai si le lien donne accès à cette photo : la photo partagée, ou une photo de l'album partagé.
     */
    public function grantsAccessTo(Photo $photo): bool
    {
        $shareable = $this->shareable;

        return match (true) {
            $shareable instanceof Photo => $shareable->is($photo),
            $shareable instanceof Album => $photo->album_id === $shareable->id,
            default => false,
        };
    }

    /**
     * Photos visibles par le lien, de la plus ancienne à la plus récente prise de vue.
     *
     * @return Builder<Photo>
     */
    public function photos(): Builder
    {
        $shareable = $this->shareable;

        return Photo::query()
            ->when(
                $shareable instanceof Album,
                fn (Builder $query) => $query->where('album_id', $shareable->id),
                fn (Builder $query) => $query->whereKey($shareable?->getKey()),
            )
            ->orderByRaw('taken_at is null')
            ->orderBy('taken_at')
            ->orderBy('id');
    }

    /**
     * Les liens expirés depuis un mois sont supprimés (commande model:prune, planifiée chaque jour).
     *
     * @return Builder
     */
    public function prunable(): Builder
    {
        return static::where('expires_at', '<=', now()->subMonth());
    }
}
