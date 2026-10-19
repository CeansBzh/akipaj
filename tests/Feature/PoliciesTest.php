<?php

namespace Tests\Feature;

use App\Enums\UserLevelEnum;
use App\Models\Album;
use App\Models\Article;
use App\Models\Comment;
use App\Models\Event;
use App\Models\Photo;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Authorization matrix of the policies (auto-discovered from the model names).
 */
class PoliciesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<int, string>  $abilities
     * @param  array<string, bool>  $expected  Expected result for each level name
     */
    protected function assertAbilities(Model|string $subject, array $abilities, array $expected, ?User $owner = null): void
    {
        foreach ($expected as $levelName => $allowed) {
            $level = constant(UserLevelEnum::class.'::'.$levelName);
            $user = $owner && $levelName === 'OWNER' ? $owner : $this->createUser($level);
            foreach ($abilities as $ability) {
                $this->assertSame($allowed, $user->can($ability, $subject), "$levelName / $ability");
            }
        }
    }

    public function test_content_readable_by_members(): void
    {
        $subjects = [Album::factory()->create(), Event::factory()->create(), Trip::factory()->create(), Article::factory()->create()];

        foreach ($subjects as $subject) {
            $this->assertAbilities($subject::class, ['viewAny'], ['GUEST' => false, 'MEMBER' => true, 'CONTRIBUTOR' => true, 'ADMINISTRATOR' => true]);
            $this->assertAbilities($subject, ['view'], ['GUEST' => false, 'MEMBER' => true, 'CONTRIBUTOR' => true, 'ADMINISTRATOR' => true]);
        }
    }

    public function test_events_trips_and_articles_are_managed_by_admins_only(): void
    {
        foreach ([Event::factory()->create(), Trip::factory()->create(), Article::factory()->create()] as $subject) {
            $this->assertAbilities($subject::class, ['create'], ['MEMBER' => false, 'CONTRIBUTOR' => false, 'ADMINISTRATOR' => true]);
            $this->assertAbilities($subject, ['update', 'delete'], ['MEMBER' => false, 'CONTRIBUTOR' => false, 'ADMINISTRATOR' => true]);
            $this->assertAbilities($subject, ['restore', 'forceDelete'], ['ADMINISTRATOR' => false]);
        }
    }

    public function test_draft_articles_are_only_visible_to_admins(): void
    {
        $this->actingAsMember();
        $draft = Article::factory()->draft()->create();

        $this->assertAbilities($draft, ['view'], ['MEMBER' => false, 'CONTRIBUTOR' => false, 'ADMINISTRATOR' => true]);
    }

    public function test_albums_are_managed_by_members(): void
    {
        $album = Album::factory()->create();

        $this->assertAbilities(Album::class, ['create'], ['GUEST' => false, 'MEMBER' => true]);
        $this->assertAbilities($album, ['update', 'delete'], ['GUEST' => false, 'MEMBER' => true, 'ADMINISTRATOR' => true]);
        $this->assertAbilities($album, ['restore', 'forceDelete'], ['MEMBER' => false, 'ADMINISTRATOR' => false]);
    }

    public function test_albums_with_photos_of_others_cannot_be_deleted(): void
    {
        $owner = $this->createUser();
        $album = Album::factory()->create();
        Photo::factory()->for($album)->create(['user_id' => $owner->id]);

        $this->assertTrue($owner->can('delete', $album));
        $this->assertFalse($this->createUser()->can('delete', $album));
        $this->assertFalse($this->createUser(UserLevelEnum::ADMINISTRATOR)->can('delete', $album));
    }

    public function test_photos_are_managed_by_their_owner_or_admins(): void
    {
        $owner = $this->createUser();
        $photo = Photo::factory()->for(Album::factory())->create(['user_id' => $owner->id]);

        $this->assertAbilities(Photo::class, ['viewAny', 'create'], ['GUEST' => false, 'MEMBER' => true]);
        $this->assertAbilities($photo, ['view'], ['GUEST' => false, 'MEMBER' => true]);
        $this->assertAbilities($photo, ['update', 'delete'], ['MEMBER' => false, 'ADMINISTRATOR' => true]);
        $this->assertTrue($owner->can('update', $photo));
        $this->assertTrue($owner->can('delete', $photo));
        // Admins pass every check through PhotoPolicy::before()
        $this->assertAbilities($photo, ['restore', 'forceDelete'], ['MEMBER' => false, 'ADMINISTRATOR' => true]);
    }

    public function test_comments_are_managed_by_their_author_and_deleted_by_admins(): void
    {
        $author = $this->createUser();
        $comment = Comment::factory()->for(Photo::factory()->for(Album::factory()), 'commentable')->create(['user_id' => $author->id]);

        $this->assertAbilities(Comment::class, ['viewAny', 'create'], ['GUEST' => false, 'MEMBER' => true]);
        $this->assertAbilities($comment, ['view', 'update', 'delete'], ['MEMBER' => false]);
        // Admins can delete but not rewrite the comments of others
        $this->assertAbilities($comment, ['delete', 'view'], ['ADMINISTRATOR' => true]);
        $this->assertAbilities($comment, ['update'], ['ADMINISTRATOR' => false]);
        $this->assertAbilities($comment, ['restore', 'forceDelete'], ['MEMBER' => false, 'ADMINISTRATOR' => true]);
        foreach (['view', 'update', 'delete'] as $ability) {
            $this->assertTrue($author->can($ability, $comment));
        }
    }
}
