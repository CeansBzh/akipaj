<?php

namespace Tests\Feature\Account;

use App\Enums\UserLevelEnum;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_list_only_shows_validated_members(): void
    {
        $this->actingAsMember();
        $member = $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Henry']);
        $guest = $this->createUser(UserLevelEnum::GUEST, ['name' => 'Lucien']);

        $response = $this->get(route('profile.index'))->assertOk()->assertViewIs('profile.index');

        $users = $response->viewData('users');
        $this->assertTrue($users->contains($member));
        $this->assertFalse($users->contains($guest));
    }

    public function test_members_can_see_any_profile(): void
    {
        $this->actingAsMember();
        $other = $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Henry']);
        $trip = Trip::factory()->create(['start_date' => '2022-06-01', 'end_date' => '2022-06-03']);
        $trip->users()->attach($other);

        $response = $this->get(route('profile.show', 'Henry'))->assertOk()->assertViewIs('profile.show');

        $this->assertTrue($response->viewData('user')->is($other));
        $this->assertEquals([2022], $response->viewData('tripsByYear')->keys()->all());
    }

    public function test_guest_level_users_can_see_their_own_profile(): void
    {
        $user = $this->actingAsLevel(UserLevelEnum::GUEST, ['name' => 'Lucien']);

        $response = $this->get(route('profile.show', 'Lucien'))->assertOk();

        $this->assertTrue($response->viewData('user')->is($user));
    }

    public function test_guest_level_users_are_redirected_from_other_profiles(): void
    {
        $this->actingAsLevel(UserLevelEnum::GUEST, ['name' => 'Lucien']);
        $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Henry']);

        $this->get(route('profile.show', 'Henry'))->assertRedirect(route('profile.show', 'Lucien'));
    }

    public function test_profile_without_username_redirects_to_own_profile(): void
    {
        $this->actingAsMember()->forceFill(['name' => 'Henry'])->save();

        $this->get(route('profile.show'))->assertRedirect(route('profile.show', 'Henry'));
    }

    public function test_unknown_username_returns_404(): void
    {
        $this->actingAsMember();

        $this->get(route('profile.show', 'nobody'))->assertNotFound();
    }

    public function test_visitors_cannot_see_profiles(): void
    {
        $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Henry']);

        $this->get(route('profile.show', 'Henry'))->assertNotFound();
        $this->get(route('profile.show'))->assertNotFound();
    }
}
