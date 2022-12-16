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
        $image = file_get_contents('https://cataas.com/cat');
        $fileName = fake()->slug . '.jpg';
        Storage::put('public/photos/' . $fileName, $image);

        return [
            'album_id' => null,
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'title' => fake()->sentence,
            'path' => Storage::url('public/photos/' . $fileName),
            'legend' => fake()->boolean(50) ? fake()->paragraph : null,
            'taken_at' => fake()->boolean(50) ? fake()->dateTime : null,
        ];
    }
}
