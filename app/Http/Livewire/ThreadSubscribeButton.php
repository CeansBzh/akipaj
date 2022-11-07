<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ThreadSubscribeButton extends Component
{
    public $commentable;
    public $subscribeToThread = false;

    public function mount($commentable)
    {
        // Si l'utilisateur est abonné au commentable on coche la case
        $this->subscribeToThread = auth()->user()->isSubscribedTo($commentable);
    }

    public function updatedSubscribeToThread($value)
    {
        if ($value) {
            $this->commentable->subscriptions()->create([
                'user_id' => auth()->id(),
            ]);
        } else {
            $subscription = $this->commentable->subscriptions()->where('user_id', auth()->id())->first();
            if ($subscription != null) {
                $subscription->delete();
            }
        }
    }
}
