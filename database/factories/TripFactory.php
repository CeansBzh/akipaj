<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\Boat;
use App\Models\Photo;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
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
        $end = fake()->dateTimeBetween($start->format('Y-m-d H:i:s').' +1 day', $start->format('Y-m-d H:i:s').' +10 days');

        return [
            'title' => fake()->text(50), // Same limits as the form validation
            'description' => fake()->text(500),
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
            $trip->users()->saveMany(User::factory($count)->create());
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
            $trip->albums()->saveMany(Album::factory($count)->create()->each(function ($album) use ($withPhotos) {
                if ($withPhotos) {
                    Photo::factory(rand(1, 10))->for($album)->create();
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
            $trip->boats()->saveMany(Boat::factory($count)->create([
                'trip_id' => $trip->id,
            ]));
        });
    }
}
