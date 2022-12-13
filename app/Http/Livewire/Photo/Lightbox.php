<?php

namespace App\Http\Livewire\Photo;

use App\Models\Photo;
use Livewire\Component;

class Lightbox extends Component
{
    public $photo;

    protected $listeners = [
        'openPhotoLightbox',
    ];

    public function mount()
    {
        $this->photo = Photo::with('user:id,name')->first();
    }

    public function openPhotoLightbox($photoId)
    {
        $this->photo = Photo::with('user:id,name')->findOrFail($photoId, ['id', 'user_id', 'title', 'path', 'legend']);
        $this->dispatchBrowserEvent('open-lightbox', 'photo-lightbox');
    }

    public function render()
    {
        return view('photo.livewire.lightbox');
    }
}
