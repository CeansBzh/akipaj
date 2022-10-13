<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Album>
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
            'title' => fake()->sentence,
            'description' => fake()->paragraph,
            'date' =>  \Carbon\Carbon::parse($this->faker->dateTimeBetween('-50 years', 'now')->format('Y-m-d'))->startOfMonth(),
        ];
    }
}
