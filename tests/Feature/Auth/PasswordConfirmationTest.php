<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $this->actingAsMember();

        $this->get(route('password.confirm'))->assertOk();
    }

    public function test_password_can_be_confirmed(): void
    {
        $this->actingAsMember();

        $this->post(route('password.confirm'), ['password' => 'password'])
            ->assertRedirect()
            ->assertSessionHasNoErrors()
            ->assertSessionHas('auth.password_confirmed_at');
    }

    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $this->actingAsMember();

        $this->post(route('password.confirm'), ['password' => 'wrong-password'])->assertSessionHasErrors('password');
    }
}
