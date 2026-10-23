<?php

namespace Tests\Feature\Models;

use App\Models\Album;
use App\Models\Article;
use App\Models\Boat;
use App\Models\Comment;
use App\Models\Photo;
use App\Models\ThreadSubscription;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_album_relations(): void
    {
        $album = Album::factory()->create();
        $first = Photo::factory()->for($album)->create();
        Photo::factory()->for($album)->create();
        $trip = Trip::factory()->create();
        $album->trips()->attach($trip);

        $this->assertCount(2, $album->photos);
        $this->assertTrue($album->oldestPhoto?->is($first));
        $this->assertTrue($album->trips->contains($trip));
        $this->assertTrue($trip->albums->contains($album));
    }

    public function test_trip_relations(): void
    {
        $trip = Trip::factory()->withBoats(2)->withUsers(3)->create();

        $this->assertCount(2, $trip->boats);
        $this->assertCount(3, $trip->users);
        $this->assertTrue($trip->boats->first()?->trip?->is($trip));
    }

    public function test_photo_and_comment_relations(): void
    {
        $photo = Photo::factory()->for(Album::factory())->create();
        $comment = Comment::factory()->for($photo, 'commentable')->create();

        $this->assertTrue($photo->comments->contains($comment));
        $this->assertTrue($comment->commentable->is($photo));
        $this->assertNotNull($comment->user);
        $this->assertNotNull($photo->album);
        $this->assertNotNull($photo->user);
    }

    public function test_commentable_subscriptions_and_subscribers(): void
    {
        $owner = $this->createUser();
        $photo = Photo::factory()->for(Album::factory())->create(['user_id' => $owner->id]);
        // A subscription to another kind of commentable sharing the same id must not be mixed up
        $profile = $this->createUser();
        $profile->subscriptions()->create(['user_id' => $this->createUser()->id]);

        // PhotoFactory subscribes the owner to the photo comments
        $subscription = $photo->subscriptions()->firstOrFail();
        $this->assertInstanceOf(ThreadSubscription::class, $subscription);
        $this->assertTrue($subscription->subscribeable->is($photo));
        $this->assertTrue($subscription->user->is($owner));
        $this->assertSame([$owner->id], $photo->subscribers->pluck('id')->all());
    }

    public function test_boat_belongs_to_a_trip(): void
    {
        $trip = Trip::factory()->create();
        $boat = Boat::factory()->for($trip)->create();

        $this->assertTrue($boat->trip->is($trip));
    }

    public function test_articles_use_their_slug_as_route_key(): void
    {
        $article = Article::factory()->create(['slug' => 'un-slug']);

        $this->assertSame('un-slug', $article->getRouteKey());
    }
}
