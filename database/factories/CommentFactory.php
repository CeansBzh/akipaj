<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_id' => \App\Models\User::inRandomOrder()->first()->id,
            'commentable_id' => \App\Models\Photo::inRandomOrder()->first()->id,
            'commentable_type' => \App\Models\Photo::class,
            'content' => fake()->paragraph,
        ];
    }
}
