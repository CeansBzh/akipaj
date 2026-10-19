<?php

namespace Tests\Feature\Auth;

use App\Enums\UserLevelEnum;
use App\Models\User;
use App\Notifications\AccountCreated;
use App\Notifications\RegistrationSuccessful;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get(route('register'))->assertOk()->assertViewIs('auth.register');
    }

    public function test_new_users_can_register_as_guests(): void
    {
        Notification::fake();

        $response = $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);

        $user = User::where('email', 'test@example.com')->firstOrFail();
        // New accounts must be validated by an administrator
        $this->assertSame(UserLevelEnum::GUEST, $user->level);
    }

    public function test_registration_notifies_the_new_user_and_the_administrators(): void
    {
        Notification::fake();
        $admin = $this->createUser(UserLevelEnum::ADMINISTRATOR);
        $member = $this->createUser(UserLevelEnum::MEMBER);

        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->firstOrFail();
        Notification::assertSentTo($user, RegistrationSuccessful::class);
        Notification::assertSentTo($admin, AccountCreated::class);
        Notification::assertNotSentTo($member, AccountCreated::class);
    }

    public function test_registration_requires_a_unique_email_and_a_confirmed_password(): void
    {
        $existing = $this->createUser();

        $this->post(route('register'), [
            'name' => 'Test User',
            'email' => $existing->email,
            'password' => 'password',
            'password_confirmation' => 'other-password',
        ])->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }
}
