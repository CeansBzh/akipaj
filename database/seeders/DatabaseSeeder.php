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
        $this->call([
            ArticleSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            PaymentSeeder::class,
            PhotoSeeder::class,
            AlbumSeeder::class,
            EventSeeder::class,
            TripSeeder::class,
            CommentSeeder::class,
        ]);
    }
}
