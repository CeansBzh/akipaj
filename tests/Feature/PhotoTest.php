<?php

namespace Tests\Feature;

use App\Enums\UserLevelEnum;
use App\Models\Album;
use App\Models\Comment;
use App\Models\Photo;
use App\Models\ThreadSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class PhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_gallery_page_is_displayed(): void
    {
        $this->actingAsMember();
        Photo::factory()->create();

        // @phpstan-ignore method.notFound (macro registered by Livewire)
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

        Storage::disk('photos')->assertExists([basename($landscape->path), 'thumbs/'.basename($landscape->thumb_path)]);
        $this->get($landscape->path)->assertOk();
        $this->get($landscape->thumb_path)->assertOk();

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

    public function test_upload_form_sends_photos_in_batches(): void
    {
        $this->actingAsMember();
        $album = Album::factory()->create();

        foreach (['premier.jpg', 'second.jpg'] as $fileName) {
            $this->postJson(route('photos.store'), [
                'album' => $album->id,
                'files' => [UploadedFile::fake()->image($fileName, 800, 600)],
            ])->assertOk()->assertExactJson(['redirect' => route('albums.show', $album)]);
        }

        $this->assertSame(2, $album->photos()->count());
        // The success alert survives the batch requests until the form redirects to the album
        $this->get(route('albums.show', $album))->assertSee('Photos ajoutées avec succès !');
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

    public function test_members_can_see_the_files_of_a_photo(): void
    {
        $this->actingAsMember();
        $photo = Photo::factory()->create();
        $fileName = basename($photo->path);

        // URLs stored for the photos uploaded when the files were in the public storage
        $photoResponse = $this->get('/storage/photos/'.$fileName);
        $thumbResponse = $this->get('/storage/photos/thumbs/thumb_'.$fileName);

        $photoResponse->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $this->assertSame(Storage::disk('photos')->get($fileName), $photoResponse->streamedContent());
        $this->assertTrue($photoResponse->headers->hasCacheControlDirective('private'));

        $thumbResponse->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        $this->assertSame(Storage::disk('photos')->get('thumbs/thumb_'.$fileName), $thumbResponse->streamedContent());
    }

    public function test_visitors_are_redirected_to_login_from_the_files_of_a_photo(): void
    {
        $photo = Photo::factory()->create();

        $this->get($photo->path)->assertRedirect(route('login'));
        $this->get($photo->thumb_path)->assertRedirect(route('login'));
    }

    public function test_guest_level_users_are_redirected_to_their_profile_from_the_files_of_a_photo(): void
    {
        $user = $this->actingAsLevel(UserLevelEnum::GUEST);
        $photo = Photo::factory()->create();

        $this->get($photo->path)->assertRedirect(route('profile.show', $user->name));
    }

    #[TestWith(['missing.jpg'])]
    #[TestWith(['thumbs'])]
    #[TestWith(['../../../.env'])]
    #[TestWith(['thumbs/../../.env'])]
    public function test_only_existing_photo_files_are_served(string $path): void
    {
        $this->actingAsMember();

        $this->get('/storage/photos/'.$path)->assertNotFound();
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
