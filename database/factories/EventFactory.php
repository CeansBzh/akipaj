<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
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
        $start = fake()->dateTimeBetween('+1 hour', '+2 year');
        $end = fake()->dateTimeBetween($start->format('Y-m-d H:i:s').' +1 day', $start->format('Y-m-d H:i:s').' +10 days');

        return [
            'name' => fake()->text(50), // Same limits as the form validation
            'description' => fake()->text(140),
            'location' => fake()->address,
            'start_time' => $start,
            'end_time' => fake()->boolean(20) ? $start : $end,
            'imagePath' => fake()->boolean(50) ? fake()->imageUrl(640, 480, 'cats') : null,
        ];
    }

    /**
     * Indicate that the model's location should be left empty.
     *
     * @return static
     */
    public function noLocation()
    {
        return $this->state(fn (array $attributes) => [
            'location' => null,
        ]);
    }

    /**
     * Indicate that the model's imagePath should be left empty.
     *
     * @return static
     */
    public function noImage()
    {
        return $this->state(fn (array $attributes) => [
            'imagePath' => null,
        ]);
    }

    /**
     * Indicate that the model's start_time should be in the past.
     *
     * @return static
     */
    public function passed()
    {
        $start = fake()->dateTimeBetween('-2 year', '-1 month');
        $end = fake()->dateTimeBetween($start->format('Y-m-d H:i:s').' +1 day', $start->format('Y-m-d H:i:s').' +10 days');

        return $this->state(fn (array $attributes) => [
            'start_time' => $start,
            'end_time' => $end,
        ]);
    }
}
