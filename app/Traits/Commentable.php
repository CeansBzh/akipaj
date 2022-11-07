<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Comment;
use App\Models\ThreadSubscription;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Relations\Relation;

trait Commentable
{
    use CascadeSoftDeletes;

    /**
     * The relationships that should be deleted on soft delete.
     *
     * @var array<int, string>
     */
    protected $cascadeDeletes = ['comments'];

    /**
     * Get all of the model's comments.
     */
    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get all of the subscriptions for the commentable.
     */
    public function subscriptions()
    {
        return $this->morphMany(ThreadSubscription::class, 'subscribeable');
    }

    /**
     * Get all of the users subscribed to the commentable.
     */
    public function subscribers()
    {
        return $this->belongsToMany(User::class, ThreadSubscription::class, 'subscribeable_id', 'user_id')
            ->where('subscribeable_type', static::class);
    }
}
