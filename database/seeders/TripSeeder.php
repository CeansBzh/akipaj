<?php

namespace Database\Seeders;

use App\Models\Album;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Seeder;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Populate trips
        Trip::factory(5)->create()->each(function ($trip) {
            // Add users to trip
            $users = User::inRandomOrder()->take(5)->get();
            $trip->users()->saveMany($users);

            // Add albums to trip
            $albums = Album::inRandomOrder()->take(5)->get();
            $trip->albums()->saveMany($albums);
        });
    }
}
