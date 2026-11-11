<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Models\Photo;
use App\Models\Share;
use App\Notifications\CommentPosted;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Notification;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Panneaux « Détails », « Commentaires » et « Partager » de la visionneuse de la galerie, posés à sa droite.
 *
 * La visionneuse (resources/js/gallery.js) ouvre et ferme les panneaux, et demande la photo à montrer avec show().
 * Le composant rend les panneaux de la photo à la fois : passer de l'un à l'autre ne demande rien au serveur.
 *
 * @property-read ?Photo $photo
 * @property-read Collection<int, Comment> $comments
 */
class PhotoDrawer extends Component
{
    use AuthorizesRequests;

    /**
     * Photo montrée, choisie par show() et non par le navigateur.
     */
    #[Locked]
    public ?int $photoId = null;

    /**
     * Vrai pendant la modification de la légende.
     */
    public bool $isEditingLegend = false;

    public string $legend = '';

    /**
     * Commentaire en cours d'écriture.
     */
    public string $comment = '';

    /**
     * Commentaire dont la suppression attend une confirmation.
     */
    #[Locked]
    public ?int $commentIdToDelete = null;

    /**
     * Dernier lien de partage créé pour la photo montrée, et sa date d'expiration.
     *
     * @var array{url: string, expiresAt: string}|null
     */
    #[Locked]
    public ?array $shareLink = null;

    /**
     * Vrai si le matelot reçoit par e-mail les nouveaux commentaires de la photo.
     */
    public bool $isSubscribed = false;

    /**
     * Montre la photo de la visionneuse, en repartant d'un panneau sans modification ni commentaire en cours.
     */
    public function show(int $photoId): void
    {
        $photo = Photo::findOrFail($photoId);
        $this->authorize('view', $photo);

        $this->photoId = $photo->id;
        $this->reset('isEditingLegend', 'legend', 'comment', 'commentIdToDelete', 'shareLink');
        $this->resetValidation();
        $this->isSubscribed = auth()->user()->isSubscribedTo($photo);
        unset($this->photo, $this->comments);
    }

    /**
     * Photo montrée, avec son matelot et son album.
     */
    #[Computed]
    public function photo(): ?Photo
    {
        return $this->photoId ? Photo::with(['user', 'album'])->find($this->photoId) : null;
    }

    /**
     * Commentaires de la photo, du plus ancien au plus récent.
     *
     * @return Collection<int, Comment>
     */
    #[Computed]
    public function comments(): Collection
    {
        return $this->photo?->comments()->with('user')->oldest()->oldest('id')->get() ?? new Collection;
    }

    public function editLegend(): void
    {
        $this->authorize('update', $this->shownPhoto());

        $this->legend = $this->shownPhoto()->legend ?? '';
        $this->isEditingLegend = true;
    }

    public function cancelLegend(): void
    {
        $this->isEditingLegend = false;
        $this->resetValidation('legend');
        $this->focusLegendButton();
    }

    public function saveLegend(): void
    {
        $photo = $this->shownPhoto();
        $this->authorize('update', $photo);
        $this->validate(
            ['legend' => 'nullable|string|max:2048'],
            ['legend.max' => 'La légende ne peut pas dépasser 2 048 caractères.'],
        );

        $photo->update(['legend' => trim($this->legend) ?: null]);

        $this->isEditingLegend = false;
        unset($this->photo);
        $this->focusLegendButton();
    }

    public function postComment(): void
    {
        $photo = $this->shownPhoto();
        $this->authorize('create', Comment::class);
        $this->validate(
            ['comment' => 'required|string|max:2000'],
            [
                'comment.required' => 'Écrivez votre commentaire avant de le publier.',
                'comment.max' => 'Votre commentaire ne peut pas dépasser 2 000 caractères.',
            ],
        );

        $comment = $photo->comments()->create([
            'content' => trim($this->comment),
            'user_id' => auth()->id(),
        ]);
        $this->comment = '';

        // Les abonnés à la photo sont prévenus par e-mail, sauf l'auteur du commentaire
        Notification::send(
            $photo->subscribers->except([$comment->user_id]),
            new CommentPosted($photo->album ? 'une photo de l\'album « '.$photo->album->title.' »' : 'une photo', $comment),
        );

        $this->commentsChanged();
    }

    public function confirmCommentDeletion(int $commentId): void
    {
        $this->authorize('delete', $this->shownComment($commentId));

        $this->commentIdToDelete = $commentId;
    }

    public function cancelCommentDeletion(): void
    {
        $this->commentIdToDelete = null;
    }

    public function deleteComment(int $commentId): void
    {
        $comment = $this->shownComment($commentId);
        $this->authorize('delete', $comment);

        $comment->delete();

        $this->commentIdToDelete = null;
        $this->commentsChanged();
    }

    /**
     * Crée un nouveau lien qui montre la photo à qui n'a pas de compte, jusqu'à son expiration.
     */
    public function createShareLink(): void
    {
        $photo = $this->shownPhoto();
        $this->authorize('share', $photo);

        $share = Share::createFor($photo, auth()->user());

        $this->shareLink = [
            'url' => route('shares.show', $share),
            'expiresAt' => $share->expires_at->isoFormat('dddd D MMMM YYYY [à] H[h]mm'),
        ];
    }

    public function updatedIsSubscribed(bool $isSubscribed): void
    {
        $photo = $this->shownPhoto();
        $subscriptions = $photo->subscriptions()->where('user_id', auth()->id());

        if ($isSubscribed && ! $subscriptions->exists()) {
            $photo->subscriptions()->create(['user_id' => auth()->id()]);
        } elseif (! $isSubscribed) {
            $subscriptions->delete();
        }
    }

    /**
     * Le champ de la légende disparaît : le focus revient sur le bouton qui l'avait ouvert.
     */
    protected function focusLegendButton(): void
    {
        $this->js("document.getElementById('photo-drawer-legend-edit')?.focus()");
    }

    /**
     * Photo montrée, introuvable si aucune ne l'est ou si elle vient d'être supprimée.
     */
    protected function shownPhoto(): Photo
    {
        return $this->photo ?? abort(404);
    }

    /**
     * Commentaire de la photo montrée : un commentaire d'une autre photo est introuvable ici.
     */
    protected function shownComment(int $commentId): Comment
    {
        return $this->shownPhoto()->comments()->findOrFail($commentId);
    }

    /**
     * Recharge les commentaires, et donne leur nombre à la visionneuse pour le point de son bouton.
     */
    protected function commentsChanged(): void
    {
        unset($this->comments);

        $this->dispatch('photo-comments-count', photoId: $this->photoId, count: $this->comments->count());
    }

    public function render(): View
    {
        return view('livewire.photo-drawer');
    }
}
