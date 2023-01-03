<?php

namespace App\Http\Requests\Photo;

use App\Models\Photo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class StorePhotoRequest extends FormRequest
{
    protected $errorBag = 'storePhoto';

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this['albumDate'] = $this['albumYear'] . '-' . $this['albumMonth'];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'files' => ['required', 'array', 'max:50'],
            'files.*' => ['required', 'image', 'mimes:png,jpg,jpeg,gif', 'max:10000', 'dimensions:max_width=2560,max_height=1600'],
            'album' => ['integer', 'exists:albums,id', 'nullable'],
            'albumTitle' => ['string', 'nullable'],
            'albumDesc' => ['exclude_if:albumTitle,null', 'required', 'string'],
            'albumDate' => ['exclude_if:albumTitle,null', 'required', 'date'],
        ];
    }

    public function savePhoto($file, $albumid)
    {
        // Lecture des données EXIF
        $exif = exif_read_data($file->getRealPath());
        // Si la date de prise de vue est présente dans les données EXIF, on l'utilise
        if (isset($exif['DateTimeOriginal'])) {
            $this->merge(['taken_at' => $exif['DateTimeOriginal']]);
        }
        // Si les données GPS sont présentes dans les données EXIF, on les utilise
        if (isset($exif['GPSLatitude']) && isset($exif['GPSLatitudeRef']) && isset($exif['GPSLongitude']) && isset($exif['GPSLongitudeRef'])) {
            $this->merge([
                'latitude' => $this->toGps($exif['GPSLatitude'], $exif['GPSLatitudeRef']),
                'longitude' => $this->toGps($exif['GPSLongitude'], $exif['GPSLongitudeRef']),
            ]);
        }
        // Pour chaque fichier enregistrement dans le stockage du site
        $imageResource = imagecreatefromjpeg($file->getRealPath());
        imagejpeg($imageResource, public_path('storage/photos/' . $file->hashName()), 100);
        // Création d'une nouvelle photo
        $photo = new Photo();
        $photo->title = $file->getClientOriginalName();
        $photo->path = Storage::url('photos/' . $file->hashName());
        $photo->legend = $this->legend;
        $photo->taken_at = $this->taken_at;
        $photo->latitude = $this->latitude;
        $photo->longitude = $this->longitude;
        // Association de la photo à l'album si nécessaire
        if (isset($albumid)) {
            $photo->album()->associate($albumid); // TODO refonte album
        }
        // Création de la relation entre la photo et l'utilisateur
        $photo->user()->associate($this->user());
        // Enregistrement de la photo dans la base de données
        $photo->save();
        // Abonnement de l'utilisateur au fil de discussion
        $photo->subscriptions()->create([
            'user_id' => $this->user()->id,
        ]);
    }

    public function toGps($coordinate, $hemisphere)
    {
        if (is_string($coordinate)) {
            $coordinate = array_map("trim", explode(",", $coordinate));
        }
        for ($i = 0; $i < 3; $i++) {
            $part = explode('/', $coordinate[$i]);
            if (count($part) == 1) {
                $coordinate[$i] = $part[0];
            } else if (count($part) == 2) {
                $coordinate[$i] = floatval($part[0]) / floatval($part[1]);
            } else {
                $coordinate[$i] = 0;
            }
        }
        list($degrees, $minutes, $seconds) = $coordinate;
        $sign = ($hemisphere == 'W' || $hemisphere == 'S') ? -1 : 1;
        return $sign * ($degrees + $minutes / 60 + $seconds / 3600);
    }
}
