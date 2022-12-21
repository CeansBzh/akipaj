<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use App\Enum\AlertLevelEnum;
use Illuminate\Http\Request;
use App\Http\Requests\Photo\StorePhotoRequest;

class PhotoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('photo.index', [
            'photos' => Photo::select('id', 'path', 'legend')->simplePaginate(50),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $albums = \App\Models\Album::all()
            ->sortBy('date')
            ->groupBy([
                function ($val) {
                    return $val->date->format('Y');
                },
            ]);

        return view('photo.create')->with('albums', $albums);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePhotoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePhotoRequest $request)
    {
        $albumid = null;
        if ($request->has('album')) {
            // Association à un album existant
            $albumid = $request->input('album');
        } elseif ($request->has('albumTitle') && isset($request->albumTitle)) {
            // Si un titre est spécifié, création d'un nouvel album
            $album = new Album();
            $album->title = $request->input('albumTitle');
            $album->description = $request->input('albumDesc');
            $album->date = \Carbon\Carbon::createFromFormat('Y-m', $request->input('albumDate'))->startOfMonth();
            $album->save();
            $albumid = $album->id;
        }

        foreach ($request->file('files') as $file) {
            $request->savePhoto($file, $albumid);
        }

        return back()->with('success', 'Photos mises en ligne avec succès !');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    public function show(Photo $photo)
    {
        return view('photo.show', [
            'photo' => $photo,
            'comments' => $photo->comments,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    public function edit(Photo $photo)
    {
        return view('photo.edit', [
            'photo' => $photo
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\Request  $request
     * @param  \App\Models\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Photo $photo)
    {
        $request->validateWithBag('updatePhoto', [
            'title' => 'required|max:255',
            'legend' => 'nullable|max:2048',
            'taken_at' => 'nullable|date',
        ]);

        $photo->update($request->all());

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Photo mise à jour avec succès !');

        return redirect()->route('photos.show', $photo);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Photo $photo)
    {
        $album = $photo->album;
        if ($photo->delete()) {
            if (isset($album) && $album->photos->count() == 0) {
                $album->delete();
            }
        } else {
            return back()->with('error', 'Erreur lors de la suppression de la photo !');
        }

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Photo supprimée avec succès !');

        return redirect()->route('photos.index');
    }
}
