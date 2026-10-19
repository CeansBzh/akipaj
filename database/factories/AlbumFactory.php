<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\Photo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Album>
 */
class AlbumFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => fake()->text(63), // Same limits as the form validation
            'description' => fake()->text(255),
            'date' => Carbon::parse($this->faker->dateTimeBetween('-50 years', 'now')->format('Y-m-d'))->startOfMonth(),
            'imagePath' => fake()->boolean(50) ? fake()->imageUrl() : null,
        ];
    }

    /**
     * Indicate that the model should contains photos.
     *
     * @return static
     */
    public function withPhotos($count)
    {
        return $this->afterCreating(function (Album $album) use ($count) {
            $album->photos()->saveMany(Photo::factory($count)->create());
        });
    }
}
