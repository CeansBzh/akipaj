<?php

namespace Tests\Feature\Account;

use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_is_displayed(): void
    {
        $user = $this->actingAsMember();

        $response = $this->get(route('settings.edit'))->assertOk()->assertViewIs('settings.edit');

        $this->assertTrue($response->viewData('user')->is($user));
    }

    public function test_account_information_can_be_updated(): void
    {
        $user = $this->actingAsMember();

        $this->patch(route('settings.update'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'remove_image' => 'false',
        ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'profile-updated')
            ->assertRedirect(route('settings.edit'));

        $user->refresh();
        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = $this->actingAsMember();

        $this->patch(route('settings.update'), [
            'name' => 'Test User',
            'email' => $user->email,
            'remove_image' => 'false',
        ])->assertSessionHasNoErrors();

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_email_must_be_unique(): void
    {
        $this->actingAsMember();
        $other = $this->createUser();

        $this->patch(route('settings.update'), [
            'email' => $other->email,
            'remove_image' => 'false',
        ])->assertSessionHasErrors('email');
    }

    public function test_personal_information_is_normalized_and_saved(): void
    {
        $user = $this->actingAsMember();

        $this->patch(route('settings.update'), [
            'remove_image' => 'false',
            'firstname' => 'Michel',
            'lastname' => 'Dupont',
            'birthdate' => '1980-05-12',
            'mobile_phone' => '06 12 34 56 78',
            'home_phone' => '02 98 00 00 00',
            'address' => '1 rue du Port',
            'postal_code' => '29200',
            'city' => 'Brest',
            'clothing_size' => '--',
        ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status', 'personal-updated');

        $user->refresh();
        $this->assertSame('0612345678', $user->mobile_phone);
        $this->assertSame('0298000000', $user->home_phone);
        $this->assertSame('1980-05-12', $user->birthdate->format('Y-m-d'));
        $this->assertNull($user->clothing_size);
    }

    public function test_invalid_personal_information_is_rejected(): void
    {
        $this->actingAsMember();

        $this->patch(route('settings.update'), [
            'remove_image' => 'false',
            'birthdate' => now()->addDay()->format('Y-m-d'),
            'postal_code' => '12',
            'clothing_size' => 'XXXL',
            'mobile_phone' => '+33 6 12 34 56 78',
        ])->assertSessionHasErrors(['birthdate', 'postal_code', 'clothing_size', 'mobile_phone']);
    }

    public function test_users_cannot_change_their_own_level(): void
    {
        $user = $this->actingAsMember();

        $this->patch(route('settings.update'), ['remove_image' => 'false', 'level' => 99]);

        $this->assertFalse($user->refresh()->isAdmin());
    }

    public function test_profile_picture_can_be_uploaded_and_removed(): void
    {
        Storage::fake('local');
        $user = $this->actingAsMember();
        $user->forceFill(['profile_picture_path' => null])->save();

        $this->patch(route('settings.update'), [
            'remove_image' => 'false',
            'profile_picture' => UploadedFile::fake()->image('avatar.png', 100, 100),
        ])->assertSessionHasNoErrors();

        $path = $user->refresh()->profile_picture_path;
        $this->assertNotNull($path);
        Storage::disk('local')->assertExists('public/profile_pictures/' . basename($path));

        $this->patch(route('settings.update'), ['remove_image' => 'true'])->assertSessionHasNoErrors();

        $this->assertNull($user->refresh()->profile_picture_path);
        Storage::disk('local')->assertMissing('public/profile_pictures/' . basename($path));
    }

    public function test_profile_picture_must_be_small(): void
    {
        $this->actingAsMember();

        $this->patch(route('settings.update'), [
            'remove_image' => 'false',
            'profile_picture' => UploadedFile::fake()->image('avatar.png', 500, 500),
        ])->assertSessionHasErrors('profile_picture');
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = $this->actingAsMember();
        $photo = Photo::factory()->create(['user_id' => $user->id]);

        $this->delete(route('settings.destroy'), ['password' => 'password'])
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        // The account is soft deleted (and pruned after a month), its photos are soft deleted with it
        $this->assertSoftDeleted($user);
        $this->assertSoftDeleted($photo);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = $this->actingAsMember();

        $this->from(route('settings.edit'))
            ->delete(route('settings.destroy'), ['password' => 'wrong-password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect(route('settings.edit'));

        $this->assertNotNull(User::find($user->id));
    }
}
