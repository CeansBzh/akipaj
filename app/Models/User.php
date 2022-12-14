<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Role;
use App\Models\Trip;
use App\Models\Photo;
use App\Models\Payment;
use App\Traits\Commentable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, CascadeSoftDeletes, Prunable, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
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
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'birthdate' => 'datetime:Y-m-d',
        'email_verified_at' => 'datetime',
    ];

    /**
     * The relationships that should be deleted on soft delete.
     *
     * @var array<int, string>
     */
    protected $cascadeDeletes = ['photos', 'comments'];

    /**
     * Get the photos for the user.
     */
    public function photos()
    {
        return $this->hasMany(Photo::class);
    }

    /**
     * Get the comments for the user.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the payments for the user.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the roles for the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Get the trips for the user.
     */
    public function trips()
    {
        return $this->belongsToMany(Trip::class);
    }

    /**
     * Get the thread subscriptions for the user.
     */
    public function threadSubscriptions()
    {
        return $this->hasMany(ThreadSubscription::class);
    }

    /**
     * Attach a role to the user.
     *
     * @param  \App\Models\Role|string  $role
     */
    public function attachRole($role)
    {
        if (is_string($role)) {
            $role = Role::whereName($role)->firstOrFail();
        }

        $this->roles()->attach($role);
    }

    /**
     * Check if user has a role.
     *
     * @param  \App\Models\Role|string  $role
     */
    public function hasRole($role)
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        return !!$role->intersect($this->roles)->count();
    }

    /**
     * Check if user is subscribed to the specified commentable.
     *
     * @param  \App\Traits\Commentable  $commentable
     */
    public function isSubscribedTo($commentable)
    {
        if (in_array(Commentable::class, class_uses_recursive($commentable::class))) {
            return $this->threadSubscriptions->contains('subscribeable_id', $commentable->id);
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
        $this->photos()->forceDelete();
        $this->comments()->forceDelete();
        $this->roles()->detach();
    }
}
