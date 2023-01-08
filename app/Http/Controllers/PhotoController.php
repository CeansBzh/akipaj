<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Photo;
use App\Enum\AlertLevelEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            'photos' => Photo::select('id')->simplePaginate(50),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Album $album)
    {
        return view('photo.create')->with('album', $album);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StorePhotoRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePhotoRequest $request)
    {
        $album = Album::find($request->album);
        // Enregistrement des photos
        foreach ($request->file('files') as $file) {
            // Lecture des données EXIF
            $exif = @exif_read_data($file->getRealPath());
            // Pour chaque fichier enregistrement dans le stockage du site
            $imageResource = imagecreatefromjpeg($file->getRealPath());
            imagejpeg($imageResource, public_path('storage/photos/' . $file->hashName()));
            // Enregistrement de la miniature
            $width = imagesx($imageResource);
            $height = imagesy($imageResource);
            // Calcul du ratio de redimensionnement: on conserve le format de l'image en limitant la largeur et la hauteur à 450px
            $ratio  = min(450 / $width, 450 / $height);
            $thumbResource = imagescale($imageResource, intval($ratio * $width), intval($ratio * $height));
            imagejpeg($thumbResource, public_path('storage/photos/thumbs/thumb_' . $file->hashName()));
            // Création d'une nouvelle photo
            $photo = new Photo();
            $photo->title = $file->getClientOriginalName();
            $photo->path = Storage::url('photos/' . $file->hashName());
            $photo->thumb_path = Storage::url('photos/thumbs/thumb_' . $file->hashName());
            $photo->width = $width;
            $photo->height = $height;
            $photo->thumb_width = imagesx($thumbResource);
            $photo->thumb_height = imagesy($thumbResource);
            // Si les données exif sont présentes
            if (is_array($exif)) {
                if (isset($exif['DateTimeOriginal'])) {
                    $photo->taken_at = $exif['DateTimeOriginal'];
                }
                if (isset($exif['GPSLatitude']) && isset($exif['GPSLatitudeRef']) && isset($exif['GPSLongitude']) && isset($exif['GPSLongitudeRef'])) {
                    $photo->latitude = $request->toGps($exif['GPSLatitude'], $exif['GPSLatitudeRef']);
                    $photo->longitude = $request->toGps($exif['GPSLongitude'], $exif['GPSLongitudeRef']);
                }
            }
            // Association de la photo à l'album
            $photo->album()->associate($album);
            // Création de la relation entre la photo et l'utilisateur
            $photo->user()->associate($request->user());
            // Enregistrement de la photo dans la base de données
            $photo->save();
            // Abonnement de l'utilisateur au fil de discussion
            $photo->subscriptions()->create([
                'user_id' => $request->user()->id,
            ]);
        }

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Photos ajoutées avec succès !');

        return redirect()->route('albums.show', $album);
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
        // Suppression de la photo
        $photo->delete();

        session()->flash('alert-' . AlertLevelEnum::SUCCESS->name, 'Photo supprimée avec succès !');

        return redirect()->route('photos.index');
    }
}
