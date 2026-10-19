<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Photo;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AlbumTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_index_lists_empty_albums(): void
    {
        $this->actingAsMember();
        $empty = Album::factory()->create();
        $withPhotos = Album::factory()->create();
        Photo::factory()->for($withPhotos)->create();

        $emptyAlbums = $this->get(route('albums.index'))->assertOk()->viewData('emptyAlbums');

        // Albums with photos are listed by the AlbumList Livewire component
        $this->assertTrue($emptyAlbums->contains($empty));
        $this->assertFalse($emptyAlbums->contains($withPhotos));
    }

    public function test_show_displays_the_album_photos(): void
    {
        $this->actingAsMember();
        $album = Album::factory()->create();
        $photos = Photo::factory(2)->for($album)->create();
        Photo::factory()->for(Album::factory())->create();

        $response = $this->get(route('albums.show', $album))->assertOk()->assertViewIs('album.show');

        $this->assertEqualsCanonicalizing($photos->pluck('id')->all(), $response->viewData('photoIds'));
    }

    public function test_members_can_create_an_album_linked_to_trips(): void
    {
        $this->actingAsMember();
        $trips = Trip::factory(2)->create();
        $this->get(route('albums.create'))->assertOk();

        $response = $this->post(route('albums.store'), [
            'title' => 'Croisière 2023',
            'description' => 'Une belle croisière',
            'month' => 7,
            'year' => 2023,
            'trips' => $trips->pluck('id')->all(),
            'image' => UploadedFile::fake()->image('cover.jpg'),
        ])->assertSessionHasNoErrors();

        $album = Album::where('title', 'Croisière 2023')->firstOrFail();
        $response->assertRedirect(route('albums.show', $album));
        $this->assertSame('2023-07-01', $album->date->format('Y-m-d'));
        $this->assertEqualsCanonicalizing($trips->pluck('id')->all(), $album->trips()->pluck('trips.id')->all());
        Storage::disk('local')->assertExists('public/albums/'.basename((string) $album->imagePath));
    }

    public function test_album_creation_is_validated(): void
    {
        $this->actingAsMember();
        Album::factory()->create(['title' => 'Doublon']);

        $this->post(route('albums.store'), [
            'title' => 'Doublon',
            'month' => 13,
            'year' => 1800,
            'trips' => [999],
        ])->assertSessionHasErrors(['title', 'description', 'month', 'year', 'trips.0']);
    }

    public function test_members_can_update_an_album(): void
    {
        $this->actingAsMember();
        $album = Album::factory()->create(['imagePath' => null]);
        $oldTrip = Trip::factory()->create();
        $newTrip = Trip::factory()->create();
        $album->trips()->attach($oldTrip);
        $this->get(route('albums.edit', $album))->assertOk();

        $this->put(route('albums.update', $album), [
            'title' => 'Nouveau titre',
            'description' => 'Nouvelle description',
            'month' => 2,
            'year' => 2020,
            'trips' => [$newTrip->id],
        ])->assertSessionHasNoErrors()->assertRedirect(route('albums.show', $album));

        $album->refresh();
        $this->assertSame('Nouveau titre', $album->title);
        $this->assertSame('2020-02-01', $album->date->format('Y-m-d'));
        $this->assertSame([$newTrip->id], $album->trips()->pluck('trips.id')->all());
    }

    public function test_album_cover_can_be_replaced_and_removed(): void
    {
        $this->actingAsMember();
        Storage::disk('local')->put('public/albums/old.jpg', 'image');
        $album = Album::factory()->create(['imagePath' => '/storage/albums/old.jpg']);
        $data = ['title' => $album->title, 'description' => 'Description', 'month' => 1, 'year' => 2020];

        $this->put(route('albums.update', $album), $data + ['image' => UploadedFile::fake()->image('new.jpg')])
            ->assertSessionHasNoErrors();

        $newPath = (string) $album->refresh()->imagePath;
        Storage::disk('local')->assertMissing('public/albums/old.jpg');
        Storage::disk('local')->assertExists('public/albums/'.basename($newPath));

        $this->put(route('albums.update', $album), $data + ['remove_image' => '1'])->assertSessionHasNoErrors();

        $this->assertNull($album->refresh()->imagePath);
        Storage::disk('local')->assertMissing('public/albums/'.basename($newPath));
    }

    public function test_members_can_delete_an_album_containing_only_their_photos(): void
    {
        $user = $this->actingAsMember();
        $album = Album::factory()->create();
        $photo = Photo::factory()->for($album)->create(['user_id' => $user->id]);

        $this->delete(route('albums.destroy', $album))->assertRedirect(route('albums.index'));

        $this->assertModelMissing($album);
        $this->assertSoftDeleted($photo);
    }

    public function test_albums_containing_photos_of_other_users_cannot_be_deleted(): void
    {
        $this->actingAsMember();
        $album = Album::factory()->create();
        Photo::factory()->for($album)->create(['user_id' => $this->createUser()->id]);

        $this->delete(route('albums.destroy', $album))->assertForbidden();

        $this->assertModelExists($album);
    }

    public function test_admins_can_delete_any_empty_album(): void
    {
        $this->actingAsAdmin();
        $album = Album::factory()->create();

        $this->delete(route('albums.destroy', $album))->assertRedirect(route('albums.index'));

        $this->assertModelMissing($album);
    }
}
