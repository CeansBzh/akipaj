<?php

namespace Database\Factories;

use App\Models\Trip;
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

    /**
     * Indicate that the model should contains users.
     *
     * @return static
     */
    public function withUsers($count)
    {
        return $this->afterCreating(function (Trip $trip) use ($count) {
            $trip->users()->saveMany(\App\Models\User::factory($count)->create());
        });
    }

    /**
     * Indicate that the model should contains albums.
     *
     * @return static
     */
    public function withAlbums($count, $withPhotos = false)
    {
        return $this->afterCreating(function (Trip $trip) use ($count, $withPhotos) {
            $trip->albums()->saveMany(\App\Models\Album::factory($count)->create()->each(function ($album) use ($withPhotos) {
                if ($withPhotos) {
                    $album->withPhotos(rand(1, 10));
                }
            }));
        });
    }

    /**
     * Indicate that the model should contains boats.
     *
     * @return static
     */
    public function withBoats($count)
    {
        return $this->afterCreating(function (Trip $trip) use ($count) {
            $trip->boats()->saveMany(\App\Models\Boat::factory($count)->create([
                'trip_id' => $trip->id,
            ]));
        });
    }
}
