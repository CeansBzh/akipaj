<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
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
        $user = User::inRandomOrder()->first() ?? User::factory()->create();

        return [
            'user_id' => $user->id,
            'amount' => fake()->randomFloat(2, 0, 1000),
            'description' => fake()->boolean(50) ? fake()->sentence : 'Virement de '.$user->name.' à '.config('app.name'),
        ];
    }
}
