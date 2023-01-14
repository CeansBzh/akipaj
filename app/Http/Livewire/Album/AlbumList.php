<?php

namespace App\Http\Livewire\Album;

use App\Models\Album;
use Livewire\Component;
use Livewire\WithPagination;

class AlbumList extends Component
{
    use WithPagination;

    public $sortTerm = 'taken_desc';
    public $sortBy = ['date', 'desc'];

    public function updatedSortTerm()
    {
        switch ($this->sortTerm) {
            case 'date_asc':
                $this->sortBy = ['date', 'asc'];
                break;
            case 'date_desc':
            default:
                $this->sortBy = ['date', 'desc'];
        }
        $this->resetPage();
    }


    public function render()
    {
        return view('album.livewire.album-list', [
            'albums' => Album::has('photos')->with('oldestPhoto:id,photos.album_id,path')->orderBy($this->sortBy[0], $this->sortBy[1])->simplePaginate(12),
        ]);
    }
}
