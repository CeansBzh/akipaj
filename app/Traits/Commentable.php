<?php

namespace App\Traits;

use App\Models\Comment;
use App\Models\ThreadSubscription;
use Dyrynda\Database\Support\CascadeSoftDeletes;

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
}
