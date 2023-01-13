<?php

namespace App\Http\Livewire;

use App\Models\Trip;
use App\Models\Album;
use App\Models\Event;
use App\Models\Photo;
use App\Models\Article;
use Livewire\Component;

class SearchInput extends Component
{
    public $searchTerm = '';
    public $icons = [
        'Sorties' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>',
        'Photos' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>',
        'Albums' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>',
        'Articles' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>',
        'Événements' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>'
    ];
    // Data found from the search
    public $searchResults = [];

    public function updatedSearchTerm()
    {
        $this->searchResults = [];
        $trimmed = trim($this->searchTerm);
        if (strlen($trimmed) < 3) {
            return;
        }

        $trips = Trip::whereLike(['title', 'description'], $trimmed)->get();
        // If trips are found, add them to the search results
        if ($trips->isNotEmpty()) {
            $this->searchResults['Sorties'] = $trips->map(function ($trip) {
                return [
                    'title' => $trip->title,
                    'url' => route('trips.show', $trip),
                ];
            })->toArray();
        }

        $albums = Album::whereLike(['title', 'description'], $trimmed)->get();
        // If albums are found, add them to the search results
        if ($albums->isNotEmpty()) {
            $this->searchResults['Albums'] = $albums->map(function ($album) {
                return [
                    'title' => $album->title,
                    'url' => route('albums.show', $album),
                ];
            })->toArray();
        }

        $photos = Photo::whereLike(['title', 'legend'], $trimmed)->get();
        // If photos are found, add them to the search results
        if ($photos->isNotEmpty()) {
            $this->searchResults['Photos'] = $photos->map(function ($photo) {
                return [
                    'title' => $photo->title,
                    'url' => route('photos.show', $photo),
                ];
            })->toArray();
        }

        $articles = Article::whereLike(['title', 'summary'], $trimmed)->get();
        // If articles are found, add them to the search results
        if ($articles->isNotEmpty()) {
            $this->searchResults['Articles'] = $articles->map(function ($article) {
                return [
                    'title' => $article->title,
                    'url' => route('articles.show', $article),
                ];
            })->toArray();
        }

        $events = Event::whereLike(['name', 'description'], $trimmed)->get();
        // If events are found, add them to the search results
        if ($events->isNotEmpty()) {
            $this->searchResults['Événements'] = $events->map(function ($event) {
                return [
                    'title' => $event->name,
                    'url' => route('events.show', $event),
                ];
            })->toArray();
        }
    }

    public function render()
    {
        return view('livewire.search-input');
    }
}
