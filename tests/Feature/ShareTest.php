<?php

namespace Tests\Feature;

use App\Enums\UserLevelEnum;
use App\Http\Controllers\ShareController;
use App\Livewire\Gallery;
use App\Livewire\PhotoDrawer;
use App\Models\Album;
use App\Models\Photo;
use App\Models\Share;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ShareTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_create_a_new_seven_day_link_for_a_photo_on_each_click(): void
    {
        $member = $this->actingAsMember();
        $photo = Photo::factory()->create();
        $this->freezeSecond();

        $component = Livewire::test(PhotoDrawer::class)
            ->call('show', $photo->id)
            ->call('createShareLink')
            ->call('createShareLink');

        $shares = $photo->shares()->get();
        $this->assertCount(2, $shares);
        $this->assertNotSame($shares[0]->token, $shares[1]->token);
        foreach ($shares as $share) {
            $this->assertTrue($share->user->is($member));
            $this->assertSame(7, $share->expires_in_days);
            $this->assertTrue($share->expires_at->eq(now()->addDays(7)));
        }
        $component->assertSee(route('shares.show', $shares[1]));
    }

    public function test_members_create_a_link_for_the_selected_album(): void
    {
        $this->actingAsMember();
        $album = Album::factory()->create();

        Livewire::withQueryParams(['albums' => [$album->id]])
            ->test(Gallery::class)
            ->call('shareAlbum')
            ->assertSee(route('shares.show', $album->shares()->sole()));
    }

    public function test_guest_level_users_cannot_create_links(): void
    {
        $this->actingAsLevel(UserLevelEnum::GUEST);
        $album = Album::factory()->create();

        Livewire::withQueryParams(['albums' => [$album->id]])
            ->test(Gallery::class)
            ->call('shareAlbum')
            ->assertForbidden();
        $this->assertSame(0, Share::count());
    }

    public function test_visitors_see_the_shared_photo_alone(): void
    {
        $photo = Photo::factory()->create(['title' => 'IMG_2034.jpg']);
        $otherPhoto = Photo::factory()->for($photo->album)->create();
        $share = Share::factory()->for($photo, 'shareable')->create();

        $response = $this->get(route('shares.show', $share));

        $response->assertOk()->assertViewIs('share.photo')->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $response->assertSee(route('shares.file', [$share, $photo]))->assertSee('IMG_2034.jpg');
        $response->assertDontSee(route('shares.file', [$share, $otherPhoto]));
        $this->get(route('shares.file', [$share, $photo]))->assertOk();
        $this->get(route('shares.thumbnail', [$share, $photo]))->assertOk();
    }

    public function test_a_photo_link_does_not_open_other_photos(): void
    {
        $photo = Photo::factory()->create();
        $otherPhoto = Photo::factory()->for($photo->album)->create();
        $share = Share::factory()->for($photo, 'shareable')->create();

        $this->get(route('shares.file', [$share, $otherPhoto]))->assertNotFound();
        $this->get(route('shares.thumbnail', [$share, $otherPhoto]))->assertNotFound();
        $this->get(route('shares.photos', $share))->assertNotFound();
        // The member routes stay closed to visitors
        $this->get($photo->path)->assertRedirect(route('login'));
    }

    public function test_visitors_see_the_photos_of_a_shared_album_only(): void
    {
        $album = Album::factory()->create(['title' => 'Croisière à Groix']);
        $albumPhoto = Photo::factory()->for($album)->create();
        $otherPhoto = Photo::factory()->for(Album::factory())->create();
        $share = Share::factory()->for($album, 'shareable')->create();

        $response = $this->get(route('shares.show', $share));

        $response->assertOk()->assertViewIs('share.album')->assertSee('Croisière à Groix');
        $response->assertSee(route('shares.thumbnail', [$share, $albumPhoto]));
        $response->assertDontSee(route('shares.thumbnail', [$share, $otherPhoto]));
        $this->get(route('shares.file', [$share, $albumPhoto]))->assertOk();
        $this->get(route('shares.file', [$share, $otherPhoto]))->assertNotFound();
    }

    public function test_shared_album_photos_load_by_pages(): void
    {
        $album = Album::factory()->create();
        $photos = Photo::factory()->for($album)->count(ShareController::PER_PAGE + 1)->create(['taken_at' => null]);
        $share = Share::factory()->for($album, 'shareable')->create();
        $secondPage = route('shares.photos', [$share, 'page' => 2]);

        $this->get(route('shares.show', $share))
            ->assertViewHas('nextPageUrl', $secondPage)
            ->assertDontSee(route('shares.thumbnail', [$share, $photos->last()]));

        $this->get($secondPage)
            ->assertOk()
            ->assertHeader('X-Next-Page', '')
            ->assertSee(route('shares.thumbnail', [$share, $photos->last()]))
            ->assertDontSee(route('shares.thumbnail', [$share, $photos->first()]));
    }

    public function test_links_stop_working_after_seven_days(): void
    {
        $photo = Photo::factory()->create();
        $share = Share::createFor($photo, $this->createUser());

        $this->travel(7)->days();
        $this->travel(-1)->minutes();
        $this->get(route('shares.show', $share))->assertOk();

        $this->travel(2)->minutes();
        $this->get(route('shares.show', $share))->assertStatus(410)->assertViewIs('share.expired');
        $this->get(route('shares.file', [$share, $photo]))->assertNotFound();
    }

    public function test_links_stop_working_once_the_photo_is_deleted(): void
    {
        $photo = Photo::factory()->create();
        $share = Share::factory()->for($photo, 'shareable')->create();

        $photo->delete();

        $this->get(route('shares.show', $share))->assertStatus(410);
        $this->get(route('shares.file', [$share, $photo->id]))->assertNotFound();
    }

    public function test_unknown_links_are_not_found(): void
    {
        $this->get(route('shares.show', 'inconnu'))->assertNotFound();
    }

    public function test_members_opening_a_link_go_to_the_gallery(): void
    {
        $this->actingAsMember();
        $photo = Photo::factory()->create();
        $photoShare = Share::factory()->for($photo, 'shareable')->create();
        $albumShare = Share::factory()->for($photo->album, 'shareable')->create();

        $this->get(route('shares.show', $photoShare))->assertRedirect(route('photos.show', $photo));
        $this->get(route('shares.show', $albumShare))
            ->assertRedirect(route('photos.index', ['albums' => [$photo->album_id]]));
    }

    public function test_links_expired_for_a_month_are_pruned(): void
    {
        $recent = Share::factory()->expired()->create();
        $old = Share::factory()->create(['expires_at' => now()->subMonth()->subDay()]);

        $this->artisan('model:prune', ['--model' => Share::class])->assertSuccessful();

        $this->assertModelExists($recent);
        $this->assertModelMissing($old);
    }
}
