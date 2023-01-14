<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Album>
 */
class AlbumFactory extends Factory
{

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        // After creating our album, we want to attach some photos to it. But it might also be kept empty (30% chance).
        return $this->afterCreating(function (Album $album) {
            if (fake()->boolean(30)) {
                return;
            }
            $photos = Photo::where('album_id', null)->inRandomOrder()->limit(5)->get();
            if ($photos->count() > 1) {
                $amount = rand(1, $photos->count());
                $photos = $photos->take($amount);
                $album->photos()->saveMany($photos);
            } else {
                $album->photos()->saveMany(\App\Models\Photo::factory(5)->create());
            }
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'title' => fake()->sentence,
            'description' => fake()->paragraph,
            'date' =>  \Carbon\Carbon::parse($this->faker->dateTimeBetween('-50 years', 'now')->format('Y-m-d'))->startOfMonth(),
            'imagePath' => fake()->boolean(50) ? fake()->imageUrl() : null,
        ];
    }
}
