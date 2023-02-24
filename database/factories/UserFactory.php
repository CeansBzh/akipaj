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
        return [
            'name' => fake()->name(),
            'email' => fake()
                ->unique()
                ->safeEmail(),
            'email_verified_at' => now(),
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'profile_picture_path' => fake()->imageUrl(),
            'firstname' => fake()->firstName(),
            'lastname' => fake()->lastName(),
            'birthdate' => fake()->dateTimeBetween('-50 years', '-18 years'),
            'mobile_phone' => fake()->phoneNumber(),
            'home_phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'postal_code' => Address::postcode(),
            'city' => fake()->city(),
            'clothing_size' => fake()->randomElement(['XS', 'S', 'M', 'L', 'XL', 'XXL']),
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
        return $this->state(
            fn(array $attributes) => [
                'email_verified_at' => null,
            ],
        );
    }

    /**
     * Indicate that the model's personal information should be left empty.
     *
     * @return static
     */
    public function noPersonalInformation()
    {
        return $this->state(
            fn(array $attributes) => [
                'firstname' => null,
                'lastname' => null,
                'birthdate' => null,
                'mobile_phone' => null,
                'home_phone' => null,
                'address' => null,
                'postal_code' => null,
                'city' => null,
                'clothing_size' => null,
            ],
        );
    }

    /**
     * Indicate that the model's profile picture should be left empty.
     *
     * @return static
     */
    public function noProfilePicture()
    {
        return $this->state(
            fn(array $attributes) => [
                'profile_picture_path' => null,
            ],
        );
    }

    /**
     * Indicate that the model's level should be set to the given value.
     *
     * @param int $level The level to set. Use the UserLevelEnum.
     * @return static
     */
    public function withLevel($level)
    {
        return $this->state(
            fn(array $attributes) => [
                'level' => $level,
            ],
        );
    }
}
