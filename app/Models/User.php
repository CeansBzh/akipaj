<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserLevelEnum;
use App\Models\Trip;
use App\Models\Photo;
use App\Models\Payment;
use App\Traits\Commentable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, CascadeSoftDeletes, Prunable, Notifiable, Commentable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'level',
        'firstname',
        'lastname',
        'birthdate',
        'mobile_phone',
        'home_phone',
        'address',
        'postal_code',
        'city',
        'clothing_size',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birthdate' => 'datetime:Y-m-d',
        'email_verified_at' => 'datetime',
        'level' => UserLevelEnum::class,
    ];

    /**
     * The relationships that should be deleted on soft delete.
     *
     * @var array<int, string>
     */
    protected $cascadeDeletes = ['photos', 'comments'];

    /**
     * Get the photos for the user.
     *
     * @return HasMany<Photo, $this>
     */
    public function photos(): HasMany
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * Get the comments sent by the user.
     *
     * @return HasMany<Comment, $this>
     */
    public function commentsSent(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the payments for the user.
     *
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the trips for the user.
     *
     * @return BelongsToMany<Trip, $this>
     */
    public function trips(): BelongsToMany
    {
        return $this->belongsToMany(Trip::class);
    }

    /**
     * Get the thread subscriptions for the user.
     *
     * @return HasMany<ThreadSubscription, $this>
     */
    public function threadSubscriptions(): HasMany
    {
        return $this->hasMany(ThreadSubscription::class);
    }

    public static function findByUsername(string $username): self
    {
        return static::where('name', $username)->firstOrFail();
    }

    public function isGuest(): bool
    {
        return $this->level->value === UserLevelEnum::GUEST->value;
    }

    public function isMemberOrAbove(): bool
    {
        return $this->level->value >= UserLevelEnum::MEMBER->value;
    }

    public function isAdmin(): bool
    {
        return $this->level->value === UserLevelEnum::ADMINISTRATOR->value;
    }

    /**
     * Check if user is subscribed to the specified commentable.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $commentable  A model using the Commentable trait
     */
    public function isSubscribedTo(Model $commentable): bool
    {
        if (in_array(Commentable::class, class_uses_recursive($commentable::class))) {
            return $this->threadSubscriptions->contains(
                fn (ThreadSubscription $subscription) => $subscription->subscribeable_id == $commentable->getKey()
                    && $subscription->subscribeable_type === $commentable->getMorphClass(),
            );
        }
        return false;
    }

    /**
     * Get the users deleted for more than 30 days.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable()
    {
        return static::onlyTrashed()->where('deleted_at', '<=', now()->subMonth());
    }

    /**
     * Delete all data associated to the pruned user.
     *
     * @return void
     */
    protected function pruning()
    {
        // The photos were soft deleted along with the user (cascadeDeletes): include the trashed ones
        $this->photos()->withTrashed()->forceDelete();
        $this->commentsSent()->withTrashed()->forceDelete();
        $this->trips()->detach();
    }
}
