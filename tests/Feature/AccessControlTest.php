<?php

namespace Tests\Feature;

use App\Enums\UserLevelEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Checks the route-level protections: the "auth" middleware and the "level" middleware (EnsureHasLevel).
 */
class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pages reserved to members (level >= MEMBER).
     *
     * @return array<string, array{string}>
     */
    public static function memberPagesProvider(): array
    {
        return [
            'articles' => ['/articles'],
            'albums' => ['/albums'],
            'events' => ['/events'],
            'trips' => ['/trips'],
            'photos' => ['/photos'],
            'members list' => ['/matelots'],
        ];
    }

    /**
     * Pages reserved to administrators.
     *
     * @return array<string, array{string}>
     */
    public static function adminPagesProvider(): array
    {
        return [
            'dashboard' => ['/admin'],
            'users' => ['/admin/users'],
        ];
    }

    #[DataProvider('memberPagesProvider')]
    public function test_visitors_are_redirected_to_login_from_member_pages(string $url): void
    {
        $this->get($url)
            ->assertRedirect(route('login'))
            ->assertSessionHas('status');
    }

    #[DataProvider('memberPagesProvider')]
    public function test_guest_level_users_are_redirected_to_their_profile_from_member_pages(string $url): void
    {
        $user = $this->actingAsLevel(UserLevelEnum::GUEST);

        $this->get($url)->assertRedirect(route('profile.show', $user->name));
    }

    #[DataProvider('memberPagesProvider')]
    public function test_members_can_access_member_pages(string $url): void
    {
        $this->actingAsMember();

        $this->get($url)->assertOk();
    }

    #[DataProvider('memberPagesProvider')]
    public function test_contributors_can_access_member_pages(string $url): void
    {
        $this->actingAsLevel(UserLevelEnum::CONTRIBUTOR);

        $this->get($url)->assertOk();
    }

    #[DataProvider('adminPagesProvider')]
    public function test_members_are_forbidden_from_admin_pages(string $url): void
    {
        $this->actingAsMember();

        $this->get($url)->assertForbidden();
    }

    #[DataProvider('adminPagesProvider')]
    public function test_administrators_can_access_admin_pages(string $url): void
    {
        $this->actingAsAdmin();

        $this->get($url)->assertOk();
    }

    public function test_settings_require_authentication(): void
    {
        $this->get(route('settings.edit'))->assertRedirect(route('login'));
        $this->patch(route('settings.update'))->assertRedirect(route('login'));
        $this->delete(route('settings.destroy'))->assertRedirect(route('login'));
    }
}
