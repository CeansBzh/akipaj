<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Comment;
use App\Models\Photo;
use App\Models\ThreadSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_page_is_displayed(): void
    {
        $this->actingAsMember();
        Photo::factory()->create();

        $this->get(route('photos.index'))->assertOk()->assertViewIs('photo.index')->assertSeeLivewire('gallery');
    }

    public function test_upload_form_is_displayed_for_an_album(): void
    {
        $this->actingAsMember();
        $album = Album::factory()->create();

        $this->get(route('photos.create', $album))->assertOk()->assertViewHas('album', $album);
    }

    public function test_members_can_upload_photos(): void
    {
        $user = $this->actingAsMember();
        $album = Album::factory()->create();

        $this->post(route('photos.store'), [
            'album' => $album->id,
            'files' => [
                UploadedFile::fake()->image('paysage.jpg', 1200, 900),
                UploadedFile::fake()->image('portrait.png', 300, 600),
            ],
        ])->assertSessionHasNoErrors()->assertRedirect(route('albums.show', $album));

        $photos = Photo::orderBy('id')->get();
        $this->assertSame(['paysage.jpg', 'portrait.png'], $photos->pluck('title')->all());

        $landscape = $photos[0];
        $this->assertTrue($landscape->album->is($album));
        $this->assertTrue($landscape->user->is($user));
        $this->assertSame([1200, 900], [$landscape->width, $landscape->height]);
        // Thumbnails fit in a 450px square while keeping the aspect ratio
        $this->assertSame([450, 337], [$landscape->thumb_width, $landscape->thumb_height]);
        $this->assertSame([225, 450], [$photos[1]->thumb_width, $photos[1]->thumb_height]);

        $this->assertFileExists($this->publicPath . '/storage/photos/' . basename($landscape->path));
        $this->assertFileExists($this->publicPath . '/storage/photos/thumbs/' . basename($landscape->thumb_path));

        // The uploader is subscribed to the comments of each photo
        $this->assertTrue($user->isSubscribedTo($landscape));
        $this->assertSame(2, ThreadSubscription::where('user_id', $user->id)->count());
    }

    public function test_upload_is_validated(): void
    {
        $this->actingAsMember();

        $this->post(route('photos.store'), [
            'album' => 999,
            'files' => [UploadedFile::fake()->create('document.pdf', 10, 'application/pdf')],
        ])->assertSessionHasErrors(['album', 'files.0']);

        $this->post(route('photos.store'), ['album' => Album::factory()->create()->id])->assertSessionHasErrors('files');
    }

    public function test_members_can_see_a_photo_and_its_comments(): void
    {
        $this->actingAsMember();
        $photo = Photo::factory()->create();
        $comment = Comment::factory()->for($photo, 'commentable')->create();

        $this->get(route('photos.show', $photo))
            ->assertOk()
            ->assertSee($photo->title)
            ->assertSee($comment->content);
    }

    public function test_owners_can_update_their_photos(): void
    {
        $user = $this->actingAsMember();
        $photo = Photo::factory()->create(['user_id' => $user->id]);
        $this->get(route('photos.edit', $photo))->assertOk();

        $this->put(route('photos.update', $photo), [
            'title' => 'Coucher de soleil',
            'legend' => 'Au large de Groix',
            'taken_at' => '2022-08-15 20:30',
        ])->assertSessionHasNoErrors()->assertRedirect(route('photos.show', $photo));

        $photo->refresh();
        $this->assertSame('Coucher de soleil', $photo->title);
        $this->assertSame('Au large de Groix', $photo->legend);
        $this->assertSame('2022-08-15 20:30', $photo->taken_at?->format('Y-m-d H:i'));
    }

    public function test_photo_update_only_changes_editable_fields(): void
    {
        $user = $this->actingAsMember();
        $photo = Photo::factory()->create(['user_id' => $user->id]);
        $path = $photo->path;

        $this->put(route('photos.update', $photo), [
            'title' => 'Titre',
            'path' => '/storage/somewhere/else.jpg',
        ])->assertSessionHasNoErrors();

        $this->assertSame($path, $photo->refresh()->path);
    }

    public function test_members_cannot_modify_photos_of_other_users(): void
    {
        $this->actingAsMember();
        $photo = Photo::factory()->create(['user_id' => $this->createUser()->id]);

        $this->get(route('photos.edit', $photo))->assertForbidden();
        $this->put(route('photos.update', $photo), ['title' => 'Piraté'])->assertForbidden();
        $this->delete(route('photos.destroy', $photo))->assertForbidden();

        $this->assertNotSame('Piraté', $photo->refresh()->title);
        $this->assertNotSoftDeleted($photo);
    }

    public function test_admins_can_modify_any_photo(): void
    {
        $this->actingAsAdmin();
        $photo = Photo::factory()->create(['user_id' => $this->createUser()->id]);

        $this->put(route('photos.update', $photo), ['title' => 'Modéré'])->assertSessionHasNoErrors();
        $this->assertSame('Modéré', $photo->refresh()->title);

        $this->delete(route('photos.destroy', $photo))->assertRedirect(route('photos.index'));
        $this->assertSoftDeleted($photo);
    }

    public function test_deleting_a_photo_soft_deletes_its_comments(): void
    {
        $user = $this->actingAsMember();
        $photo = Photo::factory()->create(['user_id' => $user->id]);
        $comment = Comment::factory()->for($photo, 'commentable')->create();

        $this->delete(route('photos.destroy', $photo))->assertRedirect(route('photos.index'));

        $this->assertSoftDeleted($photo);
        $this->assertSoftDeleted($comment);
    }
}
