<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Gallery;
use App\Models\Album;
use App\Models\Photo;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Identifiants des photos de la mosaïque, dans l'ordre de la page.
     *
     * @return list<int>
     */
    private function displayedPhotoIds(Testable $component): array
    {
        preg_match_all('#<a href="[^"]*/photos/(\d+)"[^>]*\bdata-gallery-photo\b#', $component->html(), $matches);

        return array_map('intval', $matches[1]);
    }

    /**
     * Titres des groupes de la mosaïque, avec leurs détails, dans l'ordre de la page.
     *
     * @return list<string>
     */
    private function displayedGroupHeadings(Testable $component): array
    {
        preg_match_all('#<h2 wire:key="group-[^"]*"[^>]*>(.*?)</h2>#s', $component->html(), $matches);

        return array_map(fn (string $heading): string => trim((string) preg_replace('/\s+/', ' ', strip_tags($heading))), $matches[1]);
    }

    /**
     * Crée des photos identiques sans dessiner une image pour chacune, comme le ferait la fabrique.
     *
     * @param  array<string, mixed>  $attributes
     * @return Collection<int, Photo>
     */
    private function createPhotos(int $count, array $attributes): Collection
    {
        $first = Photo::factory()->create($attributes);

        return collect([$first])->concat(collect(range(2, $count))->map(fn (): Photo => tap($first->replicate())->save()));
    }

    public function test_photos_are_grouped_by_day_newest_first_with_undated_photos_last(): void
    {
        $this->actingAsMember();
        $album = Album::factory()->create();
        $undated = Photo::factory()->for($album)->create(['taken_at' => null]);
        $morning = Photo::factory()->for($album)->create(['taken_at' => '2025-06-14 09:00']);
        $evening = Photo::factory()->for($album)->create(['taken_at' => '2025-06-14 19:00']);
        $nextDay = Photo::factory()->for($album)->create(['taken_at' => '2025-06-15 10:00']);

        $component = Livewire::test(Gallery::class);

        $this->assertSame([$nextDay->id, $evening->id, $morning->id, $undated->id], $this->displayedPhotoIds($component));
        $this->assertSame(
            ['Dimanche 15 juin 2025 1 photo', 'Samedi 14 juin 2025 2 photos', 'Date inconnue 1 photo'],
            $this->displayedGroupHeadings($component),
        );
    }

    public function test_oldest_first_order_keeps_undated_photos_last(): void
    {
        $this->actingAsMember();
        $album = Album::factory()->create();
        $undated = Photo::factory()->for($album)->create(['taken_at' => null]);
        $recent = Photo::factory()->for($album)->create(['taken_at' => '2025-06-15 10:00']);
        $old = Photo::factory()->for($album)->create(['taken_at' => '2024-06-14 10:00']);

        $component = Livewire::test(Gallery::class)->set('sort', 'anciennes');

        $this->assertSame([$old->id, $recent->id, $undated->id], $this->displayedPhotoIds($component));
    }

    public function test_photos_can_be_grouped_by_album_in_the_order_of_the_album_dates(): void
    {
        $this->actingAsMember();
        $olderAlbum = Album::factory()->create(['title' => 'Glénan', 'date' => '2024-07-01']);
        $newerAlbum = Album::factory()->create(['title' => 'Belle-Île', 'date' => '2025-06-01']);
        $lateScan = Photo::factory()->for($olderAlbum)->create(['taken_at' => '2025-08-01']);
        $firstDay = Photo::factory()->for($newerAlbum)->create(['taken_at' => '2025-06-02']);
        $secondDay = Photo::factory()->for($newerAlbum)->create(['taken_at' => '2025-06-03']);

        $component = Livewire::test(Gallery::class)->set('grouping', 'album');

        $this->assertSame([$secondDay->id, $firstDay->id, $lateScan->id], $this->displayedPhotoIds($component));
        $this->assertSame(
            ['Belle-Île Juin 2025 · 2 photos', 'Glénan Juillet 2024 · 1 photo'],
            $this->displayedGroupHeadings($component),
        );
    }

    public function test_ungrouped_photos_have_no_headings(): void
    {
        $this->actingAsMember();
        $album = Album::factory()->create();
        $old = Photo::factory()->for($album)->create(['taken_at' => '2024-06-14 10:00']);
        $recent = Photo::factory()->for($album)->create(['taken_at' => '2025-06-15 10:00']);

        $component = Livewire::test(Gallery::class)->set('grouping', 'toutes');

        $this->assertSame([$recent->id, $old->id], $this->displayedPhotoIds($component));
        $this->assertSame([], $this->displayedGroupHeadings($component));
    }

    public function test_unknown_grouping_and_order_in_the_url_fall_back_to_the_defaults(): void
    {
        $this->actingAsMember();
        $album = Album::factory()->create();
        $old = Photo::factory()->for($album)->create(['taken_at' => '2024-06-14 10:00']);
        $recent = Photo::factory()->for($album)->create(['taken_at' => '2025-06-15 10:00']);

        $component = Livewire::withQueryParams(['vue' => 'carte', 'tri' => 'hasard'])->test(Gallery::class);

        $this->assertSame([$recent->id, $old->id], $this->displayedPhotoIds($component));
        $this->assertSame(['Dimanche 15 juin 2025 1 photo', 'Vendredi 14 juin 2024 1 photo'], $this->displayedGroupHeadings($component));
    }

    public function test_photos_can_be_filtered_by_uploader_and_album(): void
    {
        $user = $this->actingAsMember();
        $album = Album::factory()->create();
        $mineInAlbum = Photo::factory()->for($album)->for($user)->create();
        $mineElsewhere = Photo::factory()->for(Album::factory())->for($user)->create();
        $theirsInAlbum = Photo::factory()->for($album)->for(User::factory())->create();
        $component = Livewire::test(Gallery::class);

        $component->set('usersFilter', [$user->id]);
        $this->assertEqualsCanonicalizing([$mineInAlbum->id, $mineElsewhere->id], $this->displayedPhotoIds($component));

        $component->set('albumsFilter', [$album->id]);
        $this->assertSame([$mineInAlbum->id], $this->displayedPhotoIds($component));

        $component->call('removeUserFilter', $user->id);
        $this->assertEqualsCanonicalizing([$mineInAlbum->id, $theirsInAlbum->id], $this->displayedPhotoIds($component));

        $component->call('resetFilters');
        $this->assertCount(3, $this->displayedPhotoIds($component));
    }

    public function test_it_can_be_filtered_on_a_user_from_the_url(): void
    {
        $this->actingAsMember();
        $album = Album::factory()->create();
        $sailor = User::factory()->create(['name' => 'Henry']);
        $photo = Photo::factory()->for($album)->for($sailor)->create();
        Photo::factory()->for($album)->for(User::factory())->create();

        $component = Livewire::withQueryParams(['auteurs' => [$sailor->id]])->test(Gallery::class);

        $this->assertSame([$photo->id], $this->displayedPhotoIds($component));
        $component->assertSee('Voir toutes les photos');
    }

    public function test_album_strip_lists_albums_with_photos_newest_first(): void
    {
        $this->actingAsMember();
        $old = Album::factory()->create(['date' => '2023-05-01']);
        $recent = Album::factory()->create(['date' => '2025-05-01']);
        Album::factory()->create(['date' => '2025-09-01']);
        Photo::factory()->for($old)->create();
        Photo::factory()->for($recent)->create();

        $html = Livewire::test(Gallery::class)->html();

        preg_match_all('/wire:click="selectAlbum\((\d+)\)"/', $html, $matches);
        $this->assertSame([$recent->id, $old->id], array_map('intval', $matches[1]));
    }

    public function test_choosing_an_album_in_the_strip_toggles_its_filter(): void
    {
        $this->actingAsMember();
        $album = Album::factory()->create();
        Photo::factory()->for($album)->create();
        $component = Livewire::test(Gallery::class)->set('albumsFilter', ['12', '13']);

        $component->call('selectAlbum', $album->id);
        $component->assertSet('albumsFilter', [$album->id]);

        $component->call('selectAlbum', $album->id);
        $component->assertSet('albumsFilter', []);
    }

    public function test_an_album_shown_alone_is_presented_above_its_photos(): void
    {
        $this->actingAsMember();
        $album = Album::factory()->create(['description' => 'Une semaine en Bretagne sud']);
        $trip = Trip::factory()->create();
        $album->trips()->attach($trip);
        Photo::factory()->for($album)->for(User::factory())->create();

        $component = Livewire::withQueryParams(['albums' => [$album->id]])->test(Gallery::class);

        $component->assertSee('Une semaine en Bretagne sud')
            ->assertSee(route('trips.show', $trip))
            ->assertSee(route('photos.create', $album))
            ->assertSee(route('albums.edit', $album))
            // L'album contient la photo d'un autre matelot : il ne peut pas le supprimer
            ->assertDontSee('Supprimer l\'album');
    }

    public function test_adding_photos_without_a_chosen_album_offers_every_album_including_empty_ones(): void
    {
        $this->actingAsMember();
        $emptyAlbum = Album::factory()->create();
        $albumWithPhotos = Album::factory()->create();
        Photo::factory()->for($albumWithPhotos)->create();

        $component = Livewire::test(Gallery::class);

        $component->assertSee(route('photos.create', $emptyAlbum))->assertSee(route('photos.create', $albumWithPhotos));
    }

    public function test_photos_load_by_pages_and_a_day_continues_on_the_next_page(): void
    {
        $this->actingAsMember();
        $photos = $this->createPhotos(101, ['taken_at' => '2025-06-14 10:00']);
        $oldestId = $photos->first()->id;
        $component = Livewire::test(Gallery::class);

        $firstPage = $this->displayedPhotoIds($component);
        $this->assertCount(100, $firstPage);
        $this->assertNotContains($oldestId, $firstPage);
        $this->assertSame(['Samedi 14 juin 2025 101 photos'], $this->displayedGroupHeadings($component));
        $component->assertSet('lastPage', 2);

        // La page ajoutée ne contient que les nouvelles photos, sans répéter le titre du jour commencé
        $component->call('loadMore');
        $this->assertSame([$oldestId], $this->displayedPhotoIds($component));
        $this->assertSame([], $this->displayedGroupHeadings($component));
        $component->assertSet('loadedPages', 2);

        $component->call('loadMore');
        $this->assertSame([], $this->displayedPhotoIds($component));
        $component->assertSet('loadedPages', 2);

        $component->set('sort', 'anciennes');
        $this->assertCount(100, $this->displayedPhotoIds($component));
        $component->assertSet('loadedPages', 1);
    }

    public function test_lightbox_offers_to_edit_only_the_viewers_own_photos(): void
    {
        $user = $this->actingAsMember();
        $mine = Photo::factory()->for($user)->create();
        $theirs = Photo::factory()->for(User::factory())->create();

        $component = Livewire::test(Gallery::class);

        $component->assertSee(route('photos.edit', $mine))->assertDontSee(route('photos.edit', $theirs));
    }

    public function test_lightbox_offers_admins_to_edit_any_photo(): void
    {
        $this->actingAsAdmin();
        $theirs = Photo::factory()->for(User::factory())->create();

        $component = Livewire::test(Gallery::class);

        $component->assertSee(route('photos.edit', $theirs));
    }

    public function test_mass_deletion_only_deletes_authorized_photos(): void
    {
        $user = $this->actingAsMember();
        $mine = Photo::factory()->for(Album::factory())->create(['user_id' => $user->id]);
        $notMine = Photo::factory()->for(Album::factory())->create(['user_id' => $this->createUser()->id]);

        Livewire::test(Gallery::class)->dispatch('deleteSelected', selectedIds: [$mine->id, $notMine->id]);

        $this->assertSoftDeleted($mine);
        $this->assertNotSoftDeleted($notMine);
    }

    public function test_users_filter_lists_only_users_with_photos(): void
    {
        $user = $this->actingAsMember();
        $this->createUser();
        Photo::factory()->for(Album::factory())->create(['user_id' => $user->id]);

        /** @var Gallery $gallery */
        $gallery = Livewire::test(Gallery::class)->instance();

        $this->assertSame([$user->id], $gallery->uploaders->pluck('id')->all());
    }
}
