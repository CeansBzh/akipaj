<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Album;
use App\Models\Photo;
use Livewire\Component;
use Livewire\WithPagination;

class Gallery extends Component
{
    use WithPagination;

    // List of photo ids to display. If null display all photos.
    public $photoIds;
    public $paginate = 100;
    // Values to filter with
    public $searchTerm;
    public $sortTerm;
    public $usersFilter;
    public $albumsFilter;
    // Available values to filter with
    public $users;
    public $albums;
    // With count term to add to the query
    public $withCount = null;

    protected $listeners = ['resetFilters', 'deleteSelected'];

    public function mount()
    {
        $this->calculateFilterable();
    }

    public function calculateFilterable()
    {
        if ($this->photoIds) {
            // Récupération des utilisateurs ayant des photos dans la liste
            $usersIds = Photo::select('user_id')->whereIn('id', $this->photoIds)->distinct()->get();
            $this->users = User::whereIn('id', $usersIds)->select(['id', 'name'])->get();
            // Récupération des albums ayant des photos dans la liste
            $albumsIds = Photo::select('album_id')->whereIn('id', $this->photoIds)->distinct()->get();
            $this->albums = Album::whereIn('id', $albumsIds)
                ->has('photos')
                ->get()
                ->groupBy([
                    function ($val) {
                        return $val->date->format('Y');
                    },
                ])->toBase();
        } else {
            // Récupération de tous les utilisateurs ayant des photos
            $this->users = User::has('photos')->select(['id', 'name'])->get();
            // Récupération de tous les albums ayant des photos
            $this->albums = Album::has('photos')
                ->select(['id', 'title', 'date'])
                ->get()
                ->groupBy([
                    function ($val) {
                        return $val->date->format('Y');
                    }
                ])->toBase();
        }
    }

    public function resetFilters()
    {
        $this->usersFilter = null;
        $this->albumsFilter = null;
    }

    public function getSorting($value)
    {
        switch ($value) {
            case 'most_comments':
                $this->withCount = 'comments';
                return ['comments_count', 'desc'];
                break;
            case 'taken_asc':
                return ['taken_at', 'asc'];
                break;
            case 'taken_desc':
            default:
                return ['taken_at', 'desc'];
        }
    }

    public function deleteSelected($selectedIds)
    {
        $photos = Photo::whereIn('id', $selectedIds)->get();
        foreach ($photos as $photo) {
            if (auth()->user()->can('delete', $photo)) {
                $photo->delete();
            }
        }
        $this->calculateFilterable();
        $this->resetPage();
    }

    public function updated($field)
    {
        if (in_array($field, ['searchTerm', 'sortTerm', 'usersFilter', 'albumsFilter', 'paginate'])) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $sortBy = $this->getSorting($this->sortTerm);
        return view('livewire.gallery', [
            'photos' => Photo::when($this->photoIds, function ($query) {
                return $query->whereIn('id', $this->photoIds);
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
