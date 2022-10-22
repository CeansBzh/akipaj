<?php

namespace App\Http\Livewire;

use App\Models\Comment;
use Livewire\Component;

class Comments extends Component
{

    /**
     * The comments list.
     */
    public $comments;

    /**
     * The commentable model.
     */
    public $commentable;

    /**
     * The content of the new comment.
     */
    public $content;
    public $newContent;

    public $commentIdToDestroy;
    public $commentIdToUpdate;

    protected $listeners = ['commentsNeedUpdate' => 'updateComments'];

    protected $rules = [
        'content' => 'required_without:newContent|nullable|string',
        'newContent' => 'required_without:content|nullable|string'
    ];

    public function updateComments($commentableId, $commentableType)
    {
        $this->commentable = $commentableType::find($commentableId);
        $this->comments = $this->commentable->comments;
    }

    public function store()
    {
        $this->validate();

        $this->commentable->comments()->create([
            'content' => $this->content,
            'user_id' => auth()->id(),
        ]);

        $this->content = '';

        $this->updateComments($this->commentable->id, get_class($this->commentable));
    }

    public function setCommentToUpdate($id)
    {
        if ($id != null && $id != $this->commentIdToUpdate) {
            $comment = Comment::findOrFail($id);
            $this->commentIdToUpdate = $comment->id;
            $this->newContent = $comment->content;
        } else {
            $this->commentIdToUpdate = null;
            $this->newContent = '';
        }
    }

    public function update(Comment $comment)
    {
        $this->validate();

        $comment->content = $this->newContent;
        $comment->save();

        $this->commentIdToUpdate = null;
        $this->newContent = '';

        $this->updateComments($this->commentable->id, get_class($this->commentable));
    }

    public function setCommentToDestroy($id)
    {
        $this->commentIdToDestroy = $this->commentIdToDestroy === $id ? null : $id;
    }

    public function destroy(Comment $comment)
    {
        $comment->delete();
        $this->updateComments($this->commentable->id, get_class($this->commentable));
    }
}
