<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $user = User::inRandomOrder()->first();
        return [
            'user_id' => $user->id,
            'amount' => fake()->randomFloat(2, 0, 1000),
            'description' => fake()->boolean(50) ? fake()->sentence : 'Virement de ' . $user->name . ' à ' . config('app.name'),
        ];
    }
}
