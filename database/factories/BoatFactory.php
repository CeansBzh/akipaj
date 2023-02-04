<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\=Boat>
 */
class BoatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->sentence,
            'type' => fake()->sentence,
            'year' => fake()->year,
            'builder' => fake()->word,
            'renter' => fake()->word,
            'city' => fake()->city,
        ];
    }
}
