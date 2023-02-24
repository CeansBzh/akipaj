<?php

namespace Database\Seeders;

use App\Enums\UserLevelEnum;
use App\Models\Role;
use App\Models\Trip;
use App\Models\User;
use App\Models\Album;
use App\Models\Event;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Photo;
use Illuminate\Database\Seeder;

class LocalEnvironmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->createDefaultUser();

        // Users
        $this->createUsers();
        $this->createUsersWithEmailUnverified();
        $this->createUsersWithoutPersonalInformation();

        // Articles
        $this->createDraftArticles();
        $this->createPublishedArticles();

        // Albums and photos
        $this->createAlbumsWithoutPhotos();
        $this->createAlbumsWithPhotos();
        $this->createAlbumsWithCommentedPhotos();

        // Trips
        $this->createTrips();
        $this->createTripsWithAllRelations();

        // Events
        $this->createEvents();
        $this->createPassedEvents();
        $this->createEventsWithoutImageOrLocation();
    }

    protected function createDefaultUser(): self
    {
        // The admin user
        User::factory()
            ->withLevel(UserLevelEnum::ADMINISTRATOR)
            ->create([
                'name' => 'Michel',
                'email' => 'michel@test.com',
            ]);

        // A member user
        User::factory()
            ->withLevel(UserLevelEnum::MEMBER)
            ->create([
                'name' => 'Henry',
                'email' => 'henry@test.com',
            ]);

        // A guest user
        User::factory()
            ->noProfilePicture()
            ->create([
                'name' => 'Lucien',
                'email' => 'lucien@test.com',
            ]);

        return $this;
    }

    protected function createUsers(): self
    {
        User::factory(8)
            ->withLevel(UserLevelEnum::MEMBER)
            ->create();

        User::factory(2)
            ->withLevel(UserLevelEnum::GUEST)
            ->create();

        return $this;
    }

    protected function createUsersWithEmailUnverified(): self
    {
        User::factory(2)
            ->withLevel(UserLevelEnum::MEMBER)
            ->unverified()
            ->create();

        User::factory(3)
            ->withLevel(UserLevelEnum::GUEST)
            ->unverified()
            ->create();

        return $this;
    }

    protected function createUsersWithoutPersonalInformation(): self
    {
        User::factory(3)
            ->withLevel(UserLevelEnum::MEMBER)
            ->noPersonalInformation()
            ->create();

        User::factory(2)
            ->withLevel(UserLevelEnum::GUEST)
            ->noPersonalInformation()
            ->create();

        return $this;
    }

    protected function createPublishedArticles(): self
    {
        Article::factory(4)->create();

        return $this;
    }

    protected function createDraftArticles(): self
    {
        Article::factory(2)
            ->draft()
            ->create();

        return $this;
    }

    protected function createAlbumsWithoutPhotos(): self
    {
        Album::factory(2)->create();

        return $this;
    }

    protected function createAlbumsWithPhotos(): self
    {
        Album::factory(4)
            ->withPhotos(4)
            ->create();

        return $this;
    }

    protected function createAlbumsWithCommentedPhotos(): self
    {
        Album::factory(2)
            ->has(Photo::factory(3)->has(Comment::factory()->count(3)))
            ->create();

        return $this;
    }

    protected function createTrips(): self
    {
        Trip::factory(5)->create();

        return $this;
    }

    protected function createTripsWithAllRelations(): self
    {
        Trip::factory(5)
            ->withBoats(2)
            ->withAlbums(2)
            ->withUsers(3)
            ->create();

        return $this;
    }

    protected function createEvents(): self
    {
        Event::factory(2)->create();

        return $this;
    }

    protected function createPassedEvents(): self
    {
        Event::factory(2)
            ->passed()
            ->create();

        return $this;
    }

    protected function createEventsWithoutImageOrLocation(): self
    {
        Event::factory(2)
            ->noImage()
            ->noLocation()
            ->create();

        return $this;
    }
}
