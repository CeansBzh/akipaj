<?php

namespace App\Http\Livewire\Photo;

use App\Models\Photo;
use Livewire\Component;

class Lightbox extends Component
{
    public $photo = null;

    protected $listeners = ['openPhotoLightbox'];

    public function openPhotoLightbox(Photo $photo)
    {
        $this->photo = $photo;
        $this->emit('commentsNeedUpdate', $photo->id, $photo::class);
        $this->dispatchBrowserEvent('open-modal', 'photo-lightbox');
    }

    public function render()
    {
        return view('livewire.photo.lightbox');
    }
}
