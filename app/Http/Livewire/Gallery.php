<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Album;
use App\Models\Photo;
use Livewire\Component;
use Illuminate\Pagination\Cursor;
use Illuminate\Database\Eloquent\Collection;

class Gallery extends Component
{
    // List of photo ids to display. If null display all photos.
    public $photoIds;
    public $photos = null;
    public $nextCursor = null;
    public $hasMorePages = null;
    // Values to filter with
    public $searchTerm;
    public $sortTerm;
    public $usersFilter;
    public $albumsFilter;
    // Available values to filter with
    public $users;
    public $albums;

    protected $listeners = ['resetFilters', 'deleteSelected'];

    public function mount()
    {
        $this->calculateFilterable();
        $this->loadPhotos();
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

    public function loadPhotos()
    {
        // If we already have all the pages, don't load anything
        if ($this->hasMorePages !== null && !$this->hasMorePages) {
            return;
        }
        // Get the sorting term
        $sortBy = $this->getSorting($this->sortTerm);
        // Get the photos. Apply the filters if they are present. Using the pagination cursor we are not loading previously loaded items.
        $photos = Photo::when($this->photoIds, function ($query) {
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
            ->orderBy($sortBy[0], $sortBy[1])
            ->cursorPaginate(30, ['*'], 'cursor', Cursor::fromEncoded($this->nextCursor));
        // Push the newly loaded photos to the existing collection
        if ($this->photos === null) {
            $this->photos = new Collection();
        }
        $this->photos->push(...$photos->items());
        // Set the next cursor if there are more pages to load
        $this->hasMorePages = $photos->hasMorePages();
        if ($this->hasMorePages) {
            $this->nextCursor = $photos->nextCursor()->encode();
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
        $this->loadPhotos();
    }

    public function updated($field)
    {
        if (in_array($field, ['searchTerm', 'sortTerm', 'usersFilter', 'albumsFilter'])) {
            $this->reset(['photos', 'nextCursor', 'hasMorePages']);
            $this->loadPhotos();
        }
    }

    public function render()
    {
        return view('livewire.gallery');
    }
}
