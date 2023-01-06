<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\Album;
use App\Enum\AlertLevelEnum;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
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
            'trip' => ['sometimes', 'exists:trips,id'],
        ]);

        // Calcul de la date de l'album : la date est calculée automatiquement à partir de la date de la première photo.
        // Si aucune photo n'est présente, la date est celle du jour, ou celle de la sortie si renseignée.
        $date = $request->trip ? Trip::find($request->trip)->start_date : now();
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
            'photos' => $album->photos()->select('id')->simplePaginate(50),
        ]);
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
