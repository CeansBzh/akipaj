<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trip>
 */
class TripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $start = fake()->dateTimeBetween('-1 year', '+1 year');
        $end = fake()->dateTimeBetween($start->format('Y-m-d H:i:s') . ' +1 day', $start->format('Y-m-d H:i:s') . ' +10 days');

        return [
            'title' => fake()->sentence,
            'description' => fake()->paragraph,
            'start_date' => $start,
            'end_date' => fake()->boolean(10) ? $start : $end,
            'imagePath' => fake()->boolean(60) ? fake()->imageUrl(640, 480, 'cats') : null,
        ];
    }
}
