<?php

namespace Tests\Feature\Models;

use App\Enums\UserLevelEnum;
use App\Models\Album;
use App\Models\Comment;
use App\Models\Photo;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_level_helpers(): void
    {
        $guest = $this->createUser(UserLevelEnum::GUEST);
        $member = $this->createUser(UserLevelEnum::MEMBER);
        $contributor = $this->createUser(UserLevelEnum::CONTRIBUTOR);
        $admin = $this->createUser(UserLevelEnum::ADMINISTRATOR);

        $this->assertSame([true, false, false, false], [$guest->isGuest(), $member->isGuest(), $contributor->isGuest(), $admin->isGuest()]);
        $this->assertSame([false, true, true, true], [$guest->isMemberOrAbove(), $member->isMemberOrAbove(), $contributor->isMemberOrAbove(), $admin->isMemberOrAbove()]);
        $this->assertSame([false, false, false, true], [$guest->isAdmin(), $member->isAdmin(), $contributor->isAdmin(), $admin->isAdmin()]);
    }

    public function test_level_is_cast_to_the_enum_and_defaults_to_guest(): void
    {
        $user = User::create(['name' => 'Nouveau', 'email' => 'nouveau@example.com', 'password' => 'secret']);

        $this->assertSame(UserLevelEnum::GUEST, $user->fresh()?->level);
    }

    public function test_find_by_username(): void
    {
        $user = $this->createUser(attributes: ['name' => 'Henry']);

        $this->assertTrue(User::findByUsername('Henry')->is($user));

        $this->expectException(ModelNotFoundException::class);
        User::findByUsername('Inconnu');
    }

    public function test_password_and_remember_token_are_hidden_from_serialization(): void
    {
        $array = $this->createUser()->toArray();

        $this->assertArrayNotHasKey('password', $array);
        $this->assertArrayNotHasKey('remember_token', $array);
    }

    public function test_is_subscribed_to_checks_both_the_id_and_the_type_of_the_commentable(): void
    {
        $user = $this->createUser();
        $photo = Photo::factory()->for(Album::factory())->create(['user_id' => $this->createUser()->id]);
        // A commentable of another type (a user profile) with the same id as the photo
        $profileWithSameId = User::findOrFail($photo->id);

        $photo->subscriptions()->create(['user_id' => $user->id]);

        $this->assertTrue($user->isSubscribedTo($photo));
        $this->assertFalse($user->isSubscribedTo($profileWithSameId));
        // Models that can't be commented are never subscribed to
        $this->assertFalse($user->isSubscribedTo(Album::factory()->create()));
    }

    public function test_relations(): void
    {
        $user = $this->createUser();
        $photo = Photo::factory()->for(Album::factory())->create(['user_id' => $user->id]);
        $comment = Comment::factory()->for($photo, 'commentable')->create(['user_id' => $user->id]);
        $trip = Trip::factory()->create();
        $trip->users()->attach($user);

        $this->assertTrue($user->photos->contains($photo));
        $this->assertTrue($user->commentsSent->contains($comment));
        $this->assertTrue($user->trips->contains($trip));
        $this->assertTrue($user->threadSubscriptions->contains('subscribeable_id', $photo->id));
    }

    public function test_soft_deleting_a_user_cascades_to_photos_and_profile_comments(): void
    {
        $user = $this->createUser();
        $photo = Photo::factory()->for(Album::factory())->create(['user_id' => $user->id]);
        $profileComment = Comment::factory()->for($user, 'commentable')->create(['user_id' => $this->createUser()->id]);
        $sentComment = Comment::factory()->for($photo, 'commentable')->create(['user_id' => $user->id]);

        $user->delete();

        $this->assertSoftDeleted($user);
        $this->assertSoftDeleted($photo);
        $this->assertSoftDeleted($profileComment);
        // Cascades through the photo
        $this->assertSoftDeleted($sentComment);
    }

    public function test_users_deleted_for_more_than_a_month_are_pruned_with_their_data(): void
    {
        $pruned = $this->createUser();
        $photo = Photo::factory()->for(Album::factory())->create(['user_id' => $pruned->id]);
        $comment = Comment::factory()->for(Photo::factory()->for(Album::factory()), 'commentable')->create(['user_id' => $pruned->id]);
        $trip = Trip::factory()->create();
        $trip->users()->attach($pruned);
        $recentlyDeleted = $this->createUser();
        $active = $this->createUser();

        $this->travel(-2)->months();
        $pruned->delete();
        $this->travelBack();
        $recentlyDeleted->delete();

        $this->artisan('model:prune', ['--model' => [User::class]])->assertSuccessful();

        $this->assertModelMissing($pruned);
        $this->assertModelMissing($photo);
        $this->assertModelMissing($comment);
        $this->assertSame(0, $trip->users()->count());
        $this->assertSoftDeleted($recentlyDeleted);
        $this->assertNotSoftDeleted($active);
    }
}
