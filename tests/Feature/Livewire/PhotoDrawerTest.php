<?php

namespace Tests\Feature\Livewire;

use App\Enums\UserLevelEnum;
use App\Livewire\PhotoDrawer;
use App\Models\Album;
use App\Models\Comment;
use App\Models\Photo;
use App\Models\User;
use App\Notifications\CommentPosted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class PhotoDrawerTest extends TestCase
{
    use RefreshDatabase;

    public function test_details_show_the_photo_information_but_not_its_title(): void
    {
        $this->actingAsMember();
        $photo = Photo::factory()
            ->for($this->createUser(attributes: ['name' => 'Anne Le Goff']))
            ->for(Album::factory()->state(['title' => 'Croisière à Groix']))
            ->create([
                'title' => 'IMG_2034.jpg',
                'legend' => 'Arrivée à Port-Tudy',
                'created_at' => '2026-06-14 10:00:00',
                'taken_at' => '2026-06-13 18:05:00',
                'latitude' => 47.6417,
                'longitude' => -3.4483,
                'width' => 4032,
                'height' => 3024,
            ]);

        $component = Livewire::test(PhotoDrawer::class)->call('show', $photo->id);

        $component->assertSee([
            'Anne Le Goff',
            'Envoyée le dimanche 14 juin 2026',
            'Arrivée à Port-Tudy',
            'samedi 13 juin 2026 à 18 h 05',
            "47°\u{202F}38,50′\u{202F}N, 3°\u{202F}26,90′\u{202F}O",
            'Croisière à Groix',
            "4\u{202F}032 × 3\u{202F}024 pixels",
        ]);
        $component->assertDontSee('IMG_2034.jpg');
    }

    public function test_details_show_no_map_for_photos_without_location(): void
    {
        $this->actingAsMember();
        $photo = Photo::factory()->create(['latitude' => null, 'longitude' => null]);

        Livewire::test(PhotoDrawer::class)->call('show', $photo->id)->assertDontSee('Ouvrir la carte');
    }

    public function test_guest_level_users_cannot_open_the_drawer(): void
    {
        $this->actingAsLevel(UserLevelEnum::GUEST);
        $photo = Photo::factory()->create();

        Livewire::test(PhotoDrawer::class)->call('show', $photo->id)->assertForbidden();
    }

    public function test_the_shown_photo_cannot_be_changed_from_the_browser(): void
    {
        $this->actingAsMember();
        $photo = Photo::factory()->create();

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::test(PhotoDrawer::class)->set('photoId', $photo->id);
    }

    public function test_owners_can_edit_the_legend_of_their_photos(): void
    {
        $owner = $this->actingAsMember();
        $photo = Photo::factory()->for($owner)->create(['legend' => 'Avant']);

        Livewire::test(PhotoDrawer::class)
            ->call('show', $photo->id)
            ->assertSee('Modifier la légende')
            ->call('editLegend')
            ->assertSet('legend', 'Avant')
            ->set('legend', '  Au mouillage à Houat  ')
            ->call('saveLegend')
            ->assertHasNoErrors()
            ->assertSet('isEditingLegend', false)
            ->assertSee('Au mouillage à Houat');

        $this->assertSame('Au mouillage à Houat', $photo->refresh()->legend);
    }

    public function test_admins_can_clear_the_legend_of_any_photo(): void
    {
        $this->actingAsAdmin();
        $photo = Photo::factory()->for(User::factory())->create(['legend' => 'Avant']);

        Livewire::test(PhotoDrawer::class)
            ->call('show', $photo->id)
            ->call('editLegend')
            ->set('legend', '')
            ->call('saveLegend')
            ->assertHasNoErrors()
            ->assertSee('Ajouter une légende');

        $this->assertNull($photo->refresh()->legend);
    }

    public function test_members_cannot_edit_the_legend_of_photos_of_others(): void
    {
        $this->actingAsMember();
        $photo = Photo::factory()->for(User::factory())->create(['legend' => 'Original']);

        Livewire::test(PhotoDrawer::class)->call('show', $photo->id)->assertDontSee('Modifier la légende');
        Livewire::test(PhotoDrawer::class)->call('show', $photo->id)->call('editLegend')->assertForbidden();
        Livewire::test(PhotoDrawer::class)->call('show', $photo->id)->set('legend', 'Piraté')->call('saveLegend')->assertForbidden();

        $this->assertSame('Original', $photo->refresh()->legend);
    }

    public function test_legends_are_limited_to_2048_characters(): void
    {
        $owner = $this->actingAsMember();
        $photo = Photo::factory()->for($owner)->create(['legend' => 'Avant']);

        Livewire::test(PhotoDrawer::class)
            ->call('show', $photo->id)
            ->call('editLegend')
            ->set('legend', str_repeat('a', 2049))
            ->call('saveLegend')
            ->assertHasErrors('legend')
            ->assertSee('La légende ne peut pas dépasser 2 048 caractères.');

        $this->assertSame('Avant', $photo->refresh()->legend);
    }

    public function test_members_can_comment_and_subscribers_are_notified(): void
    {
        Notification::fake();
        $author = $this->actingAsMember();
        $owner = $this->createUser();
        $photo = Photo::factory()->for($owner)->for(Album::factory()->state(['title' => 'Croisière à Groix']))->create();
        // PhotoFactory abonne le propriétaire ; l'auteur est abonné aussi, mais n'est pas prévenu de son propre commentaire
        $photo->subscriptions()->create(['user_id' => $author->id]);

        Livewire::test(PhotoDrawer::class)
            ->call('show', $photo->id)
            ->set('comment', 'Superbe lumière !')
            ->call('postComment')
            ->assertHasNoErrors()
            ->assertSet('comment', '')
            ->assertSee('Superbe lumière !')
            ->assertDispatched('photo-comments-count', photoId: $photo->id, count: 1);

        $comment = Comment::sole();
        $this->assertTrue($comment->user->is($author));
        $this->assertTrue($comment->commentable->is($photo));
        Notification::assertSentTo(
            $owner,
            CommentPosted::class,
            fn (CommentPosted $notification): bool => str_contains(
                $notification->toMail($owner)->introLines[0],
                'une photo de l\'album « Croisière à Groix »',
            ),
        );
        Notification::assertNotSentTo($author, CommentPosted::class);
    }

    public function test_empty_comments_are_rejected(): void
    {
        $this->actingAsMember();
        $photo = Photo::factory()->create();

        Livewire::test(PhotoDrawer::class)
            ->call('show', $photo->id)
            ->set('comment', '   ')
            ->call('postComment')
            ->assertHasErrors('comment')
            ->assertSee('Écrivez votre commentaire avant de le publier.');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_authors_can_delete_their_comments_after_confirming(): void
    {
        $author = $this->actingAsMember();
        $photo = Photo::factory()->create();
        $comment = Comment::factory()->for($photo, 'commentable')->create(['user_id' => $author->id]);

        Livewire::test(PhotoDrawer::class)
            ->call('show', $photo->id)
            ->call('confirmCommentDeletion', $comment->id)
            ->assertSee('Supprimer ce commentaire ?')
            ->call('deleteComment', $comment->id)
            ->assertSet('commentIdToDelete', null)
            ->assertDispatched('photo-comments-count', photoId: $photo->id, count: 0);

        $this->assertSoftDeleted($comment);
    }

    public function test_admins_can_delete_any_comment(): void
    {
        $this->actingAsAdmin();
        $photo = Photo::factory()->create();
        $comment = Comment::factory()->for($photo, 'commentable')->create(['user_id' => $this->createUser()->id]);

        Livewire::test(PhotoDrawer::class)->call('show', $photo->id)->call('deleteComment', $comment->id);

        $this->assertSoftDeleted($comment);
    }

    public function test_members_cannot_delete_comments_of_others(): void
    {
        $this->actingAsMember();
        $photo = Photo::factory()->create();
        $comment = Comment::factory()->for($photo, 'commentable')->create(['user_id' => $this->createUser()->id]);

        Livewire::test(PhotoDrawer::class)->call('show', $photo->id)->call('confirmCommentDeletion', $comment->id)->assertForbidden();
        Livewire::test(PhotoDrawer::class)->call('show', $photo->id)->call('deleteComment', $comment->id)->assertForbidden();

        $this->assertNotSoftDeleted($comment);
    }

    public function test_only_comments_of_the_shown_photo_can_be_deleted(): void
    {
        $author = $this->actingAsMember();
        $photo = Photo::factory()->create();
        $commentOfAnotherPhoto = Comment::factory()->for(Photo::factory(), 'commentable')->create(['user_id' => $author->id]);

        Livewire::test(PhotoDrawer::class)->call('show', $photo->id)->call('deleteComment', $commentOfAnotherPhoto->id)->assertNotFound();

        $this->assertNotSoftDeleted($commentOfAnotherPhoto);
    }

    public function test_members_can_follow_and_unfollow_the_comments_of_a_photo(): void
    {
        $user = $this->actingAsMember();
        $photo = Photo::factory()->for(User::factory())->create();

        $component = Livewire::test(PhotoDrawer::class)->call('show', $photo->id)->assertSet('isSubscribed', false);

        $component->set('isSubscribed', true);
        $this->assertTrue($photo->subscribers()->whereKey($user->id)->exists());

        $component->set('isSubscribed', false);
        $this->assertFalse($photo->subscribers()->whereKey($user->id)->exists());
    }
}
