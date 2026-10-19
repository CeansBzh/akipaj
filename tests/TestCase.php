<?php

namespace Tests;

use App\Enums\UserLevelEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\File;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Temporary directory used as the public path during the test.
     */
    protected string $publicPath;

    protected function setUp(): void
    {
        parent::setUp();

        // The views use @vite, which requires a built manifest
        $this->withoutVite();

        // PhotoController and PhotoFactory write directly in public/storage/photos:
        // point the public path to a temporary directory so the tests don't touch the real one.
        $this->publicPath = sys_get_temp_dir() . '/akipaj-public-' . uniqid();
        File::ensureDirectoryExists($this->publicPath . '/storage/photos/thumbs');
        $this->app->instance('path.public', $this->publicPath);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->publicPath);

        parent::tearDown();
    }

    /**
     * Create a user with the given level.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function createUser(UserLevelEnum $level = UserLevelEnum::MEMBER, array $attributes = []): User
    {
        return User::factory()->withLevel($level)->create($attributes);
    }

    /**
     * Create a user with the given level and authenticate as this user.
     *
     * @param  array<string, mixed>  $attributes
     */
    protected function actingAsLevel(UserLevelEnum $level, array $attributes = []): User
    {
        $user = $this->createUser($level, $attributes);
        $this->actingAs($user);

        return $user;
    }

    protected function actingAsMember(): User
    {
        return $this->actingAsLevel(UserLevelEnum::MEMBER);
    }

    protected function actingAsAdmin(): User
    {
        return $this->actingAsLevel(UserLevelEnum::ADMINISTRATOR);
    }
}
