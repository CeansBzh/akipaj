<?php

namespace Database\Factories;

use Faker\Provider\Address;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Probabilité que l'utilisateur ait rempli ses informations de profil
        $hasFilledProfile = fake()->boolean(50);

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'profile_picture_path' => fake()->boolean(50) ? fake()->imageUrl() : null,
            'firstname' => $hasFilledProfile ? fake()->firstName() : null,
            'lastname' => $hasFilledProfile ? fake()->lastName() : null,
            'birthdate' => $hasFilledProfile ? fake()->dateTimeBetween('-50 years', '-18 years') : null,
            'mobile_phone' => $hasFilledProfile ? fake()->phoneNumber() : null,
            'home_phone' => $hasFilledProfile ? fake()->phoneNumber() : null,
            'address' => $hasFilledProfile ? fake()->address() : null,
            'postal_code' => $hasFilledProfile ? Address::postcode() : null,
            'city' => $hasFilledProfile ? fake()->city() : null,
            'clothing_size' => $hasFilledProfile ? fake()->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']) : null,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
