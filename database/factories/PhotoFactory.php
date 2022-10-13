<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

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
        $image = file_get_contents('https://cataas.com/cat');
        $fileName = fake()->slug . '.jpg';
        Storage::put('public/photos/'.$fileName, $image);

        return [
            'album_id' => fake()->boolean(50) ? \App\Models\Album::inRandomOrder()->first()->id : null,
            'title' => fake()->sentence,
            'path' => Storage::url('public/photos/'.$fileName),
            'legend' => fake()->boolean(50) ? fake()->paragraph : null,
            'taken' => fake()->boolean(50) ? fake()->date : null,
        ];
    }
}
