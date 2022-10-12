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
        $image = file_get_contents(fake()->imageUrl);
        $fileName = fake()->slug . '.jpg';
        Storage::put($fileName, $image);

        return [
            'title' => fake()->sentence,
            'path' => Storage::url($fileName),
            'legend' => fake()->boolean(50) ? fake()->paragraph : null,
            'place' => fake()->boolean(50) ? fake()->city : null,
            'taken' => fake()->boolean(50) ? fake()->date : null,
        ];
    }
}
