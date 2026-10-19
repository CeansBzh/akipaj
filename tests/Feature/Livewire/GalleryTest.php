<?php

namespace Tests\Feature\Livewire;

use App\Http\Livewire\Gallery;
use App\Http\Livewire\Lightbox;
use App\Models\Album;
use App\Models\Comment;
use App\Models\Photo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Livewire;
use Livewire\Testing\TestableLivewire;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Get the ids of the photos displayed by the component.
     *
     * @return array<int, int>
     */
    protected function displayedIds(TestableLivewire $component): array
    {
        /** @var LengthAwarePaginator<Photo> $photos */
        $photos = $component->viewData('photos');

        return $photos->pluck('id')->all();
    }

    public function test_it_lists_all_photos_by_default_sorted_by_date_taken(): void
    {
        $this->actingAsMember();
        $old = Photo::factory()->for(Album::factory())->create(['taken_at' => '2020-01-01']);
        $recent = Photo::factory()->for(Album::factory())->create(['taken_at' => '2023-01-01']);

        $component = Livewire::test(Gallery::class);

        $this->assertSame([$recent->id, $old->id], $this->displayedIds($component));
        $this->assertSame([$old->id, $recent->id], $this->displayedIds($component->set('sortTerm', 'taken_asc')));
    }

    public function test_it_can_be_restricted_to_a_list_of_photos(): void
    {
        $this->actingAsMember();
        $album = Album::factory()->create(['date' => '2022-05-01']);
        $included = Photo::factory()->for($album)->create();
        Photo::factory()->for(Album::factory())->create();

        $component = Livewire::test(Gallery::class, ['photoIds' => [$included->id]]);

        $this->assertSame([$included->id], $this->displayedIds($component));
        // Filters only offer the users and albums of the listed photos
        $this->assertSame([$included->user_id], $component->get('users')->pluck('id')->all());
        $this->assertSame(['2022'], array_map('strval', array_keys($component->get('albums')->all())));
    }

    public function test_it_can_be_searched_and_filtered(): void
    {
        $user = $this->actingAsMember();
        $album = Album::factory()->create();
        $boat = Photo::factory()->for($album)->create(['title' => 'Voilier au port', 'user_id' => $user->id]);
        $other = $this->createUser();
        $sunset = Photo::factory()->for(Album::factory())->create(['title' => 'Coucher de soleil', 'legend' => 'Vu du voilier', 'user_id' => $other->id]);
        Photo::factory()->for(Album::factory())->create(['title' => 'Plage', 'legend' => null, 'user_id' => $other->id]);

        $component = Livewire::test(Gallery::class)->set('searchTerm', 'voilier');
        $this->assertEqualsCanonicalizing([$boat->id, $sunset->id], $this->displayedIds($component));

        $component->set('usersFilter', [$user->id]);
        $this->assertSame([$boat->id], $this->displayedIds($component));

        $component->call('resetFilters')->set('albumsFilter', [$album->id]);
        $this->assertSame([$boat->id], $this->displayedIds($component));
    }

    public function test_it_can_be_sorted_by_number_of_comments(): void
    {
        $this->actingAsMember();
        $quiet = Photo::factory()->for(Album::factory())->create();
        $popular = Photo::factory()->for(Album::factory())->create();
        Comment::factory(2)->for($popular, 'commentable')->create();

        $component = Livewire::test(Gallery::class)->set('sortTerm', 'most_comments');

        $this->assertSame([$popular->id, $quiet->id], $this->displayedIds($component));
    }

    public function test_mass_deletion_only_deletes_authorized_photos(): void
    {
        $user = $this->actingAsMember();
        $mine = Photo::factory()->for(Album::factory())->create(['user_id' => $user->id]);
        $notMine = Photo::factory()->for(Album::factory())->create(['user_id' => $this->createUser()->id]);

        Livewire::test(Gallery::class)->emit('deleteSelected', [$mine->id, $notMine->id]);

        $this->assertSoftDeleted($mine);
        $this->assertNotSoftDeleted($notMine);
    }

    public function test_lightbox_loads_the_requested_photo(): void
    {
        $this->actingAsMember();
        $first = Photo::factory()->for(Album::factory())->create();
        $second = Photo::factory()->for(Album::factory())->create(['title' => 'Seconde photo']);

        $component = Livewire::test(Lightbox::class);
        $this->assertSame($first->id, $component->get('photo')->id);

        $component->emit('openPhotoLightbox', $second->id)
            ->assertDispatchedBrowserEvent('open-lightbox')
            ->assertSee('Seconde photo');
        $this->assertSame($second->id, $component->get('photo')->id);
    }

    public function test_users_filter_lists_only_users_with_photos(): void
    {
        $user = $this->actingAsMember();
        $this->createUser();
        Photo::factory()->for(Album::factory())->create(['user_id' => $user->id]);

        /** @var Collection<int, \App\Models\User> $users */
        $users = Livewire::test(Gallery::class)->get('users');

        $this->assertSame([$user->id], $users->pluck('id')->all());
    }
}
