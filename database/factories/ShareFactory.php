<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\Share;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Share>
 */
class ShareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shareable_type' => Photo::class,
            'shareable_id' => Photo::factory(),
            'user_id' => User::factory(),
            'expires_in_days' => Share::DEFAULT_EXPIRY_DAYS,
        ];
    }

    /**
     * Lien déjà expiré.
     */
    public function expired(): static
    {
        return $this->state(fn (): array => ['expires_at' => now()->subMinute()]);
    }
}
