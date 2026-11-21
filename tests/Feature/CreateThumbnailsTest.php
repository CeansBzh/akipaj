<?php

namespace Tests\Feature;

use App\Models\Album;
use App\Models\Photo;
use App\Models\Trip;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateThumbnailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_jpeg_thumbnails_are_replaced_by_webp_ones_made_from_the_original_photo(): void
    {
        $disk = Storage::disk('photos');
        $disk->put('thumbs/thumb_legacy.jpg', 'old thumbnail');
        $photo = Photo::factory()->create([
            'thumb_path' => route('photos.file', 'thumbs/thumb_legacy.jpg', false),
            'thumb_width' => 1,
            'thumb_height' => 1,
        ]);
        [$width, $height] = getimagesize($disk->path($photo->fileName()));

        $this->artisan('photos:thumbnails')->assertSuccessful();

        $photo->refresh();
        $this->assertSame('/storage/photos/thumbs/thumb_legacy.webp', $photo->thumb_path);
        $this->assertSame(Photo::THUMB_SIZE, max($photo->thumb_width, $photo->thumb_height));
        $this->assertEqualsWithDelta($width / $height, $photo->thumb_width / $photo->thumb_height, 0.02);
        $this->assertSame('image/webp', $disk->mimeType('thumbs/thumb_legacy.webp'));
        $disk->assertMissing('thumbs/thumb_legacy.jpg');
    }

    public function test_photos_whose_files_are_missing_are_left_unchanged(): void
    {
        $photo = Photo::factory()->create(['thumb_path' => '/storage/photos/thumbs/thumb_lost.jpg']);
        Storage::disk('photos')->delete($photo->fileName());

        $this->artisan('photos:thumbnails')->assertSuccessful();

        $this->assertSame('/storage/photos/thumbs/thumb_lost.jpg', $photo->refresh()->thumb_path);
    }

    public function test_album_covers_get_a_thumbnail(): void
    {
        Storage::fake('local');
        Storage::putFileAs('public/trips', UploadedFile::fake()->image('cover.jpg', 1600, 1200), 'cover.jpg');
        $album = Album::factory()->create(['imagePath' => '/storage/trips/cover.jpg']);

        $this->artisan('photos:thumbnails')->assertSuccessful();

        $this->assertSame('/storage/albums/thumbs/trips-cover.webp', $album->refresh()->cover_thumb_path);
        $this->assertSame([Album::COVER_THUMB_SIZE, 450], array_slice(getimagesize(Storage::path('public/albums/thumbs/trips-cover.webp')), 0, 2));
        $this->assertSame('/storage/albums/thumbs/trips-cover.webp', $album->coverUrl());
    }

    public function test_trip_cover_images_get_their_webp_copies(): void
    {
        Storage::fake('local');
        Storage::putFileAs('public/trips', UploadedFile::fake()->image('cover.jpg', 1000, 750), 'cover.jpg');
        $trip = Trip::factory()->create(['imagePath' => '/storage/trips/cover.jpg', 'image_sizes' => null]);

        $this->artisan('photos:thumbnails')->assertSuccessful();

        $this->assertSame([
            480 => 'public/trips/sizes/cover-480.webp',
            960 => 'public/trips/sizes/cover-960.webp',
            1000 => 'public/trips/sizes/cover-1000.webp',
        ], $trip->refresh()->image_sizes);
        $this->assertSame([480, 360], array_slice(getimagesize(Storage::path('public/trips/sizes/cover-480.webp')), 0, 2));
    }
}
