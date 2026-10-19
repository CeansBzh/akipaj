<?php

namespace Tests\Feature\Livewire;

use App\Enums\UserLevelEnum;
use App\Events\RegistrationValidated;
use App\Livewire\Admin\RegistrationValidation;
use App\Livewire\Admin\User\Catalog;
use App\Livewire\Album\AlbumList;
use App\Livewire\SearchInput;
use App\Livewire\ThreadSubscribeButton;
use App\Livewire\Trip\Boats;
use App\Models\Album;
use App\Models\Article;
use App\Models\Event;
use App\Models\Photo;
use App\Models\Trip;
use App\Models\User;
use App\Notifications\RegistrationValidated as RegistrationValidatedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ComponentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_needs_at_least_three_characters(): void
    {
        $this->actingAsMember();
        Trip::factory()->create(['title' => 'Le Tour']);

        Livewire::test(SearchInput::class)->set('searchTerm', ' To ')->assertSet('searchResults', []);
    }

    public function test_search_looks_into_every_kind_of_content(): void
    {
        $this->actingAsMember();
        $trip = Trip::factory()->create(['title' => 'Croisière en Bretagne']);
        $album = Album::factory()->create(['title' => 'Photos de Bretagne']);
        $photo = Photo::factory()->for($album)->create(['title' => 'Phare', 'legend' => 'Un phare breton en Bretagne']);
        $article = Article::factory()->create(['title' => 'Bretagne 2023']);
        $event = Event::factory()->create(['name' => 'Fête de la Bretagne']);
        Trip::factory()->create(['title' => 'Méditerranée', 'description' => 'Au soleil']);

        $results = Livewire::test(SearchInput::class)->set('searchTerm', 'bretagne')->get('searchResults');

        $this->assertSame([['title' => $trip->title, 'url' => route('trips.show', $trip)]], $results['Sorties']);
        $this->assertSame([['title' => $album->title, 'url' => route('albums.show', $album)]], $results['Albums']);
        $this->assertSame([['title' => $photo->title, 'url' => route('photos.show', $photo)]], $results['Photos']);
        $this->assertSame([['title' => $article->title, 'url' => route('articles.show', $article)]], $results['Articles']);
        $this->assertSame([['title' => $event->name, 'url' => route('events.show', $event)]], $results['Événements']);
    }

    public function test_subscribe_button_reflects_and_toggles_the_subscription(): void
    {
        $user = $this->actingAsMember();
        $photo = Photo::factory()->create(['user_id' => $this->createUser()->id]);

        $component = Livewire::test(ThreadSubscribeButton::class, ['commentable' => $photo])
            ->assertSet('subscribeToThread', false)
            ->set('subscribeToThread', true);
        $this->assertTrue($user->refresh()->isSubscribedTo($photo));

        $component->set('subscribeToThread', false);
        $this->assertFalse($user->refresh()->isSubscribedTo($photo));
    }

    public function test_subscribe_button_is_checked_for_subscribers(): void
    {
        $user = $this->actingAsMember();
        $photo = Photo::factory()->create(['user_id' => $user->id]);

        Livewire::test(ThreadSubscribeButton::class, ['commentable' => $photo])->assertSet('subscribeToThread', true);
    }

    public function test_admin_can_accept_a_registration(): void
    {
        Notification::fake();
        $this->actingAsAdmin();
        $guest = $this->createUser(UserLevelEnum::GUEST, ['name' => 'Lucien']);

        Livewire::test(RegistrationValidation::class)->assertSee('Lucien')->call('accept', $guest->id);

        $this->assertSame(UserLevelEnum::MEMBER, $guest->refresh()->level);
        Notification::assertSentTo($guest, RegistrationValidatedNotification::class);
    }

    public function test_admin_can_reject_a_registration(): void
    {
        $this->actingAsAdmin();
        $guest = $this->createUser(UserLevelEnum::GUEST);

        Livewire::test(RegistrationValidation::class)->call('reject', $guest->id);

        $this->assertSoftDeleted($guest);
    }

    public function test_only_admins_can_validate_registrations(): void
    {
        EventFacade::fake();
        $this->actingAsMember();
        $guest = $this->createUser(UserLevelEnum::GUEST);

        Livewire::test(RegistrationValidation::class)->call('accept', $guest->id)->assertForbidden();
        Livewire::test(RegistrationValidation::class)->call('reject', $guest->id)->assertForbidden();

        $this->assertSame(UserLevelEnum::GUEST, $guest->refresh()->level);
        $this->assertNotSoftDeleted($guest);
        EventFacade::assertNotDispatched(RegistrationValidated::class);
    }

    public function test_user_catalog_can_be_searched_and_filtered_by_level(): void
    {
        $admin = $this->actingAsAdmin();
        $henry = $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Henry', 'email' => 'henry@example.com']);
        $lucien = $this->createUser(UserLevelEnum::GUEST, ['name' => 'Lucien', 'email' => 'lucien@example.com']);

        $component = Livewire::test(Catalog::class);
        $this->assertEqualsCanonicalizing([$admin->id, $henry->id, $lucien->id], $component->viewData('users')->pluck('id')->all());

        $component->set('searchTerm', 'henry@');
        $this->assertSame([$henry->id], $component->viewData('users')->pluck('id')->all());

        $component->set('searchTerm', '')->set('level', UserLevelEnum::GUEST->value);
        $this->assertSame([$lucien->id], $component->viewData('users')->pluck('id')->all());
    }

    public function test_album_list_only_shows_albums_with_photos_and_can_be_sorted(): void
    {
        $this->actingAsMember();
        $old = Album::factory()->create(['date' => '2019-01-01']);
        $recent = Album::factory()->create(['date' => '2022-01-01']);
        Album::factory()->create();
        Photo::factory()->for($old)->create();
        Photo::factory()->for($recent)->create();

        $component = Livewire::test(AlbumList::class);
        $this->assertSame([$recent->id, $old->id], $component->viewData('albums')->pluck('id')->all());

        $component->set('sortTerm', 'date_asc');
        $this->assertSame([$old->id, $recent->id], $component->viewData('albums')->pluck('id')->all());

        $component->set('sortTerm', 'date_desc');
        $this->assertSame([$recent->id, $old->id], $component->viewData('albums')->pluck('id')->all());
    }

    public function test_boats_can_be_added_and_removed_in_the_trip_form(): void
    {
        $this->actingAsAdmin();

        $component = Livewire::test(Boats::class)
            ->call('addBoat')
            ->call('addBoat')
            ->set('boats.1.name', 'Second');
        $this->assertCount(2, $component->get('boats'));

        $component->call('deleteBoat', 0);
        $this->assertSame('Second', $component->get('boats')[0]['name']);

        $component->call('deleteBoat', 0)->assertSet('boats', null);
    }

    public function test_registration_validation_lists_pending_accounts_only(): void
    {
        $this->actingAsAdmin();
        $this->createUser(UserLevelEnum::GUEST, ['name' => 'Lucien']);
        $this->createUser(UserLevelEnum::MEMBER, ['name' => 'Henry']);

        Livewire::test(RegistrationValidation::class)->assertSee('Lucien')->assertDontSee('Henry');
        $this->assertSame(2, User::where('level', '>', UserLevelEnum::GUEST)->count());
    }
}
