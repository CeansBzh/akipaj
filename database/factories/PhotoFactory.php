<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends Factory<Photo>
 */
class PhotoFactory extends Factory
{
    /**
     * Configure the model factory.
     *
     * @return static
     */
    public function configure()
    {
        return $this->afterCreating(function (Photo $photo) {
            // Création d'un abonnement aux commentaires de la photo pour l'utilisateur qui l'a créée
            $photo->subscriptions()->create([
                'user_id' => $photo->user_id,
            ]);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Création d'une image aléatoire, générée localement pour ne pas dépendre du réseau (seeders et tests)
        $fileName = fake()->slug.'.jpg';
        $disk = Storage::disk('photos');
        $disk->makeDirectory('thumbs');
        $imageResource = $this->generateImage();
        imagejpeg($imageResource, $disk->path($fileName));
        // Enregistrement de la miniature
        $width = imagesx($imageResource);
        $height = imagesy($imageResource);
        // Calcul du ratio de redimensionnement: on conserve le format de l'image en limitant la largeur et la hauteur à 450px
        $ratio = min(450 / $width, 450 / $height);
        $thumbResource = imagescale($imageResource, intval($ratio * $width), intval($ratio * $height));
        imagejpeg($thumbResource, $disk->path('thumbs/thumb_'.$fileName));

        // Probabilité d'avoir des coordonnées GPS
        $hasCoordinates = fake()->boolean(50);

        return [
            'album_id' => Album::inRandomOrder()->first()->id ?? Album::factory(),
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'title' => fake()->sentence,
            'path' => route('photos.file', $fileName, false),
            'thumb_path' => route('photos.file', 'thumbs/thumb_'.$fileName, false),
            'width' => $width,
            'height' => $height,
            'thumb_width' => imagesx($thumbResource),
            'thumb_height' => imagesy($thumbResource),
            'legend' => fake()->boolean(50) ? fake()->paragraph : null,
            'latitude' => $hasCoordinates ? fake()->latitude : null,
            'longitude' => $hasCoordinates ? fake()->longitude : null,
            'taken_at' => fake()->boolean(50) ? fake()->dateTime : null,
        ];
    }

    /**
     * Generate a random landscape or portrait image made of colored rectangles.
     *
     * @return \GdImage
     */
    protected function generateImage()
    {
        [$width, $height] = fake()->boolean() ? [800, 600] : [600, 800];
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255)));
        for ($i = 0; $i < 5; $i++) {
            $color = imagecolorallocate($image, rand(0, 255), rand(0, 255), rand(0, 255));
            imagefilledrectangle($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $color);
        }

        return $image;
    }
}
