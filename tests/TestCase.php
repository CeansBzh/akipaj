<?php

namespace Tests;

use App\Enums\UserLevelEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Storage;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The views use @vite, which requires a built manifest
        $this->withoutVite();

        // PhotoController and PhotoFactory write the photo files: keep the tests away from the real ones
        Storage::fake('photos');
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
