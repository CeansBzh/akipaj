<?php

namespace Database\Factories;

use App\Models\CoursePoint;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CoursePoint>
 */
class CoursePointFactory extends Factory
{
    /**
     * Donne un point au large de la Bretagne sud, le premier jour de la sortie.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'trip_id' => Trip::factory(),
            'position' => fake()->unique()->numberBetween(1, 10000),
            'day' => 1,
            'name' => fake()->city(),
            'latitude' => fake()->randomFloat(6, 47.2, 47.8),
            'longitude' => fake()->randomFloat(6, -4.0, -2.8),
        ];
    }
}
