<?php

namespace App\Http\Livewire;

use App\Models\Photo;
use Livewire\Component;

class Lightbox extends Component
{
    public $photo;

    protected $listeners = ['openPhotoLightbox', 'loadPhoto'];

    public function mount()
    {
        $this->photo = Photo::with('user:id,name')->first();
    }

    public function openPhotoLightbox($photoId)
    {
        $this->loadPhoto($photoId);
        $this->dispatchBrowserEvent('open-lightbox', 'photo-lightbox');
    }

    public function loadPhoto($photoId)
    {
        $this->photo = Photo::with('user:id,name')->findOrFail($photoId, ['id', 'user_id', 'title', 'path', 'legend']);
    }

    public function render()
    {
        return view('livewire.lightbox');
    }
}
