<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\User::factory()->create([
            'name' => 'Michel',
            'email' => 'michel@test.com',
        ]);

        \App\Models\Album::factory(5)->create();
        \App\Models\Photo::factory(10)->create();
        \App\Models\Comment::factory(20)->create();
    }
}
