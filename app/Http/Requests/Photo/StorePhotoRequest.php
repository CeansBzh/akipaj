<?php

namespace App\Http\Requests\Photo;

use App\Models\Photo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class StorePhotoRequest extends FormRequest
{
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
            'album' => ['required', 'exists:albums,id'],
        ];
    }

    /**
     * Get the validation messages that apply to the request.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'files.required' => 'Veuillez sélectionner des photos à envoyer.',
            'album.exists' => 'L\'album sélectionné n\'existe pas.'
        ];
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
