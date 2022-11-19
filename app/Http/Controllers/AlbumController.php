<?php

namespace App\Http\Controllers;

use App\Models\Album;
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
            'albums' => Album::has('photos')->with('oldestPhoto:id,photos.album_id,path')->simplePaginate(5),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Album  $album
     * @return \Illuminate\Http\Response
     */
    public function show(Album $album)
    {
        // TODO Refaire une page propre pour l'album
        return view('photo.index', [
            'photos' => $album->photos()->select('id', 'title', 'path', 'legend')->simplePaginate(50),
        ]);
    }
}
