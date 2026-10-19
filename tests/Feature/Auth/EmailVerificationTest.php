<?php

namespace Tests\Feature\Auth;

use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Email verification comes from Breeze but is dormant: App\Models\User doesn't implement MustVerifyEmail,
 * so no link is sent at registration and the "verified" middleware isn't used.
 */
class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $this->actingAsMember()->forceFill(['email_verified_at' => null])->save();

        $this->get(route('verification.notice'))->assertOk()->assertViewIs('auth.verify-email');
    }

    public function test_verified_users_are_redirected_from_the_verification_screen(): void
    {
        $this->actingAsMember();

        $this->get(route('verification.notice'))->assertRedirect(route('home', absolute: false));
    }

    public function test_email_can_be_verified(): void
    {
        $user = $this->actingAsMember();
        $user->forceFill(['email_verified_at' => null])->save();
        Event::fake();

        $verificationUrl = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1($user->email),
        ]);

        $this->get($verificationUrl)->assertRedirect(route('home', absolute: false) . '?verified=1');

        $this->assertTrue($user->fresh()?->hasVerifiedEmail());
        // The Verified event is only dispatched for MustVerifyEmail users
        Event::assertNotDispatched(Verified::class);
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = $this->actingAsMember();
        $user->forceFill(['email_verified_at' => null])->save();

        $verificationUrl = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
            'id' => $user->id,
            'hash' => sha1('wrong-email'),
        ]);

        $this->get($verificationUrl)->assertForbidden();

        $this->assertFalse($user->fresh()?->hasVerifiedEmail());
    }

    public function test_verification_link_is_not_resent_to_verified_users(): void
    {
        Notification::fake();
        $this->actingAsMember();

        $this->post(route('verification.send'))->assertRedirect(route('home', absolute: false));

        Notification::assertNothingSent();
    }

    public function test_verification_link_can_be_resent(): void
    {
        Notification::fake();
        $user = $this->actingAsMember();
        $user->forceFill(['email_verified_at' => null])->save();

        $this->post(route('verification.send'))->assertSessionHas('status', 'verification-link-sent');

        Notification::assertSentTo($user, VerifyEmail::class);
    }
}
