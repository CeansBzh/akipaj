<?php

namespace Database\Factories;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $title = fake()->sentence;
        $slug = Str::slug($title, '-');
        $body_md = fake()->markdown;
        $body_html = Str::markdown($body_md);

        return [
            'title' => $title,
            'slug' => $slug,
            'summary' => fake()->paragraph,
            'body_md' => $body_md,
            'body_html' => $body_html,
            'online' => true,
            'published_at' => fake()->dateTimeBetween('-2 year', 'today'),
            'imagePath' => fake()->imageUrl(640, 480, 'cats'),
        ];
    }

    /**
     * Indicate that the model should not be online.
     *
     * @return static
     */
    public function draft()
    {
        return $this->state(fn (array $attributes) => [
            'online' => false,
            'published_at' => null,
        ]);
    }
}
