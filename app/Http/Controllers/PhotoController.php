<?php

namespace App\Http\Controllers;

use App\Enums\AlertLevelEnum;
use App\Http\Requests\Photo\StorePhotoRequest;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PhotoController extends Controller
{
    /**
     * Create the controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(Photo::class, 'photo');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // Les photos sont récupérées dans la galerie Livewire
        return view('photo.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Album $album): View
    {
        return view('photo.create')->with('album', $album);
    }

    /**
     * Store a newly created resource in storage.
     *
     * The upload form sends the photos in batches as JSON requests, since PHP drops the files beyond
     * max_file_uploads (20 by default) in a single request.
     */
    public function store(StorePhotoRequest $request): RedirectResponse|JsonResponse
    {
        $album = Album::find($request->album);
        $disk = Storage::disk('photos');
        $disk->makeDirectory('thumbs');
        // Enregistrement des photos
        foreach ($request->file('files') as $file) {
            // Lecture des données EXIF
            $exif = @exif_read_data($file->getRealPath());
            // Pour chaque fichier enregistrement dans le stockage privé des photos
            $imageResource = imagecreatefromstring($file->getContent());
            imagejpeg($imageResource, $disk->path($file->hashName()));
            // Enregistrement de la miniature
            $width = imagesx($imageResource);
            $height = imagesy($imageResource);
            // Calcul du ratio de redimensionnement: on conserve le format de l'image en limitant la largeur et la hauteur à 450px
            $ratio = min(450 / $width, 450 / $height);
            $thumbResource = imagescale($imageResource, intval($ratio * $width), intval($ratio * $height));
            imagejpeg($thumbResource, $disk->path('thumbs/thumb_'.$file->hashName()));
            // Création d'une nouvelle photo
            $photo = new Photo;
            $photo->title = $file->getClientOriginalName();
            $photo->path = route('photos.file', $file->hashName(), false);
            $photo->thumb_path = route('photos.file', 'thumbs/thumb_'.$file->hashName(), false);
            $photo->width = $width;
            $photo->height = $height;
            $photo->thumb_width = imagesx($thumbResource);
            $photo->thumb_height = imagesy($thumbResource);
            // Si les données exif sont présentes
            if (is_array($exif)) {
                if (isset($exif['DateTimeOriginal']) && $exif['DateTimeOriginal'] != '0000:00:00 00:00:00') {
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

        session()->flash('alert-'.AlertLevelEnum::SUCCESS->name, 'Photos ajoutées avec succès !');

        if ($request->expectsJson()) {
            return response()->json(['redirect' => route('albums.show', $album)]);
        }

        return redirect()->route('albums.show', $album);
    }

    /**
     * Display the specified resource.
     */
    public function show(Photo $photo): View
    {
        return view('photo.show', [
            'photo' => $photo,
            'comments' => $photo->comments,
        ]);
    }

    /**
     * Send the file of a photo or of its thumbnail, which isn't publicly accessible.
     */
    public function file(string $path): StreamedResponse
    {
        $disk = Storage::disk('photos');

        abort_unless($disk->fileExists($path), 404);

        // The file names are unique, so the browser can keep them, but shared caches must not
        return $disk->response($path, headers: [
            'Cache-Control' => 'private, max-age=31536000, immutable',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Photo $photo): View
    {
        return view('photo.edit', [
            'photo' => $photo,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Photo $photo): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePhoto', [
            'title' => 'required|max:255',
            'legend' => 'nullable|max:2048',
            'taken_at' => 'nullable|date',
        ]);

        $photo->update($validated);

        session()->flash('alert-'.AlertLevelEnum::SUCCESS->name, 'Photo mise à jour avec succès !');

        return redirect()->route('photos.show', $photo);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Photo $photo): RedirectResponse
    {
        // Suppression de la photo
        $photo->delete();

        session()->flash('alert-'.AlertLevelEnum::SUCCESS->name, 'Photo supprimée avec succès !');

        return redirect()->route('photos.index');
    }
}
