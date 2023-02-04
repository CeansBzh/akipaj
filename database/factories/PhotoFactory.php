<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Album;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Création d'une image aléatoire
        $fileName = fake()->slug . '.jpg';
        $image = file_get_contents('https://cataas.com/cat');
        $imageResource = imagecreatefromstring($image);
        imagejpeg($imageResource, public_path('storage/photos/' . $fileName));
        // Enregistrement de la miniature
        $width = imagesx($imageResource);
        $height = imagesy($imageResource);
        // Calcul du ratio de redimensionnement: on conserve le format de l'image en limitant la largeur et la hauteur à 450px
        $ratio  = min(450 / $width, 450 / $height);
        $thumbResource = imagescale($imageResource, intval($ratio * $width), intval($ratio * $height));
        imagejpeg($thumbResource, public_path('storage/photos/thumbs/thumb_' . $fileName));

        // Probabilité d'avoir des coordonnées GPS
        $hasCoordinates = fake()->boolean(50);

        return [
            'album_id' => Album::inRandomOrder()->first()->id ?? Album::factory(),
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'title' => fake()->sentence,
            'path' => Storage::url('photos/' . $fileName),
            'thumb_path' => Storage::url('photos/thumbs/thumb_' . $fileName),
            'width' => $width,
            'height' => $height,
            'thumb_width' => imagesx($thumbResource),
            'thumb_height' => imagesy($thumbResource),
            'legend' => fake()->boolean(50) ? fake()->paragraph : null,
            'latitude' => $hasCoordinates ? fake()->latitude : null,
            'longitude' => $hasCoordinates ? fake()->longitude : null,
            'taken_at' => fake()->boolean(50) ? fake()->dateTime : null,
        ];

        // TODO thread subscriptions
    }
}
