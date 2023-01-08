<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Enum\AlertLevelEnum;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Album::class, 'album');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Gets all albums with for each the oldest photo they contain.
        // Gets only the id, album_id and path fields of each photo.
        return view('album.index', [
            'albums' => Album::with('oldestPhoto:id,photos.album_id,path')->simplePaginate(25),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('album.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:63', 'unique:albums'],
            'description' => ['required', 'string', 'max:255'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'trips' => ['nullable', 'array'],
            'trips.*' => ['nullable', 'integer', 'distinct', 'exists:trips,id'],
        ]);

        // Calcul de la date de l'album à partir du mois et de l'année.
        $date = date('Y-m-d', strtotime($request->year . '-' . $request->month . '-01'));
        // Création de l'album.
        $album = new Album();
        $album->title = $request->title;
        $album->description = $request->description;
        $album->date = $date;
        $album->save();
        // Association de la sortie avec l'album si elle est renseignée.
        if ($request->trip) {
            $album->trips()->attach($request->trip);
        }

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Album créé avec succès !');

        return redirect()->route('albums.show', $album);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function show(Album $album)
    {
        return view('album.show', [
            'album' => $album,
            'photoIds' => $album->photos()->select('id')->get()->pluck('id')->toArray(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function edit(Album $album)
    {
        return view('album.edit')->with('album', $album);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Album $album)
    {
        $request->validateWithBag('updateAlbum', [
            'title' => ['required', 'string', 'max:63', 'unique:albums,title,' . $album->id],
            'description' => ['required', 'string', 'max:255'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'year' => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'trips' => ['nullable', 'array'],
            'trips.*' => ['nullable', 'integer', 'distinct', 'exists:trips,id'],
        ]);

        // Calcul de la date de l'album à partir du mois et de l'année.
        $date = date('Y-m-d', strtotime($request->year . '-' . $request->month . '-01'));
        // Modification de l'album.
        $album->title = $request->title;
        $album->description = $request->description;
        $album->date = $date;
        $album->save();
        // Modification des sorties associées à l'album.
        $album->trips()->sync($request->trips);

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Album modifié avec succès.');

        return redirect()->route('albums.show', $album);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function destroy(Album $album)
    {
        $album->delete();

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Album supprimé avec succès.');

        return redirect()->route('albums.index');
    }
}
