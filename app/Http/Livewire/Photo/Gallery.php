<?php

namespace App\Http\Livewire\Photo;

use App\Models\Photo;
use Livewire\Component;

class Gallery extends Component
{
    public $photoIds;
    public $searchTerm;
    public $sortTerm;
    public $usersFilter;
    public $albumsFilter;
    public $additionalLogic;
    public $paginate = 25;

    protected $listeners = ['resetFilters'];

    public function resetFilters()
    {
        $this->usersFilter = null;
        $this->albumsFilter = null;
    }

    public function getSorting($value)
    {
        $this->withCount = null;
        switch ($value) {
            case 'most_comments':
                $this->withCount = 'comments';
                return ['comments_count', 'desc'];
                break;
            case 'title_asc':
                return ['title', 'asc'];
                break;
            case 'title_desc':
                return ['title', 'desc'];
                break;
            case 'created_asc':
                return ['created_at', 'asc'];
                break;
            case 'created_desc':
            default:
                return ['created_at', 'desc'];
        }
    }

    public function render()
    {
        $sortBy = $this->getSorting($this->sortTerm);
        return view('photo.livewire.gallery', [
            'photos' => Photo::when($this->photoIds, function ($query) {
                    return $query->whereIn('id', $this->photoIds);
                })
                ->when($this->searchTerm, function ($query) {
                    return $query->whereLike(['title', 'legend'], $this->searchTerm);
                })
                ->when($this->searchTerm, function ($query) {
                    return $query->whereLike(['title', 'legend'], $this->searchTerm);
                })
                ->when($this->usersFilter, function ($query) {
                    return $query->whereIn('user_id', $this->usersFilter);
                })
                ->when($this->albumsFilter, function ($query) {
                    return $query->whereIn('album_id', $this->albumsFilter);
                })
                ->when($this->withCount, function ($query) {
                    return $query->withCount($this->withCount);
                })
                ->orderBy($sortBy[0], $sortBy[1])
                ->paginate($this->paginate)
        ]);
    }
}
