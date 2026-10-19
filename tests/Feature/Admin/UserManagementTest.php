<?php

namespace Tests\Feature\Admin;

use App\Enums\UserLevelEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_is_displayed(): void
    {
        $this->actingAsAdmin();

        $this->get(route('admin.index'))->assertOk()->assertViewIs('admin.index');
    }

    public function test_users_index_is_displayed(): void
    {
        $this->actingAsAdmin();

        $this->get(route('admin.users.index'))->assertOk()->assertViewIs('admin.user.index');
    }

    public function test_user_edit_form_is_displayed(): void
    {
        $this->actingAsAdmin();
        $user = $this->createUser();

        $response = $this->get(route('admin.users.edit', $user))->assertOk()->assertViewIs('admin.user.edit');

        $this->assertTrue($response->viewData('user')->is($user));
    }

    public function test_admin_can_update_a_user(): void
    {
        $this->actingAsAdmin();
        $user = $this->createUser();

        $this->patch(route('admin.users.update', $user), [
            'name' => 'Nouveau nom',
            'email' => 'nouveau@example.com',
            'remove_image' => 'false',
            'mobile_phone' => '06 00 00 00 00',
        ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.users.edit', $user));

        $user->refresh();
        $this->assertSame('Nouveau nom', $user->name);
        $this->assertSame('nouveau@example.com', $user->email);
        $this->assertSame('0600000000', $user->mobile_phone);
        $this->assertNull($user->email_verified_at);
    }

    public function test_admin_can_replace_and_remove_a_profile_picture(): void
    {
        Storage::fake('local');
        $this->actingAsAdmin();
        $user = $this->createUser();
        Storage::disk('local')->put('public/profile_pictures/old.png', 'image');
        $user->forceFill(['profile_picture_path' => '/storage/profile_pictures/old.png'])->save();

        $this->patch(route('admin.users.update', $user), [
            'remove_image' => 'false',
            'profile_picture' => UploadedFile::fake()->image('avatar.png', 100, 100),
        ])->assertSessionHasNoErrors();

        $newPath = (string) $user->refresh()->profile_picture_path;
        Storage::disk('local')->assertMissing('public/profile_pictures/old.png');
        Storage::disk('local')->assertExists('public/profile_pictures/'.basename($newPath));

        $this->patch(route('admin.users.update', $user), ['remove_image' => 'true'])->assertSessionHasNoErrors();

        $this->assertNull($user->refresh()->profile_picture_path);
    }

    public function test_admin_cannot_reuse_another_users_email(): void
    {
        $this->actingAsAdmin();
        $user = $this->createUser();
        $other = $this->createUser();

        $this->patch(route('admin.users.update', $user), [
            'email' => $other->email,
            'remove_image' => 'false',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_can_delete_a_user(): void
    {
        $this->actingAsAdmin();
        $user = $this->createUser();

        $this->delete(route('admin.users.destroy', $user))->assertRedirect(route('admin.users.index'));

        $this->assertSoftDeleted($user);
    }

    public function test_members_cannot_manage_users(): void
    {
        $this->actingAsMember();
        $user = $this->createUser();

        $this->get(route('admin.users.edit', $user))->assertForbidden();
        $this->patch(route('admin.users.update', $user), ['remove_image' => 'false'])->assertForbidden();
        $this->delete(route('admin.users.destroy', $user))->assertForbidden();
        $this->assertNotSoftDeleted($user);
    }

    public function test_user_creation_routes_do_not_exist(): void
    {
        $this->actingAsAdmin();

        // Accounts are created through registration only
        $this->get('/admin/users/create')->assertStatus(405);
        $this->post('/admin/users')->assertStatus(405);
    }

    public function test_contributors_are_not_administrators(): void
    {
        $this->actingAsLevel(UserLevelEnum::CONTRIBUTOR);

        $this->get(route('admin.index'))->assertForbidden();
    }
}
