<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'photos' => Photo::simplePaginate(5),
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
    public function store(Request $request)
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'required|image|mimes:png,jpg,jpeg,gif',
            'album' => 'integer|exists:albums,id|nullable',
            'albumTitle' => 'string|nullable',
            'albumDesc' => 'exclude_if:albumTitle,null|required|string',
            'albumDate' => 'exclude_if:albumTitle,null|required|date',
        ]);

        $albumid = null;
        if ($request->has('album')) {
            $albumid = $request->input('album');
        } elseif ($request->has('albumTitle') && isset($request->albumTitle)) {
            $album = new Album();
            $album->title = $request->input('albumTitle');
            $album->description = $request->input('albumDesc');
            $album->date = \Carbon\Carbon::createFromFormat('Y-m', $request->input('albumDate'))->startOfMonth();
            $album->save();
            $albumid = $album->id;
        }

        foreach ($request->file('files') as $file) {
            $path = $file->store('photos', 'public');
            $photo = Photo::create([
                'title' => $file->getClientOriginalName(),
                'path' =>  Storage::url($path),
                'legend' => $request->legend,
                'taken' => $request->taken,
            ]);
            if (isset($albumid)) {
                $photo->album()->associate($albumid);
            }
            $photo->user()->associate($request->user());
            $photo->save();
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
     * @param  \App\Http\Requests\UpdatePhotoRequest  $request
     * @param  \App\Models\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Photo $photo)
    {
        $request->validate([
            'title' => 'required|max:255',
            'legend' => 'nullable|max:2048',
            'taken' => 'nullable|date',
        ]);

        $photo->update($request->all());

        return redirect()->route('photos.show', $photo)->with('success', 'Photo mise à jour avec succès !');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Photo  $photo
     * @return \Illuminate\Http\Response
     */
    public function destroy(Photo $photo)
    {
        $photo->delete();

        return redirect()->route('photos.index')->with('success', 'Photo supprimée avec succès !');
    }
}