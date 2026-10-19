<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_can_be_updated(): void
    {
        $user = $this->actingAsMember();

        $this->from(route('settings.edit'))
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'password-updated')
            ->assertRedirect(route('settings.edit'));

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password(): void
    {
        $user = $this->actingAsMember();

        $this->from(route('settings.edit'))
            ->put(route('password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertSessionHasErrorsIn('updatePassword', 'current_password')
            ->assertRedirect(route('settings.edit'));

        $this->assertTrue(Hash::check('password', $user->refresh()->password));
    }

    public function test_guests_cannot_update_a_password(): void
    {
        $this->put(route('password.update'))->assertRedirect(route('login'));
    }
}
