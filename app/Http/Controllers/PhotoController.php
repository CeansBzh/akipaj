<?php

namespace App\Http\Controllers;

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
        return view('photo.create')->with('albums', \App\Models\Album::all()
            ->groupBy([
                function ($val) {
                    return $val->date->format('Y');
                },
                function ($val) {
                    return $val->date->format('m');
                },
            ]));
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
            'album' => 'required_without_all:albumName,albumDesc,albumDate|integer',
            'albumName' => 'required_without:album|string',
            'albumDesc' => 'required_without:album|string',
            'albumDate' => 'required_without:album|date',
        ]);

        foreach ($request->file('files') as $file) {
            $path = $file->store('photos', 'public');
            $photo = Photo::create([
                'album_id' => 1, // TODO gestion albums
                'title' => $file->getClientOriginalName(),
                'path' =>  Storage::url($path),
                'legend' => $request->legend,
                'taken' => $request->taken,
            ]);
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
            'photo' => $photo
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
