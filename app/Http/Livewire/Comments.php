<?php

namespace App\Http\Livewire;

use App\Models\Comment;
use App\Notifications\CommentPosted;
use Livewire\Component;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Comments extends Component
{
    use AuthorizesRequests;

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
        $this->authorize('create', Comment::class);
        $this->validate();

        // Enregistrement du commentaire dans la base de données et remise à zéro du texte dans l'input
        $comment = $this->commentable->comments()->create([
            'content' => $this->content,
            'user_id' => auth()->id(),
        ]);
        $this->content = '';

        // Envoi d'un mail à tous les abonnés au fil de discussion sauf à l'utilisateur qui poste le commentaire
        $subscribers = $this->commentable->subscribers->except($comment->user_id);
        Notification::send($subscribers, new CommentPosted($this->commentable->title, $comment));

        // Mise à jour des commentaires dans le composant pour afficher celui qui vient d'être enregistré
        $this->updateComments($this->commentable->id, get_class($this->commentable));
    }

    public function setCommentToUpdate($id)
    {
        if ($id != null && $id != $this->commentIdToUpdate) {
            $comment = Comment::findOrFail($id);
            $this->authorize('update', $comment);
            $this->commentIdToUpdate = $comment->id;
            $this->newContent = $comment->content;
        } else {
            $this->commentIdToUpdate = null;
            $this->newContent = '';
        }
    }

    public function update(Comment $comment)
    {
        $this->authorize('update', $comment);
        $this->validate();

        $comment->content = $this->newContent;
        $comment->save();

        $this->commentIdToUpdate = null;
        $this->newContent = '';

        $this->updateComments($this->commentable->id, get_class($this->commentable));
    }

    public function setCommentToDestroy($id)
    {
        if ($id != null && $id != $this->commentIdToDestroy) {
            $comment = Comment::findOrFail($id);
            $this->authorize('delete', $comment);
            $this->commentIdToDestroy = $comment->id;
        } else {
            $this->commentIdToDestroy = null;
        }
    }

    public function destroy(Comment $comment)
    {
        $this->authorize('delete', $comment);
        $comment->delete();
        $this->updateComments($this->commentable->id, get_class($this->commentable));
    }
}
