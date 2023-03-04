<?php

namespace App\Traits;

use App\Models\User;
use App\Models\Comment;
use App\Models\ThreadSubscription;
use Dyrynda\Database\Support\CascadeSoftDeletes;

trait Commentable
{
    use CascadeSoftDeletes;

    /**
     * Initialize the commentable trait for a model. Add the cascadeDeletes property if it doesn't already exist.
     */
    public function initializeCommentable()
    {
        if (property_exists($this, 'cascadeDeletes')) {
            $this->cascadeDeletes = array_unique(array_merge($this->cascadeDeletes, ['comments']));
        } else {
            $this->cascadeDeletes = ['comments'];
        }
    }

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
        return $this->belongsToMany(User::class, ThreadSubscription::class, 'subscribeable_id', 'user_id')->where(
            'subscribeable_type',
            static::class,
        );
    }
}
