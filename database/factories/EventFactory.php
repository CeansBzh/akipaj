<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
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
            'name' => fake()->sentence,
            'description' => fake()->paragraph,
            'location' => fake()->boolean(50) ? fake()->address : null,
            'start_time' => $start,
            'end_time' => fake()->boolean(20) ? $start : $end,
            'imagePath' => fake()->boolean(50) ? fake()->imageUrl(640, 480, 'cats') : null,
        ];
    }
}
