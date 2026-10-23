<?php

namespace App\Livewire;

use App\Models\Photo;
use Livewire\Attributes\On;
use Livewire\Component;

class Lightbox extends Component
{
    public $photo;

    public function mount()
    {
        $this->photo = Photo::with('user:id,name')->first();
    }

    #[On('openPhotoLightbox')]
    public function openPhotoLightbox($photoId)
    {
        $this->loadPhoto($photoId);
        $this->dispatch('open-lightbox', name: 'photo-lightbox');
    }

    #[On('loadPhoto')]
    public function loadPhoto($photoId)
    {
        $this->photo = Photo::with('user:id,name')->findOrFail($photoId, ['id', 'user_id', 'title', 'path', 'legend']);
    }

    public function render()
    {
        return view('livewire.lightbox');
    }
}
