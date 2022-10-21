<?php

namespace App\Http\Livewire;

use App\Http\Livewire\Modal;
use App\Models\Photo;

class PhotoModal extends Modal
{

    public $photo;

    public function create(Photo $photo)
    {
        $this->photo = $photo;
        $this->show();
    }

    public function render()
    {
        return view('livewire.photo-modal');
    }
}
