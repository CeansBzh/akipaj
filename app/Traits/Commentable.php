<?php

namespace App\Traits;

use App\Models\Comment;
use App\Models\ThreadSubscription;
use App\Models\User;
use Dyrynda\Database\Support\CascadeSoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Commentable
{
    use CascadeSoftDeletes;

    /**
     * Initialize the commentable trait for a model: soft delete the comments along with the model.
     * Models using this trait must declare the $cascadeDeletes property (CascadeSoftDeletes).
     */
    public function initializeCommentable(): void
    {
        $this->cascadeDeletes = array_values(array_unique([...$this->cascadeDeletes, 'comments']));
    }

    /**
     * Get all of the model's comments.
     *
     * @return MorphMany<Comment, $this>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * Get all of the subscriptions for the commentable.
     *
     * @return MorphMany<ThreadSubscription, $this>
     */
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(ThreadSubscription::class, 'subscribeable');
    }

    /**
     * Get all of the users subscribed to the commentable.
     *
     * @return BelongsToMany<User, $this>
     */
    public function subscribers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, ThreadSubscription::class, 'subscribeable_id', 'user_id')->where(
            'subscribeable_type',
            static::class,
        );
    }
}
