<?php

namespace App\Traits;

use App\Models\Comment;
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
}
