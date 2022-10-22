<?php

namespace App\Http\Livewire;

use App\Models\Comment;
use Livewire\Component;

class Comments extends Component
{

    /**
     * The comments list.
     *
     * @var string
     */
    public $comments;

    /**
     * The commentable model.
     *
     * @var object
     */
    public $commentable;

    /**
     * The content of the new comment.
     *
     * @var string
     */
    public $content;

    public $commentIdToDestroy;

    protected $listeners = ['commentsNeedUpdate' => 'updateComments'];

    protected $rules = [
        'content' => 'required|string'
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

    // TODO Update et style champ d'envoi de commentaire

    public function setCommentToDestroy($id)
    {
        $this->commentIdToDestroy = $id;
    }

    public function destroy(Comment $comment)
    {
        // TODO Demander confirmation à l'utilisateur avant de supprimer
        $comment->delete();
        $this->updateComments($this->commentable->id, get_class($this->commentable));
    }
}
