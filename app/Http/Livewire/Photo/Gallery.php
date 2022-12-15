<?php

namespace App\Http\Livewire\Photo;

use App\Models\Album;
use App\Models\Photo;
use Livewire\Component;

class Gallery extends Component
{
    public $searchTermFilter;
    public $usersFilter;
    public $albumsFilter;
    public $paginate = 25;

    public function resetFilters() {
        $this->searchTermFilter = null;
        $this->usersFilter = null;
        $this->albumsFilter = null;
    }

    public function render()
    {
        return view('photo.livewire.gallery', [
            'photos' => Photo::whereLike(['title', 'legend'], $this->searchTermFilter ?? '')
                ->when($this->usersFilter, function ($query) {
                    return $query->whereIn('user_id', $this->usersFilter);
                })
                ->when($this->albumsFilter, function ($query) {
                    return $query->whereIn('album_id', $this->albumsFilter);
                })
                ->paginate($this->paginate),
            'albums' => Album::all()
                ->sortBy('date')
                ->groupBy([
                    function ($val) {
                        return $val->date->format('Y');
                    },
                ])
        ]);
    }
}
