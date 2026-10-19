<?php

namespace Tests\Feature\Livewire;

use App\Enums\UserLevelEnum;
use App\Livewire\Comments;
use App\Models\Comment;
use App\Models\Payment;
use App\Models\Photo;
use App\Notifications\CommentPosted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Livewire\Features\SupportTesting\Testable;
use Tests\TestCase;

class CommentsTest extends TestCase
{
    use RefreshDatabase;

    protected function mountFor(Photo $photo): Testable
    {
        return Livewire::test(Comments::class, ['commentable' => $photo, 'comments' => $photo->comments]);
    }

    public function test_members_can_post_a_comment_and_subscribers_are_notified(): void
    {
        Notification::fake();
        $author = $this->actingAsMember();
        $owner = $this->createUser();
        $photo = Photo::factory()->create(['user_id' => $owner->id]);
        // PhotoFactory subscribes the owner; the author is subscribed too and must not be notified of their own comment
        $photo->subscriptions()->create(['user_id' => $author->id]);

        $this->mountFor($photo)
            ->set('content', 'Superbe photo !')
            ->call('store')
            ->assertHasNoErrors()
            ->assertSet('content', '')
            ->assertSee('Superbe photo !');

        $comment = Comment::firstOrFail();
        $this->assertTrue($comment->user->is($author));
        $this->assertTrue($comment->commentable->is($photo));
        Notification::assertSentTo($owner, CommentPosted::class);
        Notification::assertNotSentTo($author, CommentPosted::class);
    }

    public function test_empty_comments_are_rejected(): void
    {
        $this->actingAsMember();
        $photo = Photo::factory()->create();

        $this->mountFor($photo)->set('content', '')->call('store')->assertHasErrors('content');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_guest_level_users_cannot_comment(): void
    {
        $this->actingAsLevel(UserLevelEnum::GUEST);
        $photo = Photo::factory()->create();

        $this->mountFor($photo)->set('content', 'Spam')->call('store')->assertForbidden();
    }

    public function test_authors_can_edit_their_comments(): void
    {
        $author = $this->actingAsMember();
        $photo = Photo::factory()->create();
        $comment = Comment::factory()->for($photo, 'commentable')->create(['user_id' => $author->id, 'content' => 'Avant']);

        $this->mountFor($photo)
            ->call('setCommentToUpdate', $comment->id)
            ->assertSet('commentIdToUpdate', $comment->id)
            ->assertSet('newContent', 'Avant')
            ->set('newContent', 'Après')
            ->call('update', $comment->id)
            ->assertSet('commentIdToUpdate', null);

        $this->assertSame('Après', $comment->refresh()->content);
    }

    public function test_edit_and_delete_modes_can_be_toggled_off(): void
    {
        $author = $this->actingAsMember();
        $photo = Photo::factory()->create();
        $comment = Comment::factory()->for($photo, 'commentable')->create(['user_id' => $author->id]);

        $this->mountFor($photo)
            ->call('setCommentToUpdate', $comment->id)
            ->call('setCommentToUpdate', $comment->id)
            ->assertSet('commentIdToUpdate', null)
            ->assertSet('newContent', '')
            ->call('setCommentToDestroy', $comment->id)
            ->call('setCommentToDestroy', null)
            ->assertSet('commentIdToDestroy', null);
    }

    public function test_users_cannot_edit_comments_of_others_even_as_admin(): void
    {
        $this->actingAsAdmin();
        $photo = Photo::factory()->create();
        $comment = Comment::factory()->for($photo, 'commentable')->create(['user_id' => $this->createUser()->id]);

        $this->mountFor($photo)->call('setCommentToUpdate', $comment->id)->assertForbidden();
        $this->mountFor($photo)->set('newContent', 'Modifié')->call('update', $comment->id)->assertForbidden();
    }

    public function test_authors_can_delete_their_comments(): void
    {
        $author = $this->actingAsMember();
        $photo = Photo::factory()->create();
        $comment = Comment::factory()->for($photo, 'commentable')->create(['user_id' => $author->id]);

        $this->mountFor($photo)
            ->call('setCommentToDestroy', $comment->id)
            ->assertSet('commentIdToDestroy', $comment->id)
            ->call('destroy', $comment->id);

        $this->assertSoftDeleted($comment);
    }

    public function test_admins_can_delete_any_comment(): void
    {
        $this->actingAsAdmin();
        $photo = Photo::factory()->create();
        $comment = Comment::factory()->for($photo, 'commentable')->create(['user_id' => $this->createUser()->id]);

        $this->mountFor($photo)->call('destroy', $comment->id);

        $this->assertSoftDeleted($comment);
    }

    public function test_members_cannot_delete_comments_of_others(): void
    {
        $this->actingAsMember();
        $photo = Photo::factory()->create();
        $comment = Comment::factory()->for($photo, 'commentable')->create(['user_id' => $this->createUser()->id]);

        $this->mountFor($photo)->call('destroy', $comment->id)->assertForbidden();

        $this->assertNotSoftDeleted($comment);
    }

    public function test_comments_can_be_refreshed_for_a_commentable(): void
    {
        $this->actingAsMember();
        $photo = Photo::factory()->create();
        $other = Photo::factory()->create();
        Comment::factory()->for($other, 'commentable')->create(['content' => 'Sur une autre photo']);

        $this->mountFor($photo)
            ->dispatch('commentsNeedUpdate', commentableId: $other->id, commentableType: Photo::class)
            ->assertSee('Sur une autre photo');
    }

    public function test_refresh_rejects_classes_that_are_not_commentable(): void
    {
        $this->actingAsMember();
        $photo = Photo::factory()->create();
        $payment = Payment::factory()->create();

        $this->mountFor($photo)->dispatch('commentsNeedUpdate', commentableId: $payment->id, commentableType: Payment::class)->assertStatus(400);
        $this->mountFor($photo)->dispatch('commentsNeedUpdate', commentableId: 1, commentableType: 'NotAClass')->assertStatus(400);
    }
}
