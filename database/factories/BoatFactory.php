<?php

namespace Database\Factories;

use App\Enums\BoatTypeEnum;
use App\Models\Boat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Boat>
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
            'model' => fake()->words(2, true),
            'type' => fake()->randomElement(BoatTypeEnum::cases()),
            'year' => fake()->year,
            'builder' => fake()->word,
            'renter' => fake()->word,
            'city' => fake()->city,
        ];
    }
}
