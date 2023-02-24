<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Enums\AlertLevelEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        // Gets all emptys albums (without photos). The listing of the albums is done in the view using Livewire.
        return view('album.index', ['emptyAlbums' => Album::doesntHave('photos')->get()]);
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
            'image' => ['nullable', 'mimes:png,jpg,jpeg,gif', 'max:2000'],
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
        // Enregistrement de l'image de couverture si elle est renseignée.
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $path = $request->file('image')->storeAs('public/albums', $imageName);
            $album->imagePath = Storage::url($path);
        }
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
            'image' => ['nullable', 'mimes:png,jpg,jpeg,gif', 'max:2000'],
            'trips' => ['nullable', 'array'],
            'trips.*' => ['nullable', 'integer', 'distinct', 'exists:trips,id'],
        ]);

        // Calcul de la date de l'album à partir du mois et de l'année.
        $date = date('Y-m-d', strtotime($request->year . '-' . $request->month . '-01'));
        // Modification de l'album.
        $album->title = $request->title;
        $album->description = $request->description;
        $album->date = $date;
        // Suppression de l'image actuelle si demandée, ou si une autre image a été uploadée
        if (($request->remove_image || $request->hasFile('image')) && $album->imagePath) {
            $filePath = 'public/trips/' . basename($album->imagePath);
            if (Storage::delete($filePath)) {
                $album->imagePath = null;
            }
        }
        // Si une nouvelle image a été uploadée, on la sauvegarde
        if ($request->hasFile('image')) {
            $imageName = time() . '.' . $request->image->extension();
            $path = $request->file('image')->storeAs('public/trips', $imageName);
            $album->imagePath = Storage::url($path);
        }
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
        // Vérification que l'album ne contient pas de photos appartenant à d'autres utilisateurs.
        if ($album->has('photos') && $album->photos()->where('user_id', '!=', auth()->id())->exists()) {
            session()->flash('alert-' . AlertLevelEnum::WARNING->name, 'L\'album contient des photos ne vous appartenant pas. Suppression interdite.');
            return redirect()->back();
        }
        // Suppression des photos de l'album.
        $album->photos()->delete();
        // Suppression de l'album.
        $album->delete();

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Album supprimé avec succès.');

        return redirect()->route('albums.index');
    }
}
