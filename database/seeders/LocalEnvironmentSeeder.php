<?php

namespace Database\Seeders;

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
        $this->createDefaultRoles();
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

    protected function createDefaultRoles(): self
    {
        $roles = [
            'admin' => [
                'display_name' => 'Administrateur',
                'description' => 'Le rôle le plus élevé, est autorisé à tout faire.',
            ],
            'member' => [
                'display_name' => 'Membre',
                'description' => 'Est autorisé à consulter le site de manière complète (photos, commentaires, etc.).',
            ],
            'guest' => [
                'display_name' => 'Visiteur',
                'description' => 'Utilisateur n\'ayant pas un compte validé par l\'administrateur. Ne peut pas utiliser le site comme un membre.',
            ],
        ];

        foreach ($roles as $name => $attributes) {
            Role::create(array_merge($attributes, ['name' => $name]));
        }

        return $this;
    }

    protected function createDefaultUser(): self
    {
        $admin = User::factory()->create([
            'name' => 'Michel',
            'email' => 'michel@test.com',
        ]);
        $admin->attachRole('admin');
        $admin->attachRole('member');

        $member = User::factory()->create([
            'name' => 'Henry',
            'email' => 'henry@test.com',
        ]);
        $member->attachRole('member');

        return $this;
    }

    protected function createUsers(): self
    {
        User::factory(10)->create();

        return $this;
    }

    protected function createUsersWithEmailUnverified(): self
    {
        User::factory(5)->unverified()->create();

        return $this;
    }

    protected function createUsersWithoutPersonalInformation(): self
    {
        User::factory(5)->noPersonalInformation()->create();

        return $this;
    }

    protected function createPublishedArticles(): self
    {
        Article::factory(4)->create();

        return $this;
    }

    protected function createDraftArticles(): self
    {
        Article::factory(2)->draft()->create();

        return $this;
    }

    protected function createAlbumsWithoutPhotos(): self
    {
        Album::factory(2)->create();

        return $this;
    }

    protected function createAlbumsWithPhotos(): self
    {
        Album::factory(4)->withPhotos(4)->create();

        return $this;
    }

    protected function createAlbumsWithCommentedPhotos(): self
    {
        Album::factory(2)->has(
            Photo::factory(3)->has(
                Comment::factory()->count(3)
            )
        )->create();

        return $this;
    }

    protected function createTrips(): self
    {
        Trip::factory(5)->create();

        return $this;
    }

    protected function createTripsWithAllRelations(): self
    {
        Trip::factory(5)->withBoats(2)->withAlbums(2)->withUsers(3)->create();

        return $this;
    }

    protected function createEvents(): self
    {
        Event::factory(2)->create();

        return $this;
    }

    protected function createPassedEvents(): self
    {
        Event::factory(2)->passed()->create();

        return $this;
    }

    protected function createEventsWithoutImageOrLocation(): self
    {
        Event::factory(2)->noImage()->noLocation()->create();

        return $this;
    }
}
